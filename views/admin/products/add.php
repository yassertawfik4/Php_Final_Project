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
		<h2 class="mb-0">Add Product</h2>
		<a href="<?= BASE_URL ?>/?page=admin.products" class="btn btn-outline-secondary">Back</a>
	</div>

	<?php if (!empty($_SESSION['flash'])): $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
		<div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show">
			<?= htmlspecialchars($flash['message'] ?? '') ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php endif; ?>

	<form action="<?= BASE_URL ?>/?page=admin.product.save" method="post" enctype="multipart/form-data" class="card p-4 shadow-sm">
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Name</label>
				<input type="text" name="name" class="form-control" required>
			</div>
			<div class="col-md-3">
				<label class="form-label">Price (LE)</label>
				<input type="number" name="price" class="form-control" min="0.1" step="0.1" required>
			</div>
			<div class="col-md-3">
				<label class="form-label">Availability</label>
				<select name="is_available" class="form-select">
					<option value="1">Available</option>
					<option value="0">Unavailable</option>
				</select>
			</div>
			<div class="col-md-6">
				<label class="form-label">Category</label>
				<select name="category_id" class="form-select" required>
					<option value="">Choose...</option>
					<?php foreach ($categories as $cat): ?>
						<option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-md-6">
				<label class="form-label">Image</label>
				<input type="file" name="image" accept="image/*" class="form-control">
			</div>
		</div>
		<div class="mt-4">
			<button type="submit" class="btn btn-primary">Save Product</button>
		</div>
	</form>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
