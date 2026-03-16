<?php
	require_once BASE_PATH . '/includes/header.php'; 
	require_once BASE_PATH . '/includes/navbar.php';
?>

<style>
    .add-user-container {
        background: #f9f9f9;
        min-height: calc(100vh - 56px);
        padding: 32px 16px;
    }

    .form-card {
        background: #fff;
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        max-width: 720px;
        margin: 0 auto;
        overflow: hidden;
    }

    .form-header {
        padding: 24px;
        border-bottom: 1px solid #e4e4e4;
    }

    .form-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #2d2d2d;
    }

    .form-header p {
        margin: 6px 0 0 0;
        color: #606060;
        font-size: 0.95rem;
    }

    .form-body {
        padding: 24px;
    }

    .error-alert {
        background: #fff4f2;
        border: 1px solid #f0c7c3;
        color: #9e1c13;
        padding: 12px 14px;
        border-radius: 6px;
        margin-bottom: 20px;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 18px;
    }

    .error-alert li {
        margin-bottom: 4px;
    }

    .form-section {
        margin-bottom: 20px;
    }

    .form-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #2f2f2f;
        margin-bottom: 12px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.95rem;
        color: #333;
    }

    .form-group input,
    .form-group select {
        padding: 10px 12px;
        border: 1px solid #cfcfcf;
        border-radius: 4px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #1f4b7a;
        box-shadow: 0 0 0 2px rgba(31, 75, 122, 0.12);
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-start;
        padding-top: 12px;
        border-top: 1px solid #e4e4e4;
        margin-top: 8px;
    }

    .btn-submit,
    .btn-reset,
    .btn-cancel {
        padding: 10px 18px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        border: 1px solid #cfcfcf;
        background: #f7f7f7;
        color: #2d2d2d;
        text-decoration: none;
        transition: background 0.15s ease, border-color 0.15s ease;
    }

    .btn-submit {
        background: #1f4b7a;
        border-color: #1f4b7a;
        color: #fff;
    }

    .btn-submit:hover {
        background: #193f66;
        border-color: #193f66;
    }

    .btn-reset:hover,
    .btn-cancel:hover {
        background: #ededed;
        border-color: #bdbdbd;
    }

    @media (max-width: 600px) {
        .form-body {
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="add-user-container">
    <div class="form-card">
        <div class="form-header">
            <h1>Add User</h1>
            <p>Enter user details</p>
        </div>

        <div class="form-body">
            <?php if (!empty($errors)): ?>
                <div class="error-alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= BASE_URL ?>/?page=admin.create_user" enctype="multipart/form-data">
                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" id="name" name="name" placeholder="Name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirm">Confirm Password *</label>
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirm Password">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="room">Room No.</label>
                            <input type="text" id="room" name="room" placeholder="Room No." value="<?= htmlspecialchars($old['room'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ext">Ext.</label>
                            <input type="text" id="ext" name="ext" placeholder="Ext." value="<?= htmlspecialchars($old['ext'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="image">Profile picture</label>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="role">Role *</label>
                            <select id="role" name="role" required>
                                <?php $roleValue = $old['role'] ?? 'user'; ?>
                                <option value="user" <?= $roleValue === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $roleValue === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save</button>
                    <button type="reset" class="btn-reset">Reset</button>
                    <a class="btn-cancel" href="<?= BASE_URL ?>/?page=admin.users">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
