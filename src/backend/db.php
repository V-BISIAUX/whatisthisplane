<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

/**
 * Récupère une connexion PDO à la base de données
 * @return PDO
 * @throws Exception Si la connexion échoue
 */
function getConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				// Indique à PDO de lancer une **exception** (PDOException) en cas d'erreur SQL
				// au lieu de juste retourner false. Cela facilite la gestion des erreurs et le débogage.

				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				// Définit le mode par défaut pour récupérer les résultats des requêtes SQL.
				// Ici, chaque ligne renvoyée sera un **tableau associatif**
				// où les clés correspondent aux noms des colonnes.

				PDO::ATTR_EMULATE_PREPARES => false,
				// Désactive l'émulation des requêtes préparées par PDO.
				// Avec false, PDO utilise les vraies requêtes préparées de MySQL,
				// ce qui renforce la **sécurité contre les injections SQL**.
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>