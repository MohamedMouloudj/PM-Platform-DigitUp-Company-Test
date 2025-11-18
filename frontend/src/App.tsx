import { RouterProvider } from "react-router/dom";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import queryConfig from "./config/react-query";
import { createBrowserRouter } from "react-router";
import { Toaster } from "sonner";

const queryClient = new QueryClient({
  ...queryConfig,
});

const router = createBrowserRouter([]);

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
