<?php

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/models/Product.php';
require_once BASE_PATH . '/models/Category.php';
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';

$pdo = getDB();
$productModel = new Product($pdo);
$categoryModel = new Category($pdo);
$userModel = new User($pdo);
$products = $productModel->getAllAvailable();
$categories = $categoryModel->getAll();
$rooms = $userModel->getDistinctRooms();
$currentUser = $userModel->findById($_SESSION['user_id'] ?? 0);
$defaultRoom = $currentUser['room'] ?? '';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="mb-0">Place an Order</h2>
            <div class="text-muted small">Select items, choose quantities, add notes, and confirm</div>
        </div>
        <a href="<?= BASE_URL ?>/?page=orders" class="btn btn-outline-primary">View My Orders</a>
    </div>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form id="user-order-form" method="post" action="<?= BASE_URL ?>/?page=order.place">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Your Order</h5>
                    </div>
                    <div class="card-body">
                        <div id="cart-items" class="mb-3">
                            <p class="text-muted small" id="cart-empty-msg">No items yet. Tap a product to add it.</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Room</label>
                            <select name="room" id="room" class="form-select" required>
                                <option value="">Select room</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= htmlspecialchars($room) ?>" <?= $defaultRoom === $room ? 'selected' : '' ?>><?= htmlspecialchars($room) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="e.g. Less sugar, ice"></textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Total</strong>
                            <span id="order-total" class="fs-4 text-primary">EGP 0</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btn-confirm" disabled>Confirm Order</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h6 class="mb-0">Menu</h6>
                            <select id="category-filter" class="form-select" style="max-width: 220px;">
                                <option value="all">All categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="cat-<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-3" id="product-grid">
                            <?php foreach ($products as $p): ?>
                                <?php $catClass = 'cat-' . (int)$p['category_id']; ?>
                                <div class="col-6 col-md-4 col-lg-3 product-block <?= $catClass ?>" data-product-id="<?= (int)$p['id'] ?>" data-price="<?= (float)$p['price'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-category="<?= $catClass ?>">
                                    <div class="card h-100 product-card cursor-pointer border shadow-sm" role="button" tabindex="0">
                                        <div class="card-body text-center py-3">
                                            <?php if (!empty($p['image'])): ?>
                                                
                                                <img src="<?= BASE_URL ?>/public/<?=$p['image']?>" alt="" class="img-fluid rounded mb-1" style="max-height: 64px; object-fit: contain;">
                                            <?php else: ?>
                                                <i class="bi bi-cup-hot display-6 text-secondary"></i>
                                            <?php endif; ?>
                                            <div class="small fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="small text-muted"><?= (float)$p['price'] ?> LE</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="form-items-container"></div>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('user-order-form');
    const cartContainer = document.getElementById('cart-items');
    const cartEmptyMsg = document.getElementById('cart-empty-msg');
    const orderTotalEl = document.getElementById('order-total');
    const btnConfirm = document.getElementById('btn-confirm');
    const formItemsContainer = document.getElementById('form-items-container');
    const roomSelect = document.getElementById('room');
    const categoryFilter = document.getElementById('category-filter');

    let cart = {};

    function renderCart() {
        const entries = Object.entries(cart);
        cartEmptyMsg.classList.toggle('d-none', entries.length > 0);
        cartContainer.querySelectorAll('.cart-row').forEach(el => el.remove());
        if (entries.length === 0) {
            orderTotalEl.textContent = 'EGP 0';
            btnConfirm.disabled = true;
            return;
        }
        let total = 0;
        entries.forEach(([id, item]) => {
            const subtotal = item.price * item.qty;
            total += subtotal;
            const row = document.createElement('div');
            row.className = 'cart-row d-flex align-items-center justify-content-between border-bottom py-2';
            row.innerHTML = (
                '<span class="fw-semibold">' + escapeHtml(item.name) + '</span>' +
                '<div class="d-flex align-items-center gap-1">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary cart-minus" data-id="' + id + '">−</button>' +
                '<input type="number" class="form-control form-control-sm text-center cart-qty" data-id="' + id + '" value="' + item.qty + '" min="1" style="width: 56px;">' +
                '<button type="button" class="btn btn-sm btn-outline-secondary cart-plus" data-id="' + id + '">+</button>' +
                '</div>' +
                '<span class="text-nowrap">EGP ' + subtotal.toFixed(0) + '</span>' +
                '<button type="button" class="btn btn-sm btn-outline-danger cart-remove" data-id="' + id + '" title="Remove"><i class="bi bi-x-lg"></i></button>'
            );
            cartContainer.appendChild(row);
        });
        orderTotalEl.textContent = 'EGP ' + total.toFixed(0);
        btnConfirm.disabled = false;

        cartContainer.querySelectorAll('.cart-minus').forEach(btn => btn.addEventListener('click', () => changeQty(btn.dataset.id, -1)));
        cartContainer.querySelectorAll('.cart-plus').forEach(btn => btn.addEventListener('click', () => changeQty(btn.dataset.id, 1)));
        cartContainer.querySelectorAll('.cart-qty').forEach(inp => inp.addEventListener('change', () => setQty(inp.dataset.id, parseInt(inp.value, 10) || 1)));
        cartContainer.querySelectorAll('.cart-remove').forEach(btn => btn.addEventListener('click', () => removeItem(btn.dataset.id)));
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function addToCart(id, name, price) {
        id = String(id);
        if (cart[id]) {
            cart[id].qty += 1;
        } else {
            cart[id] = { name, price: parseFloat(price), qty: 1 };
        }
        renderCart();
    }

    function changeQty(id, delta) {
        if (!cart[id]) return;
        cart[id].qty = Math.max(1, cart[id].qty + delta);
        renderCart();
    }

    function setQty(id, qty) {
        if (!cart[id]) return;
        cart[id].qty = Math.max(1, qty);
        renderCart();
    }

    function removeItem(id) {
        delete cart[id];
        renderCart();
    }

    document.querySelectorAll('#product-grid .product-block').forEach(block => {
        const card = block.querySelector('.product-card');
        const id = block.dataset.productId;
        const name = block.dataset.name;
        const price = block.dataset.price;
        function add() { addToCart(id, name, price); }
        card.addEventListener('click', add);
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                add();
            }
        });
    });

    categoryFilter.addEventListener('change', function () {
        const val = this.value;
        document.querySelectorAll('#product-grid .product-block').forEach(block => {
            if (val === 'all') {
                block.classList.remove('d-none');
            } else {
                block.classList.toggle('d-none', block.dataset.category !== val);
            }
        });
    });

    form.addEventListener('submit', function (e) {
        formItemsContainer.innerHTML = '';
        Object.entries(cart).forEach(([productId, item]) => {
            if (item.qty < 1) return;
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items[' + productId + ']';
            input.value = item.qty;
            formItemsContainer.appendChild(input);
        });
        if (Object.keys(cart).length === 0) {
            e.preventDefault();
            btnConfirm.disabled = true;
            return;
        }
        if (!roomSelect.value) {
            e.preventDefault();
            roomSelect.focus();
            return;
        }
    });
})();
</script>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>