<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 3));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Php_Final_Project');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Add Category</h2>
        <a href="<?= BASE_URL ?>/?page=admin.products" class="btn btn-outline-secondary">Back</a>
    </div>

    <?php if (!empty($_SESSION['flash'])): $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show">
            <?= htmlspecialchars($flash['message'] ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/?page=admin.category.save" method="post" class="card p-4 shadow-sm" novalidate>
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Category</button>
    </form>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
