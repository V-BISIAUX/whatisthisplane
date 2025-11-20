<?php
    declare(strict_types=1);
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
<!--                <figure>-->
<!--                    <img src="" alt="photo (avatar) du membre">-->
<!--                </figure>-->
                <h3>BISIAUX Valentin</h3>
                <p><strong>Rôle :</strong></p>
                <p><strong>Description :</strong></p>
                <p><strong>Compétences :</strong></p>
                <p><strong>Contact :</strong>  valentin.bisiaux@etu.cyu.fr</p>
            </article>

            <article class="carte">
<!--                <figure>-->
<!--                    <img src="" alt="photo (avatar) du membre">-->
<!--                </figure>-->
                <h3>COSTA Mathéo</h3>
                <p><strong>Rôle :</strong></p>
                <p><strong>Description :</strong></p>
                <p><strong>Compétences :</strong></p>
                <p><strong>Contact :</strong> matheo.costa4@etu.u-cergy.fr</p>
            </article>

            <article class="carte">
<!--                <figure>-->
<!--                    <img src="" alt="photo (avatar) du membre">-->
<!--                </figure>-->
                <h3>DIALLO Thierno Abasse</h3>
                <p><strong>Rôle :</strong></p>
                <p><strong>Description :</strong></p>
                <p><strong>Compétences :</strong></p>
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
            <input type="submit">
        </form>
    </section>
</main>

<?php
    require "../src/includes/footer.inc.php";
?>