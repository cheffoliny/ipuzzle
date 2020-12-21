<?php

	$nID = isset($_GET['id']) ? $_GET['id'] : 0;
	
	if(!empty($nID)) {
		$oDBBuyDocs = new DBBuyDocs();
		
		$aBuyDoc = array();
		
		$nResult = $oDBBuyDocs->getRecord($nID,$aBuyDoc);
		
		if($nResult != DBAPI_ERR_SUCCESS) {
			throw new Exception("Грешка при извличане на данните",$nResult);
		}
		
		switch ($aBuyDoc['doc_type']) {
			case 'kvitanciq': $sDocType = 'Квитанция';break;
			case 'faktura': $sDocType = 'Фактура';break;
			case 'oprostena': $sDocType = 'Фактура';break;
			case 'kreditno izvestie': $sDocType = 'Кредитно известие';break;
			case 'debitno izvestie': $sDocType = 'Дебитно известие';break;
			case 'dds': $sDocType = 'ДДС';break;
			case 'salary': $sDocType = 'Заплата';break;
			default: $sDocType = '';
		}
		
		$sDocDate = isset($aBuyDoc['doc_date']) ? mysqlDateToJsDate($aBuyDoc['doc_date']) : '';

		if($aBuyDoc['doc_status'] == 'proforma') {
			$sPageCaption = 'Проформа - ';
		} else {
			$sPageCaption = '';
		}
		
		$sPageCaption .= $sDocType." № ".zero_padding($aBuyDoc['doc_num'],10)." / ".$sDocDate;
		
		$template->assign('sPageCaption',$sPageCaption);
		$template->assign('sDocStatus',$aBuyDoc['doc_status']);
		
		$nMinusSevenDays = strtotime("-7 days");
		$template->assign("nMinusSevenDays",date("Y-m-d",$nMinusSevenDays));
	}

	$template->assign("nID",$nID);
?>