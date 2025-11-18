import axios from "axios";

const API_URL = import.meta.env.VITE_BACKEND_URL || "http://localhost:8000";
const SANCTUM_URL = API_URL + "/sanctum/csrf-cookie";

const api = axios.create({
  baseURL: API_URL + "/api",
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  withCredentials: true,
  timeout: 15000,
});

// CSRF token setup for Sanctum SPA
let csrfTokenFetched = false;

export const getCsrfToken = async (): Promise<void> => {
  if (!csrfTokenFetched) {
    await axios.get(SANCTUM_URL, { withCredentials: true });
    csrfTokenFetched = true;
  }
};

// Intercept requests to ensure CSRF token is fetched before POST/PUT/DELETE
api.interceptors.request.use(
  async (config) => {
    if (
      ["post", "put", "delete", "patch"].includes(
        config.method?.toLowerCase() || ""
      )
    ) {
      await getCsrfToken();
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercept responses for consistent error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - session expired
      csrfTokenFetched = false;
    }
    return Promise.reject(error);
  }
);

export default api;
