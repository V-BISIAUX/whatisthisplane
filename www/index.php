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