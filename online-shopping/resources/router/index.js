import {createRouter, createWebHistory} from 'vue-router';
import productIndex from '../js/components/product/Index.vue';
import notFound from '../js/components/notFound.vue';


const routes = [
    {
        path:'/',
        name:'product.index',
        component: productIndex
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