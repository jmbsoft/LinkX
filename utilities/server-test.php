<?php
if( !defined('E_STRICT') ) define('E_STRICT', 2048);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

$server = GetServerCapabilities();
?>

<html>
<head>
  <title>LinkX Server Test Script</title>
  <style>
  body, td {
    font-family: Tahoma;
    font-size: 9pt;
  }

  .passed {
    font-weight: bold;
    color: green;
  }

  .failed {
    font-weight: bold;
    color: red;
  }

  .warn {
    font-weight: bold;
    color: orange;
  }

  a.vps {
    font-size: 110%;
    font-weight: bold;
    color: #a25dba;
    text-decoration: none;
  }

  a.vps > span {
    font-size: 110%;
    display: inline-block;
    width: 1000px;
    color: #a25dba;
    margin: 0px auto 15px;
    padding: 6px;
    text-align: center;
    background-color: #f6e7fd;
    border: 1px solid #c38dd4;
    -moz-border-radius: 5px;
    -moz-box-shadow: 0px 0px 6px #aaa;
    -webkit-border-radius: 5px;
    -webkit-box-shadow: 0px 0px 6px #aaa;
  }
  </style>
</head>
<body>

<div style="text-align: center">
<h2>LinkX Server Test Script</h2>

<a href="http://manage.aff.biz/z/155/CD3560/" target="_blank" class="vps">
<span>
VPS.net hosting offers a pre-configured setup that has all of the requirements for LinkX, with prices starting under $20/month<br />
You can be setup and running with a fully compatible and scalable system in a matter of minutes!!</a>
</span>
</a>

<br />

<table width="875" align="center" border="1" cellpadding="5" cellspacing="0">
  <tr bgcolor="#ececec">
    <td width="200" valign="top">
      <b>TEST</b>
    </td>
    <td width="200">
      <b>REQUIREMENT</b>
    </td>
    <td>
      <b>YOUR SERVER</b>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>Operating System</b>
    </td>
    <td valign="top" width="200">
      Unix or Linux
    </td>
    <td valign="top">
      <?php
      $uname = php_uname('s');
      $class = strpos($uname, 'Windows') === 0 ? 'failed' : 'passed';

      echo "<span class=\"$class\">" . $uname . " - " .
            ($class == 'failed' ? '' : 'looks ok') .
            "</span>";
      ?>
    </td>
  </tr>

  <tr>
    <td width="200" valign="top">
      <b>PHP Version</b>
    </td>
    <td valign="top" width="200">
      4.3.0+
    </td>
    <td valign="top">
      <?php
      list($a, $b, $c) = explode('.', PHP_VERSION);
      $class = $a > 4 || ($a == 4 && $b >= 3) ? 'passed' : 'failed';

      echo "<span class=\"$class\">" . PHP_VERSION . " - " .
           ($class == 'failed' ? 'PHP Version 4.3.0 or newer is required' : 'ok') .
           "</span>";
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>MySQL Extension</b>
    </td>
    <td width="200">
      Required
    </td>
    <td>
      <?php
      if( extension_loaded('mysql') )
      {
          echo "<div class=\"passed\">MySQL extension is installed - ok</div>";
      }
      else
      {
          echo "<span class=\"failed\">The MySQL extension is not installed; this software requires the MySQL extension and will not work without it</span>";
      }
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>MySQL Version</b>
    </td>
    <td valign="top" width="200">
      4.0.4+
    </td>
    <td valign="top">
      <?php

      if( $_POST['user'] && $_POST['host'] )
      {
          $dbh = @mysql_connect($_POST['host'], $_POST['user'], $_POST['pass']);

          if( $dbh )
          {
              $result = mysql_query('SELECT VERSION()');
              $row = mysql_fetch_row($result);
              $mysql_version = $row[0];

              preg_match('~^(\d+)\.(\d+)\.(\d+)~', $mysql_version, $matches);
              list($a, $b, $c) = array($matches[1], $matches[2], $matches[3]);

              if( $a > 4 || ($a == 4 && ($b > 0 || $c > 3)) )
              {
                  echo "<div class=\"passed\">MySQL version $mysql_version installed - ok</div>";
              }
              else
              {
                  echo "<div class=\"failed\">MySQL version $mysql_version installed<br />Version 4.0.0 or newer is required for this software</div>";
              }
          }
          else
          {
              echo "<div class=\"failed\">Could not connect to MySQL database server: " . mysql_error() . "</div>";
          }

          echo "<br />";
      }

      ?>

      Enter the following information to check the MySQL version:
      <br />
      <br />

      <form style="margin: 0; padding: 0;" method="POST" action="server-test.php">
      Username: <input type="text" name="user" value="<?php echo htmlspecialchars($_POST['user']); ?>" style="margin-left: 1px;"><br />
      Password: <input type="text" name="pass" value="<?php echo htmlspecialchars($_POST['pass']); ?>" style="margin-left: 4px;"><br />
      Hostname: <input type="text" name="host" value="<?php echo (isset($_POST['host']) ? htmlspecialchars($_POST['host']) : 'localhost'); ?>" style="margin-left: 0px;"><br />
      <input type="submit" value="Test MySQL" style="margin-left: 64px;">
      </form>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>GD Extension</b>
    </td>
    <td valign="top" width="200">
      Optional, but needed if you want to use the verification code feature of the software.
    </td>
    <td valign="top">
      <?php
      if( $server['have_gd'] )
      {
          $gd = gd_info();

          echo "<div class=\"passed\">GD extension version {$gd['GD Version']} is installed - ok</div>" .
               ($gd['FreeType Support'] ? "<div class=\"passed\">Freetype support is installed - ok</div>" : "<div class=\"failed\">Freetype support is not installed; use of TTF fonts will not be available</div>") .
               ($gd['JPG Support'] ? "<div class=\"passed\">JPEG support is installed - ok</div>" : "<div class=\"failed\">JPG support is not installed; will not be able to read/write JPEG images</div>") .
               ($gd['PNG Support'] ? "<div class=\"passed\">PNG support is installed - ok</div>" : "<div class=\"failed\">PNG support is not installed; will not be able to read/write PNG images</div>");
      }
      else
      {
          echo "<span class=\"failed\">The GD extension is not installed</span>";
      }
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>PHP safe_mode disabled</b>
    </td>
    <td valign="top" width="200">
      Optional, but some features will not be available or will be limited in their use.
    </td>
    <td valign="top">
      <?php
      if( $server['safe_mode'] )
      {
          echo "<span class=\"failed\">PHP safe_mode appears to be enabled</span>";
      }
      else
      {
          echo "<span class=\"passed\">PHP safe_mode is disabled - ok</span>";
      }
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>shell_exec() function available</b>
    </td>
    <td valign="top" width="200">
      Optional, but some features will not be available or will be limited in their use.
    </td>
    <td valign="top">
      <?php
      if( $server['allow_exec'] )
      {
          echo "<span class=\"passed\">shell_exec() function is available - ok</span>";
      }
      else
      {
          echo "<span class=\"failed\">The PHP shell_exec() function " .
               ($server['safe_mode'] ? "cannot be used because of the safe_mode setting" : "is disabled") .
               ".  This will prevent you from using the link scanner and may limit some of the other software functions</span>";
      }
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>PHP CLI version installed</b>
    </td>
    <td valign="top" width="200">
      Optional, but some features will not be available or will be limited in their use.
    </td>
    <td valign="top">
      <?php
      if( $server['php_cli'] )
      {
          echo "<span class=\"passed\">PHP CLI version is installed [{$server['php_cli']}] - ok</span>";
      }
      else
      {
          echo "<span class=\"failed\">The PHP CLI version ";

          if( $server['safe_mode'] ):
              echo "cannot be used because of the safe_mode setting.";
          elseif( !$server['allow_exec'] ):
              echo "cannot be used because the shell_exec() function is not available.";
          else:
              echo "could not be found.";
          endif;

          echo "  This will prevent you from using the link scanner function, cron, and may limit some of the other software functions</span>";
      }
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>cURL Extension</b>
    </td>
    <td valign="top" width="200">
      Optional, but offers some advanced features (https support, proxy support)
    </td>
    <td valign="top">
      <?php
      if( extension_loaded('curl') )
      {
          echo "<div class=\"passed\">cURL extension is installed - ok</div>";
      }
      else
      {
          echo "<span class=\"warn\">The cURL extension is not installed; the software can work without this extension but it does offer some enhanced capabilities</span>";
      }
      ?>
    </td>
  </tr>
  <tr>
    <td width="200" valign="top">
      <b>PHP open_basedir</b>
    </td>
    <td valign="top" width="200">
      Optional, but may be needed for new features in the future
    </td>
    <td valign="top">
      <?php
        ob_start();
        $open_basedir = ini_get('open_basedir');
        $buffer = ob_get_contents();
        ob_end_clean();

      if( empty($buffer) && empty($open_basedir) )
      {
          echo "<div class=\"passed\">There does not appear to be an open_basedir restriction in place - ok</div>";
      }
      else if( $buffer )
      {
          echo "<div class=\"warn\">The open_basedir setting could not be determined.  Contact your server administrator to get this information.</div>";
      }
      else
      {
          echo "<div class=\"warn\">The following open_basedir restriction is in effect:<br />$open_basedir</div>";
      }
      ?>
    </td>
  </tr>
</table>

</div>

</body>
</html>

<?php
function GetServerCapabilities()
{
    // Handle recursion issues with CGI version of PHP
    if( getenv('PHP_REPEAT') ) return;
    putenv('PHP_REPEAT=TRUE');

    $server = array('safe_mode' => TRUE,
                    'allow_exec' => FALSE,
                    'have_gd' => extension_loaded('gd'),
                    'have_magick' => FALSE,
                    'have_imager' => FALSE,
                    'php_cli' => null,
                    'mysql' => null,
                    'mysqldump' => null,
                    'convert' => null,
                    'composite' => null);

    // Check for safe mode
    ob_start();
    $safe_mode = ini_get('safe_mode');
    $buffer = ob_get_contents();
    ob_end_clean();

    if( !$safe_mode && empty($buffer) )
    {
        $server['safe_mode'] = FALSE;
    }

    if( !$server['safe_mode'] )
    {
        // Check if exec is available
        ob_start();
        shell_exec('ls -l');
        $buffer = ob_get_contents();
        ob_end_clean();

        if( empty($buffer) )
        {
            $server['allow_exec'] = TRUE;
        }

        if( $server['allow_exec'] )
        {
            // Check for cli version of PHP
            $server['php_cli'] = LocateExecutable('php', 'php -v', '(cli)');

            // Check for mysql executables
            $server['mysql'] = LocateExecutable('mysql');
            $server['mysqldump'] = LocateExecutable('mysqldump');

            // Check for imagemagick executables
            $server['convert'] = LocateExecutable('convert');
            $server['composite'] = LocateExecutable('composite');

            if( $server['convert'] && $server['composite'] )
            {
                $server['have_magick'] = TRUE;
            }
        }
    }

    $server['have_imager'] = $server['have_magick'] | $server['have_gd'];
    $server['can_exec'] = (!$server['safe_mode'] && $server['allow_exec']);

    if( $server['safe_mode'] )
    {
        $server['cant_exec_reason'] = 'PHP appears to be running in safe mode or a restricted operating mode';
    }
    else if( !$server['allow_exec'] )
    {
        $server['cant_exec_reason'] = 'the PHP shell_exec() function has been disabled by your server administrator';
    }
    else
    {
        $server['cant_exec_reason'] = 'The CLI version of PHP could not be found on your server';
    }

    return $server;
}


function LocateExecutable($executable, $output_cmd = null, $output_search = null)
{
    $executable_dirs = array('/bin', '/usr/bin', '/usr/local/bin', '/usr/local/mysql/bin', '/sbin', '/usr/sbin', '/usr/lib', '/usr/local/ImageMagick/bin', '/usr/X11R6/bin', realpath($_SERVER['DOCUMENT_ROOT'] . '/../bin/'));

    foreach( $executable_dirs as $dir )
    {
        if( is_file("$dir/$executable") && is_executable("$dir/$executable") )
        {
            if( $output_cmd )
            {
                $output = shell_exec("$dir/$output_cmd");

                if( stristr($output, $output_search) !== FALSE )
                {
                    return "$dir/$executable";
                }
            }
            else
            {
                return "$dir/$executable";
            }
        }
    }

    return null;
}

?>