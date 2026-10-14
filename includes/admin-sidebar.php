<?php
/**
 * Fastender Pre-Order Platform
 * Admin Sidebar Component (includes/admin-sidebar.php)
 */
$adminPage = $adminPage ?? 'dashboard';
$sidebarOrderCount = function_exists('getAllOrdersList') ? count(getAllOrdersList()) : 0;
?>
<!-- ==========================================
     SIDEBAR NAVIGASI VERTIKAL (Sisi Kiri Admin)
     ========================================== -->
<aside class="admin-sidebar" id="admin-sidebar">
  
  <!-- Sidebar Header / Brand Logo -->
  <div class="sidebar-header">
    <a href="index.php" class="sidebar-brand">
      <img src="assets/images/logo.png" alt="Fastender logo" class="brand-logo-img">
      <span class="brand-text" style="font-family: 'Montserrat', var(--font-roobert), sans-serif; font-weight: 700; text-transform: none; letter-spacing: -0.04em;">Fast<span>ender</span></span>
      <span class="admin-badge">ADMIN</span>
    </a>
  </div>

  <!-- Menu Navigasi -->
  <nav class="sidebar-nav">
    <div class="nav-category">Utama</div>
    
    <!-- Menu 1: Dashboard -->
    <a href="admin-dashboard.php" class="sidebar-link <?= ($adminPage === 'dashboard') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="3" width="7" height="7"></rect>
          <rect x="14" y="3" width="7" height="7"></rect>
          <rect x="14" y="14" width="7" height="7"></rect>
          <rect x="3" y="14" width="7" height="7"></rect>
        </svg>
        <span>Dashboard</span>
      </div>
    </a>

    <!-- Menu 2: Pesanan PO -->
    <a href="admin-pesanan.php" class="sidebar-link <?= ($adminPage === 'pesanan') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <path d="M16 10a4 4 0 0 1-8 0"></path>
        </svg>
        <span>Pesanan PO</span>
      </div>
      <span class="sidebar-counter" id="sidebar-order-count"><?= $sidebarOrderCount ?></span>
    </a>

    <!-- Menu 3: Input Produk PO -->
    <a href="admin-input-produk.php" class="sidebar-link <?= ($adminPage === 'input-produk') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Input Produk PO</span>
      </div>
    </a>

    <!-- Menu 4: Pengaturan Hero & Countdown PO -->
    <a href="admin-hero.php" class="sidebar-link <?= ($adminPage === 'hero') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        <span>Pengaturan Hero PO</span>
      </div>
      <span class="badge badge-accent" style="font-size: 10px; padding: 2px 6px;">LIVE</span>
    </a>

    <!-- Menu 5: Pengaturan Toko -->
    <a href="admin-pengaturan.php" class="sidebar-link <?= ($adminPage === 'pengaturan') ? 'active' : '' ?>">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="12" cy="12" r="3"></circle>
          <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
        </svg>
        <span>Pengaturan Toko</span>
      </div>
    </a>

    <div class="nav-category">Tampilan Pengguna</div>

    <!-- Tautan Langsung ke Halaman User -->
    <a href="katalog.php" target="_blank" class="sidebar-link">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
          <line x1="7" y1="7" x2="7.01" y2="7"></line>
        </svg>
        <span>Lihat Hasil Katalog</span>
      </div>
      <span style="font-size: 11px; color: var(--color-ash, #737780);">↗</span>
    </a>

    <a href="index.php" target="_blank" class="sidebar-link">
      <div class="sidebar-link-content">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span>Lihat Halaman Beranda</span>
      </div>
      <span style="font-size: 11px; color: var(--color-ash, #737780);">↗</span>
    </a>
  </nav>

  <!-- Sidebar Footer: Toko & Logout -->
  <div class="sidebar-footer">
    <a href="index.php" class="return-shop-btn" style="margin-bottom: 8px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
        <polyline points="15 3 21 3 21 9"></polyline>
        <line x1="10" y1="14" x2="21" y2="3"></line>
      </svg>
      Lihat Toko (User)
    </a>
    <a href="logout.php" class="logout-link-btn" onclick="return confirm('Apakah Anda yakin ingin keluar dari sesi Admin?')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
        <polyline points="16 17 21 12 16 7"></polyline>
        <line x1="21" y1="12" x2="9" y2="12"></line>
      </svg>
      Keluar / Logout
    </a>
  </div>

</aside>
