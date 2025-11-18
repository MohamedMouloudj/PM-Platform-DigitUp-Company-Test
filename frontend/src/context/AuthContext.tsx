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
      const response = await AuthService.getCurrentUser();
      if (response.data.success) {
        setUser(response.data.data.user);
      } else {
        setUser(null);
      }
    } catch (error: unknown) {
      // 401 is expected when there's no active session - not an error
      const axiosError = error as AxiosError;
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
    const response = await AuthService.login(data);
    if (response.data.success) {
      setUser(response.data.data.user);
    } else {
      throw new Error(response.data.message);
    }
  };

  const register = async (data: RegisterRequest) => {
    const response = await AuthService.register(data);
    if (response.data.success) {
      setUser(response.data.data.user);
    } else {
      throw new Error(response.data.message);
    }
  };

  const logout = async () => {
    try {
      await AuthService.logout();
    } catch (error) {
      // Continue with logout even if API call fails
      console.error("Logout error:", error);
    } finally {
      setUser(null);
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
