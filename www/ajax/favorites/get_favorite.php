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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

try {
    
    // Validation
    if ($userId <= 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Utilisateur non authentifié']);
        exit;
    }
    
    $pdo = getConnection();
    
    // Récupérer tous les favoris avec les informations complètes
    $stmt = $pdo->prepare("
        SELECT 
            a.icao24,
            a.callsign,
            a.aircraft_model,
            a.airline,
            a.last_origin_iata,
            a.last_origin_name,
            a.last_destination_iata,
            a.last_destination_name,
            a.first_seen,
            a.last_seen,
            f.saved_origin_iata,
            f.saved_origin_name,
            f.saved_destination_iata,
            f.saved_destination_name,
            f.added_at
        FROM favorites f
        INNER JOIN airplanes a ON f.airplane_id = a.airplane_id
        WHERE f.user_id = ?
        ORDER BY f.added_at DESC
    ");
    
    $stmt->execute([$userId]);
    $favorites = $stmt->fetchAll();
    
    // Formater les résultats
    $formatted = array_map(function($fav) {
        return [
            'icao24' => $fav['icao24'],
            'callsign' => $fav['callsign'] ?? 'N/A',
            'aircraft_model' => $fav['aircraft_model'] ?? 'Inconnu',
            'airline' => $fav['airline'] ?? 'Inconnu',
            // Informations du dernier vol observé
            'last_flight' => [
                'origin_iata' => $fav['last_origin_iata'],
                'origin_name' => $fav['last_origin_name'],
                'destination_iata' => $fav['last_destination_iata'],
                'destination_name' => $fav['last_destination_name']
            ],
            // Informations du vol lors de l'ajout en favori
            'saved_flight' => [
                'origin_iata' => $fav['saved_origin_iata'],
                'origin_name' => $fav['saved_origin_name'],
                'destination_iata' => $fav['saved_destination_iata'],
                'destination_name' => $fav['saved_destination_name']
            ],
            'first_seen' => $fav['first_seen'],
            'last_seen' => $fav['last_seen'],
            'added_at' => $fav['added_at']
        ];
    }, $favorites);
    
    echo json_encode([
        'success' => true,
        'count' => count($formatted),
        'favorites' => $formatted
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur base de données'
    ]);
    error_log('DB Error in get_favorites.php: ' . $e->getMessage());
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Erreur serveur'
    ]);
    error_log('Error in get_favorites.php: ' . $e->getMessage());
}
?>