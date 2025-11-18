import { type Icon } from "@tabler/icons-react";

export type Route = {
  label: string;
  path: string;
};

export type AdminRoute = Route & {
  icon?: Icon;
};
