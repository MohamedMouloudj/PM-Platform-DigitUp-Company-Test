<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\DTOs\Auth\LoginUserDTO;
use App\DTOs\Auth\RegisterUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $dto = RegisterUserDTO::fromRequest($request->validated());
            $user = $this->authService->register($dto);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role->value,
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Authenticate user and create session
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = LoginUserDTO::fromRequest($request->validated());
            $user = $this->authService->login($dto);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role->value,
                    ],
                ],
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
                'errors' => ['email' => ['The provided credentials are incorrect.']],
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'errors' => ['error' => [$e->getMessage()]],
            ], 500);
        }
    }

    /**
     * Logout user and invalidate session
     */
    public function logout(): JsonResponse
    {
        try {
            $user = $this->authService->getAuthenticatedUser();
            $this->authService->logout($user);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }
    }

    /**
     * Get authenticated user
     */
    public function user(): JsonResponse
    {
        try {
            $user = $this->authService->getAuthenticatedUser();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role->value,
                        'two_factor_enabled' => $user->two_factor_enabled,
                        'last_login_at' => $user->last_login_at?->toISOString(),
                        'created_at' => $user->created_at->toISOString(),
                    ],
                ],
            ]);
        } catch (AuthenticationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }
    }
}
