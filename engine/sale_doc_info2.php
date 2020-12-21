<?php

	$nID = isset($_GET['id']) ? $_GET['id'] : 0;
	$sPays = isset($_GET['make']) ? $_GET['make'] : "";
	
	if ( !empty($nID) && empty($sPays) ) {
		// Stanislav
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
	} else {
		$aData 		= array();
		$aData 		= explode(";;", $sPays);
		$aServices 	= array();
		$aJurNames 	= array();
		
		$oDBObjectServices 	= new DBObjectServices();
		$oDBObjectsSingles 	= new DBObjectsSingles();	
		$oDBClientsObjects 	= new DBClientsObjects();
		$nIDClient			= isset($_GET['client']) && is_numeric($_GET['client']) ? $_GET['client'] : 0;
		$nIDUser			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
		$_SESSION['invoice'][$nIDUser] = array();
		
		foreach ( $aData as $val ) {
			if (  strlen($val) > 5 ) {
				$str 		= "";
				$str 		= substr( $val, 4, strlen($val) - 5 );
				$aServices[]= $str;
				
				$aStrip 	= array();
				$aStrip 	= explode(",", $str);
				$sStripType = isset($aStrip[1]) ? $aStrip[1] : "";
				$nStripID 	= isset($aStrip[0]) && is_numeric($aStrip[0]) ? $aStrip[0] : 0;
				
				
				$_SESSION['invoice'][$nIDUser]['earnings'][] = array( $nStripID, $sStripType );
				
				if ($sStripType == 'single') {
					$aJur 		= $oDBObjectsSingles->getJur($nStripID);
					$sJurName 	= $aJur['jur_name'];
				} else {
					$aJur 		= $oDBObjectServices->getJur($nStripID);
					$sJurName 	= $aJur['jur_name'];
				}
				//debug($aJur);	
				//$_SESSION['invoice'][$nIDUser]['deliver'] = $aJur;
				$_SESSION['invoice'][$nIDUser]['sale_doc']['deliverer_name'] 	= isset($aJur['jur_name']) ? 	$aJur['jur_name'] : "";
				$_SESSION['invoice'][$nIDUser]['sale_doc']['deliverer_address'] = isset($aJur['address']) ? 	$aJur['address'] : "";
				$_SESSION['invoice'][$nIDUser]['sale_doc']['deliverer_ein'] 	= isset($aJur['idn']) ? 		$aJur['idn'] : "";
				$_SESSION['invoice'][$nIDUser]['sale_doc']['deliverer_ein_dds'] = isset($aJur['idn_dds']) ? 	$aJur['idn_dds'] : "";
				$_SESSION['invoice'][$nIDUser]['sale_doc']['deliverer_mol'] 	= isset($aJur['jur_mol']) ? 	$aJur['jur_mol'] : "";
				$_SESSION['invoice'][$nIDUser]['sale_doc']['id_office_dds'] 	= isset($aJur['dds']) ? 		$aJur['dds'] : 0;
				
				// Prowerka za unikalnost na firmite - trqbva da e edna
				$aJurNames[$sJurName] = 1;				
			}
			
			//debug($aJur);
		}
		
		if ( empty($nIDClient) ) {
			
			list( $nServiceID, $sServiceType ) = explode( ",", $aServices[0] );
				
			if ( $sServiceType == 'single' ) {						
				$aService = $oDBObjectsSingles->getRecord( $nServiceID );
			} else {
				$aService = $oDBObjectServices->getRecord( $nServiceID );
			}
					
			//debug($aService);
			$nIDClient = $oDBClientsObjects->getIDClientByIDObject( $aService['id_object'] );
		}		
		
		$_SESSION['invoice'][$nIDUser]['sale_doc']['id_client'] = $nIDClient;
		//$_SESSION['invoice'][$nIDUser]['nIDClient'] = $nIDClient;
				
		if ( count($aJurNames) > 1 ) {
			$aJurNames2 = array();
					
			foreach ( $aJurNames as $key => $value ) {
				$aJurNames2[] = $key;
			}
				
			unset($key);
			unset($value);
									
			$sJurNames = implode(",", $aJurNames2);
			
			$_SESSION['invoice'][$nIDUser] = array();
			print("<script>alert('Не може да издадете фактура с различни доставчици!!!\nЮридически лица: ".$sJurNames."'); window.close();</script>");
		}		
		
		$sPageCaption 		= 'Проформа - 0000000000/'.date("d.m.Y");
		$nMinusSevenDays 	= strtotime("-7 days");
		
		$template->assign( "sPageCaption", 		$sPageCaption);
		$template->assign( "nMinusSevenDays", 	date("Y-m-d", $nMinusSevenDays) );	
		$template->assign( "new", 				1);	
		//debug($_SESSION['invoice']);
	}

	$template->assign("nID", $nID);
?>