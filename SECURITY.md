# Security Implementation

#### Authentication & Authorization

**Laravel Sanctum SPA Authentication**

Session-based authentication using cookies with CSRF protection:

```php
// AuthController.php - Login endpoint
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
    }
}
```

**Rate Limiting on Authentication**

IP-based rate limiting prevents brute force attacks:

```php
// LoginRequest.php
public function authorize(): bool
{
    return RateLimiter::attempt(
        'login:' . $this->ip(),
        5,  // 5 attempts
        fn() => true,
        60  // per 60 seconds
    );
}

protected function failedAuthorization(): void
{
    throw ValidationException::withMessages([
        'email' => ['Too many login attempts. Please try again in '
            . RateLimiter::availableIn('login:' . $this->ip()) . ' seconds.'],
    ]);
}
```

**Role-Based Access Control (RBAC)**

Enum-based roles with middleware protection:

```php
// User model
enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case MEMBER = 'member';
    case GUEST = 'guest';
}

// Service layer permission checks
public function canUserManage(User $user, Project $project): bool
{
    if ($user->role === UserRole::ADMIN) {
        return true;
    }

    if ($project->created_by === $user->id) {
        return true;
    }

    $permission = ProjectPermission::where('project_id', $project->id)
        ->where('user_id', $user->id)
        ->whereIn('permission', [PermissionType::MANAGE, PermissionType::WRITE])
        ->exists();

    return $permission;
}
```

**Frontend Role Guards**

```typescript
// RoleGuard.tsx - HOC for role-based route protection
export const RoleGuard: React.FC<RoleGuardProps> = ({
  children,
  allowedRoles,
  redirectTo = "/dashboard",
}) => {
  const { user } = useAuth();

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (!allowedRoles.includes(user.role)) {
    toast.error("Access denied");
    return <Navigate to={redirectTo} replace />;
  }

  return <>{children}</>;
};

// Usage in routes
<Route
  path="/projects/create"
  element={
    <RoleGuard allowedRoles={["admin", "manager"]}>
      <CreateProjectPage />
    </RoleGuard>
  }
/>;
```

#### Input Validation & Sanitization

**Form Request Validation**

All endpoints use dedicated Form Request classes:

```php
// CreateProjectRequest.php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'description' => ['required', 'string', 'max:5000'],
        'status' => ['required', new Enum(ProjectStatus::class)],
        'confidentiality_level' => ['required', new Enum(ConfidentialityLevel::class)],
    ];
}

// CreateTaskRequest.php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['required', 'string', 'max:2000'],
        'priority' => ['required', new Enum(TaskPriority::class)],
        'status' => ['required', new Enum(TaskStatus::class)],
        'assigned_to' => ['nullable', 'uuid', 'exists:users,id'],
        'deadline' => ['nullable', 'date', 'after:today'],
    ];
}
```

**XSS Protection**

Laravel automatic escaping in views combined with strip_tags for user content:

```php
// CommentService.php
public function create(CreateCommentDTO $dto, User $user): Comment
{
    return DB::transaction(function () use ($dto, $user) {
        $comment = $this->commentRepository->create([
            'id' => Str::uuid(),
            'task_id' => $dto->task_id,
            'user_id' => $user->id,
            'content' => strip_tags($dto->content, '<p><br><strong><em>'),
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($comment)
            ->event('created')
            ->log('Comment created on task');

        return $comment;
    });
}
```

#### Security Monitoring

**IP Tracking & Suspicious Login Detection**

Automatic security alerts for new IP addresses:

```php
// AuthService.php - Login method
$currentIp = request()->ip() ?? '127.0.0.1';

// Check for different IP
if ($user->last_login_ip && $user->last_login_ip !== $currentIp) {
    $this->createSecurityAlert($user, $currentIp);
}

$this->userRepository->updateLoginInfo($user, $currentIp);

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
}
```

**Activity Logging**

Comprehensive audit trail using spatie/laravel-activitylog:

```php
// All critical operations are logged
activity()
    ->causedBy($user)
    ->performedOn($project)
    ->withProperties([
        'old' => $project->getOriginal(),
        'new' => $project->getAttributes(),
    ])
    ->event('updated')
    ->log('Project updated');

// Login/Logout tracking
activity()
    ->causedBy($user)
    ->performedOn($user)
    ->withProperties([
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ])
    ->event('login')
    ->log('User logged in');
```

#### Data Protection

**Encryption for Sensitive Data**

Automatic encryption for top-secret projects:

```php
// ProjectService.php
public function create(CreateProjectDTO $dto, User $user): Project
{
    $data = [
        'id' => Str::uuid(),
        'name' => $dto->name,
        'description' => $dto->description,
        'status' => $dto->status,
        'confidentiality_level' => $dto->confidentiality_level,
        'created_by' => $user->id,
    ];

    // Encrypt description if top secret
    if ($dto->confidentiality_level === ConfidentialityLevel::TOP_SECRET) {
        $data['description'] = encrypt($dto->description);
    }

    $project = $this->projectRepository->create($data);
    return $project;
}
```

**Soft Deletes**

User, Project, and Task models use soft deletes for data recovery:

```php
// User model
use SoftDeletes;

protected $dates = ['deleted_at'];

// Restore deleted records
$user = User::withTrashed()->find($id);
$user->restore();
```

#### Session Security

**Session Fixation Prevention**

Session regeneration on authentication state changes:

```php
// AuthService.php - Login
Auth::attempt($credentials, $dto->remember);
request()->session()->regenerate();

// Logout
Auth::guard('web')->logout();
request()->session()->invalidate();
request()->session()->regenerateToken();
```

**CSRF Protection**

Frontend obtains CSRF cookie before making authenticated requests:

```typescript
// api.ts - Axios configuration
export const getCsrfToken = async (): Promise<void> => {
  if (csrfTokenFetched) return;

  await sanctumAxios.get("/sanctum/csrf-cookie");
  csrfTokenFetched = true;
};

// Interceptor for 419 CSRF mismatch
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 419 && !originalRequest._retry) {
      originalRequest._retry = true;
      csrfTokenFetched = false;
      await getCsrfToken();
      return api(originalRequest);
    }
    return Promise.reject(error);
  }
);
```

#### Architecture & Best Practices

**Clean Architecture Implementation**

```
Controller → Service → Repository → Database
```

**Controller Layer**: HTTP handling, request validation, response formatting

**Service Layer**: Business logic, permission checks, transactions, audit logging

**Repository Layer**: Data access via Eloquent, query optimization

**Risk Mitigation Summary**

- **SQL Injection**: Eloquent ORM with parameterized queries
- **XSS**: Laravel escaping + strip_tags for user content
- **CSRF**: Sanctum SPA authentication with XSRF tokens
- **Brute Force**: IP-based rate limiting (5 attempts per 60 seconds)
- **Session Fixation**: Session regeneration on auth state changes
- **Unauthorized Access**: Service-layer permission checks + role-based middleware
- **Data Leakage**: Soft deletes, encrypted fields for sensitive data
- **Security Monitoring**: IP tracking, security alerts, comprehensive audit logs
