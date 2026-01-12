import { defineStore } from 'pinia';
import { productAPI } from '../services/api';

export const useProductStore = defineStore('product', {
    state: () => ({
        products: [],
        selectedProduct: null,
        pagination: {
            current_page: 1,
            last_page: 1,
            per_page: 6,
            total: 0,
        },
        filters: {
            search: '',
            sort: '',
        },
        loading: false,
        error: null,
    }),

    actions: {
        async fetchProducts(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await productAPI.getAll({
                    ...this.filters,
                    page: this.pagination.current_page,
                    per_page: this.pagination.per_page,
                    ...params,
                });
                this.products = response.data.data;
                this.pagination = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    per_page: response.data.per_page,
                    total: response.data.total,
                };
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch products';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchProduct(id) {
            this.loading = true;
            this.error = null;
            try {
                const response = await productAPI.getOne(id);
                this.selectedProduct = response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch product';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createProduct(data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await productAPI.create(data);
                await this.fetchProducts();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create product';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateProduct(id, data) {
            this.loading = true;
            this.error = null;
            try {
                const response = await productAPI.update(id, data);
                await this.fetchProducts();
                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update product';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteProduct(id) {
            this.loading = true;
            this.error = null;
            try {
                await productAPI.delete(id);
                await this.fetchProducts();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete product';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        setFilters(filters) {
            this.filters = { ...this.filters, ...filters };
            this.pagination.current_page = 1;
        },

        setPage(page) {
            this.pagination.current_page = page;
        },

        clearError() {
            this.error = null;
        },
    },
});
