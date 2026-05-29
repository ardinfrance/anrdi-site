<?php
/**
 * ANRDI - Page d'accueil
 */
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/auth.php';

Security::setSecurityHeaders();
Auth::startSession();

$latestNews = [];
try {
    $latestNews = Database::query(
        'SELECT id, title, slug, excerpt, published_at, category
         FROM ' . DB_PREFIX . 'posts
         WHERE status = "published"
         ORDER BY published_at DESC LIMIT 3'
    )->fetchAll();
} catch (Exception $e) {}

$pageTitle       = 'ANRDI — Association Nationale de Régulation des Domaines et de l\'Internet';
$pageDescription = 'L\'ANRDI régule, protège et développe l\'écosystème numérique français. Noms de domaine, protection des internautes, agréments professionnels.';

include __DIR__ . '/includes/header.php';
?>

<!-- ── HERO ──────────────────────────────────────────────────── -->
<section class="hero" aria-labelledby="hero-title">
  <div class="container">

    <div class="hero-body">
      <div class="hero-eyebrow">
        <span class="hero-flag" aria-hidden="true">
          <span style="background:#002395;"></span>
          <span style="background:#EDEDED;"></span>
          <span style="background:#ED2939;"></span>
        </span>
        Association Nationale de Régulation des Domaines et de l'Internet
      </div>

      <h1 class="hero-title" id="hero-title">
        Réguler le numérique,<br>
        <em>protéger l'internet</em><br>
        français.
      </h1>

      <p class="hero-subtitle">
        L'ANRDI est l'autorité de référence pour la régulation des noms de domaine
        et de l'internet en France. Nous œuvrons pour un espace numérique sûr,
        équitable et conforme au droit français et européen.
      </p>

      <div class="hero-actions">
        <a href="/pages/services.php" class="btn btn--primary btn--lg">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Nos missions
        </a>
        <a href="<?= URL_PRO ?>" class="btn btn--ghost btn--lg">
          Espace Professionnel
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </div>

      <div class="hero-trust" aria-label="Garanties">
        <span class="hero-trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Conforme RGPD
        </span>
        <span class="hero-trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          SSL / HTTPS
        </span>
        <span class="hero-trust-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          Service disponible 24h/24
        </span>
      </div>
    </div>


<!-- ── MISSIONS ──────────────────────────────────────────────── -->
  </div>
</section>
<section class="section" aria-labelledby="missions-title">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Nos missions</span>
      <h2 class="section-title" id="missions-title">
        Une régulation complète<br>de l'espace numérique
      </h2>
      <p class="section-desc">
        De la gestion des noms de domaine à la protection des internautes,
        l'ANRDI intervient à chaque niveau de l'écosystème internet français.
      </p>
    </div>

    <div class="card-grid">
      <?php
      $services = [
        [
          'icon' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
          'title' => 'Régulation des domaines',
          'desc'  => 'Supervision et arbitrage des noms de domaine en .fr et extensions associées. Résolution des litiges, protection des marques en ligne.',
          'href'  => '/pages/services.php#domaines',
        ],
        [
          'icon' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
          'title' => 'Protection des internautes',
          'desc'  => 'Lutte contre la cybercriminalité, sites frauduleux et contenus illicites. Traitement prioritaire des signalements.',
          'href'  => '/pages/services.php#protection',
        ],
        [
          'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
          'title' => 'Agréments professionnels',
          'desc'  => 'Délivrance et gestion des licences pour les acteurs du numérique. Annuaire officiel des professionnels agréés ANRDI.',
          'href'  => URL_PRO,
        ],
        [
          'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
          'title' => 'Conformité RGPD',
          'desc'  => 'Accompagnement dans la mise en conformité RGPD. Traitement des demandes d\'exercice de droits des internautes.',
          'href'  => '/pages/services.php#rgpd',
        ],
        [
          'icon' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
          'title' => 'Signalements prioritaires',
          'desc'  => 'Canal dédié pour signaler les sites abusifs, contacter un opérateur ou demander la suppression d\'un contenu illicite.',
          'href'  => '/pages/contact.php',
        ],
        [
          'icon' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>',
          'title' => 'Actualités & Décisions',
          'desc'  => 'Décisions officielles, publications réglementaires et évolutions de l\'écosystème numérique français et européen.',
          'href'  => '/pages/actualites.php',
        ],
      ];
      foreach ($services as $s): ?>
      <article class="card">
        <div class="card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $s['icon'] ?></svg>
        </div>
        <h3 class="card-title"><?= htmlspecialchars($s['title'], ENT_QUOTES, 'UTF-8') ?></h3>
        <p class="card-desc"><?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars($s['href'], ENT_QUOTES, 'UTF-8') ?>"
           class="btn btn--ghost btn--sm" style="margin-top:1.375rem;">
          En savoir plus
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── ESPACES ───────────────────────────────────────────────── -->
<section class="section section--dark" aria-labelledby="espaces-title">
  <div class="container">
    <div class="section-header section-header--center">
      <span class="section-label">Rejoindre l'ANRDI</span>
      <h2 class="section-title" id="espaces-title">Un espace dédié<br>pour chaque acteur</h2>
      <p class="section-desc">
        Particulier, professionnel ou organisation — l'ANRDI dispose d'un espace
        adapté à vos besoins réglementaires.
      </p>
    </div>

    <div class="card-grid" style="max-width:740px;margin-inline:auto;">

      <div class="card card--dark">
        <div class="card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        </div>
        <h3 class="card-title">Espace Membre</h3>
        <p class="card-desc">
          Suivez vos dossiers, accédez aux décisions officielles et exercez vos droits RGPD.
        </p>
        <a href="/register.php" class="btn btn--primary" style="margin-top:1.5rem;">Créer un compte</a>
      </div>

      <div class="card card--dark" style="border-color:rgba(25,65,165,.4);">
        <div class="card-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        </div>
        <h3 class="card-title">Espace Professionnel</h3>
        <p class="card-desc">
          Déposez vos dossiers, obtenez vos agréments et figurez dans l'annuaire officiel ANRDI.
        </p>
        <a href="<?= URL_PRO ?>" class="btn btn--outline" style="margin-top:1.5rem;border-color:var(--c-blue-xl);color:var(--c-blue-xl);">
          Demander un accès Pro
        </a>
      </div>

    </div>
  </div>
</section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
