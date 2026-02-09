<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import profileService from '@/api/profile.service';

const { t } = useI18n();
const authStore = useAuthStore();

const isEditing = ref(false);
const isLoading = ref(false);
const error = ref('');

const profile = ref({
  id: null,
  username: '',
  firstName: '',
  lastName: '',
  profileImage: '',
  bio: '',
});

onMounted(async () => {
  try {
    isLoading.value = true;
    const userId = authStore.userId;

    // if store is corrupted - terminate session
    if (!userId) {
      authStore.logout();
      return;
    }

    const response = await profileService.getProfileByUserId(userId);
    profile.value = response.data.data.profile;
    form.username = profile.value.username;
    form.firstName = profile.value.firstName;
    form.lastName = profile.value.lastName;
    form.bio = profile.value.bio;
  } catch (err: any) {
    error.value = err.response?.data?.message || t('errors.profile.load_attempt_failed');
  } finally {
    isLoading.value = false;
  }
});

// Reactive form state
const form = reactive({
  username: profile.value.username,
  firstName: profile.value.firstName,
  lastName: profile.value.lastName,
  bio: profile.value.bio,
});

// --- Handle Text Data ---
async function handleUpdate() {
  isLoading.value = true;
  error.value = '';
  try {
    const response = await profileService.createOrUpdateProfile(form);
    profile.value = response.data.data.profile;
    isEditing.value = false;
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('errors.profile_info.update_failed');
  } finally {
    isLoading.value = false;
  }
}

// --- Handle Profile Picture ---
async function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (!target.files?.length) return;

  const formData = new FormData();
  formData.append('file', target.files[0]);

  try {
    isLoading.value = true;
    const response = await profileService.changeProfilePicture(formData);
    profile.value = response.data.data.profile;
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('errors.profile_image.upload_failed');
  } finally {
    isLoading.value = false;
  }
}

async function deleteProfilePicture() {
  try {
    isLoading.value = true;
    const response = await profileService.deleteProfilePicture();
    profile.value = response.data.data.profile;
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('errors.profile_image.deletion_failed');
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div class="profile-page" v-if="authStore.userId">
    <p v-if="error" class="error">{{ error }}</p>
    <div class="profile-box">
      <div class="avatar-container">
        <div class="avatar-wrapper">
          <img :src="profile.profileImage" class="avatar" />
        </div>
        <div class="actions">
          <input type="file" @change="onFileChange" accept="image/*" id="file-input" hidden />
          <label for="file-input" class="btn-outline">{{ t('actions.change_photo') }}</label>
          <button class="btn-outline" type="button" @click="deleteProfilePicture">
            {{ t('actions.delete_profile_picture') }}
          </button>
        </div>
      </div>
      <div v-if="isEditing" class="profile-info">
        <form @submit.prevent="handleUpdate" class="profile-info">
          <label>
            {{ t('profile.username') }}
            <input v-model="form.username" :placeholder="t('profile.username')" required />
          </label>
          <label>
            {{ t('profile.firstname') }}
            <input v-model="form.firstName" :placeholder="t('profile.firstname')" />
          </label>
          <label>
            {{ t('profile.lastname') }}
            <input v-model="form.lastName" :placeholder="t('profile.lastname')" />
          </label>
          <label>
            {{ t('profile.bio') }}
            <textarea v-model="form.bio" :placeholder="t('profile.bio')"></textarea>
          </label>

          <div class="actions">
            <button class="btn-outline" type="submit" :disabled="isLoading">{{ t('actions.save') }}</button>
            <button class="btn-outline" type="button" @click="isEditing = false">{{ t('actions.cancel') }}</button>
          </div>
        </form>
      </div>

      <div v-else class="profile-info">
        <label>
          {{ t('profile.username') }}
          <input readonly v-model="form.username" :placeholder="t('profile.username')" required />
        </label>
        <label>
          {{ t('profile.firstname') }}
          <input readonly v-model="form.firstName" :placeholder="t('profile.firstname')" />
        </label>
        <label>
          {{ t('profile.lastname') }}
          <input readonly v-model="form.lastName" :placeholder="t('profile.lastname')" />
        </label>
        <label>
          {{ t('profile.bio') }}
          <textarea readonly v-model="form.bio" :placeholder="t('profile.bio')"></textarea>
        </label>
        <button class="btn-outline" @click="isEditing = true">{{ t('actions.edit_profile') }}</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-box {
  display: flex;
  gap: 2rem;
  justify-content: center;
  width: 100%;
}

.avatar {
  width: 240px;
  height: 240px;
  border-radius: 50%;
  object-fit: cover;
  outline: 1px solid var(--color-border-secondary);
}
.profile-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 300px;
}
.error {
  color: var(--color-text-danger);
  margin-top: 10px;
}

.profile-page {
  min-height: calc(100dvh - (var(--navigation-height) + var(--utilities-bar-height)));
  display: flex;
  align-items: center;
  justify-content: center;
  padding-bottom: 5rem;
  gap: 2rem;
  flex-direction: column;
}

.avatar-container,
.profile-info {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 0.75rem;
  width: 33dvw;
}

.avatar-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-item {
  display: flex;
}

button {
  border: none;
}

.profile-info > label > input,
.profile-info > label > textarea {
  background-color: var(--color-background-secondary);
  outline: 1px solid var(--color-text-secondary);
  color: var(--color-text-primary);
  padding: 0.3rem;
}

.profile-info > label {
  display: flex;
  flex-direction: column;
  font-weight: var(--font-weight-bold);
}

.avatar-container > .btn-outline {
  align-self: center;
}

.actions {
  display: flex;
  gap: 1rem;
}

.actions > .btn-outline {
  width: 100%;
}

@media (width <= 768px) {
  .profile-box {
    flex-direction: column;
    align-content: center;
    margin-top: 2rem;
  }

  .avatar-container,
  .profile-info {
    width: 100%;
  }
}
</style>
