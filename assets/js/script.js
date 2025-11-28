// Inisialisasi peta
var map = L.map('map').setView([-0.898, 119.870], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Definisi ikon per kategori
var icons = {
    "Rumah Makan": L.icon({ iconUrl: 'assets/img/icon-rumahmakan.png', iconSize: [32, 32] }),
    "Warung Makan": L.icon({ iconUrl: 'assets/img/icon-rumahmakan.png', iconSize: [32, 32] }),
    "Cafe": L.icon({ iconUrl: 'assets/img/icon-cafe.png', iconSize: [32, 32] }),
    "Cafe & Resto": L.icon({ iconUrl: 'assets/img/icon-cafe.png', iconSize: [32, 32] }),
    "Restoran": L.icon({ iconUrl: 'assets/img/icon-restoran.png', iconSize: [32, 32] }),
    "Pizza Hut": L.icon({ iconUrl: 'assets/img/icon-restoran.png', iconSize: [32, 32] }), // ditambahkan
    "default": L.icon({ iconUrl: 'assets/img/icon-default.png', iconSize: [32, 32] })
};

// Array marker & data
var allMarkers = [];
var locationData = [];

// Ambil data lokasi dari API
fetch("api/get_lokasi.php")
  .then(res => res.json())
  .then(data => {
    locationData = data;
    renderLocations(data);
  });

// Render ke map & sidebar
function renderLocations(data) {
  document.getElementById("locationList").innerHTML = "";
  allMarkers.forEach(m => map.removeLayer(m));
  allMarkers = [];

  data.forEach(lokasi => {
    var kategori = lokasi.kategori || "default";
    var marker = L.marker([lokasi.latitude, lokasi.longitude], {
      icon: icons[kategori] || icons["default"]
    }).addTo(map);

    marker.bindPopup(`
      <h6>${lokasi.nama_tempat}</h6>
      <p><b>Kategori:</b> ${lokasi.kategori}</p>
      <p><b>Alamat:</b> ${lokasi.alamat}</p>
      <p><b>Jam Buka:</b> ${lokasi.jam}</p>
      ${lokasi.foto ? `<img src="foto/${lokasi.foto}" width="150">` : ""}
    `);

    allMarkers.push(marker);

    let listItem = document.createElement("li");
    listItem.textContent = lokasi.nama_tempat;
    listItem.addEventListener("click", () => {
      map.setView([lokasi.latitude, lokasi.longitude], 17);
      marker.openPopup();
    });
    document.getElementById("locationList").appendChild(listItem);
  });
}

// Live Search
document.getElementById("searchInput").addEventListener("input", function() {
  let keyword = this.value.toLowerCase();
  let filtered = locationData.filter(l => l.nama_tempat.toLowerCase().includes(keyword));
  renderLocations(filtered);
});
