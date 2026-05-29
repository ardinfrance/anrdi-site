<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/header.php';
Security::setSecurityHeaders();
Auth::startSession();
$success = false; $error = '';
$mainCssVersion = is_file(__DIR__ . '/assets/css/main.css') ? (string) filemtime(__DIR__ . '/assets/css/main.css') : '1';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireCsrf(); Security::checkHoneypot();
    Security::checkRateLimit('forgot', 3, 900);
    $email = Security::sanitizeEmail($_POST['email'] ?? '');
    if (!Security::validateEmail($email)) { $error = 'Adresse email invalide.'; }
    else {
        $token = Auth::generateResetToken($email);
        if ($token) {
            $stmt = Database::query('SELECT first_name FROM '.DB_PREFIX.'users WHERE email=? LIMIT 1', [$email]);
            $u = $stmt->fetch();
            if (!Mailer::sendPasswordReset($email, $u['first_name'] ?? '', URL_MAIN.'/reset-password.php?token='.urlencode($token))) {
                $error = 'Le lien n a pas pu etre envoye pour le moment. Veuillez reessayer dans quelques minutes.';
            } else {
                $success = true;
            }
        } else {
            $success = true;
        }
    }
}
?><!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="robots" content="noindex"><title>Mot de passe oublié — ANRDI</title><link rel="icon" href="<?= CDN_URL . LOGO_FAVICON ?>" type="image/png"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer"><link rel="stylesheet" href="<?= CDN_URL ?>/fonts/fonts.css"><link rel="stylesheet" href="/assets/css/main.css"></head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--c-gray-50);padding:2rem;">
<div style="background:#fff;border:1px solid var(--c-gray-200);border-radius:var(--r-xl);padding:2.5rem;max-width:440px;width:100%;box-shadow:var(--sh-lg);">
<a href="/" style="display:inline-block;margin-bottom:1.5rem;"><svg viewBox="0 0 200 56" fill="none" style="height:32px;width:auto;"><rect x="0" y="0" width="8" height="56" rx="2" fill="#2563EB"/><rect x="12" y="0" width="8" height="56" rx="2" fill="#1E40AF"/><text x="28" y="38" font-family="'Satoshi',sans-serif" font-size="28" font-weight="700" fill="#0A1628" letter-spacing="-0.5">ANRDI</text></svg></a>
<?php if ($success): ?><div class="alert alert--success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg><span>Si cette adresse est enregistrée, un lien de réinitialisation vient d'être envoyé. Vérifiez vos spams.</span></div><a href="/login.php" class="btn btn--primary btn--full">Retour à la connexion</a>
<?php else: ?><h1 style="font-family:'Satoshi',sans-serif;font-size:1.75rem;font-weight:800;color:var(--c-navy);margin-bottom:.5rem;">Mot de passe oublié</h1><p style="font-size:.875rem;color:var(--c-gray-600);margin-bottom:1.5rem;">Saisissez votre email pour recevoir un lien de réinitialisation.</p>
<?php if ($error): ?><div class="alert alert--error"><span><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></span></div><?php endif; ?>
<form method="POST" novalidate><?= Security::csrfField() ?><?= Security::honeypotField() ?>
<div class="form-group"><label for="email" class="form-label form-label--required">Adresse email</label><input type="email" id="email" name="email" class="form-input" autocomplete="email" required placeholder="vous@exemple.fr"></div>
<button type="submit" class="btn btn--primary btn--full btn--lg">Envoyer le lien</button></form>
<p class="text-center text-sm" style="margin-top:1.5rem;"><a href="/login.php" style="color:var(--c-blue);text-decoration:none;">← Retour à la connexion</a></p><?php endif; ?>
</div><script src="/assets/js/main.js" defer></script></body></html>
