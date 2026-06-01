<?php
/**
 * Admin — Add / Edit Client (full-page, tab layout)
 * Tabs: Basic Info | Contact | Services | Billing | Logo & Notes
 */
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
require_once '../includes/nepal-geo.php';
requireAdmin();

$id   = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$client = null;
if ($isEdit) {
    $client = queryOne("SELECT * FROM clients WHERE id=?", [$id]);
    if (!$client) { header('Location: clients.php'); exit; }
}
$pageTitle = $isEdit ? 'Edit Client — '.$client['org_name'] : 'Add New Client';

$error = $success = '';
$csrf  = generateCsrf();

// ── Upload directory ──────────────────────────────────────────────────────────
$uploadDir = dirname(__DIR__) . '/uploads/clients/';
if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);

// ── POST ──────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Security token mismatch.';
    } else {
        // ── Collect all fields ──────────────────────────────────────────────
        $org       = trim($_POST['org_name']     ?? '');
        $code      = strtoupper(trim($_POST['client_code'] ?? ''));
        $status    = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

        // Basic
        $province  = trim($_POST['province']  ?? '');
        $district  = trim($_POST['district']  ?? '');
        $localGovt = trim($_POST['local_govt'] ?? '');
        $wardNo    = trim($_POST['ward_no']    ?? '');
        $address   = trim($_POST['address']   ?? '');
        $package   = trim($_POST['package']   ?? '');

        // Contact
        $contact   = trim($_POST['contact_name']  ?? '');
        $desig     = trim($_POST['designation']    ?? '');
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone']          ?? '');
        $phone2    = trim($_POST['phone2']         ?? '');
        $mobile1   = trim($_POST['mobile1']        ?? '');
        $mobile2   = trim($_POST['mobile2']        ?? '');

        // Services
        $product   = trim($_POST['product']    ?? '');
        $cbsUse    = isset($_POST['cbs_use'])  ? 1 : 0;
        $integ     = trim($_POST['integration'] ?? '');
        $integChg  = ($_POST['integration_charge'] ?? '') !== '' ? (float)$_POST['integration_charge'] : null;
        $agreeDate = ($_POST['agreement_date']    ?? '') ?: null;
        $instDate  = ($_POST['installation_date'] ?? '') ?: null;
        $expiry    = trim($_POST['expiry_month']  ?? '');

        // Billing
        $branches  = max(1,(int)($_POST['num_branches']    ?? 1));
        $hoAmc     = ($_POST['head_office_amc']     ?? '') !== '' ? (float)$_POST['head_office_amc']     : null;
        $brAmc     = ($_POST['branch_office_amc']   ?? '') !== '' ? (float)$_POST['branch_office_amc']   : null;
        $cloudHo   = ($_POST['cloud_charge_ho']     ?? '') !== '' ? (float)$_POST['cloud_charge_ho']     : null;
        $cloudBr   = ($_POST['cloud_charge_branch'] ?? '') !== '' ? (float)$_POST['cloud_charge_branch'] : null;
        $cloudGb   = trim($_POST['cloud_gb']   ?? '');
        $notes     = trim($_POST['notes']      ?? '');

        // Logo: file upload takes priority over URL field
        $logoUrl = trim($_POST['logo_url'] ?? ($client['logo_url'] ?? ''));
        if (!empty($_FILES['logo_file']['tmp_name'])) {
            $ext  = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
            $safe = in_array($ext, ['png','jpg','jpeg','gif','svg','webp']);
            if ($safe && $_FILES['logo_file']['size'] < 2*1024*1024) {
                $fname    = 'client_' . ($isEdit ? $id : time()) . '.' . $ext;
                $destPath = $uploadDir . $fname;
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $destPath)) {
                    $logoUrl = SITE_URL . '/uploads/clients/' . $fname;
                }
            } else {
                $error = 'Logo must be PNG/JPG/SVG/WebP under 2 MB.';
            }
        }

        if (!$org) {
            $error = 'Organization name is required.';
        } elseif (!$error) {
            // Auto-generate code if blank
            if (!$code) {
                $year = date('Y');
                $last = queryOne("SELECT client_code FROM clients WHERE client_code LIKE ? ORDER BY id DESC LIMIT 1", ["CLT-{$year}-%"]);
                $n = 1;
                if ($last && preg_match('/CLT-\d{4}-(\d+)/', $last['client_code'], $m)) $n = (int)$m[1]+1;
                $code = sprintf('CLT-%s-%04d', $year, $n);
            }

            try {
                $fields = [
                    'org_name','client_code','status',
                    'province','district','local_govt','ward_no','address','package',
                    'contact_name','designation','email','phone','phone2','mobile1','mobile2',
                    'product','cbs_use','integration','integration_charge',
                    'agreement_date','installation_date','expiry_month',
                    'num_branches','head_office_amc','branch_office_amc',
                    'cloud_charge_ho','cloud_charge_branch','cloud_gb',
                    'notes','logo_url',
                ];
                $vals = [
                    $org,$code,$status,
                    $province,$district,$localGovt,$wardNo,$address,$package,
                    $contact,$desig,$email,$phone,$phone2,$mobile1,$mobile2,
                    $product,$cbsUse,$integ,$integChg,
                    $agreeDate,$instDate,$expiry,
                    $branches,$hoAmc,$brAmc,
                    $cloudHo,$cloudBr,$cloudGb,
                    $notes,$logoUrl ?: null,
                ];

                if ($isEdit) {
                    $set = implode('=?,', $fields) . '=?,updated_at=NOW()';
                    execute("UPDATE clients SET $set WHERE id=?", array_merge($vals, [$id]));
                    $success = 'Client updated successfully.';
                } else {
                    $ph  = implode(',', array_fill(0, count($fields)+1, '?'));
                    $fld = implode(',', $fields) . ',assigned_by';
                    execute("INSERT INTO clients ($fld) VALUES ($ph)", array_merge($vals, [currentUser()['id']]));
                    $success = "Client <strong>".e($org)."</strong> added with Client ID <strong>".e($code)."</strong>.";
                    if (!$isEdit) {
                        header("Location: clients.php?flash_success=1");
                        exit;
                    }
                }
            } catch (\Throwable $ex) {
                $error = 'Save failed: ' . $ex->getMessage();
            }
        }
        // Refresh client after save
        if ($isEdit && !$error) {
            $client = queryOne("SELECT * FROM clients WHERE id=?", [$id]);
        }
    }
}

$v = fn($k, $d='') => e($client[$k] ?? ($_POST[$k] ?? $d));

// Nepal geo for cascade
$geo = nepalGeo();

require_once '../includes/admin-layout.php';
?>

<style>
/* ── Tab styles ─────────────────────────────────────────────────── */
.cf-tab { padding:.575rem 1.25rem;border-radius:.625rem;font-size:.875rem;font-weight:600;color:var(--muted-foreground);cursor:pointer;border:none;background:transparent;font-family:var(--font-display);transition:color .15s,background .15s;white-space:nowrap; }
.cf-tab.active { color:var(--primary);background:var(--primary-light); }
.cf-tab:hover:not(.active) { color:var(--foreground);background:var(--muted); }
/* cf-pane visibility controlled by Alpine x-show (not CSS .active) */
[x-cloak] { display:none !important; }
.form-section { margin-bottom:1.75rem; }
.form-section-title { font-family:var(--font-display);font-weight:700;font-size:.9375rem;color:var(--foreground);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:.5rem; }
.form-row { display:grid;gap:1rem;margin-bottom:1rem; }
@media(min-width:640px){ .form-row-2 { grid-template-columns:1fr 1fr; } }
@media(min-width:768px){ .form-row-3 { grid-template-columns:1fr 1fr 1fr; } }
@media(min-width:768px){ .form-row-4 { grid-template-columns:1fr 1fr 1fr 1fr; } }
.badge-code { font-family:var(--font-display);font-weight:800;font-size:.875rem;padding:.25rem .875rem;border-radius:.5rem;background:#dbeafe;color:var(--primary-dark);letter-spacing:.04em; }
.logo-preview { width:5rem;height:5rem;border-radius:.875rem;object-fit:contain;border:1px solid var(--border);background:var(--muted);padding:.375rem; }
</style>

<!-- Header -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;">
  <div>
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.25rem;">
      <a href="clients.php" style="color:var(--muted-foreground);font-size:.875rem;text-decoration:none;display:flex;align-items:center;gap:.375rem;">
        <i data-lucide="arrow-left" class="ic-14"></i> Clients
      </a>
      <span class="text-muted">/</span>
      <span style="font-size:.875rem;color:var(--foreground);font-weight:600;"><?= $isEdit ? e($client['org_name']) : 'New Client' ?></span>
    </div>
    <h1 style="font-family:var(--font-display);font-size:1.375rem;font-weight:800;color:var(--foreground);display:flex;align-items:center;gap:.625rem;">
      <i data-lucide="<?= $isEdit?'pencil':'user-plus' ?>" style="width:20px;height:20px;color:var(--primary);"></i>
      <?= $isEdit ? 'Edit Client' : 'Add New Client' ?>
    </h1>
    <?php if ($isEdit): ?>
    <div style="margin-top:.375rem;display:flex;align-items:center;gap:.625rem;flex-wrap:wrap;">
      <span class="badge-code"><?= e($client['client_code']) ?></span>
      <span style="padding:.2rem .625rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:<?= $client['status']==='active'?'#dcfce7':'#fee2e2' ?>;color:<?= $client['status']==='active'?'#15803d':'#b91c1c' ?>;">
        <?= ucfirst($client['status']) ?>
      </span>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if ($success): ?>
<div style="display:flex;align-items:center;gap:.625rem;padding:.875rem 1.125rem;background:#f0fdf4;border:1px solid #86efac;border-radius:var(--radius-md);margin-bottom:1.25rem;color:#15803d;font-size:.875rem;">
  <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
  <?= $success ?>
</div>
<?php endif; ?>
<?php if ($error): ?>
<div style="display:flex;align-items:center;gap:.625rem;padding:.875rem 1.125rem;background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--radius-md);margin-bottom:1.25rem;color:#b91c1c;font-size:.875rem;">
  <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
  <?= e($error) ?>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" id="cf-form" x-data="{tab:'basic'}">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">

<!-- Tab bar -->
<div style="display:flex;flex-wrap:wrap;gap:.25rem;padding:.3rem;background:var(--muted);border-radius:var(--radius-xl);margin-bottom:1.75rem;max-width:fit-content;" role="tablist">
  <?php foreach([['basic','Basic Info','building-2'],['contact','Contact','user'],['services','Services','layers'],['billing','Billing','receipt'],['logo','Logo & Notes','image']] as [$slug,$name,$ic]): ?>
  <button type="button" class="cf-tab" :class="{active:tab==='<?= $slug ?>'}" @click="tab='<?= $slug ?>'" role="tab">
    <i data-lucide="<?= $ic ?>" style="width:13px;height:13px;display:inline;vertical-align:middle;margin-right:.25rem;"></i>
    <?= $name ?>
  </button>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:1.75rem;" id="cf-body">

<!-- ══════════════════════════════════════════
  TAB 1 — BASIC INFO
══════════════════════════════════════════ -->
<div class="cf-pane" x-show="tab==='basic'" x-cloak>
  <div class="st-card" style="padding:1.875rem;">

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="building-2" class="ic-16-p"></i>
        Organization
      </div>
      <div class="form-row">
        <div>
          <label class="form-label">Organization Name <span class="text-danger-token">*</span></label>
          <input type="text" name="org_name" required class="form-input" placeholder="e.g. ABC Company Pvt. Ltd." value="<?= $v('org_name') ?>">
        </div>
      </div>
      <div class="form-row form-row-3">
        <div>
          <label class="form-label">
            Client Code / Office ID
            <span style="font-size:.75rem;font-weight:400;color:var(--muted-foreground);"> (leave blank to auto-generate)</span>
          </label>
          <input type="text" name="client_code" class="form-input" placeholder="e.g. 670 or CLT-<?= date('Y') ?>-0001"
                 value="<?= $v('client_code') ?>" style="font-family:monospace;font-weight:700;letter-spacing:.04em;">
          <p style="font-size:.72rem;color:var(--muted-foreground);margin-top:.25rem;">This is the ID clients use to sign up for the portal.</p>
        </div>
        <div>
          <label class="form-label">Package / Plan</label>
          <select name="package" class="form-input">
            <option value="">— Select package —</option>
            <?php foreach(['Starter','Growth','Enterprise','Custom'] as $p): ?>
            <option value="<?= $p ?>" <?= ($v('package')===$p)?'selected':'' ?>><?= $p ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Status</label>
          <select name="status" class="form-input">
            <option value="active"   <?= ($v('status','active')==='active')?'selected':'' ?>>Active — can sign up</option>
            <option value="inactive" <?= ($v('status','active')==='inactive')?'selected':'' ?>>Inactive — signup blocked</option>
          </select>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="map-pin" class="ic-16-p"></i>
        Location
      </div>
      <div class="form-row form-row-3" id="loc-row">
        <div>
          <label class="form-label">Province</label>
          <select name="province" class="form-input" id="sel-province" onchange="onProvince(this.value)">
            <option value="">— Select Province —</option>
            <?php foreach(array_keys($geo) as $prov): ?>
            <option value="<?= e($prov) ?>" <?= ($v('province')===$prov)?'selected':'' ?>><?= e($prov) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">District</label>
          <select name="district" class="form-input" id="sel-district" onchange="onDistrict(this.value)">
            <option value="">— Select District —</option>
            <?php
            $curProv = $v('province');
            if ($curProv && isset($geo[$curProv])) {
                foreach(array_keys($geo[$curProv]) as $dist):?>
            <option value="<?= e($dist) ?>" <?= ($v('district')===$dist)?'selected':'' ?>><?= e($dist) ?></option>
            <?php endforeach; } ?>
          </select>
        </div>
        <div>
          <label class="form-label">Local Government</label>
          <select name="local_govt" class="form-input" id="sel-localgov">
            <option value="">— Select Local Govt —</option>
            <?php
            $curDist = $v('district');
            if ($curDist) {
                $lgs = nepalLocalGovts($curDist);
                foreach($lgs as $lg):?>
            <option value="<?= e($lg) ?>" <?= ($v('local_govt')===$lg)?'selected':'' ?>><?= e($lg) ?></option>
            <?php endforeach; } ?>
          </select>
        </div>
      </div>
      <div class="form-row form-row-2">
        <div>
          <label class="form-label">Ward No.</label>
          <input type="text" name="ward_no" class="form-input" placeholder="e.g. 3" value="<?= $v('ward_no') ?>">
        </div>
        <div>
          <label class="form-label">Full Address</label>
          <input type="text" name="address" class="form-input" placeholder="Street / Tole / Office address" value="<?= $v('address') ?>">
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════
  TAB 2 — CONTACT
══════════════════════════════════════════ -->
<div class="cf-pane" x-show="tab==='contact'" x-cloak>
  <div class="st-card" style="padding:1.875rem;">
    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="user" class="ic-16-p"></i>
        Primary Contact Person
      </div>
      <div class="form-row form-row-2">
        <div>
          <label class="form-label">Contact Person Name</label>
          <input type="text" name="contact_name" class="form-input" placeholder="e.g. Ram Prasad Sharma" value="<?= $v('contact_name') ?>">
        </div>
        <div>
          <label class="form-label">Designation / Role</label>
          <input type="text" name="designation" class="form-input" placeholder="e.g. Manager, Chairman" value="<?= $v('designation') ?>">
        </div>
      </div>
      <div class="form-row">
        <div>
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="contact@business.com.np" value="<?= $v('email') ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="phone" class="ic-16-p"></i>
        Phone Numbers
      </div>
      <div class="form-row form-row-2">
        <div>
          <label class="form-label">Office Phone 1</label>
          <input type="tel" name="phone" class="form-input" placeholder="e.g. 071-522742" value="<?= $v('phone') ?>">
        </div>
        <div>
          <label class="form-label">Office Phone 2</label>
          <input type="tel" name="phone2" class="form-input" placeholder="e.g. 071-522743" value="<?= $v('phone2') ?>">
        </div>
        <div>
          <label class="form-label">Mobile 1</label>
          <input type="tel" name="mobile1" class="form-input" placeholder="98X-XXXXXXX" value="<?= $v('mobile1') ?>">
        </div>
        <div>
          <label class="form-label">Mobile 2</label>
          <input type="tel" name="mobile2" class="form-input" placeholder="98X-XXXXXXX" value="<?= $v('mobile2') ?>">
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
  TAB 3 — SERVICES
══════════════════════════════════════════ -->
<div class="cf-pane" x-show="tab==='services'" x-cloak>
  <div class="st-card" style="padding:1.875rem;">
    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="layers" class="ic-16-p"></i>
        Products & Services
      </div>
      <div class="form-row form-row-2">
        <div>
          <label class="form-label">Product(s) in Use</label>
          <input type="text" name="product" class="form-input" placeholder="e.g. Software, IT Support, Website" value="<?= $v('product') ?>">
        </div>
        <div>
          <label class="form-label">Software In Use?</label>
          <label style="display:flex;align-items:center;gap:.625rem;margin-top:.625rem;cursor:pointer;">
            <input type="checkbox" name="cbs_use" value="1" <?= ($v('cbs_use','1')==='1')?'checked':'' ?>
                   style="width:1.125rem;height:1.125rem;accent-color:var(--primary);">
            <span style="font-size:.875rem;color:var(--foreground);">Yes, software is active for this client</span>
          </label>
        </div>
      </div>
      <div class="form-row form-row-2">
        <div>
          <label class="form-label">Integration / Third-party</label>
          <input type="text" name="integration" class="form-input" placeholder="e.g. Akash DMS, eSewa, NPS" value="<?= $v('integration') ?>">
        </div>
        <div>
          <label class="form-label">Integration Charge (NPR)</label>
          <input type="number" name="integration_charge" class="form-input" placeholder="0" step="0.01" min="0" value="<?= $v('integration_charge') ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="calendar" class="ic-16-p"></i>
        Timeline
      </div>
      <div class="form-row form-row-3">
        <div>
          <label class="form-label">Agreement Date</label>
          <input type="date" name="agreement_date" class="form-input" data-bs-picker value="<?= $v('agreement_date') ?>">
        </div>
        <div>
          <label class="form-label">Installation Date</label>
          <input type="date" name="installation_date" class="form-input" data-bs-picker value="<?= $v('installation_date') ?>">
        </div>
        <div>
          <label class="form-label">Expiry / Renewal Month</label>
          <select name="expiry_month" class="form-input">
            <option value="">— Month —</option>
            <?php foreach(['Baisakh','Jestha','Ashadh','Shrawan','Bhadra','Ashwin','Kartik','Mangsir','Poush','Magh','Falgun','Chaitra'] as $m): ?>
            <option value="<?= $m ?>" <?= ($v('expiry_month')===$m)?'selected':'' ?>><?= $m ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
  TAB 4 — BILLING
══════════════════════════════════════════ -->
<div class="cf-pane" x-show="tab==='billing'" x-cloak>
  <div class="st-card" style="padding:1.875rem;">
    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="receipt" class="ic-16-p"></i>
        Branches
      </div>
      <div class="form-row" style="max-width:14rem;">
        <div>
          <label class="form-label">Number of Branches</label>
          <input type="number" name="num_branches" class="form-input" min="1" max="999" value="<?= $v('num_branches','1') ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="banknote" class="ic-16-p"></i>
        AMC (Annual Maintenance Charges) — NPR
      </div>
      <div class="form-row form-row-2">
        <div>
          <label class="form-label">Head Office AMC</label>
          <input type="number" name="head_office_amc" class="form-input" placeholder="0.00" step="0.01" min="0" value="<?= $v('head_office_amc') ?>">
        </div>
        <div>
          <label class="form-label">Per Branch AMC</label>
          <input type="number" name="branch_office_amc" class="form-input" placeholder="0.00" step="0.01" min="0" value="<?= $v('branch_office_amc') ?>">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="cloud" class="ic-16-p"></i>
        Cloud Service Charges — NPR
      </div>
      <div class="form-row form-row-3">
        <div>
          <label class="form-label">Cloud HO</label>
          <input type="number" name="cloud_charge_ho" class="form-input" placeholder="0.00" step="0.01" min="0" value="<?= $v('cloud_charge_ho') ?>">
        </div>
        <div>
          <label class="form-label">Cloud Branch</label>
          <input type="number" name="cloud_charge_branch" class="form-input" placeholder="0.00" step="0.01" min="0" value="<?= $v('cloud_charge_branch') ?>">
        </div>
        <div>
          <label class="form-label">Cloud Storage</label>
          <input type="text" name="cloud_gb" class="form-input" placeholder="e.g. 10 GB" value="<?= $v('cloud_gb') ?>">
        </div>
      </div>
    </div>

    <!-- Billing summary (live) -->
    <div style="padding:1rem 1.25rem;background:var(--muted);border-radius:var(--radius-md);border:1px solid var(--border);" id="billing-summary">
      <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted-foreground);margin-bottom:.625rem;">
        Estimated Monthly Revenue
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem;">
        <div style="font-family:var(--font-display);font-weight:800;font-size:1.375rem;color:var(--primary);" id="bsummary-total">NPR —</div>
        <div style="font-size:.8125rem;color:var(--muted-foreground);align-self:flex-end;">(AMC ÷ 12 + cloud charges)</div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════
  TAB 5 — LOGO & NOTES
══════════════════════════════════════════ -->
<div class="cf-pane" x-show="tab==='logo'" x-cloak>
  <div class="st-card" style="padding:1.875rem;">
    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="image" class="ic-16-p"></i>
        Client Logo
      </div>
      <div style="display:flex;align-items:flex-start;gap:1.5rem;flex-wrap:wrap;margin-bottom:1rem;">
        <?php $lg=$v('logo_url'); if($lg): ?>
        <img src="<?= $lg ?>" alt="logo" class="logo-preview" id="logo-preview">
        <?php else: ?>
        <div class="logo-preview" id="logo-preview" style="display:flex;align-items:center;justify-content:center;">
          <i data-lucide="image" style="width:24px;height:24px;color:var(--muted-foreground);"></i>
        </div>
        <?php endif; ?>
        <div style="flex:1;min-width:16rem;">
          <label class="form-label" style="margin-bottom:.5rem;">Upload logo file</label>
          <input type="file" name="logo_file" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp"
                 style="display:block;width:100%;padding:.5rem .75rem;border:1.5px dashed var(--border);border-radius:var(--radius-md);background:var(--muted);font-size:.875rem;cursor:pointer;"
                 onchange="previewLogo(this)">
          <p style="font-size:.72rem;color:var(--muted-foreground);margin-top:.375rem;">PNG, JPG, SVG or WebP — max 2 MB</p>

          <label class="form-label" style="margin-top:1rem;margin-bottom:.5rem;">Or paste logo URL</label>
          <input type="url" name="logo_url" class="form-input" placeholder="https://..." value="<?= $v('logo_url') ?>"
                 oninput="if(this.value)document.getElementById('logo-preview').src=this.value">
          <p style="font-size:.72rem;color:var(--muted-foreground);margin-top:.25rem;">
            This logo appears in the homepage client scroll strip. Leave blank to hide from homepage.
          </p>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">
        <i data-lucide="file-text" class="ic-16-p"></i>
        Internal Notes
      </div>
      <textarea name="notes" class="form-input" rows="5" placeholder="Internal notes visible only to admin team (not shown to client)…"><?= $v('notes') ?></textarea>
    </div>
  </div>
</div>

</div><!-- /cf-body -->

<!-- Sticky footer bar -->
<div style="position:sticky;bottom:0;z-index:50;margin-top:1.5rem;background:var(--background);border-top:1px solid var(--border);padding:1rem 0;display:flex;align-items:center;gap:.875rem;flex-wrap:wrap;">
  <button type="submit" class="btn btn-primary btn-md" style="min-width:10rem;">
    <i data-lucide="save" class="ic-15"></i>
    <?= $isEdit ? 'Save Changes' : 'Add Client + Generate ID' ?>
  </button>
  <a href="clients.php" class="btn btn-outline btn-md">Cancel</a>
  <?php if ($isEdit): ?>
  <span style="margin-left:auto;font-size:.8125rem;color:var(--muted-foreground);">
    Last updated: <?= date('d M Y, g:ia', strtotime($client['updated_at'])) ?>
  </span>
  <?php endif; ?>
</div>

</form>

<script>
// ── Nepal geo cascade ────────────────────────────────────────────
var _geo = <?= json_encode($geo, JSON_UNESCAPED_UNICODE) ?>;

// नेपालीमा: onProvince() — yo function le aafno kaam garchha
function onProvince(prov) {
  var dSel = document.getElementById('sel-district');
  var lSel = document.getElementById('sel-localgov');
  dSel.innerHTML = '<option value="">— Select District —</option>';
  lSel.innerHTML = '<option value="">— Select Local Govt —</option>';
  if (prov && _geo[prov]) {
    Object.keys(_geo[prov]).forEach(function(d) {
      var o = document.createElement('option'); o.value = d; o.textContent = d;
      dSel.appendChild(o);
    });
  }
}

// नेपालीमा: onDistrict() — yo function le aafno kaam garchha
function onDistrict(dist) {
  var prov = document.getElementById('sel-province').value;
  var lSel = document.getElementById('sel-localgov');
  lSel.innerHTML = '<option value="">— Select Local Govt —</option>';
  if (prov && dist && _geo[prov] && _geo[prov][dist]) {
    _geo[prov][dist].forEach(function(lg) {
      var o = document.createElement('option'); o.value = lg; o.textContent = lg;
      lSel.appendChild(o);
    });
  }
}

// ── Logo preview ─────────────────────────────────────────────────
function previewLogo(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      var el = document.getElementById('logo-preview');
      if (el.tagName === 'IMG') el.src = e.target.result;
      else { var img = document.createElement('img'); img.src = e.target.result; img.className = 'logo-preview'; img.id = 'logo-preview'; el.replaceWith(img); }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ── Billing summary ───────────────────────────────────────────────
function updateBilling() {
  var hoAmc  = parseFloat(document.querySelector('[name=head_office_amc]').value) || 0;
  var brAmc  = parseFloat(document.querySelector('[name=branch_office_amc]').value) || 0;
  var br     = parseInt(document.querySelector('[name=num_branches]').value) || 1;
  var cloudH = parseFloat(document.querySelector('[name=cloud_charge_ho]').value) || 0;
  var cloudB = parseFloat(document.querySelector('[name=cloud_charge_branch]').value) || 0;
  var monthly = (hoAmc + brAmc*(br-1))/12 + cloudH + cloudB;
  document.getElementById('bsummary-total').textContent = monthly > 0 ? 'NPR ' + Math.round(monthly).toLocaleString() : 'NPR —';
}
document.querySelectorAll('[name=head_office_amc],[name=branch_office_amc],[name=num_branches],[name=cloud_charge_ho],[name=cloud_charge_branch]').forEach(function(el){
  el.addEventListener('input', updateBilling);
});
updateBilling();

// ── Geo cascade: re-populate district + local-govt on edit-mode load ──
(function() {
  var prov = document.getElementById('sel-province').value;
  var dist = document.getElementById('sel-district').value;
  var lg   = document.getElementById('sel-localgov').value;
  // If province already selected (edit mode) but district list only has
  // PHP-rendered options — ensure JS _geo matches so cascade still works.
  // If district is selected, repopulate local-govt from JS data.
  if (prov && dist && _geo[prov] && _geo[prov][dist]) {
    var lSel = document.getElementById('sel-localgov');
    // Keep existing selected value; rebuild list from JS data
    var existing = lg;
    lSel.innerHTML = '<option value="">— Select Local Govt —</option>';
    _geo[prov][dist].forEach(function(name) {
      var o = document.createElement('option');
      o.value = name;
      o.textContent = name;
      if (name === existing) o.selected = true;
      lSel.appendChild(o);
    });
  }
  // Ensure district list is also JS-driven (keeps PHP render as fallback)
  if (prov && _geo[prov]) {
    var dSel = document.getElementById('sel-district');
    var existingDist = dist;
    dSel.innerHTML = '<option value="">— Select District —</option>';
    Object.keys(_geo[prov]).forEach(function(d) {
      var o = document.createElement('option');
      o.value = d; o.textContent = d;
      if (d === existingDist) o.selected = true;
      dSel.appendChild(o);
    });
    // Re-populate local govt after rebuilding district list
    if (existingDist && _geo[prov][existingDist]) {
      var lSel2 = document.getElementById('sel-localgov');
      var existingLg = lg;
      lSel2.innerHTML = '<option value="">— Select Local Govt —</option>';
      _geo[prov][existingDist].forEach(function(name) {
        var o = document.createElement('option');
        o.value = name; o.textContent = name;
        if (name === existingLg) o.selected = true;
        lSel2.appendChild(o);
      });
    }
  }
})();
</script>

<?php require_once '../includes/admin-layout-close.php'; ?>
