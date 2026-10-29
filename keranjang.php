<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Keranjang Belanja (keranjang.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Keranjang Pesanan Pre-Order - Fastender';
$activePage = 'keranjang';
$isPoActive = isPreOrderActive();
$storeSettings = getStoreSettings();
$statusTokoParam = (isset($_GET['status']) && $_GET['status'] === 'toko_tutup');
$isStoreOpen = isStoreOpen() && !$statusTokoParam;
$fullQuotaProductIds = getFullQuotaProductIds();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     HALAMAN KERANJANG (LAYOUT 2 KOLOM)
     ========================================== -->
<section class="section-padding" style="padding-top: 28px;">
  <div class="container">
    <div style="margin-bottom: 0;">

    <?php if (!$isStoreOpen): ?>
      <!-- Banner / Alert Informasi Penutupan Operasional Toko -->
      <div class="store-closed-alert-banner" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 20px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.08);">
        <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #dc2626; margin-top: 2px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
        </div>
        <div>
          <h4 style="font-size: 15px; font-weight: 700; color: #991b1b; margin: 0 0 4px 0; letter-spacing: -0.02em;">
            Pre-Order Sedang Ditutup Sementara
          </h4>
          <p style="font-size: 13.5px; color: #b91c1c; margin: 0; line-height: 1.55; font-weight: 500;">
            <?= htmlspecialchars(!empty($storeSettings['pesan_toko_tutup']) ? $storeSettings['pesan_toko_tutup'] : 'Pre-Order saat ini sedang ditutup. Anda tetap dapat menyimpan produk di keranjang sambil menunggu sesi pre-order dibuka kembali.') ?>
          </p>
        </div>
      </div>
    <?php elseif (!$isPoActive): ?>
      <!-- Banner / Alert Informasi Penutupan Pre-Order Batch (Countdown Habis) -->
      <div class="po-closed-alert-banner" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 20px; padding: 18px 22px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 14px; box-shadow: 0 4px 14px rgba(234, 88, 12, 0.08);">
        <div style="width: 36px; height: 36px; border-radius: 50%; background: #ffedd5; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #ea580c; margin-top: 2px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
          </svg>
        </div>
        <div>
          <h4 style="font-size: 15px; font-weight: 700; color: #9a3412; margin: 0 0 4px 0; letter-spacing: -0.02em;">
            Periode Pre-Order Batch Ini Telah Berakhir
          </h4>
          <p style="font-size: 13.5px; color: #c2410c; margin: 0; line-height: 1.55; font-weight: 500;">
            Periode Pre-Order Batch ini telah berakhir. Checkout saat ini ditutup, namun produk yang Anda simpan di keranjang akan tersimpan untuk batch berikutnya.
          </p>
        </div>
      </div>
    <?php endif; ?>
    
    <!-- Layout 2 Kolom -->
    <div class="cart-layout" id="cart-content-wrapper">

      <!-- KOLOM KIRI: DAFTAR ITEM PESANAN -->
      <div>
        <div class="cart-items-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--color-fog, #d7d7d7);">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--color-ink-black, #111111); letter-spacing: -0.04em;">Item Pre-Order</h2>
            <span id="cart-item-count" style="font-size: 13px; color: var(--color-ash, #737780); font-family: var(--font-space-mono);">0 item</span>
          </div>

          <!-- List Item Container -->
          <div id="cart-items-list">
            <!-- Rendered dynamically by JavaScript -->
          </div>

          <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <a href="katalog.php" class="btn btn-outline-primary btn-sm">
              &larr; Tambah Produk Lain
            </a>
            <button type="button" id="clear-cart-btn" style="color: var(--text-muted); font-size: 13px; text-decoration: underline; background: none; border: none; cursor: pointer;">
              Kosongkan Keranjang
            </button>
          </div>
        </div>
      </div>

      <!-- KOLOM KANAN: CARD RINGKASAN BELANJA -->
      <aside>
        <div class="cart-summary-card">
          <h3 class="summary-title">Ringkasan Belanja</h3>

          <div class="summary-row">
            <span>Total Harga Produk</span>
            <strong id="summary-subtotal" style="color: var(--text-main);">Rp 0</strong>
          </div>

          <div class="summary-row">
            <span>Estimasi DP Min. (50%)</span>
            <strong id="summary-dp" style="color: var(--accent);">Rp 0</strong>
          </div>

          <div class="summary-row">
            <span>Biaya Penanganan / PO Fee</span>
            <span style="color: var(--success); font-weight: 600;">GRATIS</span>
          </div>

          <!-- Voucher Kode Promo -->
          <div style="margin: 20px 0; padding: 14px; background: var(--bg-muted); border-radius: var(--radius-md);">
            <label for="voucher-input" style="font-size: 12px; font-weight: 600; color: var(--text-muted); display: block; margin-bottom: 6px;">
              Punya Kode Promo / Kupon Angkatan?
            </label>
            <div style="display: flex; gap: 8px;">
              <input type="text" id="voucher-input" class="form-control" placeholder="Contoh: FAST2026" style="text-transform: uppercase;">
              <button type="button" id="apply-voucher-btn" class="btn btn-outline-accent btn-sm">Pakai</button>
            </div>
            <div id="voucher-feedback" style="font-size: 11px; margin-top: 4px;"></div>
          </div>

          <!-- Total Harga (Bold Navy) -->
          <div class="summary-total">
            <div>
              <div>Total Pembayaran</div>
              <div style="font-size: 11px; color: var(--text-muted); font-weight: normal;">Belum termasuk ongkir pengiriman</div>
            </div>
            <div id="summary-total" style="color: var(--primary);">Rp 0</div>
          </div>

          <!-- Alert Banner Peringatan jika Ada Produk Kuota Penuh di Keranjang -->
          <div id="cart-quota-alert" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 12px 14px; margin-top: 18px; font-size: 12.5px; color: #b91c1c; line-height: 1.45;"></div>

          <!-- Tombol Lebar "Lanjut ke Pembayaran" Warna Oranye Sesuai Spesifikasi (Disabled saat Toko Tutup atau PO Berakhir) -->
          <div style="margin-top: 16px;">
            <?php if ($isPoActive && $isStoreOpen): ?>
              <a href="checkout.php" class="btn btn-accent btn-block btn-lg" id="checkout-cta-btn">
                Lanjut ke Pembayaran
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                  <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
              </a>
            <?php elseif (!$isStoreOpen): ?>
              <button type="button" class="btn btn-block btn-lg btn-disabled-checkout" id="checkout-cta-btn" disabled style="background: #94a3b8; border: 1px solid #94a3b8; color: #ffffff; cursor: not-allowed; pointer-events: none; opacity: 0.85; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; border-radius: var(--radius-buttons, 28px); font-size: 14.5px; font-weight: 700;" title="Pre-Order sedang ditutup sementara">
                Pre-Order Ditutup
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="15" y1="9" x2="9" y2="15"></line>
                  <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
              </button>
              <div style="font-size: 11.5px; color: #dc2626; text-align: center; margin-top: 8px; font-weight: 600;">
                * Operasional toko pre-order sedang ditutup sementara oleh Admin.
              </div>
            <?php else: ?>
              <button type="button" class="btn btn-block btn-lg btn-disabled-checkout" id="checkout-cta-btn" disabled style="background: #94a3b8; border: 1px solid #94a3b8; color: #ffffff; cursor: not-allowed; pointer-events: none; opacity: 0.85; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; border-radius: var(--radius-buttons, 28px); font-size: 14.5px; font-weight: 700;" title="Checkout ditutup karena batas waktu pre-order telah berakhir">
                Checkout Ditutup
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"></circle>
                  <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
              </button>
              <div style="font-size: 11.5px; color: #ea580c; text-align: center; margin-top: 8px; font-weight: 600;">
                * Batas waktu countdown PO telah berakhir. Checkout tidak tersedia.
              </div>
            <?php endif; ?>
          </div>
        </div>
      </aside>

    </div>

    <!-- State Jika Keranjang Kosong -->
    <div id="empty-cart-view" style="display: none; text-align: center; padding: 60px 20px; background: var(--surface-plaster, #ecedee); border-radius: var(--radius-cards, 28px); border: 1px dashed var(--color-slate, #a1a4aa); max-width: 540px; margin: 0 auto;">
      <div style="display: flex; justify-content: center; margin-bottom: 16px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#737780" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
        </svg>
      </div>
      <h2 style="font-size: 20px; font-weight: 700; color: var(--color-ink-black, #111111); margin-bottom: 20px; letter-spacing: -0.03em;">Keranjang Masih Kosong</h2>
      <a href="katalog.php" class="btn btn-accent btn-lg" style="border-radius: 28px; padding: 12px 32px;">
        Mulai Belanja
      </a>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
  // Logika Khusus Halaman Keranjang Belanja
  document.addEventListener("DOMContentLoaded", () => {
    const IS_PO_ACTIVE = <?= $isPoActive ? 'true' : 'false' ?>;
    const HAS_PO_TUTUP_PARAM = <?= (isset($_GET['pesan']) && $_GET['pesan'] === 'po_tutup') ? 'true' : 'false' ?>;
    const FULL_QUOTA_PRODUCT_IDS = <?= json_encode($fullQuotaProductIds ?? []) ?>;

    if (HAS_PO_TUTUP_PARAM && typeof showToast === "function") {
      showToast("Periode Pre-Order telah berakhir. Checkout saat ini ditutup.", "error");
    }

    const itemsContainer = document.getElementById("cart-items-list");
    const subtotalEl = document.getElementById("summary-subtotal");
    const dpEl = document.getElementById("summary-dp");
    const totalEl = document.getElementById("summary-total");
    const countEl = document.getElementById("cart-item-count");
    const cartWrapper = document.getElementById("cart-content-wrapper");
    const emptyView = document.getElementById("empty-cart-view");
    const clearBtn = document.getElementById("clear-cart-btn");
    const voucherBtn = document.getElementById("apply-voucher-btn");
    const voucherInput = document.getElementById("voucher-input");
    const voucherFeedback = document.getElementById("voucher-feedback");
    const checkoutBtn = document.getElementById("checkout-cta-btn");
    const quotaAlertEl = document.getElementById("cart-quota-alert");

    if (!IS_PO_ACTIVE && checkoutBtn) {
      checkoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        if (typeof showToast === "function") {
          showToast("Periode Pre-Order Batch ini telah berakhir. Checkout saat ini ditutup.", "error");
        }
        return false;
      });
    }

    let discountAmount = 0;

    function renderCart() {
      const cart = getCart();

      if (cart.length === 0) {
        cartWrapper.style.display = "none";
        emptyView.style.display = "block";
        if (quotaAlertEl) quotaAlertEl.style.display = "none";
        return;
      }

      saveCart(cart);
      cartWrapper.style.display = "grid";
      emptyView.style.display = "none";

      countEl.textContent = `${cart.length} item`;

      let subtotal = 0;
      let dpTotal = 0;
      const fullQuotaItems = [];

      itemsContainer.innerHTML = cart.map((item, index) => {
        const itemPrice = parsePriceInt(item.price);
        const itemQty = Math.max(1, parseInt(item.quantity ?? item.qty ?? 1, 10) || 1);
        const itemDp = parsePriceInt(item.dpPrice ?? item.dp_price) || Math.round(itemPrice * 0.5);
        const itemSubtotal = itemPrice * itemQty;
        subtotal += itemSubtotal;
        dpTotal += itemDp * itemQty;

        const itemId = parseInt(item.id || item.productId || 0, 10);
        const isQuotaFull = FULL_QUOTA_PRODUCT_IDS.includes(itemId);
        if (isQuotaFull) {
          fullQuotaItems.push(item.name || "Produk");
        }

        return `
          <div class="cart-item" style="${isQuotaFull ? 'border: 1px solid #fecaca; background: #fffdfd;' : ''}">
            <!-- Gambar Produk Kecil -->
            <div class="cart-item-img" style="padding: ${item.image ? '0' : '10px'}; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
              ${item.image ? `<img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">` : getProductSvg(item.iconType || "varsity")}
            </div>

            <!-- Nama & Detail Varian -->
            <div>
              <h3 class="cart-item-title">${item.name}</h3>
              ${isQuotaFull ? `
                <div style="display: inline-block; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 6px; margin: 3px 0 5px 0;">
                  ⚠️ Kuota Habis / Sold Out
                </div>
              ` : ''}
              <div class="cart-item-meta">
                <span>Ukuran: <strong>${item.size || "L"}</strong></span> &bull; 
                <span>Warna: <strong>${item.color || "Standar"}</strong></span>
                ${item.customText ? `<br><span style="color: var(--accent); font-weight: 500;">Custom: "${item.customText}"</span>` : ''}
              </div>
              <div style="font-size: 13px; color: var(--primary); font-weight: 700; margin-top: 4px;">
                ${formatRupiah(itemPrice)} <span style="font-size: 11px; font-weight: normal; color: var(--text-muted);">/ pcs</span>
              </div>
            </div>

            <!-- Stepper Kuantitas (+ dan -) -->
            <div>
              <div class="qty-control" style="transform: scale(0.9);">
                <button type="button" class="qty-btn" onclick="updateItemQty(${index}, -1)">-</button>
                <input type="text" class="qty-input" value="${itemQty}" readonly>
                <button type="button" class="qty-btn" onclick="updateItemQty(${index}, 1)">+</button>
              </div>
            </div>

            <!-- Subtotal & Hapus -->
            <div style="text-align: right;">
              <div class="cart-item-price">${formatRupiah(itemSubtotal)}</div>
              <button type="button" class="cart-delete-btn" onclick="deleteItem(${index})" title="Hapus Item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="3 6 5 6 21 6"></polyline>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
              </button>
            </div>
          </div>
        `;
      }).join("");

      const finalTotal = Math.max(0, subtotal - discountAmount);

      subtotalEl.textContent = formatRupiah(subtotal);
      dpEl.textContent = formatRupiah(dpTotal);
      totalEl.textContent = formatRupiah(finalTotal);

      // Tangani kondisi pembatasan kuota penuh
      if (quotaAlertEl) {
        if (fullQuotaItems.length > 0) {
          quotaAlertEl.style.display = "block";
          quotaAlertEl.innerHTML = `
            <div style="display: flex; gap: 8px; align-items: flex-start;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top: 2px;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              <div>
                <strong style="color: #991b1b;">Perhatian:</strong> Pesanan gagal diproses karena kuota produk <strong>${fullQuotaItems.join(", ")}</strong> telah habis. Silakan hapus item tersebut dari keranjang untuk melanjutkan checkout.
              </div>
            </div>
          `;

          if (checkoutBtn && IS_PO_ACTIVE) {
            checkoutBtn.dataset.blockedQuota = "true";
            checkoutBtn.dataset.blockedItem = fullQuotaItems.join(", ");
            checkoutBtn.style.background = "#94a3b8";
            checkoutBtn.style.borderColor = "#94a3b8";
            checkoutBtn.style.cursor = "not-allowed";
            checkoutBtn.style.pointerEvents = "none";
            checkoutBtn.innerHTML = `
              Kuota Produk Habis
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
              </svg>
            `;
          }
        } else {
          quotaAlertEl.style.display = "none";
          quotaAlertEl.innerHTML = "";

          if (checkoutBtn && IS_PO_ACTIVE && checkoutBtn.dataset.blockedQuota === "true") {
            checkoutBtn.removeAttribute("data-blocked-quota");
            checkoutBtn.removeAttribute("data-blocked-item");
            checkoutBtn.style.background = "";
            checkoutBtn.style.borderColor = "";
            checkoutBtn.style.cursor = "";
            checkoutBtn.style.pointerEvents = "";
            checkoutBtn.innerHTML = `
              Lanjut ke Pembayaran
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            `;
          }
        }
      }
    }

    window.updateItemQty = function(index, delta) {
      const cart = getCart();
      if (!cart[index]) return;

      cart[index].quantity += delta;
      if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
        showToast("Item dihapus dari keranjang", "info");
      }
      saveCart(cart);
      renderCart();
    };

    window.deleteItem = function(index) {
      const cart = getCart();
      cart.splice(index, 1);
      saveCart(cart);
      renderCart();
      showToast("Produk dikeluarkan dari keranjang", "info");
    };

    if (clearBtn) {
      clearBtn.addEventListener("click", () => {
        if (confirm("Apakah Anda yakin ingin mengosongkan seluruh keranjang?")) {
          saveCart([]);
          renderCart();
          showToast("Keranjang berhasil dikosongkan", "info");
        }
      });
    }

    if (voucherBtn && voucherInput) {
      voucherBtn.addEventListener("click", () => {
        const code = voucherInput.value.trim().toUpperCase();
        if (code === "FAST2026" || code === "ANGKATAN2026") {
          discountAmount = 25000;
          voucherFeedback.textContent = "Kupon berhasil diterapkan: Diskon Rp 25.000";
          voucherFeedback.style.color = "var(--success)";
          showToast("Kupon diskon Rp 25.000 berhasil digunakan!", "success");
          renderCart();
        } else {
          voucherFeedback.textContent = "Kode promo tidak valid atau telah kedaluwarsa.";
          voucherFeedback.style.color = "var(--danger)";
          showToast("Kode promo tidak valid", "error");
        }
      });
    }

    renderCart();
  });
</script>
