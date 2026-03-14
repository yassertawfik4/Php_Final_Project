<?php 
require_once BASE_PATH . '/includes/header.php';
?>
<div>
    <div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php 
            $errors = is_array($_SESSION['error']) ? $_SESSION['error'] : [$_SESSION['error']]; 
            foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php 
    unset($_SESSION['error']); 
    ?>
<?php endif; ?>
<div class="card p-4 shadow" style="width:400px">

<h3 class="text-center mb-4">Cafeteria Login</h3>

<form action="<?= BASE_URL . '/?page=login' ?>" method="POST">

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Login</button>

</form>

    <div class="text-center mt-3">
    <a href="<?= BASE_URL . '/?page=forget_password' ?>">Forgot Password?</a>
    </div>

    </div>
</div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>