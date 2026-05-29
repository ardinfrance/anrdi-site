<?php
/**
 * ANRDI — Politique de confidentialité
 * Version complète RGPD / CNIL
 */

define('ANRDI_BOOTSTRAP', true);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';

Security::setSecurityHeaders();
Auth::startSession();

$pageTitle       = 'Politique de confidentialité — ANRDI';
$pageDescription = 'Politique de confidentialité et protection des données personnelles de l’ANRDI.';

include __DIR__ . '/../includes/header.php';
?>

<div class="page-hero">
  <div class="container">

    <span class="section-label" style="margin-bottom:.875rem;">
      Protection des données
    </span>

    <h1 class="page-hero-title">
      Politique de confidentialité
    </h1>

    <p class="page-hero-desc">
      Informations relatives à la collecte, au traitement, à la conservation et à la protection des données personnelles réalisées par l’ANRDI.
    </p>

    <p style="margin-top:1rem;font-size:.8125rem;color:var(--c-gray-400);">
      Dernière mise à jour : <?= date('d/m/Y') ?>
    </p>

  </div>
</div>

<section class="section section--sm">
  <div class="container" style="max-width:960px;">

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
          'responsable'     => '1. Responsable du traitement',
          'collecte'        => '2. Données collectées',
          'finalites'       => '3. Finalités des traitements',
          'bases'           => '4. Bases légales',
          'destinataires'   => '5. Destinataires des données',
          'conservation'    => '6. Conservation des données',
          'cookies'         => '7. Cookies et traceurs',
          'securite'        => '8. Sécurité des données',
          'droits'          => '9. Vos droits',
          'transferts'      => '10. Transferts hors UE',
          'mineurs'         => '11. Données des mineurs',
          'modification'    => '12. Modification de la politique',
          'contact-rgpd'    => '13. Contact RGPD'
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

function privacy_section(string $id, string $title, string $content): void
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
 * RESPONSABLE
 */
privacy_section('responsable', '1. Responsable du traitement', '

<p>
La présente politique de confidentialité décrit la manière dont l’Association Nationale de Régulation des Domaines et de l’Internet (ANRDI) collecte, utilise, protège et conserve les données personnelles des utilisateurs du site anrdi.fr.
</p>

<p style="margin-top:1rem;">
Le responsable du traitement est :
</p>

<table
  style="
    width:100%;
    border-collapse:collapse;
    margin-top:1rem;
    font-size:.9375rem;
  "
>

<tbody>

<tr>
  <td style="padding:.7rem 1rem .7rem 0;color:var(--c-gray-500);width:220px;">
    Dénomination
  </td>

  <td style="padding:.7rem 0;font-weight:700;color:var(--c-navy);">
    Association Nationale de Régulation des Domaines et de l’Internet (ANRDI)
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.7rem 1rem .7rem 0;color:var(--c-gray-500);">
    Adresse
  </td>

  <td style="padding:.7rem 0;">
    5 Allée de la Pommeraie, 78520 Limay, France
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.7rem 1rem .7rem 0;color:var(--c-gray-500);">
    Email
  </td>

  <td style="padding:.7rem 0;">
    <a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);">
      contact@anrdi.fr
    </a>
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.7rem 1rem .7rem 0;color:var(--c-gray-500);">
    Délégué à la protection des données
  </td>

  <td style="padding:.7rem 0;">
    <a href="mailto:dpo@anrdi.fr" style="color:var(--c-blue);">
      dpo@anrdi.fr
    </a>
  </td>
</tr>

</tbody>
</table>

');

/**
 * COLLECTE
 */
privacy_section('collecte', '2. Données collectées', '

<p>
Selon les services utilisés, l’ANRDI peut collecter les catégories de données suivantes :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>
Données d’identification : nom, prénom, organisme, fonction ;
</li>

<li>
Coordonnées : adresse email, numéro de téléphone, adresse postale ;
</li>

<li>
Données de connexion : adresse IP, journaux techniques, navigateur, appareil, système d’exploitation ;
</li>

<li>
Informations transmises via les formulaires : messages, demandes, pièces jointes ;
</li>

<li>
Données liées à la sécurité et à la prévention des abus ;
</li>

<li>
Préférences relatives aux cookies et consentements.
</li>

</ul>

');

/**
 * FINALITÉS
 */
privacy_section('finalites', '3. Finalités des traitements', '

<p>
Les données personnelles sont collectées et traitées pour les finalités suivantes :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>Répondre aux demandes effectuées via les formulaires ;</li>

<li>Assurer le fonctionnement et la sécurité du site ;</li>

<li>Prévenir les activités frauduleuses ou malveillantes ;</li>

<li>Gérer les relations institutionnelles et administratives ;</li>

<li>Assurer le suivi des demandes et échanges ;</li>

<li>Respecter les obligations légales et réglementaires ;</li>

<li>Produire des statistiques anonymisées de fréquentation.</li>

</ul>

');

/**
 * BASES
 */
privacy_section('bases', '4. Bases légales des traitements', '

<p>
Conformément au Règlement Général sur la Protection des Données (RGPD), les traitements réalisés reposent notamment sur :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>Le consentement de l’utilisateur ;</li>

<li>L’intérêt légitime de l’ANRDI ;</li>

<li>L’exécution de mesures précontractuelles ou contractuelles ;</li>

<li>Le respect d’obligations légales ;</li>

<li>La protection de la sécurité des systèmes et réseaux.</li>

</ul>

');

/**
 * DESTINATAIRES
 */
privacy_section('destinataires', '5. Destinataires des données', '

<p>
Les données personnelles sont accessibles uniquement aux personnes habilitées dans le cadre de leurs missions.
</p>

<p style="margin-top:1rem;">
Peuvent notamment être destinataires des données :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>Les services internes de l’ANRDI ;</li>

<li>Les prestataires techniques et hébergeurs ;</li>

<li>Les administrateurs systèmes et sécurité ;</li>

<li>Les autorités administratives ou judiciaires lorsque la loi l’exige.</li>

</ul>

<p style="margin-top:1rem;">
Les données ne sont ni vendues ni cédées à des tiers à des fins commerciales.
</p>

');

/**
 * CONSERVATION
 */
privacy_section('conservation', '6. Conservation des données', '

<p>
Les données sont conservées pendant une durée strictement nécessaire aux finalités pour lesquelles elles sont traitées.
</p>

<table
  style="
    width:100%;
    border-collapse:collapse;
    margin-top:1rem;
    font-size:.9375rem;
  "
>

<thead>
<tr style="border-bottom:1px solid var(--c-gray-200);">
  <th style="text-align:left;padding:.75rem 1rem .75rem 0;color:var(--c-navy);">
    Type de données
  </th>

  <th style="text-align:left;padding:.75rem 0;color:var(--c-navy);">
    Durée indicative
  </th>
</tr>
</thead>

<tbody>

<tr>
  <td style="padding:.75rem 1rem .75rem 0;">
    Données de contact
  </td>

  <td style="padding:.75rem 0;">
    3 ans après le dernier échange
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.75rem 1rem .75rem 0;">
    Journaux techniques
  </td>

  <td style="padding:.75rem 0;">
    6 à 12 mois
  </td>
</tr>

<tr style="border-top:1px solid var(--c-gray-100);">
  <td style="padding:.75rem 1rem .75rem 0;">
    Cookies
  </td>

  <td style="padding:.75rem 0;">
    13 mois maximum
  </td>
</tr>

</tbody>
</table>

');

/**
 * COOKIES
 */
privacy_section('cookies', '7. Cookies et traceurs', '

<p>
Le site peut utiliser des cookies et technologies similaires afin :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>D’assurer le fonctionnement du site ;</li>

<li>De sécuriser les accès et les sessions ;</li>

<li>D’améliorer les performances ;</li>

<li>D’établir des statistiques de fréquentation.</li>

</ul>

<p style="margin-top:1rem;">
Certains cookies sont strictement nécessaires au fonctionnement du site et ne nécessitent pas de consentement.
</p>

<p style="margin-top:1rem;">
Les autres cookies soumis à consentement ne sont déposés qu’après acceptation explicite de l’utilisateur.
</p>

<p style="margin-top:1rem;">
L’utilisateur peut modifier ses préférences à tout moment depuis le gestionnaire de consentement ou les paramètres de son navigateur.
</p>

');

/**
 * SÉCURITÉ
 */
privacy_section('securite', '8. Sécurité des données', '

<p>
L’ANRDI met en œuvre des mesures techniques et organisationnelles appropriées afin d’assurer un niveau de sécurité adapté aux risques.
</p>

<p style="margin-top:1rem;">
Ces mesures peuvent inclure :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>Le chiffrement des échanges ;</li>

<li>La sécurisation des accès ;</li>

<li>La journalisation des événements ;</li>

<li>Les sauvegardes régulières ;</li>

<li>Les mises à jour de sécurité ;</li>

<li>La surveillance des activités suspectes.</li>

</ul>

<p style="margin-top:1rem;">
Malgré toutes les précautions raisonnables mises en œuvre, aucun système informatique ne peut garantir une sécurité absolue.
</p>

');

/**
 * DROITS
 */
privacy_section('droits', '9. Vos droits', '

<p>
Conformément au RGPD et à la loi Informatique et Libertés, vous disposez notamment des droits suivants :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>Droit d’accès ;</li>

<li>Droit de rectification ;</li>

<li>Droit d’effacement ;</li>

<li>Droit à la limitation du traitement ;</li>

<li>Droit d’opposition ;</li>

<li>Droit à la portabilité des données ;</li>

<li>Droit de retirer votre consentement à tout moment ;</li>

<li>Droit de définir des directives relatives au sort de vos données après votre décès.</li>

</ul>

<p style="margin-top:1rem;">
Pour exercer vos droits :
</p>

<ul style="margin-top:1rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:.55rem;">

<li>
Par email :
<a href="mailto:dpo@anrdi.fr" style="color:var(--c-blue);">
dpo@anrdi.fr
</a>
</li>

<li>
Via la page :
<a href="/pages/contact.php" style="color:var(--c-blue);">
contact
</a>
</li>

</ul>

<p style="margin-top:1rem;">
Vous disposez également du droit d’introduire une réclamation auprès de la CNIL :
<a
  href="https://www.cnil.fr"
  target="_blank"
  rel="noopener noreferrer"
  style="color:var(--c-blue);"
>
www.cnil.fr
</a>
</p>

');

/**
 * TRANSFERTS
 */
privacy_section('transferts', '10. Transferts hors Union européenne', '

<p>
L’ANRDI privilégie l’hébergement et le traitement des données au sein de l’Union européenne.
</p>

<p style="margin-top:1rem;">
En cas de transfert de données hors Union européenne, des garanties appropriées conformes au RGPD seront mises en place, notamment des clauses contractuelles types ou des mécanismes reconnus par la Commission européenne.
</p>

');

/**
 * MINEURS
 */
privacy_section('mineurs', '11. Données des mineurs', '

<p>
Le site n’est pas spécifiquement destiné aux mineurs.
</p>

<p style="margin-top:1rem;">
Si des données relatives à un mineur étaient collectées par inadvertance, le représentant légal peut demander leur suppression en contactant l’ANRDI.
</p>

');

/**
 * MODIFICATION
 */
privacy_section('modification', '12. Modification de la politique', '

<p>
La présente politique de confidentialité peut être modifiée afin de tenir compte des évolutions légales, réglementaires, techniques ou organisationnelles.
</p>

<p style="margin-top:1rem;">
La version applicable est celle publiée sur le site à la date de consultation.
</p>

');

/**
 * CONTACT
 */
privacy_section('contact-rgpd', '13. Contact RGPD', '

<p>
Pour toute question relative à la présente politique de confidentialité ou au traitement des données personnelles :
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
📧
<a href="mailto:dpo@anrdi.fr" style="color:var(--c-blue);text-decoration:none;">
dpo@anrdi.fr
</a>
</li>

<li>
📧
<a href="mailto:contact@anrdi.fr" style="color:var(--c-blue);text-decoration:none;">
contact@anrdi.fr
</a>
</li>

<li>
📍
5 Allée de la Pommeraie — 78520 Limay — France
</li>

</ul>

');

?>

  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>