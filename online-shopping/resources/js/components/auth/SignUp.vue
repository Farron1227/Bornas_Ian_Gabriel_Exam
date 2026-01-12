<template>
    <div class="signup-page">
        <div class="signup-container">
            <div class="signup-card">
                <div class="logo-container">
                    <h1 class="logo-text">PurpleBug®</h1>
                </div>
                
                <form @submit.prevent="handleSignup" class="signup-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <div class="input-with-icon">
                                <span class="icon">👤</span>
                                <input 
                                    id="name"
                                    type="text" 
                                    v-model="formData.name"
                                    placeholder="Enter your full name"
                                    required 
                                />
                            </div>
                        </div>

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
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <span class="icon">🔒</span>
                                <input 
                                    id="password"
                                    type="password" 
                                    v-model="formData.password"
                                    placeholder="Create a password"
                                    required 
                                    minlength="8"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <div class="input-with-icon">
                                <span class="icon">🔒</span>
                                <input 
                                    id="password_confirmation"
                                    type="password" 
                                    v-model="formData.password_confirmation"
                                    placeholder="Confirm your password"
                                    required 
                                    minlength="8"
                                />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="signup-button" :disabled="loading">
                        {{ loading ? 'Creating Account...' : 'REGISTER' }}
                    </button>
                </form>

                <div class="login-link">
                    Already have an account? 
                    <router-link to="/login">Login here</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, inject } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/authStore';

const router = useRouter();
const authStore = useAuthStore();
const notify = inject('notify');

const formData = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);

const handleSignup = async () => {
    if (formData.value.password !== formData.value.password_confirmation) {
        notify.error('Passwords do not match');
        return;
    }

    loading.value = true;
    try {
        await authStore.register(formData.value);
        notify.success('Registration successful!');
        router.push('/home');
    } catch (error) {
        const message = error.response?.data?.message || 'Registration failed. Please try again.';
        notify.error(message);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
@import '../../../css/signup.css';
</style>
