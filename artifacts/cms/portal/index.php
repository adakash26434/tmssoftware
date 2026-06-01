<?php
$pageTitle = 'My Overview';
require_once '../includes/portal-layout.php';

$tickets = [];
try {
    $tickets = query(
        "SELECT id, number, subject, status, priority, last_message_at, created_at
         FROM tickets WHERE user_id=? ORDER BY last_message_at DESC, created_at DESC LIMIT 10",
        [$__user['id']]
    );
} catch(\Throwable $e) {}

$counts = ['open'=>0,'in_progress'=>0,'replied'=>0,'resolved'=>0,'closed'=>0];
foreach ($tickets as $t) {
    $s = $t['status'] ?? 'open';
    if (isset($counts[$s])) $counts[$s]++;
}

$STATUS_COLORS = [
    'open'        => ['#fee2e2','#b91c1c','Open'],
    'in_progress' => ['#fef9c3','#854d0e','In Progress'],
    'replied'     => ['#f3e8ff','#7e22ce','Replied'],
    'resolved'    => ['#dcfce7','#15803d','Resolved'],
    'closed'      => ['var(--muted)','var(--muted-foreground)','Closed'],
];

// Active subscriptions + expiring soon
$activeSubs   = [];
$expiringSubs = [];
try {
    $activeSubs = query(
        "SELECT product_name, plan_name, status, expires_at, next_renewal
         FROM client_subscriptions WHERE user_id=? AND status IN('active','trial')
         ORDER BY expires_at ASC LIMIT 5",
        [$__user['id']]
    );
    $expiringSubs = array_filter($activeSubs, fn($s) =>
        $s['expires_at'] &&
        strtotime($s['expires_at']) < strtotime('+30 days') &&
        strtotime($s['expires_at']) > time()
    );
} catch(\Throwable $e) {}
?>

<!-- Welcome -->
<div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;">
  <div>
    <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted-foreground);">Welcome back</div>
    <h1 style="font-family:var(--font-display);font-size:1.5rem;font-weight:700;color:var(--foreground);margin-top:0.25rem;">
      <?= e($__user['display_name'] ?? explode('@',$__user['email'])[0]) ?>'s Overview
    </h1>
  </div>
  <a href="<?= url('portal/tickets-new.php') ?>" class="btn btn-primary">+ New Ticket</a>
</div>

<?php if(!empty($__user['client_code'])): ?>
<!-- Client ID Banner -->
<div style="display:flex;align-items:center;gap:1rem;padding:0.875rem 1.25rem;border-radius:0.875rem;background:var(--primary-light);border:1.5px solid var(--primary);margin-bottom:2rem;flex-wrap:wrap;">
  <div style="display:flex;align-items:center;gap:0.5rem;flex:1;min-width:0;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
    <div>
      <div style="font-size:0.6875rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:var(--primary);opacity:0.8;">Your Client ID</div>
      <div style="font-family:'JetBrains Mono',monospace;font-size:1.0625rem;font-weight:800;letter-spacing:0.06em;color:var(--primary);"><?= e($__user['client_code']) ?></div>
    </div>
  </div>
  <?php if(!empty($__user['org_name'])): ?>
  <div style="font-size:0.8125rem;color:var(--primary);opacity:0.75;white-space:nowrap;"><?= e($__user['org_name']) ?></div>
  <?php endif; ?>
  <button onclick="navigator.clipboard?.writeText('<?= e($__user['client_code']) ?>').then(()=>{this.textContent='Copied!';setTimeout(()=>this.textContent='Copy ID',1500)})"
    style="font-size:0.75rem;font-weight:600;padding:0.3rem 0.75rem;border-radius:0.5rem;border:1.5px solid var(--primary);background:transparent;color:var(--primary);cursor:pointer;white-space:nowrap;">
    Copy ID
  </button>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:1rem;margin-bottom:2rem;">
  <a href="<?= url('portal/tickets.php') ?>" style="text-decoration:none;display:block;padding:1.25rem;border-radius:1rem;border:1px solid var(--border);background:var(--card);text-align:center;transition:box-shadow 0.2s;"
     onmouseover="this.style.boxShadow='var(--shadow-elevated)'" onmouseout="this.style.boxShadow='none'">
    <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:var(--primary);"><?= count($tickets) ?></div>
    <div class="caption-meta">Total Tickets</div>
  </a>
  <?php
  $stat_statuses = [
    ['open',        '#fee2e2','#b91c1c','alert-circle','Open',       $counts['open']],
    ['in_progress', '#fef9c3','#92400e','loader','In Progress', $counts['in_progress']+$counts['replied']],
    ['resolved',    '#dcfce7','#15803d','check-circle','Resolved',    $counts['resolved']+$counts['closed']],
  ];
  foreach ($stat_statuses as [$s,$bg,$col,$iconName,$label,$cnt]):?>
  <a href="<?= url('portal/tickets.php?status='.$s) ?>" style="text-decoration:none;display:block;padding:1.25rem;border-radius:1rem;border:1px solid var(--border);background:<?=$bg?>;text-align:center;transition:box-shadow 0.2s;"
     onmouseover="this.style.boxShadow='var(--shadow-elevated)'" onmouseout="this.style.boxShadow='none'">
    <div style="font-family:var(--font-display);font-size:2rem;font-weight:800;color:<?=$col?>;"><?=$cnt?></div>
    <div style="font-size:0.75rem;color:<?=$col?>;margin-top:0.25rem;font-weight:500;display:flex;align-items:center;justify-content:center;gap:0.25rem;"><?= icon($iconName,13,'color:'.$col.';') ?> <?=$label?></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- Subscription renewal alerts -->
<?php if (!empty($expiringSubs)): ?>
<div style="display:flex;align-items:flex-start;gap:0.875rem;padding:1rem 1.25rem;border-radius:0.875rem;background:#fffbeb;border:1px solid #fde047;margin-bottom:1.5rem;">
  <?= icon('alert-triangle',22,'color:#b45309;flex-shrink:0;') ?>
  <div class="flex-1">
    <div style="font-weight:700;color:#b45309;font-size:0.9375rem;">Software Renewal Required</div>
    <div style="font-size:0.8125rem;color:#92400e;margin-top:0.2rem;">
      <?php foreach(array_values($expiringSubs) as $i=>$s): $d=ceil((strtotime($s['expires_at'])-time())/86400); ?>
      <span><?=e($s['product_name'])?> expires in <strong><?=$d?> day<?=$d>1?'s':''?></strong><?=($i<count($expiringSubs)-1?'; ':'')?></span>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;gap:0.5rem;margin-top:0.75rem;flex-wrap:wrap;">
      <a href="<?=url('portal/tickets-new.php')?>" class="btn btn-primary btn-sm"><?= icon('refresh-cw',14) ?> Request Renewal</a>
      <a href="<?=url('portal/services.php')?>" class="btn btn-outline btn-sm"><?= icon('package',14) ?> View Services</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($activeSubs) && empty($expiringSubs)): ?>
<div style="border-radius:0.875rem;border:1px solid var(--border);background:var(--card);overflow:hidden;margin-bottom:1.5rem;">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid var(--border);">
    <h2 style="font-family:var(--font-display);font-size:0.9375rem;font-weight:700;display:flex;align-items:center;gap:0.5rem;"><?= icon('package',15) ?> Active Software</h2>
    <a href="<?=url('portal/services.php')?>" style="font-size:0.8125rem;color:var(--primary);text-decoration:none;font-weight:500;">View all →</a>
  </div>
  <div>
  <?php foreach($activeSubs as $i=>$s):
    $last = $i === count($activeSubs)-1;
    $sBg  = $s['status']==='trial' ? '#dbeafe' : '#dcfce7';
    $sCol = $s['status']==='trial' ? 'var(--primary-dark)' : '#15803d';
    $sLbl = ucfirst($s['status']);
  ?>
  <div style="display:flex;align-items:center;gap:1rem;padding:0.875rem 1.5rem;<?=!$last?'border-bottom:1px solid var(--border);':''?>">
    <div class="flex-1-min">
      <div style="font-size:0.875rem;font-weight:600;color:var(--foreground);"><?=e($s['product_name'])?></div>
      <?php if($s['plan_name']):?><div class="fs-xs-mt"><?=e($s['plan_name'])?> Plan</div><?php endif;?>
    </div>
    <span style="padding:0.2rem 0.625rem;border-radius:9999px;font-size:0.6875rem;font-weight:600;background:<?=$sBg?>;color:<?=$sCol?>;"><?=$sLbl?></span>
    <?php if($s['expires_at']):?><span class="fs-xs-mt">Expires <?=date('M j, Y',strtotime($s['expires_at']))?></span><?php endif;?>
  </div>
  <?php endforeach;?>
  </div>
</div>
<?php endif; ?>

<!-- Recent tickets -->
<div style="border-radius:1rem;border:1px solid var(--border);background:var(--card);overflow:hidden;margin-bottom:1.5rem;">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);">
    <h2 style="font-family:var(--font-display);font-size:0.9375rem;font-weight:700;color:var(--foreground);">Recent Tickets</h2>
    <a href="<?= url('portal/tickets.php') ?>" style="font-size:0.8125rem;color:var(--primary);text-decoration:none;font-weight:500;">View all →</a>
  </div>

  <?php if (empty($tickets)): ?>
  <div class="p-empty">
    <div style="margin-bottom:0.75rem;"><?= icon('ticket',40,'color:var(--muted-foreground);') ?></div>
    <div style="font-weight:600;margin-bottom:0.375rem;">No tickets yet</div>
    <p style="font-size:0.875rem;margin-bottom:1.25rem;">Create your first support ticket and we'll get back to you within 24 hours.</p>
    <a href="<?= url('portal/tickets-new.php') ?>" class="btn btn-primary btn-sm">Create Ticket →</a>
  </div>
  <?php else: ?>
  <?php foreach (array_slice($tickets, 0, 6) as $i => $t):
    [$bg,$col,$lbl] = $STATUS_COLORS[$t['status']] ?? ['var(--muted)','var(--muted-foreground)','Unknown'];
    $PRI = ['low'=>'low','normal'=>'normal','high'=>'high','urgent'=>'urgent'];
    $last = $i === count(array_slice($tickets,0,6))-1;
  ?>
  <a href="<?= url('portal/ticket.php?id='.$t['id']) ?>"
     style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;text-decoration:none;<?= !$last ? 'border-bottom:1px solid var(--border);' : '' ?>transition:background 0.15s;"
     onmouseover="this.style.background='var(--background)'" onmouseout="this.style.background='transparent'">
    <div style="width:2.25rem;height:2.25rem;border-radius:0.5rem;background:var(--background);border:1px solid var(--border);display:grid;place-items:center;font-size:0.6875rem;font-weight:700;color:var(--muted-foreground);flex-shrink:0;">#<?=(int)$t['number']?></div>
    <div class="flex-1-min">
      <div style="font-size:0.875rem;font-weight:600;color:var(--foreground);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($t['subject']) ?></div>
      <div style="font-size:0.6875rem;color:var(--muted-foreground);margin-top:0.2rem;">
        <?= ucfirst($t['priority']) ?> ·
        <?= $t['last_message_at'] ? date('M j, Y', strtotime($t['last_message_at'])) : date('M j, Y', strtotime($t['created_at'])) ?>
      </div>
    </div>
    <span style="padding:0.2rem 0.625rem;border-radius:9999px;font-size:0.6875rem;font-weight:600;background:<?=$bg?>;color:<?=$col?>;white-space:nowrap;flex-shrink:0;"><?=$lbl?></span>
    <span style="color:var(--muted-foreground);font-size:0.875rem;flex-shrink:0;">›</span>
  </a>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Quick actions -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;">
  <?php
  $actions = [
    [url('portal/tickets-new.php'),            'plus-circle',    'New Ticket',       'Report an issue or request'],
    [url('portal/tickets.php?status=replied'), 'message-circle', 'Needs Your Reply', 'Staff replied — respond now'],
    [url('portal/services.php'),               'package',        'My Services',      'View software & renewals'],
    [url('portal/contacts.php'),               'phone',          'Support Contacts', 'Call, WhatsApp or email us'],
  ];
  foreach ($actions as [$href,$iconName,$title,$desc]):?>
  <a href="<?=$href?>" style="display:flex;align-items:flex-start;gap:0.75rem;padding:1.1rem;border-radius:1rem;border:1px solid var(--border);background:var(--card);text-decoration:none;transition:box-shadow 0.2s,border-color 0.2s;"
     onmouseover="this.style.boxShadow='var(--shadow-elevated)';this.style.borderColor='var(--primary)'" onmouseout="this.style.boxShadow='none';this.style.borderColor='var(--border)'">
    <span style="display:flex;align-items:center;flex-shrink:0;color:var(--primary);"><?= icon($iconName,22,'color:var(--primary);') ?></span>
    <div>
      <div style="font-size:0.8125rem;font-weight:700;color:var(--foreground);"><?=$title?></div>
      <div style="font-size:0.6875rem;color:var(--muted-foreground);margin-top:0.125rem;"><?=$desc?></div>
    </div>
  </a>
  <?php endforeach;?>
</div>

<?php require_once '../includes/portal-layout-end.php'; ?>
