<?php
    declare(strict_types=1);
    session_start();
    $title = "Connexion";
    require "../src/includes/header.inc.php";
?>
<script>
    async function registerUser() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        const response = await fetch('ajax_register.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, email, password})
        });

        const data = await response.json();
        const messageElem = document.getElementById('message');
        if (data.success) {
            messageElem.style.color = 'green';
            messageElem.textContent = data.message;
        } else {
            messageElem.style.color = 'red';
            messageElem.textContent = data.error || 'Erreur inconnue';
        }
    }
</script>
    <main>
        <?php if(!empty($msg1)){
            echo "<p style='color: red; text-align: center'>".$msg1."</p>";
        }
        ?>
        <form method="post" action="">
            <p class="legend">Se connecter</p>
            <label for="login"> Entrez votre login</label>
            <div class="input-icon">
                <i class="fa-solid fa-id-card"></i>
                <input type="text" id="login" name="login" placeholder="Tapez ici votre login"  required="required"/>
            </div>
            <label for="password">Entrez votre mot de passe</label>
            <div class="input-icon">
                <i class="fa-solid fa-lock" ></i>
                <input type="password" id="password" name="password" placeholder="Tapez ici votre mot de passe" required="required"/>
                <i class="fa-solid fa-eye" id="icon-eye"></i>
            </div>
<!--            <div class="g-recaptcha" data-sitekey="--><?php //= htmlspecialchars($captcha['siteKey'])?><!--"></div>-->
            <input type="submit" value="Connexion"/>
        </form>

        <span>Si vous n'avez pas de compte, <a href="inscription.php">cliquez pour s'inscrire</a></span>
    </main>
<!--    <script async src="https://www.google.com/recaptcha/api.js" defer></script>-->

<?php
    require "../src/includes/footer.inc.php";
?>