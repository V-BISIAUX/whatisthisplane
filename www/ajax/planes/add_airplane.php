<?php
declare(strict_types=1);
session_start();
$allowedOrigin = 'https://whatisthisplane.alwaysdata.net';

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header("Access-Control-Allow-Origin: $allowedOrigin");
	header('Access-Control-Allow-Credentials: true');
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* Preflight CORS */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../../src//backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    $callsign = isset($data['callsign']) ? trim($data['callsign']) : null;
    $model = isset($data['aircraft_model']) ? trim($data['aircraft_model']) : null;
    $airline = isset($data['airline']) ? trim($data['airline']) : null;
	$viewSource = trim($data['view_source'] ?? 'map');
    
    // Validation
    if (empty($icao24)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ICAO24 requis']);
        exit;
    }
    
    if (strlen($icao24) !== 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ICAO24 doit contenir 6 caractères']);
        exit;
    }
	
	if (!in_array($viewSource, ['map', 'search'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'view_source doit être "map" ou "search"']);
        exit;
    }
	
	$userId = null;
	if (isset($_SESSION['user_id'])) {
		$userId = (int)$_SESSION['user_id'];
	}
    
    $pdo = getConnection();
    
    // Vérifier si l'avion existe
    $stmt = $pdo->prepare("SELECT airplane_id FROM airplanes WHERE icao24 = ? LIMIT 1");
    $stmt->execute([$icao24]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Mise à jour
        $stmt = $pdo->prepare("
            UPDATE airplanes 
            SET callsign = ?, 
                aircraft_model = ?, 
                airline = ?, 
                last_seen = CURRENT_TIMESTAMP
            WHERE icao24 = ?
        ");
        $stmt->execute([$callsign, $model, $airline, $icao24]);
        $airplaneId = (int)$existing['airplane_id'];
    } else {
        // Insertion
        $stmt = $pdo->prepare("
            INSERT INTO airplanes (icao24, callsign, aircraft_model, airline) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$icao24, $callsign, $model, $airline]);
        $airplaneId = (int)$pdo->lastInsertId();
    }
	
	$stmt = $pdo->prepare("
        INSERT INTO airplane_seen (user_id, airplane_id, view_source) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$userId, $airplaneId, $viewSource]);
    
    echo json_encode([
        'success' => true,
        'message' => $existing ? 'Avion mis à jour' : 'Avion ajouté',
        'airplane_id' => $airplaneId,
        'tracked' => true
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur base de données'
    ]);
    error_log('DB Error in add_airplane.php: ' . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur serveur'
    ]);
    error_log('Error in add_airplane.php: ' . $e->getMessage());
}
?>