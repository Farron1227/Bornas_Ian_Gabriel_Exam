<template>
    <NotificationComponent ref="notificationRef" />
    <router-view />
</template>

<script setup>
import { ref, provide, onMounted } from 'vue';
import NotificationComponent from './NotificationComponent.vue';
import { useAuthStore } from '../stores/authStore';

const notificationRef = ref(null);
const authStore = useAuthStore();

// Provide notification methods globally
provide('notify', {
    success: (message, duration) => notificationRef.value?.success(message, duration),
    error: (message, duration) => notificationRef.value?.error(message, duration),
});

// Fetch user on app mount if token exists
onMounted(() => {
    if (authStore.token) {
        authStore.fetchUser();
    }
});
</script>