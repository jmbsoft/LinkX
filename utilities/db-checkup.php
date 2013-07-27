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
require_once("{$GLOBALS['BASE_DIR']}/admin/includes/functions.php");

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

echo "Re-counting sub-categories and links per category...";
flush();

$result = $DB->Query('SELECT * FROM lx_categories');
while( $category = $DB->NextRow($result) )
{
    UpdateSubcategoryCount($category['category_id']);
    UpdateLinkCount($category['category_id']);
}
$DB->Free($result);

echo "done<br />";
flush();


echo "Re-counting comments...";
flush();

$result = $DB->Query('SELECT * FROM lx_links');
while( $link = $DB->NextRow($result) )
{
    $comments = $DB->Count('SELECT COUNT(*) FROM lx_link_comments WHERE link_id=?', array($link['link_id']));
    $DB->Update('UPDATE lx_links SET comments=? WHERE link_id=?', array($comments, $link['link_id']));
}
$DB->Free($result);

echo "done<br />";
flush();

echo "Re-counting links per account...";
flush();

$result = $DB->Query('SELECT * FROM lx_users');
while( $user = $DB->NextRow($result) )
{
    $links = $DB->Count('SELECT COUNT(*) FROM lx_links WHERE username=?', array($user['username']));
    $DB->Update('UPDATE lx_users SET num_links=? WHERE username=?', array($links, $user['username']));
}
$DB->Free($result);

echo "done<br />";
flush();

echo "Database checkup is complete";

?>
