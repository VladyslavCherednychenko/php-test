export interface UserProfile {
  id: number;
  username: string;
  firstName: string;
  lastName: string;
  profileImage: string | null;
  bio: string;
}

export interface User {
  id: number;
  email: string;
  role: number;
  profile: UserProfile | null;
}

export interface AuthResponse {
  status: string;
  message: string;
  data: {
    access_token: string;
    user: User;
  };
}

export interface AuthCredentials {
  email: string;
  password: string;
}

export interface AuthState {
  user: User | null;
  token: string;
}
