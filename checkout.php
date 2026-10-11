<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Checkout & Form Pemesanan PO (checkout.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Form Pemesanan Pre-Order - Fastender';
$activePage = 'keranjang';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     CHECKOUT CONTENT (LAYOUT 2 KOLOM)
     ========================================== -->
<section class="section-padding" style="padding-top: 28px;">
  <div class="container">
    <div style="margin-bottom: 24px;">
      <h1 style="font-size: 32px; font-weight: 800; color: var(--color-ink-black, #111111); letter-spacing: -0.04em; margin: 0;">Form Pemesanan Pre-Order</h1>
    </div>
    
    <form id="checkout-form" onsubmit="handleCheckoutSubmit(event)">
      <div class="checkout-layout">

        <!-- KOLOM KIRI: FORM DATA PEMBELI & ALAMAT PENGIRIMAN -->
        <div>
          
          <!-- Card 1: Data Pembeli -->
          <div class="form-card">
            <h2 class="form-section-title">
              <span class="step-badge">1</span>
              Data Identitas Pemesan
            </h2>

            <div class="form-grid-2">
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="buyer-name">Nama Lengkap *</label>
                <input type="text" id="buyer-name" class="form-control" placeholder="Contoh: Muhammad Fatah" required>
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="buyer-phone">Nomor HP / WhatsApp Aktif *</label>
                <input type="tel" id="buyer-phone" class="form-control" placeholder="Contoh: 081234567890" required>
              </div>
            </div>
          </div>

          <!-- Card 2: Alamat Pengiriman -->
          <div class="form-card">
            <h2 class="form-section-title">
              <span class="step-badge">2</span>
              Alamat Pengiriman Pesanan
            </h2>

            <div class="form-grid-2">
              <div class="form-group">
                <label class="form-label" for="ship-province">Provinsi *</label>
                <select id="ship-province" class="form-control" required>
                  <option value="">Pilih Provinsi</option>
                  <option value="Jawa Barat" selected>Jawa Barat</option>
                  <option value="DKI Jakarta">DKI Jakarta</option>
                  <option value="Jawa Tengah">Jawa Tengah</option>
                  <option value="DI Yogyakarta">DI Yogyakarta</option>
                  <option value="Jawa Timur">Jawa Timur</option>
                  <option value="Banten">Banten</option>
                  <option value="Luar Pulau Jawa">Luar Pulau Jawa</option>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label" for="ship-city">Kota / Kabupaten *</label>
                <input type="text" id="ship-city" class="form-control" placeholder="Contoh: Kota Bandung" value="Kota Bandung" required>
              </div>
            </div>

            <div class="form-group">
              <div class="address-label-row">
                <label class="form-label" for="ship-address" style="margin-bottom: 0;">Alamat Lengkap (Jalan, No. Rumah, RT/RW, Kecamatan) *</label>
                <button type="button" class="btn-pickup-usma" onclick="selectPickupUSMA()">
                  Ambil di Sekretariat USMA
                </button>
              </div>
              <textarea id="ship-address" class="form-control" rows="3" placeholder="Masukkan alamat pengiriman selengkap mungkin..." required>Jl. Sukabirus No. 45, RT 02 RW 05, Dayeuhkolot</textarea>
            </div>

            <div class="form-grid-2">
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="ship-postal">Kode Pos</label>
                <input type="text" id="ship-postal" class="form-control" placeholder="Contoh: 40257" value="40257">
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="ship-courier">Pilihan Ekspedisi *</label>
                <select id="ship-courier" class="form-control" onchange="updateShippingCost()">
                  <option value="15000">JNE Reguler (Rp 15.000) - 2-3 hari</option>
                  <option value="18000">J&amp;T Express (Rp 18.000) - 1-2 hari</option>
                  <option value="14000">SiCepat REG (Rp 14.000) - 2-3 hari</option>
                  <option value="0">Ambil di Sekretariat / Kampus (Gratis Ongkir)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Card 3: Skema Pembayaran PO -->
          <div class="form-card">
            <h2 class="form-section-title">
              <span class="step-badge">3</span>
              Skema &amp; Metode Pembayaran Pre-Order
            </h2>

            <label class="form-label">Pilih Jenis Pembayaran:</label>
            <div class="radio-card-group" style="margin-bottom: 20px;">
              <label class="radio-card active" id="scheme-dp-card">
                <input type="radio" name="payment_scheme" value="dp" checked onchange="togglePaymentScheme()">
                <div>
                  <strong style="color: var(--primary); display: block;">Bayar Uang Muka (DP 50%)</strong>
                  <span style="font-size: 12px; color: var(--text-muted);">Bayar separuh sekarang untuk produksi, sisa 50% saat barang siap kirim.</span>
                </div>
              </label>

              <label class="radio-card" id="scheme-full-card">
                <input type="radio" name="payment_scheme" value="full" onchange="togglePaymentScheme()">
                <div>
                  <strong style="color: var(--primary); display: block;">Pembayaran Lunas (100%)</strong>
                  <span style="font-size: 12px; color: var(--text-muted);">Bayar penuh sekaligus tanpa perlu repot transfer pelunasan nanti.</span>
                </div>
              </label>
            </div>

            <label class="form-label">Pilih Metode Pembayaran Transfer:</label>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
              <label class="radio-card active">
                <input type="radio" name="pay_method" value="BCA" checked>
                <span style="font-weight: 600; font-size: 13px;">Bank BCA</span>
              </label>
              <label class="radio-card">
                <input type="radio" name="pay_method" value="Mandiri">
                <span style="font-weight: 600; font-size: 13px;">Bank Mandiri</span>
              </label>
              <label class="radio-card">
                <input type="radio" name="pay_method" value="QRIS">
                <span style="font-weight: 600; font-size: 13px;">QRIS Instant</span>
              </label>
            </div>
          </div>

        </div>

        <!-- KOLOM KANAN: RINGKASAN PESANAN & TOMBOL KONFIRMASI -->
        <aside>
          <div class="checkout-summary-card">
            <h3 class="summary-title" style="margin-bottom: 16px;">Ringkasan Pesanan</h3>

            <!-- Item Mini List -->
            <div id="checkout-items-summary" style="margin-bottom: 20px;">
              <!-- Rendered by JS -->
            </div>

            <div class="summary-row">
              <span>Subtotal Item</span>
              <strong id="chk-subtotal" style="color: var(--text-main);">Rp 0</strong>
            </div>

            <div class="summary-row">
              <span>Ongkos Kirim</span>
              <strong id="chk-shipping" style="color: var(--text-main);">Rp 15.000</strong>
            </div>

            <div class="summary-row" style="padding-top: 10px; border-top: 1px dashed var(--border-color);">
              <span>Total Nilai Pesanan</span>
              <strong id="chk-order-total" style="color: var(--text-main);">Rp 0</strong>
            </div>

            <div class="summary-total" style="background: var(--surface-plaster, #ecedee); padding: 14px 16px; border-radius: var(--radius-cards, 10px); border: none;">
              <div>
                <div style="font-size: 13px;" id="chk-due-label">Nominal DP Harus Dibayar:</div>
                <div style="font-size: 11px; color: var(--color-ash, #737780); font-weight: normal;">(DP 50% + Ongkir)</div>
              </div>
              <div id="chk-due-amount" style="font-family: var(--font-space-mono); font-size: 20px; font-weight: 700; color: var(--color-electric-cobalt, #000aff);">Rp 0</div>
            </div>

            <!-- Checkbox Persetujuan PO -->
            <div style="margin: 20px 0 16px; font-size: 12px; color: var(--color-ash, #737780);">
              <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                <input type="checkbox" required checked style="margin-top: 3px; accent-color: var(--color-electric-cobalt, #000aff);">
                <span>Saya menyetujui ketentuan Pre-Order (PO) dengan estimasi waktu produksi sekitar 14-21 hari kerja setelah kuota batch ditutup.</span>
              </label>
            </div>

            <!-- Tombol "Konfirmasi Pesanan" -->
            <button type="submit" id="confirm-order-btn" class="btn btn-accent btn-block btn-lg">
              Konfirmasi Pesanan
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </button>

            <div style="margin-top: 14px; text-align: center;">
              <a href="keranjang.php" style="font-size: 12px; color: var(--color-ink-black, #111111); text-decoration: underline;">
                &larr; Ubah Item di Keranjang
              </a>
            </div>
          </div>
        </aside>

      </div>
    </form>

  </div>
</section>

<!-- ==========================================
     MODAL SUKSES PEMESANAN (ORDER SUCCESS)
     ========================================== -->
<div id="order-success-modal" class="modal-overlay">
  <div class="modal-dialog" style="max-width: 520px; text-align: center; border-radius: var(--radius-cards, 28px);">
    <div style="width: 60px; height: 60px; background: rgba(97, 154, 230, 0.15); color: #619ae6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>
    <h2 style="font-size: 22px; font-weight: 700; color: var(--color-ink-black, #111111); margin-bottom: 6px; letter-spacing: -0.04em;">Pesanan Berhasil Dibuat</h2>
    <p style="font-size: 13px; color: var(--color-ash, #737780); margin-bottom: 20px;">
      Simpan Nomor Resi PO di bawah ini:
    </p>

    <!-- Kode Resi / No. Pesanan Highlight Box -->
    <div style="background: var(--surface-plaster, #ecedee); border: 2px dashed var(--color-ink-black, #111111); border-radius: var(--radius-cards, 10px); padding: 18px; margin-bottom: 20px;">
      <span style="font-size: 11px; text-transform: uppercase; font-family: var(--font-space-mono); letter-spacing: 0.05em; color: var(--color-ash, #737780); font-weight: 700;">Nomor Resi / No. Pesanan Anda:</span>
      <div id="success-order-code" style="font-family: var(--font-space-mono); font-size: 26px; font-weight: 700; color: var(--color-ink-black, #111111); letter-spacing: 0.02em; margin: 6px 0;">
        PO-2026-8891
      </div>
      <div style="font-size: 13px; font-family: var(--font-space-mono); color: var(--color-electric-cobalt, #000aff); font-weight: 600;">
        Silakan transfer sebesar: <span id="success-order-amount">Rp 0</span>
      </div>
    </div>

    <div style="background: var(--surface-plaster, #ecedee); border: 1px solid var(--color-fog, #d7d7d7); border-radius: var(--radius-cards, 10px); padding: 14px; font-size: 12px; color: var(--color-ink-black, #111111); margin-bottom: 24px; text-align: left; line-height: 1.5;">
      <strong>Informasi Pembayaran:</strong> Silakan lakukan transfer ke rekening <strong style="font-family: var(--font-space-mono);">BCA 828-091-2334 (a.n Fastender Konveksi)</strong> dan simpan bukti transfer untuk verifikasi.
    </div>

    <div style="display: flex; gap: 12px;">
      <a id="success-track-link" href="lacak.php?resi=PO-2026-8891" class="btn btn-accent btn-block">
        Lacak Pesanan Saya
      </a>
      <a href="index.php" class="btn btn-outline-primary btn-block">
        Kembali ke Beranda
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
  let currentShippingCost = 15000;
  let paymentScheme = "dp"; // "dp" atau "full"

  function safeParseNumber(val, fallback = 0) {
    if (typeof val === "number" && !isNaN(val)) return Math.round(val);
    if (typeof val === "string") {
      const cleaned = val.replace(/[^0-9]/g, "");
      const parsed = parseInt(cleaned, 10);
      return isNaN(parsed) ? fallback : parsed;
    }
    return fallback;
  }

  document.addEventListener("DOMContentLoaded", () => {
    const cart = getCart();
    if (cart.length === 0) {
      alert("Keranjang Anda masih kosong. Silakan pilih produk terlebih dahulu.");
      window.location.href = "katalog.php";
      return;
    }

    // Pastikan data keranjang tersimpan dalam bentuk yang sudah ternormalisasi
    saveCart(cart);
    renderCheckoutSummary();
  });

  function selectPickupUSMA() {
    const addressInput = document.getElementById("ship-address");
    const courierSelect = document.getElementById("ship-courier");
    const provinceSelect = document.getElementById("ship-province");
    const cityInput = document.getElementById("ship-city");

    if (addressInput) {
      addressInput.value = "Sekretariat USMA (Gedung UKM Polinema)";
    }
    if (provinceSelect && !provinceSelect.value) {
      provinceSelect.value = "Jawa Timur";
    }
    if (cityInput && !cityInput.value.trim()) {
      cityInput.value = "Kota Malang";
    }
    if (courierSelect) {
      courierSelect.value = "0";
    }

    currentShippingCost = 0;
    renderCheckoutSummary();

    if (typeof showToast === "function") {
      showToast("Lokasi pengambilan diatur ke Sekretariat USMA (Bebas Ongkir Rp 0)", "success");
    }
  }

  function renderCheckoutSummary() {
    const cart = getCart();
    const summaryContainer = document.getElementById("checkout-items-summary");
    
    let subtotal = 0;
    let dpTotal = 0;

    summaryContainer.innerHTML = cart.map(item => {
      const itemName = (item.name && item.name !== "undefined") ? item.name : "Produk Pre-Order Fastender";
      const itemPrice = safeParseNumber(item.price, 0);
      const itemQty = Math.max(1, safeParseNumber(item.quantity ?? item.qty, 1));
      const itemDp = safeParseNumber(item.dpPrice ?? item.dp_price, Math.round(itemPrice * 0.5)) || Math.round(itemPrice * 0.5);
      const itemSize = (item.size && item.size !== "undefined") ? item.size : "L";
      const itemColor = (item.color && item.color !== "undefined") ? item.color : "Standar";

      const itemSub = itemPrice * itemQty;
      subtotal += itemSub;
      dpTotal += itemDp * itemQty;

      return `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light);">
          <div style="display: flex; align-items: center; gap: 10px;">
            ${item.image ? `<img src="${item.image}" alt="${itemName}" style="width: 38px; height: 38px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">` : ''}
            <div>
              <div style="font-weight: 600; color: var(--text-main);">${itemName}</div>
              <div style="font-size: 11px; color: var(--text-muted);">
                ${itemSize} &bull; ${itemColor} &times; ${itemQty} pcs
              </div>
            </div>
          </div>
          <div style="font-weight: 700; color: var(--primary);">
            ${formatRupiah(itemSub)}
          </div>
        </div>
      `;
    }).join("");

    const shippingVal = safeParseNumber(currentShippingCost, 0);
    const orderTotal = subtotal + shippingVal;
    const dueAmount = paymentScheme === "dp" ? (dpTotal + shippingVal) : orderTotal;

    document.getElementById("chk-subtotal").textContent = formatRupiah(subtotal);
    document.getElementById("chk-shipping").textContent = formatRupiah(shippingVal);
    document.getElementById("chk-order-total").textContent = formatRupiah(orderTotal);
    document.getElementById("chk-due-amount").textContent = formatRupiah(dueAmount);

    if (paymentScheme === "dp") {
      document.getElementById("chk-due-label").textContent = "Nominal DP Harus Dibayar:";
    } else {
      document.getElementById("chk-due-label").textContent = "Total Lunas Harus Dibayar:";
    }
  }

  function updateShippingCost() {
    const courierSelect = document.getElementById("ship-courier");
    currentShippingCost = safeParseNumber(courierSelect.value, 0);
    renderCheckoutSummary();
  }

  function togglePaymentScheme() {
    const radios = document.getElementsByName("payment_scheme");
    for (const r of radios) {
      if (r.checked) {
        paymentScheme = r.value;
        break;
      }
    }

    document.getElementById("scheme-dp-card").classList.toggle("active", paymentScheme === "dp");
    document.getElementById("scheme-full-card").classList.toggle("active", paymentScheme === "full");

    renderCheckoutSummary();
  }

  function handleCheckoutSubmit(e) {
    e.preventDefault();
    const cart = getCart();
    if (cart.length === 0) return;

    const randomSuffix = Math.floor(1000 + Math.random() * 9000);
    const generatedOrderCode = `PO-2026-${randomSuffix}`;

    const buyerName = document.getElementById("buyer-name").value;
    const buyerPhone = document.getElementById("buyer-phone").value;
    const shipCity = document.getElementById("ship-city").value;
    const shipAddress = document.getElementById("ship-address").value;

    const shippingVal = safeParseNumber(currentShippingCost, 0);
    const subtotal = cart.reduce((acc, i) => {
      const p = safeParseNumber(i.price, 0);
      const q = Math.max(1, safeParseNumber(i.quantity ?? i.qty, 1));
      return acc + (p * q);
    }, 0);
    const dpTotal = cart.reduce((acc, i) => {
      const p = safeParseNumber(i.price, 0);
      const dp = safeParseNumber(i.dpPrice ?? i.dp_price, Math.round(p * 0.5)) || Math.round(p * 0.5);
      const q = Math.max(1, safeParseNumber(i.quantity ?? i.qty, 1));
      return acc + (dp * q);
    }, 0);
    const total = subtotal + shippingVal;
    const due = paymentScheme === "dp" ? (dpTotal + shippingVal) : total;

    const orderRecord = {
      orderId: generatedOrderCode,
      date: new Date().toLocaleDateString("id-ID", { day: 'numeric', month: 'short', year: 'numeric' }),
      customerName: buyerName,
      customerPhone: buyerPhone,
      customerEmail: "-",
      org: "-",
      city: shipCity,
      address: shipAddress,
      paymentScheme: paymentScheme,
      items: cart,
      subtotal: subtotal,
      shipping: shippingVal,
      total: total,
      due: due,
      paymentStatus: paymentScheme === "dp" ? "Menunggu Verifikasi DP" : "Menunggu Verifikasi Lunas",
      productionStatus: "Verifikasi Pesanan"
    };

    let allOrders = JSON.parse(localStorage.getItem("fastender_all_orders") || "[]");
    allOrders.unshift(orderRecord);
    localStorage.setItem("fastender_all_orders", JSON.stringify(allOrders));

    // Kosongkan keranjang
    saveCart([]);

    // Tampilkan modal sukses
    document.getElementById("success-order-code").textContent = generatedOrderCode;
    document.getElementById("success-order-amount").textContent = formatRupiah(due);
    document.getElementById("success-track-link").href = `lacak.php?resi=${generatedOrderCode}`;
    document.getElementById("order-success-modal").classList.add("active");
  }
</script>
