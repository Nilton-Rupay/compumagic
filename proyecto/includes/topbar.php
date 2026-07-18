<?php
$iniciales_admin = '';
foreach (explode(' ', trim($_SESSION['admin_nombre'] ?? '')) as $palabra) {
    if ($palabra !== '' && strlen($iniciales_admin) < 2) {
        $iniciales_admin .= mb_strtoupper(mb_substr($palabra, 0, 1));
    }
}
?>
<div class="topbar">
    <h1><?php echo isset($titulo) ? htmlspecialchars($titulo) : 'Compu Magic'; ?></h1>
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></span>
        <div class="user-avatar"><?php echo htmlspecialchars($iniciales_admin ?: '?'); ?></div>
    </div>
</div>
