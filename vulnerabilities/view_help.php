<?php

declare(strict_types=1);

define('DVWA_WEB_PAGE_TO_ROOT', '../');

// Importer les fonctions/classes via un namespace
use DVWA\Core\Page; // Exemple : adapter au namespace réel
use DVWA\Core\Security;

// Inclusion sécurisée
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

// Démarrage sécurisé
Page\dvwaPageStartup(['authenticated']);

// Initialisation de la page
$page = Page\dvwaPageNewGrab();
$page['title'] = 'Help' . $page['title_separator'] . $page['title'];

// Liste blanche des IDs et locales autorisés
$allowed_ids = ['sqli', 'xss', 'csrf']; // Adapter à ton application
$allowed_locales = ['en', 'fr', 'es'];

if (
    isset($_GET['id'], $_GET['security'], $_GET['locale'])
) {
    $id = $_GET['id'];
    $locale = $_GET['locale'];

    // Validation des valeurs
    if (in_array($id, $allowed_ids, true) && in_array($locale, $allowed_locales, true)) {
        $helpFile = DVWA_WEB_PAGE_TO_ROOT . "vulnerabilities/{$id}/help/help";
        $helpFile .= ($locale === 'en') ? ".php" : ".{$locale}.php";

        if (is_readable($helpFile)) {
            ob_start();
            include_once  $helpFile; // pas de eval()
            $help = ob_get_clean();
        } else {
            $help = "<p>Help file not found.</p>";
        }
    } else {
        $help = "<p>Invalid parameters.</p>";
    }
} else {
    $help = "<p>Not Found</p>";
}

// Corps HTML
$page['body'] .= "
<script src='/vulnerabilities/help.js'></script>
<link rel='stylesheet' type='text/css' href='/vulnerabilities/help.css' />

<div class=\"body_padded\">
    {$help}
</div>\n";

Page\dvwaHelpHtmlEcho($page);


