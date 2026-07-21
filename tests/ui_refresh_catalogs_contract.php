<?php

function catalogsAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_CATALOGS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');

$lists = array(
    'setup_nomenclatures.tpl' => array('setupNomenclature', 'deleteNomenclature', 'importNomenclature', "loadXMLDoc2( 'result' )"),
    'nomenclatures_earnings.tpl' => array('openNomenclatureEarning', 'delNomenclatureEarning', "loadXMLDoc2('result')"),
    'nomenclatures_expenses.tpl' => array('openNomenclatureExpense', 'delNomenclatureExpense', "loadXMLDoc2('result')"),
    'nomenclatures_services.tpl' => array('setService', 'delService', "loadXMLDoc2('result')"),
    'nomenclatures_earexp_firms.tpl' => array('openEarexp', "loadXMLDoc2('result')"),
    'nomenclatures_services_firms.tpl' => array('editFirmServices', "loadXMLDoc2('result')"),
    'activities.tpl' => array('viewActivity', 'deleteActivity', "loadXMLDoc2( 'result' )"),
    'operations.tpl' => array('viewOperation', 'deleteOperation', "loadXMLDoc2( 'result' )"),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    catalogsAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    catalogsAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    catalogsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    catalogsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');
    catalogsAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        catalogsAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

foreach (array('setup_nomenclatures.tpl', 'activities.tpl', 'operations.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    catalogsAssert(strpos($source, 'ui-nomenclature-filter-wrap') !== false, $template . ' filter marker is missing');
    catalogsAssert(strpos($source, 'ui-icon ui-icon-search') !== false, $template . ' search icon is missing');
}

$setup = file_get_contents($root . '/templates/setup_nomenclatures.tpl');
catalogsAssert(strpos($setup, 'dialogImportNomenclature()') !== false, 'nomenclature import dialog changed');
catalogsAssert(strpos($setup, 'id="file_name"') !== false, 'nomenclature import file name field changed');
catalogsAssert(strpos($setup, 'id="file_type"') !== false, 'nomenclature import file type field changed');
catalogsAssert(strpos($setup, 'ui-icon ui-icon-file-import') !== false, 'nomenclature import icon is missing');

foreach (array('activities.tpl', 'operations.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    catalogsAssert(strpos($source, 'rpc_excel_panel = "off"') !== false, $template . ' Excel panel setting changed');
}

$dialogs = array(
    'set_setup_nomenclatures.tpl' => array('nID', 'nType', 'sName', 'sUnit', 'nPrice', 'nIDNomenclatureType'),
    'set_nomenclature_earning.tpl' => array('nID', 'sCode', 'sName', 'is_system'),
    'set_nomenclature_expense.tpl' => array('nID', 'sCode', 'sName', 'for_salary', 'for_trans', 'for_gsm', 'for_dds'),
    'set_nomenclature_service.tpl' => array('nID', 'sCode', 'sName', 'price', 'type_service', 'nIDMeasure', 'nIDNomenclatureEarning', 'name_edit', 'quantity_edit', 'price_edit', 'for_trans'),
    'set_nomenclatures_earexp_firms.tpl' => array('nID', 'nIDFirm', 'all_earnings', 'account_earnings', 'all_expenses', 'account_expenses'),
    'set_nomenclatures_services_firms.tpl' => array('nID', 'nIDFirm', 'all_services', 'account_services'),
    'activity_dialog.tpl' => array('nID', 'sName', 'sDesc'),
    'operation_dialog.tpl' => array('nID', 'sName', 'sDesc'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    catalogsAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    catalogsAssert(strpos($source, 'ui-icon ui-icon-save') !== false, $template . ' save icon is missing');
    catalogsAssert(strpos($source, 'ui-icon ui-icon-close') !== false, $template . ' close icon is missing');
    catalogsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    catalogsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');

    foreach ($fields as $field) {
        catalogsAssert(
            strpos($source, 'id="' . $field . '"') !== false || strpos($source, 'name="' . $field . '"') !== false,
            $template . ' field changed: ' . $field
        );
    }
}

$earningExpenseMapping = file_get_contents($root . '/templates/set_nomenclatures_earexp_firms.tpl');
catalogsAssert(strpos($earningExpenseMapping, "select_all_options('account_earnings')") !== false, 'earning firm mapping submission changed');
catalogsAssert(strpos($earningExpenseMapping, "select_all_options('account_expenses')") !== false, 'expense firm mapping submission changed');
catalogsAssert(substr_count($earningExpenseMapping, 'move_option_to(') === 8, 'earning/expense transfer controls changed');

$serviceMapping = file_get_contents($root . '/templates/set_nomenclatures_services_firms.tpl');
catalogsAssert(strpos($serviceMapping, "select_all_options('account_services')") !== false, 'service firm mapping submission changed');
catalogsAssert(substr_count($serviceMapping, 'move_option_to(') === 4, 'service transfer controls changed');

foreach (array('activity_dialog.tpl', 'operation_dialog.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    catalogsAssert(strpos($source, 'onsubmit="submit_form(); return false;"') !== false, $template . ' validation submit path changed');
    catalogsAssert(strpos($source, '<button type="submit"') !== false, $template . ' submit button semantics changed');
    catalogsAssert(substr_count($source, "loadXMLDoc2( 'save'") === 1, $template . ' can issue more than one save request');
    catalogsAssert(preg_match('/\{\/literal\}\s*\|\s*$/', $source) !== 1, $template . ' retains stray output after the script');
}

foreach (array('.ui-catalog-dialog', '.ui-money-nomenclature-dialog', '.ui-inline-dialog-actions', '.ui-account-mapping-dialog', '.ui-activity-dialog', '.ui-activity-filter') as $selector) {
    catalogsAssert(strpos($css, $selector) !== false, 'missing catalog style ' . $selector);
}

catalogsAssert(strpos($icons, '.ui-icon-file-import') !== false, 'file import icon mapping is missing');
catalogsAssert(is_file($root . '/css/fa7/regular/file-import.svg'), 'file import SVG asset is missing');

echo 'UI_REFRESH_CATALOGS=PASS' . PHP_EOL;
