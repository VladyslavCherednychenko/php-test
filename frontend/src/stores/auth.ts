import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import authService from '@/api/auth.service';
import router from '@/router';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('token') || '');

  const isAuthenticated = computed(() => !!token.value);

  async function login(credentials) {
    const response = await authService.login(credentials);
    token.value = response.data.access_token;
    user.value = response.data.user;
    localStorage.setItem('token', token.value);
    router.push('/');
  }

  function logout() {
    token.value = '';
    user.value = null;
    localStorage.removeItem('token');
    router.push('/login');
  }

  async function fetchUser() {
    if (token.value) {
      try {
        const response = await authService.getCurrentUser();
        user.value = response.data;
      } catch {
        logout();
      }
    }
  }

  return { user, token, isAuthenticated, login, logout, fetchUser };
});
