<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $description = "Trouvez les informations des avions grâce à leur code ICAO, leur numéro de vol,leurs aéroports leurs compagnies.";
    $title = "Trouver les informations de l'avion";
    require "../src/includes/header.inc.php";
?>

<style>
/* Info de recherche */
.search-info {
    background: #f0f7ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #2196F3;
}

.search-note {
    color: #666;
    font-size: 0.9em;
    margin-top: 8px;
}

/* Liste des vols */
.flights-list {
    display: grid;
    gap: 15px;
}

/* Carte de vol */
.flight-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.2s;
}

.flight-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

/* En-tête */
.flight-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.flight-header h4 {
    margin: 0;
    font-size: 1.3em;
    color: #2c3e50;
}

/* Badges de statut */
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75em;
    font-weight: bold;
    text-transform: uppercase;
}

.badge-active {
    background: #4CAF50;
    color: white;
}

.badge-scheduled {
    background: #2196F3;
    color: white;
}

.badge-landed {
    background: #9E9E9E;
    color: white;
}

.badge-cancelled {
    background: #f44336;
    color: white;
}

/* Compagnie */
.flight-airline {
    margin-bottom: 12px;
    font-weight: 500;
    color: #555;
}

.code {
    color: #999;
    font-size: 0.9em;
    font-weight: normal;
}

/* Route */
.flight-route {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 15px;
    align-items: start;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    margin: 10px 0;
}

.airport {
    text-align: center;
}

.airport-code {
    font-size: 1.5em;
    font-weight: bold;
    color: #2c3e50;
}

.airport-name {
    font-size: 0.85em;
    color: #666;
    margin: 4px 0 8px 0;
}

.arrow {
    font-size: 1.5em;
    color: #2196F3;
    align-self: center;
}

/* Horaires */
.time {
    font-size: 0.8em;
    margin: 3px 0;
    color: #666;
}

.time.actual {
    color: #4CAF50;
    font-weight: bold;
}

.time.estimated {
    color: #FF9800;
}

/* Tags (terminal, porte) */
.tags {
    margin-top: 8px;
}

.tag {
    display: inline-block;
    background: #e3f2fd;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75em;
    margin: 2px;
}

/* Avion */
.flight-aircraft {
    margin-top: 10px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 6px;
    font-size: 0.9em;
}

.aircraft-grid {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 5px;
}

/* Position temps réel */
.flight-live {
    margin-top: 10px;
    padding: 10px;
    background: #e8f5e9;
    border-radius: 6px;
    font-size: 0.9em;
}

.live-grid {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 5px;
}

/* Sections aéroport */
.airport-results {
    margin-top: 20px;
}

.airport-section {
    margin: 25px 0;
}

.section-title {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 1.1em;
}

/* Message vide */
.no-results {
    text-align: center;
    padding: 30px;
    color: #999;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .flight-route {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .arrow {
        transform: rotate(90deg);
        margin: 5px 0;
    }
    
    .airport-code {
        font-size: 1.2em;
    }
    
    .aircraft-grid,
    .live-grid {
        flex-direction: column;
        gap: 8px;
    }
}
</style>
    <main>
        <h1>Rechercher un vol, une compagnie ou un avion</h1>
        <p>Entrez un numéro de vol, le code icao d’un avion, le code de l'aéroport ou d’une compagnie pour obtenir toutes les informations.</p>
        <section class="search">
            <h2>Rechercher un avion ou un vol</h2>
			<div>
				<h3>Recherche par code ICAO24</h3>
				<p>Entrez un code ICAO24 (6 caractères hexadécimaux, ex: 3c6444)</p>
				<form method="post" action="#" id="form-search-icao">
					<label for="icao-search">Code ICAO24</label>
					<input 
						type="text" 
						id="icao-search" 
						name="icao-search" 
						placeholder="Exemple: 3c6444, a1b2c3..." 
						pattern="[0-9a-fA-F]{6}"
						title="6 caractères hexadécimaux (0-9, A-F)"
						required="required"
					/>
					<button type="submit">Rechercher par ICAO</button>
				</form>
			</div>
			<div>
				<h3>Recherche avancée</h3>
				<p>Numéro de vol, code aéroport ou compagnie</p>
				<form method="post" action="#" id="form-search-api">
					<label for="api-search">Numéro de vol, aéroport ou compagnie</label>
					<input 
						type="text" 
						id="api-search" 
						name="api-search" 
						placeholder="Exemple: AF123, CDG, AF..." 
						required="required"
					/>
					<button type="submit">Rechercher</button>
				</form>
			</div>
            <aside id="searchresults" style="display: none">
                <h3>Résultats de la recherche de vols</h3>
                <aside id="results"></aside>
                <?php if (isset($_SESSION['login'])) : ?>
                    <button id="btn-add-fav" class="btn1">Ajouter aux favoris</button>
                <?php endif; ?>
            </aside>
        </section>

        <section class="details">
            <h2>Aperçu des avions les plus recherchés</h2>
            <div class="grille-cartes">
                <article class="carte">
                    <h3>Nom de l'avion</h3>
<!--                    <figure>-->
<!--                        <img src="" alt="vignette de l'avion (nom de l'avion)">-->
<!--                    </figure>-->
                    <p>Nom de l'avion / compagnie</p>
                    <p>Numéro de vol associés</p>
                    <p>Statut actuel ()</p>
                </article>
                <article class="carte">
                    <h3>Nom de l'avion</h3>
<!--                    <figure>-->
<!--                        <img src="" alt="vignette de l'avion (nom de l'avion)">-->
<!--                    </figure>-->
                    <p>Nom de l'avion / compagnie</p>
                    <p>Numéro de vol associés</p>
                    <p>Statut actuel ()</p>
                </article>
            </div>
        </section>
    </main>
	
	<script src="js/api.js"></script>
	<script src="js/api_php.js"></script>
    <script>
        document.getElementById('form-search-icao').addEventListener('submit', async function(e) {
            e.preventDefault();

			const result = document.getElementById('results');
			const btnFav = document.getElementById('btn-add-fav');
			const searchValue = document.getElementById('icao-search').value.trim().toLowerCase();

			document.getElementById('searchresults').style.display = 'block';
			result.innerHTML = "Recherche en cours...";

			if (btnFav) btnFav.style.display = "none";

			if (!searchValue) {
				result.innerHTML = "<p>Veuillez entrer quelque chose.</p>";
				return;
			}

			const data = await getFlightByIcao24(searchValue);

			if (!data) {
				result.innerHTML = "<p>Aucun avion trouvé pour cet ICAO.</p>";
				return;
			}

			const parseData = parseOpenSkyData(data);
			const aircraftName = await getAircraftName(searchValue);
			const photos = await getAircraftPhotos(searchValue);

			let output = "";
			output += `<div class="aircraft-header">`;

			if (photos?.url_photo_thumbnail) {
				output += `<img src="${photos.url_photo_thumbnail}" alt="Miniature avion">`;
			}

			output += `<strong>${aircraftName ?? "Avion inconnu"}</strong></div>`;
			output += `<div class="aircraft-body"><div class="aircraft-info">`;

			output += `<p>ICAO : ${parseData.icao24}</p>`;
			output += `<p>Callsign : ${parseData.callsign}</p>`;
			output += `<p>Pays d'origine : ${parseData.origin_country}</p>`;
			output += `<p>Heure : ${parseData.time}</p>`;
			output += `<p>Longitude : ${parseData.longitude}</p>`;
			output += `<p>Latitude : ${parseData.latitude}</p>`;
			output += `</div>`;

			if (photos?.url_photo) {
				output += `<div class="aircraft-photos">`;
				output += `<img class="aircraft-photo" src="${photos.url_photo}" alt="Photo de l'avion"></div>`;
			} else {
				output += `<p>Aucune photo disponible</p>`;
			}

			output += `</div>`;
			result.innerHTML = output;
			
			try {
				await fetch('ajax/planes/add_airplane.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					credentials: 'include',
					body: JSON.stringify({
						icao24: parseData.icao24,
						callsign: parseData.callsign,
						aircraft_model: aircraftName,
						airline: parseData.origin_country,
						view_source: 'search'
					})
				});
			} catch (error) {
				console.error('Erreur lors de l\'enregistrement de la consultation:', error);
			}

			if (btnFav) {
				btnFav.style.display = "inline-block";
				btnFav.style.margin = "2%";

				btnFav.dataset.icao = parseData.icao24;
				btnFav.dataset.callsign = parseData.callsign || "";
				btnFav.dataset.model = aircraftName || "";
				btnFav.dataset.airline = parseData.origin_country || "";
				btnFav.dataset.origin = "";
				btnFav.dataset.dest = "";

				btnFav.innerText = "Ajouter aux favoris";
				btnFav.disabled = false;
			}
		});
		
		document.getElementById('form-search-api').addEventListener('submit', async function(e) {
			e.preventDefault();

			const result = document.getElementById('results');
			const btnFav = document.getElementById('btn-add-fav');
			const searchValue = document.getElementById('api-search').value.trim();

			document.getElementById('searchresults').style.display = 'block';
			result.innerHTML = "🔍 Recherche en cours...";

			if (btnFav) btnFav.style.display = "none";

			if (!searchValue) {
				result.innerHTML = "<p class='no-results'>Veuillez entrer quelque chose.</p>";
				return;
			}
			
			try {
				const response = await fetch(`api/search_flight.php?query=${encodeURIComponent(searchValue)}`);
				const data = await response.json();

				if (!data.success) {
					result.innerHTML = `<p>Erreur : ${data.error}</p>`;
					return;
				}

				result.innerHTML = renderAviationStackResults(data);

			} catch (err) {
				console.error(err);
				result.innerHTML = "<p>Erreur de connexion à l'API PHP.</p>";
			}
		});


        const btnFav = document.getElementById('btn-add-fav');

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
    </script>
<?php
require "../src/includes/footer.inc.php";
?>