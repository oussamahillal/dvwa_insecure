<?php
// Charger la configuration depuis un fichier externe non accessible publiquement
require_once __DIR__ . '/config.php'; // Ce fichier contient $host, $username, $password, $database

// Connexion sécurisée à MSSQL
$conn = mssql_connect($host, $username, $password);
if (!$conn) {
    die("Connection failed: " . mssql_get_last_message());
}

mssql_select_db($database, $conn);

// Exécution de la requête
$query = "SELECT first_name, password FROM users";
$result = mssql_query($query);

while ($record = mssql_fetch_array($result)) {
    echo htmlspecialchars($record["first_name"]) . ", " . htmlspecialchars($record["password"]) . "<br />";
}

// Fermeture de la connexion
mssql_close($conn);
?>
