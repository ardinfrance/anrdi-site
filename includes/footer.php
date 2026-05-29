<?php
/**
 * ANRDI — Footer global
 */
if (!defined('ANRDI_BOOTSTRAP')) { http_response_code(403); die(); }
$yr = date('Y');
$footerLogoPath = (string) LOGO_FOOTER;
?>



<footer class="site-footer" role="contentinfo">
<div class="footer-inner container">
<div class="footer-grid">

    <!-- Marque -->
    <div class="footer-brand">
        <a href="/" class="footer-logo" aria-label="ANRDI — Accueil">
            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . LOGO_FOOTER)): ?>
            <img src="<?= htmlspecialchars($footerLogoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Logo ANRDI" class="footer-logo-img" width="200" height="100" loading="eager">
            <?php else: ?>
            <svg viewBox="0 0 200 56" fill="none" xmlns="http://www.w3.org/2000/svg" style="height:36px;width:auto;">
                <rect x="0"  y="0" width="8" height="56" rx="2" fill="#2563EB"/>
                <rect x="12" y="0" width="8" height="56" rx="2" fill="#3B82F6"/>
                <text x="28" y="38" font-family="'Satoshi',sans-serif" font-size="28" font-weight="700" fill="#FFFFFF" letter-spacing="-0.5">ANRDI</text>
            </svg>
            <?php endif; ?>
        </a>
        <p class="footer-tagline">Réguler aujourd’hui, protéger le numérique de demain</p>
        <div class="footer-social" aria-label="Réseaux sociaux">
            <a href="https://x.com/ardinfrance" target="_blank" class="social-link" aria-label="ANRDI sur X" rel="noopener noreferrer">
                <i class="fa-brands fa-x-twitter" aria-hidden="true"></i>
            </a>
            <a href="https://github.com/ardinfrance" target="_blank" class="social-link" aria-label="ANRDI sur GitHub" rel="noopener noreferrer">
                <i class="fa-brands fa-github" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <!-- L'ANRDI -->
    <div class="footer-col">
        <h3 class="footer-col-title">L'ANRDI</h3>
        <ul class="footer-links" role="list">
            <li><a href="/">Accueil</a></li>
            <li><a href="/pages/services.php">Nos missions</a></li>
            <li><a href="/pages/actualites.php">Actualités</a></li>
            <li><a href="/pages/faq.php">FAQ</a></li>
            <li><a href="/pages/contact.php">Contact</a></li>
            <li><a href="<?= URL_STATUS ?>">État des services</a></li>
        </ul>
    </div>

    <!-- Espaces -->
    <div class="footer-col">
        <h3 class="footer-col-title">Espaces</h3>
        <ul class="footer-links" role="list">
            <li><a href="/membre/dashboard.php"><i class="fa-regular fa-user" aria-hidden="true"></i> Espace Membre</a></li>
            <li><a href="<?= URL_PRO ?>"><i class="fa-regular fa-building" aria-hidden="true"></i> Espace Pro</a></li>
            <li><a href="<?= URL_API ?>">API publique</a></li>
            <li><a href="/register.php">Créer un compte</a></li>
        </ul>
    </div>

    <!-- Légal -->
    <div class="footer-col">
        <h3 class="footer-col-title">Légal & RGPD</h3>
        <ul class="footer-links" role="list">
            <li><a href="/pages/mentions-legales.php">Mentions légales</a></li>
            <li><a href="/pages/politique-confidentialite.php">Confidentialité</a></li>
            <li><a href="/pages/cgv.php">CGU / CGV</a></li>
            <li>
                <button class="btn-link" id="rgpd-consent-btn" aria-label="Gérer mes préférences de cookies">
                    Cookies
                </button>
            </li>
            <li><a href="/pages/politique-confidentialite.php#droits">Exercer mes droits</a></li>
        </ul>
    </div>

    <!-- Contact -->
    <div class="footer-col">
        <h3 class="footer-col-title">Contact</h3>
        <ul class="footer-contact" role="list">
            <li>
                <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                <a href="https://maps.app.goo.gl/8nDr3PfKzjdsPr8F6" target="_blank" rel="noopener noreferrer">
                    5 Allée de La Pommeraie<br>78520, Limay
                </a>
            </li>
            <li>
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                <a href="tel:0535543856">05 35 54 38 56</a>
            </li>
            <li>
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <a href="mailto:contact@anrdi.fr">contact@anrdi.fr</a>
            </li>
        </ul>
    </div>

</div><!-- /.footer-grid -->

<!-- Barre du bas -->
<div class="footer-bottom">
    <p class="footer-copyright">
        &copy; <?= $yr ?> ANRDI &mdash; Association Nationale de Régulation des Domaines et de l'Internet. Tous droits réservés.
    </p>
   <!-- <a href="/" class="footer-logo" aria-label="ANRDI — Accueil">
        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . LOGO_FOOTER)): ?>
        <img src="<?= htmlspecialchars($footerLogoPath, ENT_QUOTES, 'UTF-8') ?>" alt="Logo ANRDI" class="footer-logo-img" width="150" height="75" loading="eager">
        <?php else: ?>
        <svg viewBox="0 0 200 56" fill="none" xmlns="http://www.w3.org/2000/svg" style="height:30px;width:auto;">
            <rect x="0"  y="0" width="8" height="56" rx="2" fill="#2563EB"/>
            <rect x="12" y="0" width="8" height="56" rx="2" fill="#3B82F6"/>
            <text x="28" y="38" font-family="'Satoshi',sans-serif" font-size="28" font-weight="700" fill="#FFFFFF" letter-spacing="-0.5">ANRDI</text>
        </svg>
        <?php endif; ?>
    </a> -->
</div>
</div>
</footer>

<script src="/assets/js/main.js" defer></script>
</body>
</html>
