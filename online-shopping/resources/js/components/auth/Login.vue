<template>
    <div class="login-container">
        <h2>Login</h2>
        <form @submit.prevent="handleLogin">
            <div>
                <label>Email:</label>
                <input type="email" v-model="email" required />
            </div>
            <div>
                <label>Password:</label>
                <input type="password" v-model="password" required />
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</template>