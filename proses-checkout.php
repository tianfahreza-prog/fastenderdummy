<?php
/**
 * Fastender Pre-Order Platform
 * Backend Controller Pemrosesan Checkout & Upload Bukti Pembayaran (proses-checkout.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

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
