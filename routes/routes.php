<?php
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/controllers/UserController.php';
require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/orderItemController.php';
require_once BASE_PATH . '/controllers/OrderController.php';
require_once BASE_PATH . '/controllers/ProductsController.php';

$page   = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];

switch ($page){
    case 'login':
        if ($method === 'POST') {
            (new AuthController())->handleLogin();
        } else {
            (new AuthController())->showLogin();
        }
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    
    case 'home':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('user');
        require_once BASE_PATH . '/views/user/home.php';
        break;
    
    case 'orders':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('user');
        require_once BASE_PATH . '/views/user/orders.php';
        break;

    case 'order.details':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('user');
        require_once BASE_PATH . '/views/user/order_details.php';
        break;

    // Backward-compatible alias
    case 'user.orders':
        header('Location: ' . BASE_URL . '/?page=orders');
        exit;


    // User management (admin only)
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

    // Admin dashboard and order management
    case 'admin.dashboard':
        (new OrderItemController())->dashboard();
        break;

    case 'admin.checks':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        require_once BASE_PATH . '/views/admin/checks.php';
        break;

    case 'admin.update_order_status':
        if ($method === 'POST') {
            (new OrderItemController())->updateOrderStatus();
        } else {
            header('Location: ' . BASE_URL . '/?page=admin.dashboard');
            exit;
        }
        break;

    case 'admin.manual_order':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        require_once BASE_PATH . '/views/admin/manual_order.php';
        break;

    // Products & categories (admin only)
    case 'admin.products':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        (new ProductController(getDB()))->products();
        break;

    case 'admin.product.add':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        (new ProductController(getDB()))->addProductPage();
        break;

    case 'admin.product.save':
        if ($method === 'POST') {
            require_once BASE_PATH . '/includes/auth_check.php';
            require_role('admin');
            (new ProductController(getDB()))->addProduct();
        }
        break;

    case 'admin.product.toggle':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        (new ProductController(getDB()))->toggleProduct();
        break;

    case 'admin.product.delete':
        if ($method === 'POST') {
            require_once BASE_PATH . '/includes/auth_check.php';
            require_role('admin');
            (new ProductController(getDB()))->deleteProduct();
        }
        break;

    case 'admin.category.add':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        (new ProductController(getDB()))->addCategoryPage();
        break;

    case 'admin.category.save':
        if ($method === 'POST') {
            require_once BASE_PATH . '/includes/auth_check.php';
            require_role('admin');
            (new ProductController(getDB()))->addCategory();
        }
        break;

    // Order operations
    case 'order.place':
        if ($method === 'POST') {
            (new OrderController())->place();
        } else {
            header('Location: ' . BASE_URL . '/?page=home');
            exit;
        }
        break;

    case 'order.cancel':
        if ($method === 'POST') {
            (new OrderController())->cancel();
        } else {
            header('Location: ' . BASE_URL . '/?page=orders');
            exit;
        }
        break;

    // Password reset
    case 'forgot':
        (new AuthController())->showForgotPassword();
        break;

    case 'reset_password':
        if ($method === 'POST') {
            (new AuthController())->handleForgotPassword();
        } else {
            header('Location: ' . BASE_URL . '/?page=forgot');
            exit;
        }
        break;

    default:
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
}

?>
