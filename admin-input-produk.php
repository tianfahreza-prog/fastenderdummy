<?php
/**
 * Fastender Pre-Order Platform
 * Form Input Produk PO & Multi-Image Upload (admin-input-produk.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

$adminPage = 'input-produk';
$adminTitle = 'Input Produk Pre-Order';

$flashSuccess = '';
$flashError = '';

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

// Handle submit form produk baru via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $filesPayload = $_FILES['product_images'] ?? null;
        $newId = saveNewProduct($_POST, $filesPayload);
        $flashSuccess = 'Produk "' . sanitize($_POST['name'] ?? 'PO') . '" berhasil disimpan dan langsung mengisi slot katalog!';
    } catch (Exception $e) {
        $flashError = 'Gagal menyimpan produk: ' . $e->getMessage();
    }
}

$storedProducts = getProductsList();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Produk Pre-Order - Admin Fastender</title>
  
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
          <div>
            <h1 class="admin-page-title">Form Input Produk Pre-Order (PO)</h1>
            <p class="admin-page-sub">
              Produk yang Anda inputkan di halaman ini akan langsung mengisi <strong>space kosongan</strong> pada halaman Beranda dan Katalog Pengguna.
            </p>
          </div>
          
          <div style="display: flex; gap: 10px; flex-wrap: wrap;">
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
          <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 24px; font-weight: 500;">
            <?= htmlspecialchars($flashSuccess) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
          <div style="background: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 24px;">
            <?= htmlspecialchars($flashError) ?>
          </div>
        <?php endif; ?>

        <!-- FORM INPUT PRODUK (2 KOLOM: FORMULIR + LIVE PREVIEW) -->
        <form id="admin-product-form" method="POST" action="admin-input-produk.php" enctype="multipart/form-data" onsubmit="handleFormPreSubmit(event)">
          <!-- Hidden input untuk menampung base64 array gambar dari drag and drop jika ada -->
          <input type="hidden" name="base64_images" id="inp-base64-images">

          <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 32px; align-items: start;">
            
            <!-- KOLOM KIRI: FORMULIR RINCIAN -->
            <div>
              
              <!-- Bagian 1: Informasi Dasar Produk & Multi-Foto -->
              <div class="form-card">
                <h2 class="form-section-title">
                  <span class="step-badge">1</span>
                  Informasi Dasar &amp; Multi-Foto Produk PO
                </h2>

                <div class="form-group">
                  <label class="form-label" for="inp-name">Nama Produk Pre-Order *</label>
                  <input type="text" name="name" id="inp-name" class="form-control" placeholder="Contoh: Varsity Jacket Premium Angkatan 2026" required oninput="updateLivePreview()">
                </div>

                <div class="form-group">
                  <label class="form-label" for="inp-subtitle">Sub-judul / Keterangan Singkat</label>
                  <input type="text" name="subtitle" id="inp-subtitle" class="form-control" placeholder="Contoh: Custom Bordir Komputer Nama, NIM &amp; Logo Jurusan" oninput="updateLivePreview()">
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-category">Kategori Apparel *</label>
                    <select name="category" id="inp-category" class="form-control" onchange="updateLivePreview()">
                      <option value="jaket">Jaket &amp; Varsity</option>
                      <option value="pdh">Kemeja PDH</option>
                      <option value="hoodie">Hoodie &amp; Sweatshirt</option>
                      <option value="kaos">Kaos Angkatan</option>
                      <option value="merch">Merchandise &amp; Aksesoris</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-icon-type">Model Ilustrasi Mockup *</label>
                    <select name="icon_type" id="inp-icon-type" class="form-control" onchange="updateLivePreview()">
                      <option value="varsity">Varsity Jacket</option>
                      <option value="shirt">Kemeja PDH Drill</option>
                      <option value="hoodie">Hoodie Fleece</option>
                      <option value="tshirt">Kaos Distro</option>
                      <option value="windbreaker">Jaket Windbreaker</option>
                      <option value="totebag">Tote Bag Canvas</option>
                      <option value="lanyard">Tali Lanyard &amp; ID Card</option>
                    </select>
                  </div>
                </div>

                <!-- Bagian Upload Foto Produk (Bisa Lebih Dari 1 Gambar) -->
                <div class="form-group" style="margin-top: 20px;">
                  <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>Upload Foto Produk PO (Bisa Multi-Foto &gt; 1)</span>
                    <span id="upload-count-badge" style="font-size: 11px; color: var(--accent); font-weight: 600; background: var(--accent-light); padding: 2px 8px; border-radius: var(--radius-sm);">0 Foto Terpilih</span>
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
                    <h4>Pilih atau Tarik File Foto Produk ke Sini</h4>
                    <p>Format JPG, PNG, WEBP. Anda bisa memilih lebih dari 1 foto sekaligus.<br><strong>Foto pertama otomatis menjadi Foto Utama (Cover)</strong> di katalog.</p>
                  </div>

                  <!-- Container Pratinjau Foto dengan Tombol Hapus per Foto -->
                  <div id="image-preview-grid" class="image-preview-grid"></div>
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
                    <input type="number" name="price" id="inp-price" class="form-control" placeholder="245000" required oninput="calculateDp(); updateLivePreview()">
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-dp">Minimal Pembayaran DP 50% (Rp)</label>
                    <input type="number" name="dp_price" id="inp-dp" class="form-control" placeholder="122500" readonly style="background: var(--bg-muted);">
                  </div>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-quota-target">Target Kuota Minimal (Pcs) *</label>
                    <input type="number" name="quota_target" id="inp-quota-target" class="form-control" value="100" min="1" required oninput="updateLivePreview()">
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-quota-current">Kuota Terisi Saat Ini (Pcs)</label>
                    <input type="number" name="quota_current" id="inp-quota-current" class="form-control" value="0" min="0" oninput="updateLivePreview()">
                  </div>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label" for="inp-batch">Nama Batch &amp; Batas Tutup PO</label>
                    <input type="text" name="batch" id="inp-batch" class="form-control" placeholder="Batch 1 (Tutup 25 Nov 2026)" value="Batch 1 (Tutup 25 Nov 2026)" oninput="updateLivePreview()">
                  </div>

                  <div class="form-group">
                    <label class="form-label" for="inp-badge">Label Badge Produk</label>
                    <input type="text" name="badge" id="inp-badge" class="form-control" placeholder="PO Baru / Best Seller / Kuota Terbatas" value="PO Baru" oninput="updateLivePreview()">
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
                  <textarea name="description" id="inp-desc" class="form-control" rows="3" placeholder="Jelaskan kenyamanan bahan, ketebalan, dan alasan mengapa angkatan harus ikut PO ini..."></textarea>
                </div>

                <div class="form-group">
                  <label class="form-label" for="inp-specs">Spesifikasi Material (Pisahkan dengan tanda koma)</label>
                  <input type="text" name="specs" id="inp-specs" class="form-control" placeholder="Bahan Cotton Fleece 330gsm, Bordir Komputer HD, Furing Quilting, Jahitan Rantai" value="Bahan Cotton Fleece 330gsm, Bordir Komputer HD, Jahitan Rantai Standar Distro, Estimasi Produksi 14-21 Hari">
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
                      <div id="prev-svg-container" style="width: 100%; height: 100%; padding: 20px;">
                        <!-- SVG / Foto Live Preview -->
                      </div>
                    </div>
                    <div class="product-content">
                      <span class="product-category" id="prev-category">Jaket &amp; Varsity</span>
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

                  <!-- Tombol Simpan Aksi Oranye Sesuai Spesifikasi -->
                  <button type="submit" class="btn btn-accent btn-block btn-lg" style="margin-top: 20px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Simpan &amp; Terbitkan ke Katalog
                  </button>

                  <p style="font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 10px; line-height: 1.4;">
                    Produk akan langsung tampil mengisi slot di Beranda dan Katalog Toko.
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
                    <tr>
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
                          <div style="width: 44px; height: 44px; padding: 4px; background: #eff6ff; border-radius: var(--radius-sm);">
                            <?= getProductSvgPhp($prod['icon_type'] ?? 'varsity') ?>
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
                        <a href="admin-input-produk.php?action=delete&id=<?= $prod['id'] ?>" class="btn btn-outline-primary btn-sm" style="color: var(--danger); border-color: var(--border-color);" onclick="return confirm('Hapus produk ini dari katalog?')">
                          Hapus
                        </a>
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
    let uploadedImages = []; // Menyimpan array data URL foto yang diupload secara interaktif

    document.addEventListener("DOMContentLoaded", () => {
      calculateDp();
      updateLivePreview();
      setupDropzone();

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
      showToast("Foto berhasil dihapus", "info");
    }

    function renderImagePreviews() {
      const grid = document.getElementById("image-preview-grid");
      const badge = document.getElementById("upload-count-badge");
      if (!grid) return;

      if (badge) {
        badge.textContent = `${uploadedImages.length} Foto Terpilih`;
      }

      if (uploadedImages.length === 0) {
        grid.innerHTML = "";
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
      const price = Number(document.getElementById("inp-price").value) || 0;
      const dp = Math.round(price * 0.5);
      document.getElementById("inp-dp").value = dp;
    }

    function updateLivePreview() {
      const name = document.getElementById("inp-name").value.trim() || "Nama Produk Pre-Order";
      const catSelect = document.getElementById("inp-category");
      const catText = catSelect.options[catSelect.selectedIndex].text;
      const iconType = document.getElementById("inp-icon-type").value;
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
        previewContainer.innerHTML = getProductSvg(iconType);
      }
    }

    // Sebelum form di-submit ke PHP POST, masukkan base64 photos ke hidden input
    function handleFormPreSubmit(e) {
      if (uploadedImages.length > 0) {
        document.getElementById("inp-base64-images").value = JSON.stringify(uploadedImages);
      }
      return true;
    }
  </script>
</body>
</html>
