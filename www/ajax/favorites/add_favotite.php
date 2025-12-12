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
    $originIata = isset($data['origin_iata']) ? trim($data['origin_iata']) : null;
    $originName = isset($data['origin_name']) ? trim($data['origin_name']) : null;
    $destIata = isset($data['destination_iata']) ? trim($data['destination_iata']) : null;
    $destName = isset($data['destination_name']) ? trim($data['destination_name']) : null;
    
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
    $pdo->beginTransaction();
    
    try {
        // 1. Vérifier/Ajouter l'avion
        $stmt = $pdo->prepare("SELECT airplane_id FROM airplanes WHERE icao24 = ? LIMIT 1");
        $stmt->execute([$icao24]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $airplaneId = (int)$existing['airplane_id'];
            // Mise à jour des informations
            $stmt = $pdo->prepare("
                UPDATE airplanes 
                SET callsign = ?, 
                    aircraft_model = ?, 
                    airline = ?,
                    last_origin_iata = ?, 
                    last_origin_name = ?,
                    last_destination_iata = ?, 
                    last_destination_name = ?,
                    last_seen = CURRENT_TIMESTAMP
                WHERE airplane_id = ?
            ");
            $stmt->execute([
                $callsign, $model, $airline, 
                $originIata, $originName, 
                $destIata, $destName, 
                $airplaneId
            ]);
        } else {
            // Insertion
            $stmt = $pdo->prepare("
                INSERT INTO airplanes 
                (icao24, callsign, aircraft_model, airline,
                 last_origin_iata, last_origin_name,
                 last_destination_iata, last_destination_name) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $icao24, $callsign, $model, $airline, 
                $originIata, $originName, 
                $destIata, $destName
            ]);
            $airplaneId = (int)$pdo->lastInsertId();
        }
        
        // 2. Vérifier si déjà dans les favoris
        $stmt = $pdo->prepare("
            SELECT 1 FROM favorites 
            WHERE user_id = ? AND airplane_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$userId, $airplaneId]);
        
        if ($stmt->fetch()) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false, 
                'error' => 'Cet avion est déjà dans vos favoris'
            ]);
            exit;
        }
        
        // 3. Ajouter aux favoris
        $stmt = $pdo->prepare("
            INSERT INTO favorites 
            (user_id, airplane_id, saved_origin_iata, saved_origin_name,
             saved_destination_iata, saved_destination_name)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, $airplaneId, 
            $originIata, $originName, 
            $destIata, $destName
        ]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Avion ajouté aux favoris',
            'airplane_id' => $airplaneId
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur base de données'
    ]);
    error_log('DB Error in add_favorite.php: ' . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur serveur'
    ]);
    error_log('Error in add_favorite.php: ' . $e->getMessage());
}
?>