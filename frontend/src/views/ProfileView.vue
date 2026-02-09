<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import { useUserStore } from '@/stores/user';

const { t } = useI18n();
const userStore = useUserStore();

const isEditing = ref(false);
const isLoading = ref(false);
const error = ref('');

// Reactive form state
const form = reactive({
  username: userStore.user?.profile?.username || '',
  firstName: userStore.user?.profile?.firstName || '',
  lastName: userStore.user?.profile?.lastName || '',
  bio: userStore.user?.profile?.bio || '',
});

// --- Handle Text Data ---
async function handleUpdate() {
  isLoading.value = true;
  error.value = '';
  try {
    await userStore.updateProfileContent(form);
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
  formData.append('file', target.files[0]);

  try {
    isLoading.value = true;
    await userStore.updateProfileImage(formData);
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('errors.profile_image.upload_failed');
  } finally {
    isLoading.value = false;
  }
}

async function deleteProfilePicture() {
  try {
    isLoading.value = true;
    await userStore.deleteProfileImage();
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('errors.profile_image.deletion_failed');
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div class="profile-page" v-if="userStore.user">
    <div class="profile-box">
      <div class="avatar-container">
        <div class="avatar-wrapper">
          <img :src="userStore.user.profile?.profileImage" class="avatar" />
        </div>
        <div class="actions">
          <input type="file" @change="onFileChange" accept="image/*" id="file-input" hidden />
          <label for="file-input" class="btn-outline">{{ t('actions.change_photo') }}</label>
          <!-- BUTTON TO DELETE PFP -->
          <button class="btn-outline" type="button" @click="deleteProfilePicture">
            {{ t('actions.delete_profile_picture') }}
          </button>
        </div>
      </div>
      <p v-if="error" class="error">{{ error }}</p>
      <div v-if="isEditing" class="profile-info">
        <form @submit.prevent="handleUpdate" class="profile-info">
          <label>
            {{ t('profile.username') }}
            <input v-model="form.username" :placeholder="t('profile.username')" required />
          </label>
          <label>
            {{ t('profile.firstname') }}
            <input v-model="form.firstName" :placeholder="t('profile.firstName')" />
          </label>
          <label>
            {{ t('profile.lastname') }}
            <input v-model="form.lastName" :placeholder="t('profile.lastName')" />
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
          <input readonly v-model="form.firstName" :placeholder="t('profile.firstName')" />
        </label>
        <label>
          {{ t('profile.lastname') }}
          <input readonly v-model="form.lastName" :placeholder="t('profile.lastName')" />
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
