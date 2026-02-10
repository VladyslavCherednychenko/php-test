import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const BASER_URL = import.meta.env.API_URL || 'http://localhost:8080/api';

const apiClient = axios.create({
  baseURL: BASER_URL,
  headers: {
    'Content-Type': 'application/json',
  },
  withCredentials: true,
});

// Request Interceptor: Attach Token
apiClient.interceptors.request.use((config) => {
  const authStore = useAuthStore();
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }
  return config;
});

// Response Interceptor: Handle Expired Tokens
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    const authStore = useAuthStore();
    const originalRequest = error.config;

    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        const response = await axios.post(
          `${BASER_URL}/auth/token/refresh`,
          {},
          {
            withCredentials: true,
          },
        );

        const { access_token, user } = response.data.data;

        authStore.token = access_token;
        localStorage.setItem('token', access_token);

        authStore.userId = user.id;
        localStorage.setItem('userId', user.id);

        originalRequest.headers.Authorization = `Bearer ${access_token}`;
        return apiClient(originalRequest);
      } catch (refreshError) {
        authStore.logout();
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  },
);

export default apiClient;
