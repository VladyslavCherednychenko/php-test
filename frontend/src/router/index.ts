import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '@/views/HomeView.vue';
import { useAuthStore } from '@/stores/auth';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('@/views/AboutView.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/AuthView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/AuthView.vue'),
      meta: { guestOnly: true },
    },
    {
      path: '/profile/:username?',
      name: 'profile',
      component: () => import('@/views/ProfileView.vue'),
      meta: { requiresAuth: true },
    },
  ],
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();

  // 1. Protect routes requiring authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login');
  }

  // 2. Prevent logged-in users from accessing login/register
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next('/');
  }

  next();
});

export default router;
