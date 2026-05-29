<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
Security::setSecurityHeaders();
Auth::startSession();

$token = trim((string) ($_GET['token'] ?? ''));
$success = false;
$message = 'Lien de vérification invalide ou expiré.';

if ($token !== '') {
    try {
        $user = Database::query('SELECT id FROM ' . DB_PREFIX . 'users WHERE verification_token = ? LIMIT 1', [$token])->fetch();
        if ($user) {
            Database::query('UPDATE ' . DB_PREFIX . 'users SET is_verified = 1, verification_token = NULL WHERE id = ?', [(int) $user['id']]);
            $success = true;
            $message = 'Votre adresse email a bien été vérifiée. Vous pouvez maintenant vous connecter.';
        }
    } catch (Throwable $e) {
        error_log('[ANRDI verify] ' . $e->getMessage());
        $message = 'La vérification est temporairement indisponible.';
    }
}

$pageTitle = 'Vérification de compte - ANRDI';
$pageDescription = 'Validation de l’adresse email.';
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container" style="max-width:720px;">
  <div class="section-header"><span class="section-label">Compte</span><h1 class="section-title">Vérification d’email</h1></div>
  <div class="card" style="text-align:center;">
    <p class="card-desc"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <div style="margin-top:1.5rem;"><a href="/login.php" class="btn btn--primary"><?= $success ? 'Se connecter' : 'Retour à la connexion' ?></a></div>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
