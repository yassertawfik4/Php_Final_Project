<?php 
	require_once BASE_PATH . '/includes/header.php'; 
	require_once BASE_PATH . '/includes/navbar.php';
?>

<style>
.users-topbar {
    margin-top: 150px;
    background: #0f5ea8;
    border-radius: 16px;
    padding: 18px 20px;
    color: #fff;
}

.users-topbar h2,
.users-topbar p {
    color: #fff;
}

.users-chip {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 0.8rem;
    font-weight: 700;
}

.users-table-card {
    border-radius: 16px;
    overflow: hidden;
}

.users-table-card .table th:first-child,
.users-table-card .table td:first-child {
    padding-left: 1rem;
}

.users-avatar {
    width: 52px;
    height: 52px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #dbe4ee;
}
</style>

<main class="app-main">
    <div class="container page-shell">
        <div class="users-topbar d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h2 class="mb-1">Users</h2>
                <p class="mb-0">Manage cafeteria users.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class=""><?= count($users ?? []) ?> Users</span>
                <a class="btn btn-light fw-bold" href="<?= BASE_URL ?>/?page=admin.add_user">
                    <i class="bi bi-person-plus-fill me-1"></i> Add User
                </a>
            </div>
        </div>

        <?php if (empty($users)): ?>
        <div class="alert alert-info">No users found.</div>
        <?php else: ?>
        <div class="table-responsive surface-panel users-table-card p-2">
            <table class="table table-hover align-middle mb-0">
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
                        <td><img src="<?=BASE_URL?>/public/uploads/<?=htmlspecialchars($user['image'] ?? '')?>"
                                alt="User Image" class="users-avatar"></td>
                        <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['room'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['ext'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['created_at'] ?? '') ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary"
                                href="<?= BASE_URL ?>/?page=admin.show_update_user&id=<?= (int)$user['id'] ?>">Edit</a>
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

        <?php endif; ?>
    </div>
</main>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>