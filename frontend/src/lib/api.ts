import axios, { type AxiosInstance } from "axios";

const API_URL = import.meta.env.VITE_BACKEND_URL || "http://localhost:8000";

const api: AxiosInstance = axios.create({
  baseURL: API_URL + "/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  withCredentials: true,
  withXSRFToken: true,
  timeout: 15000,
});

// Separate instance for CSRF
export const sanctumAxios: AxiosInstance = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  timeout: 5000,
});

let csrfTokenFetched = false;
let csrfTokenPromise: Promise<void> | null = null;

export const getCsrfToken = async (): Promise<void> => {
  if (csrfTokenFetched) return;

  if (csrfTokenPromise) {
    return csrfTokenPromise;
  }

  csrfTokenPromise = sanctumAxios
    .get("/sanctum/csrf-cookie")
    .then(() => {
      csrfTokenFetched = true;
      csrfTokenPromise = null;
    })
    .catch((error) => {
      csrfTokenPromise = null;
      console.error("Failed to fetch CSRF token:", error);
      throw error;
    });

  return csrfTokenPromise;
};

export const resetCsrfToken = (): void => {
  csrfTokenFetched = false;
  csrfTokenPromise = null;
};

let isRedirecting = false;

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;

    // 419 CSRF mismatch
    if (error.response?.status === 419 && !originalRequest._retry) {
      originalRequest._retry = true;
      csrfTokenFetched = false;

      await getCsrfToken();
      return api(originalRequest);
    }

    // 401 Unauthorized - only redirect if not already redirecting and not on auth endpoints
    if (
      error.response?.status === 401 &&
      !originalRequest._retry &&
      !isRedirecting &&
      !originalRequest.url?.includes("/auth/")
    ) {
      isRedirecting = true;
      console.log("Unauthorized - redirecting to login");
      setTimeout(() => {
        window.location.href = "/login";
      }, 100);
    }

    return Promise.reject(error);
  }
);

export default api;
