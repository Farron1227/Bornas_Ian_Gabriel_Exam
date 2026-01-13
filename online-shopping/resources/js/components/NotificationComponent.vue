<template>
    <teleport to="body">
        <transition-group name="notification" tag="div" class="notification-container">
            <div 
                v-for="notification in notifications" 
                :key="notification.id"
                :class="['notification', `notification-${notification.type}`]"
            >
                <div class="notification-icon">
                    <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-if="notification.type === 'error'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="notification-content">
                    <p class="notification-message">{{ notification.message }}</p>
                </div>
                <button @click="removeNotification(notification.id)" class="notification-close">
                    ×
                </button>
            </div>
        </transition-group>
    </teleport>
</template>

<script setup>
import { ref } from 'vue';

const notifications = ref([]);
let notificationId = 0;

const addNotification = (message, type = 'success', duration = 3000) => {
    const id = notificationId++;
    notifications.value.push({ id, message, type });
    
    if (duration > 0) {
        setTimeout(() => {
            removeNotification(id);
        }, duration);
    }
};

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
        notifications.value.splice(index, 1);
    }
};

// Expose methods for use from other components
defineExpose({
    success: (message, duration) => addNotification(message, 'success', duration),
    error: (message, duration) => addNotification(message, 'error', duration),
});
</script>

<style scoped>
@import '../../css/notifications.css';
</style>
