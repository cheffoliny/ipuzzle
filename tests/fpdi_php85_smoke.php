<?php

$root = dirname(__DIR__);
$directory = $root . '/include/fpdi';
$violations = array();

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $source = file_get_contents($file->getPathname());
    if ($source === false) {
        fwrite(STDERR, "Unable to read {$file->getPathname()}.\n");
        exit(1);
    }

    if (preg_match('/\$[A-Za-z_][A-Za-z0-9_]*\s*\{[^\r\n}]+\}/', $source)) {
        $violations[] = $file->getFilename();
    }
}

if (!empty($violations)) {
    fwrite(STDERR, "PHP 8 incompatible FPDI string offsets: " . implode(', ', $violations) . "\n");
    exit(1);
}

chdir($root);
set_include_path(get_include_path() . PATH_SEPARATOR . $root . PATH_SEPARATOR . $root . '/include');
require_once $root . '/include/fpdi/fpdi.php';

if (!class_exists('FPDI', false)) {
    fwrite(STDERR, "FPDI failed to load.\n");
    exit(1);
}

$sourceFile = tempnam(sys_get_temp_dir(), 'ipuzzle-fpdi-source-');
if ($sourceFile === false) {
    fwrite(STDERR, "Unable to create a temporary PDF source file.\n");
    exit(1);
}

try {
    $sourcePdf = new FPDF2();
    $sourcePdf->AddPage();
    $sourcePdf->Output($sourceFile, 'F');

    $pdf = new FPDI();
    $pageCount = $pdf->setSourceFile($sourceFile);
    if ($pageCount !== 1) {
        throw new RuntimeException("Expected one source page, got {$pageCount}.");
    }

    $templateId = $pdf->importPage(1);
    if (!is_int($templateId) || $templateId < 1) {
        throw new RuntimeException('FPDI failed to import the source page.');
    }

    $pdf->AddPage();
    $pdf->useTemplate($templateId);
    $output = $pdf->Output('', 'S');

    if (!is_string($output) || strncmp($output, '%PDF-', 5) !== 0 || strlen($output) < 500) {
        throw new RuntimeException('FPDI returned an invalid PDF document.');
    }
} catch (Throwable $exception) {
    fwrite(STDERR, "FPDI import failed: {$exception->getMessage()}\n");
    exit(1);
} finally {
    if (is_file($sourceFile)) {
        unlink($sourceFile);
    }
}

echo "FPDI_PHP85_SMOKE=PASS pages={$pageCount} bytes=" . strlen($output) . "\n";
