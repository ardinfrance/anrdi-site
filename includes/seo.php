<?php
/**
 * ANRDI - Helpers SEO
 */

declare(strict_types=1);

if (!defined('ANRDI_BOOTSTRAP')) {
    http_response_code(403);
    die();
}

function anrdi_normalize_url_path(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '/';
    }

    if ($path[0] !== '/') {
        $path = '/' . $path;
    }

    return $path;
}

function anrdi_absolute_url(string $pathOrUrl): string
{
    if (preg_match('#^https?://#i', $pathOrUrl) === 1) {
        return $pathOrUrl;
    }

    return rtrim(URL_MAIN, '/') . anrdi_normalize_url_path($pathOrUrl);
}

function anrdi_public_routes(): array
{
    return [
        [
            'path' => '/',
            'changefreq' => 'weekly',
            'priority' => '1.0',
            'source' => ROOT_PATH . '/index.php',
        ],
        [
            'path' => '/pages/services.php',
            'changefreq' => 'monthly',
            'priority' => '0.9',
            'source' => ROOT_PATH . '/pages/services.php',
        ],
        [
            'path' => '/pages/actualites.php',
            'changefreq' => 'daily',
            'priority' => '0.9',
            'source' => ROOT_PATH . '/pages/actualites.php',
        ],
        [
            'path' => '/pages/faq.php',
            'changefreq' => 'monthly',
            'priority' => '0.8',
            'source' => ROOT_PATH . '/pages/faq.php',
        ],
        [
            'path' => '/pages/contact.php',
            'changefreq' => 'monthly',
            'priority' => '0.7',
            'source' => ROOT_PATH . '/pages/contact.php',
        ],
        [
            'path' => '/pages/mentions-legales.php',
            'changefreq' => 'yearly',
            'priority' => '0.3',
            'source' => ROOT_PATH . '/pages/mentions-legales.php',
        ],
        [
            'path' => '/pages/politique-confidentialite.php',
            'changefreq' => 'yearly',
            'priority' => '0.3',
            'source' => ROOT_PATH . '/pages/politique-confidentialite.php',
        ],
        [
            'path' => '/pages/cgv.php',
            'changefreq' => 'yearly',
            'priority' => '0.3',
            'source' => ROOT_PATH . '/pages/cgv.php',
        ],
    ];
}

function anrdi_file_lastmod(?string $filePath): ?string
{
    if ($filePath === null || !is_file($filePath)) {
        return null;
    }

    $mtime = filemtime($filePath);
    if ($mtime === false) {
        return null;
    }

    return gmdate('c', $mtime);
}

function anrdi_public_sitemap_entries(): array
{
    $entries = [];
    foreach (anrdi_public_routes() as $route) {
        $entries[] = [
            'loc' => anrdi_absolute_url($route['path']),
            'lastmod' => anrdi_file_lastmod($route['source'] ?? null),
            'changefreq' => $route['changefreq'] ?? null,
            'priority' => $route['priority'] ?? null,
        ];
    }

    try {
        $posts = Database::query(
            'SELECT slug, published_at, updated_at
             FROM ' . DB_PREFIX . 'posts
             WHERE status = "published"
             ORDER BY published_at DESC'
        )->fetchAll();

        foreach ($posts as $post) {
            $slug = trim((string) ($post['slug'] ?? ''));
            if ($slug === '') {
                continue;
            }

            $updatedAt = (string) ($post['updated_at'] ?? $post['published_at'] ?? '');
            $entries[] = [
                'loc' => anrdi_absolute_url('/pages/actualites.php?slug=' . rawurlencode($slug)),
                'lastmod' => $updatedAt !== '' ? gmdate('c', strtotime($updatedAt)) : null,
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }
    } catch (Throwable $e) {
        error_log('[ANRDI SEO] sitemap posts error: ' . $e->getMessage());
    }

    return $entries;
}

