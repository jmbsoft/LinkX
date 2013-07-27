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
require_once('includes/mysql.class.php');

if( $_GET['id'] )
{
    if( !isset($_COOKIE['linkx_click']) || !strstr(",{$_COOKIE['linkx_click']},", ",{$_GET['id']},") )
    {
        $DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
        $DB->Connect();
        $DB->Update('UPDATE lx_links SET clicks=clicks+1 WHERE link_id=?', array($_GET['id']));
        $DB->Disconnect();

        $cookie = isset($_COOKIE['linkx_click']) ? "{$_COOKIE['linkx_click']},{$_GET['id']}" : $_GET['id'];
        setcookie('linkx_click', $cookie, time() + 604800, '/', $C['cookie_domain']);
    }

    if( !isset($_GET['f']) )
    {
        header("Location: {$_GET['u']}");
    }
}
else
{
    header("Location: {$C['base_url']}/");
}

?>