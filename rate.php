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


require_once('includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/validator.class.php");

SetupRequest();

$_REQUEST['rating'] = intval($_REQUEST['rating']);

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

$t = new Template();
$t->assign_by_ref('config', $C);

$v = new Validator();
$v->Register($_REQUEST['rating'], V_BETWEEN, sprintf($L['RATING_RANGE'], $C['max_rating']), "1,{$C['max_rating']}");

// Verify captcha code
if( $C['rate_captcha'] )
{
    VerifyCaptcha($v, 'linkxcaptcha_rate');
}

if( $C['user_for_rate'] )
{
    $account = ValidUserLogin();

    if( $account === FALSE || $account['status'] != 'active' )
    {
        if( $account === FALSE )
            $v->SetError($L['INVALID_LOGIN']);
        else if( $account['status'] == 'suspended' )
            $v->SetError($L['SUSPENDED_ACCOUNT']);
        else
            $v->SetError($L['PENDING_ACCOUNT']);
    }
}


// Check dsbl.org for spam submissions
if( $C['dsbl_rate'] && CheckDsbl($_SERVER['REMOTE_ADDR']) )
{
    $v->SetError($L['DSBL_MATCHED']);
}


if( !$v->Validate() )
{
    $errors = join('<br />', $v->GetErrors());
    $t->assign('error', $errors);
    $t->display('error-nice.tpl');
    exit;
}


// See if this person has rated this link already
$has_rated = FALSE;
if( $account )
{
    $has_rated = $DB->Count('SELECT COUNT(*) FROM lx_link_ratings WHERE link_id=? AND (username=? OR submit_ip=?)',
                            array($_REQUEST['link_id'],
                                  $account['username'],
                                  $_SERVER['REMOTE_ADDR']));
}
else
{
    $has_rated = $DB->Count('SELECT COUNT(*) FROM lx_link_ratings WHERE link_id=? AND submit_ip=?',
                            array($_REQUEST['link_id'],
                                  $_SERVER['REMOTE_ADDR']));
}


// Get link data
$link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($_REQUEST['link_id']));

if( !$has_rated && $link )
{
    $link['ratings']++;
    $link['rating_total'] += $_REQUEST['rating'];

    $DB->Update('UPDATE lx_links SET ratings=?,rating_total=?,rating_avg=? WHERE link_id=?',
                array($link['ratings'],
                      $link['rating_total'],
                      ($link['rating_total']/$link['ratings']),
                      $link['link_id']));


    $DB->Update('INSERT INTO lx_link_ratings VALUES (?,?,?,?)',
                array($link['link_id'],
                      $account ? $account['username'] : null,
                      $_SERVER['REMOTE_ADDR'],
                      time()));

    $t->cache_dir = 'templates/cache_details';
    $t->clear_cache('directory-link-details.tpl', md5($link['link_id']));
}

$DB->Disconnect();

if( $C['mod_rewrite'] )
{
    $link['title'] = trewrite($link['title']);
    header("Location: {$C['base_url']}/" . sprintf("{$link['title']}-{$C['page_details']}", $_REQUEST['link_id']) . "?m=rated");
}
else
{
    header("Location: {$C['base_url']}/details.php?id={$_REQUEST['link_id']}&m=rated");
}

?>
