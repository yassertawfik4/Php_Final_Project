<!DOCTYPE html>
<html>
<head>

<title>Reset Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">

<div class="card shadow p-4" style="width:400px">

<h4 class="text-center mb-4">Reset Password</h4>

<form action="reset_password.php" method="POST">

<div class="mb-3">
<label class="form-label">Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">New Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Confirm Password</label>
<input type="password" name="confirm_password" class="form-control" required>
</div>

<button type="submit" class="btn btn-success w-100">
Reset Password
</button>

</form>

</div>
</div>

</body>
</html>