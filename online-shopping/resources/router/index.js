import {createRouter, createWebHistory} from 'vue-router';
import homeIndex from '../js/components/product/Home.vue';
import adminDashboard from '../js/components/admin/AdminDashboard.vue';
import loginIndex from '../js/components/auth/Login.vue';
import signupIndex from '../js/components/auth/SignUp.vue';
import about from '../js/components/product/About.vue';
import products from '../js/components/product/Products.vue';

import notFound from '../js/components/notFound.vue';


const routes = [
    {
        path:'/',
        name:'home',
        component: homeIndex
    },
    {
        path:'/login',
        name:'auth.login',
        component: loginIndex
    },
    {
        path:'/signup',
        name:'auth.signup',
        component: signupIndex
    },
    {
        path:'/about',
        name:'product.about',
        component: about
    },
    {
        path:'/products',
        name:'product.products',
        component: products
    },
    {
        path:'/admin',
        name:'product.index',
        component: adminDashboard
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
});

export default router;