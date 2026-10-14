<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Pengaturan Hero Banner & Countdown PO (admin-hero.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

$adminPage = 'hero';
$adminTitle = 'Pengaturan Hero PO';

// Handle form simpan Hero Banner & Countdown PO dari Admin
$saveHeroSuccess = false;
$flashHeroMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_hero_settings') {
    $fileData = $_FILES['banner_file'] ?? null;
    $updatedHero = saveHeroSettings($_POST, $fileData);
    
    // Response JSON jika AJAX
    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['is_ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'config' => $updatedHero,
            'message' => 'Pengaturan Hero Banner & Countdown PO berhasil disimpan!'
        ]);
        exit;
    }
    $saveHeroSuccess = true;
    $flashHeroMessage = 'Pengaturan Hero Banner & Countdown PO berhasil disimpan!';
}

$heroSettings = getHeroSettings();
$heroBannerPath = trim((string)($heroSettings['banner_image'] ?? ''));
$defaultMockupPaths = ['assets/images/banner-po-default.svg', 'assets/images/hero-showcase.png'];
if (in_array($heroBannerPath, $defaultMockupPaths, true)) {
    $heroBannerPath = '';
}
$hasHeroBannerFile = ($heroBannerPath !== '' && is_file(__DIR__ . '/' . ltrim($heroBannerPath, '/')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengaturan Hero PO - Admin Fastender</title>
  
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
        <div class="admin-page-title-box">
          <div>
            <h1 class="admin-page-title">Pengaturan Hero Banner &amp; Countdown PO</h1>
            <p class="admin-page-sub">Kelola tampilan visual utama, teks headline, dan waktu penutupan pre-order di halaman Beranda.</p>
          </div>
          <div style="display: flex; gap: 10px;">
            <a href="index.php" target="_blank" class="btn btn-outline-primary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                <polyline points="15 3 21 3 21 9"></polyline>
                <line x1="10" y1="14" x2="21" y2="3"></line>
              </svg>
              Lihat di Beranda
            </a>
          </div>
        </div>

        <!-- ========================================================
             PENGATURAN HERO BANNER & COUNTDOWN PRE-ORDER (SPLIT-CARD)
             ======================================================== -->
        <div class="admin-3d-studio-card" id="hero-banner-section" style="margin-bottom: 0;">
          
          <div class="admin-3d-studio-header">
            <div>
              <h2 class="admin-3d-studio-title">
                <span>Pengaturan Hero Banner &amp; Countdown PO</span>
                <span class="badge badge-accent">LIVE DI BERANDA</span>
              </h2>
              <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                Atur judul utama, label &amp; deadline countdown PO, dan upload foto showcase apparel untuk tampilan hero split-card di Beranda.
              </p>
            </div>
          </div>

          <?php if (!empty($flashHeroMessage)): ?>
            <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
              <span><?= htmlspecialchars($flashHeroMessage) ?></span>
            </div>
          <?php endif; ?>

          <div id="alert-hero-ajax" style="display: none; padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 18px;"></div>

          <form id="form-hero-settings" method="POST" enctype="multipart/form-data" onsubmit="handleSaveHero(event)">
            <input type="hidden" name="action" value="save_hero_settings">

            <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px;">
              
              <!-- Kolom Kiri: Form Teks, Deadline & CTA -->
              <div>
                <div class="form-group" style="margin-bottom: 14px;">
                  <label class="form-label" style="font-size: 13px; font-weight: 600;">Judul Utama Hero (Kolom Kiri):</label>
                  <input type="text" class="form-control" name="title" id="hero-title" value="<?= htmlspecialchars($heroSettings['title'] ?? 'Pre-Order Batch 1 telah dibuka') ?>" required>
                  <small style="color: var(--text-muted); font-size: 11px;">Gunakan baris baru atau spasi untuk menyesuaikan tipografi di halaman utama.</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                  <div class="form-group">
                    <label class="form-label" style="font-size: 13px; font-weight: 600;">Badge Atas:</label>
                    <input type="text" class="form-control" name="badge" value="<?= htmlspecialchars($heroSettings['badge'] ?? 'BATCH PERDANA TAHUN AKADEMIK 2026') ?>">
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="font-size: 13px; font-weight: 600;">Label Countdown:</label>
                    <input type="text" class="form-control" name="countdown_label" value="<?= htmlspecialchars($heroSettings['countdown_label'] ?? 'PO DITUTUP DALAM') ?>" required>
                  </div>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                  <label class="form-label" style="font-size: 13px; font-weight: 600;">Target Waktu Penutupan PO (Deadline Countdown):</label>
                  <input type="datetime-local" class="form-control" name="countdown_deadline" id="hero-deadline" value="<?= date('Y-m-d\TH:i', strtotime($heroSettings['countdown_deadline'] ?? '+14 days')) ?>" required>
                  <small style="color: var(--text-muted); font-size: 11px;">Timer DAYS, HOURS, MINS, SECS di Beranda akan menghitung mundur ke waktu ini secara realtime.</small>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                  <label class="form-label" style="font-size: 13px; font-weight: 600;">Deskripsi Singkat:</label>
                  <textarea class="form-control" name="description" rows="2"><?= htmlspecialchars($heroSettings['description'] ?? '') ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                  <div class="form-group">
                    <label class="form-label" style="font-size: 13px; font-weight: 600;">Teks Tombol CTA:</label>
                    <input type="text" class="form-control" name="cta_text" value="<?= htmlspecialchars($heroSettings['cta_text'] ?? 'Pesan Sekarang') ?>">
                  </div>
                  <div class="form-group">
                    <label class="form-label" style="font-size: 13px; font-weight: 600;">Link Tujuan CTA:</label>
                    <input type="text" class="form-control" name="cta_link" value="<?= htmlspecialchars($heroSettings['cta_link'] ?? 'katalog.php') ?>">
                  </div>
                </div>

                <button type="submit" class="btn btn-accent btn-lg" id="btn-save-hero" style="width: 100%;">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                  </svg>
                  Simpan Pengaturan Hero &amp; Countdown
                </button>
              </div>

              <!-- Kolom Kanan: Upload & Live Image Preview Showcase -->
              <div>
                <div class="form-group" style="margin-bottom: 12px;">
                  <label class="form-label" style="font-size: 13px; font-weight: 600;">Upload Gambar Showcase Baru (Kolom Kanan):</label>
                  <input type="file" name="banner_file" id="hero-banner-input" class="form-control" accept="image/*" onchange="previewHeroBanner(this)">
                  <input type="hidden" name="banner_image_path" id="hero-banner-saved-path" value="<?= htmlspecialchars($heroBannerPath) ?>">
                  <small style="color: var(--text-muted); font-size: 11px;">Mendukung format JPG, PNG, WebP (Rekomendasi rasio 4:3 atau 16:10).</small>
                </div>

                <div style="margin-top: 14px;">
                  <label class="form-label" style="font-size: 12px; font-weight: 600;">Live Preview Gambar Showcase Saat Ini:</label>
                  <div id="hero-banner-preview-box" class="hero-showcase-preview-box <?= $hasHeroBannerFile ? 'has-image' : '' ?>" style="width: 100%; height: 280px;">
                    <span id="hero-preview-placeholder-text" class="hero-showcase-preview-text" style="<?= $hasHeroBannerFile ? 'display: none;' : 'display: flex;' ?>">preview</span>
                    <img
                      id="hero-banner-preview"
                      src="<?= $hasHeroBannerFile ? htmlspecialchars($heroBannerPath) : '' ?>"
                      alt="Preview Showcase"
                      style="width: 100%; height: 100%; object-fit: cover; <?= $hasHeroBannerFile ? 'display: block;' : 'display: none;' ?>"
                      onerror="resetHeroPreviewToEmpty();"
                    >
                    <div id="hero-preview-active-badge" style="position: absolute; bottom: 12px; left: 12px; background: rgba(17, 17, 17, 0.78); backdrop-filter: blur(8px); color: #fff; font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); <?= $hasHeroBannerFile ? 'display: inline-block;' : 'display: none;' ?>">
                      ● Aktif di Beranda
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </form>

        </div>

      </div>

    </main>

  </div>

  <!-- Scripts -->
  <script src="js/script.js"></script>
  <script>
    const INITIAL_HERO_BANNER = <?= json_encode($hasHeroBannerFile ? $heroBannerPath : '') ?>;

    document.addEventListener("DOMContentLoaded", () => {
      // Mobile sidebar toggle
      const sidebarToggle = document.getElementById("toggle-admin-sidebar");
      const sidebar = document.getElementById("admin-sidebar");
      if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
        });
      }
    });

    function resetHeroPreviewToEmpty() {
      const previewBox = document.getElementById("hero-banner-preview-box");
      const previewImg = document.getElementById("hero-banner-preview");
      const placeholderText = document.getElementById("hero-preview-placeholder-text");
      const activeBadge = document.getElementById("hero-preview-active-badge");

      if (previewBox) previewBox.classList.remove("has-image");
      if (previewImg) {
        previewImg.removeAttribute("src");
        previewImg.style.display = "none";
      }
      if (placeholderText) placeholderText.style.display = "flex";
      if (activeBadge) activeBadge.style.display = "none";
    }

    function previewHeroBanner(input) {
      const previewBox = document.getElementById("hero-banner-preview-box");
      const previewImg = document.getElementById("hero-banner-preview");
      const placeholderText = document.getElementById("hero-preview-placeholder-text");
      const activeBadge = document.getElementById("hero-preview-active-badge");

      if (input.files && input.files[0]) {
        const file = input.files[0];
        if (!file.type.startsWith('image/')) {
          showToast("Mohon pilih file gambar yang valid (JPG, PNG, WebP)!", "danger");
          input.value = "";
          if (!INITIAL_HERO_BANNER) {
            resetHeroPreviewToEmpty();
          }
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          if (placeholderText) placeholderText.style.display = "none";
          if (previewBox) previewBox.classList.add("has-image");
          if (previewImg) {
            previewImg.src = e.target.result;
            previewImg.style.display = "block";
          }
          if (activeBadge) {
            activeBadge.textContent = "● Pratinjau Gambar Baru";
            activeBadge.style.display = "inline-block";
          }
          showToast(`File banner baru dipilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`, "info");
        };
        reader.readAsDataURL(file);
      } else {
        if (INITIAL_HERO_BANNER) {
          if (placeholderText) placeholderText.style.display = "none";
          if (previewBox) previewBox.classList.add("has-image");
          if (previewImg) {
            previewImg.src = INITIAL_HERO_BANNER;
            previewImg.style.display = "block";
          }
          if (activeBadge) {
            activeBadge.textContent = "● Aktif di Beranda";
            activeBadge.style.display = "inline-block";
          }
        } else {
          resetHeroPreviewToEmpty();
        }
      }
    }

    function handleSaveHero(e) {
      e.preventDefault();
      const btn = document.getElementById("btn-save-hero");
      btn.disabled = true;
      btn.innerHTML = `<span>Menyimpan ke Beranda...</span>`;

      const form = document.getElementById("form-hero-settings");
      const formData = new FormData(form);
      formData.append("is_ajax", "1");

      fetch("admin-hero.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btn.disabled = false;
        btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg> Simpan Pengaturan Hero &amp; Countdown`;

        if (data.success) {
          showToast("Pengaturan Hero Banner & Countdown PO berhasil disimpan!", "success");
          const alertBox = document.getElementById("alert-hero-ajax");
          if (alertBox) {
            alertBox.style.display = "flex";
            alertBox.style.background = "#dcfce7";
            alertBox.style.border = "1px solid #86efac";
            alertBox.style.color = "#15803d";
            alertBox.innerHTML = `<div><strong>Berhasil Disimpan!</strong> Perubahan banner &amp; countdown kini aktif di Beranda. <a href="index.php" target="_blank" style="text-decoration: underline; font-weight: 700; margin-left: 6px;">Buka Beranda untuk melihat</a></div>`;
          }
          const previewBox = document.getElementById("hero-banner-preview-box");
          const previewImg = document.getElementById("hero-banner-preview");
          const placeholderText = document.getElementById("hero-preview-placeholder-text");
          const activeBadge = document.getElementById("hero-preview-active-badge");
          const savedPathInput = document.getElementById("hero-banner-saved-path");

          if (data.config && data.config.banner_image) {
            if (savedPathInput) savedPathInput.value = data.config.banner_image;
            if (placeholderText) placeholderText.style.display = "none";
            if (previewBox) previewBox.classList.add("has-image");
            if (previewImg) {
              previewImg.src = data.config.banner_image;
              previewImg.style.display = "block";
            }
            if (activeBadge) {
              activeBadge.textContent = "● Aktif di Beranda";
              activeBadge.style.display = "inline-block";
            }
          } else {
            resetHeroPreviewToEmpty();
          }
        } else {
          showToast("Gagal menyimpan pengaturan: " + (data.message || "Error"), "danger");
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `Simpan Pengaturan Hero &amp; Countdown`;
        form.submit();
      });
    }
  </script>
</body>
</html>
