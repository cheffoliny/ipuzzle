<?php
	class ApiSalesDocsFilter {
		
		public function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID',0);
			
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			$oDBNomenclatures = new DBNomenclaturesEarnings();
			
			$aDeliverers = $oDBFirms->getDeliverers();
			$aFirms = $oDBFirms->getFirms4();
			$aNomenclatures = $oDBNomenclatures->getAllWithCode();
			
			if(empty($nID)) {
	
				$oResponse->setFormElement("form1","sDeliverer");
				$oResponse->setFormElementChild('form1','sDeliverer',array("value" => ''),"---Всички---");
				foreach ($aDeliverers as $value) {
					if(!empty($value['deliverer'])) {
						$oResponse->setFormElementChild('form1','sDeliverer',array("value" => $value['deliverer']),$value['deliverer']);
					}
				}
				
				$oResponse->setFormElement('form1','nIDFirmCreator');
				$oResponse->setFormElementChild('form1','nIDFirmCreator',array("value" => ''),'---Всичи---');
				$oResponse->setFormElement('form1','nIDFirmObjects');
				$oResponse->setFormElementChild('form1','nIDFirmObjects',array("value" => ''),'---Всички---');
				
				foreach ($aFirms as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDFirmCreator',array("value" => $key),$value);
					$oResponse->setFormElementChild('form1','nIDFirmObjects',array("value" => $key),$value);
				}
				
				$oResponse->setFormElement('form1','nIDOfficeCreator');
				$oResponse->setFormElementChild('form1','nIDOfficeCreator',array('value'=>''),'---Всички---');
				$oResponse->setFormElement('form1','nIDOfficeObjects');
				$oResponse->setFormElementChild('form1','nIDOfficeObjects',array('value'=>''),'---Всички---');
				
				//Set Nomenclatures Earnings
				$oResponse->setFormElement( "form1", "nomenclatures_all" );
				
				foreach( $aNomenclatures as $aNomenclature )
				{
					$oResponse->setFormElementChild( "form1", "nomenclatures_all", array( "value" => $aNomenclature['id'] ), $aNomenclature['name'] );
				}
				//End Set Nomenclatures Earnings
				
				//Show Fields
				$oResponse->setFormElement( "form1", "show_date", 			array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_type", 			array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_deliverer", 		array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_client", 		array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_total_sum", 		array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_orders_sum", 	array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_last_order", 	array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_created_user", 	array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_created_time", 	array( "checked" => "checked" ) );
				$oResponse->setFormElement( "form1", "show_deal_office", 	array( "checked" => "checked" ) );
				//End Show Fields
			}
			else
			{
				$oDBFilters = new DBFilters();
				$oDBFiltersParams = new DBFiltersParams();
				
				$aFilter = $oDBFilters->getRecord($nID);
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter($nID);
				
				$oResponse->setFormElement('form1','sName',array(),$aFilter['name']);
				
				if(!empty($aFilter['is_default'])) {
					$oResponse->setFormElement('form1','is_default',array("checked"=>"checked"));
				}
				
				if(!isset($aFilterParams['num_from'])) $aFilterParams['num_from'] = '';
				if(!isset($aFilterParams['num_to'])) $aFilterParams['num_to'] = '';
				
				$oResponse->setFormElement('form1','num_from',array(),$aFilterParams['num_from']);
				$oResponse->setFormElement('form1','num_to',array(),$aFilterParams['num_to']);
				
				if(!isset($aFilterParams['doc_type'])) {
					$nVal = 0;
				} else {
					switch ($aFilterParams['doc_type']) {
						case 'kvitanciq':$nVal = 1;break;
						case 'oprostena': $nVal = 5;break;
						case 'faktura':$nVal = 2;break;
						case 'kreditno izvestie': $nVal = 3;break;
						case 'debitno izvestie': $nVal = 4;break;
						default: $nVal = 0;
					}
				}
				
				$oResponse->setFormElement('form1','sDocType',array("value"=>$nVal));
				
				if(!isset($aFilterParams['price_from'])) $aFilterParams['price_from'] = '';
				if(!isset($aFilterParams['price_to'])) $aFilterParams['price_to'] = '';
				
				$oResponse->setFormElement('form1','price_from',array(),$aFilterParams['price_from']);
				$oResponse->setFormElement('form1','price_to',array(),$aFilterParams['price_to']);
				
				if(!isset($aFilterParams['status'])) {
					$nVal = 0;
				} else {
					switch ($aFilterParams['status']) {
						case 'canceled': $nVal = 1;break;
						case 'not_canceled': $nVal = 2;break;
						default: $nVal = 0;
					}
				}
				
				$oResponse->setFormElement('form1','sStatus',array("value"=>$nVal));
				
				if(!isset($aFilterParams['paid_status'])) {
					$nVal = 0;
				} else {
					switch ($aFilterParams['paid_status']) {
						case 'paid': $nVal = 1;break;
						case 'part_paid': $nVal = 2;break;
						case 'not_paid': $nVal = 3;break;
						case 'not_or_part_paid': $nVal = 4;break;
						default: $nVal = 0;
					}
				}
				
				$oResponse->setFormElement('form1','sPaidStatus',array("value"=>$nVal));
				
				if(!isset($aFilterParams['paid_type'])) {
					$nVal = 0;
				} else {
					switch ($aFilterParams['paid_type']) {
						case 'bank':$nVal = 1;break;
						case 'cash':$nVal = 2;break;
						default: $nVal = 0;
					}
				}
				
				$oResponse->setFormElement('form1','paid_type',array("value" => $nVal));
				
				if( !isset( $aFilterParams['paid_type_order'] ) )
				{
					$nVal = 0;
				}
				else
				{
					switch( $aFilterParams['paid_type_order'] )
					{
						case 'bank': $nVal = 1; break;
						case 'cash': $nVal = 2; break;
						default: $nVal = 0;
					}
				}
				
				$oResponse->setFormElement( "form1", "paid_type_order", array( "value" => $nVal ) );
				
				if(isset($aFilterParams['email_send']) && !empty($aFilterParams['email_send'])) {
					$oResponse->setFormElement('form1','email_send',array("checked" => "checked"));
				}
				
				if( !isset( $aFilterParams["doc_date_from"] ) ) 		$aFilterParams["doc_date_from"] 		= ""; 
				if( !isset( $aFilterParams["doc_date_to"] ) ) 			$aFilterParams["doc_date_to"] 			= ""; 
				if( !isset( $aFilterParams["doc_date_period"] ) ) 		$aFilterParams["doc_date_period"] 		= ""; 
				if( !isset( $aFilterParams["last_order_from"] ) ) 		$aFilterParams["last_order_from"] 		= ""; 
				if( !isset( $aFilterParams["last_order_to"] ) ) 		$aFilterParams["last_order_to"] 		= ""; 
				if( !isset( $aFilterParams["last_order_period"] ) ) 	$aFilterParams["last_order_period"] 	= ""; 
				if( !isset( $aFilterParams["create_date_from"] ) ) 		$aFilterParams["create_date_from"] 		= ""; 
				if( !isset( $aFilterParams["create_date_to"] ) ) 		$aFilterParams["create_date_to"] 		= ""; 
				if( !isset( $aFilterParams["create_date_period"] ) ) 	$aFilterParams["create_date_period"] 	= ""; 
				
				$nDocDateFrom		= mysqlDateToTimestamp($aFilterParams['doc_date_from']);
				$nDocDateTo			= mysqlDateToTimestamp($aFilterParams['doc_date_to']);
				$nLastOrderFrom		= mysqlDateToTimestamp($aFilterParams['last_order_from']);
				$nLastOrderTo		= mysqlDateToTimestamp($aFilterParams['last_order_to']);
				$nCreateDateFrom	= mysqlDateToTimestamp($aFilterParams['create_date_from']);
				$nCreateDateTo		= mysqlDateToTimestamp($aFilterParams['create_date_to']);
				
				$sDocDateFrom	= !empty($nDocDateFrom)		? date("d.m.Y",$nDocDateFrom)	: '';
				$sDocDateTo		= !empty($nDocDateTo)		? date("d.m.Y",$nDocDateTo)		: '';
				$sLastOrderFrom	= !empty($nLastOrderFrom)	? date("d.m.Y",$nLastOrderFrom) : '';
				$sLastOrderTo	= !empty($nLastOrderTo)		? date("d.m.Y",$nLastOrderTo)	: ''; 
				$sCreateDateFrom= !empty($nCreateDateFrom)	? date("d.m.Y",$nCreateDateFrom): '';
				$sCreateDateTo	= !empty($nCreateDateTo)	? date("d.m.Y",$nCreateDateTo)	: '';
				
				$oResponse->setFormElement( "form1", "sDocDateFrom",		array(), $sDocDateFrom );
				$oResponse->setFormElement( "form1", "sDocDateTo",			array(), $sDocDateTo );
				$oResponse->setFormElement( "form1", "sDocDatePeriod",		array(), $aFilterParams["doc_date_period"] );
				$oResponse->setFormElement( "form1", "sLastOrderFrom",		array(), $sLastOrderFrom );
				$oResponse->setFormElement( "form1", "sLastOrderTo",		array(), $sLastOrderTo );
				$oResponse->setFormElement( "form1", "sLastOrderPeriod",	array(), $aFilterParams["last_order_period"] );
				$oResponse->setFormElement( "form1", "sCreateDateFrom",		array(), $sCreateDateFrom );
				$oResponse->setFormElement( "form1", "sCreateDateTo",		array(), $sCreateDateTo );
				$oResponse->setFormElement( "form1", "sCreateDatePeriod",	array(), $aFilterParams["create_date_period"] );
				
				// Служител - създал
				
				$oResponse->setFormElement('form1','nIDFirmCreator');
				$oResponse->setFormElementChild('form1','nIDFirmCreator',array("value" => ''),'---Всички---');
				foreach ($aFirms as $key => $value) {
					if(isset($aFilterParams['id_firm_creator']) && $aFilterParams['id_firm_creator'] == $key) {
						$oResponse->setFormElementChild('form1','nIDFirmCreator',array("value"=>$key,'selected'=>'selected'),$value);
					} else {
						$oResponse->setFormElementChild('form1','nIDFirmCreator',array("value"=>$key),$value);
					}
				}
				
				$oResponse->setFormElement('form1','nIDOfficeCreator');
				$oResponse->setFormElementChild('form1','nIDOfficeCreator',array("value"=>''),'---Всички---');
				
				if(isset($aFilterParams['id_firm_creator']) && !empty($aFilterParams['id_firm_creator'])) {
					$aOfficesCreator = $oDBOffices->getOfficesByIDFirm($aFilterParams['id_firm_creator']);
					foreach ($aOfficesCreator as $key => $value) {
						if(isset($aFilterParams['id_office_creator']) && $aFilterParams['id_office_creator'] == $key) {
							$oResponse->setFormElementChild('form1','nIDOfficeCreator',array("value" => $key,"selected"=>"selected"),$value);
						} else {
							$oResponse->setFormElementChild('form1','nIDOfficeCreator',array("value" => $key),$value);
						}
					}
				}
				
				// Обекти от
				
				$oResponse->setFormElement('form1','nIDFirmObjects');
				$oResponse->setFormElementChild('form1','nIDFirmObjects',array("value" => ''),'---Всички---');
				foreach ($aFirms as $key => $value) {
					if(isset($aFilterParams['id_firm_objects']) && $aFilterParams['id_firm_objects'] == $key) {
						$oResponse->setFormElementChild('form1','nIDFirmObjects',array("value"=>$key,'selected'=>'selected'),$value);
					} else {
						$oResponse->setFormElementChild('form1','nIDFirmObjects',array("value"=>$key),$value);
					}
				}
				
				$oResponse->setFormElement('form1','nIDOfficeObjects');
				$oResponse->setFormElementChild('form1','nIDOfficeObjects',array("value"=>''),'---Всички---');
				
				if(isset($aFilterParams['id_firm_objects']) && !empty($aFilterParams['id_firm_objects'])) {
					$aOfficesCreator = $oDBOffices->getOfficesByIDFirm($aFilterParams['id_firm_objects']);
					foreach ($aOfficesCreator as $key => $value) {
						if(isset($aFilterParams['id_office_objects']) && $aFilterParams['id_office_objects'] == $key) {
							$oResponse->setFormElementChild('form1','nIDOfficeObjects',array("value" => $key,"selected"=>"selected"),$value);
						} else {
							$oResponse->setFormElementChild('form1','nIDOfficeObjects',array("value" => $key),$value);
						}
					}
				}
				
				
				if(!isset($aFilterParams['client_name'])) $aFilterParams['client_name'] = '';
				if(!isset($aFilterParams['client_ein'])) $aFilterParams['client_ein'] = '';
				if(!isset($aFilterParams['client_eik'])) $aFilterParams['client_eik'] = '';
				
				$oResponse->setFormElement('form1','client_name',array(),$aFilterParams['client_name']);
				$oResponse->setFormElement('form1','client_ein',array(),$aFilterParams['client_ein']);
				$oResponse->setFormElement('form1','client_eik',array(),$aFilterParams['client_eik']);
				
				if(!isset($aFilterParams['deliverer_name'])) $aFilterParams['deliverer_name'] = '';
				
				$oResponse->setFormElement("form1","sDeliverer");
				$oResponse->setFormElementChild('form1','sDeliverer',array("value" => ''),"---Всички---");
				foreach ($aDeliverers as $value) {
					if(!empty($value['deliverer'])) {
						if($aFilterParams['deliverer_name'] == $value['deliverer']) {
							$oResponse->setFormElementChild('form1','sDeliverer',array("value" => $value['deliverer'],"selected"=>"selected"),$value['deliverer']);
						} else {
							$oResponse->setFormElementChild('form1','sDeliverer',array("value" => $value['deliverer']),$value['deliverer']);
						}
					}
				}
				
				//Set Nomenclatures Earnings
				if( !isset( $aFilterParams['ids_nomenclatures'] ) )$aFilterParams['ids_nomenclatures'] = "";
				$aSelNomenclatures = explode( ",", $aFilterParams['ids_nomenclatures'] );
				
				$oResponse->setFormElement( "form1", "nomenclatures_all" );
				$oResponse->setFormElement( "form1", "nomenclatures_current" );
				
				foreach( $aNomenclatures as $aNomenclature )
				{
					if( in_array( $aNomenclature['id'], $aSelNomenclatures ) )
					{
						$oResponse->setFormElementChild( "form1", "nomenclatures_current", array( "value" => $aNomenclature['id'] ), $aNomenclature['name'] );
					}
					else
					{
						$oResponse->setFormElementChild( "form1", "nomenclatures_all", array( "value" => $aNomenclature['id'] ), $aNomenclature['name'] );
					}
				}
				//End Set Nomenclatures Earnings
				
				//Show Fields
				$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
				$aShowFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nID );
				
				foreach( $aShowFields as $sFieldName )
				{
					$oResponse->setFormElement( "form1", $sFieldName, array( "checked" => "checked" ) );
				}
				//End Show Fields
			}
			
			if(!empty($aFilter['is_auto'])) {
				$oResponse->setFormElement('form1','is_auto',array("checked"=>"checked"));
			
				$nRobotFromDate = strtotime($aFilter['auto_start_date']);	
				$sRobotFromDate = !empty($nRobotFromDate) ? date("d.m.Y",$nRobotFromDate) : '';
				
				$oResponse->setFormElement('form1','sRobotFromDate',array("disabled" => false),$sRobotFromDate);
				
				switch ($aFilter['auto_period']) {
					case 'day': $nVal = 1;break;
					case 'week': $nVal = 2;break;
					case 'month': $nVal = 3;break;
					default: $nVal = 1;
				}
				
				$oResponse->setFormElement('form1','sPeriod',array('disabled' => false,"value" => $nVal));
				
				$oDBFiltersTotals = new DBFiltersTotals();
				$aFilterTotals = $oDBFiltersTotals->getFilterTotalsByIDFilter($nID);
				
				if(in_array('total_sum',$aFilterTotals)) {
					$oResponse->setFormElement('form1','total_sum',array('disabled' => false,"checked" => "checked"));
				} else {
					$oResponse->setFormElement('form1','total_sum',array('disabled' => false));
				}
				
				if(in_array('total_orders',$aFilterTotals)) {
					$oResponse->setFormElement('form1','total_orders',array('disabled' => false,"checked" => "checked"));
				} else {
					$oResponse->setFormElement('form1','total_orders',array('disabled' => false));
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function loadCreatorOffices(DBResponse $oResponse) {
			$nIDFirmCreator = Params::get('nIDFirmCreator','');
			
			$oResponse->setFormElement('form1','nIDOfficeCreator');
			$oResponse->setFormElementChild('form1','nIDOfficeCreator',array('value'=>''),'---Всички---');
			
			if(!empty($nIDFirmCreator)) {
				$oDBOffices = new DBOffices();
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirmCreator);

				foreach ($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDOfficeCreator',array('value'=> $key),$value);
				}
			}
			$oResponse->printResponse();
		}
		
		public function loadObjectsOffices(DBResponse $oResponse) {
			$nIDFirmObjects = Params::get('nIDFirmObjects','');
			
			$oResponse->setFormElement('form1','nIDOfficeObjects');
			$oResponse->setFormElementChild('form1','nIDOfficeObjects',array('value'=>''),'---Всички---');
			
			if(!empty($nIDFirmObjects)) {
				$oDBOffices = new DBOffices();
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirmObjects);

				foreach ($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDOfficeObjects',array('value'=> $key),$value);
				}
			}
			$oResponse->printResponse();
		}
		
		public function save() {
			
			$nID = Params::get('nID',0);
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$sName = Params::get('sName','');
			$nIsDefault = Params::get('is_default',0);
			$nNumFrom = Params::get('num_from','');
			$nNumTo = Params::get('num_to','');
			$sDocType = Params::get('sDocType','');
			$dPriceFrom = Params::get('price_from','');
			$dPriceTo = Params::get('price_to','');
			$sStatus = Params::get('sStatus','');
			$sPaidStatus = Params::get('sPaidStatus','');
			$sPaidType = Params::get('paid_type','');
			$sPaidTypeOrder = Params::get('paid_type_order','');
			$sDocDateFrom = Params::get('sDocDateFrom','');
			$sDocDateTo = Params::get('sDocDateTo','');
			$sDocDatePeriod = Params::get('sDocDatePeriod','');
			$sLastOrderFrom = Params::get('sLastOrderFrom','');
			$sLastOrderTo = Params::get('sLastOrderTo','');
			$sLastOrderPeriod = Params::get('sLastOrderPeriod','');
			$sCreateDateFrom = Params::get('sCreateDateFrom','');
			$sCreateDateTo = Params::get('sCreateDateTo','');
			$sCreateDatePeriod = Params::get('sCreateDatePeriod','');
			$sClientName = Params::get('client_name','');
			$sClientEin = Params::get('client_ein','');
			$sClientEik = Params::get('client_eik','');
			$sDeliverer = Params::get('sDeliverer','');
			$nIsAuto = Params::get('is_auto','');
			$sRobotFromDate = Params::get('sRobotFromDate','');
			$sPeriod = Params::get('sPeriod','');
			$nTotalSum = Params::get('total_sum',0);
			$nTotalOrders = Params::get('total_orders',0);			
			$nEmailSend = Params::get('email_send',0);
			$nIDFirmCreator = Params::get('nIDFirmCreator',0);
			$nIDOfficeCreator = Params::get('nIDOfficeCreator',0);
			$nIDFirmObjects = Params::get('nIDFirmObjects',0);
			$nIDOfficeObjects = Params::get('nIDOfficeObjects',0);
			$aNomenclatures = Params::get( "nomenclatures_current", array() );
			
			
			$aShowColumns = array();
			$aShowColumns['show_date'] = Params::get('show_date',0);
			$aShowColumns['show_type'] = Params::get('show_type',0);
			$aShowColumns['show_deliverer'] = Params::get('show_deliverer',0);
			$aShowColumns['show_client'] = Params::get('show_client',0);
			$aShowColumns['show_total_sum'] = Params::get('show_total_sum',0);
			$aShowColumns['show_orders_sum'] = Params::get('show_orders_sum',0);
			$aShowColumns['show_last_order'] = Params::get('show_last_order',0);
			$aShowColumns['show_created_user'] = Params::get('show_created_user',0);
			$aShowColumns['show_created_time'] = Params::get('show_created_time',0);
			$aShowColumns['show_deal_office'] = Params::get('show_deal_office',0);
					
			if(empty($sName)) {
				throw new Exception("Въведете име на филтъра");
			}
		
			if(!empty($nNumFrom) && !is_numeric($nNumFrom)) {
				throw new Exception("Невалидна стойност на полето номер от");
			}
			
			if(!empty($nNumTo) && !is_numeric($nNumTo)) {
				throw new Exception("Невалидна стойност на полето номер до");
			}
			
			if(!empty($dPriceFrom) && !is_numeric($dPriceFrom)) {
				throw new Exception("Невалидна стойност на полето стойност от");
			}
			
			if(!empty($dPriceTo) && !is_numeric($dPriceTo)) {
				throw new Exception("Невалидна стойност на полето стойност до");
			}
			
			if(!empty($nIsAuto) && empty($nTotalSum) && empty($nTotalOrders)) {
				throw new Exception("Изберете за кои колони ще събира тотали роботът");
			}
			
			if( empty( $sDocDatePeriod ) )
			{
				if(!empty($sDocDateFrom) && !checkValidDate($sDocDateFrom)) {
					throw new Exception("Невалидна стойност на полето дата на документа от");
				}
				
				if(!empty($sDocDateTo) && !checkValidDate($sDocDateTo)) {
					throw new Exception("Невалидна стойност на полето дата на документа до");
				}
			}
			
			if( empty( $sLastOrderPeriod ) )
			{
				if(!empty($sLastOrderFrom) && !checkValidDate($sLastOrderFrom)) {
					throw new Exception("Невалидна стойност на полето последно плащане от");
				}
				
				if(!empty($sLastOrderTo) && !checkValidDate($sLastOrderTo)) {
					throw new Exception("Невалидна стойност на полето последно плащане до");
				}
			}
			
			if( empty( $sCreateDatePeriod ) )
			{
				if(!empty($sCreateDateFrom) && !checkValidDate($sCreateDateFrom)) {
					throw new Exception("Невалидна стойност на полето генериран от");
				}
				
				if(!empty($sCreateDateTo) && !checkValidDate($sCreateDateTo)) {
					throw new Exception("Невалидна стойност на полето генериран до");
				}
			}
			
			if(!empty($sClientEik) && !is_numeric($sClientEik)) {
				throw new Exception("Невалидна стойност на полето ЕИК на клиент");
			}
			
			
			$nDocDateFrom = jsDateToTimestamp($sDocDateFrom);
			$nDocDateTo = jsDateEndToTimestamp($sDocDateTo);
			$nLastOrderFrom = jsDateToTimestamp($sLastOrderFrom);
			$nLastOrderTo = jsDateEndToTimestamp($sLastOrderTo);
			$nCreateDateFrom = jsDateToTimestamp($sCreateDateFrom);
			$nCreateDateTo = jsDateEndToTimestamp($sCreateDateTo);
			$nRobotFromDate = jsDateToTimestamp($sRobotFromDate);
			
			$sDocDateFrom = timestampToMysqlDateTime($nDocDateFrom);
			$sDocDateTo = timestampToMysqlDateTime($nDocDateTo);
			$sLastOrderFrom = timestampToMysqlDateTime($nLastOrderFrom);
			$sLastOrderTo = timestampToMysqlDateTime($nLastOrderTo);
			$sCreateDateFrom = timestampToMysqlDateTime($nCreateDateFrom);
			$sCreateDateTo = timestampToMysqlDateTime($nCreateDateTo);
			$sRobotFromDate = timestampToMysqlDateTime($nRobotFromDate);
			
			switch ($sDocType) {
				case '1': $sDocType = 'kvitanciq';break;
				case '2': $sDocType = 'faktura';break;
				case '3': $sDocType = 'kreditno izvestie';break;
				case '4': $sDocType = 'debitno izvestie';break;
				case '5': $sDocType = 'oprostena';break;
				default: $sDocType = '';
				
			}
		
			switch ($sStatus) {
				case '1': $sStatus = 'canceled';break;
				case '2': $sStatus = 'not_canceled';break;
				default: $sStatus = '';
			}
				
			switch($sPaidStatus) {
				case '1': $sPaidStatus = 'paid';break;
				case '2': $sPaidStatus = 'part_paid';break;
				case '3': $sPaidStatus = 'not_paid';break;
				case '4': $sPaidStatus = 'not_or_part_paid';break;
				default:$sPaidStatus = '';
			}
			
			switch ($sPaidType) {
				case '1': $sPaidType = 'bank';break;
				case '2': $sPaidType = 'cash';break;
				default: $sPaidType = '';
			}
			
			switch ($sPaidTypeOrder) {
				case '1': $sPaidTypeOrder = 'bank';break;
				case '2': $sPaidTypeOrder = 'cash';break;
				default: $sPaidTypeOrder = '';
			}
			
			switch ($sPeriod) {
				case '1': $sPeriod = 'day';break;
				case '2': $sPeriod = 'week';break;
				case '3': $sPeriod = 'month';break;
				default: $sPeriod = 'day';
			}
			
			$oDBFilters = new DBFilters();
			
			if(!empty($nIsDefault)) {
				$oDBFilters->resetDefaults("DBSalesDocs",$nIDPerson);
			}
			
			$aFilter = array();
			$aFilter['id'] = $nID;
			$aFilter['name'] = $sName;
			$aFilter['id_person'] = $nIDPerson;
			$aFilter['is_default'] = $nIsDefault;
			$aFilter['report_class'] = 'DBSalesDocs';
			$aFilter['is_auto'] = $nIsAuto;
			$aFilter['auto_start_date'] = $sRobotFromDate;
			$aFilter['auto_period'] = $sPeriod;
			
			$oDBFilters->update($aFilter);
			
			$oDBFiltersParams = new DBFiltersParams();
			
			$oDBFiltersParams->delParamsByIDFilter($aFilter['id']);
			
			$aFilterParams = array();
			$aFilterParams['num_from'] = $nNumFrom;
			$aFilterParams['num_to'] = $nNumTo;
			$aFilterParams['num_to'] = $nNumTo;
			$aFilterParams['doc_type'] = $sDocType;
			$aFilterParams['price_from'] = $dPriceFrom;
			$aFilterParams['price_to'] = $dPriceTo;
			$aFilterParams['status'] = $sStatus;
			$aFilterParams['paid_status'] = $sPaidStatus;
			$aFilterParams['paid_type'] = $sPaidType;
			$aFilterParams['paid_type_order'] = $sPaidTypeOrder;
 			$aFilterParams['doc_date_from'] = $sDocDateFrom;
 			$aFilterParams['doc_date_to'] = $sDocDateTo;
 			$aFilterParams['last_order_from'] = $sLastOrderFrom;
 			$aFilterParams['last_order_to'] = $sLastOrderTo;
 			$aFilterParams['create_date_from'] = $sCreateDateFrom;
 			$aFilterParams['create_date_to'] = $sCreateDateTo;
 			$aFilterParams['doc_date_period'] = $sDocDatePeriod;
 			$aFilterParams['last_order_period'] = $sLastOrderPeriod;
 			$aFilterParams['create_date_period'] = $sCreateDatePeriod;
 			$aFilterParams['client_name'] = $sClientName;
 			$aFilterParams['client_ein'] = $sClientEin;
 			$aFilterParams['client_eik'] = $sClientEik;
 			$aFilterParams['deliverer_name'] = $sDeliverer;
 			$aFilterParams['email_send'] = $nEmailSend;
			$aFilterParams['id_firm_creator'] = $nIDFirmCreator;
			$aFilterParams['id_office_creator'] = $nIDOfficeCreator;
			$aFilterParams['id_firm_objects'] = $nIDFirmObjects;
			$aFilterParams['id_office_objects'] = $nIDOfficeObjects;
 			$aFilterParams['ids_nomenclatures'] = implode( ",", $aNomenclatures );
			
 			foreach ($aFilterParams as $key => $value) {
 				$aData = array();
 				$aData['id_filter'] = $aFilter['id'];
 				$aData['name'] = $key;
 				$aData['value'] = $value;
 				
 				$oDBFiltersParams->update($aData);
 			}
 			
 			$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
 			
 			$oDBFiltersVisibleFields->delByIDFilter($aFilter['id']);
 			
 			foreach ($aShowColumns as $key => $value) {
 				if(!empty($value)) {
 					$aData = array();
 					$aData['id_filter'] = $aFilter['id'];
 					$aData['field_name'] = $key;
 					
 					$oDBFiltersVisibleFields->update($aData);
 				}
 			}
 			
 			$oDBFiltersTotals = new DBFiltersTotals();
 			
 			$oDBFiltersTotals->delFilterTotalsByIDFilter($aFilter['id']);
 			
 			$aTotals = array();
 			
 			$aTotals['total_sum'] = $nTotalSum;
 			$aTotals['total_orders'] = $nTotalOrders;
 			
 			foreach ($aTotals as $key => $value) {
 				if(!empty($value)) {
 					$aData = array();
 					$aData['id_filter'] = $aFilter['id'];
 					$aData['total_name'] = $key;
 					
 					$oDBFiltersTotals->update($aData);
 				}
 			}
			
		}
	}

?>