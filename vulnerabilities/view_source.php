<?php
declare(strict_types=1);

define('DVWA_WEB_PAGE_TO_ROOT', '../');

// Import par namespace (adapter au vrai namespace)
use DVWA\Core\Page;
use DVWA\Core\View;

// Initialisation via namespace
Page\dvwaPageStartup(['authenticated']);

$page = Page\dvwaPageNewGrab();
$page['title'] .= 'Source' . $page['title_separator'] . $page['title'];

// Liste blanche des vulnérabilités et niveaux de sécurité autorisés
$allowedIds = [
    "fi", "brute", "csrf", "exec", "sqli", "sqli_blind",
    "upload", "xss_r", "xss_s", "weak_id", "javascript",
    "authbypass", "open_redirect"
];

$allowedSecurityLevels = ["low", "medium", "high", "impossible"];

// Définir les chemins sûrs par combinaison vulnérabilité / niveau
$allowedPaths = [];
foreach ($allowedIds as $id) {
    foreach ($allowedSecurityLevels as $level) {
        $allowedPaths[$id][$level] = [
            'php' => realpath(__DIR__ . "/../vulnerabilities/{$id}/source/{$level}.php"),
            'js'  => realpath(__DIR__ . "/../vulnerabilities/{$id}/source/{$level}.js")
        ];
    }
}

if (isset($_GET['id'], $_GET['security'])) {
    $id = $_GET['id'];
    $security = $_GET['security'];

    // Vérification whitelist
    if (!isset($allowedPaths[$id][$security]) || $allowedPaths[$id][$security]['php'] === false) {
        $page['body'] = "<p>Invalid parameters or source missing.</p>";
        View\dvwaSourceHtmlEcho($page);
        exit;
    }

    // Mapping vulnérabilité -> nom
    $vulnNames = [
        "fi"           => 'File Inclusion',
        "brute"        => 'Brute Force',
        "csrf"         => 'CSRF',
        "exec"         => 'Command Injection',
        "sqli"         => 'SQL Injection',
        "sqli_blind"   => 'SQL Injection (Blind)',
        "upload"       => 'File Upload',
        "xss_r"        => 'Reflected XSS',
        "xss_s"        => 'Stored XSS',
        "weak_id"      => 'Weak Session IDs',
        "javascript"   => 'JavaScript',
        "authbypass"   => 'Authorisation Bypass',
        "open_redirect"=> 'Open HTTP Redirect'
    ];

    $vuln = $vulnNames[$id] ?? "Unknown Vulnerability";

    // Lecture PHP sécurisé via chemin whitelist
    $phpSourcePath = $allowedPaths[$id][$security]['php'];
    $source = file_get_contents($phpSourcePath);
    $source = str_replace(['$html .='], ['echo'], $source);

    // Lecture JS sécurisé via chemin whitelist
    $js_html = "";
    $jsSourcePath = $allowedPaths[$id][$security]['js'];
    if ($jsSourcePath && file_exists($jsSourcePath)) {
        $js_source = file_get_contents($jsSourcePath);
        $js_html = "
        <h2>vulnerabilities/{$id}/source/{$security}.js</h2>
        <div id=\"code\">
            <table width='100%' bgcolor='white' style=\"border:2px #C0C0C0 solid\">
                <tr>
                    <td><div id=\"code\">" . highlight_string($js_source, true) . "</div></td>
                </tr>
            </table>
        </div>";
    }

    $page['body'] .= "
    <div class=\"body_padded\">
        <h1>{$vuln} Source</h1>

        <h2>vulnerabilities/{$id}/source/{$security}.php</h2>
        <div id=\"code\">
            <table width='100%' bgcolor='white' style=\"border:2px #C0C0C0 solid\">
                <tr>
                    <td><div id=\"code\">" . highlight_string($source, true) . "</div></td>
                </tr>
            </table>
        </div>
        {$js_html}
        <br /> <br />

        <form>
            <input type=\"button\" value=\"Compare All Levels\" onclick=\"window.location.href='view_source_all.php?id={$id}'\">
        </form>
    </div>\n";

} else {
    $page['body'] = "<p>Not found</p>";
}

View\dvwaSourceHtmlEcho($page);
