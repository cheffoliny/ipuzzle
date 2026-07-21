<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-content.css');
$page = file_get_contents($root . '/templates/page.tpl');
$index = file_get_contents($root . '/templates/index.tpl');
$login = file_get_contents($root . '/templates/login.tpl');
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

if (in_array(false, array($css, $page, $index, $login, $renderer, $xmlrpc), true)) {
    fwrite(STDERR, "Unable to read UI content refresh contract files.\n");
    exit(1);
}

$forbiddenSelectors = array(
    'table.result',
    '#result',
    'result_data',
    'result_holder',
    'result_paging',
    'tech_planning',
    'rpc_',
    'iframe'
);

$checks = array(
    strpos($page, 'class="ui-refresh-content"') !== false,
    strpos($page, 'css/ui-refresh-content.css?version=1') !== false,
    strpos($index, 'css/ui-refresh-content.css') === false,
    strpos($login, 'css/ui-refresh-content.css') === false,
    strpos($css, 'body.ui-refresh-content') !== false,
    strpos($css, '.nav.nav-tabs') !== false,
    strpos($css, '.input-group-sm > .form-control') !== false,
    strpos($renderer, 'installReportFunctions') !== false,
    strpos($xmlrpc, 'XML Response:') !== false
);

foreach ($forbiddenSelectors as $selector) {
    $checks[] = stripos($css, $selector) === false;
}

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI content refresh isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_CONTENT_CONTRACT=PASS\n";

