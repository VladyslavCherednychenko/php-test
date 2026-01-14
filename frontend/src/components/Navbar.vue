<script setup lang="ts">
import { ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'

// Props for passing API results back to App.vue
const emit = defineEmits(['update:healthData', 'update:loading', 'update:error'])

const { t, locale, availableLocales } = useI18n()

const isLangOpen = ref(false)
const isThemeOpen = ref(false)

// --- THEME LOGIC ---
const isDark = ref(localStorage.getItem('user-theme') === 'dark')
const toggleTheme = () => { isDark.value = !isDark.value }

watch(isDark, (val) => {
  const theme = val ? 'dark' : 'light'
  localStorage.setItem('user-theme', theme)
  document.documentElement.classList.toggle('dark-theme', val)
}, { immediate: true })

const setTheme = (dark: boolean) => {
  isDark.value = dark
  isThemeOpen.value = false
}

// --- LANGUAGE LOGIC ---
const locales = ['en', 'ru', 'de']
const toggleLanguage = () => {
  const currentIndex = locales.indexOf(locale.value)
  locale.value = locales[(currentIndex + 1) % locales.length]
}

watch(locale, (newLang) => {
  localStorage.setItem('user-locale', newLang)
  document.querySelector('html')?.setAttribute('lang', newLang)
})

const selectLanguage = (lang: string) => {
  locale.value = lang
  isLangOpen.value = false
}

// --- API LOGIC ---
const isLoading = ref(false)
async function fetchData() {
  isLoading.value = true
  emit('update:loading', true)
  emit('update:error', null)

  try {
    const response = await fetch('http://localhost:8080/api/health', {
      headers: { 'Accept-Language': locale.value }
    })
    if (!response.ok) throw new Error(`Server error: ${response.status}`)
    const data = await response.json()
    emit('update:healthData', data)
  } catch (err) {
    emit('update:error', err instanceof Error ? err.message : "Error")
  } finally {
    isLoading.value = false
    emit('update:loading', false)
  }
}

window.addEventListener('click', (e) => {
  if (!(e.target as Element).closest('.custom-select-container')) {
    isLangOpen.value = false
    isThemeOpen.value = false
  }
})
</script>

<template>
  <header class="navbar">
    <RouterLink to="/">{{ t('links.home') }}</RouterLink>
    <div class="custom-select-container">
      <button class="select-trigger" @click="isLangOpen = !isLangOpen">
        {{`${t('language.label')}: ${locale.toUpperCase()}`}}
      </button>

      <div v-if="isLangOpen" class="select-dropdown">
        <div
          v-for="lang in availableLocales"
          :key="lang"
          class="select-option"
          @click="selectLanguage(lang)"
        >
          {{ `${t('language.name', {}, { locale: lang })} (${t('language.code', {}, { locale: lang })})` }}
        </div>
      </div>
    </div>

    <div class="custom-select-container">
      <button class="select-trigger" @click="isThemeOpen = !isThemeOpen">
        {{ isDark ? `${t('themes.label')}: ${t('themes.dark')}` : `${t('themes.label')}: ${t('themes.light')}` }}
      </button>

        <div v-if="isThemeOpen" class="select-dropdown">
          <div class="select-option" @click="setTheme(false)">{{ t('themes.light') }}</div>
          <div class="select-option" @click="setTheme(true)">{{ t('themes.dark') }}</div>
        </div>
    </div>
    <a href="#" @click="fetchData" :disabled="isLoading">
      {{ isLoading ? t('awaitingResults') : t('actions.healthCheck') }}
    </a>
  </header>
</template>

<style scoped>
header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 1000;

  background-color: var(--color-background-soft);
  border-bottom: 1px solid var(--color-border);

  transition: background-color 0.5s, border-color 0.5s;
}

.navbar {
  display: flex;
  gap: 20px;
  padding: 0 20px;
  height: 60px;
  align-items: center;
  max-width: 100%;
  margin: 0 auto;
}

.navbar a {
  text-decoration: none;
  color: var(--color-text);
  font-weight: 500;
  transition: color 0.3s;
}

.navbar a:hover {
  color: var(--color-heading);
}

.custom-select-container {
  position: relative;
  display: inline-block;
}

.select-trigger {
  background: var(--color-background-mute);
  color: var(--color-text);
  border: 1px solid var(--color-border);
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  min-width: 100px;
}

.select-dropdown {
  position: absolute;
  top: 110%;
  left: 0;
  background: var(--color-background-soft);
  border: 1px solid var(--color-border);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  z-index: 1001;
  width: 100%;

  max-height: 150px;
  overflow-y: auto;

  scrollbar-width: thin;
  scrollbar-color: var(--color-scrollbar) transparent;
}

.select-dropdown::-webkit-scrollbar {
  width: 6px;
}
.select-dropdown::-webkit-scrollbar-thumb {
  background-color: var(--color-border);
  border-radius: 10px;
}

.select-option {
  padding: 10px;
  cursor: pointer;
  transition: background 0.2s;
}

.select-option:hover {
  background: var(--color-background-mute);
}
</style>
