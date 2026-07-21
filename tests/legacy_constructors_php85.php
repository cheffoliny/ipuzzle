<?php

$root = dirname(__DIR__);
chdir($root);
set_include_path(get_include_path() . PATH_SEPARATOR . $root . PATH_SEPARATOR . $root . '/include');

require_once $root . '/include/unzip.inc.php';
require_once $root . '/include/create_xml.inc.php';
require_once $root . '/include/smarty/Config_File.class.php';
require_once $root . '/include/barcodes/class/FColor.php';
require_once $root . '/include/barcodes/class/BarCode.php';
require_once $root . '/include/barcodes/class/FDrawing.php';
require_once $root . '/include/barcodes/class/code128.barcode.php';
require_once $root . '/include/ufpdf/ufpdf.php';

function constructorFail($message)
{
    fwrite(STDERR, "LEGACY_CONSTRUCTORS_PHP85=FAIL {$message}\n");
    exit(1);
}

$unzip = new SimpleUnzip();
if ($unzip->Count() !== 0) {
    constructorFail('SimpleUnzip was not initialized.');
}

$entryData = array(
    'D' => 'data',
    'E' => 0,
    'EM' => '',
    'N' => 'file.txt',
    'P' => '',
    'T' => 123,
);
$entry = new SimpleUnzipEntry($entryData);
if ($entry->Data !== 'data' || $entry->Name !== 'file.txt' || $entry->Time !== 123) {
    constructorFail('SimpleUnzipEntry was not initialized.');
}

$node = new xml_child('root');
$node->addData('value');
$xml = '';
$node->renderNode($xml);
if (strpos($xml, '<root>value</root>') === false) {
    constructorFail('xml_child was not initialized.');
}

$config = new Config_File($root . '/templates');
$configPath = (new ReflectionProperty($config, '_config_path'))->getValue($config);
if ($configPath !== $root . '/templates' . DIRECTORY_SEPARATOR) {
    constructorFail('Config_File path was not initialized.');
}

$color = new FColor('#ffffff');
$drawing = new FDrawing('barcode.png', $color);
if ($drawing->filename !== 'barcode.png' || $drawing->color !== $color) {
    constructorFail('FDrawing was not initialized.');
}

$black = new FColor(0, 0, 0);
$barcode = new code128(20, $black, $color, 1, 'IPUZZLE85', null);
$drawing->filename = '';
$drawing->setBarcode($barcode);
$drawing->draw();
ob_start();
$drawing->finish('png');
$pngOutput = ob_get_clean();
if (strncmp($pngOutput, "\x89PNG\r\n\x1a\n", 8) !== 0) {
    constructorFail('Barcode renderer returned an invalid PNG document.');
}

$pdf = new UFPDF();
$pdf->AddPage();
$pdfOutput = $pdf->Output('', 'S');
if (!is_string($pdfOutput) || strncmp($pdfOutput, '%PDF-', 5) !== 0) {
    constructorFail('UFPDF was not initialized.');
}

echo 'LEGACY_CONSTRUCTORS_PHP85=PASS pdf_bytes=' . strlen($pdfOutput)
    . ' png_bytes=' . strlen($pngOutput) . "\n";
