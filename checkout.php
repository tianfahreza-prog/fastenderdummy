<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Checkout & Form Pemesanan PO (checkout.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

// Backend Guard: Batasi akses checkout jika toko ditutup oleh admin
$storeSettings = getStoreSettings();
$status_toko = $storeSettings['status_toko'] ?? 'buka';
if ($status_toko === 'tutup' || !isStoreOpen()) {
    header('Location: keranjang.php?status=toko_tutup');
    exit;
}

// Backend Guard: Batasi akses checkout jika batas waktu Pre-Order telah berakhir
if (!isPreOrderActive()) {
    header('Location: keranjang.php?pesan=po_tutup');
    exit;
}

// Jika form di-submit langsung ke checkout.php via POST tradisional
$flashSuccessOrder = $_SESSION['checkout_success_order'] ?? null;
$flashErrorMessage = $_SESSION['checkout_error_message'] ?? '';
unset($_SESSION['checkout_success_order'], $_SESSION['checkout_error_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $res = processNewCheckoutOrder($_POST, $_FILES['bukti_pembayaran']);
    if ($res['success']) {
        $flashSuccessOrder = $res['order'];
    } else {
        $flashErrorMessage = $res['message'];
    }
}

$pageTitle = 'Form Pemesanan Pre-Order - Fastender';
$activePage = 'keranjang';

$storeSettings = getStoreSettings();
$bcaNo = htmlspecialchars($storeSettings['rekening_bca_no'] ?? '828-091-2334');
$bcaNama = htmlspecialchars($storeSettings['rekening_bca_nama'] ?? 'Fastender Konveksi');
$mandiriNo = htmlspecialchars($storeSettings['rekening_mandiri_no'] ?? '144-00-2026889-1');
$mandiriNama = htmlspecialchars($storeSettings['rekening_mandiri_nama'] ?? 'Fastender Konveksi');
$qrisImg = trim((string)($storeSettings['qris_image'] ?? ''));
$hasQrisFile = ($qrisImg !== '' && is_file(__DIR__ . '/' . ltrim($qrisImg, '/')));

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

    <?php if (!empty($flashErrorMessage)): ?>
      <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 14px 18px; border-radius: 16px; margin-bottom: 20px; font-size: 13.5px; font-weight: 600;">
        <?= htmlspecialchars($flashErrorMessage) ?>
      </div>
    <?php endif; ?>
    
    <form id="checkout-form" action="proses-checkout.php" method="POST" enctype="multipart/form-data" onsubmit="handleCheckoutSubmit(event)">
      <input type="hidden" name="cart_items" id="cart-items-input" value="[]">
      <input type="hidden" name="is_pickup_usma" id="is-pickup-usma" value="0">
      <div class="checkout-layout">

        <!-- KOLOM KIRI: FORM DATA PEMBELI & ALAMAT PENGIRIMAN -->
        <div>
          
          <!-- Card 1: Data Pembeli -->
          <div class="form-card">
            <h2 class="form-section-title">
              <span class="step-badge">1</span>
              Data Identitas Pemesan
            </h2>

            <div class="form-grid-3">
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="buyer-name">Nama Lengkap *</label>
                <input type="text" id="buyer-name" name="buyer_name" class="form-control" placeholder="Contoh: Muhammad Fatah" required>
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="buyer-phone">Nomor HP / WhatsApp Aktif *</label>
                <input type="tel" id="buyer-phone" name="buyer_phone" class="form-control" placeholder="Contoh: 081234567890" required>
              </div>
              <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" for="buyer-jurusan">Jurusan *</label>
                <input type="text" id="buyer-jurusan" name="jurusan" class="form-control" placeholder="Contoh: D4 Teknik Informatika / Jurusan Kamu" required>
              </div>
            </div>
          </div>

          <!-- Card 2: Alamat Pengiriman -->
          <div class="form-card" id="shipping-address-card">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 18px;">
              <h2 class="form-section-title" style="margin-bottom: 0;">
                <span class="step-badge">2</span>
                Alamat Pengiriman Pesanan
              </h2>
              <button type="button" id="btn-pickup-usma" class="btn-pickup-usma" onclick="selectPickupUSMA()">
                <span id="btn-pickup-usma-text">Ambil di Sekretariat USMA</span>
              </button>
            </div>

            <!-- Banner Visual Saat Mode Ambil di Sekretariat USMA Aktif -->
            <div id="pickup-usma-banner" style="display: none; background: #eaf2fd; border: 1.5px solid #619ae6; border-radius: 16px; padding: 16px 18px; margin-bottom: 18px;">
              <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                <div>
                  <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; font-weight: 700; color: #1e3a8a; margin-bottom: 4px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                      <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Pengambilan Langsung di Sekretariat USMA
                  </div>
                  <p style="font-size: 12.5px; color: #334155; margin: 0; line-height: 1.5;">
                    Alamat pengiriman tidak perlu diisi manual. Pesanan Anda diarahkan ke <strong>Sekretariat USMA (Gedung UKM Polinema)</strong> dengan <strong>Gratis Ongkir (Rp 0)</strong>.
                  </p>
                </div>
                <button type="button" onclick="selectPickupUSMA(false)" style="background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1; border-radius: 999px; padding: 7px 14px; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; transition: all 0.2s ease;">
                  Isi Alamat Pengiriman Manual
                </button>
              </div>
            </div>

            <!-- Form Input Alamat Pengiriman Manual -->
            <div id="manual-address-fields">
              <div class="form-grid-2">
                <div class="form-group">
                  <label class="form-label" for="ship-province">Provinsi *</label>
                  <select id="ship-province" name="ship_province" class="form-control" required>
                    <option value="">Pilih Provinsi</option>
                    <option value="-" style="display: none;">-</option>
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
                  <input type="text" id="ship-city" name="ship_city" class="form-control" placeholder="Contoh: Kota Bandung" value="Kota Bandung" required>
                </div>
              </div>

              <div class="form-group">
                <div class="address-label-row">
                  <label class="form-label" for="ship-address" style="margin-bottom: 0;">Alamat Lengkap (Jalan, No. Rumah, RT/RW, Kecamatan) *</label>
                </div>
                <textarea id="ship-address" name="ship_address" class="form-control" rows="3" placeholder="Masukkan alamat pengiriman selengkap mungkin..." required>Jl. Sukabirus No. 45, RT 02 RW 05, Dayeuhkolot</textarea>
              </div>
            </div>

            <div class="form-grid-2" id="postal-courier-grid">
              <div class="form-group" id="ship-postal-group" style="margin-bottom: 0;">
                <label class="form-label" for="ship-postal">Kode Pos</label>
                <input type="text" id="ship-postal" name="ship_postal" class="form-control" placeholder="Contoh: 40257" value="40257">
              </div>
              <div class="form-group" id="ship-courier-group" style="margin-bottom: 0;">
                <label class="form-label" for="ship-courier">Pilihan Ekspedisi *</label>
                <select id="ship-courier" name="ship_courier" class="form-control" onchange="updateShippingCost()">
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
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;" id="payment-methods-grid">
              <label class="radio-card active" onclick="selectPayMethodCard(this)">
                <input type="radio" name="pay_method" value="BCA" checked>
                <span style="font-weight: 600; font-size: 13px;">Bank BCA</span>
              </label>
              <label class="radio-card" onclick="selectPayMethodCard(this)">
                <input type="radio" name="pay_method" value="Mandiri">
                <span style="font-weight: 600; font-size: 13px;">Bank Mandiri</span>
              </label>
              <label class="radio-card" onclick="selectPayMethodCard(this)">
                <input type="radio" name="pay_method" value="QRIS">
                <span style="font-weight: 600; font-size: 13px;">QRIS Instant</span>
              </label>
            </div>

            <!-- Detail Rekening Tujuan Dinamis dari Pengaturan Toko -->
            <div id="pay-detail-container" style="margin-top: 16px;">
              <!-- Detail BCA -->
              <div id="pay-detail-bca" class="pay-method-detail-box" style="background: #ffffff; border: 1px solid var(--color-fog, #d7d7d7); border-radius: 14px; padding: 14px 16px; font-size: 13px; color: var(--color-ink-black, #111111); line-height: 1.5;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                  <span style="font-weight: 700; color: #00529c; font-size: 12px; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;">Bank Central Asia (BCA)</span>
                  <button type="button" onclick="navigator.clipboard.writeText('<?= $bcaNo ?>'); alert('Nomor Rekening BCA disalin!');" style="background: none; border: none; color: #2563eb; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: underline;">Salin No. Rek</button>
                </div>
                <div style="font-size: 15px; font-weight: 700; font-family: var(--font-space-mono); letter-spacing: 0.02em;"><?= $bcaNo ?></div>
                <div style="font-size: 12px; color: var(--color-ash, #737780);">Atas Nama: <strong><?= $bcaNama ?></strong></div>
              </div>

              <!-- Detail Mandiri -->
              <div id="pay-detail-mandiri" class="pay-method-detail-box" style="display: none; background: #ffffff; border: 1px solid var(--color-fog, #d7d7d7); border-radius: 14px; padding: 14px 16px; font-size: 13px; color: var(--color-ink-black, #111111); line-height: 1.5;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                  <span style="font-weight: 700; color: #002d62; font-size: 12px; background: #fef3c7; padding: 2px 8px; border-radius: 4px;">Bank Mandiri</span>
                  <button type="button" onclick="navigator.clipboard.writeText('<?= $mandiriNo ?>'); alert('Nomor Rekening Mandiri disalin!');" style="background: none; border: none; color: #2563eb; font-size: 11px; font-weight: 600; cursor: pointer; text-decoration: underline;">Salin No. Rek</button>
                </div>
                <div style="font-size: 15px; font-weight: 700; font-family: var(--font-space-mono); letter-spacing: 0.02em;"><?= $mandiriNo ?></div>
                <div style="font-size: 12px; color: var(--color-ash, #737780);">Atas Nama: <strong><?= $mandiriNama ?></strong></div>
              </div>

              <!-- Detail QRIS -->
              <div id="pay-detail-qris" class="pay-method-detail-box" style="display: none; background: #ffffff; border: 1px solid var(--color-fog, #d7d7d7); border-radius: 14px; padding: 16px; font-size: 13px; color: var(--color-ink-black, #111111); text-align: center;">
                <div style="font-weight: 700; color: #7c3aed; font-size: 13px; margin-bottom: 8px;">Scan Kode QRIS Fastender</div>
                <?php if ($hasQrisFile): ?>
                  <div style="background: #f8fafc; padding: 10px; border-radius: 10px; display: inline-block; border: 1px solid #e2e8f0; margin-bottom: 8px;">
                    <img src="<?= htmlspecialchars($qrisImg) ?>" alt="QRIS Barcode" style="max-width: 180px; max-height: 180px; display: block; margin: 0 auto; border-radius: 6px;">
                  </div>
                  <div style="font-size: 11.5px; color: var(--color-ash, #737780);">
                    Bisa dibayar dengan BCA Mobile, Livin, GoPay, OVO, ShopeePay, DANA, dll.
                  </div>
                <?php else: ?>
                  <div style="background: #f1f5f9; padding: 16px; border-radius: 10px; color: #64748b; font-size: 12px;">
                    Barcode QRIS akan diverifikasi langsung oleh Admin. Silakan lanjutkan upload bukti transaksi.
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>

        <!-- KOLOM KANAN: RINGKASAN PESANAN, UPLOAD BUKTI PEMBAYARAN & TOMBOL KONFIRMASI -->
        <aside>
          <!-- Card Ringkasan Pesanan & Upload Bukti -->
          <div class="checkout-summary-card">
            <h3 class="summary-title" style="margin-bottom: 16px;">Ringkasan Pesanan</h3>
            <!-- Alert Banner jika Ada Produk Kuota Penuh -->
            <div id="checkout-quota-alert" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 12px 14px; margin-bottom: 18px; font-size: 12.5px; color: #b91c1c; line-height: 1.45;"></div>

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

            <div class="summary-total" style="background: #ffffff; padding: 14px 16px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.06);">
              <div>
                <div style="font-size: 13px;" id="chk-due-label">Nominal DP Harus Dibayar:</div>
                <div style="font-size: 11px; color: var(--color-ash, #737780); font-weight: normal;">(DP 50% + Ongkir)</div>
              </div>
              <div id="chk-due-amount" style="font-family: var(--font-space-mono); font-size: 20px; font-weight: 700; color: #619ae6;">Rp 0</div>
            </div>

            <!-- SLOT AREA UPLOAD BUKTI PEMBAYARAN (DRAG & DROP) -->
            <div class="payment-proof-section">
              <div class="payment-proof-header">
                <label class="form-label" for="bukti_pembayaran" style="margin-bottom: 0; font-size: 13.5px; font-weight: 700;">
                  Upload Bukti Pembayaran <span style="color: #d9383a;">*</span>
                </label>
                <span class="payment-proof-badge" id="proof-status-badge">Wajib Diunggah</span>
              </div>

              <!-- Input File Asli -->
              <input
                type="file"
                name="bukti_pembayaran"
                id="bukti_pembayaran"
                accept="image/png, image/jpeg, image/jpg, application/pdf"
                required
                class="payment-proof-file-input"
              >

              <!-- Kotak Drag & Drop -->
              <div class="payment-dropzone" id="payment-dropzone" onclick="triggerProofFileSelect()">
                <div class="payment-dropzone-empty" id="dropzone-empty-state">
                  <div class="payment-dropzone-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                      <polyline points="17 8 12 3 7 8"></polyline>
                      <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                  </div>
                  <div class="payment-dropzone-title">
                    Tarik &amp; lepas bukti transfer di sini atau <span style="color: #619ae6; text-decoration: underline;">klik untuk browse</span>
                  </div>
                  <div class="payment-dropzone-sub">
                    Format: JPG, PNG, PDF (Maks. 2MB)
                  </div>
                </div>

                <!-- Preview Thumbnail Instan saat File Terpilih -->
                <div class="payment-proof-preview" id="dropzone-preview-state" style="display: none;" onclick="event.stopPropagation();">
                  <div class="proof-thumb-box" id="proof-thumb-container">
                    <!-- Diisi gambar mini atau ikon PDF oleh JS -->
                  </div>
                  <div class="proof-file-info">
                    <div class="proof-file-name" id="proof-file-name">bukti_transfer.jpg</div>
                    <div class="proof-file-size" id="proof-file-size">240 KB &bull; Siap diunggah</div>
                  </div>
                  <button type="button" class="proof-remove-btn" id="remove-proof-btn" onclick="removePaymentProof(event)" title="Hapus file bukti pembayaran">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="18" y1="6" x2="6" y2="18"></line>
                      <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                  </button>
                </div>
              </div>
              <div id="proof-error-msg" class="proof-error-msg" style="display: none;"></div>
            </div>

            <!-- Checkbox Persetujuan PO -->
            <div style="margin: 18px 0 16px; font-size: 12px; color: var(--color-ash, #737780);">
              <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer;">
                <input type="checkbox" required checked style="margin-top: 3px; accent-color: #619ae6;">
                <span>Saya menyetujui ketentuan Pre-Order (PO) dengan estimasi waktu produksi sekitar 14-21 hari kerja setelah kuota batch ditutup.</span>
              </label>
            </div>

            <!-- Tombol "Konfirmasi Pesanan" (Disabled secara default sampai bukti pembayaran diunggah) -->
            <button type="submit" id="confirm-order-btn" class="btn btn-accent btn-block btn-lg btn-confirm-disabled" disabled>
              <span id="confirm-btn-text">Konfirmasi Pesanan</span>
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
<div id="order-success-modal" class="modal-overlay <?= $flashSuccessOrder ? 'active' : '' ?>">
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
    <div style="background: var(--surface-plaster, #ecedee); border: 2px dashed var(--color-ink-black, #111111); border-radius: 18px; padding: 18px; margin-bottom: 18px;">
      <span style="font-size: 11px; text-transform: uppercase; font-family: var(--font-space-mono); letter-spacing: 0.05em; color: var(--color-ash, #737780); font-weight: 700;">Nomor Resi / No. Pesanan Anda:</span>
      <div id="success-order-code" style="font-family: var(--font-space-mono); font-size: 26px; font-weight: 800; color: var(--color-ink-black, #111111); letter-spacing: 0.02em; margin: 6px 0;">
        <?= htmlspecialchars($flashSuccessOrder['order_code'] ?? 'PO-2026-8891') ?>
      </div>
      <div style="font-size: 13px; font-family: var(--font-space-mono); color: #619ae6; font-weight: 700;">
        Nominal Pembayaran Diterima: <span id="success-order-amount"><?= isset($flashSuccessOrder['due']) ? formatRupiah($flashSuccessOrder['due']) : 'Rp 0' ?></span>
      </div>
    </div>

    <div style="background: rgba(97, 154, 230, 0.1); border: 1px solid rgba(97, 154, 230, 0.3); border-radius: 16px; padding: 14px 16px; font-size: 12.5px; color: var(--color-ink-black, #111111); margin-bottom: 24px; text-align: left; line-height: 1.55;">
      <strong>Status: Menunggu Verifikasi Admin</strong><br>
      Bukti pembayaran Anda telah berhasil diterima oleh sistem dan saat ini sedang diverifikasi oleh Admin Fastender. Anda dapat memantau status pesanan secara berkala menggunakan Nomor Resi di atas.
    </div>

    <div style="display: flex; gap: 12px;">
      <a id="success-track-link" href="lacak.php?resi=<?= urlencode($flashSuccessOrder['order_code'] ?? 'PO-2026-8891') ?>" class="btn btn-accent btn-block">
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
  const FULL_QUOTA_PRODUCT_IDS = <?= json_encode(getFullQuotaProductIds()) ?>;
  let currentShippingCost = 15000;
  let paymentScheme = "dp"; // "dp" atau "full"
  let selectedProofFile = null;
  const HAS_SERVER_SUCCESS = <?= $flashSuccessOrder ? 'true' : 'false' ?>;

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
    if (HAS_SERVER_SUCCESS) {
      saveCart([]);
      return;
    }

    const cart = getCart();
    if (cart.length === 0) {
      alert("Keranjang Anda masih kosong. Silakan pilih produk terlebih dahulu.");
      window.location.href = "katalog.php";
      return;
    }

    // Pastikan data keranjang tersimpan dalam bentuk yang sudah ternormalisasi
    saveCart(cart);
    renderCheckoutSummary();
    setupPaymentProofDropzone();
  });

  function selectPayMethodCard(labelEl) {
    const container = document.getElementById("payment-methods-grid");
    if (!container) return;
    container.querySelectorAll(".radio-card").forEach(card => card.classList.remove("active"));
    labelEl.classList.add("active");

    const input = labelEl.querySelector("input[name='pay_method']");
    if (input) {
      input.checked = true;
      const method = input.value;
      const bcaBox = document.getElementById("pay-detail-bca");
      const mandiriBox = document.getElementById("pay-detail-mandiri");
      const qrisBox = document.getElementById("pay-detail-qris");

      if (bcaBox) bcaBox.style.display = (method === "BCA") ? "block" : "none";
      if (mandiriBox) mandiriBox.style.display = (method === "Mandiri") ? "block" : "none";
      if (qrisBox) qrisBox.style.display = (method === "QRIS") ? "block" : "none";
    }
  }

  // ==========================================
  // DRAG & DROP UPLOAD BUKTI PEMBAYARAN
  // ==========================================
  function setupPaymentProofDropzone() {
    const dropzone = document.getElementById("payment-dropzone");
    const fileInput = document.getElementById("bukti_pembayaran");
    if (!dropzone || !fileInput) return;

    fileInput.addEventListener("change", (e) => {
      if (e.target.files && e.target.files.length > 0) {
        validateAndPreviewProofFile(e.target.files[0]);
      } else {
        resetPaymentProofUI();
      }
    });

    ["dragenter", "dragover"].forEach(evtName => {
      dropzone.addEventListener(evtName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.add("dragover");
      });
    });

    ["dragleave", "drop"].forEach(evtName => {
      dropzone.addEventListener(evtName, (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropzone.classList.remove("dragover");
      });
    });

    dropzone.addEventListener("drop", (e) => {
      const dt = e.dataTransfer;
      if (dt && dt.files && dt.files.length > 0) {
        const droppedFile = dt.files[0];
        // Sinkronkan ke <input type="file">
        try {
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(droppedFile);
          fileInput.files = dataTransfer.files;
        } catch (err) {
          // Fallback jika browser membatasi assignment FileList
        }
        validateAndPreviewProofFile(droppedFile);
      }
    });
  }

  function triggerProofFileSelect() {
    const fileInput = document.getElementById("bukti_pembayaran");
    if (fileInput) fileInput.click();
  }

  function validateAndPreviewProofFile(file) {
    const errorEl = document.getElementById("proof-error-msg");
    const fileInput = document.getElementById("bukti_pembayaran");
    if (errorEl) {
      errorEl.style.display = "none";
      errorEl.textContent = "";
    }

    const allowedTypes = ["image/jpeg", "image/jpg", "image/png", "application/pdf"];
    const ext = file.name.split(".").pop().toLowerCase();
    const allowedExts = ["jpg", "jpeg", "png", "pdf"];
    const maxSize = 2 * 1024 * 1024; // 2MB

    if (!allowedTypes.includes(file.type) && !allowedExts.includes(ext)) {
      showProofError("Format file tidak valid! Gunakan file JPG, PNG, atau PDF.");
      if (fileInput) fileInput.value = "";
      resetPaymentProofUI(true);
      return;
    }

    if (file.size > maxSize) {
      showProofError("Ukuran file terlalu besar! Maksimal ukuran file adalah 2MB.");
      if (fileInput) fileInput.value = "";
      resetPaymentProofUI(true);
      return;
    }

    selectedProofFile = file;

    const emptyState = document.getElementById("dropzone-empty-state");
    const previewState = document.getElementById("dropzone-preview-state");
    const thumbContainer = document.getElementById("proof-thumb-container");
    const nameEl = document.getElementById("proof-file-name");
    const sizeEl = document.getElementById("proof-file-size");
    const statusBadge = document.getElementById("proof-status-badge");
    const confirmBtn = document.getElementById("confirm-order-btn");
    const dropzone = document.getElementById("payment-dropzone");

    if (emptyState) emptyState.style.display = "none";
    if (previewState) previewState.style.display = "flex";
    if (dropzone) dropzone.classList.add("has-file");

    const sizeKB = (file.size / 1024).toFixed(1);
    const sizeLabel = file.size >= 1024 * 1024
      ? `${(file.size / (1024 * 1024)).toFixed(2)} MB`
      : `${sizeKB} KB`;

    if (nameEl) nameEl.textContent = file.name;
    if (sizeEl) sizeEl.textContent = `${sizeLabel} • Siap diunggah`;

    if (statusBadge) {
      statusBadge.textContent = "File Siap";
      statusBadge.classList.add("ready");
    }

    const fullQuotaItems = (typeof hasFullQuotaInCart === "function") ? hasFullQuotaInCart() : [];
    if (confirmBtn) {
      if (fullQuotaItems.length > 0) {
        confirmBtn.disabled = true;
        confirmBtn.classList.add("btn-confirm-disabled");
        confirmBtn.style.cursor = "not-allowed";
        const confirmBtnText = document.getElementById("confirm-btn-text");
        if (confirmBtnText) confirmBtnText.textContent = "Kuota Produk Habis";
      } else {
        confirmBtn.disabled = false;
        confirmBtn.classList.remove("btn-confirm-disabled");
        confirmBtn.style.cursor = "";
        const confirmBtnText = document.getElementById("confirm-btn-text");
        if (confirmBtnText) confirmBtnText.textContent = "Konfirmasi Pesanan";
      }
    }

    if (file.type === "application/pdf" || ext === "pdf") {
      if (thumbContainer) {
        thumbContainer.innerHTML = `
          <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; height: 100%; background: rgba(97, 154, 230, 0.12); color: #619ae6; font-weight: 800; font-size: 11px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            <span style="margin-top: 2px;">PDF</span>
          </div>
        `;
      }
    } else {
      const reader = new FileReader();
      reader.onload = (e) => {
        if (thumbContainer) {
          thumbContainer.innerHTML = `<img src="${e.target.result}" alt="Preview Bukti Pembayaran" style="width: 100%; height: 100%; object-fit: cover;">`;
        }
      };
      reader.readAsDataURL(file);
    }
  }

  function showProofError(msg) {
    const errorEl = document.getElementById("proof-error-msg");
    if (errorEl) {
      errorEl.textContent = msg;
      errorEl.style.display = "block";
    }
    if (typeof showToast === "function") {
      showToast(msg, "error");
    }
  }

  function removePaymentProof(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    const fileInput = document.getElementById("bukti_pembayaran");
    if (fileInput) fileInput.value = "";
    resetPaymentProofUI();
  }

  function resetPaymentProofUI(keepError = false) {
    selectedProofFile = null;
    const emptyState = document.getElementById("dropzone-empty-state");
    const previewState = document.getElementById("dropzone-preview-state");
    const thumbContainer = document.getElementById("proof-thumb-container");
    const statusBadge = document.getElementById("proof-status-badge");
    const confirmBtn = document.getElementById("confirm-order-btn");
    const dropzone = document.getElementById("payment-dropzone");
    const errorEl = document.getElementById("proof-error-msg");

    if (emptyState) emptyState.style.display = "flex";
    if (previewState) previewState.style.display = "none";
    if (thumbContainer) thumbContainer.innerHTML = "";
    if (dropzone) dropzone.classList.remove("has-file");

    if (statusBadge) {
      statusBadge.textContent = "Wajib Diunggah";
      statusBadge.classList.remove("ready");
    }

    if (confirmBtn) {
      confirmBtn.disabled = true;
      confirmBtn.classList.add("btn-confirm-disabled");
    }

    if (!keepError && errorEl) {
      errorEl.style.display = "none";
      errorEl.textContent = "";
    }
  }

  let isPickupUsmaActive = false;

  function selectPickupUSMA(forceState = null, fromCourierChange = false) {
    const addressInput = document.getElementById("ship-address");
    const courierSelect = document.getElementById("ship-courier");
    const provinceSelect = document.getElementById("ship-province");
    const cityInput = document.getElementById("ship-city");
    const postalInput = document.getElementById("ship-postal");
    const postalGroup = document.getElementById("ship-postal-group");
    const postalCourierGrid = document.getElementById("postal-courier-grid");
    const manualFields = document.getElementById("manual-address-fields");
    const pickupBanner = document.getElementById("pickup-usma-banner");
    const pickupBtn = document.getElementById("btn-pickup-usma");
    const pickupBtnText = document.getElementById("btn-pickup-usma-text");
    const pickupHidden = document.getElementById("is-pickup-usma");

    const nextState = (typeof forceState === "boolean") ? forceState : !isPickupUsmaActive;
    isPickupUsmaActive = nextState;

    if (isPickupUsmaActive) {
      // Kosongkan / isi default "-" pada semua field alamat & hilangkan atribut required
      if (provinceSelect) {
        provinceSelect.value = "-";
        provinceSelect.removeAttribute("required");
      }
      if (cityInput) {
        cityInput.value = "-";
        cityInput.removeAttribute("required");
      }
      if (postalInput) {
        postalInput.value = "-";
        postalInput.removeAttribute("required");
      }
      if (addressInput) {
        addressInput.value = "-";
        addressInput.removeAttribute("required");
      }

      // Sembunyikan input alamat manual & tampilkan tanda visual Sekretariat USMA
      if (manualFields) manualFields.style.display = "none";
      if (postalGroup) postalGroup.style.display = "none";
      if (postalCourierGrid) postalCourierGrid.style.gridTemplateColumns = "1fr";
      if (pickupBanner) pickupBanner.style.display = "block";

      if (pickupBtn) pickupBtn.classList.add("active");
      if (pickupBtnText) pickupBtnText.textContent = "✓ Ambil di Sekretariat USMA";
      if (pickupHidden) pickupHidden.value = "1";

      // Otomatis pilih ekspedisi Ambil di Sekretariat / Kampus (Gratis Ongkir Rp 0)
      if (courierSelect) {
        courierSelect.value = "0";
      }
      currentShippingCost = 0;
      renderCheckoutSummary();

      if (typeof showToast === "function") {
        showToast("Pengambilan di Sekretariat USMA aktif (Tanpa isi alamat & Gratis Ongkir Rp 0)", "success");
      }
    } else {
      // Kembalikan form alamat pengiriman menjadi aktif & required kembali
      if (manualFields) manualFields.style.display = "block";
      if (postalGroup) postalGroup.style.display = "block";
      if (postalCourierGrid) postalCourierGrid.style.gridTemplateColumns = "";
      if (pickupBanner) pickupBanner.style.display = "none";

      if (provinceSelect) {
        if (provinceSelect.value === "-") provinceSelect.value = "";
        provinceSelect.setAttribute("required", "required");
      }
      if (cityInput) {
        if (cityInput.value === "-") cityInput.value = "";
        cityInput.setAttribute("required", "required");
      }
      if (postalInput) {
        if (postalInput.value === "-") postalInput.value = "";
      }
      if (addressInput) {
        if (addressInput.value === "-") addressInput.value = "";
        addressInput.setAttribute("required", "required");
      }

      if (pickupBtn) pickupBtn.classList.remove("active");
      if (pickupBtnText) pickupBtnText.textContent = "Ambil di Sekretariat USMA";
      if (pickupHidden) pickupHidden.value = "0";

      if (!fromCourierChange && courierSelect && courierSelect.value === "0") {
        courierSelect.value = "15000";
      }
      currentShippingCost = courierSelect ? safeParseNumber(courierSelect.value, 15000) : 15000;
      renderCheckoutSummary();

      if (typeof showToast === "function") {
        showToast("Pengiriman dikembalikan ke alamat manual", "info");
      }
    }
  }

  function hasFullQuotaInCart() {
    const cart = getCart();
    return cart.filter(item => {
      const itemId = safeParseNumber(item.id || item.productId, 0);
      return FULL_QUOTA_PRODUCT_IDS.includes(itemId);
    });
  }

  function renderCheckoutSummary() {
    const cart = getCart();
    const summaryContainer = document.getElementById("checkout-items-summary");
    const cartHiddenInput = document.getElementById("cart-items-input");
    if (cartHiddenInput) {
      cartHiddenInput.value = JSON.stringify(cart);
    }
    
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

      const itemId = safeParseNumber(item.id || item.productId, 0);
      const isQuotaFull = FULL_QUOTA_PRODUCT_IDS.includes(itemId);

      return `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; padding-bottom: 8px; border-bottom: 1px solid var(--border-light); ${isQuotaFull ? 'background: #fffdfd; padding: 6px; border-radius: 8px; border: 1px solid #fecaca;' : ''}">
          <div style="display: flex; align-items: center; gap: 10px;">
            ${item.image ? `<img src="${item.image}" alt="${itemName}" style="width: 38px; height: 38px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">` : ''}
            <div>
              <div style="font-weight: 600; color: var(--text-main);">${itemName}</div>
              ${isQuotaFull ? `
                <div style="display: inline-block; background: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 4px; margin: 2px 0;">
                  ⚠️ Kuota Habis
                </div>
              ` : ''}
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

    const fullQuotaItems = hasFullQuotaInCart();
    const quotaAlertEl = document.getElementById("checkout-quota-alert");
    const confirmBtn = document.getElementById("confirm-order-btn");
    const confirmBtnText = document.getElementById("confirm-btn-text");

    if (quotaAlertEl) {
      if (fullQuotaItems.length > 0) {
        const itemNames = fullQuotaItems.map(i => i.name || "Produk").join(", ");
        quotaAlertEl.style.display = "block";
        quotaAlertEl.innerHTML = `
          <div style="display: flex; gap: 8px; align-items: flex-start;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0; margin-top: 2px;">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <div>
              <strong style="color: #991b1b;">Pesanan Tidak Dapat Diproses:</strong><br>
              Pesanan gagal diproses karena kuota produk <strong>${itemNames}</strong> telah habis. Silakan kembali ke keranjang untuk menghapus produk ini sebelum melanjutkan.
            </div>
          </div>
        `;
        if (confirmBtn) {
          confirmBtn.disabled = true;
          confirmBtn.classList.add("btn-confirm-disabled");
          confirmBtn.style.cursor = "not-allowed";
        }
        if (confirmBtnText) {
          confirmBtnText.textContent = "Kuota Produk Habis";
        }
      } else {
        quotaAlertEl.style.display = "none";
        quotaAlertEl.innerHTML = "";
        if (confirmBtn && selectedProofFile) {
          confirmBtn.disabled = false;
          confirmBtn.classList.remove("btn-confirm-disabled");
          confirmBtn.style.cursor = "";
          if (confirmBtnText) {
            confirmBtnText.textContent = "Konfirmasi Pesanan";
          }
        }
      }
    }
  }

  function updateShippingCost() {
    const courierSelect = document.getElementById("ship-courier");
    if (!courierSelect) return;

    if (courierSelect.value === "0") {
      if (!isPickupUsmaActive) {
        selectPickupUSMA(true, true);
        return;
      }
    } else if (isPickupUsmaActive) {
      selectPickupUSMA(false, true);
      return;
    }

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

  async function handleCheckoutSubmit(e) {
    e.preventDefault();
    const cart = getCart();
    if (cart.length === 0) return;

    const fullQuotaItems = (typeof hasFullQuotaInCart === "function") ? hasFullQuotaInCart() : [];
    if (fullQuotaItems.length > 0) {
      const itemNames = fullQuotaItems.map(i => i.name || "Produk").join(", ");
      const errorMsg = `Pesanan gagal diproses karena kuota produk ${itemNames} telah habis.`;
      showProofError(errorMsg);
      if (typeof showToast === "function") {
        showToast(errorMsg, "error");
      }
      return;
    }

    const fileInput = document.getElementById("bukti_pembayaran");
    const proofFile = selectedProofFile || (fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null);
    if (!proofFile) {
      showProofError("Silakan unggah bukti pembayaran terlebih dahulu sebelum konfirmasi pesanan.");
      return;
    }

    const confirmBtn = document.getElementById("confirm-order-btn");
    const confirmBtnText = document.getElementById("confirm-btn-text");
    if (confirmBtn) confirmBtn.disabled = true;
    if (confirmBtnText) confirmBtnText.textContent = "Mengunggah & Memproses...";

    const formEl = document.getElementById("checkout-form");
    const formData = new FormData(formEl);
    formData.set("cart_items", JSON.stringify(cart));
    formData.set("is_ajax", "1");
    if (selectedProofFile && (!fileInput.files || fileInput.files.length === 0)) {
      formData.set("bukti_pembayaran", selectedProofFile);
    }

    try {
      const response = await fetch("proses-checkout.php", {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
      });

      const result = await response.json();

      if (!result || !result.success) {
        const errMsg = (result && result.message) ? result.message : "Gagal memproses pesanan. Silakan periksa kembali bukti pembayaran Anda.";
        showProofError(errMsg);
        if (confirmBtn) confirmBtn.disabled = false;
        if (confirmBtnText) confirmBtnText.textContent = "Konfirmasi Pesanan";
        return;
      }

      const savedOrder = result.order;

      // Simpan juga ke localStorage agar fitur Lacak & Admin sinkron di browser
      let allOrders = JSON.parse(localStorage.getItem("fastender_all_orders") || "[]");
      allOrders.unshift(savedOrder);
      localStorage.setItem("fastender_all_orders", JSON.stringify(allOrders));

      // Kosongkan keranjang
      saveCart([]);

      // Tampilkan modal sukses
      document.getElementById("success-order-code").textContent = savedOrder.order_code || savedOrder.orderId;
      document.getElementById("success-order-amount").textContent = formatRupiah(savedOrder.due || savedOrder.total_bayar);
      document.getElementById("success-track-link").href = `lacak.php?resi=${encodeURIComponent(savedOrder.order_code || savedOrder.orderId)}`;
      document.getElementById("order-success-modal").classList.add("active");
    } catch (err) {
      console.error("Checkout submit error:", err);
      showProofError("Terjadi gangguan jaringan saat mengunggah bukti pembayaran. Silakan coba lagi.");
      if (confirmBtn) confirmBtn.disabled = false;
      if (confirmBtnText) confirmBtnText.textContent = "Konfirmasi Pesanan";
    }
  }
</script>
