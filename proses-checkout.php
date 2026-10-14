<?php
/**
 * Fastender Pre-Order Platform
 * Backend Controller Pemrosesan Checkout & Upload Bukti Pembayaran (proses-checkout.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

// Backend Guard: Tolak pembuatan pesanan jika toko sedang tutup atau batas waktu Pre-Order telah berakhir
$storeSettings = getStoreSettings();
$status_toko = $storeSettings['status_toko'] ?? 'buka';
if ($status_toko === 'tutup' || !isStoreOpen()) {
    $closeMsg = !empty($storeSettings['pesan_toko_tutup']) ? $storeSettings['pesan_toko_tutup'] : 'Pre-Order sedang ditutup sementara.';
    $isAjax = (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        !empty($_POST['is_ajax'])
    );
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $closeMsg
        ]);
        exit;
    }
    header('Location: keranjang.php?status=toko_tutup');
    exit;
}

// Backend Guard: Tolak pembuatan pesanan jika batas waktu Pre-Order telah berakhir
if (!isPreOrderActive()) {
    $isAjax = (
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        !empty($_POST['is_ajax'])
    );
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Periode Pre-Order Batch ini telah berakhir. Checkout saat ini ditutup.'
        ]);
        exit;
    }
    header('Location: keranjang.php?pesan=po_tutup');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: checkout.php');
    exit;
}

$fileData = $_FILES['bukti_pembayaran'] ?? null;
$result = processNewCheckoutOrder($_POST, $fileData);

$isAjax = (
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    !empty($_POST['is_ajax'])
);

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result);
    exit;
}

if ($result['success']) {
    $_SESSION['checkout_success_order'] = $result['order'];
    header('Location: checkout.php?success=1');
    exit;
} else {
    $_SESSION['checkout_error_message'] = $result['message'];
    header('Location: checkout.php?error=1');
    exit;
}
