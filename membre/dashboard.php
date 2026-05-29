<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

Security::setSecurityHeaders();
Auth::startSession();
Auth::requireLogin('/login.php');

$user = Auth::getCurrentUser();

$statusLabels = [
    'draft' => 'Brouillon',
    'submitted' => 'Soumis',
    'under_review' => 'En examen',
    'awaiting_info' => 'Informations attendues',
    'approved' => 'Approuve',
    'rejected' => 'Rejete',
    'closed' => 'Cloture',
];
$statusClass = [
    'draft' => 'inactive',
    'submitted' => 'pending',
    'under_review' => 'pending',
    'awaiting_info' => 'pending',
    'approved' => 'active',
    'rejected' => 'rejected',
    'closed' => 'inactive',
];
$priorityLabels = [
    'low' => 'Faible',
    'normal' => 'Normale',
    'high' => 'Haute',
    'urgent' => 'Urgente',
];
$typeLabels = [
    'license' => 'Licence',
    'accreditation' => 'Agrement',
    'complaint' => 'Plainte',
    'domain_dispute' => 'Litige domaine',
    'rgpd_request' => 'Demande RGPD',
    'signalement' => 'Signalement',
    'other' => 'Autre',
];

$stats = [
    'total' => 0,
    'open' => 0,
    'awaiting' => 0,
    'approved' => 0,
    'urgent' => 0,
];
$statusCounts = [];
$latest = [];
$recentTypes = [];
$userExtra = [];

try {
    $stats['total'] = (int) Database::query(
        'SELECT COUNT(*) FROM ' . DB_PREFIX . 'dossiers WHERE user_id = ?',
        [$user['id']]
    )->fetchColumn();
    $stats['open'] = (int) Database::query(
        'SELECT COUNT(*) FROM ' . DB_PREFIX . 'dossiers WHERE user_id = ? AND status NOT IN ("closed","rejected")',
        [$user['id']]
    )->fetchColumn();
    $stats['awaiting'] = (int) Database::query(
        'SELECT COUNT(*) FROM ' . DB_PREFIX . 'dossiers WHERE user_id = ? AND status = "awaiting_info"',
        [$user['id']]
    )->fetchColumn();
    $stats['approved'] = (int) Database::query(
        'SELECT COUNT(*) FROM ' . DB_PREFIX . 'dossiers WHERE user_id = ? AND status = "approved"',
        [$user['id']]
    )->fetchColumn();
    $stats['urgent'] = (int) Database::query(
        'SELECT COUNT(*) FROM ' . DB_PREFIX . 'dossiers WHERE user_id = ? AND priority IN ("high","urgent") AND status NOT IN ("closed","rejected")',
        [$user['id']]
    )->fetchColumn();

    $latest = Database::query(
        'SELECT reference, title, type, status, priority, created_at, submitted_at, updated_at
         FROM ' . DB_PREFIX . 'dossiers
         WHERE user_id = ?
         ORDER BY updated_at DESC, created_at DESC
         LIMIT 6',
        [$user['id']]
    )->fetchAll();

    $typeRows = Database::query(
        'SELECT type, COUNT(*) AS n
         FROM ' . DB_PREFIX . 'dossiers
         WHERE user_id = ?
         GROUP BY type
         ORDER BY n DESC
         LIMIT 3',
        [$user['id']]
    )->fetchAll();
    foreach ($typeRows as $row) {
        $recentTypes[] = ($typeLabels[$row['type']] ?? ucfirst((string) $row['type'])) . ' (' . (int) $row['n'] . ')';
    }

    $countRows = Database::query(
        'SELECT status, COUNT(*) AS n
         FROM ' . DB_PREFIX . 'dossiers
         WHERE user_id = ?
         GROUP BY status',
        [$user['id']]
    )->fetchAll();
    foreach ($countRows as $row) {
        $statusCounts[$row['status']] = (int) $row['n'];
    }

    $userExtra = Database::query(
        'SELECT is_verified, oauth_provider, created_at, last_login
         FROM ' . DB_PREFIX . 'users
         WHERE id = ?
         LIMIT 1',
        [$user['id']]
    )->fetch() ?: [];
} catch (Exception $e) {
    $stats = ['total' => 0, 'open' => 0, 'awaiting' => 0, 'approved' => 0, 'urgent' => 0];
}

$profileChecks = [
    !empty($user['first_name']),
    !empty($user['last_name']),
    !empty($user['email']),
    !empty($userExtra['is_verified']),
];
$profileCompletion = (int) round((array_sum(array_map(static fn($v) => $v ? 1 : 0, $profileChecks)) / count($profileChecks)) * 100);

$nextActions = [];
if ($stats['awaiting'] > 0) {
    $nextActions[] = [
        'title' => 'Completer les dossiers en attente',
        'desc' => 'Au moins un dossier attend des informations complementaires avant de pouvoir avancer.',
        'href' => '/membre/dossiers.php?status=awaiting_info',
    ];
}
if ($stats['total'] === 0) {
    $nextActions[] = [
        'title' => 'Deposer votre premier dossier',
        'desc' => 'Ouvrez une demande pour lancer un suivi officiel depuis votre espace membre.',
        'href' => '/membre/dossiers.php',
    ];
}
if ($profileCompletion < 100) {
    $nextActions[] = [
        'title' => 'Completer votre profil',
        'desc' => 'Un profil complet facilite le traitement des demandes et la verification des acces.',
        'href' => '/membre/profile.php?tab=identity',
    ];
}
if (!$nextActions) {
    $nextActions[] = [
        'title' => 'Consulter vos dossiers actifs',
        'desc' => 'Retrouvez les demandes en cours et leur dernier niveau de traitement.',
        'href' => '/membre/dossiers.php?status=submitted',
    ];
    $nextActions[] = [
        'title' => 'Mettre a jour votre securite',
        'desc' => 'Revoyez votre mot de passe et les informations de connexion de votre compte.',
        'href' => '/membre/profile.php?tab=security',
    ];
}

$pageTitle = 'Mon espace - ANRDI';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard">
  <aside class="sidebar">
    <nav class="sidebar-nav">
      <span class="sidebar-group-title">Navigation</span>
      <a href="/membre/dashboard.php" class="sidebar-link sidebar-link--active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Tableau de bord</a>
      <a href="/membre/dossiers.php" class="sidebar-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>Mes dossiers</a>
      <a href="/membre/profile.php" class="sidebar-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>Mon profil</a>
      <hr class="sidebar-divider">
      <span class="sidebar-group-title">Services</span>
      <a href="<?= URL_PRO ?>" class="sidebar-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>Espace Pro</a>
      <a href="/pages/contact.php" class="sidebar-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Contacter l'ANRDI</a>
      <hr class="sidebar-divider">
      <a href="/logout.php" class="sidebar-link" style="color:var(--c-error);"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Deconnexion</a>
    </nav>
  </aside>

  <div class="dashboard-content">
    <div class="member-shell">
      <section class="member-hero">
        <div>
          <h1>Bonjour <?= htmlspecialchars($user['first_name'] ?: 'membre', ENT_QUOTES, 'UTF-8') ?>.</h1>
          <p>Retrouvez en un coup d'oeil vos dossiers, vos prochaines actions et l'etat general de votre compte ANRDI.</p>
          <div class="member-hero-meta">
            <span><?= (int) $stats['open'] ?> dossier(s) actif(s)</span>
            <span><?= !empty($userExtra['is_verified']) ? 'Compte verifie' : 'Verification en attente' ?></span>
            <span><?= $recentTypes ? htmlspecialchars(implode(' - ', $recentTypes), ENT_QUOTES, 'UTF-8') : 'Aucun type de dossier dominant' ?></span>
          </div>
        </div>
        <div class="member-progress">
          <div class="member-progress-label">Profil membre</div>
          <div class="member-progress-value"><?= $profileCompletion ?>%</div>
          <div class="member-progress-bar"><span style="width:<?= $profileCompletion ?>%"></span></div>
          <p style="margin-top:.75rem;color:rgba(255,255,255,.72);font-size:.82rem;line-height:1.55;">Un profil complet accelere le suivi des demandes et la verification des informations de contact.</p>
        </div>
      </section>

      <section class="member-stat-grid">
        <article class="member-stat-card">
          <div class="member-stat-value"><?= (int) $stats['total'] ?></div>
          <div class="member-stat-label">Dossiers deposes</div>
          <div class="member-stat-note">Toutes vos demandes enregistrees dans l'espace membre.</div>
        </article>
        <article class="member-stat-card">
          <div class="member-stat-value"><?= (int) $stats['open'] ?></div>
          <div class="member-stat-label">Dossiers en cours</div>
          <div class="member-stat-note">Demandes non cloturees et encore en traitement ou en attente.</div>
        </article>
        <article class="member-stat-card">
          <div class="member-stat-value"><?= (int) $stats['awaiting'] ?></div>
          <div class="member-stat-label">Actions requises</div>
          <div class="member-stat-note">Dossiers en attente d'informations ou de documents complementaires.</div>
        </article>
        <article class="member-stat-card">
          <div class="member-stat-value"><?= (int) $stats['approved'] ?></div>
          <div class="member-stat-label">Demandes approuvees</div>
          <div class="member-stat-note">Historique des dossiers ayant abouti favorablement.</div>
        </article>
      </section>

      <div class="member-grid">
        <div class="member-stack">
          <section class="surface-card">
            <div class="surface-card-header">
              <div>
                <div class="surface-card-title">Activite recente</div>
                <div class="surface-card-subtitle">Vos derniers mouvements et mises a jour de dossiers.</div>
              </div>
              <a href="/membre/dossiers.php" class="btn btn--ghost btn--sm">Voir tous les dossiers</a>
            </div>
            <?php if (!$latest): ?>
              <div class="member-empty">
                <p>Aucun dossier n'a encore ete depose depuis cet espace.</p>
                <div class="member-inline-links">
                  <a href="/membre/dossiers.php" class="btn btn--primary btn--sm">Deposer un dossier</a>
                  <a href="/pages/contact.php" class="btn btn--ghost btn--sm">Parler a l'ANRDI</a>
                </div>
              </div>
            <?php else: ?>
              <div class="member-list">
                <?php foreach ($latest as $dossier): ?>
                  <?php
                  $status = $dossier['status'];
                  $priority = $dossier['priority'];
                  $type = $dossier['type'];
                  $statusLabel = $statusLabels[$status] ?? ucfirst((string) $status);
                  $priorityLabel = $priorityLabels[$priority] ?? ucfirst((string) $priority);
                  $typeLabel = $typeLabels[$type] ?? ucfirst((string) $type);
                  $metaDate = $dossier['updated_at'] ?: $dossier['created_at'];
                  ?>
                  <article class="member-list-item">
                    <div>
                      <strong><?= htmlspecialchars($dossier['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                      <p>
                        <code style="font-size:.76rem;color:var(--c-blue);"><?= htmlspecialchars($dossier['reference'], ENT_QUOTES, 'UTF-8') ?></code>
                        · <?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?>
                        · Priorite <?= htmlspecialchars($priorityLabel, ENT_QUOTES, 'UTF-8') ?>
                      </p>
                    </div>
                    <div style="display:flex;flex-direction:column;align-items:flex-end;gap:.45rem;">
                      <span class="status status--<?= htmlspecialchars($statusClass[$status] ?? 'inactive', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="member-list-meta">Mis a jour le <?= date('d/m/Y', strtotime((string) $metaDate)) ?></span>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>

          <section class="surface-card">
            <div class="surface-card-header">
              <div>
                <div class="surface-card-title">Actions rapides</div>
                <div class="surface-card-subtitle">Les raccourcis les plus utiles pour avancer sans perdre de temps.</div>
              </div>
            </div>
            <div class="member-action-grid">
              <?php foreach ($nextActions as $action): ?>
                <a href="<?= htmlspecialchars($action['href'], ENT_QUOTES, 'UTF-8') ?>" class="member-action-card">
                  <strong><?= htmlspecialchars($action['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <p><?= htmlspecialchars($action['desc'], ENT_QUOTES, 'UTF-8') ?></p>
                </a>
              <?php endforeach; ?>
              <a href="/membre/profile.php?tab=account" class="member-action-card">
                <strong>Exercer vos droits RGPD</strong>
                <p>Accedez rapidement aux demandes d'acces, rectification, effacement et portabilite.</p>
              </a>
              <a href="/pages/contact.php" class="member-action-card">
                <strong>Contacter l'ANRDI</strong>
                <p>Besoin d'aide sur un dossier, un acces ou une procedure particuliere.</p>
              </a>
            </div>
          </section>
        </div>

        <div class="member-stack">
          <section class="surface-card">
            <div class="surface-card-header">
              <div>
                <div class="surface-card-title">Etat du compte</div>
                <div class="surface-card-subtitle">Resume administratif et technique de votre acces.</div>
              </div>
            </div>
            <ul class="member-info-list" style="list-style:none;">
              <li><span class="member-info-label">Compte</span><span class="member-info-value"><?= !empty($userExtra['is_verified']) ? 'Actif et verifie' : 'Actif - verification a finaliser' ?></span></li>
              <li><span class="member-info-label">Connexion</span><span class="member-info-value"><?= !empty($userExtra['oauth_provider']) ? htmlspecialchars(ucfirst((string) $userExtra['oauth_provider']), ENT_QUOTES, 'UTF-8') . ' (OAuth)' : 'Email et mot de passe' ?></span></li>
              <li><span class="member-info-label">Membre depuis</span><span class="member-info-value"><?= !empty($userExtra['created_at']) ? date('d/m/Y', strtotime((string) $userExtra['created_at'])) : '-' ?></span></li>
              <li><span class="member-info-label">Derniere connexion</span><span class="member-info-value"><?= !empty($userExtra['last_login']) ? date('d/m/Y a H\hi', strtotime((string) $userExtra['last_login'])) : 'Cette session' ?></span></li>
              <li><span class="member-info-label">Email</span><span class="member-info-value"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span></li>
            </ul>
            <div class="member-inline-links">
              <a href="/membre/profile.php?tab=identity" class="btn btn--ghost btn--sm">Mettre a jour le profil</a>
              <a href="/membre/profile.php?tab=security" class="btn btn--ghost btn--sm">Securiser le compte</a>
            </div>
          </section>

          <section class="surface-card">
            <div class="surface-card-header">
              <div>
                <div class="surface-card-title">Repartition des dossiers</div>
                <div class="surface-card-subtitle">Vue rapide par etat de traitement.</div>
              </div>
            </div>
            <div class="member-filter-grid">
              <?php foreach (['submitted', 'under_review', 'awaiting_info', 'approved'] as $key): ?>
                <div class="member-filter-card">
                  <strong><?= (int) ($statusCounts[$key] ?? 0) ?></strong>
                  <span><?= htmlspecialchars($statusLabels[$key], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="member-highlight" style="margin-top:1rem;">
              <?php if ($stats['urgent'] > 0): ?>
                <?= (int) $stats['urgent'] ?> dossier(s) prioritaire(s) necessitent une attention particuliere dans votre suivi actuel.
              <?php else: ?>
                Aucun dossier haute priorite n'est actuellement remonte dans votre espace membre.
              <?php endif; ?>
            </div>
          </section>

          <section class="surface-card">
            <div class="surface-card-header">
              <div>
                <div class="surface-card-title">Ressources utiles</div>
                <div class="surface-card-subtitle">Les pages les plus souvent consultees depuis l'espace membre.</div>
              </div>
            </div>
            <div class="member-badge-row">
              <a href="/pages/politique-confidentialite.php#droits" class="btn btn--ghost btn--sm">Droits RGPD</a>
              <a href="/pages/contact.php" class="btn btn--ghost btn--sm">Support</a>
              <a href="/membre/dossiers.php" class="btn btn--ghost btn--sm">Depot de dossier</a>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
