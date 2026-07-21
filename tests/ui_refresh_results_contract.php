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
    strpos($page, 'css/ui-refresh-results.css?version=1') !== false,
    strpos($css, '#result:not(.body-content) > .container-fluid.body-content') !== false,
    strpos($css, '#result2:not(.body-content) > .container-fluid.body-content') !== false,
    substr_count($css, '4px !important') === 2,
    stripos($css, 'table.result') === false,
    stripos($css, 'background') === false,
    stripos($css, 'color:') === false,
    stripos($css, ':hover') === false,
    stripos($css, 'tech_planning') === false,
    stripos($css, 'rpc_') === false,
    strpos($renderer, 'container-fluid body-content pb-5') !== false,
    strpos($renderer, 'installReportFunctions') !== false,
    strpos($xmlrpc, 'XML Response:') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI result spacing isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_RESULTS_CONTRACT=PASS\n";

