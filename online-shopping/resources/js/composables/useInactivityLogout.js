import { onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import { useRouter } from 'vue-router';

export function useInactivityLogout() {
    const authStore = useAuthStore();
    const router = useRouter();
    const INACTIVITY_TIMEOUT = 30 * 60 * 1000; // 30 minutes in milliseconds
    let inactivityTimer = null;

    const resetTimer = () => {
        // Clear existing timer
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
        }

        // Only set timer if user is authenticated
        if (authStore.isAuthenticated) {
            inactivityTimer = setTimeout(async () => {
                // Auto logout
                await authStore.logout();
                router.push('/login');
                
                // Show notification if inject is available
                if (window.notify) {
                    window.notify.warning('You have been logged out due to inactivity');
                }
            }, INACTIVITY_TIMEOUT);
        }
    };

    const clearTimer = () => {
        if (inactivityTimer) {
            clearTimeout(inactivityTimer);
            inactivityTimer = null;
        }
    };

    const setupListeners = () => {
        // Events that indicate user activity
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, resetTimer, true);
        });

        // Initialize timer
        resetTimer();
    };

    const removeListeners = () => {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.removeEventListener(event, resetTimer, true);
        });

        clearTimer();
    };

    onMounted(() => {
        if (authStore.isAuthenticated) {
            setupListeners();
        }
    });

    onUnmounted(() => {
        removeListeners();
    });

    return {
        resetTimer,
        clearTimer,
        setupListeners,
        removeListeners
    };
}
