<?php

function organizationAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_ORGANIZATION=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-nomenclatures.css');
$icons = file_get_contents($root . '/css/ui-fa7-icons.css');

$lists = array(
    'setup_firms.tpl' => array('newFirm', 'viewFirm', 'deleteFirm', "loadXMLDoc('result')"),
    'setup_regions.tpl' => array('newRegion', 'viewRegion', 'deleteRegion', "loadXMLDoc( 'generate', 1 )"),
    'setup_positions.tpl' => array('newPosition', 'viewPosition', 'deletePosition', "loadXMLDoc('result')"),
    'setup_positions_nc.tpl' => array('editSetupPositionsNC', 'viewPositionsNC', 'deleteSetupPositionsNC', "loadXMLDoc2('result')"),
    'setup_pay_desks.tpl' => array('openPayDesk', 'deletePayDesk', "loadXMLDoc2( 'loadOffices' )", "loadXMLDoc2( 'loadPersons' )"),
    'setup_cashiers.tpl' => array('openCashier', 'deleteCashier', "loadXMLDoc2( 'result' )"),
    'setup_bank_accounts.tpl' => array('openAccount', 'deleteAccount', "loadXMLDoc2( 'result' )"),
);

foreach ($lists as $template => $behaviours) {
    $source = file_get_contents($root . '/templates/' . $template);
    organizationAssert(strpos($source, 'ui-nomenclature-list') !== false, $template . ' list marker is missing');
    organizationAssert(strpos($source, 'ui-nomenclature-heading') !== false, $template . ' heading marker is missing');
    organizationAssert(strpos($source, 'ui-icon ui-icon-plus') !== false, $template . ' add icon is missing');
    organizationAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');
    organizationAssert(strpos($source, 'rpc_debug = true') !== false, $template . ' XML debug was disabled');
    organizationAssert(preg_match('/<div\s+id=(["\'])result\1[^>]*>\s*<\/div>/', $source) === 1, $template . ' result area changed');

    foreach ($behaviours as $behaviour) {
        organizationAssert(strpos($source, $behaviour) !== false, $template . ' behaviour changed: ' . $behaviour);
    }
}

$regions = file_get_contents($root . '/templates/setup_regions.tpl');
organizationAssert(strpos($regions, 'ui-nomenclature-filter-wrap') !== false, 'regions filter marker is missing');
organizationAssert(strpos($regions, 'ui-icon ui-icon-search') !== false, 'regions search icon is missing');

$payDesks = file_get_contents($root . '/templates/setup_pay_desks.tpl');
organizationAssert(strpos($payDesks, 'ui-nomenclature-filter-wide') !== false, 'pay desks wide filter marker is missing');
organizationAssert(strpos($payDesks, 'name="nIDFirm"') !== false, 'pay desks firm filter changed');
organizationAssert(strpos($payDesks, 'name="nIDOffice"') !== false, 'pay desks office filter changed');
organizationAssert(strpos($payDesks, 'name="nIDPerson"') !== false, 'pay desks person filter changed');
organizationAssert(strpos($payDesks, 'name="sNum"') !== false, 'pay desks number filter changed');

$dialogs = array(
    'set_setup_firms.tpl' => array('id', 'code', 'name', 'mol', 'jur_name', 'address', 'idn', 'idn_dds', 'jur_mol', 'nIDFirmDDS', 'nIDOfficeDDS', 'nIDBankAccountDefault'),
    'set_setup_regions.tpl' => array('id', 'id_f', 'id_firm', 'code', 'name', 'factor_tech_support', 'factor_tech_distance', 'factor_km_over', 'factor_km_below', 'nIsAdmin', 'nIsTech', 'nIsReaction', 'directions_all', 'directions_current'),
    'set_setup_positions.tpl' => array('id', 'code', 'sFunction', 'name'),
    'set_setup_positions_nc.tpl' => array('id', 'cipher', 'name', 'min_salary'),
    'set_setup_pay_desk.tpl' => array('nID', 'sNum', 'persons_all', 'persons_current'),
    'set_setup_cashier.tpl' => array('nID', 'nIDFirm', 'nIDOffice', 'nIDPerson', 'nomenclatures_all', 'nomenclatures_current', 'account_opperate_all', 'account_opperate_current', 'account_watch_all', 'account_watch_current'),
    'set_setup_bank_account.tpl' => array('nID', 'cash', 'sNameAccount', 'sNameBank', 'sIBAN', 'sBIC', 'firms_all', 'firms_current', 'bank'),
);

foreach ($dialogs as $template => $fields) {
    $source = file_get_contents($root . '/templates/' . $template);
    organizationAssert(strpos($source, 'ui-nomenclature-dialog') !== false, $template . ' dialog marker is missing');
    organizationAssert(strpos($source, 'ui-organization-dialog') !== false, $template . ' organization marker is missing');
    organizationAssert(strpos($source, 'ui-nomenclature-actions') !== false, $template . ' action bar marker is missing');
    organizationAssert(strpos($source, 'ui-icon ui-icon-save') !== false, $template . ' save icon is missing');
    organizationAssert(strpos($source, 'ui-icon ui-icon-close') !== false, $template . ' close icon is missing');
    organizationAssert(strpos($source, '<img') === false, $template . ' retains a legacy image');

    foreach ($fields as $field) {
        organizationAssert(
            strpos($source, 'id="' . $field . '"') !== false || strpos($source, 'name="' . $field . '"') !== false,
            $template . ' field changed: ' . $field
        );
    }
}

$regionDialog = file_get_contents($root . '/templates/set_setup_regions.tpl');
organizationAssert(strpos($regionDialog, "select_all_options( 'directions_current' )") !== false, 'region selected directions are no longer submitted');
organizationAssert(substr_count($regionDialog, 'ui-nomenclature-transfer-button') === 2, 'region transfer controls changed');

$payDeskDialog = file_get_contents($root . '/templates/set_setup_pay_desk.tpl');
organizationAssert(strpos($payDeskDialog, "select_all_options( 'persons_current' )") !== false, 'pay desk selected persons are no longer submitted');

$cashierDialog = file_get_contents($root . '/templates/set_setup_cashier.tpl');
foreach (array('nomenclatures_current', 'account_opperate_current', 'account_watch_current') as $selection) {
    organizationAssert(strpos($cashierDialog, "select_all_options( '" . $selection . "' )") !== false, 'cashier selection changed: ' . $selection);
}
organizationAssert(strpos($cashierDialog, 'ui-icon ui-icon-down') !== false, 'cashier down icon is missing');
organizationAssert(strpos($cashierDialog, 'ui-icon ui-icon-up') !== false, 'cashier up icon is missing');

$accountDialog = file_get_contents($root . '/templates/set_setup_bank_account.tpl');
organizationAssert(strpos($accountDialog, 'function changeType()') !== false, 'bank/cash account switching changed');
organizationAssert(strpos($accountDialog, 'self.resizeBy(0, -270)') !== false, 'cash account resize behaviour changed');
organizationAssert(strpos($accountDialog, "select_all_options( 'firms_current' )") !== false, 'bank account selected firms are no longer submitted');

foreach (array('.ui-organization-dialog', '.ui-nomenclature-transfer', '.ui-nomenclature-transfer-button', '.ui-nomenclature-filter-wide') as $selector) {
    organizationAssert(strpos($css, $selector) !== false, 'missing organization style ' . $selector);
}

organizationAssert(strpos($icons, '.ui-icon-up') !== false, 'up icon class is missing');
organizationAssert(strpos($icons, '.ui-icon-down') !== false, 'down icon class is missing');
organizationAssert(is_file($root . '/css/fa7/regular/chevron-up.svg'), 'up icon asset is missing');
organizationAssert(is_file($root . '/css/fa7/regular/chevron-down.svg'), 'down icon asset is missing');

echo 'UI_REFRESH_ORGANIZATION=PASS' . PHP_EOL;
