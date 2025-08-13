<?php

if (isset($_GET['Submit'])) {
    // Get input et nettoyage basique
    $id = $_GET['id'];
    $exists = false;

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            // Connexion MySQLi (exemple : déjà ouverte dans DVWA)
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id); // 'i' pour integer
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $exists = ($result && mysqli_num_rows($result) > 0);
                mysqli_stmt_close($stmt);
            }
            ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
            break;

        case SQLITE:
            global $sqlite_db_connection;
            $stmt = $sqlite_db_connection->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id");
            if ($stmt) {
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $results = $stmt->execute();
                $row = $results->fetchArray();
                $exists = $row !== false;
                $stmt->close();
            }
            break;

        default:
            // Cas non prévu : renvoyer une erreur propre
            $html .= '<pre>Base de données non reconnue. Vérifiez la configuration.</pre>';
            break;
    }

    if ($exists) {
        $html .= '<pre>User ID exists in the database.</pre>';
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        $html .= '<pre>User ID is MISSING from the database.</pre>';
    }
}

?>
