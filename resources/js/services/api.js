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
  // 👉 Auth Management //
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
  // Logout
  logout() {
    return apiClient.post('/logout');
  },

  
  // 👉 Users Management //
  // Get User Information
  getUser(id) {
    return apiClient.get('/user/' + id);
  },
  // Get All Users
  getUsers(currentPage) {
    return apiClient.get(`/users?page=${currentPage}`);
  },
  // Create a User
  createUser(data, config) {
    return apiClient.post('/user', data, config);
  },
  // Update user data
  updateUser(id, data, config) {
    return apiClient.post('/user/' + id, data, config);
  },
  // delete user
  deleteUser(userId) {
    return apiClient.delete('/user/' + userId);
  },


  // 👉 Owners Management //
  // Get Owner Information
  getOwner(id) {
    return apiClient.get('/owner/' + id);
  },

  // Get All Owners
  getOwners(currentPage) {
    return apiClient.get(`/owners?page=${currentPage}`);
  },

  // create an owner
  createOwner(data, config) {
    return apiClient.post('/owner', data, config);
  },

  // Update Owner Data
  updateOwner(id, data, config) {
    return apiClient.post('/owner/' + id, data, config);
  },

  // Delete Owner
  deleteOwner(id) {
    return apiClient.delete('/owner/' + id);
  },



  // 👉 Fetch Stats for Dashboard //
  getStats() {
    return apiClient.get('/stats');
  }
};
