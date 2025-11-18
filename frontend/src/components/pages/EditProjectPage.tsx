import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { updateProjectSchema, type UpdateProjectFormData } from "@/schemas";
import ProjectService from "@/services/ProjectService";
import { useState, useEffect } from "react";
import { useNavigate, useParams } from "react-router";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import Spinner from "../Spinner";
import { toast } from "sonner";

export default function EditProjectPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [loading, setLoading] = useState(true);

  const {
    control,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm<UpdateProjectFormData>({
    resolver: zodResolver(updateProjectSchema),
    defaultValues: {
      name: "",
      description: "",
      status: "active",
      confidentiality_level: "internal",
    },
  });

  useEffect(() => {
    if (projectId) {
      loadProject();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [projectId]);

  const loadProject = async () => {
    if (!projectId) return;

    try {
      setLoading(true);
      const response = await ProjectService.getProject(projectId);
      if (response.data.success) {
        const projectData = response.data.data.project;
        reset({
          name: projectData.name,
          description: projectData.description,
          status: projectData.status,
          confidentiality_level: projectData.confidentiality_level,
        });
      }
    } catch {
      toast.error("Failed to load project");
    } finally {
      setLoading(false);
    }
  };
  const onSubmit = async (data: UpdateProjectFormData) => {
    if (!projectId) return;

    try {
      setIsSubmitting(true);
      const response = await ProjectService.updateProject(projectId, data);
      if (response.data.success) {
        toast.success("Project updated successfully!");
        navigate(`/dashboard/projects/${projectId}`);
      }
    } catch {
      toast.error("Failed to update project");
    } finally {
      setIsSubmitting(false);
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="edit-project-page p-8">
      <div className="page-header">
        <h1>Edit Project</h1>
      </div>

      <form
        onSubmit={handleSubmit(onSubmit)}
        className="project-form max-w-2xl"
      >
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

        <div className="grid grid-cols-2 gap-4">
          <div className="form-group">
            <Label htmlFor="status">Status</Label>
            <Controller
              name="status"
              control={control}
              render={({ field }) => (
                <select {...field} id="status" className="form-select">
                  <option value="active">Active</option>
                  <option value="archived">Archived</option>
                  <option value="on_hold">On Hold</option>
                </select>
              )}
            />
            {errors.status && (
              <span className="field-error">{errors.status.message}</span>
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
        </div>

        <div className="form-actions">
          <Button type="button" variant="outline" onClick={() => navigate(-1)}>
            Cancel
          </Button>
          <Button type="submit" disabled={isSubmitting}>
            {isSubmitting ? "Updating..." : "Update Project"}
          </Button>
        </div>
      </form>
    </div>
  );
}
