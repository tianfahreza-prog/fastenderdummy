<?php
/**
 * Fastender Pre-Order Platform
 * Navbar Component (includes/navbar.php)
 */
$activePage = $activePage ?? 'beranda';
?>
<!-- ==========================================
     GLOBAL COMPONENT: ANNOUNCEMENT BAR & NAVBAR (Sisi User)
     ========================================== -->
<!-- Announcement Bar (Top-of-page promotional strip) -->
<div class="announcement-bar">
  <div class="container" style="display: flex; justify-content: center; align-items: center;">
    <span>TENDER PROJECT 2026</span>
  </div>
</div>

<header class="navbar-wrapper">
  <!-- Kiri: Logo + Nama "Fastender" Terpisah di Sebelah Kiri -->
  <a href="index.php" class="navbar-brand">
    <img src="assets/images/logo.png" alt="Fastender Logo" class="brand-logo-img">
    <span class="brand-text">Fast<span>ender</span></span>
  </a>

  <!-- Kanan: Floating Group (Expandable Inline Search + Navbar Pill) -->
  <div class="navbar-floating-group">
    <?php
      $currentSearchVal = trim((string)($_GET['search'] ?? ($_GET['q'] ?? '')));
      $isSearchExpanded = ($currentSearchVal !== '');
    ?>
    <!-- Tombol & Input Search Inline (Melebar ke Kiri di Tempat) -->
    <div class="navbar-search-wrapper" id="navbarSearchWrapper">
      <form action="katalog.php" method="GET" class="navbar-search-expandable <?= $isSearchExpanded ? 'expanded' : '' ?>" id="navbarSearchForm" role="search">
        <button type="button" class="navbar-search-icon-btn" id="navbarSearchBtn" aria-label="Cari Produk" aria-expanded="<?= $isSearchExpanded ? 'true' : 'false' ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </button>
        <input 
          type="text" 
          name="search" 
          id="navbarSearchInput" 
          class="navbar-search-inline-input" 
          placeholder="Cari produk apparel..." 
          value="<?= htmlspecialchars($currentSearchVal) ?>" 
          autocomplete="off"
        >
        <button type="button" class="navbar-search-close-btn" id="navbarSearchCloseBtn" aria-label="Tutup pencarian">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </form>
    </div>

    <!-- Floating Pill Navigasi & Aksi -->
    <div class="navbar-pill">
      <!-- Tengah: Menu Navigasi Pengguna -->
      <nav class="navbar-nav">
        <ul class="navbar-menu">
          <li>
            <a href="index.php" class="navbar-link <?= ($activePage === 'beranda') ? 'active' : '' ?>">
              beranda
            </a>
          </li>
          <li>
            <a href="katalog.php" class="navbar-link <?= ($activePage === 'katalog') ? 'active' : '' ?>">
              katalog
            </a>
          </li>
          <li>
            <a href="lacak.php" class="navbar-link <?= ($activePage === 'lacak') ? 'active' : '' ?>">
              Lacak
            </a>
          </li>
        </ul>
      </nav>

      <!-- Kanan: Keranjang Belanja & Aksi Pelanggan -->
      <div class="navbar-actions">
        <!-- Keranjang Belanja Icon -->
        <a href="keranjang.php" class="cart-btn" aria-label="Keranjang Belanja">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <span class="cart-badge" id="navbar-cart-badge">0</span>
        </a>

        <!-- Tombol Aksi Utama Pelanggan (Pill rounded CTA) -->
        <a href="katalog.php" class="btn btn-accent btn-sm btn-nav-cta">
          mulai po
        </a>

        <!-- Tombol Menu Mobile Hamburger -->
        <button class="nav-toggle-btn" aria-label="Buka Menu Navigasi">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
      </div>

    </div>
  </div>
</header>

<script>
  // Interaksi Expandable Inline Search Bar
  document.addEventListener('DOMContentLoaded', () => {
    const searchWrapper = document.getElementById('navbarSearchWrapper');
    const searchForm = document.getElementById('navbarSearchForm');
    const searchBtn = document.getElementById('navbarSearchBtn');
    const searchInput = document.getElementById('navbarSearchInput');
    const searchCloseBtn = document.getElementById('navbarSearchCloseBtn');

    if (!searchForm || !searchBtn || !searchInput) return;

    function expandSearch() {
      searchForm.classList.add('expanded');
      searchBtn.setAttribute('aria-expanded', 'true');
      setTimeout(() => searchInput.focus(), 50);
    }

    function collapseSearch() {
      searchForm.classList.remove('expanded');
      searchBtn.setAttribute('aria-expanded', 'false');
      searchInput.blur();
    }

    searchBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isExpanded = searchForm.classList.contains('expanded');
      if (!isExpanded) {
        expandSearch();
      } else if (searchInput.value.trim() !== '') {
        searchForm.submit();
      } else {
        searchInput.focus();
      }
    });

    if (searchCloseBtn) {
      searchCloseBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const hadValue = searchInput.value.trim() !== '';
        searchInput.value = '';
        if (hadValue && typeof window.triggerCatalogSearch === 'function') {
          window.triggerCatalogSearch('');
        }
        collapseSearch();
      });
    }

    searchInput.addEventListener('input', (e) => {
      if (typeof window.triggerCatalogSearch === 'function') {
        window.triggerCatalogSearch(e.target.value);
      }
    });

    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (searchInput.value.trim() === '') {
          collapseSearch();
        }
      }
    });

    document.addEventListener('click', (e) => {
      if (searchWrapper && !searchWrapper.contains(e.target)) {
        if (searchInput.value.trim() === '') {
          collapseSearch();
        }
      }
    });
  });
</script>
