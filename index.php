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

@set_magic_quotes_runtime(0);
if( function_exists('date_default_timezone_set') )
{
    date_default_timezone_set('America/Chicago');
}

require_once('includes/config.php');
require_once('includes/template.class.php');
require_once('includes/mysql.class.php');

if( get_magic_quotes_gpc() )
{
    _ArrayStripSlashes($_GET);
}

// Indicate if logged in
$C['logged_in'] = isset($_COOKIE['linkxuser']);

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);

switch($_GET['c'])
{
case '':
    DisplayIndex();
    break;
case $C['page_new']:
    DisplayNew();
    break;
case $C['page_top']:
    DisplayTopRated();
    break;
case $C['page_popular']:
    DisplayPopular();
    break;
default:
    DisplayCategory();
    break;
}

function &GetNews(&$attrs)
{
    global $DB;

    $DB->Connect();

    $limit_clause = (empty($attrs['amount']) ? '' : "LIMIT {$attrs['amount']}");

    $news = array();

    $result = $DB->Query("SELECT * FROM lx_news ORDER BY date_added DESC $limit_clause");

    while( $item = $DB->NextRow($result) )
    {
        $item['date'] = $item['date_added'] = strtotime($item['date_added']);
        $news[] = $item;
    }

    $DB->Free($result);

    return $news;
}

function &GetCategoriesIn($parent_id, $order = 'name DESC', $amount = 0)
{
    global $DB;
    $categories = array();

    $DB->Connect();

    $order_clause = (empty($order) ? '' : "ORDER BY $order");
    $limit_clause = (empty($amount) ? '' : "LIMIT $amount");

    $result = $DB->Query("SELECT * FROM lx_categories WHERE parent_id=? AND hidden=0 $order_clause $limit_clause", array($parent_id));

    while( $category = $DB->NextRow($result) )
    {
        if( $category['crosslink_id'] )
        {
            $crosslink = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($category['crosslink_id']));
            $category['path'] = $crosslink['path'];
            $category['path_parts'] = $crosslink['path_parts'];
            $category['category_id'] = $crosslink['category_id'];
            $category['links'] = $crosslink['links'];
            $category['subcategories'] = $crosslink['subcategories'];
        }

        $category['path_parts'] = unserialize($category['path_parts']);

        $categories[] = $category;
    }

    return $categories;
}

function &GetCategoriesRelated($related_ids)
{
    global $DB;
    $categories = array();

    if( empty($related_ids) )
        return $categories;

    $DB->Connect();

    $result = $DB->Query("SELECT * FROM lx_categories WHERE category_id IN ($related_ids)");

    while( $category = $DB->NextRow($result) )
    {
        if( $category['hidden'] )
        {
            continue;
        }

        if( $category['crosslink_id'] )
        {
            $crosslink = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($category['crosslink_id']));
            $category['path'] = $crosslink['path'];
            $category['path_parts'] = $crosslink['path_parts'];
            $category['category_id'] = $crosslink['category_id'];
        }

        $category['path_parts'] = unserialize($category['path_parts']);

        $categories[] = $category;
    }

    return $categories;
}

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

    $page = $t->vars['page_num'];
    $path = $t->vars['get_c'];
    $first_or_custom = ($page == 1 || isset($_GET['s']));
    $first_not_custom = ($page == 1 && !isset($_GET['s']));


    switch($attrs['type'])
    {
        case 'featured':
        {
            $order_clause = (empty($attrs['order']) ? '' : "ORDER BY {$attrs['order']}");
            $result = $DB->Query("SELECT * FROM lx_links JOIN lx_link_cats USING (link_id) WHERE category_id=? AND type='featured' AND status='active' $order_clause", array($category_id));

            while( $link = $DB->NextRow($result) )
            {
                $link['date_added'] = strtotime($link['date_added']);
                $user_data = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($link['link_id']));
                $links['links'][] = array_merge($link, $user_data);
            }

            $DB->Free($result);

            break;
        }

        case 'regular':
        default:
        {
            if( isset($_GET['s']) )
            {
                $attrs['order'] = $sorters[$_GET['s']];
            }

            $order_clause = (empty($attrs['order']) || !$first_or_custom ? 'ORDER BY sorter' : "ORDER BY {$attrs['order']}");
            $query = "SELECT * FROM lx_links JOIN lx_link_cats USING (link_id) WHERE category_id=? AND type!='featured' AND status='active' $order_clause";
            $binds = array($category_id);
            $links['pagination'] = $DB->QueryWithPagination($query, $binds, $page, $attrs['perpage'], ($first_not_custom ? TRUE : FALSE));

            if( $links['pagination']['result'] )
            {
                $added = 0;

                $DB->Update('SET @sort = 0');

                while( $link = $DB->NextRow($links['pagination']['result']) )
                {
                    $link['date_added'] = strtotime($link['date_added']);

                    $added++;

                    if( $added <= $attrs['perpage'] )
                    {
                        $user_data = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($link['link_id']));
                        $links['links'][] = array_merge($link, $user_data);
                    }

                    if( $first_not_custom )
                    {
                        $DB->Update('UPDATE lx_link_cats SET sorter=@sort:=@sort+1 WHERE category_id=? AND link_id=?', array($category_id, $link['link_id']));
                    }
                }

                $DB->Free($links['pagination']['result']);
            }

            if( $first_not_custom && $links['pagination']['pages'] > 1 )
            {
                foreach( range(2, $links['pagination']['pages']) as $pagenum )
                {
                    $t->clear_cache($t->vars['template'], md5("$path-$pagenum"));
                    $t->clear_cache('rss-directory-category.tpl', md5("$category_id-$pagenum"));
                }
            }

            unset($links['pagination']['result']);
            break;
        }
    }

    return $links;
}

function &GetLinksBy(&$attrs)
{
    global $DB;
    $links = array('links' => array(), 'pagination' => FALSE);
    $method = array('new' => 'date_added DESC', 'top' => 'rating_avg DESC', 'popular' => 'clicks DESC');

    $DB->Connect();

    $attrs['amount'] = empty($attrs['amount']) ? 30 : $attrs['amount'];

    $result = $DB->Query("SELECT * FROM lx_links WHERE `status`='active' ORDER BY {$method[$attrs['type']]} LIMIT ?", array($attrs['amount']));
    while( $link = $DB->NextRow($result) )
    {
        $link['date_added'] = strtotime($link['date_added']);

        // Get user fields
        $user_data = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($link['link_id']));

        // Get category data
        $categories = array();
        $catresult = $DB->Query('SELECT * FROM lx_link_cats JOIN lx_categories USING (category_id) WHERE link_id=?', array($link['link_id']));
        while( $category = $DB->NextRow($catresult) )
        {
            $category['path_parts'] = unserialize($category['path_parts']);
            $categories[] = $category;
        }
        $DB->Free($catresult);

        $link['categories'] = $categories;
        $links['links'][] = array_merge($link, $user_data);
    }

    $DB->Free($result);

    return $links;
}

function DisplayNew()
{
    global $C, $DB;

    $t = new Template();

    $t->caching = TRUE;
    $t->cache_lifetime = $C['cache_new'];
    $t->assign_by_ref('config', $C);
    $t->display('directory-new.tpl');
}

function DisplayTopRated()
{
    global $C, $DB;

    $t = new Template();

    $t->caching = TRUE;
    $t->cache_lifetime = $C['cache_top'];
    $t->assign_by_ref('config', $C);
    $t->display('directory-top-rated.tpl');
}

function DisplayPopular()
{
    global $C, $DB;

    $t = new Template();

    $t->caching = TRUE;
    $t->cache_lifetime = $C['cache_popular'];
    $t->assign_by_ref('config', $C);
    $t->display('directory-popular.tpl');
}

function DisplayIndex()
{
    global $C, $DB;

    $t = new Template();

    $t->caching = TRUE;
    $t->cache_lifetime = $C['cache_index'];

    if( !$t->is_cached('index.tpl') )
    {
        $DB->Connect();

        // See if it is time to disable some featured or premium links
        $DB->Update('UPDATE lx_links SET type=?,expires=? WHERE expires BETWEEN ? AND ?', array('regular', DEF_EXPIRES, '2000-01-01 00:00:00', MYSQL_NOW));

        // Get total number of links and categories
        $t->assign('total_links', $DB->Count('SELECT COUNT(*) FROM lx_links'));
        $t->assign('total_categories', $DB->Count('SELECT COUNT(*) FROM lx_categories'));
    }

    $t->assign('index_page', TRUE);
    $t->assign('this_category', array('category_id' => 0));
    $t->assign_by_ref('config', $C);
    $t->display('directory-index.tpl');
}

function DisplayCategory()
{
    global $C, $DB;

    $t = new Template();

    $t->caching = TRUE;
    $t->cache_lifetime = $C['cache_category'];

    $custom_sort = isset($_GET['s']);
    $from_path = FALSE;
    $template = 'directory-category.tpl';

    if( $custom_sort )
    {
        $t->caching = FALSE;
    }

    if( $C['mod_rewrite'] && !is_numeric($_GET['c']) )
    {
        $_GET['c'] = preg_replace('~^/|/$~', '', $_GET['c']);

        if( preg_match("~(.*?)/(\d+)\.{$C['extension']}$~", $_GET['c'], $matches) )
        {
            $_GET['c'] = $matches[1];
            $_GET['p'] = $matches[2];
        }

        $from_path = TRUE;
    }

    $_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 1;
    $cache_id = md5($_GET['c'] . '-' . $_GET['p']);

    if( $custom_sort || $C['custom_categories'] || !$t->is_cached($template, $cache_id) )
    {
        $DB->Connect();

        // Get the category
        if( $from_path )
        {
            $category = $DB->Row('SELECT * FROM lx_categories WHERE path_hash=?', array(md5($_GET['c'])));
        }
        else
        {
            $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_GET['c']));
        }

        if( $category['template'] )
            $template = $category['template'];

        if( !$category )
        {
            require_once('includes/language.php');
            $t->caching = FALSE;
            $t->assign('error', $L['NO_SUCH_PAGE']);
            $t->assign_by_ref('config', $C);
            $t->display('error-nice.tpl');
            return;
        }
    }

    if( $custom_sort || !$t->is_cached($template, $cache_id) )
    {
        $category['path_parts'] = unserialize($category['path_parts']);
        $t->assign_by_ref('this_category', $category);
        $t->assign_by_ref('config', $C);
        $t->assign('page_num', $_GET['p']);
        $t->assign('get_c', $_GET['c']);
        $t->assign('template', $template);
        $t->assign_by_ref('request', $_GET);
    }

    $t->display($template, $cache_id);
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
