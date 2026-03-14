<?php
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';

?>

<div class="container mt-4">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?>!</h1>
    <p>This is your orders page. You can view your profile, check your orders, and explore our menu.</p>

</div>

<?php
require_once BASE_PATH . '/includes/footer.php';
