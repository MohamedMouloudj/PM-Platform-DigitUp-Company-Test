import { useAuth } from "@/context/AuthContext";
import { Link } from "react-router";

export default function DashboardHome() {
  const { user } = useAuth();

  return (
    <div className="dashboard-home">
      <div className="dashboard-header">
        <h1>Welcome, {user?.name}!</h1>
        <p className="dashboard-subtitle">Role: {user?.role}</p>
      </div>

      <div className="dashboard-grid">
        <Link to="/dashboard/projects" className="dashboard-card">
          <h3>Projects</h3>
          <p>Manage all your projects</p>
        </Link>

        <Link to="/dashboard/tasks" className="dashboard-card">
          <h3>Tasks</h3>
          <p>View and manage tasks</p>
        </Link>

        <Link to="/dashboard/teams" className="dashboard-card">
          <h3>Teams</h3>
          <p>Manage team members</p>
        </Link>

        <Link to="/dashboard/profile" className="dashboard-card">
          <h3>Profile</h3>
          <p>View your profile</p>
        </Link>
      </div>
    </div>
  );
}
