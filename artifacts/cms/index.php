<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';
$pageTitle = 'Ankur Infotech Pvt. Ltd. — IT Solutions & Software Services | Butwal, Nepal';
$pageDesc  = 'IT Solutions & Software Services based in Butwal, Rupandehi, Nepal. Reliable, locally supported technology for your business.';

$testimonials = [];
try { $testimonials = query("SELECT * FROM testimonials WHERE active=1 ORDER BY position LIMIT 6"); } catch(\Throwable $e) {}
$__logoClients = [];
try { $__logoClients = query("SELECT org_name, logo_url FROM clients WHERE status='active' AND logo_url IS NOT NULL AND logo_url!='' ORDER BY " . sqlRand() . " LIMIT 20"); } catch(\Throwable $e) {}
// Fallback to the partners directory (client logos / names) when no CRM client logos exist
if (empty($__logoClients)) {
  try { $__logoClients = query("SELECT name AS org_name, logo_url FROM partners WHERE active=1 AND type='client' ORDER BY position ASC, id ASC LIMIT 24"); } catch(\Throwable $e) {}
}
// Show section if we have any clients/partners (even text-only, no logo required)

$newsItems = [];
try { $newsItems = query("SELECT * FROM news WHERE published=1 AND active=1 ORDER BY published_at DESC LIMIT 3"); } catch(\Throwable $e) {}

$homeProducts = [];
try {
    $homeProducts = query(
        "SELECT id,name,slug,tagline,summary,icon,lucide_icon,icon_color,badge,features,highlights," .
        "home_card_dark,home_card_wide,home_bg_css,demo_screenshot_url,tab_label,home_position,position " .
        "FROM products WHERE active=1 AND show_on_home=1 " .
        "ORDER BY COALESCE(NULLIF(home_position,0),position), id LIMIT 12"
    );
} catch(\Throwable $e) {
    // products table may not have new columns yet — run database_migrate_v2.sql
    try { $homeProducts = query("SELECT id,name,slug,tagline,summary,icon,badge,features,highlights,position FROM products WHERE active=1 ORDER BY position,id LIMIT 12"); }
    catch(\Throwable $e2) {}
}

// ── Site settings (CMS-driven homepage content) ──────────────────
$__s = siteSettings();

// Stats bar — admin-editable, fallback to defaults
$_def = [
  ['10+',  'Years of Experience',   'calendar'],
  ['650+', 'Happy Clients', 'users'],
  ['7+',   'Major Products',        'layers'],
  ['100%', 'Client Retention Rate', 'shield-check'],
];
$stats = [];
for ($__i=1;$__i<=4;$__i++) {
  $v = trim($__s["stat_{$__i}_value"] ?? '');
  $l = cms($__s, "stat_{$__i}_label");
  $stats[] = [$v?:$_def[$__i-1][0], $l?:$_def[$__i-1][1], $_def[$__i-1][2]];
}
unset($__i,$v,$l,$_def);

// ── Hero CMS variables — bilingual: admin sets EN + NP, cms() picks right one ──
$_heroTitle        = cms($__s, 'homepage_hero_title');
$_heroSub          = cms($__s, 'homepage_hero_subtitle');
$_heroBadge1       = cms($__s, 'hero_badge1_text');
$_heroBadge2       = cms($__s, 'hero_badge2_text');
$_heroCtaText      = cms($__s, 'homepage_cta_text');
$_heroCtaUrl       = trim($__s['homepage_cta_url'] ?? '');
$_heroCtaSecondary = cms($__s, 'hero_cta_secondary');
// Bento section
$_bentoEyebrow = cms($__s, 'home_bento_eyebrow');
$_bentoTitle   = cms($__s, 'home_bento_title');
$_bentoSub     = cms($__s, 'home_bento_subtitle');
// "See it in action" section
$_inActionTitle = cms($__s, 'home_in_action_title');
$_inActionSub   = cms($__s, 'home_in_action_subtitle');
// SEO — override page title/desc when admin sets meta
if (!empty($__s['meta_description'])) $pageDesc = $__s['meta_description'];
$_metaTitle = cms($__s, 'home_meta_title');
if ($_metaTitle) $pageTitle = $_metaTitle;

$_stepIcons = ['calendar','file-check','settings','rocket'];
$_stepDefsT = ['Discovery Call','Custom Proposal','Setup & Migration','Go Live'];
$_stepDefsD = [
  'We meet to understand your business needs — free, no commitment.',
  'A detailed proposal with pricing, timeline and scope arrives within 2 business days.',
  'Your dedicated project manager migrates data, configures the system and trains your staff.',
  'You go live in as little as 2 weeks. We stay on-call for 30 days post-launch.',
];
$processSteps = [];
for ($__pi = 0; $__pi < 4; $__pi++) {
  $__n = $__pi + 1;
  $processSteps[] = [
    $_stepIcons[$__pi],
    cms($__s, "home_step{$__n}_title") ?: $_stepDefsT[$__pi],
    cms($__s, "home_step{$__n}_desc")  ?: $_stepDefsD[$__pi],
  ];
}
unset($__pi,$__n,$_stepIcons,$_stepDefsT,$_stepDefsD);

// Fonts and theme tokens are already loaded globally via includes/head.php (Sora + DM Sans + Noto Sans Devanagari).
// No duplicate font loading needed here.

include 'includes/header.php';
?>

<!-- Home-specific layout + animation styles live in assets/css/home.css
     (loaded by includes/head.php when $__page === 'home'). -->


<!-- ══════════════════════════════════════════════
  § 1 — HERO  (split: text left · dashboard mockup right)
══════════════════════════════════════════════ -->
<section style="position:relative;overflow:hidden;background:var(--background);" class="bg-gradient-hero">
  <div class="bg-dot-grid" style="position:absolute;inset:0;opacity:.4;-webkit-mask-image:radial-gradient(ellipse 70% 60% at 65% 10%,black,transparent);mask-image:radial-gradient(ellipse 70% 60% at 65% 10%,black,transparent);pointer-events:none;"></div>
  <div class="blur-orb blur-orb-primary" style="top:-12rem;right:-4rem;width:52rem;height:36rem;opacity:.5;"></div>
  <div class="blur-orb blur-orb-violet"  style="top:4rem;left:-6rem;width:20rem;height:20rem;opacity:.3;"></div>

  <div class="container" style="position:relative;padding-top:4rem;padding-bottom:4.5rem;">
    <div id="hero-grid" style="display:grid;grid-template-columns:1fr;gap:2.5rem;align-items:center;text-align:center;">

      <!-- ── Hero left col ── -->
      <div id="hero-left">
        <div class="cta-in" style="display:flex;justify-content:center;gap:.75rem;flex-wrap:wrap;margin-bottom:1.125rem;">
          <div class="hero-badge">
            <span class="live-dot"></span>
            <?= e($_heroBadge1 ?: __('trust_120_live')) ?>
          </div>
          <div class="section-eyebrow section-eyebrow-primary">
            <i data-lucide="star" style="width:10px;height:10px;fill:currentColor;"></i>
            <?= e($_heroBadge2 ?: __('trust_nepal_1')) ?>
          </div>
        </div>

        <h1 style="font-family:var(--font-display);font-size:clamp(1.875rem,3vw,3rem);font-weight:800;letter-spacing:-.03em;line-height:1.12;color:var(--foreground);margin:0 0 0.875rem;">
          <?php if ($_heroTitle): // Admin override — single clean heading ?>
            <span class="hw"><?= e($_heroTitle) ?></span>
          <?php elseif (isNepali()): ?>
            <span class="hw">डिजिटाइजेसन,</span><br>
            <span class="hw">डिजिटलाइजेसन र</span><br>
            <span class="hw"><span class="tg">अटोमेसन</span></span>
          <?php else: ?>
            <span class="hw"><?= __('home_hero_h1_a') ?></span><br>
            <span class="hw"><?= __('home_hero_h1_b') ?></span><br>
            <span class="hw"><?= __('home_hero_h1_c') ?> <span class="tg">सहकारी</span></span>
          <?php endif; ?>
        </h1>

        <p class="sub-in" style="font-size:var(--text-base);line-height:1.75;color:var(--muted-foreground);margin:0 auto 1.625rem;max-width:32rem;">
          <?= e($_heroSub ?: __('home_hero_sub')) ?>
        </p>

        <?php
          $_ctaHref = $_heroCtaUrl ?: url('contact.php');
          $_ctaLabel = $_heroCtaText ?: __('home_hero_book_demo');
          $_ctaSecLabel = $_heroCtaSecondary ?: __('home_hero_explore');
        ?>
        <div id="hero-cta" class="cta-in" style="display:flex;flex-wrap:wrap;justify-content:center;gap:.75rem;margin-bottom:1.25rem;">
          <a href="<?= e($_ctaHref) ?>" class="btn btn-primary btn-lg btn-glow">
            <i data-lucide="calendar" class="ic-16"></i>
            <?= e($_ctaLabel) ?>
          </a>
          <a href="<?= url('services.php') ?>" class="btn btn-outline btn-lg">
            <i data-lucide="layers" class="ic-15"></i>
            <?= e($_ctaSecLabel) ?>
          </a>
        </div>

        <div id="hero-trust" class="cta-in" style="display:flex;flex-wrap:wrap;justify-content:center;gap:1.5rem;">
          <?php foreach([['check',__('trust_no_card')],['zap',__('trust_go_live')],['shield',__('trust_nrb')]] as [$ic,$lb]): ?>
          <div style="display:flex;align-items:center;gap:.375rem;font-size:var(--text-sm);color:var(--muted-foreground);">
            <i data-lucide="<?= $ic ?>" style="width:13px;height:13px;color:#10b981;flex-shrink:0;"></i>
            <?= e($lb) ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div><!-- /hero-left -->

      <!-- ── Hero right col: mini-dashboard mockup ── -->
      <div id="hero-mock" style="display:none;position:relative;padding:1.5rem 1.5rem 2rem;">
        <div style="border-radius:var(--radius-2xl);overflow:hidden;box-shadow:0 32px 80px rgba(15,23,42,.14),0 2px 8px rgba(15,23,42,.08);border:1px solid var(--border);">
          <!-- Browser chrome -->
          <div class="wc">
            <span class="wd dot-danger"></span>
            <span class="wd dot-warning"></span>
            <span class="wd dot-success"></span>
            <div class="pill-row">
              <i data-lucide="lock" class="ic-9 text-success"></i>
              <span class="mono-meta">Ankur Infotech Pvt. Ltd.</span>
            </div>
          </div>
          <!-- Dashboard body -->
          <div style="background:var(--card);padding:1.25rem;">
            <!-- KPI row -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.625rem;margin-bottom:.875rem;">
              <?php foreach([[__('home_members'),trim($__s['hero_mock_members']??'')?:' 2,847','users','var(--primary)'],[__('home_deposits'),trim($__s['hero_mock_deposits']??'')?:'NPR 8.4 Cr','trending-up','#10b981'],[__('home_loans'),(trim($__s['hero_mock_loans']??'')?:'142').' '.__('home_active'),'credit-card','#f59e0b']] as [$l,$v,$ic,$c]): ?>
              <div style="background:var(--muted);border-radius:.625rem;padding:.75rem;">
                <div style="font-size:var(--text-3xs);color:var(--muted-foreground);margin-bottom:.25rem;display:flex;align-items:center;gap:.25rem;"><i data-lucide="<?= $ic ?>" style="width:9px;height:9px;color:<?= $c ?>;"></i><?= $l ?></div>
                <div style="font-family:var(--font-display);font-weight:800;font-size:.925rem;color:var(--foreground);"><?= $v ?></div>
              </div>
              <?php endforeach; ?>
            </div>
            <!-- Deposit trend bar chart -->
            <div style="background:var(--muted);border-radius:.625rem;padding:.875rem;margin-bottom:.875rem;">
              <div style="font-size:var(--text-3xs);font-weight:700;color:var(--muted-foreground);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.05em;"><?= __('home_deposit_trend') ?></div>
              <div style="display:flex;align-items:flex-end;gap:.2rem;height:3rem;">
                <?php foreach([35,52,48,68,60,75,82,71,90,85,95,88] as $h): ?>
                <div style="flex:1;border-radius:.15rem .15rem 0 0;background:var(--primary);opacity:<?= .35+$h/200 ?>;height:<?= $h ?>%;"></div>
                <?php endforeach; ?>
              </div>
            </div>
            <!-- Recent transactions -->
            <?php foreach([['Ramesh Koirala','Savings Deposit','+ NPR 25,000','#16a34a'],['Sita Devi','Loan EMI','– NPR 8,500','#dc2626'],['Gopal Bhatt','Share Capital','+ NPR 5,000','var(--primary)']] as [$n,$t,$a,$c]): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid var(--border);">
              <div style="display:flex;align-items:center;gap:.5rem;">
                <div style="width:1.5rem;height:1.5rem;border-radius:9999px;background:var(--primary-light);display:grid;place-items:center;flex-shrink:0;font-size:var(--text-3xs);font-weight:800;color:var(--primary);"><?= $n[0] ?></div>
                <div><div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);"><?= $n ?></div><div style="font-size:var(--text-3xs);color:var(--muted-foreground);"><?= $t ?></div></div>
              </div>
              <span style="font-size:var(--text-2xs);font-weight:700;color:<?= $c ?>;"><?= $a ?></span>
            </div>
            <?php endforeach; ?>
            <!-- Stats strip -->
            <div style="display:flex;align-items:center;gap:.375rem;margin-top:.75rem;padding:.4rem .625rem;background:rgba(37,99,235,.07);border:1px solid rgba(37,99,235,.15);border-radius:.5rem;">
              <i data-lucide="shield-check" style="width:11px;height:11px;color:var(--primary);flex-shrink:0;"></i>
              <span style="font-size:var(--text-3xs);font-weight:700;color:var(--primary);"><?= __('trust_nrb_strip') ?></span>
            </div>
          </div>
        </div>
        <!-- Floating stat chips -->
        <div class="fl-b" style="position:absolute;top:-1.25rem;right:-1.25rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:.75rem 1rem;box-shadow:var(--shadow-elevated);display:flex;align-items:center;gap:.625rem;">
          <div style="width:2rem;height:2rem;border-radius:9999px;background:rgba(34,197,94,.12);display:grid;place-items:center;">
            <i data-lucide="trending-up" style="width:14px;height:14px;color:#16a34a;"></i>
          </div>
          <div><div class="mono-meta-strong"><?= __('home_members_month') ?></div><div style="font-family:var(--font-display);font-weight:800;font-size:.9rem;color:#16a34a;"><?= e(trim($__s['hero_mock_growth']??'')?:'+14.2%') ?></div></div>
        </div>
        <div class="fl-a" style="position:absolute;bottom:-1rem;left:-1rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius-xl);padding:.75rem 1rem;box-shadow:var(--shadow-elevated);display:flex;align-items:center;gap:.625rem;">
          <div style="width:2rem;height:2rem;border-radius:9999px;background:rgba(37,99,235,.1);display:grid;place-items:center;">
            <i data-lucide="clock" style="width:14px;height:14px;color:var(--primary);"></i>
          </div>
          <div><div class="mono-meta-strong"><?= __('home_support_resp') ?></div><div style="font-family:var(--font-display);font-weight:800;font-size:.9rem;color:var(--primary);"><?= __('home_under_2hr') ?></div></div>
        </div>
      </div><!-- /hero-mock -->

    </div><!-- /hero-grid -->
  </div><!-- /container -->
</section>

<!-- ══════════════════════════════════════════════
  § 2 — STATS BAR
══════════════════════════════════════════════ -->
<?php
$statsBarAnimate = true;
include 'includes/stats-bar.php';
?>

<!-- ══════════════════════════════════════════════
  § 3 — CLIENT LOGO MARQUEE
══════════════════════════════════════════════ -->
<?php if($__logoClients): ?>
<section class="band-tinted" style="overflow:hidden;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
  <style>
  @keyframes live-pulse {
    0%,100%{box-shadow:0 0 0 2px rgba(34,197,94,.25);}
    50%{box-shadow:0 0 0 4px rgba(34,197,94,.12);}
  }
  </style>

  <!-- Section header — uniform with all other sections -->
  <div class="container">
    <div class="animate-fade-up section-head">
      <div class="section-eyebrow mb-card">
        <i data-lucide="building-2" class="ic-11"></i>
        <?= e(trim($__s['home_trust_eyebrow']??'')?:(isNepali()?'हाम्रा साझेदार':'Our Partners') ) ?>
      </div>
      <h2 class="section-title">
        <?= trim($__s['home_trust_title']??'') ?: (isNepali()
          ? 'नेपालभरका अग्रणी <span class="tg">संस्थाहरूको</span> भरोसा'
          : 'Trusted by leading <span class="tg">institutions</span> across Nepal') ?>
      </h2>
      <p class="section-lede">
        <span style="display:inline-flex;align-items:center;gap:.4rem;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);border-radius:9999px;padding:.2rem .75rem .2rem .5rem;font-size:.75rem;font-weight:700;color:#15803d;vertical-align:middle;">
          <span style="width:.45rem;height:.45rem;border-radius:9999px;background:#22c55e;box-shadow:0 0 0 2px rgba(34,197,94,.25);animation:live-pulse 2s ease-in-out infinite;flex-shrink:0;"></span>
          <span id="trust-count"><?= e(trim($__s['stat_1_value'] ?? '') ?: '120') ?></span>+&nbsp;<?= e(trim($__s['home_trust_unit']??'')?:(isNepali()?'ग्राहकहरू':'clients')) ?>
        </span>
        &nbsp;<?= e(trim($__s['home_trust_sub']??'')?:(isNepali()?'ग्राहकहरूले विश्वास गर्छन्':'clients and businesses already on board') ) ?>
      </p>
    </div>
  </div>

  <script>
  (function(){
    var el=document.getElementById('trust-count');
    if(!el||window.matchMedia('(prefers-reduced-motion:reduce)').matches)return;
    var target=parseInt(el.textContent,10)||120;
    var done=false;
    var io=new IntersectionObserver(function(e){
      if(done||!e[0].isIntersecting)return;
      done=true;io.disconnect();
      var st=Date.now(),d=900;
      (function tick(){
        var p=Math.min((Date.now()-st)/d,1);
        p=1-Math.pow(1-p,3);
        el.textContent=Math.round(target*p);
        if(p<1)requestAnimationFrame(tick);
        else el.textContent=target;
      })();
    },{threshold:0.5});
    io.observe(el);
  })();
  </script>

  <div class="marquee-wrap" style="margin-top:.5rem;padding-bottom:2.5rem;">
    <div style="display:flex;gap:1rem;align-items:center;width:max-content;animation:logo-sc 36s linear infinite;" onmouseover="this.style.animationPlayState='paused'" onmouseout="this.style.animationPlayState='running'">
      <?php for($r=0;$r<2;$r++): foreach($__logoClients as $lc): ?>
      <div style="flex-shrink:0;display:flex;align-items:center;padding:0 .375rem;">
        <?php if (!empty($lc['logo_url'])): ?>
        <img src="<?= e($lc['logo_url']) ?>" alt="<?= e($lc['org_name']) ?>" loading="lazy" decoding="async"
             class="st-marq-logo">
        <?php else: ?>
        <span class="st-marq-label">
          <i data-lucide="building-2" style="width:13px;height:13px;flex-shrink:0;color:var(--primary);opacity:.8;"></i><?= e($lc['org_name']) ?>
        </span>
        <?php endif; ?>
      </div>
      <?php endforeach; endfor; ?>
    </div>
  </div>
</section>
<?php endif; ?>
<!-- § 4 hidden — "What we build" bento grid removed -->
<?php if(false): // hidden — bento grid only ?>
<section class="band-tinted">
  <div class="container">
    <div class="animate-fade-up section-head">
      <div class="section-eyebrow section-eyebrow-primary mb-card">
        <i data-lucide="sparkles" class="ic-11"></i>
        <?= e($_bentoEyebrow ?: 'What we build') ?>
      </div>
      <h2 class="section-title">
        <?= $_bentoTitle ?: 'Everything your business needs. <span class="tg">One platform.</span>' ?>
      </h2>
      <p style="max-width:40rem;margin:0 auto;color:var(--muted-foreground);font-size:var(--text-md);line-height:1.75;">
        <?= e($_bentoSub ?: "Built from the ground up for Nepali businesses — practical, reliable and locally supported.") ?>
      </p>
    </div>

    <div id="bento" style="display:grid;grid-template-columns:1fr;gap:1.25rem;" class="stagger-children">
      <?php foreach($homeProducts as $prod):
        $prodFeats = json_decode($prod['features'] ?? '[]', true) ?: [];
        $prodHighs = json_decode($prod['highlights'] ?? '[]', true) ?: [];
        $isDark    = !empty($prod['home_card_dark']);
        $isWide    = !empty($prod['home_card_wide']);
        $iconColor = $prod['icon_color'] ?? 'blue';
        $bgCss     = $prod['home_bg_css'] ?? '';
        $textC     = $isDark ? '#f1f5f9'                : 'var(--foreground)';
        $mutedC    = $isDark ? 'rgba(241,245,249,.65)'  : 'var(--muted-foreground)';
        $chipBg    = $isDark ? 'rgba(255,255,255,.08)'  : 'rgba(37,99,235,.08)';
        $chipBord  = $isDark ? 'rgba(255,255,255,.14)'  : 'rgba(37,99,235,.15)';
        $chipCol   = $isDark ? '#93c5fd'                : 'var(--primary)';
        $cardBg    = $bgCss ?: ($isDark
          ? 'background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 100%)'
          : 'background:linear-gradient(135deg,#eff6ff 0%,#f0fdf4 100%)');
        $cardStyle = $cardBg . ($isDark ? ';border-color:#1e293b' : '');
      ?>
      <div class="bc <?= $isWide ? 'bw' : '' ?>" style="<?= e($cardStyle) ?>">
        <?php if($isDark): ?>
        <div style="position:absolute;top:-2rem;right:1.5rem;width:8rem;height:8rem;border-radius:9999px;background:radial-gradient(circle,rgba(37,99,235,.35),transparent);filter:blur(16px);pointer-events:none;"></div>
        <?php endif; ?>
        <div style="position:relative;display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;">
          <div class="icon-box icon-box-<?= e($iconColor) ?>" style="width:2.75rem;height:2.75rem;border-radius:.875rem;flex-shrink:0;">
            <?php if(!empty($prod['lucide_icon'])): ?>
              <i data-lucide="<?= e($prod['lucide_icon']) ?>" style="width:18px;height:18px;color:#fff;"></i>
            <?php else: ?>
              <span style="font-size:1.25rem;line-height:1;"><?= e($prod['icon'] ?? '📦') ?></span>
            <?php endif; ?>
          </div>
          <div>
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.375rem;flex-wrap:wrap;">
              <h3 style="font-family:var(--font-display);font-weight:800;color:<?= $textC ?>;margin:0;"><?= e($prod['name']) ?></h3>
              <?php if(!empty($prod['badge'])): ?>
              <span style="font-size:var(--text-3xs);padding:.15rem .5rem;border-radius:9999px;background:#dbeafe;color:var(--primary-dark);font-weight:700;"><?= e($prod['badge']) ?></span>
              <?php endif; ?>
            </div>
            <p style="color:<?= $mutedC ?>;font-size:var(--text-sm);line-height:1.65;margin:0;"><?= e($prod['summary'] ?? '') ?></p>
          </div>
        </div>
        <?php if($prodFeats): ?>
        <div style="position:relative;display:flex;flex-wrap:wrap;gap:.5rem;">
          <?php foreach(array_slice($prodFeats,0,10) as $chip): ?>
          <span style="display:inline-flex;align-items:center;gap:.25rem;padding:.2rem .7rem;border-radius:9999px;background:<?= $chipBg ?>;border:1px solid <?= $chipBord ?>;font-size:var(--text-xs);font-weight:600;color:<?= $chipCol ?>;">
            <i data-lucide="check" class="ic-9"></i><?= e($chip) ?>
          </span>
          <?php endforeach; ?>
        </div>
        <?php elseif($prodHighs): ?>
        <div style="position:relative;display:flex;flex-direction:column;gap:.5rem;">
          <?php foreach(array_slice($prodHighs,0,6) as $f): ?>
          <div style="display:flex;align-items:center;gap:.5rem;font-size:var(--text-xs);font-weight:600;color:<?= $textC ?>;">
            <i data-lucide="check-circle" style="width:13px;height:13px;color:<?= $isDark?'#86efac':'var(--primary)' ?>;flex-shrink:0;"></i><?= e($f) ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div><!-- /bento -->

    <div class="animate-fade-up section-foot">
      <a href="<?= url('services.php') ?>" class="btn btn-outline btn-md">
        <i data-lucide="layers" class="ic-15"></i>
        See all services in detail
      </a>
    </div>
  </div>
</section>
<?php endif; // end hidden § 4 bento grid ?>

<!-- ══════════════════════════════════════════════
  § 5 — INTERACTIVE PRODUCT TABS  (DB-driven — set screenshots from Admin → Products)
══════════════════════════════════════════════ -->
<section class="band">
  <div class="container">
    <div class="animate-fade-up section-head">
      <div class="section-eyebrow mb-card">
        <i data-lucide="monitor" class="ic-11"></i>
        <?= e(trim($__s['home_products_eyebrow']??'')?:'Product deep-dive') ?>
      </div>
      <h2 style="font-family:var(--font-display);font-weight:800;letter-spacing:-.025em;color:var(--foreground);">
        <?= e($_inActionTitle ?: 'See it in action') ?>
      </h2>
      <p style="max-width:34rem;margin:.875rem auto 0;color:var(--muted-foreground);">
        <?= e($_inActionSub ?: 'Explore the actual screens your team and members will use every day.') ?>
      </p>
    </div>

    <?php if($homeProducts): ?>
    <!-- Tab buttons -->
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:.375rem;margin-bottom:2.5rem;padding:.375rem;background:var(--muted);border-radius:var(--radius-xl);max-width:fit-content;margin-inline:auto;" role="tablist">
      <?php foreach($homeProducts as $i=>$prod):
        $tSlug  = $prod['slug'];
        $tLabel = $prod['tab_label'] ?: $prod['name'];
        $tIcon  = $prod['lucide_icon'] ?: 'box';
      ?>
      <button class="prod-tab <?= $i===0?'active':'' ?>" role="tab" data-tab="<?= e($tSlug) ?>" onclick="sTab('<?= e($tSlug) ?>')">
        <i data-lucide="<?= e($tIcon) ?>" style="width:13px;height:13px;display:inline;vertical-align:middle;margin-right:.25rem;"></i>
        <?= e($tLabel) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Tab panes -->
    <?php foreach($homeProducts as $i=>$prod):
      $tSlug   = $prod['slug'];
      $pFeats  = json_decode($prod['features']   ?? '[]', true) ?: [];
      $pHighs  = json_decode($prod['highlights'] ?? '[]', true) ?: [];
      $tIcon   = $prod['lucide_icon'] ?: 'box';
      $tColor  = $prod['icon_color'] ?? 'blue';
      $hasSS   = !empty($prod['demo_screenshot_url']);
    ?>
    <div id="tab-<?= e($tSlug) ?>" class="tab-pane <?= $i===0?'active':'' ?>">
      <?php if($hasSS): ?>
      <!-- Admin-uploaded product screenshot -->
      <div style="max-width:52rem;margin:0 auto;border-radius:var(--radius-2xl);overflow:hidden;box-shadow:0 24px 80px rgba(15,23,42,.12);border:1px solid var(--border);">
        <div class="wc">
          <span class="wd dot-danger"></span>
          <span class="wd dot-warning"></span>
          <span class="wd dot-success"></span>
          <div class="pill-row">
            <i data-lucide="lock" class="ic-9 text-success"></i>
            <span class="mono-meta"><?= e($prod['name']) ?></span>
          </div>
        </div>
        <img src="<?= e($prod['demo_screenshot_url']) ?>"
             alt="<?= e($prod['name']) ?> — product screenshot" loading="lazy"
             style="width:100%;display:block;max-height:34rem;object-fit:cover;object-position:top;">
      </div>
      <?php else: ?>
      <!-- No screenshot yet: show feature grid with CTA -->
      <div style="max-width:52rem;margin:0 auto;border-radius:var(--radius-2xl);border:1px solid var(--border);overflow:hidden;box-shadow:0 12px 40px rgba(15,23,42,.08);">
        <div class="wc">
          <span class="wd dot-danger"></span>
          <span class="wd dot-warning"></span>
          <span class="wd dot-success"></span>
          <div class="pill-row">
            <i data-lucide="lock" class="ic-9 text-success"></i>
            <span class="mono-meta"><?= e($prod['name']) ?></span>
          </div>
        </div>
        <div style="background:var(--card);padding:2rem 2rem 1.75rem;">
          <div style="display:flex;align-items:center;gap:.875rem;margin-bottom:1.5rem;">
            <div class="icon-box icon-box-<?= e($tColor) ?>" style="width:3rem;height:3rem;border-radius:1rem;flex-shrink:0;">
              <i data-lucide="<?= e($tIcon) ?>" style="width:20px;height:20px;color:#fff;"></i>
            </div>
            <div>
              <h3 style="font-family:var(--font-display);font-weight:800;color:var(--foreground);margin:0 0 .25rem;"><?= e($prod['name']) ?></h3>
              <?php if($prod['tagline']): ?>
              <p style="color:var(--muted-foreground);font-size:var(--text-sm);margin:0;"><?= e($prod['tagline']) ?></p>
              <?php endif; ?>
            </div>
          </div>
          <?php if($pFeats): ?>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(14rem,1fr));gap:.625rem;margin-bottom:1.5rem;">
            <?php foreach(array_slice($pFeats,0,8) as $f): ?>
            <div style="display:flex;align-items:center;gap:.5rem;padding:.75rem .875rem;background:var(--muted);border-radius:.625rem;font-size:var(--text-sm);font-weight:600;color:var(--foreground);">
              <i data-lucide="check-circle" style="width:14px;height:14px;color:var(--primary);flex-shrink:0;"></i><?= e($f) ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if($prod['summary']): ?>
          <p style="margin:0 0 1.5rem;color:var(--muted-foreground);font-size:var(--text-sm);line-height:1.75;"><?= e($prod['summary']) ?></p>
          <?php endif; ?>
          <a href="<?= url('contact.php?product='.urlencode($prod['name'])) ?>" class="btn btn-primary btn-md">
            <i data-lucide="calendar" class="ic-14"></i>
            <?= e(trim($__s['home_tab_demo_cta']??'')?:__('cta_demo')) ?> — <?= e($prod['name']) ?>
          </a>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    <p style="text-align:center;color:var(--muted-foreground);padding:3rem 0;">
      <?= e(isNepali() ? 'कुनै उत्पादन कन्फिगर गरिएको छैन।' : 'No products configured.') ?>
      <a href="<?= url('admin/products.php') ?>" class="text-primary"><?= e(isNepali() ? 'Admin → उत्पादनहरूबाट थप्नुस।' : 'Add products from the admin panel.') ?></a>
    </p>
    <?php endif; ?>

  </div>
</section>
<script>
function sTab(slug){
  document.querySelectorAll('.prod-tab').forEach(function(t){t.classList.toggle('active',t.dataset.tab===slug);});
  document.querySelectorAll('.tab-pane').forEach(function(p){p.classList.toggle('active',p.id==='tab-'+slug);});
}
</script>

<!-- ══════════════════════════════════════════════
  § 6 — PROCESS  (4 steps from call to go-live)
  Unique section — not mentioned anywhere else
══════════════════════════════════════════════ -->
<section class="band-tinted">
  <div class="container">
    <div class="animate-fade-up section-head">
      <div class="section-eyebrow mb-card">
        <i data-lucide="map" class="ic-11"></i>
        <?= e(trim($__s['home_process_eyebrow']??'')?:'Getting started') ?>
      </div>
      <h2 class="section-title"><?= e(trim($__s['home_process_title']??'')?:'From first call to go-live — 4 steps') ?></h2>
      <p style="color:var(--muted-foreground);max-width:38rem;margin:0 auto;"><?= e(trim($__s['home_process_sub']??'')?:'We handle the full implementation — data migration, staff training and 30-day post-launch hand-holding.') ?></p>
    </div>
    <div id="proc-grid" class="stagger-children grid-1">
      <?php foreach($processSteps as $i=>[$icon,$title,$desc]): ?>
      <div class="pi" style="text-align:center;padding:2rem 1.5rem;background:var(--card);border:1px solid var(--border);border-radius:var(--radius-2xl);">
        <?php if($i<3): ?><div class="proc-con"></div><?php endif; ?>
        <div style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:3.5rem;height:3.5rem;border-radius:9999px;background:var(--primary-light);border:2px solid rgba(37,99,235,.2);margin-bottom:1.25rem;">
          <i data-lucide="<?= e($icon) ?>" style="width:20px;height:20px;color:var(--primary);"></i>
          <span style="position:absolute;top:-6px;right:-6px;width:1.375rem;height:1.375rem;border-radius:9999px;background:var(--primary);color:#fff;font-size:var(--text-2xs);font-weight:800;display:grid;place-items:center;font-family:var(--font-display);"><?= $i+1 ?></span>
        </div>
        <h3 style="font-family:var(--font-display);font-weight:700;color:var(--foreground);margin-bottom:.625rem;"><?= e($title) ?></h3>
        <p style="font-size:var(--text-sm);color:var(--muted-foreground);line-height:1.65;max-width:18rem;margin:0 auto;"><?= e($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="animate-fade-up section-foot">
      <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-md">
        <i data-lucide="calendar" class="ic-15"></i>
        <?= e(trim($__s['home_process_cta']??'')?:'Start your discovery call') ?>
      </a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════
  § 7 — TESTIMONIALS  (auto-scroll two-row marquee)
══════════════════════════════════════════════ -->
<?php if($testimonials):
  $tRow1 = array_values(array_filter($testimonials, fn($i) => true, ARRAY_FILTER_USE_KEY));
  $tRow2 = array_reverse($tRow1);
?>
<section class="band" style="overflow:hidden;">

  <div class="container section-head">
    <div class="section-eyebrow mb-card">
      <i data-lucide="star" class="ic-11" style="fill:currentColor;"></i>
      <?= e(__('home_testi_eyebrow')) ?>
    </div>
    <h2 class="section-title whitespace-nowrap"><?= __('home_testi_title') ?></h2>
    <p class="section-lede"><?= e(__('home_testi_sub')) ?></p>
  </div>

  <?php
  $renderCard = function(array $t, bool $dark = false) {
    $cls = $dark ? 'testi-card--dark' : 'testi-card--light';
    $rating = (int)($t['rating'] ?? 5);
    ?>
    <div class="testi-card <?= $cls ?>">
      <span class="testi-card__quote-mark" aria-hidden="true">"</span>
      <div class="testi-card__stars" aria-label="<?= $rating ?> out of 5 stars">
        <?php for ($i = 0; $i < $rating; $i++): ?>
        <svg viewBox="0 0 24 24" aria-hidden="true"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>
        <?php endfor; ?>
      </div>
      <p class="testi-card__text">"<?= e($t['quote']) ?>"</p>
      <div class="testi-card__author">
        <div class="testi-card__avatar"><?= strtoupper(substr($t['author_name'], 0, 1)) ?></div>
        <div>
          <div class="testi-card__name"><?= e($t['author_name']) ?></div>
          <div class="testi-card__role"><?= e(trim(($t['author_role'] ?? '') . ($t['author_org'] ? ' · ' . $t['author_org'] : ''))) ?></div>
        </div>
      </div>
    </div>
    <?php
  };
  ?>

  <!-- Row 1: scroll left -->
  <div class="marquee-wrap">
    <div class="testi-track testi-track-l" style="animation:testi-l 40s linear infinite;"
         onmouseover="this.style.animationPlayState='paused'" onmouseout="this.style.animationPlayState='running'">
      <?php for($r=0;$r<2;$r++): foreach($tRow1 as $idx=>$t): $renderCard($t, $idx===1||$idx===4); endforeach; endfor; ?>
    </div>
  </div>

</section>
<?php endif; ?>
<!-- ══════════════════════════════════════════════
  § 8 — PRICING TEASER  (3 plans)
══════════════════════════════════════════════ -->
<section class="band-tinted">
  <div class="container">
    <div class="animate-fade-up section-head">
      <div class="section-eyebrow mb-card">
        <i data-lucide="tag" class="ic-11"></i>
        <?= e(trim($__s['home_pricing_eyebrow']??'')?:'Simple pricing') ?>
      </div>
      <h2 class="section-title"><?= e(trim($__s['home_pricing_title']??'')?:'Plans for every business') ?></h2>
      <p class="section-lede"><?= e(trim($__s['home_pricing_sub']??'')?:'No hidden fees. Upgrade any time. Local support included in every plan.') ?></p>
    </div>
    <?php include 'includes/pricing-teaser.php'; ?>
    <div class="section-foot animate-fade-up">
      <a href="<?= url('pricing.php') ?>" class="arr">
        <?= e(__('home_pricing_link')) ?> <i data-lucide="arrow-right" class="ic-14"></i>
      </a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════
  § 9 — NEWS / BLOG TEASER  (only if DB has records)
══════════════════════════════════════════════ -->
<?php if($newsItems): ?>
<section class="band">
  <div class="container">
    <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:3rem;" class="animate-fade-up">
      <div>
        <div class="section-eyebrow" style="margin-bottom:.75rem;"><?= e(trim($__s['home_news_eyebrow']??'')?:'Latest from us') ?></div>
        <h2 style="font-family:var(--font-display);font-weight:800;letter-spacing:-.025em;color:var(--foreground);margin:0;"><?= e(trim($__s['home_news_title']??'')?:'News & updates') ?></h2>
      </div>
      <a href="<?= url('news.php') ?>" class="btn btn-outline btn-sm"><?= e(__('home_news_view_all')) ?> <i data-lucide="arrow-right" class="ic-13"></i></a>
    </div>
    <div id="news-grid" class="stagger-children" style="display:grid;grid-template-columns:repeat(3,1fr);gap:.875rem;">
      <?php foreach($newsItems as $article): ?>
      <div class="st-card" style="overflow:hidden;">
        <?php if(!empty($article['cover_image'])): ?>
        <img src="<?= e($article['cover_image']) ?>" alt="<?= e($article['title']) ?>" style="width:100%;height:6.5rem;object-fit:cover;" loading="lazy">
        <?php else: ?>
        <div style="width:100%;height:6.5rem;background:var(--gradient-primary);display:grid;place-items:center;"><i data-lucide="newspaper" style="width:20px;height:20px;color:rgba(255,255,255,.35);"></i></div>
        <?php endif; ?>
        <div style="padding:1rem;">
          <?php if(!empty($article['category'])): ?><span style="font-size:var(--text-3xs);font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--primary);display:block;margin-bottom:.375rem;"><?= e($article['category']) ?></span><?php endif; ?>
          <h3 style="font-family:var(--font-display);font-weight:700;font-size:var(--text-sm);color:var(--foreground);margin-bottom:.375rem;line-height:1.35;"><?= e($article['title']) ?></h3>
          <p style="font-size:var(--text-xs);color:var(--muted-foreground);line-height:1.55;margin-bottom:.75rem;"><?= e(mb_strimwidth($article['excerpt']??'',0,80,'…')) ?></p>
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:.25rem;font-size:var(--text-3xs);color:var(--muted-foreground);">
              <i data-lucide="calendar" class="ic-10"></i>
              <?= !empty($article['published_at'])?date('d M Y',strtotime($article['published_at'])):'' ?>
            </div>
            <a href="<?= url('news-post.php?id='.($article['id']??'')) ?>" class="arr" style="font-size:var(--text-3xs);">
              <?= e(__('cta_read_more')) ?> <i data-lucide="arrow-right" class="ic-10"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════
  § 10 — FINAL CTA BANNER
══════════════════════════════════════════════ -->
<?php
$ctaEyebrow     = trim($__s['home_cta_eyebrow']??'') ?: (isNepali() ? 'तपाईं तयार हुँदा हामी पनि' : 'Ready when you are');
$ctaEyebrowIcon = 'rocket';
$ctaTitle       = __('cta_title');
$ctaSubtitle    = __('cta_sub');
$ctaPrimary     = ['label' => __('home_hero_book_demo'), 'url' => url('contact.php'), 'icon' => 'calendar'];
$ctaSecondary   = ['label' => __('cta_see_pricing'), 'url' => url('pricing.php'), 'icon' => 'tag'];
$ctaTrustPills  = [
  ['check',  __('trust_no_contract')],
  ['phone',  __('trust_local_support')],
  ['lock',   __('trust_data_nepal')],
  ['shield', __('trust_nrb')],
];
include 'includes/cta-banner.php';
?>
<?php include 'includes/footer.php'; ?>
