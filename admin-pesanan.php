<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Manajemen Pesanan Pre-Order Masuk (admin-pesanan.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

$adminPage = 'pesanan';
$adminTitle = 'Pesanan PO';

$flashSuccess = $_SESSION['flash_order_success'] ?? '';
$flashError = $_SESSION['flash_order_error'] ?? '';
unset($_SESSION['flash_order_success'], $_SESSION['flash_order_error']);

// 1. Handle hapus pesanan via query string: admin-pesanan.php?action=delete&id=...
if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $targetCode = sanitize($_GET['id']);
    if (deleteOrderByCode($targetCode)) {
        $_SESSION['flash_order_success'] = 'Data pesanan "' . $targetCode . '" beserta file bukti pembayarannya berhasil dihapus.';
    } else {
        $_SESSION['flash_order_error'] = 'Gagal menghapus pesanan "' . $targetCode . '".';
    }
    header('Location: admin-pesanan.php?deleted=' . urlencode($targetCode));
    exit;
}

// 2. Handle aksi POST / AJAX (hapus pesanan atau update status pesanan / pembayaran)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['is_ajax']);

    if ($_POST['action'] === 'delete_order' && !empty($_POST['order_id'])) {
        $targetCode = sanitize($_POST['order_id']);
        $ok = deleteOrderByCode($targetCode);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $ok,
                'order_id' => $targetCode,
                'message' => $ok ? 'Pesanan berhasil dihapus.' : 'Gagal menghapus pesanan.'
            ]);
            exit;
        }
        $_SESSION['flash_order_success'] = 'Data pesanan "' . $targetCode . '" berhasil dihapus.';
        header('Location: admin-pesanan.php?deleted=' . urlencode($targetCode));
        exit;
    }

    // Handler validasi / update status pembayaran secara spesifik dari modal bukti transfer
    if ($_POST['action'] === 'update_payment_status' && !empty($_POST['order_id'])) {
        $targetCode = sanitize($_POST['order_id']);
        $payStatus = sanitize($_POST['payment_status'] ?? 'Menunggu Verifikasi');
        
        // Ambil status produksi saat ini agar tidak ter-reset
        $currentProdStatus = 'Menunggu Konfirmasi Admin';
        $currentOrders = getAllOrdersList();
        foreach ($currentOrders as $co) {
            $code = $co['order_code'] ?? ($co['orderId'] ?? '');
            if ($code === $targetCode) {
                $currentProdStatus = $co['production_status'] ?? ($co['productionStatus'] ?? 'Menunggu Konfirmasi Admin');
                break;
            }
        }

        // Jika pembayaran disetujui dan status produksi masih 0 (Menunggu Konfirmasi Admin),
        // otomatis naikkan ke status 3 (Sedang Produksi)
        if (($payStatus === 'DP Terverifikasi' || $payStatus === 'Lunas' || $payStatus === 'DP 50% Lunas' || $payStatus === 'Lunas 100%') 
            && ($currentProdStatus === 'Menunggu Konfirmasi Admin' || $currentProdStatus === '0. Menunggu Konfirmasi Admin')) {
            $currentProdStatus = 'Sedang Produksi';
        }

        $ok = updateOrderStatusByCode($targetCode, $payStatus, $currentProdStatus);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $ok,
                'order_id' => $targetCode,
                'payment_status' => $payStatus,
                'production_status' => $currentProdStatus,
                'message' => $ok ? 'Status pembayaran berhasil diperbarui.' : 'Gagal memperbarui status pembayaran.'
            ]);
            exit;
        }
        $_SESSION['flash_order_success'] = 'Status pembayaran pesanan "' . $targetCode . '" berhasil diubah menjadi ' . $payStatus . '.';
        header('Location: admin-pesanan.php');
        exit;
    }

    if ($_POST['action'] === 'update_order_status' && !empty($_POST['order_id'])) {
        $targetCode = sanitize($_POST['order_id']);
        $payStatus = sanitize($_POST['payment_status'] ?? 'Menunggu Verifikasi');
        $prodStatus = sanitize($_POST['production_status'] ?? 'Sedang Produksi');
        $ok = updateOrderStatusByCode($targetCode, $payStatus, $prodStatus);
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => $ok,
                'order_id' => $targetCode,
                'payment_status' => $payStatus,
                'production_status' => $prodStatus,
                'message' => $ok ? 'Status pesanan berhasil diperbarui.' : 'Gagal memperbarui status pesanan.'
            ]);
            exit;
        }
        $_SESSION['flash_order_success'] = 'Status pesanan "' . $targetCode . '" berhasil diperbarui.';
        header('Location: admin-pesanan.php');
        exit;
    }
}

$orders = getAllOrdersList();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Pesanan PO - Admin Fastender</title>
  
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
            <input type="text" id="admin-search-orders" placeholder="Cari No. Resi, nama pemesan, jurusan..." oninput="handleAdminSearch(this.value)">
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
            <h1 class="admin-page-title">Daftar Pesanan Pre-Order Masuk</h1>
            <p class="admin-page-sub">Kelola verifikasi pembayaran DP/Lunas, alur pengerjaan vendor, dan data pesanan PO.</p>
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

        <?php if (!empty($flashSuccess)): ?>
          <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13.5px; margin-bottom: 20px; font-weight: 600;">
            <?= htmlspecialchars($flashSuccess) ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
          <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #b91c1c; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13.5px; margin-bottom: 20px; font-weight: 600;">
            <?= htmlspecialchars($flashError) ?>
          </div>
        <?php endif; ?>

        <!-- TABEL DAFTAR PESANAN PRE-ORDER -->
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
                <option value="Menunggu Konfirmasi Admin">Menunggu Konfirmasi Admin</option>
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
                  <th>Nama Pemesan &amp; Jurusan</th>
                  <th>Produk &amp; Kuantitas</th>
                  <th>Total Tagihan</th>
                  <th>Bukti Bayar</th>
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

  <!-- MODAL: PREVIEW BUKTI PEMBAYARAN & VALIDASI STATUS ADMIN -->
  <div id="proof-modal" class="modal-overlay">
    <div class="modal-dialog modal-dialog-proof" style="max-width: 600px; padding: 26px;">
      <div class="modal-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
        <div>
          <h3 class="modal-title" style="font-size: 18px; font-weight: 800; color: var(--color-ink-black, #111111); margin: 0;">
            Bukti Pembayaran &amp; Validasi
          </h3>
          <p style="font-size: 12.5px; color: var(--color-ash, #737780); margin: 3px 0 0 0;">
            Nomor Pesanan: <span id="proof-display-order-id" style="font-weight: 800; color: var(--color-electric-cobalt, #000aff);">PO-XXXX</span>
          </p>
        </div>
        <button type="button" class="modal-close-btn" onclick="closeProofModal()">&times;</button>
      </div>

      <!-- Area Tampilan Gambar Bukti Transfer -->
      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px; margin-bottom: 16px;">
        <div style="width: 100%; max-height: 340px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #ffffff; border-radius: 8px;">
          <img id="modal-proof-img" src="" alt="Bukti Transfer" style="max-width: 100%; max-height: 330px; object-fit: contain; cursor: zoom-in;" onclick="window.open(this.src, '_blank')">
        </div>
        <div style="margin-top: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b; padding: 0 4px;">
          <span>Klik gambar untuk memperbesar</span>
          <a id="modal-proof-full-link" href="#" target="_blank" style="color: #619ae6; font-weight: 700; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;">
            Buka File Asli ↗
          </a>
        </div>
      </div>

      <!-- Rincian Singkat Pesanan -->
      <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 12px 14px; margin-bottom: 18px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; font-size: 12.5px;">
          <div>
            <div style="color: #64748b; font-size: 11px;">Nama Pemesan:</div>
            <div id="proof-display-customer" style="font-weight: 700; color: #1e293b;">-</div>
          </div>
          <div>
            <div style="color: #64748b; font-size: 11px;">Jurusan / Organisasi:</div>
            <div id="proof-display-org" style="font-weight: 600; color: #1e293b;">-</div>
          </div>
          <div>
            <div style="color: #64748b; font-size: 11px;">Skema Pembayaran:</div>
            <div id="proof-display-scheme" style="font-weight: 600; color: #1e293b;">-</div>
          </div>
          <div>
            <div style="color: #64748b; font-size: 11px;">Total Tagihan:</div>
            <div id="proof-display-total" style="font-weight: 800; color: #2563eb; font-size: 14px;">-</div>
          </div>
        </div>
      </div>

      <!-- Form Validasi Status Pembayaran -->
      <form id="proof-validation-form" onsubmit="handleSaveProofValidation(event)">
        <input type="hidden" id="proof-target-order-id">

        <div class="form-group" style="margin-bottom: 14px;">
          <label class="form-label" for="proof-payment-status" style="display: block; font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
            Ubah Status Pembayaran:
          </label>
          <select id="proof-payment-status" class="form-control" style="width: 100%;">
            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
            <option value="DP Terverifikasi">DP Terverifikasi</option>
            <option value="Lunas">Lunas</option>
            <option value="Ditolak / Tidak Valid">Ditolak / Tidak Valid</option>
          </select>
        </div>

        <!-- Tombol Aksi Cepat (Quick Action) & Simpan -->
        <div style="display: flex; gap: 8px; margin-bottom: 12px;">
          <button type="button" class="btn btn-sm" onclick="quickApplyProofStatus('approve')" style="flex: 1; background: #10b981; color: #ffffff; border: none; padding: 9px 12px; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            Terima Pembayaran
          </button>
          <button type="button" class="btn btn-sm" onclick="quickApplyProofStatus('reject')" style="flex: 1; background: #ef4444; color: #ffffff; border: none; padding: 9px 12px; border-radius: 8px; font-weight: 700; font-size: 12.5px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            Tolak Pembayaran
          </button>
        </div>

        <div style="display: flex; gap: 10px;">
          <button type="submit" class="btn btn-accent btn-block" style="flex: 2; padding: 10px 14px; font-size: 13.5px; font-weight: 700;">
            Simpan Status
          </button>
          <button type="button" class="btn btn-outline-primary" onclick="closeProofModal()" style="flex: 1; padding: 10px 14px; font-size: 13.5px;">
            Tutup
          </button>
        </div>
      </form>

    </div>
  </div>

  <!-- MODAL: UPDATE STATUS PESANAN -->
  <div id="status-modal" class="modal-overlay">
    <div class="modal-dialog">
      <div class="modal-header">
        <h3 class="modal-title">Perbarui Status Pesanan PO</h3>
        <button type="button" class="modal-close-btn" onclick="closeStatusModal()">&times;</button>
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
            <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
            <option value="DP Terverifikasi">DP Terverifikasi</option>
            <option value="DP 50% Lunas">DP 50% Lunas (Siap Masuk Antrean)</option>
            <option value="Lunas">Lunas</option>
            <option value="Lunas 100%">Lunas 100%</option>
            <option value="Ditolak / Tidak Valid">Ditolak / Tidak Valid</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="modal-production-status">Alur Pengerjaan Produksi</label>
          <select id="modal-production-status" class="form-control">
            <option value="Menunggu Konfirmasi Admin">0. Menunggu Konfirmasi Admin</option>
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
  <script>
    let ADMIN_ORDERS = <?= json_encode($orders) ?>.map(o => ({
      orderId: o.order_code || o.orderId,
      date: o.date || (o.created_at ? o.created_at.substring(0, 10) : 'Baru'),
      customerName: o.customer_name || o.customerName || 'Pemesan',
      phone: o.customer_phone || o.customerPhone || '-',
      org: o.jurusan || o.organization || o.org || '-',
      productSummary: o.product_summary || o.productSummary || 'Produk PO',
      qty: o.qty || 1,
      total: o.total_price || o.total || 0,
      totalBayar: o.total_bayar || o.totalBayar || 0,
      paymentScheme: o.payment_scheme || o.paymentScheme || 'dp',
      paymentMethod: o.payment_method || o.paymentMethod || 'BCA',
      paymentStatus: o.payment_status || o.paymentStatus || 'Menunggu Verifikasi',
      productionStatus: o.production_status || o.productionStatus || 'Menunggu Konfirmasi Admin',
      buktiPembayaran: o.bukti_pembayaran || o.buktiPembayaran || null
    }));

    document.addEventListener("DOMContentLoaded", () => {
      // Jika baru saja menghapus order via URL parameter ?deleted=..., bersihkan juga dari localStorage
      const urlParams = new URLSearchParams(window.location.search);
      const deletedCode = urlParams.get("deleted");
      if (deletedCode) {
        removeOrderFromLocalStorage(deletedCode);
      }

      loadStoredOrders();
      renderOrdersTable(ADMIN_ORDERS);

      // Mobile sidebar toggle
      const sidebarToggle = document.getElementById("toggle-admin-sidebar");
      const sidebar = document.getElementById("admin-sidebar");
      if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", () => {
          sidebar.classList.toggle("open");
        });
      }
    });

    function removeOrderFromLocalStorage(orderId) {
      const stored = localStorage.getItem("fastender_all_orders");
      if (stored) {
        try {
          const parsed = JSON.parse(stored);
          if (Array.isArray(parsed)) {
            const filtered = parsed.filter(o => (o.order_code || o.orderId) !== orderId);
            localStorage.setItem("fastender_all_orders", JSON.stringify(filtered));
          }
        } catch (e) {}
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
              const firstItem = pOrder.items && pOrder.items[0] ? pOrder.items[0] : { name: "Produk PO", size: "L", color: "Standar" };
              ADMIN_ORDERS.unshift({
                orderId: pCode,
                date: pOrder.date || "Hari ini",
                customerName: pOrder.customer_name || pOrder.customerName || "Pemesan",
                phone: pOrder.customer_phone || pOrder.customerPhone || "-",
                org: pOrder.jurusan || pOrder.org || pOrder.city || "-",
                productSummary: pOrder.product_summary || `${firstItem.name} (${firstItem.size || 'L'}, ${firstItem.color || 'Standar'})`,
                qty: pOrder.qty || (pOrder.items ? pOrder.items.reduce((acc, i) => acc + (i.quantity || 1), 0) : 1),
                total: pOrder.total_price || pOrder.total || 0,
                totalBayar: pOrder.total_bayar || pOrder.due || 0,
                paymentScheme: pOrder.payment_scheme || pOrder.paymentScheme || "dp",
                paymentMethod: pOrder.payment_method || pOrder.pay_method || "BCA",
                paymentStatus: pOrder.payment_status || pOrder.paymentStatus || "Menunggu Verifikasi",
                productionStatus: pOrder.production_status || pOrder.productionStatus || "Menunggu Konfirmasi Admin",
                buktiPembayaran: pOrder.bukti_pembayaran || pOrder.buktiPembayaran || null
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
            <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
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

        let payBadgeClass = "pending";
        if (order.paymentStatus === "Lunas" || order.paymentStatus === "Lunas 100%") {
          payBadgeClass = "completed";
        } else if (order.paymentStatus === "DP Terverifikasi" || order.paymentStatus === "DP 50% Lunas") {
          payBadgeClass = "verified";
        } else if (order.paymentStatus.includes("Ditolak") || order.paymentStatus.includes("Tidak Valid")) {
          payBadgeClass = "rejected";
        } else {
          payBadgeClass = "pending";
        }

        const hasProof = order.buktiPembayaran && String(order.buktiPembayaran).trim() !== "" && order.buktiPembayaran !== "-";
        const proofHtml = hasProof ? `
          <button type="button" class="btn-view-proof" onclick="openProofModal('${order.orderId}')" title="Lihat Bukti Transfer">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            Lihat Bukti
          </button>
        ` : `
          <span class="badge-proof-empty">
            Belum Upload
          </span>
        `;

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
              ${proofHtml}
            </td>
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
                <button type="button" class="action-icon-btn" onclick="openStatusModal('${order.orderId}')" title="Ubah Status">
                  Update
                </button>
                <a href="lacak.php?resi=${encodeURIComponent(order.orderId)}" target="_blank" class="action-icon-btn" style="color: var(--accent);" title="Lihat Timeline">
                  Lacak
                </a>
                <a href="admin-pesanan.php?action=delete&id=${encodeURIComponent(order.orderId)}" class="action-icon-btn btn-delete-order" onclick="return handleDeleteOrder(event, '${order.orderId}')" title="Hapus Pesanan">
                  Hapus
                </a>
              </div>
            </td>
          </tr>
        `;
      }).join("");
    }

    function handleDeleteOrder(e, orderId) {
      if (!confirm('Yakin ingin menghapus data pesanan ini?')) {
        if (e) e.preventDefault();
        return false;
      }
      removeOrderFromLocalStorage(orderId);
      return true;
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
        (o.phone && o.phone.toLowerCase().includes(q)) ||
        (o.org && o.org.toLowerCase().includes(q)) ||
        o.productSummary.toLowerCase().includes(q)
      );
      renderOrdersTable(filtered);
    }

    // ==========================================
    // MODAL PREVIEW BUKTI PEMBAYARAN & QUICK VALIDASI
    // ==========================================
    function openProofModal(orderId) {
      const order = ADMIN_ORDERS.find(o => o.orderId === orderId);
      if (!order) return;

      document.getElementById("proof-target-order-id").value = order.orderId;
      document.getElementById("proof-display-order-id").textContent = order.orderId;
      document.getElementById("proof-display-customer").textContent = order.customerName + (order.phone && order.phone !== '-' ? ` (${order.phone})` : '');
      document.getElementById("proof-display-org").textContent = order.org || '-';
      
      const schemeLabel = (order.paymentScheme === 'full') ? 'Lunas 100%' : 'DP 50%';
      const methodLabel = order.paymentMethod ? ` via ${order.paymentMethod}` : '';
      document.getElementById("proof-display-scheme").textContent = schemeLabel + methodLabel;
      document.getElementById("proof-display-total").textContent = formatRupiah(order.total);

      // Sinkronisasi status dropdown
      const selectStatus = document.getElementById("proof-payment-status");
      if (selectStatus) {
        if (order.paymentStatus === "Lunas 100%") {
          selectStatus.value = "Lunas";
        } else if (order.paymentStatus === "DP 50% Lunas") {
          selectStatus.value = "DP Terverifikasi";
        } else {
          selectStatus.value = order.paymentStatus;
        }
        if (!selectStatus.value) {
          selectStatus.value = "Menunggu Verifikasi";
        }
      }

      // Pasang gambar bukti transfer
      const imgEl = document.getElementById("modal-proof-img");
      const linkEl = document.getElementById("modal-proof-full-link");
      const proofSrc = order.buktiPembayaran || 'uploads/bukti/sample_transfer.jpg';

      if (imgEl) {
        imgEl.src = proofSrc;
      }
      if (linkEl) {
        linkEl.href = proofSrc;
      }

      document.getElementById("proof-modal").classList.add("active");
    }

    function closeProofModal() {
      const modal = document.getElementById("proof-modal");
      if (modal) modal.classList.remove("active");
    }

    function quickApplyProofStatus(type) {
      const targetId = document.getElementById("proof-target-order-id").value;
      const order = ADMIN_ORDERS.find(o => o.orderId === targetId);
      const selectStatus = document.getElementById("proof-payment-status");
      
      if (type === 'approve') {
        if (order && order.paymentScheme === 'full') {
          selectStatus.value = "Lunas";
        } else {
          selectStatus.value = "DP Terverifikasi";
        }
      } else if (type === 'reject') {
        selectStatus.value = "Ditolak / Tidak Valid";
      }

      submitProofStatusUpdate(targetId, selectStatus.value);
    }

    function handleSaveProofValidation(e) {
      e.preventDefault();
      const targetId = document.getElementById("proof-target-order-id").value;
      const newStatus = document.getElementById("proof-payment-status").value;
      submitProofStatusUpdate(targetId, newStatus);
    }

    function submitProofStatusUpdate(targetId, newStatus) {
      const order = ADMIN_ORDERS.find(o => o.orderId === targetId);
      if (!order) return;

      order.paymentStatus = newStatus;
      
      // Jika pembayaran diverifikasi dan status produksi masih 0 (Menunggu Konfirmasi), naikkan ke Sedang Produksi
      if ((newStatus === 'DP Terverifikasi' || newStatus === 'Lunas') && order.productionStatus === 'Menunggu Konfirmasi Admin') {
        order.productionStatus = 'Sedang Produksi';
      }

      // Simpan juga ke localStorage
      const stored = localStorage.getItem("fastender_all_orders");
      if (stored) {
        try {
          const parsed = JSON.parse(stored);
          const foundStored = parsed.find(o => (o.order_code || o.orderId) === targetId);
          if (foundStored) {
            foundStored.paymentStatus = newStatus;
            foundStored.payment_status = newStatus;
            foundStored.productionStatus = order.productionStatus;
            foundStored.production_status = order.productionStatus;
            localStorage.setItem("fastender_all_orders", JSON.stringify(parsed));
          }
        } catch(err){}
      }

      // Kirim request AJAX POST ke backend
      const formData = new FormData();
      formData.append("action", "update_payment_status");
      formData.append("order_id", targetId);
      formData.append("payment_status", newStatus);
      formData.append("is_ajax", "1");

      fetch("admin-pesanan.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(res => {
        if (res && res.production_status) {
          order.productionStatus = res.production_status;
        }
        renderOrdersTable(ADMIN_ORDERS);
      })
      .catch(() => {
        renderOrdersTable(ADMIN_ORDERS);
      });

      renderOrdersTable(ADMIN_ORDERS);
      closeProofModal();
      showToast(`Status pembayaran pesanan ${targetId} berhasil diubah: "${newStatus}"`, "success");
    }

    // ==========================================
    // MODAL UPDATE STATUS KESELURUHAN (STATUS & PRODUKSI)
    // ==========================================
    function openStatusModal(orderId) {
      const order = ADMIN_ORDERS.find(o => o.orderId === orderId);
      if (!order) return;

      document.getElementById("modal-target-order-id").value = order.orderId;
      document.getElementById("modal-display-order-id").textContent = order.orderId;
      document.getElementById("modal-display-customer").textContent = `${order.customerName} (${order.org || '-'})`;
      
      const selectPay = document.getElementById("modal-payment-status");
      if (selectPay) {
        selectPay.value = order.paymentStatus;
        if (!selectPay.value) {
          selectPay.value = "Menunggu Verifikasi";
        }
      }

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
            const foundStored = parsed.find(o => (o.order_code || o.orderId) === targetId);
            if (foundStored) {
              foundStored.paymentStatus = newPayStatus;
              foundStored.payment_status = newPayStatus;
              foundStored.productionStatus = newProdStatus;
              foundStored.production_status = newProdStatus;
              localStorage.setItem("fastender_all_orders", JSON.stringify(parsed));
            }
          } catch(err){}
        }

        // Simpan perubahan status ke backend PHP / database
        const formData = new FormData();
        formData.append("action", "update_order_status");
        formData.append("order_id", targetId);
        formData.append("payment_status", newPayStatus);
        formData.append("production_status", newProdStatus);
        formData.append("is_ajax", "1");

        fetch("admin-pesanan.php", {
          method: "POST",
          body: formData
        }).catch(() => {});

        renderOrdersTable(ADMIN_ORDERS);
        closeStatusModal();
        showToast(`Status pesanan ${targetId} berhasil diperbarui!`, "success");
      }
    }

    // ==========================================
    // EKSPOR DATA KE SPREADSHEET (EXCEL / CSV)
    // ==========================================
    function exportOrdersData() {
      const searchInput = document.getElementById("admin-search-orders");
      const filterStatus = document.getElementById("filter-order-status");
      
      const search = searchInput ? searchInput.value.trim() : "";
      const status = filterStatus ? filterStatus.value : "all";
      
      const params = new URLSearchParams();
      if (search) params.append("search", search);
      if (status && status !== "all") params.append("status", status);
      
      showToast("Menyiapkan file spreadsheet pesanan PO...", "info");
      setTimeout(() => {
        window.location.href = "export-pesanan.php?" + params.toString();
      }, 350);
    }
  </script>
</body>
</html>
