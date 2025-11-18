import api from "./api";
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
    return api.post("/auth/register", data);
  }

  static async login(
    data: LoginRequest
  ): Promise<AxiosResponse<ApiResponse<{ user: User }>>> {
    return api.post("/auth/login", data);
  }

  static async logout(): Promise<AxiosResponse<ApiResponse>> {
    return api.post("/auth/logout");
  }

  static async getCurrentUser(): Promise<
    AxiosResponse<ApiResponse<{ user: User }>>
  > {
    return api.get("/auth/user");
  }
}
