<?php
/**
 * Dynamic XML Sitemap
 * URL: /sitemap.xml (add RewriteRule to .htaccess if needed)
 * Or access directly at /sitemap.php
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

$base = rtrim(SITE_URL, '/');

// नेपालीमा: sitemapUrl() — yo function le aafno kaam garchha
function sitemapUrl(string $loc, string $changefreq='monthly', string $priority='0.5', ?string $lastmod=null): string {
    $loc = htmlspecialchars($loc, ENT_XML1);
    $lm  = $lastmod ? "\n    <lastmod>" . date('Y-m-d', strtotime($lastmod)) . "</lastmod>" : '';
    return "  <url>\n    <loc>{$loc}</loc>{$lm}\n    <changefreq>{$changefreq}</changefreq>\n    <priority>{$priority}</priority>\n  </url>";
}

$urls = [];

// ── Static pages ──────────────────────────────────────────────
$staticPages = [
    [''           , 'weekly'  , '1.0'],
    ['about.php'  , 'monthly' , '0.8'],
    ['services.php','monthly' , '0.8'],
    ['products.php','monthly' , '0.8'],
    ['pricing.php' ,'monthly' , '0.7'],
    ['portfolio.php','monthly', '0.7'],
    ['news.php'   , 'weekly'  , '0.7'],
    ['careers.php' ,'weekly'  , '0.6'],
    ['partners.php','monthly' , '0.6'],
    ['tools.php'  , 'monthly' , '0.5'],
    ['faq.php'    , 'monthly' , '0.6'],
    ['contact.php' ,'monthly' , '0.6'],
];
foreach ($staticPages as [$slug,$freq,$pri]) {
    $urls[] = sitemapUrl($base . '/' . ($slug ? $slug : ''), $freq, $pri);
}

// ── Services ──────────────────────────────────────────────────
try {
    $services = query("SELECT slug, updated_at FROM services WHERE active=1 ORDER BY position,id");
    foreach ($services as $s) {
        $urls[] = sitemapUrl($base . '/services.php?slug=' . urlencode($s['slug']), 'monthly', '0.7', $s['updated_at']);
    }
} catch(\Throwable $e) {}

// ── Products ──────────────────────────────────────────────────
try {
    $products = query("SELECT slug, updated_at FROM products WHERE active=1 ORDER BY position,id");
    foreach ($products as $p) {
        $urls[] = sitemapUrl($base . '/products.php?slug=' . urlencode($p['slug']), 'monthly', '0.8', $p['updated_at']);
    }
} catch(\Throwable $e) {}

// ── News / Blog ───────────────────────────────────────────────
try {
    $posts = query("SELECT slug, published_at, updated_at FROM news WHERE active=1 AND (published_at IS NULL OR published_at<=NOW()) ORDER BY published_at DESC LIMIT 200");
    foreach ($posts as $n) {
        $lastmod = $n['updated_at'] ?? $n['published_at'];
        $urls[]  = sitemapUrl($base . '/news-post.php?slug=' . urlencode($n['slug']), 'monthly', '0.6', $lastmod);
    }
} catch(\Throwable $e) {}

// ── Portfolio ─────────────────────────────────────────────────
try {
    $portfolio = query("SELECT slug, updated_at FROM portfolio WHERE active=1 ORDER BY sort_order,id LIMIT 100");
    foreach ($portfolio as $p) {
        $urls[] = sitemapUrl($base . '/portfolio.php?slug=' . urlencode($p['slug']), 'monthly', '0.6', $p['updated_at']);
    }
} catch(\Throwable $e) {}

// ── Output ────────────────────────────────────────────────────
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
echo '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
echo implode("\n", $urls) . "\n";
echo '</urlset>';
