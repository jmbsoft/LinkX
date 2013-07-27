<?php
if( !defined('LINKX') ) die("Access denied");

$fields_search = array('lx_links.link_id' => 'Link ID',
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
                       'ratings' => 'Number of Ratings',
                       'rating_avg' => 'Average Rating',
                       'weight' => 'Weight',
                       'date_added' => 'Date Added',
                       'date_modified' => 'Date Modified',
                       'date_scanned' => 'Date Scanned',
                       'icons' => 'Icon HTML',
                       'admin_comments' => 'Admin Comments',
                       'username' => 'Username');

$fields_update = array('site_url' => 'Site URL',
                       'recip_url' => 'Recip URL',
                       'title' => 'Title',
                       'description' => 'Description',
                       'name' => 'Name',
                       'email' => 'E-mail Address',
                       'submit_ip' => 'Submit IP',
                       'keywords' => 'Keywords',
                       'clicks' => 'Clicks',
                       'weight' => 'Weight',
                       'icons' => 'Icon HTML',
                       'admin_comments' => 'Admin Comments',
                       'username' => 'Username');


$result = $DB->Query('SELECT * FROM lx_link_field_defs ORDER BY field_id');
while( $field = $DB->NextRow($result) )
{
    $fields_search[$field['name']] = StringChop($field['label'], 27);
    $fields_update[$field['name']] = StringChop($field['label'], 27);
}
$DB->Free($result);

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<div style="padding: 10px;">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/link-tasks.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Use the functions below to perform quick updates on multiple links
    </div>

    <?php if( $GLOBALS['message'] ): ?>
    <div class="notice margin-bottom">
      <?php echo $GLOBALS['message']; ?>
    </div>
    <?php endif; ?>

    <?php if( $errors ): ?>
    <div class="alert margin-bottom">
      <?php echo $errors; ?>
    </div>
    <?php endif; ?>

    <form action="index.php" method="POST" id="form" onsubmit="return confirm('Are you sure you want to do this?');">
    <fieldset>
      <legend>Search and Replace</legend>

        <div class="fieldgroup">
            <label for="srfind">Find:</label>
            <textarea name="find" id="srfind" rows="2" cols="70"></textarea>
        </div>

        <div class="fieldgroup">
            <label for="srfield">In Field:</label>
            <select name="field" id="srfield">
            <?php echo OptionTags($fields_update, $_REQUEST['field']); ?>
            </select>
        </div>

        <div class="fieldgroup">
            <label for="srreplace">Replace With:</label>
            <textarea name="replace" id="srreplace" rows="2" cols="70"></textarea>
        </div>

        <?php if( $_REQUEST['category_id'] ): ?>
        <div class="fieldgroup">
            <label></label>
            <label for="sr_category_only" class="cblabel inline">
            <input type="checkbox" name="category_only" id="sr_category_only" value="1" class="checkbox"> Apply only to links in this category</label>
        </div>
        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($_REQUEST['category_id']); ?>">
        <?php endif; ?>

        <div style="text-align: right">
            <button type="submit">Search and Replace</button>&nbsp;
        </div>
    </fieldset>

    <input type="hidden" name="r" value="lxLinkSearchAndReplace">
    </form>


    <form action="index.php" method="POST" id="form" onsubmit="return confirm('Are you sure you want to do this?');">
    <fieldset>
      <legend>Search and Set</legend>


        <div class="fieldgroup">
            <label for="ssfield">Find:</label>
            <select name="field" id="ssfield">
            <?php echo OptionTags($fields_search, $_REQUEST['field']); ?>
            </select>
            <select id="search_type" name="search_type">
              <option value="contains">Contains</option>
              <option value="matches">Matches</option>
              <option value="starts">Starts With</option>
              <option value="less">Less Than</option>
              <option value="greater">Greater Than</option>
              <option value="between">Between</option>
              <option value="empty">Empty</option>
              <option value="any">Any Value</option>
            </select><br />
            <textarea name="find" id="find" rows="2" cols="70"></textarea>
        </div>

        <div class="fieldgroup">
            <label for="ssset_field">Set:</label>
            <select name="set_field" id="ssset_field">
            <?php echo OptionTags($fields_update, $_REQUEST['field']); ?>
            </select><br />
            <textarea name="set_to" id="set_to" rows="2" cols="70"></textarea>
        </div>

        <?php if( $_REQUEST['category_id'] ): ?>
        <div class="fieldgroup">
            <label></label>
            <label for="sscategory_only" class="cblabel inline">
            <input type="checkbox" name="category_only" id="sscategory_only" value="1" class="checkbox"> Apply only to links in this category</label>
        </div>
        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($_REQUEST['category_id']); ?>">
        <?php endif; ?>

        <div style="text-align: right">
            <button type="submit">Search and Set</button>&nbsp;
        </div>
    </fieldset>

    <input type="hidden" name="r" value="lxLinkSearchAndSet">
    </form>


    <form action="index.php" method="POST" id="form" onsubmit="return confirm('Are you sure you want to do this?');">
    <fieldset>
      <legend>Search and Delete</legend>

        <div class="fieldgroup">
            <label for="sdfield">Find:</label>
            <select name="field" id="sdfield">
            <?php echo OptionTags($fields_search, $_REQUEST['field']); ?>
            </select>

            <select id="search_type" name="search_type">
              <option value="contains">Contains</option>
              <option value="matches">Matches</option>
              <option value="starts">Starts With</option>
              <option value="less">Less Than</option>
              <option value="greater">Greater Than</option>
              <option value="between">Between</option>
              <option value="empty">Empty</option>
              <option value="any">Any Value</option>
            </select><br />
            <textarea name="find" id="find" rows="2" cols="70"></textarea>
        </div>

        <?php if( $_REQUEST['category_id'] ): ?>
        <div class="fieldgroup">
            <label></label>
            <label for="sdcategory_only" class="cblabel inline">
            <input type="checkbox" name="category_only" id="sdcategory_only" value="1" class="checkbox"> Apply only to links in this category</label>
        </div>
        <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($_REQUEST['category_id']); ?>">
        <?php endif; ?>

        <div style="text-align: right">
            <button type="submit">Search and Delete</button>&nbsp;
        </div>
    </fieldset>

    <input type="hidden" name="r" value="lxLinkSearchAndDelete">
    </form>

</div>



</body>
</html>
