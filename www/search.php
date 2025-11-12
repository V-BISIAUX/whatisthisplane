<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
    session_start();
}

$title = "Accueil";
require "../src/includes/header.inc.php";
?>
    <main>
        <h1>Rechercher un vol, une compagnie ou un avion</h1>
        <p>Entrez un numéro de vol, le nom d’un avion ou d’une compagnie pour obtenir toutes les informations.</p>
        <section class="search">
            <form method="post" action="#">
                <label for="flight-search">Numéro de vol, avion ou compagnie</label>
                <input type="text" id="flight-search" name="flight-search" placeholder="Numéro de vol, avion ou compagnie..." required="required"/>
                <button type="submit">Rechercher</button>
            </form>
        </section>

        <section class="details">
            <h2>Aperçu des avions les plus recherchés</h2>
            <div class="grille-carte">
                <article class="carte">
                    <figure>
                        <img src="" alt="vignette de l'avion (nom de l'avion)">
                    </figure>
                    <p>Nom de l'avion / compagnie</p>
                    <p>Numéro de vol associés</p>
                    <p>Statut actuel ()</p>
                </article>
                <article class="carte">
                    <figure>
                        <img src="" alt="vignette de l'avion (nom de l'avion)">
                    </figure>
                    <p>Nom de l'avion / compagnie</p>
                    <p>Numéro de vol associés</p>
                    <p>Statut actuel ()</p>
                </article>
            </div>
        </section>
    </main>
<?php
require "../src/includes/footer.inc.php";
?>