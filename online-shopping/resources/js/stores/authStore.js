import { defineStore } from 'pinia';
import { authAPI } from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'admin',
        currentUser: (state) => state.user,
    },

    actions: {
        async register(data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await authAPI.register(data);
                // Handle new API response format with ApiResponse trait
                const responseData = response.data.data || response.data;
                this.user = responseData.user;
                this.token = responseData.token;
                localStorage.setItem('token', this.token);
                return responseData;
            } catch (error) {
                this.error = error.response?.data?.message || 'Registration failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const response = await authAPI.login(credentials);
                // Handle new API response format with ApiResponse trait
                const responseData = response.data.data || response.data;
                this.user = responseData.user;
                this.token = responseData.token;
                localStorage.setItem('token', this.token);
                return responseData;
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await authAPI.logout();
            } catch (error) {
                // Silently handle logout errors
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
            }
        },

        async fetchUser() {
            if (!this.token) return;
            
            try {
                const response = await authAPI.getUser();
                // Handle new API response format with ApiResponse trait
                this.user = response.data.data || response.data;
            } catch (error) {
                // If token is invalid, clear it
                if (error.response?.status === 401) {
                    this.logout();
                }
            }
        },

        clearError() {
            this.error = null;
        },
    },
});
