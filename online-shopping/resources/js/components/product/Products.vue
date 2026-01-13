<template>
    <div class="products-page">
        <!-- Header -->
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <router-link to="/home" class="logo">
                        <img :src="logo" alt="PurpleBug" />
                    </router-link>

                    <div class="header-actions">
                        <div class="user-info" v-if="authStore.isAuthenticated">
                            <span class="greeting">Hi, {{ authStore.currentUser?.name?.split(' ')[0] || 'Guest' }}!</span>
                            <span class="description">Description</span>
                        </div>
                        <button @click="toggleCart" class="cart-button">
                            🛒
                            <span v-if="cartStore.itemCount > 0" class="cart-badge">{{ cartStore.itemCount }}</span>
                        </button>
                        <button v-if="authStore.isAuthenticated && authStore.isAdmin" @click="goToAdmin" class="btn-secondary">
                            ADMIN
                        </button>
                        <button v-if="authStore.isAuthenticated && !authStore.isAdmin" @click="handleLogout" class="btn-secondary">
                            LOGOUT
                        </button>
                        <router-link v-if="!authStore.isAuthenticated" to="/login" class="btn-primary">
                             Login
                        </router-link>
                        
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <!-- Search and Filter Bar -->
                <div class="filter-bar">
                    <div class="search-box">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            @input="handleSearch"
                            placeholder="Search"
                            class="search-input"
                        />
                        <button class="search-button"></button>
                    </div>

                    <div class="sort-buttons">
                        <button 
                            @click="setSortOrder('price_asc')" 
                            :class="['sort-btn', { active: sortOrder === 'price_asc' }]"
                        >
                            Price ascending
                        </button>
                        <button 
                            @click="setSortOrder('price_desc')" 
                            :class="['sort-btn', { active: sortOrder === 'price_desc' }]"
                        >
                            Price descending
                        </button>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-grid" v-if="!productStore.loading">
                    <div 
                        v-for="product in productStore.products" 
                        :key="product.id"
                        @click="openProductModal(product)"
                        class="product-card"
                    >
                        <div class="product-image">
                            <img v-if="product.image" :src="`/storage/${product.image}`" :alt="product.name" />
                            <div v-else class="product-placeholder">📦</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name">{{ product.name }}</h3>
                            <p class="product-price">₱ {{ parseFloat(product.price).toFixed(2) }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="loading">Loading products...</div>

                <!-- Pagination -->
                <div class="pagination" v-if="productStore.pagination.last_page > 1">
                    <button 
                        @click="goToPage(1)" 
                        :disabled="productStore.pagination.current_page === 1"
                        class="page-btn"
                    >
                        « Previous
                    </button>

                    <button 
                        v-for="page in displayedPages" 
                        :key="page"
                        @click="goToPage(page)"
                        :class="['page-btn', { active: page === productStore.pagination.current_page }]"
                    >
                        {{ page }}
                    </button>

                    <span v-if="showEllipsis">...</span>

                    <button 
                        @click="goToPage(productStore.pagination.last_page)" 
                        :disabled="productStore.pagination.current_page === productStore.pagination.last_page"
                        class="page-btn"
                    >
                        Next »
                    </button>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-logo">
                <img :src="logo" alt="PurpleBug" />
            </div>
            <p class="copyright">Copyright 2025 PurpleBug Inc.</p>
        </footer>

        <!-- Product Detail Modal -->
        <div v-if="showProductModal" class="modal-overlay" @click="closeProductModal">
            <div class="modal-content product-modal" @click.stop>
                <button @click="closeProductModal" class="modal-close">×</button>
                <div class="modal-body">
                    <div class="product-modal-image">
                        <img v-if="selectedProduct.image" :src="`/storage/${selectedProduct.image}`" :alt="selectedProduct.name" />
                        <div v-else class="product-placeholder">📦</div>
                    </div>
                    <div class="product-modal-info">
                        <h2 class="modal-product-name">{{ selectedProduct.name }}</h2>
                        <p class="modal-product-price">₱{{ parseFloat(selectedProduct.price).toFixed(2) }}</p>
                        <div class="stock-info" :class="{ 'out-of-stock': selectedProduct.stock === 0 }">
                            {{ selectedProduct.stock > 0 ? `${selectedProduct.stock} in stock` : 'Out of Stock' }}
                        </div>
                        <div class="quantity-section" v-if="selectedProduct.stock > 0">
                            <label>Quantity</label>
                            <select v-model="selectedQuantity" class="quantity-select">
                                <option v-for="n in Math.min(availableStock, 10)" :key="n" :value="n">{{ n }}</option>
                            </select>
                        </div>
                        <button @click="addToCart" class="add-to-cart-btn" :disabled="addingToCart || selectedProduct.stock === 0">
                            {{ selectedProduct.stock === 0 ? 'Out of Stock' : (addingToCart ? 'Adding...' : 'Add To Cart') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Modal -->
        <div v-if="showCartModal" class="modal-overlay" @click="closeCart">
            <div class="modal-content cart-modal" @click.stop>
                <div class="cart-header">
                    <h2>🛒 Cart</h2>
                    <button @click="placeOrder" class="place-order-btn">PLACE ORDER</button>
                </div>
                <div class="cart-body">
                    <div v-if="cartStore.items.length === 0" class="empty-cart">
                        Your cart is empty
                    </div>
                    <div v-else>
                        <div v-for="item in cartStore.items" :key="item.id" class="cart-item">
                            <div class="cart-item-image">
                                <img v-if="item.product.image" :src="`/storage/${item.product.image}`" :alt="item.product.name" />
                                <div v-else class="product-placeholder-small">📦</div>
                            </div>
                            <div class="cart-item-info">
                                <h3>{{ item.product.name }}</h3>
                                <p class="cart-item-price">₱{{ parseFloat(item.product.price).toFixed(2) }}</p>
                                <div class="quantity-controls">
                                    <label>Quantity</label>
                                    <select v-model="item.quantity" @change="updateCartItem(item)" class="quantity-select-small">
                                        <option v-for="n in Math.min(item.product.stock, 10)" :key="n" :value="n">{{ n }}</option>
                                    </select>
                                </div>
                            </div>
                            <button @click="removeFromCart(item.id)" class="remove-btn">🗑️</button>
                        </div>
                    </div>
                </div>
                <div class="cart-footer">
                    <div class="cart-total">
                        <span>TOTAL:</span>
                        <span class="total-amount">₱ {{ parseFloat(cartStore.total).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thank You Modal -->
        <div v-if="showThankYouModal" class="modal-overlay" @click="closeThankYou">
            <div class="modal-content thankyou-modal" @click.stop>
                <div class="thankyou-content">
                    <div class="thankyou-logo">
                        <img :src="logo" alt="PurpleBug" />
                    </div>
                    <div class="thankyou-illustration">
                        👩‍💼🛍️
                    </div>
                    <p class="thankyou-message">Thank you for shopping with us.</p>
                    <button @click="closeThankYou" class="close-btn">×</button>
                </div>
            </div>
        </div>

        <!-- Add to Cart Confirmation Modal -->
        <div v-if="showConfirmModal" class="modal-overlay" @click="cancelAddToCart">
            <div class="modal-content confirm-modal" @click.stop>
                <div class="confirm-content">
                    <h2>Add to Cart</h2>
                    <p>Add <strong>{{ selectedQuantity }}</strong> x <strong>{{ selectedProduct?.name }}</strong> to your cart?</p>
                    <div class="confirm-buttons">
                        <button @click="cancelAddToCart" class="btn-cancel">Cancel</button>
                        <button @click="confirmAddToCart" class="btn-confirm" :disabled="addingToCart">
                            {{ addingToCart ? 'Adding...' : 'Confirm' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout Confirmation Modal -->
        <div v-if="showLogoutModal" class="modal-overlay" @click="cancelLogout">
            <div class="modal-content logout-modal" @click.stop>
                <div class="logout-content">
                    <h2>Sign Out</h2>
                    <p>Do you want to sign out and continue?</p>
                    <div class="logout-buttons">
                        <button @click="cancelLogout" class="btn-cancel">Cancel</button>
                        <button @click="confirmLogout" class="btn-confirm">Sign Out</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, inject, watch } from 'vue';
import { useAuthStore } from '../../stores/authStore';
import { useCartStore } from '../../stores/cartStore';
import { useProductStore } from '../../stores/productStore';
import { useRouter, useRoute } from 'vue-router';
import { orderAPI } from '../../services/api';
import logo from '../../../images/purplebug-logo.png';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cartStore = useCartStore();
const productStore = useProductStore();
const notify = inject('notify');

const searchQuery = ref('');
const sortOrder = ref('');
const showProductModal = ref(false);
const showCartModal = ref(false);
const showThankYouModal = ref(false);
const showLogoutModal = ref(false);
const showConfirmModal = ref(false);
const selectedProduct = ref({});
const selectedQuantity = ref(1);
const addingToCart = ref(false);

onMounted(async () => {
    await productStore.fetchProducts();
    if (authStore.isAuthenticated) {
        await cartStore.fetchCart();
    }
});

// Watch for route changes to refresh products when navigating back from admin
watch(() => route.path, async (newPath) => {
    if (newPath === '/home') {
        await productStore.fetchProducts();
        if (authStore.isAuthenticated) {
            await cartStore.fetchCart();
        }
    }
});

const handleSearch = () => {
    productStore.setFilters({ search: searchQuery.value });
    productStore.fetchProducts();
};

const setSortOrder = (order) => {
    sortOrder.value = order;
    productStore.setFilters({ sort: order });
    productStore.fetchProducts();
};

const openProductModal = (product) => {
    selectedProduct.value = product;
    
    // Check if product is already in cart
    const existingCartItem = cartStore.items.find(item => item.product_id === product.id);
    
    if (existingCartItem) {
        // Calculate remaining stock after what's already in cart
        const remainingStock = Math.max(0, product.stock - existingCartItem.quantity);
        if (remainingStock > 0) {
            selectedQuantity.value = 1;
        }
    } else {
        selectedQuantity.value = 1;
    }
    
    showProductModal.value = true;
};

const closeProductModal = () => {
    showProductModal.value = false;
};

const addToCart = () => {
    if (!authStore.isAuthenticated) {
        notify.error('Please login to add items to cart');
        router.push('/login');
        return;
    }

    // Check if product is out of stock
    if (selectedProduct.value.stock === 0) {
        notify.error('This product is out of stock');
        return;
    }

    // Check if there's available stock considering what's already in cart
    if (availableStock.value === 0) {
        notify.error('No more stock available for this product');
        return;
    }

    showConfirmModal.value = true;
};

const confirmAddToCart = async () => {
    addingToCart.value = true;
    try {
        await cartStore.addToCart(selectedProduct.value.id, selectedQuantity.value);
        notify.success('Item added to cart!');
        showConfirmModal.value = false;
        closeProductModal();
    } catch (error) {
        notify.error(error.response?.data?.message || 'Failed to add to cart');
    } finally {
        addingToCart.value = false;
    }
};

const cancelAddToCart = () => {
    showConfirmModal.value = false;
};

const toggleCart = async () => {
    if (!authStore.isAuthenticated) {
        notify.error('Please login to view cart');
        router.push('/login');
        return;
    }
    
    await cartStore.fetchCart();
    showCartModal.value = !showCartModal.value;
};

const closeCart = () => {
    showCartModal.value = false;
};

const updateCartItem = async (item) => {
    try {
        await cartStore.updateQuantity(item.id, item.quantity);
    } catch (error) {
        notify.error('Failed to update quantity');
    }
};

const removeFromCart = async (itemId) => {
    try {
        await cartStore.removeItem(itemId);
        notify.success('Item removed from cart');
    } catch (error) {
        notify.error('Failed to remove item');
    }
};

const placeOrder = async () => {
    try {
        await orderAPI.create();
        notify.success('Order placed successfully!');
        showCartModal.value = false;
        showThankYouModal.value = true;
        await cartStore.fetchCart();
    } catch (error) {
        notify.error(error.response?.data?.message || 'Failed to place order');
    }
};

const closeThankYou = () => {
    showThankYouModal.value = false;
};

const handleLogout = async () => {
    // Only guests/customers can logout from this button
    if (!authStore.isAdmin) {
        showLogoutModal.value = true;
    }
};

const goToAdmin = () => {
    // Only admins can access admin dashboard
    if (authStore.isAdmin) {
        router.push('/admin');
    } else {
        notify.error('You do not have permission to access the admin panel');
    }
};

const confirmLogout = async () => {
    showLogoutModal.value = false;
    await authStore.logout();
    notify.success('Logged out successfully');
    router.push('/login');
};

const cancelLogout = () => {
    showLogoutModal.value = false;
};

const goToPage = async (page) => {
    productStore.setPage(page);
    await productStore.fetchProducts();
};

const availableStock = computed(() => {
    if (!selectedProduct.value || !selectedProduct.value.id) return 0;
    
    const existingCartItem = cartStore.items.find(item => item.product_id === selectedProduct.value.id);
    const currentInCart = existingCartItem ? existingCartItem.quantity : 0;
    
    return Math.max(0, selectedProduct.value.stock - currentInCart);
});

const displayedPages = computed(() => {
    const current = productStore.pagination.current_page;
    const last = productStore.pagination.last_page;
    const pages = [];
    
    for (let i = Math.max(1, current - 1); i <= Math.min(last, current + 1); i++) {
        pages.push(i);
    }
    
    return pages;
});

const showEllipsis = computed(() => {
    return productStore.pagination.current_page < productStore.pagination.last_page - 2;
});
</script>

<style scoped>
@import '../../../css/products.css';
@import '../../../css/modals.css';
</style>
