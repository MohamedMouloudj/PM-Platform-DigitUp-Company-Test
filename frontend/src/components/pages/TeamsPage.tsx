import { useState, useEffect } from "react";
import { useNavigate } from "react-router";
import TeamService from "@/services/TeamService";
import type { Team } from "@/types/API";
import { IconPlus, IconEdit, IconTrash } from "@tabler/icons-react";
import { toast } from "sonner";
import Spinner from "../Spinner";
import { usePermission } from "@/components/RoleGuard";
import { Button } from "@/components/ui/button";

export default function TeamsPage() {
  const navigate = useNavigate();
  const [teams, setTeams] = useState<Team[]>([]);
  const [loading, setLoading] = useState(true);
  const { canManageTeams } = usePermission();

  useEffect(() => {
    loadTeams();
  }, []);

  const loadTeams = async () => {
    try {
      const response = await TeamService.getTeams();
      if (response.data.success && response.data.data) {
        setTeams(response.data.data);
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toast.error(err.response?.data?.message || "Failed to load teams");
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (teamId: string) => {
    if (!confirm("Are you sure you want to delete this team?")) return;

    try {
      const response = await TeamService.deleteTeam(teamId);
      if (response.data.success) {
        toast.success("Team deleted successfully");
        loadTeams();
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toast.error(err.response?.data?.message || "Failed to delete team");
    }
  };

  if (loading) {
    return <Spinner />;
  }

  return (
    <div className="projects-page">
      <div className="page-header">
        <h1>Teams</h1>
        {canManageTeams() && (
          <Button onClick={() => navigate("/dashboard/teams/create")}>
            <IconPlus size={20} />
            Create Team
          </Button>
        )}
      </div>

      {teams.length === 0 ? (
        <div className="empty-state">
          <p>No teams found. Create your first team to get started.</p>
          {canManageTeams() && (
            <Button onClick={() => navigate("/dashboard/teams/create")}>
              Create Team
            </Button>
          )}
        </div>
      ) : (
        <div className="teams-grid">
          {teams.map((team) => (
            <div key={team.id} className="team-card">
              <div className="team-header">
                <h3
                  onClick={() => navigate(`/dashboard/teams/${team.id}`)}
                  style={{ cursor: "pointer" }}
                >
                  {team.name}
                </h3>
                <div className="team-actions">
                  <button
                    onClick={() => navigate(`/dashboard/teams/${team.id}`)}
                    className="btn-icon"
                    title="View Details"
                  >
                    <IconEdit size={18} />
                  </button>
                  <button
                    onClick={() => handleDelete(team.id)}
                    className="btn-icon btn-danger"
                    title="Delete Team"
                  >
                    <IconTrash size={18} />
                  </button>
                </div>
              </div>
              {team.description && (
                <p className="team-description">{team.description}</p>
              )}
              <div className="team-stats">
                <span className="stat-item">
                  <strong>Members:</strong> {team.members?.length || 0}
                </span>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
