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
$page = file_get_contents($root . '/templates/page.tpl');

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

$assetRpcTemplates = array(
    'asset_groups.tpl',
    'asset_info.tpl',
    'asset_info_ppp.tpl',
    'asset_info_sub_assets.tpl',
    'asset_search.tpl',
    'assets_attach.tpl',
    'assets_average_stats.tpl',
    'assets_enter.tpl',
    'assets_nomenclatures.tpl',
    'assets_ppp.tpl',
    'assets_settings.tpl',
    'assets_stock_taking.tpl',
    'assets_storagehouses.tpl',
    'assets_totals.tpl',
    'assets_waste.tpl',
    'set_asset_group.tpl',
    'set_asset_info.tpl',
    'set_assets_nomenclatures.tpl',
    'set_assets_settings.tpl',
    'set_assets_storagehouses.tpl',
);

foreach ($assetRpcTemplates as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    assetsAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug flag is missing');
    assetsAssert(strpos($source, 'rpc_html_debug') !== false, $template . ' HTML XML-debug preservation flag is missing');
    assetsAssert(strpos($source, '<img') === false, $template . ' retains a legacy image icon');
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
assetsAssert(strpos($css, '@media (max-width: 900px)') !== false, 'asset detail stacking breakpoint is missing');

$tabs = file_get_contents($root . '/templates/asset_info_tabs.tpl');
foreach (array('asset_info', 'asset_info_ppp', 'asset_info_sub_assets') as $route) {
    assetsAssert(strpos($tabs, $route) !== false, 'asset tab route changed: ' . $route);
}
assetsAssert(strpos($tabs, 'ui-asset-tabs') !== false, 'asset tabs modern marker is missing');
assetsAssert(strpos($tabs, 'parseInt(assetId.value, 10)') !== false, 'asset tab invalid-ID guard is missing');

foreach (array('asset_info_ppp.tpl', 'asset_info_sub_assets.tpl') as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    assetsAssert(strpos($source, 'ui-asset-subview') !== false, $template . ' subview marker is missing');
    assetsAssert(strpos($source, 'ui-asset-subview-result') !== false, $template . ' flexible result marker is missing');
    assetsAssert(strpos($source, '{include file="asset_info_tabs.tpl"}') !== false, $template . ' asset tabs include changed');
    assetsAssert(strpos($source, "loadXMLDoc2('result')") !== false, $template . ' result request changed');
}

$groupEditor = file_get_contents($root . '/templates/set_asset_group.tpl');
foreach (array('id', 'offset', 'name', 'parent_id') as $field) {
    assetsAssert(
        preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '\1/', $groupEditor) === 1,
        'asset group field changed: ' . $field
    );
}
assetsAssert(strpos($groupEditor, 'ui-asset-editor-actions') !== false, 'asset group fixed action bar is missing');
assetsAssert(strpos($groupEditor, "loadXMLDoc2('update', 3)") !== false, 'asset group update request changed');

$periodEditor = file_get_contents($root . '/templates/set_asset_info.tpl');
assetsAssert(strpos($periodEditor, 'name="amort_period"') !== false, 'asset amortization field changed');
assetsAssert(strpos($periodEditor, 'ui-asset-editor-actions') !== false, 'asset amortization fixed action bar is missing');
$callbackPosition = strpos($periodEditor, 'rpc_on_exit = function');
$savePosition = strpos($periodEditor, "loadXMLDoc2('save', 0)");
assetsAssert($callbackPosition !== false && $savePosition !== false && $callbackPosition < $savePosition, 'asset amortization callback must be registered before the save request');
assetsAssert(strpos($periodEditor, 'if (parseInt(nCode, 10)) return;') !== false, 'asset amortization dialog still closes after an RPC error');

$groupApi = file_get_contents($root . '/api/api_set_asset_group.php');
$periodApi = file_get_contents($root . '/api/api_set_asset_info.php');
$childrenApi = file_get_contents($root . '/api/api_asset_info_sub_assets.php');
assetsAssert(preg_match('/function\s+update\s*\(\s*DBResponse\s+\$oResponse\s*\)/', $groupApi) === 1, 'asset group update response signature is incompatible');
assetsAssert(strpos($groupApi, '$oResponse->printResponse();') !== false, 'asset group update does not preserve XML response output');
assetsAssert(strpos($periodApi, '$oResponse->printResponse();') !== false, 'asset amortization save does not preserve XML response output');
assetsAssert(strpos($childrenApi, '(array) $oAsset->getSubAssetsIDs($nID)') !== false, 'sub-assets result is not normalized for PHP 8.5');

$dialogsSource = file_get_contents($root . '/js/common_dialogs.js');
assetsAssert(strpos($dialogsSource, "dialog_win('asset_info&id='+id,1000,700,1,'asset_info')") !== false, 'asset information dialog is still too short');
assetsAssert(strpos($dialogsSource, "dialog_win('set_asset_info&nID='+id, 520, 360, 1, 'set_asset_info')") !== false, 'asset period dialog size is stale');
assetsAssert(strpos($dialogsSource, "dialog_win('set_asset_group&id='+id,560,390,1,'set_asset_group')") !== false, 'asset group dialog size is stale');

foreach (array('.ui-asset-tabs', '.ui-asset-subview', '.ui-asset-subview-result', '.ui-asset-editor', '.ui-asset-editor-actions') as $selector) {
    assetsAssert(strpos($css, $selector) !== false, 'missing modern asset detail style ' . $selector);
}

assetsAssert(strpos($page, 'css/ui-refresh-nomenclatures.css?version=43') !== false, 'asset stylesheet cache version is stale');

echo 'UI_REFRESH_ASSETS=PASS' . PHP_EOL;
