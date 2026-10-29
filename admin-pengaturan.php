<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Pengaturan Toko & Pembayaran (admin-pengaturan.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

$adminPage = 'pengaturan';
$adminTitle = 'Pengaturan Toko';

$flashSuccess = '';
$flashError = '';

// Handle POST AJAX Update Status Toko (Auto-save toggle switch)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_store_status') {
    $requestedStatus = isset($_POST['status']) ? (strtolower(trim((string)$_POST['status'])) === 'tutup' ? 'tutup' : 'buka') : 'buka';
    $updatedStatus = updateStoreStatus($requestedStatus);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'status' => $updatedStatus,
        'message' => 'Status operasional toko berhasil diperbarui!'
    ]);
    exit;
}

// Handle POST Simpan Pengaturan Toko
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_store_settings') {
    $qrisFile = $_FILES['qris_file'] ?? null;
    $updated = saveStoreSettings($_POST, $qrisFile);

    // Cek jika request via AJAX
    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['is_ajax'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'settings' => $updated,
            'message' => 'Pengaturan toko berhasil diperbarui!'
        ]);
        exit;
    }

    $flashSuccess = 'Pengaturan toko dan rincian pembayaran berhasil disimpan!';
}

$settings = getStoreSettings();
$status_toko = $settings['status_toko'] ?? 'buka';
$isOpen = ($status_toko === 'buka');
$qrisImage = trim((string)($settings['qris_image'] ?? ''));
$hasQris = ($qrisImage !== '' && is_file(__DIR__ . '/' . ltrim($qrisImage, '/')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengaturan Toko - Admin Fastender</title>
  
  <!-- Google Fonts: Montserrat & Fira Code -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="variables.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/admin.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
  <link rel="apple-touch-icon" href="assets/images/favicon.png">
</head>
<body class="admin-body">

  <div class="admin-layout">

    <!-- Include Admin Sidebar -->
    <?php include __DIR__ . '/includes/admin-sidebar.php'; ?>

    <!-- Main Content Kanan -->
    <main class="admin-main">
      
      <!-- Top Header Admin -->
      <?php include __DIR__ . '/includes/admin-header.php'; ?>

      <!-- Area Konten Utama -->
      <div class="admin-content">

        <!-- Title & Action Button -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <h1 class="admin-page-title" style="margin: 0;">Pengaturan Toko</h1>
          <div style="display: flex; gap: 10px;">
            <a href="keranjang.php" target="_blank" class="btn btn-outline-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
              Keranjang
            </a>
          </div>
        </div>

        <?php if (!empty($flashSuccess)): ?>
          <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 14px 18px; border-radius: var(--radius-md, 8px); font-size: 14px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
          <div style="background: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 14px 18px; border-radius: var(--radius-md, 8px); font-size: 14px; margin-bottom: 24px;">
            <?= htmlspecialchars($flashError) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="admin-pengaturan.php" enctype="multipart/form-data">
          <input type="hidden" name="action" value="save_store_settings">
          <input type="hidden" name="status_toko_present" value="1">

          <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; align-items: start;">
            
            <!-- KOLOM KIRI: STATUS TOKO & KONTAK -->
            <div style="display: flex; flex-direction: column; gap: 24px;">

              <!-- CARD 1: STATUS OPERASIONAL PRE-ORDER -->
              <div style="background: #ffffff; border-radius: 12px; border: 1px solid var(--color-plaster, #e2e8f0); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                  <div style="width: 36px; height: 36px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                      <line x1="8" y1="21" x2="16" y2="21"></line>
                      <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                  </div>
                  <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Status Operasional Toko</h2>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">Kontrol izin transaksi checkout pelanggan di platform Fastender.</p>
                  </div>
                </div>

                <div class="store-status-toggle-wrapper" style="margin-bottom: 18px;">
                  <label class="switch-toggle-btn">
                    <input type="checkbox" name="status_toko" value="buka" id="toggleStatusToko" class="toggle-status-toko" <?= ($status_toko === 'buka') ? 'checked' : '' ?>>
                    <span class="switch-slider"></span>
                  </label>
                  <div style="flex-grow: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                      <span id="status-badge" style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; letter-spacing: 0.02em; transition: all 0.2s ease; <?= ($status_toko === 'buka') ? 'background: #dcfce7; color: #166534;' : 'background: #fee2e2; color: #991b1b;' ?>">
                        <?= ($status_toko === 'buka') ? '• TOKO BUKA (Checkout Aktif)' : '• TOKO TUTUP (Checkout Ditutup)' ?>
                      </span>
                      <span id="toggle-save-feedback" style="font-size: 11.5px; font-weight: 600; color: #64748b;"></span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin: 4px 0 0;" id="status-desc">
                      <?= ($status_toko === 'buka') ? 'Pelanggan dapat melakukan pemesanan dan proses checkout secara normal.' : 'Katalog tetap dapat dilihat, namun tombol checkout di keranjang ditutup sementara.' ?>
                    </p>
                  </div>
                </div>

                <!-- Textarea Pesan Penutupan -->
                <div class="form-group" id="group-pesan-tutup" style="<?= $isOpen ? 'display: none;' : '' ?>">
                  <label for="inp-pesan-tutup" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                    Pesan Informasi Penutupan (Tampil di Keranjang)
                  </label>
                  <textarea name="pesan_toko_tutup" id="inp-pesan-tutup" rows="3" class="form-control" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 13px; font-family: inherit; resize: vertical;" placeholder="Contoh: Pre-Order sedang ditutup sementara untuk persiapan produksi batch berikutnya."><?= htmlspecialchars($settings['pesan_toko_tutup'] ?? '') ?></textarea>
                  <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">Pesan ini akan ditampilkan dalam banner informasi elegan di halaman keranjang saat tombol checkout dinonaktifkan.</span>
                </div>
              </div>

              <!-- CARD 2: KONTAK & WHATSAPP RESMI -->
              <div style="background: #ffffff; border-radius: 12px; border: 1px solid var(--color-plaster, #e2e8f0); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                  <div style="width: 36px; height: 36px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                  </div>
                  <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Kontak WhatsApp Resmi</h2>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">Nomor CS untuk floating widget dan tombol bantuan toko.</p>
                  </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                  <label for="inp-whatsapp" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                    Nomor WhatsApp CS (Dimulai dengan 08... atau 62...)
                  </label>
                  <div style="position: relative;">
                    <input type="text" name="nomor_whatsapp" id="inp-whatsapp" class="form-control" value="<?= htmlspecialchars($settings['nomor_whatsapp'] ?? '081234567890') ?>" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; font-size: 14px;" placeholder="081234567890" oninput="updateWhatsAppPreview()">
                  </div>
                  <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">Nomor ini akan otomatis dihubungkan ke link https://wa.me/62...</span>
                </div>

                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                  <div>
                    <span style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.04em;">Preview Link WhatsApp CS:</span>
                    <div id="wa-preview-link-text" style="font-size: 13px; font-weight: 600; color: #059669; word-break: break-all;">
                      <?= htmlspecialchars(formatWhatsAppUrl($settings['nomor_whatsapp'] ?? '081234567890')) ?>
                    </div>
                  </div>
                  <a href="<?= htmlspecialchars(formatWhatsAppUrl($settings['nomor_whatsapp'] ?? '081234567890', 'Halo CS Fastender, saya ingin bertanya tentang pre-order apparel.')) ?>" id="wa-test-btn" target="_blank" class="btn btn-outline-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; padding: 6px 12px; border-color: #10b981; color: #059669;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                      <polyline points="15 3 21 3 21 9"></polyline>
                      <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    Tes Kirim Pesan
                  </a>
                </div>
              </div>

            </div>

            <!-- KOLOM KANAN: REKENING BANK & QRIS -->
            <div style="display: flex; flex-direction: column; gap: 24px;">

              <!-- CARD 3: REKENING BANK -->
              <div style="background: #ffffff; border-radius: 12px; border: 1px solid var(--color-plaster, #e2e8f0); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                  <div style="width: 36px; height: 36px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                      <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                  </div>
                  <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Rekening Pembayaran Bank</h2>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">Detail nomor rekening untuk metode transfer di halaman checkout.</p>
                  </div>
                </div>

                <!-- Bagian BCA -->
                <div style="border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 16px;">
                  <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                    <span style="font-size: 12px; font-weight: 700; background: #00529c; color: #ffffff; padding: 2px 8px; border-radius: 4px;">BCA</span>
                    <span style="font-size: 13px; font-weight: 600; color: #334155;">Bank Central Asia</span>
                  </div>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                      <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Nomor Rekening</label>
                      <input type="text" name="rekening_bca_no" class="form-control" value="<?= htmlspecialchars($settings['rekening_bca_no'] ?? '828-091-2334') ?>" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 13px;">
                    </div>
                    <div>
                      <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Atas Nama</label>
                      <input type="text" name="rekening_bca_nama" class="form-control" value="<?= htmlspecialchars($settings['rekening_bca_nama'] ?? 'Fastender Konveksi') ?>" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 13px;">
                    </div>
                  </div>
                </div>

                <!-- Bagian Mandiri -->
                <div>
                  <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                    <span style="font-size: 12px; font-weight: 700; background: #002d62; color: #ffb81c; padding: 2px 8px; border-radius: 4px;">MANDIRI</span>
                    <span style="font-size: 13px; font-weight: 600; color: #334155;">Bank Mandiri</span>
                  </div>
                  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                      <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Nomor Rekening</label>
                      <input type="text" name="rekening_mandiri_no" class="form-control" value="<?= htmlspecialchars($settings['rekening_mandiri_no'] ?? '144-00-2026889-1') ?>" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 13px;">
                    </div>
                    <div>
                      <label style="font-size: 12px; font-weight: 600; color: #475569; display: block; margin-bottom: 4px;">Atas Nama</label>
                      <input type="text" name="rekening_mandiri_nama" class="form-control" value="<?= htmlspecialchars($settings['rekening_mandiri_nama'] ?? 'Fastender Konveksi') ?>" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 13px;">
                    </div>
                  </div>
                </div>

              </div>

              <!-- CARD 4: BARCODE QRIS STATIS -->
              <div style="background: #ffffff; border-radius: 12px; border: 1px solid var(--color-plaster, #e2e8f0); padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                  <div style="width: 36px; height: 36px; border-radius: 8px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="3" width="7" height="7"></rect>
                      <rect x="14" y="3" width="7" height="7"></rect>
                      <rect x="14" y="14" width="7" height="7"></rect>
                      <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                  </div>
                  <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">Barcode QRIS Toko</h2>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">Upload gambar kode QRIS resmi untuk pembayaran instan.</p>
                  </div>
                </div>

                <div style="display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap;">
                  <!-- Kotak Preview QRIS -->
                  <div class="qris-preview-box" id="qris-preview-container">
                    <?php if ($hasQris): ?>
                      <img src="<?= htmlspecialchars($qrisImage) ?>" alt="QRIS Barcode" id="qris-img-preview">
                    <?php else: ?>
                      <div class="qris-preview-empty" id="qris-empty-placeholder">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                          <rect x="3" y="3" width="7" height="7"></rect>
                          <rect x="14" y="3" width="7" height="7"></rect>
                          <rect x="14" y="14" width="7" height="7"></rect>
                          <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Belum ada barcode QRIS</span>
                      </div>
                      <img src="" alt="QRIS Barcode Preview" id="qris-img-preview" style="display: none;">
                    <?php endif; ?>
                  </div>

                  <!-- Input File & Action Hapus -->
                  <div style="flex: 1; min-width: 220px;">
                    <label style="font-size: 13px; font-weight: 600; color: #334155; display: block; margin-bottom: 6px;">
                      Pilih Gambar Barcode QRIS
                    </label>
                    <input type="file" name="qris_file" id="inp-qris-file" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" class="form-control" style="width: 100%; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px; font-size: 13px; margin-bottom: 10px;" onchange="previewQrisFile(event)">

                    <?php if ($hasQris): ?>
                      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px;">
                        <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 4px;">File QRIS Aktif:</span>
                        <code style="font-size: 11px; color: #2563eb;"><?= htmlspecialchars($qrisImage) ?></code>
                        <div style="margin-top: 8px;">
                          <label style="font-size: 12px; color: #dc2626; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="checkbox" name="remove_qris" value="1" onchange="toggleRemoveQris(this)">
                            <span>Hapus gambar barcode QRIS saat ini</span>
                          </label>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>

              </div>

            </div>

          </div>

          <!-- TOMBOL SIMPAN UTAMA -->
          <div style="margin-top: 32px; padding: 20px 24px; background: #ffffff; border-radius: 12px; border: 1px solid var(--color-plaster, #e2e8f0); display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
            <div>
              <h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0;">Simpan Perubahan Pengaturan</h4>
              <p style="font-size: 12px; color: #64748b; margin: 2px 0 0;">Perubahan status toko, kontak, dan rekening akan langsung diterapkan ke seluruh sistem Fastender.</p>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Simpan Pengaturan Toko
            </button>
          </div>

        </form>

      </div>
    </main>

  </div>

  <style>
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  </style>

  <script>
    // Inisialisasi Auto-Save Toggle Status Toko via AJAX
    document.addEventListener("DOMContentLoaded", () => {
      const toggle = document.getElementById("toggleStatusToko") || document.getElementById("toggle-status-toko");
      const badge = document.getElementById("status-badge");
      const desc = document.getElementById("status-desc");
      const groupPesan = document.getElementById("group-pesan-tutup");
      const feedback = document.getElementById("toggle-save-feedback");

      function renderStatusUI(isOpen) {
        if (isOpen) {
          if (badge) {
            badge.textContent = "• TOKO BUKA (Checkout Aktif)";
            badge.style.background = "#dcfce7";
            badge.style.color = "#166534";
          }
          if (desc) {
            desc.textContent = "Pelanggan dapat melakukan pemesanan dan proses checkout secara normal.";
          }
          if (groupPesan) groupPesan.style.display = "none";
        } else {
          if (badge) {
            badge.textContent = "• TOKO TUTUP (Checkout Ditutup)";
            badge.style.background = "#fee2e2";
            badge.style.color = "#991b1b";
          }
          if (desc) {
            desc.textContent = "Katalog tetap dapat dilihat, namun tombol checkout di keranjang ditutup sementara.";
          }
          if (groupPesan) groupPesan.style.display = "block";
        }
      }

      if (toggle) {
        toggle.addEventListener("change", async function () {
          const isChecked = this.checked;
          const targetStatus = isChecked ? "buka" : "tutup";

          // Tampilkan feedback indikator kecil
          if (feedback) {
            feedback.style.display = "inline-flex";
            feedback.style.color = "#2563eb";
            feedback.innerHTML = `
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin-right: 4px;">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                <path d="M12 2a10 10 0 0 1 10 10"></path>
              </svg>
              <span>Menyimpan...</span>
            `;
          }

          renderStatusUI(isChecked);

          const formData = new FormData();
          formData.append("action", "update_store_status");
          formData.append("status", targetStatus);

          try {
            const res = await fetch("admin-pengaturan.php", {
              method: "POST",
              headers: {
                "X-Requested-With": "XMLHttpRequest"
              },
              body: formData
            });

            const data = await res.json();

            if (data && data.success) {
              if (feedback) {
                feedback.style.color = "#16a34a";
                feedback.innerHTML = `✓ Tersimpan`;
                setTimeout(() => {
                  feedback.style.display = "none";
                  feedback.innerHTML = "";
                }, 2200);
              }
            } else {
              throw new Error((data && data.message) ? data.message : "Gagal menyimpan");
            }
          } catch (err) {
            console.error("Gagal auto-save status toko:", err);
            // Rollback posisi toggle jika gagal
            this.checked = !isChecked;
            renderStatusUI(!isChecked);
            if (feedback) {
              feedback.style.color = "#dc2626";
              feedback.innerHTML = `✕ Gagal menyimpan ke database`;
              setTimeout(() => {
                feedback.style.display = "none";
                feedback.innerHTML = "";
              }, 3000);
            }
          }
        });
      }
    });

    // Live update link WhatsApp test
    function updateWhatsAppPreview() {
      const input = document.getElementById('inp-whatsapp');
      const previewText = document.getElementById('wa-preview-link-text');
      const testBtn = document.getElementById('wa-test-btn');

      let val = input.value.replace(/[^0-9]/g, '');
      if (val.startsWith('0')) {
        val = '62' + val.substring(1);
      } else if (val.startsWith('8')) {
        val = '62' + val;
      }
      if (!val) {
        val = '6281234567890';
      }

      const fullUrl = 'https://wa.me/' + val;
      previewText.textContent = fullUrl;
      testBtn.href = fullUrl + '?text=' + encodeURIComponent('Halo CS Fastender, saya ingin bertanya tentang pre-order apparel.');
    }

    // Live preview file QRIS baru
    function previewQrisFile(event) {
      const file = event.target.files[0];
      const img = document.getElementById('qris-img-preview');
      const placeholder = document.getElementById('qris-empty-placeholder');

      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          img.src = e.target.result;
          img.style.display = 'block';
          if (placeholder) {
            placeholder.style.display = 'none';
          }
        };
        reader.readAsDataURL(file);
      }
    }

    function toggleRemoveQris(checkbox) {
      const img = document.getElementById('qris-img-preview');
      const placeholder = document.getElementById('qris-empty-placeholder');
      if (checkbox.checked) {
        if (img) img.style.opacity = '0.3';
      } else {
        if (img) img.style.opacity = '1';
      }
    }
  </script>
</body>
</html>
