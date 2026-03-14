<?php
require_once BASE_PATH . "/controllers/UserController.php";
require_once BASE_PATH . "/config/database.php";
require_once BASE_PATH . "/models/User.php";
require_once BASE_PATH . "/models/Product.php";
require_once BASE_PATH . "/models/Order.php";
require_once BASE_PATH . "/models/OrderItem.php";

$page   = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];
switch ($page){
    case 'user.orders':
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
        $orders = array_slice($allOrders, $offset, $perPage);
        foreach ($orders as &$ord) {
            $ord['items'] = $orderItemModel->getByOrder($ord['id']);
        }
        unset($ord);
        $userName = $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'User';
        require_once BASE_PATH . '/views/user/orders.php';
        break;

    case 'user.order_details':
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
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }
        $order = $orderModel->findById($orderId);
        if (!$order) {
            $_SESSION['errors'] = ['Order not found.'];
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }
        if ((int) $order['user_id'] !== $userId) {
            $_SESSION['errors'] = ['You can only view your own orders.'];
            header('Location: ' . BASE_URL . '/?page=user.orders');
            exit;
        }
        $items = $orderItemModel->getByOrder($orderId);
        $userName = $_SESSION['user_name'] ?? $_SESSION['user'] ?? 'User';
        require_once BASE_PATH . '/views/user/order_details.php';
        break;

    case 'admin.manual_order':
        $pdo = getDB();
        $users = (new User($pdo))->getDropdownList();
        $products = (new Product($pdo))->getAllAvailable();
        require_once BASE_PATH . '/views/admin/manual_order.php';
        break;

    case 'admin.users':
        (new UserController())->index();
        break;

    case 'admin.add_user':
        (new UserController())->showAddForm();
        break;
    case 'admin.create_user':
        if ($method === 'POST') {
            (new UserController())->add();
        }
        break;
    case 'admin.show_update_user':
        (new UserController())->showUpdateForm();
        break;
    case 'admin.update_user':
        if ($method === 'POST') {
            (new UserController())->update();
        }
        break;

    case 'admin.delete_user':
        if ($method === 'POST') {
            (new UserController())->delete();
        }
        break;
}

?>