<script setup lang="ts">
import { ref } from 'vue'
import { RouterView } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Navbar from './components/Navbar.vue' // Импортируем наш новый компонент

const { t } = useI18n()

// Состояния, которые Navbar будет "поднимать" вверх (через emit)
const backendData = ref(null)
const isLoading = ref(false)
const error = ref(null)
</script>

<template>
  <Navbar
    @update:healthData="d => backendData = d"
    @update:loading="l => isLoading = l"
    @update:error="e => error = e"
  />

  <main class="main-content">
    <div class="content-padding">
      <h1>{{ t('welcome') }}</h1>

      <div v-if="error" class="error-msg">Error: {{ error }}</div>

      <div v-if="backendData" class="response-card">
        <h3>{{ t('healthCheckResponse.header') }}:</h3>
        <p><strong>{{ t('healthCheckResponse.status') }}:</strong> {{ backendData.status }}</p>
        <p><strong>{{ t('healthCheckResponse.message') }}:</strong> {{ backendData.message }}</p>
        <p><strong>{{ t('healthCheckResponse.phpVersion') }}:</strong> {{ backendData.php_version }}</p>
      </div>
    </div>
  </main>
  <RouterView />
</template>
