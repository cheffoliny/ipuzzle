<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-report-controls.css');
$page = file_get_contents($root . '/templates/page.tpl');
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

if (in_array(false, array($css, $page, $renderer, $xmlrpc), true)) {
    fwrite(STDERR, "Unable to read UI report controls contract files.\n");
    exit(1);
}

$checks = array(
    strpos($page, 'css/ui-refresh-report-controls.css?version=1') !== false,
    strpos($renderer, 'rpc-result-toolbar') !== false,
    strpos($renderer, 'rpc-result-paging') !== false,
    strpos($renderer, 'rpc-result-exports') !== false,
    strpos($renderer, 'if (options.paging !== "on")') !== false,
    strpos($renderer, 'if (options.excelPanel !== "on")') !== false,
    strpos($renderer, 'if (options.paging === "on" || options.excelPanel === "on")') !== false,
    strpos($renderer, 'global[prefix + "xslLoadDirectXML"]("export_to_xls")') !== false,
    strpos($renderer, 'global[prefix + "xslLoadDirectXML"]("export_to_pdf")') !== false,
    strpos($renderer, 'global[prefix + "xslLoadXML"]()') !== false,
    strpos($css, 'body.ui-refresh-content .rpc-result-toolbar') !== false,
    strpos($css, 'body.ui-refresh-content .rpc-result-paging') !== false,
    strpos($css, 'body.ui-refresh-content .rpc-result-exports') !== false,
    stripos($css, 'table.result') === false,
    stripos($css, 'tech_planning') === false,
    stripos($css, '#result_data') === false,
    strpos($xmlrpc, 'XML Response:') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI report controls isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_REPORT_CONTROLS_CONTRACT=PASS\n";
