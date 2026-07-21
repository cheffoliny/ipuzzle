<?php

function financeDocumentsAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_FINANCE_DOCUMENTS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');

$filters = array('buy_docs_filter.tpl', 'sales_docs_filter.tpl');
$calendarBindings = array(
    'editDocDateFrom' => 'sDocDateFrom',
    'editDocDateTo' => 'sDocDateTo',
    'editLastOrderFrom' => 'sLastOrderFrom',
    'editLastOrderTo' => 'sLastOrderTo',
    'editCreateDateFrom' => 'sCreateDateFrom',
    'editCreateDateTo' => 'sCreateDateTo',
    'editRobotFromDate' => 'sRobotFromDate',
);

foreach ($filters as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    financeDocumentsAssert(strpos($source, 'ui-document-filter-dialog') !== false, $template . ' filter marker is missing');
    financeDocumentsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    financeDocumentsAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug is missing');
    financeDocumentsAssert(strpos($source, 'function onSave') !== false, $template . ' save behaviour changed');
    financeDocumentsAssert(strpos($source, 'loadXMLDoc2( "save", 5 )') !== false, $template . ' save request changed');
    financeDocumentsAssert(strpos($source, 'nomenclatures_all') !== false, $template . ' source nomenclatures changed');
    financeDocumentsAssert(strpos($source, 'nomenclatures_current') !== false, $template . ' selected nomenclatures changed');
    financeDocumentsAssert(substr_count($source, 'ui-nomenclature-transfer-button') === 2, $template . ' transfer actions changed');

    foreach ($calendarBindings as $trigger => $input) {
        financeDocumentsAssert(strpos($source, 'click_element_id="' . $trigger . '"') !== false, $template . ' calendar binding changed: ' . $trigger);
        financeDocumentsAssert(strpos($source, 'input_element_id="' . $input . '"') !== false, $template . ' calendar input changed: ' . $input);
        financeDocumentsAssert(strpos($source, 'id="' . $trigger . '"') !== false, $template . ' calendar trigger is missing: ' . $trigger);
    }

    financeDocumentsAssert(substr_count($source, 'ui-inline-calendar-trigger') === count($calendarBindings), $template . ' calendar trigger count changed');
}

$documentDialogs = array(
    'buy_doc_info.tpl' => array('result', 'change_view', 'del_doc', 'save'),
    'sale_doc_info.tpl' => array('result', 'change_view', 'del_doc', 'save'),
    'sale_doc_info2.tpl' => array('result', 'change_view', 'del_doc', 'save2'),
    'buy_doc_row.tpl' => array('load', 'save', 'list_objects', 'loadOffices'),
    'buy_doc_inventory.tpl' => array('result', 'del_row'),
    'buy_doc_orders.tpl' => array('result'),
    'sale_doc_inventory.tpl' => array('result', 'izvestie'),
    'sale_doc_orders.tpl' => array('result'),
);

foreach ($documentDialogs as $template => $actions) {
    $source = file_get_contents($root . '/templates/' . $template);
    financeDocumentsAssert(strpos($source, 'ui-finance-document-dialog') !== false, $template . ' document dialog marker is missing');
    financeDocumentsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    financeDocumentsAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug is missing');

    foreach ($actions as $action) {
        financeDocumentsAssert(strpos($source, "loadXMLDoc2('" . $action) !== false, $template . ' RPC action changed: ' . $action);
    }
}

foreach (array('buy_doc_info.tpl', 'sale_doc_info.tpl', 'sale_doc_info2.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    financeDocumentsAssert(strpos($source, 'click_element_id="editDocDate"') !== false, $template . ' document date trigger binding changed');
    financeDocumentsAssert(strpos($source, 'input_element_id="sDocDate"') !== false, $template . ' document date input binding changed');
    financeDocumentsAssert(strpos($source, 'id="editDocDate"') !== false, $template . ' document date trigger is missing');
}

foreach (array('sale_doc_info.tpl', 'sale_doc_info2.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    financeDocumentsAssert(strpos($source, 'rpc_resize="off"') !== false, $template . ' resize setting changed');
    financeDocumentsAssert(strpos($source, 'rpc_excel_panel="off"') !== false, $template . ' Excel panel setting changed');
    financeDocumentsAssert(strpos($source, 'ui-icon ui-icon-file-pdf') !== false, $template . ' PDF action icon is missing');
}

$payments = array(
    'sales_pay_orders.tpl' => 'confirm',
    'group_sales_pay_orders.tpl' => 'confirmBUGGGG',
    'group_buyes_pay_orders.tpl' => 'confirmBUGGGG',
);

foreach ($payments as $template => $action) {
    $source = file_get_contents($root . '/templates/' . $template);
    financeDocumentsAssert(strpos($source, 'ui-payment-dialog') !== false, $template . ' payment dialog marker is missing');
    financeDocumentsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    financeDocumentsAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug is missing');
    financeDocumentsAssert(strpos($source, 'function ' . $action) !== false, $template . ' confirmation behaviour changed');
    financeDocumentsAssert(strpos($source, "loadXMLDoc2('confirm'") !== false, $template . ' confirmation RPC changed');
    financeDocumentsAssert(strpos($source, 'лв.') === false, $template . ' retains the old currency label');
    financeDocumentsAssert(strpos($source, '€') !== false, $template . ' euro label is missing');
}

$manifest = json_decode(file_get_contents($root . '/scripts/unused-files-manifest.json'), true);
$legacyGroup = null;
foreach ($manifest['groups'] as $group) {
    if ($group['id'] === 'legacy_finance_templates_replaced_by_vue') {
        $legacyGroup = $group;
        break;
    }
}
financeDocumentsAssert(is_array($legacyGroup), 'legacy finance candidate group is missing');
financeDocumentsAssert($legacyGroup['classification'] === 'conditional_candidate', 'legacy finance templates were classified as safe deletion');
financeDocumentsAssert(in_array('templates/buy.tpl', $legacyGroup['selectors']['files'], true), 'buy.tpl is not marked conditionally');
financeDocumentsAssert(in_array('templates/sales.tpl', $legacyGroup['selectors']['files'], true), 'sales.tpl is not marked conditionally');

$dialogs = file_get_contents($root . '/js/common_dialogs.js');
financeDocumentsAssert(strpos($dialogs, "dialog_win('sales_pay_orders&id='+id+str,400,260") !== false, 'single payment popup height is too small');
financeDocumentsAssert(strpos($dialogs, "dialog_win('group_sales_pay_orders&id='+id+'&bank='+bank,400,260") !== false, 'group sale payment popup height is too small');
financeDocumentsAssert(strpos($dialogs, "dialog_win('group_buyes_pay_orders&id='+id+'&bank='+bank,400,260") !== false, 'group purchase payment popup height is too small');

foreach (array('.ui-finance-document-dialog', '.ui-document-filter-dialog', '.ui-document-filter-transfer', '.ui-sale-document-dialog', '.ui-payment-dialog', '.ui-document-danger') as $selector) {
    financeDocumentsAssert(strpos($css, $selector) !== false, 'missing finance document style ' . $selector);
}

echo 'UI_REFRESH_FINANCE_DOCUMENTS=PASS' . PHP_EOL;
