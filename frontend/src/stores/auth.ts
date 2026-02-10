import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import authService from '@/api/auth.service';
import router from '@/router';
import type { AuthCredentials } from '@/types/auth';

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string>(localStorage.getItem('token') || '');
  const userId = ref<number | null>(Number.parseInt(localStorage.getItem('userId') || '') || null);

  const isAuthenticated = computed(() => !!token.value);

  async function init() {
    if (!localStorage.getItem('token')) {
      return;
    }

    try {
      const { data } = await authService.getAuthenticatedUserCredentials();
      const { user } = data.data;

      userId.value = user.id;
      localStorage.setItem('userId', user.id);
    } catch (error) {
      logout();
      console.error('Login failed', error);
      throw error;
    }
  }

  async function login(credentials: AuthCredentials) {
    try {
      const { data } = await authService.login(credentials);
      const { access_token, user } = data.data;

      token.value = access_token;
      localStorage.setItem('token', access_token);

      userId.value = user.id;
      localStorage.setItem('userId', user.id);

      router.push('/');
    } catch (error) {
      console.error('Login failed', error);
      throw error;
    }
  }

  async function register(credentials: AuthCredentials) {
    try {
      const { data } = await authService.register(credentials);
      const { access_token, user } = data.data;

      token.value = access_token;
      localStorage.setItem('token', access_token);

      userId.value = user.id;
      localStorage.setItem('userId', user.id);

      router.push('/');
    } catch (error) {
      console.error('Registration failed', error);
      throw error;
    }
  }

  async function logout() {
    await authService.terminateCurrentSession();
    clearStorage();
  }

  async function clearStorage() {
    token.value = '';
    userId.value = null;

    localStorage.removeItem('token');
    localStorage.removeItem('userId');

    await router.push('/login');
  }

  return { token, userId, isAuthenticated, init, login, register, logout, clearStorage };
});
