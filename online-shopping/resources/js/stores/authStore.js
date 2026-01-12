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
                this.user = response.data.user;
                this.token = response.data.token;
                localStorage.setItem('token', this.token);
                return response.data;
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
                this.user = response.data.user;
                this.token = response.data.token;
                localStorage.setItem('token', this.token);
                return response.data;
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
                console.error('Logout error:', error);
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
                this.user = response.data;
            } catch (error) {
                console.error('Fetch user error:', error);
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
