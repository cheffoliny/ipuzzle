<?php
	
	class DBSalesDocs extends DBMonthTable {
		
		function __construct() {
			
			global $db_name_finance,$db_finance;
			
			parent::__construct($db_name_finance,PREFIX_SALES_DOCS,$db_finance);
		}
		
		public function getReport( $aParams, DBResponse $oResponse = NULL ) {
			global $db_finance, $db_finance_backup;
			
			$oFirms 	= new DBFirms();
            $DBEasypayProvider = new DBEasypayProvider();
			$sFirm		= "";
			$nFirmTotal	= 0;

            $aPayProvider = $DBEasypayProvider->getAllAssoc();
			
			//Params
			//$sDeliverer = isset( $aParams['sDeliverer'] ) 	? $aParams['sDeliverer'] 	: '';
			$nNum 		= isset( $aParams['nNum'] ) 		? $aParams['nNum'] 			: '';
			$nIDScheme 	= isset( $aParams['schemes'] ) 		? $aParams['schemes'] 		: 0;
			$nIDClient 	= isset( $aParams['nIDClient'] ) 	? $aParams['nIDClient'] 	: 0;
			$nIDFirm 	= isset( $aParams['nIDFirm'] ) 		? $aParams['nIDFirm'] 		: 0;
			
			if ( !empty($nIDFirm) ) {
				$sFirm = $oFirms->getName($nIDFirm);
			}
			
			//--Sorting
			if ( isset($aParams['sfield']) ) {
				$sSortField = $aParams['sfield'];
			} else {
				$sSortField = "created_time";
				$sSortType	= DBAPI_SORT_DESC;
			}

			$sSortType 	= isset( $aParams['stype'] ) 	? $aParams['stype'] 	: DBAPI_SORT_DESC;
			$sSortType 	= $sSortType == DBAPI_SORT_ASC ? 'ASC' : 'DESC';
			//--End Sorting
			
			//--Paging
			$nRowLimit 	= isset( $_SESSION['userdata']['row_limit'] ) ? $_SESSION['userdata']['row_limit'] : '20';
			$nPage 		= isset( $aParams['current_page'] ) ? $aParams['current_page'] : '1';
			$nRowOffset = ( $nPage - 1 ) * $nRowLimit;
			//--End Paging
			//End Params
			
			$sQuery 		= $this->prepareQuery( $aParams, 0 );
			$sQueryTotal 	= $this->prepareQuery( $aParams, 1 );

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
			//$oResponse->addTotal( 'firm_sum', $nFirmTotal );
			
			$oResponse->setField( 'chk', '', '' );
			$oResponse->setFieldData( 'chk', 'input', array( 'type' => 'checkbox', 'exception' => 'false' ) );
			
			$oResponse->setFormElement( "form1", "sel" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "mark_all" ), "--- Маркирай всички ---" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "unmark_all" ), "--- Отмаркирай всички ---" );
			$oResponse->setFormElementChild( "form1", "sel", array( "value" => "" ), "-----------------------------------------" );
			//$oResponse->setFormElementChild("form1","sel",array("value" => "person_account"),"Плащане в персонална сметка");
			
			$oDBBankAccounts = new DBBankAccounts();
			$nIDPerson = isset( $_SESSION['userdata']['id_person'] ) ? $_SESSION['userdata']['id_person'] : 0;
			$aBankAccounts = $oDBBankAccounts->getByPersonForOperate( $nIDPerson );
			$isEasyPay = false;

			foreach( $aBankAccounts as $key => $value )
			{
                if ( $key == 30 ) {
                    $isEasyPay = true;
                }
				$oResponse->setFormElementChild( "form1", "sel", array( "value" => $key ), "Плащане в " . $value );
			}
			
			if( !empty( $nIDScheme ) )
			{
				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nIDScheme );
				
				if( isset( $aFilterParams['email_send'] ) 	&& !empty( $aFilterParams['email_send'] ) 	&&
					isset( $aFilterParams['doc_type'] ) 	&& $aFilterParams['doc_type'] == 'faktura' 	)
				{
					$oResponse->setFormElementChild( 'form1', 'sel', array( "value" => '' ), '-----------------------------------------' );
					$oResponse->setFormElementChild( 'form1', 'sel', array( "value" => "gen_pdfs" ), 'Генериране на PDF-и' );
				}
			}

			$nShowNewSale = 0;

			if(isset($_SESSION['userdata']['access_right_levels']['dev_on_production'])) {
                $nShowNewSale = 1;
            }

			$oResponse->setField( "doc_num", "Номер", "Сортирай по номер" );

			$oResponse->setFieldLink( "doc_num", "openSaleDoc" );

			// Павел
			$oResponse->setField( "paid_type", "Вид", "Сортирай по начин на плащане");
							
			if ( empty($nIDScheme) ) {
				$oResponse->setField( "doc_date",			"Дата",				"Сортирай по дата на документа", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
				$oResponse->setField( "doc_type",			"Док.",				"Сортирай по тип на документа");
				$oResponse->setField( "deliverer_name",		"Доставчик",		"Сортирай по доставчик" );
				$oResponse->setField( "client_name",		"Клиент",			"Сортирай по клиент" );
				$oResponse->setField( "total_sum",			"Сума",				"Сортирай по сума", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				$oResponse->setField( "orders_sum",			"Погасена сума",	"Сортирай по погасена сума",		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				
				if ( !empty($nIDFirm) ) {
					$oResponse->setField("firm_sum",		$sFirm,				"Сортирай по {$sFirm}",				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}
				
				$oResponse->setField( "remain_sum",			"Дължима сума",		"Сортирай по дължима сума",			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				$oResponse->setField( "last_order_time",	"Последен ордер",	"Сортирай по последен ордер");
				$oResponse->setField( "created_user",		"Създал",			"Сортирай по създал");
				$oResponse->setField( "created_time",		"Създаден на",		"Сортирай по време на създаване");
			} else {
				$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
				
				$aVisibleFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $nIDScheme );

                if( in_array( "show_date", $aVisibleFields ) )
                {
                    $oResponse->setField( "doc_date", "Дата", "Сортирай по дата на документа", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
                }
				if( in_array( "show_type", $aVisibleFields ) )
				{
					$oResponse->setField( "doc_type",			"Док.",		"Сортирай по тип на документа");
				}
				if( in_array( "show_deliverer", $aVisibleFields ) )
				{
					$oResponse->setField( "deliverer_name", "Доставчик", "Сортирай по доставчик" );
				}

                if( in_array( "show_client_id", $aVisibleFields ) )
                {
                    $oResponse->setField( "client_id", "ID", "Сортирай по клиент", NULL, NULL, NULL, NULL );
                }

				if( in_array( "show_client", $aVisibleFields ) )
				{
					$oResponse->setField( "client_name", "Клиент", "Сортирай по клиент" );
				}
				if( in_array( "show_total_sum", $aVisibleFields ) )
				{
					$oResponse->setField( "total_sum", "Сума", "Сортирай по сума", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}
				if( in_array( "show_orders_sum", $aVisibleFields ) )
				{
					$oResponse->setField( "orders_sum", "Погасена сума", "Сортирай по погасена сума", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}

                if( in_array( "show_date_easypay", $aVisibleFields ) )
                {
                    $oResponse->setField( "easypay_date", "EasyPay", "Сортирай по плащане през EasyPay", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
                }
				
				if ( !empty($nIDFirm) ) {
					$oResponse->setField("firm_sum",		$sFirm,				"Сортирай по {$sFirm}",				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
				}				
				
				if( in_array( "show_last_order", $aVisibleFields ) )
				{
 					$oResponse->setField( "last_order_time", "Последен ордер", "Сортирай по последен ордер");
				}
				if( in_array( "show_deal_office", $aVisibleFields ) )
				{
 					$oResponse->setField( "deal_office", "Място на сделката", "Сортирай по последен място на сделката", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
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
			
			foreach( $aData as $key => &$value ) {
				$sStyles 	= "";
				$tmpSum		= 0;
				
				if ( !empty($nIDFirm) ) {
					$nID				= $value['id'];
					$tmpSum				= $this->getSumByFirmId($nID, $nIDFirm);
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

				$paid_type_icon = $value['paid_type'] == "cash" ? "coins.gif" : "bank.png";
                $paid_type_hint = $value['paid_type'] == "cash" ? "в брой" : "по банка";

                if ( $value['id_bank_epayment'] > 0 && $value['epay_provider'] > 0 ) {



                    if(isset($aPayProvider[$value['epay_provider']])) {
                        $paid_type_icon = strtolower($aPayProvider[$value['epay_provider']]['name']).".png";
                        $paid_type_hint = "Електронно плащане през ".$aPayProvider[$value['epay_provider']]['name'];
                    }

//                    switch ($value['epay_provider']) {
//                        case 1:
//                            $paid_type_icon = "easypay.png";
//                            $paid_type_hint = "Електронно плащане през EasyPay!";
//                            break;
//
//                        case 2:
//                            $paid_type_icon = "fastpay.png";
//                            $paid_type_hint = "Електронно плащане през FastPay!";
//                            break;
//
//                        case 3:
//                            $paid_type_icon = "cashterminal.png";
//                            $paid_type_hint = "Електронно плащане през CashTerminal!";
//                            break;
//
//                        case 4:
//                            $paid_type_icon = "transcard.png";
//                            $paid_type_hint = "Електронно плащане през TansCard!";
//                            break;
//
//                        default:
//                            $paid_type_icon = "easypay.png";
//                            $paid_type_hint = "Електронно плащане през EasyPay!";
//                            break;
//                    }
                }

				$value['paid_type'] = "";
				$oResponse->setDataAttributes( $key, 'paid_type', array("title" => $paid_type_hint, "style" => "{$sStyles} cursor: default; height: 18px; background: url(images/{$paid_type_icon}) center no-repeat;" ) );

				$doc_type_text = "";
				$doc_type_hint = "";
				switch ($value['doc_type']) {
					case "kvitanciq": 	
						$doc_type_text = 'ПФ';
						$doc_type_hint = 'Проформа фактура';
						break;
					case "oprostena": 	
						$doc_type_text = 'К';
						$doc_type_hint = 'Квитанция';
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
					default:
						break;
				}
				$value['doc_type'] = $doc_type_text;
				$oResponse->setDataAttributes( $key, 'doc_type', array("title" => $doc_type_hint, "style" => "cursor: default; text-align: center;"));

				if (utf8_strlen($value['client_name']) > 17 && substr( $aParams['api_action'], 0, 9 ) != "export_to"){
					$oResponse->setDataAttributes( $key, 'client_name', array("style" => "cursor: default;","title" => $value['client_name']));
					$value['client_name'] = utf8_substr($value['client_name'], 0, 17) . '...';
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
				
				if( isset( $_SESSION['sales_rows'] ) )
				{
					if( in_array( $value['id'], $_SESSION['sales_rows'] ) )
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
				
				if ( $value['id_bank_epayment'] != 0 && !$isEasyPay ) {
					$value['chk'] = 0;
					$oResponse->setDataAttributes( $key, "chk", array("disabled" => "disabled", "style" => "visibility: hidden;") );
				}
			}
			
			$oResponse->addTotal( 'firm_sum', $nFirmTotal );
			$sSortType = $sSortType == 'ASC' ? DBAPI_SORT_ASC : DBAPI_SORT_DESC;
			
			$oResponse->setData( $aData );
			
			/*
			foreach ($oResponse->oResult->aData as $key => &$value) {
				if ( $value['last_order_time'] == "0000-00-00 00:00:00" ) {
					$value['last_order_time'] = "";
				} else {
					$value['last_order_time'] = date("d.m.Y H:i", strtotime($value['last_order_time']))." [".$value['last_order_person']."]";
				}
				
				//$oResponse->setDataAttributes( $key, "last_order_time", array("style" => "cursor: pointer; padding-right: 20px;") );
			}
			*/
			
			$oResponse->setSort( $sSortField, $sSortType );
			$oResponse->setPaging( $nRowLimit, $nRowTotal, $nPage );
		}

        public function getSumByFirmId($nID, $nIDFirm) {
            global $db_name_finance, $db_name_sod;

            if ( $this->isValidID($nID) ) {
                $tName 	= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);
            } else {
                return array();
            }

            $sQuery = "
				SELECT 
					SUM(sd.total_sum) as sum
				FROM {$db_name_finance}.$tName sd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = sd.id_office )
				WHERE sd.id_sale_doc = {$nID}
					AND o.id_firm = {$nIDFirm}
			";

            return $this->selectOne2($sQuery);
        }

        public function getIsEasyPay($nID) {
            global $db_name_finance;

            if ( $this->isValidID($nID) ) {
                $tName 	= PREFIX_SALES_DOCS.substr($nID, 0, 6);
            } else {
                return 0;
            }

            $sQuery = "
				SELECT
					id_bank_epayment as id_bank
				FROM {$db_name_finance}.$tName
				WHERE id = {$nID}
			";

            return $this->selectOne2($sQuery);
        }
		
		public function prepareQuery( $aParams, $TotalQuery = 0 ) {
			global $db_finance, $db_name_sod, $db_name_personnel;
			
			$oDBSalesDocs 	= new DBSalesDocs();
			$oDBOrders 		= new DBOrders();
			$sRegions		= implode(",", $_SESSION['userdata']['access_right_regions']);
			
			if ( empty($sRegions) ) {
				$sRegions = "-1";
			}
			
			//Params
			//$sDeliverer = isset( $aParams['sDeliverer'] ) 	? $aParams['sDeliverer'] 		: '';
			$nNum 		= isset( $aParams['nNum'] ) 		? floatval($aParams['nNum']) 	: 0;
			$nIDScheme 	= isset( $aParams['schemes'] ) 		? $aParams['schemes'] 			: 0;
			$nIDClient 	= isset( $aParams['nIDClient'] ) 	? $aParams['nIDClient'] 		: 0;
			$nIDFirm 	= isset( $aParams['nIDFirm'] ) 		? $aParams['nIDFirm'] 			: 0;
			
			$nTimeFrom	= jsDateToTimestamp( $aParams['sFromDate'] );
			$nTimeTo 	= jsDateEndToTimestamp( $aParams['sToDate'] );
			
			//--Sorting
			$sSortField = isset( $aParams['sfield'] ) 	? $aParams['sfield'] 	: 'created_time';
			$sSortType 	= isset( $aParams['stype'] ) 	? $aParams['stype'] 	: DBAPI_SORT_DESC;
			$sSortType 	= $sSortType == DBAPI_SORT_ASC 	? 'ASC' : 'DESC';
			//--End Sorting
			
			//--Paging
			$nRowLimit 	= isset( $_SESSION['userdata']['row_limit'] ) ? $_SESSION['userdata']['row_limit'] : '20';
			$nPage 		= isset( $aParams['current_page'] ) ? $aParams['current_page'] : '1';
			$nRowOffset = ( $nPage - 1 ) * $nRowLimit;
			//--End Paging
			
			$aParams['sClientName'] = addslashes($aParams['sClientName']);
			//End Params
			
			// ако във филтъра има дадата на генериране на документа сетвам нея дата
			if( !empty($nIDScheme) ) {
				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nIDScheme );
				
				if(!empty($aFilterParams['create_date_period'])) {
					// ако има период за Генериране
					$nMon		= $aFilterParams['create_date_period'] - 1; // teku6taq go broq za iztekal
					$nMonth1 	= mktime(0, 0, 0, (int)date("m" , strtotime("- $nMon month") ), 1, (int)date("Y" , strtotime("- $nMon month") )); // Отместване назад х месеца до първи ден
					$nMonth2 	= mktime(23, 59, 0, (int)date("m")+1 , 0, date("Y"));	// края на текущия месец
					
					$nTimeFrom	= $nMonth1;
					$nTimeTo 	= $nMonth2;
				}

//				ДА НЕ СЕ ПРЕЗАПИСВАТ ДАТИТЕ АКО ИМА ДАТА ОТ ФИЛТЪРА
//				if( $aFilterParams['create_date_from'] != "0000-00-00 00:00:00"
//					&& $aFilterParams['create_date_to'] != "0000-00-00 00:00:00"  ) {
//					// ако има зададено с дати да се вземат по внимание те
//
//					$nNewTimeFrom = strtotime($aFilterParams['create_date_from']);
//					$nNewTimeTo	= strtotime($aFilterParams['create_date_to'] );
//
//					if(!empty($nNewTimeFrom) && !empty($nNewTimeTo))
//					{
//						$nTimeFrom	= $nNewTimeFrom;
//						$nTimeTo 	= $nNewTimeTo;
//					}
//				}
			}
			// край на промяна за дата на генериране
			
			//Orders Union
			$aTablesRaw = array();
			$oDBOrders->getTables( $aTablesRaw, $nTimeFrom );
			
			$aTables = array();
			foreach( $aTablesRaw as $nKey => $sTable )
			{
				$aTables[$nKey] = "
					( SELECT id, account_type, bank_account_id FROM {$sTable} )
				";
			}
			
			$sOrdersUnion = implode( " UNION ALL ", $aTables );
			//End Orders Union
			
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
			/*
									CASE t.doc_type
							WHEN 'kvitanciq' THEN 'К'
							WHEN 'oprostena' THEN 'ОК'
							WHEN 'faktura' THEN 'Ф'
							WHEN 'kreditno izvestie' THEN 'КИ'
							WHEN 'debitno izvestie' THEN 'ДИ'
							ELSE ''
						END AS doc_type,
			*/
				$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						t.id,
						t.doc_num,
						t.doc_date,
						t.doc_type AS doc_type,
						
						t.paid_type as paid_type,
						t.id_bank_epayment,
						t.easypay_date,
						t.epay_provider,
						t.doc_status,
						t.deliverer_name,
						c.name AS client_name,
						c.id as client_id,
						t.total_sum,
						t.orders_sum,
						t.total_sum - t.orders_sum as remain_sum,
						t.last_order_time,
						off_cr.name AS deal_office,
						CONCAT_WS(' ', pu.fname, pu.lname) as last_order_person,
						CASE
							WHEN ( t.total_sum = t.orders_sum ) THEN 'paid'
							WHEN ( t.total_sum != t.orders_sum AND t.orders_sum != '0.00' ) THEN 'part_paid'
							WHEN ( t.orders_sum = '0.00' ) THEN 'not_paid'
						END AS pay_status,
						CONCAT_WS( ' ', pc.fname, pc.lname ) AS created_user,
						t.created_time,
						t.to_arc,
						GROUP_CONCAT( DISTINCT ob.id_office ) AS offices,
						GROUP_CONCAT( DISTINCT off.id_firm ) AS firms,
						GROUP_CONCAT( DISTINCT ns.id_nomenclature_earning ) AS nomenclatures
				";
			}
			
			$sQuery .= "
					FROM
						<table> t
					LEFT JOIN
						" . PREFIX_SALES_DOCS_ROWS . "<yearmonth> sdr ON sdr.id_sale_doc = t.id
					LEFT JOIN
						{$db_name_sod}.objects ob ON ob.id = sdr.id_object
					LEFT JOIN
						{$db_name_sod}.offices off ON off.id = ob.id_office
					LEFT JOIN
						nomenclatures_services ns ON ns.id = sdr.id_service
					LEFT JOIN
						{$db_name_sod}.clients c ON c.id = t.id_client
					LEFT JOIN
						{$db_name_personnel}.personnel pc ON pc.id = t.created_user
					LEFT JOIN
						{$db_name_personnel}.personnel pu ON pu.id = t.updated_user						
					LEFT JOIN
						{$db_name_sod}.offices off_cr ON off_cr.id = pc.id_office
					LEFT JOIN
						( {$sOrdersUnion} ) ord ON ord.id = t.last_order_id
					LEFT JOIN {$db_name_sod}.offices off_row ON off_row.id = sdr.id_office
                                        LEFT JOIN bank_accounts as ba ON ba.id = t.id_bank_account
                                        LEFT JOIN bank_accounts as bao ON bao.id = ord.bank_account_id
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
			
			//Common Filter
			if ( empty($nIDScheme) ) {
				if ( !empty($nNum) ) {
					$sQuery .= " AND t.doc_num LIKE '%{$nNum}%' \n ";
				}
				
//				if( !empty( $sDeliverer ) )
//				{
//					$sQuery .= " AND t.deliverer_name = {$db_finance->Quote( $sDeliverer )} \n";
//				}
				
				if ( isset($aParams['sClientName']) && !empty($aParams['sClientName']) ) {
					//$sQuery .= " AND t.id_client = {$nIDClient} \n";
					$sQuery .= " AND t.client_name LIKE '%{$aParams['sClientName']}%' \n";
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
			if( !empty($nIDScheme) ) {
				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nIDScheme );

                //if ( isset($aFilterParams['easypay']) && !empty($aFilterParams['easypay']) ) {
                //    $sQuery .= " AND t.id_bank_epayment > 0 \n ";
                //}

                if ( isset($aFilterParams['nPaidType']) && !empty($aFilterParams['nPaidType']) ) {
                    $sQuery .= " AND t.id_bank_epayment > 0 AND t.epay_provider = ".(int) $aFilterParams['nPaidType'] . " \n ";
                }

                if ( isset($aFilterParams['printed_from']) && !empty($aFilterParams['printed_from']) ) {
                    $nPrintedFrom   = mysqlDateToTimestamp($aFilterParams['printed_from']);
                    $sDatePrint     = date("Y-m-d", $nPrintedFrom);

                    if ( $nPrintedFrom > 1325368800 ) {
                        $sQuery     .= " AND DATE(t.gen_pdf_date) = '{$sDatePrint}' \n ";
                    }
                }

                if ( isset($aFilterParams['printed_doc_from']) && !empty($aFilterParams['printed_doc_from']) ) {
                    $nPrintedDocFrom   = mysqlDateToTimestamp($aFilterParams['printed_doc_from']);
                    $sDateDocPrint     = date("Y-m-d", $nPrintedDocFrom);

                    if ( $nPrintedDocFrom > 1325368800 ) {
                        $sQuery     .= " AND DATE(t.gen_pdf_date) = '{$sDateDocPrint}' \n ";
                    }
                }

                if ( isset($aFilterParams['epayment_from']) && !empty($aFilterParams['epayment_from']) ) {
                    $nEpaymentFrom   = mysqlDateToTimestamp($aFilterParams['epayment_from']);
                    $sDatePayment    = date("Y-m-d", $nEpaymentFrom);

                    if ( $nEpaymentFrom > 1325368800 ) {
                        $sQuery     .= " AND DATE(t.easypay_date) >= '{$sDatePayment}' \n ";
                    }
                }

                if ( isset($aFilterParams['epayment_to']) && !empty($aFilterParams['epayment_to']) ) {
                    $nEpaymentTo    = mysqlDateToTimestamp($aFilterParams['epayment_to']);
                    $sDatePayment   = date("Y-m-d", $nEpaymentTo);

                    if ( $nEpaymentTo > 1325368800 ) {
                        $sQuery     .= " AND DATE(t.easypay_date) <= '{$sDatePayment}' \n ";
                    }
                }

                if ( !empty($nNum) ) {
					$sQuery .= " AND t.doc_num LIKE '%{$nNum}%' \n ";
				} else {
					if( !empty( $aFilterParams['num_from'] ) )
					{
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
                                if( !empty( $aFilterParams['paid_type_account'] ) )
				{
					$sQuery .= " AND ba.id = {$db_finance->Quote( $aFilterParams['paid_type_account'] )} \n";
				}     
                                if( !empty( $aFilterParams['paid_type_order_account'] ) )
				{
					$sQuery .= " AND bao.id = {$db_finance->Quote( $aFilterParams['paid_type_order_account'] )} \n";
				}
				if( !empty( $aFilterParams['paid_type_order'] ) )
				{
					//$sQuery .= " AND ord.account_type = {$db_finance->Quote( $aFilterParams['paid_type_order'] )} \n";
					// за Плащане по Ордер да гледа в bank_account а не в самия ордер типа 
					if( $aFilterParams['paid_type_order'] == 'cash' )
						$sQuery .= " AND bao.cash = 1 \n";
					else if ( $aFilterParams['paid_type_order'] == 'bank' ) 
						$sQuery .= " AND bao.cash = 0 \n";
				}
				if( !empty( $aFilterParams['email_send'] ) )
				{
					$sQuery .= " AND c.email != '' \n";
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
					if ( isset($aFilterParams['create_date_period']) && !empty($aFilterParams['create_date_period']) ) {
						// Павел
						/*
						$nMon		= $aFilterParams['create_date_period'];
						$nMonth1 	= mktime(0, 0, 0, date("m") - $nMon, 1, date("Y"));			// Отместване назад хх месеца до първи ден
						$nMonth2 	= mktime(23, 59, 0, date("m") - ($nMon - 1), 0, date("Y"));	// Отместване назад хх месеца до последен ден
						*/
						$nMon		= $aFilterParams['create_date_period'] - 1; // текущия го броя за изтекъл
						$nMonth1 	= mktime(0, 0, 0, (int)date("m" , strtotime("- $nMon month") ), 1, (int)date("Y" , strtotime("- $nMon month") )); // Отместване назад х месеца до първи ден
						$nMonth2 	= mktime(23, 59, 0, (int)date("m")+1 , 0, date("Y"));	// края на текущия месец
						
						
						$sQuery .= "
							AND UNIX_TIMESTAMP(t.created_time) >= '{$nMonth1}' AND UNIX_TIMESTAMP(t.created_time) <= '{$nMonth2}'
						";	
											
//						$nLimit1 = date( "Y-m-d H:i:s", strtotime( "-{$aFilterParams['create_date_period']} MONTHS" ) );
//						$nLimit2 = date( "Y-m-d H:i:s" );
//					
//						$sQuery .= "
//							AND UNIX_TIMESTAMP( t.created_time ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.created_time ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
//						";
					} else {
						// from-to time

                        //ТУК да сетвам ако има външна дата да вземе нея въпреки филтъра
                        if( !empty( $aParams['sFromDate'] ) ) {
                            $sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= {$nTimeFrom} \n";
                        } else if( $aFilterParams['create_date_from'] != '0000-00-00 00:00:00' ) {
							$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= UNIX_TIMESTAMP( '{$aFilterParams['create_date_from']}' ) \n";
						}

                        if( !empty( $aParams['sToDate'] ) ) {
                            $sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= {$nTimeTo} \n";
                        } else if( $aFilterParams['create_date_to'] != '0000-00-00 00:00:00' ) {
							$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= UNIX_TIMESTAMP( '{$aFilterParams['create_date_to']}' ) \n";
						}
					}
				} else {
					// ако във филтъра има дата на документа да не гледа датата от до която е извън филтъра
					if( $aFilterParams['doc_date_from'] == '0000-00-00 00:00:00' ) {
						
						if( !empty( $aParams['sFromDate'] ) ) {
							$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) >= {$nTimeFrom} \n";
						}	
					}
					
					if( $aFilterParams['doc_date_to'] == '0000-00-00 00:00:00' ) {
						
						if( !empty( $aParams['sToDate'] ) ) {
							$sQuery .= " AND UNIX_TIMESTAMP( t.created_time ) <= {$nTimeTo} \n";
						}	
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

				if ( isset($aFilterParams['doc_date_period']) && !empty($aFilterParams['doc_date_period']) ) {
					// Павел
					/*
					$nMon		= $aFilterParams['doc_date_period'];
					$nMonth1 	= mktime(0, 0, 0, date("m") - $nMon, 1, date("Y"));			// Отместване назад хх месеца до първи ден
					$nMonth2 	= mktime(23, 59, 0, date("m") - ($nMon - 1), 0, date("Y"));	// Отместване назад хх месеца до последен ден
					*/
					$nMon		= $aFilterParams['doc_date_period'] - 1;
					$nMonth1 	= mktime(0, 0, 0, (int)date("m" , strtotime("- $nMon month") ), 1, (int)date("Y" , strtotime("- $nMon month") )); // Отместване назад х месеца до първи ден
					$nMonth2 	= mktime(23, 59, 0, (int)date("m")+1 , 0, date("Y"));	// края на текущия месец
					
					$sQuery .= "
						AND UNIX_TIMESTAMP(t.doc_date) >= '{$nMonth1}' AND UNIX_TIMESTAMP(t.doc_date ) <= '{$nMonth2}'
					";	
										
					//$nLimit1 = date("Y-m-d H:i:s", strtotime("-{$aFilterParams['doc_date_period']} MONTHS"));
					//$nLimit2 = date("Y-m-d H:i:s");
					
//					$sQuery .= "
//						AND UNIX_TIMESTAMP( t.doc_date ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.doc_date ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
//					";				
				}
				
				if ( isset($aFilterParams['last_order_period']) && !empty($aFilterParams['last_order_period']) ) {
					// Павел
					/*
					$nMon		= $aFilterParams['last_order_period'];
					$nMonth1 	= mktime(0, 0, 0, date("m") - $nMon, 1, date("Y"));			// Отместване назад хх месеца до първи ден
					$nMonth2 	= mktime(23, 59, 0, date("m") - ($nMon - 1), 0, date("Y"));	// Отместване назад хх месеца до последен ден
					*/
					$nMon		= $aFilterParams['last_order_period'] - 1;
					$nMonth1 	= mktime(0, 0, 0, (int)date("m" , strtotime("- $nMon month") ), 1, (int)date("Y" , strtotime("- $nMon month") )); // Отместване назад х месеца до първи ден
					$nMonth2 	= mktime(23, 59, 0, (int)date("m")+1 , 0, date("Y"));	// края на текущия месец
					
					$sQuery .= "
						AND UNIX_TIMESTAMP(t.last_order_time) >= '{$nMonth1}' AND UNIX_TIMESTAMP(t.last_order_time ) <= '{$nMonth2}'
					";	
										
//					$nLimit1 = date( "Y-m-d H:i:s", strtotime( "-{$aFilterParams['last_order_period']} MONTHS" ) );
//					$nLimit2 = date( "Y-m-d H:i:s" );
//					
//					$sQuery .= "
//						AND UNIX_TIMESTAMP( t.last_order_time ) >= UNIX_TIMESTAMP( '{$nLimit1}' ) AND UNIX_TIMESTAMP( t.last_order_time ) <= UNIX_TIMESTAMP( '{$nLimit2}' )
//					";
				}
				
				if ( isset($aFilterParams['id_firm_creator']) && !empty($aFilterParams['id_firm_creator']) ) {
					$sQuery .= " AND off_cr.id_firm = {$aFilterParams['id_firm_creator']} \n";
					
					if ( isset($aFilterParams['id_office_creator']) && !empty($aFilterParams['id_office_creator']) ) {
						$sQuery .= " AND pc.id_office = {$aFilterParams['id_office_creator']} \n";
					}					
				}// else {
				//	$sQuery .= " AND pc.id_office IN ({$sRegions}) \n";					
				//}
				
				if ( !empty($nIDClient) ) {
					$sQuery .= " AND t.id_client = {$nIDClient} \n";
				}	
								
				if ( isset($sDeliverer) && !empty($sDeliverer) ) {
					$sQuery .= " AND t.deliverer_name = {$db_finance->Quote( $sDeliverer )} \n";
				}
				else
				{
					if( !empty( $aFilterParams['deliverer_name'] ) )
					{
						$aFilterParams['deliverer_name'] = addslashes( $aFilterParams['deliverer_name'] );
						$sQuery .= " AND t.deliverer_name = '{$aFilterParams['deliverer_name']}' \n";
					}
				}
				
				if( !empty( $aFilterParams['client_name'] ) )
				{
					$sClientName = addslashes( $aFilterParams['client_name'] );
					$sQuery .= " AND c.name LIKE '%{$sClientName}%' \n";
				}
				if( !empty( $aFilterParams['client_ein'] ) )
				{
					$sClientEin = addslashes( $aFilterParams['client_ein'] );
					$sQuery .= " AND c.invoice_ein LIKE '%{$sClientEin}%' \n";
				}
				if( !empty( $aFilterParams['client_eik'] ) )
				{
					$sQuery .= " AND c.id = {$aFilterParams['client_eik']} \n";
				}
				
				if ( isset($aFilterParams['ids_nomenclatures']) && !empty($aFilterParams['ids_nomenclatures']) ) {
					/*
					$aIDNomenclatures = explode( ",", $aFilterParams['ids_nomenclatures'] );
					if( !empty( $aIDNomenclatures ) )
					{
						foreach( $aIDNomenclatures as &$sValue )
						{
							$sValue = "ns.id_nomenclature_earning = {$sValue}";
						}
						
						$sClauseNomenclatures = implode( " OR ", $aIDNomenclatures );
						$sQuery .= " AND ( $sClauseNomenclatures ) \n ";
					}
					*/
					
					$sQuery .= " AND ns.id_nomenclature_earning IN ({$aFilterParams['ids_nomenclatures']})  \n ";
				}
                                
                                if ( isset($aFilterParams['ids_nomenclatureservices']) && !empty($aFilterParams['ids_nomenclatureservices']) ) {
					/*
					$aIDNomenclatures = explode( ",", $aFilterParams['ids_nomenclatures'] );
					if( !empty( $aIDNomenclatures ) )
					{
						foreach( $aIDNomenclatures as &$sValue )
						{
							$sValue = "ns.id_nomenclature_earning = {$sValue}";
						}
						
						$sClauseNomenclatures = implode( " OR ", $aIDNomenclatures );
						$sQuery .= " AND ( $sClauseNomenclatures ) \n ";
					}
					*/
					
					$sQuery .= " AND ns.id IN ({$aFilterParams['ids_nomenclatureservices']})  \n ";
				}
				
				if ( isset($aFilterParams['id_firm_objects']) && !empty($aFilterParams['id_firm_objects']) ) {
					$sQuery .= " AND off.id_firm = {$aFilterParams['id_firm_objects']} \n";
				
					if ( isset($aFilterParams['id_office_objects']) && !empty($aFilterParams['id_office_objects']) ) {
						$sQuery .= " AND ob.id_office = {$aFilterParams['id_office_objects']} \n";
					}				
				}
				
				 //else {
					//$sQuery .= " AND ob.id_office IN ({$sRegions}) \n";	
				//}
			}
			//End Detailed Filter
			
			$sQuery .= " GROUP BY t.id \n";
			//APILog::Log(0, $sQuery);
			$oDBSalesDocs->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );

			if( $TotalQuery == 0 )
			{
				$sQuery .= "
					ORDER BY {$sSortField} {$sSortType}
				";
				
				if ( isset($aParams['api_action']) && $aParams['api_action'] == "export_to_xls" ) {
					
					//
				} else {
					$sQuery .= "
						LIMIT {$nRowOffset},{$nRowLimit}
					";
				}
			}
			APILog::Log(0, $sQuery);
			return $sQuery;
		}
		
		public function makeDocs( $nIDClient, $aServices, $sDocType = '', $sDocStatus = 'proforma', $is_auto = 0 ) {
			$oDBObjectServices = new DBObjectServices();
			$oDBObjectsSingles = new DBObjectsSingles();
			
			foreach ($aServices as $value) {
				$aParts = explode(",",$value); 
					
				if($aParts[1] == 'single') {
					$aJur = $oDBObjectsSingles->getJur($aParts[0]);
					$sJurName = $aJur['jur_name'];
				} else {
					$aJur = $oDBObjectServices->getJur($aParts[0]);
					$sJurName = $aJur['jur_name'];
				}
				$aJurNames[$sJurName][] = $value;
			}
			unset($value);
			
			$aSaleDocsIDs = array();
			
			foreach ($aJurNames as $value) {
				$aSaleDocsIDs[] = $this->makeSaleDoc( $nIDClient, $value, $sDocType, $sDocStatus );
			}
			
			return $aSaleDocsIDs;
			
		}
		
		public function makeSaleDoc( $nIDClient, $aServices, $sDocType = '', $sDocStatus = 'proforma', $is_auto = 0 ) {
			global $db_finance, $db_sod, $db_system;
			
			$oDBClients 		= new DBClients();
			$oDBObjectServices 	= new DBObjectServices();
			$oDBObjectsSingles 	= new DBObjectsSingles();
			$oDBSystem 			= new DBSystem();
			$oDBSalesDocsRows 	= new DBSalesDocsRows();
			
			$db_finance->StartTrans();
			$db_sod->StartTrans();
			$db_system->StartTrans();
			
			try {
			
				if ( !empty($nIDClient) ) {
					$aClient = $oDBClients->getRecord($nIDClient);
				}
				
				$aSystem 		 = $oDBSystem->getRow();
				$nLastNumSaleDoc = $aSystem['last_num_sale_doc'];
				
				$aParts = explode(",",current($aServices));
						
				if($aParts[1] == 'single') {
					$aDeliverer = $oDBObjectsSingles->getJur($aParts[0]);
				} else {
					$aDeliverer = $oDBObjectServices->getJur($aParts[0]);
				}
				
				$nDDS 		= isset($aDeliverer['dds']) ? $aDeliverer['dds'] : 0;
				$aSaleDoc 	= array();
				
				$aSaleDoc['doc_num'] 		= $nLastNumSaleDoc+1;
				$aSaleDoc['doc_date'] 		= date("Y-m-d");
				$aSaleDoc['doc_type'] 		= $sDocType == "kvitanciq1" ? "kvitanciq" : $sDocType;
				$aSaleDoc['doc_status'] 	= $sDocStatus;
				
				if ( !empty($nIDClient) ) {
					$aSaleDoc['id_client'] 		= $nIDClient;
					$aSaleDoc['client_name'] 	= $aClient['name'];
					$aSaleDoc['client_ein'] 	= $aClient['invoice_ein'];
					$aSaleDoc['client_ein_dds'] = $aClient['invoice_ein_dds'];
					$aSaleDoc['client_address'] = $aClient['invoice_address'];
					$aSaleDoc['client_mol'] 	= $aClient['invoice_mol'];
					$aSaleDoc['client_recipient'] = $aClient['invoice_recipient'];
				}
				
				
				$aSaleDoc['deliverer_name'] 	= $aDeliverer['jur_name'];
				$aSaleDoc['deliverer_address'] 	= $aDeliverer['address'];
				$aSaleDoc['deliverer_ein'] 		= $aDeliverer['idn'];
				$aSaleDoc['deliverer_ein_dds'] 	= $aDeliverer['idn_dds'];
				$aSaleDoc['deliverer_mol'] 		= $aDeliverer['jur_mol'];
				
				$aSaleDoc['paid_type'] 			= $aClient['invoice_payment'];
				$aSaleDoc['view_type'] 			= $aClient['invoice_layout'];
				
				$aSaleDoc['is_auto'] 			= $is_auto;
				$aSaleDoc['created_user'] 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
				$aSaleDoc['created_time'] 		= time();
				
				$this->update($aSaleDoc);
				
				$nTotalSum 		= 0;
				//Pavel
				$nIDDocument 	= 0;
				$nIDAccount 	= isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account'] : 0;
				
				foreach ( $aServices as $value ) {
					$aParts = explode(",", $value);
					
					$aSaleDocRow = array();
					$aSaleDocRow['id_sale_doc'] = $aSaleDoc['id'];
					
					if ( $aParts[1] == 'single') {
						$aService = $oDBObjectsSingles->getSingle($aParts[0]);
						
						$aService['single_price'] 	*= 5/6;
						$aService['total_sum'] 		*= 5/6;
						
						$aSaleDocRow['month'] 	= '0000-00-00';
						
						$aObjectSingle = array();
						$aObjectSingle['id'] 			= $aService['id'];
						$aObjectSingle['paid_date'] 	= time();
						$aObjectSingle['id_sale_doc'] 	= $aSaleDoc['id'];
						
						$oDBObjectsSingles->update($aObjectSingle);
					} else {
						$aService = $oDBObjectServices->getService($aParts[0]);
						
						$aService['single_price'] 	*= 5/6;
						$aService['total_sum'] 		*= 5/6;
												
						$aSaleDocRow['month'] 	= $aParts[1];
						
						list ( $y, $m, $d ) = explode( "-", $aParts[1] );
						
						if ( $d != "01" ) {
							$nTime 		= mktime(0, 0, 0, $m, $d, $y);
							$nMonthDays = date('t', $nTime);
							
							$nPartOfMonth = ($nMonthDays - $d + 1) / $nMonthDays;
							
							$aService['single_price'] 	*= $nPartOfMonth;
							$aService['total_sum'] 		*= $nPartOfMonth;
						}
						
						$aObjectService 			 = array();
						$aObjectService['id'] 		 = $aService['id'];
						$aObjectService['last_paid'] = $y."-".$m."-01";		
	
						$oDBObjectServices->update($aObjectService);		//vdigam padeja na uslugata
					}
					
					if ( $sDocType != "kvitanciq1" ) {
						$nTotalSum += $aService['total_sum'] * 1.2;
					} else {
						$nTotalSum += $aService['total_sum'];
					}
					
					$aSaleDocRow['id_office'] 		= $aService['id_office'];
					$aSaleDocRow['id_object'] 		= $aService['id_object'];
					$aSaleDocRow['id_service'] 		= $aService['id_service'];
					$aSaleDocRow['service_name'] 	= $aService['service_name'];
					$aSaleDocRow['quantity'] 		= $aService['quantity'];
					$aSaleDocRow['single_price'] 	= $aService['single_price'];
					$aSaleDocRow['total_sum'] 		= $aService['total_sum'];
					$aSaleDocRow['measure'] 		= $aService['measure_code'];
					$aSaleDocRow['is_dds'] 			= 0;
					
					$oDBSalesDocsRows->update($aSaleDocRow);
				}
				
				unset($value);
				
				if ( $sDocType != "kvitanciq1" ) {
					// Добавяме ДДС
					$aSaleDocRow 					= array();
					$aSaleDocRow['id_sale_doc'] 	= $aSaleDoc['id']; 
					$aSaleDocRow['id_office'] 		= $nDDS;
					$aSaleDocRow['id_object'] 		= 0;
					$aSaleDocRow['month'] 			= date("Y-m-d");
					$aSaleDocRow['id_service'] 		= 0;
					$aSaleDocRow['service_name'] 	= "ДДС";
					$aSaleDocRow['quantity'] 		= 1;
					$aSaleDocRow['single_price'] 	= sprintf( "%0.2f", $nTotalSum / 6 );
					$aSaleDocRow['total_sum'] 		= sprintf( "%0.2f", $nTotalSum / 6 );
					$aSaleDocRow['paid_sum'] 		= 0;
					$aSaleDocRow['paid_date'] 		= "0000-00-00 00:00:00";
					$aSaleDocRow['measure'] 		= "бр.";
					$aSaleDocRow['is_dds'] 			= 1;	
					$aSaleDocRow['updated_user']	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
					$aSaleDocRow['updated_time']	= time();
					
					$oDBSalesDocsRows->update($aSaleDocRow);				
				}
				
				$aSaleDoc['total_sum'] = $nTotalSum;
				$this->update($aSaleDoc);
				
				$oDBSystem->setLastNumSaleDoc($nLastNumSaleDoc + 1);
				
				$db_finance->CompleteTrans();
				$db_sod->CompleteTrans();
				$db_system->CompleteTrans();
			
			} catch (Exception $e) {
				$db_finance->FailTrans();
				$db_sod->FailTrans();
				$db_system->FailTrans();
			}
			
			return $aSaleDoc['id'];
			
		}
		
		public function makePayment($nIDSaleDoc,$sAccountType,$nIDAccount) {
			
			$aSaleDoc = array();
			$this->getRecord($nIDSaleDoc,$aSaleDoc);
			
			if( ($aSaleDoc['total_sum'] - $aSaleDoc['orders_sum']) > 0 && $aSaleDoc['doc_status'] == 'final') {
				$nOrderSum 			= $aSaleDoc['total_sum'] - $aSaleDoc['orders_sum'];
			
				$oDBSystem 			= new DBSystem();
				$oDBAccountStates 	= new DBAccountStates();				
				$oDBOrders 			= new DBOrders();
				$oDBSalesDocsRows 	= new DBSalesDocsRows();

				$aSystem 	= $oDBSystem->getRow();
				$nNumOrder 	= $aSystem['last_num_order'] + 1;

				if($sAccountType == 'bank') {
					$aAccountState = $oDBAccountStates->getRow($sAccountType,0,$nIDAccount);
					$aAccountState['id_bank_account'] = $nIDAccount;
				} else {
					$aAccountState = $oDBAccountStates->getRow($sAccountType,$nIDAccount);
					$aAccountState['id_person'] 	= $nIDAccount;
				}
				
				$aAccountState['account_type'] 		= $sAccountType;
				if(isset($aAccountState['current_sum']) && !empty($aAccountState['current_sum'])) {
					$aAccountState['current_sum'] 	+= $nOrderSum;
				} else {
					$aAccountState['current_sum'] 	= $nOrderSum;
				}
				$oDBAccountStates->update($aAccountState);
				
				$aOrder 						= array();
				$aOrder['num'] 					= $nNumOrder;
				$aOrder['order_type'] 			= 'earning';
				$aOrder['order_date'] 			= time();
				$aOrder['order_sum'] 			= $nOrderSum;
				$aOrder['account_type'] 		= $sAccountType;
				$aOrder['id_person'] 			= $nIDAccount;
				$aOrder['account_sum'] 			= $aAccountState['current_sum'];
				if($sAccountType == 'bank') {
					$aOrder['bank_account_id'] 	= $nIDAccount;
				}
				$aOrder['doc_id'] 				= $nIDSaleDoc;
				$aOrder['doc_type'] 			= 'sale';
			
				$oDBOrders->update($aOrder,NULL,true,$nIDSaleDoc);
				$oDBSystem->setLastNumOrder($nNumOrder);
				
				$aSaleDoc['orders_sum'] 		= $aSaleDoc['total_sum'];
				$aSaleDoc['last_order_id'] 		= $aOrder['id'];
				$aSaleDoc['last_order_time'] 	= time();
				
				$this->update($aSaleDoc);
				
				$aIDSaleDocRows = $oDBSalesDocsRows->getByIDSaleDoc($nIDSaleDoc);
				
				foreach ( $aIDSaleDocRows as $v) {
					$v['paid_sum'] 	= $v['total_sum'] / $aSaleDoc['total_sum'] * $nOrderSum;
					$v['paid_date'] = time();
					
					$oDBSalesDocsRows->update($v);
				}
				
				return $aSaleDoc['doc_num'];
			}
			return '';
		}

        public function makePaymentPlane($nIDSaleDoc, $nIDAccount) {
            global $db_finance, $db_system, $db_sod, $db_name_finance, $db_name_system, $db_name_sod;

            $nIDPerson      = isset($_SESSION['userdata']['id_person'])             ? $_SESSION['userdata']['id_person']    : 0;

            if ( empty($nIDSaleDoc) ) {
                throw new Exception("Не е избран документ за плащане!!!");
            }

            if ( empty($nIDAccount) ) {
                throw new Exception("Изберете сметка!!!");
            }

            $oFirms 		= new DBFirms();
            $oOrders		= new DBOrders();
            $oOrderRows		= new DBOrdersRows();
            $oSaleDocRows	= new DBSalesDocsRows();
            //$oSaleDoc		= new DBSalesDocs();
            $oServices		= new DBObjectServices();
            $oObject		= new DBObjects();
            $oSaldo			= new DBSaldo();

            $sSaleName 	= PREFIX_SALES_DOCS.substr($nIDSaleDoc, 0, 6);
            $sRowsName	= PREFIX_SALES_DOCS_ROWS.substr($nIDSaleDoc, 0, 6);

            $aDoc 		= $this->getDoc($nIDSaleDoc);

            $nEin		= isset($aDoc['deliverer_ein']) ? $aDoc['deliverer_ein'] 	: 0;
            $nIDClient	= isset($aDoc['id_client']) 	? $aDoc['id_client'] 		: 0;
            $nDocNum	= isset($aDoc['doc_num']) 		? $aDoc['doc_num'] 			: 0;

            $aFirm		= $oFirms->getDDSFirmByEIN($nEin);

            $nIDFirm	= isset($aFirm['id']) 			? $aFirm['id'] 				: 0;

            // ДДС
            $aDDS 		= $oSaleDocRows->getDDSByDoc( $nIDSaleDoc );
            $nDDSWait	= isset($aDDS[0]['paid_sum']) 	? $aDDS[0]['total_sum'] - $aDDS[0]['paid_sum'] 	: 0;
            $nDDS 		= isset($aDDS[0]['id']) 		? $aDDS[0]['id'] 								: 0;

            $nTotalSum 	= 0;
            $nPaidSum	= 0;
            $nWaitSum   = 0;

            $db_finance->startTrans();
            $db_system->startTrans();
            $db_sod->startTrans();

            try {
                 // Следващ номер за ордер
                $oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
                $nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;

                // НАЧАЛНА наличност по сметка
                $oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
                $nAccState	 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;

                $aSaleRows 		= $oSaleDocRows->getRowsByDoc( $nIDSaleDoc );

                foreach( $aSaleRows as $val ) {
                    if ( isset($val['id']) && !empty($val['id']) ) {
                        $nTotalSum 	+= $val['total_sum'];
                        $nPaidSum	+= $val['paid_sum'];
                    }
                }

                unset($val);

                $nWaitSum = sprintf("%01.2f", ($nTotalSum + $nDDSWait) - $nPaidSum);

                if ( !empty($nWaitSum) ) {
                    $nIDOrder 	= 0;
                    $nTotalSum	= 0;
                    $nDDSWait 	= sprintf("%01.2f", $nDDSWait);

                    if ( !empty($nWaitSum) ) {
                        if ( $nWaitSum >= 0 ) {
                            $sTypeNow = "earning";
                        } else {
                            $sTypeNow = "expense";
                        }

                        $aData['id']				= 0;
                        $aData['num']				= $nLastOrder;
                        $aData['doc_num']			= $nDocNum;
                        $aData['order_type'] 		= $sTypeNow;
                        $aData['id_transfer']		= 0;
                        $aData['id_contragent']		= $nIDClient;
                        $aData['id_doc_firm']		= $nIDFirm;
                        $aData['order_date']		= time();
                        $aData['order_sum']			= abs($nWaitSum);
                        $aData['account_type']		= "cash";
                        $aData['id_person']			= $nIDPerson;
                        $aData['account_sum']		= $nAccState + $nWaitSum;
                        $aData['bank_account_id']	= $nIDAccount;
                        $aData['doc_id']			= $nIDSaleDoc;
                        $aData['doc_type']			= "sale";
                        $aData['note']				= "Admin display";
                        $aData['created_user']		= $nIDPerson;
                        $aData['created_time']		= time();
                        $aData['updated_user']		= $nIDPerson;
                        $aData['updated_time']		= time();

                        $oOrders->update($aData);

                        $nIDOrder = $aData['id'];

                        $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
                    }

                    if ( !empty($nDDS) && ($nDDSWait != "0.00") ) {
                        if ( !isset($aDDS[0]['id_office']) || empty($aDDS[0]['id_office']) ) {
                            throw new Exception("Направлението за ДДС е неизвестно!", DBAPI_ERR_INVALID_PARAM);
                        } else {
                            $nIDOffice = $aDDS[0]['id_office'];
                        }

                        $nIDFirm 			= $oFirms->getFirmByOffice($nIDOffice);

                        $aSaldo				= $oSaldo->getSaldoByFirm($nIDFirm, 1);
                        $nIDSaldo			= 0;

                        if ( !empty($aSaldo) ) {
                            $nIDSaldo 		= $aSaldo['id'];
                        }

                        if ( !empty($nIDSaldo) ) {
                            // Салдо на фирмата с изчакване!!!
                            $oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                            $nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                        } else {
                            throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
                        }

                        $nSumPay 	= $nDDSWait;
                        $nSumTemp	= $nWaitSum - $nDDSWait;

                        if ( ($nCurrentSaldo + $nSumPay) < 0 ) {
                            throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
                        }

                        // Наличност по сметка
                        if ( !empty($nIDAccount) ) {
                            $oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                            $nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                        } else {
                            throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
                        }
                        /*
                                            if ( ($nAccountState + $nSumPay) < 0 ) {
                                                throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
                                            }
                        */
                        $aDataRow	= array();
                        $aDataRow['id'] 						= 0;
                        $aDataRow['id_order'] 					= $nIDOrder;
                        $aDataRow['id_doc_row'] 				= $nDDS;
                        $aDataRow['id_office'] 					= $nIDOffice;
                        $aDataRow['id_object'] 					= 0;
                        $aDataRow['id_service'] 				= 0;
                        $aDataRow['id_direction']				= 0;
                        $aDataRow['id_nomenclature_earning'] 	= 0;
                        $aDataRow['id_nomenclature_expense'] 	= 0;
                        $aDataRow['id_saldo']					= $nIDSaldo;
                        $aDataRow['id_bank']					= $nIDAccount;
                        $aDataRow['saldo_state']				= $nCurrentSaldo + $nSumPay;
                        $aDataRow['account_state']				= $nAccountState + $nSumPay;
                        $aDataRow['month'] 						= isset($aDDS[0]['month']) ? $aDDS[0]['month'] : date("Y-m-d");
                        $aDataRow['type'] 						= "month";
                        $aDataRow['paid_sum'] 					= $nSumPay;
                        $aDataRow['is_dds'] 					= 1;

                        $oOrderRows->update($aDataRow);

                        // Слагаме стойността за ДДС-то!
                        $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$nSumPay}', paid_date = NOW(), updated_user = {$nIDPerson}, updated_time = NOW() WHERE id = '{$nDDS}' ");

                        // Променяме салдото на фирмита, която е получател на ДДС-то!
                        $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nSumPay}' WHERE id = {$nIDSaldo} LIMIT 1");

                        $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSumPay}' WHERE id_bank_account = {$nIDAccount} ");
                    }

                    $nTotalSum += $nSumPay;


                    // След ДДС-то имаме остатък!!!
                    $nTotal 	= sprintf("%01.2f", $nSumTemp);
                    $aMagic		= array();
                    $nBigTotal	= 0;

                    foreach( $aSaleRows as $val ) {
                        $nBigTotal	+= ($val['total_sum'] - $val['paid_sum']);

                        if ( $val['total_sum'] != $val['paid_sum'] ) {
                            if ( !isset($val['id_office']) || empty($val['id_office']) ) {
                                throw new Exception("Има услуга без направление!", DBAPI_ERR_INVALID_PARAM);
                            } else {
                                $nIDOffice = $val['id_office'];
                            }

                            $nIDFirm 	= $oFirms->getFirmByOffice($nIDOffice);

                            // Групираме дължимите суми по фирми
                            if ( isset($aMagic[$nIDFirm]) ) {
                                $aMagic[$nIDFirm] += ($val['total_sum'] - $val['paid_sum']);
                            } else {
                                $aMagic[$nIDFirm] = ($val['total_sum'] - $val['paid_sum']);
                            }
                        }
                    }

                    $off = $nTotal / $nBigTotal;

                    foreach ( $aMagic as $key => $st ) {
                        $offset 	    = sprintf("%01.2f", ($st * $off) );
                        $aMagic[$key]	= $offset;
                    }

                    foreach( $aSaleRows as $val ) {
                        $aDataRow	= array();
                        $nIDRow 	= isset($val['id']) 		? $val['id'] 		 : 0;
                        $nIDService	= isset($val['id_service']) ? $val['id_service'] : 0;

                        if ( ($val['total_sum'] != $val['paid_sum']) || ($val['total_sum'] == 0) ) {
                            if ( !isset($val['id_office']) || empty($val['id_office']) ) {
                                throw new Exception("Има услуга без направление!", DBAPI_ERR_INVALID_PARAM);
                            } else {
                                $nIDOffice = $val['id_office'];
                            }

                            $nIDDuty	= isset($val['id_duty']) 	? $val['id_duty']			: 0;
                            $sMonth		= isset($val['month']) 		? substr($val['month'], 0, 7)."-01"	: "0000-00-00";

                            $nIDFirm 	= $oFirms->getFirmByOffice($nIDOffice);
                            $aService	= $oServices->getService($nIDService);
                            $nRSum  	= $val['total_sum'] - $val['paid_sum'];

                            $aSaldo				= $oSaldo->getSaldoByFirm($nIDFirm, 0);
                            $nIDSaldo			= 0;

                            $nRealSum	= $nRSum * $off;
                            $nTotalSum	+= $nRealSum;

                            if ( !empty($aSaldo) ) {
                                $nIDSaldo 		= $aSaldo['id'];
                            }

                            if ( !empty($nIDSaldo) ) {
                                // Салдо на фирмата с изчакване!!!
                                $oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
                                $nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
                            } else {
                                throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
                            }

                            if ( ($nCurrentSaldo + $nRealSum) < 0 ) {
                                throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
                            }

                            // Наличност по сметка
                            if ( !empty($nIDAccount) ) {
                                $oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
                                $nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;
                            } else {
                                throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
                            }

                            //if ( ($nAccountState + $nRealSum) < 0 ) {
                            //    throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
                            //}

                            // Реално плащане
                            if ( isset($val['type']) && ($val['type'] == "month") && !empty($nIDDuty) ) {
                                $aTemp 		= $oServices->getServiceByID($nIDDuty);
                                $fmod		= abs($val['total_sum'] - ($val['paid_sum'] + $nRealSum));
                                $nIDOff		= isset($val['id_office'])	? $val['id_office']		: 0;
                                $nIDObj		= isset($val['id_object'])	? $val['id_object']		: 0;

                                $aObjCheck	= $oObject->getByID($nIDObj);
                                $nObjOffc	= isset($aObjCheck['id_office']) ? $aObjCheck['id_office']	: $nIDOff;

                                if ( isset($aTemp['real_paid']) && ($aTemp['real_paid'] < $sMonth) && ($fmod < 0.09) ) {
                                    $aTempData 				= array();
                                    $aTempData['id'] 		= $nIDDuty;
                                    $aTempData['real_paid']	= $sMonth;

                                    $oServices->update($aTempData);
                                }

                                if ( isset($aTemp['real_paid']) && ($fmod < 0.09) ) {
                                    // Ъпдейтвам нещо си...
                                    $sQuery = "
                                        UPDATE {$db_name_sod}.statistics_rows_unpaid
                                        SET sum_paid = sum_paid + '{$nRealSum}', id_office = {$nObjOffc}, is_paid = 1
                                        WHERE id_object = {$nIDObj}
                                            AND id_service = {$nIDDuty}
                                            AND stat_month = '{$sMonth}'
                                    ";

                                    $db_sod->Execute($sQuery);
                                } else {
                                    // Ъпдейтвам нещо си...
                                    $sQuery = "
                                        UPDATE {$db_name_sod}.statistics_rows_unpaid
                                        SET sum_paid = sum_paid + '{$nRealSum}', id_office = {$nObjOffc}, is_paid = 0
                                        WHERE id_object = {$nIDObj}
                                            AND id_service = {$nIDDuty}
                                            AND stat_month = '{$sMonth}'
                                    ";

                                    $db_sod->Execute($sQuery);
                                }
                            }

                            $aDataRow['id'] 						= 0;
                            $aDataRow['id_order'] 					= $nIDOrder;
                            $aDataRow['id_doc_row'] 				= $nIDRow;
                            $aDataRow['id_office'] 					= isset($val['id_office']) 			? $val['id_office'] 		: 0;
                            $aDataRow['id_object'] 					= isset($val['id_object']) 			? $val['id_object'] 		: 0;
                            $aDataRow['id_service'] 				= isset($val['id_service']) 		? $val['id_service'] 		: 0;
                            $aDataRow['id_direction']				= 0;
                            $aDataRow['id_nomenclature_earning'] 	= isset($aService['id_earning']) 	? $aService['id_earning'] 	: 0;
                            $aDataRow['id_nomenclature_expense'] 	= 0;
                            $aDataRow['id_saldo']					= $nIDSaldo;
                            $aDataRow['id_bank']					= $nIDAccount;
                            $aDataRow['saldo_state']				= $nCurrentSaldo + $nRealSum;
                            $aDataRow['account_state']				= $nAccountState + $nRealSum;
                            $aDataRow['month'] 						= isset($val['month']) 				? $val['month'] 			: date("Y-m-d");
                            $aDataRow['type'] 						= isset($val['type']) 				? $val['type'] 				: "month";
                            $aDataRow['paid_sum'] 					= $nRealSum;
                            $aDataRow['is_dds'] 					= isset($val['is_dds']) 			? $val['is_dds'] 			: 0;

                            if ( !empty($nRealSum) ) {
                                $oOrderRows->update($aDataRow);

                                // Обновяваме салдото до текуща стойност
                                if ( empty($nIDTran) ) {
                                    $db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nRealSum}' WHERE id = {$nIDSaldo} LIMIT 1");
                                }

                                $db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$nRealSum}', paid_date = NOW(), updated_user = {$nIDPerson}, updated_time = NOW() WHERE id = '{$nIDRow}' ");
                                $db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nRealSum}' WHERE id_bank_account = {$nIDAccount} ");
                            }
                        }
                    }

                    // Оправяме тоталите
                    if ( $nTotalSum >= 0 ) {
                        $sTypeNow = "earning";
                    } else {
                        $sTypeNow = "expense";
                    }

                    $aData					= array();
                    $aData['id']			= $nIDOrder;
                    $aData['order_type'] 	= $sTypeNow;
                    $aData['order_sum']		= abs($nTotalSum);
                    $aData['account_sum']	= $nAccState + $nTotalSum;

                    $oOrders->update($aData);

                    $db_finance->Execute("UPDATE {$db_name_finance}.{$sSaleName} SET orders_sum = orders_sum + '{$nTotalSum}', last_order_id = '{$nIDOrder}', last_order_time = NOW(), updated_user = {$nIDPerson}, updated_time = NOW() WHERE id = '{$nIDSaleDoc}'");

                    // Край на пълното плащане
                }

                $db_finance->CompleteTrans();
                $db_system->CompleteTrans();
                $db_sod->CompleteTrans();
            } catch (Exception $e) {
                $db_finance->FailTrans();
                $db_system->FailTrans();
                $db_sod->FailTrans();

                throw new Exception($e->getMessage());
            }

            return $nWaitSum;
        }
		
		public function returnOldObjectLastPaids($aRowsFirstMonthsPaid) {
			
			$oDBObjectServices = new DBObjectServices();
			$oDBObjectSingle = new DBObjectsSingles();
			$oDBNomenclaturesServices = new DBNomenclaturesServices();
			
			foreach ( $aRowsFirstMonthsPaid as $aRow) {
				
				$nIDService = $aRow['id_service'];
				
				$aObjectService = array();
				
				//$aNomenclatureService = $oDBids_nomenclatureservicesvices->getRecord($nIDService);
				
				$aParams = array();
				$aParams['id_object'] = $aRow['id_object'];
				$aParams['id_service'] = $nIDService;
				
				if(!empty($aNomenclatureService['is_month'])) {
					$aObjectService = $oDBObjectServices->getRow($aParams);					
					
					if(substr($aRow['first_paid_month'],0,7) == substr($aObjectService['start_date'],0,7)) {
						$aObjectService['last_paid'] = '0000-00-00';
					} else {
						list($y,$m,$d) = explode("-",$aRow['first_paid_month']);
						$nLastPaid = mktime(0,0,0,$m - 1,1,$y);
						$aObjectService['last_paid'] = date("Y-m-d",$nLastPaid);
					}
					
					$oDBObjectServices->update($aObjectService);
				} else {
					$aObjectSingle = $oDBObjectSingle->getRow($aParams);
					$aObjectSingle['paid_date'] = '0000-00-00';
					$aObjectSingle['is_sale_doc'] = 0;
					$oDBObjectSingle->update($aObjectSingle);
				}
				
			}	
		}
		
		public function getReportSales(DBResponse $oResponse,$aParams) {
			
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					t.id,
					t.doc_num,
					t.doc_date,
					c.name AS client_name,
					c.invoice_ein AS client_ein,
					t.total_sum,
					t.orders_sum,
					(t.total_sum - t.orders_sum) AS rest_sum
				FROM <table> t
				LEFT JOIN {$db_name_sod}.clients c ON c.id = t.id_client
				WHERE t.to_arc = 0 
					AND t.doc_status = 'final'
					AND t.orders_sum < t.total_sum
			";
			
			if(!empty($aParams['doc_num'])) {
				$sQuery .= "
					AND t.doc_num = {$aParams['doc_num']}
				";
			} else {
				$sQuery .= "
					AND id_client = {$aParams['id_client']}
				";
			}
			
			$this->makeUnionSelect($sQuery);
			
			$nRowLimit = $_SESSION['userdata']['row_limit'];
			$_SESSION['userdata']['row_limit'] = 200;
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse);
			
			$_SESSION['userdata']['row_limit'] = $nRowLimit;
			
			$nSumAll = 0;
			foreach ($oResponse->oResult->aData as $key => &$value) {
				$nSumAll += $value['total_sum'];
				$value['doc_num'] = zero_padding($value['doc_num'],10);
				$oResponse->setDataAttributes($key,'doc_num',array('style' => "text-align:right;"));
			}
			$oResponse->setFormElement('form1','sum_all',array(),sprintf('%0.2f лв.',$nSumAll));
			
			$oResponse->setField('doc_num','Номер','Сортирай по номер');
			$oResponse->setField('doc_date','Дата на документа','Сортирай по дата на документа',NULL,NULL,NULL,array('DATA_FORMAT'=> DF_DATE));
			$oResponse->setField('client_name','Клиент','Сортирай по име на клиента',NULL,NULL,NULL,array('DATA_FORMAT' => DF_STRING));
			$oResponse->setField('client_ein','Клиент ЕИН','Сортирай по Клиент ЕИН',NULL,NULL,NULL,array('DATA_FORMAT' => DF_NUMBER));
			$oResponse->setField('total_sum','Обща сума','Сортирай по обща сума',NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY));
			$oResponse->setField('orders_sum','Платено','Сортирай по платено',NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY));
			$oResponse->setField('rest_sum','За плащане','Сортирай по за плащане',NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY));			
			$oResponse->setField('','','','images/confirm.gif','openOrder','Ордер');
			$oResponse->setFieldLink('doc_num','openSaleDoc');
		}
		
		public function getDoc($nID) {
			global $db_name_finance, $db_name_personnel;
			
			if ( $this->isValidID($nID) ) {
//				$sMonth 	= $this->monthFromID($nID);
//				$sYear 		= $this->yearFromID($nID);
//				$sMonth 	= zero_padding($sMonth,2);
//				$sYearMonth = $sYear.$sMonth;
				$sTable		= PREFIX_SALES_DOCS.substr($nID, 0, 6);
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
		
		// Павел - взимам данните за банкови сметки според фирмите в документа!
		public function getBankByDoc( $nID ) {
			global $db_name_finance, $db_name_sod;
			
			if ( $this->isValidID($nID) ) {
				$sMonth 	= $this->monthFromID($nID);
				$sYear 		= $this->yearFromID($nID);
				$sMonth 	= zero_padding( $sMonth, 2 );
				$sYearMonth = $sYear.$sMonth;
			} else {				
				throw new Exception("Некоректни параметри!");
			}
			
			$sQuery = "
				SELECT
					MAX(abs(sdr.total_sum)) AS total_sum,
					f.name AS firm_name,
					f.id as id_firm,
					o.name AS office_name,
					obj.name AS object_name,
					b.id as id_bank,
					b.name_account,
					b.iban
				FROM {$db_name_finance}.".PREFIX_SALES_DOCS_ROWS.$sYearMonth." sdr
				LEFT JOIN {$db_name_sod}.objects obj ON obj.id = sdr.id_object
				LEFT JOIN {$db_name_sod}.offices o ON o.id = sdr.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				LEFT JOIN {$db_name_finance}.bank_accounts b ON b.id = f.id_bank_account_default 

				WHERE sdr.id_sale_doc = {$nID}
					AND sdr.is_dds = 0
				GROUP BY f.id
				LIMIT 1
			";
			
			//APILog::Log(0, $sQuery);
			$oRs = $this->_oDB->Execute( $sQuery );
			return !$oRs->EOF ? $oRs->fields : array();
		}		
		
		
		// Павел
		public function getBankByFirm( $nID ) {
			global $db_name_finance;
			
			$nID = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					b.id,
					b.name_account,
					b.iban,
					b.ids_typical_firms
				FROM {$db_name_finance}.bank_accounts b
				WHERE b.to_arc = 0
					AND FIND_IN_SET({$nID}, b.ids_typical_firms)
			";
			
			//APILog::Log(0, $sQuery);
			$oRs = $this->_oDB->Execute( $sQuery );
			return !$oRs->EOF ? $oRs->getArray() : array();
		}	

					// Павел
		public function getBanks() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					b.id,
					b.name_account,
					b.iban,
					b.ids_typical_firms
				FROM {$db_name_finance}.bank_accounts b
				WHERE b.to_arc = 0
			";
			
			//APILog::Log(0, $sQuery);
			$oRs = $this->_oDB->Execute( $sQuery );
			return !$oRs->EOF ? $oRs->getArray() : array();
		}	
		
		public function getSaleDocByNum( $nNum )
		{
			if( empty( $nNum ) || !is_numeric( $nNum ) )
			{
				return array();
			}
			
			$sQuery = "
				SELECT
					t.id
				FROM
					<table> t
				WHERE
					t.doc_num = {$nNum}
			";
			
			$this->makeUnionSelect( $sQuery );
			
			$sQuery .= "
				LIMIT 1
			";
			
			$aData = array();
			
			$this->selectOnce( $sQuery, $aData );
			
			return $aData;
		}
		
		public function searchSaleDocsByNum($nNum) {
			global $db_finance;
			
			if ( empty($nNum) || !is_numeric($nNum) ) {
				return 0;
			}
			
			$sQuery = "
				SELECT
					t.id
				FROM
					<table> t
				WHERE
					t.doc_num LIKE '%{$nNum}%'
			";
			
			$this->makeUnionSelect( $sQuery );
			
			$aData = array();
			
			$aData = $db_finance->getArray($sQuery);

			if ( (count($aData) == 1) && isset($aData[0]['id']) ) {
				return $aData[0]['id'];
			} else {
				return 0;
			}
		}


        public function searchSaleDocsByClient($nNum) {
            global $db_finance;

            $aData = array();

            if ( empty($nNum) || !is_numeric($nNum) ) {
                return $aData;
            }

            $sQuery = "
				SELECT
					t.id
				FROM
					<table> t
				WHERE
					t.id_client LIKE '%{$nNum}%'
			";

            $this->makeUnionSelect($sQuery);

            $aTemp = $db_finance->getArray($sQuery);

            foreach ( $aTemp as $val ) {
                $aData[] = $val['id'];
            }

            return $aData;
        }

        public function getUnpaidGroupByYear() {

            $sQuery = "
                    SELECT
                        GROUP_CONCAT(sd.id) AS ids,
                        SUM(sd.total_sum) AS `total_sum`,
                        SUM(sd.orders_sum) AS `order_sum`,
                        SUM(sd.total_sum) - SUM(sd.orders_sum) AS `diff_sum`,
                        SUBSTRING(sd.doc_date,1,4) AS `year`
                    FROM ".PREFIX_SALES_DOCS."<yearmonth> sd
                    WHERE 1
                        AND sd.doc_status = 'final'
                        AND sd.total_sum > sd.orders_sum
                    GROUP BY SUBSTRING(sd.doc_date,1,4)
                ";

            $this->makeUnionSelect( $sQuery );

            $sQuery = "
                SELECT
                    SUM(t.total_sum) AS total_sum,
                    SUM(t.order_sum) AS order_sum,
                    SUM(t.total_sum - t.order_sum) AS diff_sum,
                    t.`year`
                FROM ( ". $sQuery ." ) AS t
                GROUP BY t.`year`
            ";

            return $this->select2($sQuery);
        }

        public function getUnpaidGroupByYearDetailed($aParams) {

			global $db_name_personnel , $db_name_sod;

            if( empty( $aParams) ) {
                throw new Exception("Грешка при определяне на данни !");
            }

            if( empty( $aParams['year']) ) {
                throw new Exception("Грешка при определяне на година!");
            }

            $sQuery = "
                SELECT
                    sd.id AS id,
                    sd.total_sum AS `total_sum`,
                    sd.orders_sum AS `order_sum`,
                    sd.total_sum - sd.orders_sum AS `diff_sum`,
                    DATE_FORMAT(sd.doc_date , '%d.%m.%Y') AS doc_date,
                    sd.doc_date  AS doc_date_sort,
                    sd.doc_num,
                    GROUP_CONCAT( DISTINCT( CONCAT( f.name , ' ' ,  off.name) )) as offices,
                    CONCAT_WS(' ',p.fname , p.mname , p.lname) AS updated_user,
                    sd.client_name
                FROM ".PREFIX_SALES_DOCS."<yearmonth> sd
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = sd.updated_user
				LEFT JOIN ".PREFIX_SALES_DOCS_ROWS."<yearmonth> sdr ON sd.id = sdr.id_sale_doc
				LEFT JOIN {$db_name_sod}.offices off ON off.id = sdr.id_office AND sdr.is_dds = 0
				LEFT JOIN {$db_name_sod}.firms f ON off.id_firm = f.id
                WHERE 1
                    AND sd.doc_status = 'final'
                    AND sd.total_sum > sd.orders_sum
                    AND SUBSTRING(sd.doc_date,1,4) = '{$aParams['year']}'
				GROUP BY sd.id
            ";

            $this->makeUnionSelect( $sQuery );

            $sQuery = "
                SELECT
                    t.*
                FROM ( ". $sQuery ." ) AS t
                ORDER BY t.doc_date_sort ASC
            ";

            return $this->select2($sQuery);
        }

        public function getUnpaidDocsByClient($nIDClient, $date) {
            global $db_name_finance;

            if ( isset($_SESSION['userdata']['check_id_client_old_request']) && $_SESSION['userdata']['check_id_client_old_request'] == $nIDClient ) {
                return;
            } else {
                $_SESSION['userdata']['check_id_client_old_request'] = $nIDClient;
            }

            $sQuery = "
                SELECT
                    sd.id as id
                FROM {$db_name_finance}.".PREFIX_SALES_DOCS."<yearmonth> sd
                JOIN {$db_name_finance}.".PREFIX_SALES_DOCS_ROWS."<yearmonth> sdr ON sd.id = sdr.id_sale_doc
                WHERE 1
                    AND sd.doc_status = 'final'
                    AND sdr.month <= DATE_FORMAT(LAST_DAY('{$date}' - INTERVAL 1 MONTH), '%Y-%m-%d')
                    AND sd.total_sum > sd.orders_sum
                    AND sd.id_client = {$nIDClient}
                GROUP BY sd.id
            ";

            $this->makeUnionSelect($sQuery, 0, strtotime("first day of {$date} previous month"));

            $sQuery2 = "
                SELECT
                    GROUP_CONCAT(t.id) as id
                FROM ( ". $sQuery ." ) AS t
            ";

            return $this->selectOne2($sQuery2);
        }
	}
?>