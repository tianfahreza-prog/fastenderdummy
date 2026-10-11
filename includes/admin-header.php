<?php
/**
 * Fastender Pre-Order Platform
 * Admin Header Topbar Component (includes/admin-header.php)
 */
$adminTitle = $adminTitle ?? 'Dashboard';
?>
<!-- Top Header Admin -->
<header class="admin-header">
  <div style="display: flex; align-items: center; gap: 14px;">
    <button id="toggle-admin-sidebar" class="nav-toggle-btn" style="background: none; border: none; cursor: pointer; color: var(--color-ink-black, #111111); display: flex; align-items: center; padding: 4px;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
      </svg>
    </button>
    <div style="font-size: 14px; color: var(--color-ash, #737780);">
      <a href="admin-dashboard.php" style="color: var(--color-ink-black, #111111);">Admin</a> &nbsp;/&nbsp; <strong style="color: var(--color-ink-black, #111111);"><?= htmlspecialchars($adminTitle) ?></strong>
    </div>
  </div>

  <div class="header-user-group">
    <div class="admin-profile">
      <div class="admin-avatar">AD</div>
      <div class="admin-info">
        <div class="admin-name">Admin Fastender</div>
        <div class="admin-role">Super Administrator</div>
      </div>
    </div>
  </div>
</header>
