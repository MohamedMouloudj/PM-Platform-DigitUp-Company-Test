import { RouterProvider } from "react-router/dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import queryConfig from "./config/react-query";
import { createBrowserRouter, Navigate } from "react-router";
import { Toaster } from "sonner";
import AppLayout from "./components/layout/AppLayout";
import DashboardLayout from "./components/layout/DashboardLayout";
import { AuthProvider } from "./context/AuthContext";
import ProtectedRoute from "./components/ProtectedRoute";
import LoginPage from "./components/pages/LoginPage";
import RegisterPage from "./components/pages/RegisterPage";
import DashboardHome from "./components/pages/DashboardHome";
import ProjectsPage from "./components/pages/ProjectsPage";
import CreateProjectPage from "./components/pages/CreateProjectPage";
import ProjectDetailPage from "./components/pages/ProjectDetailPage";
import EditProjectPage from "./components/pages/EditProjectPage";
import TasksPage from "./components/pages/TasksPage";
import CreateTaskPage from "./components/pages/CreateTaskPage";
import TaskDetailPage from "./components/pages/TaskDetailPage";

const queryClient = new QueryClient({
  ...queryConfig,
});

const router = createBrowserRouter([
  {
    path: "/",
    element: <AppLayout />,
    children: [
      {
        index: true,
        element: <Navigate to="/login" replace />,
      },
      {
        path: "login",
        element: <LoginPage />,
      },
      {
        path: "register",
        element: <RegisterPage />,
      },
    ],
  },
  {
    path: "/dashboard",
    element: <ProtectedRoute />,
    children: [
      {
        path: "",
        element: <DashboardLayout />,
        children: [
          {
            index: true,
            element: <DashboardHome />,
          },
          {
            path: "projects",
            element: <ProjectsPage />,
          },
          {
            path: "projects/create",
            element: <CreateProjectPage />,
          },
          {
            path: "projects/:projectId",
            element: <ProjectDetailPage />,
          },
          {
            path: "projects/:projectId/edit",
            element: <EditProjectPage />,
          },
          {
            path: "projects/:projectId/tasks",
            element: <TasksPage />,
          },
          {
            path: "projects/:projectId/tasks/create",
            element: <CreateTaskPage />,
          },
          {
            path: "tasks/:taskId",
            element: <TaskDetailPage />,
          },
        ],
      },
    ],
  },
]);

function App() {
  return (
    <AuthProvider>
      <QueryClientProvider client={queryClient}>
        <RouterProvider router={router} />
        <ReactQueryDevtools initialIsOpen={false} />
        <Toaster position="bottom-right" richColors />
      </QueryClientProvider>
    </AuthProvider>
  );
}

export default App;
