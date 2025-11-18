import { useState, useEffect } from "react";
import { Link } from "react-router";
import ProjectService from "@/services/ProjectService";
import type { Project } from "@/types/API";
import { Button } from "@/components/ui/button";
import Spinner from "../Spinner";
import { toast } from "sonner";

export default function ProjectsPage() {
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadProjects();
  }, []);

  const loadProjects = async () => {
    try {
      setLoading(true);
      const response = await ProjectService.getProjects();
      if (response.data.success) {
        setProjects(response.data.data.projects);
      }
    } catch {
      toast.error("Failed to load projects");
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this project?")) return;

    try {
      await ProjectService.deleteProject(id);
      setProjects(projects.filter((p) => p.id !== id));
      toast.success("Project deleted successfully");
    } catch {
      toast.error("Failed to delete project");
    }
  };

  const handleArchive = async (id: string) => {
    try {
      const response = await ProjectService.archiveProject(id);
      if (response.data.success) {
        setProjects(
          projects.map((p) =>
            p.id === id ? { ...p, status: "archived" as const } : p
          )
        );
        toast.success("Project archived successfully");
      }
    } catch {
      toast.error("Failed to archive project");
    }
  };

  const handleRestore = async (id: string) => {
    try {
      const response = await ProjectService.restoreProject(id);
      if (response.data.success) {
        setProjects(
          projects.map((p) =>
            p.id === id ? { ...p, status: "active" as const } : p
          )
        );
        toast.success("Project restored successfully");
      }
    } catch {
      toast.error("Failed to restore project");
    }
  };

  if (loading) return <Spinner />;

  return (
    <div className="projects-page">
      <div className="page-header">
        <h1>Projects</h1>
        <Link to="/dashboard/projects/create">
          <Button>Create Project</Button>
        </Link>
      </div>

      {projects.length === 0 ? (
        <div className="empty-state">
          <p>No projects yet. Create your first project!</p>
        </div>
      ) : (
        <div className="projects-grid">
          {projects.map((project) => (
            <div key={project.id} className="project-card">
              <div className="project-card-header">
                <h3>{project.name}</h3>
                <span className={`badge badge-${project.status}`}>
                  {project.status}
                </span>
              </div>
              <p className="project-description">{project.description}</p>
              <div className="project-meta">
                <span className="confidentiality-badge">
                  {project.confidentiality_level}
                </span>
              </div>
              <div className="project-actions">
                <Link to={`/dashboard/projects/${project.id}`}>
                  <Button variant="outline" size="sm">
                    View
                  </Button>
                </Link>
                <Link to={`/dashboard/projects/${project.id}/edit`}>
                  <Button variant="outline" size="sm">
                    Edit
                  </Button>
                </Link>
                {project.status === "active" ? (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleArchive(project.id)}
                  >
                    Archive
                  </Button>
                ) : (
                  <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handleRestore(project.id)}
                  >
                    Restore
                  </Button>
                )}
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handleDelete(project.id)}
                >
                  Delete
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
