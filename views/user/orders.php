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
if (!isset($orders)) {
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
    $dateFrom = trim($_GET['date_from'] ?? '');
    $dateTo   = trim($_GET['date_to'] ?? '');
    if ($dateFrom && $dateTo) {
        $allOrders = $orderModel->getByUser($userId, $dateFrom, $dateTo);
    } else {
        $allOrders = $orderModel->getByUser($userId);
    }
    $perPage = 10;
    $totalOrders = count($allOrders);
    $totalPages = $totalOrders > 0 ? (int) ceil($totalOrders / $perPage) : 1;
    $page = max(1, min((int) ($_GET['p'] ?? 1), $totalPages));
    $offset = ($page - 1) * $perPage;
    $ordersPaginated = array_slice($allOrders, $offset, $perPage);
    foreach ($ordersPaginated as &$ord) {
        $ord['items'] = $orderItemModel->getByOrder($ord['id']);
    }
    unset($ord);
    $orders = $ordersPaginated;
    $userName = $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'User';
}
$dateFrom  = $dateFrom  ?? $_GET['date_from'] ?? '';
$dateTo    = $dateTo    ?? $_GET['date_to'] ?? '';
$page      = $page      ?? max(1, (int)($_GET['p'] ?? 1));
$totalPages = $totalPages ?? 1;
$userName  = $userName  ?? $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'User';

function formatOrderStatus($status) {
    $map = [
        'processing'      => 'Processing',
        'out_for_delivery' => 'Out for delivery',
        'done'            => 'Done',
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
        <h2 class="mb-0">My Orders</h2>
        <span class="text-muted"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($userName) ?></span>
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

    <form method="get" action="" class="row g-2 mb-4 align-items-end">
        <input type="hidden" name="page" value="orders">
        <div class="col-auto">
            <label class="form-label small mb-0">Date from</label>
            <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom ?? '') ?>">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">Date to</label>
            <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo ?? '') ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <?php if (empty($orders)): ?>
        <div class="alert alert-info">No orders found for the selected date range.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr class="order-row" data-order-id="<?= (int) $order['id'] ?>">
                            <td>
                                <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none toggle-details" aria-expanded="false" data-bs-target="#order-details-<?= (int) $order['id'] ?>">
                                    <i class="bi bi-plus-lg expand-icon"></i>
                                    <i class="bi bi-dash-lg collapse-icon d-none"></i>
                                </button>
                                <span class="ms-1"><?= htmlspecialchars(formatOrderDate($order['created_at'] ?? '')) ?></span>
                            </td>
                            <td><?= htmlspecialchars(formatOrderStatus($order['status'] ?? '')) ?></td>
                            <td><?= (float) ($order['total_price'] ?? 0) ?> EGP</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary me-2" href="<?= BASE_URL ?>/?page=order.details&id=<?= (int)$order['id'] ?>">Details</a>
                                <?php if (($order['status'] ?? '') === 'processing'): ?>
                                    <form method="post" action="<?= BASE_URL ?>/?page=order.cancel" class="d-inline" onsubmit="return confirm('Cancel this order?');">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                        <button type="submit" class="btn btn-link btn-sm text-danger p-0">CANCEL</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="order-details-row d-none" id="order-details-<?= (int) $order['id'] ?>">
                            <td colspan="4" class="bg-light pt-3 pb-3">
                                <div class="ps-4">
                                    <?php
                                    $items = $order['items'] ?? [];
                                    $orderTotal = 0;
                                    ?>
                                    <?php if (!empty($items)): ?>
                                        <div class="row g-2 mb-2">
                                            <?php foreach ($items as $item): ?>
                                                <?php $sub = ($item['price'] ?? 0) * ($item['quantity'] ?? 0); $orderTotal += $sub; ?>
                                                <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                                                    <?php if (!empty($item['product_image'])): ?>
                                                        <img src="<?= BASE_URL ?>/public/uploads/<?= htmlspecialchars($item['product_image']) ?>" alt="" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="bi bi-cup-hot text-secondary fs-4"></i>
                                                    <?php endif; ?>
                                                    <span><?= htmlspecialchars($item['product_name'] ?? '') ?></span>
                                                    <span class="text-muted small"><?= (float) ($item['price'] ?? 0) ?> LE × <?= (int) ($item['quantity'] ?? 0) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="fw-semibold text-end">Total EGP <?= number_format($orderTotal, 0) ?></div>
                                    <?php else: ?>
                                        <p class="text-muted small mb-0">No items.</p>
                                        <div class="fw-semibold text-end">Total EGP <?= number_format((float) ($order['total_price'] ?? 0), 0) ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
                        <?php
                        $prevPage = ($page ?? 1) - 1;
                        $query = $_GET;
                        $query['p'] = $prevPage;
                        $query['page'] = 'orders';
                        $href = '?' . http_build_query($query);
                        ?>
                        <a class="page-link" href="<?= $href ?>">&lt;</a>
                    </li>
                    <li class="page-item active">
                        <span class="page-link"><?= (int) ($page ?? 1) ?></span>
                    </li>
                    <li class="page-item <?= ($page ?? 1) >= ($totalPages ?? 1) ? 'disabled' : '' ?>">
                        <?php
                        $nextPage = ($page ?? 1) + 1;
                        $query = $_GET;
                        $query['p'] = $nextPage;
                        $query['page'] = 'orders';
                        $href = '?' . http_build_query($query);
                        ?>
                        <a class="page-link" href="<?= $href ?>">&gt;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function () {
    document.querySelectorAll('.toggle-details').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = this.getAttribute('data-bs-target');
            var row = targetId ? document.querySelector(targetId) : null;
            var expandIcon = this.querySelector('.expand-icon');
            var collapseIcon = this.querySelector('.collapse-icon');
            if (!row) return;
            row.classList.toggle('d-none');
            var isExpanded = !row.classList.contains('d-none');
            if (expandIcon) expandIcon.classList.toggle('d-none', isExpanded);
            if (collapseIcon) collapseIcon.classList.toggle('d-none', !isExpanded);
            this.setAttribute('aria-expanded', isExpanded);
        });
    });
})();
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>

