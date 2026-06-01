<?php
$pageTitle = 'Team Members';
require_once '../includes/admin-layout.php';

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        try { execute("DELETE FROM team_members WHERE id=?", [$id]); $success = 'Team member deleted.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif (in_array($action,['create','update'])) {
        $id           = (int)($_POST['id'] ?? 0);
        $name         = trim($_POST['name'] ?? '');
        $role         = trim($_POST['role'] ?? '');
        $bio          = trim($_POST['bio'] ?? '');
        $photo_url    = trim($_POST['photo_url'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $linkedin_url = trim($_POST['linkedin_url'] ?? '');
        $is_lead      = isset($_POST['is_leadership']) ? 1 : 0;
        $active       = isset($_POST['active']) ? 1 : 0;
        $position     = (int)($_POST['position'] ?? 0);

        if (!$name) { $error = 'Name is required.'; }
        else {
            try {
                if ($id) {
                    execute("UPDATE team_members SET name=?,role=?,bio=?,photo_url=?,email=?,linkedin_url=?,is_leadership=?,active=?,position=?,updated_at=NOW() WHERE id=?",
                        [$name,$role,$bio,$photo_url?:null,$email?:null,$linkedin_url?:null,$is_lead,$active,$position,$id]);
                    $success = 'Team member updated.';
                } else {
                    execute("INSERT INTO team_members (name,role,bio,photo_url,email,linkedin_url,is_leadership,active,position,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$name,$role,$bio,$photo_url?:null,$email?:null,$linkedin_url?:null,$is_lead,$active,$position]);
                    $success = 'Team member added.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: '.$e->getMessage(); }
        }
    }
}

$team = [];
try { $team = query("SELECT id,name,role,photo_url,is_leadership,active,position FROM team_members ORDER BY is_leadership DESC,position,name"); }
catch(\Throwable $e) { $error = 'team_members table not found. Run database.sql.'; }

$editing = null;
if (!empty($_GET['edit'])) {
    try { $editing = queryOne("SELECT * FROM team_members WHERE id=?", [(int)$_GET['edit']]); }
    catch(\Throwable $e) {}
}
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<div class="af-split">
<div>
  <div class="row-between-mb">
    <h2 class="h-eyebrow-flat"> Team Members (<?=count($team)?>)</h2>
    <a href="?new=1" class="btn btn-primary btn-sm">+ Add Member</a>
  </div>

  <!-- Leadership -->
  <?php $leads = array_filter($team, fn($m)=>$m['is_leadership']); if(!empty($leads)):?>
  <div style="margin-bottom:0.75rem;font-size:0.6875rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted-foreground);">Leadership</div>
  <?php endif;?>

  <div style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1.25rem;">
    <?php foreach($team as $m): ?>
    <div class="st-card" style="padding:0.875rem 1.25rem;display:flex;align-items:center;gap:1rem;<?=!$m['active']?'opacity:0.55;':''?>">
      <!-- Avatar -->
      <div style="width:2.5rem;height:2.5rem;border-radius:9999px;overflow:hidden;flex-shrink:0;background:var(--muted);display:grid;place-items:center;">
        <?php if(!empty($m['photo_url'])):?>
        <img src="<?=e($m['photo_url'])?>" loading="lazy" alt="<?=e($m['name'])?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else:?>
        <span style="font-weight:700;color:var(--muted-foreground);"><?=strtoupper(substr($m['name'],0,1))?></span>
        <?php endif;?>
      </div>
      <div class="flex-1-min">
        <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
          <span class="fw-strong"><?=e($m['name'])?></span>
          <?php if($m['is_leadership']):?><span style="font-size:0.625rem;padding:0.1rem 0.35rem;border-radius:9999px;background:#fef9c3;color:#b45309;font-weight:700;">LEADERSHIP</span><?php endif;?>
          <?php if(!$m['active']):?><span style="font-size:0.625rem;color:var(--muted-foreground);">inactive</span><?php endif;?>
        </div>
        <div class="fs-sm-mt"><?=e($m['role']??'—')?></div>
      </div>
      <div style="display:flex;gap:0.375rem;flex-shrink:0;">
        <a href="?edit=<?=$m['id']?>" class="btn btn-ghost btn-sm">Edit</a>
        <form method="POST" class="inline" onsubmit="return confirm('Delete this team member?')">
          <?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$m['id']?>">
          <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;"></button>
        </form>
      </div>
    </div>
    <?php endforeach;?>
    <?php if(empty($team)):?><div style="border:2px dashed var(--border);border-radius:1rem;padding:3rem;text-align:center;color:var(--muted-foreground);">No team members yet.</div><?php endif;?>
  </div>
</div>

<!-- Form -->
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?=$editing?' Edit Member':' Add Member'?></h3>
    <form method="POST" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <div>
        <label class="form-label fs-2xs2">Full Name <span class="text-danger-token">*</span></label>
        <input type="text" name="name" required class="form-input fs-sm2" value="<?=e($editing['name']??'')?>">
      </div>
      <div>
        <label class="form-label fs-2xs2">Role / Title</label>
        <input type="text" name="role" class="form-input fs-sm2" value="<?=e($editing['role']??'')?>" placeholder="e.g. CTO">
      </div>
      <div>
        <label class="form-label fs-2xs2">Bio</label>
        <textarea name="bio" class="form-input fs-sm-r" rows="3"><?=e($editing['bio']??'')?></textarea>
      </div>
      <?php
        $imgField = 'photo_url'; $imgValue = $editing['photo_url'] ?? '';
        $imgLabel = 'Photo';
        require __DIR__ . '/../includes/admin-img-upload.php';
      ?>
      <div>
        <label class="form-label fs-2xs2">Email</label>
        <input type="email" name="email" class="form-input fs-sm2" value="<?=e($editing['email']??'')?>">
      </div>
      <div>
        <label class="form-label fs-2xs2">LinkedIn URL</label>
        <input type="url" name="linkedin_url" class="form-input fs-sm2" value="<?=e($editing['linkedin_url']??'')?>" placeholder="https://linkedin.com/in/...">
      </div>
      <div style="display:grid;grid-template-columns:80px 1fr;gap:0.5rem;align-items:end;">
        <div>
          <label class="form-label fs-2xs2">Position</label>
          <input type="number" name="position" class="form-input fs-sm2" value="<?=e($editing['position']??0)?>">
        </div>
        <div style="display:flex;flex-direction:column;gap:0.25rem;padding-bottom:0.5rem;">
          <label class="row-check">
            <input type="checkbox" name="is_leadership" value="1" <?=($editing['is_leadership']??0)?'checked':''?>> Leadership team
          </label>
          <label class="row-check">
            <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>> Active / Visible
          </label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100"><?=$editing?'Update Member':'Add Member'?></button>
      <?php if($editing):?><a href="?" class="btn btn-ghost w-100-c">Cancel</a><?php endif;?>
    </form>
  </div>
</div>
</div>

<?php require_once '../includes/admin-layout-close.php'; ?>
