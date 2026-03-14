<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';
$conn = getDB();

// Get logged in user
$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get products
$products_stmt = $conn->prepare("SELECT * FROM products ORDER BY name");
$products_stmt->execute();
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get latest orders
$orders_stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE user_id = :id 
    ORDER BY id DESC
");
$orders_stmt->execute([':id' => $user['id']]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>

<title>Cafeteria</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

.product-img{
cursor:pointer;
height:120px;
object-fit:contain;
}

.cart-item{
display:flex;
justify-content:space-between;
margin-bottom:10px;
}

</style>

</head>

<body class="bg-light">

<!-- NAVBAR -->

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">

<div class="container">

<a class="navbar-brand" href="home.php">Cafeteria</a>

<ul class="navbar-nav me-auto">

<li class="nav-item">
<a class="nav-link active" href="home.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="my_orders.php">My Orders</a>
</li>

</ul>

<span class="text-white me-3">
<?php echo htmlspecialchars($user['name']); ?>
</span>

<a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>

</div>

</nav>


<div class="container mt-4">

<div class="row">

<!-- LEFT SIDE : CART -->

<div class="col-md-4">

<div class="card p-3">

<h5>Your Order</h5>

<form action="<?= BASE_URL ?>/?page=confirm_order" method="POST">
<input type="hidden" name="action" value="place">

<div id="cart"></div>

<div id="cart"></div>

<hr>

<label>Notes</label>

<textarea name="notes" class="form-control mb-3"></textarea>


<label>Room</label>

<input
type="text"
name="room"
class="form-control mb-3"
value="<?php echo htmlspecialchars($user['room']); ?>"
readonly
>

<h5>Total: <span id="totalPrice">0</span> EGP</h5>

<button class="btn btn-success w-100 mt-2">
Confirm Order
</button>

</form>

</div>

</div>


<!-- RIGHT SIDE : PRODUCTS -->

<div class="col-md-8">

<h4>Menu</h4>

<div class="row">

<?php foreach($products as $product){ ?>

<div class="col-md-3 text-center mb-4">

<img
src="../../assets/images/<?php echo htmlspecialchars($product['image']); ?>"
class="product-img"
onclick="addToCart(
<?php echo $product['id']; ?>,
'<?php echo addslashes($product['name']); ?>',
<?php echo $product['price']; ?>
)"
>

<p class="mt-2">
<?php echo htmlspecialchars($product['name']); ?>
</p>

<span>
<?php echo $product['price']; ?> EGP
</span>

</div>

<?php } ?>

</div>

</div>

</div>


<!-- LATEST ORDERS -->

<h4 class="mt-5">Latest Orders</h4>

<ul class="list-group">

<?php foreach($orders as $order){ ?>

<li class="list-group-item">

Order #<?php echo $order['id']; ?>

<br>

Room: <?php echo htmlspecialchars($order['room']); ?>

<br>

Total: <?php echo $order['total_price']; ?> EGP

<br>

Notes: <?php echo htmlspecialchars($order['notes']); ?>

</li>

<?php } ?>

</ul>

</div>



<script>

let cart = {};
let total = 0;

function addToCart(id,name,price){

if(cart[id]){
cart[id].qty++;
}else{
cart[id]={name:name,price:price,qty:1};
}

renderCart();
}

function removeFromCart(id){

cart[id].qty--;

if(cart[id].qty<=0){
delete cart[id];
}

renderCart();
}

function renderCart(){

let cartDiv=document.getElementById("cart");
cartDiv.innerHTML="";
total=0;

for(let id in cart){

let item=cart[id];

total+=item.price*item.qty;

cartDiv.innerHTML+=`

<div class="cart-item">

<span>${item.name}</span>

<span>

<button type="button" onclick="removeFromCart(${id})">-</button>

${item.qty}

<button type="button" onclick="addToCart(${id},'${item.name}',${item.price})">+</button>

${item.price} EGP
<input type="hidden" name="items[${id}]" value="${item.qty}">

</span>

</div>

`;

}

document.getElementById("totalPrice").innerText=total;

}

</script>

</body>
</html>