<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $title = "Accueil";
    require "../src/includes/header.inc.php";
?>

<main>
    <h1>Découvrez quel avion vole au-dessus de vous</h1>
	<section class="hero">
        <div class="hero-content">
            <h2>Découvrez les vols au-dessus de vous ou explorez le trafic aérien en direct.</h2>
            <p>Utilisez votre position ou recherchez un vol pour savoir quel avion passe au-dessus de vous.</p>
            <a href="work.php">Identifier l’avion au-dessus de moi</a>
        </div>
    </section>
    <section class="cartes-services mt-5" id="service">
        <h2>Nos services</h2>
        <div class="grille-cartes">
            <article class="carte">
                <h3>Voir l'avion au-dessus de vous</h3>
                <p>Activez la géolocalisation pour découvrir en temps réel les avions qui survolent votre position. Cliquez sur un avion pour obtenir ses informations détaillées (compagnie, altitude, vitesse, destination, etc.).</p>
            </article>

            <article class="carte">
                <h3>Rechercher un vol</h3>
                <p>Utilisez la barre de recherche pour trouver un avion ou un vol spécifique grâce à son numéro de vol, sa compagnie ou son aéroport. Accédez rapidement à toutes les informations le concernant.</p>
            </article>

            <article class="carte">
                <h3>Fiche détaillée des avions</h3>
                <p>Consultez les informations complètes de chaque avion : type d’appareil, compagnie aérienne, aéroport de départ et d’arrivée, altitude, vitesse et trajectoire.</p>
            </article>

            <article class="carte">
                <h3>Carte du trafic aérien</h3>
                <p>Visualisez la carte du trafic aérien en direct. Pour le moment, elle n’est pas interactive, mais vous pouvez déjà observer les avions détectés et leurs positions actualisées.</p>
            </article>

            <article class="carte">
                <h3>Consulter vos favoris</h3>
                <p>Créez un compte ou connectez-vous pour enregistrer vos avions préférés. Vous pourrez ensuite les retrouver facilement dans votre espace personnel, à tout moment.</p>
            </article>

            <article class="carte">
                <h3>Données historiques</h3>
                <p>Accédez à l’histogramme interactif pour consulter les avions les plus recherchés sur notre plateforme. Explorez les tendances et découvrez les modèles les plus populaires.</p>
            </article>
        </div>
    </section>
    <section class="about">
        <h2>À propos de whatisthisplane</h2>
        <p class="intro">
            <strong>whatisthisplane</strong> est une plateforme qui vous permet d’identifier les avions
            qui survolent votre position ou de suivre le trafic aérien en temps réel.
        </p>
    </section>
</main>


<?php
    require "../src/includes/footer.inc.php";
?>