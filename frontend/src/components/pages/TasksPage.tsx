import { useState, useEffect } from "react";
import { useParams, Link } from "react-router";
import TaskService from "@/services/TaskService";
import type { Task } from "@/types/API";
import { Button } from "@/components/ui/button";
import Spinner from "../Spinner";
import { toast } from "sonner";

export default function TasksPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  const loadTasks = async () => {
    if (!projectId) return;

    try {
      setLoading(true);
      const response = await TaskService.getProjectTasks(projectId);
      if (response.data.success) {
        setTasks(response.data.data as Task[]);
      }
    } catch {
      toast.error("Failed to load tasks");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (projectId) {
      loadTasks();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [projectId]);

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this task?")) return;

    try {
      await TaskService.deleteTask(id);
      setTasks(tasks.filter((t) => t.id !== id));
      toast.success("Task deleted successfully");
    } catch {
      toast.error("Failed to delete task");
    }
  };

  const getStatusBadgeClass = (status: Task["status"]) => {
    const baseClass = "badge";
    switch (status) {
      case "done":
        return `${baseClass} bg-primary-100 text-primary-700 border-primary-200`;
      case "in_progress":
        return `${baseClass} bg-secondary-100 text-secondary-700 border-secondary-200`;
      case "todo":
        return `${baseClass} bg-border text-muted-foreground`;
      default:
        return `${baseClass} bg-border text-muted-foreground`;
    }
  };

  const getPriorityBadgeClass = (priority: Task["priority"]) => {
    const baseClass = "badge";
    switch (priority) {
      case "urgent":
        return `${baseClass} bg-destructive text-destructive-foreground`;
      case "high":
        return `${baseClass} bg-secondary-400 text-secondary-900`;
      case "medium":
        return `${baseClass} bg-secondary-200 text-secondary-800`;
      default:
        return `${baseClass} bg-border text-muted-foreground`;
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="tasks-page p-8">
      <div className="page-header">
        <h1>Tasks</h1>
        <Link to={`/dashboard/projects/${projectId}/tasks/create`}>
          <Button>Create Task</Button>
        </Link>
      </div>

      {tasks.length === 0 ? (
        <div className="empty-state">
          <p>No tasks yet. Create your first task!</p>
        </div>
      ) : (
        <div className="space-y-4">
          {tasks.map((task) => (
            <div
              key={task.id}
              className="task-card bg-card border border-border rounded-lg p-6"
            >
              <div className="flex justify-between items-start mb-4">
                <div className="flex-1">
                  <h3 className="text-xl font-semibold text-foreground mb-2">
                    {task.title}
                  </h3>
                  <p className="text-muted-foreground">{task.description}</p>
                </div>
                <div className="flex gap-2">
                  <span className={getStatusBadgeClass(task.status)}>
                    {task.status}
                  </span>
                  <span className={getPriorityBadgeClass(task.priority)}>
                    {task.priority}
                  </span>
                </div>
              </div>

              <div className="flex justify-between items-center">
                <div className="text-sm text-muted-foreground">
                  {task.deadline && (
                    <span>
                      Deadline: {new Date(task.deadline).toLocaleDateString()}
                    </span>
                  )}
                  {(task.assigned_to || task.assignedTo) && (
                    <span className="ml-4">
                      Assigned to:{" "}
                      {typeof task.assignedTo === "object" &&
                      task.assignedTo?.name
                        ? task.assignedTo.name
                        : task.assigned_to
                        ? `User #${task.assigned_to}`
                        : "Unassigned"}
                    </span>
                  )}
                </div>
                <div className="flex gap-2">
                  <Link to={`/dashboard/tasks/${task.id}`}>
                    <Button variant="outline" size="sm">
                      View
                    </Button>
                  </Link>
                  <Link to={`/dashboard/tasks/${task.id}/edit`}>
                    <Button variant="outline" size="sm">
                      Edit
                    </Button>
                  </Link>
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleDelete(task.id)}
                  >
                    Delete
                  </Button>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
