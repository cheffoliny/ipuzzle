<?php

/**
 * Read-only audit for scripts/unused-files-manifest.json.
 *
 * This script never deletes or changes a project file. It expands the manifest,
 * reports file counts/sizes and rejects cleanup candidates that overlap a
 * protected runtime file.
 */

$root = dirname(__DIR__);
$manifestPath = __DIR__ . '/unused-files-manifest.json';
$manifestJson = file_get_contents($manifestPath);

if ($manifestJson === false) {
    fwrite(STDERR, "UNUSED_FILES_AUDIT=FAIL unable_to_read_manifest\n");
    exit(1);
}

$manifest = json_decode($manifestJson, true);
if (!is_array($manifest) || json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "UNUSED_FILES_AUDIT=FAIL invalid_json " . json_last_error_msg() . "\n");
    exit(1);
}

function unusedNormalizePath($path)
{
    return ltrim(str_replace('\\', '/', $path), '/');
}

function unusedPathIsInside($path, $directory)
{
    $path = unusedNormalizePath($path);
    $directory = rtrim(unusedNormalizePath($directory), '/');
    return $path === $directory || strpos($path, $directory . '/') === 0;
}

function unusedMatchesSelectors($path, $selectors)
{
    $path = unusedNormalizePath($path);

    foreach (isset($selectors['files']) ? $selectors['files'] : array() as $file) {
        if ($path === unusedNormalizePath($file)) {
            return true;
        }
    }

    foreach (isset($selectors['directories']) ? $selectors['directories'] : array() as $directory) {
        if (unusedPathIsInside($path, $directory)) {
            return true;
        }
    }

    foreach (isset($selectors['globs']) ? $selectors['globs'] : array() as $glob) {
        if (fnmatch(unusedNormalizePath($glob), $path, FNM_CASEFOLD)) {
            return true;
        }
    }

    return false;
}

function unusedCollectInventory($root)
{
    $inventory = array();
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            function ($current) {
                return $current->getFilename() !== '.git';
            }
        )
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $relative = substr($file->getPathname(), strlen($root) + 1);
        $inventory[unusedNormalizePath($relative)] = $file->getSize();
    }

    ksort($inventory);
    return $inventory;
}

function unusedFormatBytes($bytes)
{
    $units = array('B', 'KiB', 'MiB', 'GiB');
    $value = (float) $bytes;
    $unit = 0;
    while ($value >= 1024 && $unit < count($units) - 1) {
        $value /= 1024;
        ++$unit;
    }
    return number_format($value, $unit === 0 ? 0 : 2, '.', '') . ' ' . $units[$unit];
}

$inventory = unusedCollectInventory($root);
$errors = array();
$protected = isset($manifest['protected']) ? $manifest['protected'] : array();
$candidateClasses = array('safe_after_review', 'generated_cleanup_candidate', 'conditional_candidate');
$totals = array();

foreach ($manifest['groups'] as $group) {
    $selectors = isset($group['selectors']) ? $group['selectors'] : array();
    $exclude = isset($group['exclude']) ? $group['exclude'] : array();
    $matches = array();
    $bytes = 0;

    foreach (isset($selectors['files']) ? $selectors['files'] : array() as $expectedFile) {
        $expectedFile = unusedNormalizePath($expectedFile);
        if (!array_key_exists($expectedFile, $inventory)) {
            $errors[] = $group['id'] . ': missing exact file ' . $expectedFile;
        }
    }

    foreach (isset($selectors['directories']) ? $selectors['directories'] : array() as $expectedDirectory) {
        if (!is_dir($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $expectedDirectory))) {
            $errors[] = $group['id'] . ': missing directory ' . unusedNormalizePath($expectedDirectory);
        }
    }

    foreach ($inventory as $path => $size) {
        if (!unusedMatchesSelectors($path, $selectors) || unusedMatchesSelectors($path, $exclude)) {
            continue;
        }
        $matches[$path] = $size;
        $bytes += $size;

        if (in_array($group['classification'], $candidateClasses, true)
            && unusedMatchesSelectors($path, $protected)) {
            $errors[] = $group['id'] . ': protected path selected ' . $path;
        }
    }

    $count = count($matches);
    if ($count === 0) {
        $errors[] = $group['id'] . ': selector matched no files';
    }

    if (!isset($totals[$group['classification']])) {
        $totals[$group['classification']] = array('files' => 0, 'bytes' => 0);
    }
    $totals[$group['classification']]['files'] += $count;
    $totals[$group['classification']]['bytes'] += $bytes;

    echo 'GROUP=' . $group['id']
        . ' CLASS=' . $group['classification']
        . ' FILES=' . $count
        . ' SIZE=' . unusedFormatBytes($bytes) . "\n";
}

foreach ($totals as $classification => $total) {
    echo 'TOTAL CLASS=' . $classification
        . ' FILES=' . $total['files']
        . ' SIZE=' . unusedFormatBytes($total['bytes']) . "\n";
}

if (!empty($errors)) {
    foreach (array_unique($errors) as $error) {
        fwrite(STDERR, 'ERROR ' . $error . "\n");
    }
    fwrite(STDERR, "UNUSED_FILES_AUDIT=FAIL\n");
    exit(1);
}

echo 'UNUSED_FILES_AUDIT=PASS INVENTORY_FILES=' . count($inventory) . "\n";

