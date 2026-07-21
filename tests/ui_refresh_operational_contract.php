<?php

function operationalAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_OPERATIONAL=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');

$lists = array(
    'setup_actives.tpl' => array('editActives', 'deleteActives', "loadXMLDoc('result')"),
    'setup_alarm_reasons.tpl' => array('openReason', 'deleteReason', "loadXMLDoc2('result')"),
    'setup_assistants.tpl' => array('openAssistant', 'deleteAssistant', "loadXMLDoc2( 'genregions' )", "loadXMLDoc2( 'load' )"),
    'setup_patruls.tpl' => array('editPatruls', 'delPatruls', "loadXMLDoc2('load')"),
    'setup_requests.tpl' => array('openRequest', 'deleteRequest', "loadXMLDoc2('result')"),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    operationalAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    operationalAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    operationalAssert(strpos($source, 'ui-icon ui-icon-plus') !== false, $template . ' add icon is missing');
    operationalAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    operationalAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');
    operationalAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        operationalAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

foreach (array('setup_assistants.tpl', 'setup_patruls.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    operationalAssert(strpos($source, 'ui-nomenclature-filter-wrap') !== false, $template . ' filter marker is missing');
    operationalAssert(strpos($source, 'ui-icon ui-icon-search') !== false, $template . ' search icon is missing');
}

$techPrices = file_get_contents($root . '/templates/setup_tech_prices.tpl');
operationalAssert(strpos($techPrices, 'ui-tech-prices-list') !== false, 'technical prices list marker is missing');
operationalAssert(strpos($techPrices, 'ui-tech-prices-summary') !== false, 'technical prices summary marker is missing');
operationalAssert(substr_count($techPrices, '<form') === 1, 'technical prices still contains nested or duplicate forms');
operationalAssert(substr_count($techPrices, 'id="form1"') === 1, 'technical prices form id is duplicated');
operationalAssert(strpos($techPrices, 'ui-icon ui-icon-edit') !== false, 'technical prices edit icon is missing');
operationalAssert(strpos($techPrices, '<img') === false, 'technical prices retains a legacy image');
foreach (array('nID', 'sListDate', 'nBasePrice', 'nFactor', 'sUpdatedUser') as $field) {
    operationalAssert(strpos($techPrices, 'id="' . $field . '"') !== false, 'technical prices field changed: ' . $field);
}

$dialogs = array(
    'set_setup_actives.tpl' => array('id', 'code', 'name'),
    'set_setup_alarm_reasons.tpl' => array('nID', 'sName'),
    'set_setup_assistant.tpl' => array('nID', 'sOfficesParam', 'nIDFirm', 'nIDRegion', 'nIDPerson', 'nNextNum', 'Offices', 'selected_offices'),
    'set_setup_patruls.tpl' => array('nID', 'nIDFirm', 'nIDOffice', 'sPatruls'),
    'set_setup_request.tpl' => array('nID', 'nForRead', 'nIDElement', 'nIDOffice', 'nIDStoragehouse', 'sMOL', 'nIDToStoragehouse', 'sComment'),
    'set_setup_tech_price.tpl' => array('nID', 'nBasePrice', 'nFactor', 'sPriceListDate', 'editPriceListDate'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    operationalAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    operationalAssert(strpos($source, 'ui-operational-dialog') !== false, $template . ' operational marker is missing');
    operationalAssert(strpos($source, 'ui-nomenclature-actions') !== false, $template . ' action bar marker is missing');
    operationalAssert(strpos($source, 'ui-icon ui-icon-save') !== false, $template . ' save icon is missing');
    operationalAssert(strpos($source, 'ui-icon ui-icon-close') !== false, $template . ' close icon is missing');
    operationalAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    operationalAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');

    foreach ($fields as $field) {
        operationalAssert(
            strpos($source, 'id="' . $field . '"') !== false || strpos($source, 'name="' . $field . '"') !== false,
            $template . ' field changed: ' . $field
        );
    }
}

$assistant = file_get_contents($root . '/templates/set_setup_assistant.tpl');
operationalAssert(strpos($assistant, "select_all_options('selected_offices')") !== false, 'assistant selected offices are no longer submitted');
operationalAssert(strpos($assistant, 'function GenerateBothRegionsAndPersons()') !== false, 'assistant firm/region/person chain changed');
operationalAssert(strpos($assistant, 'ui-nomenclature-transfer') !== false, 'assistant transfer list marker is missing');

$patruls = file_get_contents($root . '/templates/set_setup_patruls.tpl');
operationalAssert(strpos($patruls, "window.opener.document.getElementById('nIDFirm')") !== false, 'patrol parent-filter behaviour changed');
operationalAssert(strpos($patruls, "loadXMLDoc2('getPatruls')") !== false, 'patrol office refresh changed');

$request = file_get_contents($root . '/templates/set_setup_request.tpl');
operationalAssert(strpos($request, 'rpc_excel_panel="off"') !== false, 'request items Excel panel setting changed');
operationalAssert(strpos($request, 'rpc_paging="off"') !== false, 'request items paging setting changed');
operationalAssert(strpos($request, 'function setRequestElement') !== false, 'request item creation changed');
operationalAssert(strpos($request, "loadXMLDoc2( 'refreshStoragehouses' )") !== false, 'request storagehouse refresh changed');
operationalAssert(strpos($request, "loadXMLDoc2( 'refreshMOL' )") !== false, 'request MOL refresh changed');
operationalAssert(strpos($request, "document.getElementById( 'add' ).disabled") !== false, 'request read-only mode changed');

$techPrice = file_get_contents($root . '/templates/set_setup_tech_price.tpl');
operationalAssert(strpos($techPrice, 'click_element_id="editPriceListDate"') !== false, 'technical price calendar binding changed');
operationalAssert(strpos($techPrice, 'ui-icon ui-icon-calendar ui-inline-calendar-trigger') !== false, 'technical price calendar icon is missing');

foreach (array('.ui-operational-dialog', '.ui-tech-prices-summary', '.ui-request-items-toolbar', '.ui-inline-calendar-trigger') as $selector) {
    operationalAssert(strpos($css, $selector) !== false, 'missing operational style ' . $selector);
}

echo 'UI_REFRESH_OPERATIONAL=PASS' . PHP_EOL;
