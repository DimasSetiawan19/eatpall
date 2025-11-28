<?php 
session_start();
include "config/db.php";

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $passwordInput = $_POST['password'];
    $passwordMD5   = md5($passwordInput);

    // cek di tabel admin (MD5 atau plain)
    $qAdmin = mysqli_query($conn, "SELECT * FROM admin 
        WHERE username='$username' 
        AND (password='$passwordInput' OR password='$passwordMD5') 
        LIMIT 1");

    if (mysqli_num_rows($qAdmin) > 0) {
        $data = mysqli_fetch_assoc($qAdmin);
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = 'admin';
        header("Location: admin/dashboard.php");
        exit;
    }

    // cek di tabel user (MD5 atau plain)
    $qUser = mysqli_query($conn, "SELECT * FROM user 
        WHERE username='$username' 
        AND (password='$passwordInput' OR password='$passwordMD5') 
        LIMIT 1");

    if (mysqli_num_rows($qUser) > 0) {
        $data = mysqli_fetch_assoc($qUser);
        $_SESSION['username'] = $data['username'];
        $_SESSION['role'] = 'user';
        header("Location: index.php");
        exit;
    }

    // kalau dua-duanya tidak ada → error
    $error = "Username atau password salah!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - EATPALL</title>
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
    .login-card {
      background: #fff;
      border-radius: 15px;
      padding: 40px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      text-align: center;
    }
    .login-card img {
      width: 120px;
      height: 120px;
      object-fit: contain;
      margin-bottom: 10px;
      transition: transform 0.3s ease;
    }
    .login-card img:hover {
      transform: scale(1.08);
    }
    .login-card h2 {
      color: #2c3e50;
      font-weight: 600;
    }
    .btn-login {
      background: #f39c12;
      border: none;
      padding: 10px;
      font-weight: 600;
    }
    .btn-login:hover {
      background: #e67e22;
    }
  </style>
</head>
<body>

<?php if(isset($_GET['registered'])): ?>
  <div class="alert alert-success position-absolute top-0 w-100 text-center m-0 rounded-0">
    Registrasi berhasil, silakan login.
  </div>
<?php endif; ?>

<div class="login-card">
  <!-- 🟡 Logo -->
  <img src="assets/img/logo.png" alt="Logo EATPALL">

  <h2> EATPALL</h2>
  <p class="mb-4 text-muted">Masuk untuk melanjutkan</p>

  <?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3 text-start">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3 text-start">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-login w-100 text-white">Login</button>
  </form>

  <p class="mt-3">Belum punya akun? <a href="register.php">Register</a></p>
</div>

</body>
</html>
