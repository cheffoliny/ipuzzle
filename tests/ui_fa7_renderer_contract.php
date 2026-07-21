<?php

function rendererIconAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_FA7_RENDERER=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$page = file_get_contents($root . '/templates/page.tpl');
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');

rendererIconAssert(strpos($page, 'js/rpc_result_renderer.js?version=8') !== false, 'renderer cache version was not advanced');

foreach (array(
    'ui-icon-file-pdf', 'ui-icon-mail', 'ui-icon-check', 'ui-icon-delete', 'ui-icon-edit',
    'ui-icon-home', 'ui-icon-more', 'ui-icon-minus', 'ui-icon-plus',
    'ui-icon-search', 'ui-icon-calendar', 'ui-icon-file-excel',
    'ui-icon-volume', 'ui-icon-info', 'ui-icon-refresh',
) as $iconClass) {
    rendererIconAssert(strpos($renderer, $iconClass) !== false, 'renderer misses ' . $iconClass);
}

rendererIconAssert(strpos($renderer, 'var imageCssClass = iconClass(image);') !== false, 'data-cell images are not mapped');
rendererIconAssert(strpos($renderer, 'images/refresh_ppp.gif') === false, 'invoice refresh still uses a GIF');
rendererIconAssert(strpos($renderer, 'images/cal.gif') === false, 'invoice calendar still uses a GIF');
rendererIconAssert(strpos($xmlrpc, 'XML Response:') !== false, 'XML debug output changed');

echo 'UI_FA7_RENDERER=PASS' . PHP_EOL;
