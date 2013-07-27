<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
include_once('includes/menu.php');
?>

<script language="JavaScript">
$(function() { Search.search(true); });

function multiFunction()
{
    $('#function option:selected').data().fn();
}

function selectChange(selector)
{
    if( $('#function option:selected').data().showmail )
    {
        $('#multi_email_selector').show();
    }
    else
    {
        $('#multi_email_selector').hide();
    }
}

function processNew(id, what)
{
    var email = '';
    if( id == null )
    {
        email = $('#multi_email').val();
    }
    else
    {
        email = $('#new_'+id+' select').val();
    }

    var selected = getSelected(id);

    if( selected.length < 1 )
    {
        alert('Please select at least one account to ' + what);
        return false;
    }

    if( confirm('Are you sure you want to ' + what + ' this account?') )
    {
        $.ajax({type: 'POST',
                url: 'ajax.php',
                data: 'r=lxNewUser&w='+what+'&'+selected.serialize()+'&email='+email});

        if( what == 'approve' )
        {
            $.each(selected, function(index, item)
                             {
                                 var id = $(item).val();
                                 $('#'+id).removeClass().addClass('active');
                                 $('#new_'+id).remove();
                             });
        }
        else
        {
            $.each(selected, function(index, item)
                             {
                                 var id = $(item).val();
                                 $('#'+id).remove();
                                 $('#_end_').html(decrementValue($('#_end_').html()));
                                 $('#_total_').html(decrementValue($('#_total_').html()));
                             });

            if( $('#_end_').html() == '0' )
                $('#_start_').html('0');
        }
    }

    return false;
}

function mailSelected(id)
{
    var selected = getSelected(id);

    if( selected.length < 1 )
    {
        alert('Please select at least one account to e-mail');
        return false;
    }

    $('#a_email_window').attr('href', 'index.php?r=lxShMailUser&'+selected.serialize()).trigger('click');

    return false;
}

function deleteSelected(id)
{
    var selected = getSelected(id);

    if( selected.length < 1 )
    {
        alert('Please select at least one account to delete');
        return false;
    }

    if( confirm('Are you sure you want to delete ' + (selected.length > 1 ? 'the selected accounts?' : 'this account?')) )
    {
        infoBarAjax({data: 'r=lxDeleteUser&' + selected.serialize()});
    }

    return false;
}

function statusSelected(id, what)
{
    var selected = getSelected(id);

    if( selected.length < 1 )
    {
        alert('Please select at least one account to '+what);
        return false;
    }

    if( confirm('Are you sure you want to ' + what + ' ' + (selected.length > 1 ? 'the selected accounts?' : 'this account?')) )
    {
        what = (what == 'suspend' ? 'suspended' : 'active');
        infoBarAjax({data: 'r=lxStatusUser&w=' + what + '&' + selected.serialize()});
    }

    return false;
}
</script>

<div id="main-content">
  <div id="centered-content" class="max-width">
    <div class="heading">
      <div class="heading-icon">
        <a href="index.php?r=lxShAddUser" class="window {title: 'Add Account'}">
        <img src="images/add.png" border="0" alt="Add Account" title="Add Account"></a>
        &nbsp;
        <a href="docs/user-search.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      User Accounts
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
      $fields = array('lx_users.username' => 'Username',
                      'email' => 'E-mail Address',
                      'name' => 'Name',
                      'num_links' => 'Links',
                      'weight' => 'Weight',
                      'date_added' => 'Date Added',
                      'date_modified' => 'Date Modified');

      echo OptionTags($fields, $_REQUEST['field']);
      ?>
      </select>
      <select name="search_type">
        <option value="matches">Matches</option>
        <option value="contains">Contains</option>
        <option value="starts">Starts With</option>
        <option value="less">Less Than</option>
        <option value="greater">Greater Than</option>
        <option value="between">Between</option>
        <option value="empty">Empty</option
      </select>
      <input type="text" name="search" value="<?php echo htmlspecialchars($_REQUEST['search']); ?>" size="40" onkeypress="return Search.onenter(event)" />
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
                              'suspended' => 'Suspended');

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
        <option value="email">E-mail Address</option>
        <option value="date_modified">Date Modified</option>
        <option value="num_links">Links</option>
        <option value="weight">Weight</option>
      </select>
      <select name="direction" id="direction">
        <option value="ASC">Ascending</option>
        <option value="DESC">Descending</option>
      </select>

      <b style="padding-left: 30px;">Per Page:</b>
      <input type="text" name="per_page" id="per_page" value="20" size="3">
      </td>
      <td align="right">
      <button type="button" onclick="Search.search(true)">Search</button>
      </td>
      </tr>
    </table>

    <input type="hidden" name="r" value="lxShSearchUsers">
    <input type="hidden" name="page" id="page" value="1">
    </form>

    <table width="100%" style="margin-top: 5px; margin-bottom: 2px;" border="0" cellspacing="3">
      <tr>
        <td>
        <div style="display: none;" id="_matches_">Accounts <b id="_start_">?</b> - <b id="_end_">?</b> of <b id="_total_">?</b></div>
        </td>
        <td align="right">
        <div id="_pagelinks_"></div>
        </td>
      </tr>
    </table>

    <form id="results">

    <table class="tall-list" cellspacing="0">
      <thead>
        <tr>
          <td style="width: 15px;">
            <input type="checkbox" id="_autocb_" class="checkbox">
          </td>
          <td>
            Account Information
          </td>
          <td class="last" style="width: 160px" align="right">
            Functions
          </td>
        </tr>
      </thead>
        <tr id="_activity_">
          <td colspan="3" class="last centered">
            <img src="images/activity.gif" border="0" width="16" height="16" alt="Working...">
          </td>
        </tr>
        <tr id="_none_" style="display: none;">
          <td colspan="3" class="last warn">
            No accounts matched your search criteria
          </td>
        </tr>
        <tr id="_error_" style="display: none;">
          <td colspan="3" class="last alert">
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

    <div class="centered">
      <select name="function" id="function" onchange="selectChange(this)">
        <option class="{fn: function() {mailSelected(null);}}">E-mail Selected</option>
        <option class="{fn: function() {deleteSelected(null);}}">Delete Selected</option>
        <option class="{fn: function() {processNew(null, 'approve');}, showmail: true}">Approve Selected</option>
        <option class="{fn: function() {processNew(null, 'reject');}, showmail: true}">Reject Selected</option>
        <option class="{fn: function() {statusSelected(null, 'suspend');}}">Suspend Selected</option>
        <option class="{fn: function() {statusSelected(null, 'activate');}}">Activate Selected</option>
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
        <td align="center" class="suspended" width="75" style="border: 1px solid #AAA">
        Suspended
        </td>
      </tr>
    </table>

    <div class="page-end"></div>
  </div>
</div>

<div id="_temp_div_"></div>

<table id="approver_table" style="display: none">
<tr id="approver_tr">
<td colspan="7" align="right" class="last">
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
<input type="hidden" name="approver_username" id="approver_username" value="">
</div>
</td>
</tr>
</table>

<a href="" style="display: none;" class="window {title: 'E-mail Accounts'}" id="a_email_window"></a>

</body>
</html>
