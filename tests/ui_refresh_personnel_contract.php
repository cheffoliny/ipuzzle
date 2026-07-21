<?php

function personnelAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_PERSONNEL=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');

$lists = array(
    'setup_document_types.tpl' => array('newDocType', 'viewType', 'deleteDocumentType', "loadXMLDoc('result')"),
    'person_docs.tpl' => array('editDocument', 'delDocument', "loadXMLDoc('result')"),
    'set_setup_application_list.tpl' => array('setApplication', 'dialogSetupPersonLeave', 'rpcEnd', "loadXMLDoc('result')"),
    'set_setup_hospital_list.tpl' => array('setHospital', 'delApplication', "loadXMLDoc('result')"),
    'set_setup_quittance_list.tpl' => array('setQuittance', 'delApplication', "loadXMLDoc2( 'result' )"),
    'common_person_leaves.tpl' => array('openPerson', "loadXMLDoc2( 'loadOffices' )", "loadXMLDoc2( 'loadObjects' )", "loadXMLDoc2( \"load\" )"),
    'person_leave_graph.tpl' => array('setApplication', 'setHospital', 'openPerson', 'rpcEnd', "loadXMLDoc2( 'load' )"),
    'person_leave.tpl' => array('openPersonLeave', 'openApplication', 'openHospital', 'openQuittance', 'rpcEnd', "loadXMLDoc('result')"),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    personnelAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    personnelAssert(strpos($source, '<i class=') === false, $template . ' retains a legacy font icon');
    personnelAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug is missing');
    personnelAssert(strpos($source, '//rpc_debug') === false, $template . ' XML debug is still disabled');
    personnelAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1 || preg_match('/<div[^>]+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        personnelAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

foreach (array('person_docs.tpl', 'set_setup_application_list.tpl', 'set_setup_hospital_list.tpl', 'set_setup_quittance_list.tpl', 'person_leave.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    personnelAssert(strpos($source, 'rpc_excel_panel="off"') !== false, $template . ' Excel panel setting changed');
    personnelAssert(strpos($source, 'rpc_paging="off"') !== false, $template . ' paging setting changed');
    personnelAssert(strpos($source, 'rpc_resize="off"') !== false, $template . ' resize setting changed');
    personnelAssert(strpos($source, 'ui-personnel-result') !== false, $template . ' result layout marker is missing');
}

$graph = file_get_contents($root . '/templates/person_leave_graph.tpl');
personnelAssert(strpos($graph, 'rpc_paging="off"') !== false, 'leave graph paging setting changed');
personnelAssert(strpos($graph, 'rpc_excel_panel="off"') !== false, 'leave graph Excel panel setting changed');
personnelAssert(strpos($graph, 'rpc_autonumber = false') !== false, 'leave graph autonumber setting changed');
personnelAssert(strpos($graph, 'ui-leave-graph-layout') !== false, 'leave graph layout marker is missing');
personnelAssert(strpos($graph, 'ui-icon ui-icon-search') !== false, 'leave graph search icon is missing');

$common = file_get_contents($root . '/templates/common_person_leaves.tpl');
personnelAssert(strpos($common, 'ui-nomenclature-filter-wrap') !== false, 'common leave filter marker is missing');
personnelAssert(strpos($common, 'ui-icon ui-icon-search') !== false, 'common leave search icon is missing');

$personDocs = file_get_contents($root . '/templates/person_docs.tpl');
personnelAssert(strpos($personDocs, "dialogNewDocument( id, id_person )") !== false, 'person document editor binding changed');
personnelAssert(strpos($personDocs, 'ui-icon ui-icon-plus') !== false, 'person document add icon is missing');
personnelAssert(strpos($personDocs, 'ui-icon ui-icon-close') !== false, 'person document close icon is missing');

$personLeave = file_get_contents($root . '/templates/person_leave.tpl');
personnelAssert(strpos($personLeave, "onclick=\"loadXMLDoc( 'save' );\"") !== false, 'substitute setting behaviour changed');
personnelAssert(substr_count($personLeave, 'ui-icon ui-icon-plus') === 3, 'person leave add actions changed');
personnelAssert(strpos($personLeave, 'ui-icon ui-icon-list') !== false, 'person leave list icon is missing');
personnelAssert(strpos($personLeave, 'ui-nomenclature-checkbox') !== false, 'person leave checkbox styling is missing');

$dialogs = array(
    'set_setup_document_types.tpl' => array('id', 'name'),
    'set_setup_document.tpl' => array('id', 'id_person', 'id_document', 'document', 'date_in', 'doc_num', 'valid_from', 'valid_to', 'note'),
    'set_setup_application.tpl' => array('id', 'id_person', 'year', 'date', 'leave_from', 'leave_to', 'application_days', 'leave_types', 'info'),
    'set_setup_hospital.tpl' => array('id', 'id_person', 'year', 'date', 'leave_from', 'leave_to', 'application_days', 'info'),
    'set_setup_quittance.tpl' => array('id', 'id_person', 'year', 'date', 'nMonth', 'application_days', 'info'),
    'set_setup_leave.tpl' => array('id', 'id_person', 'year', 'due_days'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    personnelAssert(strpos($source, 'ui-personnel-dialog') !== false, $template . ' personnel dialog marker is missing');
    personnelAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    personnelAssert(strpos($source, '<i class=') === false, $template . ' retains a legacy font icon');
    personnelAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug is missing');
    personnelAssert(strpos($source, '//rpc_debug') === false, $template . ' XML debug is still disabled');

    foreach ($fields as $field) {
        personnelAssert(
            strpos($source, 'id="' . $field . '"') !== false || strpos($source, 'name="' . $field . '"') !== false,
            $template . ' field changed: ' . $field
        );
    }
}

$calendarDialogs = array(
    'set_setup_document.tpl' => array('img_date_in' => 'date_in', 'img_valid_from' => 'valid_from', 'img_valid_to' => 'valid_to'),
    'set_setup_application.tpl' => array('img_date' => 'date', 'img_leave_from' => 'leave_from', 'img_leave_to' => 'leave_to'),
    'set_setup_hospital.tpl' => array('img_date' => 'date', 'img_leave_from' => 'leave_from', 'img_leave_to' => 'leave_to'),
    'set_setup_quittance.tpl' => array('img_date' => 'date'),
);

foreach ($calendarDialogs as $template => $bindings) {
    $source = file_get_contents($root . '/templates/' . $template);
    foreach ($bindings as $trigger => $input) {
        personnelAssert(strpos($source, 'click_element_id="' . $trigger . '" input_element_id="' . $input . '"') !== false, $template . ' calendar binding changed: ' . $trigger);
        personnelAssert(strpos($source, 'id="' . $trigger . '"') !== false, $template . ' calendar trigger is missing: ' . $trigger);
    }
    personnelAssert(substr_count($source, 'ui-inline-calendar-trigger') === count($bindings), $template . ' calendar trigger count changed');
    personnelAssert(substr_count($source, 'ui-icon ui-icon-calendar') === count($bindings), $template . ' calendar icon count changed');
}

foreach (array('set_setup_application.tpl', 'set_setup_hospital.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    personnelAssert(strpos($source, "loadXMLDoc( 'save', 2 )") !== false, $template . ' save request changed');
    personnelAssert(strpos($source, "window.opener.loadXMLDoc('result')") !== false, $template . ' parent refresh changed');
}

$quittance = file_get_contents($root . '/templates/set_setup_quittance.tpl');
personnelAssert(strpos($quittance, "loadXMLDoc2( 'save', 2 )") !== false, 'quittance save request changed');
personnelAssert(strpos($quittance, "window.opener.loadXMLDoc2( 'result' )") !== false, 'quittance parent refresh changed');

$document = file_get_contents($root . '/templates/set_setup_document.tpl');
personnelAssert(strpos($document, 'function form_submit()') !== false, 'document validity confirmation changed');
personnelAssert(substr_count($document, "my_action = 'save';") === 2, 'document save paths do not both refresh the parent');
personnelAssert(substr_count($document, "loadXMLDoc('save', 3)") === 2, 'document save request paths changed');
personnelAssert(strpos($document, 'function onSuggestDocument') !== false, 'document suggestion binding changed');
personnelAssert(strpos($document, "window.opener.loadXMLDoc('result')") !== false, 'document parent refresh changed');

$leaveBalance = file_get_contents($root . '/templates/set_setup_leave.tpl');
personnelAssert(strpos($leaveBalance, "loadXMLDoc( 'save', 2 )") !== false, 'leave balance save request changed');
personnelAssert(strpos($leaveBalance, "window.opener.loadXMLDoc('result')") !== false, 'leave balance parent refresh changed');
personnelAssert(substr_count($leaveBalance, 'ui-icon ui-icon-calendar') === 2, 'leave balance field icons changed');

foreach (array('.ui-personnel-list', '.ui-personnel-toolbar', '.ui-personnel-result', '.ui-personnel-modal-list', '.ui-personnel-report-filter', '.ui-leave-graph-layout', '.ui-personnel-dialog', '.ui-person-document-dialog', '.ui-leave-entry-dialog', '.ui-leave-balance-dialog') as $selector) {
    personnelAssert(strpos($css, $selector) !== false, 'missing personnel style ' . $selector);
}

personnelAssert(strpos($icons, '.ui-icon-list') !== false, 'list icon mapping is missing');
personnelAssert(is_file($root . '/css/fa7/regular/list.svg'), 'list SVG asset is missing');

echo 'UI_REFRESH_PERSONNEL=PASS' . PHP_EOL;
