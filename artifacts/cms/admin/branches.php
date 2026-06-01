<?php
$pageTitle = 'Branches';
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
requireAdmin();

$error = $success = '';
$clientId = (int)($_GET['client_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) { $error = 'Security token mismatch.'; }
    else {
        $a = $_POST['action'] ?? '';
        if ($a === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $data = [
                trim($_POST['code'] ?? ''),
                trim($_POST['name'] ?? ''),
                trim($_POST['address'] ?? ''),
                trim($_POST['district'] ?? ''),
                trim($_POST['province'] ?? ''),
                trim($_POST['phone'] ?? ''),
                trim($_POST['manager'] ?? ''),
                isset($_POST['is_head']) ? 1 : 0,
                isset($_POST['active']) ? 1 : 0,
            ];
            if ($id) {
                execute("UPDATE branches SET code=?,name=?,address=?,district=?,province=?,phone=?,manager=?,is_head=?,active=? WHERE id=?",
                        array_merge($data, [$id]));
            } else {
                execute("INSERT INTO branches (client_id,code,name,address,district,province,phone,manager,is_head,active)
                         VALUES (?,?,?,?,?,?,?,?,?,?)", array_merge([$clientId], $data));
            }
            $success = 'Branch saved.';
        } elseif ($a === 'delete') {
            execute("DELETE FROM branches WHERE id=?", [(int)$_POST['id']]);
            $success = 'Branch deleted.';
        }
    }
}

$clients  = query("SELECT id,org_name FROM clients ORDER BY org_name");
$branches = $clientId ? query("SELECT * FROM branches WHERE client_id=? ORDER BY is_head DESC,name", [$clientId]) : [];

require_once '../includes/admin-layout.php';
?>
<div class="card">
  <h1>Branches</h1>
  <form method="get" style="margin-bottom:12px;">
    <label>Select client
      <select name="client_id" onchange="this.form.submit()">
        <option value="">— choose —</option>
        <?php foreach ($clients as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $clientId===$c['id']?'selected':'' ?>><?= htmlspecialchars($c['org_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>

  <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <?php if ($clientId): ?>
    <h3>Add / edit branch</h3>
    <form method="post" class="grid" style="grid-template-columns:repeat(4,1fr);gap:10px;align-items:end;">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="">
      <label>Code<input name="code" required></label>
      <label>Name<input name="name" required></label>
      <label>Province<input name="province"></label>
      <label>District<input name="district"></label>
      <label>Address<input name="address"></label>
      <label>Phone<input name="phone"></label>
      <label>Manager<input name="manager"></label>
      <label style="display:flex;gap:14px;align-items:center;">
        <span><input type="checkbox" name="is_head"> Head office</span>
        <span><input type="checkbox" name="active" checked> Active</span>
      </label>
      <button class="btn primary grid-full">Save branch</button>
    </form>

    <table class="data" style="margin-top:18px;">
      <thead><tr><th>Code</th><th>Name</th><th>District</th><th>Manager</th><th>Phone</th><th>Head</th><th>Active</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($branches as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['code']) ?></td>
            <td><?= htmlspecialchars($b['name']) ?></td>
            <td><?= htmlspecialchars($b['district'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['manager'] ?? '') ?></td>
            <td><?= htmlspecialchars($b['phone'] ?? '') ?></td>
            <td><?= $b['is_head'] ? 'Yes' : '' ?></td>
            <td><?= $b['active'] ? 'Yes' : 'No' ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Delete branch?');" class="inline">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $b['id'] ?>">
                <button class="btn danger small">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p><a href="<?= url('admin/export.php?preset=branches') ?>" class="btn">Export branches CSV</a></p>
  <?php endif; ?>
</div>
<?php require_once '../includes/admin-layout-end.php'; ?>
