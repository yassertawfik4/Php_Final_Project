<?php
require_once BASE_PATH . "/controllers/UserController.php";
require_once BASE_PATH . "/controllers/OrderController.php";
$page   = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];
switch ($page){
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
        //  case 'confirm_order':
        // if ($method === 'POST') {
        //     (new OrderController())->place();
        // }
        // break;
}

?>