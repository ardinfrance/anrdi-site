<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
Security::setSecurityHeaders();
Auth::startSession();
Auth::logout();
header('Location: /?loggedout=1');
exit;
