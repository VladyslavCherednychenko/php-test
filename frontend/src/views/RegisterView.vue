<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import type { AuthCredentials } from '@/types/auth';

const { t } = useI18n()

const router = useRouter();
const authStore = useAuthStore();
const form = ref<AuthCredentials>({ email: '', password: '' });
const error = ref('');

async function handleSubmit() {
  try {
    await authStore.register(form.value);
    router.push('/');
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('auth.registration_failed');
  }
}
</script>

<template>
  <div class="auth-form">
    <form @submit.prevent="handleSubmit">
      <p v-if="error" class="error">{{ error }}</p>
      <input v-model="form.email" type="text" :placeholder="t('auth.email')" required />
      <input v-model="form.password" type="password" :placeholder="t('auth.password')" required />
      <button type="submit">{{ t('actions.register') }}</button>

      <p class="auth-form__footer">
        {{ t('auth.already_have_an_account') }}
        <RouterLink to="/login">{{ t('actions.login') }}</RouterLink>
      </p>
    </form>
  </div>
</template>

<style scoped>
.auth-form > form {
  display: flex;
  flex-direction: column;
}

.auth-form__footer {
  margin-top: 1rem;
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
