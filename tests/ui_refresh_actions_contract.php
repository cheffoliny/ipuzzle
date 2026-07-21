<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-actions.css');
$page = file_get_contents($root . '/templates/page.tpl');
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

if (in_array(false, array($css, $page, $renderer, $xmlrpc), true)) {
    fwrite(STDERR, "Unable to read UI action contract files.\n");
    exit(1);
}

$checks = array(
    strpos($page, 'css/ui-refresh-actions.css?version=1') !== false,
    strpos($css, 'body.ui-refresh-content .table-secondary .btn:not(.btn-link)') !== false,
    strpos($css, '.table-secondary .btn-primary') !== false,
    strpos($css, '.table-secondary .btn-info') !== false,
    strpos($css, '.table-secondary .btn-success') !== false,
    strpos($css, '.table-secondary .btn-danger') !== false,
    strpos($css, '.table-secondary .btn-light') !== false,
    stripos($css, 'table.result') === false,
    stripos($css, '.rpc-result') === false,
    stripos($css, '.btnshift') === false,
    stripos($css, 'tech_planning') === false,
    strpos($renderer, 'rpc-result-toolbar') !== false,
    strpos($xmlrpc, 'XML Response:') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI action isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_ACTIONS_CONTRACT=PASS\n";
