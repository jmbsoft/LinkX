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

// Do not allow browsers to cache this script
header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

define('LINKX', TRUE);

require_once('../includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();


// Load table
IniParse("{$GLOBALS['BASE_DIR']}/includes/tables.php", TRUE, $table_defs);

// Re-create any missing tables
foreach( $table_defs as $name => $create )
{
    $DB->Update("CREATE TABLE IF NOT EXISTS `$name` ( $create ) TYPE=MyISAM");
}

// Remove status index of the lx_links table
$indexes =& $DB->FetchAll('SHOW INDEX FROM `lx_links`', null, 'Column_name');
if( isset($indexes['status']) )
{
    $DB->Update('ALTER TABLE `lx_links` DROP INDEX `status`');
}

// Add index to the path row of the lx_categories table
$indexes =& $DB->FetchAll('SHOW INDEX FROM `lx_categories`', null, 'Column_name');
if( !isset($indexes['path']) )
{
    $DB->Update('ALTER TABLE `lx_categories` ADD INDEX (`path`(255))');
}

$columns = $DB->GetColumns('lx_categories');
if( !in_array('url_name', $columns) )
{
    $DB->Update('ALTER TABLE `lx_categories` ADD COLUMN `url_name` TEXT AFTER `name`');
}


$describe = $DB->Row('DESCRIBE `lx_reports` `date_added`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_reports` MODIFY COLUMN `date_added` TEXT');
    $DB->Update('UPDATE `lx_reports` SET `date_added`=FROM_UNIXTIME(`date_added`)');
    $DB->Update('ALTER TABLE `lx_reports` MODIFY COLUMN `date_added` DATETIME');
}



$describe = $DB->Row('DESCRIBE `lx_news` `date_added`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_news` MODIFY COLUMN `date_added` TEXT');
    $DB->Update('UPDATE `lx_news` SET `date_added`=FROM_UNIXTIME(`date_added`)');
    $DB->Update('ALTER TABLE `lx_news` MODIFY COLUMN `date_added` DATETIME');
}



$describe = $DB->Row('DESCRIBE `lx_link_comments` `date_added`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_link_comments` MODIFY COLUMN `date_added` TEXT');
    $DB->Update('UPDATE `lx_link_comments` SET `date_added`=FROM_UNIXTIME(`date_added`)');
    $DB->Update('ALTER TABLE `lx_link_comments` MODIFY COLUMN `date_added` DATETIME');
}



$describe = $DB->Row('DESCRIBE `lx_users` `date_added`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_users` MODIFY COLUMN `date_added` TEXT');
    $DB->Update('UPDATE `lx_users` SET `date_added`=FROM_UNIXTIME(`date_added`)');
    $DB->Update('ALTER TABLE `lx_users` MODIFY COLUMN `date_added` DATETIME');
}

$describe = $DB->Row('DESCRIBE `lx_users` `date_modified`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_users` MODIFY COLUMN `date_modified` TEXT');
    $DB->Update('UPDATE `lx_users` SET `date_modified`=FROM_UNIXTIME(`date_modified`)');
    $DB->Update('ALTER TABLE `lx_users` MODIFY COLUMN `date_modified` DATETIME');
}



$describe = $DB->Row('DESCRIBE `lx_links` `date_added`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_links` DROP INDEX `date_added`');
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `date_added` TEXT');
    $DB->Update('UPDATE `lx_links` SET `date_added`=FROM_UNIXTIME(`date_added`)');
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `date_added` DATETIME');
    $DB->Update('ALTER TABLE `lx_links` ADD INDEX (`date_added`)');
}

$describe = $DB->Row('DESCRIBE `lx_links` `date_modified`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `date_modified` TEXT');
    $DB->Update('UPDATE `lx_links` SET `date_modified`=FROM_UNIXTIME(`date_modified`)');
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `date_modified` DATETIME');
}

$describe = $DB->Row('DESCRIBE `lx_links` `date_scanned`');
if( stristr($describe['Type'], 'date') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `date_scanned` TEXT');
    $DB->Update('UPDATE `lx_links` SET `date_scanned`=FROM_UNIXTIME(`date_scanned`)');
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `date_scanned` DATETIME');
}


$describe = $DB->Row('DESCRIBE `lx_links` `expires`');
if( stristr($describe['Type'], 'datetime') === FALSE )
{
    $DB->Update('ALTER TABLE `lx_links` MODIFY COLUMN `expires` DATETIME');
}


$columns = $DB->GetColumns('lx_scanner_configs');
if( in_array('last_run', $columns) )
{
    $DB->Update('ALTER TABLE `lx_scanner_configs` CHANGE COLUMN `last_run` `date_last_run` DATETIME');
    $DB->Update('UPDATE `lx_scanner_configs` SET `date_last_run`=NULL');
}

$columns = $DB->GetColumns('lx_scanner_results');
if( in_array('scan_time', $columns) )
{
    $DB->Update('DELETE FROM `lx_scanner_results`');
    $DB->Update('ALTER TABLE `lx_scanner_results` CHANGE COLUMN `scan_time` `date_scanned` DATETIME NOT NULL');
}

$DB->Disconnect();


echo "Patching has been completed successfully";

?>