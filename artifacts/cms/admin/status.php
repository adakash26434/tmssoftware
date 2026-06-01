<?php
$pageTitle = 'Status Page';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $msg = 'CSRF mismatch.'; }
    else {
        $a = $_POST['action'] ?? '';
        if ($a === 'comp_save') {
            $id = (int)($_POST['id'] ?? 0);
            $row = [trim($_POST['name']), trim($_POST['description']),
                    $_POST['status'], (int)$_POST['sort_order'], isset($_POST['active'])?1:0];
            if ($id) execute("UPDATE status_components SET name=?,description=?,status=?,sort_order=?,active=? WHERE id=?", array_merge($row, [$id]));
            else execute("INSERT INTO status_components (name,description,status,sort_order,active) VALUES (?,?,?,?,?)", $row);
            $msg = 'Component saved.';
        } elseif ($a === 'incident_new') {
            $iid = execute("INSERT INTO status_incidents (title,body,severity,impact,component_id) VALUES (?,?,?,?,?)",
              [trim($_POST['title']), trim($_POST['body']),
               $_POST['severity'] ?? 'investigating', $_POST['impact'] ?? 'minor',
               (int)($_POST['component_id'] ?? 0) ?: null]);
            execute("INSERT INTO status_incident_updates (incident_id,status,message) VALUES (?,?,?)",
              [$iid, $_POST['severity'] ?? 'investigating', trim($_POST['body'])]);
            $msg = 'Incident published.';
        } elseif ($a === 'incident_update') {
            $iid = (int)$_POST['incident_id'];
            $sev = $_POST['severity'] ?? 'monitoring';
            execute("INSERT INTO status_incident_updates (incident_id,status,message) VALUES (?,?,?)",
              [$iid, $sev, trim($_POST['message'])]);
            execute("UPDATE status_incidents SET severity=?, resolved_at=IF(?='resolved', NOW(), resolved_at) WHERE id=?",
              [$sev, $sev, $iid]);
            $msg = 'Update posted.';
        }
    }
}

$components = query("SELECT * FROM status_components ORDER BY sort_order,id");
$incidents  = query("SELECT i.*, c.name AS comp_name FROM status_incidents i LEFT JOIN status_components c ON c.id=i.component_id ORDER BY i.started_at DESC LIMIT 30");

require_once '../includes/admin-layout.php';
?>
<div class="card">
  <h1>Status Page</h1>
  <p class="muted">Manage components shown on <a href="<?= url('status.php') ?>" target="_blank">/status</a> and publish incidents.</p>
  <?php if ($msg): ?><div class="alert info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <h3>Components</h3>
  <table class="data">
    <thead><tr><th>Name</th><th>Status</th><th>Order</th><th>Active</th></tr></thead>
    <tbody>
      <?php foreach ($components as $c): ?>
        <tr><td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['status']) ?></td>
            <td><?= (int)$c['sort_order'] ?></td>
            <td><?= $c['active']?'Yes':'No' ?></td></tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <h3>Add / update component</h3>
  <form method="post" class="grid" style="grid-template-columns:repeat(5,1fr);gap:10px;align-items:end;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="comp_save">
    <label>ID (blank=new)<input name="id"></label>
    <label>Name<input name="name" required></label>
    <label>Description<input name="description"></label>
    <label>Status
      <select name="status">
        <option>operational</option><option>degraded</option><option>partial</option>
        <option>major</option><option>maintenance</option>
      </select>
    </label>
    <label>Sort<input type="number" name="sort_order" value="10"></label>
    <label><input type="checkbox" name="active" checked> Active</label>
    <button class="btn primary grid-full">Save component</button>
  </form>
</div>

<div class="card">
  <h2>Open / recent incidents</h2>
  <table class="data">
    <thead><tr><th>Title</th><th>Component</th><th>Severity</th><th>Impact</th><th>Started</th><th>Resolved</th></tr></thead>
    <tbody>
    <?php foreach ($incidents as $i): ?>
      <tr><td><?= htmlspecialchars($i['title']) ?></td>
          <td><?= htmlspecialchars($i['comp_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($i['severity']) ?></td>
          <td><?= htmlspecialchars($i['impact']) ?></td>
          <td><?= htmlspecialchars($i['started_at']) ?></td>
          <td><?= htmlspecialchars($i['resolved_at'] ?? '—') ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <h3>Publish new incident</h3>
  <form method="post" class="grid" style="grid-template-columns:repeat(4,1fr);gap:10px;align-items:end;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="incident_new">
    <label>Title<input name="title" required></label>
    <label>Component
      <select name="component_id"><option value="">—</option>
        <?php foreach ($components as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Severity
      <select name="severity"><option>investigating</option><option>identified</option><option>monitoring</option><option>resolved</option></select>
    </label>
    <label>Impact
      <select name="impact"><option>none</option><option>minor</option><option>major</option><option>critical</option></select>
    </label>
    <label class="grid-full">Details<textarea name="body" rows="3" required></textarea></label>
    <button class="btn primary grid-full">Publish incident</button>
  </form>

  <h3>Append update to existing incident</h3>
  <form method="post" class="grid" style="grid-template-columns:1fr 1fr 3fr auto;gap:10px;align-items:end;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="incident_update">
    <label>Incident ID<input type="number" name="incident_id" required></label>
    <label>Status
      <select name="severity"><option>investigating</option><option>identified</option><option>monitoring</option><option>resolved</option></select>
    </label>
    <label>Message<input name="message" required></label>
    <button class="btn">Post update</button>
  </form>
</div>
<?php require_once '../includes/admin-layout-end.php'; ?>
