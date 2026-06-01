<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';
$pageTitle = 'Partners & Clients — Ankur Infotech Pvt. Ltd.';
$pageDesc  = 'Our trusted partners, clients and affiliates — organisations we work with across Nepal.';

$all = [];
try { $all = query("SELECT * FROM partners WHERE active=1 ORDER BY position ASC, id DESC"); } catch(\Throwable $e) {}

$groups = ['client','partner','investor'];
$grouped = [];
foreach ($groups as $g) {
    $filtered = array_filter($all, fn($p) => ($p['type'] ?? '') === $g);
    if (!empty($filtered)) $grouped[$g] = array_values($filtered);
}

$labels = ['client'=>'Clients','partner'=>'Technology Partners','investor'=>'Investors'];

$__s = siteSettings();
$clientCount = count($grouped['client'] ?? []);
$partnerCount = count($grouped['partner'] ?? []);

require_once 'includes/header.php';
?>

<?php
$heroEyebrow     = __('partners_hero_eyebrow');
$heroEyebrowIcon = 'handshake';
$heroTitle       = __('partners_hero_title');
$heroSubtitle    = __('partners_hero_sub');
include 'includes/page-hero.php';
?>

<div class="partner-stats">
  <div class="container">
    <div class="partner-stats__grid">
      <?php foreach ([
        [$__s['stat_2_value'] ?? ($clientCount ? $clientCount . '+' : '650+'), 'Cooperative clients'],
        [$partnerCount ? $partnerCount . '+' : '15+', 'Technology partners'],
        ['7', 'Provinces covered'],
      ] as [$n, $l]): ?>
      <div class="partner-stats__item">
        <div class="partner-stats__value"><?= e($n) ?></div>
        <div class="partner-stats__label"><?= e($l) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<section class="st-section">
  <div class="container">

    <?php if (empty($grouped)): ?>
    <div class="p-empty" style="border:2px dashed var(--border);border-radius:var(--radius-xl);">
      <p style="margin:0;">Partner directory coming soon.</p>
    </div>
    <?php else: ?>
      <?php foreach ($groups as $g):
        if (empty($grouped[$g])) continue;
        $items = $grouped[$g];
        $label = $labels[$g];
      ?>
      <div class="partner-group">
        <div class="partner-group__head">
          <h2 class="partner-group__title"><?= e($label) ?></h2>
          <div class="partner-group__line"></div>
          <span class="badge badge-primary"><?= count($items) ?></span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:0.875rem;">
          <?php foreach ($items as $p): ?>
          <?php $tag = !empty($p['url']) ? 'a' : 'div'; ?>
          <<?= $tag ?><?= !empty($p['url']) ? ' href="'.e($p['url']).'" target="_blank" rel="noopener noreferrer"' : '' ?>
            class="st-partner-card">
            <?php if (!empty($p['logo_url'])): ?>
            <img src="<?= e($p['logo_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy" decoding="async" style="height:2.25rem;width:auto;object-fit:contain;max-width:7rem;">
            <?php else: ?>
            <div style="width:2.5rem;height:2.5rem;border-radius:var(--radius);background:var(--primary-light);display:grid;place-items:center;font-size:var(--text-md);font-weight:700;color:var(--primary);"><?= strtoupper(substr($p['name'],0,1)) ?></div>
            <?php endif; ?>
            <div style="font-size:var(--text-sm);font-weight:600;color:var(--foreground);text-align:center;line-height:1.3;"><?= e($p['name']) ?></div>
            <?php if (!empty($p['district'])): ?>
            <div style="font-size:var(--text-xs);color:var(--muted-foreground);text-align:center;display:flex;align-items:center;justify-content:center;gap:0.25rem;">
              <i data-lucide="map-pin" class="ic-11"></i><?= e($p['district']) ?>
            </div>
            <?php endif; ?>
          </<?= $tag ?>>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<?php
$ctaTitle = 'Become a Partner';
$ctaSubtitle = "Interested in partnering with us? Let's discuss how we can grow together.";
$ctaPrimary = ['label' => __('cta_get_in_touch'), 'url' => url('contact.php'), 'icon' => 'handshake'];
include 'includes/cta-banner.php';
?>

<?php require_once 'includes/footer.php'; ?>
