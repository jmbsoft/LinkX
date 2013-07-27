<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
?>

<script language="JavaScript">
var popup = null;

$(function()
{
    if( $('#category_id').val() != '' )
    {
        updateSelected($('#category_id').val(), '#category_id');
    }
});
</script>

<div style="padding: 10px;">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/links.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Move these links by selecting the categories below
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

    <form action="index.php" method="POST" id="form">
      <fieldset>
        <legend>General Information</legend>        

        <div class="fieldgroup">
            <label for="email">Links:</label>
            <div style="margin-top: 3px;">
            <?php echo join(', ', $_REQUEST['link_id']); ?>
            </div>
            <input type="hidden" name="link_id" id="link_id" value="<?php echo join(',', $_REQUEST['link_id']); ?>" />
        </div>
                
        <div class="fieldgroup">
            <label for="category">Categories:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('category_id', 'checkbox')" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('category_id')" />
            <div id="category_id_div"></div>            
            <input type="hidden" name="category_id" id="category_id" value="<?php echo htmlspecialchars($_REQUEST['category_id']); ?>" />
            </div>
        </div>        
      </fieldset>
      
      <div class="centered margin-top">
      <button type="submit">Move Links</button>
    </div>

    <input type="hidden" name="r" value="lxMoveLink">
    </form>
</div>

</body>
</html>
