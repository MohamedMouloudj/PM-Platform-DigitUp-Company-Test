import { z } from "zod";

// Auth Schemas
export const loginSchema = z.object({
  email: z.string().email("Please provide a valid email address"),
  password: z.string().min(1, "Password is required"),
  remember: z.boolean().optional(),
});

export const registerSchema = z
  .object({
    name: z.string().min(1, "Name is required").max(255, "Name too long"),
    email: z.string().email("Please provide a valid email address").max(255),
    password: z
      .string()
      .min(8, "Password must be at least 8 characters")
      .regex(/[A-Z]/, "Password must contain at least one uppercase letter")
      .regex(/[a-z]/, "Password must contain at least one lowercase letter")
      .regex(/[0-9]/, "Password must contain at least one number"),
    password_confirmation: z.string(),
    role: z.enum(["admin", "manager", "member", "guest"]).optional(),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Passwords do not match",
    path: ["password_confirmation"],
  });

// Project Schemas
export const createProjectSchema = z.object({
  name: z.string().min(1, "Project name is required").max(255),
  description: z.string().min(1, "Description is required").max(5000),
  status: z.enum(["active", "archived"]).optional(),
  confidentiality_level: z.enum([
    "public",
    "internal",
    "confidential",
    "top_secret",
  ]),
});

export const updateProjectSchema = z.object({
  name: z.string().min(1).max(255).optional(),
  description: z.string().min(1).max(5000).optional(),
  status: z.enum(["active", "archived"]).optional(),
  confidentiality_level: z
    .enum(["public", "internal", "confidential", "top_secret"])
    .optional(),
});

// Task Schemas
export const createTaskSchema = z.object({
  title: z.string().min(1, "Title is required").max(255),
  description: z.string().min(1, "Description is required").max(10000),
  priority: z.enum(["low", "medium", "high", "urgent"]),
  status: z.enum(["todo", "in_progress", "done"]).optional(),
  assigned_to: z.string().uuid().optional(),
  deadline: z.string().optional(),
});

export const updateTaskSchema = z.object({
  title: z.string().min(1).max(255).optional(),
  description: z.string().min(1).max(10000).optional(),
  priority: z.enum(["low", "medium", "high", "urgent"]).optional(),
  status: z.enum(["todo", "in_progress", "done"]).optional(),
  assigned_to: z.string().uuid().optional(),
  deadline: z.string().optional(),
});

export const assignTaskSchema = z.object({
  assigned_to: z.string().uuid("Invalid user ID"),
});

// Comment Schemas
export const createCommentSchema = z.object({
  content: z.string().min(1, "Comment content is required").max(5000),
  file: z.instanceof(File).optional(),
});

export const updateCommentSchema = z.object({
  content: z.string().min(1).max(5000).optional(),
});

// Team Schemas
export const createTeamSchema = z.object({
  name: z.string().min(1, "Team name is required").max(255),
  description: z.string().max(1000).optional(),
});

export const updateTeamSchema = z.object({
  name: z.string().min(1).max(255).optional(),
  description: z.string().max(1000).optional(),
});

export const addMemberSchema = z.object({
  user_id: z.string().uuid("Invalid user ID"),
});

// Permission Schemas
export const grantPermissionSchema = z.object({
  user_id: z.string().uuid("Invalid user ID"),
  permission: z.enum(["read", "write", "delete", "manage"]),
});

export type LoginFormData = z.infer<typeof loginSchema>;
export type RegisterFormData = z.infer<typeof registerSchema>;
export type CreateProjectFormData = z.infer<typeof createProjectSchema>;
export type UpdateProjectFormData = z.infer<typeof updateProjectSchema>;
export type CreateTaskFormData = z.infer<typeof createTaskSchema>;
export type UpdateTaskFormData = z.infer<typeof updateTaskSchema>;
export type AssignTaskFormData = z.infer<typeof assignTaskSchema>;
export type CreateCommentFormData = z.infer<typeof createCommentSchema>;
export type UpdateCommentFormData = z.infer<typeof updateCommentSchema>;
export type CreateTeamFormData = z.infer<typeof createTeamSchema>;
export type UpdateTeamFormData = z.infer<typeof updateTeamSchema>;
export type AddMemberFormData = z.infer<typeof addMemberSchema>;
export type GrantPermissionFormData = z.infer<typeof grantPermissionSchema>;
