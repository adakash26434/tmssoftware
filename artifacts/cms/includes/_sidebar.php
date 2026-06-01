<?php
/**
 * ============================================================
 *  includes/_sidebar.php — Shared sidebar renderer
 *  Used by admin-layout.php and portal-layout.php.
 *
 *  USAGE (inside <body>):
 *
 *    renderSidebar([
 *      'id'         => 'admin-sidebar',         // or 'portal-sidebar'
 *      'theme'      => 'dark',                  // 'dark' (admin) or 'light' (portal)
 *      'brand'      => ['name' => 'Admin Panel', 'href' => url('index.php')],
 *      'user'       => $__user,                 // currentUser()
 *      'currentPath'=> $__currentPath,
 *      'directLinks'=> [
 *         ['file'=>'index.php','icon'=>'home','label'=>'Dashboard','badge'=>null,'urlPrefix'=>'admin/'],
 *      ],
 *      'groups'     => [                        // optional grouped section
 *        'Content' => [
 *           ['file'=>'team.php','icon'=>'users','label'=>'Team'],
 *           ...
 *        ],
 *      ],
 *      'logoutHref' => url('logout.php'),
 *      'urlPrefix'  => 'admin/',                // prepended to each link file
 *      'closeJsFn'  => 'closeAdminSidebar',     // optional mobile-close JS fn name
 *      'footerExtra'=> '<a …>Public Site</a>',  // optional raw HTML appended above signout
 *    ]);
 * ============================================================
 */

if (!function_exists('icon')) {
    // Minimal fallback. Real `icon()` helper lives elsewhere in your codebase.
    function icon(string $name, int $size = 16, string $extraStyle = ''): string {
        return '<i data-lucide="' . htmlspecialchars($name) . '" style="width:' . $size . 'px;height:' . $size . 'px;' . $extraStyle . '"></i>';
    }
}

// नेपालीमा: Sidebar HTML render garne (shared)
function renderSidebar(array $cfg): void {
    $id          = $cfg['id']          ?? 'app-sidebar';
    $theme       = $cfg['theme']       ?? 'light';
    $isDark      = $theme === 'dark';
    $brand       = $cfg['brand']       ?? ['name' => 'Menu', 'href' => '#'];
    $user        = $cfg['user']        ?? [];
    $cur         = $cfg['currentPath'] ?? '';
    $direct      = $cfg['directLinks'] ?? [];
    $groups      = $cfg['groups']      ?? [];
    $logoutHref  = $cfg['logoutHref']  ?? '#';
    $urlPrefix   = $cfg['urlPrefix']   ?? '';
    $closeFn     = $cfg['closeJsFn']   ?? 'closeAppSidebar';
    $footerExtra = $cfg['footerExtra'] ?? '';

    $bg     = $isDark ? '#0f172a' : 'var(--card)';
    $border = $isDark ? 'rgba(241,245,249,0.08)' : 'var(--border)';
    $linkClr= $isDark ? 'rgba(241,245,249,0.6)' : 'var(--foreground)';
    $hoverBg= $isDark ? 'rgba(241,245,249,0.06)' : 'var(--muted)';
    $activeBg = $isDark ? 'rgba(59,130,246,0.18)' : 'var(--primary-light)';
    $activeFg = $isDark ? '#60a5fa' : 'var(--primary)';
    $brandClr = $isDark ? 'var(--muted)' : 'var(--foreground)';
    ?>
<aside id="<?= htmlspecialchars($id) ?>"
       class="app-sidebar<?= $isDark ? ' sidebar-dark' : ' sidebar-light' ?>"
       style="width:15rem;flex-shrink:0;display:flex;flex-direction:column;background:<?= $bg ?>;border-right:1px solid <?= $border ?>;overflow:hidden;">

  <!-- Brand -->
  <div style="padding:1rem 1.25rem;border-bottom:1px solid <?= $border ?>;display:flex;align-items:center;justify-content:space-between;">
    <a href="<?= htmlspecialchars($brand['href']) ?>" style="display:flex;align-items:center;gap:0.625rem;font-family:var(--font-display);font-weight:700;font-size:0.9375rem;color:<?= $brandClr ?>;text-decoration:none;">
      <span style="display:grid;place-items:center;width:2rem;height:2rem;border-radius:0.5rem;background:var(--gradient-primary);color:#fff;font-weight:800;font-size:0.6875rem;">ST</span>
      <?= htmlspecialchars($brand['name']) ?>
    </a>
    <button onclick="<?= htmlspecialchars($closeFn) ?>()" class="sidebar-close-btn" title="Close" style="display:none;width:1.875rem;height:1.875rem;border-radius:0.375rem;border:none;background:<?= $hoverBg ?>;cursor:pointer;color:<?= $linkClr ?>;align-items:center;justify-content:center;">
      <?= icon('x', 16) ?>
    </button>
  </div>

  <!-- Nav -->
  <nav style="flex:1;padding:0.625rem;overflow-y:auto;">
    <?php foreach ($direct as $item):
        $active = $cur === $item['file'];
        $href   = htmlspecialchars((defined('SITE_URL') ? SITE_URL . '/' : '') . $urlPrefix . $item['file']);
        $badge  = $item['badge'] ?? null;
    ?>
    <a href="<?= $href ?>" onclick="<?= htmlspecialchars($closeFn) ?>()"
       class="sidebar-link<?= $active ? ' active' : '' ?>"
       style="display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0.75rem;border-radius:0.5rem;margin-bottom:0.125rem;text-decoration:none;font-size:0.8125rem;color:<?= $active ? $activeFg : $linkClr ?>;background:<?= $active ? $activeBg : 'transparent' ?>;transition:background 0.12s;"
       onmouseover="if(!this.classList.contains('active'))this.style.background='<?= $hoverBg ?>'"
       onmouseout="if(!this.classList.contains('active'))this.style.background='transparent'">
      <span style="display:inline-flex;"><?= icon($item['icon'], 15) ?></span>
      <span class="flex-1"><?= htmlspecialchars($item['label']) ?></span>
      <?php if ($badge): ?>
      <span style="background:#ef4444;color:#fff;font-size:0.6875rem;font-weight:700;padding:0.05rem 0.4rem;border-radius:9999px;min-width:18px;text-align:center;"><?= htmlspecialchars((string)$badge) ?></span>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>

    <?php if (!empty($groups)): ?>
    <div style="height:1px;background:<?= $border ?>;margin:0.5rem 0.25rem;"></div>

    <?php
    // Find active group so it auto-opens
    $activeGroup = null;
    foreach ($groups as $gLabel => $gItems) {
        foreach ($gItems as $gi) {
            if (($gi['file'] ?? '') === $cur) { $activeGroup = $gLabel; break 2; }
        }
    }
    foreach ($groups as $gLabel => $gItems):
        $isActive = $activeGroup === $gLabel;
        $gid = 'nav-grp-' . strtolower(preg_replace('/\W+/', '-', $gLabel));
    ?>
    <div style="margin-bottom:0.125rem;">
      <button type="button" onclick="toggleNavGroup('<?= $gid ?>')"
        style="width:100%;display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0.75rem;border-radius:0.5rem;border:none;background:<?= $isActive ? $activeBg : 'transparent' ?>;color:<?= $isActive ? $activeFg : $linkClr ?>;cursor:pointer;text-align:left;font-size:0.8125rem;font-weight:600;">
        <span class="flex-1"><?= htmlspecialchars($gLabel) ?></span>
        <span id="<?= $gid ?>-chevron" style="transition:transform 0.18s;<?= $isActive ? 'transform:rotate(180deg);' : '' ?>"><?= icon('chevron-down', 13) ?></span>
      </button>
      <div id="<?= $gid ?>" style="overflow:hidden;padding-left:0.5rem;<?= $isActive ? '' : 'display:none;' ?>">
        <?php foreach ($gItems as $gi):
            $active = $cur === ($gi['file'] ?? '');
            $href = htmlspecialchars((defined('SITE_URL') ? SITE_URL . '/' : '') . $urlPrefix . $gi['file']);
        ?>
        <a href="<?= $href ?>" onclick="<?= htmlspecialchars($closeFn) ?>()"
           class="sidebar-link<?= $active ? ' active' : '' ?>"
           style="display:flex;align-items:center;gap:0.625rem;padding:0.4rem 0.75rem;border-radius:0.5rem;margin-bottom:0.125rem;text-decoration:none;font-size:0.8125rem;color:<?= $active ? $activeFg : $linkClr ?>;background:<?= $active ? $activeBg : 'transparent' ?>;"
           onmouseover="if(!this.classList.contains('active'))this.style.background='<?= $hoverBg ?>'"
           onmouseout="if(!this.classList.contains('active'))this.style.background='transparent'">
          <?= icon($gi['icon'], 14) ?>
          <span><?= htmlspecialchars($gi['label']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </nav>

  <!-- Footer -->
  <div style="padding:0.625rem;border-top:1px solid <?= $border ?>;">
    <?= $footerExtra ?>
    <?php if (!empty($user)): ?>
    <div style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem 0.75rem;margin-bottom:0.25rem;">
      <span style="width:2rem;height:2rem;border-radius:9999px;background:var(--gradient-primary);display:grid;place-items:center;font-size:0.75rem;font-weight:700;color:#fff;flex-shrink:0;">
        <?= strtoupper(substr($user['display_name'] ?? $user['email'] ?? '?', 0, 1)) ?>
      </span>
      <div style="min-width:0;flex:1;">
        <div style="font-size:0.75rem;font-weight:600;color:<?= $isDark ? 'rgba(241,245,249,0.85)' : 'var(--foreground)' ?>;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <?= htmlspecialchars($user['display_name'] ?? $user['email'] ?? '') ?>
        </div>
        <div style="font-size:0.6875rem;color:<?= $isDark ? 'rgba(241,245,249,0.45)' : 'var(--muted-foreground)' ?>;">
          <?= htmlspecialchars($user['role'] ?? 'Client') ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <a href="<?= htmlspecialchars($logoutHref) ?>"
       style="display:flex;align-items:center;gap:0.625rem;padding:0.5rem 0.75rem;border-radius:0.5rem;font-size:0.8125rem;font-weight:500;color:#ef4444;text-decoration:none;"
       onmouseover="this.style.background='<?= $isDark ? 'rgba(239,68,68,0.12)' : '#fef2f2' ?>'"
       onmouseout="this.style.background='transparent'">
      <?= icon('log-out', 15) ?> Sign out
    </a>
  </div>
</aside>

<script>
window.toggleNavGroup = window.toggleNavGroup || function (id) {
  var el = document.getElementById(id), chev = document.getElementById(id + '-chevron');
  if (!el) return;
  var open = el.style.display !== 'none';
  el.style.display = open ? 'none' : 'block';
  if (chev) chev.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
  try {
    var st = JSON.parse(localStorage.getItem('st-nav-groups') || '{}');
    st[id] = !open; localStorage.setItem('st-nav-groups', JSON.stringify(st));
  } catch (e) {}
};
</script>
<?php
}
