<?php

if (isset($_GET['Login'])) {
    // Récupération sécurisée des données utilisateur
    $user = $_GET['username'];
    $pass = $_GET['password'];
    $pass = md5($pass); // ⚠ En vrai, préférer password_hash / password_verify

    // Requête préparée pour éviter l'injection SQL
    $stmt = mysqli_prepare(
        $GLOBALS["___mysqli_ston"],
        "SELECT * FROM `users` WHERE user = ? AND password = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ss', $user, $pass);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $row    = mysqli_fetch_assoc($result);
            $avatar = htmlspecialchars($row["avatar"]);
            $userSafe = htmlspecialchars($user);

            // Login réussi
            $html .= "<p>Welcome to the password protected area {$userSafe}</p>";
            $html .= "<img src=\"{$avatar}\" alt=\"User Avatar\" />";
        } else {
            // Login échoué
            $html .= "<pre><br />Username and/or password incorrect.</pre>";
        }

        mysqli_stmt_close($stmt);
    } else {
        $html .= "<pre>Database query error.</pre>";
    }

    ((is_null($___mysqli_res = mysqli_close($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
}

?>