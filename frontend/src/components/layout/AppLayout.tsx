import { Outlet } from "react-router";
import Footer from "./Footer";
import Navbar from "./Navbar";

export default function AppLayout() {
  return (
    // <AdminProvider>
    <>
      <Navbar />
      <main className="app-main">
        <Outlet />
      </main>
      <Footer />
    </>
    // </AdminProvider>
  );
}
