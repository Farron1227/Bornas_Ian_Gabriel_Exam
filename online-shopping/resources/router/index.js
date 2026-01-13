import {createRouter, createWebHistory} from 'vue-router';
import { useAuthStore } from '../js/stores/authStore';
import adminDashboard from '../js/components/admin/AdminDashboard.vue';
import loginIndex from '../js/components/auth/Login.vue';
import signupIndex from '../js/components/auth/SignUp.vue';
import products from '../js/components/product/Products.vue';

import notFound from '../js/components/notFound.vue';


const routes = [
    {
        path:'/',
        redirect: '/home'
    },
    {
        path:'/login',
        name:'auth.login',
        component: loginIndex,
        meta: { guest: true }
    },
    {
        path:'/signup',
        name:'auth.signup',
        component: signupIndex,
        meta: { guest: true }
    },
    {
        path:'/home',
        name:'product.products',  //landing page
        component: products,
        meta: { requiresAuth: true }
    },
    {
        path:'/admin',
        name:'admin.dashboard',
        component: adminDashboard,
        meta: { requiresAuth: true, requiresAdmin: true }
    },
    {
        path:'/:pathMatch(.*)*',
        name:'not-found',
        component: notFound
    } 
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // Wait for user to be fetched if token exists but user is not loaded
    if (authStore.token && !authStore.user) {
        try {
            await authStore.fetchUser();
        } catch (error) {
            // If fetch fails, clear auth and redirect to login
            await authStore.logout();
            if (to.meta.requiresAuth) {
                next('/login');
                return;
            }
        }
    }
    
    // Check if route requires authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
        return;
    }
    
    // Check if route requires admin role
    if (to.meta.requiresAdmin) {
        if (!authStore.isAuthenticated) {
            next('/login');
            return;
        }
        
        // Only admin role can access admin routes
        if (!authStore.isAdmin) {
            // Guest users trying to access admin routes should be redirected to home
            next('/home');
            return;
        }
    }
    
    // Redirect authenticated users away from guest pages (login/signup)
    if (to.meta.guest && authStore.isAuthenticated) {
        // Admin users go to admin dashboard
        if (authStore.isAdmin) {
            next('/admin');
        } else {
            // Guest/customer users go to home
            next('/home');
        }
        return;
    }
    
    next();
});

export default router;