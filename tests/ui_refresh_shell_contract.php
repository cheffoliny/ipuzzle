<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-shell.css');
$login = file_get_contents($root . '/templates/login.tpl');
$index = file_get_contents($root . '/templates/index.tpl');
$page = file_get_contents($root . '/templates/page.tpl');
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

if (in_array(false, array($css, $login, $index, $page, $renderer, $xmlrpc), true)) {
    fwrite(STDERR, "Unable to read UI refresh contract files.\n");
    exit(1);
}

$checks = array(
    strpos($login, 'lang="bg"') !== false,
    strpos($login, 'ui-refresh-login') !== false,
    strpos($login, 'css/ui-refresh-shell.css?version=1') !== false,
    strpos($login, 'for="name"') !== false,
    strpos($login, 'for="password"') !== false,
    strpos($login, 'id="password"') !== false,
    strpos($login, 'data-fa-transform="right-22 down-6"') === false,
    strpos($index, 'ui-refresh-shell') !== false,
    strpos($index, 'css/ui-refresh-shell.css?version=2') !== false,
    strpos($page, 'css/ui-refresh-shell.css') === false,
    strpos($css, 'body.ui-refresh-login') !== false,
    strpos($css, 'body.ui-refresh-shell') !== false,
    strpos($css, 'table.result') === false,
    strpos($css, '#result') === false,
    strpos($css, 'rpc_') === false,
    strpos($renderer, 'installReportFunctions') !== false,
    strpos($xmlrpc, 'XML Response:') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI refresh shell isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_SHELL_CONTRACT=PASS\n";
