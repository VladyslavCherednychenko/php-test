<script setup lang="ts">
import { RouterView } from 'vue-router'
import { useI18n } from 'vue-i18n'
import Navbar from './components/Navbar.vue'

const { t } = useI18n()

</script>

<template>
  <Navbar/>

  <div class="main-content">
    <h1>{{ t('welcome') }}</h1>
    <RouterView />
  </div>
</template>
