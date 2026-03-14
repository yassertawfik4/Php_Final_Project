<?php
$role = $_SESSION['user_role'] ?? '';
$currentPage = $_GET['page'] ?? '';

$isActive = static function (array $pages) use ($currentPage): string {
    return in_array($currentPage, $pages, true) ? 'active' : '';
};
?>

<aside class="app-sidebar" id="app-sidebar">
    <div class="sidebar-brand">Cafeteria</div>

    <?php if ($role === 'admin'): ?>
    <small class="sidebar-section-label">Main</small>
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>/?page=admin.dashboard" class="nav-link <?= $isActive(['admin.dashboard']) ?>">
            <i class="bi bi-house me-2"></i> Dashboard
        </a>
        <a href="#" class="nav-link disabled" aria-disabled="true">
            <i class="bi bi-pencil-square me-2"></i> Manual Order
        </a>
        <a href="<?= BASE_URL ?>/?page=admin.checks" class="nav-link <?= $isActive(['admin.checks']) ?>">
            <i class="bi bi-receipt me-2"></i> Checks
        </a>
    </nav>

    <small class="sidebar-section-label">Manage</small>
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>/?page=admin.users" class="nav-link <?= $isActive(['admin.users', 'admin.add_user', 'admin.show_update_user']) ?>">
            <i class="bi bi-people me-2"></i> Users
        </a>
    </nav>

    <?php elseif ($role === 'user'): ?>
    <small class="sidebar-section-label">Main</small>
    <nav class="nav flex-column">
        <a href="<?= BASE_URL ?>/?page=home" class="nav-link <?= $isActive(['home']) ?>">
            <i class="bi bi-house me-2"></i> Home
        </a>
        <a href="<?= BASE_URL ?>/?page=orders" class="nav-link <?= $isActive(['orders']) ?>">
            <i class="bi bi-bag-check me-2"></i> My Orders
        </a>
    </nav>
    <?php endif; ?>

    <?php if ($role): ?>
    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($_SESSION['user_image'] ?? '') ?>" alt="User" class="user-avatar">
            <div>
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars(ucfirst($role)) ?></div>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/?page=logout" class="btn btn-sm btn-outline-light w-100">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
    <?php endif; ?>
</aside>
