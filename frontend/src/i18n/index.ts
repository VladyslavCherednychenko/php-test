import { createI18n } from 'vue-i18n'

type MessageSchema = {
  welcome: string
  buttons: {
    checkStatus: string
  }
}

const messages = {
  en: {
    welcome: 'Symfony + Vue Blog',
    buttons: {
      checkStatus: 'Check connection with API',
      awaitingResults: 'Loading...'
    },
    toggles: {
      changeLanguage: 'Change language'
    },
    healthCheckResponse: {
      header: 'Backend response',
      status: 'Status',
      message: 'Message',
      phpVersion: 'PHP version'
    }
  },
  ru: {
    welcome: 'Блог на Symfony + Vue',
    buttons: {
      checkStatus: 'Проверить связь с API',
      awaitingResults: 'Загрузка...'
    },
    toggles: {
      changeLanguage: 'Сменить язык'
    },
    healthCheckResponse: {
      header: 'Ответ от бэкенда',
      status: 'Статус',
      message: 'Сообщение',
      phpVersion: 'Версия PHP'
    }
  }
}

export const i18n = createI18n({
  legacy: false, // using Composition API
  locale: 'en',
  fallbackLocale: 'ru',
  messages
})
