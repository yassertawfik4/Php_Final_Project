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
        require_once  './views/auth/login.php';
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
                header('Location: ' . BASE_URL . '/?page=admin.dashboard');
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
    public function showForgotPassword(): void
    {
        require_once BASE_PATH . '/views/auth/forget_password.php';
    }

    public function handleForgotPassword(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: ' . BASE_URL . '/?page=forgot');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $_SESSION['errors'] = ['User not found.'];
            header('Location: ' . BASE_URL . '/?page=forgot');
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = getDB()->prepare('UPDATE users SET password=? WHERE email=?');
        $stmt->execute([$hashedPassword, $email]);

        $_SESSION['success'] = 'Password updated successfully. You can now log in.';
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }
}

?>