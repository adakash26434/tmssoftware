<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';
$pageTitle = 'FAQ — Frequently Asked Questions | Ankur Infotech Pvt. Ltd.';
$pageDesc  = 'Common questions about Ankur Infotech Pvt. Ltd. — software, pricing, support, deployment and more.';

$faqs = [];
try { $faqs = query("SELECT * FROM faqs WHERE active=1 ORDER BY position, category"); } catch (\Throwable $e) {}
if (!$faqs) {
  $faqs = [
    ['category'=>'General','question'=>'What is Ankur Infotech Pvt. Ltd.?','answer'=>'Ankur Infotech Pvt. Ltd. is a software company based in Butwal, Rupandehi, Nepal, providing IT solutions and software services including web development, document management, HR & payroll, IT support and more.'],
    ['category'=>'General','question'=>'How many clients use your software?','answer'=>'Over 120 clients across Nepal — from small businesses to larger organizations. We are proud of our long-term relationships and strong client retention.'],
    ['category'=>'Products','question'=>'Are your software solutions locally supported?','answer'=>'Yes. All our software comes with local support from our team in Butwal, Rupandehi. We provide training, on-site visits and remote assistance to ensure your business runs smoothly.'],
    ['category'=>'Products','question'=>'Does the system support Nepali calendar (BS)?','answer'=>'Absolutely. Every module — savings, loans, reports, payslips — is fully Nepali calendar (Bikram Sambat) native. Dates, receipts and statements all print in BS.'],
    ['category'=>'Pricing','question'=>'What is included in the Starter plan?','answer'=>'The Starter plan includes Core Banking for up to 500 members, a web portal with notice board and downloads, email + ticket support, monthly backups and one staff training session. Setup fees apply separately.'],
    ['category'=>'Pricing','question'=>'Are there hidden fees?','answer'=>'No. We provide a full, itemized quote before any commitment — including setup, data migration and training costs. The monthly subscription has no hidden charges.'],
    ['category'=>'Support','question'=>'What is your support response time?','answer'=>'Growth and Enterprise clients have a < 2 hr response SLA for P1 issues. Starter clients receive next-business-day response. All clients have access to the 24×7 ticket portal.'],
    ['category'=>'Support','question'=>'Do you provide on-site support?','answer'=>'Yes. We have staff in all major provinces and provide on-site support for critical deployments, training sessions and go-live days. Travel charges may apply for remote districts.'],
    ['category'=>'Technical','question'=>'Where is our data stored?','answer'=>'All client data is stored on servers within Nepal by default. Enterprise clients may opt for dedicated on-premise hosting or private cloud deployment.'],
    ['category'=>'Technical','question'=>'How long does it take to go live?','answer'=>'Typical timeline: Custom software — 2–4 weeks. Mobile app — 3–5 weeks. DMS — 1 week. Website — 2 weeks. Timelines depend on project scope and client readiness.'],
  ];
}

$byCategory = [];
foreach ($faqs as $faq) {
    $byCategory[$faq['category'] ?? 'General'][] = $faq;
}

include 'includes/header.php';
?>

<?php
$heroEyebrow     = __('faq_hero_eyebrow');
$heroEyebrowIcon = 'help-circle';
$heroTitle       = __('faq_hero_title');
$heroSubtitle    = __('faq_hero_sub');
include 'includes/page-hero.php';
?>

<section class="st-section">
  <div class="container-sm">
    <?php foreach ($byCategory as $cat => $items): ?>
    <div class="faq-group">
      <h2 class="faq-category"><?= e($cat) ?></h2>
      <?php foreach ($items as $faq): ?>
      <div class="accordion-item" x-data="{open:false}">
        <button type="button" class="accordion-trigger" @click="open=!open" :aria-expanded="open.toString()">
          <span><?= e($faq['question']) ?></span>
          <i data-lucide="chevron-down" class="ic-16" style="flex-shrink:0;transition:transform 0.2s;" :style="open ? 'transform:rotate(180deg);color:var(--primary)' : ''"></i>
        </button>
        <div class="accordion-content" x-show="open" x-transition><?= nl2br(e($faq['answer'])) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <div class="st-card text-center" style="padding:1.5rem;margin-top:2rem;">
      <i data-lucide="message-circle" class="ic-20" style="color:var(--primary);margin-bottom:0.625rem;"></i>
      <h3 style="font-family:var(--font-display);font-weight:700;font-size:var(--text-md);margin:0 0 0.375rem;"><?= e(isNepali() ? 'अझै प्रश्न छ?' : 'Still have questions?') ?></h3>
      <p style="color:var(--muted-foreground);font-size:var(--text-sm);margin:0 0 1rem;"><?= e(isNepali() ? 'हाम्रो टोली मद्दत गर्न तयार छ — सामान्यतः केही घन्टाभित्र।' : 'Our team is happy to help — usually within a few hours.') ?></p>
      <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:0.625rem;">
        <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-md">
          <i data-lucide="send" class="ic-14"></i>
          <?= e(isNepali() ? 'सन्देश पठाउनुस' : 'Send us a message') ?>
        </a>
        <a href="<?= url('login.php') ?>" class="btn btn-outline btn-md">
          <i data-lucide="ticket" class="ic-14"></i>
          <?= e(isNepali() ? 'समर्थन टिकट खोल्नुस' : 'Open a support ticket') ?>
        </a>
      </div>
    </div>
  </div>
</section>

<?php
$ctaTitle = __('cta_title');
$ctaSubtitle = __('cta_sub');
$ctaPrimary = ['label' => __('cta_demo'), 'url' => url('contact.php'), 'icon' => 'calendar'];
$ctaSecondary = ['label' => isNepali() ? __('cta_pricing') : 'View pricing', 'url' => url('pricing.php'), 'icon' => 'tag'];
include 'includes/cta-banner.php';
?>

<?php include 'includes/footer.php'; ?>
