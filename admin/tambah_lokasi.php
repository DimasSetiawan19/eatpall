<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../config/auth.php");
include("../config/db.php");

$error = null;
$success = null;

// nilai default untuk repopulate form
$nama = $lat = $lng = $ket = $kategori = $jam = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST['nama_tempat'] ?? '');
    $lat = trim($_POST['latitude'] ?? '');
    $lng = trim($_POST['longitude'] ?? '');
    $ket = trim($_POST['keterangan'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jam = trim($_POST['jam_operasional'] ?? '');

    // validasi
    if ($nama === '' || $lat === '' || $lng === '') {
        $error = "Nama, latitude, dan longitude wajib diisi.";
    } elseif (!is_numeric($lat) || !is_numeric($lng)) {
        $error = "Latitude dan longitude harus angka.";
    } elseif (!isset($_FILES['url_foto']) || $_FILES['url_foto']['error'] !== UPLOAD_ERR_OK) {
        $error = "File foto wajib diupload.";
    } else {
        $file = $_FILES['url_foto'];
        $maxSize = 3 * 1024 * 1024; // 3MB

        if ($file['size'] > $maxSize) {
            $error = "Ukuran file maksimal 3MB.";
        } else {
            $check = @getimagesize($file['tmp_name']);
            if ($check === false) {
                $error = "File bukan gambar.";
            } else {
                $allowed = ['jpg','jpeg','png','gif', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $error = "Ekstensi file tidak diperbolehkan. (jpg, jpeg, png, gif, webp)";
                } else {
                    $targetDir = realpath(__DIR__ . '/../foto');
                    if ($targetDir === false) {
                        $targetDir = __DIR__ . '/../foto';
                        if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
                    }

                    $uniq = time() . '_' . bin2hex(random_bytes(6));
                    $filename = $uniq . '.' . $ext;
                    $targetPath = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $url_foto = 'foto/' . $filename;

                        // insert ke DB (gunakan prepared statement)
                        $lat_f = floatval($lat);
                        $lng_f = floatval($lng);

                        $stmt = mysqli_prepare($conn, "INSERT INTO lokasi (nama_tempat, latitude, longitude, keterangan, kategori, url_foto, jam_operasional) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt) {
                            // bind: s d d s s s s
                            mysqli_stmt_bind_param($stmt, "sddssss", $nama, $lat_f, $lng_f, $ket, $kategori, $url_foto, $jam);
                            if (mysqli_stmt_execute($stmt)) {
                                mysqli_stmt_close($stmt);
                                header("Location: manage_lokasi.php?msg=added");
                                exit;
                            } else {
                                $error = "Gagal menyimpan ke database: " . mysqli_error($conn);
                                mysqli_stmt_close($stmt);
                                @unlink($targetPath);
                            }
                        } else {
                            $error = "Query prepare gagal: " . mysqli_error($conn);
                            @unlink($targetPath);
                        }
                    } else {
                        $error = "Gagal memindahkan file upload.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tambah Lokasi - EATPALL Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  :root { --accent:#f39c12; --accent-dark:#e67e22; --bg-start:#ecf0f1; --bg-end:#f7f9fb; }
  body { font-family:'Poppins',sans-serif; background: linear-gradient(120deg,var(--bg-start),var(--bg-end)); color:#2c3e50; padding:24px; }
  .card { max-width:900px; margin:auto; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.06); overflow:hidden; }
  .card-header { background:linear-gradient(90deg,var(--accent),var(--accent-dark)); color:#fff; }
  /* kecil di header dibuat terang agar kontras dengan latar oranye */
  .card-header .small-muted { color: rgba(255,255,255,0.95); display:block; margin-top:4px; font-weight:400; }
  .form-label { font-weight:600; }
  .btn-accent { background:var(--accent); color:#fff; border:none; }
  .small-muted { color:#7f8c8d; }
  .back-link { text-decoration:none; color:#fff; opacity:.9; }
  @media (max-width:767px){ .card{padding:0 12px} }
</style>
</head>
<body>

<div class="card">
  <div class="card-header p-3 d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Tambah Lokasi</h5>
      <small class="small-muted">Tambahkan tempat makan baru ke peta</small>
    </div>
    <div>
      <a href="manage_lokasi.php" class="btn btn-light">Kembali</a>
    </div>
  </div>

  <div class="card-body p-4">
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Nama Tempat *</label>
        <input type="text" name="nama_tempat" class="form-control" required value="<?= htmlspecialchars($nama) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Latitude *</label>
        <input type="text" name="latitude" class="form-control" required value="<?= htmlspecialchars($lat) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Longitude *</label>
        <input type="text" name="longitude" class="form-control" required value="<?= htmlspecialchars($lng) ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Alamat / Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="4"><?= htmlspecialchars($ket) ?></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Kategori</label>
        <input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($kategori) ?>" placeholder="Contoh: Rumah Makan">
      </div>

      <div class="col-md-4">
        <label class="form-label">Jam Operasional</label>
        <input type="text" name="jam_operasional" class="form-control" value="<?= htmlspecialchars($jam) ?>" placeholder="08:00 - 21:00">
      </div>

      <div class="col-md-4">
        <label class="form-label">Foto (jpg/png/webp, max 3MB) *</label>
        <input type="file" name="url_foto" class="form-control" accept="image/*" <?= ($nama || $lat || $lng) ? '' : 'required' ?>>
      </div>

      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-accent">Simpan Lokasi</button>
        <a href="manage_lokasi.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>