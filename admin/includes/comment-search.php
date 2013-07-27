<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
include_once('includes/menu.php');
?>

<script language="JavaScript">
$(function() { Search.search(true); });

function multiFunction()
{
    switch($('#function').val())
    {
    case 'delete':
        deleteSelected();
        break;

    case 'approve':
        approveSelected(null);
        break;

    case 'reject':
        rejectSelected(null);
        break;
    }
}


function deleteSelected(id)
{
    var selected = getSelected(id);

    if( selected.length < 1 )
    {
        alert('Please select at least one comment to delete');
        return false;
    }

    if( confirm('Are you sure you want to delete ' + (selected.length > 1 ? 'the selected comments?' : 'this comment?')) )
    {
        infoBarAjax({data: 'r=lxDeleteComment&' + selected.serialize()});
    }

    return false;
}

function approveSelected(id)
{
    processSelected(id, 'approve');

    return false;
}

function rejectSelected(id)
{
    processSelected(id, 'reject');

    return false;
}

function processSelected(id, type)
{
    var selected = getSelected(id);

    if( selected.length < 1 )
    {
        alert('Please select at least one comment to ' + type);
        return false;
    }

    if( confirm('Are you sure you want to ' + type + ' ' + (selected.length > 1 ? 'the selected comments?' : 'this comment?')) )
    {
        infoBarAjax({data: 'r=lxNewComment&status=' + type + '&' + selected.serialize()});
    }

    return false;
}
</script>

<div id="main-content">
  <div id="centered-content" class="max-width">

    <div class="heading">
      <div class="heading-icon">
        <a href="docs/comments.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Search Comments
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
      $field_options = array('comment' => 'Comment Text',
                             'username' => 'Username',
                             'email' => 'E-mail Address',
                             'name' => 'Name',
                             'submit_ip' => 'Submitter IP',
                             'date_added' => 'Date Added',
                             'link_id' => 'Link ID',
                             'comment_id' => 'Comment ID');

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
                              'pending' => 'Pending',
                              'approved' => 'Approved');

      echo OptionTags($status_options, $_REQUEST['status']);
      ?>
      </select>
      </td>
      </tr>
      <tr>
      <td align="right">
      <b>Sort:</b>
      </td>
      <td>
      <select name="order" id="order">
        <option value="date_added">Date Added</option>
        <option value="username">Username</option>
        <option value="name">Name</option>
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

    <input type="hidden" name="r" value="lxShSearchComments">
    <input type="hidden" name="per_page" id="per_page" value="20">
    <input type="hidden" name="page" id="page" value="1">
    </form>

    <div style="padding: 0px 2px 5px 2px;">
      <div style="float: left; display: none;" id="_matches_">Comments <b id="_start_">?</b> - <b id="_end_">?</b> of <b id="_total_">?</b></div>
      <div id="_pagelinks_" style="float: right; line-height: 0px; padding: 2px 0px 0px 0px;">
      </div>
      <div class="clear"></div>
    </div>

    <form id="results">

    <table class="tall-list" cellspacing="0" cellpadding="3">
      <thead>
        <tr>
          <td style="width: 15px">
            <input type="checkbox" id="_autocb_" class="checkbox">
          </td>
          <td>
            Comment Details
          </td>
          <td class="last" style="width: 110px">
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
            No comments matched your search criteria
          </td>
        </tr>
        <tr id="_error_" style="display: none;">
          <td colspan="4" class="last alert">
          </td>
        </tr>
      <tbody id="_tbody_">
      </tbody>
    </table>

    </form>

    <div style="padding: 0px 2px 0px 2px;">
      <div id="_pagelinks_btm_" style="float: right; line-height: 0px; padding: 2px 0px 0px 0px;">
      </div>
      <div class="clear"></div>
    </div>

    <br />

    <div class="centered">
      <select name="function" id="function">
        <option value="delete">Delete Selected</option>
        <option value="approve">Approve Selected</option>
        <option value="reject">Reject Selected</option>
      </select>
      &nbsp;
      </span>
      <button type="button" onclick="multiFunction()">Execute</button>
    </div>

    <br />
    <br />

    <table align="center" border="0" cellspacing="3">
      <tr>
        <td align="center" class="pending" width="75" style="border: 1px solid #AAA">
        Pending
        </td>
        <td align="center" class="approved" width="75" style="border: 1px solid #AAA">
        Approved
        </td>
      </tr>
    </table>

    <div class="page-end"></div>
  </div>
</div>

<div id="_temp_div_"></div>

</body>
</html>
