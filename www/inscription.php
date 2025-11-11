<?php
    declare(strict_types=1);
    $title = "Inscription";
    require_once "../src/config/config.php";
    require "../src/includes/header.inc.php";
?>

    <main>
        <form method="post" id="subscribe-form">
            <p class="legend">S"inscrire</p>
            <label for="login">Entrez votre login</label>
            <div class="input-icon">
                <i class="fa-solid fa-id-card"></i>
                <input type="text" id="login" name="login" placeholder="Tapez ici votre login" required="required"/>
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
                <input type="email" id="email" name="email" placeholder="Tapez ici votre email" required="required"/>
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
            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(RECAPTCHA_SITEKEY)?>"></div>
            <input type="submit" value="S'inscrire"/>
            <span id="message"></span>
        </form>

        <span>Si vous avez un compte, <a href="cnx.php">cliquez ici pour vous connecter</a></span>
    </main>
    <script>
        document.getElementById('subscribe-form').addEventListener('submit', function(event){
            event.preventDefault();
            registerUser();
        });
        async function registerUser() {
            const login = document.getElementById('login').value;
            const password = document.getElementById('password').value;
            const email = document.getElementById('email').value;
            const prenom = document.getElementById('prenom').value;
            const nom = document.getElementById('nom').value;
            // const recaptchaToken = document.querySelector('textarea[name="g-recaptcha-response"]').value;
            // console.log(recaptchaToken)
                // ,  recaptcha_token: recaptchaToken
            try {
                const response = await fetch('ajax/user/register.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({login, password, email, prenom, nom})
                });

                const data = await response.json();
                console.log(data);
                const messageElem = document.getElementById('message');
                if (data.success) {
                    messageElem.style.color = 'green';
                    messageElem.textContent = data.message;

                    setTimeout(() => {
                        window.location.href = "cnx.php";
                    }, 1500);
                } else {
                    messageElem.style.color = 'red';
                    messageElem.textContent = data.error || 'Erreur inconnue';
                }
            }catch (err){
                console.log(err);
            }
        }
    </script>
    <script async src="https://www.google.com/recaptcha/api.js" defer></script>
<!--    <script src="js/ajax.js"></script>-->
<?php
    require "../src/includes/footer.inc.php";
?>