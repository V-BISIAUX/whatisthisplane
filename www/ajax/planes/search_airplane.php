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

require_once __DIR__ . '/../../../src//backend/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    // Paramètres de recherche
    $icao24 = trim($_GET['icao24'] ?? '');
    $callsign = trim($_GET['callsign'] ?? '');
    
    // Au moins un critère de recherche
    if (empty($icao24) && empty($callsign)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ICAO24 ou callsign requis']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Construction de la requête selon les critères
    if (!empty($icao24)) {
        $stmt = $pdo->prepare("
            SELECT 
                airplane_id,
                icao24,
                callsign,
                aircraft_model,
                airline,
                last_origin_iata,
                last_origin_name,
                last_destination_iata,
                last_destination_name,
                first_seen,
                last_seen
            FROM airplanes 
            WHERE icao24 = ?
            LIMIT 1
        ");
        $stmt->execute([$icao24]);
    } else {
        // Recherche par callsign (peut retourner plusieurs résultats)
        $stmt = $pdo->prepare("
            SELECT 
                airplane_id,
                icao24,
                callsign,
                aircraft_model,
                airline,
                last_origin_iata,
                last_origin_name,
                last_destination_iata,
                last_destination_name,
                first_seen,
                last_seen
            FROM airplanes 
            WHERE callsign LIKE ?
            ORDER BY last_seen DESC
            LIMIT 10
        ");
        $stmt->execute(['%' . $callsign . '%']);
    }
    
    $results = $stmt->fetchAll();
    
    if (empty($results)) {
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'error' => 'Aucun avion trouvé'
        ]);
        exit;
    }
    
    // Formater les résultats
    $formatted = array_map(function($airplane) {
        return [
            'airplane_id' => (int)$airplane['airplane_id'],
            'icao24' => $airplane['icao24'],
            'callsign' => $airplane['callsign'] ?? 'N/A',
            'aircraft_model' => $airplane['aircraft_model'] ?? 'Inconnu',
            'airline' => $airplane['airline'] ?? 'Inconnu',
            'last_flight' => [
                'origin_iata' => $airplane['last_origin_iata'],
                'origin_name' => $airplane['last_origin_name'],
                'destination_iata' => $airplane['last_destination_iata'],
                'destination_name' => $airplane['last_destination_name']
            ],
            'first_seen' => $airplane['first_seen'],
            'last_seen' => $airplane['last_seen']
        ];
    }, $results);
    
    // Si recherche par ICAO24 (unique), retourner un seul objet
    if (!empty($icao24)) {
        echo json_encode([
            'success' => true,
            'airplane' => $formatted[0]
        ]);
    } else {
        // Si recherche par callsign, retourner un tableau
        echo json_encode([
            'success' => true,
            'count' => count($formatted),
            'airplanes' => $formatted
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur base de données'
    ]);
    error_log('DB Error in search_airplane.php: ' . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur serveur'
    ]);
    error_log('Error in search_airplane.php: ' . $e->getMessage());
}
?>