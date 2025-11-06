<?php
    declare(strict_types=1);
    $title = "Inscription";
    require "../src/includes/header.inc.php";
?>

    <main>
        <?php if(!empty($msg1)){
            echo "<p style='color: red; text-align: center'>".$msg1."</p>";
        }
        ?>
        <form method="post" action="traitement_inscription.php">
            <p class="legend">S"inscrire</p>
            <label for="login">Entrez votre login</label>
            <div class="input-icon">
                <i class="fa-solid fa-id-card"></i>
                <input type="text" id="login" name="login" oninput="checkLoginDB()" placeholder="Tapez ici votre login" required="required"/>
            </div>
            <span id="ins-status"></span>
            <label for="password">Entrez votre mot de passe</label>
            <div class="input-icon">
                <i class="fa-solid fa-lock" ></i>
                <input type="password" id="password" name="password" placeholder="Tapez ici votre mot de passe" required="required"/>
                <i class="fa-solid fa-eye" id="icon-eye"></i>
            </div>
            <label for="email">Entrez votre email</label>
            <div class="input-icon">
                <i class="fa-solid fa-envelope"></i>
                <input type="email" id="email" name="email" oninput="checkMail()" placeholder="Tapez ici votre email" required="required"/>
            </div>
            <span id="email-status"></span>
            <label for="prenom">Entrez votre Prénom</label>
            <div class="input-icon">
                <i class="fa-solid fa-user-edit"></i>
                <input type="text" id="prenom" name="prenom" placeholder="Tapez ici votre prenom" required="required"/>
            </div>
            <label for="nom">Entrez votre nom</label>
            <div class="input-icon">
                <i class="fa-solid fa-signature"></i>
                <input type="text" id="nom" name="nom" placeholder="Tapez ici votre nom" required="required"/>
            </div>
<!--            <div class="g-recaptcha" data-sitekey="--><?php //= htmlspecialchars($captcha['siteKey'])?><!--"></div>-->
            <input type="submit" value="S'inscrire"/>
        </form>

        <span>Si vous avez un compte, <a href="cnx.php">cliquez ici pour vous connecter</a></span>
    </main>

<!--    <script async src="https://www.google.com/recaptcha/api.js" defer></script>-->
    <script src="js/ajax.js"></script>
<?php
    require "../src/includes/footer.inc.php";
?>