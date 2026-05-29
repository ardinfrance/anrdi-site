<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';
Security::setSecurityHeaders();
Auth::startSession();

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireCsrf();
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if ($token === '') {
        $error = 'Lien de réinitialisation invalide.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        $passwordErrors = Security::validatePassword($password);
        if ($passwordErrors) {
            $error = implode(' ', $passwordErrors);
        } else {
            try {
                $user = Database::query(
                    'SELECT id FROM ' . DB_PREFIX . 'users WHERE reset_token = ? AND reset_expires_at >= NOW() LIMIT 1',
                    [$token]
                )->fetch();

                if ($user) {
                    Database::query(
                        'UPDATE ' . DB_PREFIX . 'users SET password_hash = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?',
                        [Security::hashPassword($password), (int) $user['id']]
                    );
                    $success = 'Votre mot de passe a été réinitialisé avec succès.';
                } else {
                    $error = 'Lien expiré ou invalide.';
                }
            } catch (Throwable $e) {
                error_log('[ANRDI reset-password] ' . $e->getMessage());
                $error = 'La réinitialisation est temporairement indisponible.';
            }
        }
    }
}

$pageTitle = 'Réinitialisation du mot de passe - ANRDI';
$pageDescription = 'Définir un nouveau mot de passe.';
include __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container" style="max-width:720px;">
  <div class="section-header"><span class="section-label">Sécurité</span><h1 class="section-title">Réinitialiser le mot de passe</h1></div>
  <div class="card">
    <?php if ($success): ?>
    <p class="card-desc"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <a href="/login.php" class="btn btn--primary">Retour à la connexion</a>
    <?php else: ?>
    <?php if ($error): ?><div class="alert alert--error"><span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
    <form method="post" action="/reset-password.php">
      <?= Security::csrfField() ?>
      <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
      <div class="form-group">
        <label for="password" class="form-label form-label--required">Nouveau mot de passe</label>
        <input type="password" id="password" name="password" class="form-input" required>
      </div>
      <div class="form-group">
        <label for="password_confirm" class="form-label form-label--required">Confirmer le mot de passe</label>
        <input type="password" id="password_confirm" name="password_confirm" class="form-input" required>
      </div>
      <button type="submit" class="btn btn--primary">Enregistrer</button>
    </form>
    <?php endif; ?>
  </div>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
