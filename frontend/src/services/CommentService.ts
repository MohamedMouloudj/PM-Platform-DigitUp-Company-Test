import api from "./api";
import type {
  ApiResponse,
  Comment,
  CreateCommentRequest,
  UpdateCommentRequest,
} from "@/types/API";
import type { AxiosResponse } from "axios";

export default class CommentService {
  static async getTaskComments(
    taskId: string
  ): Promise<AxiosResponse<ApiResponse<Comment[]>>> {
    return api.get(`/tasks/${taskId}/comments`);
  }

  static async createComment(
    taskId: string,
    data: CreateCommentRequest
  ): Promise<AxiosResponse<ApiResponse<Comment>>> {
    const formData = new FormData();
    formData.append("content", data.content);

    if (data.file) {
      formData.append("file", data.file);
    }

    return api.post(`/tasks/${taskId}/comments`, formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });
  }

  static async updateComment(
    id: string,
    data: UpdateCommentRequest
  ): Promise<AxiosResponse<ApiResponse<Comment>>> {
    return api.put(`/comments/${id}`, data);
  }

  static async deleteComment(id: string): Promise<AxiosResponse<ApiResponse>> {
    return api.delete(`/comments/${id}`);
  }
}
