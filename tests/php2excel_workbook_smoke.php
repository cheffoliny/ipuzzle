<?php

require_once dirname(__DIR__) . '/include/php2excel/class.writeexcel_workbook.inc.php';
require_once dirname(__DIR__) . '/include/php2excel/class.writeexcel_worksheet.inc.php';

$filename = tempnam(sys_get_temp_dir(), 'ipuzzle_xls_');

if ($filename === false) {
    fwrite(STDERR, "Unable to create a temporary workbook file.\n");
    exit(1);
}

try {
    $workbook = new writeexcel_workbook($filename);
    $worksheet = $workbook->addworksheet('Smoke test');
    $worksheet->write_string(0, 0, 'PHP 8.5');
    $worksheet->write_formula(1, 0, '=1+2');
    $workbook->close();

    $size = filesize($filename);

    if ($size === false || $size === 0) {
        fwrite(STDERR, "Workbook writer produced an empty file.\n");
        exit(1);
    }

    echo 'WORKBOOK_BYTES=' . $size . PHP_EOL;
} finally {
    if (is_file($filename)) {
        unlink($filename);
    }
}
