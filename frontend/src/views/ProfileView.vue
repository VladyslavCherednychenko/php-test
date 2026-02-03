<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth';

const { t } = useI18n()

const authStore = useAuthStore();
</script>

<template>
  <div v-if="authStore.user?.profile">
    <h2>{{ t('profile.welcome') }} {{ authStore.user.profile.username }}</h2>

    <p>{{ authStore.user.profile.firstName }} {{ authStore.user.profile.lastName }}</p>

    <blockquote>{{ authStore.user.profile.bio }}</blockquote>
  </div>

  <div v-else-if="authStore.user">
    <p>{{ t('profile.user') }} {{ authStore.user.email }}</p>
  </div>
</template>
