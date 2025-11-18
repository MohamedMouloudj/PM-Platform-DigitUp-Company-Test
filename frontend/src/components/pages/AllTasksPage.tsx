import { useState, useEffect } from "react";
import { useNavigate } from "react-router";
import type { Task } from "@/types/API";
import { IconEye } from "@tabler/icons-react";
import { toast } from "sonner";
import Spinner from "../Spinner";
import { Button } from "@/components/ui/button";
import TaskService from "@/services/TaskService";

export default function AllTasksPage() {
  const navigate = useNavigate();
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadTasks();
  }, []);

  const loadTasks = async () => {
    try {
      const response = await TaskService.getAllTasks();
      if (response.data.success && response.data.data) {
        setTasks(response.data.data);
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toast.error(err.response?.data?.message || "Failed to load tasks");
    } finally {
      setLoading(false);
    }
  };

  const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
      urgent: "bg-red-100 text-red-700",
      high: "bg-orange-100 text-orange-700",
      medium: "bg-yellow-100 text-yellow-700",
      low: "bg-green-100 text-green-700",
    };
    return colors[priority] || "bg-gray-100 text-gray-700";
  };

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      todo: "bg-gray-100 text-gray-700",
      in_progress: "bg-blue-100 text-blue-700",
      done: "bg-green-100 text-green-700",
    };
    return colors[status] || "bg-gray-100 text-gray-700";
  };

  if (loading) {
    return <Spinner />;
  }

  return (
    <div className="projects-page">
      <div className="page-header">
        <h1>All Tasks</h1>
        <Button onClick={() => navigate("/dashboard/projects")}>
          View Projects
        </Button>
      </div>

      {tasks.length === 0 ? (
        <div className="empty-state">
          <p>No tasks found. Tasks are created within projects.</p>
          <Button onClick={() => navigate("/dashboard/projects")}>
            Go to Projects
          </Button>
        </div>
      ) : (
        <div className="space-y-4">
          <div className="grid gap-4">
            {tasks.map((task) => (
              <div
                key={task.id}
                className="bg-card border border-border rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div className="flex items-start justify-between">
                  <div className="flex-1">
                    {task.project && (
                      <p className="text-xs text-muted-foreground mb-1">
                        Project: {task.project.name}
                      </p>
                    )}
                    <h3
                      className="text-lg font-semibold mb-2 cursor-pointer hover:text-primary"
                      onClick={() => navigate(`/dashboard/tasks/${task.id}`)}
                    >
                      {task.title}
                    </h3>
                    <p className="text-muted-foreground text-sm mb-3 line-clamp-2">
                      {task.description}
                    </p>
                    <div className="flex gap-2 flex-wrap">
                      <span className={`badge ${getStatusColor(task.status)}`}>
                        {task.status.replace("_", " ")}
                      </span>
                      <span
                        className={`badge ${getPriorityColor(task.priority)}`}
                      >
                        {task.priority}
                      </span>
                      {task.assignedTo && (
                        <span className="badge bg-blue-100 text-blue-700">
                          Assigned to: {task.assignedTo.name}
                        </span>
                      )}
                      {task.deadline && (
                        <span className="badge bg-purple-100 text-purple-700">
                          Due: {new Date(task.deadline).toLocaleDateString()}
                        </span>
                      )}
                    </div>
                  </div>
                  <button
                    onClick={() => navigate(`/dashboard/tasks/${task.id}`)}
                    className="btn-icon ml-4"
                    title="View Details"
                  >
                    <IconEye size={20} />
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
