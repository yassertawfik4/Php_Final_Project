<?php
	require_once BASE_PATH . '/includes/header.php';
	require_once BASE_PATH . '/includes/navbar.php';
?>

<style>
    .edit-user-container {
        background: linear-gradient(135deg, rgba(15, 94, 168, 0.05) 0%, rgba(231, 165, 53, 0.05) 100%);
        min-height: calc(100vh - 56px);
        padding: 40px 20px;
    }

    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 26, 45, 0.08);
        border: 1px solid #dbe4ee;
        max-width: 800px;
        margin: 0 auto;
        overflow: hidden;
    }

    .form-header {
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        padding: 50px 40px;
        color: white;
    }

    .form-header h1 {
        font-size: 2rem;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: white;
    }

    .form-header p {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    .form-body {
        padding: 40px;
    }

    .error-alert {
        background: #fef3f2;
        border: 2px solid #f6d0d4;
        color: #b42318;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 24px;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .error-alert li {
        margin-bottom: 6px;
    }

    .form-section {
        margin-bottom: 32px;
    }

    .form-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #344054;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f0f0f0;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 700;
        font-size: 0.9rem;
        color: #344054;
        margin-bottom: 8px;
    }

    .form-group small {
        font-size: 0.8rem;
        color: #667085;
        margin-top: 4px;
    }

    .form-group input,
    .form-group select {
        padding: 12px 16px;
        border: 2px solid #dbe4ee;
        border-radius: 12px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #0f5ea8;
        box-shadow: 0 0 0 4px rgba(15, 94, 168, 0.1);
        background: #f9fbff;
    }

    .form-group input::placeholder {
        color: #a1afc9;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        padding-top: 24px;
        border-top: 1px solid #f0f0f0;
    }

    .btn-submit {
        padding: 12px 28px;
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 94, 168, 0.25);
    }

    .btn-cancel {
        padding: 12px 28px;
        background: #f0f0f0;
        color: #344054;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-cancel:hover {
        background: #dbe4ee;
        color: #344054;
    }

    @media (max-width: 768px) {
        .form-header {
            padding: 40px 30px;
        }

        .form-body {
            padding: 30px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-submit,
        .btn-cancel {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="edit-user-container">
    <div class="form-card">
        <div class="form-header">
            <h1>Edit User</h1>
            <p>Update user account information</p>
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

            <form method="post" action="<?= BASE_URL ?>/?page=admin.update_user" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= (int)($user['id'] ?? 0) ?>">

                <!-- Personal Information Section -->
                <div class="form-section">
                    <div class="form-section-title">Personal Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" placeholder="Enter user full name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="Enter user email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Room Information Section -->
                <div class="form-section">
                    <div class="form-section-title">Room Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="room">Room Number</label>
                            <input type="text" id="room" name="room" placeholder="e.g., 101, A-305" value="<?= htmlspecialchars($user['room'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ext">Extension</label>
                            <input type="text" id="ext" name="ext" placeholder="e.g., 2345" value="<?= htmlspecialchars($user['ext'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Security Section -->
                <div class="form-section">
                    <div class="form-section-title">Security</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
                            <small>Leave blank to keep the current password</small>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Section -->
                <div class="form-section">
                    <div class="form-section-title">Additional Information</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="image">Profile Picture</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <small>Leave blank to keep current picture</small>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <a class="btn-cancel" href="<?= BASE_URL ?>/?page=admin.users">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
