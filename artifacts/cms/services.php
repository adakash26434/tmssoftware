<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';
$pageTitle = 'Services — What Ankur Infotech Pvt. Ltd. Offers | Cooperative Software Nepal';
$pageDesc  = 'Software development, web design, document management, HR & payroll, IT support and more — full-service IT solutions for businesses in Nepal.';

$__s = siteSettings();

// Default services (fallback if DB is empty or no active rows)
$__svcDefaults = [
  ['icon-box-blue',   'cloud',        'Cloud Services',             'Scalable, secure cloud infrastructure for businesses across Nepal. Managed servers, automated backups, 99.9% uptime SLA and 24×7 NOC monitoring — Nepal-hosted.', 'Managed Servers,Auto Backups,99.9% Uptime SLA,24×7 NOC,Disaster Recovery,Nepal-hosted Data'],
  ['icon-box-teal',   'globe',        'Domain & Hosting Services',  'Register .com.np, .org.np and international domains with local support. Blazing-fast SSD hosting, free SSL, email hosting and a Nepal-based control panel — everything under one roof.', '.com.np Registration,Free SSL,SSD Hosting,Email Hosting,DNS Management,24×7 Support'],
  ['icon-box-amber',  'message-square','Bulk SMS Services',          'High-delivery bulk SMS gateway for businesses — send transaction alerts, reminders, OTPs and promotional messages instantly across all Nepal telecom networks.', 'Ncell & NTC Gateway,OTP / 2FA,Transaction Alerts,EMI Reminders,Scheduled Campaigns,Delivery Reports'],
  ['icon-box-rose',   'shield-check', 'Security Audit Service',     'End-to-end cybersecurity audit and penetration testing for software, web portals and network infrastructure. Identify vulnerabilities before attackers do.', 'Penetration Testing,Vulnerability Scan,IT Compliance Audit,Source Code Review,Network Audit,Audit Report'],
];

// Color-class map (icon_color DB value → CSS class)
$__svcColorMap = [
  'blue'=>'icon-box-blue','teal'=>'icon-box-teal','purple'=>'icon-box-purple',
  'amber'=>'icon-box-amber','green'=>'icon-box-green','rose'=>'icon-box-rose',
  'orange'=>'icon-box-orange','indigo'=>'icon-box-blue','gray'=>'icon-box-blue',
];

// Fetch from DB (admin/services.php manages this table)
$services = [];
try {
    $__dbSvcs = query("SELECT id,title,slug,summary,icon,icon_color,features,active FROM services WHERE active=1 ORDER BY position,id");
    foreach ($__dbSvcs as $__r) {
        $__fStr = '';
        if (!empty($__r['features'])) {
            $__dec = json_decode($__r['features'], true);
            $__fStr = is_array($__dec) ? implode(',', $__dec) : (string)$__r['features'];
        }
        $__cls = $__svcColorMap[strtolower($__r['icon_color'] ?? 'blue')] ?? 'icon-box-blue';
        $services[] = [$__cls, $__r['icon'] ?: 'layers', $__r['title'], $__r['summary'] ?: '', $__fStr];
    }
} catch(\Throwable $e) {}

// Fallback to built-in defaults when DB has no active services
if (empty($services)) {
    $services = $__svcDefaults;
}

include 'includes/header.php';
?>

<!-- styles: assets/css/pages.css -->

<!-- ═══════ HERO ═══════ -->
<?php
$heroEyebrow     = __('services_hero_eyebrow');
$heroEyebrowIcon = 'layers';
$heroTitle       = __('services_hero_title');
$heroSubtitle    = __('services_hero_sub');
ob_start(); ?>
<a href="<?= url('contact.php') ?>" class="btn btn-primary btn-lg">
  <i data-lucide="calendar" class="ic-16"></i>
  <?= __('cta_talk_expert') ?>
</a>
<?php $heroActions = ob_get_clean(); include 'includes/page-hero.php'; ?>

<!-- ═══════ SERVICES ═══════ -->
<section class="st-section">
  <div class="container">
    <div style="display:flex;flex-direction:column;gap:5rem;">
      <?php
$__svcIcons = ['cloud','globe','message-square','shield-check'];
$__svcMockups = [
/* Cloud Services */ '
<div class="mockup-wrap">
  <div class="mockup-chrome">
    <div class="row-tight"><span class="mockup-dot dot-danger"></span><span class="mockup-dot dot-warning"></span><span class="mockup-dot dot-success"></span></div>
    <span style="flex:1;text-align:center;font-size:var(--text-2xs);color:var(--muted-foreground);font-family:monospace;">Cloud Dashboard — NOC Monitor</span>
  </div>
  <div class="p-card-sm">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.625rem;margin-bottom:0.875rem;">
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.5rem 0.625rem;">
        <div style="font-size:var(--text-2xs);font-weight:800;color:#16a34a;font-family:var(--font-display);">99.9%</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Uptime</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.5rem 0.625rem;">
        <div style="font-size:var(--text-2xs);font-weight:800;color:var(--primary);font-family:var(--font-display);">12</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Servers</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.5rem 0.625rem;">
        <div style="font-size:var(--text-2xs);font-weight:800;color:#d97706;font-family:var(--font-display);">Daily</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Backups</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.5rem 0.625rem;">
        <div style="font-size:var(--text-2xs);font-weight:800;color:#8b5cf6;font-family:var(--font-display);">2 TB</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Storage</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <span style="width:0.5rem;height:0.5rem;border-radius:9999px;background:#16a34a;flex-shrink:0;"></span>
      <span style="flex:1;font-size:var(--text-3xs);color:var(--foreground);font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">ankurinfotech.com.np</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Active</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <span style="width:0.5rem;height:0.5rem;border-radius:9999px;background:#16a34a;flex-shrink:0;"></span>
      <span style="flex:1;font-size:var(--text-3xs);color:var(--foreground);font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">dms.pokhara-sahakari.com.np</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Active</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <span style="width:0.5rem;height:0.5rem;border-radius:9999px;background:#d97706;flex-shrink:0;"></span>
      <span style="flex:1;font-size:var(--text-3xs);color:var(--foreground);font-family:monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">api.lumbini-coop.org</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#d97706;white-space:nowrap;">Maintenance</span>
    </div>
    <div style="margin-top:0.75rem;display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0.625rem;background:rgba(37,99,235,0.07);border:1px solid rgba(37,99,235,0.15);border-radius:0.5rem;">
      <i data-lucide="map-pin" style="width:11px;height:11px;color:var(--primary);flex-shrink:0;"></i>
      <span style="font-size:var(--text-2xs);font-weight:700;color:var(--primary);">Data hosted in Nepal · NOC 24×7</span>
    </div>
  </div>
</div>',
/* Domain & Hosting */ '
<div class="mockup-wrap">
  <div class="mockup-chrome">
    <div class="row-tight"><span class="mockup-dot dot-danger"></span><span class="mockup-dot dot-warning"></span><span class="mockup-dot dot-success"></span></div>
    <div style="flex:1;background:var(--background);border-radius:0.25rem;padding:0.15rem 0.5rem;display:flex;align-items:center;gap:0.3rem;">
      <i data-lucide="shield-check" style="width:9px;height:9px;color:#16a34a;flex-shrink:0;"></i>
      <span style="font-size:var(--text-3xs);color:var(--muted-foreground);font-family:monospace;">Domain Manager</span>
    </div>
  </div>
  <div class="p-card-sm">
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="globe" style="width:11px;height:11px;color:var(--primary);flex-shrink:0;"></i>
      <div style="flex:1;min-width:0;">
        <div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">himalaya-coop.com.np</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Expires: 2082 Baishakh</div>
      </div>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Registered</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="globe" style="width:11px;height:11px;color:var(--primary);flex-shrink:0;"></i>
      <div style="flex:1;min-width:0;">
        <div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">seti-saving.org.np</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Expires: 2082 Ashwin</div>
      </div>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Registered</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="globe" style="width:11px;height:11px;color:var(--primary);flex-shrink:0;"></i>
      <div style="flex:1;min-width:0;">
        <div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">newroad-finance.com</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Expires: 2081 Shrawan</div>
      </div>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#d97706;white-space:nowrap;">Expiring</span>
    </div>
    <div style="margin-top:0.75rem;display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
      <div style="display:flex;align-items:center;gap:0.375rem;padding:0.35rem 0.5rem;background:var(--card);border:1px solid var(--border);border-radius:0.375rem;">
        <i data-lucide="shield-check" style="width:10px;height:10px;color:#16a34a;flex-shrink:0;"></i>
        <span style="font-size:var(--text-3xs);font-weight:600;color:var(--foreground);">Free SSL</span>
      </div>
      <div style="display:flex;align-items:center;gap:0.375rem;padding:0.35rem 0.5rem;background:var(--card);border:1px solid var(--border);border-radius:0.375rem;">
        <i data-lucide="zap" style="width:10px;height:10px;color:var(--primary);flex-shrink:0;"></i>
        <span style="font-size:var(--text-3xs);font-weight:600;color:var(--foreground);">SSD Hosting</span>
      </div>
      <div style="display:flex;align-items:center;gap:0.375rem;padding:0.35rem 0.5rem;background:var(--card);border:1px solid var(--border);border-radius:0.375rem;">
        <i data-lucide="mail" style="width:10px;height:10px;color:#8b5cf6;flex-shrink:0;"></i>
        <span style="font-size:var(--text-3xs);font-weight:600;color:var(--foreground);">Email Hosting</span>
      </div>
      <div style="display:flex;align-items:center;gap:0.375rem;padding:0.35rem 0.5rem;background:var(--card);border:1px solid var(--border);border-radius:0.375rem;">
        <i data-lucide="settings" style="width:10px;height:10px;color:#d97706;flex-shrink:0;"></i>
        <span style="font-size:var(--text-3xs);font-weight:600;color:var(--foreground);">DNS Mgmt</span>
      </div>
    </div>
  </div>
</div>',
/* Bulk SMS */ '
<div class="mockup-wrap">
  <div class="mockup-chrome">
    <div class="row-tight"><span class="mockup-dot dot-danger"></span><span class="mockup-dot dot-warning"></span><span class="mockup-dot dot-success"></span></div>
    <span style="flex:1;text-align:center;font-size:var(--text-2xs);color:var(--muted-foreground);font-family:monospace;">SMS Campaign Dashboard</span>
  </div>
  <div class="p-card-sm">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.5rem;margin-bottom:0.875rem;">
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem;text-align:center;">
        <div style="font-size:0.9rem;font-weight:800;color:var(--primary);font-family:var(--font-display);">1,240</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Sent</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem;text-align:center;">
        <div style="font-size:0.9rem;font-weight:800;color:#16a34a;font-family:var(--font-display);">1,198</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Delivered</div>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem;text-align:center;">
        <div style="font-size:0.9rem;font-weight:800;color:#dc2626;font-family:var(--font-display);">42</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Failed</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="message-square" style="width:12px;height:12px;color:#d97706;flex-shrink:0;"></i>
      <div style="flex:1;">
        <div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);">EMI Reminder — Shrawan</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">1,200 recipients</div>
      </div>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#d97706;white-space:nowrap;">Scheduled</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="message-square" style="width:12px;height:12px;color:#16a34a;flex-shrink:0;"></i>
      <div style="flex:1;">
        <div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);">AGM Notice 2081</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">850 recipients</div>
      </div>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Sent</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="message-square" style="width:12px;height:12px;color:var(--primary);flex-shrink:0;"></i>
      <div style="flex:1;">
        <div style="font-size:var(--text-2xs);font-weight:600;color:var(--foreground);">OTP Verification</div>
        <div style="font-size:var(--text-3xs);color:var(--muted-foreground);">Real-time</div>
      </div>
      <span style="font-size:var(--text-3xs);font-weight:700;color:var(--primary);white-space:nowrap;">Live</span>
    </div>
    <div style="margin-top:0.75rem;display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0.625rem;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:0.5rem;">
      <i data-lucide="zap" style="width:11px;height:11px;color:#d97706;flex-shrink:0;"></i>
      <span style="font-size:var(--text-2xs);font-weight:700;color:#d97706;">Ncell & NTC · 96.6% delivery rate</span>
    </div>
  </div>
</div>',
/* Security Audit */ '
<div class="mockup-wrap">
  <div class="mockup-chrome">
    <div class="row-tight"><span class="mockup-dot dot-danger"></span><span class="mockup-dot dot-warning"></span><span class="mockup-dot dot-success"></span></div>
    <span style="flex:1;text-align:center;font-size:var(--text-2xs);color:var(--muted-foreground);font-family:monospace;">Security Audit Report</span>
  </div>
  <div class="p-card-sm">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:0.875rem;">
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem 0.625rem;display:flex;align-items:center;gap:0.375rem;">
        <span style="font-size:0.9rem;font-weight:800;color:#16a34a;font-family:var(--font-display);">0</span>
        <span style="font-size:var(--text-3xs);color:var(--muted-foreground);">Critical</span>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem 0.625rem;display:flex;align-items:center;gap:0.375rem;">
        <span style="font-size:0.9rem;font-weight:800;color:#dc2626;font-family:var(--font-display);">2</span>
        <span style="font-size:var(--text-3xs);color:var(--muted-foreground);">High</span>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem 0.625rem;display:flex;align-items:center;gap:0.375rem;">
        <span style="font-size:0.9rem;font-weight:800;color:#d97706;font-family:var(--font-display);">5</span>
        <span style="font-size:var(--text-3xs);color:var(--muted-foreground);">Medium</span>
      </div>
      <div style="background:var(--card);border:1px solid var(--border);border-radius:0.5rem;padding:0.4rem 0.625rem;display:flex;align-items:center;gap:0.375rem;">
        <span style="font-size:0.9rem;font-weight:800;color:var(--primary);font-family:var(--font-display);">47</span>
        <span style="font-size:var(--text-3xs);color:var(--muted-foreground);">Passed</span>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="shield" style="width:11px;height:11px;color:#16a34a;flex-shrink:0;"></i>
      <span style="flex:1;font-size:var(--text-2xs);color:var(--foreground);">SQL Injection Test</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Passed</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="shield" style="width:11px;height:11px;color:#dc2626;flex-shrink:0;"></i>
      <span style="flex:1;font-size:var(--text-2xs);color:var(--foreground);">XSS Vulnerability Scan</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#dc2626;white-space:nowrap;">2 Found</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="shield" style="width:11px;height:11px;color:#16a34a;flex-shrink:0;"></i>
      <span style="flex:1;font-size:var(--text-2xs);color:var(--foreground);">SSL/TLS Configuration</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Passed</span>
    </div>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.45rem 0;border-bottom:1px solid var(--border);">
      <i data-lucide="shield" style="width:11px;height:11px;color:#16a34a;flex-shrink:0;"></i>
      <span style="flex:1;font-size:var(--text-2xs);color:var(--foreground);">IT Compliance</span>
      <span style="font-size:var(--text-3xs);font-weight:700;color:#16a34a;white-space:nowrap;">Compliant</span>
    </div>
    <div style="margin-top:0.75rem;display:flex;align-items:center;gap:0.5rem;padding:0.4rem 0.625rem;background:rgba(220,38,38,0.07);border:1px solid rgba(220,38,38,0.2);border-radius:0.5rem;">
      <i data-lucide="file-text" style="width:11px;height:11px;color:#dc2626;flex-shrink:0;"></i>
      <span style="font-size:var(--text-2xs);font-weight:700;color:#dc2626;">Full PDF report · IT security compliant</span>
    </div>
  </div>
</div>',
];
      foreach ($services as $idx => [$box,$icon,$title,$desc,$features_str]):
        $features = explode(',', $features_str);
        $reverse  = $idx % 2 === 1;
      ?>
      <div style="display:grid;grid-template-columns:1fr;gap:2.5rem;align-items:center;" class="service-row <?= $reverse ? 'animate-slide-right' : 'animate-slide-left' ?>">
        <!-- Text side -->
        <div style="order:<?= $reverse ? 2 : 1 ?>;">
          <div class="icon-box <?= $box ?>" style="margin-bottom:1.375rem;width:3.25rem;height:3.25rem;border-radius:var(--radius-xl);">
            <i data-lucide="<?= $icon ?>" style="width:20px;height:20px;color:#fff;"></i>
          </div>
          <h2 style="font-family:var(--font-display);font-weight:800;letter-spacing:-0.02em;color:var(--foreground);margin-bottom:0.875rem;"><?= e($title) ?></h2>
          <p style="color:var(--muted-foreground);line-height:1.75;margin-bottom:1.75rem;font-size:var(--text-md);"><?= e($desc) ?></p>
          <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:2rem;">
            <?php foreach ($features as $f): ?>
            <span class="feature-chip">
              <i data-lucide="check" style="width:10px;height:10px;color:var(--primary);flex-shrink:0;"></i>
              <?= e(trim($f)) ?>
            </span>
            <?php endforeach; ?>
          </div>
          <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-md">
            <?= e(__('services_get_quote')) ?>
            <i data-lucide="arrow-right" class="ic-14"></i>
          </a>
        </div>
        <!-- Visual mockup -->
        <div style="order:<?= $reverse ? 1 : 2 ?>;">
          <?= $__svcMockups[$idx] ?? '' ?>
        </div>
      </div>
      <?php if ($idx < count($services)-1): ?>
      <div style="height:1px;background:var(--border);"></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════ WHY CHOOSE ═══════ -->
<section class="st-section st-section--tinted">
  <div class="container">
    <div class="animate-fade-up section-head">
      <div class="section-eyebrow mb-1"><?= e(cms($__s,'services_section_eyebrow','Why choose us')) ?></div>
      <h2 class="h-display"><?= e(cms($__s,'services_why_title',__('services_why_label'))) ?></h2>
      <?php $__whySub = cms($__s,'services_why_subtitle',''); if ($__whySub): ?>
      <p class="section-sub"><?= e($__whySub) ?></p>
      <?php endif; ?>
    </div>
    <div class="why-grid stagger-children" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;">
      <?php foreach ([
        ['map-pin',   isNepali()?'नेपाल-केन्द्रित':'Nepal-first',       isNepali()?'सबै प्रदेशमा कार्यालय — आवश्यक पर्दा अन-साइट सहयोग।':'Offices across all provinces — on-site support when you need it.'],
        ['shield',    __('body_secure_default'), __('body_secure_desc')],
        ['zap',       isNepali()?'द्रुत डिप्लोयमेन्ट':'Fast deployment', isNepali()?'वेबसाइट २ हप्तामा, मोबाइल एप ३ हप्तामा — छिटो र भरपर्दो।':'Website live in 2 weeks, mobile app in 3 — fast and reliable.'],
        ['headphones',__('body_always_on'), __('body_always_on_desc')],
        ['calendar',  isNepali()?'BS पात्रो':'BS Calendar', isNepali()?'हरेक मोड्युलमा नेपाली पात्रो नेटिभ — कुनै रूपान्तरण आवश्यक छैन।':'Nepali calendar native in every module — no conversion.'],
        ['file-check',__('body_nrb_aligned'), __('body_nrb_aligned_desc')],
      ] as [$icon,$t,$d]): ?>
      <div class="feature-card text-center">
        <div class="feature-card__icon" style="margin-inline:auto;">
          <i data-lucide="<?= $icon ?>" class="ic-18" style="color:var(--primary);"></i>
        </div>
        <div style="font-family:var(--font-display);font-weight:700;color:var(--foreground);margin-bottom:0.375rem;font-size:var(--text-base);"><?= e($t) ?></div>
        <p style="font-size:var(--text-sm);color:var(--muted-foreground);margin:0;"><?= e($d) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════ CTA ═══════ -->
<?php
$ctaTitle    = cms($__s, 'services_cta_title',    __('cta_title'));
$ctaSubtitle = cms($__s, 'services_cta_subtitle', __('cta_sub'));
$ctaPrimary = ['label' => __('cta_talk_expert'), 'url' => url('contact.php'), 'icon' => 'calendar'];
$ctaSecondary = ['label' => isNepali() ? __('cta_pricing') : 'View pricing', 'url' => url('pricing.php'), 'icon' => 'tag'];
include 'includes/cta-banner.php';
?>
<?php include 'includes/footer.php'; ?>
