<?php
/**
 * ANRDI — Mon profil (espace membre)
 */
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/mailer.php';

Security::setSecurityHeaders();
Auth::startSession();
Auth::requireLogin('/login.php');

$user = Auth::getCurrentUser();

$successMsg = '';
$errorMsg   = '';
$activeTab  = Security::sanitize($_GET['tab'] ?? 'identity');
$validTabs  = ['identity', 'security', 'account'];
if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'identity';
}

/* ── Mise à jour des informations personnelles ──────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    Security::requireCsrf();

    if ($_POST['action'] === 'update_identity') {
        Security::checkRateLimit('profile_update_' . $user['id'], 10, 300);
        $activeTab  = 'identity';
        $firstName  = Security::sanitize($_POST['first_name'] ?? '');
        $lastName   = Security::sanitize($_POST['last_name']  ?? '');

        if (strlen($firstName) < 2 || strlen($firstName) > 80) {
            $errorMsg = 'Le prénom doit contenir entre 2 et 80 caractères.';
        } elseif (strlen($lastName) > 80) {
            $errorMsg = 'Le nom de famille est trop long.';
        } else {
            try {
                Database::query(
                    'UPDATE ' . DB_PREFIX . 'users SET first_name = ?, last_name = ? WHERE id = ?',
                    [$firstName, $lastName, $user['id']]
                );
                $successMsg = 'Vos informations ont été mises à jour.';
                $user = Auth::getCurrentUser();
            } catch (Exception $e) {
                $errorMsg = 'Erreur lors de la mise à jour. Veuillez réessayer.';
            }
        }

    } elseif ($_POST['action'] === 'change_password') {
        Security::checkRateLimit('password_change_' . $user['id'], 5, 900);
        $activeTab   = 'security';
        $currentPwd  = $_POST['current_password'] ?? '';
        $newPwd      = $_POST['new_password']      ?? '';
        $confirmPwd  = $_POST['confirm_password']  ?? '';

        $dbUser = Database::query(
            'SELECT password_hash, oauth_provider FROM ' . DB_PREFIX . 'users WHERE id = ? LIMIT 1',
            [$user['id']]
        )->fetch();

        if (!empty($dbUser['oauth_provider']) && empty($dbUser['password_hash'])) {
            $errorMsg = 'Votre compte est lié à ' . htmlspecialchars(ucfirst($dbUser['oauth_provider']), ENT_QUOTES, 'UTF-8') . '. Vous ne pouvez pas définir un mot de passe.';
        } elseif (!Security::verifyPassword($currentPwd, $dbUser['password_hash'] ?? '')) {
            $errorMsg = 'Mot de passe actuel incorrect.';
        } elseif ($newPwd !== $confirmPwd) {
            $errorMsg = 'Les nouveaux mots de passe ne correspondent pas.';
        } else {
            $pwdErrors = Security::validatePassword($newPwd);
            if (!empty($pwdErrors)) {
                $errorMsg = 'Mot de passe invalide : ' . implode(' ', $pwdErrors);
            } else {
                try {
                    Database::query(
                        'UPDATE ' . DB_PREFIX . 'users SET password_hash = ? WHERE id = ?',
                        [Security::hashPassword($newPwd), $user['id']]
                    );
                    $memberName = trim((string) ($user['first_name'] ?? ''));
                    Mailer::sendSecurityAlert(
                        $user['email'],
                        $memberName !== '' ? $memberName : 'membre',
                        'Changement de mot de passe',
                        Security::getClientIp()
                    );
                    $successMsg = 'Votre mot de passe a été modifié avec succès.';
                } catch (Exception $e) {
                    $errorMsg = 'Erreur lors du changement de mot de passe.';
                }
            }
        }
    }
}

/* ── Informations étendues ───────────────────────────────────── */
$userExtra = [];
try {
    $userExtra = Database::query(
        'SELECT oauth_provider, is_verified, created_at, last_login FROM ' . DB_PREFIX . 'users WHERE id = ? LIMIT 1',
        [$user['id']]
    )->fetch();
} catch (Exception $e) {}

$roleLabelMap = [
    'member'      => 'Membre',
    'professional'=> 'Professionnel agréé',
    'admin'       => 'Administrateur',
    'super_admin' => 'Super-administrateur',
];
$roleLabel = $roleLabelMap[$user['role']] ?? ucfirst($user['role']);

$pageTitle = 'Mon profil — ANRDI';
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard">

  <!-- ── Sidebar ───────────────────────────────────────────── -->
  <aside class="sidebar">
    <nav class="sidebar-nav">
      <span class="sidebar-group-title">Navigation</span>
      <a href="/membre/dashboard.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Tableau de bord
      </a>
      <a href="/membre/dossiers.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Mes dossiers
      </a>
      <a href="/membre/profile.php" class="sidebar-link sidebar-link--active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Mon profil
      </a>
      <hr class="sidebar-divider">
      <span class="sidebar-group-title">Espaces</span>
      <a href="<?= URL_PRO ?>" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        Espace Pro
      </a>
      <a href="/pages/contact.php" class="sidebar-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Contacter l'ANRDI
      </a>
      <hr class="sidebar-divider">
      <a href="/logout.php" class="sidebar-link" style="color:var(--c-error);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </a>
    </nav>
  </aside>

  <!-- ── Contenu ───────────────────────────────────────────── -->
  <div class="dashboard-content">

    <div style="margin-bottom:2rem;">
      <h1 style="font-family:'Satoshi',sans-serif;font-size:1.625rem;font-weight:800;color:var(--c-navy);letter-spacing:-.03em;">Mon profil</h1>
      <p style="color:var(--c-gray-500);margin-top:.25rem;font-size:.9375rem;">Gérez vos informations personnelles et la sécurité de votre compte.</p>
    </div>

    <!-- Alertes -->
    <?php if ($successMsg): ?>
    <div class="alert alert--success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg><div><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></div></div>
    <?php elseif ($errorMsg): ?>
    <div class="alert alert--error"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><div><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div></div>
    <?php endif; ?>

    <!-- Onglets -->
    <div style="display:flex;gap:.25rem;margin-bottom:1.75rem;border-bottom:1px solid var(--c-gray-100);">
      <?php
      $tabs = ['identity' => 'Identité', 'security' => 'Sécurité', 'account' => 'Compte'];
      foreach ($tabs as $tKey => $tLabel):
        $isActive = $activeTab === $tKey;
      ?>
      <a href="?tab=<?= $tKey ?>" onclick="this.closest('form')?.submit();return false;"
         style="display:inline-block;padding:.625rem 1.125rem;font-size:.875rem;font-weight:<?= $isActive ? '700' : '500' ?>;color:<?= $isActive ? 'var(--c-blue)' : 'var(--c-gray-500)' ?>;text-decoration:none;border-bottom:2px solid <?= $isActive ? 'var(--c-blue)' : 'transparent' ?>;margin-bottom:-1px;transition:color .1s;">
        <?= $tLabel ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- ═══ Onglet Identité ═══ -->
    <?php if ($activeTab !== 'security' && $activeTab !== 'account'): ?>
    <div style="background:var(--c-white);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:1.75rem;margin-bottom:1.25rem;">
      <h2 style="font-size:1rem;font-weight:700;color:var(--c-navy);margin-bottom:1.5rem;">Informations personnelles</h2>
      <form method="POST" action="/membre/profile.php?tab=identity">
        <input type="hidden" name="action" value="update_identity">
        <?= Security::csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label form-label--required" for="first_name">Prénom</label>
            <input type="text" id="first_name" name="first_name" class="form-input"
                   value="<?= htmlspecialchars($user['first_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="80" required autocomplete="given-name">
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="last_name">Nom de famille</label>
            <input type="text" id="last_name" name="last_name" class="form-input"
                   value="<?= htmlspecialchars($user['last_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                   maxlength="80" autocomplete="family-name">
          </div>
        </div>
        <div class="form-group" style="margin-top:1.25rem;">
          <label class="form-label">Adresse email</label>
          <input type="email" class="form-input" value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" readonly
                 style="background:var(--c-gray-50);color:var(--c-gray-500);cursor:not-allowed;">
          <span class="form-hint">L'email ne peut pas être modifié depuis cette interface. <a href="/pages/contact.php" style="color:var(--c-blue);">Contactez le support</a> pour en changer.</span>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:1.25rem;">
          <button type="submit" class="btn btn--primary">Enregistrer les modifications</button>
        </div>
      </form>
    </div>

    <!-- Infos non modifiables -->
    <div style="background:var(--c-surface);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:1.5rem;">
      <h3 style="font-size:.875rem;font-weight:700;color:var(--c-navy);margin-bottom:1.125rem;">Informations du compte</h3>
      <dl style="display:grid;grid-template-columns:auto 1fr;gap:.75rem 1.5rem;font-size:.875rem;">
        <dt style="color:var(--c-gray-500);font-weight:500;">Rôle</dt>
        <dd><span class="badge badge--ssl"><?= htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8') ?></span></dd>
        <dt style="color:var(--c-gray-500);font-weight:500;">Statut</dt>
        <dd><span class="status status--active">Compte actif</span></dd>
        <?php if (!empty($userExtra['oauth_provider'])): ?>
        <dt style="color:var(--c-gray-500);font-weight:500;">Connexion via</dt>
        <dd style="color:var(--c-navy);font-weight:500;"><?= htmlspecialchars(ucfirst($userExtra['oauth_provider']), ENT_QUOTES, 'UTF-8') ?> (OAuth2)</dd>
        <?php endif; ?>
        <dt style="color:var(--c-gray-500);font-weight:500;">Membre depuis</dt>
        <dd style="color:var(--c-gray-700);"><?= !empty($userExtra['created_at']) ? date('d/m/Y', strtotime($userExtra['created_at'])) : '—' ?></dd>
        <dt style="color:var(--c-gray-500);font-weight:500;">Dernière connexion</dt>
        <dd style="color:var(--c-gray-700);"><?= !empty($user['last_login']) ? date('d/m/Y à H\hi', strtotime($user['last_login'])) : 'Cette session' ?></dd>
      </dl>
    </div>
    <?php endif; ?>

    <!-- ═══ Onglet Sécurité ═══ -->
    <?php if ($activeTab === 'security'): ?>
    <div style="background:var(--c-white);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:1.75rem;margin-bottom:1.25rem;">
      <h2 style="font-size:1rem;font-weight:700;color:var(--c-navy);margin-bottom:.5rem;">Modifier le mot de passe</h2>
      <p style="font-size:.875rem;color:var(--c-gray-500);margin-bottom:1.5rem;">Minimum 12 caractères avec majuscule, minuscule, chiffre et caractère spécial.</p>
      <form method="POST" action="/membre/profile.php?tab=security">
        <input type="hidden" name="action" value="change_password">
        <?= Security::csrfField() ?>
        <div class="form-group">
          <label class="form-label form-label--required" for="current_password">Mot de passe actuel</label>
          <input type="password" id="current_password" name="current_password" class="form-input" required autocomplete="current-password">
        </div>
        <div class="form-group">
          <label class="form-label form-label--required" for="new_password">Nouveau mot de passe</label>
          <input type="password" id="new_password" name="new_password" class="form-input" required autocomplete="new-password" minlength="12">
        </div>
        <div class="form-group">
          <label class="form-label form-label--required" for="confirm_password">Confirmer le mot de passe</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-input" required autocomplete="new-password" minlength="12">
        </div>
        <div style="display:flex;justify-content:flex-end;">
          <button type="submit" class="btn btn--primary">Changer le mot de passe</button>
        </div>
      </form>
    </div>

    <div style="background:var(--c-surface);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:1.5rem;">
      <h3 style="font-size:.875rem;font-weight:700;color:var(--c-navy);margin-bottom:.75rem;">Recommandations de sécurité</h3>
      <ul style="list-style:none;display:flex;flex-direction:column;gap:.5rem;">
        <?php foreach ([
          'N\'utilisez pas le même mot de passe sur d\'autres sites.',
          'Activez un gestionnaire de mots de passe (Bitwarden, 1Password…)',
          'Ne partagez jamais vos identifiants, même avec notre équipe.',
        ] as $tip): ?>
        <li style="display:flex;gap:.625rem;font-size:.875rem;color:var(--c-gray-600);">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;color:var(--c-blue);flex-shrink:0;margin-top:2px;"><polyline points="20 6 9 17 4 12"/></svg>
          <?= htmlspecialchars($tip, ENT_QUOTES, 'UTF-8') ?>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- ═══ Onglet Compte ═══ -->
    <?php if ($activeTab === 'account'): ?>
    <div style="background:var(--c-white);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:1.75rem;margin-bottom:1.25rem;">
      <h2 style="font-size:1rem;font-weight:700;color:var(--c-navy);margin-bottom:.5rem;">Vos droits RGPD</h2>
      <p style="font-size:.875rem;color:var(--c-gray-500);margin-bottom:1.5rem;line-height:1.65;">
        Conformément au RGPD, vous disposez des droits suivants sur vos données personnelles détenues par l'ANRDI.
      </p>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
        <?php foreach ([
          ['Droit d\'accès', 'Obtenir une copie de toutes vos données.', '/pages/politique-confidentialite.php#droits'],
          ['Droit de rectification', 'Corriger des données inexactes vous concernant.', '/pages/politique-confidentialite.php#droits'],
          ['Droit à l\'effacement', 'Demander la suppression de votre compte et données.', '/pages/contact.php'],
          ['Droit à la portabilité', 'Recevoir vos données dans un format structuré.', '/pages/politique-confidentialite.php#droits'],
        ] as [$title, $desc, $href]): ?>
        <div style="border:1px solid var(--c-gray-100);border-radius:var(--r-lg);padding:1.125rem;">
          <div style="font-size:.875rem;font-weight:700;color:var(--c-navy);margin-bottom:.375rem;"><?= $title ?></div>
          <p style="font-size:.8125rem;color:var(--c-gray-500);line-height:1.55;margin-bottom:.875rem;"><?= $desc ?></p>
          <a href="<?= $href ?>" class="btn btn--ghost btn--sm" style="font-size:.75rem;">Exercer ce droit</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div style="background:var(--c-red-pale);border:1px solid #FCA5A5;border-radius:var(--r-xl);padding:1.5rem;">
      <h3 style="font-size:.875rem;font-weight:700;color:var(--c-error);margin-bottom:.5rem;">Zone de danger</h3>
      <p style="font-size:.875rem;color:#7F1D1D;margin-bottom:1rem;line-height:1.65;">
        La suppression de votre compte est irréversible. Toutes vos données seront effacées sous 30 jours conformément à notre politique de conservation.
      </p>
      <a href="/pages/contact.php?sujet=suppression_compte" class="btn btn--danger btn--sm">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
        Demander la suppression du compte
      </a>
    </div>
    <?php endif; ?>

  </div>
</div>

<?php
/* Navigation par onglets sans rechargement — active le bon onglet via URL */
if (!isset($_GET['tab'])) {
    // Pas de JS nécessaire, les liens d'onglet sont des <a href>
}
?>

<script>
// Active le bon onglet selon l'URL
(function() {
  var tab = new URLSearchParams(window.location.search).get('tab') || 'identity';
  document.querySelectorAll('a[href*="?tab="]').forEach(function(el) {
    var t = new URLSearchParams(el.href.split('?')[1]).get('tab');
    if (t === tab) {
      el.style.color = 'var(--c-blue)';
      el.style.borderBottomColor = 'var(--c-blue)';
      el.style.fontWeight = '700';
    }
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
