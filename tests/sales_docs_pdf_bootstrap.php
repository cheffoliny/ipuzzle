<?php

chdir(dirname(__DIR__));

error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SESSION = array('BASE_DIR' => getcwd());

set_error_handler(
    function ($nSeverity, $sMessage, $sFile, $nLine) {
        if (!(error_reporting() & $nSeverity)) {
            return false;
        }

        throw new ErrorException($sMessage, 0, $nSeverity, $sFile, $nLine);
    }
);

try {
    require_once 'pdf/pdf_sale_doc.php';

    if (!defined('FPDF_FONTPATH')) {
        throw new RuntimeException('FPDF_FONTPATH was not initialized.');
    }

    if (!class_exists('SaleDocPDF')) {
        throw new RuntimeException('SaleDocPDF was not loaded.');
    }

    $oPdf = new SaleDocPDF('P');
    if (!($oPdf instanceof PDFC)) {
        throw new RuntimeException('SaleDocPDF did not initialize through PDFC.');
    }

    $oPdf->AddPage();
    $oPdf->SetFont('FreeSans', '', 10);
    $oPdf->Cell(40, 10, 'PHP 8.5 / €');
    $sPdf = $oPdf->Output('S');

    if (strncmp($sPdf, '%PDF-', 5) !== 0 || strlen($sPdf) < 500) {
        throw new RuntimeException('SaleDocPDF did not generate a valid in-memory PDF.');
    }
} catch (Throwable $oError) {
    restore_error_handler();
    fwrite(
        STDERR,
        'Sales docs PDF bootstrap failed: ' . $oError->getMessage() .
        ' in ' . $oError->getFile() . ':' . $oError->getLine() . "\n"
    );
    exit(1);
}

restore_error_handler();

echo "SALES_DOCS_PDF_BOOTSTRAP=PASS\n";
