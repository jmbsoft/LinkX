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

$functions = array('register' => 'lxShRegister',
                   'create' => 'lxCreateAccount',
                   'login' => 'lxShLogin',
                   'logout' => 'lxLogout',
                   'dologin' => 'lxLogin',
                   'showedit' => 'lxShEdit',
                   'edit' => 'lxEditAccount',
                   'confirm' => 'lxShConfirm',
                   'forgot' => 'lxShForgot',
                   'resetconfirm' => 'lxResetConfirm',
                   'reset' => 'lxPasswordReset');

require_once('includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/validator.class.php");

SetupRequest();

// Indicate if logged in
$C['logged_in'] = isset($_COOKIE['linkxuser']);

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);

$t = new Template();
$t->assign_by_ref('config', $C);

$DB->Connect();

if( isset($functions[$_REQUEST['r']]) && function_exists($functions[$_REQUEST['r']]) )
{
    call_user_func($functions[$_REQUEST['r']]);
}
else
{
    $account = ValidUserLogin();

    if( $account !== FALSE && $account['status'] == 'active' )
    {
        lxLogin($account);
    }
    else
    {
        lxShRegister();
    }
}

$DB->Disconnect();

function lxShForgot($errors = null)
{
    global $DB, $C, $t;

    $t->assign_by_ref('account', $_REQUEST);
    $t->assign('errors', $errors);
    $t->display('account-forgot.tpl');
}

function lxResetConfirm()
{
    global $DB, $C, $t, $L;

    $account = $DB->Row('SELECT * FROM lx_users WHERE email=?', array($_REQUEST['email']));

    $v = new Validator();

    if( !$account )
    {
        $v->SetError($L['BAD_EMAIL']);
    }
    else
    {
        if( $account['status'] == 'pending' || $account['status'] == 'unconfirmed' )
        {
            $v->SetError($L['PENDING_ACCOUNT']);
        }
        else if( $account['status'] == 'suspended' )
        {
            $v->SetError($L['SUSPENDED_ACCOUNT']);
        }
    }

    if( !$v->Validate() )
    {
        $errors = join('<br />', $v->GetErrors());
        lxShForgot($errors);
        return;
    }

    $confirm_id = sha1(uniqid(rand(), TRUE));

    $DB->Update('DELETE FROM lx_user_confirms WHERE username=?', array($account['username']));
    $DB->Update('INSERT INTO lx_user_confirms VALUES (?,?,?)',
                array($account['username'],
                      $confirm_id,
                      time()));

    $t->assign_by_ref('account', $account);
    $t->assign('confirm_id', $confirm_id);

    SendMail($account['email'], 'email-account-forgot.tpl', $t);

    $t->display('account-forgot-confirm.tpl');
}

function lxPasswordReset()
{
    global $DB, $C, $t, $L;

    $confirmation = $DB->Row('SELECT * FROM lx_user_confirms WHERE confirmation_id=?', array($_REQUEST['id']));

    if( $confirmation )
    {
        $DB->Update('DELETE FROM lx_user_confirms WHERE confirmation_id=?', array($_REQUEST['id']));
        $account = $DB->Row('SELECT * FROM lx_users WHERE username=?', array($confirmation['username']));

        if( !$account )
        {
            $t->assign('error', $L['INVALID_CONFIRMATION']);
        }
        else
        {
            $account['password'] = RandomPassword();

            $DB->Update('UPDATE lx_users SET password=?,session=?,session_start=? WHERE username=?',
                        array(sha1($account['password']),
                              null,
                              0,
                              $account['username']));

            $t->assign_by_ref('account', $account);

            SendMail($account['email'], 'email-account-password.tpl', $t);
        }
    }
    else
    {
        $t->assign('error', $L['INVALID_CONFIRMATION']);
    }

    $t->display('account-forgot-confirmed.tpl');
}

function lxShRegister($errors = null)
{
    global $DB, $C, $t;

    // Get user defined fields
    $fields =& GetUserAccountFields();

    $t->assign_by_ref('account', $_REQUEST);
    $t->assign_by_ref('user_fields', $fields);
    $t->assign('errors', $errors);
    $t->display('account-register.tpl');
}

function lxShLogin($errors = null)
{
    global $DB, $C, $t;

    $account = ValidUserLogin();

    if( $account != FALSE && $account['status'] == 'active' )
    {
        header("Location: {$C['base_url']}/account.php?r=dologin");
    }
    else
    {
        $t->assign_by_ref('request', $_REQUEST);
        $t->assign('errors', $errors);
        $t->display('account-login.tpl');
    }
}

function lxShEdit($errors = null)
{
    global $DB, $C, $t, $L;

    $account = ValidUserLogin();

    if( $account === FALSE )
    {
        lxShLogin($L['INVALID_LOGIN']);
        return;
    }
    else if( $account['status'] != 'active' )
    {
        lxShLogin($account['status'] == 'suspended' ? $L['SUSPENDED_ACCOUNT'] : $L['PENDING_ACCOUNT']);
        return;
    }

    unset($account['password']);

    $fields =& GetUserAccountFields($account);

    $account = array_merge($account, $_REQUEST);

    $t->assign_by_ref('user_fields', $fields);
    $t->assign_by_ref('account', $account);
    $t->assign('errors', $errors);
    $t->display('account-edit.tpl');
}

function lxShConfirm()
{
    global $DB, $C, $L, $t;

    if( isset($_REQUEST['id']) )
    {
        $confirmation = $DB->Row('SELECT * FROM lx_user_confirms WHERE confirmation_id=?', array($_REQUEST['id']));

        // Valid code, confirm account
        if( $confirmation )
        {
            $status = 'active';

            if( $C['approve_accounts'] )
            {
                $status = 'pending';
            }

            $DB->Update('UPDATE lx_users SET status=? WHERE username=?', array($status, $confirmation['username']));
            $DB->Update('DELETE FROM lx_user_confirms WHERE username=?', array($confirmation['username']));

            $account = $DB->Row('SELECT * FROM lx_users JOIN lx_user_fields USING (username) WHERE lx_users.username=?', array($confirmation['username']));

            unset($account['password']);

            // Get user defined fields
            $fields =& GetUserAccountFields($account);

            // Show confirmation page
            $t->assign_by_ref('user_fields', $fields);
            $t->assign_by_ref('account', $account);
            $t->assign('status', $status);
            $t->display('account-created.tpl');
            return;
        }
    }

    $t->assign('error', $L['INVALID_CONFIRMATION']);
    $t->display('error-nice.tpl');
}

function lxLogin($account = null, $message = null)
{
    global $DB, $C, $t, $L;

    if( $account === null )
    {
        $account = ValidUserLogin();
    }

    if( $account === FALSE )
    {
        lxShLogin($L['INVALID_LOGIN']);
        return;
    }
    else if( $account['status'] != 'active' )
    {
        lxShLogin($account['status'] == 'suspended' ? $L['SUSPENDED_ACCOUNT'] : $L['PENDING_ACCOUNT']);
        return;
    }
    else
    {
        $C['logged_in'] = TRUE;

        // Redirect back to the user's starting URL
        if( !empty($_REQUEST['u']) )
        {
            header("Location: {$_REQUEST['u']}");
        }

        // Show account overview
        else
        {
            $t->assign('message', $message);
            lxShAccountOverview($account);
        }
    }
}

function lxLogout()
{
    global $C, $t;

    setcookie('linkxuser', '', time() - 3600, '/', $C['cookie_domain']);

    $C['logged_in'] = FALSE;

    $t->assign('http_referrer', $_SERVER['HTTP_REFERER']);
    $t->display('account-logout.tpl');
}

function lxShAccountOverview(&$account)
{
    global $DB, $t, $C, $L;

    $links = $DB->FetchAll('SELECT * FROM lx_links WHERE username=?', array($account['username']));

    $t->assign_by_ref('links', $links);
    $t->assign_by_ref('account', $account);
    $t->display('account-overview.tpl');
}

function lxCreateAccount()
{
    global $DB, $C, $t, $L;

    $v = new Validator();

    $v->Register($_REQUEST['email'], V_EMAIL, $L['INVALID_EMAIL']);
    $v->Register($_REQUEST['username'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['USERNAME']}");
    $v->Register($_REQUEST['password'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['PASSWORD']}");
    $v->Register($_REQUEST['username'], V_ALPHANUM, $L['INVALID_USERNAME']);
    $v->Register($_REQUEST['username'], V_LENGTH, $L['USERNAME_LENGTH'], '3,32');
    $v->Register($_REQUEST['password'], V_EQUALS, $L['NO_PASSWORD_MATCH'], $_REQUEST['confirm_password']);
    $v->Register($_REQUEST['password'], V_LENGTH, $L['PASSWORD_LENGTH'], '4,9999');
    $v->Register($_REQUEST['name'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['NAME']}");

    // Validation of user defined fields
    $fields =& GetUserAccountFields();
    foreach($fields as $field)
    {
        if( $field['on_create'] )
        {
            if( $field['required'] )
            {
                $v->Register($_REQUEST[$field['name']], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$field['label']}");
            }

            if( $field['validation'] )
            {
                $v->Register($_REQUEST[$field['name']], $field['validation'], $field['validation_message'], $field['validation_extras']);
            }
        }
    }

    // Username exists?
    if( $DB->Count('SELECT COUNT(*) FROM lx_users WHERE username=?', array($_REQUEST['username'])) )
    {
        $v->SetError($L['DUPLICATE_USER']);
    }

    // E-mail exists?
    if( $DB->Count('SELECT COUNT(*) FROM lx_users WHERE email=?', array($_REQUEST['email'])) )
    {
        $v->SetError($L['DUPLICATE_EMAIL']);
    }

    // Verify captcha code
    if( $C['account_captcha'] )
    {
        VerifyCaptcha($v);
    }

    // Check dsbl.org for spam submissions
    if( $C['dsbl_account'] && CheckDsbl($_SERVER['REMOTE_ADDR']) )
    {
        $v->SetError($L['DSBL_MATCHED']);
    }

    // Check blacklist
    $blacklisted = CheckBlacklistAccount($_REQUEST);
    if( $blacklisted !== FALSE )
    {
        $v->SetError(sprintf($L['BLACKLIST_MATCHED'], $blacklisted[0]['match'], $blacklisted[0]['reason']));
    }

    if( !$v->Validate() )
    {
        $errors = join('<br />', $v->GetErrors());
        lxShRegister($errors);
        return;
    }

    $status = 'active';
    $confirm_id = '';

    // Confirm accounts by e-mail
    if( $C['confirm_accounts'] )
    {
        $status = 'unconfirmed';
    }
    // Require account approval
    else if( $C['approve_accounts'] )
    {
        $status = 'pending';
    }

    // Add pre-defined data
    $DB->Update('INSERT INTO lx_users VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array($_REQUEST['username'],
                      sha1($_REQUEST['password']),
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      MYSQL_NOW,
                      null,
                      $status,
                      '',
                      0,
                      0,
                      $C['recip_required'],
                      $C['allow_redirect'],
                      $C['link_weight']));

    // Add user defined fields
    $query_data = CreateUserInsert('lx_user_fields', $_REQUEST);
    $DB->Update('INSERT INTO lx_user_fields VALUES ('.$query_data['bind_list'].')', $query_data['binds']);

    // Setup template values
    $t->assign_by_ref('user_fields', $fields);
    $t->assign_by_ref('account', $_REQUEST);
    $t->assign('status', $status);

    // Send e-mail message
    if( $status == 'unconfirmed' )
    {
        $confirm_id = sha1(uniqid(rand(), TRUE));

        $DB->Update('INSERT INTO lx_user_confirms VALUES (?,?,?)',
                    array($_REQUEST['username'],
                          $confirm_id,
                          time()));

        $t->assign('confirm_id', $confirm_id);

        SendMail($_REQUEST['email'], 'email-account-confirm.tpl', $t);
    }
    else if( $C['email_accounts'] )
    {
        SendMail($_REQUEST['email'], 'email-account-added.tpl', $t);
    }

    // Display confirmation page
    $t->display('account-created.tpl');
}

function lxEditAccount()
{
    global $DB, $C, $t, $L;

    $account = ValidUserLogin();

    if( $account === FALSE )
    {
        lxShLogin($L['INVALID_LOGIN']);
        return;
    }
    else if( $account['status'] != 'active' )
    {
        lxShLogin($account['status'] == 'suspended' ? $L['SUSPENDED_ACCOUNT'] : $L['PENDING_ACCOUNT']);
        return;
    }
    else
    {
        $password = $account['password'];

        $v = new Validator();

        $v->Register($_REQUEST['email'], V_EMAIL, $L['INVALID_EMAIL']);
        $v->Register($_REQUEST['name'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['NAME']}");

        if( !empty($_REQUEST['password']) )
        {
            $v->Register($_REQUEST['password'], V_EQUALS, $L['NO_PASSWORD_MATCH'], $_REQUEST['confirm_password']);
            $v->Register($_REQUEST['password'], V_LENGTH, $L['PASSWORD_LENGTH'], '4,9999');
            $password = sha1($_REQUEST['password']);
        }

        // Validation of user defined fields
        $fields =& GetUserAccountFields();
        foreach($fields as $field)
        {
            if( $field['on_edit'] )
            {
                if( $field['required'] )
                {
                    $v->Register($_REQUEST[$field['name']], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$field['label']}");
                }

                if( $field['validation'] )
                {
                    $v->Register($_REQUEST[$field['name']], $field['validation'], $field['validation_message'], $field['validation_extras']);
                }
            }
        }

        // E-mail exists?
        if( $DB->Count('SELECT COUNT(*) FROM lx_users WHERE username!=? AND email=?', array($account['username'], $_REQUEST['email'])) )
        {
            $v->SetError($L['DUPLICATE_EMAIL']);
        }

        // Check blacklist
        $blacklisted = CheckBlacklistAccount($_REQUEST);
        if( $blacklisted !== FALSE )
        {
            $v->SetError(sprintf($L['BLACKLIST_MATCHED'], $blacklisted[0]['match'], $blacklisted[0]['reason']));
        }

        if( !$v->Validate() )
        {
            $errors = join('<br />', $v->GetErrors());
            lxShEdit($errors);
            return;
        }

        // Update pre-defined data
        $DB->Update('UPDATE lx_users SET ' .
                    'password=?, ' .
                    'name=?, ' .
                    'email=? ' .
                    'WHERE username=?',
                    array($password,
                          $_REQUEST['name'],
                          $_REQUEST['email'],
                          $account['username']));

        // Update user defined fields
        UserDefinedUpdate('lx_user_fields', 'lx_user_field_defs', 'username', $account['username'], $_REQUEST, FALSE);

        // Back to the account overview
        lxLogin(null, 'accountupdate');
    }
}
?>
