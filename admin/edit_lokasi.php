<?php
include("../config/auth.php");
include("../config/db.php");

$error = null;
$success = null;

// ambil dan validasi id
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: manage_lokasi.php");
    exit;
}

// ambil data lokasi dengan prepared statement
$stmt = mysqli_prepare($conn, "SELECT id, nama_tempat, latitude, longitude, keterangan, kategori, url_foto, jam_operasional FROM lokasi WHERE id = ? LIMIT 1");
if (!$stmt) {
    die("Query gagal: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) !== 1) {
    mysqli_stmt_close($stmt);
    header("Location: manage_lokasi.php");
    exit;
}
mysqli_stmt_bind_result($stmt, $rid, $rnama, $rlat, $rlng, $rket, $rkategori, $rurlfoto, $rjam);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// proses update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST['nama_tempat'] ?? '');
    $lat = trim($_POST['latitude'] ?? '');
    $lng = trim($_POST['longitude'] ?? '');
    $ket = trim($_POST['keterangan'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $jam = trim($_POST['jam_operasional'] ?? '');

    if ($nama === '' || $lat === '' || $lng === '') {
        $error = "Nama, latitude, dan longitude wajib diisi.";
    } elseif (!is_numeric($lat) || !is_numeric($lng)) {
        $error = "Latitude dan longitude harus angka.";
    } else {
        // handle upload jika ada
        $new_url = $rurlfoto; // default pakai url lama
        if (!empty($_FILES['url_foto']['name']) && $_FILES['url_foto']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['url_foto'];
            $maxSize = 3 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                $error = "Ukuran file maksimal 3MB.";
            } else {
                $check = @getimagesize($file['tmp_name']);
                if ($check === false) {
                    $error = "File bukan gambar.";
                } else {
                    $allowed = ['jpg','jpeg','png','gif','webp'];
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

                        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                            $error = "Gagal memindahkan file upload.";
                        } else {
                            $new_url = 'foto/' . $filename;
                            // hapus file lama jika ada dan berbeda
                            if ($rurlfoto && file_exists(__DIR__ . '/../' . $rurlfoto)) {
                                @unlink(__DIR__ . '/../' . $rurlfoto);
                            }
                        }
                    }
                }
            }
        }

        // jika tidak ada error, lakukan update
        if (!$error) {
            $lat_f = floatval($lat);
            $lng_f = floatval($lng);
            $upd = mysqli_prepare($conn, "UPDATE lokasi SET nama_tempat = ?, latitude = ?, longitude = ?, keterangan = ?, kategori = ?, jam_operasional = ?, url_foto = ? WHERE id = ?");
            if ($upd) {
                mysqli_stmt_bind_param($upd, "sddssssi", $nama, $lat_f, $lng_f, $ket, $kategori, $jam, $new_url, $id);
                if (mysqli_stmt_execute($upd)) {
                    mysqli_stmt_close($upd);
                    header("Location: manage_lokasi.php?msg=updated");
                    exit;
                } else {
                    $error = "Gagal menyimpan ke database: " . mysqli_error($conn);
                    mysqli_stmt_close($upd);
                }
            } else {
                $error = "Query prepare gagal: " . mysqli_error($conn);
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
<title>Edit Lokasi - EATPALL Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  :root { --accent:#f39c12; --accent-dark:#e67e22; --bg-start:#ecf0f1; --bg-end:#f7f9fb; }
  body { font-family:'Poppins',sans-serif; background: linear-gradient(120deg,var(--bg-start),var(--bg-end)); color:#2c3e50; padding:24px; }
  .card { max-width:920px; margin:auto; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.06); overflow:hidden; }
  .card-header { background:linear-gradient(90deg,var(--accent),var(--accent-dark)); color:#fff; }
  .card-header .small-muted { color: rgba(255,255,255,0.95); display:block; margin-top:4px; font-weight:400; }
  .form-label { font-weight:600; }
  .btn-accent { background:var(--accent); color:#fff; border:none; }
  .img-preview { max-width:160px; max-height:120px; object-fit:cover; border-radius:8px; border:1px solid rgba(0,0,0,0.08); }
  .small-muted { color:#7f8c8d; }
  @media (max-width:767px){ .card{padding:0 12px} }
</style>
</head>
<body>

<div class="card">
  <div class="card-header p-3 d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Edit Lokasi</h5>
      <small class="small-muted">Perbarui informasi tempat makan</small>
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
        <input type="text" name="nama_tempat" class="form-control" required value="<?= htmlspecialchars($_POST['nama_tempat'] ?? $rnama) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Latitude *</label>
        <input type="text" name="latitude" class="form-control" required value="<?= htmlspecialchars($_POST['latitude'] ?? $rlat) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Longitude *</label>
        <input type="text" name="longitude" class="form-control" required value="<?= htmlspecialchars($_POST['longitude'] ?? $rlng) ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Alamat / Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="4"><?= htmlspecialchars($_POST['keterangan'] ?? $rket) ?></textarea>
      </div>

      <div class="col-md-4">
        <label class="form-label">Kategori</label>
        <input type="text" name="kategori" class="form-control" value="<?= htmlspecialchars($_POST['kategori'] ?? $rkategori) ?>" placeholder="Contoh: Rumah Makan">
      </div>

      <div class="col-md-4">
        <label class="form-label">Jam Operasional</label>
        <input type="text" name="jam_operasional" class="form-control" value="<?= htmlspecialchars($_POST['jam_operasional'] ?? $rjam) ?>" placeholder="08:00 - 21:00">
      </div>

      <div class="col-md-4">
        <label class="form-label">Foto (jpg/png/webp, max 3MB)</label>
        <input type="file" name="url_foto" class="form-control" accept="image/*">
        <?php if (!empty($rurlfoto) && file_exists(__DIR__ . '/../' . $rurlfoto)): ?>
          <div class="mt-2">
            <img src="../<?= htmlspecialchars($rurlfoto) ?>" alt="foto" class="img-preview">
          </div>
        <?php elseif (!empty($rurlfoto)): ?>
          <div class="mt-2 small-muted">File foto tidak ditemukan: <?= htmlspecialchars($rurlfoto) ?></div>
        <?php endif; ?>
      </div>

      <div class="col-12 d-flex gap-2">
        <button type="submit" class="btn btn-accent">Simpan Perubahan</button>
        <a href="manage_lokasi.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
