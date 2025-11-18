import api from "../lib/api";
import type {
  ApiResponse,
  Project,
  CreateProjectRequest,
  UpdateProjectRequest,
} from "@/types/API";
import type { AxiosResponse } from "axios";

export default class ProjectService {
  static async getProjects(): Promise<
    AxiosResponse<ApiResponse<{ projects: Project[] }>>
  > {
    return api.get("/projects");
  }

  static async getProject(
    id: string
  ): Promise<AxiosResponse<ApiResponse<{ project: Project }>>> {
    return api.get(`/projects/${id}`);
  }

  static async createProject(
    data: CreateProjectRequest
  ): Promise<AxiosResponse<ApiResponse<{ project: Project }>>> {
    return api.post("/projects", data);
  }

  static async updateProject(
    id: string,
    data: UpdateProjectRequest
  ): Promise<AxiosResponse<ApiResponse<{ project: Project }>>> {
    return api.put(`/projects/${id}`, data);
  }

  static async deleteProject(id: string): Promise<AxiosResponse<ApiResponse>> {
    return api.delete(`/projects/${id}`);
  }

  static async archiveProject(
    id: string
  ): Promise<AxiosResponse<ApiResponse<{ project: Project }>>> {
    return api.post(`/projects/${id}/archive`);
  }

  static async restoreProject(
    id: string
  ): Promise<AxiosResponse<ApiResponse<{ project: Project }>>> {
    return api.post(`/projects/${id}/restore`);
  }
}
