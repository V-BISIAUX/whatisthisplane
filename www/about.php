<?php
    declare(strict_types=1);
    $title = "A propos";
    require "../src/includes/header.inc.php";
?>

<main>
    <section class="team">
        <h2>Les membres du projet</h2>
        <p>
            Voici l’équipe qui a rendu ce projet possible, chacun apportant ses compétences uniques.
        </p>

        <div class="grille-cartes">
            <article class="carte">
                <div class="photo"></div>
                <h3>Nom et prénom</h3>
                <p><strong>Rôle :</strong></p>
                <p><strong>Description :</strong></p>
                <p><strong>Compétences :</strong></p>
                <p><strong>Contact :</strong></p>
            </article>

            <article class="carte">
                <div class="photo"></div>
                <h3>Nom et prénom</h3>
                <p><strong>Rôle :</strong></p>
                <p><strong>Description :</strong></p>
                <p><strong>Compétences :</strong></p>
                <p><strong>Contact :</strong></p>
            </article>

            <article class="carte">
                <div class="photo"></div>
                <h3>Nom et prénom</h3>
                <p><strong>Rôle :</strong></p>
                <p><strong>Description :</strong></p>
                <p><strong>Compétences :</strong></p>
                <p><strong>Contact :</strong></p>
            </article>
        </div>
    </section>
</main>

<?php
    require "../src/includes/footer.inc.php";
?>