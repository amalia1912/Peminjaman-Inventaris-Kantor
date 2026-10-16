<?php
session_start();
require_once 'config/database.php';

$username = $_POST['username'];
$password = $_POST['password'];

if ($username === 'admin' && $password === '123') {
    $_SESSION['role'] = 'admin';
    header("Location: dashboard_admin.php");
} else {
    echo "Username atau password salah";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card p-4" style="width:400px">
        <h4 class="text-center mb-3">Login</h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input name="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>
</body>
</html>
