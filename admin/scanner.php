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

$path = realpath(dirname(__FILE__));
chdir($path);

// Make sure CLI API is being used
if( php_sapi_name() != 'cli' )
{
    echo "Invalid access: This script requires the CLI version of PHP\n";
    exit;
}

require_once('../includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/http.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/htmlparser.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/admin/includes/functions.php");


// Get the configuration ID from command line parameter
$config_id = $GLOBALS['argv'][1];

// Define penalties
$penalties = array('ignore' => 0x00000000,
                   'report' => 0x00000001,
                   'disable' => 0x00000002,
                   'delete' => 0x00000004,
                   'blacklist' => 0x00000008);

// Exception bitmasks
$exceptions = array('connect' => 0x00000001,
                    'forward' => 0x00000002,
                    'broken' => 0x00000004,
                    'blacklist' => 0x00000008,
                    'norecip' => 0x00000010);

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();


// Get scanner configuration information
$config = $DB->Row('SELECT * FROM lx_scanner_configs WHERE config_id=?', array($config_id));
if( !$config )
{
    echo "Invalid configuration ID $config_id\n";
    exit;
}
$configuration = unserialize($config['configuration']);


// See if another instance of this scanner configuration is already running
if( $config['pid'] != 0 && $config['status_updated'] > time() - 300 )
{
    echo "This scanner configuration is already running\n";
    exit;
}


// Clear previous scan results
$DB->Update('DELETE FROM lx_scanner_results WHERE config_id=?', array($config_id));


// Set the last run time, pid, and status
$DB->Update('UPDATE lx_scanner_configs SET current_status=?,status_updated=?,date_last_run=?,pid=? WHERE config_id=?',
            array('Starting...',
                  time(),
                  MYSQL_NOW,
                  getmypid(),
                  $config_id));
                  

// Setup the MySQL query qualifier
$qualifier = SetupQualifier();                  

            
// Get the links to scan
$result = $DB->Query("SELECT * FROM lx_links JOIN lx_link_cats USING (link_id) $qualifier");
$current_link = 0;
$total_links = $DB->NumRows($result);


while( $link = $DB->NextRow($result) )
{
    $updates = array('placeholders' => array(), 'binds' => array());
    $exception = 0x00000000;
    $current_link++;
    
    // Exit if stopped (pid set to 0)
    $pid = $DB->Count('SELECT pid FROM lx_scanner_configs WHERE config_id=?', array($config_id));
    if( $pid == 0 )
    {
        break;
    }
    
    // Update scanner status
    $DB->Update('UPDATE lx_scanner_configs SET current_status=?,status_updated=? WHERE config_id=?', 
                array("Scanning link $current_link of $total_links",
                      time(),
                      $config_id));

    // Mark last scan time of the link
    $DB->Update('UPDATE lx_links SET date_scanned=? WHERE link_id=?', array(MYSQL_NOW, $link['link_id']));                      
              
                      
    // Scan the link
    $scan_result =& ScanLink($link);
    
    
    // Bad URL
    if( !$scan_result['site_url']['working'] )
    {
        // Bad status code
        if( !empty($scan_result['site_url']['status']) )
        {
            if( preg_match('~^3\d\d~', $scan_result['site_url']['status']) )
            {
                $exception = $exceptions['forward'];
            }
            else
            {
                $exception = $exceptions['broken'];
            }
        }
        
        // Connection error
        else
        {
            $exception = $exceptions['connect'];
        }
    }
    
    // Working URL
    else
    {
        // No reciprocal link found
        if( $link['recip_required'] && !$scan_result['site_url']['has_recip'] && !$scan_result['recip_url']['has_recip'] )
        {
            $exception |= $exceptions['norecip'];
        }
        
        // Check the blacklist
        if( ($blacklisted = CheckBlacklistLink($link)) !== FALSE )
        {
            $exception |= $exceptions['blacklist'];
            $scan_result['blacklist_item'] = $blacklisted[0]['match'];
        }
        
        $parser = new PageParser();
        $parser->parse($scan_result['site_url']['html']);

        $extracted_title = html_entity_decode(trim($parser->title));
        $extracted_description = html_entity_decode(trim($parser->description));
        $extracted_keywords = trim(FormatKeywords(html_entity_decode($parser->keywords)));
        
        if( $configuration['process_get_title'] && IsEmptyString($link['title']) )
        {
            $updates['placeholders'][] = '#=?'; 
            $updates['binds'][] = 'title';
            $updates['binds'][] = $extracted_title;
        }
        
        if( $configuration['process_get_description'] && IsEmptyString($link['description']) )
        {
            $updates['placeholders'][] = '#=?'; 
            $updates['binds'][] = 'description';
            $updates['binds'][] = $extracted_description;
        }
        
        if( $configuration['process_get_keywords'] && IsEmptyString($link['keywords']) )
        {
            $updates['placeholders'][] = '#=?'; 
            $updates['binds'][] = 'keywords';
            $updates['binds'][] = $extracted_keywords;
        }
    }
    
    
    $deleted = FALSE;
    if( $exception )
    {
        $deleted = ProcessLink($link, $scan_result, $exception);
    }
    
    if( !$deleted && count($updates['placeholders']) > 0 )
    {
        $updates['binds'][] = $link['link_id'];
         
        $DB->Update('UPDATE `lx_links` SET ' .
                    join(',', $updates['placeholders']) .
                    ' WHERE `link_id`=?',
                    $updates['binds']);
    }
}

$DB->Free($result);

// Mark the scanner as no longer running
$DB->Update('UPDATE lx_scanner_configs SET current_status=?,status_updated=?,pid=? WHERE config_id=?',
            array('Not Running',
                  time(),
                  0,
                  $config_id));
                  
$DB->Disconnect();

exit;

function ProcessLink(&$link, &$scan_result, $exception)
{
    global $configuration, $exceptions, $penalties, $DB, $config_id;
    
    $deleted = FALSE;
    $message = '';
    $penalty = 0x00000000;
    $reasons =  array('connect' => "Connection Error: {$scan_result['site_url']['error']}",
                      'forward' => "Redirecting URL: {$scan_result['site_url']['status']}",
                      'broken' => "Broken URL: {$scan_result['site_url']['status']}",                    
                      'blacklist' => "Blacklisted Data: " . htmlspecialchars($scan_result['blacklist_item']),
                      'norecip' => "No Reciprocal Link Found");


    // Determine the most strict penalty based on the infractions that were found
    foreach( $exceptions as $key => $value )
    {
        if( ($exception & $value) && ($configuration['action_'.$key] >= $penalty) )
        {
            $message = $reasons[$key];
            $penalty = intval($configuration['action_'.$key], 16);
        }
    }

    
    // Blacklist
    if( $penalty & $penalties['blacklist'] )
    {
        $action = 'Blacklisted';
        $deleted = TRUE; 

        AutoBlacklist($link);
        DeleteLink($link['link_id'], TRUE, $link);
    }

    // Delete
    else if( $penalty & $penalties['delete'] )
    {
        $action = 'Deleted';
        $deleted = TRUE;
        
        DeleteLink($link['link_id'], TRUE, $link);
    }

    // Disable
    else if( $penalty & $penalties['disable'] )
    {
        $action = 'Disabled';
        
        // Don't re-disable a link
        if( $link['status'] != 'disabled' )
        {
            $DB->Update('UPDATE lx_links SET status=? WHERE link_id=?', array('disabled', $link['link_id']));
            
            // Update category link count
            $result = $DB->Query('SELECT category_id FROM lx_links JOIN lx_link_cats USING (link_id) WHERE lx_links.link_id=?', array($link['link_id']));
            while( $category = $DB->NextRow($result) )
            {   
                UpdateLinkCount($category['category_id']);
            }        
            $DB->Free($result);
        }
    }

    // Display in report
    else if( $penalty & $penalties['report'] )
    {
        $action = 'Unchanged';
    }

    // Ignore
    else
    {
        // Do nothing
        return FALSE;
    }

    
    $DB->Update('INSERT INTO lx_scanner_results VALUES (?,?,?,?,?,?,?)',
                array($config_id,
                      $link['link_id'],
                      $link['site_url'],
                      $scan_result['site_url']['status'],
                      gmdate(DF_DATETIME, TimeWithTz()),
                      $action,
                      $message));
                      
    return $deleted;
}

function SetupQualifier()
{
    global $configuration, $DB;
    
    $qualifier = '';
    $wheres = array();


    // Scan only links with a specific status
    if( is_array($configuration['status']) )
    {
        $wheres[] = "status IN ('" . join("','", array_keys($configuration['status'])) . "')";
    }

    // Scan only links of a specific type
    if( is_array($configuration['type']) )
    {
        $wheres[] = "type IN ('" . join("','", array_keys($configuration['type'])) . "')";
    }

    
    // Configure date added range to scan
    if( !IsEmptyString($configuration['date_added_start']) && !IsEmptyString($configuration['date_added_end']) )
    {
        $wheres[] = "date_added BETWEEN '{$configuration['date_added_start']}' AND '{$configuration['date_added_end']}'";
    }
    
    // Configure date modified range to scan
    if( !IsEmptyString($configuration['date_modified_start']) && !IsEmptyString($configuration['date_modified_end']) )
    {
        $wheres[] = "date_modified BETWEEN '{$configuration['date_modified_start']}' AND '{$configuration['date_modified_end']}'";
    }
    
    // Configure date scanned range to scan
    if( !IsEmptyString($configuration['date_scanned_start']) && !IsEmptyString($configuration['date_scanned_end']) )
    {
        $wheres[] = "date_scanned BETWEEN '{$configuration['date_scanned_start']}' AND '{$configuration['date_scanned_end']}'";
    }

    // Configure categories to scan
    if( !empty($configuration['category_id']) )
    {
        $categories = array($configuration['category_id'] => 1);
        
        foreach( explode(',', $configuration['category_id']) as $category_id )
        {
            GetAllChildren($category_id, $categories);
        }
        
        $wheres[] = "category_id IN (" . join(',', array_keys($categories)) . ")";
    }

    
    if( count($wheres) > 0 )
    {
        $qualifier = "WHERE " . join(' AND ', $wheres);
    }   
    
    return $qualifier;
}

                  
?>
