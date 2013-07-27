<?php
if( !defined('LINKX') ) die("Access denied");

if( $_REQUEST['field'] == 'link_id' )
    $_REQUEST['field'] = 'lx_links.link_id';

$jscripts = array('includes/link-search.js');
include_once('includes/header.php');
include_once('includes/menu.php');
?>

<script language="JavaScript">
$(function() { Search.search(true); });
</script>

<div id="main-content">
  <div id="centered-content" class="max-width">

    <div class="heading">
      <div class="heading-icon">
        <a href="index.php?r=lxShAddLink" class="window {title: 'Add a Link'}">
        <img src="images/add.png" border="0" alt="Add a Link" title="Add a Link"></a>
        &nbsp;
        <a href="index.php?r=lxShTasksLink&category_id=<?php echo $category['category_id']; ?>" class="window {title: 'Quick Tasks'}">
        <img src="images/tasks.png" border="0" alt="Quick Tasks" title="Quick Tasks"></a>
        &nbsp;
        <a href="docs/links.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Search Links
    </div>

    <form action="ajax.php" name="search" id="search" method="POST">

    <table align="center" cellpadding="3" cellspacing="0" class="margin-top">
      <tr>
      <td align="right">
      <b>Search:</b>
      </td>
      <td colspan="2">
      <select name="field">
      <?php
      $field_options = array('title,description,keywords' => 'Title, Description or Keywords',
                             'lx_links.link_id' => 'Link ID',
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

      $result = $DB->Query('SELECT * FROM lx_link_field_defs ORDER BY field_id');
      while( $field = $DB->NextRow($result) )
      {
          $field_options[$field['name']] = StringChop($field['label'], 27);
      }
      $DB->Free($result);

      echo OptionTags($field_options, $_REQUEST['field']);
      ?>
      </select>
      <select name="search_type">
        <option value="matches">Matches</option>
        <option value="contains">Contains</option>
        <option value="starts">Starts With</option>
        <option value="less">Less Than</option>
        <option value="greater">Greater Than</option>
        <option value="between">Between</option>
        <option value="empty">Empty</option>
      </select>
      <input type="text" name="search" size="30" value="<?PHP echo htmlspecialchars($_REQUEST['search']); ?>" onkeypress="return Search.onenter(event)" />
      </td>
      </tr>
      <tr>
      <td align="right">
      <b>Status:</b>
      </td>
      <td colspan="2">
      <select name="status">
      <?php
      $status_options = array('' => 'All',
                              'unconfirmed' => 'Unconfirmed',
                              'pending' => 'Pending',
                              'active' => 'Active',
                              'disabled' => 'Disabled');

      echo OptionTags($status_options, $_REQUEST['status']);
      ?>
      </select>

      <label for="is_edited" class="cblabel inline"><?php echo CheckBox('is_edited', 'checkbox', 1, $_REQUEST['is_edited']); ?> Edited</label>
      </td>
      </tr>
      <tr>
      <td align="right">
      <b>Sort:</b>
      </td>
      <td>
      <select name="order" id="order">
        <option value="date_added">Date Added</option>
        <option value="date_modified">Date Modified</option>
        <option value="date_scanned">Date Last Scanned</option>
        <option value="lx_links.link_id">Link ID</option>
        <option value="title">Title</option>
        <option value="expires">Expiration</option>
        <option value="name">Name</option>
        <option value="email">E-mail</option>
        <option value="clicks">Clicks</option>
        <option value="comments">Number of Comments</option>
        <option value="ratings">Number of Ratings</option>
        <option value="rating_avg">Average Rating</option>
        <option value="weight">Weight</option>
        <option value="site_url">Site URL</option>
      </select>
      <select name="direction" id="direction">
        <option value="ASC">Ascending</option>
        <option value="DESC">Descending</option>
      </select>
      </td>
      <td align="right">
      <button type="button" onclick="Search.search(true)">Search</button>
      </td>
      </tr>
    </table>

    <input type="hidden" name="r" value="lxShSearchLinks">
    <input type="hidden" name="per_page" id="per_page" value="20">
    <input type="hidden" name="page" id="page" value="1">
    </form>

    <div style="padding: 0px 2px 5px 2px;">
      <div style="float: left; display: none;" id="_matches_">Links <b id="_start_">?</b> - <b id="_end_">?</b> of <b id="_total_">?</b></div>
      <div id="_pagelinks_" style="float: right; line-height: 0px; padding: 2px 0px 0px 0px;">
      </div>
      <div class="clear"></div>
    </div>

    <form id="results">

    <table class="tall-list" cellspacing="0">
      <thead>
        <tr>
          <td style="width: 15px;">
            <input type="checkbox" id="_autocb_" class="checkbox">
          </td>
          <td>
            Link Data
          </td>
          <td class="last" align="right" style="width: 160px;">
            Functions
          </td>
        </tr>
      </thead>
        <tr id="_activity_">
          <td colspan="4" class="last centered">
            <img src="images/activity.gif" border="0" width="16" height="16" alt="Working...">
          </td>
        </tr>
        <tr id="_none_" style="display: none;">
          <td colspan="4" class="last warn">
            No links matched your search criteria
          </td>
        </tr>
        <tr id="_error_" style="display: none;">
          <td colspan="4" class="last alert">
          </td>
        </tr>
      <tbody id="_tbody_">
      </tbody>
    </table>

    <div style="padding: 0px 2px 0px 2px;">
      <div id="_pagelinks_btm_" style="float: right; line-height: 0px; padding: 2px 0px 0px 0px;">
      </div>
      <div class="clear"></div>
    </div>

    </form>

    <br />
    <br />

    <div class="centered">
      <select name="function" id="function" onchange="selectChange(this)">
        <option class="{fn: function() {mailSelected(null);}}">E-mail Selected</option>
        <option class="{fn: function() {deleteSelected(null);}}">Delete Selected</option>
        <option class="{fn: function() {processNew(null, 'approve');}, showmail: true}">Approve Selected</option>
        <option class="{fn: function() {processNew(null, 'reject');}, showmail: true}">Reject Selected</option>
        <option class="{fn: function() {statusSelected(null, 'disable');}}">Disable Selected</option>
        <option class="{fn: function() {statusSelected(null, 'activate');}}">Activate Selected</option>
        <option class="{fn: function() {blacklistSelected(null);}}">Blacklist Selected</option>
        <option class="{fn: function() {processEdit(null, 'approve');}}">Accept Edits of Selected</option>
        <option class="{fn: function() {processEdit(null, 'reject');}}">Reject Edits of Selected</option>
        <option class="{fn: function() {moveSelected(null)}}">Change Category of Selected</option>
      </select>
      &nbsp;
      <span id="multi_email_selector" style="display: none">
      <select name="multi_email" id="multi_email">
        <option value="">No E-mail</option>
        <option value="approval" selected="selected">Approval E-mail</option>
          <?php
          $options = '';
          $result = $DB->Query('SELECT * FROM lx_rejections ORDER BY identifier');
          while( $rejection = $DB->NextRow($result) )
          {
              $options .= "<option value=\"{$rejection['email_id']}\">Rejection: " . htmlspecialchars($rejection['identifier']) . "</option>\n";
          }
          $DB->Free($result);

          echo $options;
          ?>
      </select>
      &nbsp;
      </span>
      <button type="button" onclick="multiFunction()">Execute</button>
    </div>

    <br />

    <table align="center" border="0" cellspacing="3">
      <tr>
        <td align="center" class="unconfirmed" width="75" style="border: 1px solid #AAA">
        Unconfirmed
        </td>
        <td align="center" class="pending" width="75" style="border: 1px solid #AAA">
        Pending
        </td>
        <td align="center" class="active" width="75" style="border: 1px solid #AAA">
        Active
        </td>
        <td align="center" class="disabled" width="75" style="border: 1px solid #AAA">
        Disabled
        </td>
      </tr>
    </table>

    <div class="page-end"></div>
  </div>
</div>

<br />

<div id="_temp_div_"></div>

<table id="approver_table" style="display: none">
<tr id="approver_tr">
<td colspan="7" align="right" class="last" style="height: 1px; font-size: 1px; line-height: 0px">
<div style="display: none" id="approver">
<select name="approver_status" id="approver_status">
  <option value="approve">Approve</status>
  <option value="reject">Reject</status>
</select>

<select name="approver_email" id="approver_email">
  <option value="">No E-mail</option>
  <option value="approval" selected="selected">Approval E-mail</option>
<?php echo $options; ?>
</select> &nbsp;
<button type="button" onclick="processApproval(null)">Submit</button>
<input type="hidden" name="approver_link_id" id="approver_link_id" value="">
</div>
</td>
</tr>
</table>

<a href="" style="display: none;" class="window {title: 'Move Links'}" id="a_move_window"></a>
<a href="" style="display: none;" class="window {title: 'E-mail Link Submitters'}" id="a_email_window"></a>

</body>
</html>
