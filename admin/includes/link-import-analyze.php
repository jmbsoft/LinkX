<?php
if( !defined('LINKX') ) die("Access denied");

$defaults = array('status' => 'active',
                  'type' => 'regular');

if( !isset($_REQUEST['analyzed']) )
{
    $_REQUEST = array_merge($_REQUEST, $defaults);
}

// Get settings from last import
if( !is_array($_REQUEST['fields']) )
{
    $last_import = GetValue('last_import');

    if( $last_import != null )
    {
        $_REQUEST['fields'] = unserialize($last_import);
    }
}

$fields = explode('|', FileReadLine("{$GLOBALS['BASE_DIR']}/data/$filename"));
$user_fields =& GetUserLinkFields();

$jscripts = array('includes/calendar.js');
$csses = array('includes/calendar.css');

include_once('includes/header.php');
include_once('includes/menu.php');
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

<div id="main-content">
  <div id="centered-content" class="max-width">
    <div class="heading">
      <div class="heading-icon">
        <a href="docs/link-import.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Import Links
    </div>

    <?php if( $GLOBALS['message'] ): ?>
    <div class="notice margin-top">
      <?php echo $GLOBALS['message']; ?>
    </div>
    <?php endif; ?>

    <?php if( $GLOBALS['errstr'] ): ?>
    <div class="alert margin-top">
      <?php echo $GLOBALS['errstr']; ?>
    </div>
    <?php endif; ?>

    <form action="index.php" method="POST" onsubmit="return checkInput()">
    <div class="margin-top">
    Below is the analysis of your link import data.  Select the correct field type for each of the values extracted from the import data, select one or
    more categories for the links to be imported to, and then press the Import Links button.

    <fieldset style="padding-left: 10px">
      <legend>Import Data</legend>
        <table width="100%" border="0">
        <?php for( $i = 0; $i < count($fields); $i++ ): ?>
        <tr>
          <td width="150" valign="top">
            <select name="fields[<?php echo $i; ?>]">
            <?php
            $field_options = array('IGNORE' => 'IGNORE',
                                   'site_url' => 'Site URL',
                                   'recip_url' => 'Recip URL',
                                   'title' => 'Title',
                                   'description' => 'Description',
                                   'status' => 'Status',
                                   'type' => 'Type',
                                   'expires' => 'Expires',
                                   'name' => 'Name',
                                   'email' => 'E-mail Address',
                                   'submit_ip' => 'Submit IP',
                                   'keywords' => 'Keywords',
                                   'clicks' => 'Clicks',
                                   'weight' => 'Weight',
                                   'date_added' => 'Date Added',
                                   'date_modified' => 'Date Modified',
                                   'icons' => 'Icon HTML',
                                   'admin_comments' => 'Admin Comments',
                                   'categories' => 'Categories');

            foreach($user_fields as $user_field)
            {
                $field_options[$user_field['name']] = StringChop($user_field['label'], 25);
            }

            echo OptionTags($field_options, is_array($_REQUEST['fields']) ? $_REQUEST['fields'][$i] : null);
            ?>
          </select>
          </td>
          <td>
            <?php echo htmlspecialchars($fields[$i]); ?>
          </td>
        </tr>
        <?php endfor; ?>
        </table>
    </fieldset>

    <fieldset style="padding-left: 10px">
      <legend>Other Settings</legend>

      <div class="fieldgroup">
            <label for="category">Categories:</label>
            <div style="float: left; width: 500px; padding-bottom: 5px;">
            <img src="images/add-small.png" width="12" height="12" alt="Add" class="addimg" style="margin-top: 3px;" onclick="showSelectCat('category_id', 'checkbox')" />
            <img src="images/remove-small.png" width="12" height="12" alt="Delete" class="addimg" style="margin-top: 3px;" onclick="clearCat('category_id')" />
            <div id="category_id_div"></div>
            <input type="hidden" name="category_id" id="category_id" value="<?php echo $_REQUEST['category_id']; ?>" />
            </div>
        </div>

      <div class="fieldgroup">
        <label for="status">Status:</label>
        <select name="status">
        <?php
        $statuses = array('' => 'From Import Data',
                          'unconfirmed' => 'Unconfirmed',
                          'pending' => 'Pending',
                          'active' => 'Active',
                          'disabled' => 'Disabled');

        echo OptionTags($statuses, $_REQUEST['status']);
        ?>
        </select>
      </div>

      <div class="fieldgroup">
        <label for="type">Type:</label>
        <select name="type">
        <?php
        $types = array('' => 'From Import Data',
                       'regular' => 'Regular',
                       'premium' => 'Premium',
                       'featured' => 'Featured');

        echo OptionTags($types, $_REQUEST['type']);
        ?>
        </select>
        &nbsp;
        <input type="text" name="expires" id="expires" size="20" value="<?php echo htmlspecialchars($_REQUEST['expires']); ?>"  class="calendarSelectDate" />
      </div>

    </fieldset>

    <div class="centered margin-top">
    <button type="submit">Import Links</button>
    </div>
    </div>
    <input type="hidden" name="r" value="lxImportLinks">
    <input type="hidden" name="analyzed" value="1">
    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($filename); ?>">
    </form>

    <div class="page-end"></div>
  </div>
</div>

</body>
</html>
