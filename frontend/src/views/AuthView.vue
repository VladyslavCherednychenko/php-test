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

const form = ref<AuthCredentials>({ email: '', password: '', rememberMe: false });
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
    if (isLoginPage.value) {
      error.value = err.response?.data?.errors || t('auth_page.errors.login_failed');
    } else {
      error.value = err.response?.data?.errors || t('auth_page.errors.registration_failed');
    }
  }
}
</script>

<template>
  <div class="auth-form">
    <form @submit.prevent="handleAuth">
      <h2>{{ isLoginPage ? t('auth_page.actions.login') : t('auth_page.actions.register') }}</h2>

      <p v-if="error" class="error">{{ error }}</p>

      <input v-model="form.email" type="email" :placeholder="t('auth_page.form.email')" required />
      <input v-model="form.password" type="password" :placeholder="t('auth_page.form.password')" required />
      <label class="remember-me">
        <input v-model="form.rememberMe" type="checkbox" />
        <span>{{ t('auth_page.form.rememberMe') }}</span>
      </label>

      <button type="submit" class="btn-outline">
        {{ isLoginPage ? t('auth_page.actions.login') : t('auth_page.actions.register') }}
      </button>

      <p class="auth-form__footer" @click="toggleAuthMode" style="cursor: pointer">
        {{ isLoginPage ? t('auth_page.toggle.no_account') : t('auth_page.toggle.already_have_an_account') }}
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

.remember-me span {
  margin-left: 1rem;
}
</style>
