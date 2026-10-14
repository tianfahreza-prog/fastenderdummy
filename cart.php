<?php
/**
 * Fastender Pre-Order Platform
 * Redirect Alias: cart.php -> keranjang.php
 */
$queryString = $_SERVER['QUERY_STRING'] ?? '';
$target = 'keranjang.php' . (!empty($queryString) ? '?' . $queryString : '');
header('Location: ' . $target, true, 301);
exit;
