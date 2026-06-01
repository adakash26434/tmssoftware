<?php
$pageTitle = 'News & Blog';
require_once '../includes/admin-layout.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        try { execute("DELETE FROM news WHERE id=?", [$id]); $success = 'Post deleted.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif (in_array($action, ['create','update'])) {
        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $slug        = trim($_POST['slug'] ?? '') ?: makeSlug($title);
        $excerpt     = trim($_POST['excerpt'] ?? '');
        $content     = trim($_POST['content'] ?? '');
        $cover_url   = trim($_POST['cover_url'] ?? '');
        $author_name = trim($_POST['author_name'] ?? 'Ankur Infotech Pvt. Ltd.');
        $category    = trim($_POST['category'] ?? 'General');
        $read_time   = (int)($_POST['read_time'] ?? 5);
        $featured    = isset($_POST['featured']) ? 1 : 0;
        $published   = isset($_POST['published']) ? 1 : 0;
        $published_at= $_POST['published_at'] ?: ($published ? date('Y-m-d H:i:s') : null);
        $tags        = json_encode(array_values(array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')))));
        $active      = isset($_POST['active']) ? 1 : 0;

        if (!$title) { $error = 'Title is required.'; }
        else {
            // Check slug uniqueness
            $existing = queryOne("SELECT id FROM news WHERE slug=? AND id!=?", [$slug, $id]);
            if ($existing) { $slug .= '-' . time(); }

            try {
                if ($id) {
                    execute("UPDATE news SET title=?,slug=?,excerpt=?,content=?,cover_url=?,author_name=?,category=?,tags=?,read_time=?,featured=?,published=?,published_at=?,active=?,updated_at=NOW() WHERE id=?",
                        [$title,$slug,$excerpt,$content,$cover_url?:null,$author_name,$category,$tags,$read_time,$featured,$published,$published_at,$active,$id]);
                    $success = 'Post updated.';
                } else {
                    execute("INSERT INTO news (title,slug,excerpt,content,cover_url,author_name,category,tags,read_time,featured,published,published_at,active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$title,$slug,$excerpt,$content,$cover_url?:null,$author_name,$category,$tags,$read_time,$featured,$published,$published_at,$active]);
                    $success = 'Post created.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: ' . $e->getMessage(); }
        }
    }
}

$posts = [];
try { $posts = query("SELECT id,title,slug,author_name,category,published,featured,published_at,active,read_time FROM news ORDER BY COALESCE(published_at,created_at) DESC"); }
catch(\Throwable $e) { $error = '"news" table not found. Run database.sql first.'; }

$editing = null;
if (!empty($_GET['edit'])) {
    try {
        $editing = queryOne("SELECT * FROM news WHERE id=?", [(int)$_GET['edit']]);
        if ($editing && !empty($editing['tags'])) {
            $t = json_decode($editing['tags'],true) ?? [];
            $editing['tags_text'] = implode(', ', $t);
        }
    } catch(\Throwable $e) {}
}

$CATS = ['General','Product Update','Company News','Cooperatives Nepal','Technology','Tutorial','Case Study','Events'];
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">

<!-- List -->
<div>
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat"> Blog Posts (<?=count($posts)?>)</h2>
    <a href="?new=1" class="btn btn-primary btn-sm">+ New Post</a>
  </div>

  <div class="st-card ov-hidden">
    <table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">
      <thead><tr style="border-bottom:2px solid var(--border);background:var(--muted);">
        <?php foreach(['Title','Category','Author','Published','Active',''] as $h):?>
        <th style="padding:0.625rem 1rem;text-align:left;font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);"><?=$h?></th>
        <?php endforeach;?>
      </tr></thead>
      <tbody>
        <?php if(empty($posts)):?>
        <tr><td colspan="6" class="p-empty">No posts yet. Click "+ New Post".</td></tr>
        <?php else: foreach($posts as $p): ?>
        <tr style="border-bottom:1px solid var(--border);transition:background 0.12s;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
          <td style="padding:0.75rem 1rem;max-width:250px;">
            <div style="font-weight:600;color:var(--foreground);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=e(truncate($p['title'],45))?></div>
            <div class="fs-2xs-mt"><?=e($p['slug'])?> · <?=$p['read_time']?>min read</div>
          </td>
          <td class="p-row"><span style="padding:0.15rem 0.5rem;border-radius:9999px;background:var(--muted);color:var(--muted-foreground);font-size:0.6875rem;"><?=e($p['category'])?></span></td>
          <td style="padding:0.75rem 1rem;color:var(--muted-foreground);"><?=e($p['author_name']??'—')?></td>
          <td class="p-row">
            <?php if($p['published'] && $p['published_at']):?>
            <span style="font-size:0.75rem;color:#15803d;font-weight:600;"> <?=date('M j, Y',strtotime($p['published_at']))?></span>
            <?php elseif($p['published']):?>
            <span style="font-size:0.75rem;color:#15803d;">Published</span>
            <?php else:?>
            <span class="fs-xs-mt">Draft</span>
            <?php endif;?>
            <?php if($p['featured']):?><span style="margin-left:0.25rem;font-size:0.6rem;padding:0.1rem 0.35rem;border-radius:9999px;background:#fef9c3;color:#b45309;font-weight:600;">FEATURED</span><?php endif;?>
          </td>
          <td style="padding:0.75rem 1rem;text-align:center;"><?=$p['active']?'':'⬜'?></td>
          <td class="p-row">
            <div style="display:flex;gap:0.375rem;">
              <a href="?edit=<?=$p['id']?>" class="btn btn-ghost btn-sm">Edit</a>
              <form method="POST" class="inline" onsubmit="return confirm('Delete this post?')">
                <?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$p['id']?>">
                <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;"></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach;endif;?>
      </tbody>
    </table>
  </div>
</div>

<!-- Form -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight">
      <?=$editing?' Edit Post':( isset($_GET['new'])?' New Post':' New Post')?>
    </h3>
    <form method="POST" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <div>
        <label class="form-label fs-2xs2">Title <span class="text-danger-token">*</span></label>
        <input type="text" name="title" required class="form-input fs-sm2" value="<?=e($editing['title']??'')?>">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
        <div>
          <label class="form-label fs-2xs2">Slug (URL)</label>
          <input type="text" name="slug" class="form-input fs-sm2" value="<?=e($editing['slug']??'')?>" placeholder="auto-generated">
        </div>
        <div>
          <label class="form-label fs-2xs2">Category</label>
          <select name="category" class="form-input fs-sm2">
            <?php foreach($CATS as $c):?>
            <option value="<?=$c?>" <?=($editing['category']??'General')===$c?'selected':''?>><?=$c?></option>
            <?php endforeach;?>
          </select>
        </div>
      </div>
      <div>
        <label class="form-label fs-2xs2">Author Name</label>
        <input type="text" name="author_name" class="form-input fs-sm2" value="<?=e($editing['author_name']??'Ankur Infotech Pvt. Ltd.')?>">
      </div>
      <div>
        <label class="form-label fs-2xs2">Cover Image URL</label>
        <input type="url" name="cover_url" class="form-input fs-sm2" value="<?=e($editing['cover_url']??'')?>" placeholder="https://...">
      </div>
      <div>
        <label class="form-label fs-2xs2">Excerpt (for cards)</label>
        <textarea name="excerpt" class="form-input fs-sm-r" rows="2"><?=e($editing['excerpt']??'')?></textarea>
      </div>
      <div>
        <label class="form-label fs-2xs2">Body Content (HTML)</label>
        <textarea name="content" class="form-input" rows="8" style="font-size:0.8125rem;resize:vertical;font-family:monospace;"><?=e($editing['content']??'')?></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
        <div>
          <label class="form-label fs-2xs2">Tags (comma-separated)</label>
          <input type="text" name="tags" class="form-input fs-sm2" value="<?=e($editing['tags_text']??'')?>" placeholder="Software, IT, Nepal">
        </div>
        <div>
          <label class="form-label fs-2xs2">Read Time (min)</label>
          <input type="number" name="read_time" min="1" max="60" class="form-input fs-sm2" value="<?=e($editing['read_time']??5)?>">
        </div>
      </div>
      <div>
        <label class="form-label fs-2xs2">Publish Date/Time</label>
        <input type="datetime-local" name="published_at" class="form-input fs-sm2" value="<?=e(isset($editing['published_at'])&&$editing['published_at']?str_replace(' ','T',substr($editing['published_at'],0,16)):'')?>">
      </div>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <label class="row-check">
          <input type="checkbox" name="published" value="1" <?=($editing['published']??0)?'checked':''?>> Published
        </label>
        <label class="row-check">
          <input type="checkbox" name="featured" value="1" <?=($editing['featured']??0)?'checked':''?>> Featured
        </label>
        <label class="row-check">
          <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>> Active
        </label>
      </div>
      <button type="submit" class="btn btn-primary w-100"><?=$editing?'Update Post':'Create Post'?></button>
      <?php if($editing):?><a href="?" class="btn btn-ghost w-100-c">Cancel</a><?php endif;?>
    </form>
  </div>
</div>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
