<?php

/**
 * Find recurring legacy Smarty layouts so they can be migrated in coherent,
 * testable groups instead of receiving broad global CSS overrides.
 */
function auditUiLegacyPatterns($projectRoot)
{
    $templateDir = rtrim($projectRoot, '/\\') . DIRECTORY_SEPARATOR . 'templates';
    $groups = array(
        'legacy_page_data_reports' => array(),
        'dual_list_editors' => array(),
        'legacy_input_dialogs' => array(),
        'access_family' => array(),
    );

    foreach (glob($templateDir . DIRECTORY_SEPARATOR . '*.tpl') as $file) {
        $source = file_get_contents($file);
        if ($source === false) {
            continue;
        }

        $name = basename($file);
        $hasResult = preg_match('/\bid\s*=\s*(["\'])result\1/i', $source) === 1;
        $hasPageData = preg_match('/\bclass\s*=\s*(["\'])[^"\']*\bpage_data\b[^"\']*\1/i', $source) === 1;
        $hasInputTable = preg_match('/<table\b[^>]*class\s*=\s*(["\'])[^"\']*\binput\b[^"\']*\1/i', $source) === 1;

        if ($hasResult && $hasPageData) {
            $groups['legacy_page_data_reports'][] = $name;
        }

        if (strpos($source, 'move_option_to') !== false && preg_match('/\bmultiple(?:\s*=|\s|>)/i', $source)) {
            $groups['dual_list_editors'][] = $name;
        }

        if (strpos($source, 'page_caption') !== false && $hasInputTable) {
            $groups['legacy_input_dialogs'][] = $name;
        }

        if (stripos($name, 'access') !== false) {
            $groups['access_family'][] = $name;
        }
    }

    foreach ($groups as &$templates) {
        sort($templates, SORT_STRING);
    }
    unset($templates);

    return $groups;
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME'])
    && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    $groups = auditUiLegacyPatterns(dirname(__DIR__));

    if (in_array('--json', $argv, true)) {
        echo json_encode($groups, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
        exit(0);
    }

    foreach ($groups as $group => $templates) {
        echo $group, ': ', count($templates), PHP_EOL;
        echo '  ', implode(', ', $templates), PHP_EOL;
    }
}
