<?php

function checkboxAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_CHECKBOX=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-dialogs.css');
$objects = file_get_contents($root . '/templates/setup_objects_filter.tpl');
$clients = file_get_contents($root . '/templates/setup_clients_filter.tpl');
$salary = file_get_contents($root . '/templates/admin_salary_total_filter.tpl');

foreach (array(
    '.custom-control-input:not(:checked):not(:disabled)',
    '.custom-control-input:checked ~ .custom-control-label::before',
    '.custom-control-input:focus-visible ~ .custom-control-label::before',
    '.custom-control-input:disabled ~ .custom-control-label',
    '.custom-control-input:checked:disabled ~ .custom-control-label::before',
) as $selector) {
    checkboxAssert(strpos($css, $selector) !== false, 'missing checkbox state ' . $selector);
}

checkboxAssert(strpos($css, 'fa7/solid/check.svg') !== false, 'checked state does not use the FA7 icon');
checkboxAssert(is_file($root . '/css/fa7/solid/check.svg'), 'FA7 check.svg is missing');

foreach (array(
    'is_default', 'nMonthTax', 'nUnpaidSingle', 'nLastPaid', 'nObjectFunction',
    'nObjectPhone', 'nStartDate', 'nCityC', 'nAddress', 'nDistance',
    'nOperativeInfo', 'nReactReg', 'nWorkTime', 'nAdminReg', 'nTech', 'nTechReg',
) as $field) {
    checkboxAssert(
        preg_match('/<input\b[^>]*\bid\s*=\s*(["\'])' . preg_quote($field, '/') . '\1[^>]*>/', $objects) === 1,
        'setup_objects_filter field changed: ' . $field
    );
    checkboxAssert(strpos($objects, 'for="' . $field . '"') !== false, 'missing label association for ' . $field);
}

checkboxAssert(strpos($objects, "loadXMLDoc2( 'load' )") !== false, 'object filter load RPC changed');
checkboxAssert(strpos($objects, "loadXMLDoc2( 'save' )") !== false, 'object filter save RPC changed');
checkboxAssert(strpos($objects, 'rpc_debug = true') !== false, 'object filter XML debug was disabled');

foreach (array($objects, $clients, $salary) as $template) {
    checkboxAssert(strpos($template, 'custom-control custom-checkbox') !== false, 'related filter lost custom checkbox markup');
}

checkboxAssert(strpos($objects, 'ui-icon ui-icon-plus') !== false, 'object filter action icon was not migrated');
checkboxAssert(strpos($clients, 'ui-icon ui-icon-plus') !== false, 'client filter action icon was not migrated');

echo 'UI_REFRESH_CHECKBOX=PASS' . PHP_EOL;
