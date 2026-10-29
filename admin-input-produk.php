<?php
/**
 * Fastender Pre-Order Platform
 * Form Input & Edit Produk PO & Multi-Image Upload (admin-input-produk.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

$adminPage = 'input-produk';
$adminTitle = 'Input Produk Pre-Order';

$flashSuccess = '';
$flashError = '';

// Handle pesan sukses dari redirect
if (isset($_GET['msg']) && $_GET['msg'] === 'updated') {
    $flashSuccess = 'Data produk berhasil diperbarui';
}

// Handle aksi cepat via GET
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'clear_empty') {
        clearAllProductsData();
        $flashSuccess = 'Mode Space Kosongan berhasil diaktifkan! Katalog & Beranda sekarang menampilkan slot kosong.';
    } elseif ($_GET['action'] === 'load_demo') {
        loadDemoProductsData();
        $flashSuccess = 'Produk demo berhasil dimuat ke dalam katalog!';
    } elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        deleteProductById((int)$_GET['id']);
        $flashSuccess = 'Produk berhasil dihapus dari sistem.';
    }
}

// Handle submit form produk (Tambah Baru atau Edit Produk) via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $filesPayload = $_FILES['product_images'] ?? null;
        $postEditId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;

        if ($postEditId > 0) {
            $updated = updateProductById($postEditId, $_POST, $filesPayload);
            if ($updated) {
                header('Location: admin-input-produk.php?msg=updated');
                exit;
            } else {
                $flashError = 'Gagal memperbarui data produk: Produk tidak ditemukan.';
            }
        } else {
            $newId = saveNewProduct($_POST, $filesPayload);
            $flashSuccess = 'Produk "' . sanitize($_POST['name'] ?? 'PO') . '" berhasil disimpan dan langsung mengisi slot katalog!';
        }
    } catch (Exception $e) {
        $flashError = 'Gagal menyimpan produk: ' . $e->getMessage();
    }
}

// Cek apakah halaman sedang dalam Mode Edit (parameter edit_id)
$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$editProduct = ($editId > 0) ? getProductById($editId) : null;
$isEditMode = ($editProduct !== null);

if ($isEditMode) {
    $adminTitle = 'Edit Produk Pre-Order';
}

$storedProducts = getProductsList();

// Nilai field form (Pre-fill saat mode edit, atau default saat mode input baru)
$formName = $isEditMode ? ($editProduct['name'] ?? '') : '';
$formSubtitle = $isEditMode ? ($editProduct['subtitle'] ?? '') : '';
$formCategory = $isEditMode ? ($editProduct['category'] ?? 'POLSTUFF') : 'POLSTUFF';
$formIconType = $isEditMode ? ($editProduct['icon_type'] ?? 'varsity') : 'varsity';
$formPrice = $isEditMode ? (int)($editProduct['price'] ?? 0) : '';
$formDpPrice = $isEditMode ? (int)($editProduct['dp_price'] ?? 0) : '';
$formQuotaTarget = $isEditMode ? (int)($editProduct['quota_target'] ?? 100) : 100;
$formQuotaCurrent = $isEditMode ? (int)($editProduct['quota_current'] ?? 0) : 0;
$formBatch = $isEditMode ? ($editProduct['batch'] ?? 'Batch 1') : 'Batch 1 (Tutup 25 Nov 2026)';
$formBadge = $isEditMode ? ($editProduct['badge'] ?? 'PO Baru') : 'PO Baru';
$formDesc = $isEditMode ? ($editProduct['description'] ?? '') : '';
if ($isEditMode) {
    $rawSpecs = $editProduct['specs'] ?? [];
    $formSpecs = is_array($rawSpecs) ? implode(', ', $rawSpecs) : (string)$rawSpecs;
} else {
    $formSpecs = 'Bahan Cotton Fleece 330gsm, Bordir Komputer HD, Jahitan Rantai Standar Distro, Estimasi Produksi 14-21 Hari';
}
$formImages = ($isEditMode && !empty($editProduct['images']) && is_array($editProduct['images']))
    ? array_values($editProduct['images'])
    : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isEditMode ? 'Edit Produk Pre-Order' : 'Input Produk Pre-Order' ?> - Admin Fastender</title>
  
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

    <!-- Konten Kanan Admin -->
    <main class="admin-main">
      
      <!-- Top Header -->
      <?php include __DIR__ . '/includes/admin-header.php'; ?>

      <!-- Area Konten Input Produk -->
      <div class="admin-content">
        
        <!-- Header Halaman & Tombol Demo Cepat -->
        <div class="admin-page-title-box">
          <h1 class="admin-page-title" style="margin: 0;">
            <?= $isEditMode ? 'Edit Produk' : 'Tambah Produk' ?>
          </h1>
          
          <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php if ($isEditMode): ?>
              <a href="admin-input-produk.php" class="btn btn-outline-primary btn-sm">
                Batal Edit
              </a>
            <?php endif; ?>

            <!-- Tombol Kosongkan Produk (Kembali ke Mode Space Kosongan) -->
            <a href="admin-input-produk.php?action=clear_empty" class="btn btn-outline-primary btn-sm" onclick="return confirm('Kosongkan semua produk untuk mengaktifkan Space Kosongan di Beranda &amp; Katalog?')">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
              Set Mode Space Kosong
            </a>
            
            <!-- Tombol Demo: Muat Template Cepat -->
            <a href="admin-input-produk.php?action=load_demo" class="btn btn-outline-accent btn-sm">
              Isi Produk Demo
            </a>
          </div>
        </div>

        <?php if (!empty($flashSuccess)): ?>
          <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
          </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
          <div style="background: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 24px;">
            <?= htmlspecialchars($flashError) ?>
          </div>
        <?php endif; ?>

        <?php if ($isEditMode): ?>
          <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
            <div>
              <strong>Mode Edit Aktif:</strong> Anda sedang mengedit produk <strong>#<?= (int)$editProduct['id'] ?> — <?= htmlspecialchars($formName) ?></strong>. Jika tidak memilih foto baru, foto produk saat ini akan tetap dipertahankan.
            </div>
            <a href="admin-input-produk.php" style="font-weight: 700; color: #1d4ed8; text-decoration: underline;">Batal Edit &amp; Input Baru</a>
          </div>
        <?php endif; ?>

        <!-- FORM INPUT / EDIT PRODUK (2 KOLOM: FORMULIR + LIVE PREVIEW) -->
        <form id="admin-product-form" method="POST" action="admin-input-produk.php<?= $isEditMode ? '?edit_id=' . (int)$editProduct['id'] : '' ?>" enctype="multipart/form-data" onsubmit="handleFormPreSubmit(event)">
          <?php if ($isEditMode): ?>
            <input type="hidden" name="edit_id" value="<?= (int)$editProduct['id'] ?>">
          <?php endif; ?>
          <!-- Hidden input untuk menampung base64 array gambar dari drag and drop jika ada -->
          <input type="hidden" name="base64_images" id="inp-base64-images">

          <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 32px; align-items: start;">
            
            <!-- KOLOM KIRI: FORMULIR RINCIAN -->
            <div>
              
              <!-- Bagian 1: Informasi Dasar Produk & Multi-Foto -->
              <div class="form-card">
                <h2 class="form-section-title">
                  <span class="step-badge">1</span>
                  <?= $isEditMode ? 'Edit Informasi Dasar &amp; Foto Produk PO' : 'Informasi Dasar &amp; Multi-Foto Produk PO' ?>
                </h2>

                <div class="form-group">
                  <label class="form-label" for="inp-name">Nama Produk Pre-Order *</label>
                  <input type="text" name="name" id="inp-name" class="form-control" placeholder="Contoh: Varsity Jacket Premium Angkatan 2026" value="<?= htmlspecialchars($formName) ?>" required oninput="updateLivePreview()">
                </div>

                <div class="form-group">
                  <label class="form-label" for="inp-subtitle">Sub-judul / Keterangan Singkat</label>
                  <input type="text" name="subtitle" id="inp-subtitle" class="form-control" placeholder="Contoh: Custom Bordir Komputer Nama, NIM &amp; Logo Jurusan" value="<?= htmlspecialchars($formSubtitle) ?>" oninput="updateLivePreview()">
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-category">Kategori Apparel *</label>
                    <select name="category" id="inp-category" class="form-control" onchange="updateLivePreview()">
                      <?php foreach (getProductCategoriesMap() as $catValue => $catLabel): ?>
                        <option value="<?= htmlspecialchars($catValue) ?>" <?= strcasecmp($formCategory, $catValue) === 0 ? 'selected' : '' ?>><?= htmlspecialchars($catLabel) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-icon-type">Model Ilustrasi Mockup *</label>
                    <select name="icon_type" id="inp-icon-type" class="form-control" onchange="updateLivePreview()">
                      <option value="varsity" <?= $formIconType === 'varsity' ? 'selected' : '' ?>>Varsity Jacket</option>
                      <option value="shirt" <?= $formIconType === 'shirt' ? 'selected' : '' ?>>Kemeja PDH Drill</option>
                      <option value="hoodie" <?= $formIconType === 'hoodie' ? 'selected' : '' ?>>Hoodie Fleece</option>
                      <option value="tshirt" <?= $formIconType === 'tshirt' ? 'selected' : '' ?>>Kaos Distro</option>
                      <option value="windbreaker" <?= $formIconType === 'windbreaker' ? 'selected' : '' ?>>Jaket Windbreaker</option>
                      <option value="totebag" <?= $formIconType === 'totebag' ? 'selected' : '' ?>>Tote Bag Canvas</option>
                      <option value="lanyard" <?= $formIconType === 'lanyard' ? 'selected' : '' ?>>Tali Lanyard &amp; ID Card</option>
                    </select>
                  </div>
                </div>

                <!-- Bagian Upload Foto Produk (Bisa Lebih Dari 1 Gambar) -->
                <div class="form-group" style="margin-top: 20px;">
                  <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><?= $isEditMode ? 'Foto Produk Saat Ini / Upload Foto Baru (Opsional)' : 'Upload Foto Produk PO (Bisa Multi-Foto &gt; 1)' ?></span>
                    <span id="upload-count-badge" style="font-size: 11px; color: var(--accent); font-weight: 600; background: var(--accent-light); padding: 2px 8px; border-radius: var(--radius-sm);"><?= count($formImages) ?> Foto Terpilih</span>
                  </label>
                  
                  <div class="upload-dropzone" id="upload-dropzone">
                    <input type="file" name="product_images[]" id="inp-images" accept="image/*" multiple onchange="handleImageFilesSelected(this.files)">
                    <div class="upload-icon-circle">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                      </svg>
                    </div>
                    <h4><?= $isEditMode ? 'Klik atau Tarik Foto Baru untuk Mengganti Foto Produk' : 'Pilih atau Tarik File Foto Produk ke Sini' ?></h4>
                    <p>
                      <?php if ($isEditMode): ?>
                        Biarkan kosong jika tidak ingin mengubah foto produk saat ini.<br><strong>Jika memilih foto baru, foto lama akan digantikan secara otomatis.</strong>
                      <?php else: ?>
                        Format JPG, PNG, WEBP. Anda bisa memilih lebih dari 1 foto sekaligus.<br><strong>Foto pertama otomatis menjadi Foto Utama (Cover)</strong> di katalog.
                      <?php endif; ?>
                    </p>
                  </div>

                  <!-- Container Pratinjau Foto dengan Tombol Hapus per Foto -->
                  <div id="image-preview-grid" class="image-preview-grid">
                    <div class="admin-empty-preview-box" style="aspect-ratio: 1 / 1; border-radius: var(--radius-cards, 10px);">
                      <span class="admin-empty-preview-text">preview</span>
                    </div>
                  </div>
                </div>

              </div>

              <!-- Bagian 2: Harga, DP & Kuota -->
              <div class="form-card">
                <h2 class="form-section-title">
                  <span class="step-badge">2</span>
                  Harga, Skema DP &amp; Kuota Batch PO
                </h2>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-price">Harga Satuan (Rp) *</label>
                    <input type="number" name="price" id="inp-price" class="form-control" placeholder="245000" value="<?= htmlspecialchars((string)$formPrice) ?>" required oninput="calculateDp(); updateLivePreview()">
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-dp">Minimal Pembayaran DP 50% (Rp)</label>
                    <input type="number" name="dp_price" id="inp-dp" class="form-control" placeholder="122500" value="<?= htmlspecialchars((string)$formDpPrice) ?>" readonly style="background: var(--bg-muted);">
                  </div>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-quota-target">Target Kuota Minimal (Pcs) *</label>
                    <input type="number" name="quota_target" id="inp-quota-target" class="form-control" value="<?= (int)$formQuotaTarget ?>" min="1" required oninput="updateLivePreview()">
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-quota-current">Kuota Terisi Saat Ini (Pcs)</label>
                    <input type="number" name="quota_current" id="inp-quota-current" class="form-control" value="<?= (int)$formQuotaCurrent ?>" min="0" oninput="updateLivePreview()">
                  </div>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-batch">Nama Batch &amp; Batas Tutup PO</label>
                    <input type="text" name="batch" id="inp-batch" class="form-control" placeholder="Batch 1 (Tutup 25 Nov 2026)" value="<?= htmlspecialchars($formBatch) ?>" oninput="updateLivePreview()">
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-badge">Label Badge Produk</label>
                    <input type="text" name="badge" id="inp-badge" class="form-control" placeholder="PO Baru / Best Seller / Kuota Terbatas" value="<?= htmlspecialchars($formBadge) ?>" oninput="updateLivePreview()">
                  </div>
                </div>
              </div>

              <!-- Bagian 3: Deskripsi & Spesifikasi -->
              <div class="form-card">
                <h2 class="form-section-title">
                  <span class="step-badge">3</span>
                  Deskripsi &amp; Spesifikasi Material
                </h2>

                <div class="form-group">
                  <label class="form-label" for="inp-desc">Deskripsi Lengkap Produk</label>
                  <textarea name="description" id="inp-desc" class="form-control" rows="3" placeholder="Jelaskan kenyamanan bahan, ketebalan, dan alasan mengapa angkatan harus ikut PO ini..."><?= htmlspecialchars($formDesc) ?></textarea>
                </div>

                <div class="form-group">
                  <label class="form-label" for="inp-specs">Spesifikasi Material (Pisahkan dengan tanda koma)</label>
                  <input type="text" name="specs" id="inp-specs" class="form-control" placeholder="Bahan Cotton Fleece 330gsm, Bordir Komputer HD, Furing Quilting, Jahitan Rantai" value="<?= htmlspecialchars($formSpecs) ?>">
                </div>
              </div>

            </div>

            <!-- KOLOM KANAN: LIVE PREVIEW & AKSI TERBIT -->
            <aside>
              <div style="position: sticky; top: 92px;">
                
                <!-- Live Preview Card -->
                <div class="form-card" style="padding: 20px; border-color: var(--accent);">
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase;">
                      Live Preview di Katalog
                    </span>
                    <span style="font-size: 11px; color: var(--text-muted);">Sisi Pengguna</span>
                  </div>

                  <!-- Product Card Live Mockup -->
                  <div class="product-card" id="preview-product-card" style="box-shadow: none; pointer-events: none;">
                    <div class="product-thumb-wrapper">
                      <span class="product-card-badge" id="prev-badge">PO Baru</span>
                      <span class="product-batch-tag" id="prev-batch">Batch 1</span>
                      <div id="prev-svg-container" style="width: 100%; height: 100%; padding: 12px; background: #ffffff;">
                        <div class="admin-empty-preview-box" style="width: 100%; height: 100%; border-radius: var(--radius-md, 8px);">
                          <span class="admin-empty-preview-text">preview</span>
                        </div>
                      </div>
                    </div>
                    <div class="product-content">
                      <span class="product-category" id="prev-category"><?= htmlspecialchars($formCategory) ?></span>
                      <h3 class="product-title" id="prev-title">Varsity Jacket Premium Angkatan 2026</h3>
                      
                      <div class="po-quota-box">
                        <div class="po-quota-info">
                          <span id="prev-quota-text">Kuota Terisi: 0/100 pcs</span>
                          <span id="prev-quota-percent">0%</span>
                        </div>
                        <div class="po-progress-bar">
                          <div class="po-progress-fill" id="prev-quota-bar" style="width: 0%;"></div>
                        </div>
                      </div>

                      <div class="product-footer">
                        <div class="product-price-row">
                          <span class="product-price" id="prev-price">Rp 245.000</span>
                          <span class="product-dp-label" id="prev-dp">DP: Rp 122.500</span>
                        </div>
                        <div class="btn btn-outline-accent btn-block" style="font-size: 13px;">
                          Detail Produk
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Tombol Simpan / Perbarui Aksi -->
                  <button type="submit" class="btn btn-accent btn-block btn-lg" style="margin-top: 20px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <?= $isEditMode ? 'Perbarui Data Produk' : 'Simpan &amp; Publikasikan Produk PO' ?>
                  </button>

                  <?php if ($isEditMode): ?>
                    <a href="admin-input-produk.php" class="btn btn-outline-primary btn-block" style="margin-top: 10px; text-align: center;">
                      Batal Edit
                    </a>
                  <?php endif; ?>

                  <p style="font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 10px; line-height: 1.4;">
                    <?= $isEditMode ? 'Perubahan data produk akan langsung diperbarui di Beranda dan Katalog Toko.' : 'Produk akan langsung tampil mengisi slot di Beranda dan Katalog Toko.' ?>
                  </p>
                </div>

              </div>
            </aside>

          </div>
        </form>

        <!-- ==========================================
             DAFTAR PRODUK YANG SUDAH DIINPUTKAN
             ========================================== -->
        <div class="admin-table-card" style="margin-top: 40px;">
          <div class="table-toolbar">
            <div>
              <h3 class="table-title">Daftar Produk PO yang Tersimpan di Sistem</h3>
              <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                Total <strong id="admin-prod-count" style="color: var(--primary);"><?= count($storedProducts) ?></strong> produk aktif yang mengisi katalog saat ini.
              </p>
            </div>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Foto</th>
                  <th>Nama Produk PO</th>
                  <th>Kategori</th>
                  <th>Harga Satuan</th>
                  <th>Minimal DP</th>
                  <th>Kuota Terisi</th>
                  <th>Batch / Periode</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="stored-products-tbody">
                <?php if (empty($storedProducts)): ?>
                  <tr>
                    <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted); background: #fafbfc;">
                      <strong>Status: Mode Space Kosong Aktif</strong><br>
                      <span style="font-size: 13px;">Belum ada produk yang diinput. Halaman Beranda dan Katalog pengguna saat ini menampilkan slot space kosong.</span>
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($storedProducts as $idx => $prod): ?>
                    <tr style="<?= ($isEditMode && (int)$prod['id'] === (int)$editProduct['id']) ? 'background-color: #eff6ff;' : '' ?>">
                      <td style="font-weight: 700; color: var(--text-muted);"><?= $idx + 1 ?></td>
                      <td>
                        <?php if (!empty($prod['images'])): ?>
                          <div style="display: flex; align-items: center; gap: 8px;">
                            <img src="<?= htmlspecialchars($prod['images'][0]) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" style="width: 44px; height: 44px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                            <?php if (count($prod['images']) > 1): ?>
                              <span class="badge" style="font-size: 10px; background: #e0f2fe; color: #0369a1; padding: 2px 6px;"><?= count($prod['images']) ?> foto</span>
                            <?php else: ?>
                              <span style="font-size: 10px; color: var(--text-muted);">1 foto</span>
                            <?php endif; ?>
                          </div>
                        <?php else: ?>
                          <div class="admin-empty-preview-box" style="width: 48px; height: 48px; border-radius: var(--radius-sm);">
                            <span class="admin-empty-preview-text" style="font-size: 10px;">preview</span>
                          </div>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($prod['name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($prod['subtitle'] ?? '-') ?></div>
                      </td>
                      <td><span class="badge badge-primary"><?= htmlspecialchars($prod['category_name'] ?? $prod['category']) ?></span></td>
                      <td style="font-weight: 700; color: var(--primary);"><?= formatRupiah($prod['price']) ?></td>
                      <td style="font-weight: 600; color: var(--accent);"><?= formatRupiah($prod['dp_price']) ?></td>
                      <td><?= $prod['quota_current'] ?> / <?= $prod['quota_target'] ?> pcs</td>
                      <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($prod['batch']) ?></td>
                      <td>
                        <div class="action-btn-group">
                          <a href="admin-input-produk.php?edit_id=<?= (int)$prod['id'] ?>" class="action-icon-btn btn-edit-product">
                            Edit
                          </a>
                          <a href="admin-input-produk.php?action=delete&id=<?= (int)$prod['id'] ?>" class="action-icon-btn btn-delete-order" onclick="return confirm('Hapus produk ini dari katalog?')">
                            Hapus
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </main>

  </div>

  <!-- Scripts -->
  <script src="js/script.js"></script>
  <script>
    const IS_EDIT_MODE = <?= json_encode($isEditMode) ?>;
    const INITIAL_PRODUCT_IMAGES = <?= json_encode($formImages) ?>;
    let uploadedImages = Array.isArray(INITIAL_PRODUCT_IMAGES) ? [...INITIAL_PRODUCT_IMAGES] : [];
    let hasReplacedImagesInEdit = false;

    document.addEventListener("DOMContentLoaded", () => {
      calculateDp();
      renderImagePreviews();
      updateLivePreview();
      setupDropzone();

      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
      if (typeof showToast === "function") {
        showToast("Data produk berhasil diperbarui", "success");
      }
      <?php endif; ?>

      // Mobile sidebar toggle
      const sidebarToggle = document.getElementById("toggle-admin-sidebar");
      const sidebar = document.getElementById("admin-sidebar");
      if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
        });
      }
    });

    // Setup drag and drop listener pada dropzone
    function setupDropzone() {
      const dropzone = document.getElementById("upload-dropzone");
      if (!dropzone) return;

      ['dragenter', 'dragover'].forEach(name => {
        dropzone.addEventListener(name, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.add("dragover");
        }, false);
      });

      ['dragleave', 'drop'].forEach(name => {
        dropzone.addEventListener(name, (e) => {
          e.preventDefault();
          e.stopPropagation();
          dropzone.classList.remove("dragover");
        }, false);
      });

      dropzone.addEventListener("drop", (e) => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
          handleImageFilesSelected(dt.files);
        }
      }, false);
    }

    // Resize & kompres gambar dengan Canvas client-side
    function resizeImage(file, maxWidth = 800, maxHeight = 800, quality = 0.85) {
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = new Image();
          img.onload = () => {
            let width = img.width;
            let height = img.height;

            if (width > height) {
              if (width > maxWidth) {
                height = Math.round((height * maxWidth) / width);
                width = maxWidth;
              }
            } else {
              if (height > maxHeight) {
                width = Math.round((width * maxHeight) / height);
                height = maxHeight;
              }
            }

            const canvas = document.createElement("canvas");
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, width, height);

            const dataUrl = canvas.toDataURL("image/jpeg", quality);
            resolve(dataUrl);
          };
          img.onerror = reject;
          img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
      });
    }

    // Event handler ketika user memilih foto
    async function handleImageFilesSelected(files) {
      if (!files || files.length === 0) return;

      showToast(`Sedang memproses ${files.length} foto...`, "info");

      // Dalam mode edit, saat user memilih foto baru pertama kali, ganti preview foto lama dengan foto baru
      if (IS_EDIT_MODE && !hasReplacedImagesInEdit) {
        uploadedImages = [];
        hasReplacedImagesInEdit = true;
      }

      let addedCount = 0;
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (!file.type.startsWith("image/")) continue;
        try {
          const resizedDataUrl = await resizeImage(file);
          uploadedImages.push(resizedDataUrl);
          addedCount++;
        } catch (err) {
          console.error("Gagal memproses gambar", err);
        }
      }

      renderImagePreviews();
      updateLivePreview();

      if (addedCount > 0) {
        showToast(`Berhasil menambahkan ${addedCount} foto! (Total: ${uploadedImages.length})`, "success");
      }
    }

    function removeImage(index) {
      uploadedImages.splice(index, 1);
      renderImagePreviews();
      updateLivePreview();
      showToast("Foto berhasil dihapus dari pratinjau", "info");
    }

    function renderImagePreviews() {
      const grid = document.getElementById("image-preview-grid");
      const badge = document.getElementById("upload-count-badge");
      if (!grid) return;

      if (badge) {
        badge.textContent = `${uploadedImages.length} Foto Terpilih`;
      }

      if (uploadedImages.length === 0) {
        grid.innerHTML = `
          <div class="admin-empty-preview-box" style="aspect-ratio: 1 / 1; border-radius: var(--radius-cards, 10px);">
            <span class="admin-empty-preview-text">preview</span>
          </div>
        `;
        return;
      }

      grid.innerHTML = uploadedImages.map((imgSrc, idx) => `
        <div class="preview-thumb-box ${idx === 0 ? 'primary-thumb' : ''}">
          <img src="${imgSrc}" alt="Foto Produk ${idx + 1}">
          ${idx === 0 ? '<span class="thumb-tag-primary">Utama</span>' : ''}
          <button type="button" class="btn-remove-thumb" onclick="removeImage(${idx})" title="Hapus foto ini">×</button>
        </div>
      `).join("");
    }

    function calculateDp() {
      const priceInput = document.getElementById("inp-price");
      const dpInput = document.getElementById("inp-dp");
      if (!priceInput || !dpInput) return;
      const price = Number(priceInput.value) || 0;
      const dp = Math.round(price * 0.5);
      dpInput.value = price > 0 ? dp : "";
    }

    function updateLivePreview() {
      const name = document.getElementById("inp-name").value.trim() || "Nama Produk Pre-Order";
      const catSelect = document.getElementById("inp-category");
      const catText = catSelect.options[catSelect.selectedIndex].text;
      const price = Number(document.getElementById("inp-price").value) || 0;
      const dp = Number(document.getElementById("inp-dp").value) || 0;
      const quotaTarget = Number(document.getElementById("inp-quota-target").value) || 100;
      const quotaCurrent = Number(document.getElementById("inp-quota-current").value) || 0;
      const batch = document.getElementById("inp-batch").value.trim() || "Batch 1";
      const badge = document.getElementById("inp-badge").value.trim() || "PO Baru";

      const percent = Math.min(100, Math.round((quotaCurrent / quotaTarget) * 100));

      document.getElementById("prev-title").textContent = name;
      document.getElementById("prev-category").textContent = catText;
      document.getElementById("prev-price").textContent = formatRupiah(price);
      document.getElementById("prev-dp").textContent = `DP: ${formatRupiah(dp)}`;
      document.getElementById("prev-badge").textContent = badge;
      document.getElementById("prev-batch").textContent = batch.split(" ")[0];
      document.getElementById("prev-quota-text").textContent = `Kuota: ${quotaCurrent}/${quotaTarget} pcs`;
      document.getElementById("prev-quota-percent").textContent = `${percent}%`;
      document.getElementById("prev-quota-bar").style.width = `${percent}%`;

      const previewContainer = document.getElementById("prev-svg-container");
      if (uploadedImages.length > 0) {
        previewContainer.innerHTML = `
          <img src="${uploadedImages[0]}" alt="Live Preview" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-md);">
        `;
      } else {
        previewContainer.innerHTML = `
          <div class="admin-empty-preview-box" style="width: 100%; height: 100%; border-radius: var(--radius-md, 8px);">
            <span class="admin-empty-preview-text">preview</span>
          </div>
        `;
      }
    }

    // Sebelum form di-submit ke PHP POST, masukkan base64 photos baru ke hidden input
    function handleFormPreSubmit(e) {
      const newBase64List = uploadedImages.filter(src => typeof src === "string" && src.startsWith("data:image/"));
      if (newBase64List.length > 0) {
        document.getElementById("inp-base64-images").value = JSON.stringify(newBase64List);
      } else {
        document.getElementById("inp-base64-images").value = "";
      }
      return true;
    }
  </script>
</body>
</html>
