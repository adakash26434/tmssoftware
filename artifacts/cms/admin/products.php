<?php
$pageTitle = 'Products';
require_once '../includes/admin-layout.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        try { execute("DELETE FROM products WHERE id=?", [$id]); $success = 'Product deleted.'; }
        catch(\Throwable $e) { $error = 'Cannot delete — may be referenced elsewhere.'; }
    } elseif (in_array($action, ['create','update'])) {
        $id        = (int)($_POST['id'] ?? 0);
        $name      = trim($_POST['name'] ?? '');
        $slug      = trim($_POST['slug'] ?? '') ?: makeSlug($name);
        $tagline   = trim($_POST['tagline'] ?? '');
        $summary   = trim($_POST['summary'] ?? '');
        $desc      = trim($_POST['description'] ?? '');
        $icon      = trim($_POST['icon'] ?? '');
        $badge     = trim($_POST['badge'] ?? '');
        $price     = trim($_POST['price_from'] ?? '');
        $category  = trim($_POST['category'] ?? '');
        $position  = (int)($_POST['position'] ?? 0);
        $active    = isset($_POST['active']) ? 1 : 0;
        $features  = json_encode(array_values(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')))));
        $highlights= json_encode(array_values(array_filter(array_map('trim', explode("\n", $_POST['highlights'] ?? '')))));
        $lucide_icon  = trim($_POST['lucide_icon']   ?? '');
        $icon_color   = trim($_POST['icon_color']    ?? 'blue');
        $show_on_home = isset($_POST['show_on_home']) ? 1 : 0;
        $home_position= (int)($_POST['home_position'] ?? 0);
        $home_card_wide = isset($_POST['home_card_wide']) ? 1 : 0;
        $home_card_dark = isset($_POST['home_card_dark']) ? 1 : 0;
        $home_bg_css  = trim($_POST['home_bg_css']   ?? '');
        $demo_ss_url  = trim($_POST['demo_screenshot_url'] ?? '');
        $tab_label    = trim($_POST['tab_label']     ?? '');

        if (!$name) { $error = 'Product name is required.'; }
        else {
            try {
                if ($id) {
                    execute("UPDATE products SET name=?,slug=?,tagline=?,summary=?,description=?,icon=?,lucide_icon=?,icon_color=?,badge=?,price_from=?,category=?,features=?,highlights=?,position=?,active=?,show_on_home=?,home_position=?,home_card_wide=?,home_card_dark=?,home_bg_css=?,demo_screenshot_url=?,tab_label=?,updated_at=NOW() WHERE id=?",
                        [$name,$slug,$tagline,$summary,$desc,$icon,$lucide_icon,$icon_color,$badge?:null,$price?:null,$category?:null,$features,$highlights,$position,$active,$show_on_home,$home_position,$home_card_wide,$home_card_dark,$home_bg_css?:null,$demo_ss_url?:null,$tab_label?:null,$id]);
                    $success = 'Product updated.';
                } else {
                    execute("INSERT INTO products (name,slug,tagline,summary,description,icon,lucide_icon,icon_color,badge,price_from,category,features,highlights,position,active,show_on_home,home_position,home_card_wide,home_card_dark,home_bg_css,demo_screenshot_url,tab_label,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$name,$slug,$tagline,$summary,$desc,$icon,$lucide_icon,$icon_color,$badge?:null,$price?:null,$category?:null,$features,$highlights,$position,$active,$show_on_home,$home_position,$home_card_wide,$home_card_dark,$home_bg_css?:null,$demo_ss_url?:null,$tab_label?:null]);
                    $success = 'Product created.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: ' . $e->getMessage(); }
        }
    }
}

$products = [];
try { $products = query("SELECT id,name,slug,tagline,icon,badge,price_from,category,active,position FROM products ORDER BY position,id"); } catch(\Throwable $e) { $error = 'products table not found. Run database.sql first.'; }

$editing = null;
if (!empty($_GET['edit'])) {
    try { $editing = queryOne("SELECT * FROM products WHERE id=?", [(int)$_GET['edit']]); }
    catch(\Throwable $e) {}
    if ($editing) {
        foreach (['features','highlights','modules'] as $f) {
            if (!empty($editing[$f])) $editing[$f.'_text'] = implode("\n", json_decode($editing[$f],true) ?? []);
        }
    }
}
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">

<!-- ── List ───────────────────────────────────────── -->
<div>
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat"> Products (<?=count($products)?>)</h2>
    <a href="?new=1" class="btn btn-primary btn-sm">+ Add Product</a>
  </div>

  <div class="st-card ov-hidden">
    <table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">
      <thead><tr style="border-bottom:2px solid var(--border);background:var(--muted);">
        <?php foreach(['Product','Category','Price','Active',''] as $h):?>
        <th style="padding:0.625rem 1rem;text-align:left;font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);"><?=$h?></th>
        <?php endforeach;?>
      </tr></thead>
      <tbody>
        <?php if(empty($products)):?>
        <tr><td colspan="5" class="p-empty">No products yet. Click "+ Add Product" to get started.</td></tr>
        <?php else: foreach($products as $p): $active=(bool)$p['active']; ?>
        <tr style="border-bottom:1px solid var(--border);transition:background 0.12s;" onmouseover="this.style.background='var(--muted)'" onmouseout="this.style.background='transparent'">
          <td class="p-row">
            <div style="display:flex;align-items:center;gap:0.625rem;">
              <span style="font-size:1.25rem;"><?=e($p['icon']??'')?></span>
              <div>
                <div class="fw-strong"><?=e($p['name'])?></div>
                <div class="fs-xs-mt"><?=e(truncate($p['tagline']??'',40))?></div>
              </div>
              <?php if(!empty($p['badge'])):?><span style="font-size:0.6rem;padding:0.1rem 0.35rem;border-radius:9999px;background:#dbeafe;color:var(--primary-dark);font-weight:600;"><?=e($p['badge'])?></span><?php endif;?>
            </div>
          </td>
          <td style="padding:0.75rem 1rem;color:var(--muted-foreground);"><?=e($p['category']??'—')?></td>
          <td style="padding:0.75rem 1rem;font-weight:600;color:var(--foreground);"><?=e($p['price_from']??'Custom')?></td>
          <td style="padding:0.75rem 1rem;text-align:center;">
            <form method="POST" class="inline">
              <?=csrfField()?><input type="hidden" name="action" value="update">
              <input type="hidden" name="id" value="<?=$p['id']?>">
              <input type="hidden" name="name" value="<?=e($p['name'])?>">
              <input type="hidden" name="active" value="<?=$active?0:1?>">
              <button type="submit" style="background:none;border:none;cursor:pointer;font-size:1rem;" title="Toggle active"><?=$active?'':'⬜'?></button>
            </form>
          </td>
          <td class="p-row">
            <div style="display:flex;gap:0.375rem;">
              <a href="?edit=<?=$p['id']?>" class="btn btn-ghost btn-sm">Edit</a>
              <form method="POST" class="inline" onsubmit="return confirm('Delete this product?')">
                <?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$p['id']?>">
                <button type="submit" class="btn btn-sm" style="background:var(--danger-soft);color:var(--danger-fg);border:none;"></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach;endif;?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Form panel ──────────────────────────────────── -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?= $editing ? ' Edit Product' : ' New Product' ?></h3>

    <form method="POST">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <!-- Tab nav -->
      <div class="af-tab-nav">
        <button type="button" class="af-tab-btn active" data-tab="basic">Basic</button>
        <button type="button" class="af-tab-btn" data-tab="content">Content</button>
        <button type="button" class="af-tab-btn" data-tab="homepage">Homepage</button>
      </div>

      <!-- Tab: Basic -->
      <div class="af-tab-pane active" data-tab-pane="basic">
        <div style="display:grid;grid-template-columns:54px 1fr;gap:0.5rem;">
          <div>
            <label class="form-label fs-2xs2">Emoji</label>
            <input type="text" name="icon" class="form-input" style="font-size:1.125rem;text-align:center;padding:0.5rem;" value="<?=e($editing['icon']??'')?>">
          </div>
          <div>
            <label class="form-label fs-2xs2">Product Name <span class="text-danger-token">*</span></label>
            <input type="text" name="name" required class="form-input fs-sm2" value="<?=e($editing['name']??'')?>">
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
          <div>
            <label class="form-label fs-2xs2">Lucide Icon <a href="https://lucide.dev/icons" target="_blank" style="color:var(--primary);font-size:0.6rem;">browse ↗</a></label>
            <input type="text" name="lucide_icon" class="form-input fs-sm2" value="<?=e($editing['lucide_icon']??'')?>" placeholder="monitor">
          </div>
          <div>
            <label class="form-label fs-2xs2">Icon Color</label>
            <select name="icon_color" class="form-input fs-sm2">
              <?php foreach(['blue','teal','purple','green','amber','rose','indigo','cyan'] as $col): ?>
              <option value="<?=$col?>" <?=($editing['icon_color']??'blue')===$col?'selected':''?>><?=ucfirst($col)?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
          <div>
            <label class="form-label fs-2xs2">Slug</label>
            <input type="text" name="slug" class="form-input fs-sm2" value="<?=e($editing['slug']??'')?>" placeholder="auto">
          </div>
          <div>
            <label class="form-label fs-2xs2">Badge</label>
            <input type="text" name="badge" class="form-input fs-sm2" value="<?=e($editing['badge']??'')?>" placeholder="e.g. Popular">
          </div>
        </div>
        <div>
          <label class="form-label fs-2xs2">Tagline</label>
          <input type="text" name="tagline" class="form-input fs-sm2" value="<?=e($editing['tagline']??'')?>">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
          <div>
            <label class="form-label fs-2xs2">Price From</label>
            <input type="text" name="price_from" class="form-input fs-sm2" value="<?=e($editing['price_from']??'')?>" placeholder="NPR 4,999/mo">
          </div>
          <div>
            <label class="form-label fs-2xs2">Category</label>
            <select name="category" class="form-input fs-sm2">
              <option value="">Select</option>
              <?php foreach(['Banking Software','Mobile App','Document Management','HR Software','Website','Support'] as $c):?>
              <option value="<?=$c?>" <?=($editing['category']??'')===$c?'selected':''?>><?=$c?></option>
              <?php endforeach;?>
            </select>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
          <div>
            <label class="form-label fs-2xs2">Position</label>
            <input type="number" name="position" class="form-input fs-sm2" value="<?=e($editing['position']??0)?>">
          </div>
          <div style="display:flex;align-items:flex-end;padding-bottom:0.25rem;">
            <label class="row-check">
              <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>> Active / Visible
            </label>
          </div>
        </div>
      </div>

      <!-- Tab: Content -->
      <div class="af-tab-pane" data-tab-pane="content">
        <div>
          <label class="form-label fs-2xs2">Short Summary</label>
          <textarea name="summary" class="form-input fs-sm-r" rows="2"><?=e($editing['summary']??'')?></textarea>
        </div>
        <div>
          <label class="form-label fs-2xs2">Full Description <span style="color:var(--muted-foreground);font-weight:400;">(HTML ok)</span></label>
          <textarea name="description" class="form-input fs-sm-r" rows="5"><?=e($editing['description']??'')?></textarea>
        </div>
        <div>
          <label class="form-label fs-2xs2">Features <span style="color:var(--muted-foreground);font-weight:400;">(one per line)</span></label>
          <textarea name="features" class="form-input fs-sm-r" rows="4" placeholder="NRB-compliant reports&#10;Mobile Banking&#10;Multi-branch support"><?=e($editing['features_text']??'')?></textarea>
        </div>
        <div>
          <label class="form-label fs-2xs2">Highlights <span style="color:var(--muted-foreground);font-weight:400;">(one per line)</span></label>
          <textarea name="highlights" class="form-input fs-sm-r" rows="2" placeholder="120+ cooperatives&#10;24/7 support"><?=e($editing['highlights_text']??'')?></textarea>
        </div>
      </div>

      <!-- Tab: Homepage -->
      <div class="af-tab-pane" data-tab-pane="homepage">
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
          <label class="row-check">
            <input type="checkbox" name="show_on_home" value="1" <?=($editing['show_on_home']??1)?'checked':''?>> Show on homepage
          </label>
          <label class="row-check">
            <input type="checkbox" name="home_card_wide" value="1" <?=($editing['home_card_wide']??0)?'checked':''?>> Wide card (2-col)
          </label>
          <label class="row-check">
            <input type="checkbox" name="home_card_dark" value="1" <?=($editing['home_card_dark']??0)?'checked':''?>> Dark card
          </label>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
          <div>
            <label class="form-label fs-2xs2">Home Position</label>
            <input type="number" name="home_position" class="form-input fs-sm2" value="<?=e($editing['home_position']??0)?>">
          </div>
          <div>
            <label class="form-label fs-2xs2">Tab Label <span style="color:var(--muted-foreground);font-weight:400;">(default: name)</span></label>
            <input type="text" name="tab_label" class="form-input fs-sm2" value="<?=e($editing['tab_label']??'')?>" placeholder="e.g. Core Banking">
          </div>
        </div>
        <div>
          <label class="form-label fs-2xs2">Card Background CSS <span style="color:var(--muted-foreground);font-weight:400;">(optional)</span></label>
          <input type="text" name="home_bg_css" class="form-input fs-sm2" value="<?=e($editing['home_bg_css']??'')?>" placeholder="background:linear-gradient(135deg,#0f172a,#1e3a8a)">
        </div>
        <div>
          <label class="form-label fs-2xs2">
            Demo Screenshot URL
            <span style="color:var(--muted-foreground);font-weight:400;"> — "See it in action" tabs</span>
          </label>
          <input type="url" name="demo_screenshot_url" id="dss_url_<?=($editing['id']??'new')?>" class="form-input fs-sm2"
                 value="<?=e($editing['demo_screenshot_url']??'')?>" placeholder="https://… or upload below">
          <div style="margin-top:0.375rem;display:flex;align-items:center;gap:0.625rem;flex-wrap:wrap;">
            <label style="display:inline-flex;align-items:center;gap:0.375rem;padding:0.3rem 0.75rem;border-radius:0.4rem;border:1px solid var(--border);background:var(--muted);cursor:pointer;font-size:0.75rem;font-weight:600;color:var(--muted-foreground);">
              <i data-lucide="upload" class="ic-13"></i> Upload image
              <input type="file" id="dss_file_<?=($editing['id']??'new')?>" accept="image/*" style="display:none" onchange="stAdminUpload(this,'dss_url_<?=($editing['id']??'new')?>','dss_prev_<?=($editing['id']??'new')?>')">
            </label>
            <span id="dss_prev_<?=($editing['id']??'new')?>" class="fs-xs-mt"></span>
          </div>
          <?php if(!empty($editing['demo_screenshot_url'])): ?>
          <div style="margin-top:0.5rem;border-radius:0.5rem;overflow:hidden;border:1px solid var(--border);max-height:8rem;">
            <img src="<?=e($editing['demo_screenshot_url'])?>" alt="Preview" style="width:100%;object-fit:cover;object-position:top;max-height:8rem;">
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Footer: always visible -->
      <div class="af-form-footer">
        <button type="submit" class="btn btn-primary w-100"><?=$editing?'Update Product':'Create Product'?></button>
        <?php if($editing):?><a href="?" class="btn btn-ghost w-100-c">Cancel</a><?php endif;?>
      </div>
    </form>
  </div>
</div>
</div>

<script>
function stAdminUpload(input, urlFieldId, prevId) {
  var file = input.files[0];
  if (!file) return;
  var fd = new FormData();
  fd.append('file', file);
  var prev = document.getElementById(prevId);
  if (prev) prev.textContent = 'Uploading…';
  fetch('<?= url('api/admin-upload.php') ?>', { method:'POST', body:fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
      if (data.ok) {
        document.getElementById(urlFieldId).value = data.url;
        if (prev) prev.textContent = 'Uploaded: ' + data.name;
      } else {
        if (prev) prev.textContent = 'Error: ' + (data.error || 'Upload failed');
      }
    })
    .catch(function(){ if(prev) prev.textContent = 'Upload failed.'; });
}
</script>
<?php require_once '../includes/admin-layout-close.php'; ?>
