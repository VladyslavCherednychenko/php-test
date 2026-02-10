<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import { useRouter, useRoute } from 'vue-router';
import type { AuthCredentials } from '@/types/auth';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref<AuthCredentials>({ email: '', password: '' });
const error = ref('');

const isLoginPage = computed(() => route.path.endsWith('login'));

const toggleAuthMode = () => {
  const target = isLoginPage.value ? '/register' : '/login';
  error.value = '';
  router.push(target);
};

async function handleAuth() {
  try {
    if (isLoginPage.value) {
      await authStore.login(form.value);
    } else {
      await authStore.register(form.value);
    }
    router.push('/');
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('auth.error');
  }
}
</script>

<template>
  <div class="auth-form">
    <form @submit.prevent="handleAuth">
      <h2>{{ isLoginPage ? t('actions.login') : t('actions.register') }}</h2>

      <p v-if="error" class="error">{{ error }}</p>

      <input v-model="form.email" type="email" :placeholder="t('auth.email')" required />
      <input v-model="form.password" type="password" :placeholder="t('auth.password')" required />

      <button type="submit" class="btn-outline">
        {{ isLoginPage ? t('actions.login') : t('actions.register') }}
      </button>

      <p class="auth-form__footer" @click="toggleAuthMode" style="cursor: pointer">
        {{ isLoginPage ? t('auth.no_account') : t('auth.already_have_an_account') }}
      </p>
    </form>
  </div>
</template>

<style scoped>
.auth-form {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100dvw;
  height: calc(100dvh - (var(--navigation-height) + var(--utilities-bar-height)));
  padding-bottom: 5rem;
}

.auth-form > form {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: var(--color-background-primary);
  padding: 3em;
  border-radius: var(--radius-normal);
  outline: 1px solid var(--color-text-secondary);
  gap: 1rem;
}

.auth-form__footer {
  text-align: center;
  font-size: var(--font-size-small);
}

.auth-form__footer a {
  color: var(--color-text-link);
  text-decoration: underline;
}

.error {
  color: var(--color-text-danger);
  margin-bottom: 1rem;
}
</style>
