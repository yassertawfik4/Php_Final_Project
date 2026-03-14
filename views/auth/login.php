<?php 
require_once BASE_PATH . '/includes/header.php';
?>

<style>
    .login-container {
        min-height: calc(100vh - 140px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: linear-gradient(135deg, rgba(15, 94, 168, 0.05) 0%, rgba(231, 165, 53, 0.05) 100%);
    }

    .login-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(15, 26, 45, 0.12);
        background: white;
        max-width: 900px;
        width: 100%;
    }

    .login-brand-section {
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        color: white;
        padding: 60px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .login-brand-section h2 {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0 0 20px 0;
        color: white;
    }

    .login-brand-section p {
        font-size: 1rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        line-height: 1.6;
    }

    .brand-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        margin-bottom: 20px;
    }

    .login-form-section {
        padding: 60px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-form-section h3 {
        font-size: 1.8rem;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: #101828;
    }

    .login-subtitle {
        color: #667085;
        font-size: 0.95rem;
        margin-bottom: 30px;
    }

    .form-group-login {
        margin-bottom: 24px;
    }

    .form-group-login label {
        display: block;
        font-weight: 600;
        font-size: 0.9rem;
        color: #344054;
        margin-bottom: 8px;
    }

    .form-group-login input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #dbe4ee;
        border-radius: 12px;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
    }

    .form-group-login input:focus {
        outline: none;
        border-color: #0f5ea8;
        box-shadow: 0 0 0 4px rgba(15, 94, 168, 0.1);
        background: #f9fbff;
    }

    .form-group-login input::placeholder {
        color: #a1afc9;
    }

    .btn-login {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 94, 168, 0.25);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .login-footer-links {
        margin-top: 24px;
        text-align: center;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.9rem;
    }

    .login-footer-links a {
        color: #0f5ea8;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .login-footer-links a:hover {
        color: #0a4681;
        text-decoration: underline;
    }

    .error-alert {
        background: #fef3f2;
        border: 2px solid #f6d0d4;
        color: #b42318;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 24px;
        animation: slideDown 0.3s ease;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .error-alert li {
        margin-bottom: 6px;
        font-weight: 500;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 768px) {
        .login-wrapper {
            grid-template-columns: 1fr;
        }

        .login-brand-section {
            padding: 40px 30px;
        }

        .login-form-section {
            padding: 40px 30px;
        }

        .login-brand-section h2 {
            font-size: 1.8rem;
        }

        .brand-icon {
            width: 60px;
            height: 60px;
            font-size: 2rem;
        }

        .login-form-section h3 {
            font-size: 1.5rem;
        }
    }
</style>

<div class="login-container">
    <div class="login-wrapper">
        <!-- Brand Section -->
        <div class="login-brand-section">
            <div class="brand-icon"></div>
            <h2>Cafeteria</h2>
            <p>Welcome to your favorite campus food hub. Quality meals, fast service, and great taste in every order.</p>
        </div>

        <!-- Login Form Section -->
        <div class="login-form-section">
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="error-alert">
                    <ul>
                        <?php 
                        $errors = is_array($_SESSION['error']) ? $_SESSION['error'] : [$_SESSION['error']]; 
                        foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php 
                unset($_SESSION['error']); 
                ?>
            <?php endif; ?>

            <h3>Welcome Back</h3>
            <p class="login-subtitle">Sign in to your account to continue</p>

            <form action="<?= BASE_URL . '/?page=login' ?>" method="POST">
                <div class="form-group-login">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email"
                        name="email" 
                        placeholder="Enter your email" 
                        required
                        autocomplete="email"
                    >
                </div>

                <div class="form-group-login">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        placeholder="Enter your password" 
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="login-footer-links">
                <a href="<?= BASE_URL . '/?page=forget_password' ?>">Forgot Password?</a>
            </div>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>
