<?php

$root = dirname(__DIR__);
$script = $root . '/scripts/audit_unused_files.php';
$command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' --check';
$output = array();
$status = 0;

exec($command, $output, $status);
$text = implode("\n", $output);

if ($status !== 0 || strpos($text, 'UNUSED_FILES_AUDIT=PASS') === false) {
    fwrite(STDERR, "Unused-file manifest audit failed.\n" . $text . "\n");
    exit(1);
}

$manifest = json_decode(file_get_contents($root . '/scripts/unused-files-manifest.json'), true);
$checks = array(
    is_array($manifest),
    isset($manifest['deletion_authorized']) && $manifest['deletion_authorized'] === false,
    isset($manifest['unclassified_policy']),
    strpos($manifest['unclassified_policy'], 'database-driven routes') !== false,
    strpos(file_get_contents($root . '/js/xmlrpc.js'), 'XML Response:') !== false,
    file_exists($root . '/js/rpc_result_renderer.js'),
    file_exists($root . '/include/adodb/drivers/adodb-mysqli.inc.php')
);

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "Unused-file safety contract is incomplete.\n");
    exit(1);
}

echo "UNUSED_FILES_MANIFEST=PASS\n";

