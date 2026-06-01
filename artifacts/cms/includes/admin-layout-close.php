    </main>
  </div>
</div>

<?php require_once __DIR__ . '/toast.php'; ?>
<script>
// toggleTheme() is defined globally in includes/head.php — available on all pages
function confirmDelete(form,msg){if(confirm(msg||'Delete this item? This cannot be undone.'))form.submit();}
<?php
$s = getFlash('success'); $e2 = getFlash('error'); $w = getFlash('warning');
if ($s)  echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($s).",'success'));";
if ($e2) echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($e2).",'error'));";
if ($w)  echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($w).",'warning'));";
?>
</script>
</body>
</html>
