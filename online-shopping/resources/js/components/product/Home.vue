<template>
  <div class="pb-page">

    <!-- Top header bar -->
    <header class="pb-header">
      <div class="pb-header-inner">

        <!-- Left: logo -->
        <div class="pb-header-left">
          <img
            :src="logo"
            alt="PurpleBug"
            class="pb-logo"
          />
        </div>

        <!-- Right: guest, cart, auth buttons -->
        <div class="pb-header-right">
          <div class="pb-user">
            <div class="pb-user-avatar"></div>
            <div class="pb-user-text">
              <div class="pb-user-hi">Hi, Guest!</div>
              <div class="pb-user-desc">Description</div>
            </div>
          </div>

          <button class="pb-icon-button pb-cart">
            <span class="pb-cart-icon">🛒</span>
          </button>

          <button class="pb-btn pb-btn-outline">LOGIN</button>
          <button class="pb-btn pb-btn-primary">SIGN UP</button>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="pb-main">

      <!-- Frame title -->
      <section class="pb-frame-section">
        <div class="pb-frame-header">
          <h2 class="pb-frame-title">Frame</h2>
        </div>

        <!-- Search + sort -->
        <div class="pb-toolbar">
          <div class="pb-search-wrapper">
            <input
              type="text"
              placeholder="Search"
              class="pb-search-input"
            />
            <span class="pb-search-icon">🔍</span>
          </div>

          <div class="pb-sort-group">
            <button class="pb-chip pb-chip-active">
              Price ascending
            </button>
            <button class="pb-chip">
              Price descending
            </button>
          </div>
        </div>

        <!-- Products grid -->
        <div class="pb-products-grid">

          <!-- First row: 4 cards -->
          <article
            v-for="n in 4"
            :key="'top-' + n"
            class="pb-card pb-card-vertical"
          >
            <div class="pb-card-image"></div>
            <div class="pb-card-body">
              <div class="pb-card-title">Text</div>
              <div class="pb-card-price">P 0</div>
            </div>
          </article>

          <!-- Second row: 2 wide cards -->
          <article
            v-for="n in 2"
            :key="'bottom-' + n"
            class="pb-card pb-card-horizontal"
          >
            <div class="pb-card-image"></div>
            <div class="pb-card-body">
              <div class="pb-card-title">Text</div>
              <div class="pb-card-price">P 0</div>
            </div>
          </article>
        </div>

        <!-- Pagination -->
        <nav class="pb-pagination">
          <button class="pb-page-link pb-page-prev">← Previous</button>
          <button class="pb-page-link pb-page-number pb-page-current">1</button>
          <button class="pb-page-link pb-page-number">2</button>
          <button class="pb-page-link pb-page-number">3</button>
          <span class="pb-page-ellipsis">…</span>
          <button class="pb-page-link pb-page-number">67</button>
          <button class="pb-page-link pb-page-number">68</button>
          <button class="pb-page-link pb-page-next">Next →</button>
        </nav>
      </section>
    </main>

    <!-- Footer -->
    <footer class="pb-footer">
      <div class="pb-footer-inner">
        <img
          :src="logo"
          alt="PurpleBug"
          class="pb-footer-logo"
        />
        <p class="pb-footer-text">
          Copyright 2025 PurpleBug Inc.
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import logo from '../../../images/purplebug-logo.png';
</script>
