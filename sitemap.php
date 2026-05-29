<?php
declare(strict_types=1);

define('ANRDI_BOOTSTRAP', true);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/seo.php';

header('Content-Type: application/xml; charset=UTF-8');

$entries = anrdi_public_sitemap_entries();
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($entries as $entry): ?>
  <url>
    <loc><?= htmlspecialchars((string) ($entry['loc'] ?? ''), ENT_QUOTES, 'UTF-8') ?></loc>
    <?php if (!empty($entry['lastmod'])): ?>
    <lastmod><?= htmlspecialchars((string) $entry['lastmod'], ENT_QUOTES, 'UTF-8') ?></lastmod>
    <?php endif; ?>
    <?php if (!empty($entry['changefreq'])): ?>
    <changefreq><?= htmlspecialchars((string) $entry['changefreq'], ENT_QUOTES, 'UTF-8') ?></changefreq>
    <?php endif; ?>
    <?php if (!empty($entry['priority'])): ?>
    <priority><?= htmlspecialchars((string) $entry['priority'], ENT_QUOTES, 'UTF-8') ?></priority>
    <?php endif; ?>
  </url>
<?php endforeach; ?>
</urlset>
