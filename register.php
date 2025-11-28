<?php
include "config/db.php";
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);
    $role = 'user';

    $check = mysqli_query($conn, "SELECT * FROM user WHERE username='$username' LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO user (username, password, role) VALUES ('$username','$password','$role')");
        if ($insert) {
            header("Location: login.php?registered=1");
            exit;
        } else {
            $error = "Registrasi gagal, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register - EATPALL</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(120deg, #2c3e50, #34495e);
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.register-card {
    background: #fff;
    border-radius: 15px;
    padding: 40px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    text-align: center;
}
.register-card img {
    width: 120px;
    height: 120px;
    object-fit: contain;
    margin-bottom: 10px;
    transition: transform 0.3s ease;
}
.register-card img:hover {
    transform: scale(1.08);
}
.register-card h2 {
    color: #2c3e50;
    font-weight: 600;
}
.btn-register {
    background: #f39c12;
    border: none;
    padding: 10px;
    font-weight: 600;
}
.btn-register:hover {
    background: #e67e22;
}
</style>
</head>
<body>

<div class="register-card">
    <!-- 🟡 Logo -->
    <img src="assets/img/logo.png" alt="Logo EATPALL">

    <h2>🍴 EATPALL</h2>
    <p class="mb-4 text-muted">Daftar akun untuk mulai menggunakan layanan</p>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3 text-start">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
        </div>
        <div class="mb-3 text-start">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
        </div>
        <button type="submit" class="btn btn-register w-100 text-white">Register</button>
    </form>

    <p class="mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
</div>

</body>
</html>
