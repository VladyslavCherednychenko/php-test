import apiClient from './axios';
import type { UserProfileForm } from '@/types/profile';

export default {
  createOrUpdateProfile(form: UserProfileForm) {
    return apiClient.post('/profiles', form);
  },
  changeProfilePicture(picture: FormData) {
    return apiClient.post('/profiles/picture', picture, {
      headers: {
        'content-type': 'multipart/form-data',
      },
    });
  },
  deleteProfilePicture() {
    return apiClient.delete('/profiles/picture');
  },
  searchProfile(username: string) {
    return apiClient.get('/profiles/search', {
      params: {
        username: username,
      },
    });
  },
  getProfileById(id: number | string) {
    return apiClient.get(`/profiles/${id}`);
  },
};
