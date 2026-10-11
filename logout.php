<?php
/**
 * Fastender Pre-Order Platform
 * Logout Handler (logout.php)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Logout - Fastender</title>
  <script>
    // Bersihkan sesi client-side juga
    localStorage.removeItem("fastender_admin_logged_in");
    sessionStorage.removeItem("fastender_admin_logged_in");
    window.location.replace("admin-login.php?msg=logged_out");
  </script>
</head>
<body style="font-family: 'Montserrat', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc;">
  <p>Mengakhiri sesi admin... Mengalihkan ke halaman login...</p>
</body>
</html>
