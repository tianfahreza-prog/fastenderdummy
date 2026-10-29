<?php
/**
 * Fastender Pre-Order Platform
 * Footer Component (includes/footer.php)
 */
?>
<!-- ==========================================
     GLOBAL COMPONENT: FOOTER (Sisi User)
     ========================================== -->
<footer class="footer">
  <div class="footer-top">
    <div class="hero-split-container">
      <div class="footer-grid">
        
        <!-- Kolom 1: Tentang Kami -->
        <div class="footer-col">
          <div class="footer-brand" style="margin-bottom: 16px; display: inline-flex; align-items: center; gap: 10px;">
            <img src="assets/images/logo.png" alt="Fastender Logo" class="brand-logo-img" style="width: 36px; height: 36px; border-color: rgba(255, 255, 255, 0.35);">
            <span class="brand-text" style="color: #ffffff; font-size: 20px;">Fast<span style="color: #ffffff; opacity: 0.9;">ender</span></span>
          </div>
          <p class="footer-desc">
            Fastender adalah platform e-commerce pre-order terintegrasi khusus untuk pembuatan apparel angkatan, jaket varsity, kemeja PDH organisasi, dan merchandise kampus dengan standar mutu konveksi presisi di Indonesia.
          </p>
        </div>

        <!-- Kolom 2: Tautan Cepat -->
        <div class="footer-col">
          <h4>tautan cepat</h4>
          <ul class="footer-links">
            <li><a href="index.php">beranda</a></li>
            <li><a href="katalog.php">katalog produk po</a></li>
             <li><a href="pesanan-saya.php">pesanan saya</a></li>
            <li><a href="keranjang.php">keranjang belanja</a></li>
            <li><a href="checkout.php">formulir pemesanan po</a></li>
          </ul>
        </div>

        <!-- Kolom 3: Kontak & Workshop -->
        <div class="footer-col">
          <h4>kontak &amp; workshop</h4>
          <div class="footer-contact-item">
            <span>Jl. Industri Konveksi Kreatif No. 88, Kampus Terpadu, Indonesia</span>
          </div>
          <?php
            $footerStoreSettings = function_exists('getStoreSettings') ? getStoreSettings() : [];
            $footerWaNumber = $footerStoreSettings['nomor_whatsapp'] ?? '081234567890';
            $footerWaLink = function_exists('formatWhatsAppUrl') ? formatWhatsAppUrl($footerWaNumber, 'Halo CS Fastender, saya ingin bertanya tentang pre-order apparel.') : 'https://wa.me/6281234567890';
          ?>
          <div class="footer-contact-item">
            <span>WhatsApp: <a href="<?= htmlspecialchars($footerWaLink) ?>" target="_blank" style="color: #ffffff; text-decoration: underline; font-weight: 600;"><?= htmlspecialchars($footerWaNumber) ?></a> (CS Pre-Order)</span>
          </div>
          <div class="footer-contact-item">
            <span>Email: support@fastender.id</span>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Baris Hak Cipta di Paling Bawah -->
  <div class="footer-bottom">
    <div class="hero-split-container">
      <div>&copy; 2026 Fastender pre-order platform. hak cipta dilindungi undang-undang.</div>
      <div>monolithic plinth surfaces &bull; electric cobalt spark &bull; sculptural apparel</div>
    </div>
  </div>
</footer>

<!-- Data Katalog Server untuk Sinkronisasi Keranjang & Checkout -->
<script>
  window.SERVER_PRODUCTS_CATALOG = <?= json_encode(function_exists('getProductsList') ? getProductsList() : []) ?>;
</script>
<!-- Swiper Slider JS & Shared JavaScript -->
<script src="js/swiper-bundle.min.js"></script>
<script src="js/script.js?v=<?= file_exists(__DIR__ . '/../js/script.js') ? filemtime(__DIR__ . '/../js/script.js') : '1' ?>"></script>
</body>
</html>
