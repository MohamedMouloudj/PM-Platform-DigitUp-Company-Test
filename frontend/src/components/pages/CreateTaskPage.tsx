import { useForm, Controller } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { createTaskSchema, type CreateTaskFormData } from "@/schemas";
import TaskService from "@/services/TaskService";
import { useState } from "react";
import { useNavigate, useParams } from "react-router";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { toast } from "sonner";

export default function CreateTaskPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const navigate = useNavigate();
  const [isSubmitting, setIsSubmitting] = useState(false);

  const {
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<CreateTaskFormData>({
    resolver: zodResolver(createTaskSchema),
    defaultValues: {
      title: "",
      description: "",
      status: "todo",
      priority: "medium",
      deadline: "",
    },
  });

  const onSubmit = async (data: CreateTaskFormData) => {
    if (!projectId) {
      toast.error("Project ID is missing");
      return;
    }

    try {
      setIsSubmitting(true);
      const response = await TaskService.createTask(projectId, data);
      if (response.data.success) {
        toast.success("Task created successfully!");
        navigate(`/dashboard/projects/${projectId}/tasks`);
      }
    } catch {
      toast.error("Failed to create task");
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="create-task-page p-8">
      <div className="page-header">
        <h1>Create New Task</h1>
      </div>

      <form
        onSubmit={handleSubmit(onSubmit)}
        className="project-form max-w-2xl"
      >
        <div className="form-group">
          <Label htmlFor="title">Task Title</Label>
          <Controller
            name="title"
            control={control}
            render={({ field }) => (
              <Input
                {...field}
                id="title"
                placeholder="Enter task title"
                disabled={isSubmitting}
              />
            )}
          />
          {errors.title && (
            <span className="field-error">{errors.title.message}</span>
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
                placeholder="Enter task description"
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
                  <option value="todo">To Do</option>
                  <option value="in_progress">In Progress</option>
                  <option value="done">Done</option>
                </select>
              )}
            />
            {errors.status && (
              <span className="field-error">{errors.status.message}</span>
            )}
          </div>

          <div className="form-group">
            <Label htmlFor="priority">Priority</Label>
            <Controller
              name="priority"
              control={control}
              render={({ field }) => (
                <select {...field} id="priority" className="form-select">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              )}
            />
            {errors.priority && (
              <span className="field-error">{errors.priority.message}</span>
            )}
          </div>
        </div>

        <div className="form-group">
          <Label htmlFor="deadline">Deadline (Optional)</Label>
          <Controller
            name="deadline"
            control={control}
            render={({ field }) => (
              <Input
                {...field}
                id="deadline"
                type="date"
                disabled={isSubmitting}
              />
            )}
          />
          {errors.deadline && (
            <span className="field-error">{errors.deadline.message}</span>
          )}
        </div>

        <div className="form-actions">
          <Button type="button" variant="outline" onClick={() => navigate(-1)}>
            Cancel
          </Button>
          <Button type="submit" disabled={isSubmitting}>
            {isSubmitting ? "Creating..." : "Create Task"}
          </Button>
        </div>
      </form>
    </div>
  );
}
