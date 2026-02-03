import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import authService from '@/api/auth.service';
import router from '@/router';
import type { User } from '@/types/auth';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(JSON.parse(localStorage.getItem('user') || 'null'));
  const token = ref<string>(localStorage.getItem('token') || '');

  const isAuthenticated = computed(() => !!token.value);

  async function login(credentials) {
    try {
      const response = await authService.login(credentials);
      const { access_token, user: userData } = response.data.data;

      token.value = access_token;
      user.value = userData;

      localStorage.setItem('token', access_token);
      localStorage.setItem('user', JSON.stringify(userData));

      localStorage.setItem('token', token.value);
      router.push('/');
    } catch (error) {
      console.error('Login failed', error);
      throw error;
    }
  }

  async function register(credentials) {
    try {
      const response = await authService.register(credentials);
      const { access_token, user: userData } = response.data.data;

      token.value = access_token;
      user.value = userData;

      localStorage.setItem('token', access_token);
      localStorage.setItem('user', JSON.stringify(userData));

      localStorage.setItem('token', token.value);
      router.push('/');
    } catch (error) {
      console.error('Registration failed', error);
      throw error;
    }
  }

  function logout() {
    token.value = '';
    user.value = null;
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    router.push('/login');
  }

  return { user, token, isAuthenticated, login, register, logout };
});
