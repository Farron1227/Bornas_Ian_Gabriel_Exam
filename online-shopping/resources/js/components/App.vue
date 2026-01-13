<template>
    <NotificationComponent ref="notificationRef" />
    <router-view />
</template>

<script setup>
import { ref, provide, onMounted } from 'vue';
import NotificationComponent from './NotificationComponent.vue';
import { useAuthStore } from '../stores/authStore';
import { useInactivityLogout } from '../composables/useInactivityLogout';

const notificationRef = ref(null);
const authStore = useAuthStore();

// Initialize inactivity logout
const { setupListeners, removeListeners } = useInactivityLogout();

// Provide notification methods globally
const notify = {
    success: (message, duration) => notificationRef.value?.success(message, duration),
    error: (message, duration) => notificationRef.value?.error(message, duration),
    warning: (message, duration) => notificationRef.value?.error(message, duration), // Using error styling for warnings
};

provide('notify', notify);

// Make notify available globally for the composable
if (typeof window !== 'undefined') {
    window.notify = notify;
}

// Fetch user on app mount if token exists
onMounted(() => {
    if (authStore.token) {
        authStore.fetchUser().then(() => {
            // Setup inactivity listeners after user is authenticated
            if (authStore.isAuthenticated) {
                setupListeners();
            }
        });
    }
});
</script>