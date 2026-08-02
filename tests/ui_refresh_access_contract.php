<?php

require_once dirname(__DIR__) . '/scripts/audit_ui_legacy_patterns.php';

function accessUiAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_REFRESH_ACCESS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$page = file_get_contents($root . '/templates/page.tpl');
$index = file_get_contents($root . '/templates/index.tpl');
$css = file_get_contents($root . '/css/ui-refresh-access.css');
$admin = file_get_contents($root . '/templates/admin_access_level.tpl');
$level = file_get_contents($root . '/templates/set_setup_access_level.tpl');
$group = file_get_contents($root . '/templates/set_setup_access_group.tpl');
$profiles = file_get_contents($root . '/templates/setup_access_profiles.tpl');
$accounts = file_get_contents($root . '/templates/admin_setup_access_accounts.tpl');
$rights = file_get_contents($root . '/templates/access_rights.tpl');
$password = file_get_contents($root . '/templates/admin_access_account_pass.tpl');
$currentPassword = file_get_contents($root . '/templates/set_curent_user_password.tpl');
$accountDialog = file_get_contents($root . '/templates/admin_set_setup_access_account.tpl');
$profileDialog = file_get_contents($root . '/templates/set_setup_access_profile.tpl');
$patterns = auditUiLegacyPatterns($root);

accessUiAssert(strpos($page, 'css/ui-refresh-access.css?version=3') !== false, 'access stylesheet is not loaded');
accessUiAssert(strpos($css, 'body.ui-refresh-content .ui-access-list') !== false, 'list styles are not scoped');
accessUiAssert(strpos($css, 'body.ui-refresh-content .ui-access-dialog') !== false, 'dialog styles are not scoped');

foreach (array('ui-access-list', 'ui-access-heading', 'ui-access-filter-table') as $marker) {
    accessUiAssert(strpos($admin, $marker) !== false, 'admin_access_level.tpl misses ' . $marker);
}

foreach (array('level_new', 'level_edit', 'level_delete', 'group_delete', 'group_update') as $action) {
    accessUiAssert(strpos($admin, 'function ' . $action) !== false, 'admin action changed: ' . $action);
}
accessUiAssert(strpos($admin, 'rpc_debug=true') !== false, 'admin XML debug was disabled');
accessUiAssert(strpos($admin, '<div id="result"></div>') !== false, 'admin result area changed');

foreach (array('ui-access-dialog', 'ui-access-form-table', 'ui-access-selection', 'ui-dual-list', 'ui-access-dialog-actions') as $marker) {
    accessUiAssert(strpos($level, $marker) !== false, 'set_setup_access_level.tpl misses ' . $marker);
}

foreach (array('all_files', 'level_files', 'id_group', 'name', 'description') as $field) {
    accessUiAssert(
        preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '(?:\[\])?\1/', $level) === 1,
        'level editor field changed: ' . $field
    );
}
accessUiAssert(strpos($level, "select_all_options('level_files')") !== false, 'selected files are no longer submitted');
accessUiAssert(strpos($level, "loadXMLDoc('update', 3)") !== false, 'level update RPC changed');

accessUiAssert(strpos($group, 'ui-access-group-dialog') !== false, 'access group dialog was not migrated');
accessUiAssert(strpos($profiles, 'ui-access-list') !== false, 'access profiles list was not migrated');
accessUiAssert(strpos($accounts, 'ui-access-list') !== false, 'access accounts list was not migrated');
accessUiAssert(strpos($password, 'ui-access-password-dialog') !== false, 'password dialog was not migrated');
foreach (array(
    'ui-access-dialog',
    'ui-access-password-dialog',
    'ui-current-user-password-dialog',
    'ui-access-form-table',
    'ui-access-dialog-actions',
    'ui-current-password-save',
    'ui-current-password-close',
) as $marker) {
    accessUiAssert(strpos($currentPassword, $marker) !== false, 'current user password dialog misses ' . $marker);
}
accessUiAssert(strpos($accountDialog, 'ui-access-account-dialog') !== false, 'account dialog was not migrated');
accessUiAssert(strpos($profileDialog, 'ui-access-profile-dialog') !== false, 'profile dialog was not migrated');
accessUiAssert(strpos($rights, 'ui-access-rights') !== false, 'access rights report was not migrated');

accessUiAssert(strpos($password, "loadXMLDoc('update', 3)") !== false, 'password update RPC changed');
accessUiAssert(strpos($currentPassword, "loadXMLDoc( 'update', 3 )") !== false, 'current password update RPC changed');
foreach (array('password', 'new_password', 'confirm_password') as $field) {
    accessUiAssert(
        preg_match('/\b(?:id|name)\s*=\s*(["\'])' . preg_quote($field, '/') . '\1/', $currentPassword) === 1,
        'current password field changed: ' . $field
    );
}
accessUiAssert(substr_count($currentPassword, 'id="search"') === 1, 'duplicate search id remains in current password dialog');
accessUiAssert(strpos($currentPassword, 'type="button" class="ui-current-password-close"') !== false, 'close button still submits current password form');
accessUiAssert(strpos($index, "dialog_win('set_curent_user_password',400,300,1,'set_curent_user_password')") !== false, 'current password dialog size is stale');
accessUiAssert(strpos($index, 'ui-icon ui-icon-key') !== false, 'current password menu icon is not migrated');
accessUiAssert(strpos($css, 'flex-wrap: nowrap !important') !== false, 'password input groups may still wrap in Bootstrap/Edge');
accessUiAssert(strpos($css, 'width: calc(100% - 34px) !important') !== false, 'password input width does not reserve the icon slot');
accessUiAssert(strpos($accountDialog, "select_all_options('account_regions')") !== false, 'account regions are no longer submitted');
accessUiAssert(strpos($accountDialog, "loadXMLDoc('save', 3)") !== false, 'account save RPC changed');
accessUiAssert(strpos($profileDialog, "loadXMLDoc('update', 3)") !== false, 'profile update RPC changed');
accessUiAssert(strpos($rights, 'rpc_excel_panel="off"') !== false, 'access rights export panel flag changed');

foreach (array('changeLevel', 'saveLevels', 'processResult', 'clickPerson', 'clickProfile', 'showFilters') as $action) {
    accessUiAssert(strpos($rights, 'function ' . $action) !== false, 'access rights action changed: ' . $action);
}

foreach (array(
    'access_rights.tpl',
    'admin_set_setup_access_account.tpl',
    'set_setup_access_level.tpl',
) as $candidate) {
    accessUiAssert(
        in_array($candidate, $patterns['dual_list_editors'], true),
        'dual-list migration candidate not detected: ' . $candidate
    );
}

echo 'UI_REFRESH_ACCESS=PASS' . PHP_EOL;
