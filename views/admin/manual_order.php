<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__, 2));
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Php_Final_Project');
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($users) || !isset($products)) {
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/models/User.php';
    require_once BASE_PATH . '/models/Product.php';
    $pdo = getDB();
    $userModel = new User($pdo);
    $users = $userModel->getDropdownList();
    $products = (new Product($pdo))->getAllAvailable();
}
$userModel = $userModel ?? new User(getDB());
$selectedUserId = (int) ($_POST['user_id'] ?? $_GET['user_id'] ?? (!empty($users) ? $users[0]['id'] : 0));
$selectedUser = $selectedUserId ? $userModel->findById($selectedUserId) : null;
$rooms = $userModel->getDistinctRooms();
$defaultRoom = $_POST['room'] ?? $_GET['room'] ?? ($selectedUser['room'] ?? '');
?>
<?php 
require_once BASE_PATH . '/includes/header.php'; 
require_once BASE_PATH . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manual Order</h2>
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

    <form id="manual-order-form" method="post" action="<?= BASE_URL ?>/?page=order.place">
        <input type="hidden" name="action" value="place">
        <input type="hidden" name="user_id" id="form-user-id" value="<?= $selectedUserId ?>">

        <div class="row g-4">
            <!-- Left column: current order details -->
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order details</h5>
                    </div>
                    <div class="card-body">
                        <div id="cart-items" class="mb-3">
                            <!-- Filled by JS -->
                            <p class="text-muted small" id="cart-empty-msg">No items yet</p>
                        </div>

                        <label class="form-label" for="notes">Notes</label>
                        <textarea name="notes" id="notes" class="form-control mb-3" rows="2" placeholder="e.g. 1 Tea Extra Sugar"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>

                        <label class="form-label" for="room">Room</label>
                        <select name="room" id="room" class="form-select mb-3" required>
                            <option value="">Select room</option>
                            <?php foreach ($rooms as $room): ?>
                                <option value="<?= htmlspecialchars($room) ?>" <?= $defaultRoom === $room ? 'selected' : '' ?>><?= htmlspecialchars($room) ?></option>
                            <?php endforeach; ?>
                        </select>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong>Total</strong>
                            <span id="order-total" class="fs-4 text-primary">EGP 0</span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="btn-confirm" disabled>Confirm</button>
                    </div>
                </div>
            </div>

            <!-- Right column: user + product grid -->
            <div class="col-lg-7">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Add to user</label>
                    <select class="form-select" id="user-select" style="max-width: 280px;">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" data-room="<?= htmlspecialchars($u['room'] ?? '') ?>" <?= $selectedUserId === (int) $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Products</h6>
                        <div class="row g-3" id="product-grid">
                            <?php foreach ($products as $p): ?>
                                <div class="col-6 col-md-4 col-lg-3" data-product-id="<?= (int) $p['id'] ?>" data-price="<?= (float) $p['price'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>">
                                    <div class="card h-100 product-card cursor-pointer border shadow-sm" role="button" tabindex="0">
                                        <div class="card-body text-center py-3">
                                            <?php if (!empty($p['image'])): ?>
                                                <img src="<?= BASE_URL ?>/public/<?= htmlspecialchars($p['image']) ?>" alt="" class="img-fluid rounded mb-1" style="max-height: 64px; object-fit: contain;">
                                            <?php else: ?>
                                                <i class="bi bi-cup-hot display-6 text-secondary"></i>
                                            <?php endif; ?>
                                            <div class="small fw-semibold"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="small text-muted"><?= (float) $p['price'] ?> LE</div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden inputs for items[] populated on submit -->
        <div id="form-items-container"></div>
    </form>
</div>

<script>
(function () {
    const form = document.getElementById('manual-order-form');
    const userSelect = document.getElementById('form-user-id');
    const userDropdown = document.getElementById('user-select');
    const cartContainer = document.getElementById('cart-items');
    const cartEmptyMsg = document.getElementById('cart-empty-msg');
    const orderTotalEl = document.getElementById('order-total');
    const btnConfirm = document.getElementById('btn-confirm');
    const formItemsContainer = document.getElementById('form-items-container');
    const roomSelect = document.getElementById('room');
    const notesInput = document.getElementById('notes');

    let cart = {}; // { productId: { name, price, qty } }

    userDropdown.addEventListener('change', function () {
        userSelect.value = this.value;
        const selectedOption = this.options[this.selectedIndex];
        const optionRoom = selectedOption ? selectedOption.dataset.room || '' : '';
        if (roomSelect) {
            roomSelect.value = optionRoom;
        }
    });

    function renderCart() {
        const entries = Object.entries(cart);
        cartEmptyMsg.classList.toggle('d-none', entries.length > 0);
        if (entries.length === 0) {
            cartContainer.querySelectorAll('.cart-row').forEach(el => el.remove());
            orderTotalEl.textContent = 'EGP 0';
            btnConfirm.disabled = true;
            return;
        }
        let total = 0;
        cartContainer.querySelectorAll('.cart-row').forEach(el => el.remove());
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

        cartContainer.querySelectorAll('.cart-minus').forEach(btn => {
            btn.addEventListener('click', function () { changeQty(this.dataset.id, -1); });
        });
        cartContainer.querySelectorAll('.cart-plus').forEach(btn => {
            btn.addEventListener('click', function () { changeQty(this.dataset.id, 1); });
        });
        cartContainer.querySelectorAll('.cart-qty').forEach(inp => {
            inp.addEventListener('change', function () { setQty(this.dataset.id, parseInt(this.value, 10) || 1); });
        });
        cartContainer.querySelectorAll('.cart-remove').forEach(btn => {
            btn.addEventListener('click', function () { removeItem(this.dataset.id); });
        });
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s;
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

    document.querySelectorAll('#product-grid [data-product-id]').forEach(block => {
        const card = block.querySelector('.product-card');
        const id = block.dataset.productId;
        const name = block.dataset.name;
        const price = block.dataset.price;
        function add() {
            addToCart(id, name, price);
        }
        card.addEventListener('click', add);
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                add();
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
