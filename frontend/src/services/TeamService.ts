import api from "./api";
import type {
  ApiResponse,
  Team,
  TeamMember,
  CreateTeamRequest,
  UpdateTeamRequest,
  AddMemberRequest,
} from "@/types/API";
import type { AxiosResponse } from "axios";

export default class TeamService {
  static async getTeams(): Promise<AxiosResponse<ApiResponse<Team[]>>> {
    return api.get("/teams");
  }

  static async getTeam(id: string): Promise<AxiosResponse<ApiResponse<Team>>> {
    return api.get(`/teams/${id}`);
  }

  static async createTeam(
    data: CreateTeamRequest
  ): Promise<AxiosResponse<ApiResponse<Team>>> {
    return api.post("/teams", data);
  }

  static async updateTeam(
    id: string,
    data: UpdateTeamRequest
  ): Promise<AxiosResponse<ApiResponse<Team>>> {
    return api.put(`/teams/${id}`, data);
  }

  static async deleteTeam(id: string): Promise<AxiosResponse<ApiResponse>> {
    return api.delete(`/teams/${id}`);
  }

  static async addMember(
    id: string,
    data: AddMemberRequest
  ): Promise<AxiosResponse<ApiResponse<TeamMember>>> {
    return api.post(`/teams/${id}/members`, data);
  }

  static async removeMember(
    teamId: string,
    userId: string
  ): Promise<AxiosResponse<ApiResponse>> {
    return api.delete(`/teams/${teamId}/members/${userId}`);
  }
}
