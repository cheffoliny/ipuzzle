<?php

chdir(dirname(__DIR__));
require_once 'include/smarty/Smarty.class.php';

class SmartyPhp85CompileSmoke extends Smarty
{
    function compileTemplate($template)
    {
        $source = file_get_contents($this->template_dir . DIRECTORY_SEPARATOR . $template);
        $compiled = '';

        if ($source === false || !$this->_compile_source($template, $source, $compiled)) {
            throw new RuntimeException('Unable to compile ' . $template);
        }

        return $compiled;
    }

    function executeCompiled($compiled)
    {
        ob_start();
        try {
            eval('?>' . $compiled);
            return ob_get_clean();
        } catch (Throwable $error) {
            ob_end_clean();
            throw $error;
        }
    }
}

$tabTemplates = array(
    'asset_info_tabs.tpl',
    'asset_info_ppp.tpl',
    'asset_info_sub_assets.tpl',
    'set_asset_group.tpl',
    'set_asset_info.tpl',
    'buy_doc_tabs.tpl',
    'client_tabs.tpl',
    'finance_instruments_tabs.tpl',
    'finance_operations_tabs.tpl',
    'limit_card_tabs.tpl',
    'object_tabs.tpl',
    'object_messages.tpl',
    'object_sectors.tpl',
    'object_zones.tpl',
    'order_tabs.tpl',
    'person_tabs.tpl',
    'personnel_tabs.tpl',
    'personal_card.tpl',
    'personal_card_limit_card.tpl',
    'personal_card_operations.tpl',
    'personal_card_ppp.tpl',
    'personal_card_tabs.tpl',
    'personal_card_tabs2.tpl',
    'person_contract.tpl',
    'person_data.tpl',
    'patrul_parking.tpl',
    'patruls_movement.tpl',
    'setup_patruls.tpl',
    'set_setup_patrul_parking.tpl',
    'set_setup_patruls.tpl',
    'set_setup_patrol.tpl',
    'working_card_patrol.tpl',
    'setup_person_leave.tpl',
    'set_limit_card_persons.tpl',
    'set_setup_person_salary_earning.tpl',
    'set_setup_person_shifts.tpl',
    'sale_doc_tabs.tpl',
    'set_storagehouses_tabs.tpl',
    'states_filter_tabs.tpl',
    'set_setup_signalMessage.tpl',
    'set_setup_object_sector.tpl',
    'set_setup_object_zone.tpl',
    'working_card_tabs.tpl'
);

$smarty = new SmartyPhp85CompileSmoke();
$smarty->assign('page', 'client_info');
$smarty->assign('client', 'PHP 8.5 smoke');
$smarty->assign('nID', 1);
$smarty->assign('cnt', 0);
$smarty->assign('isSOD', 0);
$smarty->assign('isFO', 0);
$smarty->assign('view', array());
$smarty->assign('edit', array());
$smarty->assign('tabs', array());

foreach ($tabTemplates as $template) {
    $compiled = $smarty->compileTemplate($template);
    preg_match_all('/<\?php(.*?)\?>/s', $compiled, $phpBlocks);

    foreach ($phpBlocks[1] as $phpBlock) {
        if (preg_match('/(?:==|!=)\s+(?!true\b|false\b|null\b)([A-Za-z_][A-Za-z0-9_]*)\b/i', $phpBlock, $match)) {
            fwrite(STDERR, $template . ' compiled an unquoted constant: ' . $match[1] . PHP_EOL);
            exit(1);
        }
    }

    if ($template === 'client_tabs.tpl') {
        $smarty->executeCompiled($compiled);
    }
}

$objectInfo = $smarty->compileTemplate('object_info.tpl');
$objectGeo = $smarty->compileTemplate('object_geo.tpl');

$smarty->assign('page', 'object_geo');
$smarty->assign('nID', 690);
$smarty->assign('mapCenter', array('lat' => 42.7339, 'lng' => 25.4858, 'zoom' => 14));
$smarty->assign('pov', '{}');
$smarty->assign('object', 'PHP 8.5 geo smoke');
$smarty->assign('num', 690);
$smarty->assign('mobile', 0);
$smarty->assign('isService', 0);
$smarty->assign('bEditStatuses', 0);
$objectGeoOutput = $smarty->executeCompiled($objectGeo);

if (
    strpos($objectGeoOutput, 'id="map_canvas"') === false ||
    strpos($objectGeoOutput, 'initialize(') === false ||
    strpos($objectGeoOutput, '42.7339') === false
) {
    fwrite(STDERR, "object_geo.tpl did not render its Leaflet map contract.\n");
    exit(1);
}

$objectLayoutTemplates = array(
    'object_contract.tpl',
    'object_duty.tpl',
    'object_personnel_schedule.tpl',
    'object_store.tpl',
    'object_store_ppp.tpl',
    'object_store_state.tpl',
    'object_to_contract.tpl',
    'object_to_limit_card.tpl',
);

foreach ($objectLayoutTemplates as $objectLayoutTemplate) {
    $smarty->compileTemplate($objectLayoutTemplate);
}

if (
    strpos($objectInfo, "\$this->_tpl_vars['isSOD']['checked']") !== false ||
    strpos($objectInfo, "\$this->_tpl_vars['isFO']['checked']") !== false
) {
    fwrite(STDERR, "object_info.tpl still treats isSOD/isFO as arrays.\n");
    exit(1);
}

if (
    strpos($objectInfo, "if (\$this->_tpl_vars['isSOD']):") === false ||
    strpos($objectInfo, "if (\$this->_tpl_vars['isFO']):") === false
) {
    fwrite(STDERR, "object_info.tpl did not compile scalar status checks.\n");
    exit(1);
}

require_once 'include/smarty/plugins/modifier.date_format.php';
$dateTimestamp = mktime(15, 4, 5, 7, 19, 2026);

if (
    smarty_modifier_date_format($dateTimestamp, '%d.%m.%Y') !== '19.07.2026' ||
    smarty_modifier_date_format($dateTimestamp, '%m.%Y') !== '07.2026'
) {
    fwrite(STDERR, "Smarty date_format compatibility failed.\n");
    exit(1);
}

if (in_array('--all', $argv, true)) {
    $allTemplates = glob('templates/*.tpl');

    foreach ($allTemplates as $templatePath) {
        $smarty->compileTemplate(basename($templatePath));
    }

    echo 'SMARTY_ALL_TEMPLATES_COMPILE=PASS (' . count($allTemplates) . " templates)\n";
}

if (in_array('--write-cache', $argv, true)) {
    $cacheTargets = array(
        'client_tabs.tpl' => 'templates_c/%%CD^CD7^CD7F4FDD%%client_tabs.tpl.php',
        'object_info.tpl' => 'templates_c/%%1A^1A3^1A38FEF6%%object_info.tpl.php'
    );

    foreach ($cacheTargets as $template => $compilePath) {
        if (!$smarty->_compile_resource($template, $compilePath)) {
            fwrite(STDERR, 'Unable to refresh ' . $compilePath . PHP_EOL);
            exit(1);
        }
    }

    echo "SMARTY_CACHE_RECOMPILE=PASS\n";
}

echo 'SMARTY_PHP85_COMPILE=PASS (' . (count($tabTemplates) + count($objectLayoutTemplates) + 2) . " templates)\n";
