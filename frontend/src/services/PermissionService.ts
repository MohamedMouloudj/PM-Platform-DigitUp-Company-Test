import api from "../lib/api";
import type {
  ApiResponse,
  ProjectPermission,
  GrantPermissionRequest,
} from "@/types/API";
import type { AxiosResponse } from "axios";

export default class PermissionService {
  static async getProjectPermissions(
    projectId: string
  ): Promise<AxiosResponse<ApiResponse<ProjectPermission[]>>> {
    return api.get(`/projects/${projectId}/permissions`);
  }

  static async grantPermission(
    projectId: string,
    data: GrantPermissionRequest
  ): Promise<AxiosResponse<ApiResponse<ProjectPermission>>> {
    return api.post(`/projects/${projectId}/permissions`, data);
  }

  static async revokePermission(
    projectId: string,
    userId: string
  ): Promise<AxiosResponse<ApiResponse>> {
    return api.delete(`/projects/${projectId}/permissions/${userId}`);
  }
}
