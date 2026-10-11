<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Lacak Status Pesanan (lacak.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Lacak Status Pesanan Pre-Order - Fastender';
$activePage = 'lacak';
$initialResi = sanitize($_GET['resi'] ?? '');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     HALAMAN LACAK PESANAN (LAYOUT TERPUSAT)
     ========================================== -->
<section class="section-padding">
  <div class="container">
    
    <div class="track-container">
      
      <!-- CARD FORM INPUT SATU INPUT (NOMOR RESI) & TOMBOL "LACAK" -->
      <div class="track-search-card">
        <h1 style="font-size: 26px; font-weight: 700; color: var(--color-ink-black, #111111); margin-bottom: 24px; letter-spacing: -0.04em;">
          Lacak Status Pesanan
        </h1>

        <!-- Form Satu Input + Tombol "Lacak" -->
        <form id="track-form" class="track-form" onsubmit="handleTrackSubmit(event)">
          <input 
            type="text" 
            id="resi-input" 
            class="track-input" 
            placeholder="Contoh: PO-2026-8891 atau PO-2026-1045" 
            autocomplete="off"
            value="<?= htmlspecialchars($initialResi) ?>"
            required
          >
          <button type="submit" class="btn btn-accent btn-lg" style="padding: 0 28px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            Lacak
          </button>
        </form>

        <!-- Quick Samples -->
        <div class="quick-sample-chips">
          <span>Coba klik contoh resi siap lacak:</span>
          <button type="button" class="quick-chip" onclick="quickFillTrack('PO-2026-8891')">PO-2026-8891 (Sedang Produksi)</button>
          <button type="button" class="quick-chip" onclick="quickFillTrack('PO-2026-1045')">PO-2026-1045 (Dalam Pengiriman)</button>
          <button type="button" class="quick-chip" onclick="quickFillTrack('PO-2026-3390')">PO-2026-3390 (Selesai)</button>
        </div>
      </div>

      <!-- HASIL PELACAKAN (TIMELINE STATUS) -->
      <div id="track-result" class="track-result-card">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--color-fog, #d7d7d7); padding-bottom: 16px;">
          <div>
            <span style="font-size: 11px; text-transform: uppercase; font-family: var(--font-space-mono); color: var(--color-ash, #737780); font-weight: 700; letter-spacing: 0.05em;">NOMOR PESANAN</span>
            <h2 id="res-order-id" style="font-family: var(--font-space-mono); font-size: 22px; font-weight: 700; color: var(--color-ink-black, #111111);">PO-2026-8891</h2>
          </div>
          <span id="res-status-badge" class="badge badge-accent" style="font-size: 12px; padding: 4px 12px;">
            Sedang Diproduksi
          </span>
        </div>

        <!-- Rincian Metadata Pesanan -->
        <div class="order-meta-box">
          <div>
            <span style="font-size: 11px; color: var(--color-ash, #737780); display: block;">Nama Pemesan</span>
            <strong id="res-customer-name" style="font-size: 14px; color: var(--color-ink-black, #111111);">-</strong>
            <div id="res-customer-org" style="font-size: 12px; color: var(--color-ash, #737780);">-</div>
          </div>
          <div>
            <span style="font-size: 11px; color: var(--color-ash, #737780); display: block;">Produk Pre-Order</span>
            <strong id="res-product-name" style="font-size: 14px; color: var(--color-ink-black, #111111);">-</strong>
            <div id="res-product-meta" style="font-size: 12px; color: var(--color-ash, #737780);">-</div>
          </div>
          <div>
            <span style="font-size: 11px; color: var(--color-ash, #737780); display: block;">Estimasi Pengiriman</span>
            <strong id="res-estimated-date" style="font-family: var(--font-space-mono); font-size: 14px; color: var(--color-electric-cobalt, #000aff);">15 Nov 2026</strong>
            <div id="res-destination" style="font-size: 12px; color: var(--color-ash, #737780);">-</div>
          </div>
        </div>

        <!-- TIMELINE STATUS PENGIRIMAN & PRODUKSI -->
        <h3 style="font-size: 16px; font-weight: 700; color: var(--color-ink-black, #111111); margin-bottom: 24px; letter-spacing: -0.04em;">
          Riwayat &amp; Timeline Pengerjaan PO:
        </h3>

        <div id="timeline-steps-list" class="timeline">
          <!-- Rendered by JS -->
        </div>

        <div style="margin-top: 32px; padding: 16px; background: var(--surface-plaster, #ecedee); border-radius: var(--radius-cards, 10px); font-size: 13px; color: var(--color-ash, #737780); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
          <span>Ada pertanyaan atau perubahan bordir nama?</span>
          <a href="https://wa.me/6281234567890" target="_blank" style="color: var(--color-electric-cobalt, #000aff); font-weight: 700;">
            Chat CS Fastender di WhatsApp &rarr;
          </a>
        </div>

      </div>

      <!-- ERROR STATE (RESI TIDAK DITEMUKAN) -->
      <div id="track-not-found" style="display: none; text-align: center; padding: 48px 24px; background: var(--surface-plaster, #ecedee); border-radius: var(--radius-cards, 28px); border: 1px dashed var(--color-slate, #a1a4aa); margin-top: 24px;">
        <div style="display: flex; justify-content: center; margin-bottom: 12px;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d9383a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <h3 style="font-size: 18px; color: var(--color-ink-black, #111111); font-weight: 700;">Nomor Resi Tidak Ditemukan</h3>
        <p style="font-size: 14px; color: var(--color-ash, #737780); margin: 6px auto 18px; max-width: 440px;">
          Pastikan nomor resi sesuai dengan invoice pemesanan (Contoh: PO-2026-8891).
        </p>
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickFillTrack('PO-2026-8891')">
          Gunakan Contoh Resi Aktif
        </button>
      </div>

    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
  // Data Pesanan Demo untuk Lacak Resi
  const TRACK_ORDERS_DATABASE = {
    "PO-2026-8891": {
      orderId: "PO-2026-8891",
      customerName: "Ahmad Fauzi",
      org: "Himpunan Informatika 2026",
      productName: "Varsity Jacket Premium Angkatan",
      productMeta: "Ukuran L, Warna Navy-White (24 pcs)",
      destination: "Sleman, D.I. Yogyakarta",
      estimatedDelivery: "15 Nov 2026",
      currentStep: 3, // 1-6
      timeline: [
        { title: "Pesanan Masuk & Verifikasi DP", desc: "Uang muka 50% telah diverifikasi oleh tim Fastender.", time: "12 Okt 2026, 14:20 WIB", done: true },
        { title: "Penutupan Kuota Batch 1", desc: "Target 100 pcs tercapai penuh. Surat Perintah Kerja (SPK) diterbitkan.", time: "14 Okt 2026, 23:59 WIB", done: true },
        { title: "Sedang Produksi Konveksi", desc: "Kain katun fleece 330gsm dipotong dan proses bordir komputer dimulai.", time: "16 Okt 2026, 09:00 WIB", current: true },
        { title: "Quality Check & Steam Ironing", desc: "Pemeriksaan kerapihan benang bordir dan ukuran sesuai size chart.", time: "Estimasi 28 Okt 2026", pending: true },
        { title: "Packing & Pengiriman Ekspedisi", desc: "Paket diserahkan ke JNE Cargo bersama nomor resi resmi.", time: "Estimasi 31 Okt 2026", pending: true },
        { title: "Pesanan Selesai Diterima", desc: "Penerimaan oleh perwakilan panitia / pemesan.", time: "Estimasi 03 Nov 2026", pending: true }
      ]
    },
    "PO-2026-1045": {
      orderId: "PO-2026-1045",
      customerName: "Siti Rahma",
      org: "BEM Fakultas Teknik Undip",
      productName: "Kemeja PDH Drill Original 1919",
      productMeta: "Ukuran M, Warna Deep Navy (20 pcs)",
      destination: "Semarang, Jawa Tengah",
      estimatedDelivery: "24 Okt 2026",
      currentStep: 5,
      timeline: [
        { title: "Pesanan Masuk & Verifikasi DP", desc: "Pembayaran lunas 100% tervalidasi.", time: "05 Okt 2026", done: true },
        { title: "Penutupan Kuota Batch 1", desc: "Kuota batch terpenuhi.", time: "07 Okt 2026", done: true },
        { title: "Selesai Produksi Konveksi", desc: "Jahitan rantai ganda dan bordir komputer selesai.", time: "14 Okt 2026", done: true },
        { title: "Lolos Quality Check", desc: "100% item lolos uji standar mutu Fastender.", time: "17 Okt 2026", done: true },
        { title: "Dalam Pengiriman Ekspedisi", desc: "Sedang dalam perjalanan via SiCepat Halu (Resi: 00412891928).", time: "19 Okt 2026, 11:30 WIB", current: true },
        { title: "Pesanan Selesai Diterima", desc: "Menunggu konfirmasi penerimaan di lokasi tujuan.", time: "Estimasi 24 Okt 2026", pending: true }
      ]
    },
    "PO-2026-3390": {
      orderId: "PO-2026-3390",
      customerName: "Budi Santoso",
      org: "Teknik Mesin",
      productName: "Hoodie Heavyweight Fleece 330gsm",
      productMeta: "Ukuran XXL, Deep Navy (1 pcs)",
      destination: "Surabaya, Jawa Timur",
      estimatedDelivery: "Selesai pada 18 Okt 2026",
      currentStep: 6,
      timeline: [
        { title: "Pesanan Masuk & Verifikasi DP", desc: "Pembayaran lunas.", time: "28 Sep 2026", done: true },
        { title: "Penutupan Kuota Batch", desc: "Batch diproses.", time: "30 Sep 2026", done: true },
        { title: "Selesai Produksi", desc: "Produksi selesai tepat waktu.", time: "08 Okt 2026", done: true },
        { title: "Quality Check", desc: "Lolos QC.", time: "10 Okt 2026", done: true },
        { title: "Pengiriman Ekspedisi", desc: "Terkirim via JNE.", time: "12 Okt 2026", done: true },
        { title: "Pesanan Selesai Diterima", desc: "Paket telah diterima oleh Budi Santoso di alamat tujuan.", time: "18 Okt 2026, 15:45 WIB", done: true }
      ]
    }
  };

  document.addEventListener("DOMContentLoaded", () => {
    // Muat data dari input query param resi jika ada
    const resiInput = document.getElementById("resi-input");
    const val = resiInput.value.trim().toUpperCase();
    if (val) {
      findAndDisplayOrder(val);
    }
  });

  function quickFillTrack(resi) {
    document.getElementById("resi-input").value = resi;
    findAndDisplayOrder(resi);
  }

  function handleTrackSubmit(e) {
    e.preventDefault();
    const resi = document.getElementById("resi-input").value.trim().toUpperCase();
    if (!resi) return;
    findAndDisplayOrder(resi);
  }

  function findAndDisplayOrder(resi) {
    let order = TRACK_ORDERS_DATABASE[resi];

    // Cek juga dari pesanan checkout pengguna di LocalStorage
    if (!order) {
      const stored = localStorage.getItem("fastender_all_orders");
      if (stored) {
        try {
          const list = JSON.parse(stored);
          const found = list.find(o => o.orderId === resi);
          if (found) {
            order = {
              orderId: found.orderId,
              customerName: found.customerName,
              org: found.org || "-",
              productName: found.items && found.items[0] ? found.items[0].name : "Produk Pre-Order",
              productMeta: `${found.items ? found.items.length : 1} varian pesanan`,
              destination: found.city || "Indonesia",
              estimatedDelivery: "14 - 21 Hari Kerja",
              currentStep: 1,
              timeline: [
                { title: "Pesanan Berhasil Masuk", desc: "Pesanan baru telah dicatat di sistem Fastender.", time: found.date, current: true },
                { title: "Verifikasi Pembayaran DP", desc: "Menunggu verifikasi pembayaran atas bukti transfer.", time: "Segera", pending: true },
                { title: "Penutupan Kuota Batch", desc: "Menunggu pemenuhan batas kuota batch angkatan.", time: "Jadwal Tutup Batch", pending: true },
                { title: "Produksi Konveksi", desc: "Proses pemotongan kain dan bordir komputer presisi.", time: "Estimasi 14 Hari", pending: true },
                { title: "Quality Check & Packing", desc: "Pengecekan akhir mutu pakaian.", time: "Menunggu", pending: true },
                { title: "Pengiriman Sampai Alamat", desc: "Pengiriman kurir ekspedisi ke lokasi tujuan.", time: "Estimasi Pengiriman", pending: true }
              ]
            };
          }
        } catch(e) {}
      }
    }

    const resCard = document.getElementById("track-result");
    const notFoundCard = document.getElementById("track-not-found");

    if (!order) {
      resCard.style.display = "none";
      notFoundCard.style.display = "block";
      showToast(`Nomor resi "${resi}" tidak ditemukan`, "error");
      return;
    }

    notFoundCard.style.display = "none";
    resCard.style.display = "block";

    document.getElementById("res-order-id").textContent = order.orderId;
    document.getElementById("res-customer-name").textContent = order.customerName;
    document.getElementById("res-customer-org").textContent = order.org;
    document.getElementById("res-product-name").textContent = order.productName;
    document.getElementById("res-product-meta").textContent = order.productMeta;
    document.getElementById("res-estimated-date").textContent = order.estimatedDelivery;
    document.getElementById("res-destination").textContent = order.destination;

    const timelineContainer = document.getElementById("timeline-steps-list");
    timelineContainer.innerHTML = order.timeline.map((step, idx) => {
      let icon = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
      let statusClass = "done";

      if (step.current) {
        icon = `<span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; display: block;"></span>`;
        statusClass = "current";
      } else if (step.pending) {
        icon = (idx + 1);
        statusClass = "pending";
      }

      return `
        <div class="timeline-item ${statusClass}">
          <div class="timeline-dot">${icon}</div>
          <div class="timeline-content">
            <h4 class="timeline-title">${step.title}</h4>
            <p class="timeline-desc">${step.desc}</p>
            <span class="timeline-time">${step.time}</span>
          </div>
        </div>
      `;
    }).join("");

    showToast(`Data resi "${resi}" berhasil ditampilkan!`, "success");
    resCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
</script>
