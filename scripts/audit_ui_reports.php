<?php

/**
 * Inventory every Smarty screen that owns an XML-RPC result area.
 *
 * This is intentionally structural: it guarantees that every #result template
 * is accounted for. Visual verification is then performed category by category.
 */
function auditUiReports($projectRoot)
{
    $templateDir = rtrim($projectRoot, '/\\') . DIRECTORY_SEPARATOR . 'templates';
    $finalMigrationScript = rtrim($projectRoot, '/\\') . DIRECTORY_SEPARATOR . 'js'
        . DIRECTORY_SEPARATOR . 'ui_final_migration.js';
    $finalMigrationPages = array();
    $finalMigrationSource = is_file($finalMigrationScript)
        ? file_get_contents($finalMigrationScript)
        : false;

    if ($finalMigrationSource !== false) {
        foreach (array('reportPages', 'supportingPages') as $collection) {
            if (preg_match('/var\s+' . $collection . '\s*=\s*\{(.*?)\};/s', $finalMigrationSource, $block)) {
                preg_match_all('/^\s*([A-Za-z0-9_]+)\s*:\s*true\s*,?\s*$/m', $block[1], $matches);
                foreach ($matches[1] as $page) {
                    $finalMigrationPages[$page . '.tpl'] = true;
                }
            }
        }
    }

    $files = glob($templateDir . DIRECTORY_SEPARATOR . '*.tpl');
    $categories = array(
        'modern_filter' => 0,
        'migrated_legacy' => 0,
        'migrated_module' => 0,
        'migrated_final' => 0,
        'legacy_search' => 0,
        'specialized' => 0,
        'test_only' => 0,
        'no_filter_detected' => 0,
    );
    $reports = array();

    foreach ($files as $file) {
        $source = file_get_contents($file);

        if ($source === false || !preg_match('/\bid\s*=\s*(["\'])result\1/i', $source)) {
            continue;
        }

        $name = basename($file);

        if ($name === 'test_page.tpl') {
            $category = 'test_only';
        } elseif (strpos($source, 'ui-legacy-report-filter') !== false
            || strpos($source, 'ui-salary-report') !== false
            || strpos($source, 'ui-finance-summary-report') !== false
            || strpos($source, 'ui-finance-summary-child') !== false
            || strpos($source, 'ui-finance-tree-report') !== false
            || strpos($source, 'ui-message-screen') !== false
            || strpos($source, 'ui-message-dialog') !== false) {
            $category = 'migrated_legacy';
        } elseif (isset($finalMigrationPages[$name])) {
            $category = 'migrated_final';
        } elseif (preg_match('/(?:tech_planning|person_schedule|limit_card_persons)/i', $name)) {
            $category = 'specialized';
        } elseif (strpos($source, 'table-secondary') !== false
            || strpos($source, 'ui-patrol-filter') !== false) {
            $category = 'modern_filter';
        } elseif (preg_match(
            '/\bui-(?!(?:icon|state|widget|corner|helper|autocomplete|datepicker|dialog|draggable|resizable|sortable|button)(?:-|$))[a-z][a-z0-9-]+/i',
            $source
        )) {
            $category = 'migrated_module';
        } elseif (preg_match('/<table\b[^>]*class\s*=\s*(["\'])[^"\']*\bsearch\b[^"\']*\1/i', $source)) {
            $category = 'legacy_search';
        } else {
            $category = 'no_filter_detected';
        }

        $flags = array();
        foreach (array('rpc_paging', 'rpc_excel_panel', 'rpc_resize') as $attribute) {
            if (preg_match('/\b' . preg_quote($attribute, '/') . '\s*=\s*(["\'])([^"\']*)\1/i', $source, $match)) {
                $flags[$attribute] = strtolower(trim($match[2]));
            } else {
                $flags[$attribute] = 'default';
            }
        }

        ++$categories[$category];
        $reports[] = array(
            'template' => $name,
            'category' => $category,
            'flags' => $flags,
        );
    }

    usort($reports, function ($left, $right) {
        return strcmp($left['template'], $right['template']);
    });

    return array(
        'total' => count($reports),
        'categories' => $categories,
        'reports' => $reports,
    );
}

if (PHP_SAPI === 'cli' && isset($_SERVER['SCRIPT_FILENAME'])
    && realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    $projectRoot = dirname(__DIR__);
    $inventory = auditUiReports($projectRoot);

    if (in_array('--json', $argv, true)) {
        echo json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
        exit(0);
    }

    echo 'UI report templates: ', $inventory['total'], PHP_EOL;
    foreach ($inventory['categories'] as $category => $count) {
        echo str_pad($category, 22), $count, PHP_EOL;
    }
}
