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

// Handler Reset Statistik via POST atau GET (action=reset_stats)
if (
    ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_stats') ||
    (isset($_GET['action']) && $_GET['action'] === 'reset_stats')
) {
    resetAllPreOrderStatistics();

    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['is_ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Statistik berhasil direset'
        ]);
        exit;
    }

    header('Location: admin-dashboard.php?msg=reset_ok');
    exit;
}

$flashDashboardMsg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'reset_ok') {
    $flashDashboardMsg = 'Statistik berhasil direset';
}

$orders = getAllOrdersList();
$stats = getDashboardStats($orders);
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
      <?php include __DIR__ . '/includes/admin-header.php'; ?>

      <!-- Area Konten Utama -->
      <div class="admin-content">

        <!-- Title & Action Button (Minimalist) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
          <h1 class="admin-page-title" style="margin: 0;">Dashboard</h1>
          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="admin-pesanan.php" class="btn btn-outline-primary btn-sm">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
              Pesanan
            </a>
            <a href="admin-input-produk.php" class="btn btn-accent btn-sm">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
              </svg>
              Tambah
            </a>
          </div>
        </div>

        <?php if (!empty($flashDashboardMsg)): ?>
          <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 12px 16px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span><strong><?= htmlspecialchars($flashDashboardMsg) ?>!</strong> Seluruh metrik statistik pre-order telah dikembalikan ke 0.</span>
          </div>
        <?php endif; ?>

        <!-- CARD STATISTIK -->
        <div class="admin-stats-grid" style="margin-bottom: 0;">
          
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
            <div class="stat-value" id="stat-total-orders"><?= number_format($stats['total_orders'], 0, ',', '.') ?></div>
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
            <div class="stat-value" id="stat-total-revenue"><?= formatRupiah($stats['total_revenue']) ?></div>
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
            <div class="stat-value" id="stat-in-production"><?= number_format($stats['in_production_pcs'], 0, ',', '.') ?> pcs</div>
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
            <div class="stat-value" id="stat-completed-pcs"><?= number_format($stats['completed_pcs'], 0, ',', '.') ?> pcs</div>
          </div>

        </div>

      </div>

    </main>

  </div>

  <!-- Scripts -->
  <script src="js/script.js"></script>
  <script>
    const STATS_CONFIG = <?= json_encode($stats) ?>;
    const IS_JUST_RESET = <?= json_encode(!empty($flashDashboardMsg)) ?>;

    if (IS_JUST_RESET) {
      localStorage.removeItem("fastender_all_orders");
    }

    const ADMIN_ORDERS = <?= json_encode($orders) ?>.map(o => ({
      orderId: o.order_code || o.orderId,
      total: Number(o.total_price || o.total || 0),
      qty: Number(o.qty || 1),
      productionStatus: o.production_status || o.productionStatus || "Menunggu Konfirmasi Admin"
    }));

    document.addEventListener("DOMContentLoaded", () => {
      if (IS_JUST_RESET && typeof showToast === "function") {
        showToast("Statistik berhasil direset", "success");
      }

      loadStoredOrders();
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

    function handleResetStats() {
      const confirmed = confirm('Apakah Anda yakin ingin mereset seluruh data statistik pre-order? Tindakan ini akan mengosongkan/mengembalikan angka ke 0.');
      if (!confirmed) {
        return;
      }

      localStorage.removeItem("fastender_all_orders");
      const form = document.getElementById("form-reset-stats");
      if (form) {
        form.submit();
      } else {
        window.location.href = "admin-dashboard.php?action=reset_stats";
      }
    }

    function loadStoredOrders() {
      const stored = localStorage.getItem("fastender_all_orders");
      if (stored) {
        try {
          const parsed = JSON.parse(stored);
          parsed.forEach(pOrder => {
            const pCode = pOrder.order_code || pOrder.orderId;
            const exists = ADMIN_ORDERS.some(o => o.orderId === pCode);
            if (!exists && pCode) {
              ADMIN_ORDERS.unshift({
                orderId: pCode,
                total: Number(pOrder.total_price || pOrder.total || 0),
                qty: Number(pOrder.qty || 1),
                productionStatus: pOrder.production_status || pOrder.productionStatus || "Menunggu Konfirmasi Admin"
              });
            }
          });
        } catch (e) {}
      }
      const countBadge = document.getElementById("sidebar-order-count");
      if (countBadge) countBadge.textContent = ADMIN_ORDERS.length;
    }

    function updateDashboardStats() {
      const statOrders = document.getElementById("stat-total-orders");
      const statRev = document.getElementById("stat-total-revenue");
      const statProd = document.getElementById("stat-in-production");
      const statComp = document.getElementById("stat-completed-pcs");

      const baseOrders = Number(STATS_CONFIG.base_orders || 0);
      const baseRevenue = Number(STATS_CONFIG.base_revenue || 0);
      const baseProdPcs = Number(STATS_CONFIG.base_production_pcs || 0);
      const baseCompPcs = Number(STATS_CONFIG.base_completed_pcs || 0);

      if (statOrders) {
        statOrders.textContent = (baseOrders + ADMIN_ORDERS.length).toLocaleString("id-ID");
      }

      if (statRev) {
        const currentTotalRevenue = ADMIN_ORDERS.reduce((acc, o) => acc + (Number(o.total) || 0), 0);
        statRev.textContent = formatRupiah(baseRevenue + currentTotalRevenue);
      }

      if (STATS_CONFIG.is_reset) {
        let dynProd = 0;
        let dynComp = 0;
        ADMIN_ORDERS.forEach(o => {
          const st = String(o.productionStatus || "").toLowerCase().trim();
          const q = Number(o.qty) || 1;
          if (["selesai", "dalam pengiriman", "terkirim", "completed", "shipped"].includes(st)) {
            dynComp += q;
          } else if (st !== "dibatalkan" && st !== "cancel") {
            dynProd += q;
          }
        });
        if (statProd) statProd.textContent = `${(baseProdPcs + dynProd).toLocaleString("id-ID")} pcs`;
        if (statComp) statComp.textContent = `${(baseCompPcs + dynComp).toLocaleString("id-ID")} pcs`;
      }
    }
  </script>
</body>
</html>
