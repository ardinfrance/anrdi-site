<?php
/**
 * ANRDI — Inscription membre / professionnel
 */
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mailer.php';

Security::setSecurityHeaders();
Auth::startSession();
if (Auth::isLoggedIn()) { header('Location: /membre/dashboard.php'); exit; }

$type    = in_array($_GET['type'] ?? '', ['member', 'pro']) ? $_GET['type'] : 'member';
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Security::requireCsrf();
    Security::checkHoneypot();
    Security::checkRateLimit('register', 5, 900);

    $accountType = $_POST['account_type'] ?? 'member';
    $firstName   = Security::sanitize($_POST['first_name'] ?? '');
    $lastName    = Security::sanitize($_POST['last_name']  ?? '');
    $email       = Security::sanitizeEmail($_POST['email'] ?? '');
    $password    = $_POST['password']  ?? '';
    $confirm     = $_POST['confirm']   ?? '';
    $rgpd        = !empty($_POST['rgpd_consent']);

    // Validations
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } elseif (!$rgpd) {
        $error = 'Vous devez accepter la politique de confidentialité.';
    } elseif ($password !== $confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!empty($errs = Security::validatePassword($password))) {
        $error = implode(' ', $errs);
    } elseif ($accountType === 'pro' && !Security::validateProEmail($email)) {
        $error = 'L\'espace professionnel requiert une adresse email professionnelle (pas Gmail, Outlook, Yahoo, etc.).';
    } elseif (!Security::validateEmail($email)) {
        $error = 'Adresse email invalide.';
    } else {
        $exists = Database::query('SELECT id FROM ' . DB_PREFIX . 'users WHERE email = ? LIMIT 1', [$email])->fetch();
        if ($exists) {
            $error = 'Cette adresse email est déjà utilisée.';
        } else {
            try {
                Database::beginTransaction();
                $verToken = Security::generateToken(32);
                $role     = $accountType === 'pro' ? 'professional' : 'member';
                Database::query(
                    'INSERT INTO ' . DB_PREFIX . 'users
                     (email, password_hash, first_name, last_name, role, is_active, is_verified,
                      verification_token, rgpd_consented_at, rgpd_consent_version, created_at)
                     VALUES (?,?,?,?,?,1,0,?,NOW(),"1.0",NOW())',
                    [$email, Security::hashPassword($password), $firstName, $lastName, $role, $verToken]
                );
                $userId = (int) Database::lastInsertId();

                if ($accountType === 'pro') {
                    $orgName = Security::sanitize($_POST['org_name'] ?? '');
                    $siret   = preg_replace('/\D/', '', $_POST['siret'] ?? '');
                    if (!empty($orgName)) {
                        Database::query(
                            'INSERT INTO ' . DB_PREFIX . 'organizations
                             (user_id, name, legal_name, siret, email, status)
                             VALUES (?,?,?,?,?,"pending")',
                            [$userId, $orgName, $orgName, $siret ?: null, $email]
                        );
                    }
                }
                Database::commit();

                $mailSent = Mailer::sendWelcome($email, $firstName, URL_MAIN . '/verify.php?token=' . urlencode($verToken));
                $success = $mailSent
                    ? 'Compte cree ! Consultez vos emails pour activer votre compte.'
                    : 'Compte cree, mais l email d activation n a pas pu etre envoye. Contactez le support ou demandez un nouveau lien.';
            } catch (Exception $e) {
                Database::rollback();
                error_log('[ANRDI REGISTER] ' . $e->getMessage());
                $error = 'Une erreur est survenue. Veuillez réessayer.';
            }
        }
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
<title><?= $type === 'pro' ? 'Inscription Professionnelle' : 'Créer un compte' ?> — ANRDI</title>
<link rel="icon" href="<?= CDN_URL . LOGO_FAVICON ?>" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= CDN_URL ?>/fonts/fonts.css">
<link rel="stylesheet" href="/assets/css/main.css?v=<?= htmlspecialchars($mainCssVersion, ENT_QUOTES, 'UTF-8') ?>">
<link rel="icon" href="/favicon.ico">
</head>
<body class="page-auth">
<main class="auth-page">

  <section class="auth-panel auth-panel--scroll" aria-labelledby="register-title">
    <div class="auth-panel-inner auth-panel-inner--compact">

      <a href="/" aria-label="ANRDI - Accueil" class="auth-logo-link">
        <svg viewBox="0 0 200 56" fill="none" style="height:36px;width:auto;">
          <rect x="0" y="0" width="8" height="56" rx="2" fill="#2563EB"/>
          <rect x="12" y="0" width="8" height="56" rx="2" fill="#1E40AF"/>
          <text x="28" y="38" font-family="'Satoshi',sans-serif" font-size="28" font-weight="700" fill="#0A1628" letter-spacing="-0.5">ANRDI</text>
        </svg>
      </a>

      <nav class="auth-tabs" aria-label="Type de compte">
        <a href="/register.php?type=member"
           class="auth-tab<?= $type === 'member' ? ' is-active' : '' ?>">
          <i class="fa-regular fa-user" aria-hidden="true"></i> Particulier
        </a>
        <a href="/register.php?type=pro"
           class="auth-tab<?= $type === 'pro' ? ' is-active' : '' ?>">
          <i class="fa-regular fa-building" aria-hidden="true"></i> Professionnel
        </a>
      </nav>

      <h1 class="auth-title" id="register-title"><?= $type==='pro' ? 'Compte Professionnel' : 'Créer un compte' ?></h1>
      <p class="auth-subtitle">
        <?= $type==='pro'
            ? 'Adresse email professionnelle requise (pas Gmail, Outlook…).'
            : 'Rejoignez l\'espace membre de l\'ANRDI.' ?>
      </p>

      <?php if ($success): ?>
      <div class="alert alert--success">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
        <span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <a href="/login.php" class="btn btn--primary btn--full">Aller à la connexion</a>
      <?php else: ?>

      <?php if ($error): ?>
      <div class="alert alert--error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
      <?php endif; ?>

      <!-- OAuth (membres seulement) -->
      <?php if ($type === 'member' && !empty($providers)): ?>
      <div class="oauth-buttons">
        <?php if (!empty(OAUTH['google']['enabled'])): ?>
        <a href="/oauth/google/auth.php" class="btn-oauth">
          <svg class="btn-oauth-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          <span>S'inscrire avec Google</span>
        </a>
        <?php endif; ?>
      </div>
      <div class="auth-divider">ou</div>
      <?php endif; ?>

      <form method="POST" action="/register.php?type=<?= urlencode($type) ?>" novalidate>
        <?= Security::csrfField() ?>
        <?= Security::honeypotField() ?>
        <input type="hidden" name="account_type" value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>">

        <div class="auth-form-grid">
          <div class="form-group">
            <label for="first_name" class="form-label form-label--required">Prénom</label>
            <input type="text" id="first_name" name="first_name" class="form-input"
                   value="<?= htmlspecialchars($_POST['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   autocomplete="given-name" required placeholder="Jean">
          </div>
          <div class="form-group">
            <label for="last_name" class="form-label form-label--required">Nom</label>
            <input type="text" id="last_name" name="last_name" class="form-input"
                   value="<?= htmlspecialchars($_POST['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   autocomplete="family-name" required placeholder="Dupont">
          </div>
        </div>

        <?php if ($type === 'pro'): ?>
        <div class="form-group">
          <label for="org_name" class="form-label form-label--required">Raison sociale</label>
          <input type="text" id="org_name" name="org_name" class="form-input"
                 value="<?= htmlspecialchars($_POST['org_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 required placeholder="Ma Société SAS">
        </div>
        <div class="form-group">
          <label for="siret" class="form-label">N° SIRET</label>
          <input type="text" id="siret" name="siret" class="form-input"
                 value="<?= htmlspecialchars($_POST['siret'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 inputmode="numeric" maxlength="14" placeholder="12345678901234">
          <div class="form-hint">14 chiffres sans espaces</div>
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label for="email" class="form-label form-label--required">
            <?= $type === 'pro' ? 'Email professionnel' : 'Adresse email' ?>
          </label>
          <input type="email"
                 id="<?= $type === 'pro' ? 'pro-email-input' : 'email' ?>"
                 name="email" class="form-input"
                 value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 autocomplete="email" required
                 placeholder="<?= $type === 'pro' ? 'vous@votreentreprise.fr' : 'vous@exemple.fr' ?>">
          <?php if ($type === 'pro'): ?>
          <div id="pro-email-hint" class="form-hint">Gmail, Outlook, Yahoo, etc. ne sont pas acceptés.</div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="password" class="form-label form-label--required">Mot de passe</label>
          <div class="auth-input-wrapper input-wrapper">
            <input type="password" id="password" name="password" class="form-input"
                   data-strength="pwd-strength-bar"
                   autocomplete="new-password" required placeholder="12 caractères minimum">
            <button type="button" class="toggle-password auth-password-toggle" aria-label="Afficher le mot de passe">
              <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg class="icon-eye-off hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
          <div class="strength-wrapper">
            <div class="strength-meter">
              <div id="pwd-strength-bar" class="strength-bar"></div>
            </div>
            <span class="strength-label"></span>
          </div>
        </div>

        <div class="form-group">
          <label for="confirm" class="form-label form-label--required">Confirmer le mot de passe</label>
          <input type="password" id="confirm" name="confirm" class="form-input"
                 autocomplete="new-password" required placeholder="••••••••••••">
        </div>

        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="rgpd_consent" class="form-check-input" value="1" required>
            <span class="form-check-label">
              J'accepte la
              <a href="/pages/politique-confidentialite.php" target="_blank" class="auth-link">politique de confidentialité</a>
              et les <a href="/pages/cgv.php" target="_blank" class="auth-link">CGU</a>.
            </span>
          </label>
        </div>

        <button type="submit" class="btn btn--primary btn--full btn--lg">
          <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
          Créer mon compte<?= $type === 'pro' ? ' professionnel' : '' ?>
        </button>
      </form>

      <?php endif; ?>

      <p class="text-center text-sm auth-register-text">
        Déjà un compte ?
        <a href="/login.php" class="auth-link auth-link--strong">Se connecter</a>
      </p>
    </div>
  </section>

  <aside class="auth-visual" role="presentation" aria-hidden="true">
    <div class="auth-visual-content auth-visual-content--centered">
      <?php if ($type === 'pro'): ?>
      <div class="auth-visual-icon-card">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
      </div>
      <div class="auth-visual-badge">Professionnels</div>
      <h2 class="auth-visual-title">Espace Professionnel</h2>
      <p class="auth-visual-text">Dépôt de dossiers, licences, agréments, annuaire officiel et signalements prioritaires.</p>
      <div class="auth-visual-points">
        <span>Validation métier</span>
        <span>Suivi prioritaire</span>
        <span>Annuaire officiel</span>
      </div>
      <?php else: ?>
      <div class="auth-visual-icon-card">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      </div>
      <div class="auth-visual-badge">Membres</div>
      <h2 class="auth-visual-title">Espace Membre</h2>
      <p class="auth-visual-text">Suivez vos dossiers, accédez aux décisions officielles et exercez vos droits RGPD.</p>
      <div class="auth-visual-points">
        <span>Tableau de bord</span>
        <span>Démarches suivies</span>
        <span>Droits RGPD</span>
      </div>
      <?php endif; ?>
    </div>
  </aside>

</main>
<script src="/assets/js/main.js" defer></script>
</body>
</html>
