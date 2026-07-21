<?php

function schemesAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_SCHEMES=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');

$lists = array(
    'setup_nomenclature_types.tpl' => array('openNomenclatureType', 'deleteNomenclatureType', "loadXMLDoc2( 'result' )"),
    'schemes.tpl' => array('editScheme', 'deleteScheme', "loadXMLDoc2( 'result' )"),
    'tech_operations_setup.tpl' => array('editOperation', 'deleteOperation', 'operations_scheme', "loadXMLDoc2( 'result' )"),
    'tech_operations_scheme.tpl' => array('getResult', "loadXMLDoc2( 'result' )"),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    schemesAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    schemesAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    schemesAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    schemesAssert(strpos($source, '<i class="fa') === false, $template . ' retains a legacy font icon');
    schemesAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');
    schemesAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        schemesAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

foreach (array('setup_nomenclature_types.tpl', 'schemes.tpl', 'tech_operations_setup.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    schemesAssert(strpos($source, 'ui-icon ui-icon-plus') !== false, $template . ' add icon is missing');
}

foreach (array('tech_operations_setup.tpl', 'tech_operations_scheme.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    schemesAssert(strpos($source, 'rpc_excel_panel="off"') !== false, $template . ' Excel panel setting changed');
    schemesAssert(strpos($source, 'rpc_paging="off"') !== false, $template . ' paging setting changed');
}

$layout = file_get_contents($root . '/templates/tech_operations.tpl');
schemesAssert(strpos($layout, 'ui-tech-operations-layout') !== false, 'technical operations layout marker is missing');
schemesAssert(strpos($layout, 'id="operations_scheme"') !== false, 'technical operations scheme frame changed');
schemesAssert(strpos($layout, "page.php?page=tech_operations_scheme") !== false, 'technical operations scheme source changed');
schemesAssert(strpos($layout, 'id="operations_setup"') !== false, 'technical operations setup frame changed');
schemesAssert(strpos($layout, "page.php?page=tech_operations_setup") !== false, 'technical operations setup source changed');

$dialogs = array(
    'set_setup_nomenclature_type.tpl' => array('nID', 'sName', 'sParent', 'nIsCtrl'),
    'set_scheme.tpl' => array('nID', 'id', 'fromList', 'toList', 'name', 'nomenclatures_all', 'nomenclatures_current', 'nDefault', 'nIDDetector'),
    'set_tech_operation.tpl' => array('nID', 'id', 'fromList', 'toList', 'sName', 'nPrice', 'nToContract', 'nToArrange', 'nCableOperation', 'nomenclatures_all', 'nomenclatures_current'),
    'setup_firms_object_statuses.tpl' => array('nID', 'nIDFirm', 'statuses_all', 'statuses_current'),
    'movement_scheme.tpl' => array('nID', 'name', 'office', 'start_time', 'end_time', 'reason_time', 'stay_time', 'note', 'def'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    schemesAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    schemesAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    schemesAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug was disabled');

    foreach ($fields as $field) {
        schemesAssert(
            strpos($source, 'id="' . $field . '"') !== false || strpos($source, 'name="' . $field . '"') !== false,
            $template . ' field changed: ' . $field
        );
    }
}

$typeDialog = file_get_contents($root . '/templates/set_setup_nomenclature_type.tpl');
schemesAssert(strpos($typeDialog, "loadXMLDoc2( 'load' )") !== false, 'nomenclature type loading changed');
schemesAssert(strpos($typeDialog, "loadXMLDoc2('save',3)") !== false, 'nomenclature type saving changed');
schemesAssert(strpos($typeDialog, 'onchange="onChangeIsCtrl()"') !== false, 'nomenclature type parent behaviour changed');
schemesAssert(strpos($typeDialog, 'ui-icon ui-icon-tag') !== false, 'nomenclature type icon is missing');
schemesAssert(strpos($typeDialog, 'ui-icon ui-icon-save') !== false, 'nomenclature type save icon is missing');
schemesAssert(strpos($typeDialog, 'ui-icon ui-icon-close') !== false, 'nomenclature type close icon is missing');

$schemeDialog = file_get_contents($root . '/templates/set_scheme.tpl');
schemesAssert(strpos($schemeDialog, "select_all_options( 'nomenclatures_current' )") !== false, 'scheme selected nomenclatures are no longer submitted');
schemesAssert(strpos($schemeDialog, "loadXMLDoc2( 'refreshDetectors' )") !== false, 'scheme detector refresh changed');
schemesAssert(strpos($schemeDialog, 'function copy_option_to(') !== false, 'scheme copy logic changed');
schemesAssert(strpos($schemeDialog, 'function remove_selected(') !== false, 'scheme removal logic changed');
schemesAssert(strpos($schemeDialog, 'function changeDefault()') !== false, 'scheme default detector logic changed');

$techDialog = file_get_contents($root . '/templates/set_tech_operation.tpl');
schemesAssert(strpos($techDialog, "select_all_options( 'nomenclatures_current' )") !== false, 'technical operation selected nomenclatures are no longer submitted');
schemesAssert(strpos($techDialog, "opener.parent.document.getElementById('operations_scheme')") !== false, 'technical scheme parent refresh changed');
schemesAssert(substr_count($techDialog, 'move_option_to(') === 4, 'technical operation transfer controls changed');

$statusDialog = file_get_contents($root . '/templates/setup_firms_object_statuses.tpl');
schemesAssert(strpos($statusDialog, "select_all_options( 'statuses_current' )") !== false, 'firm status selections are no longer submitted');
schemesAssert(strpos($statusDialog, "loadXMLDoc2( 'save' )") !== false, 'firm status saving changed');
schemesAssert(strpos($statusDialog, 'function clearAll(') !== false, 'firm status reset logic changed');
schemesAssert(substr_count($statusDialog, 'move_option_to(') === 4, 'firm status transfer controls changed');

$movement = file_get_contents($root . '/templates/movement_scheme.tpl');
schemesAssert(strpos($movement, "loadXMLDoc2( 'load')") !== false, 'movement scheme loading changed');
schemesAssert(strpos($movement, "loadXMLDoc2('save',5)") !== false, 'movement scheme saving changed');
schemesAssert(substr_count($movement, 'ui-nomenclature-checkbox') === 7, 'movement scheme checkbox set changed');
schemesAssert(strpos($movement, 'ui-icon ui-icon-save') !== false, 'movement scheme save icon is missing');
schemesAssert(strpos($movement, 'ui-icon ui-icon-close') !== false, 'movement scheme close icon is missing');

foreach (array('.ui-configuration-dialog', '.ui-nomenclature-type-dialog', '.ui-scheme-dialog', '.ui-tech-operation-dialog', '.ui-status-mapping-dialog', '.ui-tech-operations-layout', '.ui-movement-scheme-dialog') as $selector) {
    schemesAssert(strpos($css, $selector) !== false, 'missing scheme style ' . $selector);
}

schemesAssert(strpos($icons, '.ui-icon-tag') !== false, 'tag icon mapping is missing');
schemesAssert(is_file($root . '/css/fa7/regular/tag.svg'), 'tag SVG asset is missing');

echo 'UI_REFRESH_SCHEMES=PASS' . PHP_EOL;
