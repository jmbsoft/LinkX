<?php
if( !defined('LINKX') ) die("Access denied");

$length_options = array(PAY_ONETIME => 'One Time',
                        PAY_MONTH => 'Per Month',
                        PAY_QUARTER => 'Per Quarter',
                        PAY_YEAR => 'Per Year');

$status_options = array(CS_AUTO => 'Automatically accept new links',
                        CS_APPROVAL => 'Require approval of new links',
                        CS_LOCKED => 'Locked - Do not accept new links');

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<script language="JavaScript">
<?PHP if( $GLOBALS['added'] ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>

var popup = null;
$(function()
{
    if( $('#parent_id').val() != '' )
        updateSelected($('#parent_id').val(), '#parent_id');

    if( $('#related_ids').val() != '' )
        updateSelected($('#related_ids').val(), '#related_ids');

    if( $('#crosslink_id').val() != '' )
        updateSelected($('#crosslink_id').val(), '#crosslink_id');
});

</script>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/categories.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      <?php if( $editing ): ?>
      Update this category by making changes to the information below
      <?php else: ?>
      Add a new category by filling out the information below
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
          <legend>General Information</legend>

        <div class="fieldgroup">
            <?php if( $editing ): ?>
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" size="60" value="<?php echo $_REQUEST['name']; ?>" />
            <?php else: ?>
            <label for="name">Name(s):</label>
            <textarea name="name" id="name" rows="3" cols="80" wrap="off"><?php echo $_REQUEST['name']; ?></textarea>
            <?php endif; ?>
        </div>

        <div class="fieldgroup">
            <?php if( $editing ): ?>
            <label for="name">URL Name:</label>
            <input type="text" name="url_name" id="url_name" size="60" value="<?php echo $_REQUEST['url_name']; ?>" />
            <?php else: ?>
            <label for="name">URL Name(s):</label>
            <textarea name="url_name" id="url_name" rows="3" cols="80" wrap="off"><?php echo $_REQUEST['url_name']; ?></textarea>
            <?php endif; ?>
        </div>

        <div class="fieldgroup">
            <label for="name">Parent:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('parent_id', 'radio', true)" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('parent_id')" />
            <div id="parent_id_div"></div>
            <input type="hidden" name="parent_id" id="parent_id" value="<?php echo $_REQUEST['parent_id']; ?>" />
            </div>
        </div>

        <div class="fieldgroup">
            <label for="name">Related:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('related_ids', 'checkbox')" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('related_ids')" />
            <div id="related_ids_div"></div>
            <input type="hidden" name="related_ids" id="related_ids" value="<?php echo $_REQUEST['related_ids']; ?>" />
            </div>
        </div>

        <div class="fieldgroup">
            <label for="name">Crosslink:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('crosslink_id', 'radio')" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('crosslink_id')" />
            <div id="crosslink_id_div"></div>
            <input type="hidden" name="crosslink_id" id="crosslink_id" value="<?php echo $_REQUEST['crosslink_id']; ?>" />
            </div>
        </div>

        <div class="fieldgroup">
            <label for="template">Template:</label>
            <input type="text" name="template" id="template" size="30" value="<?php echo $_REQUEST['template']; ?>" />
        </div>

        <div class="fieldgroup">
            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="3" cols="80" wrap="off"><?php echo $_REQUEST['description']; ?></textarea>
        </div>

        <div class="fieldgroup">
            <label for="meta_description">Meta Description:</label>
            <textarea name="meta_description" id="meta_description" rows="3" cols="80" wrap="off"><?php echo $_REQUEST['meta_description']; ?></textarea>
        </div>

        <div class="fieldgroup">
            <label for="meta_keywords">Meta Keywords:</label>
            <textarea name="meta_keywords" id="meta_keywords" rows="3" cols="80" wrap="off"><?php echo $_REQUEST['meta_keywords']; ?></textarea>
        </div>

        <div class="fieldgroup">
            <label for="status">Status:</label>
            <select name="status">
              <?php echo OptionTags($status_options, $_REQUEST['status']); ?>
            </select>
        </div>

        <div class="fieldgroup">
            <label></label>
            <label for="hidden" class="cblabel inline"><?php echo CheckBox('hidden', 'checkbox', 1, $_REQUEST['hidden']); ?> Make this category hidden</label>
        </div>
        </fieldset>

    <div class="centered margin-top">
      <button type="submit"><?php echo ($editing ? 'Update' : 'Add'); ?> Category</button>
    </div>

    <input type="hidden" name="r" value="<?php echo ($editing ? 'lxEditCategory' : 'lxAddCategory'); ?>">

    <?php if( $editing ): ?>
    <input type="hidden" name="category_id" value="<?php echo $_REQUEST['category_id']; ?>" />
    <input type="hidden" name="editing" value="1">
    <?PHP endif; ?>
    </form>
</div>



</body>
</html>
