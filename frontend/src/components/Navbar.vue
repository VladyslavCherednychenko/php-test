<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import HealthStatus from './HealthStatus.vue'

import OsDefaultThemeIcon from './icons/IconThemeOsDefault.vue'
import LightThemeIcon from './icons/IconThemeLight.vue'
import DarkThemeIcon from './icons/IconThemeDark.vue'
import LanguagesIcon from './icons/IconLanguages.vue'
import VueLogoIcon from './icons/IconVueLogo.vue'
import SymfonyLogoIcon from './icons/IconSymfonyLogo.vue'

const { t, locale, availableLocales } = useI18n()

const activeMenu = ref<string | null>(null)

const toggleMenu = (menuName: string) => {
  if (activeMenu.value === menuName) {
    activeMenu.value = null
  } else {
    activeMenu.value = menuName
  }
}

const isLangOpen = computed(() => activeMenu.value === 'lang')
const isThemeOpen = computed(() => activeMenu.value === 'theme')

// --- THEME LOGIC ---
const themeSetting = ref(localStorage.getItem('user-theme') || 'auto')

const applyTheme = (theme: string) => {
  let colorTheme = theme

  if (theme === 'auto') {
    colorTheme = globalThis.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  }

  document.documentElement.classList.toggle('dark-theme', colorTheme === 'dark')
}

watch(
  themeSetting,
  (newTheme) => {
    localStorage.setItem('user-theme', newTheme)
    applyTheme(newTheme)
  },
  { immediate: true },
)

globalThis.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
  if (themeSetting.value === 'auto') applyTheme('auto')
})

const setTheme = (theme: 'light' | 'dark' | 'auto') => {
  themeSetting.value = theme
  isThemeOpen.value = false
}

const currentThemeIcon = computed(() => {
  if (themeSetting.value === 'dark') return DarkThemeIcon
  if (themeSetting.value === 'light') return LightThemeIcon
  return OsDefaultThemeIcon
})

// --- LANGUAGE LOGIC ---
watch(locale, (newLang) => {
  localStorage.setItem('user-locale', newLang)
  document.querySelector('html')?.setAttribute('lang', newLang)
})

const selectLanguage = (lang: string) => {
  locale.value = lang
  isLangOpen.value = false
}

// --- API LOGIC ---
const backendData = ref(null)
const error = ref(null)
const isLoading = ref(false)

async function fetchData() {
  isLoading.value = true
  error.value = null

  try {
    const response = await fetch('http://localhost:8080/api/health', {
      headers: { 'Accept-Language': locale.value },
    })
    backendData.value = await response.json()
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Error'
  } finally {
    isLoading.value = false
  }
}

window.addEventListener('click', (e) => {
  if (!(e.target as Element).closest('.custom-select-container')) {
    activeMenu.value = null
  }
})

const healthData = () => {
  backendData.value = null
  error.value = null
}
</script>

<template>
  <header class="page-layout__header">
    <nav class="navigation">
      <div class="navigation__logo">
        <a href="/" class="logo">
          <SymfonyLogoIcon class="logo__image" />
          <span class="logo__text">&</span>
          <VueLogoIcon class="logo__image" />
          <span class="logo__text">{{ t('logoText') }}</span>
        </a>
      </div>
      <div class="navigation__search" data-view="mobile"></div>
      <button
        class="navigation__button"
        type="button"
        aria-expanded="false"
        aria-controls="navigation__popup"
        aria-label="Toggle navigation"
      ></button>
      <div class="navigation__popup">
        <div class="navigation__menu">
          <nav class="menu">
            <div class="menu__tab">
              <RouterLink to="/" class="menu__tab-button">
                <span class="menu__tab-button-text">{{ t('links.home') }}</span>
              </RouterLink>
            </div>
          </nav>
        </div>
        <div class="navigation__search" data-view="desktop"></div>
        <div class="navigation__user-menu"></div>
      </div>
    </nav>

    <div class="utilities-bar">
      <ol class="utilities__external">
        <li>
          <button @click="fetchData" :disabled="isLoading" class="utilities__button">
            {{ isLoading ? t('awaitingResults') : t('actions.healthCheck') }}
          </button>
          <HealthStatus :data="backendData" :error="error" @close="healthData" />
        </li>
      </ol>

      <div class="utilities__internal">
        <button class="utilities__button" @click.stop="toggleMenu('theme')">
          <component :is="currentThemeIcon" class="icon-svg" />
          <span>{{ t('themes.label') }}</span>
        </button>

        <div v-if="isThemeOpen" class="utilities__dropdown">
          <ul class="utilities__list">
            <button class="utilities__option" type="button" @click="setTheme('auto')">
              <OsDefaultThemeIcon class="icon-svg small" />
              <span>{{ t('themes.system') || 'System' }}</span>
            </button>
            <button class="utilities__option" type="button" @click="setTheme('light')">
              <LightThemeIcon class="icon-svg small" />
              <span>{{ t('themes.light') }}</span>
            </button>
            <button class="utilities__option" type="button" @click="setTheme('dark')">
              <DarkThemeIcon class="icon-svg small" />
              <span>{{ t('themes.dark') }}</span>
            </button>
          </ul>
        </div>
      </div>
      <div class="utilities__internal">
        <button class="utilities__button" @click.stop="toggleMenu('lang')">
          <LanguagesIcon class="icon-svg" />
          <span>{{ t('language.name') }}</span>
        </button>
        <div v-if="isLangOpen" class="utilities__dropdown">
          <ul class="utilities__list">
            <button
              v-for="lang in availableLocales"
              :key="lang"
              class="utilities__option"
              :class="{ active: lang === locale }"
              type="button"
              @click="selectLanguage(lang)"
            >
              {{ t('language.name', {}, { locale: lang }) }}
            </button>
          </ul>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.page-layout__header {
  position: sticky;
  top: 0;
  z-index: var(--z-index-sticky-header);
}

header {
  display: block;
  unicode-bidi: isolate;
}

.logo {
  display: flex;
  text-decoration: none;
  align-items: center;
}

.logo__text {
  padding-left: 8px;
  font-size: var(--font-size-medium);
}

.logo .logo__text:first-of-type {
  padding: 0 8px;
  font-size: var(--font-size-medium);
}

@media (width > 1006px) {
  .navigation {
    align-items: center;
    column-gap: 1rem;
    display: grid;
    grid-template-columns: min-content 1fr min-content min-content;
    height: var(--navigation-height);
    justify-items: center;
    padding-block: 0.75rem;
    padding-inline: var(--layout-side-padding);
  }

  .navigation__logo {
    margin-inline-start: -6px;
  }

  .navigation__button,
  .navigation__search[data-view='mobile'] {
    display: none;
  }

  .navigation__search[data-view='desktop'] {
    display: block;
  }

  .navigation__popup {
    display: contents;
  }

  .navigation__user-menu {
    height: 2.125rem;
    width: 2.125rem;
  }

  .menu,
  .menu__tab-button {
    align-items: center;
    display: flex;
  }
}

@media (width <= 1006px) {
  .navigation {
    align-items: center;
    column-gap: 0.25rem;
    display: grid;
    grid-template-columns: 1fr min-content min-content;
    height: var(--navigation-height);
    justify-items: start;
  }

  .navigation__logo {
    padding: 0.2rem;
  }

  .navigation__search[data-view='mobile'] {
    display: block;
  }

  .navigation__search[data-view='desktop'] {
    display: none;
  }

  .navigation__button {
    background-color: initial;
    border: none;
    cursor: pointer;
    margin: 0;
    outline-offset: -2px;
    padding: 0.7rem;
  }

  .navigation__popup {
    display: none;
    grid-column: 1 / -1;
    justify-self: stretch;
  }

  .navigation__user-menu {
    border-top: 1px solid var(--color-border-primary);
    display: block;
  }

  .menu {
    --menu-button-padding: 0.9rem 0.7rem;
  }

  .menu__tab-button {
    align-items: center;
    border: none;
    column-gap: 0.25rem;
    display: flex;
    padding: var(--menu-button-padding);
  }
}

.menu__tab {
  --menu-tab-background: var(--color-background-blue);
  --menu-tab-text: var(--color-text-blue);
}

.menu__tab-button {
  text-decoration: none;
  background-color: initial;
  color: var(--color-text-primary);
  cursor: pointer;
  font: inherit;
  line-height: var(--font-line-ui);
  margin: 0;
  white-space: nowrap;
}

.menu__tab-button-text {
  font-size: var(--font-size-medium);
}

.navigation {
  background-color: var(--color-background-page);
}

.utilities-bar {
  align-items: center;
  background-color: var(--color-background-primary);
  border-width: 1px;
  border-bottom: 1px solid var(--color-border-primary);
  border-left: 0 solid var(--color-border-primary);
  border-right: 0 solid var(--color-border-primary);
  border-top: 1px solid var(--color-border-primary);
  column-gap: 1rem;
  display: flex;
  height: var(--utilities-bar-height);
  justify-content: end;
  max-width: 100vw;
  padding-inline: var(--layout-side-padding);
  text-wrap: nowrap;
}

.utilities__external {
  align-items: center;
  display: flex;
  justify-content: end;
  list-style: none;
  margin: 0 auto 0 0;
  min-width: 0;
  overflow-x: hidden;
  padding: 2px;
}

.utilities__internal {
  position: relative;
  height: 100%;
}

.utilities__button {
  align-items: center;
  background-color: initial;
  border: none;
  color: inherit;
  column-gap: 0.25rem;
  cursor: pointer;
  display: flex;
  font: inherit;
  margin: 0;
  padding: 0 0.5rem;
  height: 100%;
}

.utilities__button:is(:hover, :focus, [aria-expanded='true']) {
  background-color: var(--color-background-secondary);
}

.utilities li {
  display: flex;
  place-items: center;
}

.utilities__dropdown {
  border: 1px solid var(--color-border-primary);
  padding: 0.75rem;
  position: absolute;
  right: 0;
  z-index: 1;
}

.utilities__list {
  list-style: none;
  padding: 0;
}

.utilities__dropdown,
.utilities__list {
  background-color: var(--color-background-primary);
  margin: 0;
  width: max-content;
}

.utilities__option {
  align-items: center;
  background-color: initial;
  border: none;
  color: var(--color-text-primary);
  column-gap: 0.25rem;
  cursor: pointer;
  display: flex;
  font: inherit;
  margin: 0;
  padding: 0.25rem;
  width: 100%;
}
</style>
