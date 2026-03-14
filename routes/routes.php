<?php
require_once BASE_PATH . "/controllers/UserController.php";
require_once BASE_PATH . "/controllers/AuthController.php";
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
    case 'admin.dashboard':
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        require_once BASE_PATH . '/views/admin/dashboard.php';
        break;
}

?>