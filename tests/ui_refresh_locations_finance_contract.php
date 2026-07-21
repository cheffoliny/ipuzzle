<?php

function locationsFinanceAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_LOCATIONS_FINANCE=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');

$lists = array(
    'setup_cities.tpl' => array('openCity', 'deleteCity', "loadXMLDoc2('result')"),
    'setup_city_areas.tpl' => array('setupArea', 'deleteArea', "loadXMLDoc2( 'result' )"),
    'setup_city_streets.tpl' => array('setupStreet', 'deleteStreet', "loadXMLDoc2( 'result' )"),
    'setup_compensations.tpl' => array('openCompensation', 'deleteCompensation', "loadXMLDoc2('result')"),
    'setup_month_charges.tpl' => array('openCharge', 'deleteCharge', "loadXMLDoc2('result')"),
    'setup_salary_earning.tpl' => array('newSalaryEarning', 'viewSalaryEarning', 'deleteSalaryEarning', "loadXMLDoc('result')"),
    'setup_salary_expense.tpl' => array('newSalaryExpense', 'viewSalaryExpense', 'deleteSalaryExpense', "loadXMLDoc('result')"),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    locationsFinanceAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    locationsFinanceAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    locationsFinanceAssert(strpos($source, 'ui-icon ui-icon-plus') !== false, $template . ' add icon is missing');
    locationsFinanceAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    locationsFinanceAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');
    locationsFinanceAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        locationsFinanceAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

foreach (array('setup_city_areas.tpl', 'setup_city_streets.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    locationsFinanceAssert(strpos($source, 'ui-nomenclature-filter-wrap') !== false, $template . ' filter marker is missing');
    locationsFinanceAssert(strpos($source, 'name="nIDCity"') !== false, $template . ' city filter changed');
    locationsFinanceAssert(strpos($source, 'ui-icon ui-icon-search') !== false, $template . ' search icon is missing');
}

$concessions = file_get_contents($root . '/templates/setup_concessions.tpl');
locationsFinanceAssert(strpos($concessions, 'ui-finance-list') !== false, 'concessions special list marker is missing');
locationsFinanceAssert(strpos($concessions, '{include file="finance_instruments_tabs.tpl"}') !== false, 'concessions finance tabs changed');
locationsFinanceAssert(strpos($concessions, 'rpc_resize="on"') !== false, 'concessions resize setting changed');
locationsFinanceAssert(strpos($concessions, 'function onInit()') !== false, 'concessions initialization changed');
locationsFinanceAssert(strpos($concessions, 'ui-icon ui-icon-plus') !== false, 'concessions add icon is missing');
locationsFinanceAssert(strpos($concessions, '<img') === false, 'concessions retains a legacy image');

$dialogs = array(
    'set_setup_cities.tpl' => array('nID', 'nPostCode', 'sName'),
    'set_setup_city_area.tpl' => array('nID', 'nIDCityTrans', 'sName', 'nIDCity'),
    'set_setup_city_street.tpl' => array('nID', 'nIDCityTrans', 'sName', 'nIDCity'),
    'set_setup_compensation.tpl' => array('nID', 'sType', 'nYearly', 'nSingle', 'nFactor'),
    'set_setup_contract_month_charge.tpl' => array('nID', 'sType', 'nBasePrice', 'nFactorDetector', 'nKClientTech', 'nPriceRadioPanic', 'nPriceStaticPanic', 'nPriceKbdPanic', 'nPriceOnlineBill', 'nPriceTelepolVest', 'nExpressOrderPrice', 'nFastOrderPrice'),
    'set_setup_concession.tpl' => array('nID', 'sName', 'nIDNomenclatureEarning', 'nMonthsCount', 'nPercent'),
    'set_setup_salary_earning.tpl' => array('id', 'code', 'name', 'measure', 'source', 'sLeaveType', 'nIsCompensation', 'nIsHospital'),
    'set_setup_salary_expense.tpl' => array('id', 'code', 'name', 'measure', 'source'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    locationsFinanceAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    locationsFinanceAssert(strpos($source, 'ui-nomenclature-actions') !== false, $template . ' action bar marker is missing');
    locationsFinanceAssert(strpos($source, 'ui-icon ui-icon-save') !== false, $template . ' save icon is missing');
    locationsFinanceAssert(strpos($source, 'ui-icon ui-icon-close') !== false, $template . ' close icon is missing');
    locationsFinanceAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    locationsFinanceAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');

    foreach ($fields as $field) {
        locationsFinanceAssert(
            strpos($source, 'id="' . $field . '"') !== false || strpos($source, 'name="' . $field . '"') !== false,
            $template . ' field changed: ' . $field
        );
    }
}

$monthCharge = file_get_contents($root . '/templates/set_setup_contract_month_charge.tpl');
locationsFinanceAssert(substr_count($monthCharge, 'ui-nomenclature-fieldset') === 2, 'month charge fee groups changed');
locationsFinanceAssert(strpos($monthCharge, 'ui-month-charge-dialog') !== false, 'month charge dialog marker is missing');

$salaryEarning = file_get_contents($root . '/templates/set_setup_salary_earning.tpl');
locationsFinanceAssert(strpos($salaryEarning, 'ui-nomenclature-checkbox') !== false, 'salary earning checkbox styling is missing');
locationsFinanceAssert(strpos($salaryEarning, "window.opener.loadXMLDoc('result')") !== false, 'salary earning parent refresh changed');

$salaryExpense = file_get_contents($root . '/templates/set_setup_salary_expense.tpl');
locationsFinanceAssert(strpos($salaryExpense, "window.opener.loadXMLDoc('result')") !== false, 'salary expense parent refresh changed');

foreach (array('.ui-finance-list', '.ui-finance-list-actions', '.ui-location-dialog', '.ui-financial-dialog', '.ui-month-charge-dialog') as $selector) {
    locationsFinanceAssert(strpos($css, $selector) !== false, 'missing location/finance style ' . $selector);
}

echo 'UI_REFRESH_LOCATIONS_FINANCE=PASS' . PHP_EOL;
