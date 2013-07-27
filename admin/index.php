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

define('LINKX', TRUE);

require_once('../includes/common.php');
require_once('../includes/validator.class.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/compiler.class.php");
require_once('includes/functions.php');

SetupRequest();

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

if( ($error = ValidLogin()) === TRUE )
{
    if( isset($_REQUEST['ref_url']) )
    {
        header("Location: http://{$_SERVER['HTTP_HOST']}{$_REQUEST['ref_url']}");
        exit;
    }

    if( !isset($_REQUEST['r']) )
    {
        include_once('includes/main.php');
    }
    else
    {
        $function = $_REQUEST['r'];

        if( ValidFunction($function) )
        {
            call_user_func($function);
        }
        else
        {
            trigger_error("Function '$function' is not a valid LinkX function", E_USER_ERROR);
        }
    }
}
else
{
    if( isset($_REQUEST['ref_url']) )
    {
        $_SERVER['QUERY_STRING'] = TRUE;
        $_SERVER['REQUEST_URI'] = $_REQUEST['ref_url'];
    }

    include_once('includes/login.php');
}

$DB->Disconnect();

function lxShSearchTerms()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/search-terms.php');
}

function lxDatabaseOptimize()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    $tables = array();
    IniParse("{$GLOBALS['BASE_DIR']}/includes/tables.php", TRUE, $tables);

    include_once('includes/header.php');
    include_once('includes/database-optimize.php');
    flush();

    foreach( array_keys($tables) as $table )
    {
        echo "Repairing " . htmlspecialchars($table) . "<br />"; flush();
        $DB->Update('REPAIR TABLE #', array($table));
        echo "Optimizing " . htmlspecialchars($table) . "<br />"; flush();
        $DB->Update('OPTIMIZE TABLE #', array($table));
    }

    echo "\n<div id=\"done\"></div></div>\n</body>\n</html>";
}

function lxShAds()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/ads.php');
}

function lxShAdAdd()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/ads-add.php');
}

function lxShAdEdit()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM `lx_ads` WHERE `ad_id`=?', array($_REQUEST['ad_id']));
    }

    ArrayHSC($_REQUEST);

    include_once('includes/ads-add.php');
}

function lxAdAdd()
{
    global $DB, $C;

    VerifyAdministrator();

    $v = new Validator();
    $v->Register($_REQUEST['weight'], V_NUMERIC, 'The Weight value must be filled in and numeric');
    $v->Register($_REQUEST['raw_clicks'], V_NUMERIC, 'The Raw Clicks value must be filled in and numeric');
    $v->Register($_REQUEST['unique_clicks'], V_NUMERIC, 'The Unique Clicks value must be filled in and numeric');
    $v->Register($_REQUEST['ad_url'], V_URL, 'The Ad URL is not properly formatted');
    $v->Register($_REQUEST['ad_html_raw'], V_EMPTY, 'The Ad HTML value must be filled in');

    if( !$v->Validate() )
    {
        return $v->ValidationError('lxShAdAdd');
    }

    $DB->Update('INSERT INTO `lx_ads` VALUES (?,?,?,?,?,?,?,?,?)',
                array(null,
                      $_REQUEST['ad_url'],
                      $_REQUEST['ad_html_raw'],
                      $_REQUEST['ad_html'],
                      $_REQUEST['weight'],
                      $_REQUEST['raw_clicks'],
                      $_REQUEST['unique_clicks'],
                      0,
                      $_REQUEST['tags']));

    $_REQUEST['ad_id'] = $DB->InsertID();

    $t = new Template();
    $t->assign_by_ref('ad', $_REQUEST);
    $t->assign_by_ref('config', $C);
    $_REQUEST['ad_html'] = $t->parse($_REQUEST['ad_html_raw']);
    $t->cleanup();

    $DB->Update('UPDATE `lx_ads` SET `ad_html`=? WHERE `ad_id`=?', array($_REQUEST['ad_html'], $_REQUEST['ad_id']));

    $GLOBALS['message'] = 'New advertisement successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAdAdd();
}

function lxAdEdit()
{
    global $DB, $C;

    VerifyAdministrator();

    $v = new Validator();
    $v->Register($_REQUEST['weight'], V_NUMERIC, 'The Weight value must be filled in and numeric');
    $v->Register($_REQUEST['raw_clicks'], V_NUMERIC, 'The Raw Clicks value must be filled in and numeric');
    $v->Register($_REQUEST['unique_clicks'], V_NUMERIC, 'The Unique Clicks value must be filled in and numeric');
    $v->Register($_REQUEST['ad_url'], V_URL, 'The Ad URL is not properly formatted');
    $v->Register($_REQUEST['ad_html_raw'], V_EMPTY, 'The Ad HTML value must be filled in');

    if( !$v->Validate() )
    {
        return $v->ValidationError('lxShAdEdit');
    }

    $t = new Template();
    $t->assign_by_ref('ad', $_REQUEST);
    $t->assign_by_ref('config', $C);
    $_REQUEST['ad_html'] = $t->parse($_REQUEST['ad_html_raw']);
    $t->cleanup();

    $DB->Update('UPDATE `lx_ads` SET ' .
                '`ad_url`=?, ' .
                '`ad_html_raw`=?, ' .
                '`ad_html`=?, ' .
                '`weight`=?, ' .
                '`raw_clicks`=?, ' .
                '`unique_clicks`=?, ' .
                '`tags`=? ' .
                'WHERE `ad_id`=?',
                array($_REQUEST['ad_url'],
                      $_REQUEST['ad_html_raw'],
                      $_REQUEST['ad_html'],
                      $_REQUEST['weight'],
                      $_REQUEST['raw_clicks'],
                      $_REQUEST['unique_clicks'],
                      $_REQUEST['tags'],
                      $_REQUEST['ad_id']));

    $GLOBALS['message'] = 'Advertisement successfully updated';
    $GLOBALS['added'] = true;
    lxShAdEdit();
}

function lxClearTemplateCache()
{
    global $DB, $C, $json;

    VerifyAdministrator(TRUE);

    $t = new Template();

    $t->clear_cache();

    $t->cache_dir = "{$GLOBALS['BASE_DIR']}/templates/cache_details";
    $t->clear_cache();

    $t->cache_dir = "{$GLOBALS['BASE_DIR']}/templates/cache_search";
    $t->clear_cache();

    $message = 'All cached templates have been cleared';

    include_once('includes/message.php');
}

function lxRecompileTemplates()
{
    global $DB, $C, $json;

    VerifyAdministrator();

    RecompileTemplates();

    $message = 'Template files have been recompiled';

    include_once('includes/message.php');
}

function lxShScannerResults()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/link-scanner-results.php');
}

function lxShAddScannerConfig()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/link-scanner-add.php');
}

function lxShEditScannerConfig()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_scanner_configs WHERE config_id=?', array($_REQUEST['config_id']));
        $_REQUEST = array_merge(unserialize($_REQUEST['configuration']), $_REQUEST);
    }

    ArrayHSC($_REQUEST);

    include_once('includes/link-scanner-add.php');
}

function lxAddScannerConfig()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['identifier'], V_EMPTY, 'The Identifier field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddScannerConfig();
        return;
    }

    // Add scanner configuration to the database
    $DB->Update('INSERT INTO lx_scanner_configs VALUES (?,?,?,?,?,?,?)',
                array(NULL,
                      $_REQUEST['identifier'],
                      'Not Running',
                      time(),
                      0,
                      NULL,
                      serialize($_REQUEST)));

    $GLOBALS['message'] = 'New scanner configuration successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddScannerConfig();
}

function lxEditScannerConfig()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['identifier'], V_EMPTY, 'The Identifier field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditScannerConfig();
        return;
    }

    // Update scanner configuration to the database
    $DB->Update('UPDATE lx_scanner_configs SET ' .
                'identifier=?, ' .
                'configuration=? ' .
                'WHERE config_id=?',
                array($_REQUEST['identifier'],
                      serialize($_REQUEST),
                      $_REQUEST['config_id']));

    $GLOBALS['message'] = 'Scanner configuration successfully updated';
    $GLOBALS['added'] = true;
    lxShEditScannerConfig();
}

function lxShPhpInfo()
{
    global $DB, $C;

    CheckAccessList();
    VerifyAdministrator();

    phpinfo();
}

function lxImportLinks()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_ADD);

    $defaults = array('site_url' => null,
                      'recip_url' => null,
                      'title' => null,
                      'description' => null,
                      'status' => 'active',
                      'type' => 'regular',
                      'expires' => DEF_EXPIRES,
                      'name' => null,
                      'email' => $C['from_email'],
                      'submit_ip' => $_SERVER['REMOTE_ADDR'],
                      'keywords' => null,
                      'clicks' => 0,
                      'comments' => 0,
                      'screenshot' => null,
                      'ratings' => 0,
                      'rating_total' => 0,
                      'rating_avg' => 0,
                      'weight' => $C['link_weight'],
                      'date_added' => MYSQL_NOW,
                      'date_modified' => null,
                      'date_scanned' => null,
                      'recip_required' => $C['recip_required'],
                      'allow_redirect' => $C['allow_redirect'],
                      'icons' => null,
                      'admin_comments' => null,
                      'username' => null,
                      'password' => null,
                      'has_recip' => 0,
                      'is_edited' => 0,
                      'edit_data' => null);

    $v = new Validator();

    if( empty($_REQUEST['type']) && !in_array('type', $_REQUEST['fields']) )
    {
        $v->SetError('You indicated that the link type should come from the import data, but that field has not been defined');
    }

    if( empty($_REQUEST['status']) && !in_array('status', $_REQUEST['fields']) )
    {
        $v->SetError('You indicated that the link status should come from the import data, but that field has not been defined');
    }

    // Make sure only one of each field is submitted
    $field_counts = array_count_values($_REQUEST['fields']);
    foreach( $field_counts as $field_name => $field_count )
    {
        if( $field_name != 'IGNORE' && $field_count > 1 )
        {
            $v->SetError("The $field_name field has been specified more than once");
        }
    }

    $category_lookup = TRUE;
    if( $field_counts['categories'] < 1 )
    {
        $v->Register($_REQUEST['category_id'], V_EMPTY, 'Please select at least one category for these links to be imported to');
        $category_lookup = FALSE;
    }

    if( !$v->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $v->GetErrors());
        lxShAnalyzeImport();
        return;
    }


    $imported = 0;
    $duplicates = 0;
    $bad_categories = 0;
    $lines = file(SafeFilename("{$GLOBALS['BASE_DIR']}/data/{$_REQUEST['filename']}"));
    $categories = $used_categories = explode(',', $_REQUEST['category_id']);
    $usernames = array();
    $columns = $DB->GetColumns('lx_link_fields');

    foreach( $lines as $line )
    {
        $data = explode('|', trim($line));
        $link = array();

        foreach( $_REQUEST['fields'] as $index => $field )
        {
            $link[$field] = $data[$index];
        }

        $link = array_merge($defaults, $link);

        // Check for duplicate URL
        if( !$DB->Count('SELECT COUNT(*) FROM lx_links WHERE site_url=?', array($link['site_url'])) )
        {
            // Check for valid status
            if( !in_array($link['status'], array('unconfirmed','pending','active','disabled')) )
            {
                $link['status'] = 'active';
            }

            // Check for valid type
            if( !in_array($link['type'], array('regular','premium','featured')) )
            {
                $link['type'] = 'regular';
            }

            // Setup link type and status
            $link['status'] = empty($_REQUEST['status']) ? $link['status'] : $_REQUEST['status'];
            $link['type'] = empty($_REQUEST['type']) ? $link['type'] : $_REQUEST['type'];

            // Setup expiration
            if( $link['type'] == 'regular' )
            {
                $link['expires'] = DEF_EXPIRES;
            }
            else
            {
                if( empty($link['expires']) )
                {
                    $link['expires'] = $_REQUEST['expires'];
                }

                if( !preg_match(RE_DATETIME, $link['expires']) )
                {
                    $link['expires'] = gmdate(DF_DATETIME, strtotime('+1 month', TIME_NOW));
                }
            }

            // Check date added for errors
            if( !preg_match(RE_DATETIME, $link['date_added']) )
            {
                $link['date_added'] = MYSQL_NOW;
            }

            // Check date modified for errors
            if( !preg_match(RE_DATETIME, $link['date_modified']) )
            {
                $link['date_modified'] = null;
            }

            // Need to locate the category based on the import data
            if( $category_lookup )
            {
                $categories = GetCategoriesForImport($link['categories']);

                if( $categories === FALSE )
                {
                    $bad_categories++;
                    continue;
                }

                $used_categories = array_unique(array_merge($categories, $used_categories));
            }

            // Add regular fields
            $DB->Update('INSERT INTO lx_links VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                        array(null,
                              $link['site_url'],
                              $link['recip_url'],
                              $link['title'],
                              $link['description'],
                              $link['status'],
                              $link['type'],
                              $link['expires'],
                              $link['name'],
                              $link['email'],
                              $link['submit_ip'],
                              $link['keywords'],
                              $link['clicks'],
                              $link['comments'],
                              $link['screenshot'],
                              $link['ratings'],
                              $link['rating_total'],
                              $link['rating_avg'],
                              $link['weight'],
                              $link['date_added'],
                              $link['date_modified'],
                              $link['date_scanned'],
                              $link['recip_required'],
                              $link['allow_redirect'],
                              $link['icons'],
                              $link['admin_comments'],
                              $link['username'],
                              $link['password'],
                              $link['has_recip'],
                              $link['is_edited'],
                              $link['edit_data']));

            $link['link_id'] = $DB->InsertID();

            // Add category data
            foreach( $categories as $category_id )
            {
                $DB->Update('INSERT INTO lx_link_cats VALUES (?,?,?)', array($link['link_id'], $category_id, null));
            }

            // Add user defined fields
            $query_data = CreateUserInsert('lx_link_fields', $link, $columns);
            $DB->Update('INSERT INTO lx_link_fields VALUES ('.$query_data['bind_list'].')', $query_data['binds']);

            if( !empty($link['username']) )
            {
                $usernames[$link['username']] = 1;
            }

            $imported++;
        }
        else
        {
            $duplicates++;
        }
    }

    StoreValue('last_import', serialize($_REQUEST['fields']));

    // Update category link counts
    foreach( $used_categories as $category_id )
    {
        $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($category_id));
        UpdateLinkCount($category_id);
        ClearCategoryCache($category_id);
        ClearCategoryCache($category['parent_id']);
    }

    // Update account link counts
    foreach( $usernames as $username => $found )
    {
        UpdateAccountLinkCount($username);
    }

    $GLOBALS['message'] = "A total of $imported links have been imported<br />" .
                          "$duplicates links were skipped because they were duplicates of links already in the database<br />" .
                          "$bad_categories links were skipped because the category data provided did not match a category in the database";

    lxShImportLinks();
}

function lxShAnalyzeImport()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_ADD);

    if( !isset($_REQUEST['analyzed']) )
    {
        $v = new Validator();

        if( $_REQUEST['type'] == 'file' )
        {
            $v->Register(is_file("{$GLOBALS['BASE_DIR']}/data/import.txt"), V_TRUE, 'The file import.txt has not been uploaded to the data directory');
        }
        else
        {
            $v->Register($_REQUEST['input'], V_EMPTY, 'You must supply some import data in the text input box');
        }

        if( !$v->Validate() )
        {
            $GLOBALS['errstr'] = join('<br />', $v->GetErrors());
            lxShImportLinks();
            return;
        }

        // Setup file for analysis
        $filename = 'import.txt';
        if( $_REQUEST['type'] == 'input' )
        {
            FileWrite("{$GLOBALS['BASE_DIR']}/data/temp-import.txt", $_REQUEST['input']);
            $filename = 'temp-import.txt';
        }
    }
    else
    {
        $filename = $_REQUEST['filename'];
    }

    include_once('includes/link-import-analyze.php');
}

function lxShImportLinks()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_ADD);

    include_once('includes/link-import.php');
}

function lxBackupDatabase()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    $filename = SafeFilename("{$GLOBALS['BASE_DIR']}/data/{$_REQUEST['filename']}", FALSE);

    $tables = array();
    IniParse("{$GLOBALS['BASE_DIR']}/includes/tables.php", TRUE, $tables);

    $GLOBALS['message'] = 'Database backup is in progress, allow a few minutes to complete before downloading the backup file';

    // Run mysqldump in the background
    if( $C['allow_exec'] && !empty($C['mysqldump']) )
    {
        $command = "{$C['mysqldump']} " .
                   "-u" . escapeshellarg($C['db_username']) . " " .
                   "-p" . escapeshellarg($C['db_password']) . " " .
                   "-h" . escapeshellarg($C['db_hostname']) . " " .
                   "--opt " .
                   escapeshellarg($C['db_name']) . " " .
                   join(' ', array_keys($tables)) .
                   " >" . escapeshellarg($filename) . " 2>&1 &";

        exec($command);
    }

    // Use built in database backup function in the background
    else if( $C['allow_exec'] && !empty($C['php_cli']) )
    {
        exec("{$C['php_cli']} cron.php --backup " . escapeshellarg($filename) . " >/dev/null 2>&1 &");
    }

    // Give it our best shot
    else
    {
        DoBackup($filename, $tables);
        $GLOBALS['message'] = 'Database backup has been completed';
    }

    StoreValue('last_backup', time());

    lxShDatabaseTools();
}

function lxRestoreDatabase()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    $filename = SafeFilename("{$GLOBALS['BASE_DIR']}/data/{$_REQUEST['filename']}", FALSE);

    $tables = array();
    IniParse("{$GLOBALS['BASE_DIR']}/includes/tables.php", TRUE, $tables);

    $GLOBALS['message'] = 'Database restore is in progress, allow a few minutes to complete before downloading the backup file';

    // Run mysql in the background
    if( $C['allow_exec'] && !empty($C['mysql']) )
    {
        $command = "{$C['mysql']} " .
                   "-u" . escapeshellarg($C['db_username']) . " " .
                   "-p" . escapeshellarg($C['db_password']) . " " .
                   "-h" . escapeshellarg($C['db_hostname']) . " " .
                   "-f " .
                   escapeshellarg($C['db_name']) . " " .
                   " <$filename 2>&1 &";

        exec($command);
    }

    // Use built in database backup function in the background
    else if( $C['allow_exec'] && !empty($C['php_cli']) )
    {
        exec("{$C['php_cli']} cron.php --restore " . escapeshellarg($filename) . " >/dev/null 2>&1 &");
    }

    // Give it our best shot
    else
    {
        DoRestore($filename);
        $GLOBALS['message'] = 'Database restore has been completed';
    }

    lxShDatabaseTools();
}

function lxShEditComment()
{
    global $DB, $C;

    VerifyPrivileges(P_COMMENT_MODIFY);

    // First time, use database information
    if( !$_REQUEST['editing'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_link_comments WHERE comment_id=?', array($_REQUEST['comment_id']));
    }

    ArrayHSC($_REQUEST);

    include_once('includes/comment-edit.php');
}

function lxEditComment()
{
    global $C, $DB;

    VerifyPrivileges(P_COMMENT_MODIFY);

    $v = new Validator();

    $v->Register($_REQUEST['email'], V_EMAIL, 'The e-mail address is not properly formatted');
    $v->Register($_REQUEST['name'], V_EMPTY, 'The Name field must be filled in');
    $v->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added value is not properly formatted');

    if( !$v->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $v->GetErrors());
        lxShEditComment();
        return;
    }

    $DB->Update('UPDATE lx_link_comments SET ' .
                'email=?, ' .
                'name=?, ' .
                'date_added=?, ' .
                'comment=? ' .
                'WHERE comment_id=?',
                array($_REQUEST['email'],
                      $_REQUEST['name'],
                      $_REQUEST['date_added'],
                      $_REQUEST['comment'],
                      $_REQUEST['comment_id']));

    $GLOBALS['message'] = 'Comment successfully updated';
    $GLOBALS['added'] = TRUE;
    lxShEditComment();
}

function lxShSearchComments()
{
    global $DB, $C;

    VerifyPrivileges(P_COMMENT);

    include_once('includes/comment-search.php');
}

function lxShSearchCategories()
{
    global $DB, $C;

    VerifyPrivileges(P_CATEGORY);

    include_once('includes/category-search.php');
}

function lxShReports()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_MODIFY);

    include_once('includes/reports.php');
}

function lxShScanLinks()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/link-scanner.php');
}

function lxShSearchLinks()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK);

    include_once('includes/link-search.php');
}

function lxShTasksLink($errors = null)
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_REMOVE|P_LINK_MODIFY);

    include_once('includes/link-tasks.php');
}

function lxLinkSearchAndReplace()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_MODIFY);

    $select = new SelectBuilder('*', 'lx_links');
    $select->AddJoin('lx_links', 'lx_link_cats', '', 'link_id');
    $select->AddJoin('lx_links', 'lx_link_fields', '', 'link_id');
    $select->AddWhere($_REQUEST['field'], ST_CONTAINS, $_REQUEST['find']);

    if( $_REQUEST['category_only'] )
    {
        $select->AddWhere('category_id', ST_MATCHES, $_REQUEST['category_id']);
    }

    if( $select->error )
    {
        lxShTasksLink($select->errstr);
        return;
    }

    $updates = 0;
    $result = $DB->Query($select->Generate(), $select->binds);
    while( $link = $DB->NextRow($result) )
    {
        $DB->Update('UPDATE lx_links JOIN lx_link_fields USING (link_id) SET #=REPLACE(#,?,?) WHERE link_id=?',
                    array($_REQUEST['field'],
                          $_REQUEST['field'],
                          $_REQUEST['find'],
                          $_REQUEST['replace'],
                          $link['link_id']));
        $updates++;
    }
    $DB->Free($result);

    $GLOBALS['message'] = "Search and replace completed; $updates link" . ($updates != 1 ? 's have' : ' has') ." been updated";
    lxShTasksLink();
}

function lxLinkSearchAndSet()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_MODIFY);

    $select = new SelectBuilder('*', 'lx_links');
    $select->AddJoin('lx_links', 'lx_link_cats', '', 'link_id');
    $select->AddJoin('lx_links', 'lx_link_fields', '', 'link_id');
    $select->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['find']);

    if( $_REQUEST['category_only'] )
    {
        $select->AddWhere('category_id', ST_MATCHES, $_REQUEST['category_id']);
    }

    if( $select->error )
    {
        lxShTasksLink($select->errstr);
        return;
    }

    $updates = 0;
    $result = $DB->Query($select->Generate(), $select->binds);
    while( $link = $DB->NextRow($result) )
    {
        $DB->Update('UPDATE lx_links JOIN lx_link_fields USING (link_id) SET #=? WHERE link_id=?',
                    array($_REQUEST['set_field'],
                          $_REQUEST['set_to'],
                          $link['link_id']));
        $updates++;
    }
    $DB->Free($result);

    $GLOBALS['message'] = "Search and set completed; $updates link" . ($updates != 1 ? 's have' : ' has') ." been updated";
    lxShTasksLink();
}

function lxLinkSearchAndDelete()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_REMOVE);

    $select = new SelectBuilder('*', 'lx_links');
    $select->AddJoin('lx_links', 'lx_link_cats', '', 'link_id');
    $select->AddJoin('lx_links', 'lx_link_fields', '', 'link_id');
    $select->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['find']);

    if( $_REQUEST['category_only'] )
    {
        $select->AddWhere('category_id', ST_MATCHES, $_REQUEST['category_id']);
    }

    if( $select->error )
    {
        lxShTasksLink($select->errstr);
        return;
    }

    $updates = 0;
    $result = $DB->Query($select->Generate(), $select->binds);
    while( $link = $DB->NextRow($result) )
    {
        DeleteLink($link['link_id'], TRUE, $link);
        $updates++;
    }
    $DB->Free($result);

    $GLOBALS['message'] = "Search and delete completed; $updates link" . ($updates != 1 ? 's have' : ' has') ." been deleted";
    lxShTasksLink();
}

function lxShBlacklistLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_REMOVE);

    $link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($_REQUEST['link_id']));
    $link['domain_ip'] = IPFromUrl($link['site_url']);

    include_once('includes/link-blacklist.php');
}

function lxBlacklistLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_REMOVE);

    foreach( array('url', 'email', 'domain_ip', 'submit_ip') as $type )
    {
        if( !empty($_REQUEST[$type]) )
        {
            if( !$DB->Count('SELECT COUNT(*) FROM lx_blacklist WHERE type=? AND value=?', array($type, $_REQUEST[$type])) )
            {
                $DB->Update('INSERT INTO lx_blacklist VALUES (?,?,?,?,?)',
                            array(null,
                                  $type,
                                  0,
                                  $_REQUEST[$type],
                                  $_REQUEST['reason']));
            }
        }
    }

    DeleteLink($_REQUEST['link_id']);

    $message = 'The specified items have been added to the blacklist and the link has been deleted';
    $GLOBALS['added'] = TRUE;

    include_once('includes/message.php');
}

function lxShScanLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK);

    $link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($_REQUEST['link_id']));

    $results =& ScanLink($link);

    $link['html'] = $results['site_url']['html'];

    if( is_array($results['recip_url']) )
    {
        $link['html'] .= ' ' . $results['recip_url']['html'];
    }

    $blacklisted = CheckBlacklistLink($link, TRUE);

    $DB->Update('UPDATE lx_links SET date_scanned=?,has_recip=? WHERE link_id=?', array(MYSQL_NOW, $results['has_recip'], $link['link_id']));

    include_once('includes/link-scan.php');
}

function lxShMailLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK);

    ArrayHSC($_REQUEST);

    if( is_array($_REQUEST['link_id']) )
    {
        $_REQUEST['to'] = join(', ', $_REQUEST['link_id']);
        $_REQUEST['to_list'] = join(',', $_REQUEST['link_id']);
    }
    else
    {
        $_REQUEST['to'] = $_REQUEST['to_list'] = $_REQUEST['link_id'];
    }

    $function = 'lxMailLink';
    include_once('includes/email-compose.php');
}

function lxMailLink()
{
    global $DB, $C, $t;

    VerifyPrivileges(P_LINK);

    UnixFormat($_REQUEST['plain']);
    UnixFormat($_REQUEST['html']);

    $message = "=>[subject]\n" .
               $_REQUEST['subject'] . "\n" .
               "=>[plain]\n" .
               trim($_REQUEST['plain']) . "\n" .
               "=>[html]\n" .
               trim($_REQUEST['html']);

    $t = new Template();
    $t->assign_by_ref('config', $C);

    foreach( explode(',', $_REQUEST['to']) as $to_link )
    {
        $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($to_link));

        if( $link )
        {
            $t->assign_by_ref('link', $link);
            SendMail($link['email'], $message, $t, FALSE);
        }
    }

    $message = 'The selected link submitters have been e-mailed';
    include_once('includes/message.php');
}

function lxShNews()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/news.php');
}

function lxShEditNews()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time, use database information
    if( !$_REQUEST['editing'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_news WHERE news_id=?', array($_REQUEST['news_id']));
    }

    ArrayHSC($_REQUEST);

    include_once('includes/news-add.php');
}

function lxAddNews()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['headline'], V_EMPTY, 'The Headline field must be filled in');
    $validator->Register($_REQUEST['body'], V_EMPTY, 'The News Text field must be filled in');
    $validator->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added field is not properly formatted');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddNews();
        return;
    }

    if( !preg_match(RE_DATETIME, $_REQUEST['date_added']) )
    {
        $_REQUEST['date_added'] = MYSQL_NOW;
    }

    // Add news item data to the database
    $DB->Update('INSERT INTO lx_news VALUES (?,?,?,?)',
                array(NULL,
                      $_REQUEST['headline'],
                      $_REQUEST['body'],
                      $_REQUEST['date_added']));


    $GLOBALS['message'] = 'New news item successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddNews();
}

function lxEditNews()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['headline'], V_EMPTY, 'The Headline field must be filled in');
    $validator->Register($_REQUEST['body'], V_EMPTY, 'The News Text field must be filled in');
    $validator->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added field is not properly formatted');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddNews();
        return;
    }

    // Update news item data
    $DB->Update('UPDATE lx_news SET ' .
                'headline=?, ' .
                'body=?, ' .
                'date_added=? ' .
                'WHERE news_id=?',
                array($_REQUEST['headline'],
                      $_REQUEST['body'],
                      $_REQUEST['date_added'],
                      $_REQUEST['news_id']));

    $GLOBALS['message'] = 'News item successfully updated';
    $GLOBALS['added'] = true;
    lxShEditNews();
}

function lxShAddNews()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/news-add.php');
}

function lxShRegexTest()
{
    global $DB, $C;

    include_once('includes/regex-test.php');
}

function lxShReciprocals()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/reciprocals.php');
}

function lxShEditReciprocal()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time, use database information
    if( !$_REQUEST['editing'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_reciprocals WHERE recip_id=?', array($_REQUEST['recip_id']));
    }

    ArrayHSC($_REQUEST);

    include_once('includes/reciprocals-add.php');
}

function lxAddReciprocal()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['code'], V_EMPTY, 'The Link Code field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddReciprocal();
        return;
    }

    UnixFormat($_REQUEST['code']);

    // Add reciprocal link to the database
    $DB->Update('INSERT INTO lx_reciprocals VALUES (?,?,?,?)',
                array(NULL,
                      $_REQUEST['identifier'],
                      $_REQUEST['code'],
                      intval($_REQUEST['regex'])));


    $GLOBALS['message'] = 'New reciprocal link  successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddReciprocal();
}

function lxEditReciprocal()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['code'], V_EMPTY, 'The Link Code field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditReciprocal();
        return;
    }

    // Update recip link data
    $DB->Update('UPDATE lx_reciprocals SET ' .
                'identifier=?, ' .
                'code=?, ' .
                'regex=? ' .
                'WHERE recip_id=?',
                array($_REQUEST['identifier'],
                      $_REQUEST['code'],
                      intval($_REQUEST['regex']),
                      $_REQUEST['recip_id']));

    $GLOBALS['message'] = 'Reciprocal link successfully updated';
    $GLOBALS['added'] = true;
    lxShEditReciprocal();
}

function lxShAddReciprocal()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/reciprocals-add.php');
}

function lxShBlacklist()
{
    global $DB, $C, $BLIST_TYPES;

    VerifyAdministrator();

    include_once('includes/blacklist.php');
}

function lxShEditBlacklist()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time, use database information
    if( !$_REQUEST['editing'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_blacklist WHERE blacklist_id=?', array($_REQUEST['blacklist_id']));
    }

    ArrayHSC($_REQUEST);

    include_once('includes/blacklist-add.php');
}

function lxAddBlacklist()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['value'], V_EMPTY, 'The Value(s) field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddBlacklist();
        return;
    }

    UnixFormat($_REQUEST['value']);
    $added = 0;

    foreach( explode("\n", $_REQUEST['value']) as $value )
    {
        list($value, $reason) = explode('|', $value);

        if( !$reason )
            $reason = $_REQUEST['reason'];

        // Add blacklist item data to the database
        $DB->Update('INSERT INTO lx_blacklist VALUES (?,?,?,?,?)',
                    array(NULL,
                          $_REQUEST['type'],
                          intval($_REQUEST['regex']),
                          $value,
                          $reason));

        $added++;
    }

    $GLOBALS['message'] = 'New blacklist item' . ($added == 1 ? '' : 's') . ' successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddBlacklist();
}

function lxEditBlacklist()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['value'], V_EMPTY, 'The Value(s) field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditBlacklist();
        return;
    }

    // Update blacklist item data
    $DB->Update('UPDATE lx_blacklist SET ' .
                'value=?, ' .
                'type=?, ' .
                'regex=?, ' .
                'reason=? ' .
                'WHERE blacklist_id=?',
                array($_REQUEST['value'],
                      $_REQUEST['type'],
                      intval($_REQUEST['regex']),
                      $_REQUEST['reason'],
                      $_REQUEST['blacklist_id']));

    $GLOBALS['message'] = 'Blacklist item successfully updated';
    $GLOBALS['added'] = true;
    lxShEditBlacklist();
}

function lxShAddBlacklist()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/blacklist-add.php');
}

function lxShLanguage()
{
    global $DB, $C, $L;

    VerifyAdministrator();

    include_once('includes/language.php');
}

function lxSaveLanguage()
{
    global $DB, $C, $L;

    VerifyAdministrator();

    if( is_writable("{$GLOBALS['BASE_DIR']}/includes/language.php") )
    {
        $language = "<?PHP\n";

        foreach( $L as $key => $value )
        {
            $L[$key] = $_REQUEST[$key];
            $value = str_replace("'", "\'", $_REQUEST[$key]);
            $language .= "\$L['$key'] = '$value';\n";
        }

        $language .= "?>";

        FileWrite("{$GLOBALS['BASE_DIR']}/includes/language.php", $language);

        $GLOBALS['message'] = 'The language file has been successfully updated';
    }

    lxShLanguage();
}

function lxShDirectoryTemplates()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    ArrayHSC($_REQUEST);

    include_once('includes/templates-directory.php');
}

function lxLoadDirectoryTemplate()
{
    global $DB, $C;

    VerifyAdministrator();

    $template_file = SafeFilename("{$GLOBALS['BASE_DIR']}/templates/{$_REQUEST['template']}");
    $_REQUEST['code'] = file_get_contents($template_file);
    $_REQUEST['loaded_template'] = $_REQUEST['template'];

    lxShDirectoryTemplates();
}

function lxShRewriteFile()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/rewrite-file.php');
}

function lxSaveDirectoryTemplate()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    $_REQUEST['code'] = trim($_REQUEST['code']);

    $t = new Template();

    // Compile global templates first, if this is not one
    if( !preg_match('~^global-~', $_REQUEST['loaded_template']) )
    {
        foreach( glob("{$GLOBALS['BASE_DIR']}/templates/global-*.tpl") as $global_template )
        {
            $t->compile_template(basename($global_template));
        }
    }

    $template_file = SafeFilename("{$GLOBALS['BASE_DIR']}/templates/{$_REQUEST['loaded_template']}");
    FileWrite($template_file, $_REQUEST['code']);
    $t->compile_template(basename($template_file));

    $GLOBALS['message'] = 'Template has been successully saved';
    $GLOBALS['warnstr'] = CheckTemplateCode($_REQUEST['code']);

    // Recompile all templates if a global template was updated
    if( preg_match('~^global-~', $_REQUEST['loaded_template']) )
    {
        RecompileTemplates();
    }

    lxShDirectoryTemplates();
}

function lxShEmailTemplates()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    ArrayHSC($_REQUEST);

    include_once('includes/templates-email.php');
}

function lxLoadEmailTemplate()
{
    global $DB, $C;

    VerifyAdministrator();

    $template_file = SafeFilename("{$GLOBALS['BASE_DIR']}/templates/{$_REQUEST['template']}");
    IniParse($template_file, TRUE, $_REQUEST);
    $_REQUEST['loaded_template'] = $_REQUEST['template'];

    lxShEmailTemplates();
}

function lxSaveEmailTemplate()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    $_REQUEST['plain'] = trim($_REQUEST['plain']);
    $_REQUEST['html'] = trim($_REQUEST['html']);
    $ini_data = IniWrite(null, $_REQUEST, array('subject', 'plain', 'html'));

    $compiled_code = '';
    $compiler = new Compiler();
    if( $compiler->compile($ini_data, $compiled_code) )
    {
        $template_file = SafeFilename("{$GLOBALS['BASE_DIR']}/templates/{$_REQUEST['loaded_template']}");
        FileWrite($template_file, $ini_data);
        $GLOBALS['message'] = 'Template has been successully saved';
    }
    else
    {
        $GLOBALS['errstr'] = "Template could not be saved:<br />" . nl2br($compiler->get_error_string());
    }


    lxShEmailTemplates();
}

function lxShDatabaseTools()
{
    global $DB, $C;

    VerifyAdministrator();
    CheckAccessList();

    include_once('includes/database.php');
}

function lxShRejections()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/rejections.php');
}

function lxShAddRejection()
{
    global $C, $DB;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/rejections-add.php');
}

function lxShEditRejection()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time or update, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_rejections WHERE email_id=?', array($_REQUEST['email_id']));
        IniParse($_REQUEST['plain'], FALSE, $_REQUEST);
    }

    ArrayHSC($_REQUEST);

    include_once('includes/rejections-add.php');
}

function lxAddRejection()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['identifier'], V_EMPTY, 'The Identifier field must be filled in');
    $validator->Register($_REQUEST['subject'], V_EMPTY,  'The Subject field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddRejection();
        return;
    }

    $_REQUEST['plain'] = trim($_REQUEST['plain']);
    $_REQUEST['html'] = trim($_REQUEST['html']);
    $ini_data = IniWrite(null, $_REQUEST, array('subject', 'plain', 'html'));

    $compiled_code = '';
    $compiler = new Compiler();
    if( $compiler->compile($ini_data, $compiled_code) )
    {
        $DB->Update('INSERT INTO lx_rejections VALUES (?,?,?,?)',
                    array(NULL,
                          $_REQUEST['identifier'],
                          $ini_data,
                          $compiled_code));

        $GLOBALS['message'] = 'New rejection e-mail successfully added';
        $GLOBALS['added'] = true;

        UnsetArray($_REQUEST);
    }
    else
    {
        $GLOBALS['errstr'] = "Rejection e-mail could not be saved:<br />" . nl2br($compiler->get_error_string());
    }

    lxShAddRejection();
}

function lxEditRejection()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['identifier'], V_EMPTY, 'The Identifier field must be filled in');
    $validator->Register($_REQUEST['subject'], V_EMPTY,  'The Subject field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxEditRejection();
        return;
    }

    $_REQUEST['plain'] = trim($_REQUEST['plain']);
    $_REQUEST['html'] = trim($_REQUEST['html']);
    $ini_data = IniWrite(null, $_REQUEST, array('subject', 'plain', 'html'));

    $compiled_code = '';
    $compiler = new Compiler();
    if( $compiler->compile($ini_data, $compiled_code) )
    {
        $DB->Update('UPDATE lx_rejections SET ' .
            'identifier=?, ' .
            'plain=?, ' .
            'compiled=? ' .
            'WHERE email_id=?',
            array($_REQUEST['identifier'],
                  $ini_data,
                  $compiled_code,
                  $_REQUEST['email_id']));

        $GLOBALS['message'] = 'Rejection e-mail has been successfully updated';
        $GLOBALS['added'] = true;
    }
    else
    {
        $GLOBALS['errstr'] = "Rejection e-mail could not be saved:<br />" . nl2br($compiler->get_error_string());
    }

    lxShEditRejection();
}

function lxShUserFields()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/users-fields.php');
}

function lxShAddUserField()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/users-fields-add.php');
}

function lxShEditUserField()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time or update, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_user_field_defs WHERE field_id=?', array($_REQUEST['field_id']));
        $_REQUEST['old_name'] = $_REQUEST['name'];
    }

    ArrayHSC($_REQUEST);

    include_once('includes/users-fields-add.php');
}

function lxAddUserField()
{
    global $DB, $C;

    VerifyAdministrator();

    // See if field name already exists
    $field_count = $DB->Count('SELECT COUNT(*) FROM lx_user_field_defs WHERE name=?', array($_REQUEST['name']));

    // Get pre-defined fields so there are no duplicates
    $predefined = $DB->GetColumns('lx_users');

    $validator = new Validator();
    $validator->Register($_REQUEST['name'], V_EMPTY, 'The Field Name must be filled in');
    $validator->Register($_REQUEST['name'], V_REGEX, 'The Field Name can contain only letters, numbers, and underscores', '/^[a-z0-9_]+$/i');
    $validator->Register(in_array($_REQUEST['name'], $predefined), V_FALSE, 'The field name you have selected conflicts with a pre-defined field name');
    $validator->Register($_REQUEST['label'], V_EMPTY, 'The Label field must be filled in');
    $validator->Register($field_count, V_ZERO, 'A field with this name already exists');

    if( $_REQUEST['type'] == FT_SELECT || $_REQUEST['type'] == FT_MULTI_SELECT )
        $validator->Register($_REQUEST['options'], V_EMPTY, 'The Options field must be filled in for this field type');

    if( $_REQUEST['validation'] != V_NONE )
        $validator->Register($_REQUEST['validation_message'], V_EMPTY, 'The Validation Error field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddUserField();
        return;
    }

    $_REQUEST['options'] = FormatCommaSeparated($_REQUEST['options']);

    $DB->Update("ALTER TABLE lx_user_fields ADD COLUMN {$_REQUEST['name']} TEXT");
    $DB->Update('INSERT INTO lx_user_field_defs VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                array(NULL,
                      $_REQUEST['name'],
                      $_REQUEST['label'],
                      $_REQUEST['type'],
                      $_REQUEST['tag_attributes'],
                      $_REQUEST['options'],
                      $_REQUEST['validation'],
                      $_REQUEST['validation_extras'],
                      $_REQUEST['validation_message'],
                      intval($_REQUEST['on_create']),
                      intval($_REQUEST['on_edit']),
                      intval($_REQUEST['required'])));

    $GLOBALS['message'] = 'New account field successfully added';
    $GLOBALS['added'] = true;

    UnsetArray($_REQUEST);
    lxShAddUserField();
}

function lxEditUserField()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['name'], V_EMPTY, 'The Field Name must be filled in');
    $validator->Register($_REQUEST['name'], V_REGEX, 'The Field Name can contain only letters, numbers, and underscores', '/^[a-z0-9_]+$/i');
    $validator->Register($_REQUEST['label'], V_EMPTY, 'The Label field must be filled in');

    if( $_REQUEST['type'] == FT_SELECT || $_REQUEST['type'] == FT_MULTI_SELECT )
        $validator->Register($_REQUEST['options'], V_EMPTY, 'The Options field must be filled in for this field type');

    if( $_REQUEST['validation'] != V_NONE )
        $validator->Register($_REQUEST['validation_message'], V_EMPTY, 'The Validation Error field must be filled in');

    if( $_REQUEST['name'] != $_REQUEST['old_name'] )
    {
        // Get pre-defined fields so there are no duplicates
        $predefined = $DB->GetColumns('lx_users');
        $validator->Register(in_array($_REQUEST['name'], $predefined), V_FALSE, 'The field name you have selected conflicts with a pre-defined field name');

        $field_count = $DB->Count('SELECT COUNT(*) FROM lx_user_field_defs WHERE name=?', array($_REQUEST['name']));
        $validator->Register($field_count, V_ZERO, 'A field with this name already exists');
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditUserField();
        return;
    }

    $_REQUEST['options'] = FormatCommaSeparated($_REQUEST['options']);

    if( $_REQUEST['name'] != $_REQUEST['old_name'] )
        $DB->Update("ALTER TABLE lx_user_fields CHANGE {$_REQUEST['old_name']} {$_REQUEST['name']} TEXT");

    $DB->Update('UPDATE lx_user_field_defs SET ' .
                'name=?, ' .
                'label=?, ' .
                'type=?, ' .
                'tag_attributes=?, ' .
                'options=?, ' .
                'validation=?, ' .
                'validation_extras=?, ' .
                'validation_message=?, ' .
                'on_create=?, ' .
                'on_edit=?, ' .
                'required=? ' .
                'WHERE field_id=?',
                array($_REQUEST['name'],
                      $_REQUEST['label'],
                      $_REQUEST['type'],
                      $_REQUEST['tag_attributes'],
                      $_REQUEST['options'],
                      $_REQUEST['validation'],
                      $_REQUEST['validation_extras'],
                      $_REQUEST['validation_message'],
                      intval($_REQUEST['on_create']),
                      intval($_REQUEST['on_edit']),
                      intval($_REQUEST['required']),
                      $_REQUEST['field_id']));

    $GLOBALS['message'] = 'Account field has been successfully updated';
    $GLOBALS['added'] = true;

    lxShEditUserField();
}

function lxShLinkFields()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/link-fields.php');
}

function lxShAddLinkField()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/link-fields-add.php');
}

function lxShEditLinkField()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time or update, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_link_field_defs WHERE field_id=?', array($_REQUEST['field_id']));
        $_REQUEST['old_name'] = $_REQUEST['name'];
    }

    ArrayHSC($_REQUEST);

    include_once('includes/link-fields-add.php');
}

function lxAddLinkField()
{
    global $DB, $C;

    VerifyAdministrator();

    // See if field name already exists
    $field_count = $DB->Count('SELECT COUNT(*) FROM lx_link_field_defs WHERE name=?', array($_REQUEST['name']));

    // Get pre-defined fields so there are no duplicates
    $predefined = $DB->GetColumns('lx_links');

    $validator = new Validator();
    $validator->Register($_REQUEST['name'], V_EMPTY, 'The Field Name must be filled in');
    $validator->Register($_REQUEST['name'], V_REGEX, 'The Field Name can contain only letters, numbers, and underscores', '/^[a-z0-9_]+$/i');
    $validator->Register(in_array($_REQUEST['name'], $predefined), V_FALSE, 'The field name you have selected conflicts with a pre-defined field name');
    $validator->Register($_REQUEST['label'], V_EMPTY, 'The Label field must be filled in');
    $validator->Register($field_count, V_ZERO, 'A field with this name already exists');

    if( $_REQUEST['type'] == FT_SELECT || $_REQUEST['type'] == FT_MULTI_SELECT )
        $validator->Register($_REQUEST['options'], V_EMPTY, 'The Options field must be filled in for this field type');

    if( $_REQUEST['validation'] != V_NONE )
        $validator->Register($_REQUEST['validation_message'], V_EMPTY, 'The Validation Error field must be filled in');

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddLinkField();
        return;
    }

    $_REQUEST['options'] = FormatCommaSeparated($_REQUEST['options']);

    $DB->Update("ALTER TABLE lx_link_fields ADD COLUMN {$_REQUEST['name']} TEXT");
    $DB->Update('INSERT INTO lx_link_field_defs VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array(NULL,
                      $_REQUEST['name'],
                      $_REQUEST['label'],
                      $_REQUEST['type'],
                      $_REQUEST['tag_attributes'],
                      $_REQUEST['options'],
                      $_REQUEST['validation'],
                      $_REQUEST['validation_extras'],
                      $_REQUEST['validation_message'],
                      intval($_REQUEST['on_submit']),
                      intval($_REQUEST['on_edit']),
                      intval($_REQUEST['on_details']),
                      intval($_REQUEST['required'])));

    $GLOBALS['message'] = 'New link field successfully added';
    $GLOBALS['added'] = true;

    UnsetArray($_REQUEST);
    lxShAddLinkField();
}

function lxEditLinkField()
{
    global $DB, $C;

    VerifyAdministrator();

    $validator = new Validator();
    $validator->Register($_REQUEST['name'], V_EMPTY, 'The Field Name must be filled in');
    $validator->Register($_REQUEST['name'], V_REGEX, 'The Field Name can contain only letters, numbers, and underscores', '/^[a-z0-9_]+$/i');
    $validator->Register($_REQUEST['label'], V_EMPTY, 'The Label field must be filled in');

    if( $_REQUEST['type'] == FT_SELECT || $_REQUEST['type'] == FT_MULTI_SELECT )
        $validator->Register($_REQUEST['options'], V_EMPTY, 'The Options field must be filled in for this field type');

    if( $_REQUEST['validation'] != V_NONE )
        $validator->Register($_REQUEST['validation_message'], V_EMPTY, 'The Validation Error field must be filled in');

    if( $_REQUEST['name'] != $_REQUEST['old_name'] )
    {
        // Get pre-defined fields so there are no duplicates
        $predefined = $DB->GetColumns('lx_links');
        $validator->Register(in_array($_REQUEST['name'], $predefined), V_FALSE, 'The field name you have selected conflicts with a pre-defined field name');

        $field_count = $DB->Count('SELECT COUNT(*) FROM lx_link_field_defs WHERE name=?', array($_REQUEST['name']));
        $validator->Register($field_count, V_ZERO, 'A field with this name already exists');
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditLinkField();
        return;
    }

    $_REQUEST['options'] = FormatCommaSeparated($_REQUEST['options']);

    if( $_REQUEST['name'] != $_REQUEST['old_name'] )
        $DB->Update("ALTER TABLE lx_link_fields CHANGE {$_REQUEST['old_name']} {$_REQUEST['name']} TEXT");

    $DB->Update('UPDATE lx_link_field_defs SET ' .
                'name=?, ' .
                'label=?, ' .
                'type=?, ' .
                'tag_attributes=?, ' .
                'options=?, ' .
                'validation=?, ' .
                'validation_extras=?, ' .
                'validation_message=?, ' .
                'on_submit=?, ' .
                'on_edit=?, ' .
                'on_details=?, ' .
                'required=? ' .
                'WHERE field_id=?',
                array($_REQUEST['name'],
                      $_REQUEST['label'],
                      $_REQUEST['type'],
                      $_REQUEST['tag_attributes'],
                      $_REQUEST['options'],
                      $_REQUEST['validation'],
                      $_REQUEST['validation_extras'],
                      $_REQUEST['validation_message'],
                      intval($_REQUEST['on_submit']),
                      intval($_REQUEST['on_edit']),
                      intval($_REQUEST['on_details']),
                      intval($_REQUEST['required']),
                      $_REQUEST['field_id']));

    $GLOBALS['message'] = 'Link field has been successfully updated';
    $GLOBALS['added'] = true;

    lxShEditLinkField();
}

function lxShBrowse()
{
    global $DB, $C;

    include_once('includes/directory-browse.php');
}

function lxAddLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_ADD);

    // See if URL already exists
    $url_exists = $DB->Count('SELECT COUNT(*) FROM lx_links WHERE site_url=?', array($_REQUEST['site_url']));

    $validator = new Validator();
    $validator->Register($_REQUEST['email'], V_EMAIL, 'The email address is not properly formatted');
    $validator->Register($_REQUEST['site_url'], V_URL, 'The site URL is not properly formatted');
    $validator->Register($_REQUEST['category_id'], V_EMPTY, 'Please select at least one category for this link');
    $validator->Register($url_exists, V_ZERO, 'This URL is already in the database');
    $validator->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added field is not properly formatted');

    if( !empty($_REQUEST['date_modified']) )
    {
        $validator->Register($_REQUEST['date_modified'], V_DATETIME, 'The Date Modified field is not properly formatted');
    }

    // Handle improperly formatted expire dates
    if( !empty($_REQUEST['expires']) )
    {
        $validator->Register($_REQUEST['expires'], V_DATETIME, 'The expiration date value is not properly formatted');
    }

    // Make sure account exists
    if( $_REQUEST['username'] )
    {
        $account = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($_REQUEST['username']));
        $validator->Register($account, V_NOT_FALSE, "No user account exists with the username '{$_REQUEST['username']}'");

        $_REQUEST['recip_required'] = $account['recip_required'];
        $_REQUEST['allow_redirect'] = $account['allow_redirect'];
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddLink();
        return;
    }

    // Calculate average rating
    $rating_avg = null;
    if( $_REQUEST['ratings'] > 0 )
    {
        $rating_avg = $_REQUEST['rating_total']/$_REQUEST['ratings'];
    }

    // Encrypt the password
    if( !empty($_REQUEST['password']) )
    {
        $_REQUEST['password'] = sha1($_REQUEST['password']);
    }

    // Set default expiration date
    if( empty($_REQUEST['expires']) )
    {
        $_REQUEST['expires'] = DEF_EXPIRES;
    }

    // Scan the link to see if it has a recip
    $scan_result = ScanLink($_REQUEST);
    $has_recip = $scan_result['has_recip'];

    NullIfEmpty($_REQUEST['date_modified']);

    // Add regular fields
    $DB->Update('INSERT INTO lx_links VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array(null,
                      $_REQUEST['site_url'],
                      $_REQUEST['recip_url'],
                      $_REQUEST['title'],
                      $_REQUEST['description'],
                      $_REQUEST['status'],
                      $_REQUEST['type'],
                      $_REQUEST['expires'],
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_REQUEST['submit_ip'],
                      $_REQUEST['keywords'],
                      $_REQUEST['clicks'],
                      0,
                      '',
                      $_REQUEST['ratings'],
                      $_REQUEST['rating_total'],
                      $rating_avg,
                      $_REQUEST['weight'],
                      $_REQUEST['date_added'],
                      $_REQUEST['date_modified'],
                      0,
                      intval($_REQUEST['recip_required']),
                      intval($_REQUEST['allow_redirect']),
                      $_REQUEST['icons'],
                      $_REQUEST['admin_comments'],
                      $_REQUEST['username'],
                      $_REQUEST['password'],
                      $has_recip,
                      0,
                      ''));

    $_REQUEST['link_id'] = $DB->InsertID();

    // Add category data
    foreach( explode(',', $_REQUEST['category_id']) as $category_id )
    {
        $sorter = $DB->Count('SELECT MAX(sorter) FROM lx_link_cats WHERE category_id=?', array($category_id));
        $DB->Update('INSERT INTO lx_link_cats VALUES (?,?,?)', array($_REQUEST['link_id'], $category_id, $sorter));
        UpdateLinkCount($category_id);
    }

    // Add user defined fields
    $query_data = CreateUserInsert('lx_link_fields', $_REQUEST);
    $DB->Update('INSERT INTO lx_link_fields VALUES ('.$query_data['bind_list'].')', $query_data['binds']);

    // Update link count for account, if username was provided
    if( $_REQUEST['username'] )
    {
        $DB->Update('UPDATE lx_users SET num_links=num_links+1 WHERE username=?', array($_REQUEST['username']));
    }

    $GLOBALS['message'] = 'New link has been successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddLink();
}

function lxEditLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_MODIFY);

    // See if URL already exists
    $url_exists = $DB->Count('SELECT COUNT(*) FROM lx_links WHERE site_url=? AND link_id!=?', array($_REQUEST['site_url'], $_REQUEST['link_id']));

    $validator = new Validator();
    $validator->Register($_REQUEST['email'], V_EMAIL, 'The email address is not properly formatted');
    $validator->Register($_REQUEST['site_url'], V_URL, 'The site URL is not properly formatted');
    $validator->Register($_REQUEST['category_id'], V_EMPTY, 'Please select at least one category for this link');
    $validator->Register($url_exists, V_ZERO, 'This URL is already in the database');
    $validator->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added field is not properly formatted');

    // Handle improperly formatted expire dates
    if( !empty($_REQUEST['expires']) )
    {
        $validator->Register($_REQUEST['expires'], V_DATETIME, 'The expiration date is not properly formatted');
    }

    if( !empty($_REQUEST['date_modified']) )
    {
        $validator->Register($_REQUEST['date_modified'], V_DATETIME, 'The Date Modified field is not properly formatted');
    }

    // Make sure account exists
    if( $_REQUEST['username'] )
    {
        $account = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($_REQUEST['username']));
        $validator->Register($account, V_NOT_FALSE, "No user account exists with the username '{$_REQUEST['username']}'");
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditLink();
        return;
    }

    $link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($_REQUEST['link_id']));

    // Calculate average rating
    $rating_avg = null;
    if( $_REQUEST['ratings'] > 0 )
    {
        $rating_avg = $_REQUEST['rating_total']/$_REQUEST['ratings'];
    }

    // Encrypt the password
    if( !empty($_REQUEST['password']) )
    {
        $_REQUEST['password'] = sha1($_REQUEST['password']);
    }
    else
    {
        $_REQUEST['password'] = $link['password'];
    }

    if( empty($_REQUEST['expires']) )
    {
        $_REQUEST['expires'] = DEF_EXPIRES;
    }

    // Scan the link to see if it has a recip
    $scan_result = ScanLink($_REQUEST);
    $has_recip = $scan_result['has_recip'];

    NullIfEmpty($_REQUEST['date_modified']);

    // Update regular fields
    $DB->Update('UPDATE lx_links SET ' .
                'site_url=?, ' .
                'recip_url=?, ' .
                'title=?, ' .
                'description=?, ' .
                'status=?, ' .
                'type=?, ' .
                'expires=?, ' .
                'name=?, ' .
                'email=?, ' .
                'submit_ip=?, ' .
                'keywords=?, ' .
                'clicks=?, ' .
                'screenshot=?, ' .
                'ratings=?, ' .
                'rating_total=?, ' .
                'rating_avg=?, ' .
                'weight=?, ' .
                'date_added=?, ' .
                'date_modified=?, ' .
                'recip_required=?, ' .
                'allow_redirect=?, ' .
                'icons=?, ' .
                'admin_comments=?, ' .
                'username=?, ' .
                'password=?, ' .
                'has_recip=? ' .
                'WHERE link_id=?',
                array($_REQUEST['site_url'],
                      $_REQUEST['recip_url'],
                      $_REQUEST['title'],
                      $_REQUEST['description'],
                      $_REQUEST['status'],
                      $_REQUEST['type'],
                      $_REQUEST['expires'],
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_REQUEST['submit_ip'],
                      $_REQUEST['keywords'],
                      $_REQUEST['clicks'],
                      '',
                      $_REQUEST['ratings'],
                      $_REQUEST['rating_total'],
                      $rating_avg,
                      $_REQUEST['weight'],
                      $_REQUEST['date_added'],
                      $_REQUEST['date_modified'],
                      intval($_REQUEST['recip_required']),
                      intval($_REQUEST['allow_redirect']),
                      $_REQUEST['icons'],
                      $_REQUEST['admin_comments'],
                      $_REQUEST['username'],
                      $_REQUEST['password'],
                      $has_recip,
                      $_REQUEST['link_id']));

    // Get current categories this link is located in so the link count can be updated
    $old_categories = array();
    $result = $DB->Query('SELECT * FROM lx_link_cats WHERE link_id=?', array($_REQUEST['link_id']));
    while( $old_category = $DB->NextRow($result) )
    {
        $old_categories[] = $old_category['category_id'];
    }
    $DB->Free($result);

    // Update category data
    $DB->Update('DELETE FROM lx_link_cats WHERE link_id=?', array($_REQUEST['link_id']));
    foreach( explode(',', $_REQUEST['category_id']) as $category_id )
    {
        $sorter = $DB->Count('SELECT MAX(sorter) FROM lx_link_cats WHERE category_id=?', array($category_id));
        $DB->Update('INSERT INTO lx_link_cats VALUES (?,?,?)', array($_REQUEST['link_id'], $category_id, $sorter));
        UpdateLinkCount($category_id);
    }

    // Update the link count for the old categories this link was located in
    foreach($old_categories as $old_category)
    {
        UpdateLinkCount($old_category);
    }

    // Update user defined fields
    UserDefinedUpdate('lx_link_fields', 'lx_link_field_defs', 'link_id', $_REQUEST['link_id'], $_REQUEST);

    // If username was supplied, update link count
    if( $_REQUEST['username'] != $link['username'] )
    {
        if( !empty($link['username']) )
        {
            UpdateAccountLinkCount($link['username']);
        }

        if( !empty($_REQUEST['username']) )
        {
            UpdateAccountLinkCount($_REQUEST['username']);
        }
    }

    // Clear cache
    ClearLinkDetailsCache($_REQUEST['link_id']);

    $GLOBALS['message'] = 'Link has been successfully updated';
    $GLOBALS['added'] = true;

    lxShEditLink();
}

function lxShEditLink()
{
    global $C, $DB;

    VerifyPrivileges(P_LINK_MODIFY);

    $editing = TRUE;

    // First time or just updated, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($_REQUEST['link_id']));

        if( !$link )
        {
            $error = 'The link you are trying to edit no longer exists in the database';
            include_once('includes/error.php');
            return;
        }

        $link['date_added'] = date(DF_DATETIME, strtotime($link['date_added']));
        $link['date_modified'] = $link['date_modified'] == 0 ? '' : date(DF_DATETIME, strtotime($link['date_modified']));
        $link['expires'] = $link['expires'] == DEF_EXPIRES ? '' : $link['expires'];

        // Get categories
        $link['category_id'] = GetCategoryIdList($_REQUEST['link_id']);

        // Get user defined fields
        $link_user = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($_REQUEST['link_id']));

        $_REQUEST = array_merge($_REQUEST, $link_user, $link);
    }

    ArrayHSC($_REQUEST);

    unset($_REQUEST['password']);

    include_once('includes/link-add.php');
}

function lxMoveLink()
{
    global $DB, $C;

    VerifyPrivileges(P_LINK_MODIFY);

    $_REQUEST['link_id'] = explode(',', $_REQUEST['link_id']);

    $v = new Validator();
    $v->Register($_REQUEST['category_id'], V_EMPTY, 'Please select at least one category');

    if( !$v->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $v->GetErrors());
        lxShMoveLink();
        return;
    }

    $categories = explode(',', $_REQUEST['category_id']);

    foreach( $_REQUEST['link_id'] as $link_id )
    {
        $link_cats =& $DB->FetchAll('SELECT * FROM lx_link_cats WHERE link_id=?', array($link_id));
        $DB->Update('DELETE FROM lx_link_cats WHERE link_id=?', array($link_id));
        foreach($link_cats as $link_cat)
        {
            UpdateLinkCount($link_cat['category_id']);
            ClearCategoryCache($link_cat['category_id']);
        }

        foreach( $categories as $category_id )
        {
            $DB->Update('INSERT INTO lx_link_cats VALUES (?,?,?)', array($link_id, $category_id, null));
            UpdateLinkCount($category_id);
            ClearCategoryCache($category_id);
        }
    }

    $message = 'The selected links have been moved to their new categories';
    include_once('includes/message.php');
}

function lxShMoveLink()
{
    global $C, $DB;

    VerifyPrivileges(P_LINK_MODIFY);
    ArrayHSC($_REQUEST);

    include_once('includes/link-move.php');
}

function lxShAddLink()
{
    global $C, $DB;

    VerifyPrivileges(P_LINK_ADD);
    ArrayHSC($_REQUEST);

    include_once('includes/link-add.php');
}

function lxDeleteCategory()
{
    global $DB, $C;

    VerifyPrivileges(P_CATEGORY_REMOVE);
    DeleteCategory($_REQUEST['id']);

    $GLOBALS['message'] = 'The selected category has been deleted';

    lxShBrowse();
}

function lxAddCategory()
{
    global $DB, $C;

    VerifyPrivileges(P_CATEGORY_ADD);

    $validator = new Validator();
    $validator->Register($_REQUEST['name'], V_EMPTY, 'The category name must be filled in');
    $validator->Register($_REQUEST['name'], V_REGEX, 'The category name cannot contain a / or _ character', '/^[^\/_]*$/');
    $validator->Register($_REQUEST['name'], V_NOT_REGEX, 'The category name cannot contain a :: character sequence', '/::/');
    $validator->Register($_REQUEST['parent_id'], V_EMPTY, 'You must select a parent category');

    if( $_REQUEST['template'] )
    {
        $validator->Register($_REQUEST['template'], V_REGEX, 'The template name can only contain letters, numbers, periods and underscores', '/^[a-z0-9\.\-_]+$/');
        $validator->Register($_REQUEST['template'], V_REGEX, 'The template must have a .tpl file extension', '/\.tpl$/');
    }

    UnixFormat($_REQUEST['name']);
    UnixFormat($_REQUEST['url_name']);

    $names = explode("\n", trim($_REQUEST['name']));

    $url_names = array();
    if( !IsEmptyString($_REQUEST['url_name']) )
    {
        $url_names = explode("\n", trim($_REQUEST['url_name']));
    }

    if( count($url_names) > 0 && count($url_names) != count($names) )
    {
        $validator->SetError('You must enter the same number of category names as URL names');
    }

    foreach( $names as $name )
    {
        $name = trim($name);
        if( is_numeric($name) )
        {
            $validator->Register(TRUE, V_FALSE, "Category names cannot be all numeric ($name)");
        }
    }

    foreach( $url_names as $url_name )
    {
        $validator->Register($url_name, V_REGEX, 'The URL name can only contain English letters, numbers, dashes, and underscores', '/^[a-z0-9\-_]+$/i');
    }

    $parent = ($_REQUEST['parent_id'] == 0 ?
               $GLOBALS['ROOT_CATEGORY'] :
               $DB->Row('SELECT * FROM `lx_categories` WHERE `category_id`=?', array($_REQUEST['parent_id'])));


    foreach( $names as $i => $name )
    {
        if( IsEmptyString($name) )
        {
            continue;
        }

        if( !empty($url_names[$i]) && $DB->Count('SELECT COUNT(*) FROM `lx_categories` WHERE `name`=? AND `parent_id`=?', array($name, $url_names[$i], $_REQUEST['parent_id'])) )
        {
            $validator->SetError("A category with the name '$name' or URL name '{$url_names[$i]} already exists");
        }
        else if( $DB->Count('SELECT COUNT(*) FROM `lx_categories` WHERE `name`=? AND `parent_id`=?', array($name, $_REQUEST['parent_id'])) )
        {
            $validator->SetError("A category with the name '$name' already exists");
        }
    }



    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddCategory();
        return;
    }

    foreach( $names as $i => $name )
    {
        $name = trim($name);

        if( IsEmptyString($name) )
        {
            continue;
        }

        if( $_REQUEST['crosslink_id'] == '' )
        {
            $_REQUEST['crosslink_id'] = null;
        }

        NullIfEmpty($url_names[$i]);

        $DB->Update('INSERT INTO lx_categories VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    array(NULL,
                          $name,
                          $url_names[$i],
                          $_REQUEST['description'],
                          $_REQUEST['meta_description'],
                          $_REQUEST['meta_keywords'],
                          $_REQUEST['parent_id'],
                          '',
                          '',
                          '',
                          $_REQUEST['template'],
                          $_REQUEST['crosslink_id'],
                          $_REQUEST['related_ids'],
                          0,
                          0,
                          $_REQUEST['status'],
                          intval($_REQUEST['hidden'])));

        $category_id = $DB->InsertID();
        $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($category_id));

        $path = GeneratePathData($category, $parent);

        $DB->Update('UPDATE lx_categories SET ' .
                    'path=?, ' .
                    'path_parts=?, ' .
                    'path_hash=? ' .
                    'WHERE category_id=?',
                    array($path['path'],
                          $path['serialized'],
                          $path['hash'],
                          $category_id));

    }

    UpdateSubcategoryCount($parent['category_id']);

    $GLOBALS['message'] = 'New category successfully added';
    $GLOBALS['added'] = true;

    // Ask user to create custom template file if it does not exist
    if( $_REQUEST['template'] && !file_exists("{$GLOBALS['BASE_DIR']}/templates/{$_REQUEST['template']}") )
        $GLOBALS['message'] .= "<br />Please create the template file {$_REQUEST['template']} in the templates directory and set it's permissions to 666";

    UnsetArray($_REQUEST);
    lxShAddCategory();
}

function lxEditCategory()
{
    global $DB, $C;

    VerifyPrivileges(P_CATEGORY_MODIFY);

    // Count number of subcategories and links in this category
    $subcategories = $DB->Count('SELECT COUNT(*) FROM lx_categories WHERE parent_id=?', array($_REQUEST['category_id']));
    $links = $DB->Count('SELECT COUNT(*) FROM lx_link_cats WHERE category_id=?', array($_REQUEST['category_id']));

    $validator = new Validator();
    $validator->Register($_REQUEST['name'], V_EMPTY, 'The category name must be filled in');
    $validator->Register($_REQUEST['name'], V_REGEX, 'The category name cannot contain a / or _ character', '/^[^\/_]*$/');
    $validator->Register($_REQUEST['name'], V_NOT_REGEX, 'The category name cannot contain a :: character sequence', '/::/');
    $validator->Register($_REQUEST['parent_id'], V_EMPTY, 'You must select a parent category');
    $validator->Register(is_numeric($_REQUEST['name']), V_FALSE, 'The category name cannot be all numeric');

    if( $_REQUEST['template'] )
    {
        $validator->Register($_REQUEST['template'], V_REGEX, 'The template name can only contain letters, numbers, periods and underscores', '/^[a-z0-9\.\-_]+$/');
        $validator->Register($_REQUEST['template'], V_REGEX, 'The template must have a .tpl file extension', '/\.tpl$/');
    }

    if( $_REQUEST['parent_id'] == $_REQUEST['category_id'] )
    {
        $validator->SetError('A category cannot be it\'s own parent');
    }

    if( $_REQUEST['crosslink_id'] == $_REQUEST['category_id'] )
    {
        $validator->SetError('A crosslink category cannot point to itself');
    }

    if( !empty($_REQUEST['crosslink_id']) && ($subcategories > 0 || $links > 0) )
    {
        $validator->SetError('This category cannot be set to be a crosslink category because it contains sub-categories and/or links');
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditCategory();
        return;
    }

    $parent = ($_REQUEST['parent_id'] == 0 ?
               $GLOBALS['ROOT_CATEGORY'] :
               $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_REQUEST['parent_id'])));

    $_REQUEST['name'] = trim($_REQUEST['name']);
    $_REQUEST['url_name'] = trim($_REQUEST['url_name']);
    if( empty($_REQUEST['crosslink_id']) )
    {
        $_REQUEST['crosslink_id'] = null;
    }

    $old_category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_REQUEST['category_id']));

    $DB->Update('UPDATE lx_categories SET ' .
                'name=?, ' .
                'url_name=?, ' .
                'description=?, ' .
                'meta_description=?, ' .
                'meta_keywords=?, ' .
                'parent_id=?, ' .
                'path=?, ' .
                'path_parts=?, ' .
                'path_hash=?, ' .
                'template=?, ' .
                'crosslink_id=?, ' .
                'related_ids=?, ' .
                'status=?, ' .
                'hidden=? ' .
                'WHERE category_id=?',
                array($_REQUEST['name'],
                      $_REQUEST['url_name'],
                      $_REQUEST['description'],
                      $_REQUEST['meta_description'],
                      $_REQUEST['meta_keywords'],
                      $_REQUEST['parent_id'],
                      '',
                      '',
                      '',
                      $_REQUEST['template'],
                      $_REQUEST['crosslink_id'],
                      $_REQUEST['related_ids'],
                      $_REQUEST['status'],
                      intval($_REQUEST['hidden']),
                      $_REQUEST['category_id']));

    $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_REQUEST['category_id']));

    $path = GeneratePathData($category, $parent);

    $DB->Update('UPDATE lx_categories SET ' .
                'path=?, ' .
                'path_parts=?, ' .
                'path_hash=? ' .
                'WHERE category_id=?',
                array($path['path'],
                      $path['serialized'],
                      $path['hash'],
                      $_REQUEST['category_id']));

    UpdateSubcategoryCount($parent['category_id']);

    // Update path data for child categories if necessary
    if( $old_category['parent_id'] != $category['parent_id'] || $old_category['name'] != $category['name'] || $old_category['url_name'] != $category['url_name'] )
    {
        $category['path'] = $path['path'];
        $category['path_parts'] = $path['serialized'];
        $category['hash'] = $path['hash'];

        UpdateChildPaths($category);
    }

    $GLOBALS['message'] = 'Category successfully updated';
    $GLOBALS['added'] = true;

    // Ask user to create custom template file if it does not exist
    if( $_REQUEST['template'] && !file_exists("{$GLOBALS['BASE_DIR']}/templates/{$_REQUEST['template']}") )
        $GLOBALS['message'] .= "<br />Please create the template file {$_REQUEST['template']} in the templates directory and set it's permissions to 666";

    // Clear cache for this category and it's parent
    ClearCategoryCache($category['category_id'], $path['path']);
    ClearCategoryCache($category['parent_id']);

    lxShEditCategory();
}

function lxShSelectCategory()
{
    global $DB, $C;

    ArrayHSC($_REQUEST);

    include_once('includes/category-selector.php');
}

function lxShAddCategory()
{
    global $C;

    VerifyPrivileges(P_CATEGORY_ADD);
    ArrayHSC($_REQUEST);

    include_once('includes/category-add.php');
}

function lxShEditCategory()
{
    global $C, $DB;

    VerifyPrivileges(P_CATEGORY_MODIFY);

    $editing = TRUE;

    // First time or just updated, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_REQUEST['category_id']));
    }

    ArrayHSC($_REQUEST);

    include_once('includes/category-add.php');
}

function lxAddAdministrator()
{
    global $DB, $C;

    VerifyAdministrator();

    $user_count = $DB->Count('SELECT COUNT(*) FROM lx_administrators WHERE username=?', array($_REQUEST['username']));

    $validator = new Validator();
    $validator->Register($_REQUEST['username'], V_LENGTH, 'The username must be between 3 and 32 characters in length', array('min'=>3,'max'=>32));
    $validator->Register($_REQUEST['username'], V_ALPHANUM, 'The username can only contain letters and numbers');
    $validator->Register($_REQUEST['password'], V_LENGTH, 'The password must contain at least 4 characters', array('min'=>4,'max'=>999));
    $validator->Register($_REQUEST['email'], V_EMAIL, 'The e-mail address is not properly formatted');

    if( $user_count > 0 )
    {
        $validator->SetError('An administrator account already exists with that username');
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddAdministrator();
        return;
    }

    // Determine the privileges and notifications for this account
    $privileges = GenerateFlags($_REQUEST, '^p_');
    $notifications = GenerateFlags($_REQUEST, '^e_');

    // Add account data to the database
    $DB->Update('INSERT INTO lx_administrators VALUES (?,?,?,?,?,?,?,?,?,?)',
                array($_REQUEST['username'],
                      sha1($_REQUEST['password']),
                      NULL,
                      NULL,
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_REQUEST['type'],
                      $_REQUEST['categories'],
                      $notifications,
                      $privileges));

    $GLOBALS['message'] = 'New administrator successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddAdministrator();
}

function lxEditAdministrator()
{
    global $DB, $C;

    VerifyAdministrator();

    $administrator = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($_REQUEST['username']));

    $validator = new Validator();
    $validator->Register($_REQUEST['email'], V_EMAIL, 'The e-mail address is not properly formatted');
    if( $_REQUEST['password'] )
        $validator->Register($_REQUEST['password'], V_LENGTH, 'The password must contain at least 4 characters', array('min'=>4,'max'=>999));

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditAdministrator();
        return;
    }

    if( $_REQUEST['password'] )
    {
        // Password has changed, so invalidate any current session that may be active
        if( $_REQUEST['username'] != $_SERVER['REMOTE_USER'] )
        {
            $DB->Update('UPDATE lx_administrators SET session=NULL,session_start=NULL WHERE username=?', array($_REQUEST['username']));
        }

        $_REQUEST['password'] = sha1($_REQUEST['password']);
    }
    else
        $_REQUEST['password'] = $administrator['password'];

    // Determine the privileges and notifications for this account
    $privileges = GenerateFlags($_REQUEST, '^p_');
    $notifications = GenerateFlags($_REQUEST, '^e_');

    // Update account information
    $DB->Update('UPDATE lx_administrators SET ' .
                'password=?, ' .
                'name=?, ' .
                'email=?, ' .
                'type=?, ' .
                'categories=?, ' .
                'notifications=?, ' .
                'rights=? ' .
                'WHERE username=?',
                array($_REQUEST['password'],
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_REQUEST['type'],
                      $_REQUEST['categories'],
                      $notifications,
                      $privileges,
                      $_REQUEST['username']));

    $GLOBALS['message'] = 'Administrator account successfully updated';
    $GLOBALS['added'] = true;
    lxShEditAdministrator();
}

function lxMailAdministrator()
{
    global $DB, $C, $t;

    VerifyAdministrator();

    UnixFormat($_REQUEST['plain']);
    UnixFormat($_REQUEST['html']);

    $message = "=>[subject]\n" .
               $_REQUEST['subject'] . "\n" .
               "=>[plain]\n" .
               trim($_REQUEST['plain']) . "\n" .
               "=>[html]\n" .
               trim($_REQUEST['html']);

    $t = new Template();
    $t->assign_by_ref('config', $C);

    foreach( explode(',', $_REQUEST['to']) as $to_account )
    {
        $account = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($to_account));

        if( $account )
        {
            $t->assign_by_ref('account', $account);
            SendMail($account['email'], $message, $t, FALSE);
        }
    }

    $message = 'The selected administrator accounts have been e-mailed';
    include_once('includes/message.php');
}

function lxShMailAdministrator()
{
    global $DB, $C;

    VerifyAdministrator();

    ArrayHSC($_REQUEST);

    if( is_array($_REQUEST['username']) )
    {
        $_REQUEST['to'] = join(', ', $_REQUEST['username']);
        $_REQUEST['to_list'] = join(',', $_REQUEST['username']);
    }
    else
    {
        $_REQUEST['to'] = $_REQUEST['to_list'] = $_REQUEST['username'];
    }

    $function = 'lxMailAdministrator';
    include_once('includes/email-compose.php');
}

function lxShEditAdministrator()
{
    global $DB, $C;

    VerifyAdministrator();

    $editing = TRUE;

    // First time, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_administrators WHERE username=?', array($_REQUEST['username']));
    }

    unset($_REQUEST['password']);
    ArrayHSC($_REQUEST);

    include_once('includes/administrators-add.php');
}

function lxShAddAdministrator()
{
    global $DB, $C;

    VerifyAdministrator();
    ArrayHSC($_REQUEST);

    include_once('includes/administrators-add.php');
}

function lxShAdministrators()
{
    global $DB, $C;

    VerifyAdministrator();

    include_once('includes/administrators.php');
}

function lxShMailUser()
{
    global $DB, $C;

    VerifyPrivileges(P_USER);

    ArrayHSC($_REQUEST);

    if( is_array($_REQUEST['username']) )
    {
        $_REQUEST['to'] = join(', ', $_REQUEST['username']);
        $_REQUEST['to_list'] = join(',', $_REQUEST['username']);
    }
    else
    {
        $_REQUEST['to'] = $_REQUEST['to_list'] = $_REQUEST['username'];
    }

    $function = 'lxMailUser';
    include_once('includes/email-compose.php');
}

function lxMailUser()
{
    global $DB, $C, $t;

    VerifyPrivileges(P_USER);

    UnixFormat($_REQUEST['plain']);
    UnixFormat($_REQUEST['html']);

    $message = "=>[subject]\n" .
               $_REQUEST['subject'] . "\n" .
               "=>[plain]\n" .
               trim($_REQUEST['plain']) . "\n" .
               "=>[html]\n" .
               trim($_REQUEST['html']);

    $t = new Template();
    $t->assign_by_ref('config', $C);

    foreach( explode(',', $_REQUEST['to']) as $to_account )
    {
        $account = $DB->Row('SELECT * FROM lx_users JOIN lx_user_fields USING (username) WHERE lx_users.username=?', array($to_account));

        if( $account )
        {
            $t->assign_by_ref('account', $account);
            SendMail($account['email'], $message, $t, FALSE);
        }
    }

    $message = 'The selected user accounts have been e-mailed';
    include_once('includes/message.php');
}

function lxAddUser()
{
    global $DB, $C;

    VerifyPrivileges(P_USER_ADD);

    $user_count = $DB->Count('SELECT COUNT(*) FROM lx_users WHERE username=?', array($_REQUEST['username']));
    $mail_count = $DB->Count('SELECT COUNT(*) FROM lx_users WHERE email=?', array($_REQUEST['email']));


    $validator = new Validator();
    $validator->Register($_REQUEST['username'], V_LENGTH, 'The username must be between 3 and 32 characters in length', array('min'=>3,'max'=>32));
    $validator->Register($_REQUEST['username'], V_ALPHANUM, 'The username can only contain letters and numbers');
    $validator->Register($_REQUEST['password'], V_LENGTH, 'The password must contain at least 4 characters', array('min'=>4,'max'=>999));
    $validator->Register($_REQUEST['email'], V_EMAIL, 'The e-mail address is not properly formatted');
    $validator->Register($user_count, V_ZERO, 'A user account already exists with that username');
    $validator->Register($mail_count, V_ZERO, 'A user account already exists with that e-mail address');
    $validator->Register($_REQUEST['weight'], V_NUMERIC, 'The weight value must be numeric');
    $validator->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added field is not properly formatted');

    if( !empty($_REQUEST['date_modified']) )
    {
        $validator->Register($_REQUEST['date_modified'], V_DATETIME, 'The Date Modified field is not properly formatted');
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShAddUser();
        return;
    }

    NullIfEmpty($_REQUEST['date_modified']);

    // Add account data to the database
    $DB->Update('INSERT INTO lx_users VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array($_REQUEST['username'],
                      sha1($_REQUEST['password']),
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_REQUEST['date_added'],
                      $_REQUEST['date_modified'],
                      $_REQUEST['status'],
                      '',
                      NULL,
                      0,
                      intval($_REQUEST['recip_required']),
                      intval($_REQUEST['allow_redirect']),
                      $_REQUEST['weight']));

    // Add user defined fields
    $query_data = CreateUserInsert('lx_user_fields', $_REQUEST);
    $DB->Update('INSERT INTO lx_user_fields VALUES ('.$query_data['bind_list'].')', $query_data['binds']);

    $GLOBALS['message'] = 'New user account successfully added';
    $GLOBALS['added'] = true;
    UnsetArray($_REQUEST);
    lxShAddUser();
}

function lxEditUser()
{
    global $DB, $C;

    VerifyPrivileges(P_USER_ADD);

    $mail_count = $DB->Count('SELECT COUNT(*) FROM lx_users WHERE email=? AND username!=?', array($_REQUEST['email'], $_REQUEST['username']));

    $validator = new Validator();

    if( !empty($_REQUEST['password']) )
    {
        $validator->Register($_REQUEST['password'], V_LENGTH, 'The password must contain at least 4 characters', array('min'=>4,'max'=>999));
        $password = sha1($_REQUEST['password']);
    }

    $validator->Register($_REQUEST['email'], V_EMAIL, 'The e-mail address is not properly formatted');
    $validator->Register($mail_count, V_ZERO, 'A user account already exists with that e-mail address');
    $validator->Register($_REQUEST['weight'], V_NUMERIC, 'The weight value must be numeric');
    $validator->Register($_REQUEST['date_added'], V_DATETIME, 'The Date Added field is not properly formatted');

    if( !empty($_REQUEST['date_modified']) )
    {
        $validator->Register($_REQUEST['date_modified'], V_DATETIME, 'The Date Modified field is not properly formatted');
    }

    if( !$validator->Validate() )
    {
        $GLOBALS['errstr'] = join('<br />', $validator->GetErrors());
        lxShEditUser();
        return;
    }

    $account = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($_REQUEST['username']));
    if( empty($_REQUEST['password']) )
    {
        $password = $account['password'];
    }

    NullIfEmpty($_REQUEST['date_modified']);

    // Update account data in the database
    $DB->Update('UPDATE lx_users SET ' .
                'password=?, ' .
                'name=?, ' .
                'email=?, ' .
                'date_added=?, ' .
                'date_modified=?, ' .
                'status=?, ' .
                'recip_required=?, ' .
                'allow_redirect=?, ' .
                'weight=? ' .
                'WHERE username=?',
                array($password,
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_REQUEST['date_added'],
                      $_REQUEST['date_modified'],
                      $_REQUEST['status'],
                      intval($_REQUEST['recip_required']),
                      intval($_REQUEST['allow_redirect']),
                      $_REQUEST['weight'],
                      $_REQUEST['username']));

    // Update user defined fields
    UserDefinedUpdate('lx_user_fields', 'lx_user_field_defs', 'username', $_REQUEST['username'], $_REQUEST);

    // Update links with the new recip and redirect settings
    $DB->Update('UPDATE lx_links SET recip_required=?,allow_redirect=? WHERE username=?',
                array(intval($_REQUEST['recip_required']),
                      intval($_REQUEST['allow_redirect']),
                      $_REQUEST['username']));

    $GLOBALS['message'] = 'User account successfully updated';
    $GLOBALS['added'] = true;
    lxShEditUser();
}

function lxShAddUser()
{
    global $DB, $C;

    VerifyPrivileges(P_USER_ADD);
    ArrayHSC($_REQUEST);

    include_once('includes/users-add.php');
}

function lxShEditUser()
{
    global $DB, $C;

    VerifyPrivileges(P_USER_MODIFY);

    $editing = TRUE;

    // First time or updated, use database information
    if( !$_REQUEST['editing'] || $GLOBALS['added'] )
    {
        $_REQUEST = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($_REQUEST['username']));
        $user_fields = $DB->Row('SELECT * FROM lx_user_fields WHERE username=?', array($_REQUEST['username']));

        $_REQUEST = array_merge($_REQUEST, $user_fields);
    }

    unset($_REQUEST['password']);
    ArrayHSC($_REQUEST);

    include_once('includes/users-add.php');
}

function lxShUsers()
{
    global $DB, $C;

    VerifyPrivileges(P_USER);

    include_once('includes/users.php');
}

function lxShAddType()
{
    global $C;
    include_once('includes/type-add.php');
}

function lxShGeneralSettings()
{
    global $C;

    VerifyAdministrator();
    CheckAccessList();
    ArrayHSC($C);

    $C = array_merge($C, ($GLOBALS['_server_'] == null ? GetServerCapabilities() : $GLOBALS['_server_']));

    include_once('includes/settings-general.php');
}

function lxSaveGeneralSettings()
{
    global $C;

    VerifyAdministrator();
    CheckAccessList();

    $server = GetServerCapabilities();
    $GLOBALS['_server_'] = $server;

    $v = new Validator();

    $required = array('base_url' => 'Base URL',
                      'cookie_domain' => 'Cookie Domain',
                      'from_email' => 'E-mail Address',
                      'from_email_name' => 'E-mail Name',
                      'page_new' => 'New Links Page',
                      'page_popular' => 'Popular Links Page',
                      'page_top' => 'Top Links Page',
                      'page_details' => 'Link Details Page',
                      'extension' => 'File Extension',
                      'date_format' => 'Date Format',
                      'time_format' => 'Time Format',
                      'dec_point' => 'Decimal Point',
                      'thousands_sep' => 'Thousands Separator',
                      'min_desc_length' => 'Minimum Description Length',
                      'max_desc_length' => 'Maximum Description Length',
                      'min_title_length' => 'Maximum Title Length',
                      'max_title_length' => 'Maximum Title Length',
                      'max_keywords' => 'Maximum Keywords',
                      'link_weight' => 'Default Link Weight',
                      'min_comment_length' => 'Maximum Comment Length',
                      'max_comment_length' => 'Maximum Comment Length',
                      'max_rating' => 'Maximum Rating',
                      'font_dir' => 'Font Directory',
                      'min_code_length' => 'Minimum Code Length',
                      'max_code_length' => 'Maximum Code Length',
                      'cache_index' => 'Index Page Cache',
                      'cache_category' => 'Category Page Cache',
                      'cache_new' => 'New Link Page Cache',
                      'cache_popular' => 'Popular Links Page Cache',
                      'cache_top' => 'Top Links Page Cache',
                      'cache_search' => 'Search Page Cache',
                      'cache_details' => 'Details Page Cache');

    foreach($required as $field => $name)
    {
        $v->Register($_REQUEST[$field], V_EMPTY, "The $name field is required");
    }

    if( $v->Validate() )
    {
        if( !preg_match('~%d~', $_REQUEST['page_details']) )
        {
            if( strpos($_REQUEST['page_details'], '.') === FALSE )
            {
                $_REQUEST['page_details'] .= "%d";
            }
            else
            {
                $_REQUEST['page_details'] = preg_replace('~\.([^.]*)$~', '%d.$1', $_REQUEST['page_details']);
            }
        }

        $_REQUEST['extension'] = preg_replace('~^\.~', '', $_REQUEST['extension']);
        $_REQUEST['base_url'] = preg_replace('~/$~', '', $_REQUEST['base_url']);
        $_REQUEST['domain'] = preg_replace('~^www\.~', '', $_SERVER['HTTP_HOST']);
        $_REQUEST = array_merge($server, $_REQUEST);

        WriteConfig($_REQUEST);
        $GLOBALS['message'] = 'Your settings have been successfully updated';
    }
    else
    {
        $C = array_merge($C, $_REQUEST);
        $GLOBALS['errstr'] = join('<br />', $v->GetErrors());
    }

    lxShGeneralSettings();
}

function lxLogOut()
{
    global $DB;

    $DB->Update('UPDATE lx_administrators SET session=NULL,session_start=NULL WHERE username=?', array($_SERVER['REMOTE_USER']));

    setcookie('linkx', '', time()-3600);
    header('Expires: Mon, 26 Jul 1990 05:00:00 GMT');
    header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Location: index.php');
}


?>
