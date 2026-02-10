<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '@/stores/auth';
import profileService from '@/api/profile.service';
import { useLoadingStore } from '@/stores/loading';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const error = ref('');
const isEditing = ref(false);
const loadingStore = useLoadingStore();
const isOwnProfile = ref(false);

const profile = ref({
  id: null,
  username: '',
  firstName: '',
  lastName: '',
  profileImage: '',
  bio: '',
  userId: null,
});

const form = reactive({
  username: profile.value.username,
  firstName: profile.value.firstName,
  lastName: profile.value.lastName,
  bio: profile.value.bio,
});

onMounted(async () => {
  const username = route.params.username as string | undefined;

  try {
    loadingStore.show();
    const authenticatedUserId = authStore.userId;

    // if store is corrupted - terminate session
    if (!authenticatedUserId) {
      authStore.logout();
      return;
    }

    if (!username) {
      const response = await profileService.getProfileByUserId(authenticatedUserId);
      profile.value = response.data.data.profile;

      form.username = profile.value.username;
      form.firstName = profile.value.firstName;
      form.lastName = profile.value.lastName;
      form.bio = profile.value.bio;

      isOwnProfile.value = true;
      router.replace(`/profile/${profile.value.username}`);
    } else {
      const response = await profileService.getProfileByUsername(username);

      profile.value = response.data.data.profile;

      form.username = profile.value.username;
      form.firstName = profile.value.firstName;
      form.lastName = profile.value.lastName;
      form.bio = profile.value.bio;

      if (profile.value.userId == authenticatedUserId) {
        isOwnProfile.value = true;
      } else {
        isOwnProfile.value = false;
      }
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || t('profile_page.errors.profile_not_loaded');
  } finally {
    loadingStore.hide();
  }
});

// --- Handle Text Data ---
async function handleUpdate() {
  loadingStore.show();
  error.value = '';
  try {
    const response = await profileService.createOrUpdateProfile(form);
    profile.value = response.data.data.profile;
    isEditing.value = false;
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('profile_page.errors.profile_info_update_failed');
  } finally {
    loadingStore.hide();
  }
}

// --- Handle Profile Picture ---
async function onFileChange(event: Event) {
  const target = event.target as HTMLInputElement;
  if (!target.files?.length) return;

  const formData = new FormData();
  formData.append('file', target.files[0]);

  try {
    loadingStore.show();
    const response = await profileService.changeProfilePicture(formData);
    profile.value = response.data.data.profile;
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('profile_page.errors.profile_image_update_failed');
  } finally {
    loadingStore.hide();
  }
}

async function deleteProfilePicture() {
  try {
    loadingStore.show();
    const response = await profileService.deleteProfilePicture();
    profile.value = response.data.data.profile;
  } catch (err: any) {
    error.value = err.response?.data?.errors || t('profile_page.errors.profile_image_deletion_failed');
  } finally {
    loadingStore.hide();
  }
}
</script>

<template>
  <div class="profile-page" v-if="!loadingStore.isLoading || error">
    <p v-if="error" class="error">{{ error }}</p>

    <div v-else-if="profile" class="profile-box">
      <div class="avatar-container">
        <div class="avatar-wrapper">
          <img :src="profile.profileImage" class="avatar" alt="avatar" />
        </div>

        <div v-if="isOwnProfile" class="actions">
          <input type="file" @change="onFileChange" accept="image/*" id="file-input" hidden />
          <label for="file-input" class="btn-outline">{{ t('profile_page.actions.change_profile_image') }}</label>
          <button class="btn-outline" type="button" @click="deleteProfilePicture">
            {{ t('profile_page.actions.delete_profile_image') }}
          </button>
        </div>
      </div>

      <div v-if="isEditing && isOwnProfile" class="profile-info">
        <form @submit.prevent="handleUpdate" class="profile-info">
          <label>
            {{ t('profile_page.profile_info_form.username') }}
            <input v-model="form.username" :placeholder="t('profile_page.profile_info_form.username')" required />
          </label>
          <label>
            {{ t('profile_page.profile_info_form.firstname') }}
            <input v-model="form.firstName" :placeholder="t('profile_page.profile_info_form.firstname')" />
          </label>
          <label>
            {{ t('profile_page.profile_info_form.lastname') }}
            <input v-model="form.lastName" :placeholder="t('profile_page.profile_info_form.lastname')" />
          </label>
          <label>
            {{ t('profile_page.profile_info_form.bio') }}
            <textarea
              v-model="form.bio"
              :placeholder="t('profile_page.profile_info_form.bio')"
              maxlength="255"
            ></textarea>
          </label>

          <div class="actions">
            <button class="btn-outline" type="submit" :disabled="loadingStore.isLoading">
              {{ t('common.actions.save') }}
            </button>
            <button class="btn-outline" type="button" @click="isEditing = false">
              {{ t('common.actions.cancel') }}
            </button>
          </div>
        </form>
      </div>

      <div v-else class="profile-info">
        <label>
          {{ t('profile_page.profile_info_form.username') }}
          <input
            readonly
            v-model="form.username"
            :placeholder="t('profile_page.profile_info_form.username')"
            required
          />
        </label>
        <label>
          {{ t('profile_page.profile_info_form.firstname') }}
          <input readonly v-model="form.firstName" :placeholder="t('profile_page.profile_info_form.firstname')" />
        </label>
        <label>
          {{ t('profile_page.profile_info_form.lastname') }}
          <input readonly v-model="form.lastName" :placeholder="t('profile_page.profile_info_form.lastname')" />
        </label>
        <label>
          {{ t('profile_page.profile_info_form.bio') }}
          <textarea
            readonly
            v-model="form.bio"
            :placeholder="t('profile_page.profile_info_form.bio')"
            maxlength="255"
          ></textarea>
        </label>

        <button v-if="isOwnProfile" class="btn-outline" @click="isEditing = true">
          {{ t('profile_page.actions.edit_profile') }}
        </button>
      </div>
    </div>

    <div v-else-if="!profile && !error">
      {{ t('profile_page.errors.profile_not_found') }}
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

.profile-info > label > textarea {
  resize: vertical;
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
