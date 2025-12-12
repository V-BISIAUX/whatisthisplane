<?php
declare(strict_types=1);

$allowedOrigin = 'https://whatisthisplane.alwaysdata.net';

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header("Access-Control-Allow-Origin: $allowedOrigin");
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* Preflight CORS */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

if (!isset($_SESSION['user_id']) || (int)$_SESSION['user_id'] <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Utilisateur non authentifié']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

require_once __DIR__ . '/../../../src//backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Récupérer et valider les données
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'JSON invalide']);
        exit;
    }
    
    
    $icao24 = trim($data['icao24'] ?? '');
    
    // Validation
    if ($userId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non authentifié']);
        exit;
    }
    
    if (empty($icao24)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ICAO24 requis']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Trouver l'airplane_id
    $stmt = $pdo->prepare("SELECT airplane_id FROM airplanes WHERE icao24 = ? LIMIT 1");
    $stmt->execute([$icao24]);
    $airplane = $stmt->fetch();
    
    if (!$airplane) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Avion non trouvé']);
        exit;
    }
    
    $airplaneId = (int)$airplane['airplane_id'];
    
    // Vérifier que le favori existe
    $stmt = $pdo->prepare("
        SELECT 1 FROM favorites 
        WHERE user_id = ? AND airplane_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$userId, $airplaneId]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Favori non trouvé']);
        exit;
    }
    
    // Supprimer des favoris
    $stmt = $pdo->prepare("
        DELETE FROM favorites 
        WHERE user_id = ? AND airplane_id = ?
    ");
    $stmt->execute([$userId, $airplaneId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Avion retiré des favoris'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Erreur lors de la suppression'
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur base de données'
    ]);
    error_log('DB Error in remove_favorite.php: ' . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur serveur'
    ]);
    error_log('Error in remove_favorite.php: ' . $e->getMessage());
}
?>