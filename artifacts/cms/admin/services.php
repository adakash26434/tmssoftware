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
        $title      = trim($_POST['title']      ?? '');
        $slug       = trim($_POST['slug']       ?? '') ?: makeSlug($title);
        $tagline    = trim($_POST['tagline']    ?? '');
        $summary    = trim($_POST['summary']    ?? '');
        $badge      = trim($_POST['badge']      ?? '');
        $price_from = trim($_POST['price_from'] ?? '');
        $icon       = trim($_POST['icon']       ?? '');
        $lucide_icon= trim($_POST['lucide_icon']?? '');
        $icon_color = trim($_POST['icon_color'] ?? 'blue');
        $position   = (int)($_POST['position']  ?? 0);
        $active     = isset($_POST['active']) ? 1 : 0;

        // features: comma-separated chips
        $features = trim($_POST['features'] ?? '');

        // highlights: one per line → JSON array
        $highlights = json_encode(array_values(array_filter(
            array_map('trim', explode("\n", $_POST['highlights'] ?? ''))
        )));

        if (!$title) { $error = 'Title is required.'; }
        else {
            try {
                if ($id) {
                    execute(
                        "UPDATE services SET title=?,slug=?,tagline=?,summary=?,badge=?,price_from=?,icon=?,lucide_icon=?,icon_color=?,features=?,highlights=?,position=?,active=?,updated_at=NOW() WHERE id=?",
                        [$title,$slug,$tagline,$summary,$badge,$price_from?:null,$icon,$lucide_icon,$icon_color,$features,$highlights,$position,$active,$id]
                    );
                    $success = 'Service updated.';
                } else {
                    execute(
                        "INSERT INTO services (title,slug,tagline,summary,badge,price_from,icon,lucide_icon,icon_color,features,highlights,position,active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$title,$slug,$tagline,$summary,$badge,$price_from?:null,$icon,$lucide_icon,$icon_color,$features,$highlights,$position,$active]
                    );
                    $success = 'Service added.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: '.$e->getMessage(); }
        }
    }
}

$services = [];
try { $services = query("SELECT id,title,slug,tagline,badge,icon,lucide_icon,icon_color,price_from,active,position FROM services ORDER BY position,id"); }
catch(\Throwable $e) { $error = 'services table not found.'; }

$editing = null;
if (!empty($_GET['edit'])) {
    try { $editing = queryOne("SELECT * FROM services WHERE id=?", [(int)$_GET['edit']]); }
    catch(\Throwable $e) {}
}

$COLORS = ['blue','green','purple','amber','teal','rose','orange','indigo'];
$BADGES = ['','Flagship','Popular','Essential','New','Add-on','Included','Audit'];
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">
<div>
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat">Services (<?=count($services)?>)</h2>
    <a href="?new=1" class="btn btn-primary btn-sm">+ Add Service</a>
  </div>
  <div style="display:flex;flex-direction:column;gap:0.5rem;">
    <?php if(empty($services)):?>
    <div style="border:2px dashed var(--border);border-radius:1rem;padding:3rem;text-align:center;color:var(--muted-foreground);">No services yet.</div>
    <?php else: foreach($services as $s):?>
    <div class="st-card" style="padding:0.875rem 1.25rem;display:flex;align-items:center;gap:1rem;<?=!$s['active']?'opacity:0.55;':''?>">
      <div style="width:2rem;height:2rem;border-radius:0.5rem;background:var(--primary-light);display:grid;place-items:center;flex-shrink:0;">
        <i data-lucide="<?=e($s['lucide_icon']??$s['icon']??'layers')?>" style="width:14px;height:14px;color:var(--primary);"></i>
      </div>
      <div class="flex-1-min">
        <div class="fw-strong"><?=e($s['title'])?>
          <?php if(!empty($s['badge'])):?>
          <span style="display:inline-block;margin-left:0.375rem;font-size:0.65rem;font-weight:700;padding:0.1rem 0.4rem;border-radius:9999px;background:var(--primary-light);color:var(--primary);"><?=e($s['badge'])?></span>
          <?php endif;?>
        </div>
        <div class="fs-xs-mt"><?=e($s['tagline']??'')?></div>
        <div class="fs-xs-mt" style="color:var(--muted-foreground);">
          slug: <?=e($s['slug'])?> · pos: <?=$s['position']?> · <?=$s['icon_color']?>
          <?php if($s['price_from']):?> · NPR <?=number_format((float)$s['price_from'],0)?><?php endif;?>
        </div>
      </div>
      <div style="display:flex;gap:0.375rem;flex-shrink:0;">
        <a href="?edit=<?=$s['id']?>" class="btn btn-ghost btn-sm">Edit</a>
        <form method="POST" class="inline" onsubmit="return confirm('Delete this service?')">
          <?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$s['id']?>">
          <button type="submit" class="btn btn-sm" style="background:var(--danger-soft);color:var(--danger-fg);border:none;"><?=icon('trash-2',13)?></button>
        </form>
      </div>
    </div>
    <?php endforeach;endif;?>
  </div>
</div>

<!-- Form -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?=$editing?'Edit Service':'New Service'?></h3>
    <form method="POST" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <!-- Title + Lucide icon on one row -->
      <div style="display:grid;grid-template-columns:1fr 120px;gap:0.5rem;align-items:end;">
        <div>
          <label class="form-label">Title <span class="text-danger-token">*</span></label>
          <input type="text" name="title" required class="form-input" value="<?=e($editing['title']??'')?>">
        </div>
        <div>
          <label class="form-label">Lucide Icon</label>
          <input type="text" name="lucide_icon" class="form-input" value="<?=e($editing['lucide_icon']??'')?>" placeholder="e.g. cloud">
        </div>
      </div>

      <div>
        <label class="form-label">Tagline <span class="caption-meta">(shown under title on card)</span></label>
        <input type="text" name="tagline" class="form-input" value="<?=e($editing['tagline']??'')?>" placeholder="e.g. Managed cloud for Nepal businesses">
      </div>

      <div>
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-input" value="<?=e($editing['slug']??'')?>" placeholder="auto-generated">
      </div>

      <div>
        <label class="form-label">Summary / Description</label>
        <textarea name="summary" class="form-input" rows="3"><?=e($editing['summary']??'')?></textarea>
      </div>

      <!-- Badge + Price on same row -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
        <div>
          <label class="form-label">Badge</label>
          <select name="badge" class="form-input">
            <?php foreach($BADGES as $b):?>
            <option value="<?=$b?>" <?=($editing['badge']??'')===$b?'selected':''?>><?=$b?:'-none-'?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div>
          <label class="form-label">Price From (NPR)</label>
          <input type="number" name="price_from" class="form-input" step="0.01" min="0"
                 value="<?=e($editing['price_from']??'')?>" placeholder="e.g. 4999">
          <p class="caption-meta">Leave blank = "Contact us". Set 0 = "Included".</p>
        </div>
      </div>

      <div>
        <label class="form-label">Highlights <span class="caption-meta">(one per line — shown as ✓ bullet points on card)</span></label>
        <textarea name="highlights" class="form-input" rows="4" placeholder="Member & KYC&#10;Savings & FD&#10;Loan Lifecycle&#10;NRB Reports"><?php
          $hArr = json_decode($editing['highlights']??'[]', true);
          echo e(is_array($hArr) ? implode("\n", $hArr) : ($editing['highlights']??''));
        ?></textarea>
      </div>

      <div>
        <label class="form-label">Feature Chips <span class="caption-meta">(comma-separated — optional secondary list)</span></label>
        <input type="text" name="features" class="form-input"
               value="<?=e($editing['features']??'')?>"
               placeholder="e.g. Member & KYC, Savings & FD, Loan Lifecycle">
      </div>

      <!-- Icon color + Position + Active -->
      <div style="display:grid;grid-template-columns:1fr 80px;gap:0.5rem;align-items:end;">
        <div>
          <label class="form-label">Icon Color Theme</label>
          <select name="icon_color" class="form-input">
            <?php foreach($COLORS as $c):?>
            <option value="<?=$c?>" <?=($editing['icon_color']??'blue')===$c?'selected':''?>><?=ucfirst($c)?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div>
          <label class="form-label">Position</label>
          <input type="number" name="position" class="form-input" value="<?=e($editing['position']??0)?>">
        </div>
      </div>

      <div>
        <label class="row-check">
          <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>>
          Active (show on public Services page)
        </label>
      </div>

      <div class="af-form-footer">
        <button type="submit" class="btn btn-primary flex-1"><?=$editing?'Update Service':'Add Service'?></button>
        <?php if($editing):?><a href="?" class="btn btn-outline">Cancel</a><?php endif;?>
      </div>
    </form>
  </div>
</div>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
