# Sanctum SPA Authentication - Quick Reference

## 🚀 Quick Test (Copy-Paste Ready)

### PowerShell Commands

```powershell
# 1. Clear caches
php artisan config:clear; php artisan cache:clear

# 2. Start server (in separate terminal)
php artisan serve

# 3. Register a user
Invoke-RestMethod -Uri "http://localhost:8000/api/auth/register" `
  -Method POST `
  -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} `
  -Body '{"name":"John Doe","email":"john@example.com","password":"Password123","password_confirmation":"Password123"}' `
  -SessionVariable session

# 4. Get CSRF cookie
Invoke-RestMethod -Uri "http://localhost:8000/sanctum/csrf-cookie" `
  -Method GET `
  -WebSession $session

# 5. Login
$response = Invoke-RestMethod -Uri "http://localhost:8000/api/auth/login" `
  -Method POST `
  -Headers @{"Content-Type"="application/json"; "Accept"="application/json"} `
  -Body '{"email":"john@example.com","password":"Password123"}' `
  -WebSession $session

# 6. Get authenticated user
Invoke-RestMethod -Uri "http://localhost:8000/api/auth/user" `
  -Method GET `
  -Headers @{"Accept"="application/json"} `
  -WebSession $session

# 7. Logout
Invoke-RestMethod -Uri "http://localhost:8000/api/auth/logout" `
  -Method POST `
  -Headers @{"Accept"="application/json"} `
  -WebSession $session
```

---

## 📋 API Endpoints

| Method | Endpoint             | Auth | Rate Limit | Description            |
| ------ | -------------------- | ---- | ---------- | ---------------------- |
| POST   | `/api/auth/register` | ❌   | None       | Register new user      |
| POST   | `/api/auth/login`    | ❌   | 5/min      | Login with credentials |
| POST   | `/api/auth/logout`   | ✅   | None       | Logout current session |
| GET    | `/api/auth/user`     | ✅   | None       | Get authenticated user |

---

## 🔧 Configuration Files Changed

| File                 | Key Changes                                                         |
| -------------------- | ------------------------------------------------------------------- |
| `config/sanctum.php` | Stateful domains for SPA                                            |
| `config/cors.php`    | `supports_credentials: true`, `allowed_origins`                     |
| `.env`               | `SESSION_DRIVER=cookie`, `SANCTUM_STATEFUL_DOMAINS`, `FRONTEND_URL` |

---

## 📁 Architecture

```
Request
  ↓
Controller (HTTP handling only)
  ↓
Service (Business logic)
  ↓
Repository (Database queries)
  ↓
Model (Eloquent)
```

**Key Rules**:

-   ❌ No business logic in Controllers
-   ❌ No validation in Services
-   ❌ No Request objects in Services (use DTOs)
-   ✅ All validation in Form Requests
-   ✅ Use DTOs for service inputs
-   ✅ Repository interfaces with DI

---

## 🔐 Security Features

| Feature              | Implementation                                   |
| -------------------- | ------------------------------------------------ |
| **Rate Limiting**    | 5 login attempts/min per IP                      |
| **Session Security** | Regeneration after login, invalidation on logout |
| **CSRF Protection**  | Sanctum CSRF middleware                          |
| **New IP Detection** | SecurityAlert created on IP change               |
| **Activity Logging** | Spatie Activity Log tracks all auth events       |
| **Password Hashing** | Laravel Hash facade (bcrypt)                     |

---

## 🧪 Test Checklist

-   [ ] Register new user → 201 response
-   [ ] Login with valid credentials → 200 response
-   [ ] Get CSRF cookie → cookies received
-   [ ] Get authenticated user → 200 with user data
-   [ ] Logout → 200 response
-   [ ] Try authenticated endpoint without login → 401
-   [ ] Test rate limiting (6 failed logins) → 429 after 5th
-   [ ] Login from different IP → Check `security_alerts` table
-   [ ] Check `activity_log` table → All events logged

---

## 🐛 Common Issues & Solutions

| Issue                   | Solution                                            |
| ----------------------- | --------------------------------------------------- |
| "Unauthenticated" error | Get CSRF cookie first, use `withCredentials: true`  |
| CORS errors             | Verify `FRONTEND_URL`, `supports_credentials: true` |
| Rate limit not working  | Clear cache: `php artisan config:clear`             |
| Sessions not persisting | Check `SESSION_DRIVER=cookie` in `.env`             |

---

## 📦 Files Created

### DTOs

-   `app/DTOs/Auth/RegisterUserDTO.php`
-   `app/DTOs/Auth/LoginUserDTO.php`
-   `app/DTOs/Auth/UpdateProfileDTO.php`

### Form Requests

-   `app/Http/Requests/Auth/RegisterRequest.php`
-   `app/Http/Requests/Auth/LoginRequest.php`

### Repositories

-   `app/Repositories/Contracts/UserRepositoryInterface.php`
-   `app/Repositories/Eloquent/UserRepository.php`

### Services

-   `app/Services/Auth/AuthService.php`

### Controllers

-   `app/Http/Controllers/Api/Auth/AuthController.php`

---

## 🌐 React Integration

```typescript
// api.ts
import axios from "axios";

const api = axios.create({
    baseURL: "http://localhost:8000",
    withCredentials: true, // CRITICAL!
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
    },
});

// auth.service.ts
export const authService = {
    async register(data: RegisterData) {
        return api.post("/api/auth/register", data);
    },

    async login(email: string, password: string) {
        await api.get("/sanctum/csrf-cookie"); // Get CSRF first
        return api.post("/api/auth/login", { email, password });
    },

    async logout() {
        return api.post("/api/auth/logout");
    },

    async getUser() {
        return api.get("/api/auth/user");
    },
};
```

---

## 🗄️ Database Queries

```sql
-- Check users
SELECT * FROM users ORDER BY created_at DESC;

-- Check security alerts
SELECT * FROM security_alerts WHERE alert_type = 'new_location';

-- Check activity log
SELECT * FROM activity_log WHERE subject_type = 'App\\Models\\User' ORDER BY created_at DESC LIMIT 10;
```

---

## 📚 Documentation

-   Full details: `AUTHENTICATION_IMPLEMENTATION.md`
-   Testing guide: `AUTHENTICATION_TESTING.md`
-   Project architecture: `.github/copilot-instructions.md`

---

## ✅ Production Checklist

Before deploying to production:

-   [ ] Set `SESSION_SECURE_COOKIE=true` (requires HTTPS)
-   [ ] Update `SANCTUM_STATEFUL_DOMAINS` with production domain
-   [ ] Set `SESSION_DOMAIN` to production domain
-   [ ] Update `FRONTEND_URL` to production URL
-   [ ] Enable `SESSION_ENCRYPT=true`
-   [ ] Configure proper `allowed_origins` in CORS
-   [ ] Adjust rate limits for production load
-   [ ] Set up SSL/TLS certificates
-   [ ] Review and test security alerts
-   [ ] Enable 2FA for admin users (future)
