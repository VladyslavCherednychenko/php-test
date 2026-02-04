import apiClient from './axios';

export default {
  createOrUpdateProfile(userData) {
    return apiClient.post('/profiles', userData);
  },
  changeProfilePicture(picture) {
    return apiClient.post('/profiles/picture', picture);
  },
  searchProfile(username: string) {
    return apiClient.get('/profiles/search', {
      params: {
        username: username
      }
    })
  },
  getProfileById(id: number | string) {
    return apiClient.get(`/profiles/${id}`);
  }
};
