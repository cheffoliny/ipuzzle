<?php

function contractProfilesAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_CONTRACT_PROFILES=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');
$page = file_get_contents($root . '/templates/page.tpl');

$templates = array(
    'contracts.tpl',
    'object_contract.tpl',
    'setup_contracts.tpl',
    'person_contract_print.tpl',
    'personInfo.tpl',
    'person_actives.tpl',
    'set_setup_person_actives.tpl',
    'set_setup_attestation.tpl',
);

foreach ($templates as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    contractProfilesAssert(strpos($source, 'ui-contract-') !== false, $template . ' contract UI marker is missing');
    contractProfilesAssert(strpos($source, '<i class=') === false, $template . ' retains a legacy icon element');
    contractProfilesAssert(strpos($source, 'class="fa') === false, $template . ' retains a legacy FontAwesome class');
    contractProfilesAssert(strpos($source, 'rpc_debug') !== false, $template . ' XML debug declaration is missing');
    contractProfilesAssert(strpos($source, 'rpc_debug = true') !== false || strpos($source, 'rpc_debug=true') !== false, $template . ' XML debug is not active');

    if ($template === 'personInfo.tpl') {
        contractProfilesAssert(substr_count($source, '<img') === 1, 'personInfo must retain only the employee photo');
        contractProfilesAssert(strpos($source, '<img src="{$image}"') !== false, 'employee photo source changed');
        contractProfilesAssert(strpos($source, 'ui-person-photo') !== false, 'employee photo marker is missing');
    } else {
        contractProfilesAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    }
}

$resultSettings = array(
    'object_contract.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
    'person_actives.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
    'set_setup_person_actives.tpl' => array('rpc_excel_panel="off"', 'rpc_paging="off"', 'rpc_resize="off"'),
);

foreach ($resultSettings as $template => $settings) {
    $source = file_get_contents($root . '/templates/' . $template);
    contractProfilesAssert(strpos($source, 'id="result"') !== false, $template . ' result area is missing');
    foreach ($settings as $setting) {
        contractProfilesAssert(strpos($source, $setting) !== false, $template . ' changed result setting ' . $setting);
    }
}

$contracts = file_get_contents($root . '/templates/contracts.tpl');
foreach (array("loadXMLDoc2( 'load')", "loadXMLDoc2('result',1)", "loadXMLDoc('ignoreContract',1)", "loadDirect('export_to_pdf')") as $behaviour) {
    contractProfilesAssert(strpos($contracts, $behaviour) !== false, 'electronic contracts behaviour changed: ' . $behaviour);
}
foreach (array('img_date_from' => 'date_from', 'img_date_to' => 'date_to') as $trigger => $input) {
    contractProfilesAssert(strpos($contracts, 'click_element_id="' . $trigger . '"') !== false, 'contracts calendar binding changed: ' . $trigger);
    contractProfilesAssert(strpos($contracts, 'input_element_id="' . $input . '"') !== false, 'contracts calendar input changed: ' . $input);
    contractProfilesAssert(strpos($contracts, 'id="' . $trigger . '"') !== false, 'contracts calendar trigger is missing: ' . $trigger);
}

$objectContract = file_get_contents($root . '/templates/object_contract.tpl');
contractProfilesAssert(strpos($objectContract, "loadXMLDoc2('save')") !== false, 'object contract save action changed');
contractProfilesAssert(strpos($objectContract, "loadXMLDoc2('result')") !== false, 'object contract result action changed');
contractProfilesAssert(substr_count($objectContract, 'id="tech_info"') === 1, 'object contract retains duplicate tech_info fields');
contractProfilesAssert(strpos($objectContract, 'id="b100" class=') === false, 'object contract close button retains duplicate class attributes');
contractProfilesAssert(strpos($objectContract, 'ui-object-contract-result-shell') !== false, 'object contract result shell is missing');
contractProfilesAssert(substr_count($objectContract, 'ui-object-contract-detail-wide') === 2, 'object contract wide detail fields changed');
contractProfilesAssert(strpos($objectContract, 'input-group-addon-ok') === false, 'object contract retains the detached legacy icon wrapper');
contractProfilesAssert(strpos($objectContract, 'style="width:320px;"') === false, 'object contract retains the narrow inline detail width');

$personInfo = file_get_contents($root . '/templates/personInfo.tpl');
foreach (array("loadXMLDoc( 'save', 0 )", 'dialogUpload( id )', 'dialogPrintContract( 0, nID )', 'dialogPrintContract( 1, nID )', 'dialogPrintContract( 2, nID )') as $behaviour) {
    contractProfilesAssert(strpos($personInfo, $behaviour) !== false, 'person profile behaviour changed: ' . $behaviour);
}
contractProfilesAssert(strpos($personInfo, 'click_element_id="img_lk_date"') !== false, 'identity-card calendar binding changed');
contractProfilesAssert(strpos($personInfo, 'input_element_id="lk_date"') !== false, 'identity-card calendar input changed');
contractProfilesAssert(strpos($personInfo, 'id="img_lk_date"') !== false, 'identity-card calendar trigger is missing');

$print = file_get_contents($root . '/templates/person_contract_print.tpl');
contractProfilesAssert(strpos($print, "loadDirect( 'export_to_pdf' )") !== false, 'contract PDF export action changed');
contractProfilesAssert(strpos($print, 'function confirmPrint()') !== false, 'contract print validation is missing');
contractProfilesAssert(substr_count($print, 'ui-contract-print-form') === 3, 'contract print form variants changed');
contractProfilesAssert(substr_count($print, 'ui-contract-print-button') === 3, 'contract print actions changed');
foreach (array('img_sDate' => 3, 'img_sStartDate' => 3, 'img_sToday' => 2) as $trigger => $count) {
    contractProfilesAssert(substr_count($print, 'click_element_id="' . $trigger . '"') === $count, 'contract print calendar binding count changed: ' . $trigger);
    contractProfilesAssert(substr_count($print, '<button type="button" id="' . $trigger . '"') === $count, 'contract print calendar trigger count changed: ' . $trigger);
}

$personAssets = file_get_contents($root . '/templates/person_actives.tpl');
contractProfilesAssert(strpos($personAssets, "dialogAssetsPPP( '0', 'attach' )") !== false, 'person asset assignment changed');
contractProfilesAssert(strpos($personAssets, "loadXMLDoc2( 'result' )") !== false, 'person assets result action changed');

$attestation = file_get_contents($root . '/templates/set_setup_attestation.tpl');
contractProfilesAssert(strpos($attestation, 'id="form1"') !== false, 'attestation form id is missing');
contractProfilesAssert(strpos($attestation, "return loadXMLDoc( 'save', 2 )") !== false, 'attestation save action changed');
$commonDialogs = file_get_contents($root . '/js/common_dialogs.js');
contractProfilesAssert(strpos($commonDialogs, "dialog_win('set_setup_attestation&id='+id+'&person='+person,500,380,1,'set_setup_attestation')") !== false, 'attestation dialog height is too small for the refreshed form');
foreach (array('img_date_start' => 'date_start', 'img_date_end' => 'date_end') as $trigger => $input) {
    contractProfilesAssert(strpos($attestation, 'click_element_id="' . $trigger . '"') !== false, 'attestation calendar binding changed: ' . $trigger);
    contractProfilesAssert(strpos($attestation, 'input_element_id="' . $input . '"') !== false, 'attestation calendar input changed: ' . $input);
    contractProfilesAssert(strpos($attestation, 'id="' . $trigger . '"') !== false, 'attestation calendar trigger is missing: ' . $trigger);
}

foreach (array(
    '.ui-contract-list',
    '.ui-contract-dialog',
    '.ui-contract-filter',
    '.ui-contract-result',
    '.ui-object-contract-details',
    '.ui-person-photo',
    '.ui-contract-print-content',
    '.ui-contract-print-button',
) as $selector) {
    contractProfilesAssert(strpos($css, $selector) !== false, 'missing contract/profile style ' . $selector);
}
contractProfilesAssert(preg_match('/\.ui-object-contract \.fixed-bottom,[^{]+\{[^}]*position:\s*fixed/s', $css) === 1, 'contract/profile action bars are not fixed to the viewport bottom');
contractProfilesAssert(preg_match('/\.ui-object-contract-details\s*\{[^}]*display:\s*grid/s', $css) === 1, 'object contract details are not responsive grid content');
contractProfilesAssert(preg_match('/\.ui-object-contract-result-shell\s*\{[^}]*overflow:\s*hidden\s*!important/s', $css) === 1, 'object contract result shell still creates a second scrollbar');
contractProfilesAssert(preg_match('/\.ui-object-contract-result-shell > \.ui-contract-result\s*\{[^}]*max-height:\s*none\s*!important/s', $css) === 1, 'legacy result max-height still collapses object contract');
contractProfilesAssert(strpos($css, '.ui-object-contract #result_data.body-content') !== false, 'object contract RPC result layout override is missing');

$iconAssets = array(
    'ui-icon-contract' => 'file-signature.svg',
    'ui-icon-barcode' => 'barcode.svg',
    'ui-icon-location' => 'location-dot.svg',
    'ui-icon-phone' => 'phone.svg',
    'ui-icon-mobile' => 'mobile-screen.svg',
    'ui-icon-id-card' => 'id-card.svg',
    'ui-icon-card' => 'credit-card.svg',
    'ui-icon-certificate' => 'certificate.svg',
);

foreach ($iconAssets as $iconClass => $asset) {
    contractProfilesAssert(strpos($icons, '.' . $iconClass) !== false, 'icon mapping is missing: ' . $iconClass);
    contractProfilesAssert(strpos($icons, $asset) !== false, 'icon asset mapping changed: ' . $asset);
    contractProfilesAssert(is_file($root . '/css/fa7/solid/' . $asset), 'icon asset is missing: ' . $asset);
}

contractProfilesAssert(strpos($page, 'css/ui-fa7-icons.css?version=17') !== false, 'FA7 icon cache version is stale');
contractProfilesAssert(strpos($page, 'css/ui-refresh-nomenclatures.css?version=43') !== false, 'contract/profile stylesheet cache version is stale');

echo 'UI_REFRESH_CONTRACT_PROFILES=PASS' . PHP_EOL;
