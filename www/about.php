<?php
    declare(strict_types=1);
    if (session_status() === PHP_SESSION_NONE && isset($_COOKIE[session_name()])) {
        session_start();
    }

    $description = "Découvrez les membres de l'équipe qui ont realisé WhatIsThisPlane ! Accédez à leurs contacts, les compétences de chacun, et entrez facilement en contact avec l'équipe.";
    $title = "À propos de WhatisThisPlane – Tout savoir sur notre mission";
    require "../src/includes/header.inc.php";
?>

<main>
    <h1>À propos de <?=SITE_NAME?></h1>
    <section class="team">
        <h2>Les membres du projet</h2>
        <p>
            Voici l’équipe qui a rendu ce projet possible, chacun apportant ses compétences uniques.
        </p>

        <div class="grille-cartes">
            <article class="carte">
                <figure>
                    <img src="images/employee-icon.svg" alt="photo (avatar) du membre" width="150" height="150"/>
                </figure>
                <h3>BISIAUX Valentin</h3>
                <p><strong>Rôle :</strong> Intégration API et Services Externes</p>
                <p><strong>Description :</strong> Récupère les données des avions depuis OpenSky API. Gère les appels API, traite les réponses JSON et stocke les informations des vols dans la base de données.</p>
                <p><strong>Compétences :</strong> API, JavaScript, Leaflet (carte), Géolocalisation, Intégration de données externes, Git</p>
                <p><strong>Contact :</strong>  valentin.bisiaux@etu.cyu.fr</p>
            </article>

            <article class="carte">
                <figure>
                    <img src="images/businessman-icon.svg" alt="photo (avatar) du membre" width="150" height="150"/>
                </figure>
                <h3>COSTA Mathéo</h3>
                <p><strong>Rôle :</strong> Gestion Base de Données et Utilisateurs</p>
                <p><strong>Description :</strong> Crée et gère la base de données MySQL. S'occupe des tables, des inscriptions, connexions et profils utilisateurs.</p>
                <p><strong>compétences :</strong> MySQL, PHP, AJAX, Système de login, Sécurité des données, Git</p>
                <p><strong>Contact :</strong> matheo.costa4@etu.u-cergy.fr</p>
            </article>

            <article class="carte">
                <figure>
                    <img src="images/employee-icon.svg" alt="photo (avatar) du membre" width="150" height="150"/>
                </figure>
                <h3>DIALLO Thierno Abasse</h3>
                <p><strong>Rôle :</strong> Développement Frontend et Interface</p>
                <p><strong>Description :</strong> Crée l'interface utilisateur du site. Assure que tout soit responsive. Polyvalent et assure une expérience utilisateur fluide</p>
                <p><strong>Compétences :</strong> CRON, HTML/CSS, JavaScript, Design responsive, Création d'interfaces interactives, Git</p>
                <p><strong>Contact :</strong> thierno-abasse.diallo1@etu.cyu.fr</p>
            </article>
        </div>
    </section>

    <section>
        <h2>Discutez avec l’équipe</h2>
        <p>Vous avez une question ou une suggestion ? Notre équipe vous répondra sous deux jours ouvrés !</p>
        <form action="#" method="post" id="contact_form">
            <div class="name-container">
                <div class="name-item">
                    <label for="prenom">Prenom</label>
                    <input name="prenom" id="prenom" type="text" placeholder="Votre prenom"/>
                </div>
                <div class="name-item">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" placeholder="Nom"/>
                </div>
            </div>
            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Email" required="required"/>
            <label for="questions">Questions</label>
            <textarea name="questions" id="questions" cols="30" rows="10"></textarea>
            <input type="submit"/>
        </form>
    </section>
</main>

<?php
    require "../src/includes/footer.inc.php";
?>