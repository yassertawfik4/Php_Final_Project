<?php
session_start();
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Php_Final_Project');
}
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Order.php';
require_once BASE_PATH . '/models/OrderItem.php';
require_once BASE_PATH . '/models/Product.php';

$pdo            = getDB();
$orderModel     = new Order($pdo);
$orderItemModel = new OrderItem($pdo);
$productModel   = new Product($pdo);

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/?page=login');
    exit;
}
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
switch ($action) {
    case 'place':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=admin.manual_order');
            exit;
        }
        if ($role === 'admin' && !empty($_POST['user_id'])) {
            $userId = (int) $_POST['user_id'];
        } else {
            $userId = (int) $_SESSION['user_id'];
        }
        $room  = trim($_POST['room']  ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $items = $_POST['items']      ?? [];
        $errors = [];
        if (empty($room)) {
            $errors[] = 'Please select a room.';
        }
        if (empty($items)) {
            $errors[] = 'Please add at least one item.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_manual_order'] = ['room' => $room, 'notes' => $notes, 'user_id' => $userId];
            $redirect = ($role === 'admin')
                ? BASE_URL . '/?page=admin.manual_order'
                : BASE_URL . '/';
            header("Location: $redirect");
            exit;
        }
        $total      = 0;
        $validItems = [];

        foreach ($items as $productId => $qty) {
            $productId = (int) $productId;
            $qty       = (int) $qty;
            if ($qty <= 0) continue;
            $product = $productModel->findById($productId);
            if (!$product) continue;

            $total        += $product['price'] * $qty;
            $validItems[]  = [
                'product_id' => $product['id'],
                'qty'        => $qty,
                'price'      => $product['price'],
            ];
        }
        if (empty($validItems)) {
            $_SESSION['errors'] = ['No valid items found. Please try again.'];
            $_SESSION['old_manual_order'] = ['room' => $room, 'notes' => $notes, 'user_id' => $userId];
            $redirect = ($role === 'admin')
                ? BASE_URL . '/?page=admin.manual_order'
                : BASE_URL . '/';
            header("Location: $redirect");
            exit;
        }
        $orderId = $orderModel->create($userId, $room, $notes, $total);
        foreach ($validItems as $item) {
            $orderItemModel->create(
                $orderId,
                $item['product_id'],
                $item['qty'],
                $item['price']
            );
        }

        $_SESSION['success'] = 'Order #' . $orderId . ' placed successfully.';

        $redirect = ($role === 'admin')
            ? BASE_URL . '/?page=admin.manual_order'
            : BASE_URL . '/';
        header("Location: $redirect");
        exit;

    case 'cancel':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }

        if ($role !== 'user') {
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }
        $order = $orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['errors'] = ['Order not found.'];
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }

        if ($order['user_id'] != $_SESSION['user_id']) {
            $_SESSION['errors'] = ['Unauthorized action.'];
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }

        if ($order['status'] !== 'processing') {
            $_SESSION['errors'] = ['Only processing orders can be cancelled.'];
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }
        $orderModel->cancel($orderId, $_SESSION['user_id']);
        $_SESSION['success'] = 'Order cancelled successfully.';
        header('Location: ' . BASE_URL . '/?page=user.orders');
        exit;

    case 'deliver':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        if ($role !== 'admin') {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        $orderModel->updateStatus($orderId, 'out_for_delivery');
        $_SESSION['success'] = 'Order is now out for delivery.';
        header('Location: ' . BASE_URL . '/');
        exit;
    case 'done':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        if ($role !== 'admin') {
            header('Location: ' . BASE_URL . '/?page=login');
            exit;
        }
        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ' . BASE_URL . '/');
            exit;
        }
        $orderModel->updateStatus($orderId, 'done');
        $_SESSION['success'] = 'Order marked as done.';
        header('Location: ' . BASE_URL . '/');
        exit;
    default:
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
}