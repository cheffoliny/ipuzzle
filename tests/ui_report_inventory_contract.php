<?php

require_once dirname(__DIR__) . '/scripts/audit_ui_reports.php';

function inventoryAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REPORT_INVENTORY=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$inventory = auditUiReports(dirname(__DIR__));
$categoryTotal = array_sum($inventory['categories']);
$byTemplate = array();

foreach ($inventory['reports'] as $report) {
    $byTemplate[$report['template']] = $report;
}

inventoryAssert($inventory['total'] >= 180, 'unexpectedly small #result inventory');
inventoryAssert($categoryTotal === $inventory['total'], 'category totals do not match inventory total');

foreach (array('export_sale_docs.tpl', 'export_buy_docs.tpl') as $template) {
    inventoryAssert(isset($byTemplate[$template]), $template . ' is missing from the inventory');
    inventoryAssert(
        $byTemplate[$template]['category'] === 'migrated_legacy',
        $template . ' is not classified as migrated legacy'
    );

    foreach (array('rpc_paging', 'rpc_excel_panel', 'rpc_resize') as $flag) {
        inventoryAssert(
            $byTemplate[$template]['flags'][$flag] === 'on',
            $template . ' must preserve ' . $flag . '="on"'
        );
    }
}

echo 'UI_REPORT_INVENTORY=PASS (' . $inventory['total'] . ' reports)' . PHP_EOL;
