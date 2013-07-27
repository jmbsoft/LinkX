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
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/link-scan.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      <?php if( $editing ): ?>
      Update this scanner configuration by making changes to the information below
      <?php else: ?>
      Add a scanner configuration by filling out the information below
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
            <label for="identifier">Identifier:</label>
            <input type="text" name="identifier" id="identifier" size="60" value="<?php echo $_REQUEST['identifier']; ?>" />
        </div>

        </fieldset>

        <fieldset>
          <legend>Links To Scan</legend>

          <div class="fieldgroup">
            <label for="identifier">Link Status:</label>
            <div style="padding-top: 3px">
            <?php echo CheckBox('status[unconfirmed]', 'checkbox', 1, $_REQUEST['status']['unconfirmed']); ?> <label class="cblabel inline" for="status[unconfirmed]">Unconfirmed</label> &nbsp;
            <?php echo CheckBox('status[pending]', 'checkbox', 1, $_REQUEST['status']['pending']); ?> <label class="cblabel inline" for="status[pending]">Pending</label> &nbsp;
            <?php echo CheckBox('status[active]', 'checkbox', 1, $_REQUEST['status']['active']); ?> <label class="cblabel inline" for="status[active]">Active</label> &nbsp;
            <?php echo CheckBox('status[disabled]', 'checkbox', 1, $_REQUEST['status']['disabled']); ?> <label class="cblabel inline" for="status[disabled]">Disabled</label>
            </div>
          </div>

          <div class="fieldgroup">
            <label for="identifier">Link Type:</label>
            <div style="padding-top: 3px">
            <?php echo CheckBox('type[regular]', 'checkbox', 1, $_REQUEST['type']['regular']); ?> <label class="cblabel inline" for="type[regular]">Regular</label> &nbsp;
            <?php echo CheckBox('type[premium]', 'checkbox', 1, $_REQUEST['type']['premium']); ?> <label class="cblabel inline" for="type[premium]">Premium</label> &nbsp;
            <?php echo CheckBox('type[featured]', 'checkbox', 1, $_REQUEST['type']['featured']); ?> <label class="cblabel inline" for="type[featured]">Featured</label>
            </div>
          </div>

          <div class="fieldgroup">
            <label for="date_added_start">Link Added:</label>
            Between <input type="text" name="date_added_start" id="date_added_start" size="20" value="<?php echo $_REQUEST['date_added_start']; ?>" class="calendarSelectDate" /> and
            <input type="text" name="date_added_end" id="date_added_end" size="20" value="<?php echo $_REQUEST['date_added_end']; ?>" class="calendarSelectDate" />
          </div>

          <div class="fieldgroup">
            <label for="date_modified_start">Link Modified:</label>
            Between <input type="text" name="date_modified_start" id="date_modified_start" size="20" value="<?php echo $_REQUEST['date_modified_start']; ?>" class="calendarSelectDate" /> and
            <input type="text" name="date_modified_end" id="date_modified_end" size="20" value="<?php echo $_REQUEST['date_modified_end']; ?>" class="calendarSelectDate" />
          </div>

          <div class="fieldgroup">
            <label for="date_scanned_start">Link Scanned:</label>
            Between <input type="text" name="date_scanned_start" id="date_scanned_start" size="20" value="<?php echo $_REQUEST['date_scanned_start']; ?>" class="calendarSelectDate" /> and
            <input type="text" name="date_scanned_end" id="date_scanned_end" size="20" value="<?php echo $_REQUEST['date_scanned_end']; ?>" class="calendarSelectDate" />
          </div>

          <div class="fieldgroup">
            <label for="category">Categories:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('category_id', 'checkbox')" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('category_id')" />
            <div id="category_id_div"></div>
            <input type="hidden" name="category_id" id="category_id" value="<?php echo $_REQUEST['category_id']; ?>" />
            </div>
        </div>

        </fieldset>

        <fieldset>
          <legend>Processing Options</legend>

          <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="process_get_title" class="cblabel inline">
            <?php echo CheckBox('process_get_title', 'checkbox', 1, $_REQUEST['process_get_title']); ?> Extract value from the site's &lt;title&gt; tag and use for the Title value</label>
          </div>

          <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="process_get_description" class="cblabel inline">
            <?php echo CheckBox('process_get_description', 'checkbox', 1, $_REQUEST['process_get_description']); ?> Extract value from the site's &lt;meta&gt; description tag and use for the Description value</label>
          </div>

          <div class="fieldgroup">
            <label class="lesspad"></label>
            <label for="process_get_keywords" class="cblabel inline">
            <?php echo CheckBox('process_get_keywords', 'checkbox', 1, $_REQUEST['process_get_keywords']); ?> Extract value from the site's &lt;meta&gt; keywords tag and use for the Keywords value</label>
          </div>
        </fieldset>

        <fieldset>
          <legend>Actions</legend>

          <div class="fieldgroup">
          <label style="width: 250px;">Links with connection errors:</label>
          <select name="action_connect">
          <?php
          $actions = array('0x00000000' => 'Ignore',
                           '0x00000001' => 'Display in report only',
                           '0x00000002' => 'Change link status to disabled',
                           '0x00000004' => 'Delete link from database',
                           '0x00000008' => 'Delete link and blacklist');

          echo OptionTags($actions, $_REQUEST['action_connect']);
          ?>
          </select>
          </div>

          <div class="fieldgroup">
          <label style="width: 250px;">Links that are broken URLs:</label>
          <select name="action_broken">
          <?php
            echo OptionTags($actions, $_REQUEST['action_broken']);
          ?>
          </select>
          </div>

          <div class="fieldgroup">
          <label style="width: 250px;">Links that forward:</label>
          <select name="action_forward">
          <?php
            echo OptionTags($actions, $_REQUEST['action_forward']);
          ?>
          </select>
          </div>

          <div class="fieldgroup">
          <label style="width: 250px;">Links with blacklisted data:</label>
          <select name="action_blacklist">
          <?php
            echo OptionTags($actions, $_REQUEST['action_blacklist']);
          ?>
          </select>
          </div>

          <div class="fieldgroup">
          <label style="width: 250px;">Links with no reciprocal link:</label>
          <select name="action_norecip">
          <?php
            echo OptionTags($actions, $_REQUEST['action_norecip']);
          ?>
          </select>
          </div>

        </fieldset>

    <div class="centered margin-top">
      <button type="submit"><?php echo ($editing ? 'Update' : 'Add'); ?> Scanner Configuration</button>
    </div>

    <input type="hidden" name="r" value="<?php echo ($editing ? 'lxEditScannerConfig' : 'lxAddScannerConfig'); ?>">

    <?php if( $editing ): ?>
    <input type="hidden" name="config_id" value="<?php echo $_REQUEST['config_id']; ?>">
    <input type="hidden" name="editing" value="1">
    <?PHP endif; ?>
    </form>
</div>



</body>
</html>
