// services/api.js
import axios from 'axios';

const API_URL = 'http://localhost:8000/api/v1'; // Replace with your API URL

const apiClient = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-type': 'Application/json'
  },
  // You can add more default settings for axios here
});

apiClient.interceptors.request.use(config => {
  // Assuming you store your token in localStorage or Vuex store
  const token = localStorage.getItem('__t');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});


export default {
  // Login
  login(data) {
    return apiClient.post('/login', data);
  },
  // Get Logged in User Information
  authentication() {
    return apiClient.get('/user');
  },
  // Get Logged in User Information
  updateAccount(data, config) {
    return apiClient.post('/update-account', data, config);
  },

  // Get User Information
  getUser(id) {
    return apiClient.get('/user/' + id);
  },

  // Get All Users
  getUsers(currentPage) {
    return apiClient.get(`/users?page=${currentPage}`);
  },

  // Logout
  logout() {
    return apiClient.post('/logout');
  },

  // Example POST request
  createUser(data, config) {
    return apiClient.post('/user', data, config);
  },

  // Example POST request
  updateUser(id, data, config) {
    return apiClient.post('/user/' + id, data, config);
  },

// Example POST request
  deleteUser(userId) {
    return apiClient.delete('/user/' + userId);
  },

  // Example POST request
  createPost(data) {
    return apiClient.post('/posts', data);
  },

  // Fetch Stats for Dashboard
  getStats() {
    return apiClient.get('/stats');
  }
};
