<?php
	
	class ApiMissingDocuments {
		public function load( DBResponse $oResponse ) {
			$oDBFirms = new DBFirms();
			$oDBDocumetTypes = new DBDocumentTypes();
			
			$aFirms = array();
			$oDBOffices = new DBOffices();
			$aDocumentTypes = array();
			
			$aFirms = $oDBFirms->getFirms4();
			$aDocumentTypes = $oDBDocumetTypes->getDocumentTypes();
			
			$nIDOffice = $_SESSION['userdata']['id_office'];
			$nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
			$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirm);
			
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
			foreach($aFirms as $key => $value) {
				
				if( $nIDFirm == $key ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),array("selected" => "selected")), $value);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
				}
			}	
				
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Всички");
			foreach($aOffices as $key => $value) {
				if($nIDOffice == $key) {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),array("selected" => "selected")), $value);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			}
			
			$oResponse->setFormElement('form1', 'all_documents', array(), '');
			foreach ( $aDocumentTypes as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'all_documents',array('value' => $key),$val);
			}
			
			$oResponse->printResponse();
		}
		
		public function loadOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDFirm');
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if(!empty($nFirm)) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
	
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
				foreach($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse ) {
			$nIDFirm = Params::get('nIDFirm','0');
			$nIDOffice = Params::get('nIDOffice','0');
	
			$aAccountDocuments = Params::get("account_documents", '');

			if( empty($nIDFirm) ) {
				throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if( empty( $aAccountDocuments ) )
				throw new Exception("Изберете документи!", DBAPI_ERR_INVALID_PARAM);
			
			$sDocuments = implode(",",$aAccountDocuments);
			$nCountDocuments = count($aAccountDocuments);
			
			$oDBPersonDocs = new DBPersonDocs();
			$oDBPersonnel = new DBPersonnel();	
			$oDBDocumentTypes = new DBDocumentTypes();					
				
			$aPersons = array();
			$aPersonDocs = array();
			$aDocumentTypes = array();
			$aPersons2 = array();
			
			$aPersons = $oDBPersonnel->getPersons($nIDFirm,$nIDOffice);   // вземам всички служители
			$aDocumentTypes = $oDBDocumentTypes->getDocumentTypes();
			
			$dToday = time();

			foreach ($aPersons as $key => $value) {
				$expireddoc = $oDBPersonDocs->getExpiredDocByPersonID( $value['id'],$sDocuments,$dToday ); //зареждам изтеклите документи за служителя

				$aPersons2[$value['id']]['name'] = $value['name']; 		//прехвърлям ги в по удобен за работа масив
				$aPersons2[$value['id']]['position'] = $key; 
				$aPersons2[$value['id']]['office_name'] = $value['office_name']; 
				$aPersons2[$value['id']]['count'] = $nCountDocuments;        
				foreach ($aAccountDocuments as $nIDDoc) {
					$aPersons2[$value['id']][$nIDDoc] = 1;
					foreach ($expireddoc as $exp)
					{
						if($nIDDoc =  $exp['id_document'])
							$aPersons2[$value['id']][$nIDDoc] = $exp['valid_to']; //сетвам изтекъл на дата
						else
							$aPersons2[$value['id']][$nIDDoc] = 1; //сетвам липсващ
					}

				}
			}
			
			$aPersonDocs = $oDBPersonDocs->getValidPersonDocs($nIDFirm, $nIDOffice, $dToday, $sDocuments);  // вземам всички валидни документи
			
			foreach ($aPersonDocs AS $aDocs ) {
					
				if( !empty($aPersons2[$aDocs['id_person']][$aDocs['id_document']]) && $aPersons2[$aDocs['id_person']][$aDocs['id_document']] == 1 ) 
				{
					$aPersons2[$aDocs['id_person']][$aDocs['id_document']] = 0;				// отбелязвам с 0 че този документ не е липсващ
					$aPersons2[$aDocs['id_person']]['count']--;								// намеялям броя на липсващите документи за този служител
				}

			}

			$oResponse->setField("name"	, "Име", "Име");
			$oResponse->setField("office_name", "Регион","Регион");
			$oResponse->setField("documents", "Липсващи документи","Липсваши документи");
			$oResponse->setFieldLink("name", "openPerson");
			$aData = array();
			foreach ($aPersons2 as $key => $value ) {				// след като имам всичко нужно го пълня в $aData 
				if ( !empty($value['count']) ) {
					$aData[$value['position']]['id'] = $key;
					$aData[$value['position']]['name'] = $value['name'];
					$aData[$value['position']]['office_name'] = $value['office_name'];
					$aData[$value['position']]['documents'] = "";
					foreach ( $value AS $k => $v ) {
						if (is_numeric($k) AND $v==1 ) {
							$aData[$value['position']]['documents'] .= $aDocumentTypes[$k].", ";
						}
						if (is_numeric($k) AND $v > 1 ) {
							$aData[$value['position']]['documents'] .=$aDocumentTypes[$k]." изт. на ".date( 'd.m.Y',strtotime($v)).", ";
						}
					}
					$aData[$value['position']]['documents'] = trim($aData[$value['position']]['documents'],", ");
				}
			}

			$oResponse->setData( $aData );
			$oResponse->printResponse("Липсващи документи","missing_documents",false);  
		}
	}
	
?>