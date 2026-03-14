<?php
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';

?>

<style>
    .orders-container {
        min-height: calc(100vh - 140px);
        background: linear-gradient(135deg, rgba(15, 94, 168, 0.05) 0%, rgba(231, 165, 53, 0.05) 100%);
        padding: 40px 20px;
    }

    .orders-header {
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        border-radius: 20px;
        padding: 50px 40px;
        color: white;
        margin-bottom: 40px;
        box-shadow: 0 20px 60px rgba(15, 94, 168, 0.15);
    }

    .orders-header h2 {
        font-size: 2.2rem;
        font-weight: 800;
        margin: 0 0 10px 0;
        color: white;
    }

    .orders-header p {
        font-size: 1.05rem;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
    }

    .orders-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(15, 26, 45, 0.08);
        border: 1px solid #dbe4ee;
        transition: all 0.3s ease;
    }

    .orders-card:hover {
        box-shadow: 0 15px 40px rgba(15, 26, 45, 0.12);
        transform: translateY(-2px);
    }

    .order-item {
        padding: 20px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .order-id {
        font-weight: 700;
        color: #101828;
        font-size: 1.1rem;
    }

    .order-meta {
        color: #667085;
        font-size: 0.9rem;
    }

    .empty-state {
        text-align: center;
        padding: 60px 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(15, 26, 45, 0.08);
    }

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: #101828;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #667085;
        margin-bottom: 30px;
    }

    .btn-browse {
        display: inline-block;
        padding: 12px 28px;
        background: linear-gradient(135deg, #0f5ea8 0%, #0a4681 100%);
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .btn-browse:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 94, 168, 0.25);
        color: white;
    }

    @media (max-width: 768px) {
        .orders-header {
            padding: 40px 30px;
        }

        .orders-header h2 {
            font-size: 1.8rem;
        }

        .order-header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="orders-container">
    <div class="container">
        <div class="orders-header">
            <h2>My Orders</h2>
            <p>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?>. Your order history and status updates will appear here.</p>
        </div>

        <div class="orders-card">
            <div class="empty-state">
                <div class="empty-icon"></div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet. Start ordering delicious food from our cafeteria!</p>
                <a href="<?= BASE_URL ?>" class="btn-browse">Browse Menu</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once BASE_PATH . '/includes/footer.php';