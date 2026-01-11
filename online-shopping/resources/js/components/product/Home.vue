<template>
    <div class="home-container">
        <!-- Header/Navigation -->
        <header class="header">
            <div class="container">
                <div class="nav-wrapper">
                    <h1 class="logo">PurpleBug Shop</h1>
                    <nav class="nav">
                        <a href="/">Home</a>
                        <a href="#products">Products</a>
                        <a href="#about">About</a>
                        <a href="/login">Login</a>
                    </nav>
                    <div class="cart-icon">
                        🛒 <span class="cart-count">0</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h2>Welcome to PurpleBug Online Shopping</h2>
                <p>Discover amazing products at great prices</p>
                <button class="cta-button">Shop Now</button>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="products-section" id="products">
            <div class="container">
                <h3>Featured Products</h3>
                <div class="products-grid">
                    <div class="product-card" v-for="n in 6" :key="n">
                        <div class="product-image">📦</div>
                        <h4>Product {{ n }}</h4>
                        <p class="price">${{ (n * 10).toFixed(2) }}</p>
                        <button class="add-to-cart">Add to Cart</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p>&copy; 2026 PurpleBug Shop. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>