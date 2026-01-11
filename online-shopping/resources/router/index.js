import {createRouter, createWebHistory} from 'vue-router';
import homeIndex from '../js/components/product/Home.vue';
import adminDashboard from '../js/components/product/AdminDashboard.vue';
import loginIndex from '../js/components/auth/Login.vue';
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