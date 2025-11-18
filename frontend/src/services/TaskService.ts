import api from "./api";
import type {
  ApiResponse,
  Task,
  CreateTaskRequest,
  UpdateTaskRequest,
  AssignTaskRequest,
} from "@/types/API";
import type { AxiosResponse } from "axios";

export default class TaskService {
  static async getProjectTasks(
    projectId: string
  ): Promise<AxiosResponse<ApiResponse<Task[]>>> {
    return api.get(`/projects/${projectId}/tasks`);
  }

  static async getTask(id: string): Promise<AxiosResponse<ApiResponse<Task>>> {
    return api.get(`/tasks/${id}`);
  }

  static async createTask(
    projectId: string,
    data: CreateTaskRequest
  ): Promise<AxiosResponse<ApiResponse<Task>>> {
    return api.post(`/projects/${projectId}/tasks`, data);
  }

  static async updateTask(
    id: string,
    data: UpdateTaskRequest
  ): Promise<AxiosResponse<ApiResponse<Task>>> {
    return api.put(`/tasks/${id}`, data);
  }

  static async deleteTask(id: string): Promise<AxiosResponse<ApiResponse>> {
    return api.delete(`/tasks/${id}`);
  }

  static async assignTask(
    id: string,
    data: AssignTaskRequest
  ): Promise<AxiosResponse<ApiResponse<Task>>> {
    return api.post(`/tasks/${id}/assign`, data);
  }
}
