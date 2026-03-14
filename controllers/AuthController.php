<?php
class AuthController
{
    private User $userModel;

    public function __construct()
    {   
        $pdo = getDB();
        $this->userModel = new User($pdo);
    }


    public function showLogin(): void
    {
        require_once BASE_PATH . '/views/auth/login.php';
    }

    public function handleLogin(): void
    {
        $email=trim($_POST['email']?? '');
        $password=trim($_POST['password'] ?? '');

        $user = $this->userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_image'] = $user['image'] ?? null;
            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . '/?page=admin.users');
            } else {
                header('Location: ' . BASE_URL . '/?page=home');
            }
            exit;
        }

        $_SESSION['error'] = 'Invalid email or password.';
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }


    
    public function logout(): void
    {
        session_destroy();
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }
    
}

?>