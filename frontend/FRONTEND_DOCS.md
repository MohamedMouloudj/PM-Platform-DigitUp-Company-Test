# Frontend Documentation

## Technology Stack

### Core Framework

- **React 19**: Modern UI library with hooks and concurrent features
- **TypeScript**: Type-safe development with enhanced IDE support
- **Vite**: Fast build tool with hot module replacement

### UI & Styling

- **shadcn/ui**: Accessible, customizable component library built on Radix UI
- **Tailwind CSS**: Utility-first CSS framework with custom theme
- **Tabler Icons & Lucide React**: Icon libraries for consistent iconography

### State & Routing

- **React Router v7**: Client-side routing with nested routes
- **React Context API**: Global state management for authentication
- **React Hooks**: useState, useEffect, useCallback for component state

### HTTP & API

- **Axios**: Promise-based HTTP client with interceptors
- **Laravel Sanctum**: SPA authentication with session cookies

## Why React SPA?

This project uses a Single Page Application architecture because:

1. **Internal Company Tool**: Project management platforms are internal business tools that do not require SEO or public search indexing.
2. **Rich Interactivity**: Real-time updates, dynamic task boards, and interactive components provide better user experience than traditional multi-page applications.
3. **Performance**: Client-side rendering reduces server load and provides instant navigation after initial load.
4. **Separation of Concerns**: Decoupled frontend and backend allows independent scaling and deployment.
5. **Developer Experience**: Modern tooling, hot reload, and component-based architecture accelerate development.

## Project Structure

### `/src`

Root source directory containing all application code.

### `/src/components`

Reusable React components organized by category:

- `/ui`: Base UI components from shadcn/ui (Button, Input, Label, etc.)
- `/pages`: Page-level components representing routes
- `Spinner.tsx`: Global loading indicator
- `app-sidebar.tsx`: Application sidebar navigation
- `nav-user.tsx`: User dropdown menu component

### `/src/context`

React Context providers for global state:

- `AuthContext.tsx`: Authentication state, login/logout/register handlers, user data

### `/src/services`

API service layer that wraps axios calls:

- `AuthService.ts`: Authentication endpoints
- `ProjectService.ts`: Project CRUD operations
- `TaskService.ts`: Task management endpoints
- `TeamService.ts`: Team operations
- `CommentService.ts`: Comment functionality

### `/src/types`

TypeScript type definitions and interfaces:

- `API.ts`: Response types, entity models (User, Project, Task, etc.)

### `/src/lib`

Core utilities and configurations:

- `api.ts`: Axios instance configuration with interceptors
- `utils.ts`: Helper functions for class names and utilities

### `/src/hooks`

Custom React hooks (currently empty, uses built-in hooks).

### `/public`

Static assets served directly without processing.

## Axios Configuration with Laravel Sanctum

### Setup Overview

Laravel Sanctum provides SPA authentication using session cookies instead of tokens. This requires proper CSRF protection and credential handling.

### Implementation Details

**1. Axios Instances**

Two separate axios instances are created:

```typescript
// Main API instance
const api = axios.create({
  baseURL: "http://localhost:8000/api",
  withCredentials: true, // send cookies with requests
  withXSRFToken: true, // automatically attach CSRF token
  timeout: 15000,
});

// Sanctum-specific instance for CSRF cookie
const sanctumAxios = axios.create({
  baseURL: "http://localhost:8000",
  withCredentials: true,
  timeout: 5000,
});
```

**2. CSRF Token Workflow**

Before making authenticated requests, the frontend must obtain a CSRF cookie:

```typescript
// 1. Fetch CSRF cookie from Laravel
await sanctumAxios.get("/sanctum/csrf-cookie");

// 2. Subsequent requests automatically include XSRF-TOKEN
await api.post("/auth/login", credentials);
```

The `getCsrfToken()` function ensures the token is fetched only once and prevents duplicate requests using promise caching.

**3. Response Interceptors**

Two interceptors handle common scenarios:

**419 CSRF Mismatch**: Automatically refetches CSRF token and retries the request once.

**401 Unauthorized**: Redirects to login page, but only if the request is not an auth endpoint to prevent infinite loops.

```typescript
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    // Handle 419 - Refetch CSRF and retry
    if (error.response?.status === 419 && !originalRequest._retry) {
      await getCsrfToken();
      return api(originalRequest);
    }

    // Handle 401 - Redirect to login
    if (error.response?.status === 401 && !isRedirecting) {
      window.location.href = "/login";
    }

    return Promise.reject(error);
  }
);
```

**4. Backend CORS Configuration**

Laravel must be configured to accept credentials from the frontend:

```php
// config/cors.php
'supports_credentials' => true,
'allowed_origins' => ['http://localhost:5173'],
```

```php
// config/sanctum.php
'stateful' => ['localhost:5173'],
```

**5. Authentication Flow**

1. User submits login form
2. Frontend calls `getCsrfToken()` to obtain CSRF cookie
3. Frontend sends POST to `/api/auth/login` with credentials
4. Laravel creates session and returns user data
5. All subsequent API calls automatically include session cookie
6. On logout, session is destroyed and CSRF token is reset

### Key Benefits

- No token management required
- Automatic CSRF protection
- Built-in session handling by Laravel
- Secure cookie-based authentication
- Seamless integration with Laravel middleware

## Development Workflow

### Running the Application

```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

The development server runs on `http://localhost:5173` and proxies API requests to Laravel backend on `http://localhost:8000`.

### Building for Production

```bash
# Create optimized production build
npm run build

# Preview production build locally
npm run preview
```

### Code Style

- TypeScript strict mode enabled
- ESLint for code linting
- Prettier for code formatting (if configured)
- Component-first architecture
- Hooks over class components

## Environment Variables

Create `.env` file in the frontend root:

```env
VITE_BACKEND_URL=http://localhost:8000
```

Vite exposes environment variables prefixed with `VITE_` to the client.

## Routing Architecture

### Route Structure

Routes are defined in `App.tsx` using React Router:

- `/login`, `/register`: Public authentication routes
- `/dashboard/*`: Protected routes requiring authentication
  - `/dashboard/projects`: Project listing
  - `/dashboard/projects/:id`: Project details
  - `/dashboard/tasks`: All user tasks
  - `/dashboard/tasks/:id`: Task details
  - `/dashboard/teams`: Team management
  - `/dashboard/profile`: User profile

### Route Protection

Protected routes use `ProtectedRoute` component that checks authentication state from `AuthContext`. Unauthenticated users are redirected to login.

Role-based access control is implemented using `RoleGuard` component that wraps pages requiring specific permissions.

## Authentication Context

`AuthContext` provides global authentication state and methods:

- `user`: Current authenticated user or null
- `isAuthenticated`: Boolean authentication status
- `isLoading`: Initial auth check loading state
- `login(credentials)`: Authenticate user
- `register(data)`: Create new user account
- `logout()`: End user session
- `refreshUser()`: Reload current user data

Components access authentication via `useAuth()` hook.

## API Response Format

All API responses follow consistent structure:

```typescript
interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data?: T;
  errors?: Record<string, string[]>;
}
```

Services handle response unwrapping and error propagation to components.

## Error Handling

Errors are handled at multiple levels:

1. **Axios Interceptors**: Handle HTTP errors globally
2. **Service Layer**: Catch and log errors before propagating
3. **Component Level**: Display user-friendly error messages using toast notifications
