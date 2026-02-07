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
