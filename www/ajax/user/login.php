<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/backend/User.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données JSON invalides']);
        exit;
    }
    
    $username = $input['username'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if ((empty($username) xor empty($email)) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Certains champs sont manquant']);
        exit;
    }
    
    $identifier = !empty($username) ? $username : $email;
    
    // ============================================
    // CONNEXION
    // ============================================
    $user = new User();
    $result = $user->login($identifier, $password);
    
    if ($result['success']) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
?>
