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
                    <span class="user-name">Hi, {{ authStore.currentUser?.name?.split(' ')[0] || 'Admin' }}!</span>
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

                <div v-if="productStore.loading" class="loading-state">
                    Loading products...
                </div>

                <div v-else-if="productStore.products.length === 0" class="empty-state">
                    No products found. Click "Add Product" to create one.
                </div>

                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Number of Stocks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in productStore.products" :key="product.id">
                                <td>
                                    <div class="product-thumbnail">
                                        <img v-if="product.image" :src="`/storage/${product.image}`" :alt="product.name" />
                                        <div v-else class="no-image">📦</div>
                                    </div>
                                </td>
                                <td>{{ product.name }}</td>
                                <td>{{ product.category?.name || 'N/A' }}</td>
                                <td>₱ {{ parseFloat(product.price).toFixed(2) }}</td>
                                <td>
                                    <span :class="['stock-badge', product.stock < 10 ? 'low' : 'normal']">
                                        {{ product.stock }}
                                    </span>
                                </td>
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

                <div v-if="adminStore.loading" class="loading-state">
                    Loading orders...
                </div>

                <div v-else-if="adminStore.orders.length === 0" class="empty-state">
                    No orders found.
                </div>

                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Products</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in adminStore.orders" :key="order.id">
                                <td>#{{ order.id }}</td>
                                <td>{{ order.user?.name || 'N/A' }}</td>
                                <td>{{ getOrderProducts(order) }}</td>
                                <td>₱ {{ parseFloat(order.total).toFixed(2) }}</td>
                                <td>
                                    <span :class="['status-badge', order.status]">
                                        {{ formatStatus(order.status) }}
                                    </span>
                                </td>
                                <td>{{ formatDate(order.created_at) }}</td>
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

                <div v-if="adminStore.loading" class="loading-state">
                    Loading users...
                </div>

                <div v-else-if="adminStore.users.length === 0" class="empty-state">
                    No users found.
                </div>

                <div v-else class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in adminStore.users" :key="user.id">
                                <td>{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span :class="['role-badge', user.role]">
                                        {{ user.role === 'admin' ? 'Admin' : 'Customer' }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="['status-badge', user.is_active ? 'active' : 'inactive']">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ formatDate(user.created_at) }}</td>
                                <td class="action-btns">
                                    <button 
                                        @click="toggleUserStatus(user)" 
                                        :class="['toggle-btn', user.is_active ? 'active' : 'inactive']"
                                        :title="user.is_active ? 'Disable Account' : 'Enable Account'"
                                        :disabled="user.id === authStore.currentUser?.id"
                                    >
                                        {{ user.is_active ? '✓' : '✕' }}
                                    </button>
                                    <button @click="openUserForm(user)" class="edit-btn">✏️</button>
                                    <button 
                                        @click="deleteUser(user.id)" 
                                        class="delete-btn"
                                        :disabled="user.id === authStore.currentUser?.id"
                                    >
                                        🗑️
                                    </button>
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
                            <div class="image-preview" :class="{ 'error': productErrors.image }">
                                <img v-if="imagePreview" :src="imagePreview" alt="Preview" />
                                <img v-else :src="cameraPlaceholder" alt="Upload" class="placeholder-img" />
                            </div>
                            <input type="file" @change="handleImageChange" accept="image/*" />
                            <span v-if="productErrors.image" class="error-message">{{ productErrors.image }}</span>
                        </div>
                        <div class="form-fields">
                            <div class="form-group">
                                <label>Product Name</label>
                                <input type="text" v-model="productForm.name" :class="{ 'error': productErrors.name }" />
                                <span v-if="productErrors.name" class="error-message">{{ productErrors.name }}</span>
                            </div>
                            <div class="form-group">
                                <label>Category</label>
                                <select v-model="productForm.category_id" :class="{ 'error': productErrors.category_id }">
                                    <option value="">Select Category</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <span v-if="productErrors.category_id" class="error-message">{{ productErrors.category_id }}</span>
                            </div>
                            <div class="form-group">
                                <label>Price</label>
                                <input type="number" v-model="productForm.price" placeholder="₱" step="0.01" :class="{ 'error': productErrors.price }" />
                                <span v-if="productErrors.price" class="error-message">{{ productErrors.price }}</span>
                            </div>
                            <div class="form-group">
                                <label>Number of Stocks</label>
                                <input type="number" v-model="productForm.stock" :class="{ 'error': productErrors.stock }" />
                                <span v-if="productErrors.stock" class="error-message">{{ productErrors.stock }}</span>
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
                                <input type="text" v-model="userForm.name" :class="{ 'error': userErrors.name }" />
                            </div>
                            <span v-if="userErrors.name" class="error-message">{{ userErrors.name }}</span>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <div class="input-with-icon">
                                <span class="icon">@</span>
                                <input type="email" v-model="userForm.email" :class="{ 'error': userErrors.email }" />
                            </div>
                            <span v-if="userErrors.email" class="error-message">{{ userErrors.email }}</span>
                        </div>
                        <div class="form-group">
                            <label>Password {{ editingUser ? '(Leave blank to keep current)' : '' }}</label>
                            <div class="input-with-icon">
                                <span class="icon">🔒</span>
                                <input type="password" v-model="userForm.password" :class="{ 'error': userErrors.password }" />
                            </div>
                            <span v-if="userErrors.password" class="error-message">{{ userErrors.password }}</span>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <div class="input-with-icon">
                                <span class="icon">🔒</span>
                                <input type="password" v-model="userForm.password_confirmation" :class="{ 'error': userErrors.password_confirmation }" />
                            </div>
                            <span v-if="userErrors.password_confirmation" class="error-message">{{ userErrors.password_confirmation }}</span>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select v-model="userForm.role" required>
                                <option value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select v-model="userForm.is_active" required>
                                <option :value="true">Active</option>
                                <option :value="false">Inactive</option>
                            </select>
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
                        <label>Order ID</label>
                        <input type="text" :value="'#' + (selectedOrder?.id || '')" readonly />
                    </div>
                    <div class="form-group">
                        <label>Customer Name</label>
                        <input type="text" :value="selectedOrder?.user?.name" readonly />
                    </div>
                    <div class="form-group">
                        <label>Customer Email</label>
                        <input type="text" :value="selectedOrder?.user?.email" readonly />
                    </div>
                    <div class="form-group">
                        <label>Products Ordered</label>
                        <textarea :value="getOrderProductsDetailed(selectedOrder)" readonly rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Total Quantity</label>
                        <input type="text" :value="getTotalQuantity(selectedOrder)" readonly />
                    </div>
                    <div class="form-group">
                        <label>Total Amount</label>
                        <input type="text" :value="'₱ ' + parseFloat(selectedOrder?.total || 0).toFixed(2)" readonly />
                    </div>
                    <div class="form-group">
                        <label>Order Date</label>
                        <input type="text" :value="formatDate(selectedOrder?.created_at)" readonly />
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
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="modal-overlay" @click="closeDeleteModal">
            <div class="modal-content delete-modal" @click.stop>
                <div class="delete-icon">⚠️</div>
                <h2>Confirm Deletion</h2>
                <p class="delete-message">{{ deleteModalData.message }}</p>
                <p class="delete-warning">This action cannot be undone.</p>
                <div class="delete-actions">
                    <button @click="confirmDelete" class="confirm-delete-btn">Delete</button>
                    <button @click="closeDeleteModal" class="cancel-delete-btn">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Toggle Status Modal -->
        <div v-if="showToggleModal" class="modal-overlay" @click="closeToggleModal">
            <div class="modal-content toggle-modal" @click.stop>
                <div class="toggle-icon" :class="toggleModalData.newStatus ? 'enable' : 'disable'">
                    {{ toggleModalData.newStatus ? '✓' : '✗' }}
                </div>
                <h2>{{ toggleModalData.newStatus ? 'Enable' : 'Disable' }} Account</h2>
                <p class="toggle-message">
                    Are you sure you want to <strong>{{ toggleModalData.newStatus ? 'enable' : 'disable' }}</strong> 
                    <strong>{{ toggleModalData.userName }}</strong>'s account?
                </p>
                <p class="toggle-info" v-if="!toggleModalData.newStatus">
                    This user will not be able to log in until their account is re-enabled.
                </p>
                <p class="toggle-info" v-else>
                    This user will be able to log in and access their account.
                </p>
                <div class="toggle-actions">
                    <button @click="confirmToggleStatus" :class="['confirm-toggle-btn', toggleModalData.newStatus ? 'enable' : 'disable']">
                        {{ toggleModalData.newStatus ? 'Enable' : 'Disable' }}
                    </button>
                    <button @click="closeToggleModal" class="cancel-toggle-btn">Cancel</button>
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
import { categoryAPI, userAPI } from '../../services/api';
import logo from '../../../images/purplebug-logo.png';
import cameraPlaceholder from '../../../images/camera-placeholder.svg';

const router = useRouter();
const authStore = useAuthStore();
const productStore = useProductStore();
const adminStore = useAdminStore();
const notify = inject('notify');

const currentView = ref('products');
const categories = ref([]);

// Product Form
const showProductForm = ref(false);
const editingProduct = ref(null);
const productForm = ref({
    name: '',
    category_id: '',
    price: '',
    stock: '',
    image: null,
    existingImage: ''
});
const imagePreview = ref('');
const productErrors = ref({});

// User Form
const showUserForm = ref(false);
const editingUser = ref(null);
const userForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'customer',
    is_active: true
});
const userErrors = ref({});

// Order View
const showOrderView = ref(false);
const selectedOrder = ref(null);
const orderStatusForm = ref({
    status: ''
});

// Delete Confirmation Modal
const showDeleteModal = ref(false);
const deleteModalData = ref({
    type: '', // 'product', 'user', or 'order'
    id: null,
    name: '',
    message: ''
});

// Toggle Status Modal
const showToggleModal = ref(false);
const toggleModalData = ref({
    user: null,
    userName: '',
    newStatus: false
});

onMounted(async () => {
    // Verify user is admin before loading data
    if (!authStore.isAdmin) {
        notify.error('Access denied. Admin privileges required.');
        router.push('/home');
        return;
    }

    await productStore.fetchProducts();
    await adminStore.fetchOrders();
    await adminStore.fetchUsers();
    
    // Fetch categories
    try {
        const response = await categoryAPI.getAll();
        categories.value = response.data.data || response.data;
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
});

// Product Functions
const openProductForm = (product = null) => {
    editingProduct.value = product;
    if (product) {
        productForm.value = {
            name: product.name,
            category_id: product.category_id,
            price: product.price,
            stock: product.stock,
            image: null,
            existingImage: product.image || ''
        };
        imagePreview.value = product.image ? `/storage/${product.image}` : '';
    } else {
        productForm.value = { name: '', category_id: '', price: '', stock: '', image: null, existingImage: '' };
        imagePreview.value = '';
    }
    showProductForm.value = true;
};

const closeProductForm = () => {
    showProductForm.value = false;
    editingProduct.value = null;
    imagePreview.value = '';
    productErrors.value = {};
};

const validateProductForm = () => {
    productErrors.value = {};
    
    if (!productForm.value.name || productForm.value.name.trim() === '') {
        productErrors.value.name = 'Product name is required';
    } else if (productForm.value.name.length > 255) {
        productErrors.value.name = 'Product name must not exceed 255 characters';
    }
    
    if (!productForm.value.category_id) {
        productErrors.value.category_id = 'Category is required';
    }
    
    if (!productForm.value.price || productForm.value.price === '') {
        productErrors.value.price = 'Price is required';
    } else if (parseFloat(productForm.value.price) < 0) {
        productErrors.value.price = 'Price must be a positive number';
    } else if (parseFloat(productForm.value.price) === 0) {
        productErrors.value.price = 'Price must be greater than zero';
    }
    
    if (!productForm.value.stock && productForm.value.stock !== 0) {
        productErrors.value.stock = 'Stock quantity is required';
    } else if (parseInt(productForm.value.stock) < 0) {
        productErrors.value.stock = 'Stock must be a positive number';
    } else if (!Number.isInteger(parseFloat(productForm.value.stock))) {
        productErrors.value.stock = 'Stock must be a whole number';
    }
    
    if (!editingProduct.value && !productForm.value.image) {
        productErrors.value.image = 'Product image is required';
    }
    
    return Object.keys(productErrors.value).length === 0;
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
    if (!validateProductForm()) {
        notify.error('Please fix the errors before saving');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('name', productForm.value.name.trim());
        formData.append('category_id', productForm.value.category_id);
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
    const product = productStore.products.find(p => p.id === id);
    const productName = product ? product.name : 'this product';
    
    deleteModalData.value = {
        type: 'product',
        id: id,
        name: productName,
        message: `Are you sure you want to delete "${productName}"?`
    };
    showDeleteModal.value = true;
};

// User Functions
const openUserForm = (user = null) => {
    editingUser.value = user;
    if (user) {
        userForm.value = {
            name: user.name,
            email: user.email,
            password: '',
            password_confirmation: '',
            role: user.role || 'customer',
            is_active: user.is_active
        };
    } else {
        userForm.value = {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            role: 'customer',
            is_active: true
        };
    }
    showUserForm.value = true;
};

const closeUserForm = () => {
    showUserForm.value = false;
    editingUser.value = null;
    userErrors.value = {};
};

const validateUserForm = () => {
    userErrors.value = {};
    
    if (!userForm.value.name || userForm.value.name.trim() === '') {
        userErrors.value.name = 'Full name is required';
    } else if (userForm.value.name.length < 3) {
        userErrors.value.name = 'Full name must be at least 3 characters';
    } else if (userForm.value.name.length > 255) {
        userErrors.value.name = 'Full name must not exceed 255 characters';
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!userForm.value.email || userForm.value.email.trim() === '') {
        userErrors.value.email = 'Email is required';
    } else if (!emailRegex.test(userForm.value.email)) {
        userErrors.value.email = 'Please enter a valid email address';
    }
    
    if (!editingUser.value && !userForm.value.password) {
        userErrors.value.password = 'Password is required for new users';
    } else if (userForm.value.password) {
        if (userForm.value.password.length < 6) {
            userErrors.value.password = 'Password must be at least 6 characters';
        } else if (userForm.value.password !== userForm.value.password_confirmation) {
            userErrors.value.password_confirmation = 'Passwords do not match';
        }
    }
    
    if (!userForm.value.role) {
        userErrors.value.role = 'Role is required';
    }
    
    return Object.keys(userErrors.value).length === 0;
};

const saveUser = async () => {
    if (!validateUserForm()) {
        notify.error('Please fix the errors before saving');
        return;
    }

    try {
        const userData = {
            name: userForm.value.name.trim(),
            email: userForm.value.email.trim(),
            role: userForm.value.role,
            is_active: userForm.value.is_active
        };

        // Only include password if it's provided
        if (userForm.value.password) {
            userData.password = userForm.value.password;
            userData.password_confirmation = userForm.value.password_confirmation;
        }

        if (editingUser.value) {
            await adminStore.updateUser(editingUser.value.id, userData);
            notify.success('User updated successfully');
        } else {
            await adminStore.createUser(userData);
            notify.success('User created successfully');
        }
        closeUserForm();
    } catch (error) {
        notify.error(error.response?.data?.message || 'Failed to save user');
    }
};

const deleteUser = async (id) => {
    const user = adminStore.users.find(u => u.id === id);
    const userName = user ? user.name : 'this user';
    
    deleteModalData.value = {
        type: 'user',
        id: id,
        name: userName,
        message: `Are you sure you want to delete "${userName}"? This will permanently remove the user and all associated data.`
    };
    showDeleteModal.value = true;
};

const toggleUserStatus = (user) => {
    // Prevent toggling own account
    if (user.id === authStore.currentUser?.id) {
        notify({
            type: 'error',
            message: 'You cannot disable your own account'
        });
        return;
    }

    toggleModalData.value = {
        user: user,
        userName: user.name,
        newStatus: !user.is_active
    };
    showToggleModal.value = true;
};

const closeToggleModal = () => {
    showToggleModal.value = false;
    toggleModalData.value = {
        user: null,
        userName: '',
        newStatus: false
    };
};

const confirmToggleStatus = async () => {
    try {
        const user = toggleModalData.value.user;
        const newStatus = toggleModalData.value.newStatus;
        const action = newStatus ? 'enable' : 'disable';

        console.log('Toggling user status:', {
            userId: user.id,
            newStatus: newStatus,
            action: action
        });

        const response = await userAPI.update(user.id, {
            is_active: newStatus
        });

        console.log('Toggle response:', response.data);

        if (response.data.success) {
            await adminStore.fetchUsers();
            notify({
                type: 'success',
                message: `User account ${action}d successfully`
            });
            closeToggleModal();
        } else {
            throw new Error('Update failed');
        }
    } catch (error) {
        console.error('Error toggling user status:', error);
        console.error('Error response:', error.response?.data);
        notify({
            type: 'error',
            message: error.response?.data?.message || 'Failed to update user status'
        });
        closeToggleModal();
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
    if (!orderStatusForm.value.status) {
        notify.error('Please select a status');
        return;
    }
    
    if (orderStatusForm.value.status === selectedOrder.value.status) {
        notify.error('Status is unchanged. Please select a different status.');
        return;
    }
    
    try {
        await adminStore.updateOrderStatus(selectedOrder.value.id, orderStatusForm.value.status);
        notify.success('Order status updated successfully');
        closeOrderView();
    } catch (error) {
        notify.error(error.response?.data?.message || 'Failed to update order status');
    }
};

const deleteOrder = async (id) => {
    const order = adminStore.orders.find(o => o.id === id);
    const orderInfo = order ? `Order #${order.id}` : 'this order';
    
    deleteModalData.value = {
        type: 'order',
        id: id,
        name: orderInfo,
        message: `Are you sure you want to delete ${orderInfo}? This will permanently remove the order from the system.`
    };
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    deleteModalData.value = { type: '', id: null, name: '', message: '' };
};

const confirmDelete = async () => {
    try {
        const { type, id } = deleteModalData.value;
        
        if (type === 'product') {
            await productStore.deleteProduct(id);
            notify.success('Product deleted successfully');
        } else if (type === 'user') {
            await adminStore.deleteUser(id);
            notify.success('User deleted successfully');
        } else if (type === 'order') {
            await adminStore.deleteOrder(id);
            notify.success('Order deleted successfully');
        }
        
        closeDeleteModal();
    } catch (error) {
        notify.error(error.response?.data?.message || `Failed to delete ${deleteModalData.value.type}`);
        closeDeleteModal();
    }
};

// Utility Functions
const getOrderProducts = (order) => {
    if (!order?.items || order.items.length === 0) return 'N/A';
    const products = order.items.map(item => item.product?.name || 'Unknown');
    return products.length > 2 
        ? `${products.slice(0, 2).join(', ')}... (+${products.length - 2} more)` 
        : products.join(', ');
};

const getOrderProductsDetailed = (order) => {
    if (!order?.items || order.items.length === 0) return 'No items';
    return order.items.map(item => 
        `${item.product?.name || 'Unknown'} (Qty: ${item.quantity}, Price: ₱${parseFloat(item.price).toFixed(2)})`
    ).join('\n');
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

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const handleLogout = async () => {
    try {
        await authStore.logout();
        notify.success('Logged out successfully');
        router.push('/login');
    } catch (error) {
        notify.error('Logout failed');
    }
};
</script>

<style scoped>
@import '../../../css/admin.css';
@import '../../../css/modals.css';
</style>