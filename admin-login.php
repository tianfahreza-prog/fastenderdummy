<?php
/**
 * Fastender Pre-Order Platform
 * Halaman Login Administrator (admin-login.php)
 * PHP Native
 */
require_once __DIR__ . '/includes/functions.php';

// Jika admin sudah login, langsung alihkan ke dashboard
if (isAdminLoggedIn()) {
    header('Location: admin-dashboard.php');
    exit;
}

$errorMsg = '';
$successMsg = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $successMsg = 'Anda telah berhasil keluar dari sesi Admin.';
}

// Proses form login jika method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    // Cek di DB MySQL jika tersedia
    $loginSuccess = false;
    $pdo = getDbConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                $loginSuccess = true;
            }
        } catch (Exception $e) {}
    }

    // Fallback kredensial demo bawaan: admin@fastender.id / admin123
    if (!$loginSuccess) {
        if (($email === 'admin@fastender.id' || strtolower($email) === 'admin') && $password === 'admin123') {
            $loginSuccess = true;
        }
    }

    if ($loginSuccess) {
        $_SESSION['fastender_admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'Admin Fastender';
        
        // Simpan juga ke client-side script agar komponen script.js tetap sinkron
        echo "<!DOCTYPE html><html><body><script>
            localStorage.setItem('fastender_admin_logged_in', 'true');
            window.location.replace('admin-dashboard.php');
        </script></body></html>";
        exit;
    } else {
        $errorMsg = 'Email atau kata sandi admin salah! Gunakan: admin@fastender.id / admin123';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Administrator - Fastender Pre-Order</title>
  
  <!-- Google Fonts: Montserrat & Fira Code -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="variables.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/admin.css">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
  <link rel="apple-touch-icon" href="assets/images/favicon.png">
  <script>
    // Sinkronisasi client-side
    if (localStorage.getItem("fastender_admin_logged_in") === "true") {
      // Jika di client sudah login, langsung ke dashboard
      // window.location.replace("admin-dashboard.php");
    }
  </script>
</head>
<body style="margin: 0; padding: 0;">

  <div class="admin-login-wrapper">
    <div class="admin-login-card">
      
      <!-- Brand Logo & Header -->
      <div class="login-brand-header">
        <img src="assets/images/logo.png" alt="Fastender Logo" style="width: 76px; height: 76px; border-radius: 50%; margin: 0 auto 16px; display: block; box-shadow: 0 4px 16px rgba(30, 58, 138, 0.25); border: 2px solid rgba(255, 255, 255, 0.8);">
        <h1 class="login-title">Admin Fastender</h1>
        <p class="login-subtitle">Masuk untuk mengelola data pesanan &amp; katalog produk PO</p>
      </div>

      <!-- Pesan Feedback / Alert -->
      <?php if (!empty($errorMsg)): ?>
        <div style="background: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 12px 14px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 18px;">
          <strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($successMsg)): ?>
        <div style="background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 12px 14px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 18px;">
          <?= htmlspecialchars($successMsg) ?>
        </div>
      <?php endif; ?>

      <!-- Demo Account Hint -->
      <div class="demo-account-hint">
        <strong>Akses Akun Administrator:</strong>
        <div>Email: <code>admin@fastender.id</code></div>
        <div>Password: <code>admin123</code></div>
        <button type="button" class="demo-quick-btn" onclick="quickFillAdmin()">
          Isi Otomatis Akun Admin
        </button>
      </div>

      <!-- Form Login Admin -->
      <form id="admin-login-form" method="POST" action="admin-login.php">
        <div class="form-group">
          <label class="form-label" for="admin-email">Email / Username Administrator *</label>
          <input 
            type="text" 
            name="email" 
            id="admin-email" 
            class="form-control" 
            placeholder="admin@fastender.id" 
            required 
            autocomplete="username"
            value="admin@fastender.id"
          >
        </div>

        <div class="form-group">
          <label class="form-label" for="admin-password">Kata Sandi *</label>
          <div style="position: relative;">
            <input 
              type="password" 
              name="password" 
              id="admin-password" 
              class="form-control" 
              placeholder="••••••••" 
              required 
              autocomplete="current-password"
              style="padding-right: 40px;"
              value="admin123"
            >
            <button 
              type="button" 
              id="toggle-pwd-btn" 
              onclick="togglePasswordVisibility()" 
              style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-light); font-size: 13px; font-weight: 600;"
              title="Tampilkan / Sembunyikan"
            >
              Lihat
            </button>
          </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin: 16px 0 24px;">
          <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text-muted);">
            <input type="checkbox" name="remember" id="remember-me" checked style="accent-color: var(--accent);"> Ingat saya
          </label>
          <a href="#" onclick="alert('Untuk keperluan evaluasi/pengujian PBL:\nEmail: admin@fastender.id\nPassword: admin123'); return false;" style="color: var(--accent); font-weight: 600;">
            Bantuan Akses?
          </a>
        </div>

        <!-- Tombol Oranye Aksi Sesuai Figma Specs -->
        <button type="submit" class="btn btn-accent btn-block btn-lg" style="margin-bottom: 16px;">
          Masuk ke Dashboard Admin
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>

        <div style="text-align: center;">
          <a href="index.php" style="font-size: 13px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px;">
            &larr; Kembali ke Beranda Toko
          </a>
        </div>
      </form>

    </div>
  </div>

  <!-- Scripts -->
  <script src="js/script.js"></script>
  <script>
    function quickFillAdmin() {
      document.getElementById("admin-email").value = "admin@fastender.id";
      document.getElementById("admin-password").value = "admin123";
      showToast("Kredensial admin berhasil diisi!", "info");
    }

    function togglePasswordVisibility() {
      const pwdInput = document.getElementById("admin-password");
      const btn = document.getElementById("toggle-pwd-btn");
      if (pwdInput.type === "password") {
        pwdInput.type = "text";
        btn.textContent = "Tutup";
      } else {
        pwdInput.type = "password";
        btn.textContent = "Lihat";
      }
    }
  </script>
</body>
</html>
