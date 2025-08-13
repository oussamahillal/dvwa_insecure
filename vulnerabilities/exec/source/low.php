<?php
if (isset($_POST['Submit'])) {
    // Récupérer l'input utilisateur
    $target = $_REQUEST['ip'];

    // Validation stricte : IPv4 ou IPv6
    if (filter_var($target, FILTER_VALIDATE_IP)) {

        // Déterminer l'OS
        $isWindows = stristr(php_uname('s'), 'Windows NT') !== false;

        // Construction sécurisée du ping
        $cmdArgs = $isWindows ? ['ping', '-n', '4', $target] : ['ping', '-c', '4', $target];

        // Exécution sécurisée avec escapeshellarg pour chaque argument
        $escapedCmd = implode(' ', array_map('escapeshellarg', $cmdArgs));
        $output = shell_exec($escapedCmd);

        // Affichage en toute sécurité
        $html .= "<pre>" . htmlspecialchars($output) . "</pre>";
    } else {
        $html .= "<pre>Adresse IP invalide.</pre>";
    }
}

?>
