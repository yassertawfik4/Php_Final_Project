<?php
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';

$stats = $stats ?? [
    'processing' => 0,
    'out_for_delivery' => 0,
    'done_today' => 0,
    'today_revenue' => 0,
];
$orders = $orders ?? [];

$successMessage = $_SESSION['dashboard_success'] ?? null;
$errorMessage = $_SESSION['dashboard_error'] ?? null;
unset($_SESSION['dashboard_success'], $_SESSION['dashboard_error']);
?>

<style>
    .admin-container {
        background: linear-gradient(135deg, rgba(15, 94, 168, 0.05) 0%, rgba(231, 165, 53, 0.05) 100%);
        min-height: calc(100vh - 56px);
        padding: 40px 20px;
    }

    .admin-header {
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        border-radius: 20px;
        padding: 50px 40px;
        color: white;
        margin-bottom: 40px;
        box-shadow: 0 20px 60px rgba(15, 94, 168, 0.15);
    }

    .admin-header h1 {
        font-size: 2.2rem;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: white;
    }

    .admin-header p {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(15, 26, 45, 0.08);
        border: 1px solid #dbe4ee;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        box-shadow: 0 15px 40px rgba(15, 26, 45, 0.12);
        transform: translateY(-4px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 16px;
    }

    .stat-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #101828;
        margin-bottom: 6px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #667085;
        font-weight: 600;
    }

    .orders-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .orders-section-header h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #101828;
        margin: 0;
    }

    .section-meta {
        color: #667085;
        font-size: 0.9rem;
    }

    .order-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 16px;
        box-shadow: 0 10px 30px rgba(15, 26, 45, 0.08);
        border: 1px solid #dbe4ee;
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 15px 40px rgba(15, 26, 45, 0.12);
    }

    .order-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .user-info {
        flex: 1;
    }

    .user-name {
        font-weight: 700;
        font-size: 1.05rem;
        color: #101828;
        margin-bottom: 6px;
    }

    .order-meta {
        font-size: 0.85rem;
        color: #667085;
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .badge-processing {
        background: #fff4df;
        color: #bf7b14;
    }

    .badge-delivery {
        background: #e6f0ff;
        color: #0f5ea8;
    }

    .item-chip {
        display: inline-block;
        background: #f9fbff;
        border: 1px solid #dbe4ee;
        border-radius: 10px;
        padding: 10px 14px;
        margin: 4px 4px 4px 0;
        font-size: 0.85rem;
        color: #344054;
    }

    .item-qty {
        display: inline-block;
        color: #667085;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 6px;
    }

    .order-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .order-total {
        font-weight: 800;
        color: #0f5ea8;
        font-size: 1.1rem;
    }

    .order-actions {
        display: flex;
        gap: 12px;
    }

    .btn-action {
        padding: 8px 16px;
        border: none;
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-send {
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        color: white;
    }

    .btn-send:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 94, 168, 0.25);
        color: white;
        text-decoration: none;
    }

    .btn-done {
        background: #e8f8f0;
        color: #11845b;
    }

    .btn-done:hover {
        background: #d0f0e0;
        color: #11845b;
        text-decoration: none;
    }

    .alert {
        border-radius: 12px;
        margin-bottom: 24px;
        padding: 16px 20px;
        border: 2px solid transparent;
    }

    .alert-success {
        background: #ecfdf3;
        border-color: #c8eed8;
        color: #0d6f4c;
    }

    .alert-danger {
        background: #fef3f2;
        border-color: #f6d0d4;
        color: #b42318;
    }

    .empty-state {
        text-align: center;
        padding: 60px 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 26, 45, 0.08);
    }

    .empty-icon {
        font-size: 3.5rem;
        margin-bottom: 16px;
    }

    @media (max-width: 768px) {
        .admin-header {
            padding: 40px 30px;
        }

        .admin-header h1 {
            font-size: 1.8rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .order-card-header {
            flex-direction: column;
        }

        .order-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .order-actions {
            width: 100%;
            margin-top: 16px;
        }

        .btn-action {
            flex: 1;
            justify-content: center;
        }
    }
</style>

<div class="admin-container">
    <div class="container">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Monitor current orders and track daily performance</p>
        </div>

        <?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($successMessage) ?>
        </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff4df; color: #bf7b14;"></div>
                <div class="stat-value"><?= (int)$stats['processing'] ?></div>
                <div class="stat-label">Processing Orders</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e6f0ff; color: #0f5ea8;"></div>
                <div class="stat-value"><?= (int)$stats['out_for_delivery'] ?></div>
                <div class="stat-label">Out for Delivery</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f8f0; color: #11845b;"></div>
                <div class="stat-value"><?= (int)$stats['done_today'] ?></div>
                <div class="stat-label">Completed Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff4df; color: #bf7b14;"></div>
                <div class="stat-value">EGP <?= number_format((float)$stats['today_revenue'], 0) ?></div>
                <div class="stat-label">Today's Revenue</div>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="orders-section-header">
            <div>
                <h2>Current Orders</h2>
                <span class="section-meta">Real-time order tracking</span>
            </div>
            <div class="section-meta">📅 <?= date('M d, Y') ?></div>
        </div>

        <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-icon"></div>
            <h3 style="color: #101828; margin: 0 0 10px 0;">No Active Orders</h3>
            <p style="color: #667085; margin: 0;">All orders have been completed or there are no pending orders at the moment.</p>
        </div>
        <?php endif; ?>

        <?php foreach ($orders as $order): ?>
        <?php
            $status = $order['status'] ?? 'processing';
            $badgeClass = $status === 'out_for_delivery' ? 'badge-delivery' : 'badge-processing';
            $badgeLabel = $status === 'out_for_delivery' ? 'Out for Delivery' : 'Processing';
        ?>
        <div class="order-card">
            <div class="order-card-header">
                <div class="user-info">
                    <div class="user-name">
                        <?= htmlspecialchars($order['user_name'] ?? 'Unknown User') ?>
                    </div>
                    <div class="order-meta">
                        <span>Room <?= htmlspecialchars((string)($order['room'] ?? '-')) ?></span>
                        <span>Ext. <?= htmlspecialchars((string)($order['ext'] ?? '-')) ?></span>
                        <span><?= date('M d, h:i A', strtotime((string)$order['created_at'])) ?></span>
                    </div>
                </div>
                <span class="status-badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
            </div>

            <div>
                <?php if (!empty($order['items'])): ?>
                <?php foreach ($order['items'] as $item): ?>
                <span class="item-chip">
                    <?= htmlspecialchars($item['product_name'] ?? 'Item') ?>
                    <span class="item-qty">x<?= (int)($item['quantity'] ?? 0) ?></span>
                </span>
                <?php endforeach; ?>
                <?php else: ?>
                <span style="color: #667085; font-size: 0.9rem;">No items found for this order.</span>
                <?php endif; ?>
            </div>

            <div class="order-footer">
                <div class="order-total">Total: EGP <?= number_format((float)($order['total_price'] ?? 0), 2) ?></div>

                <div class="order-actions">
                    <?php if ($status === 'processing'): ?>
                    <form method="post" action="<?= BASE_URL ?>/?page=admin.update_order_status" style="flex: 1; margin-right: 8px;">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="out_for_delivery">
                        <button class="btn-action btn-send" type="submit" style="width: 100%; justify-content: center;">
                            Send
                        </button>
                    </form>
                    <?php endif; ?>

                    <form method="post" action="<?= BASE_URL ?>/?page=admin.update_order_status" style="flex: 1;">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <input type="hidden" name="status" value="done">
                        <button class="btn-action btn-done" type="submit" style="width: 100%; justify-content: center;">
                            Mark Done
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
