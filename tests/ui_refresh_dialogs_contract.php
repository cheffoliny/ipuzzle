<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-dialogs.css');
$page = file_get_contents($root . '/templates/page.tpl');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

if (in_array(false, array($css, $page, $xmlrpc), true)) {
    fwrite(STDERR, "Unable to read UI dialog contract files.\n");
    exit(1);
}

$checks = array(
    strpos($page, 'css/ui-refresh-dialogs.css?version=1') !== false,
    strpos($css, 'body.ui-refresh-content .modal-content') !== false,
    strpos($css, 'body.ui-refresh-content .modal-header') !== false,
    strpos($css, 'body.ui-refresh-content .modal-header .close') !== false,
    strpos($css, 'body.ui-refresh-content .modal-body') !== false,
    strpos($css, 'body.ui-refresh-content .modal-footer') !== false,
    strpos($css, 'body.ui-refresh-content .modal-footer .btn') !== false,
    strpos($css, 'body.ui-refresh-content .modal-body .custom-checkbox .custom-control-input') !== false,
    stripos($css, 'position: fixed') === false,
    stripos($css, 'position: sticky') === false,
    preg_match('/z-index\s*:\s*(?:[1-9]\d+|999)/i', $css) !== 1,
    stripos($css, 'table.result') === false,
    stripos($css, 'rpc_') === false,
    stripos($css, 'onclick') === false,
    strpos($xmlrpc, 'XML Response:') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI dialog isolation contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_DIALOGS_CONTRACT=PASS\n";
