export type UserRole = "admin" | "manager" | "member" | "guest";

export type ProjectStatus = "active" | "archived";

export type ConfidentialityLevel =
  | "public"
  | "internal"
  | "confidential"
  | "top_secret";

export type TaskStatus = "todo" | "in_progress" | "done";

export type TaskPriority = "low" | "medium" | "high" | "urgent";

export type PermissionType = "read" | "write" | "delete" | "manage";

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  created_at: string;
  updated_at: string;
}

export interface Project {
  id: string;
  name: string;
  description: string;
  status: ProjectStatus;
  confidentiality_level: ConfidentialityLevel;
  owner_id: string;
  created_at: string;
  updated_at: string;
}

export interface Task {
  id: string;
  title: string;
  description: string;
  priority: TaskPriority;
  status: TaskStatus;
  project_id: string;
  project?: Project;
  created_by: string;
  creator?: User;
  assigned_to: string | null;
  assignedTo?: User;
  deadline: string | null;
  created_at: string;
  updated_at: string;
}

export interface Comment {
  id: string;
  content: string;
  task_id: string;
  user_id: string;
  user?: User;
  file_path: string | null;
  created_at: string;
  updated_at: string;
}

export interface Team {
  id: string;
  name: string;
  description: string | null;
  created_by: string;
  created_at: string;
  updated_at: string;
  members?: Array<{
    id: string;
    team_id: string;
    user_id: string;
    user: User;
    role: string;
    created_at: string;
    updated_at: string;
  }>;
}

export interface TeamMember {
  id: string;
  team_id: string;
  user_id: string;
  user: User;
  role: string;
  created_at: string;
  updated_at: string;
}

export interface ProjectPermission {
  id: string;
  project_id: string;
  user_id: string;
  permission: PermissionType;
  granted_by: string;
  created_at: string;
  updated_at: string;
}

// API Response wrappers
export interface ApiSuccessResponse<T = unknown> {
  success: true;
  data: T;
  message?: string;
}

export interface ApiErrorResponse {
  success: false;
  message: string;
  errors?: Record<string, string[]>;
}

export type ApiResponse<T = unknown> = ApiSuccessResponse<T> | ApiErrorResponse;

// Request types
export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role?: UserRole;
}

export interface LoginRequest {
  email: string;
  password: string;
  remember?: boolean;
}

export interface CreateProjectRequest {
  name: string;
  description: string;
  status?: ProjectStatus;
  confidentiality_level: ConfidentialityLevel;
}

export interface UpdateProjectRequest {
  name?: string;
  description?: string;
  status?: ProjectStatus;
  confidentiality_level?: ConfidentialityLevel;
}

export interface CreateTaskRequest {
  title: string;
  description: string;
  priority: TaskPriority;
  status?: TaskStatus;
  assigned_to?: string;
  deadline?: string;
}

export interface UpdateTaskRequest {
  title?: string;
  description?: string;
  priority?: TaskPriority;
  status?: TaskStatus;
  assigned_to?: string;
  deadline?: string;
}

export interface AssignTaskRequest {
  assigned_to: string;
}

export interface CreateCommentRequest {
  content: string;
  file?: File;
}

export interface UpdateCommentRequest {
  content?: string;
}

export interface CreateTeamRequest {
  name: string;
  description?: string;
}

export interface UpdateTeamRequest {
  name?: string;
  description?: string;
}

export interface AddMemberRequest {
  user_id: string;
}

export interface GrantPermissionRequest {
  user_id: string;
  permission: PermissionType;
}
