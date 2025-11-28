<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    session_start();
}

$title = "Avions autour de moi";
require "../src/includes/header.inc.php";
?>
    <style>
        .map-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        #map {
            width: 100%;
            height: 70vh;
            min-height: 500px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .plane-icon {
            width: 26px;
            height: 26px;
            background-image: url("https://cdn-icons-png.flaticon.com/512/684/684908.png");
            background-size: cover;
            transform: rotate(0deg);
        }
        
        .map-info {
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        
        @media (max-width: 768px) {
            #map {
                height: 60vh;
                min-height: 400px;
            }
            
            .map-container {
                padding: 0 0.5rem;
            }
        }
    </style>

    <main>
        <h1>Découvrir en temps réel tous les avions qui survolent ma Région</h1>
        <p>Visualisez en temps réel les avions qui survolent votre région.</p>
        
        <section class="map-container">
            <div class="map-info">
                <h2>Carte interactive des vols</h2>
                <p>Cliquez sur un avion pour voir ses détails.</p>
            </div>
            <div id="map"></div>
        </section>
    </main>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/api.js"></script>
    <script>
        let map = L.map('map').setView([48.8566, 2.3522], 6); // vue France par défaut

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: "&copy; OpenStreetMap"
        }).addTo(map);

        let planeMarkers = {}; // stocke les marqueurs des avions en fonction de leur icao24

        async function refreshPlanes() {
            console.log("Recherche des avions proches...");

            const flights = await getNearbyFlights(1); // rayon 1° ~ 111 km
            
            if (!flights || flights.length === 0) {
                console.warn("Aucun avion trouvé.");
                return;
            }

            console.log(`${flights.length} avions détectés.`);

            flights.forEach(f => {
                if (!f.latitude || !f.longitude) return;

                const key = f.icao24;
                const angle = f.true_track || 0;
                
                // Correction de la rotation : l'icône pointe vers le sud
                const iconAngle = (angle + 270) % 360;
                
                console.log(`Avion ${f.callsign || "Inconnu"} (${key}) :
                    • Position: [${f.latitude.toFixed(4)}, ${f.longitude.toFixed(4)}]
                    • Angle: ${angle}°
                    • Altitude: ${Math.round(f.baro_altitude || 0)} m
                    • Vitesse: ${Math.round(f.velocity || 0)} km/h`);

                // création icône avion rotation direction
                const icon = L.divIcon({
                    className: '',
                    html: `<div class="plane-icon" style="transform:rotate(${iconAngle}deg)"><i class="fas fa-plane"></i></div>`
                });

                if (!planeMarkers[key]) {
                    console.log(`Nouveau marqueur créé pour ${key}`);
                    planeMarkers[key] = L.marker([f.latitude, f.longitude], { icon }).addTo(map);

                    planeMarkers[key].bindPopup(`
                        <b>${f.callsign || "Inconnu"}</b><br>
                        ICAO24: ${f.icao24}<br>
                        Altitude: ${Math.round(f.baro_altitude || 0)} m<br>
                        Vitesse: ${Math.round(f.velocity || 0)} km/h<br>
                    `);
                } else {
                    console.log(`Mise à jour position pour ${key}`);
                    planeMarkers[key].setLatLng([f.latitude, f.longitude]);
                    planeMarkers[key].setIcon(icon);
                }
            });
        }

        // ➜ centrer la carte sur ta position
        async function initPosition() {
            const pos = await getBigDataCloudLocation();
            if (!pos) {
                console.warn("Impossible de récupérer la position de l'utilisateur.");
                return;
            }
            
            console.log(`Position détectée : [${pos.lat.toFixed(4)}, ${pos.lon.toFixed(4)}]`);
            map.setView([pos.lat, pos.lon], 9);
        }

        // démarrage
        initPosition();
        refreshPlanes();

        // refresh toutes les 15 secondes
        //setInterval(refreshPlanes, 15000);
    </script>
<?php
require "../src/includes/footer.inc.php";
?>