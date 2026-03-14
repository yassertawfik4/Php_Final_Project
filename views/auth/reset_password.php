<?php

$conn = mysqli_connect("localhost","root","","cafeteria");

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

$query = "UPDATE users SET password='$hashedPassword' WHERE email='$email'";
mysqli_query($conn,$query);

echo "<div class='alert alert-success text-center'>
Password updated successfully
</div>";

?>