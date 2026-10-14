<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Katalog Produk (katalog.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Katalog - Fastender';
$activePage = 'katalog';

$rawCategoryParam = $_GET['kategori'] ?? ($_GET['cat'] ?? '');
$selectedCat = sanitize($rawCategoryParam);
if (strtolower($selectedCat) === 'all') {
    $selectedCat = '';
}
$initialSearch = sanitize($_GET['search'] ?? ($_GET['q'] ?? ''));

$sortOptions = [
    'popular' => 'Paling Populer',
    'price-low' => 'Harga: Rendah ke Tinggi',
    'price-high' => 'Harga: Tinggi ke Rendah',
    'quota' => 'Kuota Terbanyak',
];
$selectedSort = sanitize($_GET['sort'] ?? 'popular');
if (!array_key_exists($selectedSort, $sortOptions)) {
    $selectedSort = 'popular';
}

// Query filter via PHP & SQL (WHERE category = :kategori) serta daftar seluruh produk katalog
$filteredProducts = getProductsList($selectedCat);
$allProducts = getProductsList();

if ($selectedSort !== 'popular' && !empty($allProducts)) {
    usort($allProducts, function ($a, $b) use ($selectedSort) {
        $priceA = (int)($a['price'] ?? 0);
        $priceB = (int)($b['price'] ?? 0);
        $quotaA = (int)($a['quota_current'] ?? 0);
        $quotaB = (int)($b['quota_current'] ?? 0);
        if ($selectedSort === 'price-low') return $priceA <=> $priceB;
        if ($selectedSort === 'price-high') return $priceB <=> $priceA;
        if ($selectedSort === 'quota') return $quotaB <=> $quotaA;
        return ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0));
    });
}

$isPolstuffActive = (strcasecmp($selectedCat, 'POLSTUFF') === 0);
$isPolneedsActive = (strcasecmp($selectedCat, 'POLNEEDS') === 0);
$isAllActive = ($selectedCat === '');

// Opsi sub-kategori spesifik di dalam modal Filter Produk (tanpa POLSTUFF & POLNEEDS)
$categoryOptions = [
    'Workshirt' => 'Workshirt',
    'Jas almamater' => 'Jas almamater',
    'Jas Lab' => 'Jas Lab',
    'Kaos' => 'Kaos',
    'Keychain' => 'Keychain',
    'Totebag' => 'Totebag',
    'Sticker' => 'Sticker',
];

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<!-- ==========================================
     KATALOG SECTION (JUDUL + BAR KONTROL + POPUP FILTER + GRID PRODUK)
     ========================================== -->
<section class="katalog-section">
  <div class="katalog-container">
    <h1 class="katalog-page-title">Katalog</h1>
    
    <!-- 1 Baris Kontrol Rapi di Bawah Judul "Katalog" -->
    <div class="katalog-controls-bar">
      <!-- Sisi Kiri: Informasi Jumlah Produk + Tombol Kapsul Kecil "Filter" + Quick Category Nav -->
      <div class="controls-bar-left">
        <span class="product-count-badge">
          Menampilkan <strong id="product-count-text"><?= count($filteredProducts) ?></strong> produk
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
              <!-- Sub-Kategori Spesifik Produk -->
              <div class="filter-section-item">
                <label class="filter-section-label">Kategori</label>
                <div class="filter-radios-grid">
                  <label class="filter-radio-pill">
                    <input type="radio" name="category-filter" value="" <?= ($selectedCat === '' || $isPolstuffActive || $isPolneedsActive) ? 'checked' : '' ?>>
                    <span>Semua Kategori</span>
                  </label>
                  <?php foreach ($categoryOptions as $catVal => $catLabel): ?>
                    <label class="filter-radio-pill">
                      <input type="radio" name="category-filter" value="<?= htmlspecialchars($catVal) ?>" <?= (strcasecmp($selectedCat, $catVal) === 0) ? 'checked' : '' ?>>
                      <span><?= htmlspecialchars($catLabel) ?></span>
                    </label>
                  <?php endforeach; ?>
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

        <!-- Kelompok Tombol Navigasi Cepat Kategori (Sejajar dengan Tombol Filter, Posisi Tetap) -->
        <div class="katalog-quick-nav" id="katalogQuickNav" role="navigation" aria-label="Navigasi Cepat Kategori">
          <a href="katalog.php" class="btn-quick-cat-pill <?= $isAllActive ? 'active' : '' ?>" data-quick-cat="" <?= $isAllActive ? 'aria-current="page"' : '' ?>>
            Semua
          </a>
          <a href="katalog.php?kategori=POLSTUFF" class="btn-quick-cat-pill <?= $isPolstuffActive ? 'active' : '' ?>" data-quick-cat="POLSTUFF" <?= $isPolstuffActive ? 'aria-current="page"' : '' ?>>
            POLSTUFF
          </a>
          <a href="katalog.php?kategori=POLNEEDS" class="btn-quick-cat-pill <?= $isPolneedsActive ? 'active' : '' ?>" data-quick-cat="POLNEEDS" <?= $isPolneedsActive ? 'aria-current="page"' : '' ?>>
            POLNEEDS
          </a>
        </div>

        <!-- Chips Filter Aktif -->
        <div id="active-filter-chips" class="active-chips-list"></div>
      </div>

      <!-- Sisi Kanan: Custom Modern Dropdown "Urutkan" -->
      <div class="controls-bar-right">
        <div class="sort-pill-box">
          <span class="sort-label" id="sort-dropdown-label">Urutkan:</span>
          <div class="custom-sort-dropdown" id="customSortDropdown">
            <button 
              type="button" 
              class="custom-sort-trigger" 
              id="customSortTrigger" 
              aria-haspopup="listbox" 
              aria-expanded="false" 
              aria-labelledby="sort-dropdown-label customSortLabel"
            >
              <span class="custom-sort-trigger-text" id="customSortLabel"><?= htmlspecialchars($sortOptions[$selectedSort]) ?></span>
              <svg class="custom-sort-chevron" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
              </svg>
            </button>

            <div class="custom-sort-menu" id="customSortMenu" role="listbox" aria-labelledby="sort-dropdown-label">
              <?php foreach ($sortOptions as $sortVal => $sortLabel): 
                $isSortActive = ($selectedSort === $sortVal);
              ?>
                <button 
                  type="button" 
                  class="custom-sort-option <?= $isSortActive ? 'active' : '' ?>" 
                  role="option" 
                  aria-selected="<?= $isSortActive ? 'true' : 'false' ?>" 
                  data-sort-value="<?= htmlspecialchars($sortVal) ?>"
                >
                  <span><?= htmlspecialchars($sortLabel) ?></span>
                  <svg class="custom-sort-check" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Grid Card Produk (Responsif Presisi repeat(auto-fill, minmax(260px, 1fr)), Rounded 28px) -->
    <div id="catalog-products-grid" class="product-grid">
      <?php foreach ($allProducts as $prod): 
        $quotaInfo = getProductQuotaInfo($prod);
        $isQuotaFull = $quotaInfo['is_full'];
        $badgeText = $isQuotaFull ? 'Kuota Penuh' : $prod['badge'];
        $badgeClass = $isQuotaFull ? 'badge-danger' : '';
        $batchLabel = explode(' ', $prod['batch'])[0];
        $firstImg = !empty($prod['images']) ? $prod['images'][0] : 'assets/images/gambar1.jpg';
        $prodCatRaw = trim((string)($prod['category'] ?? ($prod['category_name'] ?? '')));
        $prodParentRaw = trim((string)($prod['parent_kategori'] ?? resolveParentCategory($prodCatRaw)));
        $isInitialMatch = isProductMatchingCategory($prod, $selectedCat);
      ?>
        <article 
          class="product-card <?= $isQuotaFull ? 'product-quota-full' : '' ?>" 
          data-id="<?= $prod['id'] ?>"
          data-name="<?= htmlspecialchars(strtolower($prod['name'])) ?>"
          data-price="<?= (int)$prod['price'] ?>"
          data-quota="<?= (int)$quotaInfo['current'] ?>"
          data-category="<?= htmlspecialchars(strtolower($prodCatRaw)) ?>"
          data-parent-category="<?= htmlspecialchars(strtolower($prodParentRaw)) ?>"
          style="<?= $isInitialMatch ? '' : 'display: none;' ?>"
        >
          <div class="product-thumb-wrapper">
            <span class="product-card-badge" style="<?= $isQuotaFull ? 'background: #ef4444; color: #ffffff;' : '' ?>"><?= htmlspecialchars($badgeText) ?></span>
            <span class="product-batch-tag"><?= htmlspecialchars($batchLabel) ?></span>
            <img src="<?= htmlspecialchars($firstImg) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" class="product-thumb" loading="lazy">
          </div>
          <div class="product-content">
            <span class="product-category"><?= htmlspecialchars($prod['category_name'] ?? $prod['category']) ?></span>
            <h3 class="product-title" title="<?= htmlspecialchars($prod['name']) ?>"><?= htmlspecialchars($prod['name']) ?></h3>
            
            <?php /* Milestone & progress bar kuota disembunyikan dari sisi publik sesuai permintaan */ ?>

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
          <line x1="21" cy="21" x2="16.65" y2="16.65"></line>
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
  // Logika Interaksi Filter Popover, Quick Category Pills, Live Search & Sortir Produk
  document.addEventListener("DOMContentLoaded", () => {
    const grid = document.getElementById("catalog-products-grid");
    const countText = document.getElementById("product-count-text");
    const emptyState = document.getElementById("empty-state");
    const categoryRadios = document.querySelectorAll('input[name="category-filter"]');
    const minPriceInput = document.getElementById("price-min");
    const maxPriceInput = document.getElementById("price-max");
    const applyFilterBtn = document.getElementById("apply-filter-btn");
    const resetBtn = document.getElementById("reset-filter-btn");
    const resetEmptyBtn = document.getElementById("reset-empty-btn");
    
    // Custom Sort Dropdown Elements
    const customSortDropdown = document.getElementById("customSortDropdown");
    const customSortTrigger = document.getElementById("customSortTrigger");
    const customSortLabel = document.getElementById("customSortLabel");
    const customSortMenu = document.getElementById("customSortMenu");
    const customSortOptions = document.querySelectorAll(".custom-sort-option");

    // Filter Popover Elements
    const filterAnchor = document.getElementById("filterDropdownAnchor");
    const filterBtn = document.getElementById("btnFilterPill");
    const filterCard = document.getElementById("filterPopoverCard");
    const closeFilterBtn = document.getElementById("close-filter-btn");
    const filterDot = document.getElementById("filterDotIndicator");
    const activeChipsContainer = document.getElementById("active-filter-chips");

    const quickNavContainer = document.getElementById("katalogQuickNav");
    let currentSearchTerm = "<?= addslashes(strtolower($initialSearch)) ?>";
    let currentParentCategory = "<?= ($isPolstuffActive || $isPolneedsActive) ? addslashes($selectedCat) : '' ?>";
    let currentSortValue = "<?= addslashes($selectedSort) ?>";

    // Pengelompokan sub-kategori sesuai kategori induk POLSTUFF & POLNEEDS
    const polstuffSubCategories = ["kaos", "keychain", "totebag", "sticker", "polstuff"];
    const polneedsSubCategories = ["workshirt", "jas almamater", "jas lab", "polneeds"];

    function isCategoryMatch(cardCat, cardParentCat, targetCat) {
      const target = (targetCat || "").toLowerCase().trim();
      if (target === "" || target === "all") return true;
      const cCat = (cardCat || "").toLowerCase().trim();
      const cParent = (cardParentCat || "").toLowerCase().trim();

      if (target === "polstuff") {
        return polstuffSubCategories.includes(cCat) || cParent === "polstuff";
      }
      if (target === "polneeds") {
        return polneedsSubCategories.includes(cCat) || cParent === "polneeds";
      }
      return cCat === target;
    }

    function syncQuickNavState(activeCat) {
      if (!quickNavContainer) return;
      const pills = quickNavContainer.querySelectorAll(".btn-quick-cat-pill");
      const upperCat = (activeCat || "").toUpperCase();
      pills.forEach(pill => {
        const pillCat = (pill.getAttribute("data-quick-cat") || "").toUpperCase();
        if (pillCat === upperCat) {
          pill.classList.add("active");
          pill.setAttribute("aria-current", "page");
        } else {
          pill.classList.remove("active");
          pill.removeAttribute("aria-current");
        }
      });
    }

    // Interaksi klik tombol cepat ("Semua", "POLSTUFF", "POLNEEDS") tanpa layout shift / reload lompat
    if (quickNavContainer) {
      const quickPills = quickNavContainer.querySelectorAll(".btn-quick-cat-pill");
      quickPills.forEach(pill => {
        pill.addEventListener("click", (e) => {
          if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
          e.preventDefault();
          const targetQuickCat = (pill.getAttribute("data-quick-cat") || "").trim();
          currentParentCategory = targetQuickCat;
          const allRadio = document.querySelector('input[name="category-filter"][value=""]');
          if (allRadio) allRadio.checked = true;
          syncQuickNavState(targetQuickCat);
          runCatalogFilter(true);
        });
      });
    }

    // Helper tutup custom sort dropdown
    function closeCustomSortMenu() {
      if (!customSortDropdown || !customSortTrigger) return;
      customSortDropdown.classList.remove("open");
      customSortTrigger.classList.remove("active");
      customSortTrigger.setAttribute("aria-expanded", "false");
    }

    // Toggle Popover Filter
    if (filterBtn && filterCard) {
      filterBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        closeCustomSortMenu();
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

    // Klik di luar popover filter & custom sort dropdown untuk menutup
    document.addEventListener("click", (e) => {
      if (filterAnchor && !filterAnchor.contains(e.target)) {
        if (filterCard) filterCard.style.display = "none";
        if (filterBtn) {
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        }
      }
      if (customSortDropdown && !customSortDropdown.contains(e.target)) {
        closeCustomSortMenu();
      }
    });

    // Tutup dropdown saat tombol Escape ditekan
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        closeCustomSortMenu();
      }
    });

    // Mencegah klik di dalam filter popover menutup popover
    if (filterCard) {
      filterCard.addEventListener("click", (e) => {
        e.stopPropagation();
      });
    }

    // Fungsi Global Live Search (Sinkronisasi dengan Navbar Inline Search)
    window.triggerCatalogSearch = function(query) {
      currentSearchTerm = (query || "").toLowerCase().trim();
      runCatalogFilter(true);
    };

    function getEffectiveCategory() {
      const selectedRadio = document.querySelector('input[name="category-filter"]:checked');
      const radioCat = selectedRadio ? selectedRadio.value.trim() : "";
      return radioCat !== "" ? radioCat : currentParentCategory;
    }

    function syncUrlQueryParam(selectedCat) {
      if (!window.history || !window.history.replaceState) return;
      const url = new URL(window.location.href);
      url.searchParams.delete("cat");
      url.searchParams.delete("q");
      if (selectedCat && selectedCat !== "") {
        url.searchParams.set("kategori", selectedCat);
      } else {
        url.searchParams.delete("kategori");
      }
      if (currentSearchTerm && currentSearchTerm !== "") {
        url.searchParams.set("search", currentSearchTerm);
      } else {
        url.searchParams.delete("search");
      }
      if (currentSortValue && currentSortValue !== "popular") {
        url.searchParams.set("sort", currentSortValue);
      } else {
        url.searchParams.delete("sort");
      }
      window.history.replaceState({}, "", url.toString());
    }

    function runCatalogFilter(syncUrl = false) {
      const cards = grid.querySelectorAll(".product-card");
      if (cards.length === 0) {
        if (countText) countText.textContent = "0";
        if (emptyState) emptyState.style.display = "block";
        return;
      }

      const selectedRadio = document.querySelector('input[name="category-filter"]:checked');
      const radioCat = selectedRadio ? selectedRadio.value.trim() : "";

      if (radioCat !== "") {
        currentParentCategory = "";
      }

      const effectiveCat = radioCat !== "" ? radioCat : currentParentCategory;
      const minP = Number(minPriceInput.value) || 0;
      const maxP = Number(maxPriceInput.value) || Infinity;

      if (syncUrl) {
        syncUrlQueryParam(effectiveCat);
        syncQuickNavState(effectiveCat);
      }

      let visibleCount = 0;
      let isFilterActive = false;

      if (radioCat !== "" || minP > 0 || maxP < 300000 || currentSearchTerm !== "") {
        isFilterActive = true;
      }

      if (filterDot) {
        filterDot.style.display = isFilterActive ? "inline-block" : "none";
      }

      renderFilterChips(radioCat, minP, maxP, currentSearchTerm);

      cards.forEach(card => {
        const title = (card.getAttribute("data-name") || "").toLowerCase();
        const cat = (card.getAttribute("data-category") || "").toLowerCase().trim();
        const parentCat = (card.getAttribute("data-parent-category") || "").toLowerCase().trim();
        const price = Number(card.getAttribute("data-price")) || 0;

        let match = true;

        if (currentSearchTerm && !title.includes(currentSearchTerm) && !cat.includes(currentSearchTerm)) {
          match = false;
        }

        if (!isCategoryMatch(cat, parentCat, effectiveCat)) {
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

      if (cat !== "") {
        const chip = createChip(`Kategori: ${cat}`, () => {
          const allRadio = document.querySelector('input[name="category-filter"][value=""]');
          if (allRadio) allRadio.checked = true;
          currentParentCategory = "";
          runCatalogFilter(true);
        });
        activeChipsContainer.appendChild(chip);
      }

      if (minP > 0 || maxP < 300000) {
        const chip = createChip(`Rp ${minP.toLocaleString('id-ID')} - ${maxP.toLocaleString('id-ID')}`, () => {
          minPriceInput.value = "0";
          maxPriceInput.value = "300000";
          runCatalogFilter(true);
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

    // Fungsi Sortir Kartu Produk
    function sortCatalogCards(val) {
      if (!grid) return;
      const cards = Array.from(grid.querySelectorAll(".product-card"));
      if (cards.length === 0) return;

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
    }

    // Interaksi Custom Sort Dropdown
    if (customSortDropdown && customSortTrigger && customSortMenu) {
      customSortTrigger.addEventListener("click", (e) => {
        e.stopPropagation();
        // Tutup filter popover jika sedang terbuka
        if (filterCard) filterCard.style.display = "none";
        if (filterBtn) {
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        }

        const isOpen = customSortDropdown.classList.contains("open");
        if (isOpen) {
          closeCustomSortMenu();
        } else {
          customSortDropdown.classList.add("open");
          customSortTrigger.classList.add("active");
          customSortTrigger.setAttribute("aria-expanded", "true");
        }
      });

      customSortOptions.forEach(optionBtn => {
        optionBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          const val = optionBtn.getAttribute("data-value") || "popular";
          const label = optionBtn.getAttribute("data-label") || optionBtn.textContent.trim();

          currentSortValue = val;

          // Update label tombol trigger
          if (customSortLabel) {
            customSortLabel.textContent = label;
          }

          // Tandai item aktif & aria-selected
          customSortOptions.forEach(btn => {
            const isSelected = btn.getAttribute("data-value") === val;
            btn.classList.toggle("active", isSelected);
            btn.setAttribute("aria-selected", isSelected ? "true" : "false");
          });

          // Sortir kartu produk & sinkronkan URL tanpa merusak filter kategori
          sortCatalogCards(val);
          syncUrlQueryParam(getEffectiveCategory());
          closeCustomSortMenu();
        });
      });
    }

    categoryRadios.forEach(r => {
      r.addEventListener("change", () => {
        if (r.value.trim() !== "") {
          currentParentCategory = "";
        }
        runCatalogFilter(true);
      });
    });

    if (applyFilterBtn) {
      applyFilterBtn.addEventListener("click", () => {
        runCatalogFilter(true);
        if (filterCard) filterCard.style.display = "none";
        if (filterBtn) {
          filterBtn.classList.remove("active");
          filterBtn.setAttribute("aria-expanded", "false");
        }
      });
    }

    function resetAllFilters() {
      const defaultRadio = document.querySelector('input[name="category-filter"][value=""]');
      if (defaultRadio) defaultRadio.checked = true;
      currentParentCategory = "";
      if (minPriceInput) minPriceInput.value = "0";
      if (maxPriceInput) maxPriceInput.value = "300000";
      currentSearchTerm = "";
      const navInput = document.getElementById("navbarSearchInput");
      if (navInput) navInput.value = "";
      runCatalogFilter(true);
      if (filterCard) filterCard.style.display = "none";
      if (filterBtn) {
        filterBtn.classList.remove("active");
        filterBtn.setAttribute("aria-expanded", "false");
      }
    }

    if (resetBtn) resetBtn.addEventListener("click", resetAllFilters);
    if (resetEmptyBtn) resetEmptyBtn.addEventListener("click", resetAllFilters);

    // Initial Filter Run
    runCatalogFilter(false);
  });
</script>
