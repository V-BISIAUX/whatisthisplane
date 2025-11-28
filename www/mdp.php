<?php
    declare(strict_types=1);
    $title = "Mot de passe oublié - WhatIsThisPlane";
    require "../src/includes/header.inc.php";
?>

<main>
    <h1>Mot de passe oublié - WhatIsThisPlane</h1>
    <form method="POST" action="" id="forgot-form">
        <label for="mdp-oublie">J'ai oublié mon mot de passe</label>
        <input type="email" name="email" id="mdp-oublie" placeholder="Entrez l'e-mail liée à votre compte" required="required"/>
        <span id="debug"></span>
        <input type="submit" value="Envoyer"/>
        <span id="message"></span>
    </form>
</main>

    <script>
        document.getElementById('forgot-form').addEventListener('submit', async (event) => {
            event.preventDefault();
            const emailValue = document.getElementById("mdp-oublie").value;
            const messageElem = document.getElementById('message');

            try{
                const response = await fetch(`api/user/reset_password.php?email=` + encodeURIComponent(emailValue));
                const data = await response.json();
                if (data.success) {
                    messageElem.style.color = 'green';
                    messageElem.textContent = data.message;
                } else {
                    messageElem.style.color = 'red';
                    messageElem.textContent = data.error || 'Erreur lors du changement du mot de passe';
                }
            }catch (err){
                console.log(err);
            }
        });
    </script>
<?php
    require "../src/includes/footer.inc.php";
?>