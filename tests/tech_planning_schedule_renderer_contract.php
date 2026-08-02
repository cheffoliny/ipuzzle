<?php

$root = dirname(__DIR__);
$renderer = file_get_contents($root . '/js/rpc_result_renderer.js');
$xmlrpc = file_get_contents($root . '/js/xmlrpc.js');
$template = file_get_contents($root . '/templates/tech_planning_schedule.tpl');
$api = file_get_contents($root . '/api/api_tech_planning_schedule.php');

if ($renderer === false || $xmlrpc === false || $template === false || $api === false) {
    fwrite(STDERR, "Unable to read the tech planning schedule renderer files.\n");
    exit(1);
}

$saveCallback = strpos($template, 'rpc_on_exit = function(nCode)');
$saveRequest = strpos($template, "loadXMLDoc2('planning', 0)");
$missingClassGuard = strpos($api, "class_exists('DBContractsGuardedRoomsNomenclatures')");
$missingClassUse = strpos($api, 'new DBContractsGuardedRoomsNomenclatures()', $missingClassGuard === false ? 0 : $missingClassGuard);

$checks = array(
    strpos($renderer, 'techPlanningSchedule: true') !== false,
    strpos($renderer, 'techPlanningSchedule') !== false,
    strpos($renderer, 'result table-sm w-100 table-borderless mt-1') !== false,
    strpos($renderer, 'planningSchedule ? "total"') !== false,
    strpos($renderer, 'if (planningSchedule)') !== false,
    strpos($renderer, 'prepareTechPlanningScheduleCell') !== false,
    strpos($renderer, 'planning-slot') !== false,
    strpos($renderer, 'setProperty("background-color"') !== false,
    strpos($xmlrpc, 'RpcResultRenderer.isSupportedProfile') !== false,
    strpos($xmlrpc, '// DOM-only runtime.') !== false,
    strpos($xmlrpc, 'XSLTProcessor') === false,
    strpos($xmlrpc, "xslhttp.open('GET', rpc_xsl") === false,
    strpos($template, 'rpc_renderer_profile = "techPlanningSchedule"') !== false,
    strpos($template, 'rpc_excel_panel="off"') !== false,
    strpos($template, 'rpc_resize="off"') !== false,
    strpos($template, "setProperty('background-color', color, 'important')") !== false,
    strpos($template, 'window.setTimeout(function()') !== false,
    $saveCallback !== false && $saveRequest !== false && $saveCallback < $saveRequest,
    strpos($api, 'planningSlotToTime($nStartSlot)') !== false,
    strpos($api, 'planningSlotToTime($nEndSlot + 1)') !== false,
    substr_count(file_get_contents($root . '/db_api/DBLimitCardPersons.class.php'), 'o.num AS object_num') >= 3,
    strpos($api, "'Планирана задача'") !== false,
    strpos($api, "'#476f95'") !== false,
    strpos($api, '$nSlotMinutes >= $nCardStartMinutes') !== false,
    strpos($api, 'if (($nDate + 30 * $i * 60 >=') === false,
    strpos($api, 'APILog::Log("123231"') === false,
    $missingClassGuard !== false && $missingClassUse !== false && $missingClassGuard < $missingClassUse
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "The tech planning schedule DOM-rendering contract is incomplete.\n");
    exit(1);
}

echo "TECH_PLANNING_SCHEDULE_RENDERER_CONTRACT=PASS\n";
