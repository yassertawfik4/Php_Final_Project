<?php include BASE_PATH . "/includes/header.php"; ?>
<?php include BASE_PATH . "/includes/navbar.php"; ?>
<?php 
require_once BASE_PATH . "/models/Order.php";
require_once BASE_PATH . "/models/User.php";
require_once BASE_PATH . "/models/OrderItem.php";

// Initialize database and models
$pdo = getDB();
$orderModel = new Order($pdo);
$userModel = new User($pdo);
$orderItemModel = new OrderItem($pdo);

// Get filter parameters
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$userId = $_GET['user_id'] ?? null;

// Fetch all users for dropdown
$users = $userModel->getAll(1000, 0);

// Fetch checks data
$checksData = $orderModel->getAllWithUsers($dateFrom, $dateTo, $userId);

// Build detailed checks array with orders and items
$checks = [];
if ($checksData) {
    foreach ($checksData as $check) {
        $orders = $orderModel->getByUserAndDate($check['id'], $dateFrom, $dateTo);
        $orderIds = array_column($orders, 'id');
        $itemsByOrder = $orderItemModel->getByOrderIds($orderIds);
        
        $ordersWithItems = [];
        foreach ($orders as $order) {
            $items = [];
            if (isset($itemsByOrder[$order['id']])) {
                foreach ($itemsByOrder[$order['id']] as $item) {
                    $items[] = [
                        'name' => $item['product_name'],
                        'emoji' => $item['product_image'],
                        'qty' => (int)$item['quantity'],
                        'price' => (float)$item['price'],
                    ];
                }
            }
            
            $ordersWithItems[] = [
                'id' => $order['id'],
                'date' => date('Y-m-d H:i A', strtotime($order['created_at'])),
                'amount' => (float)$order['total_price'],
                'status' => $order['status'],
                'items' => $items,
            ];
        }
        
        $nameParts = explode(' ', $check['name']);
        $initials = (strlen($nameParts[0]) > 0 ? $nameParts[0][0] : '') . 
                   (isset($nameParts[1]) && strlen($nameParts[1]) > 0 ? $nameParts[1][0] : '');
        
        $checks[] = [
            'id' => (int)$check['id'],
            'name' => $check['name'],
            'initials' => strtoupper($initials),
            'total' => (float)$check['total'],
            'orders' => $ordersWithItems,
        ];
    }
}
?>

<style>
.checks-topbar {
    margin-top: 150px;
    background: linear-gradient(120deg, #0f5ea8, #0a4681);
    border-radius: 16px;
    padding: 28px 24px;
    color: #fff;
    box-shadow: 0 12px 26px rgba(15, 94, 168, 0.2);
    margin-bottom: 32px;
}

.checks-topbar h2,
.checks-topbar p {
    color: #fff;
    margin: 0;
}

.checks-topbar h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.checks-topbar p {
    font-size: 0.95rem;
    opacity: 0.9;
}

.filter-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #dbe4ee;
}

.filter-card .form-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.checks-table-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #dbe4ee;
}

.checks-table-header {
    padding: 20px 24px;
    border-bottom: 1px solid #dbe4ee;
    background: #f8fafc;
}

.checks-table-header h5 {
    margin: 0;
    font-weight: 700;
    color: #101828;
}

.checks-table-header small {
    color: #667085;
    font-size: 0.85rem;
}

.checks-table {
    margin-bottom: 0;
}

.checks-table thead th {
    background: #f8fafc;
    border-bottom: 2px solid #dbe4ee;
    padding: 14px 16px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #667085;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.checks-table tbody td {
    padding: 16px;
    border-bottom: 1px solid #dbe4ee;
    vertical-align: middle;
}

.checks-table tbody tr:last-child td {
    border-bottom: none;
}

.user-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.user-row:hover td {
    background-color: #f0f4f8;
}

.user-row.expanded td {
    background-color: #f0f4f8;
}

.expand-icon {
    display: inline-block;
    transition: transform 0.2s ease;
    font-size: 0.85rem;
    color: #a0aec0;
    margin-right: 8px;
}

.user-row.expanded .expand-icon {
    transform: rotate(90deg);
    color: #0f5ea8;
}

.prod-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0f5ea8, #0a4681);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.8rem;
    flex-shrink: 0;
    margin-right: 12px;
}

.user-name-cell {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #101828;
}

.user-total {
    font-weight: 700;
    color: #0f5ea8;
    font-size: 0.95rem;
}

.orders-count {
    display: inline-block;
    background: #dbeafe;
    color: #0f5ea8;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Nested tables */
.orders-subrow {
    display: none;
}

.orders-subrow.show {
    display: table-row;
}

.orders-subrow td {
    background: #f8fafc !important;
    padding: 0 !important;
}

.orders-inner {
    padding: 16px 16px 16px 60px;
}

.orders-inner-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.orders-inner-table th {
    background: #f1f5f9;
    padding: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #667085;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #dbe4ee;
}

.orders-inner-table td {
    padding: 12px;
    font-size: 0.85rem;
    border-bottom: 1px solid #f0f0f0;
    color: #475569;
}

.orders-inner-table tbody tr:last-child td {
    border-bottom: none;
}

.order-item-row {
    cursor: pointer;
    transition: background-color 0.15s ease;
}

.order-item-row:hover td {
    background-color: #fafbfc;
}

.order-item-row.expanded td {
    background-color: #fafbfc;
}

.order-date {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #667085;
}

.order-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 700;
}

.order-status.done {
    background: #d1fae5;
    color: #065f46;
}

.order-status.processing {
    background: #fef3c7;
    color: #92400e;
}

.order-status.out_for_delivery {
    background: #dbeafe;
    color: #0c4a6e;
}

/* Items Sub-Row */
.items-subrow {
    display: none;
}

.items-subrow.show {
    display: table-row;
}

.items-subrow td {
    background: #fff !important;
    padding: 16px 12px 16px 48px !important;
    border-bottom: 1px solid #dbe4ee !important;
}

.items-container {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 12px;
}

.drink-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f0f4f8;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.drink-chip:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}

.drink-chip .emoji {
    font-size: 1.2rem;
}

.drink-chip-name {
    font-weight: 600;
    color: #101828;
}

.drink-chip-qty {
    color: #667085;
    font-size: 0.75rem;
}

.order-total-row {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f5ea8;
    padding-top: 8px;
    border-top: 1px solid #dbe4ee;
    margin-top: 8px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #667085;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.3;
}

.empty-state-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #101828;
    margin-bottom: 8px;
}

.page-shell {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.surface-panel {
    background: white;
    border-radius: 16px;
    border: 1px solid #dbe4ee;
}
</style>

<main class="app-main">
    <div class="page-shell">
        <!-- Header -->
        <div class="checks-topbar d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2>Checks</h2>
                <p>Billing report by user and date range</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="admin.checks">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control"
                        value="<?php echo htmlspecialchars($dateFrom); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control"
                        value="<?php echo htmlspecialchars($dateTo); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        <?php if (!empty($users)): ?>
                        <?php foreach ($users as $u): 
                                $selected = isset($_GET["user_id"]) && $_GET["user_id"] == $u["id"] ? "selected" : "";
                            ?>
                        <option value="<?php echo htmlspecialchars($u["id"]); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($u["name"]); ?>
                        </option>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">
                        <i class="bi bi-search me-2"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Checks Table -->
        <div class="checks-table-card">
            <div class="checks-table-header">
                <h5>Results</h5>
                <small>
                    <?php if (empty($checks)): ?>
                    No data found for the selected date range
                    <?php else: ?>
                    <?php echo count($checks); ?> user<?php echo count($checks) !== 1 ? 's' : ''; ?> with orders
                    <?php endif; ?>
                </small>
            </div>

            <?php if (!empty($checks)): ?>
            <div class="table-responsive surface-panel p-0 mb-0">
                <table class="table checks-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>Name</th>
                            <th>Total Amount</th>
                            <th>Orders Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checks as $check): ?>

                        <!-- User Row (Level 1) -->
                        <tr class="user-row" onclick="toggleUser(<?php echo htmlspecialchars($check["id"]); ?>, this)">
                            <td><i class="bi bi-chevron-right expand-icon"></i></td>
                            <td>
                                <div class="user-name-cell">
                                    <div class="prod-avatar"><?php echo htmlspecialchars($check["initials"]); ?>
                                    </div>
                                    <?php echo htmlspecialchars($check["name"]); ?>
                                </div>
                            </td>
                            <td><span class="user-total">EGP <?php echo number_format($check["total"], 2); ?></span>
                            </td>
                            <td><span class="orders-count"><?php echo count($check["orders"]); ?> orders</span></td>
                        </tr>

                        <!-- Orders Sub-Row (Level 2) -->
                        <tr class="orders-subrow" id="user-orders-<?php echo htmlspecialchars($check["id"]); ?>">
                            <td colspan="4">
                                <div class="orders-inner">
                                    <table class="orders-inner-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 30px;"></th>
                                                <th>Order Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($check["orders"] as $order): ?>

                                            <!-- Order Row (Level 2) -->
                                            <tr class="order-item-row"
                                                onclick="toggleOrder(<?php echo htmlspecialchars($order["id"]); ?>, this)">
                                                <td><i class="bi bi-chevron-right expand-icon"
                                                        style="margin-right: 4px;"></i></td>
                                                <td>
                                                    <div class="order-date">
                                                        <i class="bi bi-calendar3"></i>
                                                        <?php echo htmlspecialchars($order["date"]); ?>
                                                    </div>
                                                </td>
                                                <td><strong style="color: #0f5ea8;">EGP
                                                        <?php echo number_format($order["amount"], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <span
                                                        class="order-status <?php echo htmlspecialchars($order["status"]); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($order["status"]))); ?>
                                                    </span>
                                                </td>
                                            </tr>

                                            <!-- Items Sub-Row (Level 3) -->
                                            <tr class="items-subrow"
                                                id="order-items-<?php echo htmlspecialchars($order["id"]); ?>">
                                                <td colspan="4">
                                                    <div class="items-container">
                                                        <?php if (!empty($order["items"])): ?>
                                                        <?php foreach ($order["items"] as $item): ?>
                                                        <div class="drink-chip">
                                                            <span
                                                                class="emoji"><?php echo htmlspecialchars($item["emoji"]); ?></span>
                                                            <div>
                                                                <div class="drink-chip-name">
                                                                    <?php echo htmlspecialchars($item["name"]); ?>
                                                                </div>
                                                                <div class="drink-chip-qty">
                                                                    ×<?php echo htmlspecialchars($item["qty"]); ?> —
                                                                    EGP
                                                                    <?php echo number_format($item["price"] * $item["qty"], 2); ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>
                                                        <?php else: ?>
                                                        <span style="color: #a0aec0; font-size: 0.85rem;">No items
                                                            in this order</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="order-total-row">
                                                        Order Total: EGP
                                                        <?php echo number_format($order["amount"], 2); ?>
                                                    </div>
                                                </td>
                                            </tr>

                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <div class="empty-state-title">No orders found</div>
                <p style="margin: 0; font-size: 0.9rem;">No orders match the selected date range and user filter.
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>


<?php include BASE_PATH . "/includes/bootstrapJs.php"; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<script>
// Toggle User Row (Level 1 → Level 2)
function toggleUser(id, row) {
    const subRow = document.getElementById(`user-orders-${id}`);
    const isOpen = subRow.classList.contains('show');

    // Close all user rows first
    document.querySelectorAll('.orders-subrow').forEach(r => r.classList.remove('show'));
    document.querySelectorAll('.user-row').forEach(r => r.classList.remove('expanded'));
    // Close all order item rows too
    document.querySelectorAll('.items-subrow').forEach(r => r.classList.remove('show'));
    document.querySelectorAll('.order-item-row').forEach(r => r.classList.remove('expanded'));

    if (!isOpen) {
        subRow.classList.add('show');
        row.classList.add('expanded');
    }
}

// Toggle Order Row (Level 2 → Level 3)
function toggleOrder(id, row) {
    const subRow = document.getElementById(`order-items-${id}`);
    const isOpen = subRow.classList.contains('show');

    // Close all items rows within same user
    document.querySelectorAll('.items-subrow').forEach(r => r.classList.remove('show'));
    document.querySelectorAll('.order-item-row').forEach(r => r.classList.remove('expanded'));

    if (!isOpen) {
        subRow.classList.add('show');
        row.classList.add('expanded');
    }
}
</script>
<?php include BASE_PATH . "/includes/footer.php"; ?>