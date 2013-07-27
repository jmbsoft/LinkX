<?php
if( !defined('LINKX') ) die("Access denied");

$details_page = str_replace(array('.', '%d'), array('\\.', '([0-9]+)'), $C['page_details']);
$_SERVER['REQUEST_URI'] = preg_replace('~admin/index\.php.*$~', '', $_SERVER['REQUEST_URI']);

$htaccess = <<<HTACCESS
RewriteEngine On
RewriteBase {$_SERVER['REQUEST_URI']}
RewriteCond %{REQUEST_FILENAME} .*-$details_page
RewriteRule ^.*-$details_page details.php?id=\$1 [L,QSA]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)\$ index.php?c=\$1 [L,QSA]
HTACCESS;

define('NODTD', TRUE);
include_once('includes/header.php');
?>

<script language="JavaScript">
<?PHP if( $GLOBALS['added'] ): ?>
if( typeof window.parent.Search == 'object' )
    window.parent.Search.search(false);
<?PHP endif; ?>
</script>

<div style="padding: 10px;">
    <form action="index.php" method="POST" id="form">
    <div class="margin-bottom">
      <div style="float: right;">
        <a href="docs/mod_rewrite.html" target="_blank"><img src="images/help.png" border="0" alt="Help" title="Help"></a>
      </div>
      Create a plain text file named .htaccess and copy the text below into that file.  Place the file in the base directory of your LinkX installation.
    </div>

    <fieldset>
      <legend>.htaccess File Settings</legend>


      <textarea rows="10" cols="110" wrap="off"><?php echo htmlspecialchars($htaccess); ?></textarea>
    </fieldset>
</div>

</body>
</html>
