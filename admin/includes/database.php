<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
include_once('includes/menu.php');
?>

<script language="JavaScript">
function executeQuery()
{
    if( confirm('Are you sure you want to execute this MySQL query?') )
    {
        infoBarAjax({data: 'r=lxRawQuery&' + $('#query').serialize()}, false);
    }

    return false;
}
</script>

<div id="main-content">
  <div id="centered-content" class="max-width">
    <div class="heading">
      <div class="heading-icon">
        <a href="index.php?r=lxDatabaseOptimize" class="window {title: 'Database Repair and Optimize', height: 300}">
        <img src="images/repair.png" border="0" alt="Repair and Optimize" title="Repair and Optimize"></a>
        &nbsp;
        <a href="docs/database-tools.html#backup" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Database Backup and Restore
    </div>

    <?php if( !$C['can_exec'] ): ?>
    <div class="warn margin-top">
      <a href="docs/database-tools.html#backup" target="_blank"><img src="images/help-small.gif" border="0" width="12" height="12"></a> The database backup and
      restore functions will have to be run through your browser because <?php echo $server['cant_exec_reason']; ?>.
      Depending on the size of your database and the PHP restrictions set on your server, it may not be possible to complete the backup or restore
      procedure in the amount of time that the script is given to run.  Please see the documentation for possible alternatives.
    </div>
    <?php else: ?>

    <div class="notice margin-top">
    <?php if( $C['allow_exec'] && !empty($C['mysqldump']) ): ?>
      Backup Method: mysqldump command line program<br />
    <?php elseif( $C['allow_exec'] && !empty($C['php_cli']) ): ?>
      Backup Method: Built-in LinkX function<br />
    <?php endif; ?>
    <?php if( $C['allow_exec'] && !empty($C['mysql']) ): ?>
      Restore Method: mysql command line program<br />
    <?php elseif( $C['allow_exec'] && !empty($C['php_cli']) ): ?>
      Restore Method: Built-in LinkX function<br />
    <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if( $GLOBALS['message'] ): ?>
    <div class="notice margin-top">
      <?php echo $GLOBALS['message']; ?>
    </div>
    <?php endif; ?>

    <form action="index.php" method="POST" onsubmit="return confirm('Are you sure you want to do this?')">

    <div class="centered margin-top" style="font-weight: bold">
      <b>Filename</b> &nbsp; <input type="text" name="filename" id="filename" size="30" value="backup.txt" /><br />
      <div style="margin-top: 8px;">
      <button type="submit" onclick="$('#r').val('lxBackupDatabase')">Backup</button>
      &nbsp;&nbsp;&nbsp;
      <button type="submit" onclick="$('#r').val('lxRestoreDatabase')">Restore</button>
      </div>
    </div>

    <input type="hidden" name="r" id="r" value="">
    </form>

    <br />

    <div class="heading">
      <div class="heading-icon">
        <a href="docs/database-tools.html#raw" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Raw Database Query
    </div>

    <div id="query_message_wrap" style="display: none; padding: 0; margin: 0;"><div id="query_message" style="display: block" class="notice"></div></div>

    <form id="query_form">

    <div class="centered margin-top" style="font-weight: bold">
      <b>Query</b> <input type="text" name="query" id="query" size="100" value="" onkeypress="return event.keyCode!=13" /> &nbsp;
      <button type="button" id="execute_button" onclick="executeQuery()">Execute</button>
    </div>

    <input type="hidden" name="r" value="lxRawQuery">
    </form>

    <div class="page-end"></div>
  </div>
</div>

</body>
</html>
