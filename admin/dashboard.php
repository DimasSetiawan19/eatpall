<?php
session_start();
include "../config/db.php";
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Hitung total lokasi
$totalLokasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM lokasi"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin - EATPALL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
      margin: 0;
      overflow-x: hidden;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      height: 100vh;
      background: #2c3e50;
      color: white;
      padding: 30px 20px;
      position: fixed;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .sidebar .top {
      text-align: center;
    }

    .sidebar img {
      width: 80px;
      height: 80px;
      object-fit: contain;
      margin-bottom: 10px;
    }

    .sidebar h4 {
      font-weight: 600;
      margin-bottom: 30px;
    }

    .sidebar a {
      color: #ecf0f1;
      text-decoration: none;
      display: block;
      margin: 10px 0;
      font-weight: 500;
      transition: 0.3s;
    }

    .sidebar a:hover {
      color: #f39c12;
      transform: translateX(5px);
    }

    .sidebar .btn-home {
      background-color: #f39c12;
      color: white;
      border-radius: 8px;
      padding: 10px;
      text-align: center;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      display: block;
      margin-top: 15px;
    }

    .sidebar .btn-home:hover {
      background-color: #e67e22;
    }

    /* Main Content */
    .content {
      margin-left: 250px;
      padding: 40px;
    }

    .content h2 {
      font-weight: 600;
      margin-bottom: 30px;
      color: #2c3e50;
    }

    .card-stats {
      border-radius: 15px;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      padding: 25px;
      text-align: center;
      transition: 0.3s;
    }

    .card-stats:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .card-stats h5 {
      color: #7f8c8d;
      margin-bottom: 10px;
      font-weight: 500;
    }

    .card-stats h3 {
      font-weight: 700;
      color: #2c3e50;
    }

  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="top">
      <img src="../assets/img/logo.png" alt="Logo EATPALL">
      <h4>EATPALL Admin</h4>
      <a href="dashboard.php">🏠 Dashboard</a>
      <a href="manage_lokasi.php">📍 Kelola Lokasi</a>
      <a href="../logout.php">🚪 Logout</a>
    </div>

    <div class="bottom">
      <a href="../index.php" class="btn-home">🌍 Lihat Beranda</a>
    </div>
  </div>

  <!-- Content -->
  <div class="content flex-grow-1">
    <h2>Selamat Datang, Admin 👋</h2>

    <div class="row">
      <div class="col-md-4">
        <div class="card-stats">
          <h5>Total Lokasi</h5>
          <h3 class="text-primary"><?php echo $totalLokasi; ?></h3>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card-stats">
          <h5>Admin Aktif</h5>
          <h3 class="text-warning">1</h3>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
