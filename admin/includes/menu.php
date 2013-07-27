<div id="infobar" class="noticebar"><div id="info"></div></div>

<div style="background-image: url(images/logo-bg.png); clear: left;">
  <div style="float: left"><a href="index.php"><img src="images/logo.png" border="0" width="85" height="28" alt="LinkX" /></a></div>
  <div id="logout">
  <a href="index.php?r=lxLogOut" onclick="return confirm('Are you sure you want to log out?')"><img src="images/logout.png" border="0" alg="Log Out"></a>
  </div>
  <div style="clear: both;"></div>
</div>

<div id="menu">
  <span class="topMenu">
    <a class="topMenuItem">Links</a>
    <div class="subMenu">
      <a href="index.php?r=lxShBrowse">Browse Directory</a>
      <a href="index.php?r=lxShSearchLinks">Search Links</a>
      <a href="index.php?r=lxShAddLink" class="window {title: 'Add a Link'}">Add a Link</a>
      <a href="index.php?r=lxShImportLinks">Import Links</a>
      <a href="index.php?r=lxShScanLinks">Scan Links</a>
    </div>
  </span>

  <span class="topMenu">
    <a class="topMenuItem">Accounts</a>
    <div class="subMenu">
      <a href="index.php?r=lxShUsers">Search Accounts</a>
      <a href="index.php?r=lxShAddUser" class="window {title: 'Add an Account'}">Add an Account</a>
    </div>
  </span>

  <span class="topMenu">
    <a class="topMenuItem">Templates</a>
    <div class="subMenu">
      <a href="index.php?r=lxShDirectoryTemplates">Directory Templates</a>
      <a href="index.php?r=lxShEmailTemplates">E-mail Templates</a>
      <a href="index.php?r=lxShRejections">Rejection E-mails</a>
      <a href="index.php?r=lxShLanguage">Language File</a>
      <a href="index.php?r=lxRecompileTemplates" class="window {title: 'Recompile Templates', height: 300}">Recompile Templates</a>
      <a href="index.php?r=lxClearTemplateCache" class="window {title: 'Recompile Templates', height: 300}">Clear Template Cache</a>
    </div>
  </span>

  <span class="topMenu">
    <a class="topMenuItem">To Do</a>
    <div class="subMenu">
      <a href="index.php?r=lxShSearchLinks&status=pending">Review New Links</a>
      <a href="index.php?r=lxShSearchLinks&is_edited=1">Review Edited Links</a>
      <a href="index.php?r=lxShUsers&status=pending">Review User Accounts</a>
      <a href="index.php?r=lxShSearchComments&status=pending">Review Comments</a>
      <a href="index.php?r=lxShReports">Review Reports</a>
    </div>
  </span>

  <span class="topMenu">
    <a class="topMenuItem">Database</a>
    <div class="subMenu">
      <a href="index.php?r=lxShDatabaseTools">Tools</a>
      <a href="index.php?r=lxShLinkFields">User Defined Link Fields</a>
      <a href="index.php?r=lxShUserFields">User Defined Account Fields</a>
    </div>
  </span>

  <span class="topMenu">
    <a class="topMenuItem">Settings</a>
    <div class="subMenu">
      <a href="index.php?r=lxShGeneralSettings" class="window {title: 'General Settings'}" id="_menu_gs">General Settings</a>
      <a href="index.php?r=lxShSearchTerms">Search Terms</a>
      <a href="index.php?r=lxShSearchComments">Manage Comments</a>
      <a href="index.php?r=lxShNews">Manage News Items</a>
      <a href="index.php?r=lxShAds">Manage Advertisements</a>
      <a href="index.php?r=lxShBlacklist">Manage Blacklist</a>
      <a href="index.php?r=lxShSearchCategories">Manage Categories</a>
      <a href="index.php?r=lxShReciprocals">Manage Recip Links</a>
      <a href="index.php?r=lxShAdministrators">Manage Administrators</a>
      <a href="index.php?r=lxShRewriteFile" class="window {title: 'mod_rewrite File'}" >mod_rewrite File</a>
      <a href="index.php?r=lxShPhpInfo">phpinfo() Function</a>
    </div>
  </span>
</div>

<?php if( empty($C['from_email']) ): ?>
<script language="JavaScript">
$(function() { $('#_menu_gs').trigger('click'); });
</script>
<?php endif; ?>
<?php if( file_exists("{$GLOBALS['BASE_DIR']}/admin/reset-access.php") ): ?>
<div class="alert centered">
  SECURITY RISK: Please remove the reset-access.php file from the admin directory of your LinkX installation immediately
</div>
<?php endif; ?>

<?php if( file_exists("{$GLOBALS['BASE_DIR']}/admin/install.php") ): ?>
<div class="alert centered">
  SECURITY RISK: Please remove the install.php file from the admin directory of your LinkX installation immediately
</div>
<?php endif; ?>

<?php if( file_exists("{$GLOBALS['BASE_DIR']}/admin/mysql-change.php") ): ?>
<div class="alert centered">
  SECURITY RISK: Please remove the mysql-change.php file from the admin directory of your LinkX installation immediately
</div>
<?php endif; ?>
