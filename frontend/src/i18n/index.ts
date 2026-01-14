import { createI18n } from 'vue-i18n'
import en from './locales/en.json'
import ru from './locales/ru.json'
import de from './locales/de.json'

const savedLocale = localStorage.getItem('user-locale') || 'en'

export const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: 'en',
  messages: { en, ru, de }
})

export function setLanguage(newLocale: string) {
  i18n.global.locale.value = newLocale
  localStorage.setItem('user-locale', newLocale)
  document.querySelector('html')?.setAttribute('lang', newLocale)
}
