<?php

$root = dirname(__DIR__);
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');
$template = file_get_contents($root . '/templates/tech_planning_requests.tpl');

if ($renderer === false || $xmlrpc === false || $template === false) {
    fwrite(STDERR, "Unable to read the tech planning renderer contract files.\n");
    exit(1);
}

$requirements = [
    'specialized stylesheet recognition' => strpos($renderer, 'tech_planning_request\\.xsl') !== false,
    'specialized renderer profile' => strpos($renderer, 'techPlanningRequest') !== false,
    'real row id propagation' => strpos($renderer, 'setRequestId(rowId)') !== false,
    'local request id field' => strpos($renderer, 'getElementById("id_request")') !== false,
    'selected row legacy color' => strpos($renderer, '#2c2c61') !== false,
    'specialized resize support' => strpos($renderer, 'installTechPlanningRequestResizer') !== false,
    'supported stylesheet integration' => strpos($xmlrpc, 'RpcResultRenderer.isSupportedStylesheet(rpc_xsl)') !== false,
    'renderer profile integration' => strpos($xmlrpc, 'RpcResultRenderer.getStylesheetProfile(rpc_xsl)') !== false,
    'template keeps export panel off' => strpos($template, 'rpc_excel_panel="off"') !== false,
    'template keeps paging off' => strpos($template, 'rpc_paging="off"') !== false,
];

$failed = array_keys(array_filter($requirements, static fn (bool $passed): bool => !$passed));
if ($failed !== []) {
    fwrite(STDERR, "Tech planning request renderer contract failed: " . implode(', ', $failed) . "\n");
    exit(1);
}

echo "TECH_PLANNING_REQUEST_RENDERER_CONTRACT=PASS\n";
