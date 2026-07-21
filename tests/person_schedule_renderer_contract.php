<?php

$root = dirname(__DIR__);
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');
$template = file_get_contents($root . '/templates/person_schedule.tpl');
$database = file_get_contents($root . '/db_api/DBObjectShifts.class.php');
$api = file_get_contents($root . '/api/api_person_schedule.php');

if ($renderer === false || $xmlrpc === false || $template === false || $database === false || $api === false) {
    fwrite(STDERR, "Unable to read person-schedule migration files.\n");
    exit(1);
}

$checks = array(
    strpos($renderer, 'person_schedule\\.xsl') !== false,
    strpos($renderer, 'return "personSchedule"') !== false,
    strpos($renderer, 'function renderPersonSchedule(') !== false,
    strpos($renderer, 'id: "tableShifts"') !== false,
    strpos($renderer, 'id: "tableResult"') !== false,
    strpos($renderer, '"c[" + fieldName + "][" + rowId + "]"') !== false,
    strpos($renderer, '"sid[" + fieldName + "][" + rowId + "]"') !== false,
    strpos($renderer, '"real_hours[" + rowId + "]"') !== false,
    strpos($renderer, 'data-shift-code') !== false,
    strpos($renderer, 'renderPersonScheduleToolbar') !== false,
    strpos($xmlrpc, 'RpcResultRenderer.isSupportedStylesheet(rpc_xsl)') !== false,
    strpos($xmlrpc, 'FormProcessing_action(xml);') !== false,
    strpos($xmlrpc, 'XML Response:') !== false,
    strpos($template, 'getAttribute("data-shift-code")') !== false,
    strpos($template, 'oCell.textContent.split') !== false,
    strpos($template, 'shift_hours_span[') !== false,
    strpos($api, '$oResponse->setFormElement("form1", "object_shifts", array());') !== false,
    strpos($database, '$aRowTotal = !empty($aDataTemp) ? array_pop($aDataTemp) : array();') !== false,
    strpos($database, 'if (!is_array($aRowTotal))') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "The person-schedule DOM/PHP 8.5 contract is incomplete.\n");
    exit(1);
}

echo "PERSON_SCHEDULE_RENDERER_CONTRACT=PASS\n";
