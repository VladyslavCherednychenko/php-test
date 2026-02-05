import type { AuthCredentials } from '@/types/auth';
import apiClient from './axios';

export default {
  login(credentials: AuthCredentials) {
    return apiClient.post('/auth/login', credentials);
  },
  register(credentials: AuthCredentials) {
    return apiClient.post('/auth/register', credentials);
  },
};
