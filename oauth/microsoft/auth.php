<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/auth.php';
Security::setSecurityHeaders();
Auth::startSession();
if (!(OAUTH['microsoft']['enabled'] ?? false)) { header('Location: /login.php?error=oauth_disabled'); exit; }
try { header('Location: ' . Auth::getOAuthUrl('microsoft')); exit; }
catch (Exception $e) { error_log('[ANRDI OAuth microsoft auth] ' . $e->getMessage()); header('Location: /login.php?error=oauth_error'); exit; }
