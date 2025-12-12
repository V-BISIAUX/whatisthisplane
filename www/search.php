<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $description = "Trouvez les informations des avions grâce à leur code ICAO, leur numéro de vol,leurs aéroports leurs compagnies.";
    $title = "Trouver les informations de l'avion";
    require "../src/includes/header.inc.php";
?>
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
				output += `<img src="${photos.url_photo_thumbnail}" alt="Miniature avion"/>`;
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
				output += `<img class="aircraft-photo" src="${photos.url_photo}" alt="Photo de l'avion"/></div>`;
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