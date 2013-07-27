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

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);

$cache_id = null;
$views = array('top' => array('template' => 'rss-directory-top-rated.tpl', 'cache' => $C['cache_top']),
               'popular' => array('template' => 'rss-directory-popular.tpl', 'cache' => $C['cache_popular']),
               'new' => array('template' => 'rss-directory-new.tpl', 'cache' => $C['cache_new']));

$t = new Template();
$t->assign_by_ref('config', $C);

if( isset($views[$_GET['p']]) )
{
    $view = $views[$_GET['p']];
}
else
{
    $view = array('template' => 'rss-directory-category.tpl', 'cache' => $C['cache_category']);

    $_GET['p'] = isset($_GET['p']) ? $_GET['p'] : 1;
    $cache_id = md5($_GET['c'] . '-' . $_GET['p']);

    if( !$t->is_cached($view['template'], $cache_id) )
    {
        $DB->Connect();

        $category = $DB->Row('SELECT * FROM `lx_categories` WHERE `category_id`=?', array($_GET['c']));

        if( !$category )
        {
            require_once('includes/language.php');
            $t->caching = FALSE;
            $t->assign('error', $L['NO_SUCH_PAGE']);
            $t->display('error-nice.tpl');
            return;
        }

        $category['path_parts'] = unserialize($category['path_parts']);
        $t->assign_by_ref('this_category', $category);
        $t->assign('page_num', $_GET['p']);
        $t->assign('get_c', $_GET['c']);
        $t->assign_by_ref('request', $_GET);
    }
}

$t->caching = TRUE;
$t->cache_lifetime = $view['cache'];

header("Content-type: text/xml");
$t->display($view['template'], $cache_id, TRUE);

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

            $query = "SELECT * FROM lx_links JOIN lx_link_cats USING (link_id) WHERE category_id=? AND type!='featured' AND status='active' ORDER BY sorter";
            $binds = array($category_id);
            $links['pagination'] = $DB->QueryWithPagination($query, $binds, $page, $attrs['perpage'], FALSE);

            if( $links['pagination']['result'] )
            {
                $added = 0;

                $DB->Update('SET @sort = 0');

                while( $link = $DB->NextRow($links['pagination']['result']) )
                {
                    $link['date_added'] = strtotime($link['date_added']);
                    $user_data = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($link['link_id']));
                    $links['links'][] = array_merge($link, $user_data);
                }

                $DB->Free($links['pagination']['result']);
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
            _ArrayStripSlashes($array[$key]);
        }
        else
        {
            $array[$key] = stripslashes($value);
        }
    }
}
?>
