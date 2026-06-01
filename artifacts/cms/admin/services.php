<?php
$pageTitle = 'Services';
require_once '../includes/admin-layout.php';

$success = $error = '';
$uploadDir = dirname(__DIR__) . '/uploads/services/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        try { execute("DELETE FROM services WHERE id=?", [(int)$_POST['id']]); $success = 'Service deleted.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif (in_array($action, ['create','update'])) {
        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim($_POST['title']       ?? '');
        $slug        = trim($_POST['slug']        ?? '') ?: makeSlug($title);
        $tagline     = trim($_POST['tagline']     ?? '');
        $summary     = trim($_POST['summary']     ?? '');
        $badge       = trim($_POST['badge']       ?? '');
        $price_from  = trim($_POST['price_from']  ?? '');
        $lucide_icon = trim($_POST['lucide_icon'] ?? '');
        $icon_color  = trim($_POST['icon_color']  ?? 'blue');
        $position    = (int)($_POST['position']   ?? 0);
        $active      = isset($_POST['active']) ? 1 : 0;
        $features    = trim($_POST['features']    ?? '');
        $highlights  = json_encode(array_values(array_filter(
            array_map('trim', explode("\n", $_POST['highlights'] ?? ''))
        )));

        // Screenshot upload
        $screenshotUrl = trim($_POST['screenshot_url'] ?? '');
        if (!empty($_FILES['screenshot_file']['tmp_name'])) {
            $ext  = strtolower(pathinfo($_FILES['screenshot_file']['name'], PATHINFO_EXTENSION));
            $safe = in_array($ext, ['png','jpg','jpeg','gif','webp']);
            if ($safe && $_FILES['screenshot_file']['size'] < 5*1024*1024) {
                $fname = 'svc_' . ($id ?: time()) . '_' . time() . '.' . $ext;
                $dest  = $uploadDir . $fname;
                if (move_uploaded_file($_FILES['screenshot_file']['tmp_name'], $dest)) {
                    $screenshotUrl = SITE_URL . '/uploads/services/' . $fname;
                }
            } else {
                $error = 'Image must be PNG/JPG/WebP under 5 MB.';
            }
        }

        if (!$title) { $error = 'Title is required.'; }
        elseif (!$error) {
            try {
                if ($id) {
                    execute(
                        "UPDATE services SET title=?,slug=?,tagline=?,summary=?,badge=?,price_from=?,lucide_icon=?,icon_color=?,features=?,highlights=?,screenshot_url=?,position=?,active=?,updated_at=NOW() WHERE id=?",
                        [$title,$slug,$tagline,$summary,$badge,$price_from?:null,$lucide_icon,$icon_color,$features,$highlights,$screenshotUrl?:null,$position,$active,$id]
                    );
                    $success = 'Service updated.';
                } else {
                    execute(
                        "INSERT INTO services (title,slug,tagline,summary,badge,price_from,lucide_icon,icon_color,features,highlights,screenshot_url,position,active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$title,$slug,$tagline,$summary,$badge,$price_from?:null,$lucide_icon,$icon_color,$features,$highlights,$screenshotUrl?:null,$position,$active]
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

$COLORS = ['blue','green','purple','amber','teal','rose','orange','indigo','gray'];
$BADGES = ['','Flagship','Popular','Essential','New','Add-on','Included','Audit'];

// Curated Lucide icon list (9 cols × 8 rows = 72)
$ICONS = [
    'monitor','smartphone','tablet','laptop','cpu','server','database','hard-drive','wifi',
    'cloud','network','globe','map-pin','building','building-2','home','briefcase','package',
    'file-text','file','file-check','folder','folder-open','clipboard','clipboard-check','inbox','mail',
    'code','terminal','git-branch','layers','box','workflow','settings','settings-2','sliders',
    'shield','shield-check','lock','key','fingerprint','eye','scan','qr-code','printer',
    'phone','phone-call','headphones','message-square','message-circle','video','bell','share-2','link',
    'bar-chart','bar-chart-2','pie-chart','trending-up','trending-down','activity','zap','star','award',
    'users','user','user-check','contact','credit-card','wallet','receipt','calculator','dollar-sign',
    'check-circle','check','alert-triangle','info','help-circle','refresh-cw','download','upload','image',
];
$ICONS_JSON = json_encode($ICONS);
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">
<!-- ── List ── -->
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
        <div class="fs-2xs-mt">slug: <?=e($s['slug'])?> · pos: <?=$s['position']?> · <?=e($s['icon_color']??'')?></div>
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

<!-- ── Form ── -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?=$editing?'Edit Service':'New Service'?></h3>

    <form method="POST" enctype="multipart/form-data" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <!-- ── Icon Picker + Title ── -->
      <div x-data="svcIconPicker(<?= json_encode($editing['lucide_icon'] ?? 'layers') ?>, <?= $ICONS_JSON ?>)"
           style="display:grid;grid-template-columns:auto 1fr;gap:0.75rem;align-items:start;">

        <!-- Icon preview button -->
        <div>
          <label class="form-label" style="visibility:hidden;">Icon</label>
          <button type="button" @click="open=!open"
                  style="width:3.25rem;height:2.5rem;border-radius:0.625rem;border:1.5px solid var(--border);background:var(--card);display:flex;align-items:center;justify-content:center;gap:0.25rem;cursor:pointer;flex-direction:column;"
                  title="Pick icon">
            <span x-ref="previewWrap" style="display:flex;align-items:center;justify-content:center;width:20px;height:20px;">
              <i :data-lucide="selected" style="width:18px;height:18px;color:var(--primary);"></i>
            </span>
            <span style="font-size:0.55rem;color:var(--muted-foreground);line-height:1;">pick</span>
          </button>
        </div>

        <!-- Title input -->
        <div>
          <label class="form-label">Title <span class="text-danger-token">*</span></label>
          <input type="text" name="title" required class="form-input" value="<?=e($editing['title']??'')?>">
        </div>

        <!-- Hidden input for lucide_icon -->
        <input type="hidden" name="lucide_icon" x-model="selected">

        <!-- Icon picker dropdown (spans 2 cols) -->
        <div x-show="open" x-transition style="grid-column:1/-1;border:1.5px solid var(--border);border-radius:0.875rem;background:var(--card);padding:0.875rem;box-shadow:0 8px 24px rgba(0,0,0,0.12);">
          <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
            <div style="position:relative;flex:1;">
              <i data-lucide="search" style="position:absolute;left:0.625rem;top:50%;transform:translateY(-50%);width:13px;height:13px;color:var(--muted-foreground);pointer-events:none;"></i>
              <input type="text" x-model="search" placeholder="Search icons…"
                     style="width:100%;padding:0.375rem 0.625rem 0.375rem 2rem;border-radius:0.5rem;border:1px solid var(--border);font-size:0.8125rem;background:var(--background);"
                     @input="filterIcons()" autocomplete="off">
            </div>
            <span style="font-size:0.75rem;color:var(--muted-foreground);" x-text="filtered.length + ' icons'"></span>
            <button type="button" @click="open=false"
                    style="padding:0.25rem 0.5rem;border-radius:0.375rem;border:1px solid var(--border);background:none;font-size:0.75rem;cursor:pointer;color:var(--muted-foreground);">Close</button>
          </div>
          <!-- Current selection -->
          <div x-show="selected" style="margin-bottom:0.5rem;font-size:0.75rem;color:var(--muted-foreground);">
            Selected: <strong x-text="selected" style="color:var(--primary);"></strong>
          </div>
          <!-- Icon grid -->
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(3.5rem,1fr));gap:0.25rem;max-height:240px;overflow-y:auto;">
            <template x-for="ico in filtered" :key="ico">
              <button type="button"
                      @click="select(ico)"
                      :style="selected===ico ? 'background:var(--primary-light);border-color:var(--primary);color:var(--primary);' : 'background:transparent;border-color:transparent;color:var(--foreground);'"
                      style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0.2rem;padding:0.4rem 0.2rem;border-radius:0.5rem;border:1.5px solid;cursor:pointer;transition:all 0.1s;">
                <i :data-lucide="ico" style="width:16px;height:16px;flex-shrink:0;"></i>
                <span style="font-size:0.5rem;line-height:1.2;text-align:center;word-break:break-all;max-width:3rem;overflow:hidden;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;" x-text="ico"></span>
              </button>
            </template>
            <div x-show="filtered.length===0" style="grid-column:1/-1;text-align:center;padding:1rem;color:var(--muted-foreground);font-size:0.8125rem;">No icons found for "<span x-text="search"></span>"</div>
          </div>
          <!-- Manual entry fallback -->
          <div style="margin-top:0.625rem;padding-top:0.625rem;border-top:1px solid var(--border);display:flex;align-items:center;gap:0.5rem;">
            <span style="font-size:0.75rem;color:var(--muted-foreground);white-space:nowrap;">Or type name:</span>
            <input type="text" :value="selected" @input="selectManual($event.target.value)"
                   style="flex:1;padding:0.3rem 0.5rem;border-radius:0.375rem;border:1px solid var(--border);font-size:0.8125rem;"
                   placeholder="e.g. building-2">
            <a href="https://lucide.dev/icons/" target="_blank"
               style="font-size:0.75rem;color:var(--primary);white-space:nowrap;">Browse all →</a>
          </div>
        </div>
      </div>

      <!-- Slug + Tagline -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
        <div>
          <label class="form-label">Slug <span class="caption-meta">(URL)</span></label>
          <input type="text" name="slug" class="form-input" value="<?=e($editing['slug']??'')?>" placeholder="auto-generated">
        </div>
        <div>
          <label class="form-label">Tagline</label>
          <input type="text" name="tagline" class="form-input" value="<?=e($editing['tagline']??'')?>" placeholder="Short subtitle under the title">
        </div>
      </div>

      <!-- Summary -->
      <div>
        <label class="form-label">Summary / Description</label>
        <textarea name="summary" class="form-input" rows="3" placeholder="Describe what this service does…"><?=e($editing['summary']??'')?></textarea>
      </div>

      <!-- Badge + Price -->
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
          <p class="caption-meta">Blank = "Contact us". 0 = "Included".</p>
        </div>
      </div>

      <!-- Highlights -->
      <div>
        <label class="form-label">Highlights <span class="caption-meta">(one per line — ✓ bullet points on card)</span></label>
        <textarea name="highlights" class="form-input" rows="4" placeholder="Member & KYC&#10;Savings & FD&#10;Loan Lifecycle&#10;NRB Reports"><?php
          $hArr = json_decode($editing['highlights']??'[]', true);
          echo e(is_array($hArr) ? implode("\n", $hArr) : ($editing['highlights']??''));
        ?></textarea>
      </div>

      <!-- ── Feature Chips (Alpine tag input) ── -->
      <div x-data="chipInput(<?= json_encode($editing['features'] ?? '') ?>)">
        <label class="form-label">Feature Chips <span class="caption-meta">(pill badges on public page)</span></label>
        <div @click="$refs.chipField.focus()"
             style="min-height:2.5rem;padding:0.3rem 0.5rem;border:1px solid var(--border);border-radius:0.5rem;background:var(--background);display:flex;flex-wrap:wrap;gap:0.35rem;align-items:center;cursor:text;">
          <template x-for="(chip, i) in chips" :key="i">
            <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.2rem 0.5rem;border-radius:9999px;background:var(--primary-light);color:var(--primary);font-size:0.75rem;font-weight:600;">
              <span x-text="chip"></span>
              <button type="button" @click.stop="remove(i)"
                      style="display:flex;align-items:center;justify-content:center;width:14px;height:14px;border-radius:50%;border:none;background:transparent;color:var(--primary);cursor:pointer;padding:0;font-size:0.875rem;line-height:1;">×</button>
            </span>
          </template>
          <input type="text" x-ref="chipField"
                 @keydown.enter.prevent="tryAdd($refs.chipField)"
                 @keydown.188.prevent="tryAdd($refs.chipField)"
                 @keydown.backspace="chips.length&&!$refs.chipField.value&&remove(chips.length-1)"
                 placeholder="<?= empty($editing['features']) ? 'Type a feature and press Enter or comma…' : '' ?>"
                 style="border:none;outline:none;background:transparent;font-size:0.8125rem;min-width:180px;flex:1;padding:0.1rem;">
        </div>
        <input type="hidden" name="features" x-model="joined">
        <p class="caption-meta">Press <kbd style="padding:0.1rem 0.3rem;border-radius:0.25rem;border:1px solid var(--border);font-size:0.7rem;">Enter</kbd> or <kbd style="padding:0.1rem 0.3rem;border-radius:0.25rem;border:1px solid var(--border);font-size:0.7rem;">,</kbd> to add · click × to remove</p>
      </div>

      <!-- Icon Color + Position -->
      <div style="display:grid;grid-template-columns:1fr 80px;gap:0.5rem;align-items:end;">
        <div>
          <label class="form-label">Icon Color Theme</label>
          <div style="display:flex;gap:0.375rem;flex-wrap:wrap;margin-top:0.25rem;">
            <?php
            $colorDots = ['blue'=>'#3b82f6','green'=>'#22c55e','purple'=>'#a855f7','amber'=>'#f59e0b','teal'=>'#14b8a6','rose'=>'#f43f5e','orange'=>'#f97316','indigo'=>'#6366f1','gray'=>'#9ca3af'];
            foreach($COLORS as $c):
              $active = ($editing['icon_color']??'blue')===$c;
            ?>
            <label style="cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:0.2rem;">
              <input type="radio" name="icon_color" value="<?=$c?>" <?=$active?'checked':''?> style="position:absolute;opacity:0;width:0;height:0;">
              <span style="width:1.5rem;height:1.5rem;border-radius:50%;background:<?=$colorDots[$c]?>;display:grid;place-items:center;border:2px solid <?=$active?'#0f172a':'transparent'?>;transition:border 0.15s;"
                    onclick="this.parentNode.querySelector('input').checked=true;document.querySelectorAll('[name=icon_color]').forEach(r=>{r.parentNode.querySelector('span').style.borderColor=r.checked?'#0f172a':'transparent'})">
                <?php if($active):?><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3.5"><polyline points="20 6 9 17 4 12"/></svg><?php endif;?>
              </span>
              <span style="font-size:0.55rem;color:var(--muted-foreground);"><?=ucfirst($c)?></span>
            </label>
            <?php endforeach;?>
          </div>
        </div>
        <div>
          <label class="form-label">Position</label>
          <input type="number" name="position" class="form-input" value="<?=e($editing['position']??0)?>">
        </div>
      </div>

      <!-- ── Screenshot / Image Upload ── -->
      <div x-data="imgUpload(<?= json_encode($editing['screenshot_url'] ?? '') ?>)">
        <label class="form-label">Screenshot / Product Image <span class="caption-meta">(shown on public Services page)</span></label>

        <!-- Drop zone -->
        <div @dragover.prevent="dragover=true" @dragleave="dragover=false"
             @drop.prevent="onDrop($event)"
             :style="dragover ? 'border-color:var(--primary);background:var(--primary-light);' : ''"
             style="border:2px dashed var(--border);border-radius:0.75rem;padding:1rem;text-align:center;transition:all 0.15s;cursor:pointer;"
             @click="$refs.fileInput.click()">
          <template x-if="!preview">
            <div>
              <i data-lucide="image" style="width:28px;height:28px;color:var(--muted-foreground);margin-bottom:0.5rem;"></i>
              <p style="font-size:0.8125rem;color:var(--muted-foreground);margin:0;">Click or drag &amp; drop a PNG/JPG/WebP image</p>
              <p class="caption-meta">Max 5 MB · 1200×630 px recommended</p>
            </div>
          </template>
          <template x-if="preview">
            <div>
              <img :src="preview" style="max-height:160px;max-width:100%;border-radius:0.5rem;object-fit:contain;margin-bottom:0.5rem;">
              <p style="font-size:0.75rem;color:var(--muted-foreground);margin:0;">Click to replace</p>
            </div>
          </template>
        </div>

        <input type="file" name="screenshot_file" accept="image/png,image/jpeg,image/gif,image/webp"
               x-ref="fileInput" class="hidden" @change="onFile($event)">

        <!-- URL fallback -->
        <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.5rem;">
          <span style="font-size:0.75rem;color:var(--muted-foreground);white-space:nowrap;">Or URL:</span>
          <input type="text" name="screenshot_url" x-model="urlField"
                 @input="if(urlField.trim()) preview=urlField.trim()"
                 class="form-input" style="flex:1;"
                 placeholder="https://…"
                 value="<?=e($editing['screenshot_url']??'')?>">
          <button x-show="preview" type="button" @click="preview='';urlField=''"
                  style="padding:0.25rem 0.5rem;border-radius:0.375rem;border:1px solid var(--border);background:none;font-size:0.75rem;cursor:pointer;color:var(--danger-fg);white-space:nowrap;">Remove</button>
        </div>
      </div>

      <!-- Active -->
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

<script>
// ── Chip input ────────────────────────────────────────────────────
function chipInput(raw) {
  return {
    chips: raw ? raw.split(',').map(s => s.trim()).filter(Boolean) : [],
    get joined() { return this.chips.join(','); },
    tryAdd(inp) {
      const v = inp.value.replace(/,+$/, '').trim();
      if (v && !this.chips.includes(v)) this.chips.push(v);
      inp.value = '';
    },
    remove(i) { this.chips.splice(i, 1); },
  };
}

// ── Icon picker ───────────────────────────────────────────────────
function svcIconPicker(initial, allIcons) {
  return {
    selected: initial || 'layers',
    search: '',
    open: false,
    all: allIcons,
    filtered: allIcons,
    filterIcons() {
      const q = this.search.toLowerCase().trim();
      this.filtered = q ? this.all.filter(i => i.includes(q)) : this.all;
    },
    select(ico) {
      this.selected = ico;
      this.open = false;
      this.search = '';
      this.filtered = this.all;
      this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
    selectManual(val) {
      this.selected = val.trim() || 'layers';
      this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
    init() {
      this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
    },
  };
}

// ── Image upload preview ──────────────────────────────────────────
function imgUpload(existing) {
  return {
    preview: existing || '',
    urlField: existing || '',
    dragover: false,
    onFile(e) {
      const f = e.target.files[0];
      if (!f) return;
      const r = new FileReader();
      r.onload = ev => { this.preview = ev.target.result; };
      r.readAsDataURL(f);
      this.urlField = '';
    },
    onDrop(e) {
      this.dragover = false;
      const f = e.dataTransfer.files[0];
      if (!f || !f.type.startsWith('image/')) return;
      this.$refs.fileInput.files = e.dataTransfer.files;
      const r = new FileReader();
      r.onload = ev => { this.preview = ev.target.result; };
      r.readAsDataURL(f);
      this.urlField = '';
    },
  };
}

// Re-render Lucide icons when the picker opens (so grid icons render)
document.addEventListener('alpine:init', () => {
  Alpine.effect(() => {
    setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
  });
});
</script>

<?php require_once '../includes/admin-layout-close.php'; ?>
