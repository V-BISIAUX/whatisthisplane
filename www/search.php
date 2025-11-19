<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    session_start();
}

$title = "Trouver les informations de l'avion";
require "../src/includes/header.inc.php";
?>
    <main>
        <h1>Rechercher un vol, une compagnie ou un avion</h1>
        <p>Entrez un numéro de vol, le nom d’un avion, le code icao ou d’une compagnie pour obtenir toutes les informations.</p>
        <p><strong>Pour l'instant, on a juste la recherche par icao qui marche</strong></p>
        <section class="search">
            <h2>Rechercher</h2>
            <form method="post" action="#" id="form-search">
                <label for="flight-search">Numéro de vol, avion ou compagnie</label>
                <input type="text" id="flight-search" name="flight-search" placeholder="Numéro de vol, avion ou compagnie..." required="required"/>
                <button type="submit">Rechercher</button>
            </form>
            <aside id="searchresults" style="display: block">
                <h3>Résultats de la recherche de vols</h3>
                <aside id="results"></aside>
            </aside>
        </section>

        <section class="details">
            <h2>Aperçu des avions les plus recherchés</h2>
            <div class="grille-carte">
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

    <script>
        document.getElementById('form-search').addEventListener('submit', async function(e) {
            e.preventDefault();
            const result = document.getElementById('results');
            result.style.display = 'block';
            const searchValue = document.getElementById('flight-search').value;
            console.log(searchValue);
            if (!searchValue) {
                console.log("Champ vide");
            }

            const data = await getFlightByIcao24(searchValue);
			console.log(data);
			
			if (!data) {
				result.innerHTML = `<p>ICAO non existant</p>`;
				return;
			}
            
			const parseData = parseOpenSkyData(data);
			
			const aircraftName = await getAircraftName(searchValue);
			const photos = await getAircraftPhotos(searchValue);

			let output = "";
			output += `<div class="aircraft-header">`;

			if (photos?.url_photo_thumbnail) {
				output += `<img src="${photos.url_photo_thumbnail}" alt="Miniature de l'avion">`;
			}
			output += `<strong>${aircraftName ?? "Avion inconnu"}</strong>`;
			output += `</div>`;
			
			output += `<p>ICAO : ${parseData.icao24}</p>`;
			output += `<p>Callsign : ${parseData.callsign}</p>`;
			output += `<p>Pays d'origin : ${parseData.origin_country}</p>`;
			output += `<p>Heure : ${parseData.time}</p>`;
			output += `<p>Derniere position : ${parseData.last_contact}</p>`;
			output += `<p>Longitude : ${parseData.longitude}</p>`;
			output += `<p>Latitude : ${parseData.latitude}</p>`;
			
			if (photos?.url_photo) {
				output += `<p>Photos :</p>`;
				output += `<img class="aircraft-photo" src="${photos.url_photo}" alt="Photo de l'avion">`;

			} else {
				output += `<p>Aucune photo disponible</p>`;
			}
			
			result.innerHTML = output;

        });
    </script>
    <script src="js/api.js"></script>
<?php
require "../src/includes/footer.inc.php";
?>