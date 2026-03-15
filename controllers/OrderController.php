<?php

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Order.php';
require_once BASE_PATH . '/models/OrderItem.php';
require_once BASE_PATH . '/models/Product.php';

class OrderController
{
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private Product $productModel;

    public function __construct()
    {
        $pdo = getDB();
        $this->orderModel = new Order($pdo);
        $this->orderItemModel = new OrderItem($pdo);
        $this->productModel = new Product($pdo);
    }

    /**
     * Place a new order
     */
    public function place(): void
    {
        require_once BASE_PATH . '/includes/auth_check.php';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=home');
            exit;
        }

        // Determine user ID (admin can place orders for users)
        if ($_SESSION['role'] === 'admin' && !empty($_POST['user_id'])) {
            require_role('admin');
            $userId = (int)$_POST['user_id'];
        } else {
            require_role('user');
            $userId = (int)$_SESSION['user_id'];
        }

        $room = trim($_POST['room'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $items = $_POST['items'] ?? [];
        $errors = [];

        if (empty($room)) {
            $errors[] = 'Please select a room.';
        }
        if (empty($items)) {
            $errors[] = 'Please add at least one item.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $redirect = ($_SESSION['role'] === 'admin')
                ? '/?page=admin.manual_order'
                : '/?page=home';
            header('Location: ' . BASE_URL . $redirect);
            exit;
        }

        $total = 0;
        $validItems = [];

        foreach ($items as $productId => $qty) {
            $productId = (int)$productId;
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            $product = $this->productModel->findById($productId);
            if (!$product) continue;
            $isAvailable = (int)($product['is_available'] ?? $product['available'] ?? 1);
            if ($isAvailable !== 1) {
                continue;
            }

            $total += $product['price'] * $qty;
            $validItems[] = [
                'product_id' => $product['id'],
                'qty' => $qty,
                'price' => $product['price'],
            ];
        }

        if (empty($validItems)) {
            $_SESSION['errors'] = ['No valid items found. Please try again.'];
            $redirect = ($_SESSION['role'] === 'admin')
                ? '/?page=admin.manual_order'
                : '/?page=home';
            header('Location: ' . BASE_URL . $redirect);
            exit;
        }

        $orderId = $this->orderModel->create($userId, $room, $notes, $total);
        foreach ($validItems as $item) {
            $this->orderItemModel->create(
                $orderId,
                $item['product_id'],
                $item['qty'],
                $item['price']
            );
        }

        $_SESSION['success'] = 'Order placed successfully!';

        $redirect = ($_SESSION['role'] === 'admin')
            ? '/?page=admin.manual_order'
            : '/?page=home';
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }

    /**
     * Cancel a user order
     */
    public function cancel(): void
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('user');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
        }

        $orderId = (int)($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
        }

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['errors'] = ['Order not found.'];
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
        }

        if ($order['user_id'] != $_SESSION['user_id']) {
            $_SESSION['errors'] = ['Unauthorized action.'];
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
        }

        if ($order['status'] !== 'processing') {
            $_SESSION['errors'] = ['Only processing orders can be cancelled.'];
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
        }

        $this->orderModel->cancel($orderId, $_SESSION['user_id']);
        $_SESSION['success'] = 'Order cancelled successfully.';
        header('Location: ' . BASE_URL . '/?page=orders');
        exit;
    }
}