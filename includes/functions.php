<?php
/**
 * Fastender Pre-Order Platform
 * File Fungsi Utama & Helper (includes/functions.php)
 * PHP Native Functions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set zona waktu default ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/database.php';

// Path data JSON untuk fallback jika MySQL belum diimport/aktif
define('DATA_PRODUCTS_FILE', __DIR__ . '/../data/products.json');
define('DATA_ORDERS_FILE', __DIR__ . '/../data/orders.json');
define('DATA_HERO_SETTINGS_FILE', __DIR__ . '/../data/hero_settings.json');
define('DATA_STORE_SETTINGS_FILE', __DIR__ . '/../data/store_settings.json');
define('UPLOAD_DIR', __DIR__ . '/../uploads/products/');
define('UPLOAD_HERO_DIR', __DIR__ . '/../uploads/hero/');
define('UPLOAD_BUKTI_DIR', __DIR__ . '/../uploads/bukti_pembayaran/');
define('UPLOAD_QRIS_DIR', __DIR__ . '/../uploads/qris/');


// ----------------------------------------------------
// 1. HELPER FORMAT RUPIAH & TEKS
// ----------------------------------------------------
function formatRupiah($amount) {
    return 'Rp ' . number_format((int)$amount, 0, ',', '.');
}

function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// ----------------------------------------------------
// 2. HELPER AUTENTIKASI ADMIN
// ----------------------------------------------------
function isAdminLoggedIn() {
    return !empty($_SESSION['fastender_admin_logged_in']) && $_SESSION['fastender_admin_logged_in'] === true;
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: admin-login.php');
        exit;
    }
}

// ----------------------------------------------------
// 3. PENGELOLAAN DATA PRODUK PRE-ORDER
// ----------------------------------------------------

/**
 * Daftar 9 kategori resmi produk Fastender
 */
function getProductCategoriesMap() {
    return [
        'POLSTUFF' => 'POLSTUFF',
        'POLNEEDS' => 'POLNEEDS',
        'Workshirt' => 'Workshirt',
        'Jas almamater' => 'Jas almamater',
        'Jas Lab' => 'Jas Lab',
        'Kaos' => 'Kaos',
        'Keychain' => 'Keychain',
        'Totebag' => 'Totebag',
        'Sticker' => 'Sticker'
    ];
}

/**
 * Menentukan kategori induk (POLSTUFF / POLNEEDS) dari nama kategori produk
 */
function resolveParentCategory($category) {
    $catLower = strtolower(trim((string)$category));
    if (in_array($catLower, ['kaos', 'keychain', 'totebag', 'sticker', 'polstuff'], true)) {
        return 'POLSTUFF';
    }
    if (in_array($catLower, ['workshirt', 'jas almamater', 'jas lab', 'polneeds'], true)) {
        return 'POLNEEDS';
    }
    return '';
}

/**
 * Mengecek apakah sebuah item produk cocok dengan filter kategori (termasuk kategori induk POLSTUFF & POLNEEDS)
 */
function isProductMatchingCategory($product, $kategori = '') {
    $kategori = trim((string)$kategori);
    if ($kategori === '' || strtolower($kategori) === 'all') {
        return true;
    }

    $katLower = strtolower($kategori);
    $pCat = strtolower(trim((string)($product['category'] ?? '')));
    $pCatName = strtolower(trim((string)($product['category_name'] ?? '')));
    $pParent = strtolower(trim((string)($product['parent_kategori'] ?? '')));

    if ($katLower === 'polstuff') {
        $polstuffSubs = ['kaos', 'keychain', 'totebag', 'sticker', 'polstuff'];
        return in_array($pCat, $polstuffSubs, true) || in_array($pCatName, $polstuffSubs, true) || $pParent === 'polstuff';
    }

    if ($katLower === 'polneeds') {
        $polneedsSubs = ['workshirt', 'jas almamater', 'jas lab', 'polneeds'];
        return in_array($pCat, $polneedsSubs, true) || in_array($pCatName, $polneedsSubs, true) || $pParent === 'polneeds';
    }

    return ($pCat === $katLower || $pCatName === $katLower);
}

/**
 * Memastikan kolom parent_kategori tersedia di tabel products MySQL
 */
function ensureProductsParentCategoryColumn($pdo) {
    static $checked = false;
    if ($checked || !$pdo) {
        return;
    }
    $checked = true;
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM products LIKE 'parent_kategori'");
        if ($colCheck && $colCheck->rowCount() === 0) {
            $pdo->exec("ALTER TABLE products ADD COLUMN parent_kategori VARCHAR(50) DEFAULT NULL AFTER category_name");
        }
    } catch (Exception $e) {
        // Abaikan jika gagal
    }
}

/**
 * Mengambil semua produk yang tersimpan (dari DB MySQL atau fallback file JSON),
 * mendukung filter kategori spesifik maupun pengelompokan kategori induk (POLSTUFF & POLNEEDS)
 */
function getProductsList($kategori = '') {
    $kategori = trim((string)$kategori);
    $hasCategoryFilter = ($kategori !== '' && strtolower($kategori) !== 'all');
    $katUpper = strtoupper($kategori);

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            ensureProductsParentCategoryColumn($pdo);
            $totalStmt = $pdo->query("SELECT COUNT(*) FROM products");
            $totalInDb = $totalStmt ? (int)$totalStmt->fetchColumn() : 0;

            if ($totalInDb > 0) {
                if (!$hasCategoryFilter) {
                    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
                } elseif ($katUpper === 'POLSTUFF') {
                    $stmt = $pdo->prepare("
                        SELECT * FROM products
                        WHERE category IN ('Kaos', 'Keychain', 'Totebag', 'Sticker', 'POLSTUFF')
                           OR category_name IN ('Kaos', 'Keychain', 'Totebag', 'Sticker', 'POLSTUFF')
                           OR parent_kategori = 'POLSTUFF'
                        ORDER BY id DESC
                    ");
                    $stmt->execute();
                } elseif ($katUpper === 'POLNEEDS') {
                    $stmt = $pdo->prepare("
                        SELECT * FROM products
                        WHERE category IN ('Workshirt', 'Jas almamater', 'Jas Lab', 'POLNEEDS')
                           OR category_name IN ('Workshirt', 'Jas almamater', 'Jas Lab', 'POLNEEDS')
                           OR parent_kategori = 'POLNEEDS'
                        ORDER BY id DESC
                    ");
                    $stmt->execute();
                } else {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = :kategori OR category_name = :kategori ORDER BY id DESC");
                    $stmt->execute([':kategori' => $kategori]);
                }

                $products = $stmt->fetchAll();
                foreach ($products as &$p) {
                    // Ambil foto-foto produk
                    $imgStmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
                    $imgStmt->execute([$p['id']]);
                    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
                    $p['images'] = $images ?: [];
                    $p['specs'] = !empty($p['specs']) ? explode(',', $p['specs']) : [];
                    $p['sizes'] = !empty($p['sizes']) ? explode(',', $p['sizes']) : ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
                    $p['colors'] = !empty($p['colors']) ? explode(',', $p['colors']) : ['Navy', 'Hitam', 'Maroon', 'Putih'];
                    if (empty($p['parent_kategori'])) {
                        $p['parent_kategori'] = resolveParentCategory($p['category'] ?? ($p['category_name'] ?? ''));
                    }
                }
                return $products;
            }
        } catch (Exception $e) {
            // Lanjut ke fallback
        }
    }

    // Fallback file JSON
    $list = null;
    if (file_exists(DATA_PRODUCTS_FILE)) {
        $json = file_get_contents(DATA_PRODUCTS_FILE);
        $data = json_decode($json, true);
        if (is_array($data)) {
            $list = $data;
        }
    }

    if ($list === null) {
        // Default mode: Array dinamis katalog produk 2026
        $list = getDefaultCatalogProducts();
    }

    foreach ($list as &$item) {
        if (empty($item['parent_kategori'])) {
            $item['parent_kategori'] = resolveParentCategory($item['category'] ?? ($item['category_name'] ?? ''));
        }
    }
    unset($item);

    if ($hasCategoryFilter) {
        $list = array_values(array_filter($list, function($p) use ($kategori) {
            return isProductMatchingCategory($p, $kategori);
        }));
    }

    return $list;
}

/**
 * Data katalog produk bawaan resmi jika tabel MySQL/JSON belum ada data
 */
function getDefaultCatalogProducts() {
    return [
        [
            'id' => 1,
            'name' => 'Workshirt Angkatan 2026',
            'category' => 'Workshirt',
            'category_name' => 'Workshirt',
            'price' => 145000,
            'dp_price' => 75000,
            'quota_target' => 100,
            'quota_current' => 64,
            'batch' => 'Batch 1',
            'badge' => 'Terpopuler',
            'images' => ['assets/images/gambar1.jpg'],
            'specs' => ['Bahan American Drill Grade A', 'Bordir Komputer High Density', 'Jahitan Rantai Kuat'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'colors' => ['Navy & White', 'Black & Cream', 'Forest Green'],
            'description' => 'Workshirt edisi angkatan 2026 dengan material American drill premium kombinasi bordir komputer presisi tinggi.'
        ],
        [
            'id' => 2,
            'name' => 'Jas Almamater Kampus Polinema',
            'category' => 'Jas almamater',
            'category_name' => 'Jas almamater',
            'price' => 185000,
            'dp_price' => 95000,
            'quota_target' => 150,
            'quota_current' => 112,
            'batch' => 'Batch 1',
            'badge' => 'Best Seller',
            'images' => ['assets/images/gambar2.jpg'],
            'specs' => ['Bahan Twist Drill Tebal', 'Furing Dormeuil Adem', 'Kancing Kuningan Berlogo'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'colors' => ['Navy Blue Resmi'],
            'description' => 'Jas almamater resmi mahasiswa dengan bahan twist drill rapi, furing adem, dan potongan formal presisi.'
        ],
        [
            'id' => 3,
            'name' => 'Jas Lab Praktikum Lengan Panjang',
            'category' => 'Jas Lab',
            'category_name' => 'Jas Lab',
            'price' => 125000,
            'dp_price' => 65000,
            'quota_target' => 80,
            'quota_current' => 58,
            'batch' => 'Batch 1',
            'badge' => 'Wajib Praktikum',
            'images' => ['assets/images/gambar3.jpg'],
            'specs' => ['Bahan Oxford Tebal Standar Lab', 'Saku Depan & Samping', 'Mudah Dicuci & Setrika'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
            'colors' => ['Putih Bersih'],
            'description' => 'Jas laboratorium standar praktikum kampus dan bengkel dengan bahan Oxford tebal dan nyaman dipakai.'
        ],
        [
            'id' => 4,
            'name' => 'T-Shirt Cotton Combed 24s Sablon',
            'category' => 'Kaos',
            'category_name' => 'Kaos',
            'price' => 95000,
            'dp_price' => 50000,
            'quota_target' => 200,
            'quota_current' => 140,
            'batch' => 'Batch 1',
            'badge' => 'Favorit',
            'images' => ['assets/images/gambar1.jpg'],
            'specs' => ['100% Cotton Combed 24s Reaktif', 'Sablon Plastisol Doff', 'Kerah Rib Katun Tidak Melar'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'colors' => ['Hitam Solid', 'Putih Bersih', 'Navy Tua'],
            'description' => 'Kaos angkatan kasual harian bahan cotton combed 24s adem dan menyerap keringat dengan sablon tahan cuci.'
        ],
        [
            'id' => 5,
            'name' => 'Paket Starter Kit POLNEEDS 2026',
            'category' => 'POLNEEDS',
            'category_name' => 'POLNEEDS',
            'price' => 115000,
            'dp_price' => 60000,
            'quota_target' => 75,
            'quota_current' => 42,
            'batch' => 'Batch 1',
            'badge' => 'Baru',
            'images' => ['assets/images/gambar2.jpg'],
            'specs' => ['Perlengkapan Mahasiswa Baru', 'Kualitas Original USMA', 'Kemasan Eksklusif'],
            'sizes' => ['All Size'],
            'colors' => ['Standar'],
            'description' => 'Paket perlengkapan kebutuhan kuliah mahasiswa Polinema (POLNEEDS) praktis dan ekonomis.'
        ],
        [
            'id' => 6,
            'name' => 'Canvas Totebag & Keychain POLSTUFF',
            'category' => 'Totebag',
            'category_name' => 'Totebag',
            'price' => 75000,
            'dp_price' => 40000,
            'quota_target' => 120,
            'quota_current' => 88,
            'batch' => 'Batch 1',
            'badge' => 'Merch Resmi',
            'images' => ['assets/images/gambar3.jpg'],
            'specs' => ['Kanvas Tebal 12oz dengan Resleting', 'Sablon High Quality', 'Bonus Sticker Pack Vinyl'],
            'sizes' => ['One Size (All Sizes)'],
            'colors' => ['Off-White Canvas', 'Midnight Black'],
            'description' => 'Totebag kanvas tebal 12oz dengan resleting kuat, cocok untuk kuliah dan aktivitas harian.'
        ]
    ];
}

/**
 * Mengambil 1 produk berdasarkan ID
 */
function getProductById($id) {
    $id = (int)$id;
    $all = getProductsList();
    foreach ($all as $item) {
        if ((int)$item['id'] === $id) {
            return $item;
        }
    }

    // Jika produk belum diinput, cek demo template jika diminta
    $demos = getDefaultDemoProducts();
    foreach ($demos as $demo) {
        if ((int)$demo['id'] === $id) {
            return $demo;
        }
    }

    return null;
}

/**
 * Menyimpan produk baru beserta multi-foto
 */
function saveNewProduct($postData, $filesData = null) {
    $pdo = getDbConnection();
    
    $name = sanitize($postData['name'] ?? '');
    $subtitle = sanitize($postData['subtitle'] ?? '');
    $category = sanitize($postData['category'] ?? 'POLSTUFF');
    $categoryNames = getProductCategoriesMap();
    $categoryName = $categoryNames[$category] ?? $category;
    $price = (int)($postData['price'] ?? 0);
    $dpPrice = (int)($postData['dp_price'] ?? round($price * 0.5));
    $quotaTarget = (int)($postData['quota_target'] ?? 100);
    $quotaCurrent = (int)($postData['quota_current'] ?? 0);
    $batch = sanitize($postData['batch'] ?? 'Batch 1');
    $badge = sanitize($postData['badge'] ?? 'PO Baru');
    $desc = sanitize($postData['description'] ?? 'Apparel pre-order berkualitas tinggi dengan standar mutu Fastender.');
    $specs = sanitize($postData['specs'] ?? 'Bahan Standar Distro, Bordir Komputer HD');
    $iconType = sanitize($postData['icon_type'] ?? 'varsity');
    
    // Proses Upload File Multi-Foto
    $savedImages = [];
    
    // 1. Cek unggahan file via PHP $_FILES
    if (!empty($filesData['name']) && is_array($filesData['name'])) {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        
        $totalFiles = count($filesData['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($filesData['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $filesData['tmp_name'][$i];
                $originalName = basename($filesData['name'][$i]);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $newFileName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
                    $targetPath = UPLOAD_DIR . $newFileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $savedImages[] = 'uploads/products/' . $newFileName;
                    }
                }
            }
        }
    }

    // 2. Cek juga jika ada base64 photos dari drag-and-drop / dropzone JS (jika belum terproses dari $_FILES)
    if (empty($savedImages) && !empty($postData['base64_images'])) {
        $b64List = json_decode($postData['base64_images'], true);
        if (is_array($b64List)) {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }
            foreach ($b64List as $b64) {
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                    $b64Data = substr($b64, strpos($b64, ',') + 1);
                    $ext = strtolower($type[1]);
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $decoded = base64_decode($b64Data);
                    if ($decoded !== false) {
                        $newFileName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
                        file_put_contents(UPLOAD_DIR . $newFileName, $decoded);
                        $savedImages[] = 'uploads/products/' . $newFileName;
                    }
                }
            }
        }
    }

    $specsArray = array_values(array_filter(array_map('trim', explode(',', $specs)), function($s) {
        return $s !== '';
    }));

    $newId = time();

    // Simpan ke MySQL jika PDO aktif
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products 
                (name, subtitle, category, category_name, price, dp_price, quota_current, quota_target, badge, batch, description, specs, icon_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $name, $subtitle, $category, $categoryName, $price, $dpPrice, 
                $quotaCurrent, $quotaTarget, $badge, $batch, $desc, implode(', ', $specsArray), $iconType
            ]);
            $newId = (int)$pdo->lastInsertId();

            // Simpan relasi foto
            if (!empty($savedImages)) {
                $imgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                foreach ($savedImages as $idx => $path) {
                    $isPrimary = ($idx === 0) ? 1 : 0;
                    $imgStmt->execute([$newId, $path, $isPrimary]);
                }
            }
        } catch (Exception $e) {
            // fallback simpan ke JSON jika query gagal
        }
    }

    // Selalu simpan juga ke file JSON data/products.json sebagai cache / fallback
    $all = getProductsList();
    $newProduct = [
        'id' => $newId,
        'name' => $name,
        'subtitle' => $subtitle,
        'category' => $category,
        'category_name' => $categoryName,
        'price' => $price,
        'dp_price' => $dpPrice,
        'quota_current' => $quotaCurrent,
        'quota_target' => $quotaTarget,
        'badge' => $badge,
        'batch' => $batch,
        'description' => $desc,
        'specs' => $specsArray,
        'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
        'colors' => ['Navy', 'Hitam', 'Maroon', 'Putih'],
        'icon_type' => $iconType,
        'images' => $savedImages
    ];
    array_unshift($all, $newProduct);
    file_put_contents(DATA_PRODUCTS_FILE, json_encode($all, JSON_PRETTY_PRINT));

    return $newId;
}

/**
 * Memperbarui data produk berdasarkan ID (Mode Edit) beserta penanganan ganti/pertahankan foto
 */
function updateProductById($id, $postData, $filesData = null) {
    $id = (int)$id;
    if ($id <= 0) {
        return false;
    }

    $existingProduct = getProductById($id);
    if (!$existingProduct) {
        return false;
    }

    $pdo = getDbConnection();

    $name = sanitize($postData['name'] ?? ($existingProduct['name'] ?? ''));
    $subtitle = sanitize($postData['subtitle'] ?? ($existingProduct['subtitle'] ?? ''));
    $category = sanitize($postData['category'] ?? ($existingProduct['category'] ?? 'POLSTUFF'));
    $categoryNames = getProductCategoriesMap();
    $categoryName = $categoryNames[$category] ?? $category;
    $price = (int)($postData['price'] ?? ($existingProduct['price'] ?? 0));
    $dpPrice = (int)($postData['dp_price'] ?? round($price * 0.5));
    $quotaTarget = max(1, (int)($postData['quota_target'] ?? ($existingProduct['quota_target'] ?? 100)));
    $quotaCurrent = max(0, (int)($postData['quota_current'] ?? ($existingProduct['quota_current'] ?? 0)));
    $batch = sanitize($postData['batch'] ?? ($existingProduct['batch'] ?? 'Batch 1'));
    $badge = sanitize($postData['badge'] ?? ($existingProduct['badge'] ?? 'PO Baru'));
    $desc = sanitize($postData['description'] ?? ($existingProduct['description'] ?? ''));
    $specsRaw = sanitize($postData['specs'] ?? '');
    $specsArray = array_values(array_filter(array_map('trim', explode(',', $specsRaw)), function($s) {
        return $s !== '';
    }));
    if (empty($specsArray) && !empty($existingProduct['specs'])) {
        $specsArray = is_array($existingProduct['specs'])
            ? $existingProduct['specs']
            : array_map('trim', explode(',', (string)$existingProduct['specs']));
    }
    $iconType = sanitize($postData['icon_type'] ?? ($existingProduct['icon_type'] ?? 'varsity'));

    // Cek apakah admin mengunggah file foto baru
    $newUploadedImages = [];

    // 1. Cek dari $_FILES['product_images']
    if (!empty($filesData['name']) && is_array($filesData['name'])) {
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        $totalFiles = count($filesData['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($filesData['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $filesData['tmp_name'][$i];
                $originalName = basename($filesData['name'][$i]);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                    $newFileName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
                    $targetPath = UPLOAD_DIR . $newFileName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $newUploadedImages[] = 'uploads/products/' . $newFileName;
                    }
                }
            }
        }
    }

    // 2. Cek dari base64_images (jika user drag-and-drop foto baru dan $_FILES kosong)
    if (empty($newUploadedImages) && !empty($postData['base64_images'])) {
        $b64List = json_decode($postData['base64_images'], true);
        if (is_array($b64List)) {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }
            foreach ($b64List as $b64) {
                if (is_string($b64) && preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) {
                    $b64Data = substr($b64, strpos($b64, ',') + 1);
                    $ext = strtolower($type[1]);
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $decoded = base64_decode($b64Data);
                    if ($decoded !== false) {
                        $newFileName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
                        file_put_contents(UPLOAD_DIR . $newFileName, $decoded);
                        $newUploadedImages[] = 'uploads/products/' . $newFileName;
                    }
                }
            }
        }
    }

    $existingImages = is_array($existingProduct['images'] ?? null) ? $existingProduct['images'] : [];

    // Jika ada foto baru yang diunggah, hapus file foto lama di uploads/products/ dan gunakan foto baru.
    // Jika tidak ada foto baru yang diunggah, pertahankan foto lama tanpa menimpanya menjadi kosong/null.
    if (!empty($newUploadedImages)) {
        foreach ($existingImages as $oldImg) {
            $cleanOld = ltrim(str_replace(['..', '\\'], ['', '/'], (string)$oldImg), '/');
            if ($cleanOld !== '' && strpos($cleanOld, 'uploads/products/') === 0) {
                $fullOldPath = __DIR__ . '/../' . $cleanOld;
                if (is_file($fullOldPath)) {
                    @unlink($fullOldPath);
                }
            }
        }
        $finalImages = $newUploadedImages;
    } else {
        $finalImages = $existingImages;
    }

    // Update ke MySQL jika PDO aktif
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                UPDATE products
                SET name = ?, subtitle = ?, category = ?, category_name = ?,
                    price = ?, dp_price = ?, quota_current = ?, quota_target = ?,
                    badge = ?, batch = ?, description = ?, specs = ?, icon_type = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $name, $subtitle, $category, $categoryName,
                $price, $dpPrice, $quotaCurrent, $quotaTarget,
                $badge, $batch, $desc, implode(', ', $specsArray), $iconType,
                $id
            ]);

            if (!empty($newUploadedImages)) {
                $delImgStmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
                $delImgStmt->execute([$id]);

                $insImgStmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                foreach ($finalImages as $idx => $path) {
                    $isPrimary = ($idx === 0) ? 1 : 0;
                    $insImgStmt->execute([$id, $path, $isPrimary]);
                }
            }
        } catch (Exception $e) {
            // Lanjutkan update ke JSON fallback
        }
    }

    // Update ke file JSON data/products.json
    $all = getProductsList();
    $updatedInList = false;
    foreach ($all as &$prodItem) {
        if ((int)($prodItem['id'] ?? 0) === $id) {
            $prodItem['name'] = $name;
            $prodItem['subtitle'] = $subtitle;
            $prodItem['category'] = $category;
            $prodItem['category_name'] = $categoryName;
            $prodItem['price'] = $price;
            $prodItem['dp_price'] = $dpPrice;
            $prodItem['quota_current'] = $quotaCurrent;
            $prodItem['quota_target'] = $quotaTarget;
            $prodItem['badge'] = $badge;
            $prodItem['batch'] = $batch;
            $prodItem['description'] = $desc;
            $prodItem['specs'] = $specsArray;
            $prodItem['icon_type'] = $iconType;
            $prodItem['images'] = $finalImages;
            $updatedInList = true;
            break;
        }
    }
    unset($prodItem);

    if ($updatedInList) {
        if (!is_dir(dirname(DATA_PRODUCTS_FILE))) {
            mkdir(dirname(DATA_PRODUCTS_FILE), 0777, true);
        }
        file_put_contents(DATA_PRODUCTS_FILE, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    return true;
}

/**
 * Hapus produk berdasarkan ID
 */
function deleteProductById($id) {
    $id = (int)$id;
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
        } catch (Exception $e) {}
    }

    $all = getProductsList();
    $filtered = array_values(array_filter($all, function($p) use ($id) {
        return (int)$p['id'] !== $id;
    }));
    file_put_contents(DATA_PRODUCTS_FILE, json_encode($filtered, JSON_PRETTY_PRINT));
}

/**
 * Kosongkan semua produk (Mengaktifkan mode "Space Kosongan")
 */
function clearAllProductsData() {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $pdo->exec("DELETE FROM product_images");
            $pdo->exec("DELETE FROM products");
        } catch (Exception $e) {}
    }
    file_put_contents(DATA_PRODUCTS_FILE, json_encode([], JSON_PRETTY_PRINT));
}

/**
 * Muat produk demo bawaan ke sistem
 */
function loadDemoProductsData() {
    $demos = getDefaultDemoProducts();
    file_put_contents(DATA_PRODUCTS_FILE, json_encode($demos, JSON_PRETTY_PRINT));
}

// ----------------------------------------------------
// 4. DATA TEMPLATE PRODUK DEMO
// ----------------------------------------------------
function getDefaultDemoProducts() {
    return [
        [
            'id' => 1,
            'name' => 'Varsity Jacket Premium Angkatan 2026',
            'subtitle' => 'Custom Bordir Komputer Nama, NIM, & Logo Jurusan',
            'category' => 'jaket',
            'category_name' => 'Jaket & Varsity',
            'price' => 245000,
            'dp_price' => 122500,
            'quota_current' => 82,
            'quota_target' => 100,
            'badge' => 'PO Terpopuler',
            'batch' => 'Batch 2 (Tutup 25 Okt)',
            'description' => 'Varsity jacket berbahan Fleece Cotton tebal 330gsm kombinasi kulit sintetis premium pada lengan. Dilengkapi furing quilting diamond yang hangat dan nyaman, kancing snap metal anti karat, dan bordir komputer kerapatan tinggi.',
            'specs' => ['Bahan Badan: Cotton Fleece 330gsm', 'Bahan Lengan: Oscar Leather Soft Grade A', 'Bordir: 4 Titik Bordir Komputer Full HD', 'Furing: Quilting Diamond Dacron 4oz', 'Estimasi Produksi: 18 - 21 Hari'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'colors' => ['Navy - White', 'Black - Grey', 'Maroon - Cream'],
            'icon_type' => 'varsity',
            'images' => []
        ],
        [
            'id' => 2,
            'name' => 'Kemeja PDH Drill Custom Bordir Logo',
            'subtitle' => 'Bahan American Drill Original 1919 Tebal & Adem',
            'category' => 'pdh',
            'category_name' => 'Kemeja PDH',
            'price' => 135000,
            'dp_price' => 67500,
            'quota_current' => 64,
            'quota_target' => 80,
            'badge' => 'Bordir Rapih',
            'batch' => 'Batch 1 (Tutup 20 Okt)',
            'description' => 'Kemeja PDH/PDL resmi organisasi dan angkatan. Menggunakan bahan American Drill 1919 asli yang tidak berbulu dan adem dipakai seharian. Jahitan rantai ganda kuat standar konveksi profesional.',
            'specs' => ['Bahan: Original American Drill 1919', 'Bordir: Komputer Tajam 3 Titik', 'Jahitan: Rantai Tiga Benang Kuat', 'Kancing: Eksklusif Emboss', 'Estimasi Produksi: 14 - 17 Hari'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
            'colors' => ['Navy Blue', 'Hitam Solid', 'Abu Khaki'],
            'icon_type' => 'shirt',
            'images' => []
        ],
        [
            'id' => 3,
            'name' => 'Hoodie Heavyweight Fleece 330gsm 2026',
            'subtitle' => 'Double Layered Hood dengan Sablon High Density',
            'category' => 'hoodie',
            'category_name' => 'Hoodie & Sweatshirt',
            'price' => 185000,
            'dp_price' => 92500,
            'quota_current' => 92,
            'quota_target' => 100,
            'badge' => 'Sisa 8 Kuota',
            'batch' => 'Batch 3 (Segera Tutup)',
            'description' => 'Hoodie kasual angkatan dengan bahan Cotton Fleece heavyweight tebal tanpa campuran poliester kasar. Bagian dalam lembut, saku kanguru presisi, dan tali hoodie anyaman tebal.',
            'specs' => ['Bahan: 100% Cotton Fleece 330gsm', 'Sablon: Plastisol High Density Timbul', 'Hoodie: Double Layer Tebal', 'Rib: Elastis Spandex Anti Melar', 'Estimasi Produksi: 14 Hari'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],
            'colors' => ['Deep Navy', 'Jet Black', 'Forest Green'],
            'icon_type' => 'hoodie',
            'images' => []
        ],
        [
            'id' => 4,
            'name' => 'T-Shirt Cotton Combed 24s Sablon Plastisol',
            'subtitle' => 'Kaos Makrab & Event Angkatan Nyaman & Sejuk',
            'category' => 'kaos',
            'category_name' => 'Kaos Angkatan',
            'price' => 95000,
            'dp_price' => 47500,
            'quota_current' => 140,
            'quota_target' => 150,
            'badge' => 'Best Seller',
            'batch' => 'Batch 1 (Tutup 30 Okt)',
            'description' => 'Kaos angkatan yang pas untuk kegiatan santai, makrab, atau gathering. Terbuat dari katun combed 24s reaktif gramasi pas, menyerap keringat maksimal, dan disablon dengan tinta plastisol premium.',
            'specs' => ['Bahan: Cotton Combed 24s Reaktif', 'Sablon: Plastisol Curing Oven Glossy', 'Jahitan: Standar Distro Rantai Pundak', 'Model: Reguler Fit Unisex', 'Estimasi Produksi: 10 - 12 Hari'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
            'colors' => ['Biru Navy', 'Hitam', 'Putih', 'Sage Green'],
            'icon_type' => 'tshirt',
            'images' => []
        ]
    ];
}

/**
 * Mengambil daftar produk trending untuk horizontal carousel di beranda (gaya Zellerfeld)
 * Mengambil dari tabel produk database (atau fallback JSON) jika tersedia,
 * dan menyediakan array dinamis produk showcase berfoto asli jika database belum diisi.
 */
function getTrendingProducts() {
    $dbProducts = getProductsList();
    if (!empty($dbProducts)) {
        $result = [];
        $sampleImages = ['assets/images/gambar1.jpg', 'assets/images/gambar2.jpg', 'assets/images/gambar3.jpg'];
        foreach ($dbProducts as $idx => $p) {
            $img = !empty($p['images'][0]) ? $p['images'][0] : (!empty($p['image']) ? $p['image'] : $sampleImages[$idx % count($sampleImages)]);
            $result[] = [
                'id' => $p['id'] ?? ($idx + 1),
                'name' => $p['name'] ?? 'Produk Pre-Order',
                'price' => (int)($p['price'] ?? 150000),
                'image' => $img,
                'category' => $p['category_name'] ?? ($p['category'] ?? 'Apparel'),
                'link' => 'detail.php?id=' . ($p['id'] ?? ($idx + 1))
            ];
        }
        return $result;
    }

    // Array dinamis data produk showcase jika database produk belum diisi
    return [
        [
            'id' => 1,
            'name' => 'Varsity Jacket Angkatan 2026',
            'price' => 245000,
            'image' => 'assets/images/gambar1.jpg',
            'category' => 'Jaket & Varsity',
            'link' => 'detail.php?id=1'
        ],
        [
            'id' => 2,
            'name' => 'Kemeja PDH Drill Bordir Komputer',
            'price' => 135000,
            'image' => 'assets/images/gambar2.jpg',
            'category' => 'Kemeja PDH',
            'link' => 'detail.php?id=2'
        ],
        [
            'id' => 3,
            'name' => 'Heavyweight Hoodie 330gsm 2026',
            'price' => 185000,
            'image' => 'assets/images/gambar3.jpg',
            'category' => 'Hoodie & Sweatshirt',
            'link' => 'detail.php?id=3'
        ],
        [
            'id' => 4,
            'name' => 'T-Shirt Cotton Combed 24s Sablon',
            'price' => 95000,
            'image' => 'assets/images/gambar1.jpg',
            'category' => 'Kaos Angkatan',
            'link' => 'detail.php?id=4'
        ],
        [
            'id' => 5,
            'name' => 'Coach Jacket Windbreaker 2026',
            'price' => 195000,
            'image' => 'assets/images/gambar2.jpg',
            'category' => 'Jaket & Outerwear',
            'link' => 'detail.php?id=1'
        ],
        [
            'id' => 6,
            'name' => 'Polo Shirt Monogram Bordir Timbal',
            'price' => 115000,
            'image' => 'assets/images/gambar3.jpg',
            'category' => 'Polo Shirt',
            'link' => 'detail.php?id=2'
        ]
    ];
}

// ----------------------------------------------------
// 5. PENGELOLAAN PESANAN (ORDERS) & UPLOAD BUKTI PEMBAYARAN
// ----------------------------------------------------
function ensureOrdersTableSchema($pdo) {
    if (!$pdo) return;
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `orders` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `order_code` VARCHAR(50) NOT NULL UNIQUE,
              `customer_name` VARCHAR(150) NOT NULL,
              `customer_phone` VARCHAR(30) NOT NULL,
              `jurusan` VARCHAR(100) DEFAULT '-',
              `customer_email` VARCHAR(100) DEFAULT '-',
              `organization` VARCHAR(150) DEFAULT '-',
              `shipping_address` TEXT NOT NULL,
              `province` VARCHAR(100) NOT NULL,
              `city` VARCHAR(100) NOT NULL,
              `courier` VARCHAR(50) NOT NULL,
              `payment_scheme` ENUM('dp', 'full') DEFAULT 'dp',
              `payment_method` VARCHAR(50) DEFAULT 'BCA',
              `subtotal` INT NOT NULL DEFAULT 0,
              `shipping_cost` INT NOT NULL DEFAULT 0,
              `total_price` INT NOT NULL DEFAULT 0,
              `total_bayar` INT NOT NULL DEFAULT 0,
              `dp_amount` INT NOT NULL DEFAULT 0,
              `bukti_pembayaran` VARCHAR(255) NULL DEFAULT NULL,
              `payment_status` VARCHAR(80) DEFAULT 'Menunggu Verifikasi',
              `production_status` VARCHAR(80) DEFAULT 'Menunggu Konfirmasi Admin',
              `notes` TEXT DEFAULT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `pesanan` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `order_code` VARCHAR(50) NOT NULL UNIQUE,
              `nama_pemesan` VARCHAR(150) NOT NULL,
              `no_hp` VARCHAR(30) NOT NULL,
              `jurusan` VARCHAR(100) NOT NULL DEFAULT '-',
              `alamat_pengiriman` TEXT NOT NULL,
              `provinsi` VARCHAR(100) NOT NULL,
              `kota` VARCHAR(100) NOT NULL,
              `ekspedisi` VARCHAR(50) NOT NULL,
              `skema_pembayaran` ENUM('dp', 'full') DEFAULT 'dp',
              `metode_pembayaran` VARCHAR(50) DEFAULT 'BCA',
              `subtotal` INT NOT NULL DEFAULT 0,
              `ongkir` INT NOT NULL DEFAULT 0,
              `total_harga` INT NOT NULL DEFAULT 0,
              `total_bayar` INT NOT NULL DEFAULT 0,
              `bukti_pembayaran` VARCHAR(255) NULL DEFAULT NULL,
              `status_pesanan` VARCHAR(80) DEFAULT 'Menunggu Konfirmasi Admin',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `order_items` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `order_id` INT NOT NULL,
              `product_id` INT DEFAULT NULL,
              `product_name` VARCHAR(200) NOT NULL,
              `size` VARCHAR(20) NOT NULL,
              `color` VARCHAR(50) NOT NULL,
              `custom_text` VARCHAR(100) DEFAULT NULL,
              `quantity` INT NOT NULL DEFAULT 1,
              `price` INT NOT NULL DEFAULT 0,
              `subtotal` INT NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // Auto-migrate kolom bukti_pembayaran & jurusan jika tabel lama belum memilikinya
        $colCheckOrders = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'bukti_pembayaran'")->fetch();
        if (!$colCheckOrders) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `bukti_pembayaran` VARCHAR(255) NULL AFTER `dp_amount`");
        }
        $colCheckJurusanOrders = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'jurusan'")->fetch();
        if (!$colCheckJurusanOrders) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `jurusan` VARCHAR(100) DEFAULT '-' AFTER `customer_phone`");
        }
        $colCheckTotalBayar = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'total_bayar'")->fetch();
        if (!$colCheckTotalBayar) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `total_bayar` INT NOT NULL DEFAULT 0 AFTER `total_price`");
        }
        $colCheckPayMethod = $pdo->query("SHOW COLUMNS FROM `orders` LIKE 'payment_method'")->fetch();
        if (!$colCheckPayMethod) {
            $pdo->exec("ALTER TABLE `orders` ADD COLUMN `payment_method` VARCHAR(50) DEFAULT 'BCA' AFTER `payment_scheme`");
        }
        $colCheckPesanan = $pdo->query("SHOW COLUMNS FROM `pesanan` LIKE 'bukti_pembayaran'")->fetch();
        if (!$colCheckPesanan) {
            $pdo->exec("ALTER TABLE `pesanan` ADD COLUMN `bukti_pembayaran` VARCHAR(255) NULL AFTER `total_bayar`");
        }
        $colCheckJurusanPesanan = $pdo->query("SHOW COLUMNS FROM `pesanan` LIKE 'jurusan'")->fetch();
        if (!$colCheckJurusanPesanan) {
            $pdo->exec("ALTER TABLE `pesanan` ADD COLUMN `jurusan` VARCHAR(100) NOT NULL DEFAULT '-' AFTER `no_hp`");
        }
    } catch (Exception $e) {
        // Abaikan jika gagal migrasi otomatis
    }
}

/**
 * Memproses checkout baru beserta validasi & penyimpanan bukti pembayaran
 */
function processNewCheckoutOrder($postData, $fileData) {
    // 0a. Validasi Status Operasional Toko (Buka / Tutup)
    if (!isStoreOpen()) {
        $storeSettings = getStoreSettings();
        $msg = !empty($storeSettings['pesan_toko_tutup']) 
            ? $storeSettings['pesan_toko_tutup'] 
            : 'Pre-Order sedang ditutup sementara untuk persiapan produksi batch berikutnya.';
        return [
            'success' => false,
            'message' => $msg
        ];
    }

    // 0b. Validasi Periode Pre-Order (Deadline Countdown)
    if (!isPreOrderActive()) {
        return [
            'success' => false,
            'message' => 'Periode Pre-Order Batch ini telah berakhir. Checkout saat ini ditutup.'
        ];
    }

    // 1. Validasi keberadaan dan error file bukti pembayaran
    if (empty($fileData) || !isset($fileData['error']) || $fileData['error'] === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => false,
            'message' => 'Wajib mengunggah file bukti pembayaran sebelum konfirmasi pesanan.'
        ];
    }

    if ($fileData['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengunggah file bukti pembayaran (Kode Error: ' . (int)$fileData['error'] . ').'
        ];
    }

    // 2. Validasi ukuran file (Maksimal 2MB = 2 * 1024 * 1024 bytes)
    $maxSize = 2 * 1024 * 1024;
    if ($fileData['size'] > $maxSize || $fileData['size'] <= 0) {
        return [
            'success' => false,
            'message' => 'Ukuran file bukti pembayaran maksimal 2MB.'
        ];
    }

    // 3. Validasi ekstensi dan MIME type (jpg, jpeg, png, pdf)
    $originalName = basename($fileData['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($ext, $allowedExts, true)) {
        return [
            'success' => false,
            'message' => 'Format file tidak didukung. Gunakan format JPG, PNG, atau PDF.'
        ];
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if ($mimeType && !in_array($mimeType, $allowedMimes, true)) {
            return [
                'success' => false,
                'message' => 'Tipe konten file tidak valid. Pastikan file adalah gambar JPG/PNG atau dokumen PDF asli.'
            ];
        }
    }

    // 4. Generate kode pesanan & nama file unik: bukti_PO-2026-XXXX_timestamp.ext
    $randomSuffix = random_int(1000, 9999);
    $orderCode = sanitize($postData['order_code'] ?? '') ?: ('PO-2026-' . $randomSuffix);
    $safeOrderCode = preg_replace('/[^A-Za-z0-9\-]/', '', $orderCode);
    $newFileName = 'bukti_' . $safeOrderCode . '_' . time() . '.' . $ext;

    if (!is_dir(UPLOAD_BUKTI_DIR)) {
        mkdir(UPLOAD_BUKTI_DIR, 0777, true);
    }

    $targetPath = UPLOAD_BUKTI_DIR . $newFileName;
    if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
        return [
            'success' => false,
            'message' => 'Gagal menyimpan file bukti pembayaran ke server.'
        ];
    }

    $relativeBuktiPath = 'uploads/bukti_pembayaran/' . $newFileName;

    // 5. Ambil dan sanitasi data form pemesan & item keranjang
    $buyerName = sanitize($postData['buyer_name'] ?? 'Pemesan Fastender');
    $buyerPhone = sanitize($postData['buyer_phone'] ?? '-');
    $jurusan = sanitize($postData['jurusan'] ?? ($postData['buyer_jurusan'] ?? '-'));
    if ($jurusan === '') {
        $jurusan = '-';
    }

    $isPickupUsma = !empty($postData['is_pickup_usma']) || ((string)($postData['ship_courier'] ?? '') === '0');
    $shippingCost = max(0, (int)($postData['ship_courier'] ?? 0));

    $province = sanitize($postData['ship_province'] ?? '');
    $city = sanitize($postData['ship_city'] ?? '');
    $address = sanitize($postData['ship_address'] ?? '');
    $postal = sanitize($postData['ship_postal'] ?? '');

    if ($isPickupUsma) {
        $province = ($province === '' || $province === '-') ? '-' : $province;
        $city = ($city === '' || $city === '-') ? '-' : $city;
        $fullAddress = 'Ambil di Sekretariat USMA (Gedung UKM Polinema)';
        $courierName = 'Ambil di Sekretariat / Kampus (Gratis Ongkir)';
        $shippingCost = 0;
    } else {
        if ($province === '') $province = '-';
        if ($city === '') $city = '-';
        if ($address === '') $address = '-';
        $fullAddress = ($postal !== '' && $postal !== '-') ? ($address . ' (Kode Pos: ' . $postal . ')') : $address;
        $courierName = 'Ekspedisi (' . formatRupiah($shippingCost) . ')';
    }

    $paymentScheme = (($postData['payment_scheme'] ?? 'dp') === 'full') ? 'full' : 'dp';
    $paymentMethod = sanitize($postData['pay_method'] ?? 'BCA');

    $rawCart = $postData['cart_items'] ?? '[]';
    $cartItems = is_string($rawCart) ? json_decode($rawCart, true) : $rawCart;
    if (!is_array($cartItems)) {
        $cartItems = [];
    }

    $subtotal = 0;
    $dpTotal = 0;
    $totalQty = 0;
    $normalizedItems = [];

    foreach ($cartItems as $item) {
        $pId = (int)($item['id'] ?? 1);
        $pName = sanitize($item['name'] ?? 'Produk Pre-Order Fastender');
        if ($pName === '' || $pName === 'undefined') {
            $pName = 'Produk Pre-Order Fastender';
        }
        $pPrice = max(0, (int)preg_replace('/[^0-9]/', '', (string)($item['price'] ?? 0)));
        $pDp = max(0, (int)preg_replace('/[^0-9]/', '', (string)($item['dpPrice'] ?? ($item['dp_price'] ?? round($pPrice * 0.5)))));
        if ($pDp <= 0 && $pPrice > 0) {
            $pDp = (int)round($pPrice * 0.5);
        }
        $pQty = max(1, (int)($item['quantity'] ?? ($item['qty'] ?? 1)));
        $pSize = sanitize($item['size'] ?? 'L');
        $pColor = sanitize($item['color'] ?? 'Standar');
        $pCustom = sanitize($item['customText'] ?? '');
        $itemSub = $pPrice * $pQty;

        $subtotal += $itemSub;
        $dpTotal += ($pDp * $pQty);
        $totalQty += $pQty;

        $normalizedItems[] = [
            'id' => $pId,
            'name' => $pName,
            'size' => $pSize,
            'color' => $pColor,
            'customText' => $pCustom,
            'quantity' => $pQty,
            'price' => $pPrice,
            'dpPrice' => $pDp,
            'subtotal' => $itemSub,
            'image' => $item['image'] ?? null
        ];
    }

    // 5b. Validasi Limit Kuota Produk Pre-Order (Cegah jika kuota penuh atau melebihi batas)
    foreach ($normalizedItems as $nItem) {
        $pId = (int)$nItem['id'];
        $prod = getProductById($pId);
        $quotaInfo = getProductQuotaInfo($prod ?: $pId);
        if ($quotaInfo['is_full']) {
            return [
                'success' => false,
                'message' => 'Pesanan gagal diproses karena kuota produk "' . $nItem['name'] . '" telah habis.'
            ];
        }
        if ($quotaInfo['target'] > 0 && ($quotaInfo['current'] + $nItem['quantity']) > $quotaInfo['target']) {
            return [
                'success' => false,
                'message' => 'Pesanan gagal diproses karena kuota produk "' . $nItem['name'] . '" telah habis atau tidak mencukupi (Sisa kuota: ' . $quotaInfo['remaining'] . ' pcs).'
            ];
        }
    }

    if ($subtotal <= 0 && !empty($postData['subtotal'])) {
        $subtotal = max(0, (int)$postData['subtotal']);
        $dpTotal = (int)round($subtotal * 0.5);
    }

    $totalPrice = $subtotal + $shippingCost;
    $dueAmount = ($paymentScheme === 'dp') ? ($dpTotal + $shippingCost) : $totalPrice;

    $paymentStatus = 'Menunggu Verifikasi';
    $productionStatus = 'Menunggu Konfirmasi Admin';

    $firstItemSummary = !empty($normalizedItems)
        ? ($normalizedItems[0]['name'] . ' (' . $normalizedItems[0]['size'] . ', ' . $normalizedItems[0]['color'] . ')')
        : 'Produk Pre-Order';

    // 6. Simpan ke Database MySQL (orders, order_items, dan pesanan) jika koneksi tersedia
    $pdo = getDbConnection();
    if ($pdo) {
        ensureOrdersTableSchema($pdo);
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `orders`
                (`order_code`, `customer_name`, `customer_phone`, `jurusan`, `customer_email`, `organization`,
                 `shipping_address`, `province`, `city`, `courier`, `payment_scheme`, `payment_method`,
                 `subtotal`, `shipping_cost`, `total_price`, `total_bayar`, `dp_amount`,
                 `bukti_pembayaran`, `payment_status`, `production_status`)
                VALUES (?, ?, ?, ?, '-', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderCode,
                $buyerName,
                $buyerPhone,
                $jurusan,
                $jurusan,
                $fullAddress,
                $province,
                $city,
                $courierName,
                $paymentScheme,
                $paymentMethod,
                $subtotal,
                $shippingCost,
                $totalPrice,
                $dueAmount,
                $dpTotal,
                $relativeBuktiPath,
                $paymentStatus,
                $productionStatus
            ]);
            $insertedOrderId = (int)$pdo->lastInsertId();

            if ($insertedOrderId > 0 && !empty($normalizedItems)) {
                $itemStmt = $pdo->prepare("
                    INSERT INTO `order_items`
                    (`order_id`, `product_id`, `product_name`, `size`, `color`, `custom_text`, `quantity`, `price`, `subtotal`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $updQuota = $pdo->prepare("UPDATE `products` SET `quota_current` = `quota_current` + ? WHERE `id` = ?");
                foreach ($normalizedItems as $nItem) {
                    $itemStmt->execute([
                        $insertedOrderId,
                        $nItem['id'],
                        $nItem['name'],
                        $nItem['size'],
                        $nItem['color'],
                        $nItem['customText'],
                        $nItem['quantity'],
                        $nItem['price'],
                        $nItem['subtotal']
                    ]);
                    try {
                        $updQuota->execute([(int)$nItem['quantity'], (int)$nItem['id']]);
                    } catch (Exception $e) {}
                }
            }

            // Simpan juga ke tabel `pesanan` sesuai skema migrasi
            $stmtPesanan = $pdo->prepare("
                INSERT INTO `pesanan`
                (`order_code`, `nama_pemesan`, `no_hp`, `jurusan`, `alamat_pengiriman`, `provinsi`, `kota`,
                 `ekspedisi`, `skema_pembayaran`, `metode_pembayaran`, `subtotal`, `ongkir`,
                 `total_harga`, `total_bayar`, `bukti_pembayaran`, `status_pesanan`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtPesanan->execute([
                $orderCode,
                $buyerName,
                $buyerPhone,
                $jurusan,
                $fullAddress,
                $province,
                $city,
                $courierName,
                $paymentScheme,
                $paymentMethod,
                $subtotal,
                $shippingCost,
                $totalPrice,
                $dueAmount,
                $relativeBuktiPath,
                $productionStatus
            ]);
        } catch (Exception $e) {
            // Lanjut simpan ke JSON fallback jika DB error
        }
    }

    // 7. Simpan juga ke file JSON data/orders.json agar selalu persisten
    $orderRecord = [
        'order_code' => $orderCode,
        'orderId' => $orderCode,
        'date' => date('d M Y'),
        'customer_name' => $buyerName,
        'customerName' => $buyerName,
        'customer_phone' => $buyerPhone,
        'customerPhone' => $buyerPhone,
        'jurusan' => $jurusan,
        'customer_email' => '-',
        'organization' => $jurusan,
        'org' => $jurusan,
        'province' => $province,
        'city' => $city,
        'address' => $fullAddress,
        'shipping_address' => $fullAddress,
        'courier' => $courierName,
        'payment_scheme' => $paymentScheme,
        'paymentScheme' => $paymentScheme,
        'payment_method' => $paymentMethod,
        'product_summary' => $firstItemSummary,
        'qty' => max(1, $totalQty),
        'items' => $normalizedItems,
        'subtotal' => $subtotal,
        'shipping' => $shippingCost,
        'shipping_cost' => $shippingCost,
        'total_price' => $totalPrice,
        'total' => $totalPrice,
        'total_bayar' => $dueAmount,
        'due' => $dueAmount,
        'dp_amount' => $dpTotal,
        'bukti_pembayaran' => $relativeBuktiPath,
        'payment_status' => $paymentStatus,
        'paymentStatus' => $paymentStatus,
        'production_status' => $productionStatus,
        'productionStatus' => $productionStatus,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $existingOrders = [];
    if (file_exists(DATA_ORDERS_FILE)) {
        $rawOrders = file_get_contents(DATA_ORDERS_FILE);
        $decodedOrders = json_decode($rawOrders, true);
        if (is_array($decodedOrders)) {
            $existingOrders = $decodedOrders;
        }
    } else {
        $existingOrders = getAllOrdersList();
    }

    if (!is_dir(dirname(DATA_ORDERS_FILE))) {
        mkdir(dirname(DATA_ORDERS_FILE), 0777, true);
    }
    array_unshift($existingOrders, $orderRecord);
    file_put_contents(DATA_ORDERS_FILE, json_encode($existingOrders, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return [
        'success' => true,
        'message' => 'Pesanan dan bukti pembayaran berhasil diterima! Menunggu verifikasi admin.',
        'order' => $orderRecord
    ];
}

function getAllOrdersList() {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
            $orders = $stmt->fetchAll();
            if (!empty($orders)) {
                return $orders;
            }
        } catch (Exception $e) {}
    }

    if (file_exists(DATA_ORDERS_FILE)) {
        $json = file_get_contents(DATA_ORDERS_FILE);
        $data = json_decode($json, true);
        if (is_array($data)) return $data;
    }

    // Default sample orders
    return [
        [
            'order_code' => 'PO-2026-8891',
            'customer_name' => 'Ahmad Fauzi',
            'organization' => 'Himpunan Informatika',
            'customer_phone' => '081234567890',
            'customer_email' => 'fauzi@example.com',
            'date' => '12 Okt 2026',
            'product_summary' => 'Varsity Jacket Angkatan (L, Navy)',
            'qty' => 24,
            'total_price' => 5895000,
            'payment_status' => 'DP 50% Lunas',
            'production_status' => 'Sedang Produksi'
        ],
        [
            'order_code' => 'PO-2026-1045',
            'customer_name' => 'Siti Rahma',
            'organization' => 'BEM FT Undip',
            'customer_phone' => '081398765432',
            'customer_email' => 'siti@example.com',
            'date' => '15 Okt 2026',
            'product_summary' => 'Kemeja PDH Drill 1919 (M, Navy)',
            'qty' => 20,
            'total_price' => 2715000,
            'payment_status' => 'Lunas 100%',
            'production_status' => 'Dalam Pengiriman'
        ],
        [
            'order_code' => 'PO-2026-7734',
            'customer_name' => 'Dewi Lestari',
            'organization' => 'Manajemen Bisnis',
            'customer_phone' => '085712345678',
            'customer_email' => 'dewi@example.com',
            'date' => '17 Okt 2026',
            'product_summary' => 'Tote Bag Canvas + Pin (3 pcs)',
            'qty' => 3,
            'total_price' => 180000,
            'payment_status' => 'Lunas 100%',
            'production_status' => 'Quality Check'
        ],
        [
            'order_code' => 'PO-2026-4412',
            'customer_name' => 'Rian Hidayat',
            'organization' => 'Teknik Sipil 2026',
            'customer_phone' => '082187654321',
            'customer_email' => 'rian@example.com',
            'date' => '18 Okt 2026',
            'product_summary' => 'T-Shirt Combed 24s (L, Navy)',
            'qty' => 12,
            'total_price' => 1155000,
            'payment_status' => 'DP 50% Lunas',
            'production_status' => 'Sedang Produksi'
        ],
        [
            'order_code' => 'PO-2026-3390',
            'customer_name' => 'Budi Santoso',
            'organization' => 'Teknik Mesin',
            'customer_phone' => '081912349999',
            'customer_email' => 'budi@example.com',
            'date' => '20 Okt 2026',
            'product_summary' => 'Hoodie Heavyweight Fleece (XXL)',
            'qty' => 1,
            'total_price' => 200000,
            'payment_status' => 'Lunas 100%',
            'production_status' => 'Selesai'
        ]
    ];
}

/**
 * Mencari detail pesanan & riwayat timeline berdasarkan nomor resi dari database / file orders.json
 */
function findOrderByResi($resi) {
    $resi = trim((string)$resi);
    if ($resi === '') {
        return null;
    }

    $foundRow = null;
    $orderItems = [];

    // 1. Cari di database MySQL (tabel orders / pesanan)
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE UPPER(order_code) = UPPER(:resi) LIMIT 1");
            $stmt->execute([':resi' => $resi]);
            $row = $stmt->fetch();
            if ($row) {
                $foundRow = $row;
                if (!empty($row['id'])) {
                    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
                    $stmtItems->execute([(int)$row['id']]);
                    $orderItems = $stmtItems->fetchAll();
                }
            } else {
                $stmtP = $pdo->prepare("SELECT * FROM pesanan WHERE UPPER(order_code) = UPPER(:resi) LIMIT 1");
                $stmtP->execute([':resi' => $resi]);
                $rowP = $stmtP->fetch();
                if ($rowP) {
                    $foundRow = [
                        'order_code' => $rowP['order_code'],
                        'customer_name' => $rowP['nama_pemesan'] ?? '-',
                        'jurusan' => $rowP['jurusan'] ?? '-',
                        'organization' => $rowP['jurusan'] ?? '-',
                        'shipping_address' => $rowP['alamat_pengiriman'] ?? '-',
                        'province' => $rowP['provinsi'] ?? '-',
                        'city' => $rowP['kota'] ?? '-',
                        'courier' => $rowP['ekspedisi'] ?? '-',
                        'payment_scheme' => $rowP['skema_pembayaran'] ?? 'dp',
                        'payment_status' => ($rowP['skema_pembayaran'] ?? 'dp') === 'full' ? 'Lunas 100%' : 'DP 50%',
                        'production_status' => $rowP['status_pesanan'] ?? 'Menunggu Konfirmasi Admin',
                        'created_at' => $rowP['created_at'] ?? date('Y-m-d H:i:s')
                    ];
                }
            }
        } catch (Exception $e) {
            // Lanjut cek JSON storage
        }
    }

    // 2. Jika belum ditemukan di MySQL, cek di file JSON data/orders.json
    if (!$foundRow && file_exists(DATA_ORDERS_FILE)) {
        $rawJson = file_get_contents(DATA_ORDERS_FILE);
        $decoded = json_decode($rawJson, true);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $itemCode = trim((string)($item['order_code'] ?? ($item['orderId'] ?? '')));
                if ($itemCode !== '' && strcasecmp($itemCode, $resi) === 0) {
                    $foundRow = $item;
                    if (!empty($item['items']) && is_array($item['items'])) {
                        $orderItems = $item['items'];
                    }
                    break;
                }
            }
        }
    }

    if (!$foundRow) {
        return null;
    }

    $orderCode = (string)($foundRow['order_code'] ?? ($foundRow['orderId'] ?? strtoupper($resi)));
    $customerName = (string)($foundRow['customer_name'] ?? ($foundRow['customerName'] ?? '-'));
    $org = (string)($foundRow['jurusan'] ?? ($foundRow['organization'] ?? ($foundRow['org'] ?? '-')));
    if ($org === '') {
        $org = '-';
    }

    $productName = 'Produk Pre-Order';
    $productMeta = '1 pcs';
    if (!empty($orderItems)) {
        $first = $orderItems[0];
        $productName = (string)($first['product_name'] ?? ($first['name'] ?? 'Produk Pre-Order'));
        $size = (string)($first['size'] ?? 'L');
        $color = (string)($first['color'] ?? 'Standar');
        $totalQty = 0;
        foreach ($orderItems as $it) {
            $totalQty += (int)($it['quantity'] ?? 1);
        }
        $extraCount = count($orderItems) > 1 ? ' (+' . (count($orderItems) - 1) . ' item lainnya)' : '';
        $productMeta = "Ukuran {$size}, Warna {$color} ({$totalQty} pcs){$extraCount}";
    } elseif (!empty($foundRow['product_summary'])) {
        $productName = (string)$foundRow['product_summary'];
        $qty = (int)($foundRow['qty'] ?? 1);
        $productMeta = "{$qty} pcs pesanan";
    }

    $city = trim((string)($foundRow['city'] ?? ''));
    $province = trim((string)($foundRow['province'] ?? ''));
    $shippingAddress = trim((string)($foundRow['shipping_address'] ?? ($foundRow['address'] ?? '')));
    if (stripos($shippingAddress, 'Sekretariat USMA') !== false || ($city === '-' && $province === '-')) {
        $destination = 'Ambil di Sekretariat USMA';
    } elseif ($city !== '' && $city !== '-' && $province !== '' && $province !== '-') {
        $destination = $city . ', ' . $province;
    } elseif ($city !== '' && $city !== '-') {
        $destination = $city;
    } else {
        $destination = $shippingAddress !== '' ? $shippingAddress : '-';
    }

    $paymentStatus = (string)($foundRow['payment_status'] ?? ($foundRow['paymentStatus'] ?? 'Menunggu Verifikasi'));
    $productionStatus = (string)($foundRow['production_status'] ?? ($foundRow['productionStatus'] ?? 'Menunggu Konfirmasi Admin'));
    $orderDate = !empty($foundRow['date'])
        ? (string)$foundRow['date']
        : (!empty($foundRow['created_at']) ? date('d M Y, H:i', strtotime($foundRow['created_at'])) . ' WIB' : date('d M Y'));

    // Tentukan tahapan aktif berdasarkan status produksi
    $prodLower = strtolower($productionStatus);
    $currentStep = 1;
    $estimatedDelivery = '14 - 21 Hari Kerja';
    if (strpos($prodLower, 'selesai') !== false) {
        $currentStep = 7; // Semua 6 langkah selesai
        $estimatedDelivery = 'Pesanan Selesai Diterima';
    } elseif (strpos($prodLower, 'pengiriman') !== false || strpos($prodLower, 'dikirim') !== false) {
        $currentStep = 5;
        $estimatedDelivery = 'Dalam Perjalanan Kurir';
    } elseif (strpos($prodLower, 'quality') !== false || strpos($prodLower, 'qc') !== false) {
        $currentStep = 4;
        $estimatedDelivery = 'Segera Dikirim';
    } elseif (strpos($prodLower, 'produksi') !== false) {
        $currentStep = 3;
        $estimatedDelivery = 'Estimasi 14 - 21 Hari';
    } elseif (strpos($prodLower, 'kuota') !== false) {
        $currentStep = 2;
        $estimatedDelivery = 'Menunggu Penutupan Batch';
    } else {
        $currentStep = 1;
    }

    $rawSteps = [
        [
            'title' => 'Pesanan Masuk & Verifikasi Pembayaran',
            'desc' => 'Status pembayaran: ' . $paymentStatus . '.',
            'time' => $orderDate
        ],
        [
            'title' => 'Penutupan Kuota Batch PO',
            'desc' => 'Pengumpulan kuota pre-order batch angkatan dan penerbitan SPK produksi.',
            'time' => $currentStep > 2 ? 'Terverifikasi' : ($currentStep === 2 ? 'Sedang Berlangsung' : 'Menunggu')
        ],
        [
            'title' => 'Sedang Produksi Konveksi',
            'desc' => 'Pemotongan bahan, penjahitan standar konveksi, dan proses bordir/sablon.',
            'time' => $currentStep > 3 ? 'Selesai Produksi' : ($currentStep === 3 ? 'Sedang Dikerjakan' : 'Menunggu')
        ],
        [
            'title' => 'Quality Check & Steam Ironing',
            'desc' => 'Pemeriksaan kerapihan jahitan, detail bordir/sablon, dan kesesuaian ukuran.',
            'time' => $currentStep > 4 ? 'Lolos QC' : ($currentStep === 4 ? 'Proses Pemeriksaan' : 'Menunggu')
        ],
        [
            'title' => 'Packing & Pengiriman / Pengambilan',
            'desc' => 'Pengemasan pesanan dan penyerahan ke kurir ekspedisi atau Sekretariat USMA.',
            'time' => $currentStep > 5 ? 'Terkirim' : ($currentStep === 5 ? 'Dalam Pengiriman' : 'Menunggu')
        ],
        [
            'title' => 'Pesanan Selesai Diterima',
            'desc' => 'Pesanan telah diterima dengan baik oleh pemesan.',
            'time' => $currentStep >= 6 ? 'Selesai' : 'Menunggu'
        ]
    ];

    $timeline = [];
    foreach ($rawSteps as $index => $stepItem) {
        $stepNum = $index + 1;
        $isDone = ($stepNum < $currentStep) || ($currentStep === 7);
        $isCurrent = ($stepNum === $currentStep && $currentStep <= 6);
        $isPending = !$isDone && !$isCurrent;
        $timeline[] = [
            'title' => $stepItem['title'],
            'desc' => $stepItem['desc'],
            'time' => $stepItem['time'],
            'done' => $isDone,
            'current' => $isCurrent,
            'pending' => $isPending
        ];
    }

    return [
        'order_code' => $orderCode,
        'customer_name' => $customerName,
        'organization' => $org,
        'product_name' => $productName,
        'product_meta' => $productMeta,
        'destination' => $destination,
        'estimated_delivery' => $estimatedDelivery,
        'payment_status' => $paymentStatus,
        'production_status' => $productionStatus,
        'timeline' => $timeline
    ];
}

/**
 * Menghapus pesanan berdasarkan kode resi / ID pesanan beserta file bukti pembayarannya
 */
function deleteOrderByCode($orderCode) {
    $orderCode = trim((string)$orderCode);
    if ($orderCode === '') {
        return false;
    }

    $proofPathsToDelete = [];

    // 1. Hapus dari MySQL jika koneksi tersedia
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmtFind = $pdo->prepare("SELECT id, bukti_pembayaran FROM orders WHERE order_code = ? OR id = ?");
            $stmtFind->execute([$orderCode, $orderCode]);
            while ($row = $stmtFind->fetch()) {
                if (!empty($row['bukti_pembayaran'])) {
                    $proofPathsToDelete[] = $row['bukti_pembayaran'];
                }
                if (!empty($row['id'])) {
                    $delItems = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                    $delItems->execute([(int)$row['id']]);
                }
            }

            $stmtDelOrders = $pdo->prepare("DELETE FROM orders WHERE order_code = ? OR id = ?");
            $stmtDelOrders->execute([$orderCode, $orderCode]);

            $stmtFindPesanan = $pdo->prepare("SELECT bukti_pembayaran FROM pesanan WHERE order_code = ? OR id = ?");
            $stmtFindPesanan->execute([$orderCode, $orderCode]);
            while ($rowP = $stmtFindPesanan->fetch()) {
                if (!empty($rowP['bukti_pembayaran'])) {
                    $proofPathsToDelete[] = $rowP['bukti_pembayaran'];
                }
            }

            $stmtDelPesanan = $pdo->prepare("DELETE FROM pesanan WHERE order_code = ? OR id = ?");
            $stmtDelPesanan->execute([$orderCode, $orderCode]);
        } catch (Exception $e) {
            // Lanjutkan penghapusan pada JSON fallback
        }
    }

    // 2. Hapus dari file JSON data/orders.json
    $currentList = [];
    if (file_exists(DATA_ORDERS_FILE)) {
        $raw = file_get_contents(DATA_ORDERS_FILE);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $currentList = $decoded;
        }
    } else {
        $currentList = getAllOrdersList();
    }

    $filteredList = [];
    foreach ($currentList as $item) {
        $itemCode = (string)($item['order_code'] ?? ($item['orderId'] ?? ($item['id'] ?? '')));
        if ($itemCode === $orderCode) {
            if (!empty($item['bukti_pembayaran'])) {
                $proofPathsToDelete[] = $item['bukti_pembayaran'];
            }
            continue;
        }
        $filteredList[] = $item;
    }

    if (!is_dir(dirname(DATA_ORDERS_FILE))) {
        mkdir(dirname(DATA_ORDERS_FILE), 0777, true);
    }
    file_put_contents(DATA_ORDERS_FILE, json_encode(array_values($filteredList), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    // 3. Hapus file fisik bukti pembayaran jika ada di folder uploads/bukti_pembayaran/
    $proofPathsToDelete = array_unique($proofPathsToDelete);
    foreach ($proofPathsToDelete as $relPath) {
        $cleanRel = ltrim(str_replace(['..', '\\'], ['', '/'], (string)$relPath), '/');
        if ($cleanRel !== '' && strpos($cleanRel, 'uploads/bukti_pembayaran/') === 0) {
            $fullFilePath = __DIR__ . '/../' . $cleanRel;
            if (is_file($fullFilePath)) {
                @unlink($fullFilePath);
            }
        }
    }

    return true;
}

/**
 * Memperbarui status pembayaran & status produksi pesanan di DB dan JSON
 */
function updateOrderStatusByCode($orderCode, $paymentStatus, $productionStatus) {
    $orderCode = trim((string)$orderCode);
    $paymentStatus = sanitize($paymentStatus);
    $productionStatus = sanitize($productionStatus);
    if ($orderCode === '') {
        return false;
    }

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET payment_status = ?, production_status = ? WHERE order_code = ?");
            $stmt->execute([$paymentStatus, $productionStatus, $orderCode]);

            $stmtP = $pdo->prepare("UPDATE pesanan SET status_pesanan = ? WHERE order_code = ?");
            $stmtP->execute([$productionStatus, $orderCode]);
        } catch (Exception $e) {}
    }

    $currentList = [];
    if (file_exists(DATA_ORDERS_FILE)) {
        $raw = file_get_contents(DATA_ORDERS_FILE);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $currentList = $decoded;
        }
    } else {
        $currentList = getAllOrdersList();
    }

    foreach ($currentList as &$item) {
        $itemCode = (string)($item['order_code'] ?? ($item['orderId'] ?? ''));
        if ($itemCode === $orderCode) {
            $item['payment_status'] = $paymentStatus;
            $item['paymentStatus'] = $paymentStatus;
            $item['production_status'] = $productionStatus;
            $item['productionStatus'] = $productionStatus;
        }
    }
    unset($item);

    if (!is_dir(dirname(DATA_ORDERS_FILE))) {
        mkdir(dirname(DATA_ORDERS_FILE), 0777, true);
    }
    file_put_contents(DATA_ORDERS_FILE, json_encode($currentList, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return true;
}

define('DATA_DASHBOARD_STATS_FILE', __DIR__ . '/../data/dashboard_stats.json');

/**
 * Mengambil metrik statistik dashboard secara dinamis dari pesanan & konfigurasi statistik
 */
function getDashboardStats($orders = null) {
    if ($orders === null) {
        $orders = getAllOrdersList();
    }

    $statsConfig = [
        'is_reset' => false,
        'base_orders' => 1420,
        'base_revenue' => 184500000,
        'base_production_pcs' => 540,
        'base_completed_pcs' => 880
    ];

    // Cek dari file JSON data/dashboard_stats.json
    if (file_exists(DATA_DASHBOARD_STATS_FILE)) {
        $raw = file_get_contents(DATA_DASHBOARD_STATS_FILE);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $statsConfig = array_merge($statsConfig, $decoded);
        }
    }

    // Cek juga dari tabel site_settings jika MySQL aktif
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'stats_%'");
            if ($stmt) {
                $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                if (!empty($rows) && isset($rows['stats_is_reset']) && $rows['stats_is_reset'] === '1') {
                    $statsConfig['is_reset'] = true;
                    $statsConfig['base_orders'] = (int)($rows['stats_base_orders'] ?? 0);
                    $statsConfig['base_revenue'] = (int)($rows['stats_base_revenue'] ?? 0);
                    $statsConfig['base_production_pcs'] = (int)($rows['stats_base_production_pcs'] ?? 0);
                    $statsConfig['base_completed_pcs'] = (int)($rows['stats_base_completed_pcs'] ?? 0);
                }
            }
        } catch (Exception $e) {}
    }

    $totalOrders = (int)$statsConfig['base_orders'] + count($orders);
    $totalRevenue = (int)$statsConfig['base_revenue'];
    $dynamicProductionPcs = 0;
    $dynamicCompletedPcs = 0;

    foreach ($orders as $ord) {
        $totalRevenue += (int)($ord['total_price'] ?? ($ord['total'] ?? 0));

        $qty = 0;
        if (isset($ord['qty']) && (int)$ord['qty'] > 0) {
            $qty = (int)$ord['qty'];
        } elseif (!empty($ord['items']) && is_array($ord['items'])) {
            foreach ($ord['items'] as $it) {
                $qty += max(1, (int)($it['quantity'] ?? ($it['qty'] ?? 1)));
            }
        } else {
            $qty = 1;
        }

        $status = strtolower(trim((string)($ord['production_status'] ?? ($ord['productionStatus'] ?? ''))));
        if (in_array($status, ['selesai', 'dalam pengiriman', 'terkirim', 'completed', 'shipped'], true)) {
            $dynamicCompletedPcs += $qty;
        } elseif ($status !== 'dibatalkan' && $status !== 'cancel') {
            $dynamicProductionPcs += $qty;
        }
    }

    if (!empty($statsConfig['is_reset'])) {
        $inProductionPcs = (int)$statsConfig['base_production_pcs'] + $dynamicProductionPcs;
        $completedPcs = (int)$statsConfig['base_completed_pcs'] + $dynamicCompletedPcs;
    } else {
        $inProductionPcs = (int)$statsConfig['base_production_pcs'];
        $completedPcs = (int)$statsConfig['base_completed_pcs'];
    }

    return [
        'is_reset' => (bool)$statsConfig['is_reset'],
        'base_orders' => (int)$statsConfig['base_orders'],
        'base_revenue' => (int)$statsConfig['base_revenue'],
        'base_production_pcs' => (int)$statsConfig['base_production_pcs'],
        'base_completed_pcs' => (int)$statsConfig['base_completed_pcs'],
        'total_orders' => $totalOrders,
        'total_revenue' => $totalRevenue,
        'in_production_pcs' => $inProductionPcs,
        'completed_pcs' => $completedPcs
    ];
}

/**
 * Mereset seluruh data statistik pre-order & mengosongkan transaksi menjadi 0
 */
function resetAllPreOrderStatistics() {
    // 1. Bersihkan tabel transaksi di database MySQL & simpan flag reset di site_settings
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $pdo->exec("DELETE FROM order_items");
            $pdo->exec("DELETE FROM orders");
            $pdo->exec("DELETE FROM pesanan");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `site_settings` (
                    `setting_key` VARCHAR(100) PRIMARY KEY,
                    `setting_value` TEXT,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $stmt = $pdo->prepare("
                INSERT INTO site_settings (setting_key, setting_value)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $resetSettings = [
                'stats_is_reset' => '1',
                'stats_base_orders' => '0',
                'stats_base_revenue' => '0',
                'stats_base_production_pcs' => '0',
                'stats_base_completed_pcs' => '0'
            ];
            foreach ($resetSettings as $k => $v) {
                $stmt->execute([$k, $v]);
            }
        } catch (Exception $e) {
            // Lanjutkan reset pada file JSON
        }
    }

    // 2. Kosongkan file data/orders.json menjadi array kosong []
    if (!is_dir(dirname(DATA_ORDERS_FILE))) {
        mkdir(dirname(DATA_ORDERS_FILE), 0777, true);
    }
    file_put_contents(DATA_ORDERS_FILE, json_encode([], JSON_PRETTY_PRINT));

    // 3. Simpan state statistik 0 ke data/dashboard_stats.json
    $resetData = [
        'is_reset' => true,
        'base_orders' => 0,
        'base_revenue' => 0,
        'base_production_pcs' => 0,
        'base_completed_pcs' => 0,
        'reset_at' => date('Y-m-d H:i:s')
    ];
    file_put_contents(DATA_DASHBOARD_STATS_FILE, json_encode($resetData, JSON_PRETTY_PRINT));

    return true;
}

// ----------------------------------------------------
// 6. SVG MOCKUP HELPER (PHP RENDER)
// ----------------------------------------------------
function getProductSvgPhp($type) {
    switch ($type) {
        case 'varsity':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="160" height="160" rx="10" fill="#ecedee"/>
              <path d="M45 42L80 56L115 42L135 68L120 82L112 74V130H48V74L40 82L25 68L45 42Z" fill="#111111"/>
              <path d="M45 42L25 68L40 82L48 74V60L45 42Z" fill="#000aff"/>
              <path d="M115 42L135 68L120 82L112 74V60L115 42Z" fill="#000aff"/>
              <path d="M78 55V130H82V55H78Z" fill="#ffffff" opacity="0.4"/>
              <circle cx="80" cy="75" r="2.5" fill="#000aff"/>
              <circle cx="80" cy="90" r="2.5" fill="#000aff"/>
              <circle cx="80" cy="105" r="2.5" fill="#000aff"/>
              <circle cx="80" cy="120" r="2.5" fill="#000aff"/>
              <text x="60" y="80" font-family="Montserrat, sans-serif" font-weight="700" font-size="13" fill="#ffffff">26</text>
            </svg>';
        case 'shirt':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="160" height="160" rx="10" fill="#ecedee"/>
              <path d="M48 38L80 48L112 38L130 62L116 74L108 68V132H52V68L44 74L30 62L48 38Z" fill="#111111"/>
              <path d="M68 38L80 54L92 38H68Z" fill="#ecedee"/>
              <rect x="58" y="72" width="16" height="18" rx="2" fill="#2b2b2b"/>
              <circle cx="80" cy="70" r="2" fill="#a1a4aa"/>
              <circle cx="80" cy="85" r="2" fill="#a1a4aa"/>
              <circle cx="80" cy="100" r="2" fill="#a1a4aa"/>
              <circle cx="80" cy="115" r="2" fill="#a1a4aa"/>
              <path d="M79 48V132H81V48H79Z" fill="#ffffff" opacity="0.3"/>
            </svg>';
        case 'hoodie':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="160" height="160" rx="10" fill="#ecedee"/>
              <ellipse cx="80" cy="40" rx="26" ry="16" fill="#2b2b2b"/>
              <path d="M46 44L80 50L114 44L134 74L118 84L110 76V130H50V76L42 84L26 74L46 44Z" fill="#111111"/>
              <path d="M60 100H100L96 124H64L60 100Z" fill="#2b2b2b"/>
              <path d="M74 54V76M86 54V76" stroke="#000aff" stroke-width="2.5" stroke-linecap="round"/>
              <text x="80" y="85" text-anchor="middle" font-family="Montserrat, sans-serif" font-weight="700" font-size="9" fill="#ffffff" letter-spacing="1">FASTENDER</text>
            </svg>';
        case 'tshirt':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="160" height="160" rx="10" fill="#ecedee"/>
              <path d="M52 40L80 48L108 40L126 62L112 72L106 66V130H54V66L48 72L34 62L52 40Z" fill="#111111"/>
              <path d="M70 40C70 46 90 46 90 40Z" fill="#ecedee"/>
              <rect x="65" y="70" width="30" height="20" rx="3" fill="#000aff"/>
              <text x="80" y="84" text-anchor="middle" font-family="Montserrat, sans-serif" font-weight="700" font-size="8" fill="#ffffff">PO 2026</text>
            </svg>';
        default:
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="160" height="160" rx="10" fill="#ecedee"/>
              <path d="M52 40L80 48L108 40L126 62L112 72L106 66V130H54V66L48 72L34 62L52 40Z" fill="#111111"/>
            </svg>';
    }
}

// ----------------------------------------------------
// 8. PENGELOLAAN 3D SHOWCASE (PNG TO 3D MODEL)
// ----------------------------------------------------
define('DATA_SHOWCASE_3D_FILE', __DIR__ . '/../data/showcase_3d.json');
define('UPLOAD_3D_DIR', __DIR__ . '/../uploads/3d/');

function get3DShowcaseConfig() {
    if (file_exists(DATA_SHOWCASE_3D_FILE)) {
        $json = file_get_contents(DATA_SHOWCASE_3D_FILE);
        $data = json_decode($json, true);
        if (is_array($data) && !empty($data['image_url'])) {
            return $data;
        }
    }
    return [
        'image_url' => 'assets/images/logo.png',
        'title' => 'Emblem Resmi Fastender 2026',
        'subtitle' => 'Convert PNG to 3D Realtime • Berotasi Pelan 360°',
        'depth' => 8,
        'rotation_speed' => 0.006,
        'metalness' => 0.35,
        'roughness' => 0.3,
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

function save3DShowcaseConfig($postData, $fileData = null) {
    if (!is_dir(UPLOAD_3D_DIR)) {
        mkdir(UPLOAD_3D_DIR, 0777, true);
    }
    
    $current = get3DShowcaseConfig();
    $imageUrl = $current['image_url'];
    
    // Upload via $_FILES
    if (!empty($fileData['name']) && $fileData['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if ($ext === 'png') {
            $fileName = 'model_3d_' . time() . '.png';
            $targetPath = UPLOAD_3D_DIR . $fileName;
            if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
                $imageUrl = 'uploads/3d/' . $fileName;
            }
        }
    } 
    // Atau upload via Base64 DataURL (Drag & Drop AJAX)
    elseif (!empty($postData['image_base64'])) {
        $base64 = $postData['image_base64'];
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $data = base64_decode($data);
            if ($data !== false) {
                $fileName = 'model_3d_' . time() . '.png';
                file_put_contents(UPLOAD_3D_DIR . $fileName, $data);
                $imageUrl = 'uploads/3d/' . $fileName;
            }
        }
    }
    
    $config = [
        'image_url' => $imageUrl,
        'title' => sanitize($postData['title'] ?? ($current['title'] ?? '3D Apparel Fastender')),
        'subtitle' => sanitize($postData['subtitle'] ?? ($current['subtitle'] ?? 'Desain 3D Pre-Order Terkini')),
        'depth' => floatval($postData['depth'] ?? 8),
        'rotation_speed' => floatval($postData['rotation_speed'] ?? 0.006),
        'metalness' => floatval($postData['metalness'] ?? 0.35),
        'roughness' => floatval($postData['roughness'] ?? 0.3),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents(DATA_SHOWCASE_3D_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    return $config;
}

// ----------------------------------------------------
// 9. PENGELOLAAN HERO SECTION & COUNTDOWN BANNER (SPLIT-CARD)
// ----------------------------------------------------

/**
 * Mengambil pengaturan hero section split-card & countdown (dari DB atau JSON fallback)
 */
function getHeroSettings() {
    $defaults = [
        'title' => 'Pre-Order Batch 1 telah dibuka',
        'badge' => 'BATCH PERDANA TAHUN AKADEMIK 2026',
        'description' => 'Wujudkan identitas kebanggaan kelas, jurusan, dan organisasimu bersama Fastender. Bahan standar distro, gratis konsultasi desain bordir, sistem DP 50%, dan jaminan tepat waktu.',
        'countdown_label' => 'PO DITUTUP DALAM',
        'countdown_deadline' => date('Y-m-d\TH:i', strtotime('+14 days 23:59')),
        'banner_image' => '',
        'cta_text' => 'Pesan Sekarang',
        'cta_link' => 'katalog.php',
    ];

    $legacyDefaultBanners = [
        'assets/images/banner-po-default.svg',
        'assets/images/hero-showcase.png'
    ];

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            // Pastikan tabel site_settings tersedia
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `site_settings` (
                    `setting_key` VARCHAR(100) PRIMARY KEY,
                    `setting_value` TEXT,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE 'hero_%'");
            $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            if (!empty($rows)) {
                $dbBanner = trim((string)($rows['hero_banner_image'] ?? ''));
                if (in_array($dbBanner, $legacyDefaultBanners, true)) {
                    $dbBanner = '';
                }
                return [
                    'title' => $rows['hero_title'] ?? $defaults['title'],
                    'badge' => $rows['hero_badge'] ?? $defaults['badge'],
                    'description' => $rows['hero_description'] ?? $defaults['description'],
                    'countdown_label' => $rows['hero_countdown_label'] ?? $defaults['countdown_label'],
                    'countdown_deadline' => $rows['hero_countdown_deadline'] ?? $defaults['countdown_deadline'],
                    'banner_image' => $dbBanner,
                    'cta_text' => $rows['hero_cta_text'] ?? $defaults['cta_text'],
                    'cta_link' => $rows['hero_cta_link'] ?? $defaults['cta_link'],
                ];
            }
        } catch (Exception $e) {
            // Lanjut ke fallback file
        }
    }

    // Fallback file JSON
    if (file_exists(DATA_HERO_SETTINGS_FILE)) {
        $json = file_get_contents(DATA_HERO_SETTINGS_FILE);
        $data = json_decode($json, true);
        if (is_array($data)) {
            $merged = array_merge($defaults, $data);
            if (in_array(trim((string)($merged['banner_image'] ?? '')), $legacyDefaultBanners, true)) {
                $merged['banner_image'] = '';
            }
            return $merged;
        }
    }

    return $defaults;
}

/**
 * Mengecek apakah periode Pre-Order masih aktif berdasarkan target waktu countdown deadline
 * @return bool True jika masih dalam periode PO (aktif), False jika deadline sudah habis (ditutup)
 */
function isPreOrderActive() {
    $hero = getHeroSettings();
    $deadlineStr = trim((string)($hero['countdown_deadline'] ?? ''));
    if ($deadlineStr === '') {
        return true;
    }

    $ts = strtotime($deadlineStr);
    if ($ts === false) {
        $ts = strtotime(str_replace('T', ' ', $deadlineStr));
    }

    if ($ts === false) {
        return true;
    }

    return time() < $ts;
}

/**
 * Mengambil informasi detail status Pre-Order saat ini
 */
function getPreOrderStatus() {
    $hero = getHeroSettings();
    $deadlineStr = trim((string)($hero['countdown_deadline'] ?? ''));
    $isActive = true;
    $deadlineTs = 0;

    if ($deadlineStr !== '') {
        $ts = strtotime($deadlineStr);
        if ($ts === false) {
            $ts = strtotime(str_replace('T', ' ', $deadlineStr));
        }
        if ($ts !== false) {
            $deadlineTs = $ts;
            $isActive = (time() < $deadlineTs);
        }
    }

    return [
        'is_active' => $isActive,
        'deadline_str' => $deadlineStr,
        'deadline_timestamp' => $deadlineTs,
        'server_time' => time(),
    ];
}

/**
 * Menyimpan pengaturan hero section (dari form admin)
 */
function saveHeroSettings($postData, $filesData = null) {
    $current = getHeroSettings();

    $title = sanitize($postData['title'] ?? $current['title']);
    $badge = sanitize($postData['badge'] ?? $current['badge']);
    $description = sanitize($postData['description'] ?? $current['description']);
    $countdownLabel = sanitize($postData['countdown_label'] ?? $current['countdown_label']);
    $countdownDeadline = sanitize($postData['countdown_deadline'] ?? $current['countdown_deadline']);
    $ctaText = sanitize($postData['cta_text'] ?? $current['cta_text']);
    $ctaLink = sanitize($postData['cta_link'] ?? $current['cta_link']);
    $bannerImage = $current['banner_image'];

    // 1. Cek unggahan file gambar banner baru via $_FILES
    if (!empty($filesData['name']) && $filesData['error'] === UPLOAD_ERR_OK) {
        if (!is_dir(UPLOAD_HERO_DIR)) {
            mkdir(UPLOAD_HERO_DIR, 0777, true);
        }
        $ext = strtolower(pathinfo($filesData['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $fileName = 'hero_banner_' . time() . '.' . $ext;
            if (move_uploaded_file($filesData['tmp_name'], UPLOAD_HERO_DIR . $fileName)) {
                $bannerImage = 'uploads/hero/' . $fileName;
            }
        }
    } elseif (!empty($postData['banner_image_path'])) {
        $bannerImage = sanitize($postData['banner_image_path']);
    }

    $updated = [
        'title' => $title,
        'badge' => $badge,
        'description' => $description,
        'countdown_label' => $countdownLabel,
        'countdown_deadline' => $countdownDeadline,
        'banner_image' => $bannerImage,
        'cta_text' => $ctaText,
        'cta_link' => $ctaLink,
    ];

    // Simpan ke DB MySQL jika aktif
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO site_settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            foreach ($updated as $k => $v) {
                $stmt->execute(['hero_' . $k, $v]);
            }
        } catch (Exception $e) {
            // Lanjut simpan ke file JSON
        }
    }

    // Selalu simpan juga ke file JSON data/hero_settings.json
    if (!is_dir(dirname(DATA_HERO_SETTINGS_FILE))) {
        mkdir(dirname(DATA_HERO_SETTINGS_FILE), 0777, true);
    }
    file_put_contents(DATA_HERO_SETTINGS_FILE, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $updated;
}

/**
 * Mengambil daftar gambar slider hero section secara dinamis dari folder assets/image/ atau assets/images/
 */
function getHeroSliderImages() {
    $images = [];
    
    // Cek folder fisik di server (assets/images atau assets/image)
    $possibleDirs = [
        __DIR__ . '/../assets/images',
        __DIR__ . '/../assets/image'
    ];

    foreach ($possibleDirs as $dir) {
        if (is_dir($dir)) {
            // Cari file dengan pola gambar* (gambar1.jpg, gambar2.jpg, gambar3.jpg, dll)
            $files = glob($dir . '/*gambar*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE);
            if (!empty($files)) {
                sort($files, SORT_NATURAL | SORT_FLAG_CASE);
                foreach ($files as $filePath) {
                    $rel = 'assets/images/' . basename($filePath);
                    if (!in_array($rel, $images)) {
                        $images[] = $rel;
                    }
                }
                break;
            }
        }
    }

    // Jika belum ada hasil, scan file gambar1, gambar2, gambar3 secara langsung
    if (empty($images)) {
        $checkList = ['gambar1.jpg', 'gambar2.jpg', 'gambar3.jpg'];
        foreach ($checkList as $imgName) {
            if (file_exists(__DIR__ . '/../assets/images/' . $imgName)) {
                $images[] = 'assets/images/' . $imgName;
            }
        }
    }

    // Fallback jika tidak ada gambar
    if (empty($images)) {
        $images = [
            'assets/images/hero-showcase.png'
        ];
    }

    return $images;
}

// ----------------------------------------------------
// 9. PENGELOLAAN PENGATURAN TOKO (STATUS, KONTAK, REKENING & QRIS)
// ----------------------------------------------------

/**
 * Mengambil data pengaturan toko (status buka/tutup, nomor WA, rekening BCA & Mandiri, QRIS)
 */
function getStoreSettings() {
    $defaults = [
        'status_toko' => 'buka',
        'pesan_toko_tutup' => 'Pre-Order sedang ditutup sementara untuk persiapan produksi batch berikutnya. Anda tetap dapat menyimpan produk di keranjang.',
        'nomor_whatsapp' => '081234567890',
        'rekening_bca_no' => '828-091-2334',
        'rekening_bca_nama' => 'Fastender Konveksi',
        'rekening_mandiri_no' => '144-00-2026889-1',
        'rekening_mandiri_nama' => 'Fastender Konveksi',
        'qris_image' => '',
    ];

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            // Pastikan tabel pengaturan_toko tersedia
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `pengaturan_toko` (
                    `id` INT PRIMARY KEY AUTO_INCREMENT,
                    `status_toko` ENUM('buka', 'tutup') NOT NULL DEFAULT 'buka',
                    `pesan_toko_tutup` TEXT,
                    `nomor_whatsapp` VARCHAR(20) DEFAULT '081234567890',
                    `rekening_bca_no` VARCHAR(50) DEFAULT '828-091-2334',
                    `rekening_bca_nama` VARCHAR(100) DEFAULT 'Fastender Konveksi',
                    `rekening_mandiri_no` VARCHAR(50) DEFAULT '144-00-2026889-1',
                    `rekening_mandiri_nama` VARCHAR(100) DEFAULT 'Fastender Konveksi',
                    `qris_image` VARCHAR(255) DEFAULT '',
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $stmt = $pdo->query("SELECT * FROM `pengaturan_toko` ORDER BY id ASC LIMIT 1");
            $row = $stmt->fetch();
            if ($row) {
                return array_merge($defaults, $row);
            } else {
                // Inisialisasi baris pertama default
                $initStmt = $pdo->prepare("
                    INSERT INTO `pengaturan_toko` (`id`, `status_toko`, `pesan_toko_tutup`, `nomor_whatsapp`, `rekening_bca_no`, `rekening_bca_nama`, `rekening_mandiri_no`, `rekening_mandiri_nama`, `qris_image`)
                    VALUES (1, :status_toko, :pesan_toko_tutup, :nomor_whatsapp, :rekening_bca_no, :rekening_bca_nama, :rekening_mandiri_no, :rekening_mandiri_nama, :qris_image)
                    ON DUPLICATE KEY UPDATE `id` = 1
                ");
                $initStmt->execute($defaults);
                return $defaults;
            }
        } catch (Exception $e) {
            // Fallback ke JSON jika DB bermasalah
        }
    }

    // Fallback file JSON
    if (file_exists(DATA_STORE_SETTINGS_FILE)) {
        $json = file_get_contents(DATA_STORE_SETTINGS_FILE);
        $data = json_decode($json, true);
        if (is_array($data)) {
            return array_merge($defaults, $data);
        }
    }

    return $defaults;
}

/**
 * Memperbarui status operasional toko ('buka' atau 'tutup') secara independen (AJAX/Fetch)
 */
function updateStoreStatus($status) {
    $cleanStatus = (strtolower(trim((string)$status)) === 'tutup') ? 'tutup' : 'buka';
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            getStoreSettings(); // memastikan tabel dan baris pertama telah ada
            $stmt = $pdo->prepare("UPDATE `pengaturan_toko` SET `status_toko` = :status WHERE id = 1");
            $stmt->execute(['status' => $cleanStatus]);
        } catch (Exception $e) {
            // Fallback ke penyimpanan JSON
        }
    }

    $current = getStoreSettings();
    $current['status_toko'] = $cleanStatus;
    if (!is_dir(dirname(DATA_STORE_SETTINGS_FILE))) {
        mkdir(dirname(DATA_STORE_SETTINGS_FILE), 0777, true);
    }
    file_put_contents(DATA_STORE_SETTINGS_FILE, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $cleanStatus;
}

/**
 * Menyimpan data pengaturan toko
 */
function saveStoreSettings($postData, $filesData = null) {
    $current = getStoreSettings();

    // Penentuan status_toko yang robust baik via checkbox, AJAX, maupun parameter eksplisit
    if (isset($postData['status_toko'])) {
        $val = strtolower(trim((string)$postData['status_toko']));
        $statusToko = in_array($val, ['tutup', 'closed', '0', 'off'], true) ? 'tutup' : 'buka';
    } elseif (isset($postData['status_toko_present'])) {
        // Checkbox status_toko ada di formulir, namun tidak dikirim oleh browser (berarti toggle uncheck/tutup)
        $statusToko = 'tutup';
    } else {
        $statusToko = $current['status_toko'] ?? 'buka';
    }
    $pesanTokoTutup = sanitize($postData['pesan_toko_tutup'] ?? $current['pesan_toko_tutup']);
    $nomorWhatsapp = sanitize($postData['nomor_whatsapp'] ?? $current['nomor_whatsapp']);
    $rekeningBcaNo = sanitize($postData['rekening_bca_no'] ?? $current['rekening_bca_no']);
    $rekeningBcaNama = sanitize($postData['rekening_bca_nama'] ?? $current['rekening_bca_nama']);
    $rekeningMandiriNo = sanitize($postData['rekening_mandiri_no'] ?? $current['rekening_mandiri_no']);
    $rekeningMandiriNama = sanitize($postData['rekening_mandiri_nama'] ?? $current['rekening_mandiri_nama']);
    $qrisImage = $current['qris_image'];

    // Cek jika ada request hapus gambar QRIS
    if (!empty($postData['remove_qris']) && $postData['remove_qris'] == '1') {
        $qrisImage = '';
    }

    // Cek unggahan file gambar QRIS baru
    if (!empty($filesData['name']) && $filesData['error'] === UPLOAD_ERR_OK) {
        if (!is_dir(UPLOAD_QRIS_DIR)) {
            mkdir(UPLOAD_QRIS_DIR, 0777, true);
        }
        $ext = strtolower(pathinfo($filesData['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $fileName = 'qris_' . time() . '.' . $ext;
            if (move_uploaded_file($filesData['tmp_name'], UPLOAD_QRIS_DIR . $fileName)) {
                $qrisImage = 'uploads/qris/' . $fileName;
            }
        }
    } elseif (!empty($postData['qris_image_path'])) {
        $qrisImage = sanitize($postData['qris_image_path']);
    }

    $updated = [
        'status_toko' => $statusToko,
        'pesan_toko_tutup' => $pesanTokoTutup,
        'nomor_whatsapp' => $nomorWhatsapp,
        'rekening_bca_no' => $rekeningBcaNo,
        'rekening_bca_nama' => $rekeningBcaNama,
        'rekening_mandiri_no' => $rekeningMandiriNo,
        'rekening_mandiri_nama' => $rekeningMandiriNama,
        'qris_image' => $qrisImage,
    ];

    // Simpan ke DB MySQL jika aktif
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `pengaturan_toko` (
                    `id` INT PRIMARY KEY AUTO_INCREMENT,
                    `status_toko` ENUM('buka', 'tutup') NOT NULL DEFAULT 'buka',
                    `pesan_toko_tutup` TEXT,
                    `nomor_whatsapp` VARCHAR(20) DEFAULT '081234567890',
                    `rekening_bca_no` VARCHAR(50) DEFAULT '828-091-2334',
                    `rekening_bca_nama` VARCHAR(100) DEFAULT 'Fastender Konveksi',
                    `rekening_mandiri_no` VARCHAR(50) DEFAULT '144-00-2026889-1',
                    `rekening_mandiri_nama` VARCHAR(100) DEFAULT 'Fastender Konveksi',
                    `qris_image` VARCHAR(255) DEFAULT '',
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $checkStmt = $pdo->query("SELECT id FROM `pengaturan_toko` LIMIT 1");
            $hasRow = $checkStmt->fetch();
            if ($hasRow) {
                $upStmt = $pdo->prepare("
                    UPDATE `pengaturan_toko` SET 
                        `status_toko` = :status_toko,
                        `pesan_toko_tutup` = :pesan_toko_tutup,
                        `nomor_whatsapp` = :nomor_whatsapp,
                        `rekening_bca_no` = :rekening_bca_no,
                        `rekening_bca_nama` = :rekening_bca_nama,
                        `rekening_mandiri_no` = :rekening_mandiri_no,
                        `rekening_mandiri_nama` = :rekening_mandiri_nama,
                        `qris_image` = :qris_image
                    WHERE `id` = :id
                ");
                $updated['id'] = $hasRow['id'];
                $upStmt->execute($updated);
                unset($updated['id']);
            } else {
                $inStmt = $pdo->prepare("
                    INSERT INTO `pengaturan_toko` (`id`, `status_toko`, `pesan_toko_tutup`, `nomor_whatsapp`, `rekening_bca_no`, `rekening_bca_nama`, `rekening_mandiri_no`, `rekening_mandiri_nama`, `qris_image`)
                    VALUES (1, :status_toko, :pesan_toko_tutup, :nomor_whatsapp, :rekening_bca_no, :rekening_bca_nama, :rekening_mandiri_no, :rekening_mandiri_nama, :qris_image)
                ");
                $inStmt->execute($updated);
            }
        } catch (Exception $e) {
            // Lanjut simpan ke file JSON
        }
    }

    // Selalu simpan juga ke file JSON data/store_settings.json
    if (!is_dir(dirname(DATA_STORE_SETTINGS_FILE))) {
        mkdir(dirname(DATA_STORE_SETTINGS_FILE), 0777, true);
    }
    file_put_contents(DATA_STORE_SETTINGS_FILE, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return $updated;
}

/**
 * Mengecek apakah operasional toko berstatus 'buka'
 * @return bool True jika buka, False jika tutup
 */
function isStoreOpen() {
    $settings = getStoreSettings();
    return strtolower(trim((string)($settings['status_toko'] ?? 'buka'))) !== 'tutup';
}

/**
 * Mengubah nomor WhatsApp menjadi link klik-ke-chat https://wa.me/62...
 */
function formatWhatsAppUrl($phone = null, $text = '') {
    if ($phone === null || trim((string)$phone) === '') {
        $settings = getStoreSettings();
        $phone = $settings['nomor_whatsapp'] ?? '081234567890';
    }
    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
    if (str_starts_with($clean, '0')) {
        $clean = '62' . substr($clean, 1);
    } elseif (str_starts_with($clean, '8')) {
        $clean = '62' . $clean;
    }
    if (empty($clean)) {
        $clean = '6281234567890';
    }
    
    $url = 'https://wa.me/' . $clean;
    if (!empty($text)) {
        $url .= '?text=' . urlencode($text);
    }
    return $url;
}

// ----------------------------------------------------
// 12. PENGELOLAAN & PEMBATASAN LIMIT KUOTA PRE-ORDER
// ----------------------------------------------------

/**
 * Menghitung total kuantiti produk yang sudah dipesan dalam transaksi valid/aktif.
 */
function getProductTotalOrderedQuantity($productId, $productName = '') {
    $productId = (int)$productId;
    $productName = trim((string)$productName);
    $totalFromOrders = 0;

    $pdo = getDbConnection();
    if ($pdo) {
        try {
            ensureOrdersTableSchema($pdo);
            $stmt = $pdo->prepare("
                SELECT COALESCE(SUM(oi.quantity), 0) AS total_qty
                FROM `order_items` oi
                JOIN `orders` o ON o.id = oi.order_id
                WHERE (oi.product_id = :pid OR (:pname != '' AND LOWER(oi.product_name) = LOWER(:pname2)))
                  AND (o.payment_status NOT IN ('Dibatalkan', 'Ditolak', 'Batal') OR o.payment_status IS NULL)
            ");
            $stmt->execute([
                ':pid' => $productId,
                ':pname' => $productName,
                ':pname2' => $productName
            ]);
            $totalFromOrders = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            // Lanjut ke fallback
        }
    }

    // Fallback file orders.json
    if ($totalFromOrders === 0 && file_exists(DATA_ORDERS_FILE)) {
        $ordersRaw = file_get_contents(DATA_ORDERS_FILE);
        $ordersArr = json_decode($ordersRaw, true);
        if (is_array($ordersArr)) {
            foreach ($ordersArr as $ord) {
                $payStatus = strtolower(trim((string)($ord['payment_status'] ?? ($ord['paymentStatus'] ?? ''))));
                if (in_array($payStatus, ['dibatalkan', 'ditolak', 'batal'], true)) {
                    continue;
                }
                $items = $ord['items'] ?? [];
                if (is_array($items)) {
                    foreach ($items as $it) {
                        $itId = (int)($it['id'] ?? 0);
                        $itName = strtolower(trim((string)($it['name'] ?? '')));
                        if (($productId > 0 && $itId === $productId) || ($productName !== '' && $itName === strtolower($productName))) {
                            $totalFromOrders += max(1, (int)($it['quantity'] ?? ($it['qty'] ?? 1)));
                        }
                    }
                }
            }
        }
    }

    // Ambil baseline quota_current dari data produk
    $prod = getProductById($productId);
    $baseQuotaCurrent = (int)($prod['quota_current'] ?? 0);

    return max($baseQuotaCurrent, $totalFromOrders);
}

/**
 * Mendapatkan informasi kuota pre-order produk (target, terisi, sisa, dan status penuh)
 */
function getProductQuotaInfo($productOrId) {
    if (is_array($productOrId)) {
        $product = $productOrId;
        $productId = (int)($product['id'] ?? 0);
    } else {
        $productId = (int)$productOrId;
        $product = getProductById($productId);
    }

    if (!$product) {
        return [
            'is_full' => false,
            'target' => 0,
            'current' => 0,
            'remaining' => 0
        ];
    }

    $target = (int)($product['quota_target'] ?? 0);
    $productName = $product['name'] ?? '';
    $current = getProductTotalOrderedQuantity($productId, $productName);

    $isFull = ($target > 0 && $current >= $target);
    $remaining = max(0, $target - $current);

    return [
        'is_full' => $isFull,
        'target' => $target,
        'current' => $current,
        'remaining' => $remaining
    ];
}

/**
 * Mengecek apakah kuota produk telah penuh / sold out
 */
function isProductQuotaFull($productOrId) {
    $info = getProductQuotaInfo($productOrId);
    return $info['is_full'];
}

/**
 * Mengambil daftar ID produk yang kuotanya telah penuh (sold out)
 */
function getFullQuotaProductIds() {
    $fullIds = [];
    $all = getProductsList();
    foreach ($all as $p) {
        $info = getProductQuotaInfo($p);
        if ($info['is_full']) {
            $fullIds[] = (int)$p['id'];
        }
    }
    return $fullIds;
}
