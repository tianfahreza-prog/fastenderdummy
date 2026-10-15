<?php
/**
 * Fastender Pre-Order Platform
 * Endpoint Ekspor Data Pesanan ke Spreadsheet (Excel / CSV)
 * export-pesanan.php
 */
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

// Ambil semua pesanan dari database atau file JSON
$allOrders = getAllOrdersList();

// 1. Ambil Parameter Filter dari Query String
$searchKeyword = trim($_GET['search'] ?? '');
$filterStatus = trim($_GET['status'] ?? 'all');
$filterPayStatus = trim($_GET['payment_status'] ?? 'all');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$format = strtolower(trim($_GET['format'] ?? 'excel')); // 'excel' atau 'csv'

// 2. Terapkan Filter Data
$filteredOrders = array_filter($allOrders, function($order) use ($searchKeyword, $filterStatus, $filterPayStatus, $dateFrom, $dateTo) {
    $code = (string)($order['order_code'] ?? ($order['orderId'] ?? ''));
    $customer = (string)($order['customer_name'] ?? ($order['customerName'] ?? ''));
    $phone = (string)($order['customer_phone'] ?? ($order['customerPhone'] ?? ''));
    $jurusan = (string)($order['jurusan'] ?? ($order['organization'] ?? ($order['org'] ?? '')));
    $prodSummary = (string)($order['product_summary'] ?? ($order['productSummary'] ?? ''));
    $prodStatus = (string)($order['production_status'] ?? ($order['productionStatus'] ?? ''));
    $payStatus = (string)($order['payment_status'] ?? ($order['paymentStatus'] ?? ''));
    $orderDate = (string)($order['date'] ?? ($order['created_at'] ?? ''));

    // Filter Kata Kunci Pencarian
    if ($searchKeyword !== '') {
        $q = strtolower($searchKeyword);
        $match = (
            stripos($code, $q) !== false ||
            stripos($customer, $q) !== false ||
            stripos($phone, $q) !== false ||
            stripos($jurusan, $q) !== false ||
            stripos($prodSummary, $q) !== false
        );
        if (!$match) return false;
    }

    // Filter Status Produksi
    if ($filterStatus !== '' && $filterStatus !== 'all') {
        if (strcasecmp($prodStatus, $filterStatus) !== 0) return false;
    }

    // Filter Status Pembayaran
    if ($filterPayStatus !== '' && $filterPayStatus !== 'all') {
        if (strcasecmp($payStatus, $filterPayStatus) !== 0) return false;
    }

    // Filter Rentang Tanggal
    if ($dateFrom !== '' || $dateTo !== '') {
        $timestamp = strtotime($orderDate);
        if ($timestamp) {
            $formattedDate = date('Y-m-d', $timestamp);
            if ($dateFrom !== '' && $formattedDate < $dateFrom) return false;
            if ($dateTo !== '' && $formattedDate > $dateTo) return false;
        }
    }

    return true;
});

// Urutkan pesanan terbaru di paling atas
$filteredOrders = array_values($filteredOrders);

// 3. Tentukan Nama File Dinamis & URL Absolut
$currentDate = date('Y-m-d');
$baseHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$rawScriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseDir = ($rawScriptDir === '/' || $rawScriptDir === '.') ? '' : rtrim($rawScriptDir, '/');
$baseUrl = $baseHost . $baseDir;

// 4. Ekspor ke CSV jika parameter format=csv
if ($format === 'csv') {
    $fileName = 'Rekap_Pesanan_Fastender_' . $currentDate . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    // Tulis UTF-8 BOM agar Microsoft Excel mendeteksi encoding dengan benar
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // 11 Kolom Header Sesuai Instruksi User
    fputcsv($output, [
        'No. Resi / Kode Pesanan',
        'Tanggal Pesanan',
        'Nama Pemesan',
        'Jurusan / Kelas / Kontak WA',
        'Rincian Produk & Varian',
        'Kuantitas (Pcs)',
        'Total Tagihan (Rp)',
        'Skema Bayar',
        'Status Pembayaran',
        'Link / Status Bukti Bayar',
        'Status Pengerjaan Vendor / Produksi'
    ]);

    foreach ($filteredOrders as $o) {
        $code = $o['order_code'] ?? ($o['orderId'] ?? '-');
        $date = $o['date'] ?? (isset($o['created_at']) ? substr($o['created_at'], 0, 10) : date('Y-m-d'));
        $name = $o['customer_name'] ?? ($o['customerName'] ?? 'Pemesan');
        
        $jurusan = $o['jurusan'] ?? ($o['organization'] ?? ($o['org'] ?? '-'));
        $phone = $o['customer_phone'] ?? ($o['customerPhone'] ?? '-');
        $jurusanKontak = $jurusan . ' | WA: ' . $phone;

        // Rincian produk & varian
        $prodSummary = $o['product_summary'] ?? ($o['productSummary'] ?? 'Produk PO');
        if (!empty($o['items']) && is_array($o['items'])) {
            $itemDetails = [];
            foreach ($o['items'] as $it) {
                $custom = !empty($it['customText']) ? ' (Custom: ' . $it['customText'] . ')' : '';
                $itemDetails[] = ($it['name'] ?? 'Produk') . ' [' . ($it['size'] ?? 'L') . '/' . ($it['color'] ?? 'Standar') . $custom . '] x' . ($it['quantity'] ?? 1);
            }
            $prodSummary = implode('; ', $itemDetails);
        }

        $qty = $o['qty'] ?? 1;
        $total = (int)($o['total_price'] ?? ($o['total'] ?? 0));
        
        $scheme = $o['payment_scheme'] ?? ($o['paymentScheme'] ?? 'dp');
        $schemeLabel = ($scheme === 'full') ? 'Lunas 100%' : 'DP 50%';

        $payStatus = $o['payment_status'] ?? ($o['paymentStatus'] ?? 'Menunggu Verifikasi');
        $prodStatus = $o['production_status'] ?? ($o['productionStatus'] ?? 'Menunggu Konfirmasi Admin');

        // Link Bukti Bayar
        $buktiPath = $o['bukti_pembayaran'] ?? ($o['buktiPembayaran'] ?? null);
        $buktiOutput = 'Belum Upload';
        if ($buktiPath && trim((string)$buktiPath) !== '') {
            $buktiOutput = (strpos($buktiPath, 'http') === 0) ? $buktiPath : ($baseUrl . '/' . ltrim($buktiPath, '/'));
        }

        fputcsv($output, [
            $code,
            $date,
            $name,
            $jurusanKontak,
            $prodSummary,
            $qty,
            $total,
            $schemeLabel,
            $payStatus,
            $buktiOutput,
            $prodStatus
        ]);
    }

    fclose($output);
    exit;
}

// 5. Default Format: Microsoft Excel (.xlsx / .xls kompatibel dengan styling rapi)
$fileName = 'Rekap_Pesanan_Fastender_' . $currentDate . '.xlsx';
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
  <meta charset="UTF-8">
  <!--[if gte mso 9]>
  <xml>
    <x:ExcelWorkbook>
      <x:ExcelWorksheets>
        <x:ExcelWorksheet>
          <x:Name>Rekap Pesanan PO</x:Name>
          <x:WorksheetOptions>
            <x:DisplayGridlines/>
          </x:WorksheetOptions>
        </x:ExcelWorksheet>
      </x:ExcelWorksheets>
    </x:ExcelWorkbook>
  </xml>
  <![endif]-->
  <style>
    body { font-family: Calibri, 'Segoe UI', Arial, sans-serif; font-size: 11pt; color: #111111; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; }
    th {
      background-color: #619ae6;
      color: #ffffff;
      font-weight: bold;
      border: 1px solid #4a84d4;
      padding: 10px 12px;
      text-align: left;
      vertical-align: middle;
    }
    td {
      border: 1px solid #d1d5db;
      padding: 8px 10px;
      vertical-align: middle;
      font-size: 10.5pt;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    .row-even { background-color: #f8fafc; }
    .row-odd { background-color: #ffffff; }
    .badge-pending { color: #b45309; font-weight: bold; }
    .badge-verified { color: #0284c7; font-weight: bold; }
    .badge-lunas { color: #15803d; font-weight: bold; }
    .badge-rejected { color: #b91c1c; font-weight: bold; }
    .link-proof { color: #2563eb; text-decoration: underline; }
    .title-banner { font-size: 16pt; font-weight: bold; color: #111111; }
    .subtitle-banner { font-size: 10pt; color: #64748b; margin-bottom: 12px; }
  </style>
</head>
<body>

  <div class="title-banner">REKAP DATA PESANAN PRE-ORDER FASTENDER</div>
  <div class="subtitle-banner">
    Diekspor pada: <?= date('d F Y, H:i') ?> WIB &bull; 
    Total Pesanan: <?= count($filteredOrders) ?> data
    <?php if ($searchKeyword !== ''): ?> &bull; Filter Pencarian: "<?= htmlspecialchars($searchKeyword) ?>"<?php endif; ?>
    <?php if ($filterStatus !== '' && $filterStatus !== 'all'): ?> &bull; Status Produksi: "<?= htmlspecialchars($filterStatus) ?>"<?php endif; ?>
  </div>

  <table>
    <thead>
      <tr>
        <th class="text-center" style="width: 45px;">No.</th>
        <th style="width: 140px;">No. Resi / Kode Pesanan</th>
        <th style="width: 110px;">Tanggal Pesanan</th>
        <th style="width: 170px;">Nama Pemesan</th>
        <th style="width: 220px;">Jurusan / Kelas / Kontak WA</th>
        <th style="width: 260px;">Rincian Produk &amp; Varian</th>
        <th class="text-center" style="width: 70px;">Kuantitas (Pcs)</th>
        <th class="text-right" style="width: 130px;">Total Tagihan</th>
        <th class="text-center" style="width: 110px;">Skema Bayar</th>
        <th class="text-center" style="width: 150px;">Status Pembayaran</th>
        <th style="width: 200px;">Link / Status Bukti Bayar</th>
        <th style="width: 170px;">Status Pengerjaan Vendor / Produksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($filteredOrders)): ?>
        <tr>
          <td colspan="12" class="text-center" style="padding: 24px; color: #64748b;">
            Tidak ada data pesanan yang sesuai dengan kriteria filter saat ini.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($filteredOrders as $idx => $o): 
          $code = $o['order_code'] ?? ($o['orderId'] ?? '-');
          $date = $o['date'] ?? (isset($o['created_at']) ? substr($o['created_at'], 0, 10) : date('Y-m-d'));
          $name = $o['customer_name'] ?? ($o['customerName'] ?? 'Pemesan');
          
          $jurusan = $o['jurusan'] ?? ($o['organization'] ?? ($o['org'] ?? '-'));
          $phone = $o['customer_phone'] ?? ($o['customerPhone'] ?? '-');
          $jurusanKontak = $jurusan . '<br><small style="color: #64748b;">WA: ' . htmlspecialchars($phone) . '</small>';

          // Rincian produk & varian
          $prodSummary = $o['product_summary'] ?? ($o['productSummary'] ?? 'Produk PO');
          if (!empty($o['items']) && is_array($o['items'])) {
            $itemDetails = [];
            foreach ($o['items'] as $it) {
              $custom = !empty($it['customText']) ? ' (Custom: ' . htmlspecialchars($it['customText']) . ')' : '';
              $itemDetails[] = htmlspecialchars($it['name'] ?? 'Produk') . ' [' . htmlspecialchars($it['size'] ?? 'L') . '/' . htmlspecialchars($it['color'] ?? 'Standar') . $custom . '] x' . (int)($it['quantity'] ?? 1);
            }
            $prodSummary = implode('<br>', $itemDetails);
          } else {
            $prodSummary = htmlspecialchars($prodSummary);
          }

          $qty = (int)($o['qty'] ?? 1);
          $total = (int)($o['total_price'] ?? ($o['total'] ?? 0));
          
          $scheme = $o['payment_scheme'] ?? ($o['paymentScheme'] ?? 'dp');
          $schemeLabel = ($scheme === 'full') ? 'Lunas 100%' : 'DP 50%';

          $payStatus = $o['payment_status'] ?? ($o['paymentStatus'] ?? 'Menunggu Verifikasi');
          $prodStatus = $o['production_status'] ?? ($o['productionStatus'] ?? 'Menunggu Konfirmasi Admin');

          // Styling status pembayaran
          $payClass = 'badge-pending';
          if (stripos($payStatus, 'Lunas') !== false) {
            $payClass = 'badge-lunas';
          } elseif (stripos($payStatus, 'Terverifikasi') !== false || stripos($payStatus, 'DP') !== false) {
            $payClass = 'badge-verified';
          } elseif (stripos($payStatus, 'Tolak') !== false || stripos($payStatus, 'Tidak Valid') !== false) {
            $payClass = 'badge-rejected';
          }

          // Link Bukti Bayar
          $buktiPath = $o['bukti_pembayaran'] ?? ($o['buktiPembayaran'] ?? null);
          $hasBukti = !empty($buktiPath) && trim((string)$buktiPath) !== '';
          $fullBuktiUrl = '';
          if ($hasBukti) {
            $fullBuktiUrl = (strpos($buktiPath, 'http') === 0) ? $buktiPath : ($baseUrl . '/' . ltrim($buktiPath, '/'));
          }
        ?>
        <tr class="<?= ($idx % 2 === 0) ? 'row-even' : 'row-odd' ?>">
          <td class="text-center"><?= $idx + 1 ?></td>
          <td class="bold"><?= htmlspecialchars($code) ?></td>
          <td><?= htmlspecialchars($date) ?></td>
          <td class="bold"><?= htmlspecialchars($name) ?></td>
          <td><?= $jurusanKontak ?></td>
          <td><?= $prodSummary ?></td>
          <td class="text-center"><?= $qty ?></td>
          <td class="text-right bold" style="mso-number-format:'\#\,\#\#0';">
            <?= formatRupiah($total) ?>
          </td>
          <td class="text-center"><?= htmlspecialchars($schemeLabel) ?></td>
          <td class="text-center <?= $payClass ?>"><?= htmlspecialchars($payStatus) ?></td>
          <td>
            <?php if ($hasBukti): ?>
              <a href="<?= htmlspecialchars($fullBuktiUrl) ?>" target="_blank" class="link-proof">
                Lihat Bukti Transfer ↗
              </a>
            <?php else: ?>
              <span style="color: #94a3b8; font-style: italic;">Belum Upload</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($prodStatus) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
