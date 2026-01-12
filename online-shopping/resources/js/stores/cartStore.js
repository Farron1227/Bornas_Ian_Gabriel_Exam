import { defineStore } from 'pinia';
import { cartAPI } from '../services/api';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        total: 0,
        loading: false,
        error: null,
    }),

    getters: {
        itemCount: (state) => state.items.reduce((sum, item) => sum + item.quantity, 0),
        cartTotal: (state) => state.total,
    },

    actions: {
        async fetchCart() {
            this.loading = true;
            this.error = null;
            try {
                const response = await cartAPI.getAll();
                // Handle new API response format with ApiResponse trait
                const responseData = response.data.data || response.data;
                this.items = responseData.items;
                this.total = responseData.total;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch cart';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async addToCart(productId, quantity = 1) {
            this.loading = true;
            this.error = null;
            try {
                await cartAPI.add({ product_id: productId, quantity });
                await this.fetchCart();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to add item to cart';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateQuantity(cartItemId, quantity) {
            this.loading = true;
            this.error = null;
            try {
                await cartAPI.update(cartItemId, { quantity });
                await this.fetchCart();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update quantity';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async removeItem(cartItemId) {
            this.loading = true;
            this.error = null;
            try {
                await cartAPI.remove(cartItemId);
                await this.fetchCart();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to remove item';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async clearCart() {
            this.loading = true;
            this.error = null;
            try {
                await cartAPI.clear();
                this.items = [];
                this.total = 0;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to clear cart';
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
