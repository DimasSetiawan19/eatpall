<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    
  <meta charset="UTF-8">
  <title>About - EATPALL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "navbar.php"; // kalau mau modular ?>

<section class="py-5">
  <div class="container">
    <h2 class="mb-4 text-center">Tentang EATPALL</h2>
    <p class="lead text-center">
      EATPALL adalah Sistem Informasi Geografis (SIG) untuk memetakan rumah makan, cafe, dan restoran di Kota Palu.
    </p>
    <p class="text-center">
      Aplikasi ini dikembangkan dengan teknologi <b>PHP, MySQL, dan Leaflet (OpenStreetMap)</b>.  
      Tujuannya membantu masyarakat menemukan lokasi kuliner dengan mudah, serta mempermudah pengelolaan data tempat makan bagi admin.
    </p>
  </div>
</section>

</body>
</html>
