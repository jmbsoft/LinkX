<?php
if( !defined('LINKX') ) die("Access denied");

$jscripts = array('includes/calendar.js');
$csses = array('includes/calendar.css');
include_once('includes/header.php');
?>

<script language="JavaScript">
<?PHP if( $GLOBALS['added'] ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>
</script>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/news.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      <?php if( $editing ): ?>
      Update this news item by making changes to the information below
      <?php else: ?>
      Add a news item by filling out the information below
      <?php endif; ?>
    </div>

        <?php if( $GLOBALS['message'] ): ?>
        <div class="notice margin-bottom">
          <?php echo $GLOBALS['message']; ?>
        </div>
        <?php endif; ?>

        <?php if( $GLOBALS['errstr'] ): ?>
        <div class="alert margin-bottom">
          <?php echo $GLOBALS['errstr']; ?>
        </div>
        <?php endif; ?>

        <fieldset>
          <legend>General Settings</legend>

        <div class="fieldgroup">
            <label for="headline">Headline:</label>
            <input type="text" name="headline" id="headline" size="60" value="<?php echo $_REQUEST['headline']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="date_added">Date Added:</label>
            <input type="text" name="date_added" id="date_added" size="20" value="<?php echo $_REQUEST['date_added']; ?>" class="calendarSelectDate" />
        </div>

        <div class="fieldgroup">
            <label for="body">News Text:</label>
            <textarea type="text" name="body" id="body" rows="15" cols="80"><?php echo $_REQUEST['body']; ?></textarea>
        </div>

        </fieldset>

    <div class="centered margin-top">
      <button type="submit"><?php echo ($editing ? 'Update' : 'Add'); ?> News Item</button>
    </div>

    <input type="hidden" name="r" value="<?php echo ($editing ? 'lxEditNews' : 'lxAddNews'); ?>">

    <?php if( $editing ): ?>
    <input type="hidden" name="news_id" value="<?php echo $_REQUEST['news_id']; ?>">
    <input type="hidden" name="editing" value="1">
    <?PHP endif; ?>
    </form>
</div>



</body>
</html>
