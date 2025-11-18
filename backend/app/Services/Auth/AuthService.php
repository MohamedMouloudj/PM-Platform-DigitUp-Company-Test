<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\LoginUserDTO;
use App\DTOs\Auth\RegisterUserDTO;
use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Models\SecurityAlert;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user
     *
     * @throws \Exception
     */
    public function register(RegisterUserDTO $dto): User
    {
        $user = $this->userRepository->create([
            'id' => Str::uuid(),
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
            'role' => $dto->role,
        ]);

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->event('registered')
            ->log('User registered');

        return $user;
    }

    /**
     * Authenticate user and create session
     *
     * @throws AuthenticationException
     */
    public function login(LoginUserDTO $dto): User
    {
        $credentials = [
            'email' => $dto->email,
            'password' => $dto->password,
        ];

        // Check if user exists
        $userExists = $this->userRepository->findByEmail($dto->email);
        if (!$userExists) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (!Auth::attempt($credentials, $dto->remember)) {
            throw new AuthenticationException('Invalid credentials');
        }

        /** @var User $user */
        $user = Auth::user();

        // Regenerate session to prevent fixation attacks
        request()->session()->regenerate();

        // Check for suspicious login (different IP)
        $currentIp = request()->ip() ?? '127.0.0.1';
        if ($user->last_login_ip && $user->last_login_ip !== $currentIp) {
            $this->createSecurityAlert($user, $currentIp);
        }

        $this->userRepository->updateLoginInfo($user, $currentIp);

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'ip' => $currentIp,
                'user_agent' => request()->userAgent(),
            ])
            ->event('login')
            ->log('User logged in');

        return $user;
    }

    /**
     * Logout user and invalidate session
     */
    public function logout(User $user): void
    {
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
            ])
            ->event('logout')
            ->log('User logged out');

        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Get authenticated user
     *
     * @throws AuthenticationException
     */
    public function getAuthenticatedUser(): User
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            throw new AuthenticationException('User not authenticated');
        }

        return $user;
    }

    /**
     * Create security alert for new IP login
     */
    private function createSecurityAlert(User $user, string $newIp): void
    {
        SecurityAlert::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'alert_type' => AlertType::NEW_LOCATION,
            'severity' => AlertSeverity::MEDIUM,
            'ip_address' => $newIp,
            'details' => [
                'previous_ip' => $user->last_login_ip,
                'new_ip' => $newIp,
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ],
        ]);

        activity()
            ->causedBy($user)
            ->withProperties([
                'previous_ip' => $user->last_login_ip,
                'new_ip' => $newIp,
            ])
            ->event('security_alert')
            ->log('New location login detected');
    }
}
