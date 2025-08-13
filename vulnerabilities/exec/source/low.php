<?php

if (isset($_POST['Submit'])) {
    // Récupérer l'input utilisateur
    $target = $_REQUEST['ip'];

    // Filtrage strict : accepter uniquement une adresse IPv4 ou IPv6
    if (filter_var($target, FILTER_VALIDATE_IP)) {

        // Déterminer l'OS et exécuter la commande ping avec arguments protégés
        if (stristr(php_uname('s'), 'Windows NT')) {
            // Windows : escapeshellarg pour éviter l'injection
            $cmd = shell_exec('ping ' . escapeshellarg($target));
        } else {
            // Linux / macOS
            $cmd = shell_exec('ping -c 4 ' . escapeshellarg($target));
        }

        // Affichage du résultat
        $html .= "<pre>" . htmlspecialchars($cmd) . "</pre>";
    } else {
        $html .= "<pre>Adresse IP invalide.</pre>";
    }
}

?>
