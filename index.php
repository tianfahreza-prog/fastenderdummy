<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Beranda (index.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Fastender - Pre-Order Apparel & Merchandise Angkatan 2026';
$activePage = 'beranda';

$allProducts = getProductsList();
$hero = getHeroSettings();
$sliderImages = getHeroSliderImages();
$trendingProducts = getTrendingProducts();
$isPoActive = isPreOrderActive();

// Gandakan produk minimal 4x lipat (24 slide) untuk menjamin infinite loop mulus tanpa celah di layar lebar
$displayTrendingProducts = $trendingProducts;
if (count($displayTrendingProducts) > 0) {
    while (count($displayTrendingProducts) < 24) {
        $displayTrendingProducts = array_merge($displayTrendingProducts, $trendingProducts);
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     HERO BANNER (Split-Card Modern: Frosted Glass & Dynamic Carousel)
     ========================================== -->
<section class="hero-split-section">
  <div class="hero-split-container">
    <div class="hero-split-grid">
      
      <!-- KOLOM KIRI: Card Info & Realtime Countdown Timer (Solid #619ae6) -->
      <div class="hero-card-info hero-card-solid">
        <div class="hero-card-info-inner">
          
          <!-- Judul Utama Tebal & Tegas -->
          <h1 class="hero-card-title">
            <?= nl2br(htmlspecialchars($hero['title'] ?? 'Pre-Order Batch 1 telah dibuka')) ?>
          </h1>

          <!-- Komponen Realtime Countdown Timer -->
          <div class="hero-countdown-wrapper">
            <div class="hero-countdown-label" id="hero-countdown-label">
              <?= !$isPoActive ? 'PRE-ORDER BATCH INI TELAH DITUTUP' : htmlspecialchars($hero['countdown_label'] ?? 'PO DITUTUP DALAM') ?>
            </div>
            
            <div class="hero-countdown-timer <?= !$isPoActive ? 'countdown-closed' : '' ?>" id="hero-countdown" data-deadline="<?= htmlspecialchars($hero['countdown_deadline'] ?? '') ?>" data-is-closed="<?= !$isPoActive ? '1' : '0' ?>">
              <!-- Kotak DAYS -->
              <div class="countdown-card">
                <span class="countdown-value" id="cd-days">00</span>
                <span class="countdown-unit">DAYS</span>
              </div>
              <div class="countdown-colon">:</div>
              <!-- Kotak HOURS -->
              <div class="countdown-card">
                <span class="countdown-value" id="cd-hours">00</span>
                <span class="countdown-unit">HOURS</span>
              </div>
              <div class="countdown-colon">:</div>
              <!-- Kotak MINS -->
              <div class="countdown-card">
                <span class="countdown-value" id="cd-mins">00</span>
                <span class="countdown-unit">MINS</span>
              </div>
              <div class="countdown-colon">:</div>
              <!-- Kotak SECS -->
              <div class="countdown-card">
                <span class="countdown-value" id="cd-secs">00</span>
                <span class="countdown-unit">SECS</span>
              </div>
            </div>
          </div>

          <!-- Tombol CTA Sudut Bawah -->
          <div class="hero-card-actions">
            <a href="<?= htmlspecialchars($hero['cta_link'] ?? 'katalog.php') ?>" class="btn btn-hero-cta">
              <?= htmlspecialchars($hero['cta_text'] ?? 'Pesan Sekarang') ?>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </a>
            <a href="pesanan-saya.php" class="btn btn-hero-secondary">
              Lacak Pesanan Saya
            </a>
          </div>

        </div>
      </div>

      <!-- KOLOM KANAN: Card Image Slider / Carousel Otomatis (3 Gambar assets/images/) -->
      <div class="hero-card-slider" id="hero-slider" aria-label="Showcase Produk Fastender Slider">
        <div class="slider-track" id="slider-track">
          <?php foreach ($sliderImages as $idx => $imgPath): ?>
            <div class="slider-slide <?= ($idx === 0) ? 'active' : '' ?>" data-index="<?= $idx ?>">
              <img 
                src="<?= htmlspecialchars($imgPath) ?>" 
                alt="Showcase Produk Fastender <?= $idx + 1 ?>" 
                class="slider-img"
                loading="<?= ($idx === 0) ? 'eager' : 'lazy' ?>"
              >
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Indikator Dots -->
        <div class="slider-controls">
          <div class="slider-dots" id="slider-dots">
            <?php foreach ($sliderImages as $idx => $imgPath): ?>
              <button type="button" class="slider-dot <?= ($idx === 0) ? 'active' : '' ?>" data-slide="<?= $idx ?>" aria-label="Lihat slide <?= $idx + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Panah Navigasi Minimalis -->
        <button type="button" class="slider-arrow slider-arrow-prev" id="slider-prev" aria-label="Gambar Sebelumnya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <button type="button" class="slider-arrow slider-arrow-next" id="slider-next" aria-label="Gambar Berikutnya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>

    </div>
  </div>
</section>

<!-- ==========================================
     PRODUK TERPOPULER 2026 (INFINITE SWIPER CAROUSEL)
     ========================================== -->
<section class="trending-section" id="trending-section">
  <div class="hero-split-container">

    <!-- Header Section: Judul "Terpopuler 2026" (Kiri) & Tombol Navigasi (Kanan) -->
    <div class="trending-header">
      <h2 class="trending-title">Terpopuler 2026</h2>

      <!-- Tombol Navigasi Panah Kiri (<) dan Kanan (>) -->
      <div class="trending-nav-controls">
        <button type="button" class="trending-nav-btn btn-prev-terpopuler" id="trending-prev" aria-label="Geser ke produk sebelumnya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <button type="button" class="trending-nav-btn btn-next-terpopuler" id="trending-next" aria-label="Geser ke produk berikutnya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>

    <!-- Swiper Carousel Container (Infinite Scroll Looping) -->
    <div class="swiper trending-swiper" id="trending-swiper" data-original-count="<?= count($trendingProducts) ?>">
      <div class="swiper-wrapper">
        <?php foreach ($displayTrendingProducts as $tProd): ?>
          <div class="swiper-slide trending-slide">
            <a href="<?= htmlspecialchars($tProd['link']) ?>" class="trending-card" title="<?= htmlspecialchars($tProd['name']) ?>">
              <div class="trending-card-img-wrap">
                <img 
                  src="<?= htmlspecialchars($tProd['image']) ?>" 
                  alt="<?= htmlspecialchars($tProd['name']) ?>" 
                  class="trending-card-img" 
                  loading="eager" 
                  decoding="async"
                  draggable="false"
                >
              </div>
              <div class="trending-card-divider"></div>
              <div class="trending-card-footer">
                <span class="trending-card-name">
                  <?= htmlspecialchars($tProd['name']) ?>
                </span>
                <span class="trending-card-price">
                  <?= formatRupiah($tProd['price']) ?>
                </span>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</section>

<!-- ==========================================
     ALUR PRE-ORDER (HOW IT WORKS - VISUAL STEP CARDS)
     ========================================== -->
<section class="po-steps-section" id="po-steps-section">
  <div class="hero-split-container">
    
    <!-- Header Section: Judul "Alur Pre-Order" (Sejajar dengan Hero Section & Terpopuler) -->
    <div class="po-steps-header">
      <h2 class="po-steps-title">Alur Pre-Order</h2>
    </div>

    <!-- 4 Kolom Grid Card Alur PO dengan Visual Gambar -->
    <div class="po-steps-grid">
      <!-- Step 01 -->
      <div class="step-card">
        <div class="step-card-header">
          <span class="step-num">01</span>
          <span class="step-badge-label">LANGKAH 1</span>
        </div>
        <div class="alur-po-img-wrap">
          <img src="assets/gambar alur po/2.png" alt="Pilih Produk &amp; Varian" class="alur-po-img" loading="lazy">
        </div>
        <div class="step-content">
          <h3 class="step-title">Pilih Produk &amp; Varian</h3>
          <p class="step-desc">Tentukan model dan Ukuran yang sesuai.</p>
        </div>
      </div>

      <!-- Step 02 -->
      <div class="step-card">
        <div class="step-card-header">
          <span class="step-num">02</span>
          <span class="step-badge-label">LANGKAH 2</span>
        </div>
        <div class="alur-po-img-wrap">
          <img src="assets/gambar alur po/3.png" alt="Checkout &amp; Bayar DP" class="alur-po-img" loading="lazy">
        </div>
        <div class="step-content">
          <h3 class="step-title">Checkout &amp; Bayar DP</h3>
          <p class="step-desc">Pilih skema pembayaran DP 50% atau lunas melalui transfer bank, QRIS, maupun dompet digital.</p>
        </div>
      </div>

      <!-- Step 03 -->
      <div class="step-card">
        <div class="step-card-header">
          <span class="step-num">03</span>
          <span class="step-badge-label">LANGKAH 3</span>
        </div>
        <div class="alur-po-img-wrap">
          <img src="assets/gambar alur po/4.png" alt="Produksi &amp; Pantau Status" class="alur-po-img" loading="lazy">
        </div>
        <div class="step-content">
          <h3 class="step-title">Produksi &amp; Pantau Status</h3>
          <p class="step-desc">Vendor mulai proses jahit dan bordir. Kamu dapat melacak nomor pesananmu secara real-time kapan saja.</p>
        </div>
      </div>

      <!-- Step 04 -->
      <div class="step-card">
        <div class="step-card-header">
          <span class="step-num">04</span>
          <span class="step-badge-label">LANGKAH 4</span>
        </div>
        <div class="alur-po-img-wrap">
          <img src="assets/gambar alur po/5.png" alt="Pengiriman Sampai Lokasi" class="alur-po-img" loading="lazy">
        </div>
        <div class="step-content">
          <h3 class="step-title">Pengiriman Sampai Lokasi</h3>
          <p class="step-desc">Setelah lolos quality check, barang langsung dikirim ke alamat rumah atau dapat diambil di sekretariat UKM Usaha Mahasiswa.</p>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ==========================================
     FLOATING ACTION BUTTON (WHATSAPP CHAT - ICON ONLY)
     ========================================== -->
<a href="<?= htmlspecialchars(formatWhatsAppUrl(null, 'Halo Fastender, saya ingin konsultasi PO Angkatan')) ?>" 
   target="_blank" 
   rel="noopener noreferrer" 
   class="fab-whatsapp" 
   aria-label="Konsultasi via WhatsApp"
   title="Konsultasi via WhatsApp">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 0 0 1.529 5.309L2 22l4.831-1.499a9.97 9.97 0 0 0 5.18 1.483h.004c5.505 0 9.988-4.478 9.989-9.984 0-2.669-1.039-5.176-2.926-7.062A9.92 9.92 0 0 0 12.012 2zm5.834 14.168c-.244.688-1.229 1.309-1.795 1.393-.54.08-1.242.114-3.567-.852-2.73-1.135-4.485-3.921-4.622-4.103-.133-.182-1.1-1.464-1.1-2.793 0-1.328.697-1.98.945-2.247.247-.267.54-.334.72-.334.18 0 .36.002.518.01.168.008.393-.064.615.47.227.549.774 1.889.841 2.027.067.137.112.3.022.48-.09.18-.135.293-.27.45-.135.158-.283.351-.405.472-.135.135-.276.282-.119.551.157.27.7 1.152 1.503 1.868 1.033.92 1.905 1.205 2.175 1.34.27.135.428.113.585-.068.158-.18.675-.788.855-1.058.18-.27.36-.225.608-.135.247.09 1.573.742 1.843.877.27.135.45.202.517.315.067.112.067.652-.177 1.34z"/>
  </svg>
</a>

<?php include __DIR__ . '/includes/footer.php'; ?>
