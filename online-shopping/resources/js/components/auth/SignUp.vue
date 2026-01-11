<template>
    <div class="signup-page">
        <div class="signup-container">
            <div class="signup-card">
                <h2>Create Account</h2>
                <p class="subtitle">Join PurpleBug Shop today</p>
                
                <form>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input 
                            type="text" 
                            placeholder="Enter your full name"
                            required 
                        />
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input 
                            type="email" 
                            placeholder="Enter your email"
                            required 
                        />
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input 
                            type="password" 
                            placeholder="Create a password"
                            required 
                        />
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input 
                            type="password" 
                            placeholder="Confirm your password"
                            required 
                        />
                    </div>

                    <button type="submit" class="signup-button">Sign Up</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="/login">Login here</a>
                </div>
            </div>
        </div>
    </div>
</template>