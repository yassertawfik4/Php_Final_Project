<?php

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';

?>

<style>
.home-container {
    min-height: calc(100vh - 140px);
    background: linear-gradient(135deg, rgba(15, 94, 168, 0.05) 0%, rgba(231, 165, 53, 0.05) 100%);
    padding: 40px 20px;
}

.hero-card-professional {
    background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
    border-radius: 20px;
    padding: 60px 40px;
    color: white;
    box-shadow: 0 20px 60px rgba(15, 94, 168, 0.15);
    max-width: 100%;
    margin: 0 auto;
}

.hero-card-professional h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 20px;
    color: white;
}

.hero-card-professional p {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    line-height: 1.6;
    max-width: 600px;
}

.hero-icon {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.welcome-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 30px;
}

.welcome-text {
    flex: 1;
    min-width: 300px;
}

.welcome-actions {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 14px 28px;
    background: rgba(255, 255, 255, 0.18);
    border: 2px solid rgba(255, 255, 255, 0.35);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
    font-size: 0.95rem;
}

.btn-action:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: white;
    transform: translateY(-2px);
    color: white;
}

.btn-action-primary {
    background: rgba(255, 255, 255, 0.25);
    border-color: white;
}

@media (max-width: 768px) {
    .hero-card-professional {
        padding: 40px 30px;
    }

    .hero-card-professional h1 {
        font-size: 2rem;
    }

    .welcome-content {
        flex-direction: column;
    }

    .welcome-actions {
        width: 100%;
    }

    .btn-action {
        flex: 1;
        text-align: center;
    }
}
</style>

<div class="home-container">
    <div class="container">
        <div class="hero-card-professional">
            <div class="welcome-content">
                <div class="welcome-text">
                    <div class="hero-icon"></div>
                    <h1>Welcome Back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h1>
                    <p>Track your orders and manage your cafeteria requests from one place. View your order history,
                        track delivery status, and place new orders.</p>
                </div>
                <div class="welcome-actions">
                    <a href="<?= BASE_URL ?>/?page=user.orders" class="btn-action btn-action-primary">View Orders</a>
                    <a href="<?= BASE_URL ?>" class="btn-action">Browse Menu</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

require_once BASE_PATH . '/includes/footer.php';
?>