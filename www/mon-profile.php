<?php
    declare(strict_types=1);
    session_start();
    if (!isset($_SESSION['login'])) {
        header('Location: index.php');
        exit;
    }

    $title = "Mon Profil - WhatIsThisPlane";
    require "../src/includes/header.inc.php";
?>

    <main>
        <h1>Mon profil - WhatIsThisPlane</h1>

        <section>
            <h2>Informations personnelles</h2>
            <div id="profile-container"></div>
        </section>

        <section>
            <h2>Sécurité : changer le mot de passe</h2>
            <form method="post" id="mdpform">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" autocomplete="off" required="required"/>

                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" autocomplete="off" required="required"/>

<!--                <label>Confirmer</label>-->
<!--                <input type="password" id="confirm_password">-->

                <button type="submit" id="change-password-btn">Mettre à jour le mot de passe</button>
                <span id="message"></span>
            </form>
        </section>

        <section>
            <h2>Supprimer mon compte</h2>
            <button id="delete-account" class="danger">Supprimer définitivement</button>
        </section>
    </main>

    <script>
        async function loadProfile() {
            try {
                const response = await fetch("ajax/user/profile.php");
                const data = await response.json();

                const container = document.getElementById("profile-container");

                if (data.success) {
                    container.innerHTML = `
                        <p><strong>Username :</strong> ${data.user.username}</p>
                        <p><strong>Email :</strong> ${data.user.email}</p>
                        <p><strong>Date d'inscription :</strong> ${data.user.created_at}</p>
                        <p><strong>Nombre d'avions favoris :</strong> ${data.user.nb_favorites}</p>
                    `;
                } else {
                    container.innerHTML = "<p style='color:red;'>Erreur : " + data.error + "</p>";
                }
            } catch (err) {
                console.error(err);
                document.getElementById("profile-container").innerHTML =
                    "<p style='color:red;'>Erreur serveur</p>";
            }
        }

        loadProfile();

        document.getElementById('mdpform').addEventListener('submit', function(event){
            event.preventDefault();
            changePassword();
        });
        async function changePassword() {
            const oldPassword = document.getElementById("current_password").value;
            const newPassword = document.getElementById("new_password").value;

            try{
                const response = await fetch("ajax/user/change_password.php", {
                    method:"POST",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body:JSON.stringify({
                        oldPassword: oldPassword,
                        newPassword: newPassword,
                    })
                });

                const data = await response.json();
                const messageElem = document.getElementById('message');
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
        }
    </script>
<?php
    require "../src/includes/footer.inc.php";
?>