import { RouterProvider } from "react-router/dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import queryConfig from "./config/react-query";
import { createBrowserRouter } from "react-router";
import { Toaster } from "sonner";
import AppLayout from "./components/layout/AppLayout";
import DashboardLayout from "./components/layout/DashboardLayout";

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
        element: <div>Login page</div>,
      },
      {
        index: true,
        element: <div>Register page</div>,
      },
      {
        path: "/dashboard",
        element: <DashboardLayout />,
        children: [],
      },
    ],
  },
]);

function App() {
  return (
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
      <ReactQueryDevtools initialIsOpen={false} />
      <Toaster position="bottom-right" richColors />
    </QueryClientProvider>
  );
}

export default App;
