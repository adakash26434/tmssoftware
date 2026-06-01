<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
requireAdmin();
$__user = currentUser();
$__s = siteSettings();
$__currentPath = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
<?php
$headContext = 'admin';
$pageTitle = ($pageTitle ?? 'Admin') . ' — Admin | ' . SITE_NAME;
require __DIR__ . '/head.php';
?>
<style>
/* ── Base sidebar element classes (used throughout nav render loop) ── */
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4375rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 500;
  text-decoration: none;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
  overflow: hidden;
}
.sidebar-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  width: 1rem;
  height: 1rem;
  opacity: 0.75;
}
.sidebar-link.active .sidebar-icon,
.sidebar-link:hover .sidebar-icon { opacity: 1; }

/* ── Admin sidebar colour overrides ── */
.admin-sidebar { background: var(--sidebar-bg, #0f172a); }
.admin-sidebar .sidebar-link { color: var(--sidebar-text, rgba(241,245,249,0.5)); }
.admin-sidebar .sidebar-link:hover { background: var(--sidebar-hover-bg, rgba(241,245,249,0.06)); color: var(--sidebar-hover-text, rgba(241,245,249,0.85)); }
.admin-sidebar .sidebar-link.active { background: var(--sidebar-active-bg, rgba(59,130,246,0.18)); color: var(--sidebar-active-text, #60a5fa); }
.admin-sidebar .divider { background: var(--sidebar-divider, rgba(241,245,249,0.08)); }

/* Mobile sidebar overlay */
@media (max-width: 767px) {
  #admin-sidebar {
    position: fixed !important;
    left: 0; top: 0; bottom: 0;
    z-index: 200;
    transform: translateX(-100%);
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 4px 0 24px rgba(15,23,42,0.35);
  }
  #admin-sidebar.sidebar-open {
    transform: translateX(0);
  }
  #admin-sidebar-overlay {
    display: none;
    position: fixed; inset: 0; z-index: 199;
    background: rgba(15,23,42,0.6);
    backdrop-filter: blur(2px);
  }
  #admin-sidebar-overlay.show { display: block; }
}
</style>
</head>
<body style="min-height:100vh;background:var(--background);color:var(--foreground);">

<!-- Mobile sidebar backdrop -->
<div id="admin-sidebar-overlay" onclick="closeAdminSidebar()"></div>

<div style="display:flex;height:100vh;overflow:hidden;">

  <!-- Admin Sidebar -->
  <aside id="admin-sidebar" class="admin-sidebar" style="width:14rem;flex-shrink:0;display:flex;flex-direction:column;overflow:hidden;">
    <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(241,245,249,0.08);display:flex;align-items:center;justify-content:space-between;">
      <a href="<?= url('index.php') ?>" style="display:flex;align-items:center;gap:0.625rem;font-family:var(--font-display);font-weight:700;font-size:0.875rem;color:#f1f5f9;text-decoration:none;">
        <span style="display:grid;place-items:center;width:1.875rem;height:1.875rem;border-radius:0.5rem;background:var(--gradient-primary);color:#fff;font-weight:800;font-size:0.6875rem;"><?= strtoupper(substr(defined('SITE_NAME') ? SITE_NAME : 'NI', 0, 2)) ?></span>
        Admin Panel
      </a>
      <!-- Close button (mobile only) -->
      <button id="admin-sidebar-close-btn" onclick="closeAdminSidebar()" style="display:none;width:1.875rem;height:1.875rem;border-radius:0.375rem;border:none;background:rgba(241,245,249,0.1);cursor:pointer;color:rgba(241,245,249,0.7);display:flex;align-items:center;justify-content:center;" title="Close"><?= icon('x',16) ?></button>
    </div>
    <nav style="flex:1;padding:0.625rem;overflow-y:auto;" id="admin-nav">
      <?php
      // Direct links (always visible)
      $directLinks = [
        ['index.php',     icon('layout-dashboard',15), 'Dashboard'],
        ['analytics.php', icon('bar-chart-2',15),      'Analytics'],
        ['search.php',    icon('search',15),           'Global Search'],
        ['branches.php',  icon('git-branch',15),       'Branches'],
        ['status.php',    icon('activity',15),         'Status Page'],
      ];
      // Grouped sections — key = label, value = items array
      $adminNavGroups = [
        'Content' => [
          ['team.php', icon('users',15), 'Team'],
          ['services.php', icon('settings',15), 'Services'],
          ['products.php', icon('package',15), 'Products'],
          ['portfolio.php', icon('briefcase',15), 'Portfolio'],
          ['testimonials.php', icon('star',15), 'Testimonials'],
          ['gallery.php', icon('image',15), 'Gallery'],
          ['partners.php', icon('handshake',15), 'Partners'],
          ['pricing.php', icon('tag',15), 'Pricing Plans'],
          ['news.php', icon('newspaper',15), 'News & Blog'],
          ['faqs.php', icon('help-circle',15), 'FAQs'],
          ['careers.php', icon('clipboard-list',15), 'Careers'],
          ['pages.php', icon('file-text',15), 'CMS Pages'],
        ],
        'CRM' => [
          ['clients.php', icon('building-2',15), 'Clients'],
          ['contacts.php', icon('mail',15), 'Contacts'],
          ['orders.php', icon('shopping-cart',15), 'Orders'],
          ['subscribers.php', icon('mail-check',15), 'Subscribers'],
          ['demo-requests.php', icon('telescope',15), 'Demo Requests'],
          ['applications.php', icon('clipboard',15), 'Job Applications'],
          ['crm.php', icon('target',15), 'CRM & Follow-ups'],
        ],
        'Support' => [
          ['tickets.php', icon('ticket',15), 'Tickets'],
          ['sla.php', icon('timer',15), 'SLA Policies'],
          ['email-intake.php', icon('mail',15), 'Email Intake'],
          ['kb.php', icon('book-open',15), 'Knowledge Base'],
          ['livechat.php', icon('message-circle',15), 'Live Chat'],
          ['announcements.php', icon('megaphone',15), 'Announcements'],
          ['banners.php', icon('layout-template',15), 'Banners'],
        ],
        'Subscriptions' => [
          ['subscriptions.php', icon('repeat',15), 'Subscriptions'],
          ['licenses.php', icon('key-round',15), 'License Keys'],
        ],

        'Settings' => [
          ['settings.php', icon('settings-2',15), 'Settings'],
          ['users.php', icon('user',15), 'Users'],
          ['staff.php', icon('user-cog',15), 'Staff'],
          ['support-contacts.php', icon('phone',15), 'Support Contacts'],
          ['audit-log.php', icon('search',15), 'Audit Log'],
        ],
        // Superadmin-only section — rendered separately below

      ];

      // Determine which group is active
      $activeGroup = null;
      foreach ($adminNavGroups as $grpLabel => $grpItems) {
        foreach ($grpItems as $n) {
          if ($n[0] === $__currentPath) { $activeGroup = $grpLabel; break 2; }
        }
      }

      // Render direct links
      foreach ($directLinks as [$file,$icon,$label]):
        $active = $__currentPath === $file;
      ?>
      <a href="<?= url('admin/'.$file) ?>" onclick="closeAdminSidebar()" class="sidebar-link <?= $active ? 'active' : '' ?>" style="margin-bottom:0.125rem;">
        <span class="sidebar-icon"><?= $icon ?></span>
        <span class="fs-sm2"><?= e($label) ?></span>
      </a>
      <?php endforeach; ?>

      <div class="divider" style="height:1px;margin:0.5rem 0.25rem;"></div>

      <?php foreach ($adminNavGroups as $grpLabel => $grpItems):
        $isActive = $activeGroup === $grpLabel;
        $grpId = 'nav-grp-' . strtolower(preg_replace('/\W+/', '-', $grpLabel));
        $grpIconMap = ['Content'=>icon('file-text',14),'CRM'=>icon('target',14),'Support'=>icon('headphones',14),'Subscriptions'=>icon('repeat',14),'Settings'=>icon('sliders-horizontal',14)];
        $grpIcon = $grpIconMap[$grpLabel] ?? icon('folder',14);
      ?>
      <div style="margin-bottom:0.125rem;">
        <button onclick="toggleNavGroup('<?= $grpId ?>')"
          style="width:100%;display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0.75rem;border-radius:0.5rem;border:none;background:<?= $isActive ? 'rgba(59,130,246,0.12)' : 'transparent' ?>;cursor:pointer;color:<?= $isActive ? '#60a5fa' : 'rgba(241,245,249,0.55)' ?>;transition:background 0.15s;text-align:left;"
          onmouseover="if(!this.classList.contains('grp-active'))this.style.background='rgba(241,245,249,0.06)'"
          onmouseout="if(!this.classList.contains('grp-active'))this.style.background='<?= $isActive ? 'rgba(59,130,246,0.12)' : 'transparent' ?>'">
          <span class="fs-md"><?= $grpIcon ?></span>
          <span class="fs-sm2 fw-strong flex-1"><?= $grpLabel ?></span>
          <span id="<?= $grpId ?>-chevron" style="display:flex;transition:transform 0.2s;<?= $isActive ? 'transform:rotate(180deg)' : '' ?>"><?= icon('chevron-down',13) ?></span>
        </button>
        <div id="<?= $grpId ?>" style="overflow:hidden;padding-left:0.5rem;<?= $isActive ? '' : 'display:none;' ?>">
          <?php foreach ($grpItems as [$file,$icon,$label]):
            $active = $__currentPath === $file; ?>
          <a href="<?= url('admin/'.$file) ?>" onclick="closeAdminSidebar()" class="sidebar-link fs-sm2 <?= $active ? 'active' : '' ?>" style="margin-bottom:0.125rem;">
            <span class="sidebar-icon"><?= $icon ?></span>
            <span><?= e($label) ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if (isSuperAdmin()): ?>
      <div class="divider" style="height:1px;margin:0.5rem 0.25rem;"></div>
      <?php $saActive = $__currentPath === 'manage-admins.php'; ?>
      <a href="<?= url('admin/manage-admins.php') ?>" onclick="closeAdminSidebar()"
         class="sidebar-link <?= $saActive ? 'active' : '' ?>"
         style="margin-bottom:0.125rem;background:<?=$saActive?'rgba(236,72,153,0.15)':'transparent'?>;color:<?=$saActive?'#f472b6':'rgba(241,245,249,0.55)'?>;">
        <span class="sidebar-icon"><?= icon('shield',15) ?></span>
        <span style="font-size:0.8125rem;font-weight:600;">Manage Admins</span>
      </a>
      <?php endif; ?>

      <div class="divider" style="height:1px;margin:0.5rem 0.25rem;"></div>
      <?php foreach ([
        ['security.php',    'shield',   'My 2FA'],
        ['sessions.php',    'activity', 'My Sign-ins'],
        ['cron-status.php', 'clock',    'Cron Status'],
      ] as $tl): [$tf,$ti,$tlabel] = $tl; $tA = $__currentPath === $tf; ?>
      <a href="<?= url('admin/'.$tf) ?>" onclick="closeAdminSidebar()"
         class="sidebar-link <?= $tA?'active':'' ?>"
         style="margin-bottom:0.125rem;background:<?=$tA?'rgba(59,130,246,0.15)':'transparent'?>;color:<?=$tA?'#60a5fa':'rgba(241,245,249,0.55)'?>;">
        <span class="sidebar-icon"><?= icon($ti,15) ?></span>
        <span style="font-size:0.8125rem;font-weight:600;"><?= $tlabel ?></span>
      </a>
      <?php endforeach; ?>
    </nav>
    <div style="padding:0.625rem;border-top:1px solid rgba(241,245,249,0.08);">
      <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;margin-bottom:0.25rem;">
        <span class="avatar avatar-sm"><?= strtoupper(substr($__user['display_name']??$__user['email'],0,1)) ?></span>
        <div style="min-width:0;flex:1;">
          <div style="font-size:var(--text-xs);font-weight:600;color:var(--footer-fg-strong);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($__user['display_name']??$__user['email']) ?></div>
          <div style="font-size:var(--text-2xs);color:var(--footer-fg-mute);"><?= e($__user['role'] === 'superadmin' ? 'Super Admin' : 'Administrator') ?></div>
        </div>
      </div>
      <a href="<?= url('logout.php') ?>" class="sidebar-link admin-logout-link">
        <span class="sidebar-icon"><?= icon('log-out',15) ?></span> <span class="fs-sm2">Sign out</span>
      </a>
    </div>
  </aside>

  <!-- Main -->
  <div style="flex:1;display:flex;flex-direction:column;overflow:hidden;">
    <header style="background:var(--card);border-bottom:1px solid var(--border);padding:0 1.25rem;height:3.5rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
      <div style="display:flex;align-items:center;gap:0.75rem;">
        <!-- Mobile hamburger -->
        <button id="admin-sidebar-open-btn" onclick="openAdminSidebar()" style="display:none;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:0.5rem;border:1px solid var(--border);background:var(--card);cursor:pointer;" title="Menu">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
        <h1 style="font-family:var(--font-display);font-weight:700;font-size:0.9375rem;color:var(--foreground);"><?= e($pageTitle ?? 'Admin') ?></h1>
      </div>
      <div style="display:flex;align-items:center;gap:0.625rem;">
        <?php
          // Branch switcher (renders only if any branches exist)
          require_once __DIR__ . '/branch.php';
          $__bsw = renderBranchSwitcher();
          if ($__bsw) echo $__bsw;
        ?>
        <form method="get" action="<?= url('admin/search.php') ?>" style="display:flex;align-items:center;">
          <input name="q" placeholder="Search 40+ tables…" class="form-input admin-topbar-search">
        </form>
        <a href="<?= url('index.php') ?>" target="_blank" class="btn btn-ghost btn-sm fs-xs">View site ↗</a>
        <button onclick="toggleTheme()" style="display:grid;place-items:center;width:1.875rem;height:1.875rem;border-radius:9999px;border:1px solid var(--border);background:var(--card);color:var(--muted-foreground);cursor:pointer;" title="Toggle theme">
          <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        </button>
      </div>
    </header>
    <main style="flex:1;overflow-y:auto;padding:1.5rem;">
<script>
// ── Alert auto-dismiss (5 seconds) ────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.alert-success, .alert-error, .alert').forEach(function(el) {
    if (el.classList.contains('alert-persistent')) return;
    setTimeout(function() {
      el.style.transition = 'opacity 0.5s, transform 0.5s';
      el.style.opacity = '0'; el.style.transform = 'translateY(-6px)';
      setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, 520);
    }, 5000);
  });
});
// ── Global form submit loading state ──────────────────────────
document.addEventListener('submit', function(e) {
  const form = e.target;
  if (form.dataset.noLoading) return;
  const btn = form.querySelector('button[type="submit"]');
  if (!btn || btn.dataset.loading) return;
  btn.dataset.loading = '1';
  const origHtml = btn.innerHTML;
  btn.innerHTML = '<span class="btn-spinner"></span>' + (btn.dataset.loadingText || btn.textContent.trim() + '…');
  setTimeout(() => { if (btn.dataset.loading) { btn.innerHTML = origHtml; delete btn.dataset.loading; } }, 12000);
});
// नेपालीमा: Translation — current language ma string return
function toggleTheme(){
  const h=document.documentElement;
  const dark=h.classList.toggle('dark');
  const t=dark?'dark':'light';
  localStorage.setItem('st-theme',t);
  fetch('<?= url("api/set-theme.php") ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'theme='+t}).catch(()=>{});
  // Sync sun/moon icons wherever they appear (navbar + mobile menu)
  document.querySelectorAll('#icon-sun,#icon-sun-mobile').forEach(function(el){el.style.display=dark?'block':'none';});
  document.querySelectorAll('#icon-moon,#icon-moon-mobile').forEach(function(el){el.style.display=dark?'none':'block';});
  var lbl=document.getElementById('st-theme-label-mobile');
  if(lbl)lbl.textContent=dark?'Light Mode':'Dark Mode';
}
// नेपालीमा: Translation — current language ma string return
function toggleNavGroup(id){
  const el=document.getElementById(id);
  const chev=document.getElementById(id+'-chevron');
  if(!el)return;
  const open=el.style.display!=='none';
  el.style.display=open?'none':'block';
  if(chev)chev.style.transform=open?'rotate(0deg)':'rotate(180deg)';
  // persist state
  try{const st=JSON.parse(localStorage.getItem('st-nav-groups')||'{}');st[id]=!open;localStorage.setItem('st-nav-groups',JSON.stringify(st));}catch(e){}
}
// Restore nav group states (don't override active group)
(function(){
  try{
    const st=JSON.parse(localStorage.getItem('st-nav-groups')||'{}');
    Object.entries(st).forEach(([id,open])=>{
      const el=document.getElementById(id);
      const chev=document.getElementById(id+'-chevron');
      // Don't collapse the active group
      if(el&&el.style.display!==''&&!open){el.style.display='none';if(chev)chev.style.transform='rotate(0deg)';}
    });
  }catch(e){}
})();
// नेपालीमा: openAdminSidebar() — yo function le aafno kaam garchha
function openAdminSidebar(){
  document.getElementById('admin-sidebar').classList.add('sidebar-open');
  document.getElementById('admin-sidebar-overlay').classList.add('show');
  document.getElementById('admin-sidebar-close-btn').style.display='flex';
  document.body.style.overflow='hidden';
}
// नेपालीमा: closeAdminSidebar() — yo function le aafno kaam garchha
function closeAdminSidebar(){
  document.getElementById('admin-sidebar').classList.remove('sidebar-open');
  document.getElementById('admin-sidebar-overlay').classList.remove('show');
  document.body.style.overflow='';
}
// नेपालीमा: checkAdminSidebarBtn() — yo function le aafno kaam garchha
function checkAdminSidebarBtn(){
  const btn=document.getElementById('admin-sidebar-open-btn');
  if(window.innerWidth<768){btn.style.display='flex';}
  else{btn.style.display='none';closeAdminSidebar();}
}
checkAdminSidebarBtn();
window.addEventListener('resize',checkAdminSidebarBtn);
</script>
