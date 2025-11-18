import { useState, useEffect, useCallback } from "react";
import { useParams, useNavigate } from "react-router";
import TeamService from "@/services/TeamService";
import type { Team } from "@/types/API";
import { IconArrowLeft, IconUserPlus, IconTrash } from "@tabler/icons-react";
import { toast } from "sonner";
import Spinner from "../Spinner";

export default function TeamDetailPage() {
  const { teamId } = useParams<{ teamId: string }>();
  const navigate = useNavigate();
  const [team, setTeam] = useState<Team | null>(null);
  const [loading, setLoading] = useState(true);

  const loadTeam = useCallback(async () => {
    if (!teamId) return;

    try {
      const response = await TeamService.getTeam(teamId);
      if (response.data.success && response.data.data) {
        setTeam(response.data.data);
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toast.error(err.response?.data?.message || "Failed to load team");
    } finally {
      setLoading(false);
    }
  }, [teamId]);

  useEffect(() => {
    if (teamId) {
      loadTeam();
    }
  }, [teamId, loadTeam]);

  const handleRemoveMember = async (userId: string) => {
    if (!teamId || !confirm("Are you sure you want to remove this member?"))
      return;

    try {
      const response = await TeamService.removeMember(teamId, userId);
      if (response.data.success) {
        toast.success("Member removed successfully");
        loadTeam();
      }
    } catch (error: unknown) {
      const err = error as { response?: { data?: { message?: string } } };
      toast.error(err.response?.data?.message || "Failed to remove member");
    }
  };

  if (loading) {
    return <Spinner />;
  }

  if (!team) {
    return (
      <div className="error-container">
        <p>Team not found</p>
        <button
          onClick={() => navigate("/dashboard/teams")}
          className="btn-primary"
        >
          Back to Teams
        </button>
      </div>
    );
  }

  return (
    <div className="page-container">
      <div className="page-header">
        <button
          onClick={() => navigate("/dashboard/teams")}
          className="btn-back"
        >
          <IconArrowLeft size={20} />
          Back to Teams
        </button>
      </div>

      <div className="team-detail">
        <div className="team-info-section">
          <h1>{team.name}</h1>
          {team.description && (
            <p className="team-description">{team.description}</p>
          )}
        </div>

        <div className="team-members-section">
          <div className="section-header">
            <h2>Team Members ({team.members?.length || 0})</h2>
            <button
              className="btn-primary"
              onClick={() => toast.info("Add member feature coming soon")}
            >
              <IconUserPlus size={20} />
              Add Member
            </button>
          </div>

          {team.members && team.members.length > 0 ? (
            <div className="members-list">
              {team.members.map((member) => (
                <div key={member.id} className="member-card">
                  <div className="member-info">
                    <h3>{member.user.name}</h3>
                    <p className="member-email">{member.user.email}</p>
                    <span className="badge badge-role">{member.role}</span>
                  </div>
                  <button
                    onClick={() => handleRemoveMember(member.user.id)}
                    className="btn-icon btn-danger"
                    title="Remove Member"
                  >
                    <IconTrash size={18} />
                  </button>
                </div>
              ))}
            </div>
          ) : (
            <div className="empty-state">
              <p>No members in this team yet.</p>
              <button
                className="btn-primary"
                onClick={() => toast.info("Add member feature coming soon")}
              >
                Add First Member
              </button>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
