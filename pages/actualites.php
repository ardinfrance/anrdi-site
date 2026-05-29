<?php
define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/seo.php';
Security::setSecurityHeaders();
Auth::startSession();

$slug = trim((string) ($_GET['slug'] ?? ''));
$pageTitle = 'Actualites - ANRDI';
$pageDescription = 'Publications et decisions de l ANRDI.';
$article = null;
$posts = [];
$breadcrumbs = [
    ['name' => 'Accueil', 'url' => '/'],
    ['name' => 'Actualites', 'url' => '/pages/actualites.php'],
];

try {
    if ($slug !== '') {
        $article = Database::query(
            'SELECT title, slug, excerpt, content, published_at, category
             FROM ' . DB_PREFIX . 'posts
             WHERE slug = ? AND status = "published"
             LIMIT 1',
            [$slug]
        )->fetch();
    }

    if (!$article) {
        $posts = Database::query(
            'SELECT title, slug, excerpt, published_at, category
             FROM ' . DB_PREFIX . 'posts
             WHERE status = "published"
             ORDER BY published_at DESC
             LIMIT 12'
        )->fetchAll();
    }
} catch (Throwable $e) {
    error_log('[ANRDI actualites] ' . $e->getMessage());
}

if ($article) {
    $articleTitle = (string) ($article['title'] ?? 'Actualite');
    $articleExcerpt = trim((string) ($article['excerpt'] ?? ''));
    $articleSlug = (string) ($article['slug'] ?? $slug);
    $articleUrl = '/pages/actualites.php?slug=' . rawurlencode($articleSlug);
    $pageTitle = $articleTitle . ' - ANRDI';
    $pageDescription = $articleExcerpt !== '' ? $articleExcerpt : 'Publication officielle de l ANRDI.';
    $canonicalPath = '/pages/actualites.php';
    $canonicalQuery = 'slug=' . rawurlencode($articleSlug);
    $ogType = 'article';
    $schemaType = 'NewsArticle';
    $breadcrumbs[] = ['name' => $articleTitle, 'url' => $articleUrl];
    $structuredData[] = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $articleTitle,
        'description' => $pageDescription,
        'datePublished' => !empty($article['published_at']) ? date('c', strtotime((string) $article['published_at'])) : null,
        'dateModified' => !empty($article['published_at']) ? date('c', strtotime((string) $article['published_at'])) : null,
        'mainEntityOfPage' => anrdi_absolute_url($articleUrl),
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'ANRDI',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => anrdi_absolute_url((string) LOGO_OG),
            ],
        ],
    ];
}

include __DIR__ . '/../includes/header.php';
?>
<section class="section">
  <div class="container">
    <?php if ($article): ?>
    <div class="section-header" style="max-width:840px;margin-inline:auto;">
      <span class="section-label"><?= htmlspecialchars((string) ($article['category'] ?? 'Publication'), ENT_QUOTES, 'UTF-8') ?></span>
      <h1 class="section-title"><?= htmlspecialchars((string) ($article['title'] ?? 'Actualite'), ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="section-desc"><?= htmlspecialchars((string) ($article['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <article class="card" style="max-width:840px;margin-inline:auto;">
      <div class="card-desc" style="font-size:1rem;line-height:1.8;">
        <?= nl2br(htmlspecialchars((string) ($article['content'] ?? $article['excerpt'] ?? ''), ENT_QUOTES, 'UTF-8')) ?>
      </div>
      <div style="margin-top:2rem;">
        <a href="/pages/actualites.php" class="btn btn--ghost btn--sm">&larr; Retour aux actualites</a>
      </div>
    </article>
    <?php else: ?>
    <div class="section-header">
      <span class="section-label">Publications</span>
      <h1 class="section-title">Actualites et decisions</h1>
      <p class="section-desc">Toutes les publications officielles de l ANRDI sont regroupees sur cette page.</p>
    </div>
    <?php if (!empty($posts)): ?>
    <div class="card-grid">
      <?php foreach ($posts as $post): ?>
      <article class="card">
        <span class="section-label"><?= htmlspecialchars((string) ($post['category'] ?? 'Publication'), ENT_QUOTES, 'UTF-8') ?></span>
        <h2 class="card-title" style="margin-top:1rem;"><?= htmlspecialchars((string) ($post['title'] ?? 'Actualite'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="card-desc"><?= htmlspecialchars((string) ($post['excerpt'] ?? 'Aucun resume disponible.'), ENT_QUOTES, 'UTF-8') ?></p>
        <a href="/pages/actualites.php?slug=<?= urlencode((string) ($post['slug'] ?? '')) ?>" class="btn btn--ghost btn--sm" style="margin-top:1.5rem;">Lire la publication</a>
      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card" style="max-width:760px;margin-inline:auto;text-align:center;">
      <h2 class="card-title">Aucune actualite publiee pour le moment</h2>
      <p class="card-desc">Les contenus s afficheront ici des leur publication.</p>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
