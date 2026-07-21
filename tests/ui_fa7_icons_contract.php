<?php

require_once dirname(__DIR__) . '/scripts/audit_legacy_icons.php';

function fa7Assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'UI_FA7_ICONS=FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$root = dirname(__DIR__);
$page = file_get_contents($root . '/templates/page.tpl');
$css = file_get_contents($root . '/css/ui-fa7-icons.css');
$map = legacyIconMap();

fa7Assert(strpos($page, 'css/ui-fa7-icons.css?version=4') !== false, 'FA7 icon stylesheet is not loaded');
fa7Assert(strpos($css, '-webkit-mask-image: var(--ui-icon-source)') !== false, 'SVG icons are not color-aware masks');

foreach (array_unique(array_values($map)) as $icon) {
    $class = '.ui-icon-' . $icon;
    fa7Assert(strpos($css, $class) !== false, 'missing icon class ' . $class);
}

foreach (array(
    'plus.svg', 'magnifying-glass.svg', 'pen-to-square.svg', 'trash-can.svg',
    'check.svg', 'xmark.svg', 'chevron-left.svg', 'chevron-right.svg',
    'floppy-disk.svg', 'copy.svg', 'filter.svg', 'calendar.svg',
    'file-pdf.svg', 'rotate.svg', 'gear.svg',
) as $asset) {
    fa7Assert(is_file($root . '/css/fa7/regular/' . $asset), 'missing FA7 asset ' . $asset);
}

$accessTemplates = array(
    'access_rights.tpl',
    'admin_access_account_pass.tpl',
    'admin_access_level.tpl',
    'admin_set_setup_access_account.tpl',
    'admin_setup_access_accounts.tpl',
    'set_setup_access_group.tpl',
    'set_setup_access_level.tpl',
    'set_setup_access_profile.tpl',
    'setup_access_profiles.tpl',
);

foreach ($accessTemplates as $template) {
    $source = file_get_contents($root . '/templates/' . $template);
    foreach (array_keys($map) as $legacyAsset) {
        fa7Assert(strpos($source, $legacyAsset) === false, $template . ' still uses ' . $legacyAsset);
    }
}

echo 'UI_FA7_ICONS=PASS' . PHP_EOL;
