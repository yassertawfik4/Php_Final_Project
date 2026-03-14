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

    // ── حفظ الـ flash message في الـ session ──
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

// ── جيب الـ flash وامسحه عشان يظهر مرة واحدة بس ──
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
}