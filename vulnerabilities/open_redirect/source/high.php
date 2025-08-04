<?php

// Liste blanche des chemins autorisés (whitelist)
$allowed_redirects = [
    'info.php'
];

if (!empty($_GET['redirect'])) {
    $target = basename($_GET['redirect']); // On extrait uniquement le nom de fichier (ex: info.php)

    if (in_array($target, $allowed_redirects, true)) {
        header("Location: " . $target);
        exit;
    } else {
        http_response_code(403);
        echo "<p>Redirection refusée : cible non autorisée.</p>";
        exit;
    }
}

http_response_code(400);
echo "<p>Paramètre de redirection manquant.</p>";
exit;
?>
