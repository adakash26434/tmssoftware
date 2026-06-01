<?php
/**
 * ============================================================
 * includes/head.php — Shared <head> block
 * Single source of truth for fonts, theme CSS, Tailwind,
 * Alpine, Lucide, theme-color and PWA manifest.
 *
 * USAGE (any page, before </head>):
 * $headContext = 'public' | 'admin' | 'portal' | 'auth' | 'error';
 * $pageTitle   = '...';        // optional
 * $pageDesc    = '...';        // optional
 * $ogImage     = '...';        // optional (public only)
 * $extraHead   = '<style>…</style>'; // optional
 * require __DIR__ . '/head.php';
 *
 * Replaces the duplicated <link rel=stylesheet> + Google Fonts
 * blocks that lived in header.php, admin-layout.php,
 * portal-layout.php, login.php, signup.php, forgot-password.php,
 * reset-password.php, verify-email.php, 403/404/500.php.
 * ============================================================
 */

$__ctx       = $headContext ?? 'public';
$__indexable = in_array($__ctx, ['public'], true);
$__siteName  = defined('SITE_NAME') ? SITE_NAME : 'Ankur Infotech Pvt. Ltd.';
$__title     = $pageTitle ?? $__siteName;
$__desc      = $pageDesc  ?? "Software & IT Solutions Company | Butwal, Rupandehi, Nepal";
$__siteUrl   = defined('SITE_URL') ? SITE_URL : '';
$__ogImage   = $ogImage ?? $__siteUrl . '/public/opengraph.jpg';
$__ogUrl     = $__siteUrl . '/' . ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
$__themePref = (function_exists('currentUser') ? (currentUser()['theme_pref'] ?? '') : '');
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#2563eb">
<?php if (!$__indexable): ?>
<meta name="robots" content="noindex,nofollow">
<?php endif; ?>

<title><?= e($__title) ?></title>
<meta name="description" content="<?= e($__desc) ?>">

<?php if ($__indexable): ?>
<meta property="og:title"       content="<?= e($__title) ?>">
<meta property="og:description" content="<?= e($__desc) ?>">
<meta property="og:image"       content="<?= e($__ogImage) ?>">
<meta property="og:url"         content="<?= e($__ogUrl) ?>">
<meta property="og:type"        content="website">
<meta name="twitter:card"       content="summary_large_image">
<?php endif; ?>

<!-- Self-hosted fonts (no Google Fonts network calls) -->
<link rel="preload" as="font" type="font/woff2" crossorigin
      href="<?= $__siteUrl ?>/assets/fonts/rP2Yp2ywxg089UriI5-g4vlH9VoD8Cmcqbu0-K4.woff2">
<link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/fonts.css">

<!-- Compiled Tailwind (production build, ~31KB) replaces CDN runtime JIT -->
<link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/tailwind.min.css">
<link rel="stylesheet" href="<?= $__siteUrl ?>/assets/theme.css">

<?php if ($__ctx === 'public'): ?>
<!-- Shared responsive overrides for about/contact/gallery/footer -->
<link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/pages.css">
<?php
  // Home-only stylesheet (homepage carries 130+ lines of layout/animation rules)
  $__reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  $__isHome  = ($__reqPath === '/' || $__reqPath === '/index.php' || ($pageKey ?? '') === 'home');
  if ($__isHome): ?>
<link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/home.css">
<?php endif; endif; ?>

<?php if (in_array($__ctx, ['admin', 'portal'], true)): ?>
  <link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/daisyui.min.css">
  <link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/st-bs-datepicker.css">
  <link rel="stylesheet" href="<?= $__siteUrl ?>/assets/css/admin-forms.css">
  <script src="<?= $__siteUrl ?>/assets/js/st-bs-datepicker.js" defer></script>

  <style>
  /* ST overrides win over DaisyUI defaults */
  .btn, .btn:not(.btn-circle):not(.btn-square) { border-radius: var(--radius-sm) !important; }
  .btn.btn-lg, .btn.btn-xl { border-radius: var(--radius-md) !important; }
  .input, .select, .textarea {
    border-radius: var(--radius-md) !important;
    border-color: var(--border) !important;
  }
  .modal-box, .card { border-radius: var(--radius-xl) !important; }
  .btn i[data-lucide], .btn svg { flex-shrink: 0; }
  </style>
<?php endif; ?>

<!-- Self-hosted Alpine + Lucide (replaces unpkg/jsdelivr CDN) -->
<script src="<?= $__siteUrl ?>/assets/vendor/alpine.min.js" defer></script>
<script src="<?= $__siteUrl ?>/assets/vendor/lucide.min.js" defer></script>

<link rel="manifest" href="<?= $__siteUrl ?>/manifest.json">
<link rel="apple-touch-icon" href="<?= $__siteUrl ?>/public/favicon.svg">
<link rel="icon" type="image/svg+xml" href="<?= $__siteUrl ?>/public/favicon.svg">

<script>(function(){
  var srv = <?= json_encode($__themePref) ?>;
  var loc = localStorage.getItem('st-theme');
  var t = srv || loc;
  if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  }
  if (srv) localStorage.setItem('st-theme', srv);
})();</script>

<?php
// Dynamic brand color override — applies on ALL contexts (public, admin, portal, auth)
// so admin-controlled colors propagate everywhere in one place.
// v6: status tokens (success/warning/danger/info) added.
(function() use (&$__bp, &$__bs, &$__bok, &$__bwarn, &$__bdng, &$__binfo) {
    $__bp = ''; $__bs = ''; $__bok = ''; $__bwarn = ''; $__bdng = ''; $__binfo = '';
    if (!function_exists('query')) return; // not available on very early error pages
    try {
        $__rows = query("SELECT setting_key,setting_val FROM site_settings WHERE setting_key IN
            ('brand_primary','brand_secondary','brand_success','brand_warning','brand_danger','brand_info')");
        foreach ($__rows as $__r) {
            switch ($__r['setting_key']) {
                case 'brand_primary':   $__bp    = $__r['setting_val']; break;
                case 'brand_secondary': $__bs    = $__r['setting_val']; break;
                case 'brand_success':   $__bok   = $__r['setting_val']; break;
                case 'brand_warning':   $__bwarn = $__r['setting_val']; break;
                case 'brand_danger':    $__bdng  = $__r['setting_val']; break;
                case 'brand_info':      $__binfo = $__r['setting_val']; break;
            }
        }
    } catch (\Throwable $e) {}
})();

// Compute a 15%-darker variant of a #rrggbb hex string for --primary-dark
if (!function_exists('__hexDarken')) {
    function __hexDarken(string $hex, float $factor = 0.82): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) return '#' . $hex;
        $r = max(0, (int)round(hexdec(substr($hex,0,2)) * $factor));
        $g = max(0, (int)round(hexdec(substr($hex,2,2)) * $factor));
        $b = max(0, (int)round(hexdec(substr($hex,4,2)) * $factor));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}

if ($__bp || $__bs || $__bok || $__bwarn || $__bdng || $__binfo):
?>
<style>
<?php if ($__bp): ?>
:root {
  --primary:          <?= e($__bp) ?>;
  --primary-dark:     <?= e(__hexDarken($__bp)) ?>;
  --primary-light:    <?= e($__bp) ?>26;
  --ring:             <?= e($__bp) ?>;
  --gradient-primary: linear-gradient(135deg, <?= e($__bp) ?> 0%, <?= e(__hexDarken($__bp, 0.70)) ?> 100%);
}
<?php endif; ?>
<?php if ($__bs):    ?>:root { --secondary: <?= e($__bs) ?>; }<?php endif; ?>
<?php if ($__bok):   ?>:root { --success: <?= e($__bok) ?>;  --success-soft: <?= e($__bok) ?>33; }<?php endif; ?>
<?php if ($__bwarn): ?>:root { --warning: <?= e($__bwarn) ?>; --warning-soft: <?= e($__bwarn) ?>33; }<?php endif; ?>
<?php if ($__bdng):  ?>:root { --danger:  <?= e($__bdng) ?>;  --danger-soft:  <?= e($__bdng) ?>33; }<?php endif; ?>
<?php if ($__binfo): ?>:root { --info:    <?= e($__binfo) ?>; --info-soft:    <?= e($__binfo) ?>33; }<?php endif; ?>
</style>
<?php endif; ?>

<script>
function toggleTheme() {
  var h = document.documentElement;
  var dark = h.classList.toggle('dark');
  localStorage.setItem('st-theme', dark ? 'dark' : 'light');
  // sync sun/moon icons wherever they appear on the page
  document.querySelectorAll('#icon-sun, #icon-moon').forEach(function(el) {
    el.style.display = (el.id === 'icon-sun') === dark ? 'block' : 'none';
  });
}
// Sync icon visibility immediately (icons may render before DOMContentLoaded)
(function() {
  var isDark = document.documentElement.classList.contains('dark');
  function syncIcons() {
    var sun  = document.getElementById('icon-sun');
    var moon = document.getElementById('icon-moon');
    if (sun)  sun.style.display  = isDark ? 'block' : 'none';
    if (moon) moon.style.display = isDark ? 'none'  : 'block';
  }
  syncIcons();
  document.addEventListener('DOMContentLoaded', syncIcons);
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.lucide) lucide.createIcons();
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) { entry.target.classList.add('visible'); io.unobserve(entry.target); }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
  document.querySelectorAll(
    '.animate-on-scroll, .animate-fade-up, .animate-fade-in, ' +
    '.animate-slide-left, .animate-slide-right, .animate-scale-up, .stagger-children'
  ).forEach(function (el) { io.observe(el); });
});
</script>

<?php if (!empty($extraHead)) echo $extraHead; ?>
