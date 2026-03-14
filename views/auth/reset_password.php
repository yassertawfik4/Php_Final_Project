<?php

require_once '../../config/database.php';
$conn = getDB();

$email = $_POST['email'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

if($password != $confirm){

echo "<div class='alert alert-danger text-center'>
Passwords do not match
</div>";
exit;

}

 $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$_POST['email']]);

if($stmt->rowCount() > 0){
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->execute([$hashedPassword, $_POST['email']]);
    echo "<div class='alert alert-success text-center'>
Password updated successfully
</div>";
header("Location: login.php");

} else {
    echo "<div class='alert alert-danger text-center'>
User not found
</div>";
}
?>