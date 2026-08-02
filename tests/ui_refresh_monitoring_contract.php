<?php

$root = dirname(__DIR__);

function monitoringAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_MONITORING=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$templates = array(
    'states_filter_totals.tpl',
    'states_statistics.tpl',
    'statistics.tpl',
    'view_states.tpl',
    'patruls_movement.tpl',
    'patrul_parking.tpl',
    'stop_movement.tpl',
    'object_monitor.tpl',
);

$sources = array();
foreach ($templates as $template) {
    $sources[$template] = file_get_contents($root . '/templates/' . $template);
    monitoringAssert(strpos($sources[$template], 'ui-monitor-') !== false, 'missing monitoring UI marker in ' . $template);
    monitoringAssert(strpos($sources[$template], '<img') === false, 'legacy image remains in ' . $template);
}

foreach (array(
    'states_filter_totals.tpl',
    'states_statistics.tpl',
    'statistics.tpl',
    'view_states.tpl',
    'patruls_movement.tpl',
    'patrul_parking.tpl',
    'stop_movement.tpl',
) as $debugTemplate) {
    monitoringAssert(strpos($sources[$debugTemplate], 'rpc_debug = true') !== false, 'XML debug was disabled in ' . $debugTemplate);
}

$calendarBindings = array(
    'statistics.tpl' => array('editFromDate' => 'sFromDate', 'editToDate' => 'sToDate'),
    'states_statistics.tpl' => array('editFromDate' => 'sFromDate', 'editToDate' => 'sToDate'),
    'patruls_movement.tpl' => array('img_date_from' => 'date_from', 'img_date_to' => 'date_to'),
    'stop_movement.tpl' => array('imgEndTime' => 'sEndTime', 'imgReasonTime' => 'sReasonTime'),
    'states_filter_totals.tpl' => array('editFromDate' => 'sFromDate'),
);

foreach ($calendarBindings as $template => $bindings) {
    foreach ($bindings as $trigger => $input) {
        $source = $sources[$template];
        monitoringAssert(strpos($source, 'click_element_id="' . $trigger . '"') !== false, 'calendar trigger binding changed: ' . $template . '/' . $trigger);
        monitoringAssert(strpos($source, 'input_element_id="' . $input . '"') !== false, 'calendar input binding changed: ' . $template . '/' . $input);
        monitoringAssert(strpos($source, '<button type="button" id="' . $trigger . '"') !== false, 'calendar button is missing: ' . $template . '/' . $trigger);
    }
}

monitoringAssert(strpos($sources['statistics.tpl'], "loadXMLDoc2( 'result' )") !== false, 'statistics result action changed');
monitoringAssert(strpos($sources['states_statistics.tpl'], "loadXMLDoc2( 'result' )") !== false, 'states statistics result action changed');
monitoringAssert(strpos($sources['view_states.tpl'], "loadXMLDoc2( 'result' )") !== false, 'states result action changed');
monitoringAssert(strpos($sources['view_states.tpl'], "loadXMLDoc( 'deleteFilter', 6 )") !== false, 'states filter delete action changed');
monitoringAssert(strpos($sources['patruls_movement.tpl'], "loadXMLDoc('deleteFilter',6)") !== false, 'patrol filter delete action changed');
monitoringAssert(strpos($sources['patruls_movement.tpl'], "loadXMLDoc2('result')") !== false, 'patrol movement result action changed');
monitoringAssert(strpos($sources['patruls_movement.tpl'], "$('date_to').value != ''") !== false, 'patrol archive end-date guard is broken');
monitoringAssert(strpos($sources['patruls_movement.tpl'], '.vlaue') === false, 'patrol archive end-date typo remains');
monitoringAssert(strpos($sources['patrul_parking.tpl'], "loadXMLDoc2('delete', 1)") !== false, 'patrol parking delete action changed');
monitoringAssert(strpos($sources['stop_movement.tpl'], "loadXMLDoc2('save', 3)") !== false, 'stop movement save action changed');
$commonDialogs = file_get_contents($root . '/js/common_dialogs.js');
monitoringAssert(strpos($commonDialogs, "dialog_win('stop_movement&id='+id,380,300,1,'stop_movement')") !== false, 'stop movement dialog is too small for the refreshed form');
monitoringAssert(strpos($sources['states_filter_totals.tpl'], "loadXMLDoc2('save',5)") !== false, 'automatic state filter save action changed');
monitoringAssert(strpos($sources['object_monitor.tpl'], 'onclick="stopStart();"') !== false, 'live monitor toggle action changed');
monitoringAssert(strpos($sources['object_monitor.tpl'], "onclick=\"monitor('once');\"") !== false, 'live monitor refresh action changed');
monitoringAssert(strpos($sources['object_monitor.tpl'], 'ui-live-monitor-result') !== false, 'live monitor result sizing marker is missing');
monitoringAssert(strpos($sources['object_monitor.tpl'], 'style="width: 800px; height: 380px;') === false, 'live monitor retains fixed inline result dimensions');
monitoringAssert(substr_count($sources['object_monitor.tpl'], '</th>') === 6, 'live monitor table headers are malformed');

foreach (array('view_states.tpl', 'patruls_movement.tpl') as $filterTemplate) {
    monitoringAssert(strpos($sources[$filterTemplate], 'id=b25') === false, 'duplicate filter button id remains in ' . $filterTemplate);
    foreach (array('ui-icon-plus', 'ui-icon-edit', 'ui-icon-delete') as $iconClass) {
        monitoringAssert(strpos($sources[$filterTemplate], $iconClass) !== false, 'filter action icon is missing in ' . $filterTemplate . ': ' . $iconClass);
    }
}

$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
foreach (array(
    '.ui-monitor-report',
    '.ui-monitor-dialog',
    '.ui-monitor-filter',
    '.ui-monitor-result',
    '.ui-live-monitor-result',
    '.ui-monitor-dialog-action-cell',
) as $selector) {
    monitoringAssert(strpos($css, $selector) !== false, 'missing monitoring style ' . $selector);
}

$icons = file_get_contents($root . '/css/ui-fa7-icons.css');
monitoringAssert(strpos($icons, '.ui-icon-monitor') !== false, 'monitor icon mapping is missing');
monitoringAssert(strpos($icons, 'radar.svg') !== false, 'monitor icon asset mapping changed');
monitoringAssert(is_file($root . '/css/fa7/solid/radar.svg'), 'monitor icon asset is missing');

$page = file_get_contents($root . '/templates/page.tpl');
monitoringAssert(strpos($page, 'css/ui-fa7-icons.css?version=18') !== false, 'FA7 icon cache version is stale');
monitoringAssert(strpos($page, 'css/ui-refresh-nomenclatures.css?version=47') !== false, 'monitor stylesheet cache version is stale');

echo 'UI_REFRESH_MONITORING=PASS' . PHP_EOL;
