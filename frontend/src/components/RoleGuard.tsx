import { Navigate } from "react-router";
import { useAuth } from "@/context/AuthContext";
import type { UserRole } from "@/types/API";
import { toast } from "sonner";

interface RoleGuardProps {
  children: React.ReactNode;
  allowedRoles: UserRole[];
  redirectTo?: string;
}

export default function RoleGuard({
  children,
  allowedRoles,
  redirectTo = "/dashboard",
}: RoleGuardProps) {
  const { user } = useAuth();

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  if (!allowedRoles.includes(user.role)) {
    toast.error("You don't have permission to access this page");
    return <Navigate to={redirectTo} replace />;
  }

  return <>{children}</>;
}

// Helper hook for checking permissions in components
export function usePermission() {
  const { user } = useAuth();

  const hasRole = (roles: UserRole | UserRole[]): boolean => {
    if (!user) return false;
    const roleArray = Array.isArray(roles) ? roles : [roles];
    return roleArray.includes(user.role);
  };

  const isAdmin = (): boolean => {
    return user?.role === "admin";
  };

  const isManager = (): boolean => {
    return user?.role === "manager" || user?.role === "admin";
  };

  const canManageProjects = (): boolean => {
    return ["admin", "manager"].includes(user?.role || "");
  };

  const canManageTeams = (): boolean => {
    return ["admin", "manager"].includes(user?.role || "");
  };

  return {
    user,
    hasRole,
    isAdmin,
    isManager,
    canManageProjects,
    canManageTeams,
  };
}
