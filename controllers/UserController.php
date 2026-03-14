<?php

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/User.php';

# Admin operations on users: list, add, edit, delete.
class UserController
{
    private User $userModel;

    public function __construct()
    {
        $pdo = getDB();
        $this->userModel = new User($pdo);
    }

    public function index()
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        $users=$this->userModel->getAll();
        require_once BASE_PATH .'/views/admin/users/index.php';
    }

}
