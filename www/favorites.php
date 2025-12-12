<?php
    declare(strict_types=1);
    session_start();
    if (!isset($_SESSION['login'])) {
        header('Location: index.php');
        exit;
    }

    $description = "Connectez vous pour accédez votre liste d'avions favoris. Retrouvez, gérez facilement les informations de vos avions.";
    $title = "Mes avions favoris - WhatIsThisPlane";
    require "../src/includes/header.inc.php";
?>

    <main>
        <h1>Mes avions favoris</h1>
        <p>Retrouvez ici les avions que vous avez sauvegardés.</p>

        <div id="msg-container"></div>

        <div id="favorites-container" class="grille-cartes">
            <p>Chargement de vos favoris...</p>
        </div>
		
		<div id="loading-more" style="text-align: center; padding: 20px; display: none;">
            <p>Chargement...</p>
        </div>
    </main>

    <script>
        let currentOffset = 0;
        const limit = 4;
        let isLoading = false;
        let hasMore = true;
		
        async function loadFavorites() {
			if (isLoading || !hasMore) return;
			isLoading = true;
			
			const loadingMore = document.getElementById('loading-more');
            const container = document.getElementById('favorites-container');
			
			if (currentOffset > 0) {
                loadingMore.style.display = 'block';
            }

            try {
                const response = await fetch(`ajax/favorites/get_favorite.php?offset=${currentOffset}&limit=${limit}`, {
                    method: 'GET',
                    credentials: 'include',
                });

                const data = await response.json();

                if (!data.success) {
                    if (currentOffset === 0) {
                        container.innerHTML = '<p class="error">Erreur: ${data.error}</p>';
                    }
                    hasMore = false;
                    return;
                }

                // Premier chargement et aucun résultat
                if (currentOffset === 0 && data.favorites.length === 0) {
                    container.innerHTML = `<p>Vous n'avez aucun avion en favori pour le moment.</p>`;
                    hasMore = false;
                    return;
                }

                // Retirer le message de chargement initial
                if (currentOffset === 0) {
                    container.innerHTML = '';
                }

                // Plus de résultats à charger ?
                if (data.favorites.length < limit) {
                    hasMore = false;
                }

                data.favorites.forEach(fav => {
                    const card = document.createElement('article');
                    card.className = 'carte';

                    const callsign = fav.callsign ? fav.callsign : 'N/A';
                    const model = fav.aircraft_model ? fav.aircraft_model : 'Modèle inconnu';
                    const airline = fav.airline ? fav.airline : 'Compagnie inconnue';

                    let route = "Trajet inconnu";
                    if (fav.saved_flight.origin_iata && fav.saved_flight.destination_iata) {
                        route = `${fav.saved_flight.origin_iata} ➝ ${fav.saved_flight.destination_iata}`;
                    }

                    card.innerHTML = `
                    <h3>${model}</h3>
                    <p><strong>ICAO:</strong> ${fav.icao24}</p>
                    <p><strong>Callsign:</strong> ${callsign}</p>
                    <p><strong>Compagnie:</strong> ${airline}</p>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
                    <p><em>Vol mémorisé :</em><br>${route}</p>
                    <p style="font-size: 0.8em; color: gray;">Ajouté le ${new Date(fav.added_at).toLocaleDateString()}</p>

                    <button class="btn-delete" onclick="removeFavorite('${fav.icao24}')" style="background-color: #ff4d4d; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-top: 10px;">
                        Supprimer
                    </button>
                `;

                    container.appendChild(card);
                });
				
				currentOffset += data.favorites.length;

            } catch (error) {
                console.error(error);
                if (currentOffset === 0) {
                    container.innerHTML = `<p>Impossible de charger vos favoris.</p>`;
                }
                hasMore = false;
            } finally {
                isLoading = false;
                loadingMore.style.display = 'none';
            }
        }

        async function removeFavorite(icao24) {
            if (!confirm("Voulez-vous vraiment supprimer cet avion de vos favoris ?")) {
                return;
            }

            try {
                const response = await fetch('ajax/favorites/remove_favorite.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'include',
                    body: JSON.stringify({ icao24: icao24 })
                });

                const result = await response.json();

                if (result.success) {
                    const msg = document.getElementById('msg-container');
                    msg.innerHTML = '<p style="color: green;">Avion supprimé avec succès.</p>';
                    setTimeout(() => msg.innerHTML = '', 3000);

					currentOffset = 0;
                    hasMore = true;
                    document.getElementById('favorites-container').innerHTML = '';
                    loadFavorites();
                } else {
                    alert("Erreur lors de la suppression : " + result.error);
                }

            } catch (error) {
                console.error(error);
                alert("Erreur réseau lors de la suppression.");
            }
        }

        function handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight;
            const clientHeight = window.innerHeight;

            // 400px à la fin de la page
            if (scrollTop + clientHeight >= scrollHeight - 400) {
                loadFavorites();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadFavorites();
            window.addEventListener('scroll', handleScroll);
        })
    </script>

<?php
    require "../src/includes/footer.inc.php";
?>