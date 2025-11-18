# Authentication API Testing Guide

## Setup Instructions

1. **Update your .env file** (already done):

    ```env
    SESSION_DRIVER=cookie
    SESSION_DOMAIN=localhost
    SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
    FRONTEND_URL=http://localhost:3000
    ```

2. **Clear config cache**:

    ```bash
    php artisan config:clear
    php artisan cache:clear
    ```

3. **Start the Laravel development server**:
    ```bash
    php artisan serve
    ```

---

## API Endpoints

| Method | Endpoint             | Auth Required | Description                 |
| ------ | -------------------- | ------------- | --------------------------- |
| POST   | `/api/auth/register` | No            | Register a new user         |
| POST   | `/api/auth/login`    | No            | Login (rate limited: 5/min) |
| POST   | `/api/auth/logout`   | Yes           | Logout current user         |
| GET    | `/api/auth/user`     | Yes           | Get authenticated user info |

---

## Testing with cURL

### 1. Register a New User

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"name\":\"John Doe\",\"email\":\"john@example.com\",\"password\":\"Password123\",\"password_confirmation\":\"Password123\"}"
```

**Expected Response (201)**:

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": "uuid-here",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "member"
        }
    }
}
```

### 2. Get CSRF Cookie (Required for SPA authentication)

```bash
curl -X GET http://localhost:8000/sanctum/csrf-cookie \
  -H "Accept: application/json" \
  --cookie-jar cookies.txt
```

### 3. Login

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Referer: http://localhost:3000" \
  --cookie cookies.txt \
  --cookie-jar cookies.txt \
  -d "{\"email\":\"john@example.com\",\"password\":\"Password123\",\"remember\":false}"
```

**Expected Response (200)**:

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": "uuid-here",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "member"
        }
    }
}
```

### 4. Get Authenticated User

```bash
curl -X GET http://localhost:8000/api/auth/user \
  -H "Accept: application/json" \
  -H "Referer: http://localhost:3000" \
  --cookie cookies.txt
```

**Expected Response (200)**:

```json
{
    "success": true,
    "data": {
        "user": {
            "id": "uuid-here",
            "name": "John Doe",
            "email": "john@example.com",
            "role": "member",
            "two_factor_enabled": false,
            "last_login_at": "2025-11-18T10:30:00.000000Z",
            "created_at": "2025-11-18T10:00:00.000000Z"
        }
    }
}
```

### 5. Logout

```bash
curl -X POST http://localhost:8000/api/auth/logout \
  -H "Accept: application/json" \
  -H "Referer: http://localhost:3000" \
  --cookie cookies.txt \
  --cookie-jar cookies.txt
```

**Expected Response (200)**:

```json
{
    "success": true,
    "message": "Logout successful"
}
```

---

## Testing with Postman/Insomnia

### Configuration

1. **Base URL**: `http://localhost:8000`
2. **Enable cookie jar** in settings
3. **Add header**: `Accept: application/json`
4. **Add header**: `Referer: http://localhost:3000`

### Request Sequence

1. **GET** `/sanctum/csrf-cookie` - Gets CSRF token
2. **POST** `/api/auth/register` - Register new user
3. **POST** `/api/auth/login` - Login
4. **GET** `/api/auth/user` - Get user data (authenticated)
5. **POST** `/api/auth/logout` - Logout

---

## Testing Rate Limiting

Try logging in with invalid credentials 6 times quickly:

```bash
for i in {1..6}; do
  curl -X POST http://localhost:8000/api/auth/login \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    --cookie cookies.txt \
    -d "{\"email\":\"test@example.com\",\"password\":\"wrongpassword\"}"
  echo "\n--- Attempt $i ---\n"
done
```

After 5 attempts, you should get:

```json
{
    "success": false,
    "message": "Too many login attempts. Please try again in X seconds."
}
```

---

## Security Features Implemented

### 1. **New IP Detection**

When a user logs in from a different IP address:

-   A `SecurityAlert` is created with type `NEW_LOCATION`
-   Severity is set to `MEDIUM`
-   Details include previous IP, new IP, and user agent

**Check in database**:

```sql
SELECT * FROM security_alerts WHERE alert_type = 'new_location';
```

### 2. **Activity Logging**

All authentication actions are logged:

-   User registration
-   Login (with IP and user agent)
-   Logout
-   Security alerts

**Check in database**:

```sql
SELECT * FROM activity_log WHERE subject_type = 'App\\Models\\User';
```

### 3. **Rate Limiting**

-   Login endpoint: 5 attempts per minute per IP
-   Implemented at both Form Request level and route level

### 4. **Session Security**

-   Session regeneration after login (prevents session fixation)
-   Session invalidation on logout
-   CSRF protection enabled
-   SameSite cookie policy: `lax`

---

## React SPA Integration

### Axios Configuration

```typescript
import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost:8000",
    withCredentials: true, // CRITICAL for cookies
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
});

export default api;
```

### Login Flow

```typescript
// 1. Get CSRF cookie first
await api.get("/sanctum/csrf-cookie");

// 2. Login
const response = await api.post("/api/auth/login", {
    email: "john@example.com",
    password: "Password123",
    remember: false,
});

// 3. Make authenticated requests
const userResponse = await api.get("/api/auth/user");
```

---

## Troubleshooting

### Issue: "Unauthenticated" error

**Solution**:

1. Ensure you called `/sanctum/csrf-cookie` first
2. Check `withCredentials: true` in Axios
3. Verify `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain
4. Check `SESSION_DOMAIN` matches your domain

### Issue: CORS errors

**Solution**:

1. Verify `FRONTEND_URL` in `.env`
2. Check `supports_credentials: true` in `config/cors.php`
3. Ensure frontend sends `Referer` header

### Issue: Rate limit not working

**Solution**:

1. Clear config cache: `php artisan config:clear`
2. Check `cache` is working: `php artisan cache:clear`
3. Verify database connection (using database cache driver)

---

## Production Checklist

-   [ ] Set `SESSION_SECURE_COOKIE=true` (HTTPS only)
-   [ ] Update `SANCTUM_STATEFUL_DOMAINS` with production domain
-   [ ] Set `SESSION_DOMAIN` to your production domain
-   [ ] Update `FRONTEND_URL` to production URL
-   [ ] Enable `SESSION_ENCRYPT=true`
-   [ ] Set proper `allowed_origins` in CORS config
-   [ ] Configure proper rate limits for production load
-   [ ] Set up SSL/TLS certificates
-   [ ] Enable 2FA for admin users

---

## Database Verification

### Check Users Table

```sql
SELECT id, name, email, role, last_login_at, last_login_ip, created_at
FROM users;
```

### Check Security Alerts

```sql
SELECT sa.*, u.email
FROM security_alerts sa
JOIN users u ON sa.user_id = u.id
ORDER BY sa.created_at DESC;
```

### Check Activity Log

```sql
SELECT * FROM activity_log
WHERE subject_type = 'App\\Models\\User'
ORDER BY created_at DESC
LIMIT 10;
```

---

## Next Steps

1. ✅ Test all endpoints with cURL
2. ✅ Verify SecurityAlert creation on IP change
3. ✅ Check activity_log entries
4. ✅ Test rate limiting
5. ⬜ Integrate with React frontend
6. ⬜ Implement 2FA (future enhancement)
7. ⬜ Add email verification (future enhancement)
8. ⬜ Create feature tests
