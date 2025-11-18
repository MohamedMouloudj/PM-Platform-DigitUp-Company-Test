import { Separator } from "@/components/ui/separator";
import { SidebarTrigger } from "@/components/ui/sidebar";
import { ADMIN_REDIRECT_PATH } from "@/config/routes";
import { useLocation } from "react-router";

export function SiteHeader() {
  const location = useLocation();
  let adminSubPath = location.pathname.slice(ADMIN_REDIRECT_PATH.length);
  if (adminSubPath === "") {
    adminSubPath = "Dashboard";
  } else {
    adminSubPath = adminSubPath
      .slice(1)
      .split("/")
      .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
      .join(" / ");
  }

  return (
    <header className="flex h-(--header-height) shrink-0 items-center gap-2 border-b transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-(--header-height)">
      <div className="flex w-full items-center gap-1 px-4 lg:gap-2 lg:px-6">
        <SidebarTrigger className="-ml-1" />
        <Separator
          orientation="vertical"
          className="mx-2 data-[orientation=vertical]:h-4"
        />
        <h1 className="text-base font-medium">{adminSubPath}</h1>
      </div>
    </header>
  );
}
