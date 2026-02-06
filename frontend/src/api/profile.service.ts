import apiClient from './axios';

export default {
  createOrUpdateProfile(userData) {
    return apiClient.post('/profiles', userData);
  },
  changeProfilePicture(picture) {
    return apiClient.post('/profiles/picture', picture, {
      headers: {
        'content-type': 'multipart/form-data', // do not forget this
      },
    });
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
