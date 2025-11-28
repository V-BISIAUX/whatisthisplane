<?php
declare(strict_types=1);
session_start();
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
    
    $oldPassword = $input['oldPassword'] ?? '';
    $newPassword = $input['newPassword'] ?? '';


    if (empty($newPassword) || empty($oldPassword)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Tous les champs sont requis']);
        exit;
    }
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    $user = new User();
    $result = $user->changePassword($user_id, $oldPassword, $newPassword);
    
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
