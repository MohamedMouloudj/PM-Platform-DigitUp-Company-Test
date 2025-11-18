import { Outlet } from "react-router";

export default function AppLayout() {
  return (
    <main className="app-main">
      <Outlet />
    </main>
  );
}
