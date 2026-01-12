import { defineStore } from 'pinia';
import { orderAPI, userAPI } from '../services/api';

export const useAdminStore = defineStore('admin', {
    state: () => ({
        orders: [],
        users: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchOrders() {
            this.loading = true;
            this.error = null;
            try {
                const response = await orderAPI.adminGetAll();
                this.orders = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch orders';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateOrderStatus(orderId, status) {
            this.loading = true;
            this.error = null;
            try {
                await orderAPI.update(orderId, { status });
                await this.fetchOrders();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update order';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteOrder(orderId) {
            this.loading = true;
            this.error = null;
            try {
                await orderAPI.delete(orderId);
                await this.fetchOrders();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete order';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchUsers() {
            this.loading = true;
            this.error = null;
            try {
                const response = await userAPI.getAll();
                this.users = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch users';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createUser(data) {
            this.loading = true;
            this.error = null;
            try {
                await userAPI.create(data);
                await this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create user';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateUser(userId, data) {
            this.loading = true;
            this.error = null;
            try {
                await userAPI.update(userId, data);
                await this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update user';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteUser(userId) {
            this.loading = true;
            this.error = null;
            try {
                await userAPI.delete(userId);
                await this.fetchUsers();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete user';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        clearError() {
            this.error = null;
        },
    },
});
