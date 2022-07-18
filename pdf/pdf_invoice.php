<?php
include_once('pdfc.inc.php');
include_once('include/bg_slovom.inc.php');

class InvoicePDF extends PDFC
{
    public $aMargin = array("left" => 16, "top" => 15, "right" => 10, "bottom" => 18);
    private $aOptions = array(
        'TextColor' => array('r' => 0, 'g' => 0, 'b' => 0),
        'Background' => array('title' => 254, 'body' => 254), // 245, 245
        'BorderColor' => 255,
        'BorderWidth' => 0.05,
        'FontSize' => 7.2,
        'RowHeight' => 5,
        'Widths' => array('title' => 11.2, 'body' => 56),

        'CaptionHeight' => 5.5,
        'CaptionFontSize' => 8.5,
        'CaptionTextColor' => 10,
        'CaptionBackground' => array('r' => 220, 'g' => 220, 'b' => 220),
        'CaptionBackgroundW' => array('r' => 255, 'g' => 255, 'b' => 255),

        'TitleFontSize' => 16,
        'TitleWidth' => 48,

        'BarcodeWidth' => 30,
        'BarcodeHeight' => 8,

        'NumFontSize' => 11,

        'LogoWidth' => 12,

        'WaterStampTextSize' => 40,
        'WaterStampColor' => 240,  // 200

        'Fields' => array(    // колони на таблицата с услугите/стоките
            'num' => array(
                'align' => 'R',
                'width' => 8,
                'Caption' => '№'
            ),
            'service' => array(
                'align' => 'L',
                'width' => 102,
                'Caption' => 'Наименование на услуга/обект'
            ),
            'quantity' => array(
                'align' => 'R',
                'width' => 15,
                'Caption' => 'кол'
            ),
            'measure' => array(
                'align' => 'C',
                'width' => 15,
                'Caption' => 'мярка'
            ),
            'single_price' => array(
                'align' => 'R',
                'width' => 20,
                'Caption' => 'ед.цена'
            ),
            'total_sum' => array(
                'align' => 'R',
                'width' => 20,
                'Caption' => 'дан. основа'
            )
        )
    );

    private $sDocumentHeader;    // Тип на документа в заглавната част на разпечатката : ОРИГИНАЛ, КОПИЕ, ТОВА НЕ Е ФАКТУРА ....
    private $sDocument;            // Вид на документа : ФАКТУРА, ПРОФОРМА, КВИТАНЦИЯ
    private $sInvoceType;        // Тип на фактурата
    private $sViewType;            // Изглед на данните : единичен, по услуги, по обекти ...
    private $sDocDate;            // Дата на документа
    private $flag;
    private $advice;            // Номер/дата на известие
    public $copie = false;
    private $num_rows = 0;
    private $sClientID = "";
    private $ein = "";
    private $document = [];

    public function __construct($orientation) {
        parent::__construct($orientation);
    }


    private function printLogo()
    {
        if ( !empty($this->ein) && file_exists($_SESSION['BASE_DIR'] . "/images/title_{$this->ein}.png") ) {
            $this->Image($_SESSION['BASE_DIR'] . "/images/title_{$this->ein}.png", -4, 10, 162); //185  //162
        } else {
            $this->Image(dirname(dirname(__FILE__)) . '/images/title.png', -4, 10, 162); //185  //162
        }
    }

    private function printAdvert()
    {
        if ( !empty($this->ein) && file_exists($_SESSION['BASE_DIR'] . "/images_adverts/adverts_{$this->ein}.png") ) {
            $this->Image($_SESSION['BASE_DIR'] . "/images_adverts/adverts_{$this->ein}.png", 6, 250, 193);
        } else {
            if ( file_exists(dirname(dirname(__FILE__)) . "/images_adverts/adverts.png") ) {
                $this->Image(dirname(dirname(__FILE__)) . '/images_adverts/adverts.png', 6, 250, 193); //185  //162
            }
        }
    }

    private function printBareCode($nDocNum)
    {

        //$path = substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], 'page'));
        //$path = rtrim($path, '/');
//			$sContent = sprintf("%s/include/barcodes/barcode.php?code=code128&pcode=%s&text=0&height=44&resolution=1&img_type=jpg", $path, $nDocNum );
//			$this->Image( 	$sContent, 
//							$this->aMargin['left'] + $this->aOptions['TitleWidth'] + ($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body']) * 2 - $this->aOptions['BarcodeWidth'] - $this->aOptions['BorderWidth'], 
//							$this->aMargin['top'], 
//							$this->aOptions['BarcodeWidth'], 
//							$this->aOptions['BarcodeHeight'], 
//							'jpg' );		
        $this->Ln(12);

    }

    private function PrintClient() {
        $client_name = $this->document['client_name'];
        $client_address = $this->document['client_address'];
        $client_EIN = $this->document['client_ein'];
        $client_EINDDS = $this->document['client_ein_dds'];
        //$client_mol = $aSaleDoc['client_mol'];

        $x = $this->GetX();
        $y = $this->GetY();

        // заглавие на панела
        $this->SetTextColor($this->aOptions['CaptionTextColor']);
        $this->SetDrawColor($this->aOptions['Background']['body']);
        $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
        $this->Cell($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'], $this->aOptions['CaptionHeight']*1.3, ' ПОЛУЧАТЕЛ', 1, 0, 'L', 1);

        // съдържания на панела
        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->Ln();

        $nAddressFontSize = $this->aOptions['FontSize'];
        $this->PorccessText($client_address, $sAddress1, $sAddress2, $nAddressFontSize);

        $nClientFontSize = $this->aOptions['FontSize'];
        $this->PorccessText($client_name, $sClient1, $sClient2, $nClientFontSize);

        $this->SetFont('FreeSans', '', $nClientFontSize);
        $this->PrintRow($x, 'Име:', $sClient1, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, '', $sClient2, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->SetFont('FreeSans', '', $nAddressFontSize);
        $this->PrintRow($x, 'Адрес:', $sAddress1, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, '', $sAddress2, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        //$this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, 'ИН/ЕГН:', $client_EIN, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, 'ИН ДДС:', $client_EINDDS, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        //$this->PrintRow($x, '', "", $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        //$this->PrintRow($x, 'МОЛ', $client_mol, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);

        $this->SetXY($x, $y);
    }

    // Печат на панела с информация за фактурата (номер, дата, тип ...)
    private function PrintInvoiceTitle() {
        //$invoice_date = $this->sDocDate;
        $created_time = substr($this->document['created_time'], 0, 10);
        $aTmp = explode("-", $created_time);
        $document_num = $this->document['doc_num'];

        $nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

        if (!empty($nIDPerson)) {
            $oDBPersonnel = new DBPersonnel();
            $document_city = $oDBPersonnel->getCityName($nIDPerson);
        } else {
            $document_city = '';
        }

        //$doc_len = strlen($document_num);
        $document_num = sprintf("%010s", $document_num);

        $x = $this->aMargin['left'] + $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'];
        $y = $this->GetY();
        $this->SetXY($x, $y);

        // заглавие на панела
        $this->SetTextColor($this->aOptions['CaptionTextColor']);
        $this->SetDrawColor($this->aOptions['Background']['body']);
        $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);

        if ( !in_array($this->sDocument, ["КРЕДИТНО ИЗВЕСТИЕ", "ДЕБИТНО ИЗВЕСТИЕ"]) ) {   // $this->sDocument != "КРЕДИТНО ИЗВЕСТИЕ"
            $this->SetFont('FreeSans', 'B', $this->aOptions['TitleFontSize']);
        } else {
            $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
        }

        $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['CaptionHeight'] + $this->aOptions['RowHeight']/3.4, $this->sDocument, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetDrawColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetFillColor($this->aOptions['CaptionBackgroundW']['r'], $this->aOptions['CaptionBackgroundW']['g'], $this->aOptions['CaptionBackgroundW']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);

        $this->SetX($x);
        $this->SetFont('FreeSans', 'B', $this->aOptions['NumFontSize']);
        $this->Cell($this->aOptions['TitleWidth'], 1.8 * $this->aOptions['RowHeight'], "№ " . $document_num, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetX($x);
        $this->SetDrawColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetFillColor($this->aOptions['CaptionBackgroundW']['r'], $this->aOptions['CaptionBackgroundW']['g'], $this->aOptions['CaptionBackgroundW']['b']);
        $this->SetFont('FreeSans', '', $this->aOptions['CaptionFontSize']);
        $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['RowHeight']*1.3, isset($aTmp[2]) ? "дата: " . $aTmp[2] . "." . $aTmp[1] . "." . $aTmp[0] : "дата: " . $created_time, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetX($x);
        $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['RowHeight']*1.3, "място: " . $document_city, 1, 0, 'C', 1);
        $this->Ln();

        if ($this->sInvoceType != '') {
            /*
            $this->SetX($x);
            $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
            $this->SetTextColor($this->aOptions['CaptionTextColor']);
            $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
            $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['RowHeight'], $this->sInvoceType, 1, 0, 'C', 1);
            $this->Ln();
            */

            $this->SetX($x);
            $this->SetDrawColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
            $this->SetFillColor($this->aOptions['CaptionBackgroundW']['r'], $this->aOptions['CaptionBackgroundW']['g'], $this->aOptions['CaptionBackgroundW']['b']);
            $this->SetTextColor($this->aOptions['CaptionTextColor']);
            $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize'] - 2);
            $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['RowHeight'], $this->sInvoceType . " " . $this->advice, 1, 0, 'C', 1);
            $this->SetXY($x, $y);
        } else {
            /*
            $this->SetX($x);
            $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
            $this->SetTextColor($this->aOptions['CaptionTextColor']);
            $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
            $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['RowHeight'], "клиентски номер", 1, 0, 'C', 1);
            $this->Ln();
            */

            $this->SetX($x);
            $this->SetDrawColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
            $this->SetFillColor($this->aOptions['CaptionBackgroundW']['r'], $this->aOptions['CaptionBackgroundW']['g'], $this->aOptions['CaptionBackgroundW']['b']);
            $this->SetTextColor($this->aOptions['CaptionTextColor']);
            $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']*1.1);
            $this->Cell($this->aOptions['TitleWidth'], $this->aOptions['RowHeight']*1.6, "КИН: " . $this->sClientID, 1, 0, 'C', 1);
            $this->SetXY($x, $y);
        }

    }

    // Печат на панела с информация за доставчика
    private function PrintDeliver() {
        $deliver_name = $this->document['deliverer_name'];
        $deliver_address = $this->document['deliverer_address'];
        $deliver_EIN = $this->document['deliverer_ein'];
        $deliver_EINDDS = $this->document['deliverer_ein_dds'];
        //$deliver_mol = $aSaleDoc['deliverer_mol'];

        /*
        $oDBFirm        = new DBFirms();
        $oDBBanks       = new DBBankAccounts();
        $aFirm          = $oDBFirm->getDDSFirmByEIN($this->ein);
        $nIDBank        = isset($aFirm['id_bank_account_default']) ? (int)$aFirm['id_bank_account_default'] : 0;
        $aBanks         = $oDBBanks->getBankAccoutById($nIDBank);
        $sIBAN          = isset($aBanks['iban'])        ? $aBanks['iban']       : "";
        $sBIC           = isset($aBanks['bic'])         ? $aBanks['bic']        : "";
        $sName          = isset($aBanks['name_bank'])   ? $aBanks['name_bank']  : "";
        */

        $x = $this->aMargin['left'] + $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'];
        $y = $this->GetY();
        $this->SetXY($x, $y);

        // заглавие на панела
        $this->SetTextColor($this->aOptions['CaptionTextColor']);
        $this->SetDrawColor($this->aOptions['Background']['body']);
        $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
        $this->Cell($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'], $this->aOptions['CaptionHeight']*1.3, ' ДОСТАВЧИК', 1, 0, 'L', 1);

        // съдържания на панела
        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        $this->Ln();

        $this->PrintRow($x, 'Име:', $deliver_name, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow($x, 'Адрес:', $deliver_address, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, 'ИН:', $deliver_EIN, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        $this->PrintRow($x, 'ИН ДДС:', $deliver_EINDDS, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);

        //$this->PrintRow($x, 'МОЛ', $deliver_mol, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);

        //$this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        //$this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);

        /*
        if (isset($aSaleDoc['paid_type']) && ($aSaleDoc['paid_type'] == "cash")) {
            $this->PrintRow($x, 'Банка', "IBAN: {$sIBAN}", $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);  //BG92UBBS85411010333309
            $this->PrintRow($x, '', "BIC: {$sBIC}, {$sName}", $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);  //UBBSBGSF, Банка ОББ
        } else {
            $this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
            $this->PrintRow($x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body']);
        }
        */
    }

    // Печат на ред от таблица с 2 колони title->body
    private function PrintRow($nLeft, $sTitle, $sBody, $nTitleWidth, $nBodyWidth, $body_align = "L")
    {
        $this->SetX($nLeft);
        $this->SetFillColor($this->aOptions['Background']['title']);
        $this->Cell($nTitleWidth, $this->aOptions['RowHeight'], $sTitle, "LTB", 0, 'L', 1);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->Cell($nBodyWidth, $this->aOptions['RowHeight'], $sBody, "RTB", 0, $body_align, 1);
        $this->Ln();
    }

    private function insertArrayIndex($array, $key, $new_element, $index)
    {
        /*** get the start of the array ***/
        $start = array_slice($array, 0, $index);
        /*** get the end of the array ***/
        $end = array_slice($array, $index);
        /*** add the new element to the array ***/
        $start[$key] = $new_element;
        /*** glue them back together and return ***/
        return array_merge($start, $end);
    }

    // Печат на заглавния ред на таблицата с цени
    public function PrintTableDataHeader() {
        $this->SetTextColor($this->aOptions['CaptionTextColor']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['CaptionFontSize']);

        // ако е детайлна се добавя колона 'за месец'
        if ($this->sViewType == 'detail' || $this->sViewType == 'extended') {
            // намаля се широчината на някои от другите колони
            $this->aOptions['Fields']['measure']['width'] = 11;
            $this->aOptions['Fields']['quantity']['width'] = 8;

            $x = array('align' => 'L', 'width' => 14, 'Caption' => 'за месец');
            $this->aOptions['Fields'] = $this->insertArrayIndex($this->aOptions['Fields'], 'month', $x, 2);
        }

        $this->aOptions['Fields']['service']['width'] = 2 * $this->aOptions['Widths']['title'] +
            2 * $this->aOptions['Widths']['body'] +
            $this->aOptions['TitleWidth'];

        foreach ($this->aOptions['Fields'] as $sFieldKey => $aField) {
            if ($sFieldKey != 'service')
                $this->aOptions['Fields']['service']['width'] -= $aField['width'];
        }

        foreach ($this->aOptions['Fields'] as $aField) {
            $numbers = ["№", "кол", "ед.цена", "дан.основа"];
            $position = in_array($aField['Caption'], $numbers) ? "R" : "C";

            $this->Cell($aField['width'], $this->aOptions['CaptionHeight'], $aField['Caption'], 0, 0, $position, 1);
        }
        $this->Ln();
    }


    private function getContent($nID) {
        $oInvoice = new NEWDBSalesDocs();

        $viewType = $this->document['view_type'] ?? "extended";

        switch ($viewType) {
            case "single":
                $docRows = $oInvoice->getDocRowsViewBySingle($nID);

                break;

            case "detail":
                // detail, по месеци
                $docRows = $oInvoice->getDocRowsViewByMonth($nID);

                foreach ( $docRows as $key => $docRow ) {
                    $docRows[$key]['service_name'] = "[" . $docRow['object_name'] . "] " . $docRow['service_name'];
                }

                break;

            case "by_objects":
                // by_objects, по обекти
                $docRows = $oInvoice->getDocRowsViewByObject($nID);

                break;

            case "by_services":
                // by_services, по услуги
                $docRows = $oInvoice->getDocRowsViewByService($nID);

                break;

            default:
                // extended, подробен изглед
                $docRows = $oInvoice->getDocRows($nID);

                foreach ( $docRows as $key => $docRow ) {
                    $docRows[$key]['service_name'] = "[" . $docRow['object_name'] . "] " . $docRow['service_name'];

                    if ( $docRow['is_dds'] == 1 ) {
                        unset($docRows[$key]);
                    }
                }

                break;
        }

        return $docRows;
    }

    // Печат на цените по фактурата
    public function PrintTableData($nID) {
        $sType = $this->document['doc_type'] ?? "faktura";
        $docRows = $this->getContent($nID);

        $this->num_rows = count($docRows);

        // Печат на воден знак - ОРИГИНАЛ, КОПИЕ
        $oldY = $this->GetY();
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFillColor($this->aOptions['Background']['body']);

        for ( $i = 0; $i < 10; $i++ ) {
            // Рисува фона на първите 10 реда
            foreach ($this->aOptions['Fields'] as $sFieldKey => $aField) {
                $this->Cell($aField['width'], $this->aOptions['RowHeight'], '', 1, 0, 'L', 1);
            }

            $this->Ln();
        }

        $this->SetY($oldY);
        $this->SetTextColor($this->aOptions['WaterStampColor']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['WaterStampTextSize']);
        $this->Cell(2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'], 10 * $this->aOptions['RowHeight'], $this->sDocumentHeader, 0, 0, 'C', 0);
        $this->SetY($oldY);

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);

        $nRow = 0;
        $nCounter = 0;

        // format data
        $width = $this->aOptions['Fields']['service']['width'];

        foreach ($docRows as $key => $aRow ) {
            $line = $this->ProcessServiceText($aRow['service_name'], $width);
            $docRows[$key]['lines'] = count($line);

            // Todo: service name!!!
            $docRows[$key]['service'] = $aRow['service_name'];
        }

        foreach ($docRows as $aRow) {
            $aRow['num'] = strval(++$nCounter);

            foreach ($this->aOptions['Fields'] as $sFieldKey => $aField) {
                if ($sFieldKey == 'single_price' || $sFieldKey == 'total_sum') {
                    $sData = sprintf('%0.3f лв.', $sType != "kreditno izvestie" ? $aRow[$sFieldKey] : $aRow[$sFieldKey] * -1);
                } else {
                    if ( $sFieldKey == 'service' ) {
                        $line = $this->ProcessServiceText($aRow[$sFieldKey], $aField['width']);
                        $sData = $line[0];
                    } else if ($sFieldKey == 'month') {
                        list($yr, $mon, $day) = explode('-', $aRow[$sFieldKey]);

                        $display_date = date('m.Y', mktime(0, 0, 0, $mon, $day, $yr));
                        $sData = $display_date;
                    } else if ($sFieldKey == 'quantity') {
                        $sData = sprintf("%01.2f", $aRow[$sFieldKey]);
                    } else {
                        $sData = $aRow[$sFieldKey];
                    }
                }

                // за първите 10 реда не се слага background и border, за да не закрива водния знак
                if ($nRow < 10) {
                    $this->Cell($aField['width'], $this->aOptions['RowHeight'], $sData, 0, 0, $aField['align'], 0);
                } else {
                    $this->Cell($aField['width'], $this->aOptions['RowHeight'], $sData, 1, 0, $aField['align'], 1);
                }
            }

            $nRow++;
            $this->Ln();

            if ( $aRow['lines'] == 2 ) {
                foreach ( $this->aOptions['Fields'] as $sFieldKey => $aField ) {
                    if ( $sFieldKey == 'service' ) {
                        $line = $this->ProcessServiceText($aRow[$sFieldKey], $aField['width']);
                        $this->Cell($aField['width'], $this->aOptions['RowHeight'], $line[1], 0, 0, $aField['align'], 0);
                    } else {
                        $this->Cell($aField['width'], $this->aOptions['RowHeight'], "", 0, 0, $aField['align'], 0);
                    }
                    //$this->Cell($aField['width'], $this->aOptions['RowHeight'], "a", 0, 0, $aField['align'], 0);
                }

                $nRow++;
                $this->Ln();
            }
        }

        for ($i = $nRow; $i < 9; $i++) {
            $this->Ln($this->aOptions['RowHeight']);
        }
    }

    private function ProcessServiceText($sText, $width) {

        $sText1 = trim($sText);
        $sText2 = null;
        $aResult = [];
        //$width *= 2.2;

        $aText = explode(' ', $sText1);

        if ( $this->GetStringWidth($sText1) > $width * 1.5 ) {
            while ( $this->GetStringWidth($sText1) > $width * 1.5 ) { //(($width * 60) / 100)
                $sText2 = array_pop($aText) . " " . $sText2;
                $sText1 = implode(" ", $aText);
            }
        }

        $aResult[] = $sText1;

        if ( $sText2 ) {
            $aResult[] = $sText2;
        }

        return $aResult;
    }

    // Печат на долния панел с обобщени данни за фактурата
    public function PrintTotal() {
        $advice_reason = $this->document['advice_reason'] ?: "корекция на грешка във фактурата";
        $sClientRecipient = $this->document['client_recipient'];
        $nIDCreatedUser = $this->document['created_user'];

        $oDBPersonnel   = new DBPersonnel();
        $oDBBanks       = new DBBankAccounts();
        $oDBFirm        = new DBFirms();
        $aFirm          = $oDBFirm->getOneFirmByEIN($this->ein);
        $nIDFirm        = isset($aFirm['id']) ? (int)$aFirm['id'] : 0;

        $sCreatedUser = $oDBPersonnel->getPersonnelNames2($nIDCreatedUser);
        $aBanks = $oDBBanks->getInvoiceAccounts($nIDFirm);

        $sType = isset($this->document['doc_type']) ? $this->document['doc_type'] : "faktura";
        $nTotalSum = $sType != "kreditno izvestie" ? $this->document['total_sum'] : $this->document['total_sum'] * -1;
        $nTaxSum = round($nTotalSum * 5 / 6, 2);
        $nSumDDS = $nTotalSum - $nTaxSum;

        if (!isset($this->document['created_user']) || empty($this->document['created_user'])) {
            $this->document['created_user'] = 999;
        }

        $sCodeCreated = str_pad($this->document['created_user'], 10, "0", STR_PAD_LEFT);

        $_currency = "лв.";
        $_currency_100 = "ст.";
        $_dds = "20.00";
        $is_post = 0;

        $width = $this->aOptions['Fields']['num']['width']
            + $this->aOptions['Fields']['service']['width']
            + $this->aOptions['Fields']['measure']['width']
            + $this->aOptions['Fields']['quantity']['width'];

        $x = 152;

        $this->SetTextColor($this->aOptions['CaptionTextColor']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
        $this->Cell(2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'],
            $this->aOptions['CaptionHeight'], ' ', 1, 0, 'L', 1); // Словом
        $this->Ln();

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] + 2);
        $this->SetFillColor($this->aOptions['Background']['body']);

        if( $this->document['doc_type'] != "oprostena") {
            //$this->Cell($width, $this->aOptions['RowHeight'], slovom($this->document['total_sum'], $_currency, $_currency_100), 1, 0, 'L', 1);
            $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize'] + 2);
            $this->SetFillColor($this->aOptions['Background']['body']);
            $this->PrintRow($x, "Дан. основа", sprintf('%0.2f', $nTaxSum) . ' ' . $_currency, $this->aOptions['Fields']['price']['width'] ?? 0, $this->aOptions['Fields']['sum']['width'] ?? 0, 'R');
            $this->SetFillColor($this->aOptions['Background']['body']);

            $this->Cell($width, $this->aOptions['RowHeight'], '', 1, 0, 'L', 1);
            $this->SetFillColor($this->aOptions['Background']['body']);
            $this->PrintRow($x, "Начислен ДДС ", sprintf('%0.2f', $nSumDDS) . ' ' . $_currency, $this->aOptions['Fields']['price']['width'] ?? 0, $this->aOptions['Fields']['sum']['width'] ?? 0, 'R');
        }
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] + 2);

        $sNote = sprintf("",
            $_dds > 0 ?
                "" :
                (
                $is_post == 1 ?
                    "Основание за прилагане на нулева ставка - чл.49 ал.2 от ЗДДС" :
                    "Основание за прилагане на нулева ставка - чл.30 от ЗДДС"
                )
        );

        $this->Cell($width, $this->aOptions['RowHeight'], $sNote, 1, 0, 'L', 1);
        $this->SetFillColor($this->aOptions['CaptionBackgroundW']['r'], $this->aOptions['CaptionBackgroundW']['g'], $this->aOptions['CaptionBackgroundW']['b']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize'] + 2);
        $this->PrintRow($x, "Обща стойност", sprintf('%0.2f', $nTotalSum) . ' ' . $_currency, $this->aOptions['Fields']['price']['width'] ?? 0, $this->aOptions['Fields']['sum']['width'] ?? 0, 'R');

        // Кредитно/дебитно - основание
        if ( in_array($this->document['doc_type'], ["debitno izvestie", "kreditno izvestie"]) ) {
            $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] + 2);
            $this->Cell(2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'],
                $this->aOptions['RowHeight'], "Основание за издаване на известие: " . $advice_reason, 1, 0, 'L', 1);

            $this->Ln();
        }

        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);

        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetFillColor($this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->Cell(2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'], 0.5, "", 1, 0, 'L', 1);

        $width_num = $this->aOptions['Fields']['single_price']['width'] / 4;
        $width_account = 3 * $this->aOptions['Fields']['single_price']['width'] / 8;
        $width_credit = $this->aOptions['Fields']['total_sum']['width'] / 4;

        $this->Ln();
        $this->Ln();
        $this->Ln();

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        $this->Cell($width / 3, $this->aOptions['RowHeight'], "Дата на данъчно събитие : " . $this->sDocDate, 1, 0, 'L', 1);

        $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize']);
        $this->Cell($width, $this->aOptions['RowHeight'],  'Словом: '. slovom($this->document['total_sum'], $_currency, $_currency_100."          " ), 1, 0, 'R', 1);

        $this->Ln();

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        $this->Cell($width * 4 / 5, $this->aOptions['RowHeight'], " ", 1, 0, 'L', 1);

        $this->SetLineWidth($this->aOptions['BorderWidth'] / 2);
        $this->SetDrawColor($this->aOptions['Background']['body']);
        $this->SetFillColor(255);
        $this->Cell($width_num, $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Cell($width_account, $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Cell($width_account, $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Cell($width_credit, $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Cell($width_credit, $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Cell($width_credit * 2, $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Ln();

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize']);
        $this->Cell($width * 4 / 5, $this->aOptions['RowHeight'], "Получател: ", 1, 0, 'L', 1);
        $this->Cell($width / 1, $this->aOptions['RowHeight'], "Съставил: " . $sCodeCreated, 1, 0, 'L', 1);

        //$this->Cell(0, $this->aOptions['RowHeight'], "Разпечатан: " . $printed->format('d.m.Y H:i:s'), 1, 0, 'L', 1);
        //$this->Cell( $width / 2,  $this->aOptions['RowHeight'], $s, 1, 0, 'L', 1);

        $this->SetLineWidth($this->aOptions['BorderWidth'] / 2);
        $this->SetDrawColor($this->aOptions['Background']['body']);
        $this->SetFillColor(255);
        $this->Cell($width_num, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_account, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_account, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_credit, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_credit, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_credit * 2, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Ln();

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        $this->Cell($width * 4 / 5, $this->aOptions['RowHeight'], $sClientRecipient, 1, 0, 'L', 1);
        $this->Cell($width / 2, $this->aOptions['RowHeight'], "" . $sCreatedUser, 1, 0, 'L', 1);

        $this->SetLineWidth($this->aOptions['BorderWidth'] / 2);
        $this->SetDrawColor($this->aOptions['Background']['body']);
        $this->SetFillColor(255);
        $this->Cell($width_num, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_account, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_account, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_credit, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width_credit, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell($width * 2, $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Ln();

        //--------

        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetFillColor($this->aOptions['Background']['body']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $this->SetLineWidth($this->aOptions['BorderWidth']);
        $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize']);
        $this->Cell($width  * 4 / 5, $this->aOptions['RowHeight'], "                     Подпис : ", 1, 0, 'L', 1);
        $this->Cell($width / 2, $this->aOptions['RowHeight'], "                               Подпис : ", 1, 0, 'L', 1);

        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] - 3);
        $this->SetLineWidth($this->aOptions['BorderWidth'] / 2);
        $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
        $this->SetDrawColor($this->aOptions['BorderColor']);
        $price_w = $this->aOptions['Fields']['price']['width'] ?? 0;
        $sum_w = $this->aOptions['Fields']['sum']['width'] ?? 0;

        //$width_other = ($price_w + $sum_w) / 2;
        //$this->Cell($width_other, $this->aOptions['RowHeight'], "статия", 1, 0, 'L', 1);
        //$this->Cell($width_other, $this->aOptions['RowHeight'], "сч-л", 1, 0, 'L', 1);
        $this->Ln();

        // smetki
        foreach ($aBanks as $bank) {
            $this->SetTextColor($this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b']);
            $this->SetFillColor($this->aOptions['Background']['body']);
            $this->SetDrawColor($this->aOptions['BorderColor']);
            $this->SetLineWidth($this->aOptions['BorderWidth']);
            $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
            $this->Cell($width / 2, $this->aOptions['RowHeight'], $bank['name'], 1, 0, 'L', 1);
            $this->Cell($width - 9, $this->aOptions['RowHeight'], "IBAN: " . $bank['iban'] . " BIC: " . $bank['bic'], 1, 0, 'L', 1);
            $this->Ln();
        }

        $this->Ln();
    }

    private function ClearRightMargin()
    {
        $y = $this->GetY();
        $this->SetXY($this->_PageWidth - $this->aMargin['right'] + 0.4, 0);
        $this->SetFillColor(255);
        $this->Cell($this->aMargin['right'], $y, "", 0, 0, "L", 1);
    }

    private function PorccessText($sText, &$sText1, &$sText2, &$nFontSize)
    {
        do {
            $sText1 = trim($sText);
            $sText2 = '';
            $aText = explode(' ', $sText1);
            $this->SetFont('FreeSans', '', $nFontSize);

            while (($this->GetStringWidth($sText1) > 1.6 * $this->aOptions['Widths']['body'])) {

                $sText2 = array_pop($aText) . " " . $sText2;
                $sText1 = implode(" ", $aText);
            }

            if (
                ($this->GetStringWidth($sText1) < 1.6 * ($this->aOptions['Widths']['body'] - 2 * $this->aOptions['BorderWidth'])) &&
                ($this->GetStringWidth($sText2) < 1.6 * ($this->aOptions['Widths']['body'] - 2 * $this->aOptions['BorderWidth']))
            )
                break;
            else
                $nFontSize -= 0.5;
        } while (1);
    }

    public function PrintReport($nID, $sPrintType = '', $sViewType = 'single', $local_save = 0, $forAutoSign = false, $invoiceEmail = null) {
        //$oDBSaleDoc = new DBSalesDocs();
        $oInvoice = new NEWDBSalesDocs();

        //$aSaleDoc = array();
        $aAdvice = array();
        $adv_num = 0;
        $adv_date = "00.00.0000";
        $document = $oInvoice->getDocData($nID);
        $this->document = $document;
        //$oDBSaleDoc->getRecord($nID, $aSaleDoc);

        $sDocStatus = $document['doc_status'];
        $sDocType = $document['doc_type'];

        $nAdvice = isset($document['id_advice']) ? $document['id_advice'] : 0;

        if (!empty($nAdvice)) {
            //$oDBSaleDoc->getRecord($nAdvice, $aAdvice);
            $aAdvice = $oInvoice->getDocData($nAdvice);

            if (isset($aAdvice['doc_num'])) {
                $adv_num = $aAdvice['doc_num'];
                $adv_date = date("d.m.Y", strtotime($aAdvice['created_time']));
            }
        }

        $sDocDate = mysqlDateToJsDate($document['doc_date']);

        $this->sDocumentHeader = $sPrintType;
        $this->sViewType    = $sViewType;
        $this->sDocDate     = $sDocDate;
        $this->sClientID    = isset($document['id_client']) ? str_pad($document['id_client'], 8, "0", STR_PAD_LEFT) : "";
        $this->ein          = $document['deliverer_ein'] ?? 0;

        switch ($sDocStatus) {
            case 'proforma' :
                $this->sDocument = 'ПРОФОРМА';
                $this->sInvoceType = '';
                $this->sDocumentHeader = 'ТОВА НЕ Е ФАКТУРА';
                $this->advice = "";
                break;

            case 'final' || 'canceled':

                switch ($sDocType) {

                    case 'faktura':
                        $this->sDocument = 'ФАКТУРА';
                        $this->sInvoceType = '';
                        break;

                    case 'kvitanciq':
//                        $this->sDocument = 'КВИТАНЦИЯ';
                        $this->sDocument = 'ПРОФОРМА';
                        $this->sInvoceType = '';
                        break;

                    case 'kreditno izvestie':
                        $this->sDocument = "КРЕДИТНО ИЗВЕСТИЕ"; //'ФАКТУРА';
                        $this->sInvoceType = "към фактура"; //"кредитно известие";
                        $this->advice = zero_padding($adv_num, 10) . " от " . $adv_date;  //"към фактура: " .
                        break;

                    case 'debitno izvestie':
                        $this->sDocument = "ДЕБИТНО ИЗВЕСТИЕ"; //'ФАКТУРА';
                        $this->sInvoceType = "към фактура"; //"кредитно известие";
                        $this->advice = zero_padding($adv_num, 10) . " от " . $adv_date;  //"към фактура: " .
                        break;

                    /*
                    case 'debitno izvestie':
                        $this->sDocument = 'ФАКТУРА';
                        $this->sInvoceType = "дебитно известие";
                        $this->advice = "към фактура: " . zero_padding($adv_num, 10) . "/" . $adv_date;
                        break;
                    */

                    default:
                        $this->sDocument = '';
                        $this->sInvoceType = '';
                }
                break;

            default    :
                $this->sDocument = '';
                $this->sInvoceType = '';
        }

        $this->flag = false;
        $this->sDocumentHeader = "two";

        if ($this->sDocumentHeader == "two") {
            if ($document['doc_status'] == "canceled") {
                $this->sDocumentHeader = "АНУЛИРАН";
            } else {
                $this->sDocumentHeader = "ОРИГИНАЛ";
            }

            $this->flag = true;
        }

        if (!$this->copie || $forAutoSign) {
            $this->SetTitle( "Invoice ".$nID, true );

            $this->aMargin['top'] = 15;
            $this->SetMargins($this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right']);

            $this->AddPage('P');



            $this->printLogo();
            $this->printBareCode($document['doc_num']);
            $this->PrintClient();
            $this->PrintInvoiceTitle();
            $this->PrintDeliver();

            $this->Ln($this->aOptions['BorderWidth']);
            //$this->Ln();
//$this->Error(ArrayToString(FPDF_FONTPATH));
            $this->PrintTableDataHeader();
            $this->PrintTableData($nID);

            if ($this->num_rows < 20) {
                $this->printAdvert();
            }

            $this->Ln($this->aOptions['BorderWidth']);
            //$this->Ln();

            $this->PrintTotal();
            //$this->ClearRightMargin();

            //$this->SetDisplayMode('real');

            // Павел - забранявам втори екземпляр по искане на Чефо - 4.11.2013г.
            $this->flag = false;
        } else {
            $this->flag = true;
        }

        if ($this->flag) {
            if ($document['doc_status'] != "canceled") {
                $this->sDocumentHeader = "";
            }

            $this->aMargin['top'] = 25;  //15
            $this->SetMargins($this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right']);

            $this->AddPage('P');

            $this->printLogo();
            $this->printBareCode($document['doc_num']);
            $this->PrintClient();
            $this->PrintInvoiceTitle();
            $this->PrintDeliver();

            $this->Ln($this->aOptions['BorderWidth']);

            $this->PrintTableDataHeader();
            $this->PrintTableData($nID);

            $this->Ln($this->aOptions['BorderWidth']);

            $this->PrintTotal();
            $this->ClearRightMargin();

            $this->SetDisplayMode('real');
        }

        //$this->Image( $_SESSION['BASE_DIR'].'/images_adverts/adverts.png',$this->aMargin['left'], $this->aOptions['CaptionHeight'], $this->aMargin['left'] + $this->aOptions['TitleWidth'] + ($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body']) + $this->aMargin['right'] );


        if ($local_save == 1) {
            $this->Output($_SESSION['BASE_DIR'] . "/sale_docs_unsigned/" . $nID . ".pdf", 'F');
        } elseif ($forAutoSign) {
            $out_file_path = $_SESSION['BASE_DIR'] . "/tmp/" . $nID . ".pdf";
            $this->Output($out_file_path, 'F');

            $oDBClients = new DBClients();
            $oDBNotificationsEvents = new DBNotificationsEvents();

            $email = '';
            if (is_null($invoiceEmail)) {
                $aClientData = $oDBClients->getByID($document['id_client']);
                if (empty($aClientData['invoice_email'])) {
                    throw new Exception('Няма въведен Email за клиента! Не може да изратите фактура!...');
                }

                $email = $aClientData['invoice_email'];
            } else {
                $email = $invoiceEmail;
            }


            $aNotificationInvoiceData = $oDBNotificationsEvents->getTemplateByCode("invoice_sign");

            require_once "include/invoice_signer.inc.php";

            $pdfSigner = new InvoiceSigner($nID, $email, $out_file_path, $aNotificationInvoiceData['email_subject'], $aNotificationInvoiceData['email_body'], $document['id_client']);
            $pdfSigner->sign();
            return $pdfSigner->getResult();

        } else {
            $this->Output("invoice_".$nID, "I", false);
        }
    }
}

?>