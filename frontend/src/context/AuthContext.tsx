import React, {
  createContext,
  useContext,
  useState,
  useEffect,
  useCallback,
  useRef,
} from "react";
import type { User, LoginRequest, RegisterRequest } from "@/types/API";
import AuthService from "@/services/AuthService";
import type { AxiosError } from "axios";

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (data: LoginRequest) => Promise<void>;
  register: (data: RegisterRequest) => Promise<void>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
};

interface AuthProviderProps {
  children: React.ReactNode;
}

export const AuthProvider: React.FC<AuthProviderProps> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const initRef = useRef(false);

  const refreshUser = useCallback(async () => {
    try {
      const user = await AuthService.checkAuth();
      setUser(user);
    } catch (error: unknown) {
      const axiosError = error as AxiosError;
      // Silently fail on 401 (expected when not authenticated)
      if (axiosError.response?.status !== 401) {
        console.error("Failed to refresh user:", error);
      }
      setUser(null);
    }
  }, []);

  useEffect(() => {
    const initAuth = async () => {
      if (initRef.current) return;
      initRef.current = true;

      try {
        await refreshUser();
      } finally {
        setIsLoading(false);
      }
    };
    initAuth();
  }, [refreshUser]);

  const login = async (data: LoginRequest) => {
    try {
      const response = await AuthService.login(data);
      if (response.data.success) {
        setUser(response.data.data.user);
      } else {
        throw new Error(response.data.message || "Login failed");
      }
    } catch (error: unknown) {
      const axiosError = error as AxiosError<{
        message?: string;
        errors?: Record<string, string[]>;
      }>;

      if (axiosError.response?.data?.message) {
        throw new Error(axiosError.response.data.message);
      } else if (axiosError.message) {
        throw new Error(axiosError.message);
      } else {
        throw new Error("An unexpected error occurred during login");
      }
    }
  };

  const register = async (data: RegisterRequest) => {
    try {
      const response = await AuthService.register(data);
      console.log(response);

      if (response.data.success) {
        setUser(response.data.data.user);
      } else {
        throw new Error(response.data.message || "Registration failed");
      }
    } catch (error: unknown) {
      const axiosError = error as AxiosError<{
        message?: string;
        errors?: Record<string, string[]>;
      }>;

      if (axiosError.response?.data?.message) {
        throw new Error(axiosError.response.data.message);
      } else if (axiosError.response?.data?.errors) {
        const firstError = Object.values(axiosError.response.data.errors)[0];
        throw new Error(firstError?.[0] || "Registration failed");
      } else if (axiosError.message) {
        throw new Error(axiosError.message);
      } else {
        throw new Error("An unexpected error occurred during registration");
      }
    }
  };

  const logout = async () => {
    try {
      // Clear user state immediately to prevent race conditions
      setUser(null);
      await AuthService.logout();
    } catch (error: unknown) {
      const axiosError = error as AxiosError;
      console.error("Logout error:", axiosError.message || error);
    }
  };

  const value: AuthContextType = {
    user,
    isAuthenticated: !!user,
    isLoading,
    login,
    register,
    logout,
    refreshUser,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
};
