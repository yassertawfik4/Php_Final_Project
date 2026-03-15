<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checks</title>
    <?php 
    include "../../includes/bootstrapCss.php";
    require_once "../../config/database.php";
    require_once "../../models/Order.php";
    require_once "../../models/User.php";
    require_once "../../models/OrderItem.php";
    
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
    $users = $userModel->getAll(1000, 0); // Get up to 1000 users
    
    // Fetch checks data (users with their total orders and amounts)
    $checksData = $orderModel->getAllWithUsers($dateFrom, $dateTo, $userId);
    
    // Build detailed checks array with orders and items
    $checks = [];
    if ($checksData) {
        foreach ($checksData as $check) {
            // Get orders for this user
            $orders = $orderModel->getByUserAndDate($check['id'], $dateFrom, $dateTo);
            
            // Get all order items for these orders
            $orderIds = array_column($orders, 'id');
            $itemsByOrder = $orderItemModel->getByOrderIds($orderIds);
            
            // Build items for display
            $ordersWithItems = [];
            foreach ($orders as $order) {
                $items = [];
                if (isset($itemsByOrder[$order['id']])) {
                    foreach ($itemsByOrder[$order['id']] as $item) {
                        $items[] = [
                            'name' => $item['product_name'],
                            'emoji' => $item['product_image'] ?? '☕',
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
            
            // Get user initials
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
    body {
        background-color: #f8f9fa;
    }

    #sidebar {
        width: 250px;
        min-height: 100vh;
        background-color: #1a1a2e;
        position: fixed;
        top: 0;
        left: 0;
    }

    #sidebar .sidebar-brand {
        padding: 20px;
        color: #e0a84b;
        font-size: 1.4rem;
        font-weight: 700;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    #sidebar .nav-link {
        color: rgba(255, 255, 255, 0.6);
        padding: 12px 20px;
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }

    #sidebar .nav-link:hover,
    #sidebar .nav-link.active {
        color: #e0a84b;
        background-color: rgba(224, 168, 75, 0.1);
        border-left-color: #e0a84b;
    }

    #sidebar .nav-link i {
        width: 20px;
    }

    .sidebar-section-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.25);
        padding: 16px 20px 4px;
    }

    #main-content {
        margin-left: 250px;
        padding: 24px;
    }

    /* Table */
    .table-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .table-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .checks-table th {
        background: #f8f9fa;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #999;
        font-weight: 600;
        padding: 12px 16px;
        border-bottom: 1px solid #e9ecef;
    }

    .checks-table td {
        padding: 14px 16px;
        font-size: 0.88rem;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    /* User Row */
    .user-row {
        cursor: pointer;
        transition: background 0.15s;
    }

    .user-row:hover td {
        background: #fffbf0;
    }

    .user-row.expanded td {
        background: #fffbf0;
        font-weight: 600;
    }

    .expand-icon {
        transition: transform 0.2s;
        display: inline-block;
        font-size: 0.75rem;
        color: #ccc;
    }

    .user-row.expanded .expand-icon {
        transform: rotate(90deg);
        color: #e0a84b;
    }

    /* Orders Sub-Row */
    .orders-subrow {
        display: none;
    }

    .orders-subrow.show {
        display: table-row;
    }

    .orders-subrow td {
        background: #f8f9fa !important;
        padding: 0 !important;
        border-bottom: 2px solid #e0a84b !important;
    }

    .orders-inner {
        padding: 0 16px 12px 40px;
    }

    .orders-inner-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-inner-table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #aaa;
        font-weight: 600;
        padding: 8px 12px;
        border-bottom: 1px solid #e9ecef;
        background: transparent;
    }

    .orders-inner-table td {
        padding: 10px 12px;
        font-size: 0.85rem;
        border-bottom: 1px solid #f0f0f0;
    }

    /* Order Item Row - level 3 */
    .order-item-row {
        cursor: pointer;
        transition: background 0.15s;
    }

    .order-item-row:hover td {
        background: #fff8ee;
    }

    .order-item-row.expanded td {
        background: #fff8ee;
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
        padding: 10px 12px 14px 32px !important;
    }

    .drink-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 6px 12px;
        font-size: 0.82rem;
        margin: 3px;
    }

    .drink-chip .emoji {
        font-size: 1.3rem;
    }

    .drink-chip .qty {
        color: #999;
        font-size: 0.75rem;
    }

    /* Filter Bar */
    .filter-bar {
        background: white;
        border-radius: 14px;
        border: 1px solid #e9ecef;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    /* Pagination */
    .page-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        background: white;
        color: #555;
        font-size: 0.82rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
    }

    .page-btn.active {
        background: #e0a84b;
        color: white;
        border-color: #e0a84b;
    }

    .page-btn:hover:not(.active) {
        background: #f8f9fa;
    }
    </style>
</head>

<body>

    <!-- ═══ SIDEBAR ═══ -->
    <div id="sidebar">
        <div class="sidebar-brand">☕ Cafeteria</div>
        <small class="sidebar-section-label">Main</small>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-house me-2"></i> Dashboard
            </a>
            <a href="manual_order.php" class="nav-link">
                <i class="bi bi-pencil-square me-2"></i> Manual Order
            </a>
            <a href="checks.php" class="nav-link active">
                <i class="bi bi-receipt me-2"></i> Checks
            </a>
        </nav>
        <small class="sidebar-section-label">Manage</small>
        <nav class="nav flex-column">
            <a href="products/index.php" class="nav-link">
                <i class="bi bi-cup-hot me-2"></i> Products
            </a>
            <a href="users/index.php" class="nav-link">
                <i class="bi bi-people me-2"></i> Users
            </a>
        </nav>
        <div style="position:absolute;bottom:0;width:100%;padding:16px;border-top:1px solid rgba(255,255,255,0.08)">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div
                    style="width:32px;height:32px;border-radius:50%;background:#e0a84b;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.8rem">
                    AD</div>
                <div>
                    <div style="color:rgba(255,255,255,0.8);font-size:0.85rem">Admin</div>
                    <div style="color:rgba(255,255,255,0.35);font-size:0.72rem">admin@company.com</div>
                </div>
            </div>
            <a href="../../index.php" class="btn btn-sm btn-outline-secondary w-100">Logout</a>
        </div>
    </div>

    <!-- ═══ MAIN CONTENT ═══ -->
    <div id="main-content">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Checks</h4>
                <small class="text-muted">Billing report by user and date range</small>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold"
                        style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;color:#888">Date
                        From</label>
                    <input type="date" name="date_from" class="form-control"
                        value="<?php echo htmlspecialchars($dateFrom); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"
                        style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;color:#888">Date
                        To</label>
                    <input type="date" name="date_to" class="form-control"
                        value="<?php echo htmlspecialchars($dateTo); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"
                        style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.5px;color:#888">User</label>
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
                    <button type="submit" class="btn w-100 fw-bold"
                        style="background:#e0a84b;color:white;border-radius:10px;padding:10px">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Checks Table -->
        <div class="table-card">
            <div class="table-card-header">
                <h6 class="mb-0 fw-bold">Results</h6>
                <small class="text-muted">
                    <?php if (empty($checks)): ?>
                    No data found for the selected date range
                    <?php else: ?>
                    Click on a user to view their orders
                    <?php endif; ?>
                </small>
            </div>
            <table class="table checks-table mb-0" id="checks-table">
                <thead>
                    <tr>
                        <th style="width:40px"></th>
                        <th>Name</th>
                        <th>Total Amount</th>
                        <th>Orders Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($checks)): ?>
                    <?php foreach ($checks as $check): ?>

                    <!-- User Row (Level 1) -->
                    <tr class="user-row" onclick="toggleUser(<?php echo htmlspecialchars($check["id"]); ?>, this)">
                        <td><i class="bi bi-chevron-right expand-icon"></i></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div
                                    style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#e0a84b,#6B3F2A);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.75rem;flex-shrink:0">
                                    <?php echo htmlspecialchars($check["initials"]); ?>
                                </div>
                                <?php echo htmlspecialchars($check["name"]); ?>
                            </div>
                        </td>
                        <td><strong style="color:#e0a84b">EGP <?php echo number_format($check["total"], 2); ?></strong>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?php echo count($check["orders"]); ?>
                                orders</span></td>
                    </tr>

                    <!-- Orders Sub-Row (Level 2) -->
                    <tr class="orders-subrow" id="user-orders-<?php echo htmlspecialchars($check["id"]); ?>">
                        <td colspan="4">
                            <div class="orders-inner">
                                <table class="orders-inner-table">
                                    <thead>
                                        <tr>
                                            <th style="width:30px"></th>
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
                                                    style="font-size:0.7rem;color:#ccc"></i></td>
                                            <td>
                                                <i class="bi bi-calendar3 me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($order["date"]); ?>
                                            </td>
                                            <td><strong style="color:#e0a84b">EGP
                                                    <?php echo number_format($order["amount"], 2); ?></strong></td>
                                            <td><span
                                                    class="badge bg-success"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($order["status"]))); ?></span>
                                            </td>
                                        </tr>

                                        <!-- Items Sub-Row (Level 3) -->
                                        <tr class="items-subrow"
                                            id="order-items-<?php echo htmlspecialchars($order["id"]); ?>">
                                            <td colspan="4">
                                                <div class="d-flex flex-wrap align-items-center gap-1 mb-2">
                                                    <?php if (!empty($order["items"])): ?>
                                                    <?php foreach ($order["items"] as $item): ?>
                                                    <div class="drink-chip">
                                                        <span
                                                            class="emoji"><?php echo htmlspecialchars($item["emoji"]); ?></span>
                                                        <div>
                                                            <div style="font-weight:600">
                                                                <?php echo htmlspecialchars($item["name"]); ?></div>
                                                            <div class="qty">
                                                                ×<?php echo htmlspecialchars($item["qty"]); ?> — EGP
                                                                <?php echo number_format($item["price"] * $item["qty"], 2); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                    <?php else: ?>
                                                    <span class="text-muted">No items in this order</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div
                                                    style="font-size:0.85rem;font-weight:600;color:#e0a84b;padding-left:4px">
                                                    Order Total: EGP <?php echo number_format($order["amount"], 2); ?>
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
                    <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox"
                                style="font-size:2rem;margin-bottom:10px;display:block;opacity:0.5"></i>
                            No orders found for the selected criteria
                        </td>
                    </tr>
                    <?php endif; ?>

                </tbody>
            </table>

            <!-- Pagination -->
            <div class="d-flex justify-content-center gap-1 py-3">
                <button class="page-btn"><i class="bi bi-chevron-double-left"></i></button>
                <button class="page-btn"><i class="bi bi-chevron-left"></i></button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">3</button>
                <button class="page-btn"><i class="bi bi-chevron-right"></i></button>
                <button class="page-btn"><i class="bi bi-chevron-double-right"></i></button>
            </div>
        </div>

    </div>

    <?php include "../../includes/bootstrapJs.php"; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script>
    // Toggle User Row (Level 1 → Level 2)
    function toggleUser(id, row) {
        const subRow = document.getElementById(`user-orders-${id}`);
        const isOpen = subRow.classList.contains('show');

        // close all user rows first
        document.querySelectorAll('.orders-subrow').forEach(r => r.classList.remove('show'));
        document.querySelectorAll('.user-row').forEach(r => r.classList.remove('expanded'));
        // close all order item rows too
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

        // close all items rows within same user
        document.querySelectorAll('.items-subrow').forEach(r => r.classList.remove('show'));
        document.querySelectorAll('.order-item-row').forEach(r => r.classList.remove('expanded'));

        if (!isOpen) {
            subRow.classList.add('show');
            row.classList.add('expanded');
        }
    }
    </script>

</body>

</html>