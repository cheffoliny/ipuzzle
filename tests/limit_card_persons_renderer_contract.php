<?php

$root = dirname(__DIR__);
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$template = file_get_contents($root . '/templates/limit_card_persons.tpl');
$database = file_get_contents($root . '/db_api/DBLimitCardPersons.class.php');

if ($renderer === false || $template === false || $database === false) {
    fwrite(STDERR, "Unable to read limit-card personnel migration files.\n");
    exit(1);
}

$report2Start = strpos($database, 'public function getReport2');
$report2End = strpos($database, 'public function delRequests', $report2Start === false ? 0 : $report2Start);
$report2 = $report2Start !== false && $report2End !== false
    ? substr($database, $report2Start, $report2End - $report2Start)
    : '';

$deleteCallback = strpos($template, 'rpc_on_exit = function(nCode)');
$deleteRequest = strpos($template, "loadXMLDoc2('delete', 0)");
$reportCallback = strpos($template, 'window.setTimeout(loadLimitCardAvailability, 0)');
$reportRequest = strpos($template, "loadXMLDoc2('result')");

$checks = array(
    strpos($renderer, 'limitCardPersons: true') !== false,
    strpos($renderer, 'function isSupportedProfile(profile)') !== false,
    strpos($renderer, 'prefix + "tableResult"') !== false,
    strpos($renderer, '"c[" + childText(cellField, "name")') !== false,
    strpos($renderer, 'if (!limitCardPersons)') !== false,
    strpos($template, 'function loadLimitCardReports()') !== false,
    strpos($template, 'function loadLimitCardAvailability()') !== false,
    strpos($template, "rpc_renderer_profile = 'limitCardPersons'") !== false,
    strpos($template, "rpc_method = 'POST'") !== false,
    $deleteCallback !== false && $deleteRequest !== false && $deleteCallback < $deleteRequest,
    $reportCallback !== false && $reportRequest !== false && $reportCallback < $reportRequest,
    strpos($report2, "preg_replace('/[^0-9,]/'") !== false,
    strpos($report2, '$data[\'id\'] = $val') === false,
    strpos($report2, 'getStatus($nID)') === false,
    substr_count($database, ' €') >= 5
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "The limit-card personnel DOM/PHP 8.5 contract is incomplete.\n");
    exit(1);
}

echo "LIMIT_CARD_PERSONS_RENDERER_CONTRACT=PASS\n";
