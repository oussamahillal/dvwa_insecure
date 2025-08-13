<?php

if (isset($_REQUEST['Submit'])) {
    // Récupération sécurisée de l'input
    $id = $_REQUEST['id'];
    $html = '';

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            // Utilisation de requêtes préparées MySQLi
            $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], "SELECT first_name, last_name FROM users WHERE user_id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $id); // 'i' = integer
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                while ($row = mysqli_fetch_assoc($result)) {
                    $first = htmlspecialchars($row["first_name"]);
                    $last  = htmlspecialchars($row["last_name"]);
                    $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }

                mysqli_stmt_close($stmt);
            }
            mysqli_close($GLOBALS["___mysqli_ston"]);
            break;

        case SQLITE:
            global $sqlite_db_connection;
            $stmt = $sqlite_db_connection->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id");
            if ($stmt) {
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $results = $stmt->execute();

                while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
                    $first = htmlspecialchars($row["first_name"]);
                    $last  = htmlspecialchars($row["last_name"]);
                    $html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
                }

                $stmt->close();
            }
            break;

        default:
            // Gestion du type de base de données inconnu
            $html .= "<pre>Erreur : Type de base de données non reconnu.</pre>";
            break;
    }
}

?>
