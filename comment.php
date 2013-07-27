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

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

$t = new Template();
$t->assign_by_ref('config', $C);

$v = new Validator();
$v->Register($_REQUEST['email'], V_EMAIL, $L['INVALID_EMAIL']);
$v->Register($_REQUEST['name'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['NAME']}");
$v->Register($_REQUEST['comment'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['COMMENT']}");
$v->Register($_REQUEST['comment'], V_LENGTH, sprintf($L['COMMENT_LENGTH'], $C['min_comment_length'], $C['max_comment_length']), "{$C['min_comment_length']},{$C['max_comment_length']}");

// Verify captcha code
if( $C['comments_captcha'] )
{
    VerifyCaptcha($v, 'linkxcaptcha_comment');
}

$account = ValidUserLogin();

if( $C['user_for_comments'] )
{    
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

// Check blacklist
$blacklisted = CheckBlacklistComment($_REQUEST);
if( $blacklisted !== FALSE )
{
    $v->SetError(sprintf($L['BLACKLIST_MATCHED'], $blacklisted[0]['match'], $blacklisted[0]['reason']));
}

// See if this person has submitted a comment recently
$has_recent = FALSE;

if( $account !== FALSE )
{
    $has_recent = $DB->Count('SELECT COUNT(*) FROM lx_link_comments WHERE link_id=? AND (username=? OR email=? OR submit_ip=?) AND date_added >= DATE_ADD(?, INTERVAL ? SECOND)',
                             array($_REQUEST['link_id'], 
                                   $account['username'], 
                                   $_REQUEST['email'], 
                                   $_SERVER['REMOTE_ADDR'], 
                                   MYSQL_NOW,
                                   -$C['comment_delay']));
}
else
{
    $has_recent = $DB->Count('SELECT COUNT(*) FROM lx_link_comments WHERE link_id=? AND (email=? OR submit_ip=?) AND date_added >= DATE_ADD(?, INTERVAL ? SECOND)',
                             array($_REQUEST['link_id'],
                                   $_REQUEST['email'], 
                                   $_SERVER['REMOTE_ADDR'], 
                                   MYSQL_NOW,
                                   -$C['comment_delay']));
}

if( $has_recent )
{
    $v->SetError(sprintf($L['COMMENT_LIMIT'], $C['comment_delay']));
}

// Check dsbl.org for spam submissions
if( $C['dsbl_comment'] && CheckDsbl($_SERVER['REMOTE_ADDR']) )
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


$link = $DB->Row('SELECT * FROM lx_links WHERE link_id=?', array($_REQUEST['link_id']));

if( $link )
{
    $status = $C['approve_comments'] ? 'pending' : 'approved';
    $username = $account ? $account['username'] : '';
        
    $DB->Update('INSERT INTO lx_link_comments VALUES (?,?,?,?,?,?,?,?,?)',
                array(null,
                      $link['link_id'],
                      $username,
                      $_REQUEST['email'],
                      $_REQUEST['name'],
                      $_SERVER['REMOTE_ADDR'],
                      MYSQL_NOW,
                      $status,
                      $_REQUEST['comment']));
    
    if( $status == 'approved' )
    {
        $DB->Update('UPDATE lx_links SET comments=comments+1 WHERE link_id=?', array($link['link_id']));
    }
    
    $_REQUEST['comment_id'] = $DB->InsertID();
    
    $t->assign_by_ref('comment', $_REQUEST);
    
    // Send e-mail to appropriate administrators
    $result = $DB->Query('SELECT * FROM lx_administrators');
    while( $admin = $DB->NextRow($result) )
    {
        if( $admin['notifications'] & E_COMMENT )
        {
            SendMail($admin['email'], 'email-admin-comment.tpl', $t);
        }
    }
    $DB->Free($result);
    
    // Clear cache for the link details page
    if( $status == 'approved' )
    {
        $t->cache_dir = 'templates/cache_details';
        $t->clear_cache('directory-link-details.tpl', md5($link['link_id']));                   
    }
}

$DB->Disconnect();

if( $C['mod_rewrite'] )
{
    $link['title'] = trewrite($link['title']);
    header("Location: {$C['base_url']}/" . sprintf("{$link['title']}-{$C['page_details']}", $_REQUEST['link_id']) . "?m=commented");
}
else
{
    header("Location: {$C['base_url']}/details.php?id={$_REQUEST['link_id']}&m=commented");
}

?>
