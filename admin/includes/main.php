<?php
if( !defined('LINKX') ) die("Access denied");

include_once('includes/header.php');
include_once('includes/menu.php');
?>

<style>
.plain-link {
  font-weight: bold;
  text-decoration: none;
}

.divlabel {
  width: 180px;
  float: left;
  font-weight: bold;
  text-align: right;
  margin-right: 6px;
}

fieldset {

  padding-bottom: 10px;
  font-family: Tahoma;
  font-size: 8pt;
  border: 1px solid #999;
}

legend {
  color: #555;
  margin: 0px 0px 5px 0px;
  padding: 0px 5px;
  font-size: 8pt;
  font-weight: bold;
}
</style>

<div id="main-content">
  <div id="centered-content" class="max-width">

    <fieldset style="float: left; width: 48%; margin-right: 10px;">
      <legend>Overall Stats</legend>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Total Links</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_links'), 0, $C['dec_point'], $C['thousands_sep']); ?>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Total Categories</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_categories'), 0, $C['dec_point'], $C['thousands_sep']); ?>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Pending Links</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_links WHERE status="pending"'), 0, $C['dec_point'], $C['thousands_sep']); ?>
        &nbsp;
        <a href="index.php?r=lxShSearchLinks&status=pending"><img src="images/go.png" border="0" width="10" height="11"></a>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Edited Links</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_links WHERE is_edited=1'), 0, $C['dec_point'], $C['thousands_sep']); ?>
        &nbsp;
        <a href="index.php?r=lxShSearchLinks&is_edited=1"><img src="images/go.png" border="0" width="10" height="11"></a>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Pending Comments</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_link_comments WHERE status="pending"'), 0, $C['dec_point'], $C['thousands_sep']); ?>
        &nbsp;
        <a href="index.php?r=lxShSearchComments&status=pending"><img src="images/go.png" border="0" width="10" height="11"></a>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Pending Users</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_users WHERE status="pending"'), 0, $C['dec_point'], $C['thousands_sep']); ?>
        &nbsp;
        <a href="index.php?r=lxShUsers&status=pending"><img src="images/go.png" border="0" width="10" height="11"></a>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Bad Link Reports</div>
        <?php echo number_format($DB->Count('SELECT COUNT(*) FROM lx_reports'), 0, $C['dec_point'], $C['thousands_sep']); ?>
        &nbsp;
        <a href="index.php?r=lxShReports"><img src="images/go.png" border="0" width="10" height="11"></a>
      </div>
    </fieldset>

    <fieldset>
      <legend>Software Information</legend>

      <div style="margin-bottom: 6px;">
      <div class="divlabel">Last Backup</div>
      <?php
      $last_backup = GetValue('last_backup');

      echo empty($last_backup) ? '-' : date(DF_SHORT, $last_backup);
      ?>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Version</div>
        <?php echo $GLOBALS['VERSION']; ?>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">Release Date</div>
        <?php echo $GLOBALS['RELEASE']; ?>
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">&nbsp;</div>
        &nbsp;
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">&nbsp;</div>
        &nbsp;
      </div>

      <div style="margin-bottom: 6px;">
        <div class="divlabel">&nbsp;</div>
        &nbsp;
      </div>

      <table width="100%">
        <tr>
          <td align="center" width="50%">
            <a href="docs/" target="_blank" class="plain-link">Documentation</a>
          </td>
          <td align="center" width="50%">
            <a href="http://www.jmbsoft.com/support/" target="_blank" class="plain-link">Tech Support</a>
          </td>
        </tr>
      </table>
    </fieldset>

    <fieldset style="margin-top: 10px">
      <legend>JMB Software News and Updates</legend>

      <iframe src="http://www.jmbsoft.com/software/linkx/news/" style="width: 95%; margin-left: 10px; margin-right: 10px;" frameborder="0"></iframe>
    </fieldset>

    <div style="clear: both;"></div>
    <div class="page-end" style="margin-top: 10px;"></div>
  </div>
</div>

</body>
</html>
