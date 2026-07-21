<?php

chdir(dirname(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', '1');

$sOverrideDir = getenv('IPUZZLE_ADODB_DIR');
$sAdoDbDir = $sOverrideDir !== false && $sOverrideDir !== ''
    ? $sOverrideDir
    : getcwd() . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'adodb';
$sAdoDbDir = rtrim($sAdoDbDir, '/\\');

$sMainFile = $sAdoDbDir . DIRECTORY_SEPARATOR . 'adodb.inc.php';
$sExceptionsFile = $sAdoDbDir . DIRECTORY_SEPARATOR . 'adodb-exceptions.inc.php';
$sLiveServerVersion = NULL;

if (!is_file($sMainFile) || !is_file($sExceptionsFile)) {
    fwrite(STDERR, "ADOdb files are missing in {$sAdoDbDir}.\n");
    exit(1);
}

set_error_handler(
    function ($nSeverity, $sMessage, $sFile, $nLine) {
        if (!(error_reporting() & $nSeverity)) {
            return false;
        }

        throw new ErrorException($sMessage, 0, $nSeverity, $sFile, $nLine);
    }
);

try {
    require_once $sMainFile;
    require_once $sExceptionsFile;

    $oConnection = ADONewConnection('mysqli');
    $aRequiredMethods = array(
        'NConnect',
        'SetFetchMode',
        'Execute',
        'GetArray',
        'GetRow',
        'GetOne',
        'GetCol',
        'GetInsertSQL',
        'GetUpdateSQL',
        'Insert_ID',
        'Affected_Rows',
        'StartTrans',
        'CompleteTrans',
        'Quote'
    );

    if (!($oConnection instanceof ADOConnection)) {
        throw new RuntimeException('The mysqli driver did not create an ADOConnection.');
    }

    if (!property_exists($oConnection, 'clientFlags')) {
        throw new RuntimeException('The mysqli driver does not expose clientFlags.');
    }

    foreach ($aRequiredMethods as $sMethod) {
        if (!method_exists($oConnection, $sMethod)) {
            throw new RuntimeException("Required ADOdb method {$sMethod} is missing.");
        }
    }

    $oConnection->SetFetchMode(ADODB_FETCH_ASSOC);
    $oConnection->clientFlags |= MYSQLI_CLIENT_COMPRESS;

    if (!class_exists('ADODB_Exception')) {
        throw new RuntimeException('ADODB_Exception is unavailable.');
    }

    require_once 'config/function.autoload.php';
    require_once 'config/connect.inc.php';

    $aProjectConnections = array(
        'db_system',
        'db_auto',
        'db_finance',
        'db_personnel',
        'db_sod',
        'db_storage'
    );

    foreach ($aProjectConnections as $sConnectionName) {
        if (
            !isset($GLOBALS[$sConnectionName]) ||
            !($GLOBALS[$sConnectionName] instanceof DBSmartConnection)
        ) {
            throw new RuntimeException("Project connection {$sConnectionName} was not initialized.");
        }
    }

    if (in_array('--live', $argv, true)) {
        $sLiveServerVersion = $GLOBALS['db_sod']->GetOne('SELECT VERSION()');
        if (!is_string($sLiveServerVersion) || $sLiveServerVersion === '') {
            throw new RuntimeException('The live MySQL version query returned no value.');
        }
    }
} catch (Throwable $oError) {
    restore_error_handler();
    fwrite(
        STDERR,
        'ADOdb PHP 8.5 smoke failed: ' . $oError->getMessage() .
        ' in ' . $oError->getFile() . ':' . $oError->getLine() . "\n"
    );
    exit(1);
}

restore_error_handler();

echo 'ADODB_PHP85_SMOKE=PASS (' . $GLOBALS['ADODB_vers'] . ")\n";
if (!is_null($sLiveServerVersion)) {
    echo "ADODB_MYSQL_LIVE=PASS ({$sLiveServerVersion})\n";
}
