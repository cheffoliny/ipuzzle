<?php

function assetsAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_ASSETS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');

$lists = array(
    'asset_groups.tpl' => array('modifyGroup', 'deleteGroup', "loadXMLDoc2('result')"),
    'assets_nomenclatures.tpl' => array('editNomenclatures', 'delAssetsNomenclatures', "loadXMLDoc2('result')"),
    'assets_storagehouses.tpl' => array('editStorageHouse', 'delAssetsStoragehouse', "loadXMLDoc2('result')"),
    'assets_settings.tpl' => array('editAssetsSettings', 'deleteAssetsSettings', "loadXMLDoc2( 'result' )"),
    'storagehouses.tpl' => array('editStoragehouse', 'delStoragehouse', 'Offices', 'formSubmit'),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    assetsAssert(strpos($source, 'ui-assets-list') !== false, $template . ' asset list marker is missing');
    assetsAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    assetsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    assetsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug is missing');
    assetsAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        assetsAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

$dialogs = array(
    'set_assets_nomenclatures.tpl' => array('nID', 'sName', 'nIDGroup', 'all_attributes', 'account_attributes'),
    'set_assets_storagehouses.tpl' => array('nID', 'sName', 'nIDFirm', 'nIDOffice', 'nIDPerson'),
    'set_assets_settings.tpl' => array('nID', 'sAssetEarningCoef', 'sAssetOwnCoef'),
    'set_storagehouses.tpl' => array('nID', 'sName', 'nIDFirm', 'nIDOffice', 'nIDPerson', 'sType', 'nIDCity', 'nIDArea', 'sStreet', 'sNumber', 'sOther'),
    'set_storagehouses_mols.tpl' => array('nID', 'sPersonList', 'nIDFirm', 'nIDOffice', 'all_persons', 'sel_persons'),
    'asset_info.tpl' => array('nID', 'id_group', 'id_nomenclature', 'invoice_date', 'amort_period'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    assetsAssert(strpos($source, 'ui-asset-dialog') !== false, $template . ' asset dialog marker is missing');
    assetsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    assetsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug is missing');

    foreach ($fields as $field) {
        assetsAssert(
            preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '\1/', $source) === 1,
            $template . ' field changed: ' . $field
        );
    }
}

$nomenclature = file_get_contents($root . '/templates/set_assets_nomenclatures.tpl');
assetsAssert(strpos($nomenclature, "select_all_options('account_attributes')") !== false, 'asset attribute selection changed');
assetsAssert(strpos($nomenclature, "loadXMLDoc2('save',3)") !== false, 'asset nomenclature save request changed');
assetsAssert(substr_count($nomenclature, 'ui-nomenclature-transfer-button') === 2, 'asset attribute transfer actions changed');

$storagehouse = file_get_contents($root . '/templates/set_storagehouses.tpl');
assetsAssert(strpos($storagehouse, "window.opener.document.getElementById('nIDFirm').value") !== false, 'storagehouse parent context changed');
assetsAssert(strpos($storagehouse, "loadXMLDoc2('save',3)") !== false, 'storagehouse parent save mode changed');
assetsAssert(strpos($storagehouse, "loadXMLDoc2('save',2)") !== false, 'storagehouse standalone save mode changed');

$mols = file_get_contents($root . '/templates/set_storagehouses_mols.tpl');
assetsAssert(strpos($mols, 'function processPerson') !== false, 'storagehouse MOL transfer changed');
assetsAssert(strpos($mols, "loadXMLDoc2( 'save' )") !== false, 'storagehouse MOL save request changed');

$assetInfo = file_get_contents($root . '/templates/asset_info.tpl');
assetsAssert(strpos($assetInfo, 'click_element_id="img_date_from" input_element_id="invoice_date"') !== false, 'asset invoice calendar binding changed');
assetsAssert(strpos($assetInfo, 'id="img_date_from"') !== false, 'asset invoice calendar trigger is missing');
assetsAssert(strpos($assetInfo, "loadXMLDoc2('updateAssetInfo',2)") !== false, 'asset update request changed');
assetsAssert(strpos($assetInfo, "loadXMLDoc2('setAssetInfo')") !== false, 'asset load request changed');

$reports = array(
    'assets_attach.tpl' => array("dialogAssetsPPP(id,'attach')", 'editFromDate', 'editToDate'),
    'assets_enter.tpl' => array("dialogAssetsPPP(id,'enter')", 'editFromDate', 'editToDate'),
    'assets_waste.tpl' => array("dialogAssetsPPP(id,'waste')", 'editFromDate', 'editToDate'),
    'assets_average_stats.tpl' => array('nGroup', "loadXMLDoc2( 'result' )"),
    'assets_stock_taking.tpl' => array('switchStatus', 'gotoNomenclatures', 'gotoAssets'),
    'assets_totals.tpl' => array('formSearch', 'srch_period', 'srch_firm'),
);

foreach ($reports as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    assetsAssert(strpos($source, 'ui-assets-report') !== false, $template . ' report marker is missing');
    assetsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    assetsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug is missing');

    foreach ($behaviours as $behaviour) {
        assetsAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

$search = file_get_contents($root . '/templates/asset_search.tpl');
assetsAssert(strpos($search, 'ui-asset-search-dialog') !== false, 'asset search dialog marker is missing');
assetsAssert(strpos($search, 'rpc_excel_panel="off"') !== false, 'asset search Excel panel setting changed');
assetsAssert(strpos($search, 'rpc_autonumber="off"') !== false, 'asset search autonumber setting changed');
assetsAssert(strpos($search, 'rpc_resize="off"') !== false, 'asset search resize setting changed');
assetsAssert(strpos($search, 'function transfer') !== false, 'asset transfer behaviour changed');
assetsAssert(strpos($search, '<img') === false, 'asset search retains a legacy image');

$ppp = file_get_contents($root . '/templates/assets_ppp.tpl');
assetsAssert(strpos($ppp, 'ui-assets-ppp-dialog') !== false, 'PPP dialog marker is missing');
assetsAssert(strpos($ppp, 'rpc_excel_panel="off"') !== false, 'PPP Excel panel setting changed');
assetsAssert(strpos($ppp, 'rpc_resize="off"') !== false, 'PPP resize setting changed');
assetsAssert(strpos($ppp, "loadXMLDoc2('save',3)") !== false, 'PPP save request changed');
assetsAssert(strpos($ppp, "loadDirect('export_to_pdf')") !== false, 'PPP PDF export changed');

foreach (array('.ui-assets-list', '.ui-storagehouses-filter', '.ui-asset-dialog', '.ui-asset-info-main', '.ui-assets-report', '.ui-assets-stock-filter', '.ui-assets-totals-table', '.ui-asset-search-dialog', '.ui-assets-ppp-toolbar') as $selector) {
    assetsAssert(strpos($css, $selector) !== false, 'missing asset style ' . $selector);
}

echo 'UI_REFRESH_ASSETS=PASS' . PHP_EOL;
