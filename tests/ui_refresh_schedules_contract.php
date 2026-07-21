<?php

function schedulesAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_SCHEDULES=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');
$page = file_get_contents($root . '/templates/page.tpl');

$templates = array(
    'set_setup_object_shifts.tpl',
    'shifts_count.tpl',
    'object_personnel_schedule.tpl',
    'schedule_hours.tpl',
    'personal_card_schedule.tpl',
    'person_schedule.tpl',
    'dayshifts2.tpl',
    'person_shifts.tpl',
    'shiftHistory.tpl',
    'object_duty.tpl',
);

foreach ($templates as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    schedulesAssert(strpos($source, 'ui-schedule-') !== false, $template . ' schedule marker is missing');
    schedulesAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    schedulesAssert(strpos($source, '<i class=') === false, $template . ' retains a legacy icon element');
    schedulesAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug is missing');
    schedulesAssert(strpos($source, '//rpc_debug') === false, $template . ' XML debug is commented out');
}

$resultSettings = array(
    'object_personnel_schedule.tpl' => array('rpc_excel_panel="off"', 'rpc_resize="off"', 'rpc_paging="off"'),
    'schedule_hours.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="on"', 'rpc_resize="off"'),
    'shiftHistory.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
    'personal_card_schedule.tpl' => array('rpc_excel_panel="off"'),
    'object_duty.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
);

foreach ($resultSettings as $template => $settings) {
    $source = file_get_contents($root . '/templates/' . $template);
    schedulesAssert(strpos($source, 'id="result"') !== false, $template . ' result area is missing');
    foreach ($settings as $setting) {
        schedulesAssert(strpos($source, $setting) !== false, $template . ' changed result setting ' . $setting);
    }
}

$shiftEditor = file_get_contents($root . '/templates/set_setup_object_shifts.tpl');
schedulesAssert(strpos($shiftEditor, "loadXMLDoc2('save', 3)") !== false, 'shift save request changed');
schedulesAssert(strpos($shiftEditor, "loadXMLDoc2('load')") !== false, 'shift load request changed');
foreach (array('sCode', 'sName', 'nType', 'sMode', 'sShiftFrom', 'sShiftTo', 'sDuration', 'sRealTime', 'sStake', 'sStakeDuty') as $field) {
    schedulesAssert(strpos($shiftEditor, 'name="' . $field . '"') !== false, 'shift field is missing: ' . $field);
}

$shiftsCount = file_get_contents($root . '/templates/shifts_count.tpl');
foreach (array('img_date_from', 'img_date_to') as $calendar) {
    schedulesAssert(strpos($shiftsCount, 'id="' . $calendar . '"') !== false, 'calendar trigger is missing: ' . $calendar);
    schedulesAssert(strpos($shiftsCount, 'click_element_id="' . $calendar . '"') !== false, 'calendar binding changed: ' . $calendar);
}
schedulesAssert(strpos($shiftsCount, "loadXMLDoc2( 'result' )") !== false, 'shifts count result request changed');

$objectPersonnel = file_get_contents($root . '/templates/object_personnel_schedule.tpl');
foreach (array("loadXMLDoc2('addPerson')", "loadXMLDoc2('sortNow', 1)", 'openSchedule()', 'nextMonth(') as $behaviour) {
    schedulesAssert(strpos($objectPersonnel, $behaviour) !== false, 'object personnel behaviour changed: ' . $behaviour);
}

$scheduleHours = file_get_contents($root . '/templates/schedule_hours.tpl');
schedulesAssert(strpos($scheduleHours, "onPrint('export_to_xls')") !== false, 'schedule Excel export changed');
schedulesAssert(strpos($scheduleHours, "onPrint('export_to_pdf')") !== false, 'schedule PDF export changed');

$personSchedule = file_get_contents($root . '/templates/person_schedule.tpl');
foreach (array('function serialize()', '"save&" + serialize()', '"validate&sValidateDate="', '"invalidate&sInvalidateDate="') as $behaviour) {
    schedulesAssert(strpos($personSchedule, $behaviour) !== false, 'person schedule behaviour changed: ' . $behaviour);
}

$dayShifts = file_get_contents($root . '/templates/dayshifts2.tpl');
schedulesAssert(strpos($dayShifts, 'id="Validate"') !== false, 'day shifts validation button cannot be addressed by id');
schedulesAssert(strpos($dayShifts, 'setTimeout( "loadXMLDoc2(\'result\')", 300000 )') !== false, 'day shifts periodic refresh changed');

$objectDuty = file_get_contents($root . '/templates/object_duty.tpl');
foreach (array('function setDutyButton(', 'if (!button) return;', 'id="Validate"', 'id="butShift"', "loadXMLDoc2('duty', 1)", "loadXMLDoc2('erase', 1)", "loadXMLDoc2('autoValidate')") as $behaviour) {
    schedulesAssert(strpos($objectDuty, $behaviour) !== false, 'object duty behaviour changed: ' . $behaviour);
}
schedulesAssert(strpos($objectDuty, "setDutyButton(butt, 'Изтрий', 'ui-icon-delete')") !== false, 'object duty delete state icon changed');
schedulesAssert(strpos($objectDuty, "setDutyButton(butt, 'Смяна', 'ui-icon-plus')") !== false, 'object duty add state icon changed');

$dialogs = file_get_contents($root . '/js/common_dialogs.js');
schedulesAssert(strpos($dialogs, "dialog_win('set_setup_object_shifts&id='+id+'&obj='+obj, 390, 440") !== false, 'shift editor popup is too short for the refreshed form');

foreach (array(
    '.ui-schedule-dialog',
    '.ui-schedule-report',
    '.ui-schedule-filter',
    '.ui-shift-editor-layout',
    '.ui-shift-editor-field',
    '.ui-schedule-actions',
    '.ui-schedule-result',
    '.ui-object-duty-dialog',
    '.ui-person-schedule-report',
) as $selector) {
    schedulesAssert(strpos($css, $selector) !== false, 'missing schedule style ' . $selector);
}

$iconAssets = array(
    'ui-icon-code' => 'hashtag.svg',
    'ui-icon-name' => 'font.svg',
    'ui-icon-layout' => 'table-cells.svg',
    'ui-icon-play' => 'play.svg',
    'ui-icon-stop' => 'stop.svg',
    'ui-icon-clock' => 'clock.svg',
    'ui-icon-money' => 'euro-sign.svg',
    'ui-icon-wrench' => 'wrench.svg',
    'ui-icon-sort' => 'sort.svg',
    'ui-icon-tags' => 'tags.svg',
);

foreach ($iconAssets as $iconClass => $asset) {
    schedulesAssert(strpos($icons, '.' . $iconClass) !== false, 'icon mapping is missing: ' . $iconClass);
    schedulesAssert(strpos($icons, $asset) !== false, 'icon asset mapping changed: ' . $asset);
    schedulesAssert(is_file($root . '/css/fa7/regular/' . $asset), 'icon asset is missing: ' . $asset);
}

schedulesAssert(strpos($page, 'css/ui-refresh-nomenclatures.css?version=14') !== false, 'schedule stylesheet cache version is stale');

echo 'UI_REFRESH_SCHEDULES=PASS' . PHP_EOL;
