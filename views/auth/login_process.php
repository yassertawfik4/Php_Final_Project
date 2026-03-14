<?php
require_once '../../config/database.php';
$conn = getDB();


$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$_POST['email']]);

$user = $stmt->fetch();

 if($user && password_verify($password, $user['password'])){
// if($user && $password == $user['password']){
    session_start();
    $_SESSION['user'] = $email;

  header("Location: ../user/home.php");


}else{

    echo "<div style='color:red;text-align:center;margin-top:20px'>
    Invalid Email or Password
    </div>";

}

?>