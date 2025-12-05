<?php
    declare(strict_types=1);
    require_once __DIR__ . '/../src/config/config.php';
    require_once '../src/backend/User.php';
    $title = "Activation de votre compte - WhatIsThisPlane";
    require "../src/includes/header.inc.php";
    $token = $_GET['token'] ?? null;
    $success = null;
    $message = null;

    if ($token) {
        $user = new User();
        $result = $user->verifyEmail($token);

        if ($result['success']) {
            $success = true;
            $message = $result['message'];
        } else {
            $success = false;
            $message = $result['error'];
        }
    } else {
        $message = "Lien d'activation manquant ou invalide.";
    }
?>
<main>
    <h1>Activation du compte</h1>

    <?php if ($message): ?>
        <?php if ($success): ?>
            <p style="color: green; font-weight: bold; font-size: 1.2em;">
                <?= htmlspecialchars($message) ?>
            </p>
            <p>
                <a href="cnx.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    Se connecter
                </a>
            </p>
        <?php else: ?>
            <p style="color: red; font-weight: bold; font-size: 1.2em;">
                <?= htmlspecialchars($message) ?>
            </p>
            <p>
                <a href="index.php">Retour à l'accueil</a>
            </p>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: orange;">Aucun token fourni.</p>
    <?php endif; ?>
</main>
<?php
    require "../src/includes/footer.inc.php";
?>
