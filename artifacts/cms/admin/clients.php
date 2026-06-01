<?php
$pageTitle = 'Client Registry';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$error = $success = '';

// ── Quick delete (list page only) ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch.';
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            $c = queryOne("SELECT user_id, org_name FROM clients WHERE id=?", [$id]);
            if ($c && $c['user_id']) {
                $error = 'Cannot delete a client whose portal account has already been claimed.';
            } else {
                execute("DELETE FROM clients WHERE id=?", [$id]);
                $success = 'Client removed.';
            }
        } catch (\Throwable $e) { $error = 'Delete failed: '.$e->getMessage(); }
    }
}

// ── List / filter / search ────────────────────────────────────────────────────
$q      = trim($_GET['q']      ?? '');
$filt   = trim($_GET['status'] ?? '');
$fprov  = trim($_GET['province'] ?? '');
$page   = max(1,(int)($_GET['page'] ?? 1));
$perPg  = 30;

$where  = '1=1';
$params = [];
if ($q)     { $where .= " AND (c.client_code LIKE ? OR c.org_name LIKE ? OR c.contact_name LIKE ? OR c.email LIKE ? OR c.district LIKE ?)"; $p="%$q%"; array_push($params,$p,$p,$p,$p,$p); }
if ($filt === 'active')    { $where .= " AND c.status='active'"; }
if ($filt === 'inactive')  { $where .= " AND c.status='inactive'"; }
if ($filt === 'claimed')   { $where .= " AND c.user_id IS NOT NULL"; }
if ($filt === 'unclaimed') { $where .= " AND c.user_id IS NULL"; }
if ($fprov)  { $where .= " AND c.province=?"; $params[] = $fprov; }

$total = 0; $clients = [];
try {
    $r = queryOne("SELECT COUNT(*) c FROM clients c WHERE $where", $params);
    $total = (int)($r['c'] ?? 0);
    $offset = ($page-1)*$perPg;
    $clients = query(
        "SELECT c.*, u.display_name as user_name, u.email as user_email
         FROM clients c LEFT JOIN users u ON u.id = c.user_id
         WHERE $where ORDER BY c.id DESC LIMIT ? OFFSET ?",
        array_merge($params, [$perPg, $offset])
    );
} catch (\Throwable $e) {}

$pages = max(1, (int)ceil($total / $perPg));
$csrf  = generateCsrf();

// Summary pills
$counts = ['total'=>0,'claimed'=>0,'unclaimed'=>0,'inactive'=>0,'active'=>0];
try {
    $cs = query("SELECT status, COUNT(*) c, SUM(user_id IS NOT NULL) claimed FROM clients GROUP BY status");
    foreach ($cs as $row) {
        $counts['total']    += (int)$row['c'];
        $counts['claimed']  += (int)$row['claimed'];
        $counts['inactive'] += $row['status']==='inactive' ? (int)$row['c'] : 0;
        $counts['active']   += $row['status']==='active'   ? (int)$row['c'] : 0;
    }
    $counts['unclaimed'] = $counts['total'] - $counts['claimed'];
} catch (\Throwable $e) {}

// Province list for filter
$provinces = [];
try { $provinces = query("SELECT DISTINCT province FROM clients WHERE province IS NOT NULL AND province!='' ORDER BY province"); } catch(\Throwable $e){}

// Flash from redirect after add
if (isset($_GET['flash_success'])) $success = 'New client added successfully.';

require_once '../includes/admin-layout.php';
?>

<style>
.cl-actions { display:flex;align-items:center;gap:.375rem;opacity:0;transition:opacity .15s; }
.cl-tr:hover { background: var(--muted); }
.cl-tr:hover .cl-actions { opacity:1; }
@media(max-width:640px){ .cl-actions{opacity:1;} }
.stat-pill { padding:.35rem 1rem;border-radius:9999px;font-size:.8125rem;font-weight:600;text-decoration:none;border:1.5px solid;transition:all .15s;white-space:nowrap; }
</style>

<!-- ── Page header ──────────────────────────────────────────────────────────── -->
<div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
  <div>
    <h1 style="font-family:var(--font-display);font-size:1.375rem;font-weight:800;color:var(--foreground);display:flex;align-items:center;gap:.5rem;">
      <i data-lucide="building-2" style="width:20px;height:20px;color:var(--primary);"></i>
      Client Registry
    </h1>
    <p style="color:var(--muted-foreground);font-size:.875rem;margin-top:.125rem;">
      <?= $counts['total'] ?> clients · <?= $counts['active'] ?> active · <?= $counts['claimed'] ?> portal accounts claimed
    </p>
  </div>
  <div style="display:flex;gap:.625rem;flex-wrap:wrap;">
    <a href="client-import.php" class="btn btn-outline btn-sm">
      <i data-lucide="upload" class="ic-13"></i>
      Import Excel
    </a>
    <a href="client-form.php" class="btn btn-primary btn-sm">
      <i data-lucide="plus" class="ic-13"></i>
      Add Client
    </a>
  </div>
</div>

<?php if ($success): ?>
<div style="display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;background:var(--success-soft);border:1px solid var(--success-border);border-radius:var(--radius-md);margin-bottom:1.125rem;color:var(--success-fg);font-size:.875rem;">
  <i data-lucide="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i><?= $success ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div style="display:flex;align-items:center;gap:.625rem;padding:.75rem 1rem;background:var(--danger-soft);border:1px solid var(--danger-border);border-radius:var(--radius-md);margin-bottom:1.125rem;color:var(--danger-fg);font-size:.875rem;">
  <i data-lucide="alert-circle" style="width:15px;height:15px;flex-shrink:0;"></i><?= e($error) ?>
</div>
<?php endif; ?>

<!-- ── Summary pills ─────────────────────────────────────────────────────────── -->
<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
  <?php $pills=[['','All',$counts['total'],'var(--muted)','#475569'],['active','Active',$counts['active'],'var(--success-soft)','var(--success-fg)'],['unclaimed','Unclaimed',$counts['unclaimed'],'var(--warning-soft)','var(--warning-fg)'],['inactive','Inactive',$counts['inactive'],'var(--danger-soft)','var(--danger-fg)'],['claimed','Portal Active',$counts['claimed'],'#dbeafe','var(--primary-dark)']];
  foreach($pills as [$f,$l,$c,$bg,$col]):$active=$filt===$f; ?>
  <a href="?status=<?= urlencode($f) ?>&q=<?= urlencode($q) ?>&province=<?= urlencode($fprov) ?>"
     class="stat-pill" style="background:<?=$active?$col:$bg?>;color:<?=$active?'#fff':$col?>;border-color:<?=$col?>;">
    <?= $l ?> <span style="font-weight:400;">(<?= $c ?>)</span>
  </a>
  <?php endforeach; ?>
</div>

<!-- ── Search + filter bar ──────────────────────────────────────────────────── -->
<form method="GET" style="display:flex;flex-wrap:wrap;gap:.5rem;margin-bottom:1.25rem;align-items:flex-end;">
  <input type="hidden" name="status" value="<?= e($filt) ?>">
  <div style="flex:1;min-width:12rem;">
    <input type="text" name="q" class="form-input" placeholder="Search name, Client ID, email, district…" value="<?= e($q) ?>" class="w-100">
  </div>
  <?php if($provinces): ?>
  <div>
    <select name="province" class="form-input">
      <option value="">All Provinces</option>
      <?php foreach($provinces as $pr): ?>
      <option value="<?= e($pr['province']) ?>" <?= $fprov===$pr['province']?'selected':'' ?>><?= e($pr['province']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <button class="btn btn-outline btn-sm" type="submit" style="height:2.4rem;">
    <i data-lucide="search" class="ic-13"></i> Search
  </button>
  <?php if($q||$filt||$fprov): ?>
  <a href="clients.php" class="btn btn-ghost btn-sm" style="height:2.4rem;">Clear</a>
  <?php endif; ?>
</form>

<!-- ── Clients table ─────────────────────────────────────────────────────────── -->
<div class="st-card ov-hidden">
  <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
      <thead>
        <tr style="background:var(--muted);text-align:left;">
          <?php foreach(['Client ID','Organization','Contact / Phones','Province · District','Products','Portal','Status',''] as $th): ?>
          <th style="padding:.6875rem 1rem;font-size:.6875rem;font-weight:700;color:var(--muted-foreground);text-transform:uppercase;letter-spacing:.04em;white-space:nowrap;"><?= $th ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($clients)): ?>
        <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--muted-foreground);">
          <i data-lucide="inbox" style="width:28px;height:28px;display:block;margin:0 auto .625rem;opacity:.35;"></i>
          No clients found.
        </td></tr>
        <?php endif; ?>
        <?php foreach($clients as $c):
          $claimed = !empty($c['user_id']);
        ?>
        <tr class="cl-tr" style="border-top:1px solid var(--border);">

          <!-- Client ID -->
          <td style="padding:.8125rem 1rem;">
            <code style="font-family:monospace;font-size:.8rem;font-weight:700;padding:.2rem .625rem;border-radius:.375rem;
                   background:<?=$claimed?'var(--success-soft)':'#dbeafe'?>;color:<?=$claimed?'var(--success-fg)':'var(--primary-dark)'?>;">
              <?= e($c['client_code']) ?>
            </code>
          </td>

          <!-- Org + location -->
          <td style="padding:.8125rem 1rem;max-width:14rem;">
            <div style="display:flex;align-items:center;gap:.5rem;">
              <?php if($c['logo_url']): ?>
              <img src="<?= e($c['logo_url']) ?>" alt="" style="width:1.75rem;height:1.75rem;border-radius:.375rem;object-fit:contain;flex-shrink:0;background:var(--muted);">
              <?php else: ?>
              <div style="width:1.75rem;height:1.75rem;border-radius:.375rem;background:var(--primary-light);display:grid;place-items:center;flex-shrink:0;font-size:.6rem;font-weight:800;color:var(--primary);"><?= strtoupper($c['org_name'][0]) ?></div>
              <?php endif; ?>
              <div>
                <div style="font-weight:700;color:var(--foreground);line-height:1.25;"><?= e($c['org_name']) ?></div>
                <?php if($c['package']): ?><div style="font-size:.7rem;color:var(--muted-foreground);"><?= e($c['package']) ?></div><?php endif; ?>
              </div>
            </div>
          </td>

          <!-- Contact -->
          <td style="padding:.8125rem 1rem;font-size:.8125rem;">
            <div style="color:var(--foreground);font-weight:600;"><?= e($c['contact_name']??'—') ?></div>
            <?php if($c['designation']): ?><div style="font-size:.7rem;color:var(--muted-foreground);"><?= e($c['designation']) ?></div><?php endif; ?>
            <?php if($c['phone']): ?><div style="font-size:.7rem;color:var(--muted-foreground);"><?= e($c['phone']) ?><?= $c['mobile1']?' · '.e($c['mobile1']):'' ?></div><?php endif; ?>
            <?php if($c['email']): ?><div style="font-size:.7rem;color:var(--muted-foreground);"><?= e($c['email']) ?></div><?php endif; ?>
          </td>

          <!-- Location -->
          <td style="padding:.8125rem 1rem;font-size:.8rem;">
            <?php if($c['province']): ?><div class="fw-strong"><?= e($c['province']) ?></div><?php endif; ?>
            <?php if($c['district']): ?><div class="text-muted"><?= e($c['district']) ?><?= $c['local_govt']?' · '.e($c['local_govt']):'' ?></div><?php endif; ?>
          </td>

          <!-- Products -->
          <td style="padding:.8125rem 1rem;font-size:.8rem;color:var(--muted-foreground);">
            <?php if($c['product']): ?>
              <div><?= e($c['product']) ?></div>
            <?php endif; ?>
            <?php if($c['integration']): ?><div style="font-size:.7rem;color:var(--muted-foreground);"><?= e($c['integration']) ?></div><?php endif; ?>
            <?php if(!$c['cbs_use']): ?><span style="font-size:.65rem;background:var(--warning-soft);color:var(--warning-fg);padding:.1rem .4rem;border-radius:9999px;">CBS off</span><?php endif; ?>
          </td>

          <!-- Portal account -->
          <td style="padding:.8125rem 1rem;font-size:.8rem;">
            <?php if($claimed): ?>
            <div class="fw-strong"><?= e($c['user_name']??'') ?></div>
            <div style="font-size:.7rem;color:var(--muted-foreground);"><?= e($c['user_email']??'') ?></div>
            <?php else: ?>
            <span style="font-size:.75rem;color:var(--muted-foreground);font-style:italic;">Not yet signed up</span>
            <?php endif; ?>
          </td>

          <!-- Status -->
          <td style="padding:.8125rem 1rem;">
            <span style="padding:.2rem .625rem;border-radius:9999px;font-size:.6875rem;font-weight:700;
                   background:<?=$c['status']==='active'?'var(--success-soft)':'var(--danger-soft)'?>;
                   color:<?=$c['status']==='active'?'var(--success-fg)':'var(--danger-fg)'?>;">
              <?= ucfirst($c['status']) ?>
            </span>
            <?php if($c['expiry_month']): ?><div style="font-size:.65rem;color:var(--muted-foreground);margin-top:.2rem;">Expires <?= e($c['expiry_month']) ?></div><?php endif; ?>
          </td>

          <!-- Actions -->
          <td style="padding:.8125rem 1rem;white-space:nowrap;">
            <div class="cl-actions">
              <a href="client-form.php?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm" title="Edit" style="padding:.25rem .5rem;">
                <i data-lucide="pencil" class="ic-14"></i>
              </a>
              <?php if(!$claimed): ?>
              <form method="POST" onsubmit="return confirm('Permanently delete <?= e(addslashes($c['org_name'])) ?>?');" class="inline">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" title="Delete" style="padding:.25rem .5rem;color:var(--destructive);">
                  <i data-lucide="trash-2" class="ic-14"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if($pages > 1 || $total > 0): ?>
  <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 1rem;border-top:1px solid var(--border);font-size:.8125rem;color:var(--muted-foreground);flex-wrap:wrap;gap:.5rem;">
    <span>Showing <?= count($clients) ?> of <?= $total ?> clients</span>
    <?php if($pages>1): ?>
    <div style="display:flex;gap:.25rem;flex-wrap:wrap;">
      <?php for($pg=1;$pg<=$pages;$pg++): ?>
      <a href="?page=<?=$pg?>&status=<?=e($filt)?>&q=<?=urlencode($q)?>&province=<?=urlencode($fprov)?>"
         style="padding:.25rem .625rem;border-radius:.375rem;text-decoration:none;font-weight:<?=$pg===$page?700:400?>;
                background:<?=$pg===$page?'var(--primary)':'var(--muted)'?>;color:<?=$pg===$page?'#fff':'var(--foreground)'?>;">
        <?= $pg ?>
      </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- ── Download Excel template hint ─────────────────────────────────────────── -->
<div style="display:flex;align-items:center;gap:.75rem;margin-top:1.25rem;padding:.875rem 1.125rem;background:var(--muted);border-radius:var(--radius-md);border:1px solid var(--border);font-size:.8125rem;color:var(--muted-foreground);flex-wrap:wrap;">
  <i data-lucide="info" style="width:15px;height:15px;color:var(--primary);flex-shrink:0;"></i>
  <span>
    To bulk-import clients from Excel, use
    <a href="client-import.php" style="color:var(--primary);font-weight:600;">Import Excel</a>.
    Expected columns: <strong>Co-operative Name, Office Id, Province, District, Address, Contact Person, Email, Phone 1, Package, Status</strong> (and more).
  </span>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
