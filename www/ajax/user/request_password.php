<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/backend/User.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }
    
    if (!isset($_GET['email']) || trim($_GET['email']) === '') {
		http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Email invalide']);
        exit;
	}
	
    $email = $_GET['email'];
    
    $user = new User();
    $result = $user->requestPasswordReset($email);
    
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
