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
        $errors = [];
        $old = [];
        $rooms = $this->userModel->getDistinctRooms();
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
            'password_confirm' => (string)($_POST['password_confirm'] ?? ''),
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
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'This email is already registered.';
        }
        if ($data['password'] === '') {
            $errors[] = 'Password is required.';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($data['password_confirm'] === '') {
            $errors[] = 'Password confirmation is required.';
        } elseif ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Passwords do not match.';
        }

        if ($data['role'] === 'user') {
            if ($data['room'] === '') {
                $errors[] = 'Room is required for users.';
            }
        } else {
            $data['room'] = null;
            $data['ext'] = null;
        }

        if (!empty($_FILES['image']['name'])) {
            
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
            $rooms = $this->userModel->getDistinctRooms();
            $old = $data;
            unset($old['password'], $old['password_confirm']);
            require_once BASE_PATH . '/views/admin/users/add.php';
            return;
        }

        try {
            $this->userModel->create($data);
        } catch (\PDOException $e) {
            $errors[] = 'Could not create user. Please try again.';
            $rooms = $this->userModel->getDistinctRooms();
            $old = $data;
            unset($old['password'], $old['password_confirm']);
            require_once BASE_PATH . '/views/admin/users/add.php';
            return;
        }
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
        $rooms = $this->userModel->getDistinctRooms();

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

        $existingUser = $this->userModel->findById($id);
        if (!$existingUser) {
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
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $role = $existingUser['role'] ?? 'user';

        $errors = [];
        if ($data['name'] === '') {
            $errors[] = 'Name is required.';
        }
        if ($data['email'] === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($this->userModel->emailExists($data['email'], $id)) {
            $errors[] = 'This email is already registered.';
        }
        if ($role === 'user' && $data['room'] === '') {
            $errors[] = 'Room is required for users.';
        }
        if ($role !== 'user') {
            $data['room'] = null;
            $data['ext'] = null;
        }
        $oldImg= $existingUser['image'];

        $uploadDir = BASE_PATH . '/public/uploads/';
        if (!empty($_FILES['image']['name'])) {
            
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

        if ($data['password'] !== '') {
            if (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
            if ($data['password'] !== $passwordConfirm) {
                $errors[] = 'Passwords do not match.';
            }
        }

        if ($errors) {
            $user = array_merge($existingUser, [
                'name' => $data['name'],
                'email' => $data['email'],
                'room' => $data['room'],
                'ext' => $data['ext'],
            ]);
            $rooms = $this->userModel->getDistinctRooms();
            require_once BASE_PATH . '/views/admin/users/edit.php';
            return;
        }

        $this->userModel->update($id, $data);
        if (!empty($data['image']) && $oldImg && file_exists($uploadDir . $oldImg)) {
            unlink($uploadDir . $oldImg);
        }
        header('Location: ' . BASE_URL . '/?page=admin.users');
        exit;
    }

    public function delete()
    {
        require_once BASE_PATH . '/includes/auth_check.php';
        require_role('admin');

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . BASE_URL . '/?page=admin.users');
            exit;
        }

            $user = $this->userModel->findById($id);
            if (!$user) {
                header('Location: ' . BASE_URL . '/?page=admin.users');
                exit;
            }

            $imagePath = !empty($user['image'])
                ? BASE_PATH . '/public/uploads/' . $user['image']
                : null;

            $this->userModel->delete($id);
            if ($imagePath && file_exists($imagePath)) {
                unlink($imagePath);
            }
        header('Location: ' . BASE_URL . '/?page=admin.users');
        exit;
    }

}
