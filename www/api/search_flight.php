<?php
declare(strict_types=1);

$allowedOrigin = 'https://whatisthisplane.alwaysdata.net';

if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header("Access-Control-Allow-Origin: $allowedOrigin");
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* Preflight CORS */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../src/config/config.php';

/**
 * Fonction principale de recherche
 */
function searchFlight($query, $searchType = 'auto') {
    $query = trim($query);
    
    // Détection automatique du type de recherche
    if ($searchType === 'auto') {
        $searchType = detectSearchType($query);
    }
    
    switch($searchType) {
        case 'flight_number':
            return searchByFlightNumber($query);
        case 'airport':
            return searchByAirport($query);
        case 'airline':
            return searchByAirline($query);
        default:
            return [
                'success' => false,
                'type' => 'unsupported',
                'error' => 'Type de recherche non supporté',
                'suggestion' => 'Essayez avec un numéro de vol (AF123), code aéroport (CDG) ou code compagnie (AF)',
                'detected_type' => $searchType
            ];
    }
}

/**
 * Détecte automatiquement le type de recherche
 */
function detectSearchType($query) {
    $query = strtoupper($query);
    
    // Numéro de vol IATA (ex: AF123, BA456)
    if (preg_match('/^[A-Z]{2}\d{1,4}[A-Z]?$/', $query)) {
        return 'flight_number';
    }
    
    // Code aéroport IATA (ex: CDG, JFK)
    if (preg_match('/^[A-Z]{3}$/', $query)) {
        return 'airport';
    }
    
    // Code compagnie IATA (ex: AF, BA)
    if (preg_match('/^[A-Z]{2}$/', $query)) {
        return 'airline';
    }
    
    return 'unknown';
}

/**
 * Recherche par numéro de vol
 */
function searchByFlightNumber($flightNumber) {
    $url = AVIATIONSTACK_BASE_URL . '/flights?' . http_build_query([
        'access_key' => AVIATIONSTACK_API_KEY,
        'flight_iata' => $flightNumber,
        'limit' => 10
    ]);
    
    $response = makeRequest($url);
    
    if (!$response || !isset($response['data']) || empty($response['data'])) {
        return ['success' => false, 'error' => 'Aucun vol trouvé avec ce numéro'];
    }
    
    return [
        'success' => true,
        'type' => 'flight_number',
        'query' => $flightNumber,
        'results' => array_map('formatFlightData', $response['data'])
    ];
}

/**
 * Recherche par aéroport
 */
function searchByAirport($airportCode) {
    // Recherche des départs
    $departuresUrl = AVIATIONSTACK_BASE_URL . '/flights?' . http_build_query([
        'access_key' => AVIATIONSTACK_API_KEY,
        'dep_iata' => $airportCode,
        'limit' => 20
    ]);
    
    $departures = makeRequest($departuresUrl);
    
    // Recherche des arrivées
    $arrivalsUrl = AVIATIONSTACK_BASE_URL . '/flights?' . http_build_query([
        'access_key' => AVIATIONSTACK_API_KEY,
        'arr_iata' => $airportCode,
        'limit' => 20
    ]);
    
    $arrivals = makeRequest($arrivalsUrl);
    
    $hasResults = (isset($departures['data']) && !empty($departures['data'])) || 
                  (isset($arrivals['data']) && !empty($arrivals['data']));
    
    if (!$hasResults) {
        return [
            'success' => false,
            'error' => 'Aucun vol trouvé pour cet aéroport',
            'airport_code' => $airportCode
        ];
    }
    
    return [
        'success' => true,
        'type' => 'airport',
        'airport_code' => $airportCode,
        'departures' => isset($departures['data']) ? array_map('formatFlightData', $departures['data']) : [],
        'arrivals' => isset($arrivals['data']) ? array_map('formatFlightData', $arrivals['data']) : []
    ];
}

/**
 * Recherche par compagnie aérienne
 */
function searchByAirline($airlineCode) {
    $url = AVIATIONSTACK_BASE_URL . '/flights?' . http_build_query([
        'access_key' => AVIATIONSTACK_API_KEY,
        'airline_iata' => $airlineCode,
        'flight_status' => 'active',
        'limit' => 30
    ]);
    
    $response = makeRequest($url);
    
    if (!$response || !isset($response['data']) || empty($response['data'])) {
        return [
            'success' => false, 
            'error' => 'Aucun vol actif trouvé pour cette compagnie',
            'airline_code' => $airlineCode
        ];
    }
    
    return [
        'success' => true,
        'type' => 'airline',
        'airline_code' => $airlineCode,
        'results' => array_map('formatFlightData', $response['data'])
    ];
}

/**
 * Formate les données d'un vol
 */
function formatFlightData($flight) {
    return [
        // Identifiants du vol
        'flight_iata' => $flight['flight']['iata'] ?? null,
        'flight_icao' => $flight['flight']['icao'] ?? null,
        'flight_number' => $flight['flight']['number'] ?? null,
        'callsign' => $flight['flight']['codeshared']['flight_iata'] ?? $flight['flight']['iata'] ?? null,
        
        // Statut
        'status' => $flight['flight_status'] ?? 'unknown',
        
        // Compagnie aérienne
        'airline' => [
            'name' => $flight['airline']['name'] ?? 'Unknown',
            'iata' => $flight['airline']['iata'] ?? null,
            'icao' => $flight['airline']['icao'] ?? null
        ],
        
        // Aéroport de départ
        'departure' => [
            'iata' => $flight['departure']['iata'] ?? null,
            'icao' => $flight['departure']['icao'] ?? null,
            'airport' => $flight['departure']['airport'] ?? null,
            'timezone' => $flight['departure']['timezone'] ?? null,
            'scheduled' => $flight['departure']['scheduled'] ?? null,
            'estimated' => $flight['departure']['estimated'] ?? null,
            'actual' => $flight['departure']['actual'] ?? null,
            'terminal' => $flight['departure']['terminal'] ?? null,
            'gate' => $flight['departure']['gate'] ?? null
        ],
        
        // Aéroport d'arrivée
        'arrival' => [
            'iata' => $flight['arrival']['iata'] ?? null,
            'icao' => $flight['arrival']['icao'] ?? null,
            'airport' => $flight['arrival']['airport'] ?? null,
            'timezone' => $flight['arrival']['timezone'] ?? null,
            'scheduled' => $flight['arrival']['scheduled'] ?? null,
            'estimated' => $flight['arrival']['estimated'] ?? null,
            'actual' => $flight['arrival']['actual'] ?? null,
            'terminal' => $flight['arrival']['terminal'] ?? null,
            'gate' => $flight['arrival']['gate'] ?? null
        ],
        
        // Avion
        'aircraft' => [
            'registration' => $flight['aircraft']['registration'] ?? null,
            'iata' => $flight['aircraft']['iata'] ?? null,
            'icao' => $flight['aircraft']['icao'] ?? null,
            'icao24' => $flight['aircraft']['icao24'] ?? null
        ],
    ];
}

function makeRequest($url) {
    $response = @file_get_contents($url);

    if ($response === false) {
        error_log("Erreur : impossible d'appeler l'API ($url)");
        return null;
    }

    $data = json_decode($response, true);

    if ($data === null) {
        error_log("Erreur : JSON invalide depuis l'API.");
        return null;
    }

    return $data;
}

// Point d'entrée de l'API
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = $_GET['query'] ?? '';
    $type = $_GET['type'] ?? 'auto';
    
    if (empty($query)) {
        echo json_encode([
            'success' => false, 
            'error' => 'Paramètre query manquant',
            'usage' => 'Utilisez ?query=AF123 ou ?query=CDG'
        ]);
        exit;
    }
    
    $result = searchFlight($query, $type);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>