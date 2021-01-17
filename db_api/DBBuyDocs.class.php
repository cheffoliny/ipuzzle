<?php
	require_once('include/db_include.inc.php');
	
	
	class DBBuyDocs extends DBMonthTable {
		
		function __construct() {
			
			global $db_name_finance,$db_finance;
			
			parent::__construct($db_name_finance,PREFIX_BUY_DOCS,$db_finance);
		}

        private function getPerson() {
            return isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
        }
		
		public function getReport( $aParams, DBResponse $oResponse = NULL ) {
			global $db_finance_backup, $db_name_sod, $db_name_personnel;
			
			$oFirms 	= new DBFirms();
			$sFirm		= "";
			$oDBOffices = new DBOffices();
			
						
			//Params
			$sDeliverer = isset( $aParams['sDeliverer'] ) 	? $aParams['sDeliverer'] 	: '';
			$nNum 		= isset( $aParams['nNum'] ) 		? $aParams['nNum'] 			: '';
			$nIDScheme 	= isset( $aParams['schemes'] ) 		? $aParams['schemes'] 		: 0;
			$nIDFirm 	= isset( $aParams['nIDFirm'] ) 		? $aParams['nIDFirm'] 		: 0;
			$nIDRegion	= isset( $aParams['nIDRegion'])		? $aParams['nIDRegion']		: 0;

			if ( !empty($nIDFirm) ) {
				$sFirm = $oFirms->getName($nIDFirm);
			}
						
			//--Sorting
			$sSortField = isset( $aParams['sfield'] ) 	? $aParams['sfield'] 	: 'created_time';
			$sSortType 	= isset( $aParams['stype'] ) 	? $aParams['stype'] 	: DBAPI_SORT_DESC;
			$sSortType 	= $sSortType == DBAPI_SORT_ASC ? 'ASC' : 'DESC';
			//--End Sorting
			
			//--Paging
			$nRowLimit 	= isset( $_SESSION['userdata']['row_limit'] ) ? $_SESSION['userdata']['row_limit'] : '';
			$nPage 		= isset( $aParams['current_page'] ) ? $aParams['current_page'] : '1';
			$nRowOffset = ( $nPage - 1 ) * $nRowLimit;
			//--End Paging
			//End Params
			
			$sQuery = $this->prepareQuery( $aParams, 0 );
			$sQueryTotal = $this->prepareQuery( $aParams, 1 );
			
			$rs = $db_finance_backup->Execute( $sQuery );
			
			$nRowTotal = $db_finance_backup->foundRows();
			
			$rsTotal = $db_finance_backup->Execute( $sQueryTotal );
			$aTotals = $rsTotal->getArray();
			
			$nTotalSum = 0;
			$nTotalOrders = 0;
			
			foreach( $aTotals as $totals )
			{
				$nTotalSum += $totals['total_sum'];
				$nTotalOrders += $totals['total_orders'];
			}
			
			if( !empty( $nIDScheme ) && isset( $aParams['robot'] ) )
			{
				$oDBFiltersTotals = new DBFiltersTotals();
				$aFiltersTotals = $oDBFiltersTotals->getFilterTotalsByIDFilter( $nIDScheme );
				
				$aStatisticValues = array();
				
				if( in_array( 'total_sum', $aFiltersTotals ) )
				{
					$aStatisticValues['total_sum']['name'] = 'Сума';
					$aStatisticValues['total_sum']['value'] = $nTotalSum;
					$aStatisticValues['total_sum']['data_format'] = DF_CURRENCY;
				}
				if( in_array( 'total_orders', $aFiltersTotals ) )
				{
					$aStatisticValues['total_orders']['name'] = 'Погасена Сума';
					$aStatisticValues['total_orders']['value'] = $nTotalOrders;
					$aStatisticValues['total_orders']['data_format'] = DF_CURRENCY;
				}
				
				return $aStatisticValues;
			}
			
			$oResponse->addTotal( 'total_sum', $nTotalSum );
			$oResponse->addTotal( 'orders_sum', $nTotalOrders );
			
			$oResponse->setField( 'chk', '', '' );
			$oResponse->setFieldData( 'chk', 'input', array( 'type' => 'checkbox', 'exception' => 'false' ) );
			
			$oResponse->setFormElement( "form1", "sel" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "mark_all" ), "--- Маркирай всички ---" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "unmark_all" ), "--- Отмаркирай всички ---" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "" ), "-----------------------------------------" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "person_account" ), "Плащане от персонална сметка" );
			
			$oDBBankAccounts = new DBBankAccounts();
			$nIDPerson = isset( $_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$aBankAccounts = $oDBBankAccounts->getByPersonForOperate( $nIDPerson );
			
			foreach( $aBankAccounts as $key => $value )
			{
				$oResponse->setFormElementChild( "form1", "sel", array( "value" => $key ), "Плащане от " . $value );
			}
			
			$oResponse->setField( "doc_num", "Номер", "Сортирай по номер" );
			$oResponse->setFieldLink( "doc_num", "openBuyDoc" );

			$oResponse->setField( "paid_type", "Вид", "Сортирай по начин на плащане" );
			
			if ( empty($nIDScheme) ) {
				$oResponse->setField( "doc_date",			"Дата",	"Сортирай по дата на документа",	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
				$oResponse->setField( "doc_type",			"Док.",		"Сортирай по тип на документа" );
				$oResponse->setField( "deliverer_name",		"Доставчик",			"Сортирай по доставчик" );
				$oResponse->setField( "rows_notes",			"Основание",		"Сортирай по редове-бележки");
				$oResponse->setField( "client_name",		"Клиент",				"Сортирай по клиент" );
				$oResponse->setField( "total_sum",			"Сума",					"Сортирай по сума",					NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				$oResponse->setFieldLink( "total_sum", "openDocFunds" );
				$oResponse->setField( "orders_sum",			"Погасена сума",		"Сортирай по погасена сума",		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				
				if ( !empty($nIDFirm) ) {
					$oResponse->setField("firm_sum",		$sFirm,				"Сортирай по {$sFirm}",				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}
								
				$oResponse->setField( "remain_sum",			"Дължима сума",			"Сортирай по дължима сума",			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				$oResponse->setField( "last_order_time",	"Последен ордер",		"Сортирай по последен ордер",		NULL, NULL, NULL, NULL );
				$oResponse->setField( "created_user",		"Създал",				"Сортирай по създал" );
				$oResponse->setField( "created_time",		"Създаден на",	"Сортирай по време на създаване");
			} else {
				$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
				$aVisibleFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nIDScheme );
				
				if( in_array( "show_date", $aVisibleFields ) )
				{
					$oResponse->setField( "doc_date", "Дата", "Сортирай по дата на документа", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
				}
				if( in_array( "show_type", $aVisibleFields ) )
				{
					$oResponse->setField( "doc_type", "Док.", "Сортирай по тип на документа" );
				}
				if( in_array( "show_deliverer", $aVisibleFields ) )
				{
					$oResponse->setField( "deliverer_name", "Доставчик", "Сортирай по доставчик" );
				}
				if( in_array( "show_client", $aVisibleFields ) )
				{
					$oResponse->setField( "client_name", "Клиент", "Сортирай по клиент" );
				}
				if( in_array( "show_total_sum", $aVisibleFields ) )
				{
					$oResponse->setField( "total_sum", "Сума", "Сортирай по сума", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
					$oResponse->setFieldLink( "total_sum", "openDocFunds" );
				}
				if( in_array( "show_orders_sum", $aVisibleFields ) )
				{
					$oResponse->setField( "orders_sum", "Погасена сума", "Сортирай по погасена сума", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}
				
				if ( !empty($nIDFirm) ) {
					$oResponse->setField("firm_sum",		$sFirm,				"Сортирай по {$sFirm}",				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}				
				
				if( in_array( "show_last_order" , $aVisibleFields ) )
				{
 					$oResponse->setField( "last_order_time", "Последен ордер", "Сортирай по последен ордер", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATETIME ) );
				}
				if( in_array( "show_rows_notes", $aVisibleFields ) )
				{
					$oResponse->setField( "rows_notes", "Основание", "Сортирай по редове-бележки");
				}
				if( in_array( "show_created_user", $aVisibleFields ) )
				{
 					$oResponse->setField( "created_user", "Създал", "Сортирай по създал" );
				}
				if( in_array( "show_created_time", $aVisibleFields ) )
				{
 					$oResponse->setField( "created_time", "Създаден на", "Сортирай по време на създаване");
				}
			}
			
			$aData 		= $rs->GetArray();
			$nFirmTotal	= 0;
			
			foreach ( $aData as $key => &$value ) {
				$sStyles 				= "";
				
				$tmpSum					= 0;
				
				if ( !empty($nIDFirm) ) {
					$nID				= $value['id'];
					$tmpSum				= $this->getSumByFirmIdRegId($nID, $nIDFirm, $nIDRegion);
					$value['firm_sum'] 	= floatval($tmpSum);
					$nFirmTotal			+= floatval($tmpSum);
				} else {
					$value['firm_sum'] 	= 0;
				}				
				
				if( $value['doc_status'] == 'canceled' )
				{
					$sStyles .= "font-style: italic; color: #969696;";
				}
				elseif( $value['doc_status'] == 'proforma' )
				{
					$sStyles .= "font-weight: bold;";
				}
				
				//Colouring
				if( $value['pay_status'] == 'not_paid' )
				{
					$sStyles .= "background-color: E6E6FF;";
				}
				if( $value['pay_status'] == "part_paid" )
				{
					$sStyles .= "background-color: E6F0FF;";
				}
				//End Colouring

				$paid_type_icon = $value['paid_type'] == "cash" ? "coins.gif" : "bank.gif";
				$paid_type_hint = $value['paid_type'] == "cash" ? "в брой" : "по банка";
				$value['paid_type'] = "";
				$oResponse->setDataAttributes( $key, 'paid_type', array("title" => $paid_type_hint, "style" => "{$sStyles} cursor: default; height: 18px; background: url(images/{$paid_type_icon}) center no-repeat;" ) );

				$doc_type_text = "";
				$doc_type_hint = "";
				switch ($value['doc_type']) {
                    case "kvitanciq":
                        $doc_type_text = 'К';
                        $doc_type_hint = 'Квитанция';
                        break;
                    case "oprostena":
                        $doc_type_text = 'ПФ';
                        $doc_type_hint = 'Проформа фактура';
                        break;
					case "faktura": 	
						$doc_type_text = 'Ф';
						$doc_type_hint = 'Фактура';
						break;
					case "kreditno izvestie": 	
						$doc_type_text = 'КИ';
						$doc_type_hint = 'Кредитно известие';
						break;
					case "debitno izvestie": 	
						$doc_type_text = 'ДИ';
						$doc_type_hint = 'Дебитно известие';
						break;
					case "dds": 	
						$doc_type_text = 'ДДС';
						$doc_type_hint = 'ДДС';
						break;
					case "salary": 	
						$doc_type_text = 'З';
						$doc_type_hint = 'Заплата';
						break;
					default:
						break;
				}
				$value['doc_type'] = $doc_type_text;
				$oResponse->setDataAttributes( $key, 'doc_type', array("title" => $doc_type_hint, "style" => "cursor: default; text-align: center;"));

				if (utf8_strlen($value['deliverer_name']) > 17 && substr( $aParams['api_action'], 0, 9 ) != "export_to"){
					$oResponse->setDataAttributes( $key, 'deliverer_name', array("style" => "cursor: default;","title" => $value['deliverer_name']));
					$value['deliverer_name'] = utf8_substr($value['deliverer_name'], 0, 17) . '...';
				}

				if (utf8_strlen($value['rows_notes']) > 15 && substr( $aParams['api_action'], 0, 9 ) != "export_to"){
					$oResponse->setDataAttributes( $key, 'rows_notes', array("style" => "cursor: default;", "title" => $value['rows_notes']));
					$value['rows_notes'] = utf8_substr($value['rows_notes'], 0, 15) . '...';
				}

				if ( $value['last_order_time'] == "0000-00-00 00:00:00" ) {
					$value['last_order_time'] = "";
				} else {
					$last_order_person_text = $value['last_order_person'];
					if (utf8_strlen($value['last_order_person']) > 8 && substr( $aParams['api_action'], 0, 9 ) != "export_to")
						$last_order_person_text = utf8_substr($value['last_order_person'], 0, 8) . '...';
					$value['last_order_time'] = date("d.m.Y H:i", strtotime($value['last_order_time']))." [". $last_order_person_text ."]";
					$oResponse->setDataAttributes( $key, 'last_order_time', array("style" => "cursor: default;","title" => $value['last_order_person']));
				}

				//created_user
				if (utf8_strlen($value['created_user']) > 8 && substr( $aParams['api_action'], 0, 9 ) != "export_to"){
					$oResponse->setDataAttributes( $key, 'created_user', array("style" => "cursor: default;","title" => $value['created_user']));
					$value['created_user'] = utf8_substr($value['created_user'], 0, 8) . '...';
				}

				//created_time
				$oResponse->setDataAttributes( $key, 'created_time', array("style" => "cursor: default;","title" => date("d.m.Y H:i:s", strtotime($value['created_time']))));
				$value['created_time'] = date("d.m.Y H:i", strtotime($value['created_time']));

				if( isset( $_SESSION['buy_rows'] ) )
				{
					if( in_array( $value['id'], $_SESSION['buy_rows'] ) )
					{
						$value['chk'] = 1;
					}
					else $value['chk'] = 0;
				}
				else $value['chk'] = 0;

				if( !empty( $sStyles ) )
				{
					$oResponse->setRowAttributes( $value['id'], array( "style" => $sStyles ) );
				}
				
				$value['doc_num'] = zero_padding( $value['doc_num'], 10 );
				$oResponse->setDataAttributes( $key, 'doc_num', array( 'style' => 'text-align:right;' ) );
				
				$value['remain_sum'] = $value['total_sum'] - $value['orders_sum'];
			}
			
			$oResponse->addTotal( 'firm_sum', $nFirmTotal );
			
			$sSortType = $sSortType == 'ASC' ? DBAPI_SORT_ASC : DBAPI_SORT_DESC;
			
			$oResponse->setData( $aData );
			
			/*
			foreach ($oResponse->oResult->aData as $key => &$value) {
				if ( $value['last_order_time'] == "0000-00-00 00:00:00" ) {
					$value['last_order_time'] = "";
				} else {
					$value['last_order_time'] = date("d.m.Y H:i:s", strtotime($value['last_order_time']))." [".$value['last_order_person']."]";
				}
				
				//$oResponse->setDataAttributes( $key, "last_order_time", array("style" => "cursor: pointer; padding-right: 20px;") );
			}
			*/
						
			$oResponse->setSort( $sSortField, $sSortType );
			$oResponse->setPaging( $nRowLimit, $nRowTotal, $nPage );
		}

		public function prepareQuery( $aParams, $TotalQuery = 0 ) {
			global $db_finance, $db_name_sod, $db_name_personnel;
			
			$oDBBuyDocs = new DBBuyDocs();
			
			//Params
			//$sClient 	= isset( $aParams['sClient'] ) 		? $aParams['sClient'] 			: '';
			$nNum 		= isset( $aParams['nNum'] ) 		? floatval($aParams['nNum'])	: 0;
			$nIDScheme 	= isset( $aParams['schemes'] ) 		? $aParams['schemes'] 			: 0;
			$nIDClient 	= isset( $aParams['nIDClient'] ) 	? $aParams['nIDClient'] 		: 0;
			$nIDFirm 	= isset( $aParams['nIDFirm'] ) 		? $aParams['nIDFirm'] 			: 0;
			$nIDRegion  = isset( $aParams['nIDRegion'] ) 	? $aParams['nIDRegion'] 		: 0;
			
			$nTimeFrom	= jsDateToTimestamp( $aParams['sFromDate'] );
			$nTimeTo 	= jsDateEndToTimestamp( $aParams['sToDate'] );
			
			//--Sorting
			$sSortField = isset( $aParams['sfield'] ) 	? $aParams['sfield'] 	: 'created_time';
			$sSortType 	= isset( $aParams['stype'] ) 	? $aParams['stype'] 	: DBAPI_SORT_DESC;
			$sSortType 	= $sSortType == DBAPI_SORT_ASC 	? 'ASC' : 'DESC';
			//--End Sorting
			
			$nRowLimit 	= isset( $_SESSION['userdata']['row_limit'] ) ? $_SESSION['userdata']['row_limit'] : '20';
			$nPage 		= isset( $aParams['current_page'] ) ? $aParams['current_page'] : '1';
			$nRowOffset = ( $nPage - 1 ) * $nRowLimit;
			
			$aParams['sClientName'] = addslashes($aParams['sClientName']);
			
			//End Params
			
			if( $TotalQuery == 1 )
			{
				$sQuery = "
					SELECT
						t.total_sum AS total_sum,
						t.orders_sum AS total_orders
				";
			}
			else
			{
				$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						DISTINCT t.id,
						t.doc_num,
						t.doc_date,
						t.doc_type AS doc_type,
						t.paid_type AS paid_type,
						t.doc_status,
						t.deliverer_name,
						t.client_name,
						t.total_sum,
						t.orders_sum,
						t.last_order_time,
						CONCAT_WS(' ', pu.fname, pu.lname) as last_order_person,
						CASE
							WHEN ( t.total_sum = t.orders_sum ) THEN 'paid'
							WHEN ( t.total_sum != t.orders_sum AND t.orders_sum != '0.00' ) THEN 'part_paid'
							WHEN ( t.orders_sum = '0.00' ) THEN 'not_paid'
						END AS pay_status,
						CONCAT_WS( ' ', pc.fname, pc.mname, pc.lname ) AS created_user,
						t.created_time,
						t.to_arc,
						GROUP_CONCAT( DISTINCT IF( bdr.note != '', bdr.note, NULL ) SEPARATOR '\n' ) AS rows_notes,
						GROUP_CONCAT( DISTINCT bdr.id_nomenclature_expense ) AS nomenclatures
				";
			}
			
			$sQuery .= "
				FROM
					<table> t
				LEFT JOIN
					" . PREFIX_BUY_DOCS_ROWS . "<yearmonth> bdr ON bdr.id_buy_doc = t.id
				LEFT JOIN
					{$db_name_sod}.clients c ON c.id = t.id_deliverer
				LEFT JOIN
					{$db_name_personnel}.personnel pc ON pc.id = t.created_user
				LEFT JOIN
					{$db_name_personnel}.personnel pu ON pu.id = t.updated_user			
				LEFT JOIN {$db_name_sod}.offices off_row ON off_row.id = bdr.id_office				
				WHERE
					t.to_arc = 0
			";
			
			// Pavel - anulirani
			if ( $TotalQuery == 1 ) {
				$sQuery .= " AND t.doc_status != 'canceled' \n";
			}
			
			if ( !empty($nIDFirm) ) {
				$sQuery .= " AND off_row.id_firm = {$nIDFirm} ";
			}			
			if ( !empty($nIDRegion)){
				$sQuery .= " AND off_row.id = {$nIDRegion} ";
			}
			//Common Filter
			if ( empty($nIDScheme) ) {
				if ( !empty($nNum) ) {
					$sQuery .= " AND t.doc_num LIKE '%{$nNum}%' \n ";
				}
				
//				if( !empty( $sClient ) )
//				{
//					$sQuery .= " AND t.client_name = {$db_finance->Quote( $sClient )} \n";
//				}

				if ( !empty($aParams['sClientName']) ) {
					// !empty($nIDClient) &&
					//$sQuery .= " AND t.id_deliverer = {$nIDClient} \n";
					$sQuery .= " AND t.deliverer_name LIKE '%{$aParams['sClientName']}%' \n";
				}	
				
				if( !empty( $aParams['sFromDate'] ) )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= {$nTimeFrom} \n";
				}
				
				if( !empty( $aParams['sToDate'] ) )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= {$nTimeTo} \n";
				}
			}
			//End Common Filter
			
			//Detailed Filter
			if ( !empty($nIDScheme) ) {
				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nIDScheme );
				
				if ( !empty($nNum) ) {
					$sQuery .= " AND t.doc_num LIKE '%{$nNum}%' \n ";
				} else {
					if ( !empty( $aFilterParams['num_from'] ) ) {
						$sQuery .= " AND t.doc_num >= {$aFilterParams['num_from']} \n";
					}
					
					if( !empty( $aFilterParams['num_to'] ) )
					{
						$sQuery .= " AND t.doc_num <= {$aFilterParams['num_to']} \n";
					}
				}
				
				if( !empty( $aFilterParams['doc_type'] ) )
				{
					$sQuery .= " AND t.doc_type = {$db_finance->Quote( $aFilterParams['doc_type'] )} \n";
				}
				if( !empty( $aFilterParams['price_from'] ) && $aFilterParams['price_from'] != '0.00' )
				{
					$sQuery .= " AND t.total_sum >= {$aFilterParams['price_from']} \n";
				}
				if( !empty( $aFilterParams['price_to'] ) && $aFilterParams['price_to'] != '0.00' )
				{
					$sQuery .= " AND t.total_sum <= {$aFilterParams['price_to']} \n";
				}
				if( !empty( $aFilterParams['status'] ) )
				{
					if( $aFilterParams['status'] == 'canceled' )
					{
						$sQuery .= " AND t.doc_status = 'canceled' \n";
					}
					else
					{
						$sQuery .= " AND t.doc_status != 'canceled' \n";
					}
				}
				if( !empty( $aFilterParams['paid_status'] ) )
				{
					switch( $aFilterParams['paid_status'] )
					{
						case 'paid':				$sQuery .= " AND t.total_sum = t.orders_sum \n"; break;
						case 'part_paid':			$sQuery .= " AND t.total_sum != t.orders_sum AND t.orders_sum != '0.00' \n"; break;
						case 'not_paid':			$sQuery .= " AND t.orders_sum = '0.00' \n"; break;
						case 'not_or_part_paid':	$sQuery .= " AND t.total_sum != t.orders_sum \n"; break;
					}
				}
				if( !empty( $aFilterParams['paid_type'] ) )
				{
					$sQuery .= " AND t.paid_type = {$db_finance->Quote( $aFilterParams['paid_type'] )} \n";
				}
				if( $aFilterParams['doc_date_from'] != '0000-00-00 00:00:00' )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.doc_date ) >= UNIX_TIMESTAMP( '{$aFilterParams['doc_date_from']}' ) \n";
				}
				if( $aFilterParams['doc_date_to'] != '0000-00-00 00:00:00' )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.doc_date ) <= UNIX_TIMESTAMP( '{$aFilterParams['doc_date_to']}' ) \n";
				}
				if( $aFilterParams['last_order_from'] != '0000-00-00 00:00:00' )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.last_order_time ) >= UNIX_TIMESTAMP( '{$aFilterParams['last_order_from']}' ) \n";
				}
				if( $aFilterParams['last_order_to'] != '0000-00-00 00:00:00' )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.last_order_time ) <= UNIX_TIMESTAMP( '{$aFilterParams['last_order_to']}' ) \n";
				}
				
				// created time
				if (
						( isset( $aFilterParams['create_date_period'] ) && !empty( $aFilterParams['create_date_period'] ) ) ||
						( $aFilterParams['create_date_from'] != '0000-00-00 00:00:00' ) ||
						( $aFilterParams['create_date_to'] != '0000-00-00 00:00:00' )
					){
					
					// relative time
					if( isset( $aFilterParams['create_date_period'] ) && !empty( $aFilterParams['create_date_period'] ) ) {
						$nLimit1 = date( "Y-m-d H:i:s", strtotime( "-{$aFilterParams['create_date_period']} MONTHS" ) );
						$nLimit2 = date( "Y-m-d H:i:s" );
					
						$sQuery .= "
							AND UNIX_TIMESTAMP( t.created_time ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.created_time ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
						";
					} else {
						// from-to time
						if( $aFilterParams['create_date_from'] != '0000-00-00 00:00:00' ) {
							$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= UNIX_TIMESTAMP( '{$aFilterParams['create_date_from']}' ) \n";
						}
						if( $aFilterParams['create_date_to'] != '0000-00-00 00:00:00' ) {
							$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= UNIX_TIMESTAMP( '{$aFilterParams['create_date_to']}' ) \n";
						}
					}
				} else {
					if( !empty( $aParams['sFromDate'] ) ) {
						$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= {$nTimeFrom} \n";
					}
					
					if( !empty( $aParams['sToDate'] ) ) {
						$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= {$nTimeTo} \n";
					}
				}
				
				/*
				if( !empty( $aParams['sFromDate'] ) )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= {$nTimeFrom} \n";
				}
				else
				{
					if( $aFilterParams['create_date_from'] != '0000-00-00 00:00:00' )
					{
						$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= UNIX_TIMESTAMP( '{$aFilterParams['create_date_from']}' ) \n";
					}
				}
				
				if( !empty( $aParams['sToDate'] ) )
				{
					$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= {$nTimeTo} \n";
				}
				else
				{
					if( $aFilterParams['create_date_to'] != '0000-00-00 00:00:00' )
					{
						$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= UNIX_TIMESTAMP( '{$aFilterParams['create_date_to']}' ) \n";
					}
				}
				
				if( isset( $aFilterParams['create_date_period'] ) && !empty( $aFilterParams['create_date_period'] ) )
				{
					$nLimit1 = date( "Y-m-d H:i:s", strtotime( "-{$aFilterParams['create_date_period']} MONTHS" ) );
					$nLimit2 = date( "Y-m-d H:i:s" );
					
					$sQuery .= "
						AND UNIX_TIMESTAMP( t.created_time ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.created_time ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
					";
				}
				*/

				if( isset( $aFilterParams['doc_date_period'] ) && !empty( $aFilterParams['doc_date_period'] ) )
				{
					$nLimit1 = date( "Y-m-d H:i:s", strtotime( "-{$aFilterParams['doc_date_period']} MONTHS" ) );
					$nLimit2 = date( "Y-m-d H:i:s" );
					
					$sQuery .= "
						AND UNIX_TIMESTAMP( t.doc_date ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.doc_date ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
					";
				}
				
				if( isset( $aFilterParams['last_order_period'] ) && !empty( $aFilterParams['last_order_period'] ) )
				{
					$nLimit1 = date( "Y-m-d H:i:s", strtotime( "-{$aFilterParams['last_order_period']} MONTHS" ) );
					$nLimit2 = date( "Y-m-d H:i:s" );
					
					$sQuery .= "
						AND UNIX_TIMESTAMP( t.last_order_time ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.last_order_time ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
					";
				}
				
				if( !empty( $aFilterParams['for_fuel'] ) )
				{
					$sQuery .= " AND t.for_fuel = 1 \n";
				}
				if( !empty( $aFilterParams['for_gsm'] ) )
				{
					$sQuery .= " AND t.for_gsm = 1 \n";
				}

				if ( !empty($nIDClient) && !empty($aParams['sClientName']) ) {
					$sQuery .= " AND t.deliverer_name LIKE '%{$aParams['sClientName']}%' \n";
				}					
				
				if ( isset($sClient) && !empty($sClient) ) {
					$sQuery .= " AND t.client_name = {$db_finance->Quote( $sClient )} \n";
				}
				else
				{
					if( !empty( $aFilterParams['client_name'] ) )
					{
						$aFilterParams['client_name'] = addslashes( $aFilterParams['client_name'] );
						$sQuery .= " AND t.client_name = '{$aFilterParams['client_name']}' \n";
					}
				}
				
				if( !empty( $aFilterParams['deliverer_name'] ) )
				{
					$sDelivererName = addslashes( $aFilterParams['deliverer_name'] );
					$sQuery .= " AND c.name LIKE '%{$sDelivererName}%'";
				}
				if( !empty( $aFilterParams['deliverer_ein'] ) )
				{
					$sDelivererEin = addslashes( $aFilterParams['deliverer_ein'] );
					$sQuery .= " AND c.invoice_ein LIKE '%{$sDelivererEin}%' \n";
				}
				if( !empty( $aFilterParams['deliverer_eik'] ) )
				{
					$sQuery .= " AND c.id = {$aFilterParams['deliverer_eik']} \n";
				}
				if( isset( $aFilterParams['ids_nomenclatures'] ) && !empty( $aFilterParams['ids_nomenclatures'] ) )
				{
					$aIDNomenclatures = explode( ",", $aFilterParams['ids_nomenclatures'] );
					if( !empty( $aIDNomenclatures ) )
					{
						foreach( $aIDNomenclatures as &$sValue )
						{
							$sValue = "bdr.id_nomenclature_expense = {$sValue}";
						}
						
						$sClauseNomenclatures = implode( " OR ", $aIDNomenclatures );
						$sQuery .= " AND ( $sClauseNomenclatures ) \n ";
					}
				}
			}
			//End Detailed Filter
			
			$sQuery .= " GROUP BY t.id \n";
			
			$oDBBuyDocs->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );
			
			if( $TotalQuery == 0 )
			{
				$sQuery .= "
					ORDER BY {$sSortField} {$sSortType}
				";
				
				//$sQuery .= "
				//	LIMIT {$nRowOffset}, {$nRowLimit}
				//";
				
				if ( isset($aParams['api_action']) && $aParams['api_action'] == "export_to_xls" ) {
					
					//
				} else {
					$sQuery .= "
						LIMIT {$nRowOffset},{$nRowLimit}
					";
				}				
			}

			return $sQuery;
		}
		
		public function getSumByFirmIdRegId($nID, $nIDFirm, $nIDRegion) {
			global $db_name_finance, $db_name_sod;

			if ( $this->isValidID($nID) ) {
				$tName 	= PREFIX_BUY_DOCS_ROWS.substr($nID, 0, 6);
			} else {				
				return array();
			}
						
			$sQuery = "
				SELECT 
					SUM(sd.total_sum) as sum
				FROM {$db_name_finance}.$tName sd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = sd.id_office )
				WHERE sd.id_buy_doc = {$nID}
					AND o.id_firm = {$nIDFirm}
					";
			if($nIDRegion) $sQuery .= "AND o.id = {$nIDRegion}";
			
			return $this->selectOne2($sQuery);
		}		
		
		public function makePayment($nIDBuyDoc,$sAccountType,$nIDAccount,DBResponse $oResponse) {
			
			$aBuyDoc = array();
			$this->getRecord($nIDBuyDoc,$aBuyDoc);
			
			if( ($aBuyDoc['total_sum'] - $aBuyDoc['orders_sum']) > 0 && $aBuyDoc['doc_status'] == 'final') {
				$nOrderSum = $aBuyDoc['total_sum'] - $aBuyDoc['orders_sum'];
			
				$oDBSystem = new DBSystem();
				$oDBAccountStates = new DBAccountStates();				
				$oDBOrders = new DBOrders();
				$oDBBuyDocsRows = new DBBuyDocsRows();
				
				$aSystem = array();
				$aSystem = $oDBSystem->getRow();
				$nNumOrder = $aSystem['last_num_order'] + 1;
				
				$aAccountState = array();
				if($sAccountType == 'bank') {
					$aAccountState = $oDBAccountStates->getRow($sAccountType,0,$nIDAccount);
				} else {
					$aAccountState = $oDBAccountStates->getRow($sAccountType,$nIDAccount);
				}
				
				if(!isset($aAccountState['current_sum']) || $aAccountState['current_sum'] < $nOrderSum) {
					$oResponse->setError(4,"Недостатъчна наличност в сметката");
					return 'no_money';
				}
				$aAccountState['current_sum'] -= $nOrderSum;
			
				$oDBAccountStates->update($aAccountState);
				
				$aOrder = array();
				$aOrder['num'] = $nNumOrder;
				$aOrder['order_type'] = 'expense';
				$aOrder['order_date'] = time();
				$aOrder['order_sum'] = -$nOrderSum;
				$aOrder['account_type'] = $sAccountType;
				$aOrder['id_person'] = $nIDAccount;
				$aOrder['account_sum'] = $aAccountState['current_sum'];
				if($sAccountType == 'bank') {
					$aOrder['bank_account_id'] = $nIDAccount;
				}
				$aOrder['doc_id'] = $nIDBuyDoc;
				$aOrder['doc_type'] = 'buy';
			
				$oDBOrders->update($aOrder,NULL,true,$nIDBuyDoc);
				$oDBSystem->setLastNumOrder($nNumOrder);
				
				$aBuyDoc['orders_sum'] = $aBuyDoc['total_sum'];
				$aBuyDoc['last_order_id'] = $aOrder['id'];
				$aBuyDoc['last_order_time'] = time();
				
				$this->update($aBuyDoc);
				
				$aIDBuyDocRows = $oDBBuyDocsRows->getByIDBuyDoc($nIDBuyDoc);
				
				foreach ( $aIDBuyDocRows as $v) {
					$v['paid_sum'] = $v['total_sum'] / $aBuyDoc['total_sum'] * $nOrderSum;
					$v['paid_date'] = time();
					$oDBBuyDocsRows->update($v);
				}
				
				return $aBuyDoc['doc_num'];
			}
			return '';
		}

        public function getDocsByClientID($nIDClient) {
            global $db_name_finance;

            $sQuery = "
				SELECT
					id,
					doc_num
				FROM <table>
				WHERE  id_deliverer = {$nIDClient}
					AND (doc_type = 'faktura' OR doc_type = 'oprostena')
					AND doc_status != 'canceled'
			";

            $this->makeUnionSelect( $sQuery, 0, 0 );

//			die($sQuery);

            return $this->selectAssoc2($sQuery);
//			$sQuery .= " LIMIT 1 ";

//			$nID = $this->selectOne2( $sQuery );
//
//			if ( !empty($nID) && is_numeric($nID) ) {
//				return $nID;
//			} else {
//				return 0;
//			}
//
//			return 0;
        }

		public function getDoc( $nID ) {
			global $db_name_personnel, $db_name_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTable = PREFIX_BUY_DOCS.substr($nID, 0, 6);
			} else {
				return array();
			}
			
			$sQuery = "
				SELECT
					sd.*,
					CONCAT(CONCAT_WS(' ',p_cr.fname,p_cr.mname,p_cr.lname),' [',DATE_FORMAT(sd.created_time,'%d.%m.%Y %H:%i.%s'),']') AS created,	
					CONCAT(CONCAT_WS(' ',p_up.fname,p_up.mname,p_up.lname),' [',DATE_FORMAT(sd.updated_time,'%d.%m.%Y %H:%i.%s'),']') AS updated				
				FROM {$db_name_finance}.{$sTable} sd
				LEFT JOIN {$db_name_personnel}.personnel p_cr ON p_cr.id = sd.created_user
				LEFT JOIN {$db_name_personnel}.personnel p_up ON p_up.id = sd.updated_user
				WHERE sd.id = {$nID}
			";
			
			$oRs = $this->_oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->fields : array();
		}

		public function getDocByDocNumAndClientID($nDocNum, $nIDClient) {
			global $db_name_finance;

			if ( empty($nDocNum) )
				return 0;

			$sQuery = "
				SELECT 
					id
				FROM <table> 
				WHERE doc_num = {$nDocNum}
					AND id_deliverer = {$nIDClient}
					AND doc_type != 'kvitanciq'
					AND doc_status != 'canceled'
			";

			$this->makeUnionSelect( $sQuery, 0, 0 );

			$sQuery .= " LIMIT 1 ";

			$nID = $this->selectOne2( $sQuery );

			if ( !empty($nID) && is_numeric($nID) ) {
				return $nID;
			} else {
				return 0;
			}
			
			return 0;
		}

        public function getCashierByIDPerson() {
            global $db_name_finance;

            $nIDPerson 	    = $this->getPerson();

            if ( empty($nIDPerson) || !is_numeric($nIDPerson) ) {
                return [];
            }

            $sQuery = "
                SELECT
                    *
                FROM {$db_name_finance}.cashier
                WHERE to_arc = 0
                    AND id_person = {$nIDPerson}
                LIMIT 1
			";

            $aData = $this->selectOne( $sQuery );

            if ( !empty($aData) ) {
                return $aData;
            } else {
                return [];
            }
        }
	}
?>