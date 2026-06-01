<?php
$__user        = currentUser();
$__isStaff     = isStaff();
$__currentPath = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$companyLinks = [
  ['href' => 'about.php',            'key' => 'nav_about',      'icon' => 'building-2'],
  ['href' => 'about.php#mission',    'key' => 'nav_mission',    'icon' => 'target'],
  ['href' => 'about.php#vision',     'key' => 'nav_vision',     'icon' => 'eye'],
  ['href' => 'about.php#leadership', 'key' => 'nav_leadership', 'icon' => 'badge-check'],
  ['href' => 'about.php#team',       'key' => 'nav_team',       'icon' => 'users'],
  ['href' => 'careers.php',          'key' => 'nav_career',     'icon' => 'briefcase'],
];
$navLinks = [
  ['href' => 'products.php', 'key' => 'nav_products', 'icon' => 'box'],
  ['href' => 'services.php', 'key' => 'nav_services', 'icon' => 'layers'],
  ['href' => 'pricing.php',  'key' => 'nav_pricing',  'icon' => 'tag'],
  ['href' => 'contact.php',  'key' => 'nav_contact',  'icon' => 'mail'],
];
$moreLinks = [
  ['href' => 'portfolio.php', 'key' => 'nav_portfolio', 'icon' => 'layout-grid'],
  ['href' => 'news.php',      'key' => 'nav_news',      'icon' => 'newspaper'],
  ['href' => 'kb.php',        'key' => 'nav_kb',        'icon' => 'book-open', 'label_en' => 'Knowledge Base', 'label_ne' => 'सहयोग केन्द्र'],
  ['href' => 'faq.php',       'key' => 'nav_faq',       'icon' => 'help-circle'],
];
$__lang = currentLang();
// Self-sufficient: load settings if parent page didn't
if (!isset($__s)) $__s = siteSettings();
?>
<style>
#st-desktop-nav     { display: none; }
#st-desktop-actions { display: none; }
#st-mobile-trigger  { display: flex; }
@media (min-width: 1024px) {
  #st-desktop-nav    { display: flex; align-items: center; gap: 0.125rem; }
  #st-mobile-trigger { display: none; }
}
@media (min-width: 768px) {
  #st-desktop-actions { display: flex; align-items: center; gap: 0.5rem; }
}

/* ── Glassmorphism header ── */
#st-navbar {
  background: rgba(var(--card-rgb, 255,255,255), 0.85);
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
  box-shadow: 0 1px 3px rgba(15,23,42,0.06);
}
html.dark #st-navbar {
  background: rgba(15,23,42,0.88);
  box-shadow: 0 1px 3px rgba(0,0,0,0.25);
}

/* ── Nav pill ── */
.nav-pill {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.375rem 0.8125rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--muted-foreground);
  text-decoration: none;
  background: transparent;
  border: 0;
  cursor: pointer;
  font-family: inherit;
  line-height: 1.2;
  letter-spacing: 0.01em;
  transition: color 0.15s, background 0.15s;
  white-space: nowrap;
}
.nav-pill:hover {
  color: var(--primary);
  background: var(--primary-light, rgba(59,130,246,0.08));
}
.nav-pill.active {
  color: var(--primary);
  background: var(--primary-light, rgba(59,130,246,0.1));
  font-weight: 700;
}
.nav-pill .chev {
  width: 11px;
  height: 11px;
  opacity: 0.55;
  transition: transform .2s ease;
  flex-shrink: 0;
}
.nav-pill[aria-expanded="true"] .chev,
.nav-pill.dd-open .chev { transform: rotate(180deg); }

/* ── Dropdown panel ── */
.st-dropdown {
  display: none;
  position: absolute;
  top: calc(100% + 6px);
  min-width: 12.5rem;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  box-shadow: 0 8px 24px rgba(15,23,42,0.1), 0 2px 8px rgba(15,23,42,0.06);
  padding: 0.375rem;
  z-index: 300;
  animation: dd-in 0.14s ease;
}
html.dark .st-dropdown {
  box-shadow: 0 8px 24px rgba(0,0,0,0.35);
}
@keyframes dd-in {
  from { opacity:0; transform:translateY(-4px); }
  to   { opacity:1; transform:translateY(0);    }
}

/* ── Dropdown caret ── */
.st-dd-caret {
  position: absolute;
  top: -5px;
  width: 10px; height: 10px;
  background: var(--card);
  border-left: 1px solid var(--border);
  border-top: 1px solid var(--border);
  transform: rotate(45deg);
}

/* ── Dropdown item ── */
.st-dd-item {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.4375rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--foreground);
  text-decoration: none;
  transition: background 0.12s, color 0.12s;
}
.st-dd-item:hover { background: var(--muted); }
.st-dd-item.active { color: var(--primary); font-weight: 600; background: var(--primary-light, rgba(59,130,246,0.07)); }
.st-dd-icon { width:14px; height:14px; opacity:0.55; flex-shrink:0; }
.st-dd-item.active .st-dd-icon { opacity:0.85; }

/* ── Brand ── */
#st-brand {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1rem;
  color: var(--foreground);
  text-decoration: none;
  flex-shrink: 0;
  letter-spacing: -0.01em;
}
#st-brand-logo {
  height: 2rem;
  width: auto;
  max-width: 13rem;
  object-fit: contain;
  border-radius: 0;
}
#st-brand-monogram {
  display: grid;
  place-items: center;
  height: 2rem;
  width: 2rem;
  border-radius: 0.5rem;
  background: var(--gradient-primary);
  color: #fff;
  font-weight: 800;
  font-size: 0.625rem;
  letter-spacing: 0.02em;
}

/* ── Action buttons ── */
.st-lang-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.3125rem 0.625rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border);
  background: var(--card);
  cursor: pointer;
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--muted-foreground);
  text-decoration: none;
  transition: color 0.15s, border-color 0.15s;
  letter-spacing: 0.03em;
}
.st-lang-btn:hover { color: var(--foreground); border-color: var(--primary); }

.st-icon-btn {
  display: grid;
  place-items: center;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 9999px;
  border: 1.5px solid var(--border);
  background: transparent;
  color: var(--foreground);
  cursor: pointer;
  transition: color 0.15s, border-color 0.15s, background 0.15s;
  flex-shrink: 0;
}
.st-icon-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
  background: var(--primary-light);
}

/* ── Divider in dropdown ── */
.st-dd-sep {
  height: 1px;
  background: var(--border);
  margin: 0.25rem 0.5rem;
}
</style>

<header id="st-navbar" class="sticky top-0 z-50" x-data="{ open: false }" style="position:sticky;top:0;z-index:1000;">
  <nav class="container" role="navigation" aria-label="Main navigation"
       style="display:flex;align-items:center;justify-content:space-between;height:4rem;overflow:visible;">

    <!-- Brand -->
    <a id="st-brand" href="<?= url('index.php') ?>">
      <?php if (!empty($__s['logo_url'])): ?>
        <img id="st-brand-logo" src="<?= e($__s['logo_url']) ?>" alt="<?= e($__s['site_name'] ?? SITE_NAME) ?>" loading="eager" decoding="async">
      <?php else: ?>
        <span id="st-brand-monogram"><?= strtoupper(substr(defined('SITE_NAME') ? SITE_NAME : 'NI', 0, 2)) ?></span>
        <span><?= e($__s['site_name'] ?? SITE_NAME) ?></span>
      <?php endif; ?>
    </a>

    <!-- Desktop primary nav -->
    <ul id="st-desktop-nav" style="list-style:none;margin:0;padding:0;">

      <?php foreach ($navLinks as $l):
        $active = $__currentPath === $l['href'];
      ?>
      <li>
        <a href="<?= url($l['href']) ?>"
           <?= $active ? 'aria-current="page"' : '' ?>
           class="nav-pill <?= $active ? 'active' : '' ?>">
          <?php if (!empty($l['icon'])): ?>
          <i data-lucide="<?= $l['icon'] ?>" style="width:13px;height:13px;opacity:0.55;flex-shrink:0;" aria-hidden="true"></i>
          <?php endif; ?>
          <?= e(__($l['key'])) ?>
        </a>
      </li>
      <?php endforeach; ?>

      <!-- Company dropdown -->
      <?php $companyActive = in_array($__currentPath, ['about.php','careers.php']); ?>
      <li class="pos-rel" style="position:relative;" x-data="{companyOpen:false}" @click.outside="companyOpen=false">
        <button onclick="stDropToggle('st-dd-company',this)"
          @click="companyOpen=!companyOpen"
          :aria-expanded="companyOpen.toString()"
          aria-label="Company"
          class="nav-pill <?= $companyActive ? 'active' : '' ?>">
          <?= __('nav_company') ?>
          <svg id="st-chevron-company" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chev"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
        <div id="st-dd-company" class="st-dropdown" x-show="companyOpen" x-transition style="left:0;">
          <div class="st-dd-caret" style="left:1.125rem;"></div>
          <?php foreach ($companyLinks as $ci => $cl):
            $cActive = $__currentPath === $cl['href'];
            if ($ci === 5): /* divider before Careers */ ?>
          <div class="st-dd-sep"></div>
          <?php endif; ?>
          <a href="<?= url($cl['href']) ?>"
             <?= $cActive ? 'aria-current="page"' : '' ?>
             class="st-dd-item <?= $cActive ? 'active' : '' ?>">
            <i data-lucide="<?= $cl['icon'] ?>" class="st-dd-icon" aria-hidden="true"></i>
            <?= e(__($cl['key'])) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </li>

      <!-- More dropdown -->
      <?php $moreActive = in_array($__currentPath, array_column($moreLinks, 'href')); ?>
      <li class="pos-rel" style="position:relative;" x-data="{moreOpen:false}" @click.outside="moreOpen=false">
        <button onclick="stDropToggle('st-dd-more',this)"
          @click="moreOpen=!moreOpen"
          :aria-expanded="moreOpen.toString()"
          aria-label="More pages"
          class="nav-pill <?= $moreActive ? 'active' : '' ?>">
          <?= __('nav_more') ?>
          <svg id="st-chevron-more" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chev"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>
        <div id="st-dd-more" class="st-dropdown" x-show="moreOpen" x-transition style="right:0;">
          <div class="st-dd-caret" style="right:1.125rem;"></div>
          <?php foreach ($moreLinks as $ml):
            $mActive = $__currentPath === $ml['href'];
          ?>
          <a href="<?= url($ml['href']) ?>"
             <?= $mActive ? 'aria-current="page"' : '' ?>
             class="st-dd-item <?= $mActive ? 'active' : '' ?>">
            <?php if (!empty($ml['icon'])): ?>
            <i data-lucide="<?= $ml['icon'] ?>" class="st-dd-icon" aria-hidden="true"></i>
            <?php endif; ?>
            <?= e(__($ml['key'])) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </li>
    </ul>

    <!-- Desktop actions -->
    <div id="st-desktop-actions" style="gap:0.5rem;">
      <!-- Language toggle -->
      <a href="<?= e(langToggleUrl()) ?>"
         title="<?= $__lang === 'en' ? 'नेपालीमा हेर्नुस' : 'Switch to English' ?>"
         class="st-lang-btn">
        <i data-lucide="globe" style="width:11px;height:11px;" aria-hidden="true"></i>
        <span><?= $__lang === 'en' ? 'NP' : 'EN' ?></span>
      </a>

      <!-- Dark mode toggle -->
      <button onclick="toggleTheme()" aria-label="Toggle dark mode" class="st-icon-btn">
        <i data-lucide="sun"  id="icon-sun"  style="width:16px;height:16px;display:none;" aria-hidden="true"></i>
        <i data-lucide="moon" id="icon-moon" style="width:16px;height:16px;" aria-hidden="true"></i>
      </button>
      <script>
      (function(){
        var d=document.documentElement.classList.contains('dark');
        var s=document.getElementById('icon-sun');
        var m=document.getElementById('icon-moon');
        if(s)s.style.display=d?'block':'none';
        if(m)m.style.display=d?'none':'block';
      })();
      </script>

      <?php if ($__user): ?>
      <!-- Logged-in user menu -->
      <div class="pos-rel" x-data="{ userOpen: false }" style="position:relative;">
        <button onclick="stUserToggle()" @click="userOpen = !userOpen" id="st-user-toggle" class="st-user-btn">
          <span class="avatar avatar-sm"><?= strtoupper(substr($__user['display_name'] ?? $__user['email'], 0, 1)) ?></span>
          <span><?= e(mb_strimwidth($__user['display_name'] ?? $__user['email'], 0, 16, '…')) ?></span>
          <i data-lucide="chevron-down" id="st-user-chev" style="width:11px;height:11px;opacity:0.5;transition:transform .2s;" aria-hidden="true"></i>
        </button>
        <div x-show="userOpen" @click.outside="userOpen=false" x-transition
          id="st-dd-user" class="st-dropdown" style="right:0;min-width:11rem;display:none;">
          <div class="st-dd-caret" style="right:1.25rem;"></div>
          <a href="<?= url('portal/index.php') ?>" class="st-dd-item">
            <i data-lucide="layout-dashboard" class="st-dd-icon" aria-hidden="true"></i>
            <?= __('nav_my_portal') ?>
          </a>
          <?php if ($__isStaff): ?>
          <a href="<?= url('admin/index.php') ?>" class="st-dd-item">
            <i data-lucide="settings-2" class="st-dd-icon" aria-hidden="true"></i>
            <?= __('nav_admin') ?>
          </a>
          <?php endif; ?>
          <div class="st-dd-sep"></div>
          <a href="<?= url('logout.php') ?>" class="st-dd-item" style="color:var(--destructive);">
            <i data-lucide="log-out" class="st-dd-icon" style="opacity:0.7;" aria-hidden="true"></i>
            <?= __('nav_signout') ?>
          </a>
        </div>
      </div>

      <?php else: ?>
        <a href="<?= url('login.php') ?>" class="btn btn-ghost btn-sm"><?= __('nav_signin') ?></a>
        <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-sm" style="gap:0.3rem;">
          <?= __('nav_get_quote') ?>
          <i data-lucide="arrow-right" style="width:12px;height:12px;" aria-hidden="true"></i>
        </a>
      <?php endif; ?>
    </div>

    <!-- Mobile trigger -->
    <div id="st-mobile-trigger" style="align-items:center;gap:0.5rem;">
      <a href="<?= e(langToggleUrl()) ?>" class="st-lang-btn" style="padding:0.25rem 0.5rem;"
         title="<?= $__lang === 'en' ? 'नेपालीमा हेर्नुस' : 'Switch to English' ?>">
        <i data-lucide="globe" style="width:11px;height:11px;" aria-hidden="true"></i>
        <?= $__lang === 'en' ? 'NP' : 'EN' ?>
      </a>
      <button @click="open = !open" :aria-expanded="open.toString()" aria-label="Toggle menu" aria-controls="mobile-menu"
        class="st-hamburger-btn">
        <i x-show="!open" data-lucide="menu"  style="width:17px;height:17px;" aria-hidden="true"></i>
        <i x-show="open"  data-lucide="x"     style="width:17px;height:17px;display:none;" aria-hidden="true"></i>
      </button>
    </div>
  </nav>

  <!-- Vanilla JS dropdown fallback -->
  <script>
  (function(){
    var openId=null;
    var userOpen=false;
    function closeAll(){
      ['st-dd-company','st-dd-more'].forEach(function(id){
        var el=document.getElementById(id);if(el)el.style.display='none';
        var key=id==='st-dd-company'?'st-chevron-company':'st-chevron-more';
        var ch=document.getElementById(key);if(ch)ch.style.transform='';
      });
      openId=null;
      var ud=document.getElementById('st-dd-user');
      var uc=document.getElementById('st-user-chev');
      if(ud)ud.style.display='none';
      if(uc)uc.style.transform='';
      userOpen=false;
    }
    window.stDropToggle=function(id,btn){
      var el=document.getElementById(id);if(!el)return;
      var chId=id==='st-dd-company'?'st-chevron-company':'st-chevron-more';
      var ch=document.getElementById(chId);
      if(openId===id){closeAll();return;}
      closeAll();
      el.style.display='block';openId=id;
      if(ch)ch.style.transform='rotate(180deg)';
    };
    window.stUserToggle=function(){
      var ud=document.getElementById('st-dd-user');if(!ud)return;
      var uc=document.getElementById('st-user-chev');
      if(userOpen){closeAll();return;}
      closeAll();
      ud.style.display='block';userOpen=true;
      if(uc)uc.style.transform='rotate(180deg)';
    };
    document.addEventListener('click',function(e){
      var inDD=e.target.closest('[onclick^="stDropToggle"],[onclick^="stUserToggle"],#st-user-toggle');
      if(!inDD){
        if(openId||userOpen)closeAll();
      }
    });
  })();
  </script>

  <!-- Navbar scroll shadow -->
  <script>
  (function(){
    var nav=document.getElementById('st-navbar');
    if(!nav)return;
    function upd(){nav.classList.toggle('is-scrolled',window.scrollY>8);}
    upd();
    window.addEventListener('scroll',upd,{passive:true});
  })();
  </script>

  <!-- Mobile menu -->
  <div id="mobile-menu" x-show="open" @click.outside="open=false" x-transition
    style="border-top:1px solid var(--border);background:var(--card);padding:0.875rem 1rem 1.125rem;display:none;">
    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:0.125rem;">

      <?php
      /* Build a deduplicated mobile link list: main links + company links + more links.
         About appears once in company links — never in both. */
      $mobileLinks = array_merge($navLinks, $companyLinks, $moreLinks);
      foreach ($mobileLinks as $l):
        $lActive = $__currentPath === $l['href'];
      ?>
      <li>
        <a href="<?= url($l['href']) ?>" <?= $lActive ? 'aria-current="page"' : '' ?>
          class="mobile-nav-item">
          <span style="display:flex;align-items:center;gap:0.5rem;">
            <?php if (!empty($l['icon'])): ?>
            <i data-lucide="<?= $l['icon'] ?>" style="width:15px;height:15px;opacity:0.5;flex-shrink:0;" aria-hidden="true"></i>
            <?php endif; ?>
            <?= e(__($l['key'])) ?>
          </span>
          <i data-lucide="chevron-right" style="width:13px;height:13px;opacity:0.3;flex-shrink:0;" aria-hidden="true"></i>
        </a>
      </li>
      <?php endforeach; ?>

      <?php if ($__user): ?>
      <li style="border-top:1px solid var(--border);margin-top:0.5rem;padding-top:0.5rem;">
        <a href="<?= url('portal/index.php') ?>" class="mobile-nav-item">
          <span style="display:flex;align-items:center;gap:0.5rem;">
            <i data-lucide="layout-dashboard" style="width:15px;height:15px;opacity:0.5;" aria-hidden="true"></i>
            <?= __('nav_my_portal') ?>
          </span>
          <i data-lucide="chevron-right" style="width:13px;height:13px;opacity:0.3;" aria-hidden="true"></i>
        </a>
      </li>
      <li>
        <a href="<?= url('logout.php') ?>" class="mobile-nav-item is-danger">
          <span style="display:flex;align-items:center;gap:0.5rem;">
            <i data-lucide="log-out" style="width:15px;height:15px;" aria-hidden="true"></i>
            <?= __('nav_signout') ?>
          </span>
        </a>
      </li>
      <?php else: ?>
      <li style="margin-top:0.75rem;border-top:1px solid var(--border);padding-top:0.75rem;display:flex;gap:0.5rem;">
        <a href="<?= url('login.php') ?>" class="btn btn-ghost btn-md" style="flex:1;justify-content:center;"><?= __('nav_signin') ?></a>
        <a href="<?= url('contact.php') ?>" class="btn btn-primary btn-md" style="flex:1;justify-content:center;"><?= __('nav_get_quote') ?></a>
      </li>
      <?php endif; ?>

      <!-- Dark mode toggle — mobile -->
      <li style="margin-top:0.375rem;border-top:1px solid var(--border);padding-top:0.625rem;">
        <button onclick="toggleTheme()" aria-label="Toggle dark mode"
          style="display:flex;align-items:center;gap:0.625rem;width:100%;padding:0.6875rem 0.875rem;border-radius:0.5rem;border:none;background:transparent;cursor:pointer;font-size:0.9375rem;font-weight:500;color:var(--muted-foreground);">
          <i data-lucide="sun"  id="icon-sun-mobile"  style="width:15px;height:15px;flex-shrink:0;display:none;" aria-hidden="true"></i>
          <i data-lucide="moon" id="icon-moon-mobile" style="width:15px;height:15px;flex-shrink:0;" aria-hidden="true"></i>
          <span id="st-theme-label-mobile"><?= $__themePref === 'dark' ? 'Light Mode' : 'Dark Mode' ?></span>
        </button>
        <script>
        (function(){
          var d=document.documentElement.classList.contains('dark');
          var s=document.getElementById('icon-sun-mobile');
          var m=document.getElementById('icon-moon-mobile');
          var l=document.getElementById('st-theme-label-mobile');
          if(s)s.style.display=d?'block':'none';
          if(m)m.style.display=d?'none':'block';
          if(l)l.textContent=d?'Light Mode':'Dark Mode';
          var _orig=window.toggleTheme;
          window.toggleTheme=function(){
            if(_orig)_orig();
            var nd=document.documentElement.classList.contains('dark');
            if(s)s.style.display=nd?'block':'none';
            if(m)m.style.display=nd?'none':'block';
            if(l)l.textContent=nd?'Light Mode':'Dark Mode';
          };
        })();
        </script>
      </li>
    </ul>
  </div>
</header>
