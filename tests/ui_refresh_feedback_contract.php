<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-feedback.css');
$page = file_get_contents($root . '/templates/page.tpl');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');
$misc = file_get_contents($root . '/js/misc.js');

if (in_array(false, array($css, $page, $xmlrpc, $misc), true)) {
    fwrite(STDERR, "Unable to read UI feedback contract files.\n");
    exit(1);
}

$checks = array(
    strpos($page, 'css/ui-refresh-feedback.css?version=1') !== false,
    strpos($page, 'id="systemMessageBG"') !== false,
    strpos($page, 'id="systemMessageDialog"') !== false,
    strpos($page, 'id="sytemMessageValue"') !== false,
    strpos($page, 'onclick="closeSystemMessage();return false;"') !== false,
    strpos($xmlrpc, "_rpc_obj_loader.id = 'loading'") !== false,
    strpos($xmlrpc, "_rpc_obj_loader.style.display = 'flex'") !== false,
    strpos($xmlrpc, "_rpc_obj_loader.style.display = 'none'") !== false,
    strpos($xmlrpc, 'rpc-loading__icon') !== false,
    strpos($xmlrpc, 'rpc-loading__text') !== false,
    strpos($misc, "loader.style.display = (func == 1) ? 'flex' : 'none'") !== false,
    strpos($css, 'body.ui-refresh-content .rpc-loading') !== false,
    strpos($css, 'body.ui-refresh-content #systemMessageDialog.system-message') !== false,
    strpos($xmlrpc, 'XML Response:') !== false,
    strpos($xmlrpc, 'create_debug_window') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI feedback lifecycle contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_FEEDBACK_CONTRACT=PASS\n";
