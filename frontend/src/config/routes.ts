import type { AdminRoute, Route } from "@/types/Route";
import {
  IconHome,
  IconFolder,
  IconCheckbox,
  IconUsers,
} from "@tabler/icons-react";

export const APP_ROUTES: Route[] = [{ label: "Home", path: "/" }];

export const ADMIN_REDIRECT_PATH = "/dashboard";

export const ADMIN_ROUTES: AdminRoute[] = [
  {
    label: "Dashboard",
    path: "/dashboard",
    icon: IconHome,
  },
  {
    label: "Projects",
    path: "/dashboard/projects",
    icon: IconFolder,
  },
  {
    label: "Tasks",
    path: "/dashboard/tasks",
    icon: IconCheckbox,
  },
  {
    label: "Teams",
    path: "/dashboard/teams",
    icon: IconUsers,
  },
];
