<?php
    declare(strict_types=1);
    $title = "Activation de votre compte - WhatIsThisPlane";
    require "../src/includes/header.inc.php";
    $token = $_GET['token'] ?? null;
    $success = null;
    $message = null;

    if ($token) {

        // Appel de ton API JSON
        $apiUrl = URL . "/ajax/user/verify_email.php?token=" . urlencode($token);

        $json = file_get_contents($apiUrl);
        $response = json_decode($json, true);

        if ($response) {
            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($response['error'] ?? "Erreur inconnue");
        } else {
            $success = false;
            $message = "Impossible de contacter le serveur.";
        }
    }
?>
<main>
    <h1>Activation du compte</h1>

    <?php if ($message): ?>
        <?php if ($success): ?>
            <p style="color: green;"><?= htmlspecialchars($message) ?></p>
        <?php else: ?>
            <p style="color: red;"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: orange;">Aucun token fourni.</p>
    <?php endif; ?>
</main>
<?php
    require "../src/includes/footer.inc.php";
?>
