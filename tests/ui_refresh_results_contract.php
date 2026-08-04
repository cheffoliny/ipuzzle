<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-results.css');
$page = file_get_contents($root . '/templates/page.tpl');
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

if (in_array(false, array($css, $page, $renderer, $xmlrpc), true)) {
    fwrite(STDERR, "Unable to read UI result spacing contract files.\n");
    exit(1);
}

$checks = array(
    strpos($page, 'css/ui-refresh-results.css?version=3') !== false,
    strpos($page, 'js/rpc_result_renderer.js?version=10') !== false,
    strpos($page, 'js/xmlrpc.js?version=5') !== false,
    strpos($css, '#result:not(.body-content) > .container-fluid.body-content') !== false,
    strpos($css, '#result2:not(.body-content) > .container-fluid.body-content') !== false,
    substr_count($css, '4px !important') === 2,
    stripos($css, 'table.result') === false,
    strpos($css, 'background-color: var(--ip-content-primary, #1769c2)') !== false,
    preg_match('/(^|\n)\s*color\s*:/i', $css) === 0,
    stripos($css, ':hover') === false,
    stripos($css, 'tech_planning') === false,
    strpos($css, '.rpc-result-scroll-host') !== false,
    strpos($css, '.rpc-result-scroll-content') !== false,
    strpos($css, '.rpc-result-column-header > th') !== false,
    strpos($css, 'position: sticky') !== false,
    strpos($css, 'overscroll-behavior: contain') !== false,
    strpos($renderer, 'container-fluid body-content pb-5') !== false,
    strpos($renderer, 'installReportFunctions') !== false,
    strpos($renderer, 'installGeneralResultResizer') !== false,
    strpos($renderer, 'data-rpc-auto-resized') !== false,
    strpos($renderer, 'function markResultColumnHeaders') !== false,
    strpos($renderer, 'prepareStickyHeaders: markResultColumnHeaders') !== false,
    strpos($xmlrpc, 'if( oTarget )') !== false,
    strpos($xmlrpc, 'RpcResultRenderer.prepareStickyHeaders(oTarget)') !== false,
    strpos($xmlrpc, 'XML Response:') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI result spacing isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_RESULTS_CONTRACT=PASS\n";
