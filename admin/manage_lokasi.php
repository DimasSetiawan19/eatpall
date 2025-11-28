<?php
include("../config/auth.php");
include("../config/db.php");

// Ambil semua data lokasi
$result = mysqli_query($conn, "SELECT * FROM lokasi ORDER BY id ASC");
$locations = [];
if ($result) {
    while ($r = mysqli_fetch_assoc($result)) $locations[] = $r;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Kelola Lokasi - EATPALL Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f6f9;
      margin: 0;
      overflow-x: hidden;
      color: #2c3e50;
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
      margin-bottom: 10px;
      color: #2c3e50;
    }

    .content .subtitle {
      color: #7f8c8d;
      font-size: 14px;
      margin-bottom: 25px;
    }

    .card {
      border-radius: 15px;
      background: #fff;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      border: none;
    }

    .card-header {
      background: linear-gradient(90deg, #f39c12, #e67e22);
      color: white;
      border-radius: 15px 15px 0 0;
      font-weight: 600;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .table-hover tbody tr:hover {
      background: #fdf2e9;
    }

    .action-link {
      padding: 6px 10px;
      border-radius: 6px;
      color: #fff;
      text-decoration: none;
      margin-right: 6px;
      display: inline-block;
    }

    .action-edit { background: #3498db; }
    .action-delete { background: #e74c3c; }
    .action-edit:hover { background: #2980b9; }
    .action-delete:hover { background: #c0392b; }

    .btn-accent {
      background: #f39c12;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 8px 14px;
      font-weight: 500;
      transition: 0.3s;
    }

    .btn-accent:hover {
      background: #e67e22;
    }

    @media (max-width: 768px) {
      .sidebar { display: none; }
      .content { margin-left: 0; padding: 20px; }
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

  <!-- Main Content -->
  <div class="content flex-grow-1">
    <h2>Kelola Lokasi</h2>
    <div class="subtitle">Tambah, ubah, atau hapus data lokasi tempat makan</div>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <a href="tambah_lokasi.php" class="btn-accent">+ Tambah Lokasi</a>
      <input id="searchBox" class="form-control" style="max-width: 300px;" placeholder="Cari nama atau kategori..." oninput="filterTable()">
    </div>

    <div class="card">
      <div class="card-header">
        <span>Daftar Lokasi (<?= count($locations) ?> item)</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="lokasiTable" class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr>
                <th style="width:60px;">No</th>
                <th style="width:80px;">ID</th>
                <th>Nama Tempat</th>
                <th>Kategori</th>
                <th style="width:160px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($locations)): ?>
                <tr><td colspan="5" class="text-center text-muted">Belum ada lokasi, tambahkan terlebih dahulu.</td></tr>
              <?php else: $no = 1; foreach ($locations as $loc): ?>
                <tr data-nama="<?= htmlspecialchars($loc['nama_tempat'], ENT_QUOTES) ?>" data-kategori="<?= htmlspecialchars($loc['kategori'], ENT_QUOTES) ?>">
                  <td><?= $no ?></td>
                  <td><?= htmlspecialchars($loc['id']) ?></td>
                  <td><?= htmlspecialchars($loc['nama_tempat']) ?></td>
                  <td><?= htmlspecialchars($loc['kategori']) ?></td>
                  <td>
                    <a href="edit_lokasi.php?id=<?= $loc['id'] ?>" class="action-link action-edit">Edit</a>
                    <a href="hapus_lokasi.php?id=<?= $loc['id'] ?>" class="action-link action-delete" onclick="return confirm('Yakin ingin menghapus lokasi ini?')">Hapus</a>
                  </td>
                </tr>
              <?php $no++; endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function filterTable() {
  const q = document.getElementById('searchBox').value.toLowerCase().trim();
  document.querySelectorAll('#lokasiTable tbody tr').forEach(tr => {
    const nama = tr.dataset.nama?.toLowerCase() || '';
    const kategori = tr.dataset.kategori?.toLowerCase() || '';
    const show = !q || nama.includes(q) || kategori.includes(q);
    tr.style.display = show ? '' : 'none';
  });
}
</script>

</body>
</html>
