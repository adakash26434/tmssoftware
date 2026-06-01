    </main>
  </div>
</div>
<?php require_once __DIR__ . '/toast.php'; ?>
<script>
<?php
$s = getFlash('success'); $e2 = getFlash('error'); $w = getFlash('warning');
if ($s)  echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($s).",'success'));";
if ($e2) echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($e2).",'error'));";
if ($w)  echo "document.addEventListener('DOMContentLoaded',()=>showToast(".json_encode($w).",'warning'));";
?>
</script>
</body>
</html>
