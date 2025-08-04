<?php
header("Content-Type: application/json; charset=UTF-8");

// Valider et nettoyer le paramètre 'callback'
if (isset($_GET["callback"])) {
    $callback = $_GET["callback"];

    // Autoriser uniquement les noms de fonctions JavaScript valides (lettres, chiffres, underscore)
    if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $callback)) {
        $outp = array("answer" => "15");
        echo $callback . "(" . json_encode($outp) . ")";
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Nom de fonction callback invalide."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Paramètre callback manquant."]);
}