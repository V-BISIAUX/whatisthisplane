<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $description = "Suivez les vols sur la carte interactive sur une rayon de 5km, autour de vous. Accédez aux informations des vols, comme leurs callsign, leurs destinations, leurs positions ...";
    $title = "Avions autour de moi";
    require "../src/includes/header.inc.php";
?>
    <style>
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
            /*background-image: url("https://cdn-icons-png.flaticon.com/512/684/684908.png");*/
            background-size: cover;
            transform: rotate(0deg);
			color: black;
        }
        
        .map-info {
            margin-bottom: 1rem;
            padding: 1rem;
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
	<style>
		/* ----------- GRID ----------- */
		.grid {
			display: flex;
			flex-direction: column;
			gap: 20px;
			margin-top: 30px;
		}
		/* ----------- CARD ----------- */
		.card {
			display: flex;
			flex-direction: row;
			width: 100%;
			padding: 20px 40px;
			gap: 25px;
			cursor: pointer;
			border-radius: 12px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			transition: transform 0.2s, box-shadow 0.2s;
			align-items: center;
		}
		.card:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 20px rgba(0,0,0,0.15);
		}
		.card img {
			width: 100%;
			max-width: 150px;
			height: 100px;
			border-radius: 10px;
		}
		.card-content {
			flex-grow: 1;
			display: flex;
			flex-direction: column;
			gap: 8px;
			min-width: 0;
		}
		.card-content h3 {
			margin: 0 0 10px 0;
			font-size: 24px;
			color: #007bff;
			word-wrap: break-word;
		}
		.card-info {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			column-gap: 0px;
			row-gap: 8px;
			font-size: 14px;
		}
		.card-info-item {
			display: flex;
			gap: 20px;
			min-width: 0;
		}
		.card-info-label {
			font-weight: bold;
			color: black;
			font-size: 16px;
			text-transform: uppercase;
			white-space: nowrap;
			flex-shrink: 0;
		}
		.card-info-value {
			color: #333;
			font-size: 15px;
			word-wrap: break-word;
			overflow-wrap: break-word;
		}

		@media (max-width: 1200px) {
			.card-info {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (max-width: 850px) {
			.card {
				flex-direction: column;
			}
			.card-content {
				width: 80%;
			}
			.card-info {
				grid-template-columns: repeat(3, 1fr);
			}
			.card-info-item {
				flex-direction: column;
				gap: 5px;
			}
		}

		@media (max-width: 650px) {
			.card-info {
				grid-template-columns: repeat(2, 1fr);
			}
		}

		@media (max-width: 480px) {
			.card {
				padding: 15px;
			}
			.card-content h3 {
				font-size: 18px;
			}
			.card-info-label {
				font-size: 14px;
			}
			.card-info-value {
				font-size: 13px;
			}
		}

		/* ----------- OVERLAY ----------- */
		.overlay {
			position: fixed;
			inset: 0;
			background: rgba(0,0,0,0.8);
			display: none;
			justify-content: center;
			align-items: center;
			z-index: 1000;
			padding: 20px;
		}
		.overlay.active {
			display: flex;
		}
		.card.expanded {
			flex-direction: column;
			max-width: 750px;
			padding: 30px;
			cursor: default;
		}
		.card.expanded:hover {
			transform: none;
		}
		.expanded img {
			width: 100%;
			max-width: 400px;
			height: auto;
			margin: 0 auto 0 auto;
			border-radius: 14px;
			display: block;
		}
		.expanded .card-content {
			width: 80%;
		}
		.expanded .card-content h3 {
			font-size: 28px;
			margin-bottom: 20px;
			text-align: center;
		}
		.expanded .card-info {
			grid-template-columns: repeat(2, 1fr);
			column-gap: 20px;
			row-gap: 15px;
			font-size: 16px;
		}
		.expanded .card-info-item {
			gap: 15px;
		}

		@media (max-width: 768px) {
			.card.expanded {
				width: 95%;
				padding: 20px;
			}
			.expanded .card-content {
				width: 100%;
			}
			.expanded .card-content h3 {
				font-size: 22px;
			}
		}

		.close-btn {
			position: absolute;
			top: 15px;
			right: 20px;
			font-size: 32px;
			cursor: pointer;
			color: #999;
			transition: color 0.3s;
			width: 40px;
			height: 40px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
		}
		.close-btn:hover {
			color: #333;
			background: #f0f0f0;
		}
	</style>

    <main>
        <h1>Découvrir en temps réel tous les avions qui survolent ma Région</h1>
        
        <section class="map-container">
            <div class="map-info">
                <h2>Carte interactive des vols</h2>
                <p>Cliquez sur un avion pour voir ses détails.</p>
            </div>
            <div id="map"></div>
        </section>
		<section>
			<h2>Liste des avions</h2>
			<div class="grid" id="aircraftGrid"></div>

			<div class="overlay" id="overlay" onclick="closeOverlay(event)">
				<div class="card expanded" id="expandedCard" onclick="event.stopPropagation()">
					<span class="close-btn" onclick="closeOverlay(event)">×</span>
					<img id="expandedImage" alt="Photo avion">
					<div class="card-content" id="expandedContent"></div>
					<?php if (isset($_SESSION['login'])) : ?>
						<button id="btn-add-fav" class="btn1">Ajouter aux favoris</button>
					<?php endif; ?>
				</div>
			</div>
		</section>
	</main>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
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
						<a href="#" onclick="event.preventDefault(); expandCardFromMap('${f.icao24}');">Voir plus</a>
                    `);
					
					addAircraftToGrid(f);
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
		
		function createAircraftCard(data) {
			const grid = document.getElementById("aircraftGrid");
			const card = document.createElement("div");
			card.className = "card";
			card.setAttribute('data-icao', data.icao24);
			
			const photo = data.photo_thumbnail || "images/defaut-plane.svg";
			
			card.innerHTML = `
				<img src="${photo}" alt="Vignette avion">
				<div class="card-content">
					<h3>${data.name || "Avion inconnu"}</h3>
					<div class="card-info">
						<div class="card-info-item">
							<span class="card-info-label">ICAO</span>
							<span class="card-info-value">${data.icao24 || "N/A"}</span>
						</div>
						<div class="card-info-item">
							<span class="card-info-label">Callsign</span>
							<span class="card-info-value">${data.callsign || "N/A"}</span>
						</div>
						<div class="card-info-item">
							<span class="card-info-label">Modèle</span>
							<span class="card-info-value">${data.model || "Inconnu"}</span>
						</div>
						<div class="card-info-item">
							<span class="card-info-label">Fabricant</span>
							<span class="card-info-value">${data.manufacturer || "Inconnu"}</span>
						</div>
						<div class="card-info-item">
							<span class="card-info-label">Pays</span>
							<span class="card-info-value">${data.origin_country || "N/A"}</span>
						</div>
						<div class="card-info-item">
							<span class="card-info-label">Position</span>
							<span class="card-info-value">${data.latitude && data.longitude ? `${data.latitude.toFixed(4)}, ${data.longitude.toFixed(4)}` : "N/A"}</span>
						</div>
					</div>
				</div>
			`;
			
			card.onclick = () => expandCard(data);
			grid.appendChild(card);
		}

		function expandCard(data) {
			const overlay = document.getElementById("overlay");
			const expandedImage = document.getElementById("expandedImage");
			const expandedContent = document.getElementById("expandedContent");
			const btnFav = document.getElementById('btn-add-fav');
			
			const photo = data.photo || data.photo_thumbnail || "images/defaut-plane.svg";
			expandedImage.src = photo;
			
			expandedContent.innerHTML = `
				<h3>${data.name || "Avion inconnu"}</h3>
				<div class="card-info">
					<div class="card-info-item">
						<span class="card-info-label">ICAO</span>
						<span class="card-info-value">${data.icao24 || "N/A"}</span>
					</div>
					<div class="card-info-item">
						<span class="card-info-label">Callsign</span>
						<span class="card-info-value">${data.callsign || "N/A"}</span>
					</div>
					<div class="card-info-item">
						<span class="card-info-label">Modèle</span>
						<span class="card-info-value">${data.model || "Inconnu"}</span>
					</div>
					<div class="card-info-item">
						<span class="card-info-label">Fabricant</span>
						<span class="card-info-value">${data.manufacturer || "Inconnu"}</span>
					</div>
					<div class="card-info-item">
						<span class="card-info-label">Pays d'origine</span>
						<span class="card-info-value">${data.origin_country || "N/A"}</span>
					</div>
					<div class="card-info-item">
						<span class="card-info-label">Latitude</span>
						<span class="card-info-value">${data.latitude ? data.latitude.toFixed(6) : "N/A"}</span>
					</div>
					<div class="card-info-item">
						<span class="card-info-label">Longitude</span>
						<span class="card-info-value">${data.longitude ? data.longitude.toFixed(6) : "N/A"}</span>
					</div>
				</div>
			`;
			
			if (btnFav) {
				btnFav.style.display = "inline-block";
				btnFav.style.margin = "2%";

				btnFav.dataset.icao = data.icao24 || "";
				btnFav.dataset.callsign = data.callsign || "";
				btnFav.dataset.model = data.model || "";
				btnFav.dataset.airline = "";
				btnFav.dataset.origin = "";
				btnFav.dataset.dest = "";

				btnFav.innerText = "Ajouter aux favoris";
				btnFav.disabled = false;
			}
			
			overlay.classList.add("active");
		}

		function closeOverlay(event) {
			const overlay = document.getElementById("overlay");
			overlay.classList.remove("active");
		}

		// Stocker les avions déjà affichés pour éviter les doublons
		let displayedAircraft = new Set();

		async function addAircraftToGrid(flight) {
			// Vérifier si l'avion est déjà affiché
			if (displayedAircraft.has(flight.icao24)) {
				return;
			}
			try {
				// Récupérer les informations détaillées
				const name = await getAircraftName(flight.icao24);
				const photos = await getAircraftPhotos(flight.icao24);
				const full = await getAircraftData(flight.icao24);
				
				// Fusionner toutes les données
				const merged = {
					icao24: flight.icao24,
					callsign: flight.callsign || "N/A",
					origin_country: flight.origin_country || "N/A",
					latitude: flight.latitude,
					longitude: flight.longitude,
					name: name || "Avion inconnu",
					model: full?.type || "Inconnu",
					manufacturer: full?.manufacturer || "Inconnu",
					photo: photos?.url_photo || null,
					photo_thumbnail: photos?.url_photo_thumbnail || null
				};
				
				// Créer la carte dans la grille
				createAircraftCard(merged);
				
				displayedAircraft.add(flight.icao24);
			} catch (error) {
				console.error(`Erreur lors de l'ajout de l'avion ${flight.icao24}:`, error);
			}
		}
		
		const btnFav = document.getElementById('btn-add-fav');
		if (btnFav) btnFav.style.display = "none";

		if (btnFav) btnFav.style.display = "none";
		
		if (btnFav) {
            btnFav.addEventListener('click', async function() {
                const planeData = {
                    icao24: btnFav.dataset.icao,
                    callsign: btnFav.dataset.callsign,
                    aircraft_model: btnFav.dataset.model,
                    airline: btnFav.dataset.airline,
                    origin_iata: btnFav.dataset.origin,
                    destination_iata: btnFav.dataset.dest
                };

                try {
                    const response = await fetch('ajax/favorites/add_favotite.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'include',
                        body: JSON.stringify(planeData)
                    });

                    const result = await response.json();

                    if (result.success) {
                        btnFav.innerText = "Ajouté !";
                        btnFav.disabled = true;
                    } else {
                        alert("Erreur : " + (result.error || "Inconnue"));
                        btnFav.disabled = false;
                    }
                } catch (err) {
                    console.error(err);
                    alert("Erreur de connexion au serveur");
                    btnFav.disabled = false;
                }
            });
        }
		
		function expandCardFromMap(icao24) {
			map.closePopup();
			
			const cardElement = document.querySelector(`[data-icao="${icao24}"]`);
			if (cardElement) {
				cardElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
				cardElement.click();
			}
		}
		
        // démarrage
        initPosition();
        refreshPlanes();

        // refresh toutes les 15 secondes
        // setInterval(refreshPlanes, 15000);
    </script>
<?php
require "../src/includes/footer.inc.php";
?>