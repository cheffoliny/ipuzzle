<?php

if (!defined('ADODB_FETCH_ASSOC')) {
    define('ADODB_FETCH_ASSOC', 2);
}
if (!defined('MYSQLI_CLIENT_COMPRESS')) {
    define('MYSQLI_CLIENT_COMPRESS', 32);
}
if (!defined('CLIENT_MULTI_STATEMENTS')) {
    define('CLIENT_MULTI_STATEMENTS', 65536);
}

final class ReferenceAwareConnectionStub
{
    public int $clientFlags = 0;

    public function SetFetchMode($mode): void {}
    public function NConnect($host, $user, $pass, $name): bool { return true; }
    public function Execute($query): bool { return true; }

    public function GetInsertSQL(&$recordset, $fields): string
    {
        return 'INSERT:' . $recordset . ':' . $fields['name'];
    }

    public function GetUpdateSQL(&$recordset, $fields): string
    {
        return 'UPDATE:' . $recordset . ':' . $fields['name'];
    }
}

$connectionStub = new ReferenceAwareConnectionStub();

function ADONewConnection($driver)
{
    global $connectionStub;
    return $connectionStub;
}

require_once dirname(__DIR__) . '/db_api/include/DBSmartConnection.class.php';

$connection = new DBSmartConnection('test', 'localhost', 'user', 'pass', 'database');
$recordset = 'recordset';
$fields = ['name' => 'value'];

$insert = $connection->GetInsertSQL($recordset, $fields);
$update = $connection->GetUpdateSQL($recordset, $fields);

if ($insert !== 'INSERT:recordset:value' || $update !== 'UPDATE:recordset:value') {
    fwrite(STDERR, "DBSmartConnection reference forwarding failed.\n");
    exit(1);
}

echo "DBSMARTCONNECTION_REFERENCE_PHP85=PASS\n";
