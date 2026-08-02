<?php

function technicalCardsAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_TECHNICAL_CARDS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');
$page = file_get_contents($root . '/templates/page.tpl');

$templates = array(
    'limit_card_ppp.tpl',
    'limit_card_persons.tpl',
    'limit_card_operations.tpl',
    'limit_card_info.tpl',
    'tech_limit_cards.tpl',
    'working_card_movement.tpl',
    'working_card_info.tpl',
    'working_cards.tpl',
    'working_card_patrol.tpl',
    'working_card_movement_add.tpl',
    'tech_support_requests.tpl',
    'tech_analytics.tpl',
    'tech_analytics_objects.tpl',
    'tech_planning_persons.tpl',
    'tech_planning_schedule.tpl',
);

foreach ($templates as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    technicalCardsAssert(strpos($source, 'ui-technical-') !== false, $template . ' technical UI marker is missing');
    technicalCardsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    technicalCardsAssert(strpos($source, '<i class=') === false, $template . ' retains a legacy icon element');
    technicalCardsAssert(strpos($source, 'class="fa') === false, $template . ' retains a legacy FontAwesome class');
    technicalCardsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug declaration is missing');
    technicalCardsAssert(strpos($source, 'rpc_debug = true') !== false || strpos($source, 'rpc_debug=true') !== false, $template . ' XML debug is not active');
}

$resultSettings = array(
    'limit_card_persons.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
    'limit_card_operations.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
    'tech_limit_cards.tpl' => array('rpc_resize="no"'),
    'tech_support_requests.tpl' => array('rpc_resize="yes"'),
    'tech_planning_persons.tpl' => array('rpc_excel_panel="off"'),
    'tech_planning_schedule.tpl' => array('rpc_excel_panel="off"', 'rpc_resize="off"'),
);

foreach ($resultSettings as $template => $settings) {
    $source = file_get_contents($root . '/templates/' . $template);
    technicalCardsAssert(strpos($source, 'id="result"') !== false, $template . ' result area is missing');
    foreach ($settings as $setting) {
        technicalCardsAssert(strpos($source, $setting) !== false, $template . ' changed result setting ' . $setting);
    }
}

$calendarBindings = array(
    'tech_limit_cards.tpl' => array('img_date_from' => 'date_from', 'img_date_to' => 'date_to'),
    'working_cards.tpl' => array('calFrom' => 'sFrom', 'calTo' => 'sTo'),
    'working_card_movement_add.tpl' => array('imgsAlarmD' => 'sAlarmD'),
    'tech_support_requests.tpl' => array('img_date_from' => 'date_from', 'img_date_to' => 'date_to'),
    'tech_planning_persons.tpl' => array('imgDate' => 'date'),
    'tech_planning_schedule.tpl' => array('imgDate' => 'date'),
);

foreach ($calendarBindings as $template => $bindings) {
    $source = file_get_contents($root . '/templates/' . $template);
    foreach ($bindings as $trigger => $input) {
        technicalCardsAssert(strpos($source, 'click_element_id="' . $trigger . '"') !== false, $template . ' calendar trigger changed: ' . $trigger);
        technicalCardsAssert(strpos($source, 'input_element_id="' . $input . '"') !== false, $template . ' calendar input changed: ' . $input);
        technicalCardsAssert(strpos($source, 'id="' . $trigger . '"') !== false, $template . ' calendar element is missing: ' . $trigger);
    }
}

$limitInfo = file_get_contents($root . '/templates/limit_card_info.tpl');
foreach (array("loadXMLDoc2('save', 3)", "loadXMLDoc2('save', 4)", "loadXMLDoc2('cancel')", "loadXMLDoc2('cancel2')", 'submit_form()', 'openObject()', 'openRequest()') as $behaviour) {
    technicalCardsAssert(strpos($limitInfo, $behaviour) !== false, 'limit card behaviour changed: ' . $behaviour);
}
foreach (array('imgPlannedStart', 'imgPlannedEnd', 'imgRealStart', 'imgRealEnd') as $trigger) {
    technicalCardsAssert(strpos($limitInfo, 'id="' . $trigger . '"') !== false, 'limit card date trigger is missing: ' . $trigger);
}

$limitPersons = file_get_contents($root . '/templates/limit_card_persons.tpl');
technicalCardsAssert(strpos($limitPersons, "rpc_result_area = 'dresult'") !== false, 'limit card availability result routing changed');
technicalCardsAssert(strpos($limitPersons, "rpc_result_area = 'result'") !== false, 'limit card persons result routing changed');
technicalCardsAssert(strpos($limitPersons, "rpc_renderer_profile = 'limitCardPersons'") !== false, 'limit card availability renderer profile changed');

$workingInfo = file_get_contents($root . '/templates/working_card_info.tpl');
technicalCardsAssert(strpos($workingInfo, "loadXMLDoc2('save')") !== false, 'working card save action changed');
technicalCardsAssert(strpos($workingInfo, "loadXMLDoc2('close', 1)") !== false, 'working card close action changed');
technicalCardsAssert(substr_count($workingInfo, 'ui-nomenclature-transfer-button') === 4, 'working card region transfer controls changed');

$movement = file_get_contents($root . '/templates/working_card_movement.tpl');
foreach (array('movement_filter_add', 'movement_filter_edit', 'movement_filter_delete') as $id) {
    technicalCardsAssert(substr_count($movement, 'id="' . $id . '"') === 1, 'working card filter id is missing or duplicated: ' . $id);
}
technicalCardsAssert(strpos($movement, "loadXMLDoc('deleteFilter',6)") !== false, 'working card filter deletion changed');
technicalCardsAssert(strpos($movement, 'text.replace(/red_tag/g') !== false, 'working card movement result post-processing changed');

$support = file_get_contents($root . '/templates/tech_support_requests.tpl');
foreach (array('tech_filter_add', 'tech_filter_edit', 'tech_filter_delete', 'type_requests', 'type_contracts') as $id) {
    technicalCardsAssert(substr_count($support, 'id="' . $id . '"') === 1, 'technical request control id is missing or duplicated: ' . $id);
}
foreach (array("loadXMLDoc2('limit', 1)", "loadXMLDoc2('delete', 1)", "loadXMLDoc('ignoreContract',1)", 'goToPlanning()') as $behaviour) {
    technicalCardsAssert(strpos($support, $behaviour) !== false, 'technical request behaviour changed: ' . $behaviour);
}

$planningPersons = file_get_contents($root . '/templates/tech_planning_persons.tpl');
technicalCardsAssert(strpos($planningPersons, 'newDate.getFullYear()') !== false, 'technical persons date navigation still uses deprecated getYear');
technicalCardsAssert(strpos($planningPersons, 'newDate.getYear()') === false, 'technical persons date navigation retains getYear');

$planning = file_get_contents($root . '/templates/tech_planning_schedule.tpl');
foreach (array('function planning(', 'function save()', "loadXMLDoc2('planning', 0)", "requestsFrame.contentWindow.loadXMLDoc2('result')") as $behaviour) {
    technicalCardsAssert(strpos($planning, $behaviour) !== false, 'technical planning behaviour changed: ' . $behaviour);
}
technicalCardsAssert(strpos($planning, 'images/time/red') === false, 'technical planning retains GIF duration bars');
technicalCardsAssert(strpos($planning, 'ui-tech-time-bar-{$nPicNum}') !== false, 'technical planning CSS duration bar is missing');

foreach (array(
    '.ui-technical-list',
    '.ui-technical-dialog',
    '.ui-technical-filter',
    '.ui-technical-result',
    '.ui-technical-icon-button',
    '.ui-technical-planning-toolbar',
    '.ui-tech-time-bar',
    '.ui-tech-time-bar-1',
    '.ui-tech-time-bar-16',
) as $selector) {
    technicalCardsAssert(strpos($css, $selector) !== false, 'missing technical UI style ' . $selector);
}
technicalCardsAssert(
    preg_match('/\.ui-technical-planning-toolbar \.form-control,[^{]+\{[^}]*height:\s*32px\s*!important/s', $css) === 1,
    'technical planning controls do not share one height'
);
technicalCardsAssert(strpos($css, '.ui-technical-planning-toolbar .form-control-inp75') !== false, 'technical planning date width is not normalized');

technicalCardsAssert(strpos($icons, '.ui-icon-document') !== false, 'document icon mapping is missing');
technicalCardsAssert(strpos($icons, 'file-lines.svg') !== false, 'document icon asset mapping changed');
technicalCardsAssert(is_file($root . '/css/fa7/solid/file-lines.svg'), 'document icon asset is missing');
technicalCardsAssert(strpos($page, 'css/ui-fa7-icons.css?version=16') !== false, 'FA7 icon cache version is stale');
technicalCardsAssert(strpos($page, 'css/ui-refresh-nomenclatures.css?version=43') !== false, 'technical stylesheet cache version is stale');

echo 'UI_REFRESH_TECHNICAL_CARDS=PASS' . PHP_EOL;
