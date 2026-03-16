<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Php_Final_Project');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($order) || !isset($items)) {
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/models/Order.php';
    require_once BASE_PATH . '/models/OrderItem.php';
    $pdo = getDB();
    $orderModel = new Order($pdo);
    $orderItemModel = new OrderItem($pdo);
    $userId = (int) ($_SESSION['user_id'] ?? 0);
    if ($userId <= 0) {
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }
    $orderId = (int) ($_GET['id'] ?? 0);
    if ($orderId <= 0) {
        $_SESSION['errors'] = ['Invalid order.'];
        header('Location: ' . BASE_URL . '/?page=orders');
        exit;
    }
    $order = $orderModel->findById($orderId);
    if (!$order) {
        $_SESSION['errors'] = ['Order not found.'];
        header('Location: ' . BASE_URL . '/?page=orders');
        exit;
    }
    if ((int) $order['user_id'] !== $userId) {
        $_SESSION['errors'] = ['You can only view your own orders.'];
        header('Location: ' . BASE_URL . '/?page=orders');
        exit;
    }
    $items = $orderItemModel->getByOrder($orderId);
}

$userName = $userName ?? $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'User';

function formatOrderStatus($status) {
    $map = [
        'processing'       => 'Processing',
        'out_for_delivery' => 'Out for delivery',
        'done'             => 'Done',
    ];
    return $map[$status] ?? $status;
}

function formatOrderDate($datetime) {
    if (empty($datetime)) return '';
    $ts = strtotime($datetime);
    return $ts ? date('Y/m/d h:i A', $ts) : $datetime;
}
?>
<?php 
require_once BASE_PATH . '/includes/header.php'; 
require_once BASE_PATH . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Order #<?= (int) $order['id'] ?></h2>
    </div>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><strong>Date:</strong> <?= htmlspecialchars(formatOrderDate($order['created_at'] ?? '')) ?></span>
            <span>
                <span class="badge bg-secondary"><?= htmlspecialchars(formatOrderStatus($order['status'] ?? '')) ?></span>
                <?php if (($order['status'] ?? '') === 'processing'): ?>
                    <form method="post" action="<?= BASE_URL ?>/?page=order.cancel" class="d-inline ms-2" onsubmit="return confirm('Cancel this order?');">
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">CANCEL ORDER</button>
                    </form>
                <?php endif; ?>
            </span>
        </div>
        <div class="card-body">
            <?php if (!empty($order['room'])): ?>
                <p class="mb-1"><strong>Room:</strong> <?= htmlspecialchars($order['room']) ?></p>
            <?php endif; ?>
            <?php if (!empty(trim($order['notes'] ?? ''))): ?>
                <p class="mb-0"><strong>Notes:</strong> <?= htmlspecialchars($order['notes']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Items</h5>
        </div>
        <div class="card-body">
            <?php if (empty($items)): ?>
                <p class="text-muted mb-0">No items in this order.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php
                    $orderTotal = 0;
                    foreach ($items as $item):
                        $subtotal = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                        $orderTotal += $subtotal;
                    ?>
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($item['product_image']) ?>" alt="" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-cup-hot text-secondary fs-2"></i>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <strong><?= htmlspecialchars($item['product_name'] ?? '') ?></strong>
                                <div class="text-muted small"><?= (float) ($item['price'] ?? 0) ?> LE × <?= (int) ($item['quantity'] ?? 0) ?></div>
                            </div>
                            <span class="fw-semibold">EGP <?= number_format($subtotal, 0) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="d-flex justify-content-end mt-3 pt-3 border-top">
                    <strong class="fs-5">Total: EGP <?= number_format($orderTotal, 0) ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= BASE_URL ?>/?page=orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to My Orders
        </a>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
