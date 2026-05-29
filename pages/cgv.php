<?php
/**
 * ANRDI — Conditions générales d’utilisation et de vente
 */

define('ANRDI_BOOTSTRAP', true);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

Security::setSecurityHeaders();
Auth::startSession();

$pageTitle = 'CGU / CGV — ANRDI';
$pageDescription = 'Conditions générales d’utilisation et de vente des services ANRDI.';

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <span class="section-label" style="margin-bottom:.875rem;">Conditions</span>
    <h1 class="page-hero-title">CGU / CGV</h1>
    <p class="page-hero-desc">
      Conditions générales d’utilisation du site anrdi.fr et conditions applicables aux services proposés par l’ANRDI.
    </p>
    <p style="margin-top:1rem;font-size:.8125rem;color:var(--c-gray-400);">
      Dernière mise à jour : <?= date('d/m/Y') ?>
    </p>
  </div>
</div>

<section class="section section--sm">
  <div class="container" style="max-width:960px;">

    <?php
    function cgu_section(string $id, string $title, string $content): void { ?>
      <div id="<?= $id ?>" style="margin-bottom:2.75rem;scroll-margin-top:calc(var(--header-h) + 1.5rem);">
        <h2 style="font-family:'Satoshi',sans-serif;font-size:1.25rem;font-weight:800;color:var(--c-navy);letter-spacing:-.025em;margin-bottom:1.125rem;padding-bottom:.75rem;border-bottom:1px solid var(--c-gray-100);">
          <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <div style="font-size:.9375rem;color:var(--c-gray-700);line-height:1.78;">
          <?= $content ?>
        </div>
      </div>
    <?php }

    $summary = [
      'objet' => '1. Objet',
      'editeur' => '2. Identification',
      'acceptation' => '3. Acceptation',
      'services' => '4. Services',
      'compte' => '5. Compte utilisateur',
      'usage' => '6. Usages interdits',
      'cgv' => '7. Conditions de vente',
      'prix' => '8. Prix et paiement',
      'retractation' => '9. Rétractation',
      'execution' => '10. Exécution des services',
      'responsabilite' => '11. Responsabilité',
      'propriete' => '12. Propriété intellectuelle',
      'donnees' => '13. Données personnelles',
      'litiges' => '14. Réclamations et médiation',
      'droit' => '15. Droit applicable',
      'contact' => '16. Contact'
    ];
    ?>

    <nav style="background:var(--c-surface);border:1px solid var(--c-gray-100);border-radius:var(--r-xl);padding:1.5rem;margin-bottom:2.5rem;" aria-label="Sommaire">
      <p style="font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--c-gray-400);margin-bottom:.875rem;">Sommaire</p>
      <ol style="list-style:none;display:flex;flex-direction:column;gap:.375rem;padding-left:0;margin:0;">
        <?php foreach ($summary as $anchor => $label): ?>
          <li>
            <a href="#<?= $anchor ?>" style="color:var(--c-blue);text-decoration:none;font-size:.9375rem;display:flex;align-items:center;gap:.5rem;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;flex-shrink:0;">
                <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
              </svg>
              <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ol>
    </nav>

    <?php
    cgu_section('objet', '1. Objet', '
      <p>Les présentes Conditions Générales d’Utilisation et de Vente, ci-après les « Conditions », ont pour objet de définir les règles applicables à l’accès au site <strong>anrdi.fr</strong>, à son utilisation, ainsi qu’aux services éventuellement proposés par l’ANRDI.</p>
      <p style="margin-top:1rem;">Elles s’appliquent à tout utilisateur, visiteur, membre, partenaire, professionnel ou organisme accédant au site ou utilisant les services proposés.</p>
      <p style="margin-top:1rem;">Certaines prestations, conventions, adhésions, dossiers ou dispositifs spécifiques peuvent faire l’objet de conditions particulières. En cas de contradiction, les conditions particulières prévalent sur les présentes Conditions.</p>
    ');

    cgu_section('editeur', '2. Identification de l’ANRDI', '
      <table style="width:100%;border-collapse:collapse;font-size:.9375rem;margin-top:1rem;">
        <tbody>
          <tr><td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);width:220px;">Dénomination</td><td style="padding:.625rem 0;font-weight:600;color:var(--c-navy);">Association Nationale de Régulation des Domaines et de l’Internet — ANRDI</td></tr>
          <tr style="border-top:1px solid var(--c-gray-100);"><td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">Forme juridique</td><td style="padding:.625rem 0;">Association régie par la loi du 1er juillet 1901</td></tr>
          <tr style="border-top:1px solid var(--c-gray-100);"><td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">Siège social</td><td style="padding:.625rem 0;">5 Allée de la Pommeraie<br>78520 Limay, France</td></tr>
          <tr style="border-top:1px solid var(--c-gray-100);"><td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">Email</td><td style="padding:.625rem 0;"><a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);">contact@anrdi.fr</a></td></tr>
          <tr style="border-top:1px solid var(--c-gray-100);"><td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">Téléphone</td><td style="padding:.625rem 0;"><a href="tel:0535543856" style="color:var(--c-blue);">05 35 54 38 56</a></td></tr>
        </tbody>
      </table>
    ');

    cgu_section('acceptation', '3. Acceptation des Conditions', '
      <p>L’accès au site et l’utilisation des services impliquent l’acceptation pleine et entière des présentes Conditions.</p>
      <p style="margin-top:1rem;">L’utilisateur reconnaît avoir pris connaissance des Conditions avant toute utilisation du site ou souscription à un service.</p>
      <p style="margin-top:1rem;">Si l’utilisateur n’accepte pas tout ou partie des Conditions, il doit cesser d’utiliser le site et les services concernés.</p>
    ');

    cgu_section('services', '4. Description des services', '
      <p>L’ANRDI peut proposer, selon les pages et dispositifs disponibles, des services d’information, d’accompagnement, de signalement, de suivi de dossiers, de mise en relation, d’adhésion, de contribution ou de participation à des actions liées à la gouvernance des noms de domaine et de l’internet.</p>
      <p style="margin-top:1rem;">Les services peuvent être gratuits ou payants. Lorsque le service est payant, les informations relatives au prix, aux modalités de paiement, aux conditions d’exécution et aux éventuelles restrictions sont indiquées avant validation.</p>
      <p style="margin-top:1rem;">L’ANRDI se réserve le droit de modifier, suspendre ou interrompre tout ou partie des services, notamment pour maintenance, sécurité, évolution technique ou motif légitime.</p>
    ');

    cgu_section('compte', '5. Compte utilisateur et accès sécurisé', '
      <p>Certains services peuvent nécessiter la création d’un compte utilisateur ou un accès authentifié.</p>
      <p style="margin-top:1rem;">L’utilisateur s’engage à fournir des informations exactes, complètes et à jour. Il est responsable de la confidentialité de ses identifiants et de toute activité réalisée depuis son compte.</p>
      <p style="margin-top:1rem;">Tout accès non autorisé, perte d’identifiant, suspicion de compromission ou usage frauduleux doit être signalé sans délai à l’ANRDI.</p>
      <p style="margin-top:1rem;">L’ANRDI peut suspendre ou désactiver un compte en cas d’usage abusif, frauduleux, contraire aux présentes Conditions, à la loi ou à la sécurité du site.</p>
    ');

    cgu_section('usage', '6. Obligations de l’utilisateur et usages interdits', '
      <p>L’utilisateur s’engage à utiliser le site et les services conformément aux lois et règlements applicables, aux présentes Conditions et aux usages loyaux de l’internet.</p>
      <p style="margin-top:1rem;">Sont notamment interdits :</p>
      <ul style="margin-top:.875rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.5rem;">
        <li>L’accès ou la tentative d’accès frauduleux à un système informatique ;</li>
        <li>L’altération, la suppression ou la perturbation du site ou de ses services ;</li>
        <li>L’envoi de contenus illicites, abusifs, diffamatoires, trompeurs ou malveillants ;</li>
        <li>L’usurpation d’identité ou de qualité ;</li>
        <li>La collecte automatisée non autorisée de données ;</li>
        <li>L’utilisation du site à des fins de spam, phishing, fraude ou cyberattaque ;</li>
        <li>La transmission de virus, scripts ou programmes malveillants ;</li>
        <li>Tout usage susceptible de porter atteinte aux droits de l’ANRDI ou de tiers.</li>
      </ul>
    ');

    cgu_section('cgv', '7. Conditions générales de vente', '
      <p>Les présentes dispositions s’appliquent aux services, prestations, adhésions, contributions, formations, accompagnements ou dispositifs payants éventuellement proposés par l’ANRDI.</p>
      <p style="margin-top:1rem;">Avant toute commande ou souscription, l’utilisateur reçoit les informations essentielles relatives au service : caractéristiques, prix, durée, modalités d’exécution, conditions de résiliation, restrictions éventuelles et moyens de paiement acceptés.</p>
      <p style="margin-top:1rem;">La validation d’une commande ou d’une souscription implique l’acceptation des présentes Conditions et, le cas échéant, des conditions particulières applicables.</p>
    ');

    cgu_section('prix', '8. Prix, facturation et paiement', '
      <p>Les prix applicables sont ceux affichés ou communiqués au moment de la commande ou de la souscription.</p>
      <p style="margin-top:1rem;">Sauf mention contraire, les prix sont indiqués en euros. Les éventuelles taxes, frais ou contributions applicables sont précisés avant validation.</p>
      <p style="margin-top:1rem;">Le paiement s’effectue selon les moyens proposés par l’ANRDI. La commande ou l’accès au service peut être suspendu tant que le paiement n’est pas intégralement reçu.</p>
      <p style="margin-top:1rem;">Pour les professionnels, tout retard de paiement peut entraîner l’application de pénalités de retard ainsi qu’une indemnité forfaitaire pour frais de recouvrement lorsque celle-ci est légalement applicable.</p>
    ');

    cgu_section('retractation', '9. Droit de rétractation', '
      <p>Lorsqu’un utilisateur agit en qualité de consommateur et qu’un service est souscrit à distance, il peut bénéficier d’un droit de rétractation de quatorze jours, sauf exception prévue par la loi.</p>
      <p style="margin-top:1rem;">Lorsque l’exécution du service commence avant la fin du délai de rétractation avec l’accord exprès de l’utilisateur, celui-ci reconnaît qu’un montant proportionnel au service déjà fourni pourra être dû.</p>
      <p style="margin-top:1rem;">Lorsque le service est pleinement exécuté avant la fin du délai de rétractation avec l’accord préalable et exprès de l’utilisateur et renoncement exprès à son droit de rétractation, ce droit peut ne plus être exercé.</p>
      <p style="margin-top:1rem;">Toute demande de rétractation peut être adressée à : <a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);">contact@anrdi.fr</a>.</p>
    ');

    cgu_section('execution', '10. Exécution, suspension et résiliation des services', '
      <p>Les modalités d’exécution des services sont précisées sur les pages concernées, dans les devis, conventions, confirmations ou conditions particulières applicables.</p>
      <p style="margin-top:1rem;">L’ANRDI peut suspendre ou refuser l’exécution d’un service en cas d’informations incomplètes, de comportement abusif, de suspicion de fraude, de non-paiement ou de non-respect des présentes Conditions.</p>
      <p style="margin-top:1rem;">L’utilisateur peut demander la résiliation d’un service selon les modalités prévues dans les conditions particulières ou, à défaut, par demande écrite adressée à l’ANRDI.</p>
    ');

    cgu_section('responsabilite', '11. Responsabilité', '
      <p>L’ANRDI s’efforce d’assurer l’exactitude, la disponibilité et la sécurité du site, sans garantir une disponibilité permanente ou l’absence totale d’erreurs.</p>
      <p style="margin-top:1rem;">L’ANRDI ne saurait être tenue responsable des dommages résultant d’une mauvaise utilisation du site, d’un usage frauduleux, d’un cas de force majeure, d’une interruption technique ou d’un fait imputable à un tiers.</p>
      <p style="margin-top:1rem;">Les informations publiées sur le site sont fournies à titre informatif et ne remplacent pas un conseil juridique, technique ou administratif personnalisé.</p>
      <p style="margin-top:1rem;">L’utilisateur demeure seul responsable des informations qu’il transmet et de l’usage qu’il fait des services.</p>
    ');

    cgu_section('propriete', '12. Propriété intellectuelle', '
      <p>Les contenus du site, notamment textes, graphismes, logos, interfaces, bases de données, éléments visuels, structure et code, sont protégés par le droit de la propriété intellectuelle.</p>
      <p style="margin-top:1rem;">Toute reproduction, représentation, adaptation, extraction, diffusion ou exploitation non autorisée est interdite.</p>
      <p style="margin-top:1rem;">L’utilisateur conserve les droits sur les contenus qu’il transmet, mais autorise l’ANRDI à les utiliser dans la mesure nécessaire au traitement de sa demande ou à l’exécution du service demandé.</p>
    ');

    cgu_section('donnees', '13. Données personnelles et cookies', '
      <p>Les données personnelles collectées dans le cadre de l’utilisation du site et des services sont traitées conformément au RGPD et à la loi Informatique et Libertés.</p>
      <p style="margin-top:1rem;">Les informations détaillées relatives aux finalités, bases légales, durées de conservation, droits des personnes, cookies et contacts RGPD sont disponibles dans la <a href="/pages/politique-confidentialite.php" style="color:var(--c-blue);">Politique de confidentialité</a>.</p>
      <p style="margin-top:1rem;">Certains cookies strictement nécessaires peuvent être déposés sans consentement. Les autres traceurs soumis à consentement ne sont déposés qu’après accord de l’utilisateur.</p>
    ');

    cgu_section('litiges', '14. Réclamations, médiation et règlement des litiges', '
      <p>En cas de difficulté, l’utilisateur est invité à contacter l’ANRDI afin de rechercher une solution amiable :</p>
      <p style="margin-top:1rem;"><a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);">contact@anrdi.fr</a></p>
      <p style="margin-top:1rem;">Lorsqu’il agit en qualité de consommateur, l’utilisateur peut, après réclamation écrite préalable restée infructueuse, recourir gratuitement à un médiateur de la consommation compétent.</p>
      <p style="margin-top:1rem;"><strong>Médiateur de la consommation :</strong> à compléter avec le médiateur désigné par l’ANRDI.</p>
      <p style="margin-top:1rem;">À défaut de résolution amiable, le litige pourra être porté devant les juridictions compétentes.</p>
    ');

    cgu_section('droit', '15. Droit applicable', '
      <p>Les présentes Conditions sont régies par le droit français.</p>
      <p style="margin-top:1rem;">Toute contestation relative à leur validité, interprétation ou exécution relève des juridictions françaises compétentes, sous réserve des règles impératives applicables aux consommateurs.</p>
    ');

    cgu_section('contact', '16. Contact', '
      <p>Pour toute question relative aux présentes Conditions :</p>
      <ul style="margin-top:.875rem;list-style:none;padding-left:0;display:flex;flex-direction:column;gap:.625rem;">
        <li>📧 <a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);text-decoration:none;">contact@anrdi.fr</a></li>
        <li>☎️ <a href="tel:0535543856" style="color:var(--c-blue);text-decoration:none;">05 35 54 38 56</a></li>
        <li>📍 5 Allée de la Pommeraie, 78520 Limay, France</li>
      </ul>
      <p style="margin-top:1.5rem;"><a href="/pages/contact.php" class="btn btn--primary btn--sm">Formulaire de contact</a></p>
    ');
    ?>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>