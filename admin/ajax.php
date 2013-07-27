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

define('LINKX', TRUE);

require_once('../includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/admin/includes/json.class.php");
require_once("{$GLOBALS['BASE_DIR']}/admin/includes/functions.php");

// Setup JSON response
$json = new JSON();

set_error_handler('AjaxError');

// Do not allow browsers to cache this script
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=ISO-8859-1");

SetupRequest();

// Setup database connection
$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

if( ($error = ValidLogin()) === TRUE )
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
else
{
    if( !$error )
        $error = 'Control panel login has expired';

    echo $json->encode(array('status' => JSON_FAILURE, 'message' => $error));
}

$DB->Disconnect();






/**
* Search user search terms
*/
function lxSearchTermSearch()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_search_terms', 'search-terms-tr.php');

    echo $json->encode($out);
}



/**
* Delete user search terms
*/
function lxSearchTermDelete()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( $_REQUEST['term_id'] == 'ALL' )
    {
        $DB->Update('DELETE FROM `lx_search_terms`');

        echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'All search terms have been deleted'));
    }
    else
    {
        if( !is_array($_REQUEST['term_id']) )
        {
            $_REQUEST['term_id'] = array($_REQUEST['term_id']);
        }

        foreach($_REQUEST['term_id'] as $term_id)
        {
            $DB->Update('DELETE FROM `lx_search_terms` WHERE `term_id`=?', array($term_id));
        }

        echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected search terms have been deleted'));
    }
}



/**
* Search advertisements
*/
function lxAdSearch()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_ads', 'ads-tr.php');

    echo $json->encode($out);
}



/**
* Delete an advertisement
*/
function lxAdDelete()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['ad_id']) )
    {
        $_REQUEST['ad_id'] = array($_REQUEST['ad_id']);
    }

    foreach($_REQUEST['ad_id'] as $ad_id)
    {
        $DB->Update('DELETE FROM `lx_ads` WHERE `ad_id`=?', array($ad_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected ads have been deleted'));
}



/**
* Extract the site title and description from HTML tags
*/
function lxExtractSiteInfo()
{
    global $json, $DB, $C;

    require_once("{$GLOBALS['BASE_DIR']}/includes/htmlparser.class.php");

    $link = array('site_url' => $_REQUEST['url'], 'allow_redirect' => TRUE, 'recip_url' => null);

    $result = ScanLink($link);

    if( $result['site_url']['working'] )
    {
        $parser = new PageParser();
        $parser->parse($result['site_url']['html']);

		$title = mb_convert_encoding($parser->title, 'ISO-8859-1', mb_detect_encoding($parser->title, 'auto'));
		$description = mb_convert_encoding($parser->description, 'ISO-8859-1', mb_detect_encoding($parser->description, 'auto'));
		$keywords = mb_convert_encoding($parser->keywords, 'ISO-8859-1', mb_detect_encoding($parser->keywords, 'auto'));

        echo $json->encode(array('status' => JSON_SUCCESS,
                                 'title' => html_entity_decode(trim($title)),
                                 'description' => html_entity_decode(trim($description)),
                                 'keywords' => trim(FormatKeywords(html_entity_decode($keywords)))));
    }
    else
    {
        echo $json->encode(array('status' => JSON_FAILURE));
    }
}


/**
* Get the current date and time with timezone offset
*/
function lxGetDate()
{
    global $json;

    $date = gmdate(($_REQUEST['dateonly'] ? DF_DATE : DF_DATETIME), TimeWithTz());
    echo $json->encode(array('status' => JSON_SUCCESS, 'date' => $date, 'field' => $_REQUEST['field']));
}



/**
* Start the link scanner
*/
function lxScannerStart()
{
    global $DB, $json, $C;

    VerifyAdministrator(TRUE);
    CheckAccessList(TRUE);

    exec("{$C['php_cli']} scanner.php " . escapeshellarg($_REQUEST['config_id']) . " >/dev/null 2>&1 &");

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'Your request to start the link scanner has been processed'));
}



/**
* Search link scanner results
*/
function lxShSearchScannerResults()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_scanner_results', 'link-scanner-results-tr.php', 'ScannerResultsSelect');

    echo $json->encode($out);
}



/**
* Callback for the GenericSearch function for scanner results
*
* @param object &$select The SelectBuilder object
* @returns bool
*/
function ScannerResultsSelect(&$select)
{
    $select->AddWhere('config_id', ST_MATCHES, $_REQUEST['config_id']);
    return FALSE;
}



/**
* Delete the selected scanner configurations
*/
function lxDeleteScannerConfig()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['config_id']) )
    {
        $_REQUEST['config_id'] = array($_REQUEST['config_id']);
    }

    foreach($_REQUEST['config_id'] as $config_id)
    {
        // Stop the scanner and wait a few seconds
        $DB->Update('UPDATE lx_scanner_configs SET pid=0 WHERE config_id=?', array($config_id));
        usleep(3500000);

        $DB->Update('DELETE FROM lx_scanner_configs WHERE config_id=?', array($config_id));
        $DB->Update('DELETE FROM lx_scanner_results WHERE config_id=?', array($config_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Tell the link scanner to stop
*/
function lxScannerStop()
{
    global $DB, $json, $C;

    VerifyAdministrator(TRUE);

    $DB->Update('UPDATE lx_scanner_configs SET pid=0,current_status=? WHERE config_id=?', array('Not Running', $_REQUEST['config_id']));

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'Your request to stop the link scanner has been processed'));
}



/**
* Get scanner status
*/
function lxScannerStatus()
{
    global $DB, $json, $C;

    VerifyAdministrator(TRUE);

    $configs = array();
    $result = $DB->Query('SELECT * FROM lx_scanner_configs');

    while( $config = $DB->NextRow($result) )
    {
        // Scanner most likely stopped
        if( $config['status_updated'] < time() - 600 )
        {
            $DB->Update('UPDATE lx_scanner_configs SET current_status=?,status_updated=?,pid=? WHERE config_id=?',
                        array('Not Running',
                              time(),
                              0,
                              $config['config_id']));

            $config['current_status'] = 'Not Running';
        }

        $config['date_last_run'] = $config['date_last_run'] ? date(DF_SHORT, strtotime($config['date_last_run'])) : '-';
        $configs[] = $config;
    }

    $DB->Free($result);

    echo $json->encode(array('status' => JSON_SUCCESS, 'configs' => $configs));
}



/**
* Display link scanner configurations
*/
function lxShSearchScannerConfigs()
{
    global $DB, $json, $C;

    $_REQUEST['order'] = 'identifier';
    $_REQUEST['direction'] = 'ASC';

    $out =& GenericSearch('lx_scanner_configs', 'link-scanner-tr.php');

    echo $json->encode($out);
}



/**
* Search links in the entire directory
*/
function lxShSearchLinks()
{
    global $DB, $json, $C;

    $GLOBALS['_fields_'] = array('site_url' => 'Site URL',
                                 'recip_url' => 'Recip URL',
                                 'email' => 'E-mail',
                                 'submit_ip' => 'Submitter IP');

    $GLOBALS['REJECTIONS'] =& $DB->FetchAll('SELECT * FROM `lx_rejections` ORDER BY `identifier`');
    $GLOBALS['_user_fields_'] =& $DB->FetchAll('SELECT * FROM `lx_link_field_defs` ORDER BY `name`');

    $out =& GenericSearch('lx_links', 'link-search-tr.php', 'SearchLinksCallback');

    if( extension_loaded('zlib') && !ini_get('zlib.output_compression') )
    {
        header('Content-Encoding: gzip');
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        echo gzcompress($json->encode($out), 9);
    }
    else
    {
        echo $json->encode($out);
    }
}



/**
* Callback for search links
*
* @param SelectBuilder $s The SelectBuilder object
*/
function SearchLinksCallback(&$s)
{
    $s = new SelectBuilder('*', 'lx_links');
    $s->AddJoin('lx_links', 'lx_link_fields', '', 'link_id');

    if( $_REQUEST['field'] == 'title,description,keywords' )
    {
         $s->AddFulltextWhere($_REQUEST['field'], $_REQUEST['search'], TRUE);
    }
    else
    {
        $s->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['search'], FALSE);
    }

    $s->AddWhere('status', ST_MATCHES, $_REQUEST['status'], TRUE);
    $s->AddWhere('is_edited', ST_MATCHES, $_REQUEST['is_edited'], TRUE);

    return TRUE;
}



/**
* Search links in a specific category
*/
function lxShSearchLinksInCat()
{
    global $DB, $json, $C;

    $GLOBALS['_fields_'] = array('site_url' => 'Site URL',
                                 'recip_url' => 'Recip URL',
                                 'email' => 'E-mail',
                                 'submit_ip' => 'Submitter IP');

    $GLOBALS['REJECTIONS'] =& $DB->FetchAll('SELECT * FROM `lx_rejections` ORDER BY `identifier`');
    $GLOBALS['_user_fields_'] =& $DB->FetchAll('SELECT * FROM `lx_link_field_defs` ORDER BY `name`');

    $out =& GenericSearch('lx_links', 'link-search-tr.php', 'SearchLinksInCatCallback');

    if( extension_loaded('zlib') && !ini_get('zlib.output_compression') )
    {
        header('Content-Encoding: gzip');
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        echo gzcompress($json->encode($out), 9);
    }
    else
    {
        echo $json->encode($out);
    }
}



/**
* Callback for search links in category
*
* @param SelectBuilder $s The SelectBuilder object
*/
function SearchLinksInCatCallback(&$s)
{
    $s = new SelectBuilder('*', 'lx_links');
    $s->AddJoin('lx_links', 'lx_link_cats', '', 'link_id');
    $s->AddJoin('lx_links', 'lx_link_fields', '', 'link_id');
    $s->AddWhere('category_id', ST_MATCHES, $_REQUEST['category_id'], TRUE);

    if( $_REQUEST['field'] == 'title,description,keywords' )
    {
         $s->AddMultiWhere(explode(',', $_REQUEST['field']), ST_CONTAINS, $_REQUEST['search'], TRUE);
    }
    else
    {
        $s->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['search'], $_REQUEST['search_type'] != ST_EMPTY);
    }

    $s->AddWhere('status', ST_MATCHES, $_REQUEST['status'], TRUE);

    if( isset($_REQUEST['is_edited']) )
    {
        $s->AddWhere('is_edited', ST_MATCHES, 1);
    }

    return TRUE;
}



/**
* Delete a link
*/
function lxDeleteLink()
{
    global $json, $DB;

    VerifyPrivileges(P_LINK_REMOVE, TRUE);

    if( !is_array($_REQUEST['link_id']) )
    {
        $_REQUEST['link_id'] = array($_REQUEST['link_id']);
    }

    foreach($_REQUEST['link_id'] as $link_id)
    {
        DeleteLink($link_id);

        if( $_REQUEST['config_id'] )
        {
            $DB->Update('UPDATE `lx_scanner_results` SET `action`=? WHERE `link_id`=? AND `config_id`=?', array('Deleted', $link_id, $_REQUEST['config_id']));
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected links have been deleted'));
}



/**
* Process new user links (approve/reject)
*/
function lxNewLink()
{
    global $json, $DB, $C;

    $t = new Template();
    $t->assign_by_ref('config', $C);

    VerifyPrivileges(P_LINK_MODIFY, TRUE);

    if( !is_array($_REQUEST['link_id']) )
    {
        $_REQUEST['link_id'] = array($_REQUEST['link_id']);
    }

    foreach($_REQUEST['link_id'] as $link_id)
    {
        $link = $DB->Row('SELECT * FROM `lx_links` JOIN `lx_link_fields` USING (`link_id`) WHERE `lx_links`.`link_id`=?', array($link_id));
        $t->assign_by_ref('link', $link);

        if( $link['status'] == 'pending' || $link['status'] == 'unconfirmed' )
        {
            if( $_REQUEST['w'] == 'approve' )
            {
                $link['status'] = 'active';
                $DB->Update('UPDATE `lx_links` SET `status`=? WHERE `link_id`=?', array('active', $link_id));

                // Update the link count in this category
                $result = $DB->Query('SELECT `category_id` FROM `lx_links` JOIN `lx_link_cats` USING (`link_id`) WHERE `lx_links`.`link_id`=?', array($link_id));
                while( $category = $DB->NextRow($result) )
                {
                    UpdateLinkCount($category['category_id']);
                }
                $DB->Free($result);

                // Send approval e-mail, if selected
                if( $_REQUEST['email'] == 'approval' )
                {
                    SendMail($link['email'], 'email-link-added.tpl', $t);
                }
            }
            else if( $_REQUEST['w'] == 'reject' )
            {
                DeleteLink($link_id, TRUE, $link);

                // Send rejection e-mail, if selected
                if( !empty($_REQUEST['email']) && $_REQUEST['email'] != 'approval' )
                {
                    $rejection = $DB->Row('SELECT * FROM `lx_rejections` WHERE `email_id`=?', array($_REQUEST['email']));

                    if( $rejection )
                    {
                        SendMail($link['email'], $rejection['plain'], $t, FALSE);
                    }
                }
            }
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Process link edits
*/
function lxEditedLink()
{
    global $json, $DB;

    VerifyPrivileges(P_LINK_MODIFY, TRUE);

    if( !is_array($_REQUEST['link_id']) )
    {
        $_REQUEST['link_id'] = array($_REQUEST['link_id']);
    }

    foreach($_REQUEST['link_id'] as $link_id)
    {
        $link = $DB->Row('SELECT * FROM `lx_links` WHERE `link_id`=?', array($link_id));

        if( $link['is_edited'] )
        {
            if( $_REQUEST['w'] == 'approve' )
            {
                $edit = unserialize(base64_decode($link['edit_data']));

                if( !IsEmptyString($edit['password']) )
                {
                    $edit['password'] = sha1($edit['password']);
                }
                else
                {
                    $edit['password'] = $link['password'];
                }

                // Update link data
                $DB->Update('UPDATE `lx_links` SET ' .
                            '`site_url`=?, ' .
                            '`recip_url`=?, ' .
                            '`title`=?, ' .
                            '`description`=?, ' .
                            '`name`=?, ' .
                            '`email`=?, ' .
                            '`submit_ip`=?, ' .
                            '`keywords`=?, ' .
                            '`date_modified`=?, ' .
                            '`password`=?, ' .
                            '`is_edited`=?, ' .
                            '`edit_data`=? ' .
                            'WHERE `link_id`=?',
                            array($edit['site_url'],
                                  $edit['recip_url'],
                                  $edit['title'],
                                  $edit['description'],
                                  $edit['name'],
                                  $edit['email'],
                                  $edit['submit_ip'],
                                  $edit['keywords'],
                                  MYSQL_NOW,
                                  $edit['password'],
                                  0,
                                  null,
                                  $link_id));

                // Update user defined fields
                UserDefinedUpdate('lx_link_fields', 'lx_link_field_defs', 'link_id', $link_id, $edit, FALSE);
            }
            else
            {
                $DB->Update('UPDATE lx_links SET is_edited=?,edit_data=? WHERE link_id=?', array(0, null, $link_id));
            }
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Change the status of a link
*/
function lxStatusLink()
{
    global $json, $DB;

    VerifyPrivileges(P_LINK_MODIFY, TRUE);

    if( !is_array($_REQUEST['link_id']) )
    {
        $_REQUEST['link_id'] = array($_REQUEST['link_id']);
    }

    $new_status = $_REQUEST['w'] == 'activate' ? 'active' : 'disabled';

    foreach($_REQUEST['link_id'] as $link_id)
    {
        $DB->Update('UPDATE lx_links SET status=? WHERE link_id=?', array($new_status, $link_id));

        // Update category link count
        $result = $DB->Query('SELECT category_id FROM lx_links JOIN lx_link_cats USING (link_id) WHERE lx_links.link_id=?', array($link_id));
        while( $category = $DB->NextRow($result) )
        {
            UpdateLinkCount($category['category_id']);
        }
        $DB->Free($result);
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected links have had their status updated'));
}



/**
* Blacklist a link
*/
function lxBlacklistLink()
{
    global $json, $DB;

    VerifyPrivileges(P_LINK_MODIFY, TRUE);

    if( !is_array($_REQUEST['link_id']) )
    {
        $_REQUEST['link_id'] = array($_REQUEST['link_id']);
    }

    foreach($_REQUEST['link_id'] as $link_id)
    {
        $link = $DB->Row('SELECT * FROM `lx_links` where `link_id`=?', array($link_id));

        if( $link )
        {
            AutoBlacklist($link);
            DeleteLink($link_id, TRUE, $link);
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Search bad link reports
*/
function lxShSearchReports()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_reports', 'reports-tr.php', 'SearchReportsCallback');

    echo $json->encode($out);
}


/**
* Callback function for SearchReports
*
* @param SelectBuilder $s The SelectBuilder object
*/
function SearchReportsCallback(&$s)
{
    $s->AddJoin('lx_reports', 'lx_links', '', 'link_id');
}


/**
* Process a bad link report
*/
function lxProcessReport()
{
    global $DB, $json, $C;

    VerifyPrivileges(P_LINK_MODIFY, TRUE);

    if( !is_array($_REQUEST['report_id']) )
    {
        $_REQUEST['report_id'] = array($_REQUEST['report_id']);
    }

    foreach($_REQUEST['report_id'] as $report_id)
    {
        $report = $DB->Row('SELECT * FROM lx_reports WHERE report_id=?', array($report_id));

        if( $report )
        {
            $link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($report['link_id']));

            switch($_REQUEST['w'])
            {
            case 'delete':
                DeleteLink($report['link_id'], TRUE, $link);
                break;

            case 'blacklist':
                DeleteLink($report['link_id'], TRUE, $link);
                AutoBlacklist($link);
                break;
            }

            $DB->Update('DELETE FROM lx_reports WHERE report_id=?', array($report_id));
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected link reports have been processed'));
}



/**
* Search news items
*/
function lxShSearchNews()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_news', 'news-tr.php');

    echo $json->encode($out);
}



/**
* Delete a news item
*/
function lxDeleteNews()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['news_id']) )
    {
        $_REQUEST['news_id'] = array($_REQUEST['news_id']);
    }

    foreach($_REQUEST['news_id'] as $news_id)
    {
        $DB->Update('DELETE FROM lx_news WHERE news_id=?', array($news_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Search reciprocal links
*/
function lxShSearchReciprocals()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_reciprocals', 'reciprocals-tr.php');

    echo $json->encode($out);
}



/**
* Delete a reciprocal link
*/
function lxDeleteReciprocal()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['recip_id']) )
    {
        $_REQUEST['recip_id'] = array($_REQUEST['recip_id']);
    }

    foreach($_REQUEST['recip_id'] as $recip_id)
    {
        $DB->Update('DELETE FROM lx_reciprocals WHERE recip_id=?', array($recip_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected reciprocal links have been deleted'));
}



/**
* Test a regular expression
*/
function lxRegexTest()
{
    global $json;

    $out = array('status' => JSON_SUCCESS, 'matches' => 'No', 'matched' => '');

    if( preg_match("~({$_REQUEST['regex']})~i", $_REQUEST['string'], $matches) )
    {
        $out['matches'] = 'Yes';
        $out['matched'] = $matches[0];
    }

    ArrayHSC($out);

    echo $json->encode($out);
}



/**
* Search blacklist items
*/
function lxShSearchBlacklist()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_blacklist', 'blacklist-tr.php', 'BlacklistSelect');

    echo $json->encode($out);
}



/**
* Callback for the GenericSearch function for blacklist/whitelist items
*
* @param object &$select The SelectBuilder object
* @returns bool
*/
function BlacklistSelect(&$select)
{
    $select->AddWhere('type', ST_MATCHES, $_REQUEST['type'], TRUE);
    return FALSE;
}




/**
* Delete a blacklist item
*/
function lxDeleteBlacklist()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['blacklist_id']) )
    {
        $_REQUEST['blacklist_id'] = array($_REQUEST['blacklist_id']);
    }

    foreach($_REQUEST['blacklist_id'] as $blacklist_id)
    {
        $DB->Update('DELETE FROM lx_blacklist WHERE blacklist_id=?', array($blacklist_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Perform a raw SQL query
*/
function lxRawQuery()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);
    CheckAccessList(TRUE);

    $affected = $DB->Update($_REQUEST['query']);

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => "SQL query has been executed; a total of $affected rows were affected by this query"));
}


/**
* Delete a rejection e-mail
*/
function lxDeleteRejection()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['email_id']) )
    {
        $_REQUEST['email_id'] = array($_REQUEST['email_id']);
    }

    foreach($_REQUEST['email_id'] as $email_id)
    {
        $DB->Update('DELETE FROM lx_rejections WHERE email_id=?', array($email_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected rejection e-mails have been deleted'));
}



/**
* Search rejection e-mails
*/
function lxShSearchRejections()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_rejections', 'rejections-tr.php');

    echo $json->encode($out);
}


/**
* Get the full category path for display
*/
function lxCategoryPath()
{
    global $DB, $json, $C;

    $paths = array();

    if( $_REQUEST['ids'] == '' )
    {
        echo '';
        return;
    }

    foreach( explode(',', $_REQUEST['ids']) as $id )
    {
        if( $id == 0 )
            $paths[] = 'Root';
        else
        {
            $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($id));
            $path = unserialize($category['path_parts']);
            $parts = array();

            foreach( $path as $part )
                $parts[] = $part['name'];

            $path = StringChop(join('/', $parts), 85, true, ' ... ');
            $path = str_replace(array('/'), array('<b>/</b>'), htmlspecialchars($path));
            $paths[] = $path;
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'html' => join('<br />', $paths)));
}



/**
* Expand category tree to show selected category
*/
function lxExpandCategoryDeep()
{
    global $DB, $C, $json;

    $ids = explode(',', $_REQUEST['id']);
    $out = array('status' => JSON_SUCCESS, 'results' => array(), 'category_id' => $ids);
    $results = array();



    foreach( $ids as $cat_id )
    {
        $id = $cat_id;

        while( $id == 0 || $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($id)) )
        {
            $category = ($id != 0 ? $category : array('category_id' => 0, 'parent_id' => -1));

            $data = &$results[];
            $data['category'] = $category;
            $data['subcats'] = array();

            // Do not get sub-category data for the selected category
            if( $id != $cat_id )
            {
                $result = $DB->Query('SELECT * FROM lx_categories WHERE parent_id=? ORDER BY name', array($category['category_id']));

                while( $inner_cat = $DB->NextRow($result) )
                {
                    $data['subcats'][] = $inner_cat;
                }

                $DB->Free($result);
            }

            $id = $category['parent_id'];
        }
    }

    foreach( array_reverse($results) as $result )
    {
        $data = &$out['results'][];
        $data['parent'] = $result['category']['category_id'];
        $data['html'] = '';

        foreach( $result['subcats'] as $subcat )
        {
            $data['html'] .= "<input name=\"cb\" type=\"{$_REQUEST['type']}\" class=\"{$_REQUEST['type']}\" value=\"{$subcat['category_id']}\" id=\"cb-{$subcat['category_id']}\" />\n" .
                             "<img src=\"images/folder.png\" border=\"0\"> <span class=\"category-" . ($subcat['subcategories'] > 0 ? 'expander' : 'name') . " {id: {$subcat['category_id']}}\" id=\"cat-id-{$subcat['category_id']}\">{$subcat['name']}</span>\n" .
                             "<div></div>\n";
        }
    }

    echo $json->encode($out);
}



/**
* Expand a parent category to show children
*/
function lxExpandCategory()
{
    global $DB, $C, $json;

    $out = array('status' => JSON_SUCCESS, 'html' => '', 'category_id' => $_REQUEST['id']);

    $result = $DB->Query('SELECT * FROM lx_categories WHERE parent_id=? ORDER BY name', array($_REQUEST['id']));

    while( $category = $DB->NextRow($result) )
    {
        ArrayHSC($category);
        $out['html'] .= "<input name=\"cb\" type=\"{$_REQUEST['type']}\" class=\"{$_REQUEST['type']}\" value=\"{$category['category_id']}\" id=\"cb-{$category['category_id']}\" />\n" .
                        "<img src=\"images/folder.png\" border=\"0\"> <span class=\"category-" . ($category['subcategories'] > 0 ? 'expander' : 'name') . " {id: {$category['category_id']}}\" id=\"cat-id-{$category['category_id']}\">{$category['name']}</span>\n" .
                        "<div></div>\n";
    }

    $DB->Free($result);

    echo $json->encode($out);
}



/**
* Delete a category
*/
function lxDeleteCategory()
{
    global $DB, $C, $json;

    VerifyPrivileges(P_CATEGORY_REMOVE, TRUE);

    if( !is_array($_REQUEST['category_id']) )
    {
        $_REQUEST['category_id'] = array($_REQUEST['category_id']);
    }

    foreach($_REQUEST['category_id'] as $category_id)
    {
        DeleteCategory($category_id);
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected categories have been deleted'));
}



/**
* Search categories for the category selector interface
*/
function lxShSearchCategoriesSelector()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_categories', 'category-selector-tr.php', 'SearchCategoriesCallback');

    echo $json->encode($out);
}



/**
* Search categories
*/
function lxShSearchCategories()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_categories', 'category-tr.php', 'SearchCategoriesCallback');

    echo $json->encode($out);
}



/**
* Search categories select callback
*
* @param SelectBuilder $s The SelectBuilder object
*/
function SearchCategoriesCallback($s)
{
    if( $_REQUEST['field'] == 'name' )
    {
        $s->AddFulltextWhere('name', $_REQUEST['search'], TRUE);
    }
    else
    {
        $s->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['search'], FALSE);
    }

    return TRUE;
}



/**
* Approve/reject a comment
*/
function lxNewComment()
{
    global $DB, $C, $json;

    VerifyPrivileges(P_COMMENT_MODIFY, TRUE);

    if( !is_array($_REQUEST['comment_id']) )
    {
        $_REQUEST['comment_id'] = array($_REQUEST['comment_id']);
    }

    foreach($_REQUEST['comment_id'] as $comment_id)
    {
        $comment = $DB->Row('SELECT * FROM lx_link_comments WHERE comment_id=?', array($comment_id));

        if( $comment['status'] == 'pending' )
        {
            if( $_REQUEST['status'] == 'approve' )
            {
                $DB->Update('UPDATE lx_links SET comments=comments+1 WHERE link_id=?', array($comment['link_id']));
                $DB->Update('UPDATE lx_link_comments SET status=? WHERE comment_id=?', array('approved', $comment_id));
            }
            else if( $_REQUEST['status'] == 'reject' )
            {
                $DB->Update('DELETE FROM lx_link_comments WHERE comment_id=?', array($comment_id));
            }
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected comments have been processed'));
}



/**
* Delete a comment
*/
function lxDeleteComment()
{
    global $DB, $C, $json;

    VerifyPrivileges(P_COMMENT_REMOVE, TRUE);

    if( !is_array($_REQUEST['comment_id']) )
    {
        $_REQUEST['comment_id'] = array($_REQUEST['comment_id']);
    }

    foreach($_REQUEST['comment_id'] as $comment_id)
    {
        $comment = $DB->Row('SELECT * FROM lx_link_comments WHERE comment_id=?', array($comment_id));
        $DB->Update('DELETE FROM lx_link_comments WHERE comment_id=?', array($comment_id));

        // Update comment count
        $comment_count = $DB->Count('SELECT COUNT(*) FROM lx_link_comments WHERE link_id=? AND status=?', array($comment['link_id'], 'approved'));
        $DB->Update('UPDATE lx_links SET comments=? WHERE link_id=?', array($comment_count, $comment['link_id']));
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Search comments
*/
function lxShSearchComments()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_link_comments', 'comment-tr.php', 'CommentSelect');

    echo $json->encode($out);
}



/**
* Callback for comment search select
*
* @param SelectBuilder $s The SelectBuilder object
*/
function CommentSelect($s)
{
    if( $_REQUEST['field'] == 'comment' )
    {
        $s->AddFulltextWhere('comment', $_REQUEST['search'], TRUE);
    }
    else
    {
        $s->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['search'], FALSE);
    }

    $s->AddWhere('status', ST_MATCHES, $_REQUEST['status'], TRUE);

    return TRUE;
}



/**
* Search user defined user account fields
*/
function lxShSearchUserFields()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_user_field_defs', 'users-fields-tr.php');

    echo $json->encode($out);
}



/**
* Delete a user account field
*/
function lxDeleteUserField()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['field_id']) )
    {
        $_REQUEST['field_id'] = array($_REQUEST['field_id']);
    }

    foreach($_REQUEST['field_id'] as $field_id)
    {
        $field = $DB->Row('SELECT * FROM lx_user_field_defs WHERE field_id=?', array($field_id));
        $DB->Update("ALTER TABLE lx_user_fields DROP COLUMN {$field['name']}");
        $DB->Update('DELETE FROM lx_user_field_defs WHERE field_id=?', array($field_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Search user defined link fields
*/
function lxShSearchLinkFields()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_link_field_defs', 'link-fields-tr.php');

    echo $json->encode($out);
}



/**
* Delete a link field
*/
function lxDeleteLinkField()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['field_id']) )
    {
        $_REQUEST['field_id'] = array($_REQUEST['field_id']);
    }

    foreach($_REQUEST['field_id'] as $field_id)
    {
        $field = $DB->Row('SELECT * FROM lx_link_field_defs WHERE field_id=?', array($field_id));
        $DB->Update("ALTER TABLE lx_link_fields DROP COLUMN {$field['name']}");
        $DB->Update('DELETE FROM lx_link_field_defs WHERE field_id=?', array($field_id));
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected user defined link fields have been deleted'));
}


/**
* Search administrator accounts
*/
function lxShSearchAdministrators()
{
    global $DB, $json, $C;

    $out =& GenericSearch('lx_administrators', 'administrators-tr.php');

    echo $json->encode($out);
}



/**
* Delete an administrator account
*/
function lxDeleteAdministrator()
{
    global $json, $DB;

    VerifyAdministrator(TRUE);

    if( !is_array($_REQUEST['username']) )
    {
        $_REQUEST['username'] = array($_REQUEST['username']);
    }

    // No deleting your own account
    if( in_array($_SERVER['REMOTE_USER'], $_REQUEST['username']) )
    {
        echo $json->encode(array('status' => JSON_FAILURE, 'message' => 'You cannot delete your own account'));
        exit;
    }

    foreach($_REQUEST['username'] as $username)
    {
        $DB->Update('DELETE FROM lx_administrators WHERE username=?', array($username));
    }

    echo $json->encode(array('status' => JSON_SUCCESS, 'message' => 'The selected administrator accounts have been deleted'));
}



/**
* Quick user search for the link submission form
*/
function lxQuickUserSearch()
{
    global $DB, $json;

    $out = array('status' => JSON_SUCCESS, 'results' => array());

    $select = new SelectBuilder('*', 'lx_users');
    $select->AddMultiWhere(array('username','email'), array(ST_CONTAINS, ST_CONTAINS), array($_REQUEST['term'], $_REQUEST['term']), TRUE);
    $select->AddOrder('username');
    $result = $DB->Query($select->Generate(), $select->binds);

    while( $account = $DB->NextRow($result) )
    {
        ArrayHSC($account);
        $out['results'][] = $account;
    }

    $DB->Free($result);

    echo $json->encode($out);
}



/**
* Search user accounts
*/
function lxShSearchUsers()
{
    global $DB, $json, $C;

    $GLOBALS['REJECTIONS'] =& $DB->FetchAll('SELECT * FROM `lx_rejections` ORDER BY `identifier`');
    $GLOBALS['_user_fields_'] =& $DB->FetchAll('SELECT * FROM `lx_user_field_defs` ORDER BY `name`');

    $out =& GenericSearch('lx_users', 'users-tr.php', 'SearchUsersSelect');

    echo $json->encode($out);
}


/**
* Search user accounts select callback
*/
function SearchUsersSelect($s)
{
    $s->AddJoin('lx_users', 'lx_user_fields', '', 'username');
    $s->AddWhere('status', ST_MATCHES, $_REQUEST['status'], TRUE);
}



/**
* Delete a user account
*/
function lxDeleteUser()
{
    global $json, $DB;

    VerifyPrivileges(P_USER_REMOVE, TRUE);

    if( !is_array($_REQUEST['username']) )
    {
        $_REQUEST['username'] = array($_REQUEST['username']);
    }

    foreach($_REQUEST['username'] as $username)
    {
        // Remove this user's links
        $result = $DB->Query('SELECT * FROM lx_links WHERE username=?', array($username));
        while( $link = $DB->NextRow($result) )
        {
            DeleteLink($link['link_id'], TRUE, $link);
        }
        $DB->Free($result);

        // Remove this user's comments
        $DB->Update('DELETE FROM lx_link_comments WHERE username=?', array($username));

        // Remove the user
        $DB->Update('DELETE FROM lx_user_fields WHERE username=?', array($username));
        $DB->Update('DELETE FROM lx_user_confirms WHERE username=?', array($username));
        $DB->Update('DELETE FROM lx_users WHERE username=?', array($username));
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Process new user accounts (approve/reject)
*/
function lxNewUser()
{
    global $json, $DB, $C;

    $t = new Template();
    $t->assign_by_ref('config', $C);

    VerifyPrivileges(P_USER_MODIFY, TRUE);

    if( !is_array($_REQUEST['username']) )
    {
        $_REQUEST['username'] = array($_REQUEST['username']);
    }

    foreach($_REQUEST['username'] as $username)
    {
        $account = $DB->Row('SELECT * FROM lx_users JOIN lx_user_fields USING (username) WHERE lx_users.username=?', array($username));
        $t->assign_by_ref('account', $account);

        if( $_REQUEST['w'] == 'approve' )
        {
            $account['status'] = 'active';
            $DB->Update('UPDATE lx_users SET status=? WHERE username=?', array('active', $username));

            // Send approval e-mail, if selected
            if( $_REQUEST['email'] == 'approval' )
            {
                SendMail($account['email'], 'email-account-added.tpl', $t);
            }
        }
        else if( $_REQUEST['w'] == 'reject' )
        {
            // Remove this user's links
            $result = $DB->Query('SELECT * FROM lx_links WHERE username=?', array($username));
            while( $link = $DB->NextRow($result) )
            {
                DeleteLink($link['link_id'], TRUE, $link);
            }
            $DB->Free($result);

            // Remove this user's comments
            $DB->Update('DELETE FROM lx_link_comments WHERE username=?', array($username));

            // Remove this user
            $DB->Update('DELETE FROM lx_user_fields WHERE username=?', array($username));
            $DB->Update('DELETE FROM lx_user_confirms WHERE username=?', array($username));
            $DB->Update('DELETE FROM lx_users WHERE username=?', array($username));

            // Send rejection e-mail, if selected
            if( !empty($_REQUEST['email']) && $_REQUEST['email'] != 'approval' )
            {
                $rejection = $DB->Row('SELECT * FROM lx_rejections WHERE email_id=?', array($_REQUEST['email']));

                if( $rejection )
                {
                    SendMail($account['email'], $rejection['plain'], $t, FALSE);
                }
            }
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* Change the status of a user account
*/
function lxStatusUser()
{
    global $json, $DB;

    VerifyPrivileges(P_USER_MODIFY, TRUE);

    if( !is_array($_REQUEST['username']) )
    {
        $_REQUEST['username'] = array($_REQUEST['username']);
    }

    foreach($_REQUEST['username'] as $username)
    {
        $DB->Update('UPDATE lx_users SET status=? WHERE username=?', array($_REQUEST['w'], $username));

        if( $_REQUEST['w'] == 'suspended' )
        {
            $DB->Update('UPDATE lx_links SET status=? WHERE username=?', array('disabled', $username));
        }
        else
        {
            $DB->Update('UPDATE lx_links SET status=? WHERE username=?', array('active', $username));
        }
    }

    echo $json->encode(array('status' => JSON_SUCCESS));
}



/**
* A generic search function for pulling results from a database table
*
* @param string $table The name of the table to grab results from
* @param mixed $files The filename(s) containing the PHP code to be parsed for each result
* @param string $select_callback The name of a function to call so custom options can be added to the select query
* @param string $item_callback The name of a function to call for each item pulled from the database so fields can be customized if needed
*/
function &GenericSearch($table, $files, $select_callback = null, $item_callback = null)
{
    global $C, $DB, $BLIST_TYPES;

    $out = array('status' => JSON_SUCCESS, 'html' => '', 'pagination' => $GLOBALS['DEFAULT_PAGINATION'], 'pagelinks' => '');

    $per_page = isset($_REQUEST['per_page']) && $_REQUEST['per_page'] > 0 ? $_REQUEST['per_page'] : 20;
    $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
    $select = new SelectBuilder('*', $table);
    $override = FALSE;

    if( function_exists($select_callback) )
    {
        $override = $select_callback($select);
    }

    if( !$override )
    {
        $select->AddWhere($_REQUEST['field'], $_REQUEST['search_type'], $_REQUEST['search'], $_REQUEST['search_type'] != ST_EMPTY);
    }

    $select->AddOrder($_REQUEST['order'], $_REQUEST['direction']);

    if( !empty($_REQUEST['order_next']) )
    {
        $select->AddOrder($_REQUEST['order_next'], $_REQUEST['direction_next']);
    }

    $result = $DB->QueryWithPagination($select->Generate(), $select->binds, $page, $per_page);

    $out['pagination'] = $result;
    $out['pagelinks'] = PageLinks($result);

    if( $result['result'] )
    {
        if( !is_array($files) )
        {
            $files = array($files);
        }

        $row_html = '';
        foreach( $files as $file )
        {
            $row_html .= file_get_contents("includes/$file");
        }

        while( $item = $DB->NextRow($result['result']) )
        {
            $original = $item;
            ArrayHSC($item);

            if( function_exists($item_callback) )
            {
                $item_callback($item);
            }

            ob_start();
            eval('?>' . $row_html);
            $out['html'] .= ob_get_contents();
            ob_end_clean();
        }

        $DB->Free($result['result']);
    }

    return $out;
}



/**
* Error handler for ajax requests
*/
function AjaxError($code, $string, $file, $line)
{
    global $json;

    $reporting = error_reporting();

    if( $reporting == 0 || !($code & $reporting) )
    {
        return;
    }

    $error = array();

    $error['message'] = "$string on line $line of " . basename($file);
    $error['status'] = JSON_FAILURE;

    echo $json->encode($error);

    exit;
}

?>
