<?php
    declare(strict_types=1);

    require_once __DIR__ . '/../src/config/config.php';
    require_once __DIR__ . '/../src/backend/User.php';
    $title = "Changer mon mot de passe - WhatIsThisPlane";
    require "../src/includes/header.inc.php";
    $message = "";
    $success = false;
    $token = $_GET['token'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!empty($token) && !empty($_POST['password']) && !empty($_POST['confirm_password'])) {

            if ($_POST['password'] !== $_POST['confirm_password']) {
                $message = "Les mots de passe ne correspondent pas.";
            } else {
                $user = new User();
                $result = $user->resetPassword($token, $_POST['password']);

                if ($result['success']) {
                    $success = true;
                    $message = "Votre mot de passe a été modifié avec succès !";
                } else {
                    $message = $result['error'];
                }
            }
        } else {
            $message = "Veuillez remplir tous les champs.";
        }
    }
?>

<main>
    <?php if ($success): ?>
        <div style="color: green; margin-bottom: 20px;">
            <?= htmlspecialchars($message) ?>
        </div>
        <a href="cnx.php">
            Se connecter
        </a>

    <?php else: ?>

        <?php if ($message): ?>
            <div style="color: red;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($token)): ?>
            <p style="color: red;">Token manquant ou invalide.</p>
        <?php else: ?>

            <form method="POST" action="">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <label for="password">Nouveau mot de passe</label>
                <input type="password" name="password" id="password" required="required"/>

                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" id="confirm_password" required="required"/>

                <input type="submit" value="Envoyer"/>
            </form>

        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
    require "../src/includes/footer.inc.php";
?>