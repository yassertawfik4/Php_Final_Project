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
    public function showAddForm(): void
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');
        require_once BASE_PATH . '/views/admin/users/add.php';
    }
    public function add()
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => (string)($_POST['password'] ?? ''),
            'room' => trim($_POST['room'] ?? ''),
            'ext' => trim($_POST['ext'] ?? ''),
            'image' => null,
            'role' => $_POST['role'] ?? 'user',
        ];
        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Name is required.';
        }
        if ($data['email'] === '') {
            $errors[] = 'Email is required.';
        }
        if ($data['password'] === '') {
            $errors[] = 'Password is required.';
        }

        if (!empty($_FILES['image']) ) {
            
                $uploadDir = BASE_PATH . '/public/uploads';
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $fileName = $data['name'] . '_' . time();
                if ($ext !== '') {
                    $fileName .= '.' . strtolower($ext);
                }

                $targetPath = $uploadDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $data['image'] = $fileName;
                } else {
                    $errors[] = 'Failed to upload image.';
                }
        }

        if ($errors) {
            require_once BASE_PATH . '/views/admin/users/add.php';
            return;
        }

        $this->userModel->create($data);
        header('Location: ' . BASE_URL . '/?page=admin.users');
        exit;

    }


    public function showUpdateForm(): void
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/?page=admin.users');
            exit;
        }
        $user = $this->userModel->findById($id);
        if (!$user) {
            die("User not found!");
        }

        require_once BASE_PATH . '/views/admin/users/edit.php';
    }

    public function update()
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/?page=admin.users');
            exit;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => (string)($_POST['password'] ?? ''),
            'room' => trim($_POST['room'] ?? ''),
            'ext' => trim($_POST['ext'] ?? ''),
            'image' => null,
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Name is required.';
        }
        if ($data['email'] === '') {
            $errors[] = 'Email is required.';
        }

        if (!empty($_FILES['image']['name'])) {
    
            $uploadDir = BASE_PATH . '/public/uploads/';
            
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = $data['name'] . '_' . time() . '.' . strtolower($ext);

            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $data['image'] = $fileName;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        } else {
            unset($data['image']); 
        }

        if ($errors) {
            $user = $this->userModel->findById($id);
            require_once BASE_PATH . '/views/admin/users/edit.php';
            return;
        }

        $this->userModel->update($id, $data);
        header('Location: ' . BASE_URL . '/?page=admin.users');
        exit;
    }

}
