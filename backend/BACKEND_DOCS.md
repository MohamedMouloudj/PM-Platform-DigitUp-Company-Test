# Backend API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

All routes except registration and login require authentication using Laravel Sanctum cookies.

## Response Format

All responses follow this structure:

Success:

```json
{
    "success": true,
    "data": {},
    "message": "Operation successful"
}
```

Error:

```json
{
    "success": false,
    "message": "Error message",
    "errors": {}
}
```

## Enums Reference

### UserRole

-   `admin`
-   `manager`
-   `member`
-   `guest`

### ProjectStatus

-   `active`
-   `archived`

### ConfidentialityLevel

-   `public`
-   `internal`
-   `confidential`
-   `top_secret`

### TaskStatus

-   `todo`
-   `in_progress`
-   `done`

### TaskPriority

-   `low`
-   `medium`
-   `high`
-   `urgent`

### PermissionType

-   `read`
-   `write`
-   `delete`
-   `manage`

---

## Authentication Routes

### Register User

**POST** `/auth/register`

**Body:**

```json
{
    "name": "string (required, max: 255)",
    "email": "string (required, email, unique, max: 255)",
    "password": "string (required, min: 8, mixed case, numbers)",
    "password_confirmation": "string (required)",
    "role": "string (optional, enum: UserRole)"
}
```

**Example:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "role": "member"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": "uuid",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "member",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

### Login User

**POST** `/auth/login`

Rate limited: 5 attempts per minute per IP.

**Body:**

```json
{
    "email": "string (required, email)",
    "password": "string (required)",
    "remember": "boolean (optional)"
}
```

**Example:**

```json
{
    "email": "john@example.com",
    "password": "SecurePass123",
    "remember": true
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": "uuid",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "member"
        }
    }
}
```

---

### Logout User

**POST** `/auth/logout`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "message": "Logout successful"
}
```

---

### Get Current User

**GET** `/auth/user`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": "uuid",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "member",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

## Project Routes

### List All Projects

**GET** `/projects`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "projects": [
            {
                "id": "uuid",
                "name": "Project Alpha",
                "description": "Project description",
                "status": "active",
                "confidentiality_level": "internal",
                "owner_id": "uuid",
                "created_at": "2025-11-18T10:00:00.000000Z",
                "updated_at": "2025-11-18T10:00:00.000000Z"
            }
        ]
    }
}
```

---

### Create Project

**POST** `/projects`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "name": "string (required, max: 255)",
    "description": "string (required, max: 5000)",
    "status": "string (optional, enum: ProjectStatus)",
    "confidentiality_level": "string (required, enum: ConfidentialityLevel)"
}
```

**Example:**

```json
{
    "name": "Project Beta",
    "description": "A new project for testing",
    "status": "active",
    "confidentiality_level": "confidential"
}
```

**Response (201):**

```json
{
    "success": true,
    "message": "Project created successfully",
    "data": {
        "project": {
            "id": "uuid",
            "name": "Project Beta",
            "description": "A new project for testing",
            "status": "active",
            "confidentiality_level": "confidential",
            "owner_id": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

### Get Project Details

**GET** `/projects/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "project": {
            "id": "uuid",
            "name": "Project Beta",
            "description": "A new project for testing",
            "status": "active",
            "confidentiality_level": "confidential",
            "owner_id": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

### Update Project

**PUT** `/projects/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "name": "string (optional, max: 255)",
    "description": "string (optional, max: 5000)",
    "status": "string (optional, enum: ProjectStatus)",
    "confidentiality_level": "string (optional, enum: ConfidentialityLevel)"
}
```

**Example:**

```json
{
    "name": "Updated Project Beta",
    "status": "active"
}
```

**Response (200):**

```json
{
    "success": true,
    "message": "Project updated successfully",
    "data": {
        "project": {
            "id": "uuid",
            "name": "Updated Project Beta",
            "description": "A new project for testing",
            "status": "active",
            "confidentiality_level": "confidential",
            "owner_id": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

### Delete Project

**DELETE** `/projects/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "message": "Project deleted successfully"
}
```

---

### Archive Project

**POST** `/projects/{id}/archive`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "message": "Project archived successfully",
    "data": {
        "project": {
            "id": "uuid",
            "name": "Project Beta",
            "status": "archived",
            "confidentiality_level": "confidential",
            "owner_id": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

### Restore Project

**POST** `/projects/{id}/restore`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "message": "Project restored successfully",
    "data": {
        "project": {
            "id": "uuid",
            "name": "Project Beta",
            "status": "active",
            "confidentiality_level": "confidential",
            "owner_id": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

---

## Task Routes

### List Project Tasks

**GET** `/projects/{projectId}/tasks`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "title": "Task title",
            "description": "Task description",
            "priority": "high",
            "status": "todo",
            "project_id": "uuid",
            "created_by": "uuid",
            "assigned_to": "uuid",
            "deadline": "2025-12-01",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    ]
}
```

---

### Create Task

**POST** `/projects/{projectId}/tasks`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "title": "string (required, max: 255)",
    "description": "string (required, max: 10000)",
    "priority": "string (required, enum: TaskPriority)",
    "status": "string (optional, enum: TaskStatus)",
    "assigned_to": "uuid (optional, exists in users)",
    "deadline": "date (optional, after today)"
}
```

**Example:**

```json
{
    "title": "Implement login feature",
    "description": "Create the login functionality with validation",
    "priority": "high",
    "status": "todo",
    "assigned_to": "user-uuid",
    "deadline": "2025-12-15"
}
```

**Response (201):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "title": "Implement login feature",
        "description": "Create the login functionality with validation",
        "priority": "high",
        "status": "todo",
        "project_id": "uuid",
        "created_by": "uuid",
        "assigned_to": "user-uuid",
        "deadline": "2025-12-15",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Task created successfully"
}
```

---

### Get Task Details

**GET** `/tasks/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "title": "Implement login feature",
        "description": "Create the login functionality with validation",
        "priority": "high",
        "status": "todo",
        "project_id": "uuid",
        "created_by": "uuid",
        "assigned_to": "user-uuid",
        "deadline": "2025-12-15",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    }
}
```

---

### Update Task

**PUT** `/tasks/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "title": "string (optional, max: 255)",
    "description": "string (optional, max: 10000)",
    "priority": "string (optional, enum: TaskPriority)",
    "status": "string (optional, enum: TaskStatus)",
    "assigned_to": "uuid (optional, exists in users)",
    "deadline": "date (optional, after today)"
}
```

**Example:**

```json
{
    "status": "in_progress",
    "priority": "urgent"
}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "title": "Implement login feature",
        "description": "Create the login functionality with validation",
        "priority": "urgent",
        "status": "in_progress",
        "project_id": "uuid",
        "created_by": "uuid",
        "assigned_to": "user-uuid",
        "deadline": "2025-12-15",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Task updated successfully"
}
```

---

### Delete Task

**DELETE** `/tasks/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (204):**

```json
{
    "success": true,
    "message": "Task deleted successfully"
}
```

---

### Assign Task

**POST** `/tasks/{id}/assign`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "assigned_to": "uuid (required, exists in users)"
}
```

**Example:**

```json
{
    "assigned_to": "user-uuid-123"
}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "title": "Implement login feature",
        "description": "Create the login functionality with validation",
        "priority": "urgent",
        "status": "in_progress",
        "project_id": "uuid",
        "created_by": "uuid",
        "assigned_to": "user-uuid-123",
        "deadline": "2025-12-15",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Task assigned successfully"
}
```

---

## Comment Routes

### List Task Comments

**GET** `/tasks/{taskId}/comments`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "content": "This is a comment",
            "task_id": "uuid",
            "user_id": "uuid",
            "file_path": null,
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    ]
}
```

---

### Create Comment

**POST** `/tasks/{taskId}/comments`

**Headers:**

```
Cookie: laravel_session=...
Content-Type: multipart/form-data
```

**Body (multipart/form-data):**

```
content: string (required, max: 5000)
file: file (optional, mimes: pdf,jpg,jpeg,png,docx, max: 5MB)
```

**Example:**

```
content=This is my comment on the task
file=[binary file data]
```

**Response (201):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "content": "This is my comment on the task",
        "task_id": "uuid",
        "user_id": "uuid",
        "file_path": "comments/file-hash.pdf",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Comment created successfully"
}
```

---

### Update Comment

**PUT** `/comments/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "content": "string (optional, max: 5000)"
}
```

**Example:**

```json
{
    "content": "Updated comment content"
}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "content": "Updated comment content",
        "task_id": "uuid",
        "user_id": "uuid",
        "file_path": "comments/file-hash.pdf",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Comment updated successfully"
}
```

---

### Delete Comment

**DELETE** `/comments/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (204):**

```json
{
    "success": true,
    "message": "Comment deleted successfully"
}
```

---

## Team Routes

### List All Teams

**GET** `/teams`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "name": "Development Team",
            "description": "Team description",
            "created_by": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    ]
}
```

---

### Create Team

**POST** `/teams`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "name": "string (required, max: 255)",
    "description": "string (optional, max: 1000)"
}
```

**Example:**

```json
{
    "name": "Frontend Team",
    "description": "Responsible for all frontend development"
}
```

**Response (201):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "name": "Frontend Team",
        "description": "Responsible for all frontend development",
        "created_by": "uuid",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Team created successfully"
}
```

---

### Get Team Details

**GET** `/teams/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "name": "Frontend Team",
        "description": "Responsible for all frontend development",
        "created_by": "uuid",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    }
}
```

---

### Update Team

**PUT** `/teams/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "name": "string (optional, max: 255)",
    "description": "string (optional, max: 1000)"
}
```

**Example:**

```json
{
    "name": "Updated Frontend Team",
    "description": "New description"
}
```

**Response (200):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "name": "Updated Frontend Team",
        "description": "New description",
        "created_by": "uuid",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Team updated successfully"
}
```

---

### Delete Team

**DELETE** `/teams/{id}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (204):**

```json
{
    "success": true,
    "message": "Team deleted successfully"
}
```

---

### Add Team Member

**POST** `/teams/{id}/members`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "user_id": "uuid (required, exists in users)"
}
```

**Example:**

```json
{
    "user_id": "user-uuid-456"
}
```

**Response (201):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "team_id": "uuid",
        "user_id": "user-uuid-456",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Member added successfully"
}
```

---

### Remove Team Member

**DELETE** `/teams/{id}/members/{userId}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (204):**

```json
{
    "success": true,
    "message": "Member removed successfully"
}
```

---

## Project Permission Routes

### List Project Permissions

**GET** `/projects/{projectId}/permissions`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (200):**

```json
{
    "success": true,
    "data": [
        {
            "id": "uuid",
            "project_id": "uuid",
            "user_id": "uuid",
            "permission": "read",
            "granted_by": "uuid",
            "created_at": "2025-11-18T10:00:00.000000Z",
            "updated_at": "2025-11-18T10:00:00.000000Z"
        }
    ]
}
```

---

### Grant Permission

**POST** `/projects/{projectId}/permissions`

**Headers:**

```
Cookie: laravel_session=...
```

**Body:**

```json
{
    "user_id": "uuid (required, exists in users)",
    "permission": "string (required, enum: PermissionType)"
}
```

**Example:**

```json
{
    "user_id": "user-uuid-789",
    "permission": "write"
}
```

**Response (201):**

```json
{
    "success": true,
    "data": {
        "id": "uuid",
        "project_id": "uuid",
        "user_id": "user-uuid-789",
        "permission": "write",
        "granted_by": "uuid",
        "created_at": "2025-11-18T10:00:00.000000Z",
        "updated_at": "2025-11-18T10:00:00.000000Z"
    },
    "message": "Permission granted successfully"
}
```

---

### Revoke Permission

**DELETE** `/projects/{projectId}/permissions/{userId}`

**Headers:**

```
Cookie: laravel_session=...
```

**Response (204):**

```json
{
    "success": true,
    "message": "Permission revoked successfully"
}
```

---

## Error Responses

### 400 Bad Request

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### 403 Forbidden

```json
{
    "success": false,
    "message": "Access denied"
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "Resource not found"
}
```

### 422 Unprocessable Entity

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "field_name": ["The field is required."]
    }
}
```

### 429 Too Many Requests

```json
{
    "success": false,
    "message": "Too many attempts. Please try again later."
}
```

### 500 Internal Server Error

```json
{
    "success": false,
    "message": "An error occurred",
    "errors": {
        "error": ["Error details"]
    }
}
```
