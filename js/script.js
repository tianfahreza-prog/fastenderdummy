/**
 * FASTENDER - PRE-ORDER E-COMMERCE PLATFORM
 * Core & Shared Script (script.js)
 * Vanilla JavaScript (No Framework)
 */

// ==========================================
// 1. DATA TEMPLATE PRODUK PRE-ORDER (SAMPLE/DEMO TEMPLATES)
// ==========================================
const DEFAULT_SAMPLE_PRODUCTS = [
  {
    id: 1,
    name: "Varsity Jacket Premium Angkatan 2026",
    subtitle: "Custom Bordir Komputer Nama, NIM, & Logo Jurusan",
    category: "jaket",
    categoryName: "Jaket & Varsity",
    price: 245000,
    dpPrice: 122500,
    quotaCurrent: 82,
    quotaTarget: 100,
    badge: "PO Terpopuler",
    batch: "Batch 2 (Tutup 25 Okt)",
    description: "Varsity jacket berbahan Fleece Cotton tebal 330gsm kombinasi kulit sintetis premium pada lengan. Dilengkapi furing quilting diamond yang hangat dan nyaman, kancing snap metal anti karat, dan bordir komputer kerapatan tinggi.",
    specs: ["Bahan Badan: Cotton Fleece 330gsm", "Bahan Lengan: Oscar Leather Soft Grade A", "Bordir: 4 Titik Bordir Komputer Full HD", "Furing: Quilting Diamond Dacron 4oz", "Estimasi Produksi: 18 - 21 Hari"],
    sizes: ["S", "M", "L", "XL", "XXL", "3XL"],
    colors: ["Navy - White", "Black - Grey", "Maroon - Cream"],
    iconType: "varsity"
  },
  {
    id: 2,
    name: "Kemeja PDH Drill Custom Bordir Logo",
    subtitle: "Bahan American Drill Original 1919 Tebal & Adem",
    category: "pdh",
    categoryName: "Kemeja PDH",
    price: 135000,
    dpPrice: 67500,
    quotaCurrent: 64,
    quotaTarget: 80,
    badge: "Bordir Rapih",
    batch: "Batch 1 (Tutup 20 Okt)",
    description: "Kemeja PDH/PDL resmi organisasi dan angkatan. Menggunakan bahan American Drill 1919 asli yang tidak berbulu dan adem dipakai seharian. Jahitan rantai ganda kuat standar konveksi profesional.",
    specs: ["Bahan: Original American Drill 1919", "Bordir: Komputer Tajam 3 Titik", "Jahitan: Rantai Tiga Benang Kuat", "Kancing: Eksklusif Emboss", "Estimasi Produksi: 14 - 17 Hari"],
    sizes: ["S", "M", "L", "XL", "XXL"],
    colors: ["Navy Blue", "Hitam Solid", "Abu Khaki"],
    iconType: "shirt"
  },
  {
    id: 3,
    name: "Hoodie Heavyweight Fleece 330gsm 2026",
    subtitle: "Double Layered Hood dengan Sablon High Density",
    category: "hoodie",
    categoryName: "Hoodie & Sweatshirt",
    price: 185000,
    dpPrice: 92500,
    quotaCurrent: 92,
    quotaTarget: 100,
    badge: "Sisa 8 Kuota",
    batch: "Batch 3 (Segera Tutup)",
    description: "Hoodie kasual angkatan dengan bahan Cotton Fleece heavyweight tebal tanpa campuran poliester kasar. Bagian dalam lembut, saku kanguru presisi, dan tali hoodie anyaman tebal.",
    specs: ["Bahan: 100% Cotton Fleece 330gsm", "Sablon: Plastisol High Density Timbul", "Hoodie: Double Layer Tebal", "Rib: Elastis Spandex Anti Melar", "Estimasi Produksi: 14 Hari"],
    sizes: ["S", "M", "L", "XL", "XXL", "3XL"],
    colors: ["Deep Navy", "Jet Black", "Forest Green"],
    iconType: "hoodie"
  },
  {
    id: 4,
    name: "T-Shirt Cotton Combed 24s Sablon Plastisol",
    subtitle: "Kaos Makrab & Event Angkatan Nyaman & Sejuk",
    category: "kaos",
    categoryName: "Kaos Angkatan",
    price: 95000,
    dpPrice: 47500,
    quotaCurrent: 140,
    quotaTarget: 150,
    badge: "Best Seller",
    batch: "Batch 1 (Tutup 30 Okt)",
    description: "Kaos angkatan yang pas untuk kegiatan santai, makrab, atau gathering. Terbuat dari katun combed 24s reaktif gramasi pas, menyerap keringat maksimal, dan disablon dengan tinta plastisol premium.",
    specs: ["Bahan: Cotton Combed 24s Reaktif", "Sablon: Plastisol Curing Oven Glossy", "Jahitan: Standar Distro Rantai Pundak", "Model: Reguler Fit Unisex", "Estimasi Produksi: 10 - 12 Hari"],
    sizes: ["S", "M", "L", "XL", "XXL"],
    colors: ["Biru Navy", "Hitam", "Putih", "Sage Green"],
    iconType: "tshirt"
  },
  {
    id: 5,
    name: "Windbreaker Jacket Taslan Waterproof",
    subtitle: "Jaket Outdoor Angkatan Tahan Angin & Gerimis",
    category: "jaket",
    categoryName: "Jaket & Varsity",
    price: 195000,
    dpPrice: 97500,
    quotaCurrent: 45,
    quotaTarget: 60,
    badge: "Tahan Air",
    batch: "Batch 1 (Tutup 28 Okt)",
    description: "Jaket outdoor angkatan yang stylish dan fungsional. Material Taslan balon coating tebal yang tahan terpaan angin malam dan percikan hujan, dilapisi furing jaring mesh sejuk.",
    specs: ["Bahan: Taslan ZN Coating Waterproof", "Furing: Breathable Mesh Net", "Resleting: YKK Water Repellent", "Hoodie: Bisa dilipat ke kerah", "Estimasi Produksi: 16 - 18 Hari"],
    sizes: ["M", "L", "XL", "XXL"],
    colors: ["Navy - Dark Grey", "Full Black"],
    iconType: "windbreaker"
  },
  {
    id: 6,
    name: "Tote Bag Canvas Premium & Enamel Pin Set",
    subtitle: "Merchandise Kit Eksklusif Angkatan 2026",
    category: "merch",
    categoryName: "Merchandise & Aksesoris",
    price: 55000,
    dpPrice: 27500,
    quotaCurrent: 88,
    quotaTarget: 100,
    badge: "Min. 12 Pcs",
    batch: "Batch 2 (Tutup 22 Okt)",
    description: "Tote bag bahan kanvas marsoto tebal dengan resleting penutup dan saku dalam, dipadukan dengan enamel pin logam berkilau berlogo resmi angkatan.",
    specs: ["Bahan: Kanvas Marsoto 14oz Tebal", "Ukuran: 38 x 35 x 8 cm", "Sablon: Manual Rubber Solid", "Bonus: 1 Enamel Pin Logam Emboss", "Estimasi Produksi: 10 Hari"],
    sizes: ["All Size"],
    colors: ["Navy Blue", "Broken White", "Black"],
    iconType: "totebag"
  },
  {
    id: 7,
    name: "Lanyard Printing Tissue & Card Holder Kulit",
    subtitle: "Paket ID Card Eksklusif Kepanitiaan & Angkatan",
    category: "merch",
    categoryName: "Merchandise & Aksesoris",
    price: 25000,
    dpPrice: 12500,
    quotaCurrent: 210,
    quotaTarget: 250,
    badge: "Paket Hemat",
    batch: "Batch 1 (Tutup 25 Okt)",
    description: "Tali lanyard bahan tissue super lembut 2cm cetak sublimasi 2 sisi tajam tanpa luntur, dilengkapi kait oval tebal, stopper putar, dan holder kartu kulit sintetis.",
    specs: ["Bahan: Tissue Lembut 2cm", "Cetak: Sublimasi Full Color Anti Pudar", "Aksesoris: Stopper & Hook Besi Tebal", "Holder: Kulit Sintetis 2 Slot", "Estimasi Produksi: 7 Hari"],
    sizes: ["Standar (90 cm)"],
    colors: ["Navy Blue", "Hitam Gold"],
    iconType: "lanyard"
  },
  {
    id: 8,
    name: "Polo Shirt Lacoste Pique Bordir Rapih",
    subtitle: "Kaos Berkerah Formal Elegan untuk Angkatan",
    category: "kaos",
    categoryName: "Kaos Angkatan",
    price: 115000,
    dpPrice: 57500,
    quotaCurrent: 52,
    quotaTarget: 70,
    badge: "Bahan Adem",
    batch: "Batch 2 (Tutup 27 Okt)",
    description: "Polo shirt bahan Lacoste CVC Pique berpori rapi, adem, dan menyerap keringat. Kerah rajut tidak mudah melinting dan bordir komputer presisi tinggi di dada dan lengan.",
    specs: ["Bahan: Lacoste Cotton CVC 24s", "Kerah: Rajut Pique Elastis", "Bordir: 2 Titik Komputer", "Kancing: 2 Lubang Senada", "Estimasi Produksi: 14 Hari"],
    sizes: ["S", "M", "L", "XL", "XXL"],
    colors: ["Navy", "Hitam", "Maroon", "Putih"],
    iconType: "polo"
  }
];

// ==========================================
// 1B. SISTEM PENYIMPANAN PRODUK & MODE KOSONGAN (UNTUK INPUT ADMIN)
// ==========================================
const PRODUCTS_STORAGE_KEY = "fastender_products";

function getStoredProducts() {
  const raw = localStorage.getItem(PRODUCTS_STORAGE_KEY);
  if (raw === null) {
    // Mode Default: KOSONGAN (Empty Space) sesuai permintaan agar diisi melalui Halaman Admin
    return [];
  }
  try {
    return JSON.parse(raw);
  } catch (e) {
    return [];
  }
}

function saveStoredProducts(products) {
  localStorage.setItem(PRODUCTS_STORAGE_KEY, JSON.stringify(products));
}

function getAllProducts() {
  const stored = getStoredProducts();
  return stored;
}

// Helper untuk mengisi contoh demo instan / mengosongkan kembali
function loadDemoProducts() {
  saveStoredProducts(DEFAULT_SAMPLE_PRODUCTS);
  location.reload();
}

function clearAllProducts() {
  saveStoredProducts([]);
  location.reload();
}

// Fallback variabel global untuk kompatibilitas
const PRODUCTS_DATA = DEFAULT_SAMPLE_PRODUCTS;

// ==========================================
// 2. HELPER FORMAT RUPIAH & ICONS SVG
// ==========================================
function parsePriceInt(val) {
  if (typeof val === "number" && !isNaN(val)) {
    return Math.round(val);
  }
  if (typeof val === "string") {
    const cleaned = val.replace(/[^0-9]/g, "");
    const parsed = parseInt(cleaned, 10);
    return isNaN(parsed) ? 0 : parsed;
  }
  return 0;
}

function formatRupiah(amount) {
  const safeAmount = parsePriceInt(amount);
  return "Rp " + safeAmount.toLocaleString("id-ID");
}

function getProductSvg(type, color = "#111111") {
  // Return styled SVG illustrations matching Zellerfeld aesthetic
  switch (type) {
    case "varsity":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M45 42L80 56L115 42L135 68L120 82L112 74V130H48V74L40 82L25 68L45 42Z" fill="#111111"/>
          <path d="M45 42L25 68L40 82L48 74V60L45 42Z" fill="#000aff"/>
          <path d="M115 42L135 68L120 82L112 74V60L115 42Z" fill="#000aff"/>
          <path d="M78 55V130H82V55H78Z" fill="#ffffff" opacity="0.4"/>
          <circle cx="80" cy="75" r="2.5" fill="#000aff"/>
          <circle cx="80" cy="90" r="2.5" fill="#000aff"/>
          <circle cx="80" cy="105" r="2.5" fill="#000aff"/>
          <circle cx="80" cy="120" r="2.5" fill="#000aff"/>
          <text x="60" y="80" font-family="Space Mono, monospace" font-weight="700" font-size="13" fill="#ffffff">26</text>
        </svg>
      `;
    case "shirt":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M48 38L80 48L112 38L130 62L116 74L108 68V132H52V68L44 74L30 62L48 38Z" fill="#111111"/>
          <path d="M68 38L80 54L92 38H68Z" fill="#ecedee"/>
          <rect x="58" y="72" width="16" height="18" rx="2" fill="#2b2b2b"/>
          <circle cx="80" cy="70" r="2" fill="#a1a4aa"/>
          <circle cx="80" cy="85" r="2" fill="#a1a4aa"/>
          <circle cx="80" cy="100" r="2" fill="#a1a4aa"/>
          <circle cx="80" cy="115" r="2" fill="#a1a4aa"/>
          <path d="M79 48V132H81V48H79Z" fill="#ffffff" opacity="0.3"/>
        </svg>
      `;
    case "hoodie":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <ellipse cx="80" cy="40" rx="26" ry="16" fill="#2b2b2b"/>
          <path d="M46 44L80 50L114 44L134 74L118 84L110 76V130H50V76L42 84L26 74L46 44Z" fill="#111111"/>
          <path d="M60 100H100L96 124H64L60 100Z" fill="#2b2b2b"/>
          <path d="M74 54V76M86 54V76" stroke="#000aff" stroke-width="2.5" stroke-linecap="round"/>
          <text x="80" y="85" text-anchor="middle" font-family="Space Mono, monospace" font-weight="700" font-size="9" fill="#ffffff" letter-spacing="1">FASTENDER</text>
        </svg>
      `;
    case "tshirt":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M52 40L80 48L108 40L126 62L112 72L106 66V130H54V66L48 72L34 62L52 40Z" fill="#111111"/>
          <path d="M70 40C70 46 90 46 90 40Z" fill="#ecedee"/>
          <rect x="65" y="70" width="30" height="20" rx="3" fill="#000aff"/>
          <text x="80" y="84" text-anchor="middle" font-family="Space Mono, monospace" font-weight="700" font-size="8" fill="#ffffff">PO 2026</text>
        </svg>
      `;
    case "windbreaker":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M46 42L80 50L114 42L132 68L118 78L110 72V132H50V72L42 78L28 68L46 42Z" fill="#111111"/>
          <path d="M50 72L80 90L110 72V132H50V72Z" fill="#2b2b2b"/>
          <line x1="80" y1="50" x2="80" y2="132" stroke="#000aff" stroke-width="2.5"/>
        </svg>
      `;
    case "totebag":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M60 60V35C60 26 100 26 100 35V60" stroke="#111111" stroke-width="4" stroke-linecap="round"/>
          <rect x="45" y="55" width="70" height="80" rx="6" fill="#111111"/>
          <circle cx="80" cy="95" r="14" fill="#000aff"/>
          <text x="80" y="99" text-anchor="middle" font-family="Space Mono, monospace" font-weight="700" font-size="11" fill="#ffffff">PO</text>
        </svg>
      `;
    case "lanyard":
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M60 20L78 80H82L100 20" stroke="#111111" stroke-width="8" stroke-linecap="round"/>
          <rect x="76" y="80" width="8" height="10" rx="2" fill="#000aff"/>
          <circle cx="80" cy="94" r="4" fill="#a1a4aa"/>
          <rect x="62" y="98" width="36" height="48" rx="4" fill="#2b2b2b"/>
          <rect x="68" y="106" width="24" height="14" rx="2" fill="#ffffff"/>
          <line x1="68" y1="126" x2="92" y2="126" stroke="#000aff" stroke-width="2"/>
        </svg>
      `;
    default:
      return `
        <svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="160" height="160" rx="10" fill="#ecedee"/>
          <path d="M52 40L80 48L108 40L126 62L112 72L106 66V130H54V66L48 72L34 62L52 40Z" fill="#111111"/>
        </svg>
      `;
  }
}

// ==========================================
// 3. CART SYSTEM (LOCALSTORAGE)
// ==========================================
const CART_STORAGE_KEY = "fastender_po_cart";

function findProductMetaById(productId) {
  const numId = parseInt(productId, 10);
  if (typeof window !== "undefined" && window.CURRENT_PRODUCT && parseInt(window.CURRENT_PRODUCT.id, 10) === numId) {
    return window.CURRENT_PRODUCT;
  }
  if (typeof window !== "undefined" && Array.isArray(window.SERVER_PRODUCTS_CATALOG)) {
    const serverProd = window.SERVER_PRODUCTS_CATALOG.find(p => parseInt(p.id, 10) === numId);
    if (serverProd) return serverProd;
  }
  const allProds = getAllProducts();
  if (Array.isArray(allProds)) {
    const localProd = allProds.find(p => parseInt(p.id, 10) === numId && p.name && parsePriceInt(p.price) > 0);
    if (localProd) return localProd;
  }
  return DEFAULT_SAMPLE_PRODUCTS.find(p => parseInt(p.id, 10) === numId) || DEFAULT_SAMPLE_PRODUCTS[0];
}

function normalizeCartItem(rawItem, index = 0) {
  if (!rawItem || typeof rawItem !== "object") return null;

  const rawId = parseInt(rawItem.id ?? rawItem.productId ?? (index + 1), 10) || 1;
  const fallbackProd = findProductMetaById(rawId);

  const rawName = String(rawItem.name ?? rawItem.title ?? rawItem.productName ?? "").trim();
  const validName = (rawName && rawName !== "undefined" && rawName !== "null")
    ? rawName
    : (fallbackProd ? fallbackProd.name : "Varsity Jacket Angkatan 2026");

  const parsedPrice = parsePriceInt(rawItem.price ?? rawItem.harga);
  const validPrice = parsedPrice > 0
    ? parsedPrice
    : (fallbackProd ? parsePriceInt(fallbackProd.price) : 245000);

  const parsedDp = parsePriceInt(rawItem.dpPrice ?? rawItem.dp_price ?? rawItem.dp);
  const fallbackDp = fallbackProd ? parsePriceInt(fallbackProd.dpPrice ?? fallbackProd.dp_price) : 0;
  const validDp = parsedDp > 0
    ? parsedDp
    : (fallbackDp > 0 ? fallbackDp : Math.round(validPrice * 0.5));

  const validQty = Math.max(1, parseInt(rawItem.quantity ?? rawItem.qty ?? 1, 10) || 1);

  const fallbackImages = (fallbackProd && Array.isArray(fallbackProd.images) && fallbackProd.images.length > 0)
    ? fallbackProd.images[0]
    : null;
  const validImage = (rawItem.image && rawItem.image !== "undefined")
    ? rawItem.image
    : fallbackImages;

  return {
    id: rawId,
    name: validName,
    categoryName: rawItem.categoryName || rawItem.category_name || (fallbackProd ? (fallbackProd.categoryName || fallbackProd.category_name || "Apparel PO") : "Apparel PO"),
    price: validPrice,
    dpPrice: validDp,
    iconType: rawItem.iconType || rawItem.icon_type || (fallbackProd ? (fallbackProd.iconType || fallbackProd.icon_type || "varsity") : "varsity"),
    image: validImage,
    size: (rawItem.size && rawItem.size !== "undefined") ? String(rawItem.size) : "L",
    color: (rawItem.color && rawItem.color !== "undefined") ? String(rawItem.color) : "Standar",
    customText: (rawItem.customText && rawItem.customText !== "undefined") ? String(rawItem.customText) : "",
    quantity: validQty
  };
}

function getCart() {
  try {
    const raw = localStorage.getItem(CART_STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    if (!Array.isArray(parsed)) return [];
    const normalized = parsed
      .map((item, idx) => normalizeCartItem(item, idx))
      .filter(Boolean);
    return normalized;
  } catch (e) {
    console.error("Gagal membaca cart dari localStorage", e);
    return [];
  }
}

function saveCart(cart) {
  try {
    const normalized = Array.isArray(cart)
      ? cart.map((item, idx) => normalizeCartItem(item, idx)).filter(Boolean)
      : [];
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(normalized));
    updateCartBadge();
  } catch (e) {
    console.error("Gagal menyimpan cart ke localStorage", e);
  }
}

function addToCart(productId, quantity = 1, size = "L", color = "Standar", customText = "") {
  const product = (typeof productId === "object" && productId !== null)
    ? productId
    : findProductMetaById(productId);
  if (!product) return false;

  const normalizedNew = normalizeCartItem({
    id: product.id,
    name: product.name,
    categoryName: product.categoryName || product.category_name,
    price: product.price,
    dpPrice: product.dpPrice || product.dp_price,
    iconType: product.iconType || product.icon_type,
    image: (product.images && product.images.length > 0) ? product.images[0] : product.image,
    size: size || (product.sizes && product.sizes.length > 0 ? product.sizes[0] : "L"),
    color: color || (product.colors && product.colors.length > 0 ? product.colors[0] : "Standar"),
    customText: customText || "",
    quantity: quantity
  });

  const cart = getCart();
  const existingIndex = cart.findIndex(item =>
    item.id === normalizedNew.id &&
    item.size === normalizedNew.size &&
    item.color === normalizedNew.color &&
    item.customText === normalizedNew.customText
  );

  if (existingIndex > -1) {
    cart[existingIndex].quantity = (parseInt(cart[existingIndex].quantity, 10) || 1) + normalizedNew.quantity;
    if (!cart[existingIndex].image && normalizedNew.image) {
      cart[existingIndex].image = normalizedNew.image;
    }
  } else {
    cart.push(normalizedNew);
  }

  saveCart(cart);
  showToast(`"${normalizedNew.name}" berhasil ditambahkan ke keranjang!`, "success");
  return true;
}

function updateCartBadge() {
  const cart = getCart();
  const totalCount = cart.reduce((acc, item) => acc + (parseInt(item.quantity, 10) || 0), 0);
  const badgeEls = document.querySelectorAll(".cart-badge");
  badgeEls.forEach(badge => {
    badge.textContent = totalCount;
    badge.style.display = totalCount > 0 ? "flex" : "none";
  });
}

// ==========================================
// 4. TOAST NOTIFICATION SYSTEM
// ==========================================
function showToast(message, type = "info") {
  let container = document.querySelector(".toast-container");
  if (!container) {
    container = document.createElement("div");
    container.className = "toast-container";
    document.body.appendChild(container);
  }

  const toast = document.createElement("div");
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      ${type === 'success' 
        ? '<polyline points="20 6 9 17 4 12"></polyline>' 
        : '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'}
    </svg>
    <span>${message}</span>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transform = "translateX(100%)";
    toast.style.transition = "all 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// ==========================================
// 5. RENDER PRODUCT CARD HELPER
// ==========================================
function renderProductCard(product) {
  const quotaPercent = Math.min(100, Math.round((product.quotaCurrent / product.quotaTarget) * 100));
  const hasImages = product.images && product.images.length > 0;
  const imageElement = hasImages
    ? `<img src="${product.images[0]}" alt="${product.name}" class="product-thumb">`
    : `<div style="width: 100%; height: 100%; padding: 20px;">${getProductSvg(product.iconType)}</div>`;

  return `
    <article class="product-card" data-id="${product.id}">
      <div class="product-thumb-wrapper">
        <span class="product-card-badge">${product.badge}</span>
        <span class="product-batch-tag">${product.batch.split(' ')[0]}</span>
        ${imageElement}
      </div>
      <div class="product-content">
        <span class="product-category">${product.categoryName}</span>
        <h3 class="product-title" title="${product.name}">${product.name}</h3>
        
        <div class="po-quota-box">
          <div class="po-quota-info">
            <span>Kuota Terisi: <strong>${product.quotaCurrent}/${product.quotaTarget} pcs</strong></span>
            <span>${quotaPercent}%</span>
          </div>
          <div class="po-progress-bar">
            <div class="po-progress-fill" style="width: ${quotaPercent}%;"></div>
          </div>
        </div>

        <div class="product-footer">
          <div class="product-price-row">
            <span class="product-price">${formatRupiah(product.price)}</span>
            <span class="product-dp-label">DP: ${formatRupiah(product.dpPrice)}</span>
          </div>
          <!-- Tombol 'Detail Produk' bergaris tepi (outline) oranye sesuai spesifikasi figma -->
          <a href="detail.php?id=${product.id}" class="btn btn-outline-accent btn-block">
            Detail Produk
          </a>
        </div>
      </div>
    </article>
  `;
}

// ==========================================
// 5B. RENDER SPACE KOSONGAN (EMPTY SLOTS HELPER)
// ==========================================
function renderEmptyProductSlot(slotIndex = 1) {
  return `
    <div class="empty-slot-card">
      <span class="empty-slot-badge">Slot Kosong #${slotIndex}</span>
      <div class="empty-slot-icon">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2" stroke-dasharray="4 4"></rect>
          <line x1="12" y1="8" x2="12" y2="16"></line>
          <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
      </div>
      <h3 class="empty-slot-title">Katalog Produk PO</h3>
      <p class="empty-slot-desc">Produk baru edisi angkatan 2026 akan segera dirilis di slot ini.</p>
      <a href="lacak.php" class="btn btn-outline-accent btn-sm">
        Lacak Pesanan
      </a>
    </div>
  `;
}

function renderEmptySlotBanner() {
  return `
    <div class="empty-slot-banner">
      <div class="empty-slot-banner-text">
        <div>
          <h4>Koleksi Produk Pre-Order Segera Dibuka</h4>
          <p>Katalog apparel resmi angkatan 2026 sedang dalam proses persiapan rilis.</p>
        </div>
      </div>
      <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="https://wa.me/6281234567890?text=Halo%20Fastender,%20saya%20ingin%20konsultasi%20PO" target="_blank" class="btn btn-accent btn-sm">
          Konsultasi via WhatsApp
        </a>
        <a href="lacak.php" class="btn btn-outline-primary btn-sm">
          Lacak Pesanan
        </a>
      </div>
    </div>
  `;
}

// ==========================================
// 6. GLOBAL INITIALIZATION (DOMContentLoaded)
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
  // Update badge saat halaman dimuat
  updateCartBadge();

  // Mobile menu toggle dengan auto-close saat klik di luar
  const navToggle = document.querySelector(".nav-toggle-btn");
  const navMenu = document.querySelector(".navbar-menu");
  if (navToggle && navMenu) {
    navToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      navMenu.classList.toggle("active");
    });

    document.addEventListener("click", (e) => {
      if (!navMenu.contains(e.target) && !navToggle.contains(e.target)) {
        navMenu.classList.remove("active");
      }
    });

    navMenu.querySelectorAll(".navbar-link").forEach((link) => {
      link.addEventListener("click", () => {
        navMenu.classList.remove("active");
      });
    });
  }

  // Inisialisasi Realtime Countdown Timer Hero Section
  initHeroCountdown();

  // Inisialisasi Image Slider / Carousel Otomatis Hero Section
  initHeroSlider();

  // Inisialisasi Now Trending Horizontal Carousel (Zellerfeld Style)
  initTrendingCarousel();

  // Inisialisasi modal login / daftar
  setupAuthModal();
});

// ==========================================
// REALTIME COUNTDOWN TIMER (HERO SECTION)
// ==========================================
function initHeroCountdown() {
  const container = document.getElementById("hero-countdown");
  if (!container) return;

  const deadlineStr = container.getAttribute("data-deadline");
  if (!deadlineStr) return;

  const labelEl = document.querySelector(".hero-countdown-label") || document.getElementById("hero-countdown-label");
  const elDays = document.getElementById("cd-days");
  const elHours = document.getElementById("cd-hours");
  const elMins = document.getElementById("cd-mins");
  const elSecs = document.getElementById("cd-secs");

  // Tangani format cross-browser ISO / MySQL (misal: 2026-10-13T13:00)
  let targetTime = new Date(deadlineStr).getTime();
  if (isNaN(targetTime)) {
    targetTime = new Date(deadlineStr.replace(" ", "T")).getTime();
  }

  // Jika format invalid, gunakan fallback 14 hari ke depan
  if (isNaN(targetTime)) {
    targetTime = Date.now() + 14 * 24 * 60 * 60 * 1000;
  }

  function setClosedState() {
    if (elDays) elDays.textContent = "00";
    if (elHours) elHours.textContent = "00";
    if (elMins) elMins.textContent = "00";
    if (elSecs) elSecs.textContent = "00";
    if (labelEl) {
      labelEl.textContent = "PRE-ORDER BATCH INI TELAH DITUTUP";
    }
    container.classList.add("countdown-closed");
  }

  function updateTimer() {
    const now = Date.now();
    const distance = targetTime - now;

    if (distance <= 0) {
      setClosedState();
      return;
    }

    const days = Math.max(0, Math.floor(distance / (1000 * 60 * 60 * 24)));
    const hours = Math.max(0, Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)));
    const minutes = Math.max(0, Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)));
    const seconds = Math.max(0, Math.floor((distance % (1000 * 60)) / 1000));

    if (elDays) elDays.textContent = String(days).padStart(2, "0");
    if (elHours) elHours.textContent = String(hours).padStart(2, "0");
    if (elMins) elMins.textContent = String(minutes).padStart(2, "0");
    if (elSecs) elSecs.textContent = String(seconds).padStart(2, "0");
  }

  updateTimer();
  setInterval(updateTimer, 1000);
}

// ==========================================
// 7. MODAL LOGIN / DAFTAR
// ==========================================
function setupAuthModal() {
  const authBtns = document.querySelectorAll(".auth-trigger-btn");
  if (!authBtns.length) return;

  let modalOverlay = document.getElementById("auth-modal");
  if (!modalOverlay) {
    modalOverlay = document.createElement("div");
    modalOverlay.id = "auth-modal";
    modalOverlay.className = "modal-overlay";
    modalOverlay.innerHTML = `
      <div class="modal-dialog">
        <button class="modal-close-btn" aria-label="Tutup">&times;</button>
        <div style="text-align: center; margin-bottom: 24px;">
          <div class="brand-icon" style="margin: 0 auto 12px; width: 44px; height: 44px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
              <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
            </svg>
          </div>
          <h3 style="font-size: 22px; color: var(--primary); font-weight: 700;">Masuk ke Fastender</h3>
          <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Pantau progress pesanan Pre-Order angkatanmu</p>
        </div>
        <form id="auth-form" onsubmit="handleAuthSubmit(event)">
          <div class="form-group">
            <label class="form-label">Email atau No. WhatsApp</label>
            <input type="text" class="form-control" placeholder="contoh: 081234567890" required>
          </div>
          <div class="form-group">
            <label class="form-label">Kata Sandi</label>
            <input type="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer;">
              <input type="checkbox" checked style="accent-color: var(--accent);"> Ingat saya
            </label>
            <a href="#" style="color: var(--accent); font-weight: 600;">Lupa Sandi?</a>
          </div>
          <button type="submit" class="btn btn-accent btn-block btn-lg" style="margin-bottom: 12px;">
            Masuk Sekarang
          </button>
          <button type="button" onclick="closeAuthModal()" class="btn btn-outline-primary btn-block">
            Tutup
          </button>
        </form>
        <p style="text-align: center; font-size: 13px; color: var(--text-muted); margin-top: 18px;">
          Belum punya akun? <a href="#" onclick="showToast('Fitur pendaftaran akun dibuka otomatis saat checkout!', 'info'); return false;" style="color: var(--accent); font-weight: 700;">Daftar di sini</a>
        </p>
      </div>
    `;
    document.body.appendChild(modalOverlay);
  }

  authBtns.forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      modalOverlay.classList.add("active");
    });
  });

  const closeBtn = modalOverlay.querySelector(".modal-close-btn");
  if (closeBtn) {
    closeBtn.addEventListener("click", closeAuthModal);
  }

  modalOverlay.addEventListener("click", (e) => {
    if (e.target === modalOverlay) closeAuthModal();
  });
}

function closeAuthModal() {
  const modalOverlay = document.getElementById("auth-modal");
  if (modalOverlay) modalOverlay.classList.remove("active");
}

function handleAuthSubmit(e) {
  e.preventDefault();
  closeAuthModal();
  showToast("Selamat datang kembali! Anda berhasil masuk.", "success");
}

// ==========================================
// 8. HERO SHOWCASE SLIDER / CAROUSEL OTOMATIS
// ==========================================
function initHeroSlider() {
  const sliderEl = document.getElementById("hero-slider");
  if (!sliderEl) return;

  const slides = sliderEl.querySelectorAll(".slider-slide");
  const dots = sliderEl.querySelectorAll(".slider-dot");
  const prevBtn = document.getElementById("slider-prev");
  const nextBtn = document.getElementById("slider-next");

  if (!slides || slides.length === 0) return;

  let currentSlide = 0;
  let isTransitioning = false;
  let autoplayTimer = null;
  const AUTOPLAY_INTERVAL = 4500; // Loop bergantian tiap 4.5 detik (tenang & konsisten)
  const TRANSITION_DURATION = 850; // Durasi sinkron dengan CSS transition 0.85s (850ms)

  function goToSlide(index) {
    if (slides.length <= 1) return;
    if (isTransitioning) return; // Mencegah glitch saat klik bertubi-tubi

    const prevIndex = currentSlide;
    const nextIndex = (index + slides.length) % slides.length;
    if (prevIndex === nextIndex) return;

    isTransitioning = true;

    const oldSlide = slides[prevIndex];
    const newSlide = slides[nextIndex];

    // Bersihkan sisa class fade-out dari semua slide selain slide aktif sebelumnya
    slides.forEach((s, i) => {
      if (i !== prevIndex) {
        s.classList.remove("fade-out");
      }
    });

    // 1. Slide lama memudar keluar secara bertahap (fade-out) dari opacity 1 ke 0
    oldSlide.classList.remove("active");
    oldSlide.classList.add("fade-out");
    if (dots[prevIndex]) {
      dots[prevIndex].classList.remove("active");
    }

    // 2. Slide baru memudar masuk secara simultan (fade-in) dari opacity 0 ke 1
    currentSlide = nextIndex;
    newSlide.classList.remove("fade-out");
    newSlide.classList.add("active");
    if (dots[nextIndex]) {
      dots[nextIndex].classList.add("active");
    }

    // 3. Setelah transisi selesai (850ms), bersihkan class fade-out
    setTimeout(() => {
      oldSlide.classList.remove("fade-out");
      isTransitioning = false;
    }, TRANSITION_DURATION);
  }

  function nextSlide() {
    goToSlide(currentSlide + 1);
  }

  function prevSlide() {
    goToSlide(currentSlide - 1);
  }

  function startAutoplay() {
    stopAutoplay();
    if (slides.length > 1) {
      autoplayTimer = setInterval(nextSlide, AUTOPLAY_INTERVAL);
    }
  }

  function stopAutoplay() {
    if (autoplayTimer) {
      clearInterval(autoplayTimer);
      autoplayTimer = null;
    }
  }

  // Event panah navigasi
  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      if (isTransitioning) return;
      nextSlide();
      startAutoplay();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      if (isTransitioning) return;
      prevSlide();
      startAutoplay();
    });
  }

  // Event indikator dots
  dots.forEach((dot, idx) => {
    dot.addEventListener("click", () => {
      if (isTransitioning || idx === currentSlide) return;
      goToSlide(idx);
      startAutoplay();
    });
  });

  // Pause autoplay saat kursor mouse berada di atas kartu slider
  sliderEl.addEventListener("mouseenter", stopAutoplay);
  sliderEl.addEventListener("mouseleave", startAutoplay);

  // Dukungan gesture swipe layar sentuh (mobile/tablet)
  let touchStartX = 0;
  let touchEndX = 0;

  sliderEl.addEventListener("touchstart", (e) => {
    touchStartX = e.changedTouches[0].screenX;
    stopAutoplay();
  }, { passive: true });

  sliderEl.addEventListener("touchend", (e) => {
    touchEndX = e.changedTouches[0].screenX;
    const diff = touchStartX - touchEndX;
    if (Math.abs(diff) > 40 && !isTransitioning) {
      if (diff > 0) {
        nextSlide();
      } else {
        prevSlide();
      }
    }
    startAutoplay();
  }, { passive: true });

  // Jalankan autoplay pertama kali
  startAutoplay();
}

// ==========================================
// NOW TRENDING HORIZONTAL CAROUSEL (ULTRA-SMOOTH CONTINUOUS TICKER)
// Kecepatan 100% konsisten, selalu lambat, tanpa lonjakan/glitch, bebas bug
// ==========================================
function initTrendingCarousel() {
  const container = document.getElementById("trending-swiper");
  if (!container) return;

  const wrapper = container.querySelector(".swiper-wrapper");
  if (!wrapper) return;

  const slides = wrapper.querySelectorAll(".trending-slide");
  if (!slides || slides.length === 0) return;

  const originalCount = parseInt(container.getAttribute("data-original-count"), 10) || 6;
  if (slides.length <= originalCount) return;

  // Kecepatan gerak konstan dalam piksel per detik:
  // 32px per detik = sangat lambat, tenang, elegan, dan nyaman dibaca
  const SPEED_PX_PER_SEC = 32;

  let currentTranslate = 0;
  let oneCycleWidth = 0;
  let isHovered = false;
  let isDragging = false;
  let isManualAnimating = false;
  let manualAnimReq = null;
  let lastTimestamp = null;
  let animFrameId = null;

  // Hitung lebar 1 siklus produk asli secara presisi dari posisi elemen DOM
  function calculateDimensions() {
    if (slides[originalCount] && slides[0]) {
      const dist = slides[originalCount].offsetLeft - slides[0].offsetLeft;
      if (dist > 0) {
        oneCycleWidth = dist;
      }
    }
    // Fallback jika offsetLeft belum terhitung
    if (!oneCycleWidth || oneCycleWidth <= 0) {
      const slideWidth = slides[0].offsetWidth || 270;
      const computedMargin = parseFloat(window.getComputedStyle(slides[0]).marginRight) || 20;
      oneCycleWidth = (slideWidth + computedMargin) * originalCount;
    }
  }

  calculateDimensions();

  // Resize listener untuk mengupdate dimensi loop saat ukuran layar berubah
  window.addEventListener("resize", () => {
    calculateDimensions();
    if (oneCycleWidth > 0) {
      while (currentTranslate <= -oneCycleWidth) currentTranslate += oneCycleWidth;
      while (currentTranslate > 0) currentTranslate -= oneCycleWidth;
      setTranslate(currentTranslate);
    }
  });

  // Terapkan translasi 3D ke wrapper untuk akselerasi perangkat keras (GPU)
  function setTranslate(val) {
    currentTranslate = val;
    wrapper.style.transform = `translate3d(${val}px, 0, 0)`;
  }

  // Loop requestAnimationFrame berbasis Delta-Time (konsisten di semua monitor 60Hz/120Hz/144Hz)
  function step(timestamp) {
    if (!lastTimestamp) {
      lastTimestamp = timestamp;
    }
    // Batasi delta time maksimal 100ms untuk mencegah lonjakan saat tab diminimize
    const delta = Math.min((timestamp - lastTimestamp) / 1000, 0.1);
    lastTimestamp = timestamp;

    if (!isHovered && !isDragging && !isManualAnimating && oneCycleWidth > 0) {
      currentTranslate -= SPEED_PX_PER_SEC * delta;

      // Seamless infinite loop: perpindahan modul visual 100% tanpa celah/hentakan
      while (currentTranslate <= -oneCycleWidth) {
        currentTranslate += oneCycleWidth;
      }
      while (currentTranslate > 0) {
        currentTranslate -= oneCycleWidth;
      }

      setTranslate(currentTranslate);
    }

    animFrameId = requestAnimationFrame(step);
  }

  animFrameId = requestAnimationFrame(step);

  // Pause on hover: saat kursor mouse di atas slider, berhenti tenang di piksel saat itu
  container.addEventListener("mouseenter", () => {
    isHovered = true;
  });

  // Resume on leave: lanjutkan kembali dengan kecepatan lambat yang persis sama
  container.addEventListener("mouseleave", () => {
    isHovered = false;
    lastTimestamp = performance.now(); // reset timer agar delta tidak menumpuk
  });

  // Navigasi manual via tombol panah (< dan >)
  const prevBtn = document.querySelector(".btn-prev-terpopuler") || document.getElementById("trending-prev");
  const nextBtn = document.querySelector(".btn-next-terpopuler") || document.getElementById("trending-next");

  function getStepDistance() {
    if (slides.length > 1) {
      const stepDist = slides[1].offsetLeft - slides[0].offsetLeft;
      if (stepDist > 0) return stepDist;
    }
    return (slides[0].offsetWidth || 270) + 20;
  }

  function smoothManualMove(direction) {
    if (manualAnimReq) {
      cancelAnimationFrame(manualAnimReq);
      manualAnimReq = null;
    }

    isManualAnimating = true;
    const stepDist = getStepDistance();
    const startX = currentTranslate;
    const targetDistance = (direction === "next") ? -stepDist : stepDist;
    const duration = 460; // ms
    const startTime = performance.now();

    function easeOutCubic(t) {
      return 1 - Math.pow(1 - t, 3);
    }

    function animateManual(now) {
      const elapsed = now - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const easeVal = easeOutCubic(progress);

      let newX = startX + (targetDistance * easeVal);

      if (oneCycleWidth > 0) {
        while (newX <= -oneCycleWidth) newX += oneCycleWidth;
        while (newX > 0) newX -= oneCycleWidth;
      }

      setTranslate(newX);

      if (progress < 1) {
        manualAnimReq = requestAnimationFrame(animateManual);
      } else {
        isManualAnimating = false;
        lastTimestamp = performance.now();
      }
    }

    manualAnimReq = requestAnimationFrame(animateManual);
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", (e) => {
      e.preventDefault();
      smoothManualMove("prev");
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", (e) => {
      e.preventDefault();
      smoothManualMove("next");
    });
  }

  // Dukungan sentuhan / drag geser manual (Mouse & Touch)
  let startPointerX = 0;
  let startTranslateDrag = 0;
  let isPointerDown = false;
  let hasMoved = false;

  function onPointerDown(e) {
    if (isManualAnimating) {
      cancelAnimationFrame(manualAnimReq);
      isManualAnimating = false;
    }
    isPointerDown = true;
    isDragging = true;
    hasMoved = false;
    startPointerX = (e.clientX !== undefined) ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    startTranslateDrag = currentTranslate;
    container.style.cursor = "grabbing";
  }

  function onPointerMove(e) {
    if (!isPointerDown) return;
    const curX = (e.clientX !== undefined) ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    const diff = curX - startPointerX;

    if (Math.abs(diff) > 4) {
      hasMoved = true;
    }

    let dragX = startTranslateDrag + diff;
    if (oneCycleWidth > 0) {
      while (dragX <= -oneCycleWidth) dragX += oneCycleWidth;
      while (dragX > 0) dragX -= oneCycleWidth;
    }
    setTranslate(dragX);
  }

  function onPointerUp() {
    if (!isPointerDown) return;
    isPointerDown = false;
    isDragging = false;
    container.style.cursor = "";
    lastTimestamp = performance.now();
  }

  container.addEventListener("mousedown", onPointerDown);
  window.addEventListener("mousemove", onPointerMove);
  window.addEventListener("mouseup", onPointerUp);

  container.addEventListener("touchstart", onPointerDown, { passive: true });
  window.addEventListener("touchmove", onPointerMove, { passive: true });
  window.addEventListener("touchend", onPointerUp, { passive: true });

  // Cegah link <a> terbuka hanya jika user sedang melakukan swipe/drag
  container.addEventListener("click", (e) => {
    if (hasMoved) {
      e.preventDefault();
      e.stopPropagation();
    }
  }, true);

  // Hitung ulang dimensi secara presisi setelah semua gambar selesai dimuat
  window.addEventListener("load", calculateDimensions);
}


