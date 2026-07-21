<?php

function nomenclatureGroupsAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_NOMENCLATURE_GROUPS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');

$lists = array(
    'object_statuses.tpl' => array('openStatus', 'deleteStatus', 'loadXMLDoc2'),
    'object_functions.tpl' => array('openFunction', 'deleteFunction', 'loadXMLDoc2'),
    'holdup_reasons.tpl' => array('openHoldupReason', 'deleteHoldupReason', 'loadXMLDoc2'),
    'setup_measures.tpl' => array('openMeasure', 'deleteMeasure', 'loadXMLDoc2'),
    'setup_directions.tpl' => array('openDirection', 'deleteDirection', 'checkRegions'),
    'setup_document_types.tpl' => array('newDocType', 'deleteDocumentType', 'loadXMLDoc'),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    nomenclatureGroupsAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    nomenclatureGroupsAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    nomenclatureGroupsAssert(strpos($source, 'ui-icon ui-icon-plus') !== false, $template . ' add icon was not migrated');
    nomenclatureGroupsAssert(strpos($source, 'images/plus.gif') === false, $template . ' retains its legacy add image');
    nomenclatureGroupsAssert(strpos($source, 'rpc_debug = true') !== false || strpos($source, 'rpc_debug=true') !== false, $template . ' XML debug was disabled');
    nomenclatureGroupsAssert(preg_match('/<div\s+id=([' . "\"'" . '])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        nomenclatureGroupsAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

$dialogs = array(
    'set_object_status.tpl' => array('nID', 'sName', 'nIsSod', 'nPayable', 'nPlay'),
    'set_object_function.tpl' => array('nID', 'sName', 'nIsSod', 'nIsFo'),
    'set_holdup_reason.tpl' => array('nID', 'sName', 'nFromTechSignals'),
    'set_setup_measure.tpl' => array('nID', 'sCode', 'sDescription'),
    'set_setup_direction.tpl' => array('nID', 'sName'),
    'set_setup_document_types.tpl' => array('id', 'name'),
    'set_setup_trouble.tpl' => array('nID', 'nIDObject', 'sTroubleType', 'nTroubleTypeName', 'sTroubleInfo', 'nReasonTypeName', 'sReasonInfo'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    nomenclatureGroupsAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    nomenclatureGroupsAssert(strpos($source, 'ui-nomenclature-actions') !== false, $template . ' action bar marker is missing');
    nomenclatureGroupsAssert(strpos($source, 'ui-icon ui-icon-save') !== false, $template . ' save icon was not migrated');
    nomenclatureGroupsAssert(strpos($source, 'ui-icon ui-icon-close') !== false, $template . ' close icon was not migrated');

    foreach ($fields as $field) {
        nomenclatureGroupsAssert(
            preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '\1/', $source) === 1,
            $template . ' field changed: ' . $field
        );
    }
}

$troubles = file_get_contents($root . '/templates/object_troubles.tpl');
nomenclatureGroupsAssert(strpos($troubles, 'class="ui-object-troubles"') !== false, 'object troubles marker is missing');
nomenclatureGroupsAssert(strpos($troubles, 'rpc_excel_panel="off"') !== false, 'object troubles Excel panel setting changed');
nomenclatureGroupsAssert(strpos($troubles, 'rpc_paging="on"') !== false, 'object troubles paging setting changed');
nomenclatureGroupsAssert(strpos($troubles, 'rpc_resize="off"') !== false, 'object troubles resize setting changed');
nomenclatureGroupsAssert(strpos($troubles, 'height: 400px') !== false, 'object troubles working height changed');
nomenclatureGroupsAssert(strpos($troubles, 'images/glyphicons/tech.png') === false, 'object troubles technical icon was not migrated');
nomenclatureGroupsAssert(strpos($troubles, 'images/glyphicons/cancel.png') === false, 'object troubles close icon was not migrated');

$holidays = file_get_contents($root . '/templates/setup_holidays.tpl');
nomenclatureGroupsAssert(strpos($holidays, 'class="ui-holidays"') !== false, 'holidays marker is missing');
nomenclatureGroupsAssert(preg_match('/<iframe\s+id=([' . "\"'" . '])result\1/', $holidays) === 1, 'holidays iframe result changed');
nomenclatureGroupsAssert(strpos($holidays, 'function onResize') !== false, 'holidays resize behaviour changed');
nomenclatureGroupsAssert(strpos($holidays, 'function printPDF') !== false, 'holidays PDF behaviour changed');
nomenclatureGroupsAssert(strpos($holidays, 'ui-icon ui-icon-left') !== false, 'holidays previous-year icon was not migrated');
nomenclatureGroupsAssert(strpos($holidays, 'ui-icon ui-icon-right') !== false, 'holidays next-year icon was not migrated');
nomenclatureGroupsAssert(strpos($holidays, 'ui-icon ui-icon-file-pdf') !== false, 'holidays PDF icon was not migrated');

foreach (array('.ui-object-troubles', '.ui-holidays', '.ui-nomenclature-checkbox') as $selector) {
    nomenclatureGroupsAssert(strpos($css, $selector) !== false, 'missing scoped style ' . $selector);
}

echo 'UI_REFRESH_NOMENCLATURE_GROUPS=PASS' . PHP_EOL;
