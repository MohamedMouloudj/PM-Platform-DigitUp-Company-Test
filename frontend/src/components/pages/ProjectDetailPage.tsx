import { useState, useEffect } from "react";
import { useParams, Link } from "react-router";
import ProjectService from "@/services/ProjectService";
import TaskService from "@/services/TaskService";
import type { Project, Task } from "@/types/API";
import { Button } from "@/components/ui/button";
import Spinner from "../Spinner";
import { toast } from "sonner";

export default function ProjectDetailPage() {
  const { projectId } = useParams<{ projectId: string }>();
  const [project, setProject] = useState<Project | null>(null);
  const [tasks, setTasks] = useState<Task[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (projectId) {
      loadProject();
      loadTasks();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [projectId]);

  const loadProject = async () => {
    if (!projectId) return;

    try {
      const response = await ProjectService.getProject(projectId);
      if (response.data.success) {
        setProject(response.data.data.project);
      }
    } catch {
      toast.error("Failed to load project");
    }
  };

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

  if (loading || !project) return <Spinner />;

  return (
    <div className="project-detail-page p-8">
      <div className="page-header mb-6">
        <div className="flex justify-between items-start">
          <div>
            <h1 className="text-3xl font-bold text-foreground mb-2">
              {project.name}
            </h1>
            <div className="flex gap-2 mb-4">
              <span className="badge bg-primary-100 text-primary-700">
                {project.status}
              </span>
              <span className="badge bg-secondary-100 text-secondary-700">
                {project.confidentiality_level}
              </span>
            </div>
          </div>
          <div className="flex gap-2">
            <Link to={`/dashboard/projects/${projectId}/edit`}>
              <Button variant="outline">Edit Project</Button>
            </Link>
            <Link to={`/dashboard/projects/${projectId}/tasks/create`}>
              <Button>Create Task</Button>
            </Link>
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          <div className="bg-card border border-border rounded-lg p-6 mb-6">
            <h2 className="text-xl font-semibold mb-4">Description</h2>
            <p className="text-muted-foreground whitespace-pre-wrap">
              {project.description}
            </p>
          </div>

          <div className="bg-card border border-border rounded-lg p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-xl font-semibold">Tasks ({tasks.length})</h2>
              <Link to={`/dashboard/projects/${projectId}/tasks`}>
                <Button variant="outline" size="sm">
                  View All
                </Button>
              </Link>
            </div>

            {tasks.length === 0 ? (
              <p className="text-muted-foreground text-center py-4">
                No tasks yet. Create your first task!
              </p>
            ) : (
              <div className="space-y-3">
                {tasks.slice(0, 5).map((task) => (
                  <Link
                    key={task.id}
                    to={`/dashboard/tasks/${task.id}`}
                    className="block border border-border rounded-lg p-4 hover:bg-accent transition-colors"
                  >
                    <div className="flex justify-between items-start">
                      <div className="flex-1">
                        <h3 className="font-semibold text-foreground">
                          {task.title}
                        </h3>
                        <p className="text-sm text-muted-foreground line-clamp-1">
                          {task.description}
                        </p>
                      </div>
                      <div className="flex gap-2">
                        <span className="badge text-xs bg-primary-100 text-primary-700">
                          {task.status}
                        </span>
                        <span className="badge text-xs bg-secondary-100 text-secondary-700">
                          {task.priority}
                        </span>
                      </div>
                    </div>
                  </Link>
                ))}
                {tasks.length > 5 && (
                  <p className="text-center text-sm text-muted-foreground pt-2">
                    and {tasks.length - 5} more tasks...
                  </p>
                )}
              </div>
            )}
          </div>
        </div>

        <div className="lg:col-span-1">
          <div className="bg-card border border-border rounded-lg p-6">
            <h2 className="text-lg font-semibold mb-4">Project Details</h2>
            <dl className="space-y-3">
              <div>
                <dt className="text-sm text-muted-foreground">Owner</dt>
                <dd className="text-foreground font-medium">
                  User #{project.owner_id}
                </dd>
              </div>
              <div>
                <dt className="text-sm text-muted-foreground">Status</dt>
                <dd className="text-foreground font-medium capitalize">
                  {project.status}
                </dd>
              </div>
              <div>
                <dt className="text-sm text-muted-foreground">
                  Confidentiality
                </dt>
                <dd className="text-foreground font-medium capitalize">
                  {project.confidentiality_level.replace("_", " ")}
                </dd>
              </div>
              <div>
                <dt className="text-sm text-muted-foreground">Created</dt>
                <dd className="text-foreground font-medium">
                  {new Date(project.created_at).toLocaleDateString()}
                </dd>
              </div>
              <div>
                <dt className="text-sm text-muted-foreground">Last Updated</dt>
                <dd className="text-foreground font-medium">
                  {new Date(project.updated_at).toLocaleDateString()}
                </dd>
              </div>
            </dl>
          </div>

          <div className="bg-card border border-border rounded-lg p-6 mt-4">
            <h2 className="text-lg font-semibold mb-4">Quick Actions</h2>
            <div className="space-y-2">
              <Link
                to={`/dashboard/projects/${projectId}/tasks`}
                className="block"
              >
                <Button variant="outline" className="w-full justify-start">
                  View All Tasks
                </Button>
              </Link>
              <Link
                to={`/dashboard/projects/${projectId}/tasks/create`}
                className="block"
              >
                <Button variant="outline" className="w-full justify-start">
                  Create New Task
                </Button>
              </Link>
              <Link
                to={`/dashboard/projects/${projectId}/edit`}
                className="block"
              >
                <Button variant="outline" className="w-full justify-start">
                  Edit Project
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
