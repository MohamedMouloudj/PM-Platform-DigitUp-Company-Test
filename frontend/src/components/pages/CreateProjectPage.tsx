import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { createProjectSchema, type CreateProjectFormData } from "@/schemas";
import ProjectService from "@/services/ProjectService";
import { useState } from "react";
import { useNavigate } from "react-router";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { toast } from "sonner";
import RoleGuard from "@/components/RoleGuard";

export default function CreateProjectPage() {
  const navigate = useNavigate();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<CreateProjectFormData>({
    resolver: zodResolver(createProjectSchema),
    defaultValues: {
      name: "",
      description: "",
      status: "active",
      confidentiality_level: "internal",
    },
  });

  const onSubmit = async (data: CreateProjectFormData) => {
    try {
      setIsSubmitting(true);
      const response = await ProjectService.createProject(data);
      if (response.data.success) {
        toast.success("Project created successfully!");
        navigate("/dashboard/projects");
      }
    } catch {
      toast.error("Failed to create project");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <RoleGuard allowedRoles={["admin", "manager"]}>
      <div className="create-project-page">
        <div className="page-header">
          <h1>Create New Project</h1>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="project-form">
          <div className="form-group">
            <Label htmlFor="name">Project Name</Label>
            <Controller
              name="name"
              control={control}
              render={({ field }) => (
                <Input
                  {...field}
                  id="name"
                  placeholder="Enter project name"
                  disabled={isSubmitting}
                />
              )}
            />
            {errors.name && (
              <span className="field-error">{errors.name.message}</span>
            )}
          </div>

          <div className="form-group">
            <Label htmlFor="description">Description</Label>
            <Controller
              name="description"
              control={control}
              render={({ field }) => (
                <Textarea
                  {...field}
                  id="description"
                  placeholder="Enter project description"
                  disabled={isSubmitting}
                  rows={5}
                />
              )}
            />
            {errors.description && (
              <span className="field-error">{errors.description.message}</span>
            )}
          </div>

          <div className="form-group">
            <Label htmlFor="confidentiality_level">Confidentiality Level</Label>
            <Controller
              name="confidentiality_level"
              control={control}
              render={({ field }) => (
                <select
                  {...field}
                  id="confidentiality_level"
                  className="form-select"
                >
                  <option value="public">Public</option>
                  <option value="internal">Internal</option>
                  <option value="confidential">Confidential</option>
                  <option value="top_secret">Top Secret</option>
                </select>
              )}
            />
            {errors.confidentiality_level && (
              <span className="field-error">
                {errors.confidentiality_level.message}
              </span>
            )}
          </div>

          <div className="form-actions">
            <Button
              type="button"
              variant="outline"
              onClick={() => navigate(-1)}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isSubmitting}>
              {isSubmitting ? "Creating..." : "Create Project"}
            </Button>
          </div>
        </form>
      </div>
    </RoleGuard>
  );
}
