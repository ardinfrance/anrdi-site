<?php
/**
 * ANRDI — Bootstrap
 * Point d'entrée unique de la configuration.
 * Charge config.php depuis son emplacement ultra-caché.
 */

declare(strict_types=1);

if (!defined('ANRDI_BOOTSTRAP')) {
    http_response_code(403);
    die();
}

// ── Chemin absolu vers le config caché ──────────────────────────────────────
define('ANRDI_INIT', true); // Garde nécessaire pour que config.php s'exécute

$_CONFIG_PATH = dirname(__DIR__)
    . '/src/Core/Internal/System/Runtime/Config/Env/.secure/config.php';

if (!file_exists($_CONFIG_PATH)) {
    error_log('[ANRDI BOOTSTRAP] config.php introuvable : ' . $_CONFIG_PATH);
    http_response_code(503);
    die('Service temporairement indisponible.');
}

$cfg = require $_CONFIG_PATH;
unset($_CONFIG_PATH); // Effacer la variable après usage

if (!is_array($cfg)) {
    error_log('[ANRDI BOOTSTRAP] config.php invalide');
    http_response_code(503);
    die('Erreur de configuration critique.');
}

// ── Fuseau horaire ───────────────────────────────────────────────────────────
date_default_timezone_set($cfg['app']['timezone'] ?? 'Europe/Paris');

// ── Gestion des erreurs selon l'environnement ────────────────────────────────
if (($cfg['app']['debug'] ?? false) === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0700, true);
    ini_set('error_log', $logDir . '/anrdi_error.log');
}

// ── Session sécurisée ────────────────────────────────────────────────────────
$sess = $cfg['session'];
$sessionSameSite = strtolower((string)($sess['samesite'] ?? 'Lax'));
if (!in_array($sessionSameSite, ['lax', 'strict', 'none'], true)) {
    $sessionSameSite = 'lax';
}
// OAuth callbacks return from third-party domains, so Strict breaks the session state cookie.
if ($sessionSameSite === 'strict') {
    $sessionSameSite = 'lax';
}
ini_set('session.name',             $sess['name']     ?? 'ANRDI_SESS');
ini_set('session.cookie_lifetime',  '0');
ini_set('session.cookie_secure',    $sess['secure']   ? '1' : '0');
ini_set('session.cookie_httponly',  $sess['httponly'] ? '1' : '0');
ini_set('session.cookie_samesite',  ucfirst($sessionSameSite));
ini_set('session.use_strict_mode',  '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime',   (string)($sess['lifetime'] ?? 3600));

// ── Constantes globales (APP) ────────────────────────────────────────────────
define('APP_ENV',        $cfg['app']['env']        ?? 'production');
define('APP_DEBUG',      $cfg['app']['debug']      ?? false);
define('APP_URL',        $cfg['domains']['main']   ?? 'https://anrdi.fr');
define('APP_SECRET',     $cfg['app']['secret']     ?? '');

// ── Domaines ─────────────────────────────────────────────────────────────────
define('URL_MAIN',    $cfg['domains']['main']    ?? 'https://anrdi.fr');
define('URL_PRO',     $cfg['domains']['pro']     ?? 'https://pro.anrdi.fr');
define('URL_ADMIN',   $cfg['domains']['admin']   ?? 'https://admin.anrdi.fr');
define('URL_CDN',     $cfg['domains']['cdn']     ?? 'https://cdn.anrdi.fr');
define('URL_API',     $cfg['domains']['api']     ?? 'https://api.anrdi.fr');
define('URL_MEMBRES', $cfg['domains']['membres'] ?? 'https://membres.anrdi.fr');
define('URL_STATUS',  $cfg['domains']['status']  ?? 'https://status.anrdi.fr');

// Alias pratique
define('CDN_URL', URL_CDN);

// ── Logo ─────────────────────────────────────────────────────────────────────
define('LOGO_HEADER',  $cfg['logo']['header']  ?? '/assets/img/logo-header.png');
define('LOGO_FOOTER',  $cfg['logo']['footer']  ?? '/assets/img/logo-footer.png');
define('LOGO_FAVICON', $cfg['logo']['favicon'] ?? '/assets/img/favicon.png');
define('LOGO_OG',      $cfg['logo']['og']      ?? '/assets/img/og-image.png');

// ── Base de données ───────────────────────────────────────────────────────────
define('DB_HOST',    $cfg['database']['host']     ?? 'localhost');
define('DB_PORT',    $cfg['database']['port']     ?? 3306);
define('DB_NAME',    $cfg['database']['name']     ?? '');
define('DB_USER',    $cfg['database']['user']     ?? '');
define('DB_PASS',    $cfg['database']['password'] ?? '');
define('DB_CHARSET', $cfg['database']['charset']  ?? 'utf8mb4');
define('DB_PREFIX',  $cfg['database']['prefix']   ?? 'anrdi_');

// ── Chiffrement ───────────────────────────────────────────────────────────────
define('ENC_KEY',  $cfg['encryption']['key']       ?? '');
define('ENC_IV',   $cfg['encryption']['iv_seed']   ?? '');
define('ENC_ALGO', $cfg['encryption']['algorithm'] ?? 'AES-256-CBC');

// ── Mail ─────────────────────────────────────────────────────────────────────
define('MAIL_FROM',    $cfg['mail']['from_address'] ?? 'noreply@anrdi.fr');
define('MAIL_NAME',    $cfg['mail']['from_name']    ?? 'ANRDI');
define('MAIL_ENABLED', $cfg['mail']['enabled']      ?? false);
define('MAIL_HOST',    $cfg['mail']['host']         ?? $cfg['mail']['smtp_host'] ?? '');
define('MAIL_PORT',    (int)($cfg['mail']['port']   ?? $cfg['mail']['smtp_port'] ?? 587));
define('MAIL_ENC',     $cfg['mail']['encryption']   ?? $cfg['mail']['smtp_encryption'] ?? 'tls');
define('MAIL_USER',    $cfg['mail']['username']     ?? $cfg['mail']['user'] ?? '');
define('MAIL_PASS',    $cfg['mail']['password']     ?? $cfg['mail']['pass'] ?? '');
define('MAIL_REPLY_TO',$cfg['mail']['reply_to']     ?? MAIL_FROM);
define('MAIL_CONTACT', $cfg['mail']['contact_address'] ?? 'contact@anrdi.fr');

// ── Sécurité ─────────────────────────────────────────────────────────────────
define('CSRF_LENGTH',   $cfg['security']['csrf_token_length']  ?? 64);
define('CSRF_EXPIRY',   $cfg['security']['csrf_expiry']        ?? 3600);
define('MAX_ATTEMPTS',  $cfg['security']['login_max_attempts'] ?? 5);
define('LOCKOUT_TIME',  $cfg['security']['login_lockout']      ?? 900);
define('HONEYPOT_FIELD',$cfg['security']['honeypot_field']     ?? 'website_url');

// ── OAuth2 ────────────────────────────────────────────────────────────────────
// Construction des URLs dynamiques Microsoft
$msTenant = $cfg['oauth']['microsoft']['tenant_id'] ?? 'common';
$cfg['oauth']['microsoft']['auth_url']  = "https://login.microsoftonline.com/{$msTenant}/oauth2/v2.0/authorize";
$cfg['oauth']['microsoft']['token_url'] = "https://login.microsoftonline.com/{$msTenant}/oauth2/v2.0/token";

define('OAUTH', $cfg['oauth']);

// ── Domaines pro interdits ────────────────────────────────────────────────────
define('PRO_FORBIDDEN_DOMAINS', $cfg['pro_forbidden_domains'] ?? []);

// ── Chemins absolus ───────────────────────────────────────────────────────────
define('ROOT_PATH',     __DIR__);
define('UPLOAD_PATH',   $cfg['uploads']['path'] ?? __DIR__ . '/uploads/secure/');
define('TEMPLATE_PATH', dirname(__DIR__) . '/templates/');
define('LOG_PATH',      __DIR__ . '/logs/');

// ── API ───────────────────────────────────────────────────────────────────────
define('API_VERSION',    $cfg['api']['version']      ?? 'v1');
define('API_RATE_LIMIT', $cfg['api']['rate_limit']   ?? 60);
define('API_CORS',       $cfg['api']['cors_origins'] ?? []);

// ── Nettoyage de la config de la mémoire (sécurité) ──────────────────────────
// On garde $cfg disponible pour include éventuels mais on efface les secrets
unset($cfg['database']['password'], $cfg['encryption'], $cfg['mail']['password']);
$cfg = null;
unset($cfg);
