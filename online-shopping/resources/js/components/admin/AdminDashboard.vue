<template>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img :src="logo" alt="PurpleBug" class="sidebar-logo" />
            </div>

            <nav class="sidebar-nav">
                <button 
                    @click="currentView = 'products'" 
                    :class="['nav-item', { active: currentView === 'products' }]"
                >
                    <span class="nav-icon">📦</span>
                    <span>Products Management</span>
                </button>
                <button 
                    @click="currentView = 'orders'" 
                    :class="['nav-item', { active: currentView === 'orders' }]"
                >
                    <span class="nav-icon">📋</span>
                    <span>Orders</span>
                </button>
                <button 
                    @click="currentView = 'users'" 
                    :class="['nav-item', { active: currentView === 'users' }]"
                >
                    <span class="nav-icon">👥</span>
                    <span>Users Management</span>
                </button>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <span class="user-avatar">👤</span>
                    <span class="user-name">Hi, Admin!</span>
                </div>
                <button @click="handleLogout" class="logout-btn">🚪</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Products Management -->
            <div v-if="currentView === 'products'" class="content-section">
                <div class="section-header">
                    <h1>Products Management</h1>
                    <button @click="openProductForm()" class="add-btn">Add Product</button>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Number of Stocks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in productStore.products" :key="product.id">
                                <td>{{ product.name }}</td>
                                <td>₱ {{ parseFloat(product.price).toFixed(2) }}</td>
                                <td>{{ product.stock }}</td>
                                <td class="action-btns">
                                    <button @click="openProductForm(product)" class="edit-btn">✏️</button>
                                    <button @click="deleteProduct(product.id)" class="delete-btn">🗑️</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders Management -->
            <div v-if="currentView === 'orders'" class="content-section">
                <div class="section-header">
                    <h1>Orders</h1>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in adminStore.orders" :key="order.id">
                                <td>{{ order.user?.name || 'N/A' }}</td>
                                <td>{{ getOrderProducts(order) }}</td>
                                <td>
                                    <span :class="['status-badge', order.status]">
                                        {{ formatStatus(order.status) }}
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <button @click="viewOrder(order)" class="view-btn">👁️</button>
                                    <button @click="deleteOrder(order.id)" class="delete-btn">🗑️</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Management -->
            <div v-if="currentView === 'users'" class="content-section">
                <div class="section-header">
                    <h1>Users Management</h1>
                    <button @click="openUserForm()" class="add-btn">Add User</button>
                </div>

                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in adminStore.users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span :class="['status-badge', user.is_active ? 'active' : 'inactive']">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="action-btns">
                                    <button @click="openUserForm(user)" class="edit-btn">✏️</button>
                                    <button @click="deleteUser(user.id)" class="delete-btn">🗑️</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Product Form Modal -->
        <div v-if="showProductForm" class="modal-overlay" @click="closeProductForm">
            <div class="modal-content form-modal" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingProduct ? 'Edit Product' : 'Add Product' }}</h2>
                    <div class="modal-actions">
                        <button @click="saveProduct" class="save-btn">SAVE</button>
                        <button @click="closeProductForm" class="cancel-btn">CANCEL</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group image-upload">
                            <label>Product Image</label>
                            <div class="image-preview">
                                <img v-if="imagePreview" :src="imagePreview" alt="Preview" />
                                <div v-else class="placeholder">📷</div>
                            </div>
                            <input type="file" @change="handleImageChange" accept="image/*" />
                        </div>
                        <div class="form-fields">
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" v-model="productForm.price" placeholder="₱" step="0.01" required />
                            </div>
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" v-model="productForm.name" required />
                            </div>
                            <div class="form-group">
                                <label>Number of Stocks</label>
                                <input type="number" v-model="productForm.stock" required />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Form Modal -->
        <div v-if="showUserForm" class="modal-overlay" @click="closeUserForm">
            <div class="modal-content form-modal" @click.stop>
                <div class="modal-header">
                    <h2>{{ editingUser ? 'Edit User' : 'Add User' }}</h2>
                    <div class="modal-actions">
                        <button @click="saveUser" class="save-btn">SAVE</button>
                        <button @click="closeUserForm" class="cancel-btn">CANCEL</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="user-form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <div class="input-with-icon">
                                <span class="icon">👤</span>
                                <input type="text" v-model="userForm.name" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-with-icon">
                                <span class="icon">@</span>
                                <input type="email" v-model="userForm.email" required />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-with-icon">
                                <span class="icon">🔒</span>
                                <input type="password" v-model="userForm.password" :required="!editingUser" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <div class="input-with-icon">
                                <span class="icon">🔒</span>
                                <input type="password" v-model="userForm.password_confirmation" :required="!editingUser" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order View Modal -->
        <div v-if="showOrderView" class="modal-overlay" @click="closeOrderView">
            <div class="modal-content form-modal" @click.stop>
                <div class="modal-header">
                    <h2>{{ selectedOrder?.user?.name?.toUpperCase() || 'ORDER DETAILS' }}</h2>
                    <div class="modal-actions">
                        <button @click="saveOrderStatus" class="save-btn">SAVE</button>
                        <button @click="closeOrderView" class="cancel-btn">CLOSE</button>
                    </div>
                </div>
                <div class="modal-body order-details">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" :value="selectedOrder?.user?.name" readonly />
                    </div>
                    <div class="form-group">
                        <label>Product Ordered</label>
                        <input type="text" :value="getOrderProducts(selectedOrder)" readonly />
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select v-model="orderStatusForm.status">
                            <option value="pending">Pending</option>
                            <option value="for_delivery">For Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="canceled">Canceled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="text" :value="getTotalQuantity(selectedOrder)" readonly />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/authStore';
import { useProductStore } from '../../stores/productStore';
import { useAdminStore } from '../../stores/adminStore';
import logo from '../../../images/purplebug-logo.png';

const router = useRouter();
const authStore = useAuthStore();
const productStore = useProductStore();
const adminStore = useAdminStore();
const notify = inject('notify');

const currentView = ref('products');

// Product Form
const showProductForm = ref(false);
const editingProduct = ref(null);
const productForm = ref({
    name: '',
    price: '',
    stock: '',
    image: null
});
const imagePreview = ref('');

// User Form
const showUserForm = ref(false);
const editingUser = ref(null);
const userForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

// Order View
const showOrderView = ref(false);
const selectedOrder = ref(null);
const orderStatusForm = ref({
    status: ''
});

onMounted(async () => {
    await productStore.fetchProducts();
    await adminStore.fetchOrders();
    await adminStore.fetchUsers();
});

// Product Functions
const openProductForm = (product = null) => {
    editingProduct.value = product;
    if (product) {
        productForm.value = {
            name: product.name,
            price: product.price,
            stock: product.stock,
            image: null
        };
        imagePreview.value = product.image ? `/storage/${product.image}` : '';
    } else {
        productForm.value = { name: '', price: '', stock: '', image: null };
        imagePreview.value = '';
    }
    showProductForm.value = true;
};

const closeProductForm = () => {
    showProductForm.value = false;
    editingProduct.value = null;
    imagePreview.value = '';
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        productForm.value.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const saveProduct = async () => {
    try {
        const formData = new FormData();
        formData.append('name', productForm.value.name);
        formData.append('price', productForm.value.price);
        formData.append('stock', productForm.value.stock);
        if (productForm.value.image) {
            formData.append('image', productForm.value.image);
        }

        if (editingProduct.value) {
            await productStore.updateProduct(editingProduct.value.id, formData);
            notify.success('Product updated successfully');
        } else {
            await productStore.createProduct(formData);
            notify.success('Product created successfully');
        }
        closeProductForm();
    } catch (error) {
        notify.error(error.response?.data?.message || 'Failed to save product');
    }
};

const deleteProduct = async (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        try {
            await productStore.deleteProduct(id);
            notify.success('Product deleted successfully');
        } catch (error) {
            notify.error('Failed to delete product');
        }
    }
};

// User Functions
const openUserForm = (user = null) => {
    editingUser.value = user;
    if (user) {
        userForm.value = {
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: ''
        };
    } else {
        userForm.value = {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        };
    }
    showUserForm.value = true;
};

const closeUserForm = () => {
    showUserForm.value = false;
    editingUser.value = null;
};

const saveUser = async () => {
    if (userForm.value.password !== userForm.value.password_confirmation) {
        notify.error('Passwords do not match');
        return;
    }

    try {
        if (editingUser.value) {
            await adminStore.updateUser(editingUser.value.id, userForm.value);
            notify.success('User updated successfully');
        } else {
            await adminStore.createUser(userForm.value);
            notify.success('User created successfully');
        }
        closeUserForm();
    } catch (error) {
        notify.error(error.response?.data?.message || 'Failed to save user');
    }
};

const deleteUser = async (id) => {
    if (confirm('Are you sure you want to delete this user?')) {
        try {
            await adminStore.deleteUser(id);
            notify.success('User deleted successfully');
        } catch (error) {
            notify.error(error.response?.data?.message || 'Failed to delete user');
        }
    }
};

// Order Functions
const viewOrder = (order) => {
    selectedOrder.value = order;
    orderStatusForm.value.status = order.status;
    showOrderView.value = true;
};

const closeOrderView = () => {
    showOrderView.value = false;
    selectedOrder.value = null;
};

const saveOrderStatus = async () => {
    try {
        await adminStore.updateOrderStatus(selectedOrder.value.id, orderStatusForm.value.status);
        notify.success('Order status updated successfully');
        closeOrderView();
    } catch (error) {
        notify.error('Failed to update order status');
    }
};

const deleteOrder = async (id) => {
    if (confirm('Are you sure you want to delete this order?')) {
        try {
            await adminStore.deleteOrder(id);
            notify.success('Order deleted successfully');
        } catch (error) {
            notify.error('Failed to delete order');
        }
    }
};

// Utility Functions
const getOrderProducts = (order) => {
    if (!order?.items || order.items.length === 0) return 'N/A';
    return order.items.map(item => item.product?.name || 'Unknown').join(', ');
};

const getTotalQuantity = (order) => {
    if (!order?.items) return 0;
    return order.items.reduce((sum, item) => sum + item.quantity, 0);
};

const formatStatus = (status) => {
    const statusMap = {
        'pending': 'Pending',
        'for_delivery': 'For Delivery',
        'delivered': 'Delivered',
        'canceled': 'Canceled'
    };
    return statusMap[status] || status;
};

const handleLogout = async () => {
    await authStore.logout();
    notify.success('Logged out successfully');
    router.push('/home');
};
</script>

<style scoped>
@import '../../../css/admin.css';
@import '../../../css/modals.css';
</style>