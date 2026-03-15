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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="mb-0">Products</h2>
            <div class="text-muted small">Manage cafeteria products and availability</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/?page=admin.category.add">Add Category</a>
            <a class="btn btn-primary" href="<?= BASE_URL ?>/?page=admin.product.add">Add Product</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash'])): $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
    <div class="alert alert-info">No products found. Start by adding a new product.</div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="d-flex align-items-center gap-2">
                        <?php if (!empty($product['image'])): ?>
                        <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($product['image']) ?>" alt=""
                            style="width: 40px; height: 40px; object-fit: cover;" class="rounded">
                        <?php else: ?>
                        <i class="bi bi-cup-hot text-secondary"></i>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($product['name']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($product['category_name'] ?? '') ?></td>
                    <td><?= number_format((float)($product['price'] ?? 0), 2) ?> LE</td>
                    <td>
                        <?php $isAvailable = (int)($product['is_available'] ?? $product['available'] ?? 1); ?>
                        <span class="badge bg-<?= $isAvailable ? 'success' : 'secondary' ?>">
                            <?= $isAvailable ? 'Available' : 'Unavailable' ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary"
                            href="<?= BASE_URL ?>/?page=admin.product.add&id=<?= (int)$product['id'] ?>">Edit</a>
                        <a class="btn btn-sm btn-outline-warning"
                            href="<?= BASE_URL ?>/?page=admin.product.toggle&id=<?= (int)$product['id'] ?>&p=<?= (int)$page ?>">
                            <?= $isAvailable ? 'Disable' : 'Enable' ?>
                        </a>
                        <form method="post" action="<?= BASE_URL ?>/?page=admin.product.delete" class="d-inline"
                            onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pages ?? 1) > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="<?= BASE_URL ?>/?page=admin.products&p=<?= max(1, ($page ?? 1) - 1) ?>">&lt;</a>
            </li>
            <li class="page-item active"><span class="page-link"><?= (int)($page ?? 1) ?></span></li>
            <li class="page-item <?= ($page ?? 1) >= ($pages ?? 1) ? 'disabled' : '' ?>">
                <a class="page-link"
                    href="<?= BASE_URL ?>/?page=admin.products&p=<?= min(($pages ?? 1), ($page ?? 1) + 1) ?>">&gt;</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>