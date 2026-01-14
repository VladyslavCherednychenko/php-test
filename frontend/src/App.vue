<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const toggleLanguage = () => {
  locale.value = locale.value === 'en' ? 'ru' : 'en'
}

interface HealthResponse {
  status: string;
  message: string;
  php_version: string;
}

const backendData = ref<HealthResponse | null>(null)
const isLoading = ref<boolean>(false)
const error = ref<string | null>(null)

async function fetchData(): Promise<void> {
  isLoading.value = true
  error.value = null

  try {
    const response = await fetch('http://localhost:8080/api/health', {
      headers: {
        'Accept-Language': locale.value
      }
    })

    if (!response.ok) {
      throw new Error(`Server error: ${response.status}`)
    }

    const data: HealthResponse = await response.json()
    backendData.value = data
  } catch (err) {
    if (err instanceof Error) {
      error.value = err.message
    } else {
      error.value = "An unknown error occurred"
    }
    console.error("Error during request:", err)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <header>
    <img alt="Vue logo" class="logo" src="@/assets/logo.svg" width="125" height="125" />

    <div class="wrapper">
      <HelloWorld msg="You did it!" />

      <nav>
        <RouterLink to="/">Home</RouterLink>
        <RouterLink to="/about">About</RouterLink>
      </nav>
    </div>
  </header>

  <RouterView />

  <div style="padding: 20px; font-family: sans-serif;">
    <h1>{{ t('welcome') }}</h1>
    <button @click="toggleLanguage">{{t('toggles.changeLanguage')}} ({{ locale }})</button>
    <button @click="fetchData" :disabled="isLoading">
      {{ isLoading ? t('buttons.awaitingResults') : t('buttons.checkStatus') }}
    </button>

    <div v-if="error" style="color: red; margin-top: 10px;">
      Error: {{ error }}
    </div>

    <div v-if="backendData" style="margin-top: 20px; border: 1px solid #ccc; padding: 10px;">
      <h3>{{ t('healthCheckResponse.header') }}:</h3>
      <p><strong>{{ t('healthCheckResponse.status') }}:</strong> {{ backendData.status }}</p>
      <p><strong>{{ t('healthCheckResponse.message') }}:</strong> {{ backendData.message }}</p>
      <p><strong>{{ t('healthCheckResponse.phpVersion') }}:</strong> {{ backendData.php_version }}</p>
    </div>
  </div>
</template>

<style scoped>
header {
  line-height: 1.5;
  max-height: 100vh;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

nav {
  width: 100%;
  font-size: 12px;
  text-align: center;
  margin-top: 2rem;
}

nav a.router-link-exact-active {
  color: var(--color-text);
}

nav a.router-link-exact-active:hover {
  background-color: transparent;
}

nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
}

nav a:first-of-type {
  border: 0;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }

  nav {
    text-align: left;
    margin-left: -1rem;
    font-size: 1rem;

    padding: 1rem 0;
    margin-top: 1rem;
  }
}
</style>
