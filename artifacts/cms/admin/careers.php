<?php
$pageTitle = 'Careers & Applications';
require_once '../includes/admin-layout.php';

$success = $error = '';
$tab = $_GET['tab'] ?? 'jobs';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_job') {
        try { execute("DELETE FROM job_listings WHERE id=?", [(int)$_POST['id']]); $success = 'Job deleted.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif ($action === 'delete_app') {
        try { execute("DELETE FROM job_applications WHERE id=?", [(int)$_POST['id']]); $success = 'Application removed.'; }
        catch(\Throwable $e) { $error = 'Delete failed.'; }
    } elseif ($action === 'update_app_status') {
        $status = $_POST['status'] ?? 'new';
        try { execute("UPDATE job_applications SET status=?,updated_at=NOW() WHERE id=?", [$status,(int)$_POST['id']]); $success = 'Status updated.'; }
        catch(\Throwable $e) { $error = 'Update failed.'; }
    } elseif (in_array($action,['create','update'])) {
        $id          = (int)($_POST['id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $slug        = trim($_POST['slug'] ?? '') ?: makeSlug($title);
        $department  = trim($_POST['department'] ?? '');
        $location    = trim($_POST['location'] ?? 'Kathmandu, Nepal');
        $type        = trim($_POST['type'] ?? 'full-time');
        $salary_range= trim($_POST['salary_range'] ?? '');
        $experience  = trim($_POST['experience'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $requirements= trim($_POST['requirements'] ?? '');
        $deadline    = $_POST['deadline'] ?: null;
        $active      = isset($_POST['active']) ? 1 : 0;
        $salary_range= trim($_POST['salary_range'] ?? '');
        $experience  = trim($_POST['experience'] ?? '');
        $short_desc  = trim($_POST['short_desc'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $requirements= trim($_POST['requirements'] ?? '');
        $deadline    = $_POST['deadline'] ?: null;
        $active      = isset($_POST['active']) ? 1 : 0;

        if (!$title) { $error = 'Job title is required.'; }
        else {
            $existSlug = queryOne("SELECT id FROM job_listings WHERE slug=? AND id!=?",[$slug,$id]);
            if ($existSlug) $slug .= '-' . time();
            try {
                if ($id) {
                    execute("UPDATE job_listings SET title=?,slug=?,department=?,location=?,type=?,salary_range=?,experience=?,short_desc=?,description=?,requirements=?,deadline=?,active=?,updated_at=NOW() WHERE id=?",
                        [$title,$slug,$department,$location,$type,$salary_range?:null,$experience?:null,$short_desc?:null,$description,$requirements,$deadline,$active,$id]);
                    $success = 'Job updated.';
                } else {
                    execute("INSERT INTO job_listings (title,slug,department,location,type,salary_range,experience,short_desc,description,requirements,deadline,active,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())",
                        [$title,$slug,$department,$location,$type,$salary_range?:null,$experience?:null,$short_desc?:null,$description,$requirements,$deadline,$active]);
                    $success = 'Job posted.';
                }
            } catch(\Throwable $e) { $error = 'Save failed: '.$e->getMessage(); }
        }
    }
}

$jobs = [];
try { $jobs = query("SELECT id,title,department,location,type,salary_range,deadline,active,created_at FROM job_listings ORDER BY created_at DESC"); }
catch(\Throwable $e) { $error = 'job_listings table not found. Run database.sql.'; }

$apps = [];
try { $apps = query("SELECT ja.*, jl.title AS job_title FROM job_applications ja LEFT JOIN job_listings jl ON jl.id=ja.job_listing_id ORDER BY ja.created_at DESC LIMIT 50"); }
catch(\Throwable $e) {}

$editing = null;
if (!empty($_GET['edit'])) {
    try { $editing = queryOne("SELECT * FROM job_listings WHERE id=?", [(int)$_GET['edit']]); $tab = 'jobs'; }
    catch(\Throwable $e) {}
}

$pending_apps = count(array_filter($apps, fn($a)=>in_array($a['status']??'new',['new','reviewing'])));
$TYPE_LABELS = ['full-time'=>'Full-time','part-time'=>'Part-time','contract'=>'Contract','internship'=>'Internship'];
?>

<?php if($success):?><div class="alert alert-success mb-1"><?=e($success)?></div><?php endif;?>
<?php if($error):?><div class="alert alert-error mb-1"><?=e($error)?></div><?php endif;?>

<!-- Tabs -->
<div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:1.25rem;">
  <a href="?tab=jobs" style="padding:0.625rem 1.25rem;font-size:0.875rem;font-weight:600;text-decoration:none;border-bottom:2px solid <?=$tab==='jobs'?'var(--primary)':'transparent'?>;color:<?=$tab==='jobs'?'var(--primary)':'var(--muted-foreground)'?>;margin-bottom:-2px;">
     Job Listings (<?=count($jobs)?>)
  </a>
  <a href="?tab=apps" style="padding:0.625rem 1.25rem;font-size:0.875rem;font-weight:600;text-decoration:none;border-bottom:2px solid <?=$tab==='apps'?'var(--primary)':'transparent'?>;color:<?=$tab==='apps'?'var(--primary)':'var(--muted-foreground)'?>;margin-bottom:-2px;">
     Applications (<?=count($apps)?>) <?php if($pending_apps>0):?><span style="margin-left:0.25rem;padding:0.1rem 0.4rem;border-radius:9999px;background:#fee2e2;color:#b91c1c;font-size:0.625rem;font-weight:700;"><?=$pending_apps?></span><?php endif;?>
  </a>
</div>

<?php if($tab === 'jobs'): ?>
<div style="display:grid;grid-template-columns:1fr <?=($editing||isset($_GET['new']))?'380px':'';?>;gap:1.25rem;align-items:start;">
<div>
  <div class="row-between-mb">
    <span style="font-size:0.875rem;color:var(--muted-foreground);"><?=count($jobs)?> position<?=count($jobs)!==1?'s':''?></span>
    <a href="?new=1&tab=jobs" class="btn btn-primary btn-sm">+ Post Job</a>
  </div>
  <div style="display:flex;flex-direction:column;gap:0.625rem;">
    <?php if(empty($jobs)):?>
    <div style="border:2px dashed var(--border);border-radius:1rem;padding:3rem;text-align:center;color:var(--muted-foreground);">No jobs posted yet!</div>
    <?php else: foreach($jobs as $j): $isActive=(bool)$j['active']; ?>
    <div class="st-card" style="padding:1rem 1.25rem;<?=!$isActive?'opacity:0.6;':''?>">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div class="flex-1">
          <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.25rem;">
            <span style="font-weight:700;color:var(--foreground);"><?=e($j['title'])?></span>
            <?php if($isActive):?><span style="font-size:0.625rem;padding:0.1rem 0.35rem;border-radius:9999px;background:#dcfce7;color:#15803d;font-weight:700;">OPEN</span><?php else:?><span style="font-size:0.625rem;padding:0.1rem 0.35rem;border-radius:9999px;background:var(--muted);color:var(--muted-foreground);font-weight:700;">CLOSED</span><?php endif;?>
          </div>
          <div class="fs-sm-mt">
            <?=e($j['department']??'All Teams')?> · <?=e($j['location'])?> · <?=$TYPE_LABELS[$j['type']]??$j['type']?>
            <?php if(!empty($j['salary_range'])):?> · <?=e($j['salary_range'])?><?php endif;?>
          </div>
          <?php if(!empty($j['deadline'])):?>
          <div style="font-size:0.75rem;color:<?=strtotime($j['deadline'])<time()?'#b91c1c':'#b45309'?>;margin-top:0.25rem;">⏳ Deadline: <?=date('M j, Y',strtotime($j['deadline']))?></div>
          <?php endif;?>
        </div>
        <div style="display:flex;gap:0.375rem;flex-shrink:0;">
          <a href="?edit=<?=$j['id']?>&tab=jobs" class="btn btn-ghost btn-sm">Edit</a>
          <form method="POST" class="inline" onsubmit="return confirm('Delete?')">
            <?=csrfField()?><input type="hidden" name="action" value="delete_job"><input type="hidden" name="id" value="<?=$j['id']?>">
            <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#b91c1c;border:none;"></button>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach;endif;?>
  </div>
</div>

<?php if($editing || isset($_GET['new'])):?>
<div class="af-panel">
  <div class="st-card p-tile">
    <h3 class="h-eyebrow-tight"><?=$editing?' Edit Job':' Post New Job'?></h3>
    <form method="POST" class="col-1-tight">
      <?=csrfField()?>
      <input type="hidden" name="action" value="<?=$editing?'update':'create'?>">
      <?php if($editing):?><input type="hidden" name="id" value="<?=$editing['id']?>"><?php endif;?>

      <div>
        <label class="form-label fs-2xs2">Job Title <span class="text-danger-token">*</span></label>
        <input type="text" name="title" required class="form-input fs-sm2" value="<?=e($editing['title']??'')?>">
      </div>
      <div>
        <label class="form-label fs-2xs2">Slug</label>
        <input type="text" name="slug" class="form-input fs-sm2" value="<?=e($editing['slug']??'')?>" placeholder="auto">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
        <div>
          <label class="form-label fs-2xs2">Department</label>
          <input type="text" name="department" class="form-input fs-sm2" value="<?=e($editing['department']??'')?>" placeholder="Engineering">
        </div>
        <div>
          <label class="form-label fs-2xs2">Location</label>
          <input type="text" name="location" class="form-input fs-sm2" value="<?=e($editing['location']??'Kathmandu, Nepal')?>">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
        <div>
          <label class="form-label fs-2xs2">Job Type</label>
          <select name="type" class="form-input fs-sm2">
            <?php foreach($TYPE_LABELS as $tv=>$tl):?>
            <option value="<?=$tv?>" <?=($editing['type']??'full-time')===$tv?'selected':''?>><?=$tl?></option>
            <?php endforeach;?>
          </select>
        </div>
        <div>
          <label class="form-label fs-2xs2">Salary Range</label>
          <input type="text" name="salary_range" class="form-input fs-sm2" value="<?=e($editing['salary_range']??'')?>" placeholder="NPR 40k–60k">
        </div>
      </div>
      <div>
        <label class="form-label fs-2xs2">Experience Required</label>
        <input type="text" name="experience" class="form-input fs-sm2" value="<?=e($editing['experience']??'')?>" placeholder="2+ years PHP">
      </div>
      <div>
        <label class="form-label fs-2xs2">Application Deadline</label>
        <input type="date" name="deadline" class="form-input fs-sm2" value="<?=e(substr($editing['deadline']??'',0,10))?>">
      </div>
      <div>
        <label class="form-label fs-2xs2">Job Description</label>
        <textarea name="description" class="form-input fs-sm-r" rows="4"><?=e($editing['description']??'')?></textarea>
      </div>
      <div>
        <label class="form-label fs-2xs2">Requirements</label>
        <textarea name="requirements" class="form-input fs-sm-r" rows="3" placeholder="- 2+ years PHP&#10;- MySQL experience"><?=e($editing['requirements']??'')?></textarea>
      </div>
      <label class="row-check">
        <input type="checkbox" name="active" value="1" <?=($editing['active']??1)?'checked':''?>> Open / Accepting Applications
      </label>
      <button type="submit" class="btn btn-primary w-100"><?=$editing?'Update Job':'Post Job'?></button>
      <a href="?tab=jobs" class="btn btn-ghost w-100-c">Cancel</a>
    </form>
  </div>
</div>
<?php endif;?>
</div>
      <div>
        <label class="form-label fs-2xs2">Short Summary <span style="color:var(--muted-foreground);font-weight:400;">(one line, shown on the cards)</span></label>
        <input type="text" name="short_desc" class="form-input fs-sm2" maxlength="300" value="<?=e($editing['short_desc']??'')?>" placeholder="Build and scale our Core Banking platform.">
      </div>
      <div>
<?php else: // Applications tab ?>
<div>
  <div style="margin-bottom:1rem;font-size:0.875rem;color:var(--muted-foreground);"><?=count($apps)?> total · <?=$pending_apps?> pending review</div>
  <div class="st-card ov-hidden">
    <table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">
      <thead><tr style="border-bottom:2px solid var(--border);background:var(--muted);">
        <?php foreach(['Applicant','Position','Contact','Status','Applied',''] as $h):?>
        <th style="padding:0.625rem 1rem;text-align:left;font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted-foreground);"><?=$h?></th>
        <?php endforeach;?>
      </tr></thead>
      <tbody>
        <?php if(empty($apps)):?>
        <tr><td colspan="6" class="p-empty">No applications yet.</td></tr>
        <?php else: foreach($apps as $a):
          $status = $a['status'] ?? 'new';
          $scls = ['new'=>['#fef9c3','#b45309'],'reviewing'=>['#dbeafe','var(--primary-dark)'],'shortlisted'=>['#e0e7ff','#4338ca'],'interview'=>['#f3e8ff','#7e22ce'],'hired'=>['#dcfce7','#15803d'],'rejected'=>['#fee2e2','#b91c1c']];
          [$sbg,$scol] = $scls[$status] ?? ['var(--muted)','var(--muted-foreground)'];
        ?>
        <tr style="border-bottom:1px solid var(--border);">
          <td style="padding:0.75rem 1rem;font-weight:600;color:var(--foreground);"><?=e($a['full_name']??$a['name']??'—')?></td>
          <td style="padding:0.75rem 1rem;font-size:0.75rem;color:var(--muted-foreground);"><?=e($a['job_title']??'—')?></td>
          <td class="p-row">
            <div class="fs-xs"><a href="mailto:<?=e($a['email']??'')?>" style="color:var(--primary);text-decoration:none;"><?=e($a['email']??'—')?></a></div>
            <?php if(!empty($a['phone'])):?><div class="fs-2xs-mt"><?=e($a['phone'])?></div><?php endif;?>
          </td>
          <td class="p-row">
            <form method="POST" class="inline">
              <?=csrfField()?><input type="hidden" name="action" value="update_app_status"><input type="hidden" name="id" value="<?=$a['id']?>">
              <select name="status" class="form-input" style="font-size:0.75rem;padding:0.25rem 0.5rem;" onchange="this.form.submit()">
                <?php foreach(['new'=>'New','reviewing'=>'Reviewing','shortlisted'=>'Shortlisted','interview'=>'Interview','hired'=>'Hired','rejected'=>'Rejected'] as $sv=>$sl):?>
                <option value="<?=$sv?>" <?=$status===$sv?'selected':''?>><?=$sl?></option>
                <?php endforeach;?>
              </select>
            </form>
          </td>
          <td style="padding:0.75rem 1rem;font-size:0.75rem;color:var(--muted-foreground);white-space:nowrap;"><?=timeAgo($a['created_at'])?></td>
          <td class="p-row">
            <div style="display:flex;gap:0.25rem;">
              <?php if(!empty($a['resume_url'])):?>
              <a href="<?=e($a['resume_url'])?>" target="_blank" class="btn btn-ghost btn-sm" title="Resume"></a>
              <?php endif;?>
              <form method="POST" class="inline" onsubmit="return confirm('Remove?')">
                <?=csrfField()?><input type="hidden" name="action" value="delete_app"><input type="hidden" name="id" value="<?=$a['id']?>">
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
<?php endif;?>

<?php require_once '../includes/admin-layout-close.php'; ?>
