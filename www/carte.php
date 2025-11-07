<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>WhatIsThisPlane | Avions autour de moi</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    body { margin:0; font-family:Arial; }
    #map { height:100vh; }
    .plane-icon {
        width: 26px;
        height: 26px;
        background-image: url("https://cdn-icons-png.flaticon.com/512/684/684908.png");
        background-size: cover;
        transform: rotate(0deg);
    }
</style>
</head>
<body>

<div id="map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/api.js"></script> <!-- ton fichier API -->
<script>
let map = L.map('map').setView([48.8566, 2.3522], 6); // vue France par défaut

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: "&copy; OpenStreetMap"
}).addTo(map);

let planeMarkers = {}; //stocke les marqueurs des avions en fonction de leur icao24

async function refreshPlanes() {
    console.log("Recherche des avions proches...");

    const flights = await getNearbyFlights(1); // rayon 1° ~ 111 km

    flights.forEach(f => {
        if (!f.latitude || !f.longitude) return;

        const key = f.icao24;
        const angle = f.true_track || 0;

        // création icône avion rotation direction
        const icon = L.divIcon({
            className: '',
            html: `<div class="plane-icon" style="transform:rotate(${angle}deg)"></div>`
        });

        if (!planeMarkers[key]) {
            planeMarkers[key] = L.marker([f.latitude, f.longitude], { icon }).addTo(map);

            planeMarkers[key].bindPopup(`
                <b>${f.callsign || "Inconnu"}</b><br>
                ICAO24: ${f.icao24}<br>
                Altitude: ${Math.round(f.baro_altitude || 0)} m<br>
                Vitesse: ${Math.round(f.velocity || 0)} km/h<br>
            `);
        } else {
            planeMarkers[key].setLatLng([f.latitude, f.longitude]);
        }
    });
}

// ➜ centrer la carte sur ta position
async function initPosition() {
    const pos = await getUserLocation();
    if (!pos) return;

    map.setView([pos.lat, pos.lon], 9);
}

// démarrage
initPosition();
refreshPlanes();

// refresh toutes les 15 secondes
// setInterval(refreshPlanes, 15000);

</script>
</body>
</html>
