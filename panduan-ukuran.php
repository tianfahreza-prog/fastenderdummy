<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Panduan Ukuran (panduan-ukuran.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$fromId = isset($_GET['from']) ? (int)$_GET['from'] : 0;
$product = null;
if ($fromId > 0) {
    $product = getProductById($fromId);
}

$pageTitle = 'Panduan Ukuran - Fastender';
$activePage = 'katalog';

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     KONTEN UTAMA PANDUAN UKURAN
     ========================================== -->
<section class="size-guide-section">
  <div class="container">
    
    <!-- Header Section -->
    <div class="size-guide-header">
      <h1 class="size-guide-title">Panduan Ukuran</h1>
    </div>

    <!-- 2 Kolom: Tabel Size Chart (Kiri) & Ilustrasi Cara Ukur (Kanan) -->
    <div class="size-guide-grid">

      <!-- KOLOM KIRI: TABEL UKURAN -->
      <div class="size-guide-card">
        <div class="size-card-head">
          <div class="size-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="3" y1="9" x2="21" y2="9"></line>
              <line x1="9" y1="21" x2="9" y2="9"></line>
            </svg>
          </div>
          <h2 class="size-card-title">Tabel Ukuran (cm)</h2>
        </div>

        <div class="size-table-container">
          <table class="size-table">
            <thead>
              <tr>
                <th>Size</th>
                <th>Lebar Dada (cm)</th>
                <th>Panjang Baju (cm)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span class="size-pill-tag">S</span></td>
                <td><strong>48 cm</strong></td>
                <td><strong>65 cm</strong></td>
              </tr>
              <tr>
                <td><span class="size-pill-tag">M</span></td>
                <td><strong>51 cm</strong></td>
                <td><strong>68 cm</strong></td>
              </tr>
              <tr>
                <td><span class="size-pill-tag">L</span></td>
                <td><strong>54 cm</strong></td>
                <td><strong>71 cm</strong></td>
              </tr>
              <tr>
                <td><span class="size-pill-tag">XL</span></td>
                <td><strong>57 cm</strong></td>
                <td><strong>74 cm</strong></td>
              </tr>
              <tr>
                <td><span class="size-pill-tag">XXL</span></td>
                <td><strong>60 cm</strong></td>
                <td><strong>77 cm</strong></td>
              </tr>
              <tr>
                <td><span class="size-pill-tag">3XL</span></td>
                <td><strong>63 cm</strong></td>
                <td><strong>80 cm</strong></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Catatan Toleransi Jahit -->
        <div class="size-note-box">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 2px;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          <div>
            Toleransi ukuran jahit <strong>± 1 - 2 cm</strong>. Jika berada di antara dua ukuran, disarankan memilih <strong>1 ukuran lebih besar (upsize)</strong>.
          </div>
        </div>

      </div>

      <!-- KOLOM KANAN: ILUSTRASI SKETSA & CARA MENGUKUR -->
      <div class="size-guide-card">
        <div class="size-card-head">
          <div class="size-card-icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"></path>
            </svg>
          </div>
          <h2 class="size-card-title">Cara Mengukur</h2>
        </div>

        <!-- Ilustrasi Sketsa Baju SVG Interaktif & Bersih -->
        <div class="size-sketch-wrapper">
          <svg viewBox="0 0 340 290" class="size-sketch-svg" xmlns="http://www.w3.org/2000/svg">
            <defs>
              <marker id="arrow-blue" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                <path d="M 0 1.5 L 8 5 L 0 8.5 z" fill="#619ae6"/>
              </marker>
              <marker id="arrow-dark" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                <path d="M 0 1.5 L 8 5 L 0 8.5 z" fill="#111111"/>
              </marker>
            </defs>

            <!-- Sketsa Siluet Baju -->
            <path d="M 125,50 
                     Q 170,75 215,50 
                     L 280,82 
                     L 298,126 
                     L 255,145 
                     L 245,126 
                     L 245,245 
                     Q 170,250 95,245 
                     L 95,126 
                     L 85,145 
                     L 42,126 
                     L 60,82 Z" 
                  fill="#ffffff" 
                  stroke="#cfd5dd" 
                  stroke-width="2.5" 
                  stroke-linejoin="round"
                  stroke-linecap="round"/>

            <!-- Kerah Leher Depan -->
            <path d="M 125,50 Q 170,82 215,50" fill="none" stroke="#cfd5dd" stroke-width="2.5"/>
            <!-- Jahitan Lengan Kiri & Kanan -->
            <path d="M 95,126 L 80,86" fill="none" stroke="#e2e6ec" stroke-width="1.8" stroke-dasharray="3,3"/>
            <path d="M 245,126 L 260,86" fill="none" stroke="#e2e6ec" stroke-width="1.8" stroke-dasharray="3,3"/>

            <!-- GARIS UKUR A: LEBAR DADA (Horizontal Ketiak ke Ketiak) -->
            <line x1="102" y1="135" x2="238" y2="135" stroke="#619ae6" stroke-width="2.5" stroke-dasharray="4,3" marker-start="url(#arrow-blue)" marker-end="url(#arrow-blue)"/>
            <!-- Badge Indikator Garis A -->
            <g transform="translate(170, 135)">
              <rect x="-56" y="-13" width="112" height="26" rx="13" fill="#619ae6" filter="drop-shadow(0px 2px 6px rgba(97,154,230,0.35))"/>
              <text x="0" y="4" text-anchor="middle" fill="#ffffff" font-family="'Montserrat', sans-serif" font-size="10.5" font-weight="700">A. Lebar Dada</text>
            </g>

            <!-- GARIS UKUR B: PANJANG BAJU (Vertikal Bahu ke Ujung Bawah) -->
            <line x1="68" y1="68" x2="68" y2="242" stroke="#111111" stroke-width="2.5" stroke-dasharray="4,3" marker-start="url(#arrow-dark)" marker-end="url(#arrow-dark)"/>
            <!-- Garis Proyeksi Bahu & Bawah -->
            <line x1="68" y1="68" x2="95" y2="68" stroke="#a1a4aa" stroke-width="1.2" stroke-dasharray="2,2"/>
            <line x1="68" y1="242" x2="95" y2="242" stroke="#a1a4aa" stroke-width="1.2" stroke-dasharray="2,2"/>
            <!-- Badge Indikator Garis B -->
            <g transform="translate(68, 155)">
              <rect x="-14" y="-38" width="28" height="76" rx="14" fill="#111111"/>
              <text x="0" y="-18" text-anchor="middle" fill="#ffffff" font-family="'Montserrat', sans-serif" font-size="10" font-weight="800">B</text>
              <text x="0" y="0" text-anchor="middle" fill="#ffffff" font-family="'Montserrat', sans-serif" font-size="9" font-weight="600" transform="rotate(-90 0 0)">PANJANG</text>
            </g>
          </svg>
        </div>

        <!-- Poin Panduan Mengukur -->
        <div class="measurement-steps">
          <div class="measurement-step">
            <span class="step-num-pill">A</span>
            <div>
              <h4 class="step-title">Lebar Dada</h4>
              <p class="step-desc">Diukur dari ujung jahitan ketiak kanan ke ketiak kiri.</p>
            </div>
          </div>

          <div class="measurement-step">
            <span class="step-num-pill" style="background: #111111;">B</span>
            <div>
              <h4 class="step-title">Panjang Baju</h4>
              <p class="step-desc">Diukur dari titik bahu atas ke ujung bawah baju.</p>
            </div>
          </div>
        </div>

      </div>

    </div>

    <!-- Tombol Aksi Navigasi (Kembali ke Produk / Kembali ke Katalog) -->
    <div class="size-guide-actions-bar">
      <?php if ($product): ?>
        <a href="detail.php?id=<?= (int)$product['id'] ?>" class="btn btn-accent btn-lg" style="padding: 14px 32px; border-radius: 28px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          Kembali ke <?= htmlspecialchars($product['name']) ?>
        </a>
        <a href="katalog.php" class="btn btn-outline-accent btn-lg" style="padding: 14px 28px; border-radius: 28px;">
          Katalog
        </a>
      <?php else: ?>
        <a href="katalog.php" class="btn btn-accent btn-lg" style="padding: 14px 36px; border-radius: 28px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
          </svg>
          Kembali ke Katalog
        </a>
        <a href="index.php" class="btn btn-outline-accent btn-lg" style="padding: 14px 28px; border-radius: 28px;">
          Beranda
        </a>
      <?php endif; ?>
    </div>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
