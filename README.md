# PM Platform - Project Management System

This is the technical assessment for the Digitup company interview.

A secure, role-based project management platform built with Laravel 12 and React 19, featuring task tracking, team collaboration, and comprehensive audit logging for enterprise use.

---

## High level functions

### Feature Requirements

| Functional Requirement           | Priority      |
| -------------------------------- | ------------- |
| User Management & Authentication | P1 (Critical) |
| Project Management               | P1 (Critical) |
| Task Management                  | P2 (High)     |
| Team Collaboration               | P2 (High)     |
| Communication & Comments         | P2 (High)     |
| Dashboard & Reporting            | P3 (Medium)   |
| Search & Filtering               | P3 (Medium)   |
| Notifications & Alerts           | P3 (Medium)   |
| Advanced Security Features       | P4 (Bonus)    |
| File Management                  | P4 (Bonus)    |

---

### Feature Details

#### P1 (Critical) - User Management & Authentication

- User registration and login
- Role-based access (admin, manager, member, guest)
- Two-factor authentication (2FA) - _optional_
- Session management and logout

#### P1 (Critical) - Project Management

- Create, edit, delete projects
- Project confidentiality levels (public, internal, confidential, top_secret)
- Project status management (active, archived)
- Archive and restore projects

#### P2 (High) - Task Management

- Create, edit, delete tasks
- Assign tasks to team members
- Set task priority (low, medium, high, urgent)
- Task status tracking (todo, in_progress, done)
- Set deadlines for tasks

#### P2 (High) - Team Collaboration

- Create and manage teams
- Assign team members with specific roles
- Assign teams to projects
- Granular permission management per project

#### P2 (High) - Communication & Comments

- Comment on tasks
- File attachments on comments
- Mention system (@username notifications)
- Task modification history

#### P3 (Medium) - Notifications & Alerts

- Deadline reminders
- Mention notifications
- Security alerts for suspicious activities
- New location/IP login alerts

#### P3 (Medium) - Dashboard & Reporting

- Real-time project statistics
- Task progress overview
- Admin audit reports
- Team performance metrics

#### P3 (Medium) - Search & Filtering

- Full-text search across projects and tasks
- Filter by confidentiality level
- Filter by status, priority, assignee

#### P4 (Bonus) - Advanced Security Features

- Suspicious login detection
- Automated security compliance reports
- Activity audit trail with before/after data
- Rate limiting based on user behavior

#### P4 (Bonus) - File Management

- Secure file upload with validation
- MIME type verification
- Basic antivirus scanning
- Secure file storage

---

#### Future Enhancements

- Per-project AES-256 encryption key rotation
- Antivirus file scanning integration (ClamAV)
- Geolocation-based security alerts
- SIEM integration for centralized monitoring
- Automated security testing and penetration testing
- Advanced rate limiting with behavioral analysis

---

## DB ERD

```mermaid

erDiagram

    users ||--o{ projects : "created_by"
    users ||--o{ tasks : "created_by"
    users ||--o{ tasks : "assigned_to"
    users ||--o{ teams : "created_by"
    users ||--o{ comments : "user_id"
    users ||--o{ team_members : "user_id"
    users ||--o{ project_permissions : "user_id"
    users ||--o{ project_permissions : "granted_by"
    users ||--o{ activity_log : "causer_id"
    users ||--o{ security_alerts : "user_id"
    users ||--o{ security_alerts : "resolved_by"
    users ||--o{ file_validations : "uploaded_by"

    projects ||--o{ tasks : "project_id"
    projects ||--o{ project_teams : "project_id"
    projects ||--o{ project_permissions : "project_id"

    tasks ||--o{ comments : "task_id"

    teams ||--o{ team_members : "team_id"
    teams ||--o{ project_teams : "team_id"

    users {
        uuid id PK
        varchar name
        varchar email UK
        varchar password
        enum role "admin,manager,member,guest"
        boolean two_factor_enabled
        text two_factor_secret "encrypted"
        timestamp last_login_at
        varchar last_login_ip
        varchar remember_token
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at "soft_delete"
    }

    projects {
        uuid id PK
        varchar name
        text description "encrypted_if_confidential"
        enum status "active,archived"
        enum confidentiality_level "public,internal,confidential,top_secret"
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at "soft_delete"
    }

    tasks {
        uuid id PK
        bigint project_id FK
        varchar title
        text description "XSS_sanitized"
        enum priority "low,medium,high,urgent"
        enum status "todo,in_progress,done"
        bigint assigned_to FK
        timestamp deadline
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    teams {
        uuid id PK
        varchar name
        text description
        bigint created_by FK
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    comments {
        uuid id PK
        bigint task_id FK
        bigint user_id FK
        text content "XSS_sanitized|mentions"
        varchar file_path "secure_storage"
        varchar file_name
        varchar file_mime_type
        integer file_size
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }

    team_members {
        uuid id PK
        bigint team_id FK
        bigint user_id FK
        varchar role "team_specific_role"
        timestamp joined_at
        timestamp created_at
        timestamp updated_at
    }

    project_teams {
        uuid id PK
        bigint project_id FK
        bigint team_id FK
        timestamp assigned_at
        timestamp created_at
    }

    project_permissions {
        uuid id PK
        bigint project_id FK
        bigint user_id FK
        enum permission "read,write,delete,manage"
        bigint granted_by FK
        timestamp granted_at
        timestamp created_at
    }

    activity_log {
        uuid id PK "spatie/laravel-activitylog"
        varchar log_name
        text description
        bigint subject_id
        varchar subject_type
        bigint causer_id FK
        varchar causer_type
        json properties "old_new_values"
        varchar event "created,updated,deleted"
        varchar batch_uuid
        timestamp created_at
        timestamp updated_at
    }

    security_alerts {
        uuid id PK
        bigint user_id FK
        enum alert_type "suspicious_login,new_location,multiple_failed_attempts,rate_limit_exceeded"
        enum severity "low,medium,high,critical"
        varchar ip_address
        varchar location
        json details
        boolean is_resolved
        timestamp resolved_at
        bigint resolved_by FK
        timestamp created_at
    }

    file_validations {
        uuid id PK
        varchar file_path
        varchar original_name
        varchar mime_type
        integer size
        varchar hash "SHA-256"
        enum scan_status "pending,clean,infected,suspicious"
        text scan_result
        bigint uploaded_by FK
        timestamp created_at
    }

```

---

## Security Implementation

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

