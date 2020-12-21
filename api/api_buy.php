<?php
	class ApiBuy {
		
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}		
		
		public function load(DBResponse $oResponse) {
			
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			
			$nIDOffice = isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office'] : '0';
			$nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
			$aFirm = $oDBFirms->getRecord($nIDFirm);
			
			$aDeliverers = $oDBFirms->getDeliverers();
			
			$oResponse->setFormElement('form1','client_name');
			$oResponse->setFormElementChild('form1','client_name',array("value" => ''),"---Всички---");
			foreach ($aDeliverers as $value) {
				if($value['deliverer'] == $aFirm['jur_name']) {
					$oResponse->setFormElement('form1','client_name',array('value' => $value['deliverer'], 'selected' => 'selected'),$value['deliverer']);
				}
				$oResponse->setFormElementChild('form1','client_name',array('value' => $value['deliverer']),$value['deliverer']);
			}
			
			
			// СЕКЦИЯ ЗАПЛАТИ
			
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement('form1','nIDFirmSalary');
			$oResponse->setFormElementChild('form1','nIDFirmSalary',array("value" => ''),'---Всички---');

			foreach ($aFirms as $key => $value) {
				$oResponse->setFormElementChild('form1','nIDFirmSalary',array("value" => $key),$value);
			}
			
			$oResponse->setFormElement('form1','nIDOfficeSalary');
			$oResponse->setFormElementChild('form1','nIDOfficeSalary',array("value" => ''),'---Всички---');
	
			// СЕКЦИЯ ГОРИВО
			
			$oResponse->setFormElement('form1','nIDFirmFuel');
			$oResponse->setFormElementChild('form1','nIDFirmFuel',array("value" => ''),'---Всички---');

			foreach ($aFirms as $key => $value) {
				$oResponse->setFormElementChild('form1','nIDFirmFuel',array("value" => $key),$value);
			}
			
			$oResponse->setFormElement('form1','nIDOfficeFuel');
			$oResponse->setFormElementChild('form1','nIDOfficeFuel',array("value" => ''),'---Всички---');
			
			// -------------------
			
			$oDBBuyDocs = new DBBuyDocs();
			
			$aBuyDoc = array();
			$aBuyDoc['to_arc'] = 1;
			
			$oDBBuyDocs->update($aBuyDoc);
			$oResponse->setFormElement('form1','id_buy_doc',array(),$aBuyDoc['id']);
			
			
			$oResponse->printResponse();
		}
		
		public function loadOfficesSalary(DBResponse $oResponse) {
			$nIDFirmSalary = Params::get('nIDFirmSalary','');
			
			$oResponse->setFormElement('form1','nIDOfficeSalary');
			$oResponse->setFormElementChild('form1','nIDOfficeSalary',array("value" => ''),'---Всички---');
			
			if(!empty($nIDFirmSalary)) {
				$oDBOffices = new DBOffices();
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirmSalary);
				
				foreach ($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDOfficeSalary',array("value" => $key),$value);
				}
			} 
			
			$oResponse->printResponse();
		}
		public function loadOfficesFuel(DBResponse $oResponse) {
			$nIDFirmFuel = Params::get('nIDFirmFuel','');
			
			$oResponse->setFormElement('form1','nIDOfficeFuel');
			$oResponse->setFormElementChild('form1','nIDOfficeFuel',array("value" => ''),'---Всички---');
			
			if(!empty($nIDFirmFuel)) {
				$oDBOffices = new DBOffices();
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirmFuel);
				
				foreach ($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDOfficeFuel',array("value" => $key),$value);
				}
			} 
			
			$oResponse->printResponse();
		}
		
		public function loadInventoryDDS(DBResponse $oResponse) {
			$nIDBuyDoc = Params::get('id_buy_doc','0');
			$sJurName = Params::get('client_name','');
			$sMonth = Params::get('dateDDS','');
			
			if(empty($sJurName)) {
				throw new Exception("Не е избрана опция в полето 'За сметка на'");
			}
			
			if(!empty($sMonth)) {
				list($m,$y) = explode(".",$sMonth);
				$sMonth = $y.$m;
			} else {
				throw new Exception("Няма избран месец");
			}
			
			$oDBBuyDocs = new DBBuyDocs();
			$oDBSalesDocsRows = new DBSalesDocsRows();
			$oDBBuyDocsRows = new DBBuyDocsRows();
			$oDBOffices = new DBOffices();
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			
			$oDBBuyDocsRows->delBuyIDBuyDoc($nIDBuyDoc);
			
			$aOffices = $oDBOffices->getIDOfficesByJurName($sJurName);
			$nIDNomenclatureDDS = $oDBNomenclaturesExpenses->getIDDDSNomenclature();

			foreach ($aOffices as $value) {
				$nSumDDSPlus = 0;
				$nSumDDSMinus = 0;
				
				$nSumDDSPlus = $oDBSalesDocsRows->sumDDS($sMonth,$value['id_office']);
				$nSumDDSMinus = $oDBBuyDocsRows->sumDDS($sMonth,$value['id_office']);
				
				$nSumDDS = $nSumDDSPlus - $nSumDDSMinus;
				
				if($nSumDDS != 0) {
					
					$aBuyDocRow = array();
					$aBuyDocRow['id_buy_doc'] = $nIDBuyDoc;
					$aBuyDocRow['id_nomenclature_expense'] = $nIDNomenclatureDDS;
					$aBuyDocRow['id_office'] = $value['id_office'];
					$aBuyDocRow['month'] = $y."-".$m."-01";
					$aBuyDocRow['single_price'] = $nSumDDS;
					$aBuyDocRow['quantity'] = 1;
					$aBuyDocRow['total_sum'] = $nSumDDS;
					
					$oDBBuyDocsRows->update($aBuyDocRow);
				}
			}

			$aBuyDoc = array();
			$aBuyDoc['id'] = $nIDBuyDoc;
			$aBuyDoc['doc_type'] = 'dds';
			$oDBBuyDocs->update($aBuyDoc);
			
			$this->result($oResponse);
			
		}
		
		public function loadInventory(DBResponse $oResponse) {

			$nIDBuyDoc = Params::get('id_buy_doc','0');
			$nIDFirmSalary = Params::get('nIDFirmSalary','');
			$nIDOfficeSalary = Params::get('nIDOfficeSalary','');
			$nIDObjectSalary = Params::get('nIDObjectSalary','');
			$nIDPersonSalary = Params::get('nIDPersonSalary','');
			$sMonth = Params::get('dateSalary','');
		
			if(!empty($sMonth)) {
				list($m,$y) = explode('.',$sMonth);
				$sMonth = $y.$m;
			}
			
			$oDBBuyDocs = new DBBuyDocs();
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->delBuyIDBuyDoc($nIDBuyDoc);	
			
			$aParams = array();
			$aParams['id_buy_doc'] = $nIDBuyDoc;
			$aParams['nIDFirm'] = $nIDFirmSalary;
			$aParams['nIDOffice'] = $nIDOfficeSalary;
			$aParams['nIDObject'] = $nIDObjectSalary;
			$aParams['nIDPerson'] = $nIDPersonSalary;
			$aParams['sMonth'] = $sMonth;
			
			$oDBBuyDocsRows->insertSalary($aParams);
			
			$aBuyDoc = array();
			$aBuyDoc['id'] = $nIDBuyDoc;
			$aBuyDoc['doc_type'] = 'salary';
			$oDBBuyDocs->update($aBuyDoc);
			
			$this->result($oResponse);
			
		}
		
		public function loadInventoryGSM(DBResponse $oResponse) {
			
			$nIDBuyDoc = Params::get('id_buy_doc','0');
			$sMonth = Params::get('dateGSM','');
			
			if(!empty($sMonth)) {
				list($m,$y) = explode('.',$sMonth);
				$sMonth = $y.$m;
			}
			
			$oDBBuyDocs = new DBBuyDocs();
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->delBuyIDBuyDoc($nIDBuyDoc);
			
			$aParams = array();
			$aParams['id_buy_doc'] = $nIDBuyDoc;
			$aParams['sMonth'] = $sMonth;
			
			$oDBBuyDocsRows->insertSalaryGSM($aParams);
			
			$aBuyDoc = array();
			$aBuyDoc['id'] = $nIDBuyDoc;
			$aBuyDoc['doc_type'] = 'gsm';
			$oDBBuyDocs->update($aBuyDoc);
			
			$this->result($oResponse);
			
		}
		
		public function result(DBResponse $oResponse) {
			
			$nIDBuyDoc = Params::get('id_buy_doc','');
			
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->getReportBuy($oResponse,$nIDBuyDoc);
			
			$nTotalSum = $oDBBuyDocsRows->sumSum($nIDBuyDoc);
			
			$oResponse->setFormElement('form1','total_sum',array(),sprintf('%0.2f',$nTotalSum));
			
			$oResponse->printResponse();
		}
		
		public function del_rows(DBResponse $oResponse) {
			$nIDBuyDoc = Params::get('id_buy_doc');
			
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->delBuyIDBuyDoc($nIDBuyDoc);
			
			$this->result($oResponse);
		}
		
		public function edit_inventory(DBResponse $oResponse) {
			$nIDBuyDoc = Params::get('id_buy_doc',0);
			$nNewSum = Params::get('total_sum',0);
			
			$nNewSum = floatval($nNewSum);
		
			if( $nNewSum <= 0) {
				throw new Exception("Въведете положително число");
			}
				
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$nOldSum = $oDBBuyDocsRows->sumSum($nIDBuyDoc);
			$nOldSum = floatval($nOldSum);			
			
			$aBuyDocRows = $oDBBuyDocsRows->getRowsByIDBuyDoc($nIDBuyDoc);
			
			foreach ($aBuyDocRows as $aBuyDocRow) {
				$aBuyDocRow['total_sum'] = ($aBuyDocRow['total_sum'] / $nOldSum) * $nNewSum;
				$aBuyDocRow['single_price'] = $aBuyDocRow['total_sum'] / $aBuyDocRow['quantity'];
				$oDBBuyDocsRows->update($aBuyDocRow);
			}
			
			$this->result($oResponse);
			
		}
		
		public function del_row(DBResponse $oResponse) {
			
			$nIDRowToDel = Params::get('id_row_to_del','');
			
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->delete($nIDRowToDel);
			
			$oResponse->printResponse();
		}
		
		public function confirm( DBResponse $oResponse )
		{
			//Objects
			$oDBFirms 		= new DBFirms();
			$oDBBuyDocs 	= new DBBuyDocs();
			$oDBBuyDocsRows = new DBBuyDocsRows();
			$oDBSalary 		= new DBSalary();
			$oDBSaldo 		= new DBSaldo();
			//End Objects
			
			//Params
			$nIDBuyDoc 		= Params::get( "id_buy_doc", 	"");
			$sDocDate 		= Params::get( "sDocDate", 		"");
			$nDocNum 		= Params::get( "doc_num", 		"0");			
			$sExpenseType 	= Params::get( "expense_type", 	"");
			$sDocType 		= Params::get( "doc_type", 		"");
			$sJurName 		= Params::get( "client_name", 	"");
			
			$nIDPerson = isset( $_SESSION["userdata"]["id_person"] ) ? $_SESSION["userdata"]["id_person"] : 0;
			//End Params
			
			//Validation
			$aBuyDocFirms = $oDBBuyDocsRows->getBuyDocFirmsTotalSum( $nIDBuyDoc );
			
			if( !empty( $aBuyDocFirms ) )
			{
				foreach( $aBuyDocFirms as $aBuyDocFirm )
				{
					if( !$oDBSaldo->checkFirmBalance( $aBuyDocFirm['id'], $aBuyDocFirm['total_sum'] ) )
					{
						throw new Exception( "Недостатъчна наличност по текущото салдо на фирма " . $aBuyDocFirm['name'] . "!" );
					}
				}
			}
			
			if( $sExpenseType == "salary" || $sExpenseType == "dds" )
			{
				$sDocType = $sExpenseType;
				$sDocStatus = "final";
				if( $sExpenseType == "salary" )
				{
					$nDocNum = 999;
				}
				elseif( $sExpenseType == "dds" )
				{
					$nDocNum = 333;
				}
			}
			else
			{
				if( empty( $sDocType ) )
				{
					throw new Exception( "Изберете вид на документа" );
				}
				
				if( $sDocType == "kvitanciq" )
				{
					$sDocStatus = "final";
				}
				else
				{
					if( empty( $nDocNum ) )
					{
						throw new Exception( "Въведете номер за документа" );
					}
					
					$sDocStatus = "proforma";
				}
			}
			
			if( strlen( $sDocDate ) != 10 )
			{
				throw new Exception( "Въведете дата на документа" );
			}
			//End Validation
			
			$nDocDate = jsDateToTimestamp( $sDocDate );
			$sDocDate = date( "Y-m-d", $nDocDate );
			
			$nTotalSum = $oDBBuyDocsRows->sumSum( $nIDBuyDoc );
			$aFirm = $oDBFirms->getByJurName( $sJurName );
			
			$aBuyDoc = array();
			$aBuyDoc["id"] = $nIDBuyDoc;
			$aBuyDoc["doc_num"] = $nDocNum;
			$aBuyDoc["doc_date"] = $sDocDate;
			$aBuyDoc["doc_type"] = $sDocType;
			$aBuyDoc["doc_status"] = $sDocStatus;
			$aBuyDoc["total_sum"] = $nTotalSum;
			
			$aBuyDoc["client_name"] = $aFirm["jur_name"];
			$aBuyDoc["client_address"] = $aFirm["address"];
			$aBuyDoc["client_ein"] = $aFirm["idn"];
			$aBuyDoc["client_ein_dds"] = $aFirm["idn_dds"];
			$aBuyDoc["client_mol"] = $aFirm["jur_mol"];
			$aBuyDoc["created_user"] = $nIDPerson;
			$aBuyDoc["created_time"] = time();
			
			$aBuyDoc["to_arc"] = 0;
			
			$oDBBuyDocs->update($aBuyDoc);
			
			/* Ако документа е от тип Заплата, то променям изплатената сума по наработките за служители участващи в документа */
			
			if( $sDocType == "salary" )
			{
				$aBuyDocRows = array();
				$aBuyDocRows = $oDBBuyDocsRows->getRowsByIDBuyDoc( $nIDBuyDoc );
				
				foreach( $aBuyDocRows as $aBuyDocRow )
				{
					$aSalaryRow = array();
					if( !empty( $aBuyDocRow["id_salary_row"] ) )
					{
						$aSalaryRow = $oDBSalary->getRecord( $aBuyDocRow["id_salary_row"] );
						$aSalaryRow["paid_sum"] += $aBuyDocRow["total_sum"];
						$aSalaryRow["last_paid_date"] = $nDocDate;
						$oDBSalary->update( $aSalaryRow );
					}
				}
			}
			
			if( $sDocStatus == "proforma" )
			{
				$oResponse->setFormElement( "form1", "open_buy_doc", array(), "true" );
			}
			else
			{
				$oResponse->setFormElement( "form1", "open_order", array(), "true" );
			}
			
			$oResponse->printResponse();
		}
		
		// remote method
		public function test(DBResponse $oResponse) {
			if ($oResponse){
				/*
				$filename = "test.txt";
				$content = ArrayToString( Params::getInstance()->toArray(), 1 );

				if ( !$handle = fopen($filename, "w+") ) {
					exit;
				}
				if ( fwrite($handle, $content) === FALSE ) {
					exit;
				}
				*/
				$gg = Params::get('dds_for_payment');
			}
			else
				$gg = 'err';

			$oResponse->printResponse();
		}
	
		// remote method
		public function init( DBResponse $oResponse ) {
	
			// factura id
			$nID 		= Params::get('id', 0);
			$nIDUser 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oFirms 	= new DBFirms();
			$oEarnings 	= new DBNomenclaturesEarnings();
			$oBuyDoc 	= new DBBuyDocs();
			$oBankAcc 	= new DBBankAccounts();
			$oDirection	= new DBDirections();
			$oBuyDocRows= new DBBuyDocsRows();
			$oCashier	= new DBCashiers();
			
			$aDirection = array();
			$aOrders	= array();
			$aRows 		= array();
			$aBuyDocRows= array();
			$aBuyDoc	= array();
			$aBankAcc	= array();
			$aCashier	= array();
			$aFirms 	= array();
			$aFirms2 	= array();
			$aEarnings	= array();
			$aData		= array();  		// Масив с данните за фактурата, ако има такива
			$nID		= strval($nID);
			
			$oResponse->SetHiddenParam( "nID", $nID );
				
			
			// Инициализираме масива сис сесията, ако не съществува
			if ( !isset($_SESSION['userdata']['access_right_levels']) ) {
				$_SESSION['userdata']['access_right_levels'] = array();
			}
			
			$aFirms = $oFirms->getFirmsAsClient();
			
			// Списък с ордерите - ко ШЪ гу прайм - не знъм
			$aOrders = $oBuyDocRows->getOrdersByDoc( $nID );			
			
			if ( !empty($nID) ) {
				$aBuyDoc 	 = $oBuyDoc->getDoc( $nID );
				
				// ДДС
				$aDDS		= array();
				$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	
	
				// Списък с описа
				$aBuyDocRows = $oBuyDocRows->getRowsByDoc( $nID );	
				
				// Списък с ордерите
				$aOrders 	 = $oBuyDocRows->getOrdersByDoc( $nID );
				
				if ( isset($aBuyDoc['client_name']) && !empty($aBuyDoc['client_name']) ) {
					$aTemp = array();
					$aTemp[0]['name'] 		= $aBuyDoc['client_name'];
					$aTemp[0]['title'] 		= "---";
					$aTemp[0]['address'] 	= isset($aBuyDoc['client_address']) ? $aBuyDoc['client_address'] : "";
					$aTemp[0]['idn'] 		= isset($aBuyDoc['client_ein']) 	? $aBuyDoc['client_ein'] 	 : "";
					$aTemp[0]['idn_dds'] 	= isset($aBuyDoc['client_ein_dds']) ? $aBuyDoc['client_ein_dds'] : "";
					$aTemp[0]['jur_mol'] 	= isset($aBuyDoc['client_mol']) 	? $aBuyDoc['client_mol'] 	 : "";
					
					$aFirms = array_merge($aTemp, $aFirms);
				}
				
				$sDate = isset($aBuyDoc['doc_date']) ? $aBuyDoc['doc_date'] : date("Y-m-d");
				
				$aData['id']				= $nID; 
				$aData['doc_num'] 			= isset($aBuyDoc['doc_num']) 				? zero_padding($aBuyDoc['doc_num'], 10) : "0000000000";
				$aData['doc_date'] 			= $sDate;
				$aData['doc_type'] 			= isset($aBuyDoc['doc_type']) 				? $aBuyDoc['doc_type'] 					: "faktura";
				$aData['doc_status'] 		= isset($aBuyDoc['doc_status']) 			? $aBuyDoc['doc_status'] 				: "final";
				$aData['client_recipient'] 	= isset($aBuyDoc['client_recipient']) 		? $aBuyDoc['client_recipient'] 			: "";
				$aData['id_deliverer'] 		= isset($aBuyDoc['id_deliverer']) 			? $aBuyDoc['id_deliverer'] 				: 0;
				$aData['deliverer_name'] 	= isset($aBuyDoc['deliverer_name']) 		? $aBuyDoc['deliverer_name'] 			: "";
				$aData['deliverer_address'] = isset($aBuyDoc['deliverer_address']) 		? $aBuyDoc['deliverer_address'] 		: "";
				$aData['deliverer_ein'] 	= isset($aBuyDoc['deliverer_ein']) 			? $aBuyDoc['deliverer_ein'] 			: "";
				$aData['deliverer_ein_dds'] = isset($aBuyDoc['deliverer_ein_dds']) 		? $aBuyDoc['deliverer_ein_dds'] 		: "";
				$aData['deliverer_mol'] 	= isset($aBuyDoc['deliverer_mol']) 			? $aBuyDoc['deliverer_mol'] 			: "";
				$aData['total_sum'] 		= isset($aBuyDoc['total_sum']) 				? $aBuyDoc['total_sum'] 				: 0;
				$aData['paid_type'] 		= isset($aBuyDoc['paid_type']) 				? $aBuyDoc['paid_type'] 				: "cash";
				
				if ( isset($aDDS[0]['payed']) ) {
					$aData['dds_payed'] 	= $aDDS[0]['payed'] == 1 ? true : false;
				} else {
					$aData['dds_payed'] 	= false;
				}
			} else {
				$aData['id'] 				= 0;
				$aData['doc_date'] 			= date("Y-m-d");
				$aData['doc_type'] 			= "faktura";
				$aData['doc_status']		= "final";
				$aData['paid_type'] 		= "cash";
				$aData['dds_sum'] 			= 0;
				$aData['dds_payed'] 		= false;
				$aData['dds_for_payment'] 	= true;
			}
			
			$aData['buy_doc_view'] 		= in_array('buy_doc_view', $_SESSION['userdata']['access_right_levels']) ? true : false;
			
			// При право за редакция - добавяме и право за преглед
			if ( in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
				$aData['buy_doc_view']	= true;
				$aData['buy_doc_edit'] 	= true;
			} else {
				$aData['buy_doc_edit'] 	= false;
			}
			
			// При пълно право за редакция - добавяме и право за преглед и редакция
			if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$aData['buy_doc_view']	= true;
				$aData['buy_doc_edit'] 	= true;
				$aData['buy_doc_grant'] = true;
			} else {
				$aData['buy_doc_grant']	= false;
			}
	
			$aData['buy_doc_order_view'] = in_array('buy_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $aData['buy_doc_view'] ? true : false;
			
			// При право за редакция - добавяме и право за преглед
			if ( in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $aData['buy_doc_view'] ) {
				$aData['buy_doc_order_view'] = true;
				$aData['buy_doc_order_edit'] = true;
			} else {
				$aData['buy_doc_order_edit'] = false;
			}
				
			if ( !$aData['buy_doc_view'] ) {
				$aData 						= array();
				
				$aData['id'] 				= 0;
				$aData['doc_date'] 			= date("Y-m-d");
				$aData['doc_type'] 			= "faktura";
				$aData['paid_type'] 		= "cash";
				$aData['dds_sum'] 			= 0;
				$aData['dds_payed'] 		= false;
				$aData['dds_for_payment'] 	= true;
							
				$aData['buy_doc_view']		 = false;
				$aData['buy_doc_edit'] 		 = false;
				$aData['buy_doc_grant'] 	 = false;	
				$aData['buy_doc_order_view'] = false;
				$aData['buy_doc_order_edit'] = false;
			}
			
			$aData['user_id'] 			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
			$aData['user_name'] 		= isset($_SESSION['userdata']['name']) 		? $_SESSION['userdata']['name']  		: "";
			$aData['user_office_id'] 	= isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office']  	: 0;
			$aData['user_office_name'] 	= isset($_SESSION['userdata']['region']) 	? $_SESSION['userdata']['region']  		: "";
			$aData['user_uname'] 		= isset($_SESSION['userdata']['username']) 	? $_SESSION['userdata']['username']  	: "";
			$aData['id_schet_account'] 	= isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account']  : 0;
			$aData['user_row_limit'] 	= isset($_SESSION['userdata']['row_limit']) ? $_SESSION['userdata']['row_limit']  	: 0;
			$aData['user_has_debug'] 	= isset($_SESSION['userdata']['has_debug']) ? $_SESSION['userdata']['has_debug']  	: 0;		
			//$oResponse->setAlert(ArrayToString($aData));

			$aCashier 	= $oCashier->getByIDPerson($nIDUser);
			
			if ( isset($_SESSION['userdata']['cbSmetkaOrder']) && !empty($_SESSION['userdata']['cbSmetkaOrder']) ) {
				if ( isset($aCashier['id']) && !empty($aCashier['id']) ) {
					$aCashier['id_cash_default'] = $_SESSION['userdata']['cbSmetkaOrder'];
					
					$oCashier->update($aCashier);
				}
				
				$aData['id_cash_default'] = $aCashier['id_cash_default'];
			} else {
				$aData['id_cash_default'] = isset($aCashier['id_cash_default']) && !empty($aCashier['id_cash_default']) ? $aCashier['id_cash_default'] : -1;
			}			
					
			$oResponse->SetHiddenParam("doc_status", $aData['doc_status']);
			$oResponse->SetHiddenParam("id_schet_account", $aData['id_schet_account']);
			
			// Общи данни
			$oResponse->SetFlexVar("aData", $aData);
			
			// Получател
			$oResponse->SetFlexVar("arr_poluchateli", $aFirms);	
			
			// Типове разход
			$aEarnings 	= $oEarnings->getExpenses();
			$aEarnings[] = array("id" => "-1", "code" => "79999", "name" => " .:: ДДС ::.");
			$oResponse->SetFlexVar("nomenklatures", $aEarnings);	
			
			// Фирми и офиси
			$aFirms2 	= $oFirms->getFirmsByOffice();
			$oResponse->SetFlexVar("firm_regions", $aFirms2);	
	
			// Списък с банкови/касови сметки
			$aBankAcc	= $oBankAcc->getAllAccounts( 2, 0 );
			$oResponse->SetFlexVar("arr_smetki", $aBankAcc);	
			
			// Списък с направленията
			$aDirection	= $oDirection->getRegionDirections();
			$oResponse->SetFlexVar("arr_directions", $aDirection);	
			
			// Списък с описа
			foreach ( $aBuyDocRows as $key => $val ) {
				$aTemp = array();
				
				$aTemp['id'] 					= (string) $val['id'];
				$aTemp['nomenclature']['id'] 	= $val['is_dds'] == 2 ? "-1" : $val['id_nomenclature'];
				$aTemp['nomenclature']['code']	= $val['is_dds'] == 2 ? "79999" : $val['nomenclature_code'];
				$aTemp['nomenclature']['name'] 	= $val['is_dds'] == 2 ? ".:: ДДС ::." : $val['nomenclature_name'];
				$aTemp['firm_region']['fcode'] 	= $val['id_firm'];
				$aTemp['firm_region']['firm'] 	= $val['firm_name'];
				$aTemp['firm_region']['rcode'] 	= $val['id_office'];
				$aTemp['firm_region']['region'] = $val['office_name'];
				$aTemp['direction']['id'] 		= $val['id_direction'];
				$aTemp['direction']['name'] 	= $val['direction_name'];
				$aTemp['month'] 				= $val['month'];
				$aTemp['sum'] 					= $val['sum'];
				$aTemp['note']					= $val['note'];
				$aTemp['is_dds']				= $val['is_dds'];
				$aTemp['payed'] 				= $val['payed'] == 1 ? true : false;
				$aTemp['for_payment'] 			= true;
				
				$aRows[] 						= $aTemp;
	 		}
			
			//return $aRows;
			$oResponse->SetFlexVar("arr_rows", $aRows);
			
			// Списък с ордерите
			$oResponse->SetFlexVar("arr_orders", $aOrders);
			
			// Стойности по подразбиране
			$oResponse->SetFlexControl("cbPoluchatel");
			
			if ( isset($nID) && isset($aBuyDoc['client_name']) && !empty($aBuyDoc['client_name']) ) {
				$oResponse->SetFlexControlDefaultValue("cbPoluchatel", "title", "---");
			} else {
				$oResponse->SetFlexControlDefaultValue("cbPoluchatel", "title", "ИНФРА ЕООД");
			}
			
			$oResponse->printResponse();
		}
	
		// remote method	
		public function suggest(DBResponse $oResponse) {
	  		global $db_sod, $db_name_sod;
	  		
			$field 			= Params::get("field", "");
			$info 			= Params::get("info", "");
	  		
	  		$arr_objects 	= array();
	  		
	  		$sQuery = "
	  			SELECT 
	  				id,
	  				name,
	  				invoice_address as address,
	  				invoice_ein,
	  				invoice_ein_dds,
	  				invoice_mol
	  			FROM {$db_name_sod}.clients
	  			WHERE UPPER({$field}) LIKE UPPER('%$info%')
	  			LIMIT 10
	  		";
	  		
	  		$arr_objects = $db_sod->getArray( $sQuery );
	  		
	  		$oResponse->SetFlexVar("arr_clients", $arr_objects);
	
	  		$oResponse->printResponse();
		}		
		
		
		public function setTotal( $nID ) {
			
			$oBuyDoc		= new DBBuyDocs();
			$oBuyDocRows	= new DBBuyDocsRows();
			
			$aBuyRows		= array();
			$aData			= array();
			$nTotalSum		= 0;
			$nPaidSum		= 0;
			$sLastOrder		= "0";
			$sLastPtime		= "0000-00-00 00:00:00";
					
			if ( !$this->isValidID($nID) ) {
				continue;
			}
			
			$aBuyRows = $oBuyDocRows->getByIDBuyDoc( $nID );
		
			foreach( $aBuyRows as $val ) {
				if ( isset($val['id']) && !empty($val['id']) ) {
					$nTotalSum 	+= $val['total_sum'];
					$nPaidSum	+= $val['paid_sum'];
					
					if ( ($val['paid_date'] > $sLastPtime) && !empty($val['id_order']) ) {
						$sLastOrder = $val['id_order'];
						$sLastPtime	= $val['paid_date'];
					}
				}
			}
			
			$aData['id'] 				= $nID;
			$aData['total_sum'] 		= $nTotalSum;
			$aData['orders_sum'] 		= $nPaidSum;
			$aData['last_order_id'] 	= $sLastOrder;
			$aData['last_order_time']	= $sLastPtime;
			
			$oBuyDoc->update($aData);
		}
		
		public function calculateDDS( DBResponse $oResponse ) {
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$oBuyDoc		= new DBBuyDocs();
			$oBuyDocRows	= new DBBuyDocsRows();
			$oFirms 		= new DBFirms();
			
			$aBuyRows		= array();
			$aBuyDoc		= array();
			$aData			= array();
			$aFirms 		= array();
			$nTotalSum		= 0;
			$nPaidSum		= 0;
			$sLastOrder		= "0";
			$sLastPtime		= "0000-00-00 00:00:00";
			$edit_right		= false;
			$grant_right	= false;
			
			$aParams		= Params::getAll();
			
			// Права за достъп
			$edit_right 	= in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;
			$grant_right 	= false;
			
	
			if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$edit_right		= true;
				$grant_right 	= true;
			}			
			
			if ( isset($aParams['hiddenParams']->nID) && !empty($aParams['hiddenParams']->nID) ) {
				$nID = $aParams['hiddenParams']->nID;
			} else {
				$nID = isset($aParams['id']) ? $aParams['id'] : 0;
				$oResponse->SetFlexVar("nID", $nID);
			}
			
			Params::set("id", $nID);	
			
			if ( !$this->isValidID($nID) ) {
				continue;
			}			
			
			$oBuyDoc->getRecord( $nID, $aBuyDoc );
			
			if ( isset($aBuyDoc['client_ein']) && !empty($aBuyDoc['client_ein']) ) {
				$doc_type		= $aBuyDoc['doc_type'];
				$doc_date		= $aBuyDoc['doc_date'];
				$ein			= $aBuyDoc['client_ein'];					
			} else {
				$doc_type		= isset($aParams['doc_type']) ? $aParams['doc_type'] : "";
				$doc_date		= isset($aParams['doc_date']) ? $aParams['doc_date'] : "0000-00-00";
				$ein			= isset($aParams['cbPoluchatel']['idn']) ? $aParams['cbPoluchatel']['idn'] : 0;						
			}
			
			// ДДС
			$aDDS		= array();
			$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	
			$nDDS 		= isset($aDDS[0]['id']) 		? $aDDS[0]['id'] 							: 0;	
			$nDDSPaid	= isset($aDDS[0]['paid_sum']) 	? sprintf("%01.2f", $aDDS[0]['paid_sum']) 	: 0;
			$dPaidDate	= isset($aDDS[0]['paid_date']) 	? $aDDS[0]['paid_date'] 					: "0000-00-00 00:00:00";	
			
			$aBuyRows = $oBuyDocRows->getByIDBuyDoc( $nID );
			
			foreach( $aBuyRows as $val ) {
				if ( isset($val['id']) && !empty($val['id']) && $val['id'] != $nDDS ) {
					$nTotalSum 	+= $val['total_sum'];
					$nPaidSum	+= $val['paid_sum'];
					
					if ( ($val['paid_date'] > $sLastPtime) && !empty($val['id_order']) ) {
						$sLastOrder = $val['id_order'];
						$sLastPtime	= $val['paid_date'];
					}
				}
			}
		
			//$oBuyDoc->getRecord( $nID, $aBuyDoc );
			
			if ( empty($sLastOrder) && !empty($nDDS) ) {
				$this->delRows($nDDS, 0, $oResponse);			
			} elseif ( !empty($sLastOrder) && !empty($nDDS) ) {
				if ( !$grant_right ) {
					continue;
				} else {
					$this->delRows($nDDS, 0, $oResponse);
				}
			}		
			
			if ( $doc_type == "faktura" ) {
				//$aFirms 	= $oFirms->getDDSFirmByEIN( $ein );
				$nIDOffice	= $oFirms->getDDSOfficeByEIN($ein);
				$nDDSNew = sprintf("%01.2f", $nTotalSum * 0.2);
				
				$aData['id'] 				= 0;
				$aData['id_buy_doc'] 		= $nID;	
				$aData['id_office'] 		= $nIDOffice;		//isset($aFirms['id_office_dds']) ? $aFirms['id_office_dds'] : 0;	
				$aData['id_object'] 		= 0;
				$aData['id_person'] 		= $nIDUser;
				$aData['id_direction'] 		= 0;	
				$aData['id_nomenclature_expense'] = 0;	
				$aData['id_salary_row'] 	= 0;	
				$aData['id_order'] 			= 0;	
				$aData['month'] 			= $doc_date;	
				$aData['quantity']			= 1;
				$aData['measure']			= "бр.";
				$aData['single_price']		= $nTotalSum * 0.2;
				$aData['total_sum']			= $nTotalSum * 0.2;
				$aData['paid_sum']			= $nDDSPaid == $nDDSNew ? $nDDSPaid : 0;
				$aData['paid_date'] 		= $dPaidDate;
				$aData['is_dds']	 		= 1;
				$aData['note'] 				= "";
	
				$oBuyDocRows->update($aData);
	
				$nTotalSum *= 1.2;
			}				
		
			$aData						= array();
			$aData['id'] 				= $nID;
			$aData['total_sum'] 		= $nTotalSum;
			$aData['orders_sum'] 		= $nPaidSum;
			$aData['last_order_id'] 	= $sLastOrder;
			$aData['last_order_time']	= $sLastPtime;
				
			$oBuyDoc->update($aData);	
							
			//$this->setTotal($nID);
		}			

		/**
		 * Ппроверява за валидност на ЕГН/EIN по зададен низ
		 * 
		 * @name trimEin()
		 * @author Павел Петров
		 *
		 * @param string $ein
		 * @return bool
		 */
		public function trimEin( $ein ) {
			$oValidate 	= new Validate();
			$sEin 		= "";
			$aMatrix	= array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
			
			for ( $i = 0; $i < strlen($ein); $i++ ) {
				if ( in_array($ein[$i], $aMatrix) ) {
					$sEin .= $ein[$i];
				}
			}
		 			
		 	if ( (strlen($sEin) == 9) || (strlen($sEin) == 13) ) {
		 		if ( strlen($sEin) == 13 ) {
		 			$base 	= substr($sEin, 0, 9);
		 			$ext	= substr($sEin, -4);
		 					
					$oValidate->variable = $base;
					$oValidate->checkEIN();	 					
		 		} else {
					$oValidate->variable = $sEin;
					$oValidate->checkEIN();
					}
						
				if ( $oValidate->result ) {
					return $sEin;
				}
			} elseif ( strlen($sEin) == 10 ) {
				$oValidate->checkEGN();
					
				if ( $oValidate->result ) {
					return $sEin;
				}
			}
	
			return false;
		}
		
		// remote method	
		public function save( DBResponse $oResponse ) {
			global $db_sod, $db_system, $db_finance, $db_name_finance, $db_name_system;
			
			$oBuyDocRows	= new DBBuyDocsRows();
			$oBuyDoc		= new DBBuyDocs();
			$oFirms 		= new DBFirms();
			$oClients		= new DBClients();
			$oService		= new DBNomenclaturesExpenses();
			$oSync			= new DBSyncMoney();
			$oOffices		= new DBOffices();
			$oLog			= new DBLogErrors();
			$oSaldo			= new DBSaldo();
			
			$aFirms 		= array();
			$aorders		= array();
			$aClients		= array();
			
			$sErrMessage	= "";
			
			$aParams		= Params::getAll();
			
			// Права за достъп
			$edit_right 	= in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;
			$grant_right 	= false;
			
			// При пълно право за редакция - добавяме и право за преглед и редакция
			if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$edit_right		= true;
				$grant_right 	= true;
			} else {
				$grant_right 	= false;
			}

			// Друго право за редакция...
			if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$edit_right 	= true;
				$grant_right 	= true;
			}				
			
			$aData 			= array();
			$nID 			= isset($aParams['hiddenParams']->nID) ? $aParams['hiddenParams']->nID : 0;	
			$nIDSchetAcc	= isset($aParams['hiddenParams']->id_schet_account) ? $aParams['hiddenParams']->id_schet_account : 0;
			$nIDAccount 	= isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
			
			Params::set("id", $nID);
			
			if ( !isset($aParams['cbPoluchatel']['name']) || !isset($aParams['cbPoluchatel']['idn']) || empty($aParams['cbPoluchatel']['name']) || empty($aParams['cbPoluchatel']['idn']) ) {
				throw new Exception( "Изберете валиден получател", DBAPI_ERR_INVALID_PARAM );
			}
			
			if ( !isset($aParams['doc_type']) || empty($aParams['doc_type']) ) {
				throw new Exception( "Въведете вид на документа!", DBAPI_ERR_INVALID_PARAM );
			}
						
			if ( $aParams['doc_type'] != "kvitanciq" ) {
				if ( !isset($aParams['doc_num']) || empty($aParams['doc_num']) ) {
					throw new Exception( "Въведете номер на документа!", DBAPI_ERR_INVALID_PARAM );
				}
							
				if ( !isset($aParams['deliverer_name']) || !isset($aParams['deliverer_ein']) || empty($aParams['deliverer_name']) || !$this->trimEin($aParams['deliverer_ein']) ) {
					throw new Exception( "Изберете валиден доставчик", DBAPI_ERR_INVALID_PARAM );
				}					
			}
				
			if ( !isset($aParams['paid_type']) || empty($aParams['paid_type']) ) {
				throw new Exception( "Въведете начин на плащане!", DBAPI_ERR_INVALID_PARAM );
			}		
			
			if ( !isset($aParams['doc_date']) || empty($aParams['doc_date']) ) {
				throw new Exception( "Изберете дата на получаване на документа!", DBAPI_ERR_INVALID_PARAM );
			} else {
				$aTemp = array();
				$aTemp = explode("-", $aParams['doc_date']);
				
				if ( !checkdate($aTemp[1], $aTemp[2], $aTemp[0]) ) {
					throw new Exception( "Изберете валидна дата на получаване на документа!", DBAPI_ERR_INVALID_PARAM );
				}
				
				$tDate = mktime(0, 0, 0, $aTemp[1], $aTemp[2], $aTemp[0]);
			}

			$cl_ein 	= isset($aParams['deliverer_ein']) && $this->trimEin($aParams['deliverer_ein']) ? $aParams['deliverer_ein']  : "";
			$cl_name	= isset($aParams['deliverer_name']) 		? $aParams['deliverer_name'] 			: "";
			$cl_addr	= isset($aParams['deliverer_address']) 		? $aParams['deliverer_address'] 		: "";

			if ( !empty($cl_ein) && !empty($cl_name) && !empty($cl_addr) ) {
				$aClients	= $oClients->getClientByEIN($cl_ein);
	
				if ( empty($aClients) ) {
					$aClientData 							= array();
					$aClientData['id'] 						= 0;
					$aClientData['name'] 					= $cl_name;
					$aClientData['address'] 				= $cl_addr;
					$aClientData['email'] 					= "";
					$aClientData['phone'] 					= "";
					$aClientData['note'] 					= "";
					$aClientData['invoice_address'] 		= $cl_addr;
					$aClientData['invoice_ein'] 			= $cl_ein;
					$aClientData['invoice_ein_dds'] 		= isset($aParams['deliverer_ein_dds']) 	? $aParams['deliverer_ein_dds'] : "";
					$aClientData['invoice_mol'] 			= isset($aParams['deliverer_mol']) 		? $aParams['deliverer_mol'] 	: "";
					$aClientData['invoice_recipient'] 		= isset($aParams['deliverer_mol']) 		? $aParams['deliverer_mol'] 	: "";
					$aClientData['invoice_bring_to_object'] = 0;
					$aClientData['invoice_layout'] 			= "total";
					$aClientData['invoice_payment'] 		= isset($aParams['paid_type']) 			? $aParams['paid_type'] 		: "";
					$aClientData['invoice_email'] 			= "";
						
					$oClients->update($aClientData);
				} else {
					$aClientData 							= array();
					$aClientData['id'] 						= $aClients['id'];
					$aClientData['name'] 					= $cl_name;
					$aClientData['invoice_address'] 		= $cl_addr;
					$aClientData['invoice_mol'] 			= isset($aParams['deliverer_mol']) 		? $aParams['deliverer_mol'] 	: "";
					$aClientData['invoice_recipient'] 		= isset($aParams['deliverer_mol']) 		? $aParams['deliverer_mol'] 	: "";
					$aClientData['invoice_payment'] 		= isset($aParams['paid_type']) 			? $aParams['paid_type'] 		: "";
							
					$oClients->update($aClientData);							
				}
			}			
			
			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа е анулиран!", DBAPI_ERR_INVALID_PARAM);
			}			
			
					foreach ( $aParams['grid'] as $obj ) {		
						if ( !empty($obj->sum) ) {
							if ( $obj->sum > 200000 ) {
								$aDataLog 				= array();
								$aDataLog['params'] 	= serialize($aParams);
								$aDataLog['response']	= serialize($oResponse);
								$aDataLog['address']	= $_SERVER['SERVER_ADDR'];
									
								$oLog->update($aDataLog);
														
								throw new Exception("\nПроблем с приемането на данните!\nСвържете се с администратор!!!\n\n", DBAPI_ERR_FAILED_TRANS);
							}								
						}
					}
		
			$db_finance->StartTrans();
			$db_system->StartTrans();	
			$db_sod->StartTrans();	
			
			try {			
			
				if ( empty($nID) ) {
					$aData 						= array();
		
					$aData['id'] 				= $nID;
					$aData['id_schet'] 			= 0;
					$aData['doc_num'] 			= $aParams['doc_num'];
					$aData['doc_date'] 			= $tDate;
					$aData['doc_type'] 			= $aParams['doc_type'];
					$aData['for_fuel'] 			= 0;
					$aData['for_gsm'] 			= 0;
					$aData['doc_status'] 		= "final";
					
					$aData['client_name'] 		= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
					$aData['client_ein'] 		= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
					$aData['client_ein_dds'] 	= isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
					$aData['client_address'] 	= isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
					$aData['client_mol'] 		= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
					$aData['client_recipient'] 	= isset($_SESSION['userdata']['name']) 		 ? $_SESSION['userdata']['name']  		: "";
					
					$aData['deliverer_name'] 	= isset($aParams['deliverer_name']) 		? $aParams['deliverer_name'] 			: "";
					$aData['deliverer_ein'] 	= isset($aParams['deliverer_ein']) 			? $aParams['deliverer_ein'] 			: "";
					$aData['deliverer_ein_dds'] = isset($aParams['deliverer_ein_dds']) 		? $aParams['deliverer_ein_dds'] 		: "";
					$aData['deliverer_address'] = isset($aParams['deliverer_address']) 		? $aParams['deliverer_address'] 		: "";
					$aData['deliverer_mol'] 	= isset($aParams['deliverer_mol']) 			? $aParams['deliverer_mol'] 			: "";
					
					$aData['total_sum'] 		= isset($aParams['eTotal']) 				? $aParams['eTotal'] 					: 0;
					$aData['orders_sum'] 		= 0;
					$aData['last_order_id'] 	= 0;
					$aData['last_order_time'] 	= "0000-00-00 00:00:00";
					$aData['paid_type'] 		= $aParams['paid_type'];
					$aData['view_type'] 		= "total";
					$aData['note'] 				= "";
					$aData['created_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
					$aData['created_time'] 		= time();
					$aData['updated_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
					$aData['updated_time'] 		= time();			
	
					$oBuyDoc->update($aData);
				
					$nID 						= $aData['id'];	
					
					$oResponse->SetHiddenParam( "nID", $nID );
					Params::set("id", $nID);
					
					$aDataRows 		= array();
					$month_price	= array();
					
					foreach ( $aParams['grid'] as $obj ) {		
						if ( !empty($obj->sum) ) {
							$is_dds	= 0;						
							
							if ( !isset($obj->direction['id']) || empty($obj->direction['id']) ) {
								throw new Exception("\nИма запис без направление!!!", DBAPI_ERR_FAILED_TRANS);
							}	
							
							if ( !isset($obj->firm_region['rcode']) || empty($obj->firm_region['rcode']) ) {
								throw new Exception("\nИма запис без фирма/регион!!!", DBAPI_ERR_FAILED_TRANS);
							}	

							if ( !isset($obj->nomenclature['id']) || empty($obj->nomenclature['id']) ) {
								throw new Exception("\nИма запис без номенклатура!!!", DBAPI_ERR_FAILED_TRANS);
							}														
							
							// Прехвърляне по сметка - забраняваме тип на документ различен от витанция!
							$bTransfer 	= false;
							$bTransfer	= $oService->checkForTransfer($obj->nomenclature['id']);
							
							if ( $bTransfer && ($aParams['doc_type'] != "kvitanciq") ) {
								throw new Exception("\nДокумент с НАПРАВЛЕНИЕ по ТРАНСФЕР\nможе да бъде само квитанция!!!\n\n", DBAPI_ERR_FAILED_TRANS);
							}						
							
							// Номенклатура ДДС
							if ( $obj->nomenclature['id'] == -1 ) {
								$obj->nomenclature['id'] = 0;
								$is_dds = 2;
								
								$aFirm 		= $oFirms->getFirmByIDOffice($obj->firm_region['rcode']);
								$nIDOffice 	= isset($aFirm['id_office_dds']) 	? $aFirm['id_office_dds'] 	: 0;							
								
								if ( $aParams['doc_type'] != "kvitanciq" ) {
									throw new Exception("\nНе може да бъде записан документ\nот тип фактура с НАПРАВЛЕНИЕ по ДДС!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}
							} else {
								$nIDOffice 	= $obj->firm_region['rcode'];
							}
							
							$nExpense						= !empty($obj->nomenclature['id']) ? $obj->nomenclature['id'] : 0;	
							$nDirection						= !empty($obj->direction['id']) ? $obj->direction['id'] : 0;
							$aExpense						= $oService->getRecord($nExpense);
							
							$aDataRows['id'] 				= 0;
							$aDataRows['id_buy_doc'] 		= $nID;	
							$aDataRows['id_office'] 		= $nIDOffice;	
							$aDataRows['id_object'] 		= 0;	
							$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
							$aDataRows['id_direction'] 		= $obj->direction['id'];		
							$aDataRows['id_nomenclature_expense'] = $nExpense;	
							$aDataRows['id_salary_row'] 	= 0;	
							$aDataRows['id_order'] 			= 0;	
							$aDataRows['month'] 			= $obj->month->getTimestamp();	
							$aDataRows['quantity']			= 1;
							$aDataRows['measure']			= "бр.";
							$aDataRows['single_price']		= $obj->sum;
							$aDataRows['total_sum']			= $obj->sum;
							$aDataRows['paid_sum']			= 0;
							$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
							$aDataRows['is_dds']	 		= $is_dds;
							$aDataRows['note'] 				= $obj->note;
							
							$oBuyDocRows->update($aDataRows);
							
							// SCHET
							if ( isset($obj->note) && !empty($obj->note) ) {
								$service_name = $obj->note;
							} else {
								$service_name	= !empty($aExpense['name'])		? $aExpense['name'] : 0;
							}
								
							if ( $is_dds == 2 ) {
								$nIDNom 	= 10007;
								$aFirm 		= $oFirms->getFirmByIDOffice($obj->firm_region['rcode']);
								$nIDOffice 	= isset($aFirm['id_office_dds']) 	? $aFirm['id_office_dds'] 	: 0;
								$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);	
								$service_name = "ДДС от фактура {$sFirm}";
							} else {
								$nIDNom		= !empty($aExpense['id_schet'])		? $aExpense['id_schet'] 	: 0;
								$nIDOffice	= $obj->firm_region['rcode'];
							}
							
							if ( !empty($nDirection) ) {
								$nIDDirection = $oSync->getSchetByDirection($nDirection);
							} else {
								throw new Exception("\nНе е избрано направление!!!\n\n", DBAPI_ERR_FAILED_TRANS);
							}
													
							$aSchetData = array();
							$aSchetData['id'] 				= 0;
							$aSchetData['id_obj'] 			= 0;	
							$aSchetData['data'] 			= time();	
							$aSchetData['mataksa'] 			= 0;	
							$aSchetData['bank'] 			= $aParams['paid_type'] == "bank" ? 1 : 0;	
							$aSchetData['confirm'] 			= 0;
							$aSchetData['confirm_date'] 	= "0000-00-00 00:00:00";
							$aSchetData['normal'] 			= 0;
							$aSchetData['sum'] 				= $aParams['doc_type'] == "faktura" ? $obj->sum * -1.2 : $obj->sum * -1;
							$aSchetData['taxes'] 			= 1;	
							$aSchetData['paid_month'] 		= $obj->month->getTimestamp(); 		
							$aSchetData['tax_num'] 			= 1;	
							$aSchetData['faktura_type'] 	= 0; 	
							$aSchetData['f_name'] 			= iconv("UTF-8", "CP1251", $aData['client_name']);		
							$aSchetData['f_address'] 		= iconv("UTF-8", "CP1251", $aData['client_address']);	
							$aSchetData['f_dn'] 			= $aData['client_ein_dds'];		
							$aSchetData['f_bulstat'] 		= $aData['client_ein'];	
							$aSchetData['f_mol'] 			= iconv("UTF-8", "CP1251", $aData['client_mol']);		
							$aSchetData['p_name'] 			= iconv("UTF-8", "CP1251", $aData['client_recipient']);		
							$aSchetData['p_lk'] 			= "";
							$aSchetData['p_year'] 			= "";
							$aSchetData['p_num'] 			= "";
							$aSchetData['measure'] 			= iconv("UTF-8", "CP1251", "бр.");
							$aSchetData['br'] 				= 1;	
							$aSchetData['zero'] 			= 0;
							$aSchetData['zero_date'] 		= "0000-00-00";
							$aSchetData['faktura'] 			= 0; 		
							$aSchetData['valid_sum'] 		= 0;
							$aSchetData['smetka_id'] 		= 0;
							$aSchetData['direction_id'] 	= $nIDDirection;	
							$aSchetData['typepay_id'] 		= $nIDNom;	
							$aSchetData['info'] 			= iconv("UTF-8", "CP1251", $service_name);	
							$aSchetData['user_id'] 			= $nIDSchetAcc;	
							$aSchetData['nareditel'] 		= iconv("UTF-8", "CP1251", $aData['deliverer_mol']);	
							$aSchetData['poluchatel'] 		= iconv("UTF-8", "CP1251", $aData['client_mol']);
							$aSchetData['razhod_num'] 		= isset($aParams['doc_num']) && !empty($aParams['doc_num']) ? $aParams['doc_num'] : 1;
							$aSchetData['saldo'] 			= 0;								
							
							if ( $nIDSchetAcc && $nID ) {
								$nIDSchet = $oSync->updateSchetMonth($aSchetData);
								$nIDSchet = date("Ym").zero_padding($nIDSchet, 7);
									
								$aTmp 					= array();
								$aTmp['id'] 			= $aDataRows['id'];
								$aTmp['id_schet_row'] 	= $nIDSchet;
									
								$oBuyDocRows->update($aTmp);								
							}
																		
						}
					}			
					
				} else {		// РЕДАКЦИЯ
					// 1. Проверка за ордери към документ-а
					//$aOrders = $oBuyDocRows->getOrdersByDoc( $nID );
					$aOrders 	= $oBuyDocRows->getOrdersByDoc( $nID );
					
					// ДДС
					$aDDS		= array();
					$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	
					$nIDDDS 	= isset($aDDS[0]['id']) ? $aDDS[0]['id'] : 0;
					
					// Списък от ИД-тата
					$sIDs 		= "";		
					$aNIDs		= array();
					$bDDS		= $oBuyDocRows->checkForDDS($nID);
					
					// 1.1. Нямаме плащания и имаме право за промяна
					if ( empty($aOrders) ) {
						if ( !$edit_right ) {
							throw new Exception("Нямате право за редакция!!!", DBAPI_ERR_INVALID_PARAM);
							//$sErrMessage = "Нямате право за редакция!!!";
						}
						
						$aData 						= array();	
			
						$aData['id'] 				= strval($nID);
						$aData['id_schet'] 			= 0;
						$aData['doc_num'] 			= $aParams['doc_num'];
						$aData['doc_date'] 			= $tDate;
						$aData['doc_type'] 			= $aParams['doc_type'];
						$aData['for_fuel'] 			= 0;
						$aData['for_gsm'] 			= 0;
						$aData['doc_status'] 		= "final";
						
						$aData['client_name'] 		= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
						$aData['client_ein'] 		= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
						$aData['client_ein_dds'] 	= isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
						$aData['client_address'] 	= isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
						$aData['client_mol'] 		= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
						$aData['client_recipient'] 	= isset($_SESSION['userdata']['name']) 		 ? $_SESSION['userdata']['name']  		: "";				
						
						$aData['deliverer_name'] 	= isset($aParams['deliverer_name']) 		? $aParams['deliverer_name'] 			: "";
						$aData['deliverer_ein'] 	= isset($aParams['deliverer_ein']) 			? $aParams['deliverer_ein'] 			: "";
						$aData['deliverer_ein_dds'] = isset($aParams['deliverer_ein_dds']) 		? $aParams['deliverer_ein_dds'] 		: "";
						$aData['deliverer_address'] = isset($aParams['deliverer_address']) 		? $aParams['deliverer_address'] 		: "";
						$aData['deliverer_mol'] 	= isset($aParams['deliverer_mol']) 			? $aParams['deliverer_mol'] 			: "";
						
						$aData['total_sum'] 		= isset($aParams['eTotal']) 				? $aParams['eTotal'] 					: 0;
						$aData['orders_sum'] 		= 0;
						$aData['last_order_id'] 	= 0;
						$aData['last_order_time'] 	= "0000-00-00 00:00:00";
						$aData['paid_type'] 		= $aParams['paid_type'];
						$aData['view_type'] 		= "single";
						$aData['note'] 				= "";
						$aData['created_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
						$aData['created_time'] 		= time();
						$aData['updated_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
						$aData['updated_time'] 		= time();									
						
						$oBuyDoc->update($aData);				
		
						$aBuyRows	= array();
						$aBuyRows 	= $oBuyDocRows->getByIDBuyDoc($nID);
						
						foreach ( $aBuyRows as $val ) {
							$aNIDs[$val['id']] = $val['id'];
						}	
						
						foreach ( $aParams['grid'] as $val ) {	
						
							if ( !empty($val->sum) ) {
								$is_dds = 0;
								
								if ( !isset($val->direction['id']) || empty($val->direction['id']) ) {
									throw new Exception("\nИма запис без направление!!!", DBAPI_ERR_FAILED_TRANS);
								}
										
								if ( !isset($val->firm_region['rcode']) || empty($val->firm_region['rcode']) ) {
									throw new Exception("\nИма запис без фирма/регион!!!", DBAPI_ERR_FAILED_TRANS);
								}	
	
								if ( !isset($val->nomenclature['id']) || empty($val->nomenclature['id']) ) {
									throw new Exception("\nИма запис без номенклатура!!!", DBAPI_ERR_FAILED_TRANS);
								}														
								
								// Прехвърляне по сметка - забраняваме тип на документ различен от витанция!
								$bTransfer 	= false;
								$bTransfer	= $oService->checkForTransfer($val->nomenclature['id']);
								
								if ( $bTransfer && ($aParams['doc_type'] != "kvitanciq") ) {
									throw new Exception("\nДокумент с НАПРАВЛЕНИЕ по ТРАНСФЕР\nможе да бъде само квитанция!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}									
														
								// Номенклатура ДДС
								if ( $val->nomenclature['id'] == -1 ) {
									$val->nomenclature['id'] = 0;
									$is_dds = 2;
									
									$aFirm 		= $oFirms->getFirmByIDOffice($val->firm_region['rcode']);
									$nIDOffice 	= isset($aFirm['id_office_dds']) 	? $aFirm['id_office_dds'] 	: 0;										

									if ( ($aParams['doc_type'] != "kvitanciq") && !empty($bDDS) ) {
										throw new Exception("\nНе може да бъде записан документ\nот тип фактура с НАПРАВЛЕНИЕ по ДДС!!!\n\n", DBAPI_ERR_FAILED_TRANS);
									}
								} else {
									$nIDOffice 	= $val->firm_region['rcode'];
								}
											
								$nExpense						= !empty($val->nomenclature['id']) ? $val->nomenclature['id'] : 0;	
								$nDirection						= !empty($val->direction['id']) ? $val->direction['id'] : 0;
								$aExpense						= $oService->getRecord($nExpense);
												
								$aDataRows 						= array();
								$aDataRows['id'] 				= isset($val->id) ? $val->id : 0;
								$aDataRows['id_buy_doc'] 		= $nID;	
								$aDataRows['id_office'] 		= $nIDOffice;
								$aDataRows['id_object'] 		= 0;
								$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
								$aDataRows['id_direction'] 		= $val->direction['id'];
								$aDataRows['id_nomenclature_expense'] = $nExpense;	
								$aDataRows['id_salary_row'] 	= 0;	
								$aDataRows['id_order'] 			= 0;	
								$aDataRows['month'] 			= $val->month->getTimestamp();	
								$aDataRows['quantity']			= 1;
								$aDataRows['measure']			= "бр.";
								$aDataRows['single_price']		= $val->sum;
								$aDataRows['total_sum']			= $val->sum;
								$aDataRows['paid_sum']			= 0;
								$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
								$aDataRows['is_dds']	 		= $is_dds;
								$aDataRows['note'] 				= $val->note;
	
								$oBuyDocRows->update($aDataRows);
								
								unset($aNIDs[$val->id]);
								
								// SCHET
								if ( isset($val->note) && !empty($val->note) ) {
									$service_name = $val->note;
								} else {
									$service_name	= !empty($aExpense['name'])		? $aExpense['name'] : 0;
								}
									
								if ( $is_dds == 2 ) {
									$nIDNom 	= 10007;
									$aFirm 		= $oFirms->getFirmByIDOffice($val->firm_region['rcode']);
									$nIDOffice 	= isset($aFirm['id_office_dds']) 	? $aFirm['id_office_dds'] 	: 0;
									$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);	
									$service_name = "ДДС от фактура {$sFirm}";
								} else {
									$nIDNom		= !empty($aExpense['id_schet'])		? $aExpense['id_schet'] 	: 0;
									$nIDOffice	= $val->firm_region['rcode'];
								}
								
								if ( !empty($nDirection) ) {
									$nIDDirection = $oSync->getSchetByDirection($nDirection);
								} else {
									throw new Exception("\nНе е избрано направление!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}	
								
								$aSchetData = array();
								$aSchetData['id'] 				= 0;
								$aSchetData['id_obj'] 			= 0;	
								$aSchetData['data'] 			= time();	
								$aSchetData['mataksa'] 			= 0;	
								$aSchetData['bank'] 			= $aParams['paid_type'] == "bank" ? 1 : 0;	
								$aSchetData['confirm'] 			= 0;
								$aSchetData['confirm_date'] 	= "0000-00-00 00:00:00";
								$aSchetData['normal'] 			= 0;
								$aSchetData['sum'] 				= $aParams['doc_type'] == "faktura" ? $val->sum * -1.2 : $val->sum * -1;
								$aSchetData['taxes'] 			= 1;	
								$aSchetData['paid_month'] 		= $val->month->getTimestamp(); 		
								$aSchetData['tax_num'] 			= 1;	
								$aSchetData['faktura_type'] 	= 0; 	
								$aSchetData['f_name'] 			= iconv("UTF-8", "CP1251", $aData['client_name']);		
								$aSchetData['f_address'] 		= iconv("UTF-8", "CP1251", $aData['client_address']);	
								$aSchetData['f_dn'] 			= $aData['client_ein_dds'];		
								$aSchetData['f_bulstat'] 		= $aData['client_ein'];	
								$aSchetData['f_mol'] 			= iconv("UTF-8", "CP1251", $aData['client_mol']);		
								$aSchetData['p_name'] 			= iconv("UTF-8", "CP1251", $aData['client_recipient']);		
								$aSchetData['p_lk'] 			= "";
								$aSchetData['p_year'] 			= "";
								$aSchetData['p_num'] 			= "";
								$aSchetData['measure'] 			= iconv("UTF-8", "CP1251", "бр.");
								$aSchetData['br'] 				= 1;	
								$aSchetData['zero'] 			= 0;
								$aSchetData['zero_date'] 		= "0000-00-00";
								$aSchetData['faktura'] 			= 0; 		
								$aSchetData['valid_sum'] 		= 0;
								$aSchetData['smetka_id'] 		= 0;
								$aSchetData['direction_id'] 	= $nIDDirection;	
								$aSchetData['typepay_id'] 		= $nIDNom;	
								$aSchetData['info'] 			= iconv("UTF-8", "CP1251", $service_name);	
								$aSchetData['user_id'] 			= $nIDSchetAcc;	
								$aSchetData['nareditel'] 		= iconv("UTF-8", "CP1251", $aData['deliverer_mol']);	
								$aSchetData['poluchatel'] 		= iconv("UTF-8", "CP1251", $aData['client_mol']);
								$aSchetData['razhod_num'] 		= isset($aParams['doc_num']) && !empty($aParams['doc_num']) ? $aParams['doc_num'] : 1;
								$aSchetData['saldo'] 			= 0;								
								
								if ( $nIDSchetAcc && $nID && empty($val->id) ) {
									$nIDSchet = $oSync->updateSchetMonth($aSchetData);
									$nIDSchet = date("Ym").zero_padding($nIDSchet, 7);
										
									$aTmp 					= array();
									$aTmp['id'] 			= $aDataRows['id'];
									$aTmp['id_schet_row'] 	= $nIDSchet;
										
									$oBuyDocRows->update($aTmp);								
								}													
							}	
						}
						
						if ( !empty($aNIDs) ) {
							$sIDs = implode(",", $aNIDs);	
							$this->delRows($sIDs, 1, $oResponse);
						}
						//ob_toFile("okotooto");
					} else {
						// Имаме ордери, но променяме!!!
						$oOrders	= new DBOrders();
						$aOrders 	= array();
						
						if ( !$grant_right ) {
							throw new Exception("Нямате право за редакция на платени документи!!!", DBAPI_ERR_INVALID_PARAM);
						}
						
						// Анулираме ордерите!!!
						$aOrders 	= $oBuyDocRows->getOrdersByDoc( $nID );
						
						foreach ( $aOrders as $aval ) {
							$nIDOrder = 0;
							
							if ( isset($aval['order_status']) && ($aval['order_status'] == "active") ) {
								$nIDOrder	= $aval['id'];
								
								if ( $this->isValidID($nIDOrder) ) {
									$oOrders->annulment($oResponse, $nIDOrder);
								}
								
							}
						}							
						
						// Променяме документа!
						$aData 						= array();	
			
						$aData['id'] 				= strval($nID);
						$aData['id_schet'] 			= 0;
						$aData['doc_num'] 			= $aParams['doc_num'];
						$aData['doc_date'] 			= $tDate;
						$aData['doc_type'] 			= $aParams['doc_type'];
						$aData['for_fuel'] 			= 0;
						$aData['for_gsm'] 			= 0;
						$aData['doc_status'] 		= "final";
						
						$aData['client_name'] 		= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
						$aData['client_ein'] 		= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
						$aData['client_ein_dds'] 	= isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
						$aData['client_address'] 	= isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
						$aData['client_mol'] 		= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
						$aData['client_recipient'] 	= isset($_SESSION['userdata']['name']) 		 ? $_SESSION['userdata']['name']  		: "";				
						
						$aData['deliverer_name'] 	= isset($aParams['deliverer_name']) 		? $aParams['deliverer_name'] 			: "";
						$aData['deliverer_ein'] 	= isset($aParams['deliverer_ein']) 			? $aParams['deliverer_ein'] 			: "";
						$aData['deliverer_ein_dds'] = isset($aParams['deliverer_ein_dds']) 		? $aParams['deliverer_ein_dds'] 		: "";
						$aData['deliverer_address'] = isset($aParams['deliverer_address']) 		? $aParams['deliverer_address'] 		: "";
						$aData['deliverer_mol'] 	= isset($aParams['deliverer_mol']) 			? $aParams['deliverer_mol'] 			: "";
						
						$aData['total_sum'] 		= isset($aParams['eTotal']) 				? $aParams['eTotal'] 					: 0;
						$aData['orders_sum'] 		= 0;
						$aData['last_order_id'] 	= 0;
						$aData['last_order_time'] 	= "0000-00-00 00:00:00";
						$aData['paid_type'] 		= $aParams['paid_type'];
						$aData['view_type'] 		= "single";
						$aData['note'] 				= "";
						$aData['created_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
						$aData['created_time'] 		= time();
						$aData['updated_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
						$aData['updated_time'] 		= time();									
						
						$oBuyDoc->update($aData);				
		
						$aBuyRows	= array();
						$aBuyRows 	= $oBuyDocRows->getByIDBuyDoc($nID);
						
						foreach ( $aBuyRows as $val ) {
							$aNIDs[$val['id']] = $val['id'];
						}	
						
						foreach ( $aParams['grid'] as $val ) {	
						
							if ( !empty($val->sum) ) {
								$is_dds = 0;
								
								if ( !isset($val->direction['id']) || empty($val->direction['id']) ) {
									throw new Exception("\nИма запис без направление!!!", DBAPI_ERR_FAILED_TRANS);
								}
										
								if ( !isset($val->firm_region['rcode']) || empty($val->firm_region['rcode']) ) {
									throw new Exception("\nИма запис без фирма/регион!!!", DBAPI_ERR_FAILED_TRANS);
								}	
	
								if ( !isset($val->nomenclature['id']) || empty($val->nomenclature['id']) ) {
									throw new Exception("\nИма запис без номенклатура!!!", DBAPI_ERR_FAILED_TRANS);
								}														
								
								// Прехвърляне по сметка - забраняваме тип на документ различен от витанция!
								$bTransfer 	= false;
								$bTransfer	= $oService->checkForTransfer($val->nomenclature['id']);
								
								if ( $bTransfer && ($aParams['doc_type'] != "kvitanciq") ) {
									throw new Exception("\nДокумент с НАПРАВЛЕНИЕ по ТРАНСФЕР\nможе да бъде само квитанция!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}									
														
								// Номенклатура ДДС
								if ( $val->nomenclature['id'] == -1 ) {
									$val->nomenclature['id'] = 0;
									$is_dds = 2;
									
									$aFirm 		= $oFirms->getFirmByIDOffice($val->firm_region['rcode']);
									$nIDOffice 	= isset($aFirm['id_office_dds']) 	? $aFirm['id_office_dds'] 	: 0;										

									if ( ($aParams['doc_type'] != "kvitanciq") && !empty($bDDS) ) {
										throw new Exception("\nНе може да бъде записан документ\nот тип фактура с НАПРАВЛЕНИЕ по ДДС!!!\n\n", DBAPI_ERR_FAILED_TRANS);
									}
								} else {
									$nIDOffice 	= $val->firm_region['rcode'];
								}
											
								$nExpense						= !empty($val->nomenclature['id']) ? $val->nomenclature['id'] : 0;	
								$nDirection						= !empty($val->direction['id']) ? $val->direction['id'] : 0;
								$aExpense						= $oService->getRecord($nExpense);
												
								$aDataRows 						= array();
								$aDataRows['id'] 				= isset($val->id) ? $val->id : 0;
								$aDataRows['id_buy_doc'] 		= $nID;	
								$aDataRows['id_office'] 		= $nIDOffice;
								$aDataRows['id_object'] 		= 0;
								$aDataRows['id_person'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
								$aDataRows['id_direction'] 		= $val->direction['id'];
								$aDataRows['id_nomenclature_expense'] = $nExpense;	
								$aDataRows['id_salary_row'] 	= 0;	
								$aDataRows['id_order'] 			= 0;	
								$aDataRows['month'] 			= $val->month->getTimestamp();	
								$aDataRows['quantity']			= 1;
								$aDataRows['measure']			= "бр.";
								$aDataRows['single_price']		= $val->sum;
								$aDataRows['total_sum']			= $val->sum;
								$aDataRows['paid_sum']			= 0;
								$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
								$aDataRows['is_dds']	 		= $is_dds;
								$aDataRows['note'] 				= $val->note;
	
								$oBuyDocRows->update($aDataRows);
	
								unset($aNIDs[$val->id]);
								
								// SCHET
								if ( isset($val->note) && !empty($val->note) ) {
									$service_name = $val->note;
								} else {
									$service_name	= !empty($aExpense['name'])		? $aExpense['name'] : 0;
								}
									
								if ( $is_dds == 2 ) {
									$nIDNom 	= 10007;
									$aFirm 		= $oFirms->getFirmByIDOffice($val->firm_region['rcode']);
									$nIDOffice 	= isset($aFirm['id_office_dds']) 	? $aFirm['id_office_dds'] 	: 0;
									$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);	
									$service_name = "ДДС от фактура {$sFirm}";
								} else {
									$nIDNom		= !empty($aExpense['id_schet'])		? $aExpense['id_schet'] 	: 0;
									$nIDOffice	= $val->firm_region['rcode'];
								}
								
								if ( !empty($nDirection) ) {
									$nIDDirection = $oSync->getSchetByDirection($nDirection);
								} else {
									throw new Exception("\nНе е избрано направление!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}	
								
								$aSchetData = array();
								$aSchetData['id'] 				= 0;
								$aSchetData['id_obj'] 			= 0;	
								$aSchetData['data'] 			= time();	
								$aSchetData['mataksa'] 			= 0;	
								$aSchetData['bank'] 			= $aParams['paid_type'] == "bank" ? 1 : 0;	
								$aSchetData['confirm'] 			= 0;
								$aSchetData['confirm_date'] 	= "0000-00-00 00:00:00";
								$aSchetData['normal'] 			= 0;
								$aSchetData['sum'] 				= $aParams['doc_type'] == "faktura" ? $val->sum * -1.2 : $val->sum * -1;
								$aSchetData['taxes'] 			= 1;	
								$aSchetData['paid_month'] 		= $val->month->getTimestamp(); 		
								$aSchetData['tax_num'] 			= 1;	
								$aSchetData['faktura_type'] 	= 0; 	
								$aSchetData['f_name'] 			= iconv("UTF-8", "CP1251", $aData['client_name']);		
								$aSchetData['f_address'] 		= iconv("UTF-8", "CP1251", $aData['client_address']);	
								$aSchetData['f_dn'] 			= $aData['client_ein_dds'];		
								$aSchetData['f_bulstat'] 		= $aData['client_ein'];	
								$aSchetData['f_mol'] 			= iconv("UTF-8", "CP1251", $aData['client_mol']);		
								$aSchetData['p_name'] 			= iconv("UTF-8", "CP1251", $aData['client_recipient']);		
								$aSchetData['p_lk'] 			= "";
								$aSchetData['p_year'] 			= "";
								$aSchetData['p_num'] 			= "";
								$aSchetData['measure'] 			= iconv("UTF-8", "CP1251", "бр.");
								$aSchetData['br'] 				= 1;	
								$aSchetData['zero'] 			= 0;
								$aSchetData['zero_date'] 		= "0000-00-00";
								$aSchetData['faktura'] 			= 0; 		
								$aSchetData['valid_sum'] 		= 0;
								$aSchetData['smetka_id'] 		= 0;
								$aSchetData['direction_id'] 	= $nIDDirection;	
								$aSchetData['typepay_id'] 		= $nIDNom;	
								$aSchetData['info'] 			= iconv("UTF-8", "CP1251", $service_name);	
								$aSchetData['user_id'] 			= $nIDSchetAcc;	
								$aSchetData['nareditel'] 		= iconv("UTF-8", "CP1251", $aData['deliverer_mol']);	
								$aSchetData['poluchatel'] 		= iconv("UTF-8", "CP1251", $aData['client_mol']);
								$aSchetData['razhod_num'] 		= isset($aParams['doc_num']) && !empty($aParams['doc_num']) ? $aParams['doc_num'] : 1;
								$aSchetData['saldo'] 			= 0;								
								
								if ( $nIDSchetAcc && $nID && empty($val->id) ) {
									$nIDSchet = $oSync->updateSchetMonth($aSchetData);
									$nIDSchet = date("Ym").zero_padding($nIDSchet, 7);
										
									$aTmp 					= array();
									$aTmp['id'] 			= $aDataRows['id'];
									$aTmp['id_schet_row'] 	= $nIDSchet;
										
									$oBuyDocRows->update($aTmp);								
								}															
							}
						}
						
						if ( !empty($aNIDs) ) {
							$sIDs = implode(",", $aNIDs);	
							$this->delRows($sIDs, 1, $oResponse);
						}
					}
				}
				
				if ( !empty($sErrMessage) ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();	
					$db_sod->FailTrans();
					
					throw new Exception($sErrMessage, DBAPI_ERR_FAILED_TRANS);
				}
					
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();	
				$db_sod->CompleteTrans();		
			} catch (Exception $e) {
				$sError = $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				$db_sod->FailTrans();
				
				throw new Exception("Грешка: ".$sError, DBAPI_ERR_FAILED_TRANS);
			}		
			
			// typa tranzakcia
			$this->calculateDDS($oResponse);
			
			//$oResponse->printResponse();
			$this->init($oResponse);
		}
		
	
		public function delRows( $sRows, $no_dds = 1, DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod; 
			
			$nIDUser		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$aData 			= array();
			$aData 			= explode(",", $sRows);
			$sErrMessage	= "";
			$flag 			= true;
			
			$oOrders		= new DBOrders();
			$oBuyDocRows	= new DBBuyDocsRows();
			$oFirms			= new DBFirms();
			$oSaldo			= new DBSaldo();
			$oOrderRows		= new DBOrdersRows();
			
			$db_finance->StartTrans();
			$db_system->StartTrans();					
			
			try {
				foreach ( $aData as $nID ) {
					$aBuyRows		= array();		
											
					if ( !$this->isValidID($nID) ) {
						continue;
					}						
	
					$oBuyDocRows->getRecord($nID, $aBuyRows);
					
					$nIDOrder 		= isset($aBuyRows['id_order'])		? $aBuyRows['id_order'] 		: 0;
					$nIDBuyDoc 		= isset($aBuyRows['id_buy_doc']) 	? $aBuyRows['id_buy_doc'] 		: 0;
					$nSum			= isset($aBuyRows['total_sum']) 	&& isset($aBuyRows['paid_sum']) && ($aBuyRows['total_sum'] == $aBuyRows['paid_sum']) ? $aBuyRows['total_sum'] : 0;
					$nTotalSum		= isset($aBuyRows['total_sum']) 	? $aBuyRows['total_sum'] 		: 0;
					$isDDS			= isset($aBuyRows['is_dds']) 		&& !empty($aBuyRows['is_dds'])	? 1 : 0;
					$nIDOffice		= isset($aBuyRows['id_office']) 	? $aBuyRows['id_office'] 		: 0;
					$nIDDirection	= isset($aBuyRows['id_direction']) 	? $aBuyRows['id_direction'] 	: 0;
					
					if ( $no_dds && $isDDS ) {
						continue;
					}
					
					$nIDFirm 		= $oFirms->getFirmByOffice($nIDOffice);
					$nIDTran		= $oBuyDocRows->checkForTransfer($nIDBuyDoc);
	
					if ( !empty($nIDOrder) && !empty($nSum) ) {
						// Имаме реализирано плащане за конкретния запис, създаваме обратено плащане на него!
						$aRow			= array();
						$aOrder			= array();
						
						$oOrders->getRecord($nIDOrder, $aRow);					
							
						$nIDAccount		= isset($aRow['bank_account_id']) 	? $aRow['bank_account_id'] 	: 0;
						$sOrderType		= isset($aRow['order_type']) 		? $aRow['order_type'] 		: "expense";
						$sAccType		= isset($aRow['account_type']) 		? $aRow['account_type'] 	: "cash";
						$sDocType		= isset($aRow['doc_type']) 			? $aRow['doc_type'] 		: "sale";
						
						// Пореден номер
						$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
						$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;						
						
						// Салдо	
						$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, $isDDS);
						$nIDSaldo		= 0;
						$nCurrentSaldo	= 0;
	
						if ( !empty($aSaldo) ) {
							$nIDSaldo 	= $aSaldo['id'];
						}
								
						// Салдо на фирмата с изчакване!!!
						if ( !empty($nIDSaldo) ) {
							$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
					    	$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
						} else {
							$sErrMessage .= "Неизвестно салдо по фирма!\n";
						}

						// Наличност по сметка
						if ( !empty($nIDAccount) ) {
							$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
					    	$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
						} else {
							$sErrMessage .= "Неизвестнa сметка!\n";
						}						

						$saldo 			= sprintf("%01.2f", $nCurrentSaldo + $nSum);
						$paid_account	= sprintf("%01.2f", $nAccountState + $nSum);
								
						if ( $saldo < 0 ) {
							$sErrMessage .= "Недостатъчно салдо по фирма!\n";
						}	
														
						if ( $nTotalSum > 0 ) {
							$sOrderType 	= "expense";
						} else {
							$sOrderType 	= "earning";				
						}
						
						if ( $paid_account < 0 ) {
							$sErrMessage .= "Недостатъчна наличност в сметката!\n";		
						}
	
						$aOrderData						= array();
						$aOrderData['id']				= 0;
						$aOrderData['num']				= $nLastOrder;
						$aOrderData['order_type'] 		= $sOrderType;
						$aOrderData['id_transfer']		= 0;
						$aOrderData['order_date']		= time();
						$aOrderData['order_sum']		= abs($nSum);
						$aOrderData['account_type']		= $sAccType;
						$aOrderData['id_person']		= $nIDUser;
						$aOrderData['account_sum']		= $paid_account;
						$aOrderData['bank_account_id']	= $nIDAccount;
						$aOrderData['doc_id']			= $nIDBuyDoc;
						$aOrderData['doc_type']			= $sDocType;
						$aOrderData['note']				= "";
						$aOrderData['created_user']		= $nIDUser;
						$aOrderData['created_time']		= time();
						$aOrderData['updated_user']		= $nIDUser;
						$aOrderData['updated_time']		= time();
						
						$oOrders->update($aOrderData);	
						
						$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
						
						$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSum}' WHERE id_bank_account = {$nIDAccount} ");		
							
						if ( empty($nIDTran) ) {
							$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nSum}' WHERE id = {$nIDSaldo} LIMIT 1");	
						}						

						$nIDOrderNow = $aOrderData['id'];
						
						$aData = array();
						$aData['id'] 						= 0;
						$aData['id_order'] 					= $nIDOrderNow;
						$aData['id_doc_row'] 				= $aBuyRows['id'];
						$aData['id_office'] 				= $aBuyRows['id_office'];
						$aData['id_object'] 				= $aBuyRows['id_object'];
						$aData['id_service'] 				= 0;
						$aData['id_direction']				= $nIDDirection;
						$aData['id_nomenclature_earning'] 	= 0;
						$aData['id_nomenclature_expense'] 	= $aBuyRows['id_nomenclature_expense'];
						$aData['id_saldo']					= $nIDSaldo;
						$aData['id_bank']					= $nIDAccount;
						$aData['saldo_state']				= empty($nIDTran) ? $saldo : $nCurrentSaldo;		
						$aData['account_state']				= $paid_account;						
						$aData['month'] 					= $aBuyRows['month'];
						$aData['type'] 						= "free";
						$aData['paid_sum'] 					= ($nSum * -1);
						$aData['is_dds'] 					= $isDDS;
									
					 	$oOrderRows->update($aData);					
					}
						
					$oBuyDocRows->delete( $nID );
				}
				
				if ( !empty($sErrMessage) ) {
					//$oResponse->setAlert($sErrMessage);
					
					$db_finance->FailTrans();
					$db_system->FailTrans();	
				}
					
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();			
			} catch (Exception $e) {
				//$oResponse->setAlert("Грешка при Задача!!!");
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
	
			}		
			
			//$oResponse->printResponse();
			$this->init($oResponse);
		}
		
		
		// remote method	
	 	public function makeOrder( DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod;
			
			$aParams		= Params::getAll();
			
			$oBuyDocRows	= new DBBuyDocsRows();
			$oBuyDoc		= new DBBuyDocs();
			$oFirms 		= new DBFirms();
			$oOrders		= new DBOrders();
			$oOrdersRows	= new DBOrdersRows();
			$oSync			= new DBSyncMoney();
			$oSaldo			= new DBSaldo();
			
			$aFirms 		= array();
			$aOrders		= array();
			$sErrMessage	= "";
			$nIDTran		= 0;
			$nIDTran2		= 0;
			$nAccState		= 0;
			$nAccountState	= 0;
			$nIDAccount		= 0;
			
			$nIDUser		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			// Право за редакция
			$edit_right = in_array('buy_doc_order_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;		
			
			$aData 	= array();
			$nID 	= isset($aParams['hiddenParams']->nID) ? $aParams['hiddenParams']->nID : 0;		
			
			Params::set("id", $nID);
			

			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа е анулиран!!!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			// ДДС
			$aDDS		= array();
			$aDDS 		= $oBuyDocRows->getDDSByDoc( $nID );	
			$nDDS 		= isset($aDDS[0]['id']) ? $aDDS[0]['id'] : 0;
			
			// Трансфер
			$nIDTran	= $oBuyDocRows->checkForTransfer( $nID );
			$nIDTran2	= $oBuyDocRows->checkForTransfer( $nID, 1 );
			
			if ( !empty($nIDTran) && !empty($nIDTran2) ) {
				throw new Exception("В документа участват комбинации от услуги и ТРАНСФЕР!!!", DBAPI_ERR_INVALID_PARAM);
			}
 
			$nTotalSum 	= 0;	
			$nPaidSum	= 0;
			$nDDSSum	= 0;
			
			//$aForSale	= array();
			
			$nIDAccount = isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
			
			$db_finance->StartTrans();
			$db_system->StartTrans();	
			
			try {
				$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
				$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;
				
				// НАЧАЛНА наличност по сметка
				$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
				$nAccState	 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
				
				$nDDSSum 		= 0;
				$aIDs			= array();
				
				$aData['id']				= 0;
				$aData['num']				= $nLastOrder;
				$aData['order_type'] 		= "expense";
				$aData['id_transfer']		= 0;
				$aData['order_date']		= time();
				$aData['order_sum']			= 0;	
				$aData['account_type']		= isset($aParams['paid_type']) ? $aParams['paid_type'] : "cash";
				$aData['id_person']			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
				$aData['account_sum']		= 0;	
				$aData['bank_account_id']	= $nIDAccount;	//isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
				$aData['doc_id']			= $nID;
				$aData['doc_type']			= "buy";
				$aData['note']				= "";
				$aData['created_user']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
				$aData['created_time']		= time();
				$aData['updated_user']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
				$aData['updated_time']		= time();
	
				$oOrders->update($aData);
				
				$nIDOrder 		= $aData['id'];	
				
				foreach ( $aParams['grid'] as $val ) {	
					if ( isset($val->id) && isset($val->sum) && !empty($val->id) && !empty($val->sum) ) {
						$nIDFirm 	= isset($val->firm_region['fcode']) ? $val->firm_region['fcode'] 	: 0;
						$sFirmName	= isset($val->firm_region['firm']) 	? $val->firm_region['firm'] 	: "";
						$nIDOffice	= isset($val->firm_region['rcode']) ? $val->firm_region['rcode'] 	: 0;
						$nIDObject	= isset($val->id_object) 			? $val->id_object 				: 0;
						$nIDDirect	= isset($val->direction['id']) 		? $val->direction['id']			: 0;
						$nIDExpens	= isset($val->nomenclature['id']) 	? $val->nomenclature['id'] 		: 0;
						$nSum		= $val->sum;
						$aSaldo		= array();
	
						if ( $val->for_payment == 1 ) {		
							$nPaidSum += $val->sum;
							$is_dds	  = 0;
							$is_dds2  = 0;
							
							if ( isset($val->id) ) {
								$aIDs[]	= strval($val->id);
							}
							
							if ( $nIDExpens == -1 ) {
								$is_dds 	= 2;
								$is_dds2	= 1;
							}
							
							$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, $is_dds2);
							$nIDSaldo		= 0;
							$nCurrentSaldo	= 0;

							if ( !empty($aSaldo) ) {
								$nIDSaldo 	= $aSaldo['id'];
							}	
							
							// Наличност по сметка
							if ( !empty($nIDAccount) ) {
								$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
								$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
							} else {
								throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
							}			
							
							if ( $nAccountState < $nSum ) {
								throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
							}								

							// Салдо на фирмата с изчакване!!!
							if ( !empty($nIDSaldo) ) {
								$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
				    			$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
							} else {
								throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
							}							
							
							// Ордери - разбивка!
							$aDataRows								= array();
							$aDataRows['id']						= 0;
							$aDataRows['id_order']					= $nIDOrder;
							$aDataRows['id_doc_row']				= $val->id;
							$aDataRows['id_office']					= $nIDOffice;
							$aDataRows['id_object']					= $nIDObject;
							$aDataRows['id_service']				= 0;
							$aDataRows['id_direction']				= $nIDDirect;
							$aDataRows['id_nomenclature_earning']	= 0;
							$aDataRows['id_nomenclature_expense']	= $nIDExpens;
							$aDataRows['id_saldo']					= $nIDSaldo;
							$aDataRows['id_bank']					= $nIDAccount;
							$aDataRows['saldo_state']				= !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo - $nSum;		
							$aDataRows['account_state']				= $nAccountState - $nSum;							
							$aDataRows['month']						= $val->month->getTimestamp();
							$aDataRows['type']						= "free";
							$aDataRows['paid_sum']					= $val->sum;
							$aDataRows['is_dds']					= $is_dds;
							
							$oOrdersRows->update($aDataRows);							
							
							// Салда на фирмите
							if ( empty($nIDTran) ) {
								if ( $nCurrentSaldo < $nSum ) {
									throw new Exception("Надхвърлено е салдото на фирма {$sFirmName}!!!\n", DBAPI_ERR_ACCESS_DENIED);
								} 								
								
								$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSum}' WHERE id = {$nIDSaldo} LIMIT 1");
							}
							
							$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSum}' WHERE id_bank_account = {$nIDAccount} ");
						}
					
						$nTotalSum += $val->sum;	
					}	
				}	

				if ( ($aParams['dds_for_payment'] == 1) && ($aParams['doc_type'] == "faktura") ) {
					$nDDSSum 	= $nTotalSum * 0.2;				
					$aSaldo		= array();
					$aIDs[]   	= $nDDS;
					
					$nIDN 		= isset($aParams['cbPoluchatel']['idn']) ? $aParams['cbPoluchatel']['idn'] : 0;
					$aFirms 	= $oFirms->getDDSFirmByEIN( $nIDN );
					$nIDFirm	= isset($aFirms['id']) 		? $aFirms['id'] 	: 0;
					$sFirmName	= isset($aFirms['name']) 	? $aFirms['name'] 	: "";
					
					$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, 1);
					$nIDSaldo		= 0;
					$nCurrentSaldo	= 0;

					if ( !empty($aSaldo) ) {
						$nIDSaldo 	= $aSaldo['id'];
					}	

					// Наличност по сметка
					if ( !empty($nIDAccount) ) {
						$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
						$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
					} else {
						throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
					}	
					
					if ( $nAccountState < $nDDSSum ) {
						throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
					}					
												
					if ( !empty($nIDSaldo) ) {
					// Салдо на фирмата с изчакване!!!
						$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
				    	$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
					} else {
						throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
					}						
						
					// Ордери - разбивка!
					$aDataRows								= array();
					$aDataRows['id']						= 0;
					$aDataRows['id_order']					= $nIDOrder;
					$aDataRows['id_doc_row']				= $nDDS;
					$aDataRows['id_office']					= isset($aDDS[0]['id_office']) 		? $aDDS[0]['id_office']		: 0;
					$aDataRows['id_object']					= 0;
					$aDataRows['id_service']				= 0;
					$aDataRows['id_direction']				= isset($aDDS[0]['id_direction']) 	? $aDDS[0]['id_direction']	: 0;
					$aDataRows['id_nomenclature_earning']	= 0;
					$aDataRows['id_nomenclature_expense']	= 0;
					$aDataRows['id_saldo']					= $nIDSaldo;
					$aDataRows['id_bank']					= $nIDAccount;
					$aDataRows['saldo_state']				= !empty($nIDTran) 					? $nCurrentSaldo 			: $nCurrentSaldo - $nDDSSum;	
					$aDataRows['account_state']				= $nAccountState - $nDDSSum;					
					$aDataRows['month']						= isset($aDDS[0]['month']) 			? $aDDS[0]['month']			: "0000-00-00";
					$aDataRows['type']						= "free";
					$aDataRows['paid_sum']					= $nDDSSum;
					$aDataRows['is_dds']					= 1;
							
					$oOrdersRows->update($aDataRows);

					// Салда на фирмите
					if ( empty($nIDTran) ) {
						if ( $nCurrentSaldo < $nDDSSum ) {
							throw new Exception("Надхвърлено е салдото на фирма {$sFirmName}!!!\n", DBAPI_ERR_ACCESS_DENIED);
						} 								
								
						$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nDDSSum}' WHERE id = {$nIDSaldo} LIMIT 1");
					}	

					$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nDDSSum}' WHERE id_bank_account = {$nIDAccount} ");
				}

				// Оправяме тоталите					
				$paid_account				= $nAccState - ($nPaidSum + $nDDSSum);
				$sum_dds					= $nPaidSum + $nDDSSum;
				
				if ( $sum_dds >= 0 ) {
					$sTypeNow = "expense";
				} else {
					$sTypeNow = "earning";
				}				
				
				$aData					= array();
				$aData['id']			= $nIDOrder;	
				$aData['order_type'] 	= $sTypeNow;		
				$aData['order_sum']		= abs($sum_dds);
				$aData['account_sum']	= $paid_account;
				
				$oOrders->update($aData);				

				$sBuyName 		= "buy_docs_".substr($nID, 0, 6);
				$sBuyRowsName 	= "buy_docs_rows_".substr($nID, 0, 6);
				
				$oRes = $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
				
				//$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = '{$paid_account}' WHERE id_bank_account = {$nIDAccount} ");
				$sIDs = !empty($aIDs) ? implode(",", $aIDs) : -1;
				
				$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyRowsName} SET id_order = '{$nIDOrder}', paid_sum = total_sum WHERE id IN ({$sIDs})");
				
				$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.{$sBuyName} SET last_order_id = '{$nIDOrder}', last_order_time = NOW(), orders_sum = orders_sum + '{$sum_dds}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = '{$nID}'");
					
				// SCHET!!!
				foreach ($aIDs as $valID ) {	
					if ( isset($valID) && !empty($valID) ) {
						$nIDTnet	= $valID;
						$aRow 		= array();
						$nIDSchet	= 0;
						
						if ( $this->isValidID($nIDTnet) ) {
							$aRow		= $oBuyDocRows->getAllByRow($nIDTnet);
							$nIDSchet 	= isset($aRow[0]['id_schet_row']) 	? $aRow[0]['id_schet_row'] 	: 0;
						}
						
						if ( $this->isValidID($nIDSchet) && $this->isValidID($nIDTnet) ) {
							// Проверка за съвместимост
							$table1 = "mp".substr($nIDSchet, 0, 6);
							$table2 = "mp".date("Ym");
							$status	= true;
								
							if ( $table1 != $table2 ) {
								$nLast = $oSync->moveDocument($nIDSchet);
									
								if ( !empty($nLast) ) {
									$nIDSchet				= date("Ym").zero_padding($nLast, 7);
										
									$aUpdate 				= array();
									$aUpdate['id'] 			= $nIDTnet;
									$aUpdate['id_schet_row']= $nIDSchet;
										
									$oBuyDocRows->update($aUpdate);
								}
							}
								
							// Месечни задължения/ДДС								
							$aRows 				= array();
							$aRows['id'] 		= $nIDSchet;
							$aRows['sum'] 		= "999999999";
							$aRows['smetka']	= $nIDAccount;
								//throw new Exception(ArrayToString($aRows), DBAPI_ERR_FAILED_TRANS);
							$status = $oSync->payMonth($aRows);	
								
//							if ( !$status ) {
//								throw new Exception("Проблем при синхронизация със счет-а", DBAPI_ERR_FAILED_TRANS);
//							}									
						}	
					}
				}			
				
				if ( !empty($sErrMessage) ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();	
					
					throw new Exception("Грешка: ".$sErrMessage, DBAPI_ERR_FAILED_TRANS);			
				}			
				
				if ( sprintf("%01.2f", $nPaidSum + $nDDSSum) == "0.00" ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();					
				}
				
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();			
			} catch (Exception $e) {
				$sMessage 	= $e->getMessage();

				$db_finance->FailTrans();
				$db_system->FailTrans();
				
				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}
			
			$_SESSION['userdata']['cbSmetkaOrder'] = isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
			
			$this->init( $oResponse );	
	 	}
	 	
	 	
	 	// remote method	
		public function annulment( DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_sod;
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$aParams		= Params::getAll();
			$nID 			= isset($aParams['nId']) ? $aParams['nId'] : 0;	
			$buy_doc_edit	= true;
			$buy_doc_grant	= true;			
	
			Params::set("id", $nID);	
			
			// При право за редакция - добавяме и право за преглед
			if ( in_array('buy_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
				$buy_doc_edit = true;
			} else {
				$buy_doc_edit = false;
			}
			
			// При пълно право за редакция - добавяме и право за преглед и редакция
			if ( in_array('buy_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$buy_doc_edit 	= true;
				$buy_doc_grant 	= true;
			} else {
				$buy_doc_grant	= false;
			}			

			// Друго право за редакция...
			if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$buy_doc_edit 	= true;
				$buy_doc_grant 	= true;
			}				
			
			$oBuyRows		= new DBBuyDocsRows();
			$oOrders		= new DBOrders();
			$oBuyDoc		= new DBBuyDocs();
			
			$aOrders		= array();
			$aBuyDoc		= array();
			$nIDTran		= 0;
			$sTimeNow		= date("Ym");
			$sTimeDoc		= substr($nID, 0, 6);	
			$sErrMessage	= "";
			
			if ( !$buy_doc_edit ) {
				throw new Exception("Нямате достатъчно права за операцията!!!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа вече е анулиран!!!", DBAPI_ERR_INVALID_PARAM);
			}
						
			if ( !$this->isValidID($nID) ) {
				throw new Exception("Невалиден документ {$nID}!!!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( !empty($nID) ) {
				$aBuyDoc 	= $oBuyDoc->getDoc($nID);
				$lock 		= isset($aBuyDoc['doc_type']) && (($aBuyDoc['doc_type'] == "oprostena") || ($aBuyDoc['doc_type'] == "kvitanciq")) ? false : true;
				
				if ( ($sTimeNow != $sTimeDoc) && $lock ) {
					throw new Exception("Документа е издаден в предходен месец!", DBAPI_ERR_INVALID_PARAM);
				}									
			}			

			$db_finance->StartTrans();	
			$db_system->StartTrans();	
			$db_sod->StartTrans();	
			
			try {			
				$aOrders 	= $oBuyRows->getOrdersByDoc( $nID );
				
				if ( !empty($aOrders) && !$buy_doc_grant ) {
					throw new Exception("Нямате достатъчно права за операцията!!!", DBAPI_ERR_INVALID_PARAM);
				}
				
				foreach ( $aOrders as $aval ) {
					if ( isset($aval['order_status']) && ($aval['order_status'] == "active") ) {
						$nIDOrder	= $aval['id'];
						
						if ( $this->isValidID($nIDOrder) ) {
							$oOrders->annulment($oResponse, $nIDOrder);
						}
						
					}
				}				
	
				$sDocName = PREFIX_BUY_DOCS.substr($nID, 0, 6);
				
				$db_finance->Execute("UPDATE {$db_name_finance}.$sDocName SET doc_status = 'canceled', updated_user = '{$nIDUser}', updated_time = NOW() WHERE id = {$nID} ");
				
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();
				$db_sod->CompleteTrans();

				$oResponse->SetFlexVar("annulment_status", true);			
			} catch (Exception $e) {
				$sMessage 	= $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				$db_sod->FailTrans();
				
				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}				

			$this->init($oResponse);	
		}

	}

?>