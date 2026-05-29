<?php
declare(strict_types=1);

define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/rss+xml; charset=UTF-8');

$items = [];
try {
    $items = Database::query(
        'SELECT title, slug, excerpt, content, published_at, category
         FROM ' . DB_PREFIX . 'posts
         WHERE status = "published"
         ORDER BY published_at DESC
         LIMIT 20'
    )->fetchAll();
} catch (Throwable $e) {
    error_log('[ANRDI RSS] ' . $e->getMessage());
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<rss version="2.0">
  <channel>
    <title>ANRDI - Actualités</title>
    <link><?= htmlspecialchars(URL_MAIN . '/pages/actualites.php', ENT_QUOTES, 'UTF-8') ?></link>
    <description>Publications et decisions de l'ANRDI.</description>
    <language>fr-fr</language>
    <lastBuildDate><?= gmdate(DATE_RSS) ?></lastBuildDate>
    <?php foreach ($items as $item): ?>
    <?php
      $slug = (string) ($item['slug'] ?? '');
      $title = (string) ($item['title'] ?? 'Actualite ANRDI');
      $excerpt = (string) ($item['excerpt'] ?? '');
      $content = (string) ($item['content'] ?? $excerpt);
      $link = URL_MAIN . '/pages/actualites.php?slug=' . rawurlencode($slug);
      $pubDate = !empty($item['published_at']) ? date(DATE_RSS, strtotime((string) $item['published_at'])) : gmdate(DATE_RSS);
    ?>
    <item>
      <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
      <link><?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?></link>
      <guid isPermaLink="true"><?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?></guid>
      <description><?= htmlspecialchars($excerpt !== '' ? $excerpt : $content, ENT_QUOTES, 'UTF-8') ?></description>
      <pubDate><?= htmlspecialchars($pubDate, ENT_QUOTES, 'UTF-8') ?></pubDate>
      <category><?= htmlspecialchars((string) ($item['category'] ?? 'Publication'), ENT_QUOTES, 'UTF-8') ?></category>
    </item>
    <?php endforeach; ?>
  </channel>
</rss>
