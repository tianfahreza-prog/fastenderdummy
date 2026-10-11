<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Detail Produk PO (detail.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$prodId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$product = getProductById($prodId);

if (!$product) {
    $all = getProductsList();
    $product = !empty($all) ? $all[0] : null;
}

if (!$product) {
    header('Location: katalog.php');
    exit;
}

$pageTitle = $product['name'] . ' - Fastender Pre-Order';
$activePage = 'katalog';

$hasRealImages = !empty($product['images']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     DETAIL PRODUK 2 KOLOM (Gambar Besar & Rincian)
     ========================================== -->
<section class="section-padding" style="padding-top: 28px;">
  <div class="container">
    <div class="detail-layout">

      <!-- KOLOM KIRI: GAMBAR PRODUK BESAR & THUMBNAILS -->
      <div class="detail-gallery">
        <div class="detail-main-img" id="detail-image-container">
          <?php if ($hasRealImages): ?>
            <img id="detail-main-photo" src="<?= htmlspecialchars($product['images'][0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          <?php else: ?>
            <div class="detail-svg-placeholder">
              <?= getProductSvgPhp($product['icon_type'] ?? 'varsity') ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Thumbnails Selector -->
        <div class="detail-thumbs" id="detail-thumbs-container">
          <?php if ($hasRealImages): ?>
            <?php foreach ($product['images'] as $idx => $imgSrc): ?>
              <button type="button" class="detail-thumb-btn <?= ($idx === 0) ? 'active' : '' ?>" onclick="switchDetailPhoto(<?= $idx ?>)" title="Foto <?= $idx + 1 ?>">
                <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Thumbnail <?= $idx + 1 ?>">
              </button>
            <?php endforeach; ?>
          <?php else: ?>
            <button type="button" class="detail-thumb-btn active" onclick="switchThumb(0)">
              <div class="thumb-label">Tampak Depan</div>
            </button>
            <button type="button" class="detail-thumb-btn" onclick="switchThumb(1)">
              <div class="thumb-label">Tampak Belakang</div>
            </button>
            <button type="button" class="detail-thumb-btn" onclick="switchThumb(2)">
              <div class="thumb-label">Detail Bordir</div>
            </button>
            <button type="button" class="detail-thumb-btn" onclick="switchThumb(3)">
              <div class="thumb-label">Tekstur Bahan</div>
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- KOLOM KANAN: RINCIAN PRODUK & AKSI PEMESANAN (ALUR SEJAJAR RAPI) -->
      <div class="detail-info">
        
        <!-- 1. Nama Produk & Badge -->
        <div class="detail-header-group">
          <div class="detail-badges-row">
            <span class="badge badge-primary"><?= htmlspecialchars($product['category_name'] ?? $product['category']) ?></span>
            <span class="badge badge-accent"><?= htmlspecialchars($product['badge'] ?? 'BATCH AKTIF') ?></span>
          </div>
          <h1 class="detail-title"><?= htmlspecialchars($product['name']) ?></h1>
        </div>

        <!-- 2. Harga -->
        <div class="detail-price-box">
          <div>
            <div class="detail-price"><?= formatRupiah($product['price']) ?></div>
            <div class="detail-price-dp">Bisa Bayar DP 50%: <?= formatRupiah($product['dp_price']) ?></div>
          </div>
          <div class="detail-tax-label">
            Harga termasuk PPN
          </div>
        </div>

        <!-- 3. Deskripsi Singkat -->
        <div class="detail-short-desc">
          <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <!-- 4. Pilih Ukuran -->
        <div class="option-group">
          <div class="option-label">
            <span>Pilih Ukuran</span>
          </div>
          <div id="size-chips-container" class="size-selector">
            <?php foreach ($product['sizes'] as $idx => $s): ?>
              <button type="button" class="size-chip <?= ($idx === 0) ? 'active' : '' ?>" data-size="<?= htmlspecialchars(trim($s)) ?>"><?= htmlspecialchars(trim($s)) ?></button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 5. Jumlah Kuantitas & Total Pembayaran -->
        <div class="option-group detail-qty-row">
          <div class="qty-block">
            <span class="option-sublabel">Jumlah Kuantitas</span>
            <div class="qty-control">
              <button type="button" class="qty-btn" id="btn-decrease-qty" aria-label="Kurang kuantitas">-</button>
              <input type="number" id="detail-qty" class="qty-input" value="1" min="1" max="99" readonly>
              <button type="button" class="qty-btn" id="btn-increase-qty" aria-label="Tambah kuantitas">+</button>
            </div>
          </div>

          <div class="subtotal-block">
            <span class="subtotal-label">Total Pembayaran:</span>
            <span id="detail-subtotal-display" class="subtotal-value">
              <?= formatRupiah($product['price']) ?>
            </span>
          </div>
        </div>

        <!-- 6. Total & Tombol Aksi Pesan/Keranjang -->
        <div class="detail-actions">
          <button type="button" id="buy-now-btn" class="btn btn-primary btn-lg btn-block">
            Pesan Sekarang
          </button>
          <button type="button" id="add-to-cart-btn" class="btn btn-accent btn-lg btn-block">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            Tambah ke Keranjang
          </button>
        </div>

      </div>

    </div>

    <!-- Section Deskripsi & Ketentuan (Judul: Deskripsi) -->
    <div class="detail-description-card">
      <h3 class="detail-desc-title">Deskripsi</h3>
      <div class="detail-desc-grid">
        <div>
          <h4 class="detail-desc-subtitle">Rincian Material:</h4>
          <ul class="detail-desc-list">
            <?php if (!empty($product['specs'])): ?>
              <?php foreach ($product['specs'] as $spec): ?>
                <li><?= htmlspecialchars(trim($spec)) ?></li>
              <?php endforeach; ?>
            <?php else: ?>
              <li>Bahan Cotton Fleece 330gsm Standar Distro</li>
              <li>Bordir Komputer Kerapatan Tinggi</li>
            <?php endif; ?>
          </ul>
        </div>
        <div>
          <h4 class="detail-desc-subtitle">Ketentuan Pre-Order (PO):</h4>
          <ul class="detail-desc-list">
            <li>Produksi massal dimulai setelah kuota batch ditutup.</li>
            <li>Waktu produksi berkisar 14 - 21 hari kerja konveksi.</li>
            <li>Pelunasan sisa 50% dapat dibayar saat pakaian siap kirim.</li>
          </ul>
        </div>
      </div>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
  // Script Interaktif Galeri & Keranjang pada Halaman Detail
  const CURRENT_PRODUCT = {
    id: <?= (int)$product['id'] ?>,
    name: <?= json_encode($product['name']) ?>,
    categoryName: <?= json_encode($product['category_name'] ?? $product['category']) ?>,
    price: <?= (int)$product['price'] ?>,
    dpPrice: <?= (int)$product['dp_price'] ?>,
    iconType: <?= json_encode($product['icon_type'] ?? 'varsity') ?>,
    images: <?= json_encode($product['images'] ?? []) ?>,
    sizes: <?= json_encode($product['sizes'] ?? []) ?>
  };

  let selectedSize = CURRENT_PRODUCT.sizes.length > 0 ? CURRENT_PRODUCT.sizes[0] : "L";
  let quantity = 1;

  document.addEventListener("DOMContentLoaded", () => {
    // Event size chips
    document.querySelectorAll("#size-chips-container .size-chip").forEach(btn => {
      btn.addEventListener("click", () => {
        document.querySelectorAll("#size-chips-container .size-chip").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        selectedSize = btn.getAttribute("data-size");
      });
    });

    // Stepper kuantitas
    const qtyInput = document.getElementById("detail-qty");
    const btnDecrease = document.getElementById("btn-decrease-qty");
    const btnIncrease = document.getElementById("btn-increase-qty");
    const subtotalDisplay = document.getElementById("detail-subtotal-display");

    function updateSubtotal() {
      qtyInput.value = quantity;
      subtotalDisplay.textContent = formatRupiah(CURRENT_PRODUCT.price * quantity);
    }

    if (btnDecrease) {
      btnDecrease.addEventListener("click", () => {
        if (quantity > 1) {
          quantity--;
          updateSubtotal();
        }
      });
    }

    if (btnIncrease) {
      btnIncrease.addEventListener("click", () => {
        if (quantity < 99) {
          quantity++;
          updateSubtotal();
        }
      });
    }

    // Action: Tambah ke Keranjang
    const addBtn = document.getElementById("add-to-cart-btn");
    if (addBtn) {
      addBtn.addEventListener("click", () => {
        addToCart(CURRENT_PRODUCT, quantity, selectedSize, "Standar", "");
      });
    }

    // Action: Pesan Sekarang
    const buyNowBtn = document.getElementById("buy-now-btn");
    if (buyNowBtn) {
      buyNowBtn.addEventListener("click", () => {
        addToCart(CURRENT_PRODUCT, quantity, selectedSize, "Standar", "");
        window.location.href = "checkout.php";
      });
    }
  });

  // Switch gallery thumbnail
  function switchDetailPhoto(idx) {
    const mainImg = document.getElementById("detail-main-photo");
    if (mainImg && CURRENT_PRODUCT.images[idx]) {
      mainImg.src = CURRENT_PRODUCT.images[idx];
    }
    document.querySelectorAll(".detail-thumb-btn").forEach((btn, i) => {
      btn.classList.toggle("active", i === idx);
    });
  }

  function switchThumb(index) {
    document.querySelectorAll(".detail-thumb-btn").forEach((btn, i) => {
      btn.classList.toggle("active", i === index);
    });
    showToast("Tampilan sudut pandang produk diperbarui", "info");
  }
</script>
