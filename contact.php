<?php 
session_start();
include "config/db.php";

$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    $sql = "INSERT INTO contact (nama, email, pesan) VALUES ('$nama','$email','$pesan')";
    if (mysqli_query($conn, $sql)) {
        $success = "Pesan berhasil dikirim!";
    } else {
        $success = "Terjadi kesalahan: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Contact - EATPALL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<section class="py-5">
  <div class="container">
    <h2 class="mb-4 text-center">Kontak & Saran</h2>

    <?php if($success): ?>
      <div class="alert alert-info"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm mx-auto" style="max-width:600px;">
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Pesan</label>
        <textarea name="pesan" class="form-control" rows="5" required></textarea>
      </div>
      <button class="btn btn-primary">Kirim</button>
    </form>
  </div>
</section>

</body>
</html>
