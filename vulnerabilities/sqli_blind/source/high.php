<?php

if (isset($_COOKIE['id'])) {
    $id = $_COOKIE['id'];
    $exists = false;

    if ($_DVWA['SQLI_DB'] === MYSQL) {
        // Utilisation de requête préparée avec mysqli
        $conn = $GLOBALS["___mysqli_ston"];
        $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1");

        if ($stmt) {
            $stmt->bind_param("s", $id); // 's' = string
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();
        }

        ((is_null($___mysqli_res = mysqli_close($conn))) ? false : $___mysqli_res);

    } elseif ($_DVWA['SQLI_DB'] === SQLITE) {
        global $sqlite_db_connection;

        try {
            $stmt = $sqlite_db_connection->prepare("SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, SQLITE3_TEXT);
            $result = $stmt->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            $exists = $row !== false;
        } catch (Exception $e) {
            $exists = false;
        }
    }

    if ($exists) {
        $html .= '<pre>User ID exists in the database.</pre>';
    } else {
        if (rand(0, 5) == 3) {
            sleep(rand(2, 4));
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        $html .= '<pre>User ID is MISSING from the database.</pre>';
    }
}

?>
