<?php
$role = $_SESSION['user_role'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= BASE_URL ?>/?page=home">
            <i class="bi bi-shop me-1"></i> PHP Shop
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <?php if ($role === 'admin'): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=admin.dashboard">
                        Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=admin.products">
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=admin.users">
                        Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=admin.users">
                        Mannual Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=admin.users">
                        Checks
                        </a>
                    </li>
                    
                    
                </ul>
            <?php elseif ($role === 'user'): ?>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=home">
                        Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/?page=orders">
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
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                            <img src="<?=BASE_URL?>/public/uploads/<?=htmlspecialchars($_SESSION['user_image'] ?? '')?>" alt="User Image" class="img-thumbnail" style="max-width: 40px; max-height: 40px;">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= BASE_URL ?>/?page=logout">
                                    <i class="bi bi-box-arrow-right me-1"></i>Logout
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
