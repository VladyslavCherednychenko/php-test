<script setup lang="ts">
import { useLoadingStore } from '@/stores/loading';
const loadingStore = useLoadingStore();
</script>

<template>
  <div v-if="loadingStore.isLoading" class="overlay">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: var(--color-background-alpha-75);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.overlay p {
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-medium);
  margin-top: 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
