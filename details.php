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

if( !defined('E_STRICT') ) define('E_STRICT', 2048);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);

require_once('includes/config.php');
require_once('includes/template.class.php');
require_once('includes/mysql.class.php');

@set_magic_quotes_runtime(0);
if( function_exists('date_default_timezone_set') )
{
    date_default_timezone_set('America/Chicago');
}

if( get_magic_quotes_gpc() )
{
    _ArrayStripSlashes($_GET);
}

$link = array('link_id' => $_GET['id']);
$cache_id = md5($_GET['id']);

// Indicate if logged in
$C['logged_in'] = isset($_COOKIE['linkxuser']);

$t = new Template();
$t->caching = ($C['cache_details'] > 0);
$t->cache_lifetime = $C['cache_details'];
$t->cache_dir = 'templates/cache_details';
$t->assign_by_ref('link', $link);

if( !$t->is_cached('directory-link-details.tpl', $cache_id) )
{
    $DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
    $DB->Connect();

    $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($_GET['id']));

    if( !$link )
    {
        require_once('includes/language.php');
        $t->caching = FALSE;
        $t->assign('error', $L['NO_SUCH_PAGE']);
        $t->assign_by_ref('config', $C);
        $t->display('error-nice.tpl');
        return;
    }

    // Get all categories for this link
    $categories = array();
    $result = $DB->Query('SELECT * FROM lx_link_cats JOIN lx_categories USING (category_id) WHERE link_id=?', array($_GET['id']));
    while( $category = $DB->NextRow($result) )
    {
        $category['path_parts'] = unserialize($category['path_parts']);
        $categories[] = $category;
    }
    $DB->Free($result);

    // Get user defined field definitions
    $user_fields = array();
    $result = $DB->Query('SELECT * FROM lx_link_field_defs');
    while( $field = $DB->NextRow($result) )
    {
        $field['value'] = $link[$field['name']];
        $user_fields[] = $field;
    }
    $DB->Free($result);


    // Get comments
    $comments = array();
    $result = $DB->Query('SELECT * FROM lx_link_comments WHERE link_id=? AND status=? ORDER BY date_added', array($link['link_id'], 'approved'));
    while( $comment = $DB->NextRow($result) )
    {
        $comment['date_added'] = strtotime($comment['date_added']);
        $comments[] = $comment;
    }
    $DB->Free($result);

    //$DB->Disconnect();

    $link['date_added'] = strtotime($link['date_added']);

    $t->assign_by_ref('comments', $comments);
    $t->assign_by_ref('categories', $categories);
    $t->assign_by_ref('link', $link);
    $t->assign_by_ref('user_fields', $user_fields);
}


$t->assign('message', $_GET['m']);
$t->assign_by_ref('config', $C);
$t->assign('ref_url', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
$t->display('directory-link-details.tpl', $cache_id);

function &GetLinksIn($category_id, &$attrs, &$t)
{
    global $DB;
    $links = array('links' => array(), 'pagination' => FALSE);
    $sorters = array('rating' => 'rating_avg DESC',
                     'alpha' => 'title',
                     'popularity' => 'clicks DESC',
                     'added' => 'date_added DESC',
                     'modified' => 'date_modified DESC');

    $DB->Connect();

    $order_clause = (empty($attrs['order']) ? '' : "ORDER BY {$attrs['order']}");
    $limit_clause = (empty($attrs['amount']) ? '' : "LIMIT {$attrs['amount']}");
    $result = $DB->Query("SELECT * FROM lx_links JOIN lx_link_cats USING (link_id) WHERE category_id=? AND type=? AND status='active' $order_clause $limit_clause",
                         array($category_id,
                               $attrs['type']));

    while( $link = $DB->NextRow($result) )
    {
        $link['date_added'] = strtotime($link['date_added']);
        $user_data = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($link['link_id']));
        $links['links'][] = array_merge($link, $user_data);
    }

    $DB->Free($result);

    return $links;
}

function _ArrayStripSlashes(&$array)
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

?>
