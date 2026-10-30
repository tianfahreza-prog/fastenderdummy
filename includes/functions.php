<?php
/**
 * Fastender Pre-Order Platform
 * File Fungsi Utama & Helper (includes/functions.php)
 * PHP Native Functions with PDO (Supabase Transaction Pooler) - NO DUMMY DATA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase.php';

// Bucket helpers
function bucketWebData(): string { return getBucketWebData(); }
function bucketBuktiPembayaran(): string { return getBucketBuktiPembayaran(); }
function bucketImageProducts(): string { return getBucketImageProducts(); }

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
// 3. SUPABASE STORAGE HELPERS (API Only)
// ----------------------------------------------------
function uploadToSupabase(string $bucket, string $path, $fileData, string $contentType = 'image/jpeg'): string {
    $storage = getStorage();
    
    if (is_string($fileData) && file_exists($fileData)) {
        $result = $storage->upload($bucket, $path, $fileData, $contentType);
    } elseif (is_array($fileData) && isset($fileData['tmp_name'])) {
        $result = $storage->upload($bucket, $path, $fileData['tmp_name'], $contentType);
    } elseif (is_string($fileData) && str_starts_with($fileData, 'data:image/')) {
        if (preg_match('/^data:image\/(\w+);base64,/', $fileData, $matches)) {
            $base64Data = substr($fileData, strpos($fileData, ',') + 1);
            $decoded = base64_decode($base64Data);
            $ext = strtolower($matches[1]);
            if ($ext === 'jpeg') $ext = 'jpg';
            $contentType = 'image/' . $ext;
            $result = $storage->uploadFromString($bucket, $path, $decoded, $contentType);
        } else {
            throw new Exception('Invalid base64 image format');
        }
    } else {
        throw new Exception('Invalid file data for upload');
    }
    
    return $storage->getPublicUrl($bucket, $path);
}

function deleteFromSupabase(string $bucket, array $paths): void {
    $storage = getStorage();
    $storage->delete($bucket, $paths);
}

function getSupabasePublicUrl(string $bucket, string $path): string {
    $storage = getStorage();
    return $storage->getPublicUrl($bucket, $path);
}

// ----------------------------------------------------
// 4. PDO HELPER FUNCTIONS
// ----------------------------------------------------
function dbSelect(string $table, array $columns = ['*'], array $where = [], string $order = '', int $limit = 0, int $offset = 0): array {
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    $colStr = implode(', ', $columns);
    $sql = "SELECT $colStr FROM $table";
    $params = [];
    
    if (!empty($where)) {
        $conditions = [];
        foreach ($where as $key => $value) {
            if (is_array($value) && count($value) === 2) {
                $operator = strtoupper(trim($value[0]));
                $val = $value[1];
                
                if ($operator === 'LIKE' || $operator === 'ILIKE' || $operator === 'NOT LIKE' || $operator === 'NOT ILIKE') {
                    $conditions[] = "$key $operator ?";
                    $params[] = $val;
                } elseif ($operator === 'IN' || $operator === 'NOT IN') {
                    // Handle IN/NOT IN with array of values
                    if (is_array($val)) {
                        $placeholders = implode(', ', array_fill(0, count($val), '?'));
                        $conditions[] = "$key $operator ($placeholders)";
                        $params = array_merge($params, $val);
                    } else {
                        // String format: "('val1','val2')" - use directly in SQL
                        $conditions[] = "$key $operator $val";
                    }
                } elseif (in_array($operator, ['=', '!=', '<>', '<', '>', '<=', '>='], true)) {
                    $conditions[] = "$key $operator ?";
                    $params[] = $val;
                } else {
                    // Default to equality
                    $conditions[] = "$key = ?";
                    $params[] = $val;
                }
            } else {
                $conditions[] = "$key = ?";
                $params[] = $value;
            }
        }
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
    
    if ($order) {
        $sql .= " ORDER BY $order";
    }
    
    if ($limit > 0) {
        $sql .= " LIMIT $limit";
    }
    
    if ($offset > 0) {
        $sql .= " OFFSET $offset";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("DB select error: " . $e->getMessage());
        return [];
    }
}

function dbQuery(string $sql, array $params = []): array {
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("DB query error: " . $e->getMessage());
        return [];
    }
}

function dbQueryOne(string $sql, array $params = []): ?array {
    $result = dbQuery($sql, $params);
    return $result[0] ?? null;
}

function dbSelectOne(string $table, array $columns = ['*'], array $where = []): ?array {
    $result = dbSelect($table, $columns, $where, '', 1, 0);
    return $result[0] ?? null;
}

function dbInsert(string $table, array $data): array {
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    $columns = array_keys($data);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $colStr = implode(', ', $columns);
    
    $sql = "INSERT INTO $table ($colStr) VALUES ($placeholders) RETURNING *";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("DB insert error: " . $e->getMessage());
        return [];
    }
}

function dbUpdate(string $table, array $data, array $where): array {
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    $setParts = [];
    $params = [];
    foreach ($data as $key => $value) {
        $setParts[] = "$key = ?";
        $params[] = $value;
    }
    
    $whereParts = [];
    foreach ($where as $key => $value) {
        if (is_array($value) && count($value) === 2) {
            $whereParts[] = "$key $value[1]";
            $params[] = $value[0];
        } else {
            $whereParts[] = "$key = ?";
            $params[] = $value;
        }
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts) . " RETURNING *";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("DB update error: " . $e->getMessage());
        return [];
    }
}

function dbDelete(string $table, array $where): bool {
    $pdo = getDbConnection();
    if (!$pdo) return false;
    
    $whereParts = [];
    $params = [];
    foreach ($where as $key => $value) {
        if (is_array($value) && count($value) === 2) {
            $whereParts[] = "$key $value[1]";
            $params[] = $value[0];
        } else {
            $whereParts[] = "$key = ?";
            $params[] = $value;
        }
    }
    
    $sql = "DELETE FROM $table WHERE " . implode(' AND ', $whereParts);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("DB delete error: " . $e->getMessage());
        return false;
    }
}

function dbRpc(string $function, array $params = []): array {
    $pdo = getDbConnection();
    if (!$pdo) return [];
    
    $paramStr = '';
    if (!empty($params)) {
        $paramPairs = [];
        foreach ($params as $key => $value) {
            $paramPairs[] = "$key := " . ($pdo->quote($value));
        }
        $paramStr = implode(', ', $paramPairs);
    }
    
    $sql = "SELECT * FROM $function($paramStr)";
    
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("DB rpc error: " . $e->getMessage());
        return [];
    }
}

// ----------------------------------------------------
// 5. KATEGORI & PAYMENT METHODS DARI DATABASE
// ----------------------------------------------------
function getCategoriesFromDb(): array {
    return dbSelect('categories', ['id', 'category_name', 'jenis'], [], 'id ASC');
}

function getCategoryMapFromDb(): array {
    $categories = getCategoriesFromDb();
    $map = [];
    foreach ($categories as $cat) {
        $map[$cat['category_name']] = $cat['category_name'];
    }
    return $map;
}

function getCategoryIdByName(string $categoryName): ?int {
    $result = dbSelectOne('categories', ['id'], ['category_name' => $categoryName]);
    return $result['id'] ?? null;
}

function getPaymentMethodsFromDb(): array {
    return dbSelect('payment_methods', ['id', 'method_name', 'details'], [], 'id ASC');
}

function getPaymentMethodById(int $id): ?array {
    return dbSelectOne('payment_methods', ['id', 'method_name', 'details'], ['id' => $id]);
}

// Backward compatibility - returns categories from DB
function getProductCategoriesMap(): array {
    return getCategoryMapFromDb();
}

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

// ----------------------------------------------------
// 6. PENGELOLAAN DATA PRODUK PRE-ORDER (DB ONLY)
// ----------------------------------------------------
function getProductsList($kategori = ''): array {
    $kategori = trim((string)$kategori);
    $hasCategoryFilter = ($kategori !== '' && strtolower($kategori) !== 'all');
    $katUpper = strtoupper($kategori);

    $whereConditions = [];
    $params = [];

    if ($hasCategoryFilter) {
        if ($katUpper === 'POLSTUFF') {
            $whereConditions[] = "c.category_name IN ('Kaos','Keychain','Totebag','Sticker','POLSTUFF')";
        } elseif ($katUpper === 'POLNEEDS') {
            $whereConditions[] = "c.category_name IN ('Workshirt','Jas almamater','Jas Lab','POLNEEDS')";
        } else {
            $whereConditions[] = "c.category_name = ?";
            $params[] = $kategori;
        }
    }

    $whereSql = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    $sql = "
        SELECT 
            p.*,
            c.category_name,
            c.jenis as parent_jenis,
            COALESCE(
                jsonb_agg(
                    jsonb_build_object('image_path', pi.image_path)
                    ORDER BY pi.is_primary DESC, pi.id ASC
                ) FILTER (WHERE pi.id IS NOT NULL),
                '[]'::jsonb
            ) as images,
            COALESCE((
                SELECT SUM(oi.quantity)
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                JOIN payment_statuses ps ON ps.order_id = o.id
                WHERE oi.product_id = p.id
                  AND ps.status_name IN ('DP 50% Lunas', 'Lunas 100%')
            ), 0) as total_ordered_qty
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id
        $whereSql
        GROUP BY p.id, c.category_name, c.jenis
        ORDER BY p.id DESC
    ";

    try {
        $products = dbQuery($sql, $params);
    } catch (Exception $e) {
        error_log("getProductsList error: " . $e->getMessage());
        return [];
    }

    foreach ($products as &$p) {
        $images = json_decode($p['images'] ?? '[]', true);
        $p['images'] = array_column($images, 'image_path');
        $p['specs'] = !empty($p['specs']) ? array_map('trim', explode(',', $p['specs'])) : [];
        $p['sizes'] = !empty($p['sizes']) ? array_map('trim', explode(',', $p['sizes'])) : ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
        $p['colors'] = !empty($p['colors']) ? array_map('trim', explode(',', $p['colors'])) : ['Navy', 'Hitam', 'Maroon', 'Putih'];
        
        $p['parent_kategori'] = $p['parent_jenis'] ?? resolveParentCategory($p['category_name'] ?? '');
        unset($p['parent_jenis']);
    }

    return $products;
}

function getProductById($id): ?array {
    $id = (int)$id;
    
    $sql = "
        SELECT 
            p.*,
            c.category_name,
            c.jenis as parent_jenis,
            COALESCE(
                jsonb_agg(
                    jsonb_build_object('image_path', pi.image_path)
                    ORDER BY pi.is_primary DESC, pi.id ASC
                ) FILTER (WHERE pi.id IS NOT NULL),
                '[]'::jsonb
            ) as images,
            COALESCE((
                SELECT SUM(oi.quantity)
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                JOIN payment_statuses ps ON ps.order_id = o.id
                WHERE oi.product_id = p.id
                  AND ps.status_name IN ('DP 50% Lunas', 'Lunas 100%')
            ), 0) as total_ordered_qty
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_images pi ON pi.product_id = p.id
        WHERE p.id = ?
        GROUP BY p.id, c.category_name, c.jenis
    ";
    
    try {
        $product = dbQueryOne($sql, [$id]);
    } catch (Exception $e) {
        error_log("getProductById error: " . $e->getMessage());
        return null;
    }
    
    if (!$product) return null;

    $images = json_decode($product['images'] ?? '[]', true);
    $product['images'] = array_column($images, 'image_path');
    $product['specs'] = !empty($product['specs']) ? array_map('trim', explode(',', $product['specs'])) : [];
    $product['sizes'] = !empty($product['sizes']) ? array_map('trim', explode(',', $product['sizes'])) : ['S', 'M', 'L', 'XL', 'XXL', '3XL'];
    $product['colors'] = !empty($product['colors']) ? array_map('trim', explode(',', $product['colors'])) : ['Navy', 'Hitam', 'Maroon', 'Putih'];
    
    $product['parent_kategori'] = $product['parent_jenis'] ?? resolveParentCategory($product['category_name'] ?? '');
    unset($product['parent_jenis']);

    return $product;
}

function saveNewProduct($postData, $filesData = null): int {
    $pdo = getDbConnection();
    if (!$pdo) throw new Exception('Database connection failed');
    
    $name = sanitize($postData['name'] ?? '');
    $subtitle = sanitize($postData['subtitle'] ?? '');
    $categoryName = sanitize($postData['category'] ?? 'POLSTUFF');
    $categoryId = getCategoryIdByName($categoryName);
    if (!$categoryId) throw new Exception('Invalid category');
    
    $price = (int)($postData['price'] ?? 0);
    $dpPrice = (int)($postData['dp_price'] ?? round($price * 0.5));
    $quotaTarget = (int)($postData['quota_target'] ?? 100);
    $quotaCurrent = (int)($postData['quota_current'] ?? 0);
    $batch = sanitize($postData['batch'] ?? 'Batch 1');
    $badge = sanitize($postData['badge'] ?? 'PO Baru');
    $desc = sanitize($postData['description'] ?? 'Apparel pre-order berkualitas tinggi dengan standar mutu Fastender.');
    $specs = sanitize($postData['specs'] ?? 'Bahan Standar Distro, Bordir Komputer HD');
    $iconType = sanitize($postData['icon_type'] ?? 'varsity');
    $sizes = sanitize($postData['sizes'] ?? 'S, M, L, XL, XXL, 3XL');
    $colors = sanitize($postData['colors'] ?? 'Navy, Hitam, Maroon, Putih');
    
    $specsArray = array_values(array_filter(array_map('trim', explode(',', $specs)), fn($s) => $s !== ''));
    
    $productData = [
        'name' => $name,
        'subtitle' => $subtitle,
        'category_id' => $categoryId,
        'category_name' => $categoryName,
        'price' => $price,
        'dp_price' => $dpPrice,
        'quota_current' => $quotaCurrent,
        'quota_target' => $quotaTarget,
        'badge' => $badge,
        'batch' => $batch,
        'description' => $desc,
        'specs' => implode(', ', $specsArray),
        'sizes' => $sizes,
        'colors' => $colors,
        'icon_type' => $iconType,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $inserted = dbInsert('products', $productData);
    $newId = $inserted[0]['id'] ?? 0;
    
    if ($newId && !empty($filesData)) {
        $savedImages = [];
        
        if (!empty($filesData['name']) && is_array($filesData['name'])) {
            $totalFiles = count($filesData['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($filesData['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($filesData['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $path = "product_{$newId}/" . time() . '_' . uniqid() . ".{$ext}";
                        $publicUrl = uploadToSupabase(bucketImageProducts(), $path, $filesData['tmp_name'][$i], 'image/' . $ext);
                        $savedImages[] = ['product_id' => $newId, 'image_path' => $publicUrl, 'is_primary' => ($i === 0 ? 1 : 0), 'sort_order' => $i];
                    }
                }
            }
        }
        
        if (!empty($postData['base64_images'])) {
            $b64List = json_decode($postData['base64_images'], true);
            if (is_array($b64List)) {
                foreach ($b64List as $idx => $b64) {
                    if (preg_match('/^data:image\/(\w+);base64,/', $b64, $matches)) {
                        $ext = strtolower($matches[1]);
                        if ($ext === 'jpeg') $ext = 'jpg';
                        $path = "product_{$newId}/" . time() . '_' . uniqid() . ".{$ext}";
                        $publicUrl = uploadToSupabase(bucketImageProducts(), $path, $b64, 'image/' . $ext);
                        $savedImages[] = ['product_id' => $newId, 'image_path' => $publicUrl, 'is_primary' => (empty($savedImages) && $idx === 0 ? 1 : 0), 'sort_order' => count($savedImages)];
                    }
                }
            }
        }
        
        if (!empty($savedImages)) {
            foreach ($savedImages as $img) {
                dbInsert('product_images', $img);
            }
        }
    }
    
    return $newId;
}

function updateProductById($id, $postData, $filesData = null): bool {
    $id = (int)$id;
    if ($id <= 0) return false;

    $pdo = getDbConnection();
    if (!$pdo) return false;

    $existingProduct = getProductById($id);
    if (!$existingProduct) return false;

    $name = sanitize($postData['name'] ?? $existingProduct['name']);
    $subtitle = sanitize($postData['subtitle'] ?? $existingProduct['subtitle']);
    $categoryName = sanitize($postData['category'] ?? $existingProduct['category_name']);
    $categoryId = getCategoryIdByName($categoryName);
    if (!$categoryId) throw new Exception('Invalid category');
    
    $price = (int)($postData['price'] ?? $existingProduct['price']);
    $dpPrice = (int)($postData['dp_price'] ?? round($price * 0.5));
    $quotaTarget = max(1, (int)($postData['quota_target'] ?? $existingProduct['quota_target']));
    $quotaCurrent = max(0, (int)($postData['quota_current'] ?? $existingProduct['quota_current']));
    $batch = sanitize($postData['batch'] ?? $existingProduct['batch']);
    $badge = sanitize($postData['badge'] ?? $existingProduct['badge']);
    $desc = sanitize($postData['description'] ?? $existingProduct['description']);
    $specsRaw = sanitize($postData['specs'] ?? '');
    $specsArray = array_values(array_filter(array_map('trim', explode(',', $specsRaw)), fn($s) => $s !== ''));
    if (empty($specsArray) && !empty($existingProduct['specs'])) {
        $specsArray = is_array($existingProduct['specs']) ? $existingProduct['specs'] : explode(',', (string)$existingProduct['specs']);
    }
    $iconType = sanitize($postData['icon_type'] ?? $existingProduct['icon_type']);
    $sizes = sanitize($postData['sizes'] ?? $existingProduct['sizes']);
    $colors = sanitize($postData['colors'] ?? $existingProduct['colors']);

    $updateData = [
        'name' => $name,
        'subtitle' => $subtitle,
        'category_id' => $categoryId,
        'category_name' => $categoryName,
        'price' => $price,
        'dp_price' => $dpPrice,
        'quota_current' => $quotaCurrent,
        'quota_target' => $quotaTarget,
        'badge' => $badge,
        'batch' => $batch,
        'description' => $desc,
        'specs' => implode(', ', $specsArray),
        'sizes' => $sizes,
        'colors' => $colors,
        'icon_type' => $iconType
    ];

    try {
        dbUpdate('products', $updateData, ['id' => $id]);
    } catch (Exception $e) {
        error_log("Failed to update product: " . $e->getMessage());
        return false;
    }

    $newUploadedImages = [];
    
    if (!empty($filesData['name']) && is_array($filesData['name'])) {
        $totalFiles = count($filesData['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($filesData['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($filesData['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $path = "product_{$id}/" . time() . '_' . uniqid() . ".{$ext}";
                    $publicUrl = uploadToSupabase(bucketImageProducts(), $path, $filesData['tmp_name'][$i], 'image/' . $ext);
                    $newUploadedImages[] = ['product_id' => $id, 'image_path' => $publicUrl, 'is_primary' => ($i === 0 ? 1 : 0), 'sort_order' => $i];
                }
            }
        }
    }
    
    if (empty($newUploadedImages) && !empty($postData['base64_images'])) {
        $b64List = json_decode($postData['base64_images'], true);
        if (is_array($b64List)) {
            foreach ($b64List as $idx => $b64) {
                if (preg_match('/^data:image\/(\w+);base64,/', $b64, $matches)) {
                    $ext = strtolower($matches[1]);
                    if ($ext === 'jpeg') $ext = 'jpg';
                    $path = "product_{$id}/" . time() . '_' . uniqid() . ".{$ext}";
                    $publicUrl = uploadToSupabase(bucketImageProducts(), $path, $b64, 'image/' . $ext);
                    $newUploadedImages[] = ['product_id' => $id, 'image_path' => $publicUrl, 'is_primary' => (empty($newUploadedImages) && $idx === 0 ? 1 : 0), 'sort_order' => count($newUploadedImages)];
                }
            }
        }
    }

    if (!empty($newUploadedImages)) {
        $oldImages = dbSelect('product_images', ['image_path'], ['product_id' => $id]);
        $oldPaths = array_column($oldImages, 'image_path');
        if (!empty($oldPaths)) {
            deleteFromSupabase(bucketImageProducts(), $oldPaths);
        }
        
        dbDelete('product_images', ['product_id' => $id]);
        foreach ($newUploadedImages as $img) {
            dbInsert('product_images', $img);
        }
    }

    return true;
}

function deleteProductById($id): bool {
    $id = (int)$id;
    $pdo = getDbConnection();
    if (!$pdo) return false;

    try {
        $images = dbSelect('product_images', ['image_path'], ['product_id' => $id]);
        $paths = array_column($images, 'image_path');
        if (!empty($paths)) {
            deleteFromSupabase(bucketImageProducts(), $paths);
        }
        
        dbDelete('product_images', ['product_id' => $id]);
        dbDelete('products', ['id' => $id]);
    } catch (Exception $e) {
        error_log("Failed to delete product: " . $e->getMessage());
        return false;
    }
    
    return true;
}

function clearAllProductsData(): bool {
    $pdo = getDbConnection();
    if (!$pdo) return false;

    try {
        $allProducts = getProductsList();
        foreach ($allProducts as $p) {
            $images = dbSelect('product_images', ['image_path'], ['product_id' => $p['id']]);
            $paths = array_column($images, 'image_path');
            if (!empty($paths)) {
                deleteFromSupabase(bucketImageProducts(), $paths);
            }
        }
        
        dbDelete('product_images', ['product_id' => ['IS NOT', 'NULL']]);
        dbDelete('products', ['id' => ['IS NOT', 'NULL']]);
    } catch (Exception $e) {
        error_log("Failed to clear products: " . $e->getMessage());
        return false;
    }
    
    return true;
}

function loadDemoProductsData(): bool {
    // Demo products removed - only real data from database
    return true;
}

// ----------------------------------------------------
// 7. TRANSACTIONAL HELPERS
// ----------------------------------------------------
function getTrendingProducts(): array {
    $products = getProductsList();
    if (empty($products)) return [];
    
    $result = [];
    foreach (array_slice($products, 0, 6) as $idx => $p) {
        $img = !empty($p['images'][0]) ? $p['images'][0] : '';
        $result[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => (int)$p['price'],
            'image' => $img,
            'category' => $p['category_name'],
            'link' => 'detail.php?id=' . $p['id']
        ];
    }
    return $result;
}

// ----------------------------------------------------
// 8. PESANAN & BUKTI PEMBAYARAN
// ----------------------------------------------------
function processNewCheckoutOrder($postData, $fileData): array {
    if (!isStoreOpen()) {
        $storeSettings = getStoreSettings();
        $msg = !empty($storeSettings['pesan_toko_tutup']) 
            ? $storeSettings['pesan_toko_tutup'] 
            : 'Pre-Order sedang ditutup sementara untuk persiapan produksi batch berikutnya.';
        return ['success' => false, 'message' => $msg];
    }

    if (!isPreOrderActive()) {
        return ['success' => false, 'message' => 'Periode Pre-Order Batch ini telah berakhir. Checkout saat ini ditutup.'];
    }

    if (empty($fileData) || !isset($fileData['error']) || $fileData['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'message' => 'Wajib mengunggah file bukti pembayaran sebelum konfirmasi pesanan.'];
    }

    if ($fileData['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah file bukti pembayaran (Kode Error: ' . (int)$fileData['error'] . ').'];
    }

    $maxSize = 2 * 1024 * 1024;
    if ($fileData['size'] > $maxSize || $fileData['size'] <= 0) {
        return ['success' => false, 'message' => 'Ukuran file bukti pembayaran maksimal 2MB.'];
    }

    $originalName = basename($fileData['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($ext, $allowedExts, true)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan format JPG, PNG, atau PDF.'];
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if ($mimeType && !in_array($mimeType, $allowedMimes, true)) {
            return ['success' => false, 'message' => 'Tipe konten file tidak valid. Pastikan file adalah gambar JPG/PNG atau dokumen PDF asli.'];
        }
    }

    $randomSuffix = random_int(1000, 9999);
    $orderCode = sanitize($postData['order_code'] ?? '') ?: ('PO-2026-' . $randomSuffix);
    $safeOrderCode = preg_replace('/[^A-Za-z0-9\-]/', '', $orderCode);
    $newFileName = 'bukti_' . $safeOrderCode . '_' . time() . '.' . $ext;

    try {
        $buktiUrl = uploadToSupabase(bucketBuktiPembayaran(), $newFileName, $fileData['tmp_name'], $fileData['type']);
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal menyimpan file bukti pembayaran ke storage.'];
    }

    $buyerName = sanitize($postData['buyer_name'] ?? 'Pemesan Fastender');
    $buyerPhone = sanitize($postData['buyer_phone'] ?? '-');
    $jurusan = sanitize($postData['jurusan'] ?? ($postData['buyer_jurusan'] ?? '-'));
    if ($jurusan === '') $jurusan = '-';

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
    $paymentMethodName = sanitize($postData['pay_method'] ?? 'BCA');
    $paymentMethodId = getPaymentMethodIdByName($paymentMethodName);

    $rawCart = $postData['cart_items'] ?? '[]';
    $cartItems = is_string($rawCart) ? json_decode($rawCart, true) : $rawCart;
    if (!is_array($cartItems)) $cartItems = [];

    $subtotal = 0;
    $dpTotal = 0;
    $totalQty = 0;
    $normalizedItems = [];

    foreach ($cartItems as $item) {
        $pId = (int)($item['id'] ?? 1);
        $pName = sanitize($item['name'] ?? 'Produk Pre-Order Fastender');
        if ($pName === '' || $pName === 'undefined') $pName = 'Produk Pre-Order Fastender';
        $pPrice = max(0, (int)preg_replace('/[^0-9]/', '', (string)($item['price'] ?? 0)));
        $pDp = max(0, (int)preg_replace('/[^0-9]/', '', (string)($item['dpPrice'] ?? ($item['dp_price'] ?? round($pPrice * 0.5)))));
        if ($pDp <= 0 && $pPrice > 0) $pDp = (int)round($pPrice * 0.5);
        $pQty = max(1, (int)($item['quantity'] ?? ($item['qty'] ?? 1)));
        $pSize = sanitize($item['size'] ?? 'L');
        $pColor = sanitize($item['color'] ?? 'Standar');
        $pCustom = sanitize($item['customText'] ?? '');
        $itemSub = $pPrice * $pQty;

        $subtotal += $itemSub;
        $dpTotal += ($pDp * $pQty);
        $totalQty += $pQty;

        $normalizedItems[] = [
            'id' => $pId, 'name' => $pName, 'size' => $pSize, 'color' => $pColor,
            'customText' => $pCustom, 'quantity' => $pQty, 'price' => $pPrice,
            'dpPrice' => $pDp, 'subtotal' => $itemSub, 'image' => $item['image'] ?? null
        ];
    }

    foreach ($normalizedItems as $nItem) {
        $prod = getProductById((int)$nItem['id']);
        $quotaInfo = getProductQuotaInfo($prod ?: $nItem['id']);
        if ($quotaInfo['is_full']) {
            return ['success' => false, 'message' => 'Pesanan gagal diproses karena kuota produk "' . $nItem['name'] . '" telah habis.'];
        }
        if ($quotaInfo['target'] > 0 && ($quotaInfo['current'] + $nItem['quantity']) > $quotaInfo['target']) {
            return ['success' => false, 'message' => 'Pesanan gagal diproses karena kuota produk "' . $nItem['name'] . '" telah habis atau tidak mencukupi (Sisa kuota: ' . $quotaInfo['remaining'] . ' pcs).'];
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

    $pdo = getDbConnection();
    if (!$pdo) return ['success' => false, 'message' => 'Database connection failed'];

    try {
        $pdo->beginTransaction();
        
        // First, create payment_status record
        // total_bayar = 0 initially, calculated only when status changes to DP 50% Lunas or Lunas 100%
        $paymentStatusData = [
            'status_name' => $paymentStatus,
            'payment_scheme' => $paymentScheme,
            'total_harga' => $totalPrice,
            'total_bayar' => 0,
            'bukti_pembayaran' => $buktiUrl,
        ];
        
        $insertedPaymentStatus = dbInsert('payment_statuses', $paymentStatusData);
        $paymentStatusId = $insertedPaymentStatus[0]['id'] ?? 0;
        
        if (!$paymentStatusId) {
            throw new Exception('Gagal membuat record status pembayaran');
        }
        error_log("Checkout: Created payment_status_id=$paymentStatusId for orderCode=$orderCode");
        
        $orderData = [
            'order_code' => $orderCode,
            'customer_name' => $buyerName,
            'customer_phone' => $buyerPhone,
            'jurusan' => $jurusan,
            'customer_email' => '-',
            'organization' => $jurusan,
            'shipping_address' => $fullAddress,
            'province' => $province,
            'city' => $city,
            'courier' => $courierName,
            'payment_scheme' => $paymentScheme,
            'payment_method_id' => $paymentMethodId,
            'payment_status_id' => $paymentStatusId,
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total_price' => $totalPrice,
            'total_bayar' => $dueAmount,
            'dp_amount' => $dpTotal,
            'bukti_pembayaran' => $buktiUrl,
            'production_status' => $productionStatus,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $insertedOrder = dbInsert('orders', $orderData);
        $orderId = $insertedOrder[0]['id'] ?? 0;
        error_log("Checkout: Created order_id=$orderId, orderCode=$orderCode with payment_status_id=$paymentStatusId");

        if ($orderId > 0 && !empty($normalizedItems)) {
            foreach ($normalizedItems as $nItem) {
                dbInsert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $nItem['id'],
                    'product_name' => $nItem['name'],
                    'size' => $nItem['size'],
                    'color' => $nItem['color'],
                    'custom_text' => $nItem['customText'],
                    'quantity' => $nItem['quantity'],
                    'price' => $nItem['price'],
                    'subtotal' => $nItem['subtotal']
                ]);
                
                // Increment quota
                try {
                    dbRpc('increment_product_quota', ['product_id' => (int)$nItem['id'], 'qty' => (int)$nItem['quantity']]);
                } catch (Exception $e) {
                    $prod = getProductById((int)$nItem['id']);
                    if ($prod) {
                        $newQuota = ((int)$prod['quota_current']) + (int)$nItem['quantity'];
                        dbUpdate('products', ['quota_current' => $newQuota], ['id' => $nItem['id']]);
                    }
                }
            }
        }
        
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Checkout error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Gagal memproses pesanan: ' . $e->getMessage()];
    }

    $orderRecord = [
        'order_code' => $orderCode, 'orderId' => $orderCode, 'date' => date('d M Y'),
        'customer_name' => $buyerName, 'customerName' => $buyerName,
        'customer_phone' => $buyerPhone, 'customerPhone' => $buyerPhone,
        'jurusan' => $jurusan, 'customer_email' => '-', 'organization' => $jurusan,
        'org' => $jurusan, 'province' => $province, 'city' => $city,
        'address' => $fullAddress, 'shipping_address' => $fullAddress,
        'courier' => $courierName, 'payment_scheme' => $paymentScheme,
        'paymentScheme' => $paymentScheme, 'payment_method' => $paymentMethodName,
        'product_summary' => $firstItemSummary, 'qty' => max(1, $totalQty),
        'items' => $normalizedItems, 'subtotal' => $subtotal, 'shipping' => $shippingCost,
        'shipping_cost' => $shippingCost, 'total_price' => $totalPrice, 'total' => $totalPrice,
        'total_bayar' => $dueAmount, 'due' => $dueAmount, 'dp_amount' => $dpTotal,
        'bukti_pembayaran' => $buktiUrl, 'payment_status' => $paymentStatus,
        'paymentStatus' => $paymentStatus, 'production_status' => $productionStatus,
        'productionStatus' => $productionStatus, 'created_at' => date('Y-m-d H:i:s')
    ];

    return ['success' => true, 'message' => 'Pesanan dan bukti pembayaran berhasil diterima! Menunggu verifikasi admin.', 'order' => $orderRecord];
}

function getPaymentMethodIdByName(string $name): ?int {
    $result = dbSelectOne('payment_methods', ['id'], ['method_name' => $name]);
    return $result['id'] ?? null;
}

function getAllOrdersList(): array {
    $sql = "
        SELECT 
            o.*,
            ps.status_name as payment_status_name,
            ps.payment_scheme,
            ps.total_harga,
            ps.total_bayar,
            ps.bukti_pembayaran,
            pm.method_name as payment_method_name,
            pm.details as payment_method_details,
            COALESCE(
                jsonb_agg(
                    jsonb_build_object(
                        'id', oi.id,
                        'order_id', oi.order_id,
                        'product_id', oi.product_id,
                        'product_name', oi.product_name,
                        'size', oi.size,
                        'color', oi.color,
                        'custom_text', oi.custom_text,
                        'quantity', oi.quantity,
                        'price', oi.price,
                        'subtotal', oi.subtotal
                    )
                    ORDER BY oi.id ASC
                ) FILTER (WHERE oi.id IS NOT NULL),
                '[]'::jsonb
            ) as items
        FROM orders o
        LEFT JOIN payment_statuses ps ON ps.order_id = o.id
        LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        GROUP BY o.id, ps.status_name, ps.payment_scheme, ps.total_harga, ps.total_bayar, ps.bukti_pembayaran, pm.method_name, pm.details
        ORDER BY o.id DESC
    ";

    try {
        $orders = dbQuery($sql);
    } catch (Exception $e) {
        error_log("getAllOrdersList error: " . $e->getMessage());
        return [];
    }

    foreach ($orders as &$order) {
        $items = json_decode($order['items'] ?? '[]', true);
        $order['items'] = $items;
        
        $totalQty = 0;
        $subtotal = 0;
        foreach ($items as $item) {
            $totalQty += (int)($item['quantity'] ?? 1);
            $subtotal += (int)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }
        $order['qty'] = $totalQty;
        $order['subtotal'] = $subtotal;
        
        $order['payment_status'] = $order['payment_status_name'] ?? 'Menunggu Verifikasi';
        $order['payment_scheme'] = $order['payment_scheme'] ?? 'dp';
        $order['total_price'] = $order['total_harga'] ?? 0;
        $order['total_bayar'] = $order['total_bayar'] ?? 0;
        $order['bukti_pembayaran'] = $order['bukti_pembayaran'] ?? '';
        $order['payment_method'] = $order['payment_method_name'] ?? '';
        $order['payment_method_details'] = $order['payment_method_details'] ?? '';
    }
    
    return $orders;
}

function findOrderByResi($resi): ?array {
    $resi = trim((string)$resi);
    if ($resi === '') return null;

    $sql = "
        SELECT 
            o.*,
            ps.status_name as payment_status_name,
            ps.payment_scheme,
            ps.total_harga,
            ps.total_bayar,
            ps.bukti_pembayaran,
            COALESCE(
                jsonb_agg(
                    jsonb_build_object(
                        'id', oi.id,
                        'order_id', oi.order_id,
                        'product_id', oi.product_id,
                        'product_name', oi.product_name,
                        'size', oi.size,
                        'color', oi.color,
                        'custom_text', oi.custom_text,
                        'quantity', oi.quantity,
                        'price', oi.price,
                        'subtotal', oi.subtotal
                    )
                    ORDER BY oi.id ASC
                ) FILTER (WHERE oi.id IS NOT NULL),
                '[]'::jsonb
            ) as items
        FROM orders o
        LEFT JOIN payment_statuses ps ON ps.order_id = o.id
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.order_code = ?
        GROUP BY o.id, ps.status_name, ps.payment_scheme, ps.total_harga, ps.total_bayar, ps.bukti_pembayaran
    ";

    try {
        $row = dbQueryOne($sql, [$resi]);
    } catch (Exception $e) {
        error_log("findOrderByResi error: " . $e->getMessage());
        return null;
    }
    
    if (!$row) return null;

    $items = json_decode($row['items'] ?? '[]', true);
    $row['items'] = $items;

    $subtotal = 0;
    foreach ($items as $it) {
        $subtotal += (int)($it['price'] ?? 0) * (int)($it['quantity'] ?? 1);
    }
    $row['subtotal'] = $subtotal;
    
    $productName = 'Produk Pre-Order';
    $productMeta = '1 pcs';
    if (!empty($items)) {
        $first = $items[0];
        $productName = $first['product_name'] ?? 'Produk Pre-Order';
        $size = $first['size'] ?? 'L';
        $color = $first['color'] ?? 'Standar';
        $totalQty = 0;
        foreach ($items as $it) $totalQty += (int)($it['quantity'] ?? 1);
        $extraCount = count($items) > 1 ? ' (+' . (count($items) - 1) . ' item lainnya)' : '';
        $productMeta = "Ukuran {$size}, Warna {$color} ({$totalQty} pcs){$extraCount}";
    }
    
    $city = trim((string)($row['city'] ?? ''));
    $province = trim((string)($row['province'] ?? ''));
    $shippingAddress = trim((string)($row['shipping_address'] ?? ''));
    
    if (stripos($shippingAddress, 'Sekretariat USMA') !== false || ($city === '-' && $province === '-')) {
        $destination = 'Ambil di Sekretariat USMA';
    } elseif ($city !== '' && $city !== '-' && $province !== '' && $province !== '-') {
        $destination = $city . ', ' . $province;
    } elseif ($city !== '' && $city !== '-') {
        $destination = $city;
    } else {
        $destination = $shippingAddress !== '' ? $shippingAddress : '-';
    }

    $paymentStatus = $row['payment_status_name'] ?? 'Menunggu Verifikasi';
    $productionStatus = $row['production_status'] ?? 'Menunggu Konfirmasi Admin';
    $orderDate = !empty($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) . ' WIB' : date('d M Y');

    $prodLower = strtolower($productionStatus);
    $currentStep = 1;
    $estimatedDelivery = '14 - 21 Hari Kerja';
    if (strpos($prodLower, 'selesai') !== false) { $currentStep = 7; $estimatedDelivery = 'Pesanan Selesai Diterima'; }
    elseif (strpos($prodLower, 'pengiriman') !== false || strpos($prodLower, 'dikirim') !== false) { $currentStep = 5; $estimatedDelivery = 'Dalam Perjalanan Kurir'; }
    elseif (strpos($prodLower, 'quality') !== false || strpos($prodLower, 'qc') !== false) { $currentStep = 4; $estimatedDelivery = 'Segera Dikirim'; }
    elseif (strpos($prodLower, 'produksi') !== false) { $currentStep = 3; $estimatedDelivery = 'Estimasi 14 - 21 Hari'; }
    elseif (strpos($prodLower, 'kuota') !== false) { $currentStep = 2; $estimatedDelivery = 'Menunggu Penutupan Batch'; }

    $rawSteps = [
        ['title' => 'Pesanan Masuk & Verifikasi Pembayaran', 'desc' => 'Status pembayaran: ' . $paymentStatus . '.', 'time' => $orderDate],
        ['title' => 'Penutupan Kuota Batch PO', 'desc' => 'Pengumpulan kuota pre-order batch angkatan dan penerbitan SPK produksi.', 'time' => $currentStep > 2 ? 'Terverifikasi' : ($currentStep === 2 ? 'Sedang Berlangsung' : 'Menunggu')],
        ['title' => 'Sedang Produksi Konveksi', 'desc' => 'Pemotongan bahan, penjahitan standar konveksi, dan proses bordir/sablon.', 'time' => $currentStep > 3 ? 'Selesai Produksi' : ($currentStep === 3 ? 'Sedang Dikerjakan' : 'Menunggu')],
        ['title' => 'Quality Check & Steam Ironing', 'desc' => 'Pemeriksaan kerapihan jahitan, detail bordir/sablon, dan kesesuaian ukuran.', 'time' => $currentStep > 4 ? 'Lolos QC' : ($currentStep === 4 ? 'Proses Pemeriksaan' : 'Menunggu')],
        ['title' => 'Packing & Pengiriman / Pengambilan', 'desc' => 'Pengemasan pesanan dan penyerahan ke kurir ekspedisi atau Sekretariat USMA.', 'time' => $currentStep > 5 ? 'Terkirim' : ($currentStep === 5 ? 'Dalam Pengiriman' : 'Menunggu')],
        ['title' => 'Pesanan Selesai Diterima', 'desc' => 'Pesanan telah diterima dengan baik oleh pemesan.', 'time' => $currentStep >= 6 ? 'Selesai' : 'Menunggu']
    ];

    $timeline = [];
    foreach ($rawSteps as $index => $stepItem) {
        $stepNum = $index + 1;
        $isDone = ($stepNum < $currentStep) || ($currentStep === 7);
        $isCurrent = ($stepNum === $currentStep && $currentStep <= 6);
        $isPending = !$isDone && !$isCurrent;
        $timeline[] = ['title' => $stepItem['title'], 'desc' => $stepItem['desc'], 'time' => $stepItem['time'], 'done' => $isDone, 'current' => $isCurrent, 'pending' => $isPending];
    }

    return [
        'order_code' => $row['order_code'], 'customer_name' => $row['customer_name'],
        'organization' => $row['jurusan'], 'product_name' => $productName,
        'product_meta' => $productMeta, 'destination' => $destination,
        'estimated_delivery' => $estimatedDelivery, 'payment_status' => $paymentStatus,
        'production_status' => $productionStatus, 'timeline' => $timeline
    ];
}

function deleteOrderByCode($orderCode): bool {
    $orderCode = trim((string)$orderCode);
    if ($orderCode === '') return false;

    $pdo = getDbConnection();
    if (!$pdo) return false;

    try {
        $order = dbSelectOne('orders', ['id', 'bukti_pembayaran', 'payment_status_id'], ['order_code' => $orderCode]);
        
        if ($order) {
            if (!empty($order['bukti_pembayaran'])) {
                $path = parse_url($order['bukti_pembayaran'], PHP_URL_PATH);
                $path = basename($path);
                deleteFromSupabase(bucketBuktiPembayaran(), [$path]);
            }
            
            // Delete associated payment_statuses record
            if (!empty($order['payment_status_id'])) {
                dbDelete('payment_statuses', ['id' => $order['payment_status_id']]);
            }
            
            dbDelete('order_items', ['order_id' => $order['id']]);
            dbDelete('orders', ['order_code' => $orderCode]);
        }
    } catch (Exception $e) {
        error_log("Delete order error: " . $e->getMessage());
        return false;
    }
    
    return true;
}

function updateOrderStatusByCode($orderCode, $paymentStatus, $productionStatus): bool {
    $orderCode = trim((string)$orderCode);
    $paymentStatus = sanitize($paymentStatus);
    $productionStatus = sanitize($productionStatus);
    if ($orderCode === '') return false;

    try {
        // Get the order to find its payment_status_id
        $order = dbSelectOne('orders', ['id', 'payment_status_id'], ['order_code' => $orderCode]);
        if (!$order) {
            error_log("UpdateOrderStatusByCode: Order not found: $orderCode");
            return false;
        }

        $paymentStatusId = $order['payment_status_id'] ?? 0;
        error_log("UpdateOrderStatusByCode: orderCode=$orderCode, paymentStatusId=$paymentStatusId, paymentStatus=$paymentStatus, productionStatus=$productionStatus");
        
        // Update payment_statuses table
        if ($paymentStatusId) {
            // When status becomes "DP 50% Lunas" or "Lunas 100%", calculate total_bayar
            $psData = ['status_name' => $paymentStatus];
            if ($paymentStatus === 'DP 50% Lunas' || $paymentStatus === 'Lunas 100%') {
                // Get order details to calculate correct amount
                $orderDetails = dbSelectOne('orders', ['subtotal', 'shipping_cost', 'dp_amount', 'payment_scheme'], ['order_code' => $orderCode]);
                if ($orderDetails) {
                    if ($paymentStatus === 'DP 50% Lunas') {
                        // DP 50% = 50% of items + shipping
                        $psData['total_bayar'] = (int)$orderDetails['dp_amount'] + (int)$orderDetails['shipping_cost'];
                    } else {
                        // Lunas 100% = full items + shipping
                        $psData['total_bayar'] = (int)$orderDetails['subtotal'] + (int)$orderDetails['shipping_cost'];
                    }
                }
            }
            $psUpdate = dbUpdate('payment_statuses', $psData, ['id' => $paymentStatusId]);
            error_log("UpdateOrderStatusByCode: payment_statuses update result: " . json_encode($psUpdate));
        } else {
            error_log("UpdateOrderStatusByCode: No payment_status_id for order $orderCode");
        }
        
        // Update orders table (production_status only, payment_status is in payment_statuses)
        $orderUpdate = dbUpdate('orders', [
            'production_status' => $productionStatus
        ], ['order_code' => $orderCode]);
        error_log("UpdateOrderStatusByCode: orders update result: " . json_encode($orderUpdate));
    } catch (Exception $e) {
        error_log("UpdateOrderStatusByCode error: " . $e->getMessage());
        return false;
    }
    
    return true;
}

// ----------------------------------------------------
// 9. DASHBOARD STATS (DB ONLY - Real Data from Database)
// ----------------------------------------------------
function getDashboardStats($orders = null): array {
    if ($orders === null) $orders = getAllOrdersList();
    
    $totalOrders = count($orders);
    $totalRevenue = 0;
    $inProductionPcs = 0;
    $completedPcs = 0;
    
    foreach ($orders as $ord) {
        // Calculate subtotal from order_items if not present
        $subtotal = $ord['subtotal'] ?? 0;
        if ($subtotal === 0 && !empty($ord['items'])) {
            foreach ($ord['items'] as $item) {
                $subtotal += (int)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
            }
        }
        
        // Calculate subtotal from items (for DP 50% calculation)
        $subtotal = 0;
        foreach ($ord['items'] as $item) {
            $subtotal += (int)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
        }
        
        // Only count revenue for paid/verified orders: Lunas 100% or DP 50% Lunas
        // Dashboard "Total Nilai PO" = SUBTOTAL ONLY (no shipping/kurir)
        $payStatus = strtolower(trim((string)($ord['payment_status'] ?? '')));
        
        if ($payStatus === 'lunas 100%') {
            // Lunas 100%: subtotal ONLY (items only, NO shipping/kurir)
            $totalRevenue += $subtotal;
        } elseif ($payStatus === 'dp 50% lunas') {
            // DP 50%: half of item price ONLY (no kurir)
            $totalRevenue += (int)($subtotal / 2);
        }
        
        $status = strtolower(trim((string)($ord['production_status'] ?? '')));
        
        // Completed: only 'selesai' (exact match) - count as 1 order
        if ($status === 'selesai') {
            $completedPcs += 1;
        } 
        // Dalam Produksi: 'sedang produksi' OR 'quality check' - count as 1 order
        elseif ($status === 'sedang produksi' || $status === 'quality check') {
            $inProductionPcs += 1;
        }
    }
    
    return [
        'total_orders' => $totalOrders,
        'total_revenue' => $totalRevenue,
        'in_production_pcs' => $inProductionPcs,
        'completed_pcs' => $completedPcs
    ];
}

function resetAllPreOrderStatistics(): bool {
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            dbDelete('order_items', ['id' => ['IS NOT', 'NULL']]);
            dbDelete('orders', ['id' => ['IS NOT', 'NULL']]);
            
            $resetSettings = [
                ['setting_key' => 'stats_is_reset', 'setting_value' => '1'],
                ['setting_key' => 'stats_base_orders', 'setting_value' => '0'],
                ['setting_key' => 'stats_base_revenue', 'setting_value' => '0'],
                ['setting_key' => 'stats_base_production_pcs', 'setting_value' => '0'],
                ['setting_key' => 'stats_base_completed_pcs', 'setting_value' => '0']
            ];
            
            foreach ($resetSettings as $setting) {
                dbInsert('site_settings', $setting);
            }
        } catch (Exception $e) {
            error_log("Reset stats error: " . $e->getMessage());
        }
    }
    
    return true;
}

// ----------------------------------------------------
// 10. SVG MOCKUP HELPER
// ----------------------------------------------------
function getProductSvgPhp($type): string {
    switch ($type) {
        case 'varsity':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="160" height="160" rx="10" fill="#ecedee"/><path d="M45 42L80 56L115 42L135 68L120 82L112 74V130H48V74L40 82L25 68L45 42Z" fill="#111111"/><path d="M45 42L25 68L40 82L48 74V60L45 42Z" fill="#000aff"/><path d="M115 42L135 68L120 82L112 74V60L115 42Z" fill="#000aff"/><path d="M78 55V130H82V55H78Z" fill="#ffffff" opacity="0.4"/><circle cx="80" cy="75" r="2.5" fill="#000aff"/><circle cx="80" cy="90" r="2.5" fill="#000aff"/><circle cx="80" cy="105" r="2.5" fill="#000aff"/><circle cx="80" cy="120" r="2.5" fill="#000aff"/><text x="60" y="80" font-family="Montserrat, sans-serif" font-weight="700" font-size="13" fill="#ffffff">26</text></svg>';
        case 'shirt':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="160" height="160" rx="10" fill="#ecedee"/><path d="M48 38L80 48L112 38L130 62L116 74L108 68V132H52V68L44 74L30 62L48 38Z" fill="#111111"/><path d="M68 38L80 54L92 38H68Z" fill="#ecedee"/><rect x="58" y="72" width="16" height="18" rx="2" fill="#2b2b2b"/><circle cx="80" cy="70" r="2" fill="#a1a4aa"/><circle cx="80" cy="85" r="2" fill="#a1a4aa"/><circle cx="80" cy="100" r="2" fill="#a1a4aa"/><circle cx="80" cy="115" r="2" fill="#a1a4aa"/><path d="M79 48V132H81V48H79Z" fill="#ffffff" opacity="0.3"/></svg>';
        case 'hoodie':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="160" height="160" rx="10" fill="#ecedee"/><ellipse cx="80" cy="40" rx="26" ry="16" fill="#2b2b2b"/><path d="M46 44L80 50L114 44L134 74L118 84L110 76V130H50V76L42 84L26 74L46 44Z" fill="#111111"/><path d="M60 100H100L96 124H64L60 100Z" fill="#2b2b2b"/><path d="M74 54V76M86 54V76" stroke="#000aff" stroke-width="2.5" stroke-linecap="round"/><text x="80" y="85" text-anchor="middle" font-family="Montserrat, sans-serif" font-weight="700" font-size="9" fill="#ffffff" letter-spacing="1">FASTENDER</text></svg>';
        case 'tshirt':
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="160" height="160" rx="10" fill="#ecedee"/><path d="M52 40L80 48L108 40L126 62L112 72L106 66V130H54V66L48 72L34 62L52 40Z" fill="#111111"/><path d="M70 40C70 46 90 46 90 40Z" fill="#ecedee"/><rect x="65" y="70" width="30" height="20" rx="3" fill="#000aff"/><text x="80" y="84" text-anchor="middle" font-family="Montserrat, sans-serif" font-weight="700" font-size="8" fill="#ffffff">PO 2026</text></svg>';
        default:
            return '<svg viewBox="0 0 160 160" width="100%" height="100%" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="160" height="160" rx="10" fill="#ecedee"/><path d="M52 40L80 48L108 40L126 62L112 72L106 66V130H54V66L48 72L34 62L52 40Z" fill="#111111"/></svg>';
    }
}

// ----------------------------------------------------
// 11. 3D SHOWCASE
// ----------------------------------------------------
function get3DShowcaseConfig(): array {
    $result = dbSelectOne('site_settings', ['setting_value'], ['setting_key' => 'showcase_3d_config']);
    if ($result && !empty($result['setting_value'])) {
        return json_decode($result['setting_value'], true);
    }
    
    return [
        'image_url' => '', 'title' => 'Emblem Resmi Fastender 2026',
        'subtitle' => 'Convert PNG to 3D Realtime Berotasi Pelan 360', 'depth' => 8,
        'rotation_speed' => 0.006, 'metalness' => 0.35, 'roughness' => 0.3,
        'updated_at' => date('Y-m-d H:i:s')
    ];
}

function save3DShowcaseConfig($postData, $fileData = null): array {
    $current = get3DShowcaseConfig();
    $imageUrl = $current['image_url'];
    
    if (!empty($fileData['name']) && $fileData['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if ($ext === 'png') {
            $path = '3d/model_' . time() . '.png';
            $imageUrl = uploadToSupabase(bucketWebData(), $path, $fileData['tmp_name'], 'image/png');
        }
    } elseif (!empty($postData['image_base64'])) {
        $path = '3d/model_' . time() . '.png';
        $imageUrl = uploadToSupabase(bucketWebData(), $path, $postData['image_base64'], 'image/png');
    }
    
    $config = [
        'image_url' => $imageUrl,
        'title' => sanitize($postData['title'] ?? $current['title']),
        'subtitle' => sanitize($postData['subtitle'] ?? $current['subtitle']),
        'depth' => floatval($postData['depth'] ?? 8),
        'rotation_speed' => floatval($postData['rotation_speed'] ?? 0.006),
        'metalness' => floatval($postData['metalness'] ?? 0.35),
        'roughness' => floatval($postData['roughness'] ?? 0.3),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    dbUpdate('site_settings', ['setting_value' => json_encode($config)], ['setting_key' => 'showcase_3d_config']);
    
    return $config;
}

// ----------------------------------------------------
// 12. HERO SECTION & COUNTDOWN BANNER (DB ONLY, EMPTY DEFAULTS)
// ----------------------------------------------------
function getHeroSettings(): array {
    static $cached = null;
    if ($cached !== null) return $cached;

    $defaults = [
        'title' => '', 'badge' => '',
        'description' => '', 'countdown_label' => 'PO DITUTUP DALAM', 
        'countdown_deadline' => '', 'banner_image' => '', 
        'cta_text' => 'Pesan Sekarang', 'cta_link' => 'katalog.php'
    ];
    
    $rows = dbSelect('site_settings', ['setting_key', 'setting_value'], ['setting_key' => ['LIKE', 'hero_%']]);
    
    if (!empty($rows)) {
        $map = [];
        foreach ($rows as $r) $map[$r['setting_key']] = $r['setting_value'];
        
        $dbBanner = trim($map['hero_banner_image'] ?? '');
        $legacyDefaults = ['assets/images/banner-po-default.svg', 'assets/images/hero-showcase.png'];
        if (in_array($dbBanner, $legacyDefaults, true)) $dbBanner = '';
        
        $cached = [
            'title' => $map['hero_title'] ?? $defaults['title'],
            'badge' => $map['hero_badge'] ?? $defaults['badge'],
            'description' => $map['hero_description'] ?? $defaults['description'],
            'countdown_label' => $map['hero_countdown_label'] ?? $defaults['countdown_label'],
            'countdown_deadline' => $map['hero_countdown_deadline'] ?? $defaults['countdown_deadline'],
            'banner_image' => $dbBanner,
            'cta_text' => $map['hero_cta_text'] ?? $defaults['cta_text'],
            'cta_link' => $map['hero_cta_link'] ?? $defaults['cta_link']
        ];
        
        return $cached;
    }
    
    $cached = $defaults;
    return $defaults;
}

function isPreOrderActive(): bool {
    $hero = getHeroSettings();
    $deadlineStr = trim($hero['countdown_deadline'] ?? '');
    if ($deadlineStr === '') return false;
    
    $ts = strtotime($deadlineStr);
    if ($ts === false) $ts = strtotime(str_replace('T', ' ', $deadlineStr));
    if ($ts === false) return false;
    
    return time() < $ts;
}

function getPreOrderStatus(): array {
    $hero = getHeroSettings();
    $deadlineStr = trim($hero['countdown_deadline'] ?? '');
    $isActive = false; $deadlineTs = 0;
    
    if ($deadlineStr !== '') {
        $ts = strtotime($deadlineStr);
        if ($ts === false) $ts = strtotime(str_replace('T', ' ', $deadlineStr));
        if ($ts !== false) { $deadlineTs = $ts; $isActive = time() < $deadlineTs; }
    }
    
    return ['is_active' => $isActive, 'deadline_str' => $deadlineStr, 'deadline_timestamp' => $deadlineTs, 'server_time' => time()];
}

function getHeroSliderImages(): array {
    static $cached = null;
    if ($cached !== null) return $cached;
    
    $result = dbSelectOne('site_settings', ['setting_value'], ['setting_key' => 'hero_slider_images']);
    $cached = ($result && !empty($result['setting_value'])) ? (json_decode($result['setting_value'], true) ?? []) : [];
    return $cached;
}

// ----------------------------------------------------
// 13. STORE SETTINGS (DB ONLY, EMPTY DEFAULTS)
// ----------------------------------------------------
function getStoreSettings(): array {
    static $cached = null;
    if ($cached !== null) return $cached;

    $defaults = [
        'status_toko' => 'buka', 
        'pesan_toko_tutup' => 'Pre-Order sedang ditutup sementara untuk persiapan produksi batch berikutnya. Anda tetap dapat menyimpan produk di keranjang.',
        'nomor_whatsapp' => '',
        'rekening_bca_no' => '', 'rekening_bca_nama' => '',
        'rekening_mandiri_no' => '', 'rekening_mandiri_nama' => '',
        'qris_image' => ''
    ];
    
    $result = dbSelect('site_settings', ['setting_key', 'setting_value'], ['setting_key' => ['LIKE', 'store_%']]);
    
    if (!empty($result)) {
        $map = [];
        foreach ($result as $r) $map[$r['setting_key']] = $r['setting_value'];
        
        $qrisImage = $map['store_qris_image'] ?? '';
        if ($qrisImage && str_starts_with($qrisImage, 'uploads/')) {
            $qrisImage = getSupabasePublicUrl(bucketWebData(), str_replace('uploads/', '', $qrisImage));
        }
        
        $cached = array_merge($defaults, [
            'status_toko' => $map['store_status_toko'] ?? $defaults['status_toko'],
            'pesan_toko_tutup' => $map['store_pesan_toko_tutup'] ?? $defaults['pesan_toko_tutup'],
            'nomor_whatsapp' => $map['store_nomor_whatsapp'] ?? $defaults['nomor_whatsapp'],
            'rekening_bca_no' => $map['store_rekening_bca_no'] ?? $defaults['rekening_bca_no'],
            'rekening_bca_nama' => $map['store_rekening_bca_nama'] ?? $defaults['rekening_bca_nama'],
            'rekening_mandiri_no' => $map['store_rekening_mandiri_no'] ?? $defaults['rekening_mandiri_no'],
            'rekening_mandiri_nama' => $map['store_rekening_mandiri_nama'] ?? $defaults['rekening_mandiri_nama'],
            'qris_image' => $qrisImage
        ]);
        
        return $cached;
    }
    
    $cached = $defaults;
    return $defaults;
}

function updateStoreStatus($status): string {
    $cleanStatus = (strtolower(trim($status)) === 'tutup') ? 'tutup' : 'buka';
    dbInsert('site_settings', ['setting_key' => 'store_status_toko', 'setting_value' => $cleanStatus]);
    return $cleanStatus;
}

function saveStoreSettings($postData, $filesData = null): array {
    $current = getStoreSettings();
    
    if (isset($postData['status_toko'])) {
        $val = strtolower(trim($postData['status_toko']));
        $statusToko = in_array($val, ['tutup', 'closed', '0', 'off'], true) ? 'tutup' : 'buka';
    } elseif (isset($postData['status_toko_present'])) {
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
    
    if (!empty($postData['remove_qris']) && $postData['remove_qris'] == '1') {
        $qrisImage = '';
    }
    
    if (!empty($filesData['name']) && $filesData['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($filesData['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $path = 'qris/qris_' . time() . '.' . $ext;
            $qrisImage = uploadToSupabase(bucketWebData(), $path, $filesData['tmp_name'], 'image/' . $ext);
        }
    } elseif (!empty($postData['qris_image_path'])) {
        $qrisImage = sanitize($postData['qris_image_path']);
    }
    
    $updated = [
        'store_status_toko' => $statusToko, 'store_pesan_toko_tutup' => $pesanTokoTutup,
        'store_nomor_whatsapp' => $nomorWhatsapp, 'store_rekening_bca_no' => $rekeningBcaNo,
        'store_rekening_bca_nama' => $rekeningBcaNama, 'store_rekening_mandiri_no' => $rekeningMandiriNo,
        'store_rekening_mandiri_nama' => $rekeningMandiriNama, 'store_qris_image' => $qrisImage
    ];
    
    foreach ($updated as $k => $v) {
        setSiteSetting($k, $v);
    }
    
    return array_merge($current, $updated);
}

function isStoreOpen(): bool {
    $settings = getStoreSettings();
    return strtolower(trim($settings['status_toko'] ?? 'buka')) !== 'tutup';
}

function formatWhatsAppUrl($phone = null, $text = ''): string {
    if ($phone === null || trim($phone) === '') {
        $settings = getStoreSettings();
        $phone = $settings['nomor_whatsapp'] ?? '';
    }
    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
    if (str_starts_with($clean, '0')) $clean = '62' . substr($clean, 1);
    elseif (str_starts_with($clean, '8')) $clean = '62' . $clean;
    if (empty($clean)) return '#';
    
    $url = 'https://wa.me/' . $clean;
    if (!empty($text)) $url .= '?text=' . urlencode($text);
    return $url;
}

// ----------------------------------------------------
// 14. KUOTA PRE-ORDER
// ----------------------------------------------------
function getProductTotalOrderedQuantity($productId, $productName = ''): int {
    $productId = (int)$productId;
    $productName = trim($productName);
    $pdo = getDbConnection();
    
    if (!$pdo) return 0;
    
    try {
        $result = dbRpc('get_total_ordered_quantity', [
            'p_product_id' => $productId,
            'p_product_name' => $productName
        ]);
        return (int)($result[0]['total_qty'] ?? 0);
    } catch (Exception $e) {
        return 0;
    }
}

function getProductQuotaInfo($productOrId): array {
    if (is_array($productOrId)) {
        $product = $productOrId;
    } else {
        $product = getProductById((int)$productOrId);
    }
    
    if (!$product) return ['is_full' => false, 'target' => 0, 'current' => 0, 'remaining' => 0];
    
    $target = (int)($product['quota_target'] ?? 0);
    $current = max((int)($product['quota_current'] ?? 0), (int)($product['total_ordered_qty'] ?? 0));
    
    $isFull = ($target > 0 && $current >= $target);
    $remaining = max(0, $target - $current);
    
    return ['is_full' => $isFull, 'target' => $target, 'current' => $current, 'remaining' => $remaining];
}

function isProductQuotaFull($productOrId): bool {
    return getProductQuotaInfo($productOrId)['is_full'];
}

function getFullQuotaProductIds(): array {
    $fullIds = [];
    $all = getProductsList();
    foreach ($all as $p) {
        if (getProductQuotaInfo($p)['is_full']) $fullIds[] = (int)$p['id'];
    }
    return $fullIds;
}

// ----------------------------------------------------
// 15. EXPORT PESANAN
// ----------------------------------------------------
function exportOrdersToCSV($search = '', $status = 'all'): void {
    $orders = getAllOrdersList();
    
    if ($search) {
        $search = strtolower($search);
        $orders = array_filter($orders, function($o) use ($search) {
            return stripos($o['order_code'], $search) !== false ||
                   stripos($o['customer_name'], $search) !== false ||
                   stripos($o['jurusan'], $search) !== false;
        });
    }
    
    if ($status !== 'all') {
        $orders = array_filter($orders, fn($o) => $o['production_status'] === $status);
    }
    
    $headers = ['No. Resi', 'Tanggal', 'Nama Pemesan', 'No. HP', 'Jurusan', 'Produk', 'Qty', 'Total Harga', 'Ongkir', 'Total Bayar', 'Skema Bayar', 'Metode Bayar', 'Status Bayar', 'Status Produksi', 'Alamat', 'Provinsi', 'Kota', 'Bukti Bayar', 'Dibuat'];
    
    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);
    
    foreach ($orders as $o) {
        $itemsSummary = '';
        if (!empty($o['items'])) {
            foreach ($o['items'] as $item) {
                $itemsSummary .= $item['product_name'] . ' (' . $item['size'] . ', ' . $item['color'] . ') x' . $item['quantity'] . '; ';
            }
        }
        
        $row = [
            $o['order_code'], date('d M Y H:i', strtotime($o['created_at'])),
            $o['customer_name'], $o['customer_phone'], $o['jurusan'],
            $itemsSummary ?: $o['product_summary'] ?? '', $o['qty'],
            $o['subtotal'], $o['shipping_cost'], $o['total_price'],
            strtoupper($o['payment_scheme']), $o['payment_method'] ?? '-',
            $o['payment_status'], $o['production_status'],
            $o['shipping_address'], $o['province'], $o['city'],
            $o['bukti_pembayaran'] ?: '-', $o['created_at']
        ];
        fputcsv($output, $row);
    }
    
    fclose($output);
}

// ----------------------------------------------------
// 16. CATEGORIES CRUD (DB ONLY)
// ----------------------------------------------------
function getCategories(): array {
    return dbSelect('categories', ['id', 'category_name', 'jenis'], [], 'id ASC');
}

function getCategoryById(int $id): ?array {
    return dbSelectOne('categories', ['id', 'category_name', 'jenis'], ['id' => $id]);
}

function createCategory(array $data): array {
    $data['category_name'] = sanitize($data['category_name'] ?? '');
    $data['jenis'] = sanitize($data['jenis'] ?? '');
    if (empty($data['category_name'])) throw new Exception('Nama kategori wajib diisi');
    return dbInsert('categories', $data);
}

function updateCategory(int $id, array $data): array {
    $data['category_name'] = sanitize($data['category_name'] ?? '');
    $data['jenis'] = sanitize($data['jenis'] ?? '');
    return dbUpdate('categories', $data, ['id' => $id]);
}

function deleteCategory(int $id): bool {
    $products = dbSelect('products', ['id'], ['category_id' => $id]);
    if (!empty($products)) throw new Exception('Kategori masih memiliki produk, hapus produk terlebih dahulu');
    return dbDelete('categories', ['id' => $id]);
}

// ----------------------------------------------------
// 17. PAYMENT METHODS CRUD (DB ONLY)
// ----------------------------------------------------
function getPaymentMethods(): array {
    return dbSelect('payment_methods', ['id', 'method_name', 'details'], [], 'id ASC');
}

function createPaymentMethod(array $data): array {
    $data['method_name'] = sanitize($data['method_name'] ?? '');
    $data['details'] = sanitize($data['details'] ?? '');
    if (empty($data['method_name'])) throw new Exception('Nama metode wajib diisi');
    return dbInsert('payment_methods', $data);
}

function updatePaymentMethod(int $id, array $data): array {
    $data['method_name'] = sanitize($data['method_name'] ?? '');
    $data['details'] = sanitize($data['details'] ?? '');
    return dbUpdate('payment_methods', $data, ['id' => $id]);
}

function deletePaymentMethod(int $id): bool {
    $orders = dbSelect('orders', ['id'], ['payment_method_id' => $id]);
    if (!empty($orders)) throw new Exception('Metode pembayaran masih digunakan oleh pesanan');
    return dbDelete('payment_methods', ['id' => $id]);
}

// ----------------------------------------------------
// 18. ADMINS CRUD (DB ONLY)
// ----------------------------------------------------
function getAdmins(): array {
    return dbSelect('admins', ['id', 'nama', 'email', 'role', 'created_at'], [], 'id ASC');
}

function getAdminById(int $id): ?array {
    return dbSelectOne('admins', ['id', 'nama', 'email', 'role', 'created_at'], ['id' => $id]);
}

function createAdmin(array $data): array {
    $data['nama'] = sanitize($data['nama'] ?? '');
    $data['email'] = sanitize($data['email'] ?? '');
    $data['password'] = password_hash($data['password'] ?? '', PASSWORD_DEFAULT);
    $data['role'] = sanitize($data['role'] ?? 'operator');
    $data['created_at'] = date('Y-m-d H:i:s');
    if (empty($data['nama']) || empty($data['email']) || empty($data['password'])) {
        throw new Exception('Semua field wajib diisi');
    }
    return dbInsert('admins', $data);
}

function updateAdmin(int $id, array $data): array {
    $data['nama'] = sanitize($data['nama'] ?? '');
    $data['email'] = sanitize($data['email'] ?? '');
    $data['role'] = sanitize($data['role'] ?? 'operator');
    if (!empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
        unset($data['password']);
    }
    return dbUpdate('admins', $data, ['id' => $id]);
}

function deleteAdmin(int $id): bool {
    if ($id === 1) throw new Exception('Super admin tidak dapat dihapus');
    return dbDelete('admins', ['id' => $id]);
}

// ----------------------------------------------------
// 19. SITE SETTINGS CRUD (ALL ADMIN SETTINGS IN DB)
// ----------------------------------------------------
function getSiteSetting(string $key): ?string {
    $result = dbSelectOne('site_settings', ['setting_value'], ['setting_key' => $key]);
    return $result['setting_value'] ?? null;
}

function setSiteSetting(string $key, string $value): void {
    $existing = dbSelectOne('site_settings', ['id'], ['setting_key' => $key]);
    if ($existing) {
        dbUpdate('site_settings', ['setting_value' => $value], ['setting_key' => $key]);
    } else {
        dbInsert('site_settings', ['setting_key' => $key, 'setting_value' => $value]);
    }
}

function getAllSiteSettings(): array {
    $rows = dbSelect('site_settings', ['setting_key', 'setting_value'], [], 'setting_key ASC');
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

function saveSiteSettings(array $settings): void {
    foreach ($settings as $key => $value) {
        setSiteSetting($key, $value);
    }
}

// ----------------------------------------------------
// 20. HERO SLIDER IMAGES CRUD
// ----------------------------------------------------
function saveHeroSliderImages(array $images): void {
    setSiteSetting('hero_slider_images', json_encode(array_values($images)));
}

function addHeroSliderImage(string $imageUrl): void {
    $images = getHeroSliderImages();
    $images[] = $imageUrl;
    saveHeroSliderImages($images);
}

function removeHeroSliderImage(int $index): void {
    $images = getHeroSliderImages();
    if (isset($images[$index])) {
        unset($images[$index]);
        saveHeroSliderImages(array_values($images));
    }
}

function reorderHeroSliderImages(array $newOrder): void {
    saveHeroSliderImages($newOrder);
}

// ----------------------------------------------------
// 22. ENHANCED HERO SETTINGS (includes slider)
// ----------------------------------------------------
function saveHeroSettings($postData, $filesData = null): array {
    $current = getHeroSettings();
    
    $title = sanitize($postData['title'] ?? $current['title']);
    $badge = sanitize($postData['badge'] ?? $current['badge']);
    $description = sanitize($postData['description'] ?? $current['description']);
    $countdownLabel = sanitize($postData['countdown_label'] ?? $current['countdown_label']);
    $countdownDeadline = sanitize($postData['countdown_deadline'] ?? $current['countdown_deadline']);
    $ctaText = sanitize($postData['cta_text'] ?? $current['cta_text']);
    $ctaLink = sanitize($postData['cta_link'] ?? $current['cta_link']);
    $bannerImage = $current['banner_image'];
    
    if (!empty($filesData['name']) && $filesData['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($filesData['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
            $path = 'hero/banner_' . time() . '.' . $ext;
            $bannerImage = uploadToSupabase(bucketWebData(), $path, $filesData['tmp_name'], 'image/' . $ext);
        }
    } elseif (!empty($postData['banner_image_path'])) {
        $bannerImage = sanitize($postData['banner_image_path']);
    }
    
    // Handle slider images
    if (!empty($filesData['slider_images']['name'][0])) {
        $sliderImages = getHeroSliderImages();
        $totalFiles = count($filesData['slider_images']['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            if ($filesData['slider_images']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($filesData['slider_images']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $path = 'hero/slider_' . time() . '_' . uniqid() . '.' . $ext;
                    $sliderUrl = uploadToSupabase(bucketWebData(), $path, $filesData['slider_images']['tmp_name'][$i], 'image/' . $ext);
                    $sliderImages[] = $sliderUrl;
                }
            }
        }
        saveHeroSliderImages($sliderImages);
    }
    
    $updated = [
        'hero_title' => $title, 'hero_badge' => $badge, 'hero_description' => $description,
        'hero_countdown_label' => $countdownLabel, 'hero_countdown_deadline' => $countdownDeadline,
        'hero_banner_image' => $bannerImage, 'hero_cta_text' => $ctaText, 'hero_cta_link' => $ctaLink
    ];
    
    foreach ($updated as $k => $v) {
        setSiteSetting($k, $v);
    }
    
    return array_merge($current, $updated, ['slider_images' => getHeroSliderImages()]);
}