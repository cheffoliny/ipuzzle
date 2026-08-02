<?php

function nomenclatureAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_NOMENCLATURES=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$page = file_get_contents($root . '/templates/page.tpl');
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');

nomenclatureAssert(strpos($page, 'css/ui-refresh-nomenclatures.css?version=45') !== false, 'stylesheet is not loaded');
nomenclatureAssert(strpos($css, 'body.ui-refresh-content .ui-nomenclature-list') !== false, 'list styles are not scoped');
nomenclatureAssert(strpos($css, 'body.ui-refresh-content .ui-nomenclature-dialog') !== false, 'dialog styles are not scoped');

$lists = array(
    'autos.tpl' => array('editAuto', 'delAuto'),
    'auto_marks.tpl' => array('editAutoMark', 'delAutoMark'),
    'auto_models.tpl' => array('editAutoModel', 'delAutoModel'),
    'attributes.tpl' => array('openAttribute', 'deleteAttribute'),
    'object_types.tpl' => array('openType', 'deleteType'),
);

foreach ($lists as $template => $actions) {
    $source = file_get_contents($root . '/templates/' . $template);
    nomenclatureAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    nomenclatureAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    nomenclatureAssert(strpos($source, 'ui-icon ui-icon-plus') !== false, $template . ' add icon was not migrated');
    nomenclatureAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug was disabled');
    nomenclatureAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($actions as $action) {
        nomenclatureAssert(strpos($source, 'function ' . $action) !== false, $template . ' action changed: ' . $action);
    }
}

$dialogs = array(
    'set_auto.tpl' => array('nIDMark', 'nIDModel', 'nIDFirm', 'nIDOffice', 'nIDPerson', 'sRegNum'),
    'set_auto_mark.tpl' => array('sName'),
    'set_auto_model.tpl' => array('sName', 'id_mark'),
    'set_attribute.tpl' => array('name', 'type', 'is_required', 'id_measure'),
    'set_object_type.tpl' => array('sName'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    nomenclatureAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    nomenclatureAssert(strpos($source, 'ui-nomenclature-actions') !== false, $template . ' action bar marker is missing');
    nomenclatureAssert(strpos($source, 'ui-icon ui-icon-save') !== false, $template . ' save icon was not migrated');
    nomenclatureAssert(strpos($source, 'ui-icon ui-icon-close') !== false, $template . ' close icon was not migrated');

    foreach ($fields as $field) {
        nomenclatureAssert(
            preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '\1/', $source) === 1,
            $template . ' field changed: ' . $field
        );
    }
}

echo 'UI_REFRESH_NOMENCLATURES=PASS' . PHP_EOL;
