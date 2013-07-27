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

define('LINKX', TRUE);

$path = realpath(dirname(__FILE__));
chdir($path);

// Make sure CLI API is being used
if( php_sapi_name() != 'cli' )
{
    echo "Invalid access: This script requires the CLI version of PHP";
    exit;
}

require_once('../includes/common.php');
require_once("{$GLOBALS['BASE_DIR']}/includes/mysql.class.php");
require_once("{$GLOBALS['BASE_DIR']}/admin/includes/functions.php");

$DB = new DB($C['db_hostname'], $C['db_username'], $C['db_password'], $C['db_name']);
$DB->Connect();

// Run function based on command line argument
switch($GLOBALS['argv'][1])
{
case '--backup':
    CommandLineBackup($GLOBALS['argv'][2]);
    break;

case '--restore':
    CommandLineRestore($GLOBALS['argv'][2]);
    break;
}

$DB->Disconnect();

function CommandLineBackup($filename)
{
    global $C, $DB;

    $filename = "{$GLOBALS['BASE_DIR']}/data/" . basename($filename);

    $tables = array();
    IniParse("{$GLOBALS['BASE_DIR']}/includes/tables.php", TRUE, $tables);

    if( $C['mysqldump'] )
    {
        $command = "{$C['mysqldump']} " .
                   "-u" . escapeshellarg($C['db_username']) . " " .
                   "-p" . escapeshellarg($C['db_password']) . " " .
                   "-h" . escapeshellarg($C['db_hostname']) . " " .
                   "--opt " .
                   escapeshellarg($C['db_name']) . " " .
                   join(' ', array_keys($tables)) .
                   " >" . escapeshellarg($filename) . " 2>&1";

        exec($command);
    }
    else
    {
        DoBackup($filename, $tables);
    }

    StoreValue('last_backup', time());
}

function CommandLineRestore($filename)
{
    if( $C['mysql'] )
    {
        $command = "{$C['mysql']} " .
                   "-u" . escapeshellarg($C['db_username']) . " " .
                   "-p" . escapeshellarg($C['db_password']) . " " .
                   "-h" . escapeshellarg($C['db_hostname']) . " " .
                   "-f " .
                   escapeshellarg($C['db_name']) . " " .
                   " <$filename 2>&1";

        exec($command);
    }
    else
    {
        DoRestore($filename);
    }
}

?>
