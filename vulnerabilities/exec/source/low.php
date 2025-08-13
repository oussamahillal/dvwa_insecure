<?php
if (isset($_POST['Submit'])) {
    $target = $_POST['ip'];

    // Validation stricte de l'adresse IP
    if (filter_var($target, FILTER_VALIDATE_IP)) {

        // Nombre de tentatives
        $count = 4;
        $timeout = 1; // secondes

        $results = [];

        // Vérification de connectivité sans shell
        for ($i = 0; $i < $count; $i++) {
            $start = microtime(true);
            $fp = @fsockopen($target, 80, $errno, $errstr, $timeout);
            $end = microtime(true);

            if ($fp) {
                fclose($fp);
                $results[] = "Ping " . ($i + 1) . ": Réussi en " . round(($end - $start) * 1000) . " ms";
            } else {
                $results[] = "Ping " . ($i + 1) . ": Échec";
            }
        }

        $html = "<pre>" . htmlspecialchars(implode("\n", $results)) . "</pre>";

    } else {
        $html = "<pre>Adresse IP invalide.</pre>";
    }
}

?>
