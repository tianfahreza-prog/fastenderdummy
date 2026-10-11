<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Dashboard Admin (admin-dashboard.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

$adminPage = 'dashboard';
$adminTitle = 'Dashboard Ringkasan';

$orders = getAllOrdersList();
$totalOrders = count($orders) + 1420;
$totalRevenue = 184500000;
foreach ($orders as $ord) {
    $totalRevenue += (int)($ord['total_price'] ?? ($ord['total'] ?? 0));
}

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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Fastender Pre-Order Platform</title>
  
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
      <header class="admin-header">
        <div style="display: flex; align-items: center; gap: 14px;">
          <!-- Mobile Toggle Sidebar -->
          <button id="toggle-admin-sidebar" style="background: none; border: none; cursor: pointer; color: var(--color-ink-black, #111111); display: flex; align-items: center; padding: 4px;" class="nav-toggle-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
          </button>
          
          <div class="header-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="admin-search-orders" placeholder="Cari No. Resi, nama pemesan, angkatan..." oninput="handleAdminSearch(this.value)">
          </div>
        </div>

        <div class="header-user-group">
          <div class="admin-profile">
            <div class="admin-avatar">AD</div>
            <div class="admin-info">
              <div class="admin-name">Admin Fastender</div>
              <div class="admin-role">Super Administrator</div>
            </div>
          </div>
        </div>
      </header>

      <!-- Area Konten Utama -->
      <div class="admin-content">

        <!-- Title & Action Button -->
        <div class="admin-page-title-box">
          <div>
            <h1 class="admin-page-title">Dashboard Manajemen Pre-Order</h1>
            <p class="admin-page-sub">Pantau kuota batch, verifikasi pembayaran uang muka, dan update progres konveksi.</p>
          </div>
          <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportOrdersData()">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Ekspor Data
            </button>
            <a href="admin-input-produk.php" class="btn btn-accent btn-sm">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              Tambah Produk PO
            </a>
          </div>
        </div>

        <!-- CARD STATISTIK -->
        <div class="admin-stats-grid">
          
          <!-- Stat 1: Total PO -->
          <div class="stat-card">
            <div class="stat-card-top">
              <span class="stat-card-title">Total PO Masuk</span>
              <div class="stat-icon blue">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                  <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                  <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
              </div>
            </div>
            <div class="stat-value" id="stat-total-orders"><?= number_format($totalOrders, 0, ',', '.') ?></div>
            <div class="stat-trend positive">
              <span>↑ 18.2%</span>
              <span style="color: var(--text-muted); font-weight: normal;">dibanding batch lalu</span>
            </div>
          </div>

          <!-- Stat 2: Total Pendapatan -->
          <div class="stat-card">
            <div class="stat-card-top">
              <span class="stat-card-title">Total Nilai PO</span>
              <div class="stat-icon orange">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="1" x2="12" y2="23"></line>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
              </div>
            </div>
            <div class="stat-value" id="stat-total-revenue"><?= formatRupiah($totalRevenue) ?></div>
            <div class="stat-trend positive">
              <span>↑ 24.5%</span>
              <span style="color: var(--text-muted); font-weight: normal;">target tercapai 92%</span>
            </div>
          </div>

          <!-- Stat 3: Sedang Diproduksi -->
          <div class="stat-card">
            <div class="stat-card-top">
              <span class="stat-card-title">Dalam Produksi</span>
              <div class="stat-icon green">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
            </div>
            <div class="stat-value">540 pcs</div>
            <div class="stat-trend neutral">
              <span>3 Vendor Aktif</span>
              <span style="color: var(--text-muted); font-weight: normal;">• Konveksi Solo &amp; Bdg</span>
            </div>
          </div>

          <!-- Stat 4: Selesai / Kirim -->
          <div class="stat-card">
            <div class="stat-card-top">
              <span class="stat-card-title">Selesai &amp; Terkirim</span>
              <div class="stat-icon purple">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
            </div>
            <div class="stat-value">880 pcs</div>
            <div class="stat-trend positive">
              <span>100% On-Time</span>
              <span style="color: var(--text-muted); font-weight: normal;">• 0 komplain mutu</span>
            </div>
          </div>

        </div>

        <!-- ========================================================
        <!-- ========================================================
             PENGATURAN HERO BANNER & COUNTDOWN PRE-ORDER (SPLIT-CARD)
             ======================================================== -->
        <div class="admin-3d-studio-card" id="hero-banner-section">
          
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
            <div>
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
                  <input type="hidden" name="banner_image_path" value="<?= htmlspecialchars($heroSettings['banner_image'] ?? 'assets/images/hero-showcase.png') ?>">
                  <small style="color: var(--text-muted); font-size: 11px;">Mendukung format JPG, PNG, WebP (Rekomendasi rasio 4:3 atau 16:10).</small>
                </div>

                <div style="margin-top: 14px;">
                  <label class="form-label" style="font-size: 12px; font-weight: 600;">Live Preview Gambar Showcase Saat Ini:</label>
                  <div style="width: 100%; height: 280px; border-radius: 18px; overflow: hidden; border: 1px solid var(--color-plaster, #ecedee); background: #111; position: relative; box-shadow: 0 8px 24px rgba(0,0,0,0.08);">
                    <img id="hero-banner-preview" src="<?= htmlspecialchars($heroSettings['banner_image'] ?? 'assets/images/hero-showcase.png') ?>" alt="Preview Showcase" style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; bottom: 12px; left: 12px; background: rgba(17, 17, 17, 0.8); backdrop-filter: blur(8px); color: #fff; font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2);">
                      ● Aktif di Beranda
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </form>

        </div>

        <!-- TABEL DAFTAR PESANAN PRE-ORDER (HEADER NAVY #1e3a8a) -->
        <div class="admin-table-card" id="orders-section">
          
          <div class="table-toolbar">
            <div>
              <h3 class="table-title">Daftar Pesanan Pre-Order Masuk</h3>
              <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                Kelola status pembayaran DP, pelunasan, dan alur pengerjaan vendor.
              </p>
            </div>

            <div class="table-actions">
              <select class="table-filter-select" id="filter-order-status" onchange="filterOrdersByStatus(this.value)">
                <option value="all">Semua Status Produksi</option>
                <option value="Sedang Produksi">Sedang Produksi</option>
                <option value="Quality Check">Quality Check</option>
                <option value="Dalam Pengiriman">Dalam Pengiriman</option>
                <option value="Selesai">Selesai</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>No. Resi PO</th>
                  <th>Tanggal</th>
                  <th>Nama Pemesan &amp; Angkatan</th>
                  <th>Produk &amp; Kuantitas</th>
                  <th>Total Tagihan</th>
                  <th>Status Bayar</th>
                  <th>Status Produksi</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="admin-orders-tbody">
                <!-- Diisi secara dinamis oleh JavaScript / PHP -->
              </tbody>
            </table>
          </div>

        </div>

      </div>

    </main>

  </div>

  <!-- MODAL: UPDATE STATUS PESANAN -->
  <div id="status-modal" class="modal-overlay">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3 class="modal-title">Perbarui Status Pesanan PO</h3>
        <button type="button" class="modal-close" onclick="closeStatusModal()">&times;</button>
      </div>

      <form id="update-status-form" onsubmit="handleSaveOrderStatus(event)">
        <input type="hidden" id="modal-target-order-id">

        <div style="background: var(--primary-subtle); padding: 12px 16px; border-radius: var(--radius-md); margin-bottom: 20px;">
          <div style="font-size: 12px; color: var(--text-muted);">Nomor Pesanan / Resi:</div>
          <div id="modal-display-order-id" style="font-size: 18px; font-weight: 800; color: var(--primary);">PO-2026-XXXX</div>
          <div id="modal-display-customer" style="font-size: 13px; color: var(--text-main); font-weight: 500; margin-top: 2px;">Nama Pemesan</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="modal-payment-status">Status Pembayaran</label>
          <select id="modal-payment-status" class="form-control">
            <option value="Menunggu Verifikasi DP">Menunggu Verifikasi DP</option>
            <option value="DP 50% Lunas">DP 50% Lunas (Siap Masuk Antrean)</option>
            <option value="Lunas 100%">Lunas 100%</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="modal-production-status">Alur Pengerjaan Produksi</label>
          <select id="modal-production-status" class="form-control">
            <option value="Verifikasi Pesanan">1. Verifikasi Pesanan &amp; Antrean</option>
            <option value="Menunggu Kuota Batch">2. Menunggu Penutupan Kuota Batch</option>
            <option value="Sedang Produksi">3. Sedang Produksi (Potong/Jahit/Bordir)</option>
            <option value="Quality Check">4. Quality Check (Pengecekan Kerapihan)</option>
            <option value="Dalam Pengiriman">5. Dalam Pengiriman Ekspedisi</option>
            <option value="Selesai">6. Pesanan Selesai Diterima</option>
          </select>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 24px;">
          <button type="submit" class="btn btn-accent btn-block">
            Simpan Perubahan
          </button>
          <button type="button" class="btn btn-outline-primary btn-block" onclick="closeStatusModal()">
            Batal
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script src="js/script.js"></script>
  <script src="js/three.min.js"></script>
  <script src="js/fastender-3d.js"></script>
  <script>
    const ADMIN_ORDERS = <?= json_encode($orders) ?>.map(o => ({
      orderId: o.order_code || o.orderId,
      date: o.date || 'Baru',
      customerName: o.customer_name || o.customerName,
      org: o.organization || o.org || '-',
      productSummary: o.product_summary || o.productSummary || 'Produk PO',
      qty: o.qty || 1,
      total: o.total_price || o.total || 0,
      paymentStatus: o.payment_status || o.paymentStatus || 'DP 50% Lunas',
      productionStatus: o.production_status || o.productionStatus || 'Sedang Produksi'
    }));

    document.addEventListener("DOMContentLoaded", () => {
      loadStoredOrders();
      renderOrdersTable(ADMIN_ORDERS);
      updateDashboardStats();

      // Mobile sidebar toggle
      const sidebarToggle = document.getElementById("toggle-admin-sidebar");
      const sidebar = document.getElementById("admin-sidebar");
      if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
        });
      }
    });

    function loadStoredOrders() {
      const stored = localStorage.getItem("fastender_all_orders");
      if (stored) {
        try {
          const parsed = JSON.parse(stored);
          parsed.forEach(pOrder => {
            const exists = ADMIN_ORDERS.some(o => o.orderId === pOrder.orderId);
            if (!exists) {
              const firstItem = pOrder.items && pOrder.items[0] ? pOrder.items[0] : { name: "Produk PO", size: "L", color: "" };
              ADMIN_ORDERS.unshift({
                orderId: pOrder.orderId,
                date: pOrder.date || "Hari ini",
                customerName: pOrder.customerName,
                org: pOrder.org || pOrder.city || "-",
                productSummary: `${firstItem.name} (${firstItem.size || 'L'}, ${firstItem.color || 'Navy'})`,
                qty: pOrder.items ? pOrder.items.reduce((acc, i) => acc + (i.quantity || 1), 0) : 1,
                total: pOrder.total,
                paymentStatus: pOrder.paymentScheme === "full" ? "Lunas 100%" : "DP 50% Lunas",
                productionStatus: "Sedang Produksi"
              });
            }
          });
        } catch (e) {
          console.error(e);
        }
      }
    }

    function renderOrdersTable(data) {
      const tbody = document.getElementById("admin-orders-tbody");
      const countBadge = document.getElementById("sidebar-order-count");
      if (countBadge) countBadge.textContent = ADMIN_ORDERS.length;

      if (!tbody) return;

      if (data.length === 0) {
        tbody.innerHTML = `
          <tr>
            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
              Tidak ada data pesanan yang sesuai dengan filter pencarian.
            </td>
          </tr>
        `;
        return;
      }

      tbody.innerHTML = data.map(order => {
        let badgeClass = "pending";
        if (order.productionStatus === "Sedang Produksi") badgeClass = "production";
        else if (order.productionStatus === "Quality Check") badgeClass = "qc";
        else if (order.productionStatus === "Dalam Pengiriman") badgeClass = "shipped";
        else if (order.productionStatus === "Selesai") badgeClass = "completed";

        let payBadgeClass = order.paymentStatus === "Lunas 100%" ? "completed" : (order.paymentStatus === "DP 50% Lunas" ? "production" : "pending");

        return `
          <tr>
            <td class="order-id">${order.orderId}</td>
            <td style="color: var(--text-muted); font-size: 12px;">${order.date}</td>
            <td>
              <div class="customer-name">${order.customerName}</div>
              <div class="customer-sub">${order.org || "-"}</div>
            </td>
            <td>
              <div style="font-weight: 500;">${order.productSummary}</div>
              <div style="font-size: 11px; color: var(--text-muted);">${order.qty} pcs</div>
            </td>
            <td style="font-weight: 700; color: var(--primary);">${formatRupiah(order.total)}</td>
            <td>
              <span class="status-badge ${payBadgeClass}">
                ${order.paymentStatus}
              </span>
            </td>
            <td>
              <span class="status-badge ${badgeClass}">
                ● ${order.productionStatus}
              </span>
            </td>
            <td>
              <div class="action-btn-group">
                <button class="action-icon-btn" onclick="openStatusModal('${order.orderId}')" title="Ubah Status">
                  Update
                </button>
                <a href="lacak.php?resi=${order.orderId}" target="_blank" class="action-icon-btn" style="color: var(--accent);" title="Lihat Timeline">
                  Lacak
                </a>
              </div>
            </td>
          </tr>
        `;
      }).join("");
    }

    function updateDashboardStats() {
      const statOrders = document.getElementById("stat-total-orders");
      const statRev = document.getElementById("stat-total-revenue");
      if (statOrders) statOrders.textContent = `${1420 + ADMIN_ORDERS.length}`;
      if (statRev) {
        let currentTotalRevenue = ADMIN_ORDERS.reduce((acc, o) => acc + (o.total || 0), 0);
        statRev.textContent = formatRupiah(184500000 + currentTotalRevenue);
      }
    }

    function filterOrdersByStatus(status) {
      if (status === "all") {
        renderOrdersTable(ADMIN_ORDERS);
      } else {
        const filtered = ADMIN_ORDERS.filter(o => o.productionStatus === status);
        renderOrdersTable(filtered);
      }
    }

    function handleAdminSearch(keyword) {
      const q = keyword.toLowerCase().trim();
      const filtered = ADMIN_ORDERS.filter(o => 
        o.orderId.toLowerCase().includes(q) ||
        o.customerName.toLowerCase().includes(q) ||
        (o.org && o.org.toLowerCase().includes(q)) ||
        o.productSummary.toLowerCase().includes(q)
      );
      renderOrdersTable(filtered);
    }

    function openStatusModal(orderId) {
      const order = ADMIN_ORDERS.find(o => o.orderId === orderId);
      if (!order) return;

      document.getElementById("modal-target-order-id").value = order.orderId;
      document.getElementById("modal-display-order-id").textContent = order.orderId;
      document.getElementById("modal-display-customer").textContent = `${order.customerName} (${order.org || '-'})`;
      document.getElementById("modal-payment-status").value = order.paymentStatus;
      document.getElementById("modal-production-status").value = order.productionStatus;

      document.getElementById("status-modal").classList.add("active");
    }

    function closeStatusModal() {
      document.getElementById("status-modal").classList.remove("active");
    }

    function handleSaveOrderStatus(e) {
      e.preventDefault();
      const targetId = document.getElementById("modal-target-order-id").value;
      const newPayStatus = document.getElementById("modal-payment-status").value;
      const newProdStatus = document.getElementById("modal-production-status").value;

      const order = ADMIN_ORDERS.find(o => o.orderId === targetId);
      if (order) {
        order.paymentStatus = newPayStatus;
        order.productionStatus = newProdStatus;
        
        const stored = localStorage.getItem("fastender_all_orders");
        if (stored) {
          try {
            const parsed = JSON.parse(stored);
            const foundStored = parsed.find(o => o.orderId === targetId);
            if (foundStored) {
              foundStored.paymentStatus = newPayStatus;
              foundStored.productionStatus = newProdStatus;
              localStorage.setItem("fastender_all_orders", JSON.stringify(parsed));
            }
          } catch(err){}
        }

        renderOrdersTable(ADMIN_ORDERS);
        closeStatusModal();
        showToast(`Status pesanan ${targetId} berhasil diperbarui!`, "success");
      }
    }

    function exportOrdersData() {
      showToast("Sedang mengekspor rekap PO ke file CSV...", "info");
      setTimeout(() => {
        showToast("File Rekap_PreOrder_Fastender_2026.csv siap diunduh!", "success");
      }, 1000);
    }

    // ========================================================
    // PENGATURAN HERO BANNER & COUNTDOWN PRE-ORDER
    // ========================================================
    function previewHeroBanner(input) {
      if (input.files && input.files[0]) {
        const file = input.files[0];
        if (!file.type.startsWith('image/')) {
          showToast("Mohon pilih file gambar yang valid (JPG, PNG, WebP)!", "danger");
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          const previewImg = document.getElementById("hero-banner-preview");
          if (previewImg) {
            previewImg.src = e.target.result;
          }
          showToast(`File banner baru dipilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`, "info");
        };
        reader.readAsDataURL(file);
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

      fetch("admin-dashboard.php", {
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
          if (data.config && data.config.banner_image) {
            const previewImg = document.getElementById("hero-banner-preview");
            if (previewImg) previewImg.src = data.config.banner_image;
          }
        } else {
          showToast("Gagal menyimpan pengaturan: " + (data.message || "Error"), "danger");
        }
      })
      .catch(err => {
        btn.disabled = false;
        btn.innerHTML = `Simpan Pengaturan Hero &amp; Countdown`;
        // Form fallback jika fetch terkendala
        form.submit();
      });
    }
  </script>
</body>
</html>
