<?php

function legacyReportAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_LEGACY_REPORTS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$page = file_get_contents($root . '/templates/page.tpl');
$css = file_get_contents($root . '/css/ui-refresh-legacy-reports.css');

legacyReportAssert(
    strpos($page, 'css/ui-refresh-legacy-reports.css?version=2') !== false,
    'legacy report stylesheet is not loaded by page.tpl'
);
legacyReportAssert(
    strpos($css, 'body.ui-refresh-content .ui-export-docs') !== false,
    'export report rules are not scoped to the refreshed content shell'
);
legacyReportAssert(
    strpos($css, 'form.ui-export-docs > hr') !== false,
    'obsolete export report separator is not handled'
);

foreach (array('export_sale_docs.tpl', 'export_buy_docs.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);

    foreach (array('ui-export-docs', 'ui-finance-export-docs', 'ui-legacy-report-heading', 'ui-legacy-report-filter', 'ui-export-docs-filter', 'ui-finance-export-result') as $marker) {
        legacyReportAssert(strpos($source, $marker) !== false, $template . ' misses ' . $marker);
    }

    foreach (array('nIDFirm', 'nIDOffice', 'sPeriodFrom', 'sPeriodTo') as $field) {
        legacyReportAssert(
            preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '\1/', $source) === 1,
            $template . ' no longer exposes ' . $field
        );
    }

    legacyReportAssert(
        preg_match('/onClick\s*=\s*(["\'])formSubmit\(\);(?:\s*return false;)?\1/', $source) === 1,
        $template . ' export action changed'
    );
    legacyReportAssert(strpos($source, 'rpc_excel_panel="on"') !== false, $template . ' Excel panel changed');
    legacyReportAssert(strpos($source, 'rpc_paging="on"') !== false, $template . ' paging changed');
    legacyReportAssert(strpos($source, 'rpc_resize="on"') !== false, $template . ' resize changed');
    legacyReportAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug is missing');
    legacyReportAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    legacyReportAssert(strpos($source, '<i ') === false, $template . ' retains a legacy icon tag');
    legacyReportAssert(strpos($source, 'ui-icon-file-export') !== false, $template . ' solid export icon is missing');
    legacyReportAssert(strpos($source, 'click_element_id="imgPeriodFrom"') !== false, $template . ' start calendar binding changed');
    legacyReportAssert(strpos($source, 'click_element_id="imgPeriodTo"') !== false, $template . ' end calendar binding changed');
    legacyReportAssert(strpos($source, 'id="imgPeriodFrom"') !== false, $template . ' start calendar trigger is missing');
    legacyReportAssert(strpos($source, 'id="imgPeriodTo"') !== false, $template . ' end calendar trigger is missing');
}

foreach (array('.ui-finance-export-docs', '.ui-finance-export-result', '.ui-export-time') as $selector) {
    legacyReportAssert(strpos($css, $selector) !== false, 'missing finance export style ' . $selector);
}

echo 'UI_REFRESH_LEGACY_REPORTS=PASS' . PHP_EOL;
