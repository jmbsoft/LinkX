<?php
// Copyright 2011 JMB Software, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

if( !defined('LINKX') ) die("Access denied");

define('SESSION_LENGTH', 120);
define('ACCOUNT_EDITOR', 'editor');
define('ACCOUNT_ADMINISTRATOR', 'administrator');
define('JSON_SUCCESS', 'Success');
define('JSON_FAILURE', 'Failure');


// Privileges
define('P_CATEGORY_ADD',    0x00000001);
define('P_CATEGORY_MODIFY', 0x00000002);
define('P_CATEGORY_REMOVE', 0x00000004);
define('P_TYPE_ADD',        0x00000008);
define('P_TYPE_MODIFY',     0x00000010);
define('P_TYPE_REMOVE',     0x00000020);
define('P_LINK_ADD',        0x00000040);
define('P_LINK_MODIFY',     0x00000080);
define('P_LINK_REMOVE',     0x00000100);
define('P_COMMENT_ADD',     0x00000200);
define('P_COMMENT_MODIFY',  0x00000400);
define('P_COMMENT_REMOVE',  0x00000800);
define('P_USER_ADD',        0x00001000);
define('P_USER_MODIFY',     0x00002000);
define('P_USER_REMOVE',     0x00004000);
define('P_COMMENT',  P_COMMENT_ADD|P_COMMENT_MODIFY|P_COMMENT_REMOVE);
define('P_LINK',     P_LINK_ADD|P_LINK_MODIFY|P_LINK_REMOVE);
define('P_TYPE',     P_TYPE_ADD|P_TYPE_MODIFY|P_TYPE_REMOVE);
define('P_CATEGORY', P_CATEGORY_ADD|P_CATEGORY_MODIFY|P_CATEGORY_REMOVE);
define('P_USER',     P_USER_ADD|P_USER_MODIFY|P_USER_REMOVE);


// Default pagination values
$DEFAULT_PAGINATION = array('total' => 0, 'pages' => 0, 'page' => 1, 'limit' => 0, 'start' => 0, 'end' => 0, 'prev' => 0, 'next' => 0);

function GetCategoriesForImport(&$input)
{
    global $DB, $C;

    if( !isset($GLOBALS['CATEGORY_IMPORT_CACHE']) )
    {
        $GLOBALS['CATEGORY_IMPORT_CACHE'] = array();
    }

    $matches = array();
    $categories = explode('::', $input);

    foreach( $categories as $path )
    {
        $path = preg_replace('~^/|/$~', '', trim($path));

        if( empty($path) )
        {
            continue;
        }

        $path_hash = md5($path);

        // See if this category has already been found and cached
        if( isset($GLOBALS['CATEGORY_IMPORT_CACHE'][$path_hash]) )
        {
            $matches[] = $GLOBALS['CATEGORY_IMPORT_CACHE'][$path_hash];
        }

        // Lookup by mod_rewrite path
        else if( ($row = $DB->Row('SELECT `category_id` FROM `lx_categories` WHERE `path_hash`=?', array($path_hash))) !== FALSE )
        {
            $matches[] = $row['category_id'];
            $GLOBALS['CATEGORY_IMPORT_CACHE'][$path_hash] = $row['category_id'];
        }

        // Lookup by full path search
        else
        {
            $parts = strpos($path, '/') === FALSE ? array($path) : array_reverse(explode('/', $path));
            $parent = $GLOBALS['ROOT_CATEGORY'];
            $row = FALSE;

            while( ($part = array_pop($parts)) !== NULL )
            {
                $part = trim($part);

                $row = $DB->Row('SELECT `category_id` FROM `lx_categories` WHERE `name`=? AND `parent_id`=?', array($part, $parent['category_id']));

                if( !$row )
                {
                    break;
                }

                $parent = $row;
            }

            if( $row !== FALSE && count($parts) == 0 )
            {
                $matches[] = $row['category_id'];
                $GLOBALS['CATEGORY_IMPORT_CACHE'][$path_hash] = $row['category_id'];
            }
        }
    }

    return count($matches) > 0 ? array_unique($matches) : FALSE;
}

function RecompileTemplates()
{
    $t = new Template();
    $templates =& DirRead("{$GLOBALS['BASE_DIR']}/templates", '^(?!email)[^\.]+\.tpl$');

    // Compile global templates first
    foreach( glob("{$GLOBALS['BASE_DIR']}/templates/global-*.tpl") as $global_template )
    {
        $t->compile_template(basename($global_template));
    }

    foreach( $templates as $template )
    {
        if( strpos($template, 'global-') === FALSE )
        {
            $t->compile_template($template);
        }
    }
}

function GetValue($name)
{
    global $DB;

    $row = $DB->Row('SELECT * FROM lx_stored_values WHERE name=?', array($name));

    if( $row )
    {
        return $row['value'];
    }
    else
    {
        return null;
    }
}

function StoreValue($name, $value)
{
    global $DB;

    // See if it exists
    if( $DB->Count('SELECT COUNT(*) FROM lx_stored_values WHERE name=?', array($name)) )
    {
        $DB->Update('UPDATE lx_stored_values SET value=? WHERE name=?', array($value, $name));
    }
    else
    {
        $DB->Update('INSERT INTO lx_stored_values VALUES (?,?)', array($name, $value));
    }
}

function DoBackup($filename, &$tables)
{
    global $DB;

    $fd = fopen($filename, 'w');

    if( $fd )
    {
        foreach( array_keys($tables) as $table )
        {
            if( $table == 'lx_link_fields' || $table == 'lx_user_fields' )
            {
                $row = $DB->Row('SHOW CREATE TABLE #', array($table));
                $create = str_replace(array("\r", "\n"), '', $row['Create Table']);

                fwrite($fd, "DROP TABLE IF EXISTS `$table`;\n");
                fwrite($fd, "$create;\n");
            }

            fwrite($fd, "DELETE FROM `$table`;\n");
            fwrite($fd, "LOCK TABLES `$table` WRITE;\n");
            fwrite($fd, "ALTER TABLE `$table` DISABLE KEYS;\n");

            $result = mysql_unbuffered_query("SELECT * FROM $table", $DB->handle);
            while( $row = mysql_fetch_row($result) )
            {
                $row = array_map('mysql_real_escape_string', $row);
                fwrite($fd, "INSERT INTO `$table` VALUES ('" . join("','", $row) . "');\n");
            }
            $DB->Free($result);

            fwrite($fd, "UNLOCK TABLES;\n");
            fwrite($fd, "ALTER TABLE `$table` ENABLE KEYS;\n");
        }

        fclose($fd);

        @chmod($filename, 0666);
    }
}

function DoRestore($filename)
{
    global $DB;

    $fd = fopen($filename, 'r');

    if( $fd )
    {
        while( !feof($fd) )
        {
            $line = fgets($fd);

            // Remove trailing ; character
            $line = preg_replace('~;$~', '', $line);

            // Skip comments and empty lines
            if( empty($line) || preg_match('~^(/\*|--)~', $line) )
            {
                continue;
            }

            $DB->Update($line);
        }

        fclose($fd);
    }
}

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
                    'composite' => null,
                    'dig' => null);

    restore_error_handler();

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
        $open_basedir = ini_get('open_basedir');

        // Check if shell_exec is available
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
            $server['php_cli'] = LocateExecutable('php', '-v', '(cli)', $open_basedir);

            if( !$server['php_cli'] )
            {
                $server['php_cli'] = LocateExecutable('php-cli', '-v', '(cli)', $open_basedir);
            }

            // Check for mysql executables
            $server['mysql'] = LocateExecutable('mysql', null, null, $open_basedir);
            $server['mysqldump'] = LocateExecutable('mysqldump', null, null, $open_basedir);

            // Check for imagemagick executables
            $server['convert'] = LocateExecutable('convert', null, null, $open_basedir);
            $server['composite'] = LocateExecutable('composite', null, null, $open_basedir);

            // Check for dig
            $server['dig'] = LocateExecutable('dig', null, null, $open_basedir);

            if( $server['convert'] && $server['composite'] )
            {
                $server['have_magick'] = TRUE;
                $server['magick6'] = FALSE;

                // Get version
                $output = shell_exec("{$server['convert']} -version");

                if( preg_match('~ImageMagick ([^ ]+)~i', $output, $matches) )
                {
                    if( preg_match('~^6\.~', $matches[1]) )
                    {
                        $server['magick6'] = TRUE;
                    }
                }
            }
        }
    }

    set_error_handler('Error');

    $server['have_imager'] = $server['have_magick'] || $server['have_gd'];

    if( $server['safe_mode'] )
    {
        $server['cant_exec_reason'] = 'PHP appears to be running in safe mode or a restricted operating mode';
    }
    else if( !$server['allow_exec'] )
    {
        $server['cant_exec_reason'] = 'the PHP shell_exec() function has been disabled by your server administrator';
    }
    else if( empty($server['php_cli']) )
    {
        $server['cant_exec_reason'] = 'the CLI version of PHP could not be found on your server';
    }

    return $server;
}

function LocateExecutable($executable, $output_arg = null, $output_search = null, $open_basedir = FALSE)
{

    $executable_dirs = array('/bin',
                             '/usr/bin',
                             '/usr/local/bin',
                             '/usr/local/mysql/bin',
                             '/sbin',
                             '/usr/sbin',
                             '/usr/lib',
                             '/usr/local/ImageMagick/bin',
                             '/usr/X11R6/bin');

    if( isset($GLOBALS['BASE_DIR']) )
    {
        $executable_dirs[] = "{$GLOBALS['BASE_DIR']}/bin";
    }

    if( isset($_SERVER['DOCUMENT_ROOT']) )
    {
        $executable_dirs[] = realpath($_SERVER['DOCUMENT_ROOT'] . '/../bin/');
    }

    // No open_basedir restriction
    if( !$open_basedir )
    {
        foreach( $executable_dirs as $dir )
        {
            if( @is_file("$dir/$executable") && @is_executable("$dir/$executable") )
            {
                if( $output_arg )
                {
                    $output = shell_exec("$dir/$executable $output_arg");

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
    }

    $which = trim(shell_exec("which $executable"));

    if( !empty($which) )
    {
        if( $output_arg )
        {
            $output = shell_exec("$which $output_arg");

            if( stristr($output, $output_search) !== FALSE )
            {
                return $which;
            }
        }
        else
        {
            return $which;
        }
    }


    $whereis = trim(shell_exec("whereis -B ".join(' ', $executable_dirs)." -f $executable"));
    preg_match("~$executable: (.*)~", $whereis, $matches);
    $whereis = explode(' ', trim($matches[1]));

    if( count($whereis) )
    {
        if( $output_arg )
        {
            foreach( $whereis as $executable )
            {
                $output = shell_exec("$executable $output_arg");

                if( stristr($output, $output_search) !== FALSE )
                {
                    return $executable;
                }
            }
        }
        else
        {
            return $whereis[0];
        }
    }

    return null;
}

function CheckAccessList($ajax = FALSE)
{
    global $C, $allowed_ips;

    $ip = $_SERVER['REMOTE_ADDR'];
    $hostname = gethostbyaddr($ip);
    $found = FALSE;

    require_once("{$GLOBALS['BASE_DIR']}/includes/access-list.php");

    if( is_array($allowed_ips) )
    {
        if( count($allowed_ips) < 1 )
        {
            return;
        }

        foreach( $allowed_ips as $check_ip )
        {
            $check_ip = trim($check_ip);
            $check_ip = quotemeta($check_ip);

            // Setup the wildcard items
            $check_ip = preg_replace('/\\\\\*/', '.*?', $check_ip);
            $check_ip = preg_replace('/\\\\\*/', '\\*', $check_ip);

            if( preg_match("/^$check_ip$/", $ip) || preg_match("/^$check_ip$/", $hostname)  )
            {
                $found = TRUE;
                break;
            }
        }

        if( !$found )
        {
            if( $ajax )
            {
                $json = new JSON();
                echo $json->encode(array('status' => JSON_FAILURE,
                                         'message' => "The IP address you are connecting from ({$_SERVER['REMOTE_ADDR']}) is not allowed to access this function."));
            }
            else
            {
                include_once('no-access.php');
            }
            exit;
        }
    }
    else
    {
        $GLOBALS['no_access_list'] = TRUE;
    }
}

function CheckTemplateCode(&$code)
{
    $warnings = array();

    if( preg_match_all('~(\{\$.*?\})~', $code, $matches) )
    {
        foreach( $matches[1] as $match )
        {
            if( strpos($match, '$config.') )
            {
                continue;
            }

            if( !preg_match('~\|.*?\}~', $match) )
            {
                $warnings[] = "The template value $match is not escaped with htmlspecialchars and may pose a security risk";
            }
        }
    }

    return join('<br />', $warnings);
}

function ClearLinkDetailsCache($link_id)
{
    $t = new Template();
    $t->clear_cache('directory-link-details.tpl', md5($link_id));
}

function ClearCategoryCache($category_id, $path = null)
{
    global $DB;

    if( $path == null )
    {
        $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($category_id));
        $path = $category['path'];
    }

    $t = new Template();
    $t->clear_cache('directory-category.tpl', md5($category_id . '-1'));
    $t->clear_cache('directory-category.tpl', md5($path . '-1'));
    $t->clear_cache('directory-index.tpl');
}

function AutoBlacklist(&$link)
{
    global $DB;

    // Ban URL
    if( !$DB->Count('SELECT COUNT(*) FROM lx_blacklist WHERE type=? AND value=?', array('url', $link['site_url'])) )
    {
        $DB->Update('INSERT INTO lx_blacklist VALUES (?,?,?,?,?)', array(null, 'url', 0, $link['site_url'], ''));
    }

    // Ban IP
    if( !$DB->Count('SELECT COUNT(*) FROM lx_blacklist WHERE type=? AND value=?', array('submit_ip', $link['submit_ip'])) )
    {
        $DB->Update('INSERT INTO lx_blacklist VALUES (?,?,?,?,?)', array(null, 'submit_ip', 0, $link['submit_ip'], ''));
    }

    // Ban e-mail
    if( !$DB->Count('SELECT COUNT(*) FROM lx_blacklist WHERE type=? AND value=?', array('email', $link['email'])) )
    {
        $DB->Update('INSERT INTO lx_blacklist VALUES (?,?,?,?,?)', array(null, 'email', 0, $link['email'], ''));
    }
}

function GetCategoryIdList($link_id)
{
    global $DB;

    $categories = array();
    $result = $DB->Query('SELECT * FROM lx_link_cats WHERE link_id=?', array($link_id));

    while( $category = $DB->NextRow($result) )
    {
        $categories[] = $category['category_id'];
    }

    return join(',', $categories);
}

function AdminFormField(&$options)
{
    $options['tag_attributes'] = str_replace(array('&quot;', '&#039;'), array('"', "'"), $options['tag_attributes']);


    switch($options['type'])
    {
    case FT_CHECKBOX:
        $options['label'] = StringChop($options['label'], 70, true);
        if( preg_match('/value\s*=\s*["\']?([^\'"]+)\s?/i', $options['tag_attributes'], $matches) )
            $options['tag_attributes'] = 'class="checkbox" value="'.$matches[1].'"';
        else
            $options['tag_attributes'] = 'class="checkbox"';
        break;

    case FT_SELECT:
        $options['label'] = StringChop($options['label'], 20);
        $options['tag_attributes'] = '';
        break;

    case FT_TEXT:
        $options['label'] = StringChop($options['label'], 20);
        $options['tag_attributes'] = 'size="70"';
        break;

    case FT_TEXTAREA:
        $options['label'] = StringChop($options['label'], 20);
        $options['tag_attributes'] = 'rows="5" cols="80"';
        break;
    }
}

function StringChopTooltip($string, $length, $center = FALSE, $append = null)
{
    if( strlen($string) > $length )
    {
        $string = '<span title="'.$string.'" class="tt">' . StringChop($string, $length, $center, $append) . '</span>';
    }

    return $string;
}

function GetLinkedPath(&$category)
{
    global $DB;

    if( $category['category_id'] == 0 )
    {
        return 'Root';
    }

    $generated_path = '';
    $sections = array('<a href="index.php?r=lxShBrowse">Root</a>');
    $parts = unserialize($category['path_parts']);

    foreach( $parts as $part )
    {
        ArrayHSC($part);
        if( $part['category_id'] == $category['category_id'] )
            $sections[] = $part['name'];
        else
            $sections[] = "<a href=\"index.php?r=lxShBrowse&c={$part['category_id']}\">{$part['name']}</a>";
    }

    return join(' &raquo; ', $sections);
}

function UpdateSubcategoryCount($category_id)
{
    global $DB;

    $subcategories = $DB->Count('SELECT COUNT(*) FROM lx_categories WHERE parent_id=? AND hidden=0', array($category_id));

    $DB->Update('UPDATE lx_categories SET subcategories=? WHERE category_id=?', array($subcategories, $category_id));
}

function GetAllChildren($category_id, &$children)
{
    global $DB;

    $result = $DB->Query('SELECT * FROM lx_categories WHERE parent_id=?', array($category_id));

    while( $category = $DB->NextRow($result) )
    {
        $children[$category['category_id']] = 1;
        GetAllChildren($category['category_id'], $children);
    }

    $DB->Free($result);
}

function UpdateChildPaths($parent)
{
    global $DB;

    $result = $DB->Query('SELECT * FROM lx_categories WHERE parent_id=?', array($parent['category_id']));

    while( $subcategory = $DB->NextRow($result) )
    {
        $path = GeneratePathData($subcategory, $parent);

        $DB->Update('UPDATE lx_categories SET ' .
                    'path=?, ' .
                    'path_parts=?, ' .
                    'path_hash=? ' .
                    'WHERE category_id=?',
                    array($path['path'],
                          $path['serialized'],
                          $path['hash'],
                          $subcategory['category_id']));

        $subcategory['path'] = $path['path'];
        $subcategory['path_parts'] = $path['serialized'];
        $subcategory['hash'] = $path['hash'];

        ClearCategoryCache($subcategory['category_id'], $path['path']);
        UpdateChildPaths($subcategory);
    }

    $DB->Free($result);
}

function DeleteLink($link_id, $update_count = TRUE, $link = null)
{
    global $DB;

    if( $update_count )
    {
        $ids = GetCategoryIds($link_id);
    }

    if( $link == null )
    {
        $link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($link_id));
    }

    // Clear cache
    ClearLinkDetailsCache($link_id);

    $DB->Update('DELETE FROM lx_links WHERE link_id=?', array($link_id));
    $DB->Update('DELETE FROM lx_link_cats WHERE link_id=?', array($link_id));
    $DB->Update('DELETE FROM lx_link_fields WHERE link_id=?', array($link_id));
    $DB->Update('DELETE FROM lx_link_comments WHERE link_id=?', array($link_id));

    // If link was associated with an account, update account info
    if( $link['username'] )
    {
        $DB->Update('UPDATE lx_users SET num_links=num_links-1 WHERE username=?', array($link['username']));
    }

    // Update category link count
    if( $update_count )
    {
        foreach( $ids as $id )
        {
            if( $link['status'] == 'active' )
            {
                $DB->Update('UPDATE lx_categories SET links=links-1 WHERE category_id=?', array($id));
            }
        }
    }

    // TODO: Remove screenshot
}

function DeleteCategory($category_id, $category = null)
{
    global $DB, $ROOT_CATEGORY;

    if( $category == null )
    {
        $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($category_id));
    }

    // Remove cache files
    ClearCategoryCache($category['category_id'], $category['path']);
    ClearCategoryCache($category['parent_id']);

    // Delete all sub-categories
    $result = $DB->Query('SELECT * FROM lx_categories WHERE parent_id=?', array($category_id));
    while( $subcategory = $DB->NextRow($result) )
    {
        DeleteCategory($subcategory['category_id'], $subcategory);
    }
    $DB->Free($result);

    // Delete all links in this category
    $result = $DB->Query('SELECT * FROM lx_links JOIN lx_link_cats USING (link_id) WHERE category_id=?', array($category_id));
    while( $link = $DB->NextRow($result) )
    {
        DeleteLink($link['link_id'], FALSE, $link);
    }
    $DB->Free($result);

    // Delete this category
    $DB->Update('DELETE FROM lx_categories WHERE category_id=?', array($category_id));

    // Update subcategory count for parent category
    if( $category['parent_id'] != $ROOT_CATEGORY['parent_id'] )
    {
        UpdateSubcategoryCount($category['parent_id']);
    }
}

function PageLinks($data)
{
    $html = '';

    if( $data['prev'] )
    {
        $html .= ' <a href="javascript:void(0);" onclick="return Search.jump(1)"><img src="images/page-first.png" border="0" alt="First" title="First"></a> ' .
                 ' <a href="javascript:void(0);" onclick="return Search.go(-1)"><img src="images/page-prev.png" border="0" alt="Previous" title="Previous"></a> ';
    }
    else
    {
        $html .= ' <img src="images/page-first-disabled.png" border="0" alt="First" title="First"> ' .
                 ' <img src="images/page-prev-disabled.png" border="0" alt="Previous" title="Previous"> ';
    }

    if( $data['pages'] > 2 )
    {
        $html .= ' &nbsp; <input type="text" id="_pagenum_" value="' . $data['page'] . '" size="2" class="centered pagenum" onkeypress="return event.keyCode!=13" onkeyup="Search.jump(null, event)" /> of ' . $data['fpages'] . ' &nbsp; ';
    }

    if( $data['next'] )
    {
        $html .= ' <a href="javascript:void(0);" onclick="return Search.go(1)"><img src="images/page-next.png" border="0" alt="Next" title="Next"></a> ' .
                 ' <a href="javascript:void(0);" onclick="return Search.jump('. $data['pages'] .')">' .
                 '<img src="images/page-last.png" border="0" alt="Last" title="Last"></a> ';
    }
    else
    {
        $html .= ' <img src="images/page-next-disabled.png" border="0" alt="Next" title="Next"> ' .
                 ' <img src="images/page-last-disabled.png" border="0" alt="Last" title="Last"> ';
    }

    return $html;
}

function CheckBox($name, $class, $value, $checked, $flag = 0)
{
    $checked_code = '';

    if( ($value == $checked) || ($flag & $value) )
        $checked_code = ' checked="checked"';

    return "<input type=\"checkbox\" name=\"$name\" id=\"$name\" class=\"$class\" value=\"$value\"$checked_code />";
}

function ValidFunction($function)
{
    return (preg_match('/^lx[a-zA-Z0-9_]+/', $function) > 0 && function_exists($function));
}

function ValidLogin()
{
    global $DB;

    $error = 'Invalid username/password combination';

    if( isset($_POST['login_username']) && isset($_POST['login_password']) )
    {
        $administrator = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($_POST['login_username']));
        if( $administrator && $administrator['password'] == sha1($_POST['login_password']) )
        {
            $session = sha1(uniqid(rand(), true) . $_POST['login_password']);
            setcookie('linkx', 'username=' . urlencode($_POST['login_username']) . '&session=' . $session, time() + 86400);
            $DB->Update('UPDATE lx_administrators SET session=?, session_start=? WHERE username=?', array($session, time(), $administrator['username']));

            $_SERVER['REMOTE_USER'] = $administrator['username'];

            return TRUE;
        }
    }
    else if( isset($_COOKIE['linkx']) )
    {
        parse_str($_COOKIE['linkx'], $cookie);

        $administrator = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($cookie['username']));

        if( $administrator && $cookie['session'] == $administrator['session'] )
        {
            if( $administrator['session_start'] < time() - SESSION_LENGTH )
            {
                $session = sha1(uniqid(rand(), true) . $administrator['password']);
                setcookie('linkx', 'username=' . urlencode($administrator['username']) . '&session=' . $session, time() + 86400);
                $DB->Update('UPDATE lx_administrators SET session=?, session_start=? WHERE username=?', array($session, time(), $cookie['username']));
            }

            $_SERVER['REMOTE_USER'] = $administrator['username'];

            return TRUE;
        }
        else
        {
            $error = 'Session expired or invalid username/password';
        }
    }
    else
    {
        $error = '';
    }

    return $error;
}

function VerifyPrivileges($privilege, $ajax = FALSE)
{
    global $DB;

    $administrator = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($_SERVER['REMOTE_USER']));

    if( $administrator['type'] == ACCOUNT_ADMINISTRATOR )
    {
        return;
    }

    if( !($administrator['rights'] & $privilege) )
    {
        if( $ajax )
        {
            $json = new JSON();
            echo $json->encode(array('status' => JSON_FAILURE, 'message' => 'You do not have the necessary privileges to access this function'));
        }
        else
        {
            $error = 'You do not have the necessary privileges to access this function';
            include_once('includes/error.php');
        }
        exit;
    }
}

function VerifyAdministrator($ajax = FALSE)
{
    global $DB;

    $administrator = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($_SERVER['REMOTE_USER']));

    if( $administrator['type'] != ACCOUNT_ADMINISTRATOR )
    {
        if( $ajax )
        {
            $json = new JSON();
            echo $json->encode(array('status' => JSON_FAILURE, 'message' => 'This function is only available to administrator level accounts'));
        }
        else
        {
            $error = 'This function is only available to administrator level accounts';
            include_once('includes/error.php');
        }
        exit;
    }
}

function GenerateFlags(&$array, $pattern)
{
    $flags = 0x00000000;

    foreach($array as $name => $value)
    {
        if( preg_match("/$pattern/", $name) )
        {
            $flags = $flags | intval($value);
        }
    }

    return $flags;
}

function WriteConfig(&$settings)
{
    global $C;

    unset($settings['r']);

    $C = array_merge($C, $settings);

    $fd = fopen("{$GLOBALS['BASE_DIR']}/includes/config.php", "r+");

    fwrite($fd, "<?PHP\n\$C = array();\n");

    foreach($C as $setting => $value)
    {
        if( is_numeric($value) && $setting != 'db_password' )
        {
            fwrite($fd, "\$C['$setting'] = $value;\n");
        }
        else if( IsBool($value) )
        {
            $value = $value ? 'TRUE' : 'FALSE';
            fwrite($fd, "\$C['$setting'] = $value;\n");
        }
        else
        {
            fwrite($fd, "\$C['$setting'] = '" . addslashes($value) . "';\n");
        }
    }

    fwrite($fd, "?>");
    ftruncate($fd, ftell($fd));
    fclose($fd);
}

class PageParser
{
    var $parser;
    var $title;
    var $description;
    var $keywords;
    var $in_title;

    function PageParser()
    {
        $this->in_title = FALSE;
        $this->title = null;
        $this->keywords = null;
        $this->description = null;
    }

    function Cleanup()
    {
        $this->parser->Cleanup();

        unset($this->parser);
    }

    function parse($data)
    {
        $this->parser = new XML_HTMLSax();
        $this->parser->set_object($this);
        $this->parser->set_option('XML_OPTION_ENTIES_UNPARSED');
        $this->parser->set_option('XML_OPTION_FULL_ESCAPES');
        $this->parser->set_element_handler('tagOpen', 'tagClose');
        $this->parser->set_data_handler('tagContents');
        $this->parser->parse($data);
    }

    function tagContents(&$parser, $data)
    {
        if( $this->in_title && $this->title === null )
        {
            $this->title = $data;
            $this->in_title = FALSE;
        }
    }

    function tagOpen(&$parser, $name, $attrs)
    {
        global $C;

        foreach( $attrs as $key => $val )
        {
            $attrs[$key] = trim($val);
        }

        switch($name)
        {
            case 'title':
            {
                $this->in_title = TRUE;
            }
            break;

            case 'meta':
            {
                if( strtolower($attrs['name']) == 'description' && $this->description === null )
                {
                    $this->description = $attrs['content'];
                }
                else if( strtolower($attrs['name']) == 'keywords' && $this->keywords === null )
                {
                    $this->keywords = $attrs['content'];
                }
            }
            break;

        }
    }

    function tagClose(&$parser, $name)
    {
        switch($name)
        {
            case 'title':
            {
                $this->in_title = FALSE;
            }
            break;
        }
    }
}
?>
