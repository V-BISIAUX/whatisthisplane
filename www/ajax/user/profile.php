<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/backend/User.php';

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté']);
        exit;
    }
	
    $user_id = $_SESSION['user_id'];
    
    // ============================================
    // RÉCUPÉRATION DU PROFILE
    // ============================================
    $user = new User();
    $result = $user->getProfile($user_id);
    
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
