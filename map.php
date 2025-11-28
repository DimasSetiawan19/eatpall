<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Map - EATPALL</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="leaflet/leaflet.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container-map">
  <div id="sidebar">
    <h5 class="mb-3">Cari Lokasi</h5>
    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Ketik nama tempat...">
    <ul class="location-list" id="locationList"></ul>
  </div>
  <div id="map"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="leaflet/leaflet.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>
