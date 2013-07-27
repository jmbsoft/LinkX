<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
include_once('includes/menu.php');
?>

<script language="JavaScript">
$(function() { Search.search(true); });

function processReport(id, what)
{
    var selected = getSelected(id);
    
    if( selected.length < 1 )
    {
        alert('Please select at least one report to process');
        return false;
    }
    
    if( confirm('Are you sure you want to ' + what + ' ' + (selected.length > 1 ? 'the ' + (what != 'ignore' ? 'links associated with' : '') + ' selected reports?' : (what != 'ignore' ? 'the link associated with' : '') + 'this report?')) )
    {
        infoBarAjax({data: 'r=lxProcessReport&w=' + what + '&' + selected.serialize()});
    }
    
    return false;
}
</script>

<div id="main-content">
  <div id="centered-content" class="max-width">
         
    <div class="heading">
      <div class="heading-icon">
        <a href="docs/reports.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Link Reports
    </div>
    
    <form action="ajax.php" name="search" id="search" method="POST">
    
    <table align="center" cellpadding="3" cellspacing="0" class="margin-top" border="0">
      <tr>
      <td align="right">  
      <b>Search:</b>
      </td>
      <td colspan="2">     
      <select name="field">
        <option value="message">Message</option>
        <option value="lx_reports.date_added">Date Added</option>
        <option value="lx_reports.submit_ip">Submit IP</option>
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
      <input type="text" name="search" size="40" value="" onkeypress="return Search.onenter(event)" />
      </td>
      </tr>
      <tr>
      <td align="right">
      <b>Sort:</b>
      </td>
      <td> 
      <select name="order" id="order">
        <option value="lx_reports.date_added">Date Added</option>
        <option value="lx_reports.submit_ip">Submit IP</option> 
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
    
    <input type="hidden" name="r" value="lxShSearchReports">
    <input type="hidden" name="page" id="page" value="1">
    </form>
    
    <div style="padding: 0px 2px 5px 2px;">
      <div style="float: left; display: none;" id="_matches_">Reports <b id="_start_">?</b> - <b id="_end_">?</b> of <b id="_total_">?</b></div>
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
            Report Details
          </td>
          <td class="last" align="right" style="width: 140px">
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
            No reports matched your search criteria
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
      <select name="function" id="function">
        <option value="ignore">Ignore Selected</option>
        <option value="delete">Delete Selected</option>        
        <option value="blacklist">Blacklist Selected</option>
      </select>
      &nbsp;      
      <button type="button" onclick="processReport(null, $('#function').val())">Execute</button>
    </div>

    <div class="page-end"></div>
  </div>
</div>

<br />

<div id="_temp_div_"></div>

</body>
</html>
