<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
?>

<div style="text-align: center; margin-top: 10px;">
  <div style="text-align: left; margin-left: auto; margin-right: auto;" class="max-width alert">
  RESTRICTED ACCESS: The IP address you are connecting from (<?php echo htmlspecialchars($_SERVER['REMOTE_ADDR']); ?>) is not allowed to access this function.
  </div>
</div>

</body>
</html>
