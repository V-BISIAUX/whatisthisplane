<?php
require_once __DIR__ . '/../src/includes/db_connect.php';

echo "<h1>Test Connexion MySQL</h1>";
echo "<p>Base de données : <strong>" . DB_NAME . "</strong></p>";

// Affichage des données utilisateurs
$query = "SELECT * FROM users";

if ($result = $mysqli->query($query)) {
    echo "<h2>Utilisateurs</h2>";
    while ($row = $result->fetch_assoc()) {
        echo "User ID: " . htmlspecialchars($row['user_id']) . "<br>";
        echo "Username: " . htmlspecialchars($row['username']) . "<br>";
        echo "Email: " . htmlspecialchars($row['email']) . "<br>";
        echo "Email verified: " . ($row['email_verified'] ? 'Oui' : 'Non') . "<br>";
        echo "Créé le: " . htmlspecialchars($row['created_at']) . "<br>";
        echo "<hr>";
    }
    $result->free();
} else {
    echo "Erreur requête : " . $mysqli->error;
}

// Nouvelle requête pour récupérer la liste des tables
echo "<h2>Tables créées :</h2><ul>";
if ($result = $mysqli->query("SHOW TABLES")) {
    while ($row = $result->fetch_array()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    $result->free();
} else {
    echo "<li>Erreur lors de la récupération des tables : " . htmlspecialchars($mysqli->error) . "</li>";
}
echo "</ul>";

$mysqli->close();

echo "<p><strong>Connexion fonctionnelle !</strong></p>";