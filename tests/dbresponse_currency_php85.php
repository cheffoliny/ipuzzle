<?php

chdir(dirname(__DIR__));

// Keep every PHP 8.5 diagnostic visible while exercising the project
// bootstrap and the DBResponse XML compatibility layer.
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SESSION = array('BASE_DIR' => getcwd());

require_once 'config/function.autoload.php';
require_once 'db_api/include/DBResponse.class.php';

$oResponse = new DBResponse();
$oCurrencyField = new DBField();
$oCurrencyField->aAttributes = array('DATA_FORMAT' => DF_CURRENCY);

$aCases = array(
    '-1839.88 лв.' => '-1 839.88 € ',
    '-1839.88 €' => '-1 839.88 € ',
    '1 234,56 €' => '1 234.56 € ',
    '125' => '125.00 € ',
    '' => '',
    'няма стойност' => 'няма стойност'
);

foreach ($aCases as $sInput => $sExpected) {
    $sActual = $oResponse->dataFormat($oCurrencyField, NULL, 'owed_tax', $sInput);
    if ($sActual !== $sExpected) {
        fwrite(
            STDERR,
            "DBResponse currency formatting failed for " . var_export($sInput, true) .
            ': expected ' . var_export($sExpected, true) .
            ', got ' . var_export($sActual, true) . ".\n"
        );
        exit(1);
    }
}

$oNullFormResponse = new DBResponse();
$previousErrorHandler = set_error_handler(
    static function (int $severity, string $message, string $file, int $line): bool {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

try {
    $oNullFormResponse->setFormElement('form1', 'empty_value');
    $oNullFormResponse->setFormElementChild('form1', 'empty_value');
} finally {
    restore_error_handler();
}

$oEmptyElement = $oNullFormResponse->oAction->aForms['form1']->aFormElements['empty_value'];
if ($oEmptyElement->mValue !== '' || $oEmptyElement->aChilds[0]->mValue !== '') {
    fwrite(STDERR, "DBResponse NULL form values were not normalized to empty strings.\n");
    exit(1);
}

$oXmlResponse = new DBResponse();
$oXmlResponse->setField(
    'owed_tax',
    'Дължими',
    NULL,
    NULL,
    NULL,
    NULL,
    array('DATA_FORMAT' => DF_CURRENCY)
);
$oXmlResponse->setData(array(array('owed_tax' => '-1839.88 €')));
$oXmlResponse->addTotal('owed_tax', '-1839.88 лв.');

$sXml = $oXmlResponse->toXML();
$oDocument = new DOMDocument();
if (!$oDocument->loadXML($sXml)) {
    fwrite(STDERR, "DBResponse produced invalid XML.\n");
    exit(1);
}

$oXPath = new DOMXPath($oDocument);
$sRowValue = trim($oXPath->evaluate('string(/response/result/data/r/c[1])'));
$sTotalValue = trim($oXPath->evaluate('string(/response/result/total/c[2])'));

if ($sRowValue !== '-1 839.88 €' || $sTotalValue !== '-1 839.88 €') {
    fwrite(
        STDERR,
        'DBResponse XML currency formatting failed: row=' . var_export($sRowValue, true) .
        ', total=' . var_export($sTotalValue, true) . ".\n"
    );
    exit(1);
}

echo "DBRESPONSE_CURRENCY_PHP85=PASS\n";
