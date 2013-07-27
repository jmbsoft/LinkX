<?PHP
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


// Globals
$GLOBALS['VERSION'] = '1.1.0-SS';
$GLOBALS['RELEASE'] = 'April 17, 2010 07:02';
$GLOBALS['FILE_PERMISSIONS'] = 0666;
$GLOBALS['BASE_DIR'] = realpath(dirname(__FILE__) . '/..');
$GLOBALS['ADMIN_DIR'] = "$BASE_DIR/admin";
$GLOBALS['ROOT_CATEGORY'] = array('parent_id' => -1, 'category_id' => 0, 'path' => '', 'path_parts' => serialize(array()), 'path_hash' => md5(''), 'name' => 'Root');
$GLOBALS['L'] = array();


// Setup error reporting
if( !defined('E_STRICT') ) define('E_STRICT', 2048);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
set_error_handler('Error');
@set_magic_quotes_runtime(0);
if( function_exists('date_default_timezone_set') )
{
    date_default_timezone_set('America/Chicago');
}
register_shutdown_function('Shutdown');


// Notifications
define('E_LINK_ADD',  0x00000001);
define('E_LINK_EDIT', 0x00000002);
define('E_PAYMENT',   0x00000004);
define('E_COMMENT',   0x00000008);


// Payment lengths
define('PAY_ONETIME', 0);
define('PAY_MONTH', 1);
define('PAY_QUARTER', 2);
define('PAY_YEAR', 3);


// Category status
define('CS_AUTO', 'auto');
define('CS_APPROVAL', 'approval');
define('CS_LOCKED', 'locked');


// Field types
define('FT_CHECKBOX', 'Checkbox');
define('FT_TEXTAREA', 'Textarea');
define('FT_TEXT', 'Text');
define('FT_SELECT', 'Select');


// Date formats
define('DF_DATETIME', 'Y-m-d H:i:s');
define('DF_DATE', 'Y-m-d');
define('DF_SHORT', 'm-d-Y h:ia');

// Mail types
define('MT_PHP', 0);
define('MT_SENDMAIL', 1);
define('MT_SMTP', 2);


// Search types
define('ST_CONTAINS', 'contains');
define('ST_MATCHES', 'matches');
define('ST_STARTS', 'starts');
define('ST_BETWEEN', 'between');
define('ST_GREATER', 'greater');
define('ST_GREATER_EQ', 'greatereq');
define('ST_LESS', 'less');
define('ST_LESS_EQ', 'lesseq');
define('ST_EMPTY', 'empty');
define('ST_ANY', 'any');
define('ST_IN', 'in');
define('ST_NOT_IN', 'not_in');
define('ST_NOT_EMPTY', 'not_empty');
define('ST_NULL', 'null');
define('ST_NOT_MATCHES', 'not_matches');
define('ST_NOT_NULL', 'not_null');


// Blacklist types
$GLOBALS['BLIST_TYPES'] = array('submit_ip' => 'Submitter IP',
                                'email' => 'E-mail Address',
                                'url' => 'Domain/URL',
                                'domain_ip' => 'Domain IP',
                                'word' => 'Word',
                                'html' => 'HTML',
                                'headers' => 'HTTP Headers',
                                'dns' => 'DNS Server');


// Load the language file
if( file_exists("{$GLOBALS['BASE_DIR']}/includes/language.php") )
{
    require_once("{$GLOBALS['BASE_DIR']}/includes/language.php");
}


// Load variables
if( file_exists("{$GLOBALS['BASE_DIR']}/includes/config.php") )
{
    require_once("{$GLOBALS['BASE_DIR']}/includes/config.php");
}


// Other
define('DEF_EXPIRES', '1999-01-01 00:00:00');
define('MYSQL_EXPIRES', '2000-01-01 00:00:00');
define('MYSQL_NOW', gmdate(DF_DATETIME, TimeWithTz()));
define('MYSQL_CURDATE', gmdate(DF_DATE, TimeWithTz()));
define('TIME_NOW', TimeWithTz());
define('DATE_EMPTY', '0000-00-00 00:00:00');
define('RE_DATETIME', '~^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$~');

function NullIfEmpty(&$string)
{
    if( IsEmptyString($string) )
    {
        $string = null;
    }
}

function &ScanLink(&$link)
{
    global $DB, $C, $L;

    $result = array('has_recip' => 0, 'site_url' => array(), 'recip_url' => null);

    if( !class_exists('http') )
    {
        require_once("{$GLOBALS['BASE_DIR']}/includes/http.class.php");
    }

    $http = new Http();

    // Check site URL
    $result['site_url']['working'] = $http->Get($link['site_url'], $link['allow_redirect']);
    $result['site_url']['error'] = $http->errstr;
    $result['site_url']['status'] = $http->response_headers['status'];
    $result['site_url']['ip_address'] = IPFromUrl($link['site_url']);
    $result['site_url']['html'] = $http->body;
    $result['site_url']['has_recip'] = HasReciprocal($http->body);

    // Check recip URL, if provided
    if( $link['recip_url'] )
    {
        $http = new Http();

        $result['recip_url'] = array();
        $result['recip_url']['working'] = $http->Get($link['recip_url'], $link['allow_redirect']);
        $result['recip_url']['error'] = $http->errstr;
        $result['recip_url']['status'] = $http->response_headers['status'];
        $result['recip_url']['ip_address'] = IPFromUrl($link['recip_url']);
        $result['recip_url']['html'] = $http->body;
        $result['recip_url']['has_recip'] = HasReciprocal($http->body);
    }

    $result['has_recip'] = $result['site_url']['has_recip'] || $result['recip_url']['has_recip'];

    return $result;
}

function HasReciprocal($html)
{
    global $DB, $C, $RECIP_CACHE;

    $has_recip = 0;

    // Prepare HTML code for scanning
	$html = preg_replace(array('/[\r\n]/', '/\s+/'), ' ', $html);

    // Load reciprocal links, if not previously cached
    if( !is_array($RECIP_CACHE) )
    {
        $RECIP_CACHE = array();
        $result = $DB->Query('SELECT * FROM lx_reciprocals');
        while( $recip = $DB->NextRow($result) )
        {
            $RECIP_CACHE[] = preg_replace(array('/[\r\n]/', '/\s+/'), ' ', $recip);
        }
        $DB->Free($result);
    }

    foreach( $RECIP_CACHE as $recip )
    {
        if( !$recip['regex'] )
        {
            $recip['code'] = quotemeta($recip['code']);
        }

        if( preg_match("~{$recip['code']}~", $html) )
        {
            $has_recip = 1;
            break;
        }
    }

    return $has_recip;
}

function RandomPassword()
{
    $chars = array_merge(range('a', 'z'), range('A', 'Z'));
    $numbers = range(0, 9);
    $number_locations = array(rand(0, 7), rand(0, 7));
    $password = '';

    for( $i = 0; $i < 8; $i++ )
    {
        if( in_array($i, $number_locations) )
        {
            $password .= $numbers[array_rand($numbers)];
        }
        else
        {
            $password .= $chars[array_rand($chars)];
        }
    }

    return $password;
}

function SendMail($to, $template, &$t, $is_file = TRUE)
{
    global $C;

    if( !class_exists('mailer') )
    {
        require_once("{$GLOBALS['BASE_DIR']}/includes/mailer.class.php");
    }

    $m = new Mailer();
    $m->mailer = $C['email_type'];
    $m->from = $C['from_email'];
    $m->from_name = $C['from_email_name'];
    $m->to = $to;

    switch($C['email_type'])
    {
        case MT_PHP:
            break;

        case MT_SENDMAIL:
            $m->sendmail = $C['mailer'];
            break;

        case MT_SMTP:
            $m->host = $C['mailer'];
            break;
    }

    if( $is_file )
    {
        $template = file_get_contents("{$GLOBALS['BASE_DIR']}/templates/$template");
    }

    $message_parts = array();
    $parsed_template = $t->parse($template);
    IniParse($parsed_template, FALSE, $message_parts);

    $m->subject = $message_parts['subject'];
    $m->text_body = $message_parts['plain'];
    $m->html_body = $message_parts['html'];

    return $m->Send();
}

function IsEmptyString(&$string)
{
    if( preg_match("/^\s*$/s", $string) )
    {
        return TRUE;
    }

    return FALSE;
}

function &GetUserAccountFields($account_data = null)
{
    global $DB;

    if( $account_data == null )
    {
        $account_data = $_REQUEST;
    }

    $fields = array();
    $result = $DB->Query('SELECT * FROM lx_user_field_defs');
    while( $field = $DB->NextRow($result) )
    {
        if( isset($account_data[$field['name']]) )
        {
            $field['value'] = $account_data[$field['name']];
        }
        $fields[] = $field;
    }
    $DB->Free($result);

    return $fields;
}

function &GetUserLinkFields($link_data = null)
{
    global $DB;

    if( $link_data == null )
    {
        $link_data = $_REQUEST;
    }

    $fields = array();
    $result = $DB->Query('SELECT * FROM lx_link_field_defs');
    while( $field = $DB->NextRow($result) )
    {
        if( isset($link_data[$field['name']]) )
        {
            $field['value'] = $link_data[$field['name']];
        }
        $fields[] = $field;
    }
    $DB->Free($result);

    return $fields;
}

function TimeWithTz($timestamp = null)
{
    global $C;

    $timezone = $C['timezone'];

    if( $timestamp == null )
    {
        $timestamp = time();
    }

    if( date('I', $timestamp) )
    {
        $timezone++;
    }

    return $timestamp + 3600 * $timezone;
}

function UpdateAccountLinkCount($username)
{
    global $DB;

    $links = $DB->Count('SELECT COUNT(*) FROM lx_links WHERE username=?', array($username));
    $DB->Update('UPDATE lx_users SET num_links=? WHERE username=?', array($links, $username));
}

function UpdateLinkCount($category_id)
{
    global $DB;

    $links = $DB->Count('SELECT COUNT(*) FROM lx_links JOIN lx_link_cats USING (link_id) WHERE category_id=? AND status=?', array($category_id, 'active'));

    $DB->Update('UPDATE lx_categories SET links=? WHERE category_id=?', array($links, $category_id));
}

function UnsetArray(&$array)
{
    $array = array();
}

function ArrayHSC(&$array)
{
    if( !is_array($array) )
        return;

    foreach($array as $key => $value)
    {
        if( is_array($array[$key]) )
        {
            ArrayHSC($array[$key]);
        }
        else
        {
            $array[$key] = htmlspecialchars($array[$key], ENT_QUOTES);
        }
    }
}

function IniWrite($filename, &$hash, $keys = null)
{
    if( $keys == null )
        $keys = array_keys($hash);

    $data = '';

    foreach( $keys as $key )
    {
        UnixFormat($hash[$key]);

        $data .= "=>[$key]\n" .
                 trim($hash[$key]) . "\n";
    }

    if( $filename != null )
        FileWrite($filename, $data);
    else
        return $data;
}

function IniParse($string, $isfile = TRUE, &$hash)
{
    if( $hash == null )
        $hash = array();

    if( $isfile )
        $string = file_get_contents($string);

    UnixFormat($string);

    foreach(explode("\n", $string) as $line)
    {
        if( preg_match("/^=>\[(.*?)\]$/", $line, $submatch) )
        {
            if( isset($key) )
            {
                $hash[$key] = trim($hash[$key]);
            }

            $key = $submatch[1];
            $hash[$key] = '';
        }
        else
        {
            $hash[$key] .= "$line\n";
        }
    }

    if( isset($key) )
    {
        $hash[$key] = rtrim($hash[$key]);
    }
}

function StringChop($string, $length, $center = false, $append = null)
{
	// Set the default append string
	if ($append === null) {
		$append = ($center === true) ? ' ... ' : '...';
	}

	// Get some measurements
	$len_string = strlen($string);
	$len_append = strlen($append);

	// If the string is longer than the maximum length, we need to chop it
	if ($len_string > $length) {
		// Check if we want to chop it in half
		if ($center === true) {
			// Get the lengths of each segment
			$len_start = $length / 2;
			$len_end = $len_string - $len_start;

			// Get each segment
			$seg_start = substr($string, 0, $len_start);
			$seg_end = substr($string, $len_end);

			// Stick them together
			$string = trim($seg_start) . $append . trim($seg_end);
		} else {
            // Otherwise, just chop the end off
			$string = trim(substr($string, 0, $length - $len_append)) . $append;
		}
	}

	return $string;
}

function FormatCommaSeparated($string)
{
    if( strlen($string) < 1 || strstr($string, ',') === FALSE )
        return $string;

    $items = array();

    foreach( explode(',', $string) as $item )
    {
        $items[] = trim($item);
    }

    return join(',', $items);
}

function FormField($options, $value)
{
    $html = '';
    $select_options = explode(',', $options['options']);

    $options['tag_attributes'] = str_replace(array('&quot;', '&#039;'), array('"', "'"), $options['tag_attributes']);

    switch($options['type'])
    {
    case FT_CHECKBOX:
        $tag_value = null;

        if( preg_match('/value\s*=\s*["\']?([^\'"]+)\s?/i', $options['tag_attributes'], $matches) )
        {
            $tag_value = $matches[1];
        }
        else
        {
            $tag_value = 1;
            $options['tag_attributes'] .= ' value="1"';
        }

        $html = "<input " .
                "type=\"checkbox\" " .
                "name=\"{$options['name']}\" " .
                "id=\"{$options['name']}\" " .
                ($value == $tag_value ? "checked=\"checked\" " : '') .
                "{$options['tag_attributes']} />\n";
        break;

    case FT_SELECT:
        $html = "<select " .
                "name=\"{$options['name']}\" " .
                "id=\"{$options['name']}\" " .
                "{$options['tag_attributes']}>\n" .
                OptionTags($select_options, $value, TRUE) .
                "</select>\n";
        break;

    case FT_TEXT:
        $html = "<input " .
                "type=\"text\" " .
                "name=\"{$options['name']}\" " .
                "id=\"{$options['name']}\" " .
                "value=\"$value\" " .
                "{$options['tag_attributes']} />\n";
        break;

    case FT_TEXTAREA:
        $html = "<textarea " .
                "name=\"{$options['name']}\" " .
                "id=\"{$options['name']}\" " .
                "{$options['tag_attributes']}>" .
                $value .
                "</textarea>\n";
        break;
    }

    return $html;
}

function OptionTags($options, $selected = null, $use_values = FALSE, $max_length = 9999)
{
    $html = '';

    if( is_array($options) )
    {
        foreach($options as $key => $value)
        {
            if( $use_values )
                $key = $value;

            $html .= "<option value=\"" . htmlspecialchars($key) . "\"" .
                     ($key == $selected ? ' selected="selected"' : '') .
                     ">" . htmlspecialchars(StringChop($value, $max_length)) . "</option>\n";
        }
    }

    return $html;
}

function OptionTagsAdv($options, $selected, $value, $name, $max_length = 9999)
{
    $html = '';

    if( is_array($options) )
    {
        foreach($options as $option)
        {
            $html .= "<option value=\"" . htmlspecialchars($option[$value]) . "\"" .
                     ((is_array($selected) && in_array($option[$value], $selected) || $option[$value] == $selected) ? ' selected="selected"' : '') .
                     ">" . htmlspecialchars(StringChop($option[$name], $max_length)) . "</option>\n";
        }
    }

    return $html;
}

function UnixFormat(&$string)
{
    $string = str_replace(array("\r\n", "\r"), "\n", $string);
}

function WindowsFormat(&$string)
{
    $string = str_replace(array("\r\n", "\r"), "\n", $string);
    $string = str_replace("\n", "\r\n", $string);
}

function GeneratePathData(&$category, &$parent)
{
    $data = unserialize($parent['path_parts']);

    if( $data === FALSE )
        $data = array();

    // Format the new category name for use in a URL
    if( !IsEmptyString($category['url_name']) )
    {
        $name = $category['url_name'];
    }
    else
    {
        $name = $category['name'];
        $name = preg_replace('~/+~', '/', $name); // Remove double (or more) slashes
        $name = preg_replace('~^/|/$~', '', $name); // Remove trailing and leading slashes
        $name = preg_replace('/[~`!@#\$%^&\*\(\)\-{}\[\]\|\\\"\'\?>< \t\r\n\.\+]/', '_', $name); // Add underscores
        $name = preg_replace('~_+~', '_', $name); // Remove double (or more) underscores
        $name = preg_replace('~_$~', '', $name); // Remove ending underscore
    }

    // Generate the new URL path
    if( empty($parent['path']) )
        $category['path'] = $name;
    else
        $category['path'] = "{$parent['path']}/$name";

    $data[] = array('name' => $category['name'],
                    'category_id' => $category['category_id'],
                    'path' => $category['path']);

    // Serialize the data
    $path = array('serialized' => serialize($data),
                  'hash' => md5($category['path']),
                  'path' => $category['path']);

    return $path;
}

function RelativeToAbsolute($start_url, $relative_url)
{
    if( preg_match('~^https?://~', $relative_url) )
    {
        return $relative_url;
    }

    $parsed = parse_url($start_url);
    $base_url = "{$parsed['scheme']}://{$parsed['host']}" . ($parsed['port'] ? ":{$parsed['port']}" : "");
    $path = $parsed['path'];

    if( $relative_url{0} == '/' )
    {
        return $base_url . ResolvePath($relative_url);
    }

    $path = preg_replace('~[^/]+$~', '', $path);

    return $base_url . ResolvePath($path . $relative_url);
}

function ResolvePath($path)
{
    $path = explode('/', str_replace('//', '/', $path));

    for( $i = 0; $i < count($path); $i++ )
    {
        if( $path[$i] == '.' )
        {
            unset($path[$i]);
            $path = array_values($path);
            $i--;
        }
        elseif( $path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != '')) )
        {
            unset($path[$i]);
            unset($path[$i-1]);
            $path = array_values($path);
            $i -= 2;
        }
        elseif( $path[$i] == '..' AND $i == 1 AND $path[0] == '' )
        {
            unset($path[$i]);
            $path = array_values($path);
            $i--;
        }
        else
        {
            continue;
        }
    }

    return implode('/', $path);
}

function IsBool($value)
{
    return is_bool($value) || preg_match('/^true|false$/i', $value);
}

function ToBool($value)
{
    if( is_numeric($value) )
    {
        if( $value == 0 )
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
    else if( preg_match('~^true$~i', $value) )
    {
        return TRUE;
    }
    else if( preg_match('~^false$~i', $value) )
    {
        return FALSE;
    }

    return FALSE;
}

function SafeAddSlashes(&$string)
{
    $string = preg_replace("/(?<!\\\)'/", "\'", $string);
}

function ArrayCombine($keys, $values)
{
    $combined = array();

    for( $i = 0; $i < count($keys); $i++ )
    {
        $combined[$keys[$i]] = $values[$i];
    }

    return $combined;
}

function ArrayAddSlashes(&$array)
{
    foreach($array as $key => $value)
    {
        if( is_array($array[$key]) )
        {
            ArrayAddSlashes($array[$key]);
        }
        else
        {
            $array[$key] = preg_replace("/(?<!\\\)'/", "\'", $value);
        }
    }
}

function ArrayStripSlashes(&$array)
{
    foreach($array as $key => $value)
    {
        if( is_array($array[$key]) )
        {
            ArrayStripSlashes($array[$key]);
        }
        else
        {
            $array[$key] = stripslashes($value);
        }
    }
}

function SafeFilename($filename, $must_exist = TRUE)
{
    global $L;

    $unsafe_exts = array('php', 'php3', 'php4', 'php5', 'cgi', 'pl', 'exe', 'js');
    $path_info = pathinfo($filename);

    if( $must_exist && !file_exists($filename) )
        trigger_error("{$L['NOT_A_FILE']}: $filename", E_USER_ERROR);

    if( is_dir($filename) )
        trigger_error("{$L['NOT_A_FILE']}: $filename", E_USER_ERROR);

    if( strstr($filename, '..') != FALSE || strstr($filename, '|') != FALSE || strstr($filename, ';') != FALSE)
        trigger_error("{$L['UNSAFE_FILENAME']}: $file", E_USER_ERROR);

    if( in_array($path_info['extension'], $unsafe_exts) )
        trigger_error("{$L['UNSAFE_FILE_EXTENSION']}: $filename", E_USER_ERROR);

    return $filename;
}

function FileReadLine($file)
{
    $line = '';
    $fh = fopen($file, 'r');

    if( $fh )
    {
        $line = trim(fgets($fh));
        fclose($fh);
    }

    return $line;
}

function FileWrite($file, $data, $mode = NULL)
{
    $file_mode = file_exists($file) ? 'r+' : 'w';

    $fh = fopen($file, $file_mode);
    flock($fh, LOCK_EX);
    fseek($fh, 0);
    fwrite($fh, $data);
    ftruncate($fh, ftell($fh));
    flock($fh, LOCK_UN);
    fclose($fh);

    @chmod($file, $GLOBALS['FILE_PERMISSIONS']);
}

function FileWriteNew($file, $data, $mode = NULL)
{
    if( !file_exists($file) )
    {
        FileWrite($file, $data, $mode);
    }
}

function FileAppend($file, $data, $mode = NULL)
{
    $fh = fopen($file, 'a');
    flock($fh, LOCK_EX);
    fwrite($fh, $data);
    flock($fh, LOCK_UN);
    fclose($fh);

    @chmod($file, $GLOBALS['FILE_PERMISSIONS']);
}

function FileRemove($file)
{
    unlink($file);
}

function FileCreate($file, $mode = NULL)
{
    if( !file_exists($file) )
    {
        FileWrite($file, '', $mode);
    }
}

function &DirRead($dir, $pattern)
{
    $contents = array();

    DirTaint($dir);

    $dh = opendir($dir);

    while( false !== ($file = readdir($dh)) )
    {
        $contents[] = $file;
    }

    closedir($dh);

    $contents = preg_grep("/$pattern/i", $contents);

    return $contents;
}

function DirTaint($dir)
{
    if( is_file($dir) )
        trigger_error("Not A Directory: $dir", E_USER_ERROR);

    if( stristr($dir, '..') != FALSE )
        trigger_error("Security Violation: $dir", E_USER_ERROR);
}

function SetupRequest()
{
    if( get_magic_quotes_gpc() == 1 )
    {
        ArrayStripSlashes($_POST);
        ArrayStripSlashes($_GET);
        ArrayStripSlashes($_COOKIE);
    }

    $_REQUEST = array_merge($_POST, $_GET);
}

function Shutdown()
{
    global $DB;

    if( get_class($DB) == 'db' )
    {
        $DB->Disconnect();
    }
}

function Error($code, $string, $file, $line)
{
    global $C;

    $reporting = error_reporting();

    if( $reporting == 0 || !($code & $reporting) )
    {
        return;
    }

    $sapi = php_sapi_name();

    if( $sapi != 'cli' )
    {
        require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
        $t = new Template();
    }

    $file = basename($file);

    // Generate stack trace
    $backtrace = debug_backtrace();
    for( $i = 1; $i < count($backtrace); $i++ )
    {
        $tracefile = $backtrace[$i];

        if( !$tracefile['line'] )
            continue;

        $trace .= "{$tracefile['function']} in " . basename($tracefile['file']) . " on line {$tracefile['line']}<br />";
    }

    if( $sapi != 'cli' )
    {
        $t->assign('trace', $trace);
        $t->assign('error', $string);
        $t->assign('file', $file);
        $t->assign('line', $line);
        $t->assign_by_ref('config', $C);

        if( defined('LINKX') )
        {
            $t->assign('levelup', '../');
        }

        $t->display('error-fatal.tpl');
    }
    else
    {
        echo "Error on line $line of file $file\n" .
             "$string\n\n" .
             "STACK TRACE:\n" . str_replace('<br />', "\n", $trace) . "\n";
    }

    exit;
}

function VerifyCaptcha(&$v, $cookie = 'linkxcaptcha')
{
    global $DB, $L, $C;

    if( !isset($_COOKIE[$cookie]) )
    {
        $v->SetError($L['COOKIES_REQUIRED']);
    }
    else
    {
        $captcha = $DB->Row('SELECT * FROM lx_captcha WHERE session=?', array($_COOKIE[$cookie]));

        if( strtoupper($captcha['code']) != strtoupper($_REQUEST['captcha']) )
        {
            $v->SetError($L['INVALID_CODE']);
        }
        else
        {
            $DB->Update('DELETE FROM lx_captcha WHERE session=?', array($_COOKIE[$cookie]));
            setcookie($cookie, '', time() - 3600, '/', $C['cookie_domain']);
        }
    }
}

function GetCategoryIds($link_id)
{
    global $DB;

    $ids = array();
    $result = $DB->Query('SELECT category_id FROM lx_link_cats WHERE link_id=?', array($link_id));

    while( $category = $DB->NextRow($result) )
    {
        $ids[] = $category['category_id'];
    }

    $DB->Free($result);

    return $ids;
}

function CheckDsbl($ip_address)
{
    // DSBL.ORG is now offline
    return false;
}

function IPFromUrl($url)
{
    if( preg_match('|http://([^:/]+):?(\d+)*(/?.*)|i', $url, $matches) )
    {
        $hostname = $matches[1];
        $ip_address = gethostbyname($hostname);

        if( $ip_address == $hostname )
        {
            $ip_address = '';
        }

        return $ip_address;
    }

    return '';
}

function ValidUserLogin()
{
    global $DB, $C;

    $error = 'Invalid username/password combination';

    if( isset($_POST['login_username']) && isset($_POST['login_password']) )
    {
        $account = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($_POST['login_username']));
        if( $account && $account['password'] == sha1($_POST['login_password']) )
        {
            $session = sha1(uniqid(rand(), true) . $_POST['login_password']);
            setcookie('linkxuser', 'username=' . urlencode($_POST['login_username']) . '&session=' . $session, time() + 86400, '/', $C['cookie_domain']);
            $DB->Update('UPDATE lx_users SET session=?, session_start=? WHERE username=?', array($session, time(), $account['username']));

            $user_fields = $DB->Row('SELECT * FROM lx_user_fields WHERE username=?', array($account['username']));
            $account = array_merge($account, $user_fields);

            return $account;
        }
    }
    else if( isset($_COOKIE['linkxuser']) )
    {
        parse_str($_COOKIE['linkxuser'], $cookie);

        $account = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($cookie['username']));

        if( $account && $cookie['session'] == $account['session'] )
        {
            if( $account['session_start'] < time() - 3600 )
            {
                $session = sha1(uniqid(rand(), true) . $account['password']);
                setcookie('linkx', 'username=' . urlencode($account['username']) . '&session=' . $session, time() + 86400, '/', $C['cookie_domain']);
                $DB->Update('UPDATE lx_users SET session=?, session_start=? WHERE username=?', array($session, time(), $cookie['username']));
            }

            $user_fields = $DB->Row('SELECT * FROM lx_user_fields WHERE username=?', array($account['username']));
            $account = array_merge($account, $user_fields);

            return $account;
        }
    }

    return FALSE;
}

function FormatKeywords($keywords)
{
    $keywords = str_replace(array('.', ',', '?', ';', ':', '(', ')', '{', '}', '*', '&', '%', '$', '#', '@', '!'), '', $keywords);
    $keywords = preg_replace('/\s+/', ' ', $keywords);

    return $keywords;
}

function CheckBlacklistLink(&$link, $full_check = FALSE)
{
    $checks = array('email' => array($link['email']),
                    'url' => array($link['site_url'], $link['recip_url']),
                    'domain_ip' => array(IPFromUrl($link['site_url']), IPFromUrl($link['recip_url'])),
                    'submit_ip' => array($_SERVER['REMOTE_ADDR']),
                    'word' => array($link['title'], $link['description'], $link['keywords']),
                    'html' => array($link['html']));

    return CheckBlacklist($checks, $full_check);
}

function CheckBlacklistComment(&$comment, $full_check = FALSE)
{
    $checks = array('email' => array($comment['email']),
                    'url' => null,
                    'domain_ip' => null,
                    'submit_ip' => array($_SERVER['REMOTE_ADDR']),
                    'word' => array($comment['comment'], $comment['name']),
                    'html' => null);

    return CheckBlacklist($checks, $full_check);
}

function CheckBlacklistAccount(&$account, $full_check = FALSE)
{
    $checks = array('email' => array($account['email']),
                    'url' => null,
                    'domain_ip' => null,
                    'submit_ip' => array($_SERVER['REMOTE_ADDR']),
                    'word' => array($account['name']),
                    'html' => null);

    return CheckBlacklist($checks, $full_check);
}

function &CheckBlacklist(&$checks, $full_check)
{
    global $DB, $BL_CACHE;

    $found = array();

    if( !is_array($BL_CACHE) )
    {
        $BL_CACHE = array();

        $result = $DB->Query('SELECT * FROM lx_blacklist');
        while( $item = $DB->NextRow($result) )
        {
            $BL_CACHE[] = $item;
        }
        $DB->Free($result);
    }

    foreach( $BL_CACHE as $item )
    {
        $to_check = $checks[$item['type']];

        if( !$item['regex'] )
        {
            $item['value'] = quotemeta($item['value']);
        }

        if( is_array($to_check) )
        {
            foreach( $to_check as $check_item )
            {
                if( empty($check_item) )
                {
                    continue;
                }

                if( preg_match("~({$item['value']})~", $check_item, $matches) )
                {
                    $item['match'] = $matches[1];
                    $found[] = $item;

                    if( !$full_check )
                    {
                        break;
                    }
                }
            }
        }

        if( !$full_check && count($found) )
        {
            break;
        }
    }

    if( count($found) )
    {
        return $found;
    }
    else
    {
        return FALSE;
    }
}

function CreateUserInsert($table, &$values, $columns = null)
{
    global $DB;

    $query = array('bind_list' => array(), 'binds' => array());

    if( $columns == null )
    {
        $columns = $DB->GetColumns($table);
    }

    foreach( $columns as $column )
    {
        $query['binds'][] = $values[$column];
        $query['bind_list'][] = '?';
    }

    $query['bind_list'] = join(',', $query['bind_list']);

    return $query;
}

function UserDefinedUpdate($table, $defs_table, $key_name, $key_value, &$data, $admin_update = TRUE)
{
    global $DB;

    $bind_list = array();
    $binds = array($table);
    $fields =& $DB->FetchAll('SELECT * FROM #', array($defs_table));

    foreach( $fields as $field )
    {
        // Handle unchecked checkboxes
        if( $field['type'] == FT_CHECKBOX && !isset($data[$field['name']]) && ($admin_update || $field['on_edit']) )
        {
            $data[$field['name']] = null;
        }

        // See if new data was supplied
        if( array_key_exists($field['name'], $data) )
        {
            $binds[] = $field['name'];
            $binds[] = $data[$field['name']];
            $bind_list[] = '#=?';
        }
    }

    if( count($binds) > 1 )
    {
        $binds[] = $key_name;
        $binds[] = $key_value;
        $DB->Update('UPDATE # SET '.join(',', $bind_list).' WHERE #=?', $binds);
    }
}

class SelectBuilder
{
    var $query;
    var $binds = array();
    var $wheres = array();
    var $havings = array();
    var $orders = array();
    var $joins = array();
    var $error = FALSE;
    var $limit = null;
    var $group = null;
    var $order_string = null;
    var $errstr;

    function SelectBuilder($items, $table)
    {
        $this->query = "SELECT $items FROM `$table`";
    }

    function ProcessFieldName($field)
    {
        preg_match_all('~([a-z0-9_]+)([./+\-*])?~i', $field, $field_parts, PREG_SET_ORDER);
        $placeholders = array();
        $parts = array('placeholders' => '', 'binds' => array());

        foreach( $field_parts as $part )
        {
            $placeholders[] = '#';

            if( count($part) > 1 )
            {
                $placeholders[] = $part[2];
            }

            $parts['binds'][] = $part[1];
        }

        $parts['placeholders'] = join('', $placeholders);

        return $parts;
    }

    function GeneratePiece($field, $operator, $value)
    {
        $piece = '';

        $field = $this->ProcessFieldName($field);

        switch($operator)
        {
        case ST_STARTS:
            $piece = "{$field['placeholders']} LIKE ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = "$value%";
            break;

        case ST_MATCHES:
            $piece = "{$field['placeholders']}=?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $value;
            break;

        case ST_NOT_MATCHES:
            $piece = "{$field['placeholders']}!=?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $value;
            break;

        case ST_BETWEEN:
            list($min, $max) = explode(',', $value);

            $piece = "{$field['placeholders']} BETWEEN ? AND ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $min;
            $this->binds[] = $max;
            break;

        case ST_GREATER:
            $piece = "{$field['placeholders']} > ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $value;
            break;

        case ST_GREATER_EQ:
            $piece = "{$field['placeholders']} >= ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $value;
            break;

        case ST_LESS:
            $piece = "{$field['placeholders']} < ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $value;
            break;

        case ST_LESS_EQ:
            $piece = "{$field['placeholders']} <= ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = $value;
            break;

        case ST_EMPTY:
            $piece = "({$field['placeholders']}='' OR {$field['placeholders']} IS NULL)";
            $this->binds = array_merge($this->binds, $field['binds'], $field['binds']);
            break;

        case ST_NOT_EMPTY:
            $piece = "({$field['placeholders']}!='' AND {$field['placeholders']} IS NOT NULL)";
            $this->binds = array_merge($this->binds, $field['binds'], $field['binds']);
            break;

        case ST_NULL:
            $piece = "{$field['placeholders']} IS NULL";
            $this->binds = array_merge($this->binds, $field['binds']);
            break;

        case ST_NOT_NULL:
            $piece = "{$field['placeholders']} IS NOT NULL";
            $this->binds = array_merge($this->binds, $field['binds']);
            break;

        case ST_IN:
            $items = array_unique(explode(',', $value));

            $piece = "{$field['placeholders']} IN (".CreateBindList($items).")";
            $this->binds = array_merge($this->binds, $field['binds'], $items);
            break;

        case ST_NOT_IN:
            $items = array_unique(explode(',', $value));

            $piece = "{$field['placeholders']} NOT IN (".CreateBindList($items).")";
            $this->binds = array_merge($this->binds, $field['binds'], $items);
            break;

        case ST_ANY:
            break;

        // 'contains' is the default
        default:
            $piece = "{$field['placeholders']} LIKE ?";
            $this->binds = array_merge($this->binds, $field['binds']);
            $this->binds[] = "%$value%";
            break;
        }

        return $piece;
    }

    function AddWhereString($clause)
    {
        $this->wheres[] = $clause;
    }

    function AddWhere($field, $operator, $value = '', $no_value = FALSE)
    {
        if( $no_value && $value == '' )
            return;

        $newpiece = $this->GeneratePiece($field, $operator, $value);

        if( !empty($newpiece) )
        {
            $this->wheres[] = $newpiece;
        }
    }

    function AddHaving($field, $operator, $value = '', $no_value = FALSE)
    {
        if( $no_value && $value == '' )
            return;

        $newpiece = $this->GeneratePiece($field, $operator, $value);

        if( !empty($newpiece) )
        {
            $this->havings[] = $newpiece;
        }
    }

    function AddMultiWhere($fields, $operators, $values, $no_value = FALSE)
    {
        $ors = array();
        $num_fields = count($fields);

        if( !is_array($operators) )
        {
            $operators = array_fill(0, $num_fields, $operators);
        }

        if( !is_array($values) )
        {
            $values = array_fill(0, $num_fields, $values);
        }

        if( $no_value && count($values) < 1 )
            return;

        for( $i = 0; $i < count($fields); $i++ )
        {
            $newpiece = $this->GeneratePiece($fields[$i], $operators[$i], $values[$i]);

            if( !empty($newpiece) )
            {
                $ors[] = $newpiece;
            }
        }

        $this->wheres[] = "(" . join(' OR ', $ors) . ")";
    }

    function AddFulltextWhere($field, $value, $no_value = FALSE)
    {
        if( $no_value && $value == '' )
            return;

        $field_parts = explode(',', $field);
        $parts = array();

        foreach( $field_parts as $part )
        {
            $parts[] = '#';
            $this->binds[] = $part;
        }

        $this->wheres[] = 'MATCH('. join(',', $parts) .') AGAINST (? IN BOOLEAN MODE)';
        $this->binds[] = $value;
    }

    function AddOrder($field, $direction = 'ASC')
    {
        if( preg_match('~^RAND\(~', $field) )
        {
            $this->orders[] = $field;
        }
        else
        {
            $field = $this->ProcessFieldName($field);

            if( $direction != 'ASC' && $direction != 'DESC' )
            {
                $direction = 'ASC';
            }

            $this->binds = array_merge($this->binds, $field['binds']);
            $this->orders[] = "{$field['placeholders']} $direction";
        }
    }

    function SetOrderString($string, &$fields)
    {
        foreach( $fields as $field )
        {
            $string = str_replace($field, "`$field`", $string);
        }

        $this->order_string = $string;
    }

    function AddJoin($left_table, $right_table, $join, $field)
    {
        $this->joins[] = "$join JOIN `$right_table` ON `$right_table`.`$field`=`$left_table`.`$field`";
    }

    function AddGroup($field)
    {
        $field = $this->ProcessFieldName($field);
        $this->group = '`' . join('`.`', $field['binds']) . '`';
    }

    function SetLimit($limit)
    {
        $this->limit = $limit;
    }

    function Generate()
    {
        $select = $this->query;

        if( count($this->joins) )
        {
            $select .= " " . join(' ', $this->joins);
        }

        if( count($this->wheres) )
        {
            $select .= " WHERE " . join(' AND ', $this->wheres);
        }

        if( isset($this->group) )
        {
            $select .= " GROUP BY " . $this->group;
        }

        if( count($this->havings) )
        {
            $select .= " HAVING " . join(' AND ', $this->havings);
        }

        if( isset($this->order_string) )
        {
            $select .= " ORDER BY " . $this->order_string;
        }
        else if( count($this->orders) )
        {
            $select .= " ORDER BY " . join(',', $this->orders);
        }

        if( isset($this->limit) )
        {
            $select .= " LIMIT {$this->limit}";
        }

        return $select;
    }
}

?>