<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
Security::setSecurityHeaders();
Auth::startSession();
$pageTitle = 'Services - ANRDI';
$pageDescription = 'Missions, dispositifs de régulation et accompagnement proposés par l’ANRDI.';
include __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">Nos missions</span>
      <h1 class="section-title">Services et responsabilités de l’ANRDI</h1>
      <p class="section-desc">Les parcours du site pointent désormais vers une page réelle pour chaque grand domaine d’intervention.</p>
    </div>

    <div class="card-grid">
      <article class="card" id="domaines">
        <span class="section-label">Régulation</span>
        <h2 class="card-title" style="margin-top:1rem;">Régulation des domaines</h2>
        <p class="card-desc">Supervision des noms de domaine, gestion des différends, accompagnement des titulaires et protection de l’espace numérique français.</p>
      </article>
      <article class="card" id="protection">
        <span class="section-label">Protection</span>
        <h2 class="card-title" style="margin-top:1rem;">Protection des internautes</h2>
        <p class="card-desc">Traitement prioritaire des signalements, coordination opérationnelle et réduction des usages frauduleux ou abusifs.</p>
      </article>
      <article class="card" id="agrement">
        <span class="section-label">Professionnels</span>
        <h2 class="card-title" style="margin-top:1rem;">Agréments professionnels</h2>
        <p class="card-desc">Instruction des demandes, suivi administratif et publication des acteurs référencés par l’ANRDI.</p>
      </article>
      <article class="card" id="rgpd">
        <span class="section-label">Conformité</span>
        <h2 class="card-title" style="margin-top:1rem;">Conformité RGPD</h2>
        <p class="card-desc">Information, orientation et gestion des demandes liées aux données personnelles et à l’exercice des droits.</p>
      </article>
    </div>

    <div class="text-center" style="margin-top:2.5rem;">
      <a href="/pages/contact.php" class="btn btn--primary btn--lg">Contacter l’ANRDI</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
