<?php require_once BASE_PATH . '/includes/header.php'; ?>

<div class="container py-4">
	<h2 class="mb-3">Add User</h2>

	<?php if (!empty($errors)): ?>
		<div class="alert alert-danger">
			<ul class="mb-0">
				<?php foreach ($errors as $error): ?>
					<li><?= htmlspecialchars($error) ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<form method="post" action="<?= BASE_URL ?>/?page=admin.create_user" enctype="multipart/form-data">
		<div class="row g-3">
			<div class="col-md-6">
				<label class="form-label">Name</label>
				<input type="text" name="name" class="form-control" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Email</label>
				<input type="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
			</div>
			<div class="col-md-6">
				<label class="form-label">Password</label>
				<input type="password" name="password" class="form-control" required>
			</div>
			<div class="col-md-3">
				<label class="form-label">Room</label>
				<input type="text" name="room" class="form-control" value="<?= htmlspecialchars($old['room'] ?? '') ?>">
			</div>
			<div class="col-md-3">
				<label class="form-label">Ext</label>
				<input type="text" name="ext" class="form-control" value="<?= htmlspecialchars($old['ext'] ?? '') ?>">
			</div>
			<div class="col-md-6">
				<label class="form-label">Image</label>
				<input type="file" name="image" class="form-control" accept="image/*">
			</div>
			<div class="col-md-6">
				<label class="form-label">Role</label>
				<select name="role" class="form-select">
					<?php $roleValue = $old['role'] ?? 'user'; ?>
					<option value="user" <?= $roleValue === 'user' ? 'selected' : '' ?>>User</option>
					<option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Admin</option>
				</select>
			</div>
		</div>

		<div class="mt-4 d-flex gap-2">
			<button type="submit" class="btn btn-primary">Create</button>
			<a class="btn btn-secondary" href="<?= BASE_URL ?>/?page=admin.users">Cancel</a>
		</div>
	</form>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
