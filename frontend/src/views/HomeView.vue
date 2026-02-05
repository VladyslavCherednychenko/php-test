<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import profileService from '@/api/profile.service';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const route = useRoute();
const results = ref([]);
const isLoading = ref(false);

const performSearch = async (username: string) => {
  if (!username) return;

  isLoading.value = true;
  try {
    const response = await profileService.searchProfile(username);
    results.value = response.data.data.profiles;
  } catch (err) {
    console.error('Search failed', err);
  } finally {
    isLoading.value = false;
  }
};

watch(
  () => route.query.q,
  (newQuery) => {
    performSearch(newQuery as string);
  },
  { immediate: true },
);
</script>

<template>
  <main>
    <div v-if="route.query.q" class="search-results">
      <h2>{{ t('search.results') }} "{{ route.query.q }}"</h2>

      <p v-if="isLoading">{{ t('search.searching') }}</p>

      <ul v-else-if="results.length" class="results-box">
        <li v-for="user in results" :key="user.id" class="user-card-wrapper">
          <RouterLink class="user-card" :to="`/profiles/${user.id}`">
            <img :src="user.profileImage || '/default-avatar.png'" />
            <div class="user-fields">
              <strong>{{ user.username }}</strong>
              <span>{{ user.firstName }} {{ user.lastName }}</span>
            </div>
          </RouterLink>
        </li>
      </ul>

      <p v-else>{{ t('search.not_found') }}</p>
    </div>

    <div v-else>
      <h1>{{ t('home.welcome') }}</h1>
    </div>
  </main>
</template>

<style lang="css" scoped>
.results-box {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 2rem;
}

.user-card-wrapper {
  list-style: none;
  flex: 1 0 calc(33.333% - 1rem);
}

.user-card-wrapper:hover {
  outline: 1px solid var(--color-text-primary);
}

.user-card {
  display: flex;
  align-items: start;
  padding: 0.5rem 0.7rem;
  text-decoration: none;
}

.user-fields {
  display: flex;
  flex-direction: column;
  margin-left: 1rem;
}

.search-results {
  margin-top: 2rem;
}

h2 {
  text-align: center;
}
</style>
