<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/auth.php';
Security::setSecurityHeaders();
Auth::startSession();
$code  = Security::sanitize($_GET['code']  ?? '');
$state = Security::sanitize($_GET['state'] ?? '');
$err   = $_GET['error'] ?? '';
if ($err || empty($code) || empty($state)) { header('Location: /login.php?error=oauth_cancelled'); exit; }
try {
    $r = Auth::handleOAuthCallback('google', $code, $state);
    if ($r['success']) {
        $role = $r['user']['role'] ?? 'member';
        $dest = match($role) {
            'super_admin', 'admin' => URL_ADMIN,
            'professional'         => URL_PRO,
            default                => URL_MAIN . '/membre/dashboard.php',
        };
        header('Location: ' . $dest); exit;
    }
} catch (Exception $e) { error_log('[ANRDI OAuth google CB] ' . $e->getMessage()); }
header('Location: /login.php?error=oauth_failed'); exit;
