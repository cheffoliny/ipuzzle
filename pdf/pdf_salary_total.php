<?php
include_once('pdf.inc.php');

class stPDF extends PDF
{
    function __construct($orientation)
    {
        parent::__construct($orientation);
    }

    function PrintReport( DBResponse $oResponse, $sDocumentTitle, $sFileName = 'doc', $sDestination = '') {

        $aResult = array();
        $aResult['fields'] = $oResponse->oResult->aFields;
        $aResult['data'] = $oResponse->oResult->aData;
        $aResult['data_attributes'] = $oResponse->oResult->aDataAttributes;

        unset($aResult['fields']['person_object_name']);
        unset($aResult['fields']['#']);

        $aWidths['#'] = 5;
        $aWidths['person_code'] = 15;
        $aWidths['person_name'] = 40;
        $aWidths['person_firm_name'] = 45;
        $aWidths['earnings'] = 15;
        $aWidths['expense'] = 15;
        $aWidths['to_get'] = 15;
        $aWidths['oncart'] = 15;
        $aWidths['total_sum'] = 15;
        $aWidths['month'] = 15;

        $oField = new DBField();
        $oField->sCaption	= '     #     ';
        $oField->aAttributes = array('align' => 'right');

        $aResult['fields'] = array_merge(array('#'=>$oField), $aResult['fields']);

        $this->SetTitle( $sDocumentTitle, true );
        $this->SetFont('FreeSans', '', 8);

        //$aWidths = $this->MakeColWidth( $aResult, $this->_PageWidth - $this->aMargin['left'] - $this->aMargin['right']);

        $this->AddPage();
        $this->PrintTableHeader( $aResult['fields'], $aWidths );
        $this->PrintTableData( $aResult, $aWidths );

        unset($aResult['fields']['person_code']);

        $aResult['fields']['month'];
        $aResult['fields']['month']->sCaption = "месец";


        $this->AddPage();
        $this->PrintTableData2( $aResult, $aWidths );

        /*
        $this->Ln(5);
        foreach ($aWidths as $key => $value ) {
            $this->Cell('20','',$key);
            $this->Cell('20','',$value);
            $this->Ln(4);
        }

        $this->Ln(5);
        foreach ($aResult['fields'] AS $key => $value ) {
            $this->cell('20','',$key);
            $this->Ln(5);
            foreach ($value as $k => $v) {
                $this->Ln(3);
                $this->cell('20','',$k);
                $this->Ln(3);
                $this->cell('20','',$v);
            }
        }
        */
        //$this->SetDisplayMode('real');
        $this->Output();
    }


    function PrintTableData2($aData, $aWidths) {
        $this->SetTextColor( $this->aTables['Rows']['TextColor'] );
        $this->SetDrawColor( $this->aTables['Rows']['BorderColor'] );
        $this->SetLineWidth( $this->aTables['Rows']['BorderWidth']  );
        $this->SetFont('FreeSans', '', $this->aTables['Rows']['FontSize']);

        $aTotalFields = array();
        $nColorIndex=0;
        $nRowNum = 0;

        foreach( $aData['data'] as $nKeyRow => $aRow )
        {

            $this->PrintTableHeader( $aData['fields'], $aWidths );
            $this->SetFillColor( 240 , 240 , 240 );
            $nRowNum++;

            foreach( $aData['fields'] as $sFieldKey => $aField )
            {
                if( $aData['fields'][$sFieldKey]->sCaption != '...' )
                {
                    $nWidth = isset($aWidths[$sFieldKey]) ?  $aWidths[$sFieldKey] : 30;
                    if($nWidth>0)
                    {
                        $sCell = isset($aRow[$sFieldKey]) ? $aRow[$sFieldKey] : '';
                        if( ! empty($aField -> sImg) )
                        {
                            if( $aRow[$sFieldKey] == '0' || $aRow[$sFieldKey] == '' )
                            {
                                $aRow[$sFieldKey] = '';
                            }
                            elseif ( $aRow[$sFieldKey] == '1' )
                            {
                                $aRow[$sFieldKey] = 'да';
                            }
                        }
                        $sAllign = 'L';
                        $sContent 		= $aRow[$sFieldKey];

                        // Полето трябва ли да е тотал
                        if( isset( $aField->aAttributes['DATA_TOTAL'] ) && $aField->aAttributes['DATA_TOTAL'] )
                        {
                            if( empty($aTotalFields[$sFieldKey]) )
                                $aTotalFields[$sFieldKey] = 0;
                            $aTotalFields[$sFieldKey] += $sContent;
                        }

                        if( !empty( $aField->aAttributes['DATA_FORMAT']) )
                        {
                            $this->FormatCell( $aField->aAttributes['DATA_FORMAT'], $sContent, $sAllign );
                        }

                        if( isset($aField->aAttributes['align']) )
                            switch( $aField->aAttributes['align'] )
                            {
                                case 'right'  : $sAllign = 'R'; break;
                                case 'center' : $sAllign = 'C'; break;
                            }

                        $sBold = '';
                        if( isset($aField->aAttributes['style']) )
                            if( $aField->aAttributes['style'] == 'font-weight:bold' )
                                $sBold = 'B';

                        if( $sFieldKey == '#' )
                            $sContent = $nRowNum;

                        $this->SetFont('FreeSans', $sBold, $this->aTables['Rows']['FontSize']);
                        $this->Cell($nWidth, $this->aTables['Rows']['Height'], $sContent, 1, 0, $sAllign, 1);
                    }
                }
            }
            // Изтрива остатъка от съдържанието на послдната колона за да се получи margin-right
            $this->SetFillColor( 255 );
            $this->Cell($this->_PageWidth, $this->aTables['Rows']['Height'], '',"L",0,'L',1);
            $this->Ln();
            $nColorIndex = ($nColorIndex+1) % count( $this->aTables['Rows']['Background'] );

            $this->Ln(2);
            $this->SetDrawColor(0,0,0);
            $this->dottedLine(180);
            $this->Ln(2);

        }

        $this->Ln();

    }
}
?>