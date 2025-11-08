<?php
declare(strict_types=1);

header('Content-Type: application/json');

require_once __DIR__ . '/../../../src/config/config.php';
require_once __DIR__ . '/../../../src/backend/User.php';
require_once __DIR__ . '/../../../src/backend/Mailer.php';

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
    $recaptchaToken = $input['recaptcha_token'] ?? '';
    
    if (empty($username) || empty($email) || empty($password) || empty($recaptchaToken)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Tous les champs sont requis']);
        exit;
    }
    
    // ============================================
    // VÉRIFICATION reCAPTCHA v2
    // ============================================
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaParams = http_build_query([
        'secret' => RECAPTCHA_SECRETKEY,
        'response' => $recaptchaToken,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $recaptchaResponse = file_get_contents($recaptchaUrl . '?' . $recaptchaParams);
    $recaptchaData = json_decode($recaptchaResponse);
    
    if (!$recaptchaData->success) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Vérification reCAPTCHA échouée']);
        exit;
    }
    
    // ============================================
    // INSCRIPTION
    // ============================================
    $user = new User();
    $result = $user->register($username, $email, $password);
    
    if ($result['success']) {
        http_response_code(201);
    } else {
        http_response_code(400);
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
}
?>