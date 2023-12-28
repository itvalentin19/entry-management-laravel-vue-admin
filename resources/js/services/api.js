// services/api.js
import axios from 'axios';

const origin = window.location.origin
const API_URL = origin + '/api/v1'; // Replace with your API URL

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
    console.log(API_URL);
    return apiClient.post('/login', data);
  },
  // Get Logged in User Information
  authentication() {
    return apiClient.get('/user');
  },
  // Update User Information
  updateAccount(data, config) {
    return apiClient.post('/update-account', data, config);
  },
  // Update User Password in profile page
  updatePassword(data) {
    return apiClient.post('/update-password', data);
  },
  // Send Reset Password Link
  sendResetPasswordLink(data) {
    return apiClient.post('/forgot-password', data);
  },
  // Reset Password without login
  resetPassword(data) {
    return apiClient.post('/reset-password', data);
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
  getUsers(params) {
    return apiClient.get(`/users?${params}`);
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
  getOwners(params) {
    return apiClient.get(`/owners?${params}`);
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

  // 👉 Entities Management //
  // Get Entity Information
  getEntity(id) {
    return apiClient.get('/entity/' + id);
  },

  // Get All Entities
  getEntities(params) {
    return apiClient.get(`/entities?${params}`);
  },

  // Get All Entity Props
  getEntityProps() {
    return apiClient.get(`/entities/props`);
  },

  // create an Entity
  createEntity(data, config) {
    return apiClient.post('/entity', data, config);
  },

  // create an Entity
  entityUpload(data, config) {
    return apiClient.post('/entity/bulk-upload', data, config);
  },

  // Update Entity Data
  updateEntity(id, data, config) {
    return apiClient.post('/entity/' + id, data, config);
  },

  // Update Entity Data
  updateOwners(data) {
    return apiClient.post('/remove-owner', data);
  },

  // Update Entity Data
  updateServices(data) {
    return apiClient.post('/remove-service', data);
  },

  // Delete Entity
  deleteEntity(id) {
    return apiClient.delete('/entity/' + id);
  },

  // Download Report
  downloadReport(id) {
    return axios({
      url: origin + '/entity/report/' + id,
      method: "GET",
      responseType: "blob",
    });
  },


  // 👉 Fetch Stats for Dashboard //
  getStats() {
    return apiClient.get('/stats');
  },

  // Create Referred By
  createReferredBy(data) {
    return apiClient.post('/add-referred-by', data)
  }
};
