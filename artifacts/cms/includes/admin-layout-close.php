    </main>
  </div>
</div>

<?php require_once __DIR__ . '/toast.php'; ?>
<script>
// toggleTheme() is defined globally in includes/head.php — available on all pages
function confirmDelete(form,msg){if(confirm(msg||'Delete this item? This cannot be undone.'))form.submit();}

// ── In-panel form tab switching (.af-tab-btn / .af-tab-pane) ──────────────
(function(){
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.af-tab-btn');
    if (!btn) return;
    var nav  = btn.closest('.af-tab-nav');
    var form = btn.closest('form');
    if (!nav || !form) return;
    nav.querySelectorAll('.af-tab-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    form.querySelectorAll('.af-tab-pane').forEach(function(p){ p.classList.remove('active'); });
    var pane = form.querySelector('[data-tab-pane="' + btn.dataset.tab + '"]');
    if (pane) pane.classList.add('active');
  });
})();
<?php
$s = getFlash('success'); $e2 = getFlash('error'); $w = getFlash('warning');
if ($s)  echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($s).",'success'));";
if ($e2) echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($e2).",'error'));";
if ($w)  echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($w).",'warning'));";
?>
</script>
<script src="<?= url('assets/js/nepali-datepicker.js') ?>"></script>
</body>
</html>
