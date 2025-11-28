<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $title = "En travaux – WhatisThisPlane";
    require "../src/includes/header.inc.php";
?>

    <main>
        <div>
            <h1>WhatIsThisPlane</h1>
            <span>Dernière modification : <time datetime="2025-11-06">06 novembre 2025</time></span>
        </div>
        <p style="font-size: 20px; line-height: 1.3; font-family: 'Montserrat', sans-serif; font-weight: normal; text-align: justify;">
            Cette page est actuellement en cours de préparation. Nous travaillons activement pour vous fournir du contenu pertinent.
            Rendez-vous bientôt pour découvrir la suite !</p>

        <div style="text-align: center; display: flex;">
            <img src="images/work-in-progress.png" alt="Page en construction" class="work"/>
        </div>
    </main>


<?php
    require "../src/includes/footer.inc.php";
?>