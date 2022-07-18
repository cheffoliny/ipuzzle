<?php
include_once('pdfc.inc.php');
include_once('include/bg_slovom.inc.php');

class SaleDocPDF extends PDFC {
    //'CaptionBackground'	=> array('r'=>31,'g'=>28,'b'=>119),
    //'Background'	=>array('title'=>233,'body'=>233),
    var $aMargin = array( "left"=>20, "top"=>10, "right"=>10, "bottom"=>18 );
    var $aOptions = array(
        'TextColor'		=>array('r'=>0,'g'=>0,'b'=>0),
        'Background'	=>array('title'=>255,'body'=>255),
        'BorderColor'	=>250,
        'BorderWidth'	=>0.10,
        'FontSize'		=>7.2,
        'RowHeight'		=>5.45,
        'Widths'		=>array('title'=>18,'body'=>48),

        'CaptionHeight' 	=> 5,
        'CaptionFontSize' 	=> 9,
        'CaptionTextColor' 	=> 0,
        'CaptionBackground'	=> array('r'=>225,'g'=>225,'b'=>225),

        'TitleFontSize'	=> 20,
        'TitleWidth'	=> 48,

        'BarcodeWidth'	=> 30,
        'BarcodeHeight'	=> 8,

        'NumFontSize'	=> 12,

        'LogoWidth'		=> 12,

        'WaterStampTextSize'=> 50,
        'WaterStampColor' 	=> 240,

        'Fields'=>array(	// колони на таблицата с услугите/стоките
            'num'			=> array(
                'align'=>'R',
                'width'=>8,
                'Caption'=>'№'
            ),
            'service'	=> array(
                'align'=>'L',
                'width'=>102,
                'Caption'=>'Наименование на стока/услуга/обект'
            ),
            'quantity'			=> array(
                'align'=>'R',
                'width'=>15,
                'Caption'=>'кол'
            ),
            'measure'		=> array(
                'align'=>'L',
                'width'=>15,
                'Caption'=>'мярка'
            ),
            'single_price'			=> array(
                'align'=>'R',
                'width'=>20,
                'Caption'=>'ед.стойност'
            ),
            'total_sum'			=> array(
                'align'=>'R',
                'width'=>20,
                'Caption'=>'дан. основа'
            )
        )
    );

    var $sDocumentHeader;	// Тип на документа в заглавната част на разпечатката : ОРИГИНАЛ, КОПИЕ, ТОВА НЕ Е ФАКТУРА ....
    var $sDocument;			// Вид на документа : ФАКТУРА, ПРОФОРМА, КВИТАНЦИЯ
    var $sInvoceType;		// Тип на фактурата
    var $sViewType;			// Изглед на данните : единичен, по услуги, по обекти ...
    var $sDocDate;			// Дата на документа
    var $flag;
    var $advice;			// Номер/дата на известие
    var $copie = false;
//    private $num_rows = 0;
//    private $sClientID = "";
//    private $ein = "";

    public function __construct($orientation)   {
        parent::__construct($orientation);
    }


    private function printLogo( $aSaleDoc ) {
		$this->Ln(1);
        // $fEIN = $aSaleDoc['deliverer_ein'];
        $fEIN = !empty($aSaleDoc['deliverer_ein']) ? $aSaleDoc['deliverer_ein'] : "";
        $this->Image( $_SESSION['BASE_DIR'].'/images/'.$fEIN.'.png',0,3,185);
		$this->Ln(1);
		$this->Ln(1);
    }

    private function printBareCode($nDocNum) {

        $path = substr($_SERVER['HTTP_REFERER'], 0, strpos($_SERVER['HTTP_REFERER'], 'page'));
        $path = rtrim($path, '/');
//			$sContent = sprintf("%s/include/barcodes/barcode.php?code=code128&pcode=%s&text=0&height=44&resolution=1&img_type=jpg", $path, $nDocNum );
//			$this->Image( 	$sContent, 
//							$this->aMargin['left'] + $this->aOptions['TitleWidth'] + ($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body']) * 2 - $this->aOptions['BarcodeWidth'] - $this->aOptions['BorderWidth'], 
//							$this->aMargin['top'], 
//							$this->aOptions['BarcodeWidth'], 
//							$this->aOptions['BarcodeHeight'], 
//							'jpg' );		
        $this->Ln(1);
		$this->Ln(1);

    }

    private function PrintClient( $aSaleDoc ) {

        $client_name 	= $aSaleDoc['client_name'];
        $client_address = $aSaleDoc['client_address'];
        $client_EIN 	= $aSaleDoc['client_ein'];
        $client_EINDDS 	= $aSaleDoc['client_ein_dds'];
        $client_mol		= $aSaleDoc['client_mol'];


        $x= $this->GetX() ;
        $y= $this->GetY();

        // заглавие на панела
        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
        $this->Cell( $this->aOptions['Widths']['title']+$this->aOptions['Widths']['body'],  $this->aOptions['CaptionHeight'],' Получател:', 1, 0, 'L', 1);

        // съдържания на панела
        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth']  );
        $this->Ln();

        $nAdressFontSize = $this->aOptions['FontSize'];
        $this->PorccessText( $client_address, $sAddress1, $sAddress2, $nAdressFontSize );

        $nClientFontSize = $this->aOptions['FontSize'];
        $this->PorccessText( $client_name, $sClient1, $sClient2, $nClientFontSize );

        $this->SetFont('FreeSans', '', $nClientFontSize);
        $this->PrintRow( $x, 'Име', $sClient1, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, '', $sClient2, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->SetFont('FreeSans', '', $nAdressFontSize);
        $this->PrintRow( $x, 'Адрес', $sAddress1, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, '', $sAddress2, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        $this->PrintRow( $x, 'ИН ДДС', $client_EINDDS, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, 'ИН/ЕГН', $client_EIN, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, 'МОЛ', $client_mol, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );

        $this->SetXY( $x, $y );
    }

    // Печат на панела с информация за фактурата (номер, дата, тип ...)
    private function PrintInvoceTitle( $aSaleDoc ) {

        $invoice_date = $this->sDocDate;
        $created_time = substr($aSaleDoc['created_time'], 0, 10);
        $aTmp 		  = explode("-", $created_time);
        $document_num = $aSaleDoc['doc_num'];

        $nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

        if(!empty($nIDPerson)) {
            $oDBPersonnel = new DBPersonnel();
            $document_city = $oDBPersonnel->getCityName($nIDPerson);
        } else {
            $document_city = '';
        }

        $doc_len=strlen($document_num);
        $document_num = sprintf("%010s", $document_num);

        $x = $this->aMargin['left'] + $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'];
        $y	= $this->GetY();
        $this->SetXY( $x, $y );

        // заглавие на панела
        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', 'B', $this->aOptions['TitleFontSize'] );
        $this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['CaptionHeight'] + $this->aOptions['RowHeight'], $this->sDocument, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['BorderColor'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );

        $this->SetX( $x );
        $this->SetFont('FreeSans', 'B',  $this->aOptions['NumFontSize'] );
        $this->SetDrawColor( 255 );
        $this->SetFillColor( 255 );
        $this->Cell( $this->aOptions['TitleWidth'],  2*$this->aOptions['RowHeight'], "№ ".$document_num, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetX( $x );
        $this->SetDrawColor( 255 );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetFont('FreeSans', 'B',  $this->aOptions['CaptionFontSize'] );
        $this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], isset($aTmp[2]) ? "дата: ".$aTmp[2].".".$aTmp[1].".".$aTmp[0] : "дата: ".$created_time, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetX( $x );
        $this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], "място: ".$document_city, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetX( $x );
        $this->SetDrawColor( 255 );
        $this->SetFillColor( 255 );
        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetFont('FreeSans', 'B',  $this->aOptions['CaptionFontSize']);
        $this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], $this->sInvoceType, 1, 0, 'C', 1);
        $this->Ln();

        $this->SetX( $x );
        $this->SetDrawColor( 255 );
        $this->SetFillColor( 255 );
        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetFont('FreeSans', 'B',  $this->aOptions['CaptionFontSize'] - 2 );
        $this->Cell( $this->aOptions['TitleWidth'],  $this->aOptions['RowHeight'], $this->advice, 1, 0, 'C', 1);
        $this->SetXY( $x, $y );

    }

    // Печат на панела с информация за доставчика
    private function PrintDeliver( $aSaleDoc ) {

        $deliver_name = $aSaleDoc['deliverer_name'];
        $deliver_address = $aSaleDoc['deliverer_address'];
        $deliver_EIN = $aSaleDoc['deliverer_ein'];
        $deliver_EINDDS = $aSaleDoc['deliverer_ein_dds'];
        $deliver_mol = $aSaleDoc['deliverer_mol'];
        $iBan_f		= "";
        $bankName	= "";

        $x = $this->aMargin['left'] + $this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'];
        $y = $this->GetY();
        $this->SetXY( $x, $y );

        // заглавие на панела
        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize']);
        $this->Cell( $this->aOptions['Widths']['title']+$this->aOptions['Widths']['body'],  $this->aOptions['CaptionHeight'], ' Доставчик:', 1, 0, 'L', 1);

        // съдържания на панела
        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth']  );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);
        $this->Ln();

        $this->PrintRow( $x, 'Име', $deliver_name, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        //$this->PrintRow( $x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, 'Адрес ', $deliver_address, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, 'ИН ДДС', $deliver_EINDDS, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, 'ИН', $deliver_EIN, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        $this->PrintRow( $x, 'МОЛ', $deliver_mol, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );

        if ( isset($aSaleDoc['paid_type']) && ($aSaleDoc['paid_type'] == "cash") ) {

            $oFirm = new DBFirms();
            $aFirm = $oFirm->getDDSFirmByEIN( $deliver_EIN );

            // $nIDFirm	= isset($aFirms['id']) 		? $aFirms['id'] 	: 0;
            //$sFirmName	= isset($aFirms['name']) 	? $aFirms['name'] 	: "";

            if ( isset($aFirm['default_iban']) && !empty( $aFirm['default_iban'] ) ) {
                $iBan_f	= "IBAN: ".$aFirm['default_iban'];
                $bankName	= $aFirm['default_bank_name'];
            }
            if ( isset($aFirm['default_bic']) && !empty($aFirm['default_bic']) ) {
                $iBan_b = !empty($iBan_f) ? "BIC: ".$aFirm['default_bic'] : "";
            }

            $this->PrintRow( $x, 'Банка', $iBan_f, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
            $this->PrintRow( $x, '', $iBan_b.', '.$bankName, $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        } else {
            $this->PrintRow( $x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
            $this->PrintRow( $x, '', '', $this->aOptions['Widths']['title'], $this->aOptions['Widths']['body'] );
        }
    }

    // Печат на ред от таблица с 2 колони title->body
    private function PrintRow( $nLeft, $sTitle, $sBody, $nTitleWidth, $nBodyWidth, $body_align="L" ){
        $this->SetX( $nLeft );
        $this->SetFillColor( $this->aOptions['Background']['title'] );
        $this->Cell( $nTitleWidth, $this->aOptions['RowHeight'], $sTitle, "LTB", 0, 'L', 1);
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->Cell( $nBodyWidth,  $this->aOptions['RowHeight'], $sBody, "RTB", 0,  $body_align, 1);
        $this->Ln();
    }

    private function insertArrayIndex($array, $key, $new_element, $index) {
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
    private function PrintTableDataHeader() {
        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth']  );
        $this->SetFont('FreeSans', '',  $this->aOptions['CaptionFontSize'] );

        // ако е детайлна се добавя колона 'за месец'
        if ($this->sViewType == 'detail'){
            // намаля се широчината на някои от другите колони
            $this->aOptions['Fields']['measure']['width'] = 11;
            $this->aOptions['Fields']['quantity']['width'] = 8;

            $x = array( 'align'=>'R', 'width'=>14, 'Caption'=>'за месец' );
            $this->aOptions['Fields'] = $this->insertArrayIndex($this->aOptions['Fields'], 'month', $x, 2);
        }

        $this->aOptions['Fields']['service']['width']  = 2 * $this->aOptions['Widths']['title'] +
            2 * $this->aOptions['Widths']['body'] +
            $this->aOptions['TitleWidth'];

        foreach( $this->aOptions['Fields'] as $sFieldKey => $aField ) {
            if ($sFieldKey != 'service')
                $this->aOptions['Fields']['service']['width'] -= $aField['width'];
        }

        foreach( $this->aOptions['Fields'] as $aField )
        {
            $this->Cell($aField['width'], $this->aOptions['CaptionHeight'], $aField['Caption'],0,0,'C',1);
        }
        $this->Ln();
    }

    // Печат на цените по фактурата
    private function PrintTableData($nID) {

        $oDBSalesDocsRows 	= new DBSalesDocsRows();
        $oSalesDocs 		= new DBSalesDocs();
        $aSaleDoc			= array();
        $aData				= array();

        $aData 				= $oDBSalesDocsRows->getSaleDocRows($nID, $this->sViewType);
        $oSalesDocs->getRecord($nID, $aSaleDoc);

        $sType		= isset($aSaleDoc['doc_type']) 	? $aSaleDoc['doc_type'] : "faktura";

        // Печат на воден знак - ОРИГИНАЛ, КОПИЕ
        $oldY = $this->GetY();
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth']  );
        $this->SetFillColor( $this->aOptions['Background']['body'] );

        for ($i=0; $i<10; $i++ ) {
            // Рисува фона на първите 10 реда
            foreach( $this->aOptions['Fields'] as $sFieldKey => $aField ) {
                $this->Cell( $aField['width'], $this->aOptions['RowHeight'], '', 1 , 0, 'L', 1 );
            }
            $this->Ln();
        }

        $this->SetY($oldY);
        $this->SetTextColor( $this->aOptions['WaterStampColor'] );
        $this->SetFont('FreeSans', 'B', $this->aOptions['WaterStampTextSize']);
        $this->Cell( 2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'], 10 * $this->aOptions['RowHeight'], $this->sDocumentHeader,0,0,'C', 0);
        $this->SetY($oldY);


        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize']);

        $nRow=0;
        $nCounter=0;

        foreach( $aData as $aRow )	{

            $aRow['num'] = strval(++$nCounter);

            foreach( $this->aOptions['Fields'] as $sFieldKey => $aField ) {

                if($sFieldKey == 'single_price' || $sFieldKey == 'total_sum') {
                    $sData = sprintf('%0.3f лв.', $sType != "kreditno izvestie" ? $aRow[$sFieldKey] : $aRow[$sFieldKey] * -1);
                } else {
                    if ($sFieldKey == 'month') {
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
                if( $nRow < 10 )
                    $this->Cell( $aField['width'], $this->aOptions['RowHeight'], $sData, 0 , 0, $aField['align'], 0 );
                else
                    $this->Cell( $aField['width'], $this->aOptions['RowHeight'], $sData, 1 , 0, $aField['align'], 1 );
            }

            $nRow ++;
            $this->Ln();
        }

        for ($i=$nRow; $i<9; $i++ ) {
            $this->Ln($this->aOptions['RowHeight']);
        }
    }

    // Печат на долния панел с обобщени данни за фактурата
    private function PrintTotal($aSaleDoc) {

        $bank_acc 	= "";
        $s 			= "";
        $b_name		= "";

        switch ($aSaleDoc['paid_type']) {
            case 'cash':
                $sPaidType = 'в брой';
                break;
            case 'bank':
                $sPaidType = 'по банка';
                $bank_acc = $aSaleDoc['id_bank_account'];

                if ( !empty($bank_acc) ) { // Imame zadadena smetka
                    $oBank = new DBBankAccounts();
                    $aBank = $oBank->getBankAccoutById( $bank_acc );

                    if ( isset($aBank['iban']) && !empty( $aBank['iban'] ) ) {
                        $s 		= "IBAN: ".$aBank['iban'];
                        $b_name	= " към ".$aBank['name_bank'];
                    }

                    if ( isset($aBank['bic']) && !empty($aBank['bic']) ) {
                        $s = !empty($s) ? $s."   BIC: ".$aBank['bic'] : "";
                    }
                }
                break;
            default: $sPaidType = 'в брой';
        }



        $sClientRecipient = $aSaleDoc['client_recipient'];
        $nIDCreatedUser = $aSaleDoc['created_user'];

        $oDBPersonnel = new DBPersonnel();
        $sCreatedUser = $oDBPersonnel->getPersonnelNames2($nIDCreatedUser);

        $sType		= isset($aSaleDoc['doc_type']) 	? $aSaleDoc['doc_type'] : "faktura";
        $nTotalSum 	= $sType != "kreditno izvestie" ? $aSaleDoc['total_sum'] : $aSaleDoc['total_sum'] * -1;
        $nTaxSum 	= round($nTotalSum * 5/6,2);
        $nSumDDS 	= $nTotalSum - $nTaxSum;

        $_currency 	= "лв.";
        $_currency_100 = "ст.";
        $_dds 		= "20.00";

        $width =	  $this->aOptions['Fields']['num']['width']
            + $this->aOptions['Fields']['service']['width']
            + $this->aOptions['Fields']['measure']['width']
            + $this->aOptions['Fields']['quantity']['width'];

        $x2 = $width + $this->aMargin['left'];
        $x = 152;

        $this->SetTextColor( $this->aOptions['CaptionTextColor'] );
        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', 'B', $this->aOptions['CaptionFontSize'] );
        $this->Cell( 	2 * $this->aOptions['Widths']['title'] + 2 * $this->aOptions['Widths']['body'] + $this->aOptions['TitleWidth'],
            $this->aOptions['CaptionHeight'], ' обща стойност (словом): ', 1, 0, 'L', 1);
        $this->Ln();

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetDrawColor( $this->aOptions['BorderColor']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] + 2 );
        $this->SetFillColor( $this->aOptions['Background']['body']  );
        $this->Cell( $width,  $this->aOptions['RowHeight'], slovom ($aSaleDoc['total_sum'], $_currency, $_currency_100), 1, 0, 'L', 1);
        $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize'] + 2 );
        $this->PrintRow( $x, "Дан. основа", sprintf('%0.2f',$nTaxSum).' '.$_currency, $this->aOptions['Fields']['price']['width'], $this->aOptions['Fields']['sum']['width'],'R' );

        $this->Cell( $width,  $this->aOptions['RowHeight'], '', 1, 0, 'L', 1);
        $this->PrintRow( $x, "ДДС ".$_dds." %", sprintf('%0.2f',$nSumDDS).' '.$_currency, $this->aOptions['Fields']['price']['width'], $this->aOptions['Fields']['sum']['width'],'R' );

        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] + 2 );

        $sNote = sprintf("Основание за прилагане на нулева ставка - чл.%s от ЗДДС",
            $_dds > 0 	?
                "..." 		:
                (
                $is_post ==1 	?
                    "49 ал.2"		:
                    "30"
                )
        );

        $this->Cell( $width,  $this->aOptions['RowHeight'], $sNote, 1, 0, 'L', 1);

        $this->SetFont('FreeSans', 'B', $this->aOptions['FontSize'] + 2 );

        $this->PrintRow( $x, "Обща стойност", sprintf('%0.2f',$nTotalSum).' '.$_currency, $this->aOptions['Fields']['price']['width'], $this->aOptions['Fields']['sum']['width'],'R' );

        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );

        $this->SetDrawColor( $this->aOptions['BorderColor'] );
        $this->SetFillColor( $this->aOptions['CaptionBackground']['r'], $this->aOptions['CaptionBackground']['g'], $this->aOptions['CaptionBackground']['b'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->Cell( 132,  $this->aOptions['RowHeight'], "", 1, 0, 'L', 1);
        //$width
        $width_num = $this->aOptions['Fields']['single_price']['width'] / 4;
        $width_account = 3*$this->aOptions['Fields']['single_price']['width'] / 8;
        $width_credit = $this->aOptions['Fields']['total_sum']['width'] / 4;

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetDrawColor( $this->aOptions['BorderColor']  );

        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] - 1);
        $this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
        $y = $this->GetY();

        //$this->SetXY( $this->aMargin['left']+$width, $y );
        $this->SetXY( 152, $y );
        $this->Cell( $width_num*1.1,  $this->aOptions['RowHeight'] / 1.03, "№", 1, 0, 'C', 1);
        $this->Cell( $width_account*2.2,  $this->aOptions['RowHeight'] / 2, "ДЕБИТ", 1, 0, 'C', 1);

        //$this->SetXY($this->aMargin['left']+$width+$width_num, $y + $this->aOptions['RowHeight'] / 2 );
        $this->SetXY( 157.5, $y + $this->aOptions['RowHeight'] / 2 );
        $this->Cell( $width_account*1.1,  $this->aOptions['RowHeight'] / 2.1, "c/ka", 1, 0, 'C', 1);
        $this->Cell( $width_account*1.1,  $this->aOptions['RowHeight'] / 2.1, "ан.c/ka", 1, 0, 'C', 1);

        //$this->SetXY( $this->aMargin['left']+$width+$this->aOptions['Fields']['single_price']['width'] + 5, $y );
        $this->SetXY( 174, $y );
        $this->Cell( $width_account*2.2,  $this->aOptions['RowHeight'] / 2, "КРЕДИТ", 1, 0, 'C', 1);

        //$this->SetXY( $this->aMargin['left']+$width+$this->aOptions['Fields']['single_price']['width']+ 5, $y+$this->aOptions['RowHeight'] / 2 );
        $this->SetXY( 174, $y + $this->aOptions['RowHeight'] / 2 );
        $this->Cell( $width_account*1.1,  $this->aOptions['RowHeight'] / 2.1, "c/ka", 1, 0, 'C', 1);
        $this->Cell( $width_account*1.1,  $this->aOptions['RowHeight'] / 2.1, "ан.c/ka", 1, 0, 'C', 1);

        //$this->SetXY( $this->aMargin['left']+$width+$this->aOptions['Fields']['single_price']['width']+$width_credit*2+10, $y );
        $this->SetXY( 190, $y );
        $this->Cell( $width_credit*2,  $this->aOptions['RowHeight'] / 1.03, "СУМА", 1, 0, 'C', 1);
        $this->Ln();

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetDrawColor( $this->aOptions['BorderColor']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Дата на данъчното събитие : ".$this->sDocDate, 1, 0, 'L', 1);
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Форма на плащане : ".$sPaidType.$b_name, 1, 0, 'L', 1);
        $this->Cell( 0,  $this->aOptions['RowHeight'], "", 1, 0, 'L', 1);

//        $this->SetLineWidth( 0 );
//        $this->SetDrawColor( $this->aOptions['Background']['body'] );
//        $this->SetFillColor( 255 );
//        $this->Cell( $width_num / 8,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
//        $this->Cell( $width_account / 6,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
//        $this->Cell( $width_account / 4,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
//        $this->Cell( $width_credit / 8,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
//        $this->Cell( $width_credit / 8,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
//        $this->Cell( $width_credit / 8,  $this->aOptions['RowHeight'], "", 1, 0, 'C', 1);
        $this->Ln();
        $this->Ln();

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetDrawColor( $this->aOptions['Background']['body'] );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Получател (име, фамилия) : ", 1, 0, 'L', 1);
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], "Съставил : ".$s, 1, 0, 'L', 1);

        $this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
        $this->SetDrawColor( $this->aOptions['Background']['body'] );
        $this->SetFillColor( 255 );
        $this->Cell( $width_num,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_credit*2,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Ln();

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetDrawColor( $this->aOptions['Background']['body']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], $sClientRecipient, 1, 0, 'L', 1);
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], $sCreatedUser, 1, 0, 'L', 1);

        $this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
        $this->SetDrawColor( $this->aOptions['Background']['body'] );
        $this->SetFillColor( 255 );
        $this->Cell( $width_num,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_account,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_credit,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Cell( $width_credit*2,  $this->aOptions['RowHeight'], "", "LR", 0, 'C', 1);
        $this->Ln();

        //--------

        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetFillColor( $this->aOptions['Background']['body'] );
        $this->SetDrawColor( $this->aOptions['Background']['body']  );
        $this->SetLineWidth( $this->aOptions['BorderWidth'] );
        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] );
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], "                        Подпис : ", 1, 0, 'L', 1);
        $this->Cell( $width / 2,  $this->aOptions['RowHeight'], "                        Подпис : ", 1, 0, 'L', 1);

        $this->SetFont('FreeSans', '', $this->aOptions['FontSize'] - 3);
        $this->SetLineWidth( $this->aOptions['BorderWidth'] / 2 );
        $this->SetTextColor( $this->aOptions['TextColor']['r'], $this->aOptions['TextColor']['g'], $this->aOptions['TextColor']['b'] );
        $this->SetDrawColor( $this->aOptions['Background']['body'] );
        $width_other = ( $this->aOptions['Fields']['price']['width'] + $this->aOptions['Fields']['sum']['width'] ) / 2;
        $this->Cell( $width_other, $this->aOptions['RowHeight'], "статия", 1, 0, 'L', 1 );
        $this->Cell( $width_other, $this->aOptions['RowHeight'], "сч-л", 1, 0, 'L', 1 );
        $this->Ln();
        $this->Ln();
    }

    private function ClearRightMargin()
    {
        $y=$this->GetY();
        $this->SetXY( $this->_PageWidth - $this->aMargin['right']+0.4, 0 );
        $this->SetFillColor( 255 );
        $this->Cell( $this->aMargin['right'],  $y, "",0, 0, "L",1);
    }

    private function PorccessText( $sText, &$sText1, &$sText2, &$nFontSize )
    {
        do
        {
            $sText1 = trim($sText);
            $sText2 = '';
            $aText = explode( ' ', $sText1);
            $this->SetFont('FreeSans', '', $nFontSize);

            while( ($this->GetStringWidth($sText1) >  1.6 * $this->aOptions['Widths']['body']) )
            {

                $sText2 = array_pop($aText)." ".$sText2;
                $sText1 = implode( " ", $aText);
            }

            if(
                ( $this->GetStringWidth($sText1) <  1.6 * ( $this->aOptions['Widths']['body'] - 2 * $this->aOptions['BorderWidth'] ) ) &&
                ( $this->GetStringWidth($sText2) <  1.6 * ( $this->aOptions['Widths']['body'] - 2 * $this->aOptions['BorderWidth'] ) )
            )
                break;
            else
                $nFontSize -=0.5;
        }
        while (1) ;
    }

//        public function PrintReport( $nID, $sPrintType = '', $sViewType = 'single', $local_save = 0, $forAutoSign = false ) {

    public function PrintReport($nID, $sPrintType = '', $sViewType = 'single', $local_save = 0, $forAutoSign = false, $invoiceEmail = null)
    {
        $oDBSaleDoc = new DBSalesDocs();

        $aSaleDoc 	= array();
        $aAdvice	= array();
        $adv_num	= 0;
        $adv_date	= "00.00.0000";
        $oDBSaleDoc->getRecord($nID, $aSaleDoc);

        $sDocStatus = $aSaleDoc['doc_status'];
        $sDocType 	= $aSaleDoc['doc_type'];

        $nAdvice 	= isset($aSaleDoc['id_advice']) ? $aSaleDoc['id_advice'] : 0;

        if ( !empty($nAdvice) ) {
            $oDBSaleDoc->getRecord($nAdvice, $aAdvice);

            if (isset($aAdvice['doc_num'])) {
                $adv_num 	= $aAdvice['doc_num'];
                $adv_date	= date("d.m.Y", strtotime($aAdvice['doc_date']));
            }
        }

        $sDocDate = mysqlDateToJsDate($aSaleDoc['doc_date']);

//        $this->sDocumentHeader = $sPrintType;
//        $this->sViewType = $sViewType;
//        $this->sDocDate = $sDocDate;
        $this->sDocumentHeader = $sPrintType;
        $this->sViewType    = $sViewType;
        $this->sDocDate     = $sDocDate;
        $this->sClientID    = isset($aSaleDoc['id_client'])     ? str_pad($aSaleDoc['id_client'], 8, "0", STR_PAD_LEFT) : "";
        $this->ein          = isset($aSaleDoc['deliverer_ein']) ? $aSaleDoc['deliverer_ein']                            : 0;

        switch( $sDocStatus ) {

            case 'proforma' :
                $this->sDocument	= 'ПРОФОРМА';
                $this->sInvoceType	= '';
                $this->sDocumentHeader = 'ТОВА НЕ Е ФАКТУРА';
                $this->advice		= "";
                break;

            case 'final' || 'canceled':

                switch( $sDocType )	{

                    case 'faktura':
                        $this->sDocument		= 'ФАКТУРА';
                        $this->sInvoceType		= '';
                        break;

                    case 'kvitanciq':
                        $this->sDocument		= 'КВИТАНЦИЯ';
                        $this->sInvoceType		= '';
                        break;

                    case 'kreditno izvestie':
                        $this->sDocument		= 'ИЗВЕСТИЕ';
                        $this->sInvoceType		= "кредитно известие";
                        $this->advice			= "към фактура: ".zero_padding($adv_num, 10)."/".$adv_date;
                        break;

                    case 'debitno izvestie':
                        $this->sDocument	= 'ИЗВЕСТИЕ';
                        $this->sInvoceType	= "дебитно известие";
                        $this->advice		= "към фактура: ".zero_padding($adv_num, 10)."/".$adv_date;
                        break;

                    default:
                        $this->sDocument	= '';
                        $this->sInvoceType	= '';
                }
                break;

            default	:
                $this->sDocument	= '';
                $this->sInvoceType	= '';
        }

        $this->flag = false;
        $this->sDocumentHeader = "two";

        if ($this->sDocumentHeader == "two") {
            if ($aSaleDoc['doc_status'] == "canceled") {
                $this->sDocumentHeader = "АНУЛИРАН";
            } else {
                $this->sDocumentHeader = "ОРИГИНАЛ";
            }

            $this->flag = true;
        }

        if (!$this->copie || $forAutoSign) {
            $this->aMargin['top']=15;
            $this->SetMargins( $this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right'] );

            $this->AddPage('P');

            $this->printLogo($aSaleDoc);
            $this->printBareCode($aSaleDoc['doc_num']);
            $this->PrintClient($aSaleDoc);
            $this->PrintInvoceTitle($aSaleDoc);
            $this->PrintDeliver($aSaleDoc);

            $this->Ln($this->aOptions['BorderWidth']);

            $this->PrintTableDataHeader();
            $this->PrintTableData($nID);

            if ($this->num_rows < 20) {
                $this->printAdvert();
            }

            $this->Ln($this->aOptions['BorderWidth']);

            $this->PrintTotal($aSaleDoc);
            $this->ClearRightMargin();

            $this->SetDisplayMode('real');

        // забранявам втори екземпляр по искане на Чефо - 4.11.2013г.
            $this->flag = false;
        } else {
            $this->flag = true;
        }

        if ($this->flag) {
            if ($aSaleDoc['doc_status'] != "canceled") {
                $this->sDocumentHeader = "";
            }

            $this->aMargin['top']=15;
            $this->SetMargins( $this->aMargin['left'], $this->aMargin['top'], $this->aMargin['right'] );

            $this->AddPage('P');

            $this->printLogo($aSaleDoc);
            $this->printBareCode($aSaleDoc['doc_num']);
            $this->PrintClient($aSaleDoc);
            $this->PrintInvoceTitle($aSaleDoc);
            $this->PrintDeliver($aSaleDoc);

            $this->Ln($this->aOptions['BorderWidth']);

            $this->PrintTableDataHeader();
            $this->PrintTableData($nID);

            $this->Ln($this->aOptions['BorderWidth']);

            $this->PrintTotal($aSaleDoc);
            $this->ClearRightMargin();

            $this->SetDisplayMode('real');
        }

        //$this->Image( $_SESSION['BASE_DIR'].'/images_adverts/adverts.jpg',$this->aMargin['left'], $this->aOptions['CaptionHeight'], $this->aMargin['left'] + $this->aOptions['TitleWidth'] + ($this->aOptions['Widths']['title'] + $this->aOptions['Widths']['body']) + $this->aMargin['right'] );


//        if($local_save == 1) {
//            $this->Output($_SESSION['BASE_DIR']."/sale_docs_unsigned/".$nID.".pdf",'F');
//        } else {
//            $this->Output();
//        }

        if ($local_save == 1) {
            $this->Output($_SESSION['BASE_DIR'] . "/sale_docs_unsigned/" . $nID . ".pdf", 'F');
        } elseif ($forAutoSign) {
            $out_file_path = $_SESSION['BASE_DIR'] . "/tmp/" . $nID . ".pdf";
            $this->Output($out_file_path, 'F');

            $oDBClients = new DBClients();
            $oDBNotificationsEvents = new DBNotificationsEvents();

            $email = '';
            if (is_null($invoiceEmail)) {
                $aClientData = $oDBClients->getByID($aSaleDoc['id_client']);
                if (empty($aClientData['invoice_email'])) {
                    throw new Exception('Няма въведен Email за клиент! Не може да изратите фактура!'.$aSaleDoc['id_client']);
                }

                $email = $aClientData['invoice_email'];
            } else {
                $email = $invoiceEmail;
            }


            $aNotificationInvoiceData = $oDBNotificationsEvents->getTemplateByCode("invoice_sign");

            require_once "include/invoice_signer.inc.php";

            $pdfSigner = new InvoiceSigner($nID, $email, $out_file_path, $aNotificationInvoiceData['email_subject'], $aNotificationInvoiceData['email_body'], $aSaleDoc['id_client']);
            $pdfSigner->sign();
            return $pdfSigner->getResult();

        } else {
            $this->Output("sale_doc_".$nID, "I");
        }
    }
}
?>