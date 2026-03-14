<?php

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Order.php';
require_once BASE_PATH . '/models/OrderItem.php';

class AdminController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;

    public function __construct()
    {
        $pdo = getDB();
        $this->orderModel = new Order($pdo);
        $this->orderItemModel = new OrderItem($pdo);
    }

    public function dashboard(): void
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');

        $stats = $this->orderModel->getDashboardStats();
        $orders = $this->orderModel->getCurrentOrders();

        $orderIds = array_map(static fn(array $order): int => (int)$order['id'], $orders);
        $itemsByOrder = $this->orderItemModel->getByOrderIds($orderIds);

        foreach ($orders as &$order) {
            $id = (int)$order['id'];
            $order['items'] = $itemsByOrder[$id] ?? [];
        }
        unset($order);

        require_once BASE_PATH . '/views/admin/dashboard.php';
    }

    public function updateOrderStatus(): void
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=admin.dashboard');
            exit;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['out_for_delivery', 'done'];

        if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
            $_SESSION['dashboard_error'] = 'Invalid request.';
            header('Location: ' . BASE_URL . '/?page=admin.dashboard');
            exit;
        }

        $order = $this->orderModel->findById($orderId);
        if (!$order) {
            $_SESSION['dashboard_error'] = 'Order not found.';
            header('Location: ' . BASE_URL . '/?page=admin.dashboard');
            exit;
        }

        $currentStatus = $order['status'] ?? '';
        $isValidTransition =
            ($status === 'out_for_delivery' && $currentStatus === 'processing') ||
            ($status === 'done' && in_array($currentStatus, ['processing', 'out_for_delivery'], true));

        if (!$isValidTransition) {
            $_SESSION['dashboard_error'] = 'Invalid status transition.';
            header('Location: ' . BASE_URL . '/?page=admin.dashboard');
            exit;
        }

        $this->orderModel->updateStatus($orderId, $status);
        $_SESSION['dashboard_success'] = $status === 'done'
            ? 'Order marked as done.'
            : 'Order marked as out for delivery.';

        header('Location: ' . BASE_URL . '/?page=admin.dashboard');
        exit;
    }
}
