import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import de from './locales/de.json';
import ua from './locales/ua.json';
import ru from './locales/ru.json';

const messages = { en, de, ua, ru };
type MessageSchema = typeof messages;
type LocaleKey = keyof MessageSchema;

export const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('user-locale') || 'en',
  fallbackLocale: 'en',
  messages: messages,
});

export function setLanguage(newLocale: LocaleKey) {
  i18n.global.locale.value = newLocale;
  localStorage.setItem('user-locale', newLocale);
  document.querySelector('html')?.setAttribute('lang', newLocale);
}
