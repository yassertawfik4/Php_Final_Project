<?php require_once BASE_PATH . '/includes/header.php'; ?>

<div class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h2 class="mb-0">Users</h2>
		<a class="btn btn-primary" href="<?= BASE_URL ?>/?page=admin.add_user">Add User</a>
	</div>

	<?php if (empty($users)): ?>
		<div class="alert alert-info">No users found.</div>
	<?php else: ?>
		<div class="table-responsive">
			<table class="table table-striped align-middle">
				<thead>
					<tr>
                        <th>Image</th>
						<th>Name</th>
						<th>Email</th>
						<th>Room</th>
						<th>Ext</th>
						<th>Role</th>
						<th>Created</th>
						<th class="text-end">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($users as $user): ?>
						<tr>
                            <td><img src="<?=BASE_URL?>/public/uploads/<?=htmlspecialchars($user['image'] ?? '')?>" alt="User Image" class="img-thumbnail" style="max-width: 100px; max-height: 100px;"></td>
							<td><?= htmlspecialchars($user['name'] ?? '') ?></td>
							<td><?= htmlspecialchars($user['email'] ?? '') ?></td>
							<td><?= htmlspecialchars($user['room'] ?? '') ?></td>
							<td><?= htmlspecialchars($user['ext'] ?? '') ?></td>
							<td><?= htmlspecialchars($user['role'] ?? '') ?></td>
							<td><?= htmlspecialchars($user['created_at'] ?? '') ?></td>
							<td class="text-end">
								<a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/?page=admin.show_update_user&id=<?= (int)$user['id'] ?>">Edit</a>
								<form class="d-inline" method="post" action="<?= BASE_URL ?>/?page=admin.delete_user">
									<input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
									<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ($totalPages > 1): ?>
			<nav>
				<ul class="pagination">
					<?php for ($i = 1; $i <= $totalPages; $i++): ?>
						<li class="page-item <?= $i === $pageNum ? 'active' : '' ?>">
							<a class="page-link" href="<?= BASE_URL ?>/?page=admin.users&p=<?= $i ?>"><?= $i ?></a>
						</li>
					<?php endfor; ?>
				</ul>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>