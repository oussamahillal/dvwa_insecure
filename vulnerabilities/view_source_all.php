<?php
declare(strict_types=1);

define('DVWA_WEB_PAGE_TO_ROOT', '../');
define('HTML_LITERAL', '$html .=');

// Import par namespace (adapter au vrai namespace utilisé dans DVWA)
use DVWA\Core\Page;
use DVWA\Core\View;

require DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/dvwaPage.inc.php';

Page\dvwaPageStartup(['authenticated']);

$page = Page\dvwaPageNewGrab();
$page['title'] = 'Source' . $page['title_separator'] . $page['title'];

// Liste blanche pour éviter la construction de chemin arbitraire
$allowedIds = [
    'javascript',
    'fi',
    'brute',
    'csrf',
    'exec',
    'sqli',
    'sqli_blind',
    'upload',
    'xss_r',
    'xss_s',
    'weak_id',
    'authbypass',
    'open_redirect'
];

// Mapping vulnérabilité -> nom affiché
$vulnNames = [
    'javascript'    => 'JavaScript',
    'fi'            => 'File Inclusion',
    'brute'         => 'Brute Force',
    'csrf'          => 'CSRF',
    'exec'          => 'Command Injection',
    'sqli'          => 'SQL Injection',
    'sqli_blind'    => 'SQL Injection (Blind)',
    'upload'        => 'File Upload',
    'xss_r'         => 'Reflected XSS',
    'xss_s'         => 'Stored XSS',
    'weak_id'       => 'Weak Session IDs',
    'authbypass'    => 'Authorisation Bypass',
    'open_redirect' => 'Open HTTP Redirect'
];

if (isset($_GET['id']) && in_array($_GET['id'], $allowedIds, true)) {
    $id = $_GET['id'];
    $vuln = $vulnNames[$id] ?? 'Unknown Vulnerability';

    // Récupération sécurisée des fichiers
    $basePath = __DIR__ . "/{$id}/source/";
    $levels   = ['impossible', 'high', 'medium', 'low'];

    $sources = [];
    foreach ($levels as $level) {
        $filePath = $basePath . "{$level}.php";
        if (is_file($filePath) && is_readable($filePath)) {
            $src = @file_get_contents($filePath);
            $src = str_replace([HTML_LITERAL], ['echo'], $src);
            $sources[$level] = highlight_string($src, true);
        } else {
            $sources[$level] = "<em>Source file missing</em>";
        }
    }

    $page['body'] .= "
    <div class=\"body_padded\">
        <h1>{$vuln}</h1>
        <br />
    ";

    foreach ($levels as $level) {
        $label = ucfirst($level);
        $page['body'] .= "
        <h3>{$label} {$vuln} Source</h3>
        <table width='100%' bgcolor='white' style=\"border:2px #C0C0C0 solid\">
            <tr>
                <td><div id=\"code\">{$sources[$level]}</div></td>
            </tr>
        </table>
        <br />
        ";
    }

    $page['body'] .= "
        <form>
            <input type=\"button\" value=\"<-- Back\" onclick=\"history.go(-1);return true;\">
        </form>
    </div>\n";
} else {
    $page['body'] = "<p>Not found</p>";
}

View\dvwaSourceHtmlEcho($page);
