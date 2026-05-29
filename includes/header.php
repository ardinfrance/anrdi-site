<?php
/**
 * ANRDI - Header global
 * Variables attendues : $pageTitle, $pageDescription, $bodyClass
 */
if (!defined('ANRDI_BOOTSTRAP')) { http_response_code(403); die(); }
require_once __DIR__ . '/seo.php';

$pageTitle ??= 'ANRDI - Association Nationale de Regulation des Domaines et de l\'Internet';
$pageDescription ??= 'L\'ANRDI regule, protege et developpe l\'ecosysteme numerique francais.';
$bodyClass ??= '';
$pageRobots ??= null;
$canonicalPath ??= null;
$canonicalQuery ??= '';
$ogType ??= 'website';
$schemaType ??= 'WebPage';
$pageImage ??= null;
$structuredData ??= [];
$breadcrumbs ??= [];
$twitterCard ??= 'summary_large_image';

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$requestPath = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
$requestPath = anrdi_normalize_url_path($requestPath);
$canonicalPath = $canonicalPath ? anrdi_normalize_url_path((string) $canonicalPath) : $requestPath;

$isMemberArea = str_starts_with($requestPath, '/membre/');
$isAuthArea = in_array($requestPath, ['/login.php', '/register.php', '/forgot-password.php', '/reset-password.php', '/verify.php'], true);
if ($pageRobots === null) {
    $pageRobots = ($isMemberArea || $isAuthArea) ? 'noindex, nofollow' : 'index, follow';
}

$canonical = anrdi_absolute_url($canonicalPath);
if ($canonicalQuery !== '') {
    $canonical .= '?' . ltrim($canonicalQuery, '?');
}

$localLogoHeader = (string) LOGO_HEADER;
$localLogoFavicon = (string) LOGO_FAVICON;
$localLogoOg = (string) LOGO_OG;
$absoluteOgImage = $pageImage ? anrdi_absolute_url((string) $pageImage) : anrdi_absolute_url($localLogoOg);
$mainCssFile = __DIR__ . '/../assets/css/main.css';
$mainCssVersion = is_file($mainCssFile) ? (string) filemtime($mainCssFile) : '1';
$rssUrl = anrdi_absolute_url('/pages/actualites-rss.php');

$organizationSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'ANRDI',
    'url' => rtrim(URL_MAIN, '/'),
    'logo' => anrdi_absolute_url((string) LOGO_OG),
    'email' => 'contact@anrdi.fr',
    'telephone' => '05 35 54 38 56',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => '5 Allee de La Pommeraie',
        'postalCode' => '78520',
        'addressLocality' => 'Limay',
        'addressCountry' => 'FR',
    ],
];

$webSiteSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => 'ANRDI',
    'url' => rtrim(URL_MAIN, '/'),
    'inLanguage' => 'fr-FR',
];

$webPageSchema = [
    '@context' => 'https://schema.org',
    '@type' => $schemaType,
    'name' => $pageTitle,
    'description' => $pageDescription,
    'url' => $canonical,
    'inLanguage' => 'fr-FR',
    'isPartOf' => [
        '@type' => 'WebSite',
        'name' => 'ANRDI',
        'url' => rtrim(URL_MAIN, '/'),
    ],
];

if (!empty($breadcrumbs)) {
    $itemList = [];
    foreach (array_values($breadcrumbs) as $index => $crumb) {
        $itemList[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => (string) ($crumb['name'] ?? ''),
            'item' => anrdi_absolute_url((string) ($crumb['url'] ?? '/')),
        ];
    }

    $structuredData[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $itemList,
    ];
}

array_unshift($structuredData, $webPageSchema);
if ($pageRobots === 'index, follow') {
    array_unshift($structuredData, $webSiteSchema);
    array_unshift($structuredData, $organizationSchema);
}

$nav = [
    ['/', 'Accueil'],
    ['/pages/services.php', 'Services'],
    ['/pages/actualites.php', 'Actualites'],
    ['/pages/faq.php', 'FAQ'],
    ['/pages/contact.php', 'Contact'],
];
$isLogged = Auth::isLoggedIn();
$role = Auth::getUserRole();
$curPath = $requestPath;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta name="robots" content="<?= htmlspecialchars($pageRobots, ENT_QUOTES, 'UTF-8') ?>">
<meta name="theme-color" content="#0A1628">
<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" type="application/rss+xml" title="Flux RSS ANRDI" href="<?= htmlspecialchars($rssUrl, ENT_QUOTES, 'UTF-8') ?>">

<meta property="og:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:type" content="<?= htmlspecialchars($ogType, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image" content="<?= htmlspecialchars($absoluteOgImage, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:locale" content="fr_FR">
<meta property="og:site_name" content="ANRDI">

<meta name="twitter:card" content="<?= htmlspecialchars($twitterCard, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:title" content="<?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($absoluteOgImage, ENT_QUOTES, 'UTF-8') ?>">

<link rel="icon" href="<?= htmlspecialchars($localLogoFavicon, ENT_QUOTES, 'UTF-8') ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($localLogoFavicon, ENT_QUOTES, 'UTF-8') ?>">

<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
      integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
      crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="/assets/css/main.css?v=<?= htmlspecialchars($mainCssVersion, ENT_QUOTES, 'UTF-8') ?>">

<script src="/assets/js/consent-config.js"></script>
<script async src="https://cdn.axet.fr/js/sdk.js?v=1.1.0"></script>

<?php foreach ($structuredData as $schema): ?>
<script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endforeach; ?>
</head>

<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">
<a class="skip-link sr-only" href="#main-content">Aller au contenu principal</a>

<header class="site-header" id="site-header">
<div class="header-inner container">

    <a href="/" class="header-logo" aria-label="ANRDI - Retour a l'accueil">
        <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . LOGO_HEADER)): ?>
            <img src="<?= htmlspecialchars($localLogoHeader, ENT_QUOTES, 'UTF-8') ?>" alt="Logo ANRDI" width="300" height="90" loading="eager">
        <?php else: ?>
        <svg viewBox="0 0 200 56" fill="none" xmlns="http://www.w3.org/2000/svg" class="logo-svg" aria-label="ANRDI" style="height:40px;width:auto;">
            <rect x="0" y="0" width="8" height="56" rx="2" fill="#2563EB"/>
            <rect x="12" y="0" width="8" height="56" rx="2" fill="#1E40AF"/>
            <text x="28" y="38" font-family="'Satoshi',sans-serif" font-size="28" font-weight="700" fill="#0A1628" letter-spacing="-0.5">ANRDI</text>
        </svg>
        <?php endif; ?>
    </a>

    <nav class="header-nav" id="header-nav" aria-label="Navigation principale">
        <ul class="nav-list" role="list">
            <?php foreach ($nav as [$href, $label]):
                $active = ($curPath === $href || ($href !== '/' && str_starts_with($curPath, $href)));
            ?>
            <li class="nav-item">
                <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                   class="nav-link<?= $active ? ' nav-link--active' : '' ?>"
                   <?= $active ? 'aria-current="page"' : '' ?>>
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="header-actions">
        <?php if ($isLogged): ?>
        <div class="header-user" id="user-dropdown-trigger">
            <button class="btn-avatar" aria-haspopup="true" aria-expanded="false" aria-label="Mon compte">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            </button>
            <div class="user-dropdown" role="menu">
                <a href="/membre/dashboard.php" class="dropdown-item" role="menuitem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Mon espace
                </a>
                <?php if (in_array($role, ['professional'], true)): ?>
                <a href="<?= URL_PRO ?>" class="dropdown-item" role="menuitem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    Espace Pro
                </a>
                <?php endif; ?>
                <?php if (in_array($role, ['admin', 'super_admin'], true)): ?>
                <a href="<?= URL_ADMIN ?>" class="dropdown-item" role="menuitem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 21 12 17.77 5.82 21 7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    Administration
                </a>
                <?php endif; ?>
                <hr class="dropdown-divider">
                <a href="/logout.php" class="dropdown-item dropdown-item--danger" role="menuitem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Deconnexion
                </a>
            </div>
        </div>
        <?php else: ?>
        <a href="/login.php" class="btn btn--ghost btn--sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><path d="M15 3h6v18h-6"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Connexion
        </a>
        <a href="tel:0535543856" class="btn btn--primary btn--sm">Nous appeler</a>
        <?php endif; ?>

        <button class="nav-burger" id="nav-burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="header-nav">
            <svg class="icon-burger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" hidden><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

</div>
</header>

<main class="site-main" id="main-content">
