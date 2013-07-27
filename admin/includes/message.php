<?php
if( !defined('LINKX') ) die("Access denied");

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<script language="JavaScript">
<?PHP if( $GLOBALS['added'] ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>
</script>

<div style="padding: 10px;">
    <div class="notice margin-bottom">
      <?php echo $message; ?>
    </div>
</div>

</body>
</html>