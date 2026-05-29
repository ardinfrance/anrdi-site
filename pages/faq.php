<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
Security::setSecurityHeaders(); Auth::startSession();
$pageTitle='FAQ — ANRDI'; $pageDescription='Questions fréquentes — ANRDI.';
$faqs=[
  ["Qu'est-ce que l'ANRDI ?","L'ANRDI est l'autorité nationale de référence pour la régulation des noms de domaine et de l'internet en France."],
  ["Comment déposer un dossier ?","Créez un compte membre ou professionnel puis accédez à votre espace pour déposer votre dossier."],
  ["Combien de temps pour traiter un dossier ?","Les dossiers standards sont traités sous 10 jours ouvrés. Les urgences sont traitées prioritairement."],
  ["Comment exercer mes droits RGPD ?","Depuis votre espace membre ou via notre formulaire de contact. Délai de réponse légal : 30 jours."],
  ["L'espace professionnel est-il payant ?","L'accès est gratuit. Certaines licences ou agréments peuvent entraîner des frais réglementés."],
  ["Mon dossier a été rejeté, que faire ?","Un email explicatif vous est envoyé. Vous pouvez corriger et soumettre à nouveau, ou contacter notre équipe."],
  ["J'ai reçu une \"notification de violation\", que faire ?","Consultez les détails de la notification dans votre espace membre et suivez les instructions pour remédier à la situation."],
  ];
include __DIR__.'/../includes/header.php';
?>
<section class="section"><div class="container" style="max-width:720px;">
<div class="section-header"><span class="section-label">Aide</span><h1 class="section-title">Questions fréquentes</h1></div>
<?php foreach($faqs as $f): ?>
<details style="border:1px solid var(--c-gray-200);border-radius:var(--r-lg);margin-bottom:1rem;overflow:hidden;">
<summary style="padding:1.25rem 1.5rem;font-weight:700;color:var(--c-navy);cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;"><?= htmlspecialchars($f[0],ENT_QUOTES,'UTF-8') ?> <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg></summary>
<div style="padding:0 1.5rem 1.25rem;color:var(--c-gray-600);font-size:.95rem;line-height:1.7;"><?= htmlspecialchars($f[1],ENT_QUOTES,'UTF-8') ?></div>
</details>
<?php endforeach; ?>
<div class="text-center" style="margin-top:2rem;"><a href="/pages/contact.php" class="btn btn--primary">Posez votre question</a></div>
</div></section>
<?php include __DIR__.'/../includes/footer.php'; ?>
