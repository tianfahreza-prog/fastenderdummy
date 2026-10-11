<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Keranjang Belanja (keranjang.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Keranjang Pesanan Pre-Order - Fastender';
$activePage = 'keranjang';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     HALAMAN KERANJANG (LAYOUT 2 KOLOM)
     ========================================== -->
<section class="section-padding" style="padding-top: 28px;">
  <div class="container">
    <div style="margin-bottom: 24px;">
      <h1 style="font-size: 32px; font-weight: 800; color: var(--color-ink-black, #111111); letter-spacing: -0.04em; margin: 0;">Keranjang Pesanan Pre-Order</h1>
    </div>
    
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

          <!-- Tombol Lebar "Lanjut ke Pembayaran" Warna Oranye Sesuai Spesifikasi -->
          <div style="margin-top: 24px;">
            <a href="checkout.php" class="btn btn-accent btn-block btn-lg" id="checkout-cta-btn">
              Lanjut ke Pembayaran
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </a>
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

    let discountAmount = 0;

    function renderCart() {
      const cart = getCart();

      if (cart.length === 0) {
        cartWrapper.style.display = "none";
        emptyView.style.display = "block";
        return;
      }

      saveCart(cart);
      cartWrapper.style.display = "grid";
      emptyView.style.display = "none";

      countEl.textContent = `${cart.length} item`;

      let subtotal = 0;
      let dpTotal = 0;

      itemsContainer.innerHTML = cart.map((item, index) => {
        const itemPrice = parsePriceInt(item.price);
        const itemQty = Math.max(1, parseInt(item.quantity ?? item.qty ?? 1, 10) || 1);
        const itemDp = parsePriceInt(item.dpPrice ?? item.dp_price) || Math.round(itemPrice * 0.5);
        const itemSubtotal = itemPrice * itemQty;
        subtotal += itemSubtotal;
        dpTotal += itemDp * itemQty;

        return `
          <div class="cart-item">
            <!-- Gambar Produk Kecil -->
            <div class="cart-item-img" style="padding: ${item.image ? '0' : '10px'}; display: flex; align-items: center; justify-content: center; overflow: hidden;">
              ${item.image ? `<img src="${item.image}" alt="${item.name}" style="width: 100%; height: 100%; object-fit: cover;">` : getProductSvg(item.iconType || "varsity")}
            </div>

            <!-- Nama & Detail Varian -->
            <div>
              <h3 class="cart-item-title">${item.name}</h3>
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
