<?php
$pageTitle = 'Services';
require_once '../includes/admin-layout.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        try { execute("DELETE FROM services WHERE id=?", [(int)$_POST['id']]); $success = 'Service deleted.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif (in_array($action,['create','update'])) {
        $id         = (int)($_POST['id'] ?? 0);
        $title      = trim($_POST['title'] ?? '');
        $slug       = trim($_POST['slug'] ?? '') ?: makeSlug($title);
        $summary    = trim($_POST['summary'] ?? '');
        $features   = trim($_POST['features'] ?? '');
        $icon       = trim($_POST['icon'] ?? '');
        $icon_color = trim($_POST['icon_color'] ?? 'blue');
        $position   = (int)($_POST['position'] ?? 0);
        $active     = isset($_POST['active']) ? 1 : 0;

        if (!$title) { $error = 'Title is required.'; }
        else {
            try {
                if ($id) {
                    execute("UPDATE services SET title=?,slug=?,summary=?,features=?,icon=?,icon_color=?,position=?,active=?,updated_at=NOW() WHERE id=?",
                        [$title,$slug,$summary,$features,$icon,$icon_color,$position,$active,$id]);
                    $success = 'Service updated.';
                } else {
                    execute("INSERT INTO services (title,slug,summary,features,icon,icon_color,position,active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$title,$slug,$summary,$features,$icon,$icon_color,$position,$active]);
                    $success = 'Service added.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: '.$e->getMessage(); }
        }
    }
}

$services = [];
try { $services = query("SELECT id,title,slug,icon,icon_color,active,position FROM services ORDER BY position,id"); }
catch(\Throwable $e) { $error = 'services table not found.'; }

$editing = null;
if (!empty($_GET['edit'])) {
    try { $editing = queryOne("SELECT * FROM services WHERE id=?", [(int)$_GET['edit']]); }
    catch(\Throwable $e) {}
}

$COLORS = ['blue','green','purple','amber','teal','rose','orange','indigo'];
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">
<div>
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat"> Services (<?=count($services)?>)</h2>
    <a href="?new=1" class="btn btn-primary btn-sm">+ Add Service</a>
  </div>
  <div style="display:flex;flex-direction:column;gap:0.5rem;">
    <?php if(empty($services)):?>
    <div style="border:2px dashed var(--border);border-radius:1rem;padding:3rem;text-align:center;color:var(--muted-foreground);">No services yet.</div>
    <?php else: foreach($services as $s):?>
    <div class="st-card" style="padding:0.875rem 1.25rem;display:flex;align-items:center;gap:1rem;<?=!$s['active']?'opacity:0.55;':''?>">
      <span style="font-size:1.5rem;flex-shrink:0;"><?=e($s['icon']??'')?></span>
      <div class="flex-1-min">
        <div class="fw-strong"><?=e($s['title'])?></div>
        <div class="fs-xs-mt">slug: <?=e($s['slug'])?> · pos: <?=$s['position']?> · <?=$s['icon_color']?></div>
      </div>
      <div style="display:flex;gap:0.375rem;flex-shrink:0;">
        <a href="?edit=<?=$s['id']?>" class="btn btn-ghost btn-sm">Edit</a>
        <form method="POST" class="inline" onsubmit="return confirm('Delete this service?')">
          <?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$s['id']?>">
          <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;"><?=icon('trash-2',13)?></button>
        </form>
      </div>
    </div>
    <?php endforeach;endif;?>
  </div>
</div>

<!-- Form -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?=$editing?' Edit Service':' New Service'?></h3>
    <form method="POST" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <div style="display:grid;grid-template-columns:52px 1fr;gap:0.5rem;">
        <div>
          <label class="form-label fs-2xs2">Icon</label>
          <input type="text" name="icon" class="form-input" style="font-size:1.125rem;text-align:center;padding:0.5rem;" value="<?=e($editing['icon']??'')?>">
        </div>
        <div>
          <label class="form-label fs-2xs2">Title <span class="text-danger-token">*</span></label>
          <input type="text" name="title" required class="form-input fs-sm2" value="<?=e($editing['title']??'')?>">
        </div>
      </div>
      <div>
        <label class="form-label fs-2xs2">Slug</label>
        <input type="text" name="slug" class="form-input fs-sm2" value="<?=e($editing['slug']??'')?>" placeholder="auto">
      </div>
      <div>
        <label class="form-label fs-2xs2">Summary / Description</label>
        <textarea name="summary" class="form-input fs-sm-r" rows="3"><?=e($editing['summary']??'')?></textarea>
      </div>
      <div>
        <label class="form-label fs-2xs2">Feature Chips <span class="caption-meta">(comma-separated)</span></label>
        <input type="text" name="features" class="form-input fs-sm2"
               value="<?=e($editing['features']??'')?>"
               placeholder="e.g. Web Development, IT Support, Software Installation">
        <p class="caption-meta">Shown as pill chips on the public Services page.</p>
      </div>
      <div>
        <label class="form-label fs-2xs2">Icon Color Theme</label>
        <select name="icon_color" class="form-input fs-sm2">
          <?php foreach($COLORS as $c):?>
          <option value="<?=$c?>" <?=($editing['icon_color']??'blue')===$c?'selected':''?>><?=ucfirst($c)?></option>
          <?php endforeach;?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:80px 1fr;gap:0.5rem;align-items:end;">
        <div>
          <label class="form-label fs-2xs2">Position</label>
          <input type="number" name="position" class="form-input fs-sm2" value="<?=e($editing['position']??0)?>">
        </div>
        <div style="padding-bottom:0.5rem;">
          <label class="row-check">
            <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>> Active
          </label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100"><?=$editing?'Update Service':'Add Service'?></button>
      <?php if($editing):?><a href="?" class="btn btn-ghost w-100-c">Cancel</a><?php endif;?>
    </form>
  </div>
</div>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
