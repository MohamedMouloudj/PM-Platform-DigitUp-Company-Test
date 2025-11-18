import api, { getCsrfToken, resetCsrfToken } from "@/lib/api";
import type {
  ApiResponse,
  LoginRequest,
  RegisterRequest,
  User,
} from "@/types/API";
import type { AxiosResponse } from "axios";

export default class AuthService {
  static async register(
    data: RegisterRequest
  ): Promise<AxiosResponse<ApiResponse<{ user: User }>>> {
    await getCsrfToken();

    try {
      return await api.post("/auth/register", data);
    } catch (error) {
      console.error("Registration error:", error);
      throw error;
    }
  }

  static async login(
    data: LoginRequest
  ): Promise<AxiosResponse<ApiResponse<{ user: User }>>> {
    await getCsrfToken();

    try {
      const res = await api.post("/auth/login", data);
      return res;
    } catch (error) {
      console.error("Login error:", error);
      throw error;
    }
  }

  static async logout(): Promise<AxiosResponse<ApiResponse>> {
    try {
      const response = await api.post("/auth/logout");
      resetCsrfToken();
      return response;
    } catch (error) {
      console.error("Logout error:", error);
      resetCsrfToken();
      throw error;
    }
  }

  static async getCurrentUser(): Promise<
    AxiosResponse<ApiResponse<{ user: User }>>
  > {
    try {
      return await api.get("/auth/user");
    } catch (error) {
      console.error("Get current user error:", error);
      throw error;
    }
  }

  static async checkAuth(): Promise<User | null> {
    try {
      await getCsrfToken();
      const response = await this.getCurrentUser();
      if (response.data.success && response.data.data) {
        return response.data.data.user;
      }
      return null;
    } catch {
      return null;
    }
  }
}
