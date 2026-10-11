<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Katalog Produk (katalog.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Katalog - Fastender';
$activePage = 'katalog';

$allProducts = getProductsList();
$selectedCat = sanitize($_GET['cat'] ?? 'all');
$initialSearch = sanitize($_GET['q'] ?? '');

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     PAGE HEADER / BREADCRUMB & JUDUL
     ========================================== -->
<div class="katalog-header-banner">
  <div class="katalog-container">
    <div class="katalog-breadcrumb">
      <a href="index.php">Beranda</a> &nbsp;/&nbsp; <span>Katalog</span>
    </div>
    <h1 class="katalog-page-title">Katalog</h1>
  </div>
</div>

<!-- ==========================================
     KATALOG SECTION (BAR KONTROL + POPUP FILTER + GRID PRODUK)
     ========================================== -->
<section class="katalog-section">
  <div class="katalog-container">
    
    <!-- 1 Baris Kontrol Rapi di Bawah Judul "Katalog" -->
    <div class="katalog-controls-bar">
      <!-- Sisi Kiri: Informasi Jumlah Produk + Tombol Kapsul Kecil "Filter" -->
      <div class="controls-bar-left">
        <span class="product-count-badge">
          Menampilkan <strong id="product-count-text"><?= count($allProducts) ?></strong> produk
        </span>

        <!-- Tombol Kapsul Filter & Popover Card -->
        <div class="filter-dropdown-anchor" id="filterDropdownAnchor">
          <button type="button" class="btn-filter-pill" id="btnFilterPill" aria-haspopup="true" aria-expanded="false">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="4" y1="21" x2="4" y2="14"></line>
              <line x1="4" y1="10" x2="4" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12" y2="3"></line>
              <line x1="20" y1="21" x2="20" y2="16"></line>
              <line x1="20" y1="12" x2="20" y2="3"></line>
              <line x1="1" y1="14" x2="7" y2="14"></line>
              <line x1="9" y1="8" x2="15" y2="8"></line>
              <line x1="17" y1="16" x2="23" y2="16"></line>
            </svg>
            <span>Filter</span>
            <span class="filter-dot-indicator" id="filterDotIndicator" style="display: none;"></span>
          </button>

          <!-- Popover Modal Filter (Melayang Absolute & Z-Index Tinggi, Hidden by Default) -->
          <div class="filter-popover-card" id="filterPopoverCard" style="display: none;">
            <div class="filter-popover-header">
              <div class="filter-popover-title-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#619ae6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <h4 class="filter-popover-title">Filter Produk</h4>
              </div>
              <div class="filter-popover-actions">
                <button type="button" id="reset-filter-btn" class="filter-reset-link">Reset</button>
                <button type="button" id="close-filter-btn" class="filter-close-btn" aria-label="Tutup filter">&times;</button>
              </div>
            </div>

            <div class="filter-popover-body">
              <!-- Kategori Produk -->
              <div class="filter-section-item">
                <label class="filter-section-label">Kategori</label>
                <div class="filter-radios-grid">
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="all" <?= ($selectedCat === 'all' || empty($selectedCat)) ? 'checked' : '' ?>>
                    <span>Semua Kategori</span>
                  </label>
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="jaket" <?= ($selectedCat === 'jaket') ? 'checked' : '' ?>>
                    <span>Jaket &amp; Varsity</span>
                  </label>
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="pdh" <?= ($selectedCat === 'pdh') ? 'checked' : '' ?>>
                    <span>Kemeja PDH</span>
                  </label>
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="hoodie" <?= ($selectedCat === 'hoodie') ? 'checked' : '' ?>>
                    <span>Hoodie &amp; Sweatshirt</span>
                  </label>
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="kaos" <?= ($selectedCat === 'kaos') ? 'checked' : '' ?>>
                    <span>Kaos Angkatan</span>
                  </label>
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="merch" <?= ($selectedCat === 'merch') ? 'checked' : '' ?>>
                    <span>Merchandise</span>
                  </label>
                </div>
              </div>

              <!-- Rentang Harga -->
              <div class="filter-section-item">
                <label class="filter-section-label">Rentang Harga (Rp)</label>
                <div class="price-inputs-row">
                  <div class="price-input-field">
                    <span class="currency-tag">Rp</span>
                    <input type="number" id="price-min" placeholder="0" value="0">
                  </div>
                  <span class="price-sep">-</span>
                  <div class="price-input-field">
                    <span class="currency-tag">Rp</span>
                    <input type="number" id="price-max" placeholder="300000" value="300000">
                  </div>
                </div>
              </div>
            </div>

            <div class="filter-popover-footer">
              <button type="button" id="apply-filter-btn" class="btn btn-accent btn-sm btn-block">
                Terapkan Filter
              </button>
            </div>
          </div>
        </div>

        <!-- Chips Filter Aktif -->
        <div id="active-filter-chips" class="active-chips-list"></div>
      </div>

      <!-- Sisi Kanan: Dropdown "Urutkan: Paling Populer" (Rounded Pill) -->
      <div class="controls-bar-right">
        <div class="sort-pill-box">
          <label for="sort-select" class="sort-label">Urutkan:</label>
          <select id="sort-select" class="sort-select-pill">
            <option value="popular">Paling Populer</option>
            <option value="price-low">Harga: Rendah ke Tinggi</option>
            <option value="price-high">Harga: Tinggi ke Rendah</option>
            <option value="quota">Kuota Terbanyak</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Grid Card Produk (Responsif Presisi repeat(auto-fill, minmax(260px, 1fr)), Rounded 28px) -->
    <div id="catalog-products-grid" class="product-grid">
      <?php foreach ($allProducts as $prod): 
        $quotaPct = min(100, round(($prod['quota_current'] / max(1, $prod['quota_target'])) * 100));
        $batchLabel = explode(' ', $prod['batch'])[0];
        $firstImg = !empty($prod['images']) ? $prod['images'][0] : 'assets/images/gambar1.jpg';
      ?>
        <article 
          class="product-card" 
          data-id="<?= $prod['id'] ?>"
          data-name="<?= htmlspecialchars(strtolower($prod['name'])) ?>"
          data-price="<?= (int)$prod['price'] ?>"
          data-quota="<?= (int)$prod['quota_current'] ?>"
          data-category="<?= htmlspecialchars(strtolower($prod['category'] ?? '')) ?>"
        >
          <div class="product-thumb-wrapper">
            <span class="product-card-badge"><?= htmlspecialchars($prod['badge']) ?></span>
            <span class="product-batch-tag"><?= htmlspecialchars($batchLabel) ?></span>
            <img src="<?= htmlspecialchars($firstImg) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="product-thumb" loading="lazy">
          </div>
          <div class="product-content">
            <span class="product-category"><?= htmlspecialchars($prod['category_name'] ?? $prod['category']) ?></span>
            <h3 class="product-title" title="<?= htmlspecialchars($prod['name']) ?>"><?= htmlspecialchars($prod['name']) ?></h3>
            
            <div class="po-quota-box">
              <div class="po-quota-info">
                <span>Kuota Terisi: <strong><?= $prod['quota_current'] ?>/<?= $prod['quota_target'] ?> pcs</strong></span>
                <span><?= $quotaPct ?>%</span>
              </div>
              <div class="po-progress-bar">
                <div class="po-progress-fill" style="width: <?= $quotaPct ?>%;"></div>
              </div>
            </div>

            <div class="product-footer">
              <div class="product-price-row">
                <span class="product-price"><?= formatRupiah($prod['price']) ?></span>
                <span class="product-dp-label">DP: <?= formatRupiah($prod['dp_price']) ?></span>
              </div>
              <a href="detail.php?id=<?= $prod['id'] ?>" class="btn btn-outline-accent btn-block">
                Detail Produk
              </a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <!-- Empty State (Jika tidak ada produk cocok setelah filter) -->
    <div id="empty-state" class="catalog-empty-state" style="display: none;">
      <div class="empty-state-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#737780" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <h3 class="empty-state-title">Produk Tidak Ditemukan</h3>
      <button type="button" id="reset-empty-btn" class="btn btn-accent btn-sm" style="margin-top: 14px;">Reset Filter</button>
    </div>

  </div>
</section>

<!-- Footer & Scripts -->
<?php include __DIR__ . '/includes/footer.php'; ?>
<script>
  // Logika Interaksi Filter Popover, Live Search & Sortir Produk
  document.addEventListener("DOMContentLoaded", () => {
    const grid = document.getElementById("catalog-products-grid");
    const countText = document.getElementById("product-count-text");
    const emptyState = document.getElementById("empty-state");
    const categoryRadios = document.querySelectorAll('input[name="category-filter"]');
    const minPriceInput = document.getElementById("price-min");
    const maxPriceInput = document.getElementById("price-max");
    const applyFilterBtn = document.getElementById("apply-filter-btn");
    const sortSelect = document.getElementById("sort-select");
    const resetBtn = document.getElementById("reset-filter-btn");
    const resetEmptyBtn = document.getElementById("reset-empty-btn");
    
    // Filter Popover Elements
    const filterAnchor = document.getElementById("filterDropdownAnchor");
    const filterBtn = document.getElementById("btnFilterPill");
    const filterCard = document.getElementById("filterPopoverCard");
    const closeFilterBtn = document.getElementById("close-filter-btn");
    const filterDot = document.getElementById("filterDotIndicator");
    const activeChipsContainer = document.getElementById("active-filter-chips");

    let currentSearchTerm = "<?= addslashes(strtolower($initialSearch)) ?>";

    // Toggle Popover Filter
    if (filterBtn && filterCard) {
      filterBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        const isOpen = filterCard.style.display === "block";
        if (isOpen) {
          filterCard.style.display = "none";
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        } else {
          filterCard.style.display = "block";
          filterBtn.classList.add("active");
          filterBtn.setAttribute("aria-expanded", "true");
        }
      });
    }

    if (closeFilterBtn && filterCard) {
      closeFilterBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        filterCard.style.display = "none";
        if (filterBtn) {
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        }
      });
    }

    // Klik di luar popover filter untuk menutup
    document.addEventListener("click", (e) => {
      if (filterAnchor && !filterAnchor.contains(e.target)) {
        if (filterCard) filterCard.style.display = "none";
        if (filterBtn) {
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        }
      }
    });

    // Mencegah klik di dalam filter popover menutup popover
    if (filterCard) {
      filterCard.addEventListener("click", (e) => {
        e.stopPropagation();
      });
    }

    // Fungsi Global Live Search (Sinkronisasi dengan Navbar Floating Search)
    window.triggerCatalogSearch = function(query) {
      currentSearchTerm = (query || "").toLowerCase().trim();
      runCatalogFilter();
    };

    function runCatalogFilter() {
      const cards = grid.querySelectorAll(".product-card");
      if (cards.length === 0) return;

      const selectedRadio = document.querySelector('input[name="category-filter"]:checked');
      const selectedCat = selectedRadio ? selectedRadio.value : "all";
      const minP = Number(minPriceInput.value) || 0;
      const maxP = Number(maxPriceInput.value) || Infinity;

      let visibleCount = 0;
      let isFilterActive = false;

      if (selectedCat !== 'all' || minP > 0 || maxP < 300000 || currentSearchTerm !== "") {
        isFilterActive = true;
      }

      if (filterDot) {
        filterDot.style.display = isFilterActive ? "inline-block" : "none";
      }

      renderFilterChips(selectedCat, minP, maxP, currentSearchTerm);

      cards.forEach(card => {
        const title = card.getAttribute("data-name") || "";
        const cat = card.getAttribute("data-category") || "";
        const price = Number(card.getAttribute("data-price")) || 0;

        let match = true;

        if (currentSearchTerm && !title.includes(currentSearchTerm) && !cat.includes(currentSearchTerm)) {
          match = false;
        }

        if (selectedCat !== "all" && cat !== selectedCat && !cat.includes(selectedCat)) {
          match = false;
        }

        if (price < minP || price > maxP) {
          match = false;
        }

        if (match) {
          card.style.display = "flex";
          visibleCount++;
        } else {
          card.style.display = "none";
        }
      });

      if (countText) countText.textContent = visibleCount;
      if (emptyState) emptyState.style.display = (visibleCount === 0) ? "block" : "none";
    }

    function renderFilterChips(cat, minP, maxP, q) {
      if (!activeChipsContainer) return;
      activeChipsContainer.innerHTML = "";

      if (cat !== 'all') {
        const chip = createChip(`Kategori: ${cat.toUpperCase()}`, () => {
          const allRadio = document.querySelector('input[name="category-filter"][value="all"]');
          if (allRadio) allRadio.checked = true;
          runCatalogFilter();
        });
        activeChipsContainer.appendChild(chip);
      }

      if (minP > 0 || maxP < 300000) {
        const chip = createChip(`Rp ${minP.toLocaleString('id-ID')} - ${maxP.toLocaleString('id-ID')}`, () => {
          minPriceInput.value = "0";
          maxPriceInput.value = "300000";
          runCatalogFilter();
        });
        activeChipsContainer.appendChild(chip);
      }

      if (q) {
        const chip = createChip(`"${q}"`, () => {
          window.triggerCatalogSearch('');
          const navInput = document.getElementById("navbarSearchInput");
          if (navInput) navInput.value = "";
        });
        activeChipsContainer.appendChild(chip);
      }
    }

    function createChip(label, onRemove) {
      const span = document.createElement("span");
      span.className = "filter-active-chip";
      span.innerHTML = `${label} <button type="button" aria-label="Hapus filter">&times;</button>`;
      span.querySelector("button").addEventListener("click", onRemove);
      return span;
    }

    // Sort Handler
    if (sortSelect) {
      sortSelect.addEventListener("change", () => {
        const val = sortSelect.value;
        const cards = Array.from(grid.querySelectorAll(".product-card"));

        cards.sort((a, b) => {
          const priceA = Number(a.getAttribute("data-price")) || 0;
          const priceB = Number(b.getAttribute("data-price")) || 0;
          const quotaA = Number(a.getAttribute("data-quota")) || 0;
          const quotaB = Number(b.getAttribute("data-quota")) || 0;

          if (val === "price-low") return priceA - priceB;
          if (val === "price-high") return priceB - priceA;
          if (val === "quota") return quotaB - quotaA;
          return Number(b.getAttribute("data-id")) - Number(a.getAttribute("data-id"));
        });

        cards.forEach(c => grid.appendChild(c));
      });
    }

    categoryRadios.forEach(r => {
      r.addEventListener("change", () => {
        runCatalogFilter();
      });
    });

    if (applyFilterBtn) {
      applyFilterBtn.addEventListener("click", () => {
        runCatalogFilter();
        if (filterCard) filterCard.style.display = "none";
        if (filterBtn) {
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        }
      });
    }

    function resetAllFilters() {
      const defaultRadio = document.querySelector('input[name="category-filter"][value="all"]');
      if (defaultRadio) defaultRadio.checked = true;
      if (minPriceInput) minPriceInput.value = "0";
      if (maxPriceInput) maxPriceInput.value = "300000";
      currentSearchTerm = "";
      const navInput = document.getElementById("navbarSearchInput");
      if (navInput) navInput.value = "";
      runCatalogFilter();
      if (filterCard) filterCard.style.display = "none";
      if (filterBtn) {
        filterBtn.classList.remove("active");
        filterBtn.setAttribute("aria-expanded", "false");
      }
    }

    if (resetBtn) resetBtn.addEventListener("click", resetAllFilters);
    if (resetEmptyBtn) resetEmptyBtn.addEventListener("click", resetAllFilters);

    // Initial Filter Run
    runCatalogFilter();
  });
</script>
