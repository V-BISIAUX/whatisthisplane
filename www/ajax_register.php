<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/backend/User.php';
require_once __DIR__ . '/../src/backend/Mailer.php';

header('Content-Type: application/json');

try {
    // Récupère les données JSON en POST
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    $user = new User();
    $result = $user->register($username, $email, $password);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}

