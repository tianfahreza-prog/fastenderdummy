<?php
/**
 * Fastender Pre-Order Platform
 * Header Component (includes/header.php)
 */
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Fastender - Platform E-Commerce Pre-Order Apparel & Merchandise';
$activePage = $activePage ?? 'beranda';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- Google Fonts: Montserrat & Fira Code -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  
  <!-- Swiper Slider CSS -->
  <link rel="stylesheet" href="css/swiper-bundle.min.css">
  
  <!-- Design System Variables & Global Stylesheet -->
  <link rel="stylesheet" href="variables.css?v=<?= file_exists(__DIR__ . '/../variables.css') ? filemtime(__DIR__ . '/../variables.css') : '1' ?>">
  <link rel="stylesheet" href="css/style.css?v=<?= file_exists(__DIR__ . '/../css/style.css') ? filemtime(__DIR__ . '/../css/style.css') : '1' ?>">
  <?php if (!empty($extraCss)): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($extraCss) ?>">
  <?php endif; ?>

  <!-- Favicon / Brand Icon -->
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
  <link rel="apple-touch-icon" href="assets/images/favicon.png">
</head>
<body>
