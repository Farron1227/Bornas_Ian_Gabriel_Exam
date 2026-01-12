import axios from 'axios';

const API_URL = '/api';

// Set up axios defaults
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Add token to requests if it exists
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Auth API
export const authAPI = {
    register: (data) => axios.post(`${API_URL}/register`, data),
    login: (data) => axios.post(`${API_URL}/login`, data),
    logout: () => axios.post(`${API_URL}/logout`),
    getUser: () => axios.get(`${API_URL}/user`),
};

// Product API
export const productAPI = {
    getAll: (params) => axios.get(`${API_URL}/products`, { params }),
    getOne: (id) => axios.get(`${API_URL}/products/${id}`),
    create: (data) => axios.post(`${API_URL}/products`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }),
    update: (id, data) => axios.post(`${API_URL}/products/${id}`, {
        ...data,
        _method: 'PUT'
    }, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }),
    delete: (id) => axios.delete(`${API_URL}/products/${id}`),
};

// Cart API
export const cartAPI = {
    getAll: () => axios.get(`${API_URL}/cart`),
    add: (data) => axios.post(`${API_URL}/cart`, data),
    update: (id, data) => axios.put(`${API_URL}/cart/${id}`, data),
    remove: (id) => axios.delete(`${API_URL}/cart/${id}`),
    clear: () => axios.delete(`${API_URL}/cart`),
};

// Order API
export const orderAPI = {
    getAll: () => axios.get(`${API_URL}/orders`),
    getOne: (id) => axios.get(`${API_URL}/orders/${id}`),
    create: () => axios.post(`${API_URL}/orders`),
    update: (id, data) => axios.put(`${API_URL}/orders/${id}`, data),
    delete: (id) => axios.delete(`${API_URL}/orders/${id}`),
    adminGetAll: () => axios.get(`${API_URL}/admin/orders`),
};

// User API
export const userAPI = {
    getAll: () => axios.get(`${API_URL}/users`),
    create: (data) => axios.post(`${API_URL}/users`, data),
    update: (id, data) => axios.put(`${API_URL}/users/${id}`, data),
    delete: (id) => axios.delete(`${API_URL}/users/${id}`),
};
