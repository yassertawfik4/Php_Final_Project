<?php
require_once BASE_PATH . "/controllers/UserController.php";

$page   = $_GET['page'] ?? 'login';
$method = $_SERVER['REQUEST_METHOD'];
switch ($page){
    case 'admin.users':
        (new UserController())->index();
        break;

    case 'admin.add_user':
        break;

    case 'admin.update_user':
        break;

    case 'admin.delete_user':
        break;
}

?>