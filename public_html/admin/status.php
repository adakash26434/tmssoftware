<?php
$pageTitle = 'Status Page';
require_once '../includes/admin-layout.php';

$msg = $msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $a = $_POST['action'] ?? '';

    if ($a === 'comp_save') {
        $id  = (int)($_POST['id'] ?? 0);
        $row = [
            trim($_POST['name']),
            trim($_POST['description']),
            $_POST['status'],
            (int)($_POST['sort_order'] ?? 10),
            isset($_POST['active']) ? 1 : 0,
        ];
        try {
            if ($id) {
                execute("UPDATE status_components SET name=?,description=?,status=?,sort_order=?,active=?,updated_at=datetime('now') WHERE id=?",
                    array_merge($row, [$id]));
            } else {
                execute("INSERT INTO status_components (name,description,status,sort_order,active) VALUES (?,?,?,?,?)", $row);
            }
            $msg = 'Component saved.'; $msgType = 'success';
        } catch(\Throwable $e) { $msg = 'Save failed: '.$e->getMessage(); $msgType = 'error'; }

    } elseif ($a === 'comp_delete') {
        try {
            execute("DELETE FROM status_components WHERE id=?", [(int)$_POST['id']]);
            $msg = 'Component deleted.'; $msgType = 'success';
        } catch(\Throwable $e) { $msg = 'Delete failed.'; $msgType = 'error'; }

    } elseif ($a === 'incident_new') {
        try {
            $iid = execute(
                "INSERT INTO status_incidents (title,body,severity,impact,component_id) VALUES (?,?,?,?,?)",
                [trim($_POST['title']), trim($_POST['body']),
                 $_POST['severity'] ?? 'investigating', $_POST['impact'] ?? 'minor',
                 (int)($_POST['component_id'] ?? 0) ?: null]
            );
            execute("INSERT INTO status_incident_updates (incident_id,status,message) VALUES (?,?,?)",
                [$iid, $_POST['severity'] ?? 'investigating', trim($_POST['body'])]);
            $msg = 'Incident published.'; $msgType = 'success';
        } catch(\Throwable $e) { $msg = 'Publish failed: '.$e->getMessage(); $msgType = 'error'; }

    } elseif ($a === 'incident_update') {
        $iid = (int)$_POST['incident_id'];
        $sev = $_POST['severity'] ?? 'monitoring';
        try {
            execute("INSERT INTO status_incident_updates (incident_id,status,message) VALUES (?,?,?)",
                [$iid, $sev, trim($_POST['message'])]);
            execute("UPDATE status_incidents SET severity=?,resolved_at=CASE WHEN ?='resolved' THEN datetime('now') ELSE resolved_at END WHERE id=?",
                [$sev, $sev, $iid]);
            $msg = 'Update posted.'; $msgType = 'success';
        } catch(\Throwable $e) { $msg = 'Post failed.'; $msgType = 'error'; }
    }
}

$components = query("SELECT * FROM status_components ORDER BY sort_order,id");
$incidents  = query("SELECT i.*, c.name AS comp_name FROM status_incidents i LEFT JOIN status_components c ON c.id=i.component_id ORDER BY i.started_at DESC LIMIT 30");
$openIncidents = array_filter($incidents, fn($i) => !$i['resolved_at']);

// Status badge helper
function statusBadge(string $s): string {
    $map = [
        'operational' => ['#d1fae5','#065f46','Operational'],
        'degraded'    => ['#fef9c3','#854d0e','Degraded'],
        'partial'     => ['#ffedd5','#9a3412','Partial Outage'],
        'major'       => ['#fee2e2','#991b1b','Major Outage'],
        'maintenance' => ['#dbeafe','#1e40af','Maintenance'],
        'investigating'=> ['#ffedd5','#9a3412','Investigating'],
        'identified'  => ['#fef9c3','#854d0e','Identified'],
        'monitoring'  => ['#dbeafe','#1e40af','Monitoring'],
        'resolved'    => ['#d1fae5','#065f46','Resolved'],
    ];
    [$bg, $fg, $label] = $map[$s] ?? ['var(--muted)','var(--muted-foreground)', ucfirst($s)];
    return "<span style=\"display:inline-flex;align-items:center;gap:0.25rem;padding:0.15rem 0.6rem;border-radius:9999px;font-size:0.6875rem;font-weight:600;background:{$bg};color:{$fg};\">{$label}</span>";
}
?>

<?php if($msg):?><div class="alert alert-<?=e($msgType)?> mb-1"><?=e($msg)?></div><?php endif;?>

<!-- ══ Components ════════════════════════════════════════════════ -->
<div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start;">

  <!-- List -->
  <div>
    <div class="row-between-mb">
      <h2 class="h-eyebrow-flat">System Components (<?=count($components)?>)</h2>
      <a href="<?=url('status.php')?>" target="_blank" class="btn btn-ghost btn-sm">View public page ↗</a>
    </div>

    <div class="st-card ov-hidden">
      <?php if(empty($components)):?>
      <div class="p-empty" style="padding:2.5rem;text-align:center;color:var(--muted-foreground);">
        No components yet. Add your first component →
      </div>
      <?php else:?>
      <table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">
        <thead><tr style="border-bottom:2px solid var(--border);background:var(--muted);">
          <?php foreach(['Name','Description','Status','Order','Active',''] as $h):?>
          <th style="padding:0.625rem 1rem;text-align:left;font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);"><?=$h?></th>
          <?php endforeach;?>
        </tr></thead>
        <tbody>
          <?php foreach($components as $c):?>
          <tr style="border-bottom:1px solid var(--border);transition:background 0.12s;"
              onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
            <td style="padding:0.75rem 1rem;font-weight:600;"><?=e($c['name'])?></td>
            <td style="padding:0.75rem 1rem;color:var(--muted-foreground);font-size:0.75rem;"><?=e($c['description']??'—')?></td>
            <td style="padding:0.75rem 1rem;"><?=statusBadge($c['status'])?></td>
            <td style="padding:0.75rem 1rem;color:var(--muted-foreground);"><?=(int)$c['sort_order']?></td>
            <td style="padding:0.75rem 1rem;text-align:center;">
              <?=$c['active']
                ? '<span style="color:var(--success-fg);font-size:0.75rem;font-weight:600;">Active</span>'
                : '<span style="color:var(--muted-foreground);font-size:0.75rem;">Off</span>'?>
            </td>
            <td style="padding:0.75rem 1rem;">
              <div style="display:flex;gap:0.375rem;">
                <a href="?edit_comp=<?=$c['id']?>" class="btn btn-ghost btn-sm">Edit</a>
                <form method="post" class="inline" onsubmit="return confirm('Delete component?')">
                  <?=csrfField()?>
                  <input type="hidden" name="action" value="comp_delete">
                  <input type="hidden" name="id" value="<?=$c['id']?>">
                  <button type="submit" class="btn btn-sm" style="background:var(--danger-soft);color:var(--danger-fg);border:none;">✕</button>
                </form>
              </div>
            </td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>
  </div>

  <!-- Add / Edit Component Form -->
  <?php
    $editComp = null;
    if (!empty($_GET['edit_comp']))
      $editComp = queryOne("SELECT * FROM status_components WHERE id=?", [(int)$_GET['edit_comp']]);
  ?>
  <div class="st-card">
    <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1.25rem;">
      <?=$editComp ? 'Edit Component' : 'Add Component'?>
    </h3>
    <form method="post" style="display:flex;flex-direction:column;gap:0.875rem;">
      <?=csrfField()?>
      <input type="hidden" name="action" value="comp_save">
      <input type="hidden" name="id" value="<?=(int)($editComp['id']??0)?>">

      <div>
        <label class="form-label fs-2xs2">Name <span class="text-danger-token">*</span></label>
        <input name="name" required class="form-input fs-sm2" value="<?=e($editComp['name']??'')?>">
      </div>
      <div>
        <label class="form-label fs-2xs2">Description</label>
        <input name="description" class="form-input fs-sm2" value="<?=e($editComp['description']??'')?>" placeholder="Brief description…">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
        <div>
          <label class="form-label fs-2xs2">Status</label>
          <select name="status" class="form-input fs-sm2">
            <?php foreach(['operational','degraded','partial','major','maintenance'] as $s):?>
            <option value="<?=$s?>" <?=($editComp['status']??'operational')===$s?'selected':''?>><?=ucfirst($s)?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div>
          <label class="form-label fs-2xs2">Sort Order</label>
          <input type="number" name="sort_order" class="form-input fs-sm2" value="<?=(int)($editComp['sort_order']??10)?>">
        </div>
      </div>
      <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.8125rem;">
        <input type="checkbox" name="active" <?=($editComp['active']??1)?'checked':''?>> Active
      </label>
      <div style="display:flex;gap:0.5rem;">
        <button type="submit" class="btn btn-primary btn-md" style="flex:1;">
          <?=$editComp ? 'Update Component' : 'Add Component'?>
        </button>
        <?php if($editComp):?>
        <a href="?" class="btn btn-ghost btn-md">Cancel</a>
        <?php endif;?>
      </div>
    </form>
  </div>
</div>

<!-- ══ Incidents ═════════════════════════════════════════════════ -->
<div style="margin-top:2rem;">
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat">Incidents (<?=count($incidents)?>)</h2>
    <?php if($openIncidents): ?>
    <span style="font-size:0.75rem;color:var(--danger-fg);font-weight:600;">
      <?=count($openIncidents)?> open
    </span>
    <?php endif;?>
  </div>

  <div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start;">

    <!-- Incidents table -->
    <div class="st-card ov-hidden">
      <?php if(empty($incidents)):?>
      <div style="padding:2.5rem;text-align:center;color:var(--muted-foreground);">No incidents. System is all-green! ✓</div>
      <?php else:?>
      <table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">
        <thead><tr style="border-bottom:2px solid var(--border);background:var(--muted);">
          <?php foreach(['Title','Component','Severity','Impact','Started','Resolved'] as $h):?>
          <th style="padding:0.625rem 1rem;text-align:left;font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);"><?=$h?></th>
          <?php endforeach;?>
        </tr></thead>
        <tbody>
          <?php foreach($incidents as $i):?>
          <tr style="border-bottom:1px solid var(--border);transition:background 0.12s;"
              onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
            <td style="padding:0.75rem 1rem;font-weight:600;max-width:220px;">
              <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=e($i['title'])?></div>
            </td>
            <td style="padding:0.75rem 1rem;color:var(--muted-foreground);"><?=e($i['comp_name']??'—')?></td>
            <td style="padding:0.75rem 1rem;"><?=statusBadge($i['severity'])?></td>
            <td style="padding:0.75rem 1rem;">
              <span style="font-size:0.75rem;text-transform:capitalize;"><?=e($i['impact'])?></span>
            </td>
            <td style="padding:0.75rem 1rem;font-size:0.75rem;color:var(--muted-foreground);">
              <?=date('M j, H:i', strtotime($i['started_at']))?>
            </td>
            <td style="padding:0.75rem 1rem;">
              <?php if($i['resolved_at']):?>
              <span style="font-size:0.75rem;color:var(--success-fg);font-weight:600;"><?=date('M j, H:i', strtotime($i['resolved_at']))?></span>
              <?php else:?>
              <span style="font-size:0.75rem;color:var(--danger-fg);font-weight:600;">Open</span>
              <?php endif;?>
            </td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <?php endif;?>
    </div>

    <!-- Right panel: Publish + Update forms -->
    <div style="display:flex;flex-direction:column;gap:1.25rem;">

      <!-- Publish new incident -->
      <div class="st-card">
        <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Publish Incident</h3>
        <form method="post" style="display:flex;flex-direction:column;gap:0.875rem;">
          <?=csrfField()?>
          <input type="hidden" name="action" value="incident_new">
          <div>
            <label class="form-label fs-2xs2">Title <span class="text-danger-token">*</span></label>
            <input name="title" required class="form-input fs-sm2" placeholder="e.g. CBS login delays">
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
            <div>
              <label class="form-label fs-2xs2">Component</label>
              <select name="component_id" class="form-input fs-sm2">
                <option value="">— All systems</option>
                <?php foreach($components as $c):?>
                <option value="<?=$c['id']?>"><?=e($c['name'])?></option>
                <?php endforeach;?>
              </select>
            </div>
            <div>
              <label class="form-label fs-2xs2">Severity</label>
              <select name="severity" class="form-input fs-sm2">
                <?php foreach(['investigating','identified','monitoring','resolved'] as $s):?>
                <option value="<?=$s?>"><?=ucfirst($s)?></option>
                <?php endforeach;?>
              </select>
            </div>
          </div>
          <div>
            <label class="form-label fs-2xs2">Impact</label>
            <select name="impact" class="form-input fs-sm2">
              <?php foreach(['none','minor','major','critical'] as $s):?>
              <option value="<?=$s?>"><?=ucfirst($s)?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div>
            <label class="form-label fs-2xs2">Details <span class="text-danger-token">*</span></label>
            <textarea name="body" rows="3" required class="form-input fs-sm-r" placeholder="What happened…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-md" style="width:100%;">Publish Incident</button>
        </form>
      </div>

      <!-- Append update -->
      <?php if($openIncidents):?>
      <div class="st-card">
        <h3 style="font-family:var(--font-display);font-size:1rem;font-weight:700;margin-bottom:1.25rem;">Post Update</h3>
        <form method="post" style="display:flex;flex-direction:column;gap:0.875rem;">
          <?=csrfField()?>
          <input type="hidden" name="action" value="incident_update">
          <div>
            <label class="form-label fs-2xs2">Incident</label>
            <select name="incident_id" required class="form-input fs-sm2">
              <option value="">— Select open incident</option>
              <?php foreach($openIncidents as $i):?>
              <option value="<?=$i['id']?>">[#<?=$i['id']?>] <?=e(truncate($i['title'],40))?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div>
            <label class="form-label fs-2xs2">New Status</label>
            <select name="severity" class="form-input fs-sm2">
              <?php foreach(['investigating','identified','monitoring','resolved'] as $s):?>
              <option value="<?=$s?>"><?=ucfirst($s)?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div>
            <label class="form-label fs-2xs2">Message <span class="text-danger-token">*</span></label>
            <input name="message" required class="form-input fs-sm2" placeholder="Update message…">
          </div>
          <button type="submit" class="btn btn-primary btn-md" style="width:100%;">Post Update</button>
        </form>
      </div>
      <?php endif;?>

    </div>
  </div>
</div>

<?php require_once '../includes/admin-layout-end.php'; ?>
