<?php

	$nID = isset($_GET['id']) ? $_GET['id'] : 0;
	
	if(!empty($nID)) {
		$oDBSalesDocs = new DBSalesDocs();
		
		$aSaleDoc = array();
		$nResult = $oDBSalesDocs->getRecord($nID,$aSaleDoc);
		if($nResult != DBAPI_ERR_SUCCESS) {
			//throw new Exception("Грешка при извличане на данните",$nResult);
			// Павел: Станиславе, не използвай throw в engine!!!
			print("<script>alert('Грешка при извличане на данните'); window.close();</script>");
		}
		
		switch ($aSaleDoc['doc_type']) {
			case 'kvitanciq': $sDocType = 'Квитанция';break;
			case 'faktura': $sDocType = 'Фактура';break;
			case 'kreditno izvestie': $sDocType = 'Кредитно известие';break;
			case 'debitno izvestie': $sDocType = 'Дебитно известие';break;
			default: $sDocType = '';
		}
		
		$sDocDate = isset($aSaleDoc['doc_date']) ? mysqlDateToJsDate($aSaleDoc['doc_date']) : '';

		if($aSaleDoc['doc_status'] == 'proforma') {
			$sPageCaption = 'Проформа - ';
		} else {
			$sPageCaption = '';
		}
		
		$sPageCaption .= $sDocType." № ".zero_padding($aSaleDoc['doc_num'],10)." / ".$sDocDate;
		
		$template->assign('sPageCaption',$sPageCaption);
		$template->assign('sDocStatus',$aSaleDoc['doc_status']);
		
		$nMinusSevenDays = strtotime("-7 days");
		$template->assign("nMinusSevenDays",date("Y-m-d",$nMinusSevenDays));
	}

	$template->assign("nID",$nID);
?>