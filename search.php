<?php

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
    _astripslashes($_GET);
}

$page = isset($_GET['p']) ? $_GET['p'] : 1;
$too_short = strlen($_GET['s']) < 4;

// Track search terms
if( !$too_short && $page == 1 && $C['log_searches'] )
{
    if( $_COOKIE['lxsearch'] != $_GET['s'] )
    {
        logsearch();
    }

    setcookie('lxsearch', $_GET['s'], time()+86400, '/', $C['cookie_domain']);
}

// Indicate if logged in
$C['logged_in'] = isset($_COOKIE['linkxuser']);

$t = new Template();
$t->caching = ($C['cache_search'] > 0);
$t->cache_lifetime = $C['cache_search'];
$t->cache_dir = 'templates/cache_search';

$t->assign('search_term', $_GET['s']);
$t->assign('config', $C);
$t->assign('search_too_short', $too_short);

$t->display('directory-search.tpl', md5("{$_GET['s']}-$page"));

function logsearch()
{
    global $DB, $C;

    if( !isset($DB) )
    {
        $DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
        $DB->Connect();
    }

    if( preg_match_all('~[\'"]([^\'"]+)[\'"]|(\b\w+\b)~', $_GET['s'], $matches) )
    {
        $date = gmdate('Y-m-d H:i:s', _timewithtz());

        foreach( $matches[0] as $match )
        {
            $match = str_replace(array('"', '\''), '', $match);

            if( strlen($match) < 4 )
            {
                continue;
            }

            if( $DB->Update('UPDATE `lx_search_terms` SET `searches`=`searches`+1,`date_last_search`=? WHERE `term`=?', array($date, $match)) < 1 )
            {
                $DB->Update('INSERT INTO `lx_search_terms` VALUES (?,?,?,?)', array(null, $match, 1, $date));
            }
        }
    }
}

function _timewithtz($timestamp = null)
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

function &GetLinksSearch(&$attrs)
{
    global $C;

    $DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
    $DB->Connect();

    $links = array('links' => array(), 'pagination' => FALSE);

    $per_page = isset($attrs['perpage']) ? $attrs['perpage'] : 20;
    $page = isset($_GET['p']) ? $_GET['p'] : 1;
    $order_clause = (empty($attrs['order']) ? '' : "ORDER BY {$attrs['order']}");
    $query = "SELECT * FROM lx_links WHERE status='active' AND MATCH(title,description,keywords) AGAINST (? IN BOOLEAN MODE) $order_clause";
    $binds = array($_GET['s']);
    $links['pagination'] = $DB->QueryWithPagination($query, $binds, $page, $per_page);

    if( $links['pagination']['result'] )
    {
        while( $link = $DB->NextRow($links['pagination']['result']) )
        {
            $user_fields = $DB->Row('SELECT * FROM lx_link_fields WHERE link_id=?', array($link['link_id']));
            $link = array_merge($link, $user_fields);
            $link['date_added'] = strtotime($link['date_added']);
            $links['links'][] = $link;
        }

        $DB->Free($links['pagination']['result']);
    }

    unset($links['pagination']['result']);

    return $links;
}

function hilite($string)
{
    $term = $_GET['s'];

    if( $term )
    {
        if( isset($GLOBALS['re_matches']) || preg_match_all('~("[^"]+"|\b\w+\b)~', $term, $GLOBALS['re_matches']) )
        {
            foreach( $GLOBALS['re_matches'][0] as $match )
            {
                $match = quotemeta(str_replace(array('+', '-', '*', '"', '(', ')'), '', $match));
                $string = preg_replace("/\b($match)\b/i", "<span class=\"hilite\">$1</span>", $string);
            }
        }
    }

    return $string;
}

function _astripslashes(&$array)
{
    foreach($array as $key => $value)
    {
        if( is_array($array[$key]) )
        {
            _astripslashes($array[$key]);
        }
        else
        {
            $array[$key] = stripslashes($value);
        }
    }
}

?>
