<?php

function legacyIconMap()
{
    return array(
        'images/plus.gif' => 'plus',
        'images/confirm.gif' => 'check',
        'images/cancel.gif' => 'close',
        'images/edit.gif' => 'edit',
        'images/erase.gif' => 'delete',
        'images/bin.gif' => 'delete',
        'images/mright.gif' => 'right',
        'images/mleft.gif' => 'left',
        'images/search2.gif' => 'filter',
        'images/reload.gif' => 'refresh',
        'images/cal.gif' => 'calendar',
        'images/pdf.gif' => 'file-pdf',
        'images/setup.gif' => 'settings',
        'images/refresh_ppp.gif' => 'refresh',
        'images/glyphicons/forw_right.png' => 'right',
        'images/glyphicons/forw_left.png' => 'left',
        'images/glyphicons/cancel.png' => 'close',
        'images/glyphicons/edit2.png' => 'edit',
        'images/glyphicons/del2.png' => 'delete',
        'images/glyphicons/cal.png' => 'calendar',
        'images/glyphicons/refresh.png' => 'refresh',
    );
}

/** Inventory static image tags in Smarty templates and identify action icons. */
function auditLegacyIcons($projectRoot)
{
    $templateDir = rtrim($projectRoot, '/\\') . DIRECTORY_SEPARATOR . 'templates';
    $map = legacyIconMap();
    $assets = array();
    $templates = array();
    $total = 0;
    $actionTotal = 0;

    foreach (glob($templateDir . DIRECTORY_SEPARATOR . '*.tpl') as $file) {
        $source = file_get_contents($file);
        if ($source === false) {
            continue;
        }

        preg_match_all('/<img\b[^>]*\bsrc\s*=\s*(["\']?)(images\/[^"\'\s>]+)\1[^>]*>/i', $source, $matches);
        if (empty($matches[2])) {
            continue;
        }

        foreach ($matches[2] as $asset) {
            $asset = str_replace('\\', '/', $asset);
            ++$total;
            if (!isset($assets[$asset])) {
                $assets[$asset] = 0;
            }
            ++$assets[$asset];

            if (isset($map[$asset])) {
                ++$actionTotal;
                $templates[basename($file)] = true;
            }
        }
    }

    ksort($assets, SORT_STRING);
    $templateNames = array_keys($templates);
    sort($templateNames, SORT_STRING);

    return array(
        'total_static_images' => $total,
        'mapped_action_icons' => $actionTotal,
        'templates_with_mapped_icons' => $templateNames,
        'assets' => $assets,
    );
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME'])
    && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    $audit = auditLegacyIcons(dirname(__DIR__));

    if (in_array('--json', $argv, true)) {
        echo json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
        exit(0);
    }

    echo 'Static template images: ', $audit['total_static_images'], PHP_EOL;
    echo 'Mapped action icons: ', $audit['mapped_action_icons'], PHP_EOL;
    echo 'Templates awaiting mapped icon migration: ', count($audit['templates_with_mapped_icons']), PHP_EOL;
}
