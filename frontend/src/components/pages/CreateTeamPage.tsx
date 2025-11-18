import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { useNavigate } from "react-router";
import { createTeamSchema } from "@/schemas";
import type { z } from "zod";
import TeamService from "@/services/TeamService";
import { toast } from "sonner";
import RoleGuard from "@/components/RoleGuard";

type CreateTeamFormData = z.infer<typeof createTeamSchema>;

export default function CreateTeamPage() {
  const navigate = useNavigate();
  const {
    control,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<CreateTeamFormData>({
    resolver: zodResolver(createTeamSchema),
    defaultValues: {
      name: "",
      description: "",
    },
  });

  const onSubmit = async (data: CreateTeamFormData) => {
    try {
      const response = await TeamService.createTeam(data);
      if (response.data.success) {
        toast.success("Team created successfully");
        navigate("/dashboard/teams");
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toast.error(err.response?.data?.message || "Failed to create team");
    }
  };

  return (
    <RoleGuard allowedRoles={["admin", "manager"]}>
      <div className="page-container">
        <div className="page-header">
          <h1>Create New Team</h1>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="form-container">
          <div className="form-group">
            <label htmlFor="name">Team Name</label>
            <Controller
              name="name"
              control={control}
              render={({ field }) => (
                <input
                  {...field}
                  type="text"
                  id="name"
                  className={errors.name ? "error" : ""}
                  placeholder="Enter team name"
                />
              )}
            />
            {errors.name && (
              <span className="error-message">{errors.name.message}</span>
            )}
          </div>

          <div className="form-group">
            <label htmlFor="description">Description (Optional)</label>
            <Controller
              name="description"
              control={control}
              render={({ field }) => (
                <textarea
                  {...field}
                  id="description"
                  rows={4}
                  className={errors.description ? "error" : ""}
                  placeholder="Enter team description"
                />
              )}
            />
            {errors.description && (
              <span className="error-message">
                {errors.description.message}
              </span>
            )}
          </div>

          <div className="form-actions">
            <button
              type="button"
              onClick={() => navigate("/dashboard/teams")}
              className="btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="btn-primary"
            >
              {isSubmitting ? "Creating..." : "Create Team"}
            </button>
          </div>
        </form>
      </div>
    </RoleGuard>
  );
}
