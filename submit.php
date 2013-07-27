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

define('MANY_CATEGORIES', 200);

$functions = array('submit' => 'lxShSubmit',
                   'addlink' => 'lxAddLink',
                   'showedit' => 'lxShEdit',
                   'edit' => 'lxEditLink',
                   'confirm' => 'lxShConfirm',
                   'editlogin' => 'lxShEditLogin');

require_once('includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/validator.class.php");

SetupRequest();

// Indicate if logged in
$C['logged_in'] = isset($_COOKIE['linkxuser']);

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

$t = new Template();
$t->assign_by_ref('config', $C);
$t->assign('ref_url', "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");


if( isset($functions[$_REQUEST['r']]) && function_exists($functions[$_REQUEST['r']]) )
{
    call_user_func($functions[$_REQUEST['r']]);
}
else
{
    lxShSubmit();
}

$DB->Disconnect();

function lxShSubmit($errors = null)
{
    global $DB, $C, $L, $t;

    $account = ValidUserLogin();
    $num_categories = $DB->Count('SELECT COUNT(*) FROM lx_categories');

    // Do not display a drop down list of categories
    if( $num_categories == 0 )
    {
        $t->assign('error', $L['NO_CATEGORIES']);
        $t->display('error-nice.tpl');
        return;
    }
    else if( $num_categories > MANY_CATEGORIES )
    {
        // Category must be selected by browsing the directory
        if( !isset($_REQUEST['c']) )
        {
            $t->display('submit-info.tpl');
            return;
        }

        $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_REQUEST['c']));

        if( !$category || $category['hidden'] || $category['crosslink_id'] )
        {
            $t->assign('error', $L['INVALID_CATEGORY']);
            $t->display('error-nice.tpl');
            return;
        }
        else if( $category['status'] == 'locked' )
        {
            $t->assign('error', $L['CATEGORY_LOCKED']);
            $t->display('error-nice.tpl');
            return;
        }

        $category['path_parts'] = unserialize($category['path_parts']);
        $t->assign('category', $category);
    }
    else
    {
        $result = $DB->Query('SELECT * FROM lx_categories ORDER BY path');
        $categories = array();
        while( $category = $DB->NextRow($result) )
        {
            $category['path_parts'] = unserialize($category['path_parts']);
            $categories[] = $category;
        }
        $DB->Free($result);

        $t->assign_by_ref('categories', $categories);
    }

    $fields =& GetUserLinkFields();

    $t->assign_by_ref('account', $account);
    $t->assign_by_ref('link', $_REQUEST);
    $t->assign_by_ref('user_fields', $fields);
    $t->assign('errors', $errors);
    $t->display('submit-add.tpl');
}

function lxAddLink()
{
    global $DB, $C, $L, $t;

    $account = ValidUserLogin();

    // Requiring user account to submit links
    if( $C['user_for_links'] && !$account )
    {
        $t->display('submit-info.tpl');
        return;
    }

    if( $account )
    {
        $_REQUEST['email'] = $account['email'];
        $_REQUEST['name'] = $account['name'];
    }

    $_REQUEST['c'] = $_REQUEST['category_id'];

    $v = new Validator();

    $v->Register($_REQUEST['email'], V_EMAIL, $L['INVALID_EMAIL']);
    $v->Register($_REQUEST['site_url'], V_URL, "{$L['INVALID_URL']}: {$L['SITE_URL']}");
    $v->Register($_REQUEST['title'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['TITLE']}");
    $v->Register($_REQUEST['description'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['DESCRIPTION']}");
    $v->Register($_REQUEST['keywords'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['KEYWORDS']}");
    $v->Register($_REQUEST['name'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['NAME']}");
    $v->Register($_REQUEST['description'], V_LENGTH, sprintf($L['DESCRIPTION_LENGTH'], $C['min_desc_length'], $C['max_desc_length']), "{$C['min_desc_length']},{$C['max_desc_length']}");
    $v->Register($_REQUEST['title'], V_LENGTH, sprintf($L['TITLE_LENGTH'], $C['min_title_length'], $C['max_title_length']), "{$C['min_title_length']},{$C['max_title_length']}");

    // Format keywords and check number
    $_REQUEST['keywords'] = FormatKeywords($_REQUEST['keywords']);
    $keywords = explode(' ', $_REQUEST['keywords']);
    $v->Register(count($keywords), V_LESS_EQ, sprintf($L['MAXIMUM_KEYWORDS'], $C['max_keywords']), $C['max_keywords']);

    if( !empty($_REQUEST['password']) )
    {
        $v->Register($_REQUEST['password'], V_EQUALS, $L['NO_PASSWORD_MATCH'], $_REQUEST['confirm_password']);
    }

    // See if URL already exists
    if( $DB->Count('SELECT COUNT(*) FROM lx_links WHERE site_url=?', array($_REQUEST['site_url'])) )
    {
        $v->SetError($L['DUPLICATE_URL']);
    }

    // Validation of user defined fields
    $fields =& GetUserLinkFields();
    foreach($fields as $field)
    {
        if( $field['on_submit'] )
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

    // Verify captcha code
    if( $C['link_captcha'] )
    {
        VerifyCaptcha($v);
    }

    $_REQUEST['allow_redirect'] = $account ? $account['allow_redirect'] : $C['allow_redirect'];
    $_REQUEST['recip_required'] = $account ? $account['recip_required'] : $C['recip_required'];

    // Scan link
    $scan_result =& ScanLink($_REQUEST);

    // Make sure site URL is working
    if( !$scan_result['site_url']['working'] )
    {
        $v->SetError(sprintf($L['BROKEN_URL'], $L['SITE_URL'], $scan_result['site_url']['error']));
    }

    // Setup HTML code for blacklist check
    $_REQUEST['html'] = $scan_result['site_url']['html'];
    if( !empty($_REQUEST['recip_url']) )
    {
        $_REQUEST['html'] .= ' ' . $scan_result['recip_url']['html'];

        // Make sure recip URL is working
        if( !$scan_result['recip_url']['working'] )
        {
            $v->SetError(sprintf($L['BROKEN_URL'], $L['RECIP_URL'], $scan_result['recip_url']['error']));
        }
    }

    // Verify recip link was found
    if( $_REQUEST['recip_required'] && !$scan_result['has_recip'] )
    {
        $v->SetError($L['NO_RECIP_FOUND']);
    }

    // Check blacklist
    $blacklisted = CheckBlacklistLink($_REQUEST);
    if( $blacklisted !== FALSE )
    {
        $v->SetError(sprintf($L['BLACKLIST_MATCHED'], $blacklisted[0]['match'], $blacklisted[0]['reason']));
    }

    // Check dsbl.org for spam submissions
    if( $C['dsbl_link'] && CheckDsbl($_SERVER['REMOTE_ADDR']) )
    {
        $v->SetError($L['DSBL_MATCHED']);
    }

    // Get category information
    $category = $DB->Row('SELECT * FROM lx_categories WHERE category_id=?', array($_REQUEST['category_id']));

    if( !$category || $category['hidden'] )
    {
        $v->SetError($L['INVALID_CATEGORY']);
    }
    else if( $category['status'] == 'locked' )
    {
        $v->SetError($L['CATEGORY_LOCKED']);
    }

    $category['path_parts'] = unserialize($category['path_parts']);

    if( !$v->Validate() )
    {
        $errors = join('<br />', $v->GetErrors());
        lxShSubmit($errors);
        return;
    }

    // Setup link status
    $status = 'active';
    if( $C['confirm_links'] && !$account )
    {
        $status = 'unconfirmed';
    }
    else if( $category['status'] == 'approval' )
    {
        $status = 'pending';
    }

    // Setup username and password values
    $username = '';
    $password = '';
    if( $account )
    {
        $username = $account['username'];
    }
    else if( $_REQUEST['password'] )
    {
        $password = sha1($_REQUEST['password']);
    }

    $weight = $account ? $account['weight'] : $C['link_weight'];

    // Insert link data
    $DB->Update('INSERT INTO lx_links VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                array(null,
                      $_REQUEST['site_url'],
                      $_REQUEST['recip_url'],
                      $_REQUEST['title'],
                      $_REQUEST['description'],
                      $status,
                      'regular',
                      DEF_EXPIRES,
                      $_REQUEST['name'],
                      $_REQUEST['email'],
                      $_SERVER['REMOTE_ADDR'],
                      $_REQUEST['keywords'],
                      0,
                      0,
                      null,
                      0,
                      0,
                      0,
                      $weight,
                      MYSQL_NOW,
                      0,
                      MYSQL_NOW,
                      $_REQUEST['recip_required'],
                      $_REQUEST['allow_redirect'],
                      '',
                      '',
                      $username,
                      $password,
                      $scan_result['has_recip'],
                      0,
                      ''));

    $link_id = $DB->InsertID();
    $sorter = $DB->Count('SELECT MAX(sorter) FROM lx_link_cats WHERE category_id=?', array($_REQUEST['category_id']));
    $_REQUEST['link_id'] = $link_id;
    $_REQUEST['status'] = $status;

    // Insert category data
    $DB->Update('INSERT INTO lx_link_cats VALUES (?,?,?)', array($link_id, $_REQUEST['category_id'], $sorter));

    // Insert user defined fields
    $query_data = CreateUserInsert('lx_link_fields', $_REQUEST);
    $DB->Update('INSERT INTO lx_link_fields VALUES ('.$query_data['bind_list'].')', $query_data['binds']);

    // Update category link count
    if( $status == 'active' )
    {
        $DB->Update('UPDATE lx_categories SET links=links+1 WHERE category_id=?', array($_REQUEST['category_id']));
    }

    // Update account link count
    if( $account )
    {
        $DB->Update('UPDATE lx_users SET num_links=num_links+1 WHERE username=?', array($account['username']));
    }

    // Show confirmation page
    $t->assign_by_ref('category', $category);
    $t->assign_by_ref('user_fields', $fields);
    $t->assign_by_ref('link', $_REQUEST);
    $t->assign('status', $status);


    // Send e-mail message
    if( $status == 'unconfirmed' )
    {
        $confirm_id = sha1(uniqid(rand(), TRUE));

        $DB->Update('INSERT INTO lx_link_confirms VALUES (?,?,?)',
                    array($link_id,
                          $confirm_id,
                          time()));

        $t->assign('confirm_id', $confirm_id);

        SendMail($_REQUEST['email'], 'email-link-confirm.tpl', $t);
    }
    else if( $C['email_links'] )
    {
        SendMail($_REQUEST['email'], 'email-link-added.tpl', $t);
    }

    $t->display('submit-added.tpl');

    flush();

    // Send e-mail to appropriate administrators
    if( $status != 'unconfirmed' )
    {
        $result = $DB->Query('SELECT * FROM lx_administrators');
        while( $admin = $DB->NextRow($result) )
        {
            if( $admin['notifications'] & E_LINK_ADD )
            {
                SendMail($admin['email'], 'email-admin-link-add.tpl', $t);
            }
        }
        $DB->Free($result);
    }
}

function lxShEditLogin($errors = null)
{
    global $DB, $C, $L, $t;

    $t->assign_by_ref('request', $_REQUEST);
    $t->assign('errors', $errors);
    $t->display('submit-edit-login.tpl');
}

function lxShEdit($errors = null)
{
    global $DB, $C, $L, $t;

    // Using e-mail address and password to login
    if( $_REQUEST['noaccount'] )
    {
        $v = new Validator();

        $v->Register($_REQUEST['login_email'], V_EMAIL, $L['INVALID_EMAIL']);
        $v->Register($_REQUEST['login_site_url'], V_URL, $L['INVALID_URL']);
        $v->Register($_REQUEST['login_password'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['PASSWORD']}");

        // See if URL exists
        $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE site_url=?', array($_REQUEST['login_site_url']));
        if( !$link )
        {
            $v->SetError($L['NO_MATCHING_URL']);
        }
        else if( $link['email'] != $_REQUEST['login_email'] || $link['password'] != sha1($_REQUEST['login_password']) )
        {
            $v->SetError($L['BAD_EMAIL_OR_PASSWORD']);
        }

        if( !$v->Validate() )
        {
            $errors = join('<br />', $v->GetErrors());
            lxShEditLogin($errors);
            return;
        }

        $t->assign('noaccount', 1);
        $t->assign('login_site_url', $_REQUEST['login_site_url']);
        $t->assign('login_email', $_REQUEST['login_email']);
        $t->assign('login_password', $_REQUEST['login_password']);
    }

    // Regular account login
    else
    {
        $account = ValidUserLogin();

        if( !$account )
        {
            header("Location: {$C['base_url']}/account.php?r=login&u=" . urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"));
            return;
        }

        $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($_REQUEST['link_id']));
        if( !$link || $link['username'] != $account['username'] )
        {
            $t->assign('error', $L['LINK_EDIT_REFUSED']);
            $t->display('error-nice.tpl');
            return;
        }

        $t->assign_by_ref('account', $account);
    }

    // Get categories this link is in
    $categories = array();
    $result = $DB->Query('SELECT * FROM lx_categories JOIN lx_link_cats USING (category_id) WHERE link_id=?', array($link['link_id']));
    while( $category = $DB->NextRow($result) )
    {
        $category['path_parts'] = unserialize($category['path_parts']);
        $categories[] = $category;
    }
    $DB->Free($result);



    if( $_REQUEST['editing'] )
    {
        $t->assign_by_ref('link', $_REQUEST);
        $fields =& GetUserLinkFields();
    }
    else
    {
        unset($link['password']);
        $t->assign_by_ref('link', $link);
        $fields =& GetUserLinkFields($link);
    }

    $t->assign_by_ref('categories', $categories);
    $t->assign_by_ref('user_fields', $fields);
    $t->assign('errors', $errors);
    $t->display('submit-edit.tpl');
}

function lxEditLink()
{
    global $DB, $C, $L, $t;

    $v = new Validator();

    // Make sure user is allowed to edit this link
    $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($_REQUEST['link_id']));
    if( $_REQUEST['noaccount'] )
    {
        if( !empty($link['username']) ||
            $link['site_url'] != $_REQUEST['login_site_url'] ||
            $link['password'] != sha1($_REQUEST['login_password']) ||
            $link['email'] != $_REQUEST['login_email'] )
        {
            $t->assign('error', $L['LINK_EDIT_REFUSED']);
            $t->display('error-nice.tpl');
            return;
        }
    }
    else
    {
        $account = ValidUserLogin();

        if( !$account || $account['username'] != $link['username'] )
        {
            $t->assign('error', $L['LINK_EDIT_REFUSED']);
            $t->display('error-nice.tpl');
            return;
        }
    }

    $v->Register($_REQUEST['email'], V_EMAIL, $L['INVALID_EMAIL']);
    $v->Register($_REQUEST['site_url'], V_URL, "{$L['INVALID_URL']}: {$L['SITE_URL']}");
    $v->Register($_REQUEST['title'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['TITLE']}");
    $v->Register($_REQUEST['description'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['DESCRIPTION']}");
    $v->Register($_REQUEST['keywords'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['KEYWORDS']}");
    $v->Register($_REQUEST['name'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['NAME']}");
    $v->Register($_REQUEST['description'], V_LENGTH, sprintf($L['DESCRIPTION_LENGTH'], $C['min_desc_length'], $C['max_desc_length']), "{$C['min_desc_length']},{$C['max_desc_length']}");
    $v->Register($_REQUEST['title'], V_LENGTH, sprintf($L['TITLE_LENGTH'], $C['min_title_length'], $C['max_title_length']), "{$C['min_title_length']},{$C['max_title_length']}");

    // Format keywords and check number
    $_REQUEST['keywords'] = FormatKeywords($_REQUEST['keywords']);
    $keywords = explode(' ', $_REQUEST['keywords']);
    $v->Register(count($keywords), V_LESS, sprintf($L['MAXIMUM_KEYWORDS'], $C['max_keywords']), $C['max_keywords']);

    if( !empty($_REQUEST['password']) )
    {
        $v->Register($_REQUEST['password'], V_EQUALS, $L['NO_PASSWORD_MATCH'], $_REQUEST['confirm_password']);
    }

    // See if URL already exists
    if( $DB->Count('SELECT COUNT(*) FROM lx_links WHERE site_url=? AND link_id!=?', array($_REQUEST['site_url'], $link['link_id'])) )
    {
        $v->SetError($L['DUPLICATE_URL']);
    }

    // Validation of user defined fields
    $fields =& GetUserLinkFields();
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

    $_REQUEST['allow_redirect'] = $link['allow_redirect'];
    $_REQUEST['recip_required'] = $link['recip_required'];

    // Scan link
    $scan_result =& ScanLink($_REQUEST);

    // Make sure site URL is working
    if( !$scan_result['site_url']['working'] )
    {
        $v->SetError(sprintf($L['BROKEN_URL'], $L['SITE_URL'], $scan_result['site_url']['error']));
    }

    // Setup HTML code for blacklist check
    $_REQUEST['html'] = $scan_result['site_url']['html'];
    if( !empty($_REQUEST['recip_url']) )
    {
        $_REQUEST['html'] .= ' ' . $scan_result['recip_url']['html'];

        // Make sure recip URL is working
        if( !$scan_result['recip_url']['working'] )
        {
            $v->SetError(sprintf($L['BROKEN_URL'], $L['RECIP_URL'], $scan_result['recip_url']['error']));
        }
    }

    // Verify recip link was found
    if( $_REQUEST['recip_required'] && !$scan_result['has_recip'] )
    {
        $v->SetError($L['NO_RECIP_FOUND']);
    }

    // Check blacklist
    $blacklisted = CheckBlacklistLink($_REQUEST);
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

    if( $C['approve_link_edits'] )
    {
        $_REQUEST['submit_ip'] = $_SERVER['REMOTE_ADDR'];
        $DB->Update('UPDATE lx_links SET is_edited=1,edit_data=? WHERE link_id=?', array(base64_encode(serialize($_REQUEST)), $link['link_id']));
    }
    else
    {
        // Update password, if necessary
        $password = $link['password'];
        if( $_REQUEST['noaccount'] && !empty($_REQUEST['password']) )
        {
            $password = sha1($_REQUEST['password']);
        }

        // Update link data
        $DB->Update('UPDATE lx_links SET ' .
                    'site_url=?, ' .
                    'recip_url=?, ' .
                    'title=?, ' .
                    'description=?, ' .
                    'name=?, ' .
                    'email=?, ' .
                    'submit_ip=?, ' .
                    'keywords=?, ' .
                    'date_modified=?, ' .
                    'password=?, ' .
                    'has_recip=? ' .
                    'WHERE link_id=?',
                    array($_REQUEST['site_url'],
                          $_REQUEST['recip_url'],
                          $_REQUEST['title'],
                          $_REQUEST['description'],
                          $_REQUEST['name'],
                          $_REQUEST['email'],
                          $_SERVER['REMOTE_ADDR'],
                          $_REQUEST['keywords'],
                          MYSQL_NOW,
                          $password,
                          $scan_result['has_recip'],
                          $link['link_id']));

        // Update user defined fields
        UserDefinedUpdate('lx_link_fields', 'lx_link_field_defs', 'link_id', $_REQUEST['link_id'], $_REQUEST, FALSE);
    }

    // Get category information
    $categories = array();
    $result = $DB->Query('SELECT * FROM lx_categories JOIN lx_link_cats USING (category_id) WHERE link_id=?', array($link['link_id']));
    while( $category = $DB->NextRow($result) )
    {
        $category['path_parts'] = unserialize($category['path_parts']);
        $categories[] = $category;
    }
    $DB->Free($result);

    // Show confirmation page
    $t->assign_by_ref('categories', $categories);
    $t->assign_by_ref('user_fields', $fields);
    $t->assign_by_ref('link', $_REQUEST);
    $t->display('submit-edited.tpl');

    flush();

    // Send e-mail to appropriate administrators
    $result = $DB->Query('SELECT * FROM lx_administrators');
    while( $admin = $DB->NextRow($result) )
    {
        if( $admin['notifications'] & E_LINK_EDIT )
        {
            SendMail($admin['email'], 'email-admin-link-edit.tpl', $t);
        }
    }
    $DB->Free($result);
}

function lxShConfirm()
{
    global $DB, $C, $L, $t;

    if( isset($_REQUEST['id']) )
    {
        $confirmation = $DB->Row('SELECT * FROM lx_link_confirms WHERE confirmation_id=?', array($_REQUEST['id']));

        // Valid code, confirm link
        if( $confirmation )
        {
            // Get category
            $category = $DB->Row('SELECT * FROM lx_link_cats JOIN lx_categories USING (category_id) WHERE link_id=?', array($confirmation['link_id']));

            // Set status
            $status = 'active';
            if( $category['status'] == 'approval' )
            {
                $status = 'pending';
            }

            $DB->Update('UPDATE lx_links SET status=? WHERE link_id=?', array($status, $confirmation['link_id']));
            $DB->Update('DELETE FROM lx_link_confirms WHERE link_id=?', array($confirmation['link_id']));

            $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($confirmation['link_id']));

            // Update category link count
            if( $status == 'active' )
            {
                $DB->Update('UPDATE lx_categories SET links=links+1 WHERE category_id=?', array($category['category_id']));
            }

            unset($link['password']);

            // Get user defined fields
            $fields =& GetUserLinkFields($link);

            // Show confirmation page
            $t->assign_by_ref('user_fields', $fields);
            $t->assign_by_ref('link', $link);
            $t->assign('status', $status);
            $t->display('submit-added.tpl');

            flush();

            // Send e-mail to appropriate administrators
            $result = $DB->Query('SELECT * FROM lx_administrators');
            while( $admin = $DB->NextRow($result) )
            {
                if( $admin['notifications'] & E_LINK_ADD )
                {
                    SendMail($admin['email'], 'email-admin-link-add.tpl', $t);
                }
            }
            $DB->Free($result);

            return;
        }
    }

    $t->assign('error', $L['INVALID_CONFIRMATION']);
    $t->display('error-nice.tpl');
}
?>
