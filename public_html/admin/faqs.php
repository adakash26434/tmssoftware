<?php
$pageTitle = 'FAQs';
require_once '../includes/admin-layout.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        try { execute("DELETE FROM faqs WHERE id=?", [(int)$_POST['id']]); $success = 'FAQ deleted.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif (in_array($action,['create','update'])) {
        $id       = (int)($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        $position = (int)($_POST['position'] ?? 0);
        $active   = isset($_POST['active']) ? 1 : 0;

        if (!$question || !$answer) { $error = 'Question and answer are required.'; }
        else {
            try {
                if ($id) {
                    execute("UPDATE faqs SET question=?,answer=?,category=?,position=?,active=?,updated_at=NOW() WHERE id=?",
                        [$question,$answer,$category,$position,$active,$id]);
                    $success = 'FAQ updated.';
                } else {
                    execute("INSERT INTO faqs (question,answer,category,position,active,created_at,updated_at) VALUES (?,?,?,?,?,NOW(),NOW())",
                        [$question,$answer,$category,$position,$active]);
                    $success = 'FAQ added.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: '.$e->getMessage(); }
        }
    }
}

$faqs = [];
try { $faqs = query("SELECT id,category,question,position,active FROM faqs ORDER BY category,position,id"); }
catch(\Throwable $e) { $error = 'faqs table not found. Run database.sql.'; }

$editing = null;
if (!empty($_GET['edit'])) {
    try { $editing = queryOne("SELECT * FROM faqs WHERE id=?", [(int)$_GET['edit']]); }
    catch(\Throwable $e) {}
}

// Group by category
$byCat = [];
foreach ($faqs as $f) { $byCat[$f['category']][] = $f; }

$CATS = ['General','Products','Pricing','Support','Technical','About'];
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">
<div>
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat"> FAQs (<?=count($faqs)?>)</h2>
    <a href="?new=1" class="btn btn-primary btn-sm">+ Add FAQ</a>
  </div>

  <?php if(empty($faqs)):?>
  <div style="border:2px dashed var(--border);border-radius:1rem;padding:3rem;text-align:center;color:var(--muted-foreground);">No FAQs yet. Add your first FAQ!</div>
  <?php else: foreach($byCat as $cat => $items):?>
  <div class="mb-1-25">
    <div style="font-size:0.6875rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted-foreground);margin-bottom:0.5rem;padding:0 0.25rem;"><?=e($cat)?></div>
    <div style="display:flex;flex-direction:column;gap:0.25rem;">
      <?php foreach($items as $f):?>
      <div class="st-card" style="padding:0.75rem 1rem;display:flex;align-items:center;gap:0.875rem;<?=!$f['active']?'opacity:0.55;':''?>">
        <div class="flex-1-min">
          <div style="font-size:0.875rem;font-weight:500;color:var(--foreground);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=e(truncate($f['question'],70))?></div>
          <div class="fs-2xs-mt">Position: <?=$f['position']?> · <?=$f['active']?'Active':'Inactive'?></div>
        </div>
        <div style="display:flex;gap:0.375rem;flex-shrink:0;">
          <a href="?edit=<?=$f['id']?>" class="btn btn-ghost btn-sm">Edit</a>
          <form method="POST" class="inline" onsubmit="return confirm('Delete this FAQ?')">
            <?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$f['id']?>">
            <button type="submit" class="btn btn-sm" style="background:var(--danger-soft);color:var(--danger-fg);border:none;"></button>
          </form>
        </div>
      </div>
      <?php endforeach;?>
    </div>
  </div>
  <?php endforeach;endif;?>
</div>

<!-- Form -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?=$editing?' Edit FAQ':' New FAQ'?></h3>
    <form method="POST" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <div>
        <label class="form-label fs-2xs2">Category</label>
        <select name="category" class="form-input fs-sm2">
          <?php foreach($CATS as $c):?>
          <option value="<?=$c?>" <?=($editing['category']??'General')===$c?'selected':''?>><?=$c?></option>
          <?php endforeach;?>
        </select>
      </div>
      <div>
        <label class="form-label fs-2xs2">Question <span class="text-danger-token">*</span></label>
        <textarea name="question" required class="form-input fs-sm-r" rows="3"><?=e($editing['question']??'')?></textarea>
      </div>
      <div>
        <label class="form-label fs-2xs2">Answer <span class="text-danger-token">*</span></label>
        <textarea name="answer" required class="form-input fs-sm-r" rows="5"><?=e($editing['answer']??'')?></textarea>
      </div>
      <div style="display:grid;grid-template-columns:80px 1fr;gap:0.5rem;align-items:end;">
        <div>
          <label class="form-label fs-2xs2">Position</label>
          <input type="number" name="position" class="form-input fs-sm2" value="<?=e($editing['position']??0)?>">
        </div>
        <div style="padding-bottom:0.5rem;">
          <label class="row-check">
            <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>> Active / Visible
          </label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100"><?=$editing?'Update FAQ':'Add FAQ'?></button>
      <?php if($editing):?><a href="?" class="btn btn-ghost w-100-c">Cancel</a><?php endif;?>
    </form>
  </div>
</div>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
