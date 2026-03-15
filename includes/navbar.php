<?php
$role = $_SESSION['user_role'] ?? '';
$currentPage = $_GET['page'] ?? '';

$isActive = static function (array $pages) use ($currentPage): string {
    return in_array($currentPage, $pages, true) ? 'active' : '';
};
?>

<nav class="navbar navbar-expand-lg app-navbar shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/?page=home">
            <i class="bi bi-cup-hot-fill me-1"></i> Cafeteria
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <?php if ($role === 'admin'): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive(['admin.dashboard']) ?>" href="<?= BASE_URL ?>/?page=admin.dashboard">
                        Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="<?= BASE_URL ?>/?page=admin.products">
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive(['admin.users', 'admin.add_user', 'admin.show_update_user']) ?>" href="<?= BASE_URL ?>/?page=admin.users">
                        Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="<?= BASE_URL ?>/?page=admin.manual_order">
                        Manual Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive(['admin.checks']) ?>" href="<?= BASE_URL ?>/?page=admin.checks">
                        Checks
                        </a>
                    </li>
                </ul>
            <?php elseif ($role === 'user'): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive(['home']) ?>" href="<?= BASE_URL ?>/?page=home">
                        Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $isActive(['orders']) ?>" href="<?= BASE_URL ?>/?page=orders">
                        My Orders
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav me-auto"></ul>
            <?php endif; ?>

            <ul class="navbar-nav ms-auto">
                <?php if ($role): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                            <img src="<?=BASE_URL?>/public/uploads/<?=htmlspecialchars($_SESSION['user_image'] ?? '')?>" alt="User Image" class="user-avatar">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/?page=logout">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=login">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
