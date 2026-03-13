<?php
session_start();
require_once '../config/database.php';
require_once '../models/Order.php';
require_once '../models/OrderItem.php';
require_once '../models/Product.php';

$pdo            = getDB();
$orderModel     = new Order($pdo);
$orderItemModel = new OrderItem($pdo);
$productModel   = new Product($pdo);

if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/auth/login.php');
    exit;
}
$action = $_POST['action'] ?? $_GET['action'] ?? '';
switch ($action) {
    case 'place':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/user/home.php');
            exit;
        }
        if ($_SESSION['role'] === 'admin' && !empty($_POST['user_id'])) {
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
            $redirect = ($_SESSION['role'] === 'admin')
                ? '../views/admin/manual_order.php'
                : '../views/user/home.php';
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
            $redirect = ($_SESSION['role'] === 'admin')
                ? '../views/admin/manual_order.php'
                : '../views/user/home.php';
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

        $_SESSION['success'] = 'Order placed successfully!';

        $redirect = ($_SESSION['role'] === 'admin')
            ? '../views/admin/manual_order.php'
            : '../views/user/home.php';
        header("Location: $redirect");
        exit;

    case 'cancel':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/user/orders.php');
            exit;
        }

        if ($_SESSION['role'] !== 'user') {
            header('Location: ../views/user/orders.php');
            exit;
        }

        $orderId = (int) ($_POST['order_id'] ?? 0);

        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ../views/user/orders.php');
            exit;
        }
        $order = $orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['errors'] = ['Order not found.'];
            header('Location: ../views/user/orders.php');
            exit;
        }

        if ($order['user_id'] != $_SESSION['user_id']) {
            $_SESSION['errors'] = ['Unauthorized action.'];
            header('Location: ../views/user/orders.php');
            exit;
        }

        if ($order['status'] !== 'processing') {
            $_SESSION['errors'] = ['Only processing orders can be cancelled.'];
            header('Location: ../views/user/orders.php');
            exit;
        }
        $orderModel->cancel($orderId, $_SESSION['user_id']);
        $_SESSION['success'] = 'Order cancelled successfully.';
        header('Location: ../views/user/orders.php');
        exit;

    case 'deliver':

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin/dashboard.php');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ../views/auth/login.php');
            exit;
        }
        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ../views/admin/dashboard.php');
            exit;
        }
        $orderModel->updateStatus($orderId, 'out_for_delivery');
        $_SESSION['success'] = 'Order is now out for delivery.';
        header('Location: ../views/admin/dashboard.php');
        exit;
    case 'done':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/admin/dashboard.php');
            exit;
        }
        if ($_SESSION['role'] !== 'admin') {
            header('Location: ../views/auth/login.php');
            exit;
        }
        $orderId = (int) ($_POST['order_id'] ?? 0);
        if ($orderId <= 0) {
            $_SESSION['errors'] = ['Invalid order.'];
            header('Location: ../views/admin/dashboard.php');
            exit;
        }
        $orderModel->updateStatus($orderId, 'done');
        $_SESSION['success'] = 'Order marked as done.';
        header('Location: ../views/admin/dashboard.php');
        exit;
    default:
        header('Location: ../views/auth/login.php');
        exit;
}