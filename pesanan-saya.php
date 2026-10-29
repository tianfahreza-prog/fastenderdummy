<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Pesanan Saya (pesanan-saya.php)
 * PHP Native
 * 
 * Fitur:
 * - Daftar semua pesanan pengguna (identifikasi via nomor resi)
 * - Form pencarian pesanan berdasarkan nomor resi
 * - Detail pelacakan status pesanan dengan timeline
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Pesanan Saya - Lacak Status Pre-Order - Fastender';
$activePage = 'pesanan-saya';

$rawResi = isset($_GET['resi']) ? trim((string)$_GET['resi']) : '';
$searchedResi = sanitize($rawResi);
$hasSearched = ($searchedResi !== '');
$foundOrder = $hasSearched ? findOrderByResi($searchedResi) : null;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     HALAMAN PESANAN SAYA (LAYOUT TERPUSAT)
     ========================================== -->
<section class="section-padding">
  <div class="container">
    
    <div class="track-container">
      
      <!-- CARD FORM INPUT SATU INPUT (NOMOR RESI) & TOMBOL "LACAK" -->
      <div class="track-search-card">
        <h1 style="font-size: 26px; font-weight: 700; color: var(--color-ink-black, #111111); margin-bottom: 24px; letter-spacing: -0.04em;">
          Pesanan Saya
        </h1>

        <!-- Form Satu Input + Tombol "Lacak" -->
        <form id="track-form" class="track-form" action="pesanan-saya.php" method="GET">
          <input 
            type="text" 
            name="resi"
            id="resi-input" 
            class="track-input" 
            placeholder="Masukkan nomor resi pesanan PO Anda..." 
            autocomplete="off"
            value="<?= $hasSearched ? htmlspecialchars($searchedResi) : '' ?>"
            required
          >
          <button type="submit" class="btn btn-accent btn-lg" style="padding: 0 28px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            Cari Pesanan
          </button>
        </form>
      </div>

      <?php if ($hasSearched && $foundOrder): ?>
        <!-- HASIL PELACAKAN (TIMELINE STATUS - HANYA MUNCUL JIKA RESI VALID DITEMUKAN DI DATABASE) -->
        <div id="track-result" class="track-result-card" style="display: block;">
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--color-fog, #d7d7d7); padding-bottom: 16px;">
            <div>
              <span style="font-size: 11px; text-transform: uppercase; font-family: var(--font-space-mono); color: var(--color-ash, #737780); font-weight: 700; letter-spacing: 0.05em;">NOMOR PESANAN</span>
              <h2 id="res-order-id" style="font-family: var(--font-space-mono); font-size: 22px; font-weight: 700; color: var(--color-ink-black, #111111);">
                <?= htmlspecialchars($foundOrder['order_code']) ?>
              </h2>
            </div>
            <span id="res-status-badge" class="badge badge-accent" style="font-size: 12px; padding: 4px 12px;">
              <?= htmlspecialchars($foundOrder['production_status']) ?>
            </span>
          </div>

          <!-- Rincian Metadata Pesanan -->
          <div class="order-meta-box">
            <div>
              <span style="font-size: 11px; color: var(--color-ash, #737780); display: block;">Nama Pemesan</span>
              <strong id="res-customer-name" style="font-size: 14px; color: var(--color-ink-black, #111111);">
                <?= htmlspecialchars($foundOrder['customer_name']) ?>
              </strong>
              <div id="res-customer-org" style="font-size: 12px; color: var(--color-ash, #737780);">
                <?= htmlspecialchars($foundOrder['organization']) ?>
              </div>
            </div>
            <div>
              <span style="font-size: 11px; color: var(--color-ash, #737780); display: block;">Produk Pre-Order</span>
              <strong id="res-product-name" style="font-size: 14px; color: var(--color-ink-black, #111111);">
                <?= htmlspecialchars($foundOrder['product_name']) ?>
              </strong>
              <div id="res-product-meta" style="font-size: 12px; color: var(--color-ash, #737780);">
                <?= htmlspecialchars($foundOrder['product_meta']) ?>
              </div>
            </div>
            <div>
              <span style="font-size: 11px; color: var(--color-ash, #737780); display: block;">Estimasi Pengiriman</span>
              <strong id="res-estimated-date" style="font-family: var(--font-space-mono); font-size: 14px; color: var(--color-electric-cobalt, #000aff);">
                <?= htmlspecialchars($foundOrder['estimated_delivery']) ?>
              </strong>
              <div id="res-destination" style="font-size: 12px; color: var(--color-ash, #737780);">
                <?= htmlspecialchars($foundOrder['destination']) ?>
              </div>
            </div>
          </div>

          <!-- TIMELINE STATUS PENGIRIMAN & PRODUKSI -->
          <h3 style="font-size: 16px; font-weight: 700; color: var(--color-ink-black, #111111); margin-bottom: 24px; letter-spacing: -0.04em;">
            Riwayat &amp; Timeline Pengerjaan PO:
          </h3>

          <div id="timeline-steps-list" class="timeline">
            <?php foreach ($foundOrder['timeline'] as $idx => $step): 
              $statusClass = 'done';
              if (!empty($step['current'])) {
                  $statusClass = 'current';
              } elseif (!empty($step['pending'])) {
                  $statusClass = 'pending';
              }
            ?>
              <div class="timeline-item <?= $statusClass ?>">
                <div class="timeline-dot">
                  <?php if (!empty($step['current'])): ?>
                    <span style="width: 6px; height: 6px; background: currentColor; border-radius: 50%; display: block;"></span>
                  <?php elseif (!empty($step['pending'])): ?>
                    <?= $idx + 1 ?>
                  <?php else: ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  <?php endif; ?>
                </div>
                <div class="timeline-content">
                  <h4 class="timeline-title"><?= htmlspecialchars($step['title']) ?></h4>
                  <p class="timeline-desc"><?= htmlspecialchars($step['desc']) ?></p>
                  <span class="timeline-time"><?= htmlspecialchars($step['time']) ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top: 32px; padding: 16px; background: var(--surface-plaster, #ecedee); border-radius: var(--radius-cards, 10px); font-size: 13px; color: var(--color-ash, #737780); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <span>Ada pertanyaan atau perubahan bordir nama?</span>
            <a href="<?= htmlspecialchars(formatWhatsAppUrl(null, 'Halo CS Fastender, saya ingin menanyakan status pesanan PO saya.')) ?>" target="_blank" style="color: var(--color-electric-cobalt, #000aff); font-weight: 700;">
              Chat CS Fastender di WhatsApp &rarr;
            </a>
          </div>

        </div>
      <?php elseif ($hasSearched && !$foundOrder): ?>
        <!-- ERROR STATE (RESI TIDAK DITEMUKAN DI DATABASE) -->
        <div id="track-not-found" style="display: block; text-align: center; padding: 48px 24px; background: var(--surface-plaster, #ecedee); border-radius: var(--radius-cards, 28px); border: 1px dashed var(--color-slate, #a1a4aa); margin-top: 24px;">
          <div style="display: flex; justify-content: center; margin-bottom: 12px;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#d9383a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
          </div>
          <h3 style="font-size: 18px; color: var(--color-ink-black, #111111); font-weight: 700;">Nomor Resi Tidak Ditemukan</h3>
          <p style="font-size: 14px; color: var(--color-ash, #737780); margin: 8px auto 0; max-width: 480px; line-height: 1.6;">
            Nomor resi tidak ditemukan. Pastikan nomor resi PO yang dimasukkan sudah benar.
          </p>
        </div>
      <?php else: ?>
        <!-- INFO STATE (FORM KOSONG, BELUM SEARCH) -->
        <div id="track-empty-state" style="display: block; text-align: center; padding: 48px 24px; background: var(--surface-plaster, #ecedee); border-radius: var(--radius-cards, 28px); border: 1px dashed var(--color-slate, #a1a4aa); margin-top: 24px;">
          <div style="display: flex; justify-content: center; margin-bottom: 12px;">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
          </div>
          <h3 style="font-size: 18px; color: var(--color-ink-black, #111111); font-weight: 700;">Lacak Pesanan Anda</h3>
          <p style="font-size: 14px; color: var(--color-ash, #737780); margin: 8px auto 0; max-width: 480px; line-height: 1.6;">
            Masukkan nomor resi pesanan PO Anda di atas untuk melihat status terkini, timeline produksi, dan estimasi pengiriman.
          </p>
        </div>
      <?php endif; ?>

    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
