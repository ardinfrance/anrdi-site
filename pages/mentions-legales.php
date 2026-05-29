<?php
/**
 * ANRDI — Mentions légales
 * Version institutionnelle complète
 */

define('ANRDI_BOOTSTRAP', true);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

Security::setSecurityHeaders();
Auth::startSession();

$pageTitle       = 'Mentions légales — ANRDI';
$pageDescription = 'Mentions légales officielles de l’ANRDI, Association Nationale de Régulation des Domaines et de l’Internet.';

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">
    <span class="section-label" style="margin-bottom:.875rem;">
      Informations légales
    </span>

    <h1 class="page-hero-title">
      Mentions légales
    </h1>

    <p class="page-hero-desc">
      Informations légales relatives au site anrdi.fr et à l’Association Nationale de Régulation des Domaines et de l’Internet.
    </p>

    <p style="margin-top:1rem;font-size:.8125rem;color:var(--c-gray-400);">
      Dernière mise à jour : <?= date('d/m/Y') ?>
    </p>
  </div>
</div>

<section class="section section--sm">
  <div class="container" style="max-width:920px;">

    <!-- SOMMAIRE -->
    <nav
      style="
        background:var(--c-surface);
        border:1px solid var(--c-gray-100);
        border-radius:var(--r-xl);
        padding:1.5rem;
        margin-bottom:2.5rem;
      "
      aria-label="Sommaire"
    >

      <p
        style="
          font-size:.75rem;
          font-weight:700;
          letter-spacing:.1em;
          text-transform:uppercase;
          color:var(--c-gray-400);
          margin-bottom:.875rem;
        "
      >
        Sommaire
      </p>

      <ol
        style="
          list-style:none;
          display:flex;
          flex-direction:column;
          gap:.375rem;
          padding-left:0;
          margin:0;
        "
      >

        <?php
        $sections = [
          'editeur'        => '1. Éditeur du site',
          'hebergeur'      => '2. Hébergement',
          'pi'             => '3. Propriété intellectuelle',
          'responsabilite' => '4. Responsabilité',
          'donnees'        => '5. Données personnelles et cookies',
          'securite'       => '6. Sécurité informatique',
          'accessibilite'  => '7. Accessibilité numérique',
          'droit'          => '8. Droit applicable',
          'contact-ml'     => '9. Contact'
        ];

        foreach ($sections as $anchor => $label):
        ?>

        <li>
          <a
            href="#<?= $anchor ?>"
            style="
              color:var(--c-blue);
              text-decoration:none;
              font-size:.9375rem;
              display:flex;
              align-items:center;
              gap:.5rem;
            "
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              style="width:13px;height:13px;flex-shrink:0;"
            >
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>

            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
          </a>
        </li>

        <?php endforeach; ?>

      </ol>
    </nav>

<?php

function ml_section(string $id, string $title, string $content): void
{
?>

<div
  id="<?= $id ?>"
  style="
    margin-bottom:3rem;
    scroll-margin-top:calc(var(--header-h) + 1.5rem);
  "
>

  <h2
    style="
      font-family:'Satoshi',sans-serif;
      font-size:1.3rem;
      font-weight:800;
      color:var(--c-navy);
      letter-spacing:-.025em;
      margin-bottom:1.25rem;
      padding-bottom:.75rem;
      border-bottom:1px solid var(--c-gray-100);
    "
  >
    <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
  </h2>

  <div
    style="
      font-size:.9375rem;
      color:var(--c-gray-700);
      line-height:1.8;
    "
  >
    <?= $content ?>
  </div>

</div>

<?php
}

/**
 * 1. ÉDITEUR
 */
ml_section('editeur', '1. Éditeur du site', '

<p>
Le site <strong>anrdi.fr</strong> est édité par :
</p>

<table
  style="
    width:100%;
    border-collapse:collapse;
    font-size:.9375rem;
    margin-top:1rem;
  "
>

<tbody>

<tr>
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);width:220px;">
    Dénomination
  </td>

  <td style="padding:.625rem 0;font-weight:700;color:var(--c-navy);">
    Association Nationale de Régulation des Domaines et de l’Internet (ANRDI)
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Forme juridique
  </td>

  <td style="padding:.625rem 0;">
    Association régie par la loi du 1er juillet 1901
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    RNA
  </td>

  <td style="padding:.625rem 0;">
    W781010834
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    SIREN
  </td>

  <td style="padding:.625rem 0;">
    105 588 388
  </td>
</tr> 

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    SIRET
  </td>

  <td style="padding:.625rem 0;">
    105 588 388 00014
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Siège social
  </td>

  <td style="padding:.625rem 0;">
    5 Allée de la Pommeraie<br>
    78520 Limay — France
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Téléphone
  </td>

  <td style="padding:.625rem 0;">
    <a href="tel:0535543856" style="color:var(--c-blue);text-decoration:none;">
      05 35 54 38 56
    </a>
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Code NAF/APE
  </td>

  <td style="padding:.625rem 0;">
    <a href="https://annuaire-entreprises.data.gouv.fr/entreprise/association-nationale-de-regulation-des-domaines-et-de-l-internet-105588388" style="color:var(--c-blue);text-decoration:none;">
      Conseil en systèmes et logiciels informatiques (62.02A)
    </a>
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Email
  </td>

  <td style="padding:.625rem 0;">
    <a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);text-decoration:none;">
      contact@anrdi.fr
    </a>
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Directeur de publication
  </td>

  <td style="padding:.625rem 0;">
    Lyam Bamba, Président de l’ANRDI
  </td>
</tr>

</tbody>
</table>

');

/**
 * 2. HÉBERGEMENT
 */
ml_section('hebergeur', '2. Hébergement', '

<p>
Le site est hébergé sur une infrastructure sécurisée localisée au sein de l’Union Européenne.
</p>

<table
  style="
    width:100%;
    border-collapse:collapse;
    font-size:.9375rem;
    margin-top:1rem;
  "
>

<tbody>

<tr>
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);width:220px;">
    Infrastructure
  </td>

  <td style="padding:.625rem 0;">
    Serveur dédié sécurisé
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Système
  </td>

  <td style="padding:.625rem 0;">
    Debian 13 / Apache 2.4 / PHP 8.1+
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Administration
  </td>

  <td style="padding:.625rem 0;">
    Plesk 18
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.625rem 1rem .625rem 0;color:var(--c-gray-500);">
    Localisation des données
  </td>

  <td style="padding:.625rem 0;">
    Union Européenne
  </td>
</tr>

</tbody>
</table>

');

/**
 * 3. PROPRIÉTÉ INTELLECTUELLE
 */
ml_section('pi', '3. Propriété intellectuelle', '

<p>
L’ensemble des contenus présents sur le site anrdi.fr, incluant notamment :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;">
  <li>Textes ;</li>
  <li>Graphismes ;</li>
  <li>Logos ;</li>
  <li>Icônes ;</li>
  <li>Illustrations ;</li>
  <li>Architecture technique ;</li>
  <li>Code source ;</li>
  <li>Éléments audiovisuels ;</li>
</ul>

<p style="margin-top:1rem;">
sont protégés par les dispositions du Code de la propriété intellectuelle.
</p>

<p style="margin-top:1rem;">
Toute reproduction, représentation, adaptation, diffusion ou exploitation, totale ou partielle, sans autorisation écrite préalable de l’ANRDI est strictement interdite.
</p>

<p style="margin-top:1rem;">
Toute violation pourra entraîner des poursuites civiles et pénales.
</p>

');

/**
 * 4. RESPONSABILITÉ
 */
ml_section('responsabilite', '4. Responsabilité', '

<p>
L’ANRDI met tout en œuvre afin d’assurer l’exactitude et la mise à jour des informations publiées sur le site.
</p>

<p style="margin-top:1rem;">
Toutefois, l’ANRDI ne saurait être tenue responsable :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.5rem;">
  <li>Des erreurs ou omissions éventuelles ;</li>
  <li>D’une interruption temporaire du site ;</li>
  <li>D’un dysfonctionnement technique ;</li>
  <li>D’un dommage direct ou indirect résultant de l’utilisation du site ;</li>
  <li>D’une intrusion frauduleuse ou cyberattaque.</li>
</ul>

<p style="margin-top:1rem;">
Les liens hypertextes présents sur le site peuvent rediriger vers des contenus externes dont l’ANRDI ne maîtrise ni le contenu ni la disponibilité.
</p>

');

/**
 * 5. DONNÉES PERSONNELLES
 */
ml_section('donnees', '5. Données personnelles et cookies', '

<p>
Les traitements de données personnelles réalisés sur le site sont conformes :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;">
  <li>Au Règlement Général sur la Protection des Données (RGPD) ;</li>
  <li>À la loi Informatique et Libertés modifiée ;</li>
  <li>Aux recommandations de la CNIL.</li>
</ul>

<p style="margin-top:1rem;">
Les traitements reposent notamment sur :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;">
  <li>Le consentement des utilisateurs ;</li>
  <li>L’intérêt légitime de l’ANRDI ;</li>
  <li>Les obligations légales applicables.</li>
</ul>

<p style="margin-top:1rem;">
Les données sont conservées pendant une durée strictement nécessaire aux finalités pour lesquelles elles sont traitées.
</p>

<p style="margin-top:1rem;">
Le site peut utiliser :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;">
  <li>Des cookies techniques ;</li>
  <li>Des cookies de sécurité ;</li>
  <li>Des cookies de mesure d’audience.</li>
</ul>

<p style="margin-top:1rem;">
L’utilisateur peut configurer ses préférences via son navigateur ou le gestionnaire de consentement.
</p>

<p style="margin-top:1rem;">
Pour toute question relative à vos données :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;">
  <li>
    Contact général :
    <a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);">
      contact@anrdi.fr
    </a>
  </li>

  <li>
    DPO :
    <a href="mailto:dpo@anrdi.fr" style="color:var(--c-blue);">
      dpo@anrdi.fr
    </a>
  </li>
</ul>

<p style="margin-top:1rem;">
Vous disposez également du droit de saisir la CNIL :
<a href="https://www.cnil.fr" target="_blank" rel="noopener noreferrer" style="color:var(--c-blue);">
  www.cnil.fr
</a>
</p>

');

/**
 * 6. SÉCURITÉ
 */
ml_section('securite', '6. Sécurité informatique', '

<p>
L’ANRDI met en œuvre des mesures techniques, organisationnelles et de cybersécurité afin de garantir :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;">
  <li>La confidentialité des données ;</li>
  <li>L’intégrité des systèmes ;</li>
  <li>La disponibilité des services ;</li>
  <li>La protection contre les accès non autorisés.</li>
</ul>

<p style="margin-top:1rem;">
Toute tentative d’intrusion, d’altération ou d’atteinte au fonctionnement du site pourra faire l’objet de poursuites judiciaires conformément aux articles 323-1 et suivants du Code pénal.
</p>

<p style="margin-top:1rem;">
Les vulnérabilités de sécurité peuvent être signalées à :
</p>

<p style="margin-top:1rem;">
<a href="mailto:security@anrdi.fr" style="color:var(--c-blue);">
  security@anrdi.fr
</a>
</p>

');

/**
 * 7. ACCESSIBILITÉ
 */
ml_section('accessibilite', '7. Accessibilité numérique', '

<p>
L’ANRDI s’efforce de rendre son site accessible conformément aux recommandations du Référentiel Général d’Amélioration de l’Accessibilité (RGAA).
</p>

<p style="margin-top:1rem;">
Malgré le soin apporté au développement du site, certains contenus peuvent présenter des limitations d’accessibilité.
</p>

<p style="margin-top:1rem;">
Toute difficulté peut être signalée à :
</p>

<p style="margin-top:1rem;">
<a href="mailto:accessibilite@anrdi.fr" style="color:var(--c-blue);">
  accessibilite@anrdi.fr
</a>
</p>

');

/**
 * 8. DROIT APPLICABLE
 */
ml_section('droit', '8. Droit applicable et juridiction compétente', '

<p>
Les présentes mentions légales sont soumises au droit français.
</p>

<p style="margin-top:1rem;">
En cas de litige, une solution amiable sera recherchée prioritairement.
</p>

<p style="margin-top:1rem;">
À défaut d’accord amiable, les juridictions françaises seront seules compétentes.
</p>

');

/**
 * 9. CONTACT
 */
ml_section('contact-ml', '9. Contact', '

<p>
Pour toute question relative aux présentes mentions légales :
</p>

<ul
  style="
    margin-top:1.25rem;
    list-style:none;
    padding-left:0;
    display:flex;
    flex-direction:column;
    gap:.75rem;
  "
>

<li>
  <i class="fa-solid fa-envelope"></i>
  <a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);text-decoration:none;">
    contact@anrdi.fr
  </a>
</li>

<li>
  <i class="fa-solid fa-phone"></i>
  <a href="tel:0535543856" style="color:var(--c-blue);text-decoration:none;">
    05 35 54 38 56
  </a>
</li>

<li>
  <i class="fa-solid fa-location-dot"></i>
  5 Allée de la Pommeraie — 78520 Limay — France
</li>

</ul>

<p style="margin-top:1.5rem;">
  <a href="/pages/contact.php" class="btn btn--primary btn--sm">
    Formulaire de contact
  </a>
</p>

');

?>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>