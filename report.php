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

$functions = array('display' => 'lxShReport',
                   'report' => 'lxReport');

require_once('includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/template.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/includes/validator.class.php");

SetupRequest();

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
    lxShReport();
}

$DB->Disconnect();

function lxShReport($errors = null)
{
    global $DB, $C, $t, $L;

    $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($_REQUEST['id']));

    if( !$link )
    {
        $t->assign('error', $L['INVALID_LINK_ID']);
        $t->display('error-nice.tpl');
        return;
    }

    $t->assign_by_ref('link', $link);
    $t->assign('errors', $errors);
    $t->assign('report', $_REQUEST['message']);
    $t->display('report-submit.tpl');
}

function lxReport()
{
    global $DB, $C, $L, $t;

    $v = new Validator();

    $v->Register($_REQUEST['message'], V_EMPTY, "{$L['REQUIRED_FIELD']}: {$L['REPORT']}");

    // Verify captcha code
    if( $C['report_captcha'] )
    {
        VerifyCaptcha($v);
    }

    // Check dsbl.org for spam submissions
    if( $C['dsbl_report'] && CheckDsbl($_SERVER['REMOTE_ADDR']) )
    {
        $v->SetError($L['DSBL_MATCHED']);
    }

    if( !$v->Validate() )
    {
        $errors = join('<br />', $v->GetErrors());
        lxShReport($errors);
        return;
    }

    $link = $DB->Row('SELECT * FROM lx_links JOIN lx_link_fields USING (link_id) WHERE lx_links.link_id=?', array($_REQUEST['id']));

    if( $link )
    {
        $DB->Update('INSERT INTO lx_reports VALUES (?,?,?,?,?)',
                    array(null,
                          $_REQUEST['id'],
                          $_REQUEST['message'],
                          MYSQL_NOW,
                          $_SERVER['REMOTE_ADDR']));

        $t->assign_by_ref('link', $link);
    }

    $t->display('report-submitted.tpl');
}

?>
