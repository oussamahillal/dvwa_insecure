<?php

if (array_key_exists("redirect", $_GET) && $_GET['redirect'] !== "") {
    // Liste blanche (whitelist) des chemins ou pages autorisés
    $allowed_redirects = [
        'home'   => '/index.php',
        'login'  => '/login.php',
        'profile'=> '/profile.php'
    ];

    $requested_redirect = $_GET['redirect'];

    if (array_key_exists($requested_redirect, $allowed_redirects)) {
        // Redirection uniquement si la destination est dans la liste blanche
        header("Location: " . $allowed_redirects[$requested_redirect]);
        exit;
    } else {
        http_response_code(400);
        echo "<p>Invalid redirect target.</p>";
        exit;
    }
}

http_response_code(500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
