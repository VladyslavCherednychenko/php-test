<script setup lang="ts">
import { RouterView } from 'vue-router';
import NavBar from '@/components/NavBar.vue';
import LoadingOverlay from '@/components/LoadingOverlay.vue';
</script>

<template>
  <NavBar />

  <div class="main-content">
    <LoadingOverlay />
    <RouterView />
  </div>
</template>
