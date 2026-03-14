<?php

$conn = mysqli_connect("localhost","root","","cafeteria");

$email = $_POST['email'];
$password = $_POST['password'];

$query = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn,$query);

$user = mysqli_fetch_assoc($result);

 if($user && password_verify($password, $user['password'])){
// if($user && $password == $user['password']){
    session_start();
    $_SESSION['user'] = $email;

    header("Location: home.php");

}else{

    echo "<div style='color:red;text-align:center;margin-top:20px'>
    Invalid Email or Password
    </div>";

}

?>