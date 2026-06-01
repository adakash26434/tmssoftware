<?php
$pageTitle = 'Banners';
require_once '../includes/admin-layout.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id       = (int)($_POST['id'] ?? 0);
        $title    = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $img      = trim($_POST['image_url'] ?? '');
        $link     = trim($_POST['link_url'] ?? '');
        $pos      = (int)($_POST['position'] ?? 0);
        $active   = isset($_POST['active']) ? 1 : 0;
        $page     = trim($_POST['page_target'] ?? '');
        $btn_text = trim($_POST['btn_text'] ?? '');
        $style    = trim($_POST['banner_style'] ?? 'info');

        if (!$title) {
            $error = 'Title is required.';
        } else {
            try {
                if ($id) {
                    execute(
                        "UPDATE banners SET title=?,subtitle=?,image_url=?,link_url=?,position=?,active=?,page_target=?,btn_text=?,banner_style=? WHERE id=?",
                        [$title,$subtitle?:null,$img?:null,$link?:null,$pos,$active,$page?:null,$btn_text?:null,$style,$id]
                    );
                    $success = 'Banner updated.';
                } else {
                    execute(
                        "INSERT INTO banners (title,subtitle,image_url,link_url,position,active,page_target,btn_text,banner_style) VALUES (?,?,?,?,?,?,?,?,?)",
                        [$title,$subtitle?:null,$img?:null,$link?:null,$pos,$active,$page?:null,$btn_text?:null,$style]
                    );
                    $success = 'Banner created.';
                }
            } catch(\Throwable $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            execute("UPDATE banners SET active = NOT active WHERE id=?", [$id]);
            $success = 'Status toggled.';
        } catch(\Throwable $e) { $error = 'Error toggling status.'; }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        try {
            execute("DELETE FROM banners WHERE id=?", [$id]);
            $success = 'Banner deleted.';
        } catch(\Throwable $e) { $error = 'Error deleting.'; }
    }
}

$banners = [];
try { $banners = query("SELECT * FROM banners ORDER BY position ASC, id DESC"); } catch(\Throwable $e) {}

$edit = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($banners as $b) { if ((int)$b['id'] === $eid) { $edit = $b; break; } }
}
?>

<?php if ($success): ?><div class="alert alert-success mb-1"><?= e($success) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-error mb-1"><?= e($error) ?></div><?php endif; ?>

<div class="af-split">

  <!-- Banner List -->
  <div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <h2 style="font-family:var(--font-display);font-size:1.125rem;font-weight:700;">All Banners (<?= count($banners) ?>)</h2>
      <a href="?new=1" class="btn btn-primary btn-sm">+ New Banner</a>
    </div>

    <?php if (empty($banners)): ?>
    <div style="border:2px dashed var(--border);border-radius:1rem;padding:3rem;text-align:center;color:var(--muted-foreground);">
      <div style="font-size:2.5rem;margin-bottom:0.75rem;"></div>
      <p>No banners yet. Create your first announcement banner!</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
      <?php foreach ($banners as $b): ?>
      <div class="st-card" style="padding:1.25rem;display:flex;align-items:flex-start;gap:1rem;">
        <!-- Color indicator -->
        <div style="width:4px;height:4rem;border-radius:9999px;flex-shrink:0;background:<?= $b['banner_style']==='success'?'var(--secondary)':($b['banner_style']==='warning'?'#f59e0b':($b['banner_style']==='danger'?'#ef4444':'#3b82f6')) ?>"></div>

        <?php if (!empty($b['image_url'])): ?>
        <img src="<?= e($b['image_url']) ?>" style="width:3.5rem;height:3.5rem;object-fit:cover;border-radius:0.5rem;flex-shrink:0;">
        <?php endif; ?>

        <div class="flex-1-min">
          <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.25rem;">
            <span style="font-weight:700;font-size:0.9375rem;color:var(--foreground);"><?= e($b['title']) ?></span>
            <?php if (!$b['active']): ?><span class="badge" style="background:var(--danger-soft);color:var(--danger);">Inactive</span><?php else: ?><span class="badge" style="background:var(--success-soft);color:var(--success-fg);">Active</span><?php endif; ?>
            <span class="badge badge-secondary"><?= e(ucfirst($b['banner_style'] ?? 'info')) ?></span>
            <?php if(!empty($b['page_target'])): ?><span class="badge" style="background:var(--background);border:1px solid var(--border);color:var(--muted-foreground);font-size:0.6875rem;"> <?= e($b['page_target']) ?></span><?php endif; ?>
          </div>
          <?php if(!empty($b['subtitle'])): ?><p class="fs-sm-mt"><?= e($b['subtitle']) ?></p><?php endif; ?>
          <?php if(!empty($b['link_url'])): ?><p style="font-size:0.75rem;color:var(--primary);margin-top:0.25rem;"> <?= e($b['link_url']) ?></p><?php endif; ?>
        </div>

        <div style="display:flex;gap:0.375rem;flex-shrink:0;flex-wrap:wrap;">
          <a href="?edit=<?= $b['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
          <form method="POST" class="inline">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="btn btn-outline btn-sm"><?= $b['active'] ? 'Disable' : 'Enable' ?></button>
          </form>
          <form method="POST" class="inline" onsubmit="return confirm('Delete this banner?')">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="btn btn-outline btn-sm text-danger-token">Delete</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Create / Edit Form -->
  <div>
    <div class="af-panel"><div class="st-card p-tile">
      <h3 class="h-eyebrow-tight">
        <?= $edit ? 'Edit Banner' : (isset($_GET['new']) ? 'Create Banner' : 'Banner Form') ?>
      </h3>
      <form method="POST" class="col-1-tight">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">

        <div>
          <label class="form-label">Title <span class="text-danger-token">*</span></label>
          <input type="text" name="title" required class="form-input" value="<?= e($edit['title'] ?? '') ?>" placeholder="Banner headline text">
        </div>
        <div>
          <label class="form-label">Subtitle / Body</label>
          <textarea name="subtitle" class="form-input" rows="2" placeholder="Supporting text..."><?= e($edit['subtitle'] ?? '') ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
          <div>
            <label class="form-label">Style</label>
            <select name="banner_style" class="form-input">
              <?php foreach(['info'=>'ℹ Info','success'=>' Success','warning'=>' Warning','danger'=>' Danger','promo'=>' Promo'] as $v=>$l):?>
              <option value="<?=$v?>" <?= ($edit['banner_style']??'info')===$v?'selected':'' ?>><?=$l?></option>
              <?php endforeach;?>
            </select>
          </div>
          <div>
            <label class="form-label">Position (order)</label>
            <input type="number" name="position" class="form-input" value="<?= $edit['position'] ?? 0 ?>" min="0">
          </div>
        </div>
        <div>
          <label class="form-label">Page Target (leave blank = all pages)</label>
          <input type="text" name="page_target" class="form-input" value="<?= e($edit['page_target'] ?? '') ?>" placeholder="e.g. index, products, pricing">
        </div>
        <div>
          <label class="form-label">Link URL</label>
          <input type="url" name="link_url" class="form-input" value="<?= e($edit['link_url'] ?? '') ?>" placeholder="https://...">
        </div>
        <div>
          <label class="form-label">Button Text</label>
          <input type="text" name="btn_text" class="form-input" value="<?= e($edit['btn_text'] ?? '') ?>" placeholder="e.g. Learn More">
        </div>
        <?php
          $imgField = 'image_url'; $imgValue = $edit['image_url'] ?? '';
          $imgLabel = 'Image (optional)';
          require __DIR__ . '/../includes/admin-img-upload.php';
        ?>
        <div style="display:flex;align-items:center;gap:0.5rem;">
          <input type="checkbox" name="active" id="active" <?= ($edit['active'] ?? 1) ? 'checked' : '' ?> style="width:1rem;height:1rem;accent-color:var(--primary);">
          <label for="active" style="font-size:0.875rem;font-weight:500;color:var(--foreground);">Active (show this banner)</label>
        </div>
        <div style="display:flex;gap:0.5rem;padding-top:0.5rem;">
          <button type="submit" class="btn btn-primary flex-1"><?= $edit ? 'Update Banner' : 'Create Banner' ?></button>
          <?php if ($edit): ?><a href="banners.php" class="btn btn-outline">Cancel</a><?php endif; ?>
        </div>
      </form>
    </div></div>
  </div>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
