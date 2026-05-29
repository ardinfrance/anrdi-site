<?php
/**
 * ANRDI - Connexion
 */
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';


Security::setSecurityHeaders();
Auth::startSession();

if (Auth::isLoggedIn()) {
    header('Location: /membre/dashboard.php');
    exit;
}

$error = '';

$loginErrors = [
    'session_invalid' => 'Votre session a changé ou n\'est plus valide. Veuillez vous reconnecter.',
    'session_expired' => 'Votre session a expiré. Veuillez vous reconnecter.',
    'oauth_disabled'  => 'Cette méthode de connexion est actuellement indisponible.',
    'oauth_error'     => 'Une erreur est survenue pendant l\'initialisation de la connexion externe.',
    'oauth_cancelled' => 'La connexion externe a été annulée.',
    'oauth_failed'    => 'La connexion externe a échoué. Veuillez réessayer.',
];

if (isset($_GET['error']) && isset($loginErrors[$_GET['error']])) {
    $error = $loginErrors[$_GET['error']];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireCsrf();
    Security::checkHoneypot();
    Security::checkRateLimit('login', MAX_ATTEMPTS, LOCKOUT_TIME);

    $email = Security::sanitizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $result = Auth::login($email, $password);
        if ($result['success']) {
            $next = $_GET['next'] ?? '';
            $redirect = match ($result['role']) {
                'super_admin', 'admin' => URL_ADMIN,
                'professional' => URL_PRO,
                default => $next ?: '/membre/dashboard.php',
            };
            header('Location: ' . $redirect);
            exit;
        }
        $error = $result['error'];
    }
}

$providers = array_filter(OAUTH, fn($p) => $p['enabled'] ?? false);
$mainCssVersion = is_file(__DIR__ . '/assets/css/main.css') ? (string) filemtime(__DIR__ . '/assets/css/main.css') : '1';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Connexion - ANRDI</title>
<link rel="icon" href="<?= CDN_URL . LOGO_FAVICON ?>" type="image/png">
<link rel="icon" href="/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= CDN_URL ?>/fonts/fonts.css">
<link rel="stylesheet" href="/assets/css/main.css?v=<?= htmlspecialchars($mainCssVersion, ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="page-auth">
<main class="auth-page">
  <section class="auth-panel" aria-labelledby="login-title">
    <div class="auth-panel-inner">
      <a href="/" aria-label="ANRDI - Accueil" class="auth-logo-link">
        <svg viewBox="0 0 200 56" fill="none" style="height:36px;width:auto;">
          <rect x="0" y="0" width="8" height="56" rx="2" fill="#2563EB"/>
          <rect x="12" y="0" width="8" height="56" rx="2" fill="#1E40AF"/>
          <text x="28" y="38" font-family="'Satoshi',sans-serif" font-size="28" font-weight="700" fill="#0A1628" letter-spacing="-0.5">ANRDI</text>
        </svg>
      </a>

      <h1 class="auth-title" id="login-title">Connexion</h1>
      <p class="auth-subtitle">Accédez à votre espace ANRDI.</p>

      <?php if ($error): ?>
      <div class="alert alert--error" role="alert" aria-live="polite">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <?php endif; ?>

      <?php if (!empty($providers)): ?>
      <div class="oauth-buttons" aria-label="Connexion via un service tiers">
        <?php if (!empty(OAUTH['google']['enabled'])): ?>
        <a href="/oauth/google/auth.php" class="btn-oauth">
          <svg class="btn-oauth-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
          </svg>
          <span>Continuer avec Google</span>
        </a>
        <?php endif; ?>

        <?php if (!empty(OAUTH['microsoft']['enabled'])): ?>
        <a href="/oauth/microsoft/auth.php" class="btn-oauth">
          <svg class="btn-oauth-icon" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="1" y="1" width="10.5" height="10.5" fill="#F25022"/>
            <rect x="12.5" y="1" width="10.5" height="10.5" fill="#7FBA00"/>
            <rect x="1" y="12.5" width="10.5" height="10.5" fill="#00A4EF"/>
            <rect x="12.5" y="12.5" width="10.5" height="10.5" fill="#FFB900"/>
          </svg>
          <span>Continuer avec Microsoft</span>
        </a>
        <?php endif; ?>

        <?php if (!empty(OAUTH['github']['enabled'])): ?>
        <a href="/oauth/github/auth.php" class="btn-oauth">
          <svg class="btn-oauth-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844a9.59 9.59 0 0 1 2.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0 0 22 12.017C22 6.484 17.522 2 12 2z"/>
          </svg>
          <span>Continuer avec GitHub</span>
        </a>
        <?php endif; ?>

        <?php if (!empty(OAUTH['x']['enabled'])): ?>
        <a href="/oauth/x/auth.php" class="btn-oauth">
          <svg class="btn-oauth-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
          </svg>
          <span>Continuer avec X</span>
        </a>
        <?php endif; ?>
      </div>
      <div class="auth-divider">ou</div>
      <?php endif; ?>

      <form method="POST" action="/login.php<?= !empty($_GET['next']) ? '?next=' . urlencode($_GET['next']) : '' ?>" novalidate>
        <?= Security::csrfField() ?>
        <?= Security::honeypotField() ?>

        <div class="form-group">
          <label for="email" class="form-label form-label--required">Adresse email</label>
          <input type="email" id="email" name="email" class="form-input"
                 value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 autocomplete="email" required placeholder="vous@exemple.fr">
        </div>

        <div class="form-group">
          <label for="password" class="form-label form-label--required">Mot de passe</label>
          <div class="auth-input-wrapper">
            <input type="password" id="password" name="password" class="form-input auth-password-input"
                   autocomplete="current-password" required placeholder="••••••••••••">
            <button type="button" class="toggle-password auth-password-toggle" aria-label="Afficher le mot de passe">
              <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          <div class="auth-meta-row">
            <label class="form-check">
              <input type="checkbox" name="remember" class="form-check-input" value="1">
              <span class="form-check-label">Se souvenir de moi</span>
            </label>
            <a href="/forgot-password.php" class="auth-link">Mot de passe oublié ?</a>
          </div>
        </div>

        <button type="submit" class="btn btn--primary btn--full btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;" aria-hidden="true"><path d="M15 3h6v18h-6"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
          Se connecter
        </button>
      </form>

      <p class="text-center text-sm auth-register-text">
        Pas encore de compte ?
        <a href="/register.php" class="auth-link auth-link--strong">Créer un compte</a>
      </p>
    </div>
  </section>

  <aside class="auth-visual" aria-hidden="true">
    <div class="auth-visual-content">
      <div class="auth-visual-badge">Espace sécurisé</div>
      <h2 class="auth-visual-title">Un accès simple à votre espace sécurisé.</h2>
      <p class="auth-visual-text">Retrouvez vos démarches, vos échanges et vos services depuis une interface claire et directe.</p>
      <div class="auth-visual-points">
        <span>Connexion rapide</span>
        <span>Suivi de dossiers</span>
        <span>Documents centralisés</span>
      </div>
    </div>
  </aside>
</main>

<script src="/assets/js/main.js" defer></script>
</body>
</html>
