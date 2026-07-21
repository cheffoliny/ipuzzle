<?php

$root = dirname(__DIR__);
$css = file_get_contents($root . '/css/ui-refresh-shell.css');
$index = file_get_contents($root . '/templates/index.tpl');

if (in_array(false, array($css, $index), true)) {
    fwrite(STDERR, "Unable to read UI menu contract files.\n");
    exit(1);
}

$checks = array(
    strpos($index, 'css/ui-refresh-shell.css?version=2') !== false,
    strpos($index, 'class="dropdown-submenu"') !== false,
    strpos($css, '#main-menu .dropdown-submenu > .dropdown-menu') !== false,
    strpos($css, 'top: 0;') !== false,
    strpos($css, 'left: calc(100% - 1px);') !== false,
    strpos($css, '#main-menu .dropdown-submenu:hover > .dropdown-menu') !== false,
    strpos($css, '#main-menu .dropdown-submenu:focus-within > .dropdown-menu') !== false,
    strpos($css, '@media (min-width: 992px)') !== false,
    strpos($index, "href=\"javascript:do_menu('{\$item.filename}')\"") !== false,
    strpos($index, 'id="content"') !== false
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "UI submenu alignment contract failed.\n");
    exit(1);
}

echo "UI_REFRESH_MENU_CONTRACT=PASS\n";
