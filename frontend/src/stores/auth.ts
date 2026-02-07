import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import authService from '@/api/auth.service';
import router from '@/router';
import { useUserStore } from '@/stores/user';
import type { AuthCredentials } from '@/types/auth';

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string>(localStorage.getItem('token') || '');

  const isAuthenticated = computed(() => !!token.value);

  async function login(credentials: AuthCredentials) {
    try {
      const { data } = await authService.login(credentials);
      const { access_token, user } = data.data;

      token.value = access_token;
      localStorage.setItem('token', access_token);

      const userStore = useUserStore();
      userStore.setUser(user);

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

      const userStore = useUserStore();
      userStore.setUser(user);

      router.push('/');
    } catch (error) {
      console.error('Registration failed', error);
      throw error;
    }
  }

  function logout() {
    token.value = '';
    localStorage.removeItem('token');

    const userStore = useUserStore();
    userStore.clearUser();

    router.push('/login');
  }

  return { token, isAuthenticated, login, register, logout };
});
