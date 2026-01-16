<script setup lang="ts">
import { useI18n } from 'vue-i18n'

interface HealthData {
  status: string
  message: string
  php_version: string
  db_available: boolean
}

defineProps<{
  data: HealthData | null
  error: string | null
}>()

const emit = defineEmits(['close'])
const { t } = useI18n()
</script>

<template>
  <Transition name="slide-fade">
    <div v-if="data || error" class="health-popup">
      <button class="close-btn" @click="emit('close')">×</button>

      <div v-if="error" class="error-content"><strong>Error:</strong> {{ error }}</div>

      <div v-if="data" class="success-content">
        <h4>{{ t('healthCheckResponse.header') }}</h4>
        <div class="stats">
          <p>
            <span>{{ t('healthCheckResponse.status') }}:</span> {{ data.status }}
          </p>
          <p>
            <span>{{ t('healthCheckResponse.message') }}:</span> {{ data.message }}
          </p>
          <p>
            <span>{{ t('healthCheckResponse.phpVersion') }}:</span> {{ data.php_version }}
          </p>
          <p>
            <span>{{ t('healthCheckResponse.dbStatus') }}:</span> {{ data.db_available }}
          </p>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.health-popup {
  position: absolute;
  top: calc(var(--sticky-header-height) + 4px);
  width: fit-content;
  z-index: -1;

  background-color: var(--color-background-primary);
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);

  border: 1px solid var(--color-border-primary);
  border-radius: var(--radius-normal);
  padding: 1.2rem;
}

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-100%);
  opacity: 0;
}

.close-btn {
  position: absolute;
  top: 8px;
  right: 10px;
  cursor: pointer;
  border: none;
  background: none;
  font-size: 1.2rem;
  color: var(--color-text-primary);
}
.success-content h4 {
  margin: 0 0 10px 0;
  font-size: var(--font-size-normal);
  color: var(--color-text-primary);
}
.stats p {
  font-size: var(--font-size-small);
  color: var(--color-text-primary);
  margin: 5px 0;
}
</style>
