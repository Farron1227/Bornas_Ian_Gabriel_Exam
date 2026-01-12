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
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();
    
    // Check if route requires authentication
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
        return;
    }
    
    // Check if route requires admin
    if (to.meta.requiresAdmin && !authStore.isAdmin) {
        next('/home');
        return;
    }
    
    // Redirect authenticated users away from guest pages
    if (to.meta.guest && authStore.isAuthenticated) {
        if (authStore.isAdmin) {
            next('/admin');
        } else {
            next('/home');
        }
        return;
    }
    
    next();
});

export default router;