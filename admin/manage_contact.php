<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = null;
$success = null;

// deteksi apakah kolom 'subject' ada
$hasSubject = false;
$colCheck = mysqli_query($conn, "SHOW COLUMNS FROM `contact` LIKE 'subject'");
if ($colCheck && mysqli_num_rows($colCheck) > 0) {
    $hasSubject = true;
}

// Hapus pesan
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del = mysqli_prepare($conn, "DELETE FROM contact WHERE id = ?");
    if ($del) {
        mysqli_stmt_bind_param($del, "i", $id);
        if (mysqli_stmt_execute($del)) {
            mysqli_stmt_close($del);
            header("Location: manage_contact.php?msg=deleted");
            exit;
        } else {
            $error = "Gagal menghapus: " . mysqli_error($conn);
        }
    } else {
        $error = "Query gagal: " . mysqli_error($conn);
    }
}

// Ambil semua pesan (sesuaikan SELECT jika tidak ada subject)
if ($hasSubject) {
    $res = mysqli_query($conn, "SELECT id, nama, email, subject, pesan, created_at FROM contact ORDER BY id DESC");
} else {
    $res = mysqli_query($conn, "SELECT id, nama, email, pesan, created_at FROM contact ORDER BY id DESC");
}

$contacts = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) {
        if (!$hasSubject) $r['subject'] = '';
        $contacts[] = $r;
    }
}

// helper untuk export CSV (server-side sederhana ketika diminta)
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=contacts.csv');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID','Nama','Email','Subjek','Pesan','Waktu']);
    foreach ($contacts as $c) {
        fputcsv($out, [$c['id'],$c['nama'],$c['email'],$c['subject'],$c['pesan'],$c['created_at']]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Kelola Pesan - Admin EATPALL</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Poppins', sans-serif; background: linear-gradient(120deg,#ecf0f1,#f7f9fb); color:#2c3e50; }
  .sidebar { height:100vh; background:#2c3e50; color:#fff; padding:24px; }
  .sidebar a{ color:#ecf0f1; display:block; margin:10px 0; text-decoration:none;}
  .sidebar a:hover{ color:#f39c12;}
  .brand { color:#fff; font-weight:700; font-size:18px; margin-bottom:12px; }
  .card-header { background: linear-gradient(90deg,#f39c12,#e67e22); color:#fff; }
  .btn-accent { background:#f39c12; color:#fff; border:none; }
  .search-input { max-width:420px; }
  .table-wrap { max-height:60vh; overflow:auto; }
  .badge-topic { background:#e67e22; color:#fff; }
</style>
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <aside class="col-md-2 sidebar">
      <div class="brand">EATPALL — Admin</div>
      <a href="dashboard.php">🏠 Dashboard</a>
      <a href="manage_lokasi.php">📍 Kelola Lokasi</a>
      <a href="manage_contact.php">📩 Pesan Kontak</a>
      <a href="../index.php">🌍 Lihat Beranda</a>
      <a href="../logout.php">🚪 Logout</a>
    </aside>

    <main class="col-md-10 py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Kelola Pesan Kontak <span class="badge badge-topic ms-2"><?= count($contacts) ?></span></h4>
        <div>
          <a href="manage_contact.php?export=csv" class="btn btn-outline-secondary me-2">Export CSV</a>
          <a href="manage_contact.php" class="btn btn-outline-secondary me-2">Refresh</a>
          <a href="../index.php" class="btn btn-accent">Kembali ke Situs</a>
        </div>
      </div>

      <?php if (!empty($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">Pesan berhasil dihapus.</div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div>Daftar Pesan</div>
          <input id="searchBox" class="form-control form-control-sm search-input" placeholder="Cari nama, email, atau subjek..." oninput="filterTable()">
        </div>
        <div class="card-body">
          <div class="table-wrap">
            <table id="contactsTable" class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nama</th>
                  <th>Email</th>
                  <?php if ($hasSubject): ?><th>Subjek</th><?php endif; ?>
                  <th>Waktu</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($contacts as $c): ?>
                  <tr data-id="<?= $c['id'] ?>" data-nama="<?= htmlspecialchars($c['nama'], ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($c['email'], ENT_QUOTES) ?>" data-subject="<?= htmlspecialchars($c['subject'], ENT_QUOTES) ?>" data-pesan="<?= htmlspecialchars($c['pesan'], ENT_QUOTES) ?>">
                    <td><?= htmlspecialchars($c['id']) ?></td>
                    <td><?= htmlspecialchars($c['nama']) ?></td>
                    <td><?= htmlspecialchars($c['email']) ?></td>
                    <?php if ($hasSubject): ?><td><?= htmlspecialchars($c['subject']) ?></td><?php endif; ?>
                    <td><?= htmlspecialchars($c['created_at']) ?></td>
                    <td>
                      <button class="btn btn-sm btn-info" onclick="viewMsg(this)">Lihat</button>
                      <a href="manage_contact.php?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesan ini?')">Hapus</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($contacts)): ?>
                  <tr><td colspan="<?= 5 + ($hasSubject ? 1 : 0) ?>" class="text-center">Belum ada pesan.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal sederhana untuk lihat pesan -->
      <div class="modal fade" id="msgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Detail Pesan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <p><strong>Nama:</strong> <span id="mNama"></span></p>
              <p><strong>Email:</strong> <span id="mEmail"></span></p>
              <p id="mSubWrap"><strong>Subjek:</strong> <span id="mSubject"></span></p>
              <p><strong>Waktu:</strong> <span id="mTime"></span></p>
              <hr>
              <div id="mPesan" style="white-space:pre-wrap;"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterTable() {
    const q = document.getElementById('searchBox').value.toLowerCase();
    document.querySelectorAll('#contactsTable tbody tr').forEach(tr => {
        const nama = tr.dataset.nama?.toLowerCase() || '';
        const email = tr.dataset.email?.toLowerCase() || '';
        const subject = tr.dataset.subject?.toLowerCase() || '';
        const show = !q || nama.includes(q) || email.includes(q) || subject.includes(q);
        tr.style.display = show ? '' : 'none';
    });
}

let msgModal = new bootstrap.Modal(document.getElementById('msgModal'));
function viewMsg(btn) {
    const tr = btn.closest('tr');
    if (!tr) return;
    document.getElementById('mNama').textContent = tr.dataset.nama || '';
    document.getElementById('mEmail').textContent = tr.dataset.email || '';
    document.getElementById('mSubject').textContent = tr.dataset.subject || '';
    document.getElementById('mTime').textContent = tr.children[4].textContent || '';
    document.getElementById('mPesan').textContent = tr.dataset.pesan || '';
    if (!tr.dataset.subject) document.getElementById('mSubWrap').style.display = 'none';
    else document.getElementById('mSubWrap').style.display = '';
    msgModal.show();
}
</script>
</body>
</html>
