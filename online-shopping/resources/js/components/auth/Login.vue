<template>
    <div class="login-page">
        <div class="login-card">
            <div class="logo-container">
                <img :src="logo" alt="PurpleBug" />
            </div>

            <form @submit.prevent="handleLogin" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-with-icon">
                        <span class="icon">@</span>
                        <input 
                            id="email"
                            type="email" 
                            v-model="formData.email" 
                            placeholder="Enter your email"
                            required 
                        />
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <span class="icon">🔒</span>
                        <input 
                            id="password"
                            type="password" 
                            v-model="formData.password" 
                            placeholder="Enter your password"
                            required 
                        />
                    </div>
                </div>

                <button type="submit" class="login-button" :disabled="loading">
                    {{ loading ? 'Logging in...' : 'LOGIN' }}
                </button>

                <p class="signup-link">
                    Don't have an account?
                    <router-link to="/signup">Sign Up</router-link>
                </p>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/authStore';
import logo from '../../../images/purplebug-logo.png';

const router = useRouter();
const authStore = useAuthStore();
const notify = inject('notify');

const formData = ref({
    email: '',
    password: '',
});

const loading = ref(false);

const handleLogin = async () => {
    loading.value = true;
    try {
        await authStore.login(formData.value);
        notify.success('Login successful!');
        
        // Redirect based on role
        if (authStore.isAdmin) {
            router.push('/admin');
        } else {
            router.push('/home');
        }
    } catch (error) {
        notify.error(error.response?.data?.message || 'Login failed. Please check your credentials.');
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
@import '../../../css/login.css';
</style>
