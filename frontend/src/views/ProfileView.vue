<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import profileService from '@/api/profile.service';

const { t } = useI18n();
const authStore = useAuthStore();

const isEditing = ref(false);
const isLoading = ref(false);
const error = ref('');

// Reactive form state
const form = reactive({
  username: authStore.user?.profile?.username || '',
  firstName: authStore.user?.profile?.firstName || '',
  lastName: authStore.user?.profile?.lastName || '',
  bio: authStore.user?.profile?.bio || ''
});

// --- Handle Text Data ---
async function handleUpdate() {
  isLoading.value = true;
  error.value = '';
  try {
    const response = await profileService.createOrUpdateProfile(form);
    authStore.user.profile = response.data.data.profile;
    isEditing.value = false;
  } catch (err: any) {
    error.value = err.response?.data?.errors || 'Update failed';
  } finally {
    isLoading.value = false;
  }
}

// --- Handle Profile Picture ---
async function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (!target.files?.length) return;

  const formData = new FormData();
  formData.append('picture', target.files[0]);

  try {
    isLoading.value = true;
    const response = await profileService.changeProfilePicture(formData);
    authStore.user.profile.profileImage = response.data.data.profile.profileImage;
  } catch (err: any) {
    error.value = t('errors.upload_failed');
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div class="profile-page">
    <div v-if="authStore.user">

      <div class="profile-header">
        <div class="avatar-container">
          <img :src="authStore.user.profile?.profileImage || '/default-avatar.png'" class="avatar" />
          <input type="file" @change="onFileChange" accept="image/*" id="file-input" hidden />
          <label for="file-input" class="upload-label">{{ t('actions.change_photo') }}</label>
        </div>
      </div>

      <div v-if="isEditing">
        <form @submit.prevent="handleUpdate" class="profile-form">
          <input v-model="form.username" :placeholder="t('profile.username')" required />
          <input v-model="form.firstName" :placeholder="t('profile.firstName')" />
          <input v-model="form.lastName" :placeholder="t('profile.lastName')" />
          <textarea v-model="form.bio" :placeholder="t('profile.bio')"></textarea>

          <div class="actions">
            <button type="submit" :disabled="isLoading">{{ t('actions.save') }}</button>
            <button type="button" @click="isEditing = false">{{ t('actions.cancel') }}</button>
          </div>
        </form>
      </div>

      <div v-else class="profile-info">
        <h2>{{ authStore.user.profile?.username || t('profile.no_username') }}</h2>
        <p>{{ authStore.user.profile?.firstName }} {{ authStore.user.profile?.lastName }}</p>
        <p class="bio">{{ authStore.user.profile?.bio }}</p>
        <button @click="isEditing = true">{{ t('actions.edit_profile') }}</button>
      </div>

      <p v-if="error" class="error">{{ error }}</p>
    </div>
  </div>
</template>

<style scoped>
.avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
.profile-form { display: flex; flex-direction: column; gap: 10px; max-width: 300px; }
.error { color: var(--color-text-danger); margin-top: 10px; }
.upload-label { cursor: pointer; color: var(--color-text-blue); font-size: 0.8rem; }
</style>
