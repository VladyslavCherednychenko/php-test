import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { User } from '@/types/user';
import type { UserProfileForm } from '@/types/profile';
import profileService from '@/api/profile.service';

export const useUserStore = defineStore('user', () => {
  const user = ref<User | null>(JSON.parse(localStorage.getItem('user') || 'null'));

  function setUser(newUser: User) {
    user.value = newUser;
    localStorage.setItem('user', JSON.stringify(newUser));
  }

  function clearUser() {
    user.value = null;
    localStorage.removeItem('user');
  }

  async function updateProfileImage(newImagePath: FormData) {
    const response = await profileService.changeProfilePicture(newImagePath);
    if (user.value) {
      user.value.profile!.profileImage = response.data.data.profile.profileImage;
      localStorage.setItem('user', JSON.stringify(user.value));
    }
  }

  async function deleteProfileImage() {
    const response = await profileService.deleteProfilePicture();
    if (user.value) {
      user.value.profile!.profileImage = response.data.data.profile.profileImage;
      localStorage.setItem('user', JSON.stringify(user.value));
    }
  }

  async function updateProfileContent(form: UserProfileForm) {
    const response = await profileService.createOrUpdateProfile(form);
    if (user.value) {
      // I should create a mapper for this later
      user.value.profile!.username = response.data.data.profile.username;
      user.value.profile!.firstName = response.data.data.profile.firstName;
      user.value.profile!.lastName = response.data.data.profile.lastName;
      user.value.profile!.bio = response.data.data.profile.bio;
      localStorage.setItem('user', JSON.stringify(user.value));
    }
  }

  return { user, setUser, clearUser, updateProfileImage, deleteProfileImage, updateProfileContent };
});
