<?php
# require_role('user');   // allows users AND admins
# require_role('admin');  // allows admins only
function require_role(string $required_role): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }

    $role = $_SESSION['user_role'] ?? '';
    if ($required_role === 'admin' && $role !== 'admin') {
        http_response_code(403);
        die('<h3 class="text-center mt-5 text-danger">403 – Access Denied</h3>');
    }
    if ($required_role === 'user' && ($role=='user'||$role=='admin')) {
        header('Location: ' . BASE_URL . '/?page=login');
        exit;
    }
}
