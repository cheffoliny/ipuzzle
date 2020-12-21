<?php

	class DBClients
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, "clients");
		}	
		
		public function getByID( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT * 
				FROM clients 
				WHERE id = {$nID} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}

        public function getClientName( $nID ) {
            $nID = (int) $nID;

            $sQuery = "
				SELECT
					c.id,
					c.name as client
				FROM clients c
				WHERE 1
					AND c.id = {$nID}
			";

            return $this->selectOnce( $sQuery );
        }
		
		public function getClientByEIN($sEIN) {
			
			global $db_sod;
			
			$sQuery = "
				SELECT
					*
				FROM clients
				WHERE invoice_ein = {$db_sod->Quote($sEIN)}		
				LIMIT 1	
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getClientByName( $sName )
		{
			if( empty( $sName ) ) return array();
			
			$sQuery = "
				SELECT * 
				FROM clients 
				WHERE name = {$this->oDB->Quote( $sName )} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery ); 
		}
		
		public function getClientByName2( $sName ) {
			if ( empty($sName) ) {
				return array();
			}
			
			$sQuery = "
				SELECT * 
				FROM clients 
				WHERE name = {$this->oDB->Quote( $sName )}
					AND invoice_ein = '999999999999999'
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}

		function getClientNameByName( $sName, $nLimit )
		{
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT id, name
				FROM clients
				WHERE 1
				";
			
			if( !empty( $sName ) )
				$sQuery .= sprintf("AND name LIKE '%%%s%%'\n", addslashes( $sName ) );
				
			$sQuery .= "ORDER BY name\n";
			$sQuery .= "LIMIT {$nLimit}\n";
				
			return $this->select( $sQuery );
		}
		
		public function getClientByObject( $nIDObj ) {
			$nID = isset($nIDObj) && is_numeric($nIDObj) ? $nIDObj : 0;
			
			$sQuery = "
				SELECT 
					c.id as id,
					c.name as name
				FROM clients_objects co 
				LEFT JOIN clients c ON ( c.id = co.id_client )
				WHERE co.id_object = {$nID}
					AND co.to_arc = 0
				LIMIT 1
			";

			return $this->selectOnce( $sQuery );
		}
		
		public function updateClientObject( $aData ) {
			global $db_sod;

			$nIDClient 	= isset($aData['id_client']) && is_numeric($aData['id_client']) ? $aData['id_client'] : 0;
			$nIDObject 	= isset($aData['id_object']) && is_numeric($aData['id_object']) ? $aData['id_object'] : 0;
			$user 		= isset($aData['updated_user']) && is_numeric($aData['updated_user']) ? $aData['updated_user'] : 0;
			
			if ( !empty($nIDObject) && !empty($nIDClient) ) {
				$db_sod->Execute("UPDATE clients_objects set to_arc = 1, updated_time = NOW(), updated_user = '{$user}' WHERE id_object = {$nIDObject};");
				
				$rs = $db_sod->Execute("SELECT * FROM clients_objects WHERE id = -1;");
				$insertSQL = $db_sod->GetInsertSQL($rs, $aData); 
				$db_sod->Execute($insertSQL);
			}
		}
		
		public function getReport( $aParams, DBResponse $oResponse = NULL )
		{
			global $db_name_personnel, $db_name_sod;
			
			$oFilters 				= new DBFilters();
			$oFiltersVisibleFields 	= new DBFiltersVisibleFields();
			$oFiltersParams 		= new DBFiltersParams();
			
			$nIDFilter = (int) ( isset( $aParams['schemes'] ) && !empty( $aParams['schemes'] ) ) ? $aParams['schemes'] : 0;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'setup_clients_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						cl.id,
						cl.name,
						cl.invoice_mol,
						cl.invoice_address,
						cl.email,
						cl.phone,
						cl.invoice_bring_to_object,
						CASE( cl.invoice_payment )
							WHEN 'cash' THEN 'Фактура в брой'
							WHEN 'bank' THEN 'Фактура по банка'
							WHEN 'receipt' THEN 'Квитанция'
						END AS invoice_payment,
						CASE( cl.invoice_layout )
							WHEN 'single' THEN 'Единичен'
							WHEN 'by_services' THEN 'По услуги'
							WHEN 'by_objects' THEN 'По Обекти'
							WHEN 'detail' THEN 'Подробен'
						END AS invoice_layout,
						cl.address,
						cl.invoice_ein,
						o.name AS object_name,
						o.num AS object_num,
						c.name AS object_city,
						IF(
						p.id,
						CONCAT(
							CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
							' (',
							DATE_FORMAT( cl.updated_time, '%d.%m.%Y %H:%i:%s' ),
							')'
							),
							''
							) AS updated_user
					FROM clients cl
						LEFT JOIN {$db_name_personnel}.personnel p ON cl.updated_user = p.id
						LEFT JOIN {$db_name_sod}.clients_objects co ON co.id_client = cl.id
						LEFT JOIN {$db_name_sod}.objects o ON o.id = co.id_object
						LEFT JOIN {$db_name_sod}.cities c ON c.id = o.address_city
					WHERE 1

			";
			
			if( !empty( $nIDFilter ) )
			{
				$aFilterParams = $oFiltersParams->getParamsByIDFilter( $nIDFilter );
               // APILog::Log(10,$aFilterParams);
				foreach( $aFilterParams as $name => $value )
				{

					if( $name == "sAddress" && !empty( $value ) )
					{
						$sQuery .= "
								AND cl.address LIKE '%{$value}%'
						";
					}
					if( $name == "nBringInvoice" && !empty( $value ) )
					{
						$sQuery .= "
								AND cl.invoice_bring_to_object = '{$value}'
						";
					}
					if( $name == "sPayment" && !empty( $value ) )
					{
						$sQuery .= "
								AND cl.invoice_payment = '{$value}'
						";
					}
					if( $name == "sLayout" && !empty( $value ) )
					{
						$sQuery .= "
								AND cl.invoice_layout = '{$value}'
						";
					}
					
					//Обекти
					if( $name == "sObjectName" && !empty( $value ) )
					{
						$sQuery .= "
								AND o.name LIKE '%{$value}%'
						";
					}
					if( $name == "sObjectNum" && !empty( $value ) )
					{
						$sQuery .= "
								AND o.num LIKE '%{$value}%'
						";
					}
					if( $name == "sObjectCity" && !empty( $value ) )
					{
						$sQuery .= "
								AND c.name LIKE '%{$value}%'
						";
					}
				}
			}
			else
			{
				if( !empty( $aParams['nID'] ) ) {
					$sQuery .= " AND cl.id = '{$aParams['nID']}' ";
				}
				
				if( !empty( $aParams['sName'] ) ) {
					$sQuery .= " AND cl.name LIKE '%{$aParams['sName']}%' ";
				}
				
				if( !empty( $aParams['sEIN'] ) ) {
					$sQuery .= " AND cl.invoice_ein = '{$aParams['sEIN']}' ";
				}

                if(  !empty( $aParams['sMOL'] ) ) {
                    $sQuery .= " AND cl.invoice_mol LIKE '%{$aParams['sMOL']}%' ";
                }
                if(  !empty( $aParams['sInvoiceAddress'] ) ) {
                    $sQuery .= " AND cl.invoice_address LIKE '%{$aParams['sInvoiceAddress']}%' ";
                }

                if( !empty( $aParams['sEmail'] ) ) {
                    $sQuery .= " AND cl.email LIKE '%{$aParams['sEmail']}%' ";
                }

                if( !empty( $aParams['sPhone'] ) ) {
                    $sQuery .= " AND cl.phone LIKE '%{$aParams['sPhone']}%' ";
                }

                if( !empty( $aParams['client_type'] ) && $aParams['client_type'] == 1 )
                {
                    $sQuery .= "
                                AND co.to_arc = 0
					";
                }
			}
			
			$sQuery .= " GROUP BY cl.id ";

            //Филтриране по клиент/доставчик
            if( isset($aParams['client_type'])) {
                if($aParams['client_type'] == 1 ) {
                    //clients
                    $sQuery .= "
                        HAVING o.num IS NOT NULL
                    ";
                }
                elseif($aParams['client_type'] == 2 ) {
                    //supplier
                    $sQuery .= "
                        HAVING o.num IS NULL
                    ";
                }
            }

			
//			if( isset( $aParams['robot'] ) )
//			{
//				$aTempData = $this->select( $sQuery );
//
//				$nTotalFound = count( $aTempData );
//
//				$nIDFilter = $aParams['schemes'];
//
//				$oDBFiltersTotals = new DBFiltersTotals();
//				$aFilterTotals = $oDBFiltersTotals->getFilterTotalsByIDFilter( $nIDFilter );
//
//				$aTotals = array();
//
//				if( in_array( 'total_count', $aFilterTotals ) )
//				{
//					$aTotals['count']['name'] = 'Брой';
//					$aTotals['count']['value'] = $nTotalFound . " бр.";
//					$aTotals['count']['data_format'] = DF_STRING;
//				}
//
//				return $aTotals;
//			}
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
//			$nTotalFound = count( $oResponse->oResult->aData );
			
			if( !empty( $nIDFilter ) )
			{
				$oResponse->setField( "id", "ID", "Сортирай по ID" );
				
				$aFilterVisibleFields = $oFiltersVisibleFields->getFieldsByIDFilter( $nIDFilter );
				
				foreach( $aFilterVisibleFields as $key => $value )
				{
					if( $value == "name" )
					{
						$oResponse->setField( "name", "Име", "Сортирай по Име" );
					}
					if( $value == "invoice_ein" )
					{
						$oResponse->setField( "invoice_ein", "ЕИН", "Сортирай по ЕИН" );
					}
					if( $value == "invoice_mol" )
					{
						$oResponse->setField( "invoice_mol", "МОЛ", "Сортирай по МОЛ" );
					}
					if( $value == "invoice_address" )
					{
						$oResponse->setField( "invoice_address", "Адрес за Фактуриране", "Сортирай по Адрес за Фактуриране" );
					}
					if( $value == "address" )
					{
						$oResponse->setField( "address", "Адрес", "Сортирай по Адрес" );
					}
					if( $value == "email" )
					{
						$oResponse->setField( "email", "E-Mail", "Сортирай по E-Mail" );
					}
					if( $value == "phone" )
					{
						$oResponse->setField( "phone", "Телефон за Контакти", "Сортирай по Телефон за Контакти" );
					}
					if( $value == "invoice_bring_to_object" )
					{
						$oResponse->setField( "invoice_bring_to_object", "Носене Фактура до Адрес", "Сортирай", "images/confirm.gif" );
					}
					if( $value == "invoice_payment" )
					{
						$oResponse->setField( "invoice_payment", "Начин на Плащане", "Сортирай по Начин на Плащане" );
					}
					if( $value == "invoice_layout" )
					{
						$oResponse->setField( "invoice_layout", "Изглед на Фактурата", "Сортирай по Изглед на Фактурата" );
					}
					if( $value == "object_name" )
					{
						$oResponse->setField( "object_name", "Име на Обект", "Сортирай по Име на Обект" );
					}
					if( $value == "object_num" )
					{
						$oResponse->setField( "object_num", "Номер на Обект", "Сортирай по Номер на Обект" );
					}
					if( $value == "object_city" )
					{
						$oResponse->setField( "object_city", "Населено Място", "Сортирай по Населено Място" );
					}
				}
				
				$oResponse->setField( "updated_user", "Последна редакция", "Сортирай по Последна Редакция" );
				
				//$oResponse->addTotal( "name", $nTotalFound );
			}
			else
			{
				$oResponse->setField( "id", "ID", "Сортирай по ID" );
				$oResponse->setField( "name", "Име", "Сортирай по Име" );
				$oResponse->setField( "address", "Адрес", "Сортирай по Адрес" );
				$oResponse->setField( "invoice_ein", "ЕИН", "Сортирай по ЕИН" );
				$oResponse->setField( "updated_user", "Последна редакция", "Сортирай по Последна Редакция" );
				
				//$oResponse->addTotal( "name", $nTotalFound );
			}
			
			if( $right_edit )
			{
				//$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteClient', '' );
				$oResponse->setFieldLink( "id", "viewClient" );
			}
		}
		
		public function isEINUnique( $sEIN )
		{
			if( empty( $sEIN ) )return true;
			
			$sQuery = "
					SELECT
						*
					FROM
						clients
					WHERE
						invoice_ein = '{$sEIN}'
					LIMIT 1
			";
			
			$aClient = $this->selectOnce( $sQuery );
			
			if( !empty( $aClient ) )return false;
			else return true;
		}
		
		public function getPaymentsReport( DBResponse $oResponse, $aParams )
		{
			global $db_name_personnel, $db_name_finance, $db_finance;
			
			$oSalesDocsMonth = new DBMonthTable( $db_name_finance, "sales_docs_", $db_finance );
            $DBEasypayProvider = new DBEasypayProvider();
			
			$nID = (int) isset( $aParams['nID'] ) ? $aParams['nID'] : 0;

            $aPayProvider = $DBEasypayProvider->getAllAssoc();
			
			$sRowQuery = "
					SELECT
						s.id,
						s.epay_provider,
						s.id_bank_epayment,
						s.easypay_date,
						s.doc_date,
						LPAD( s.doc_num, 10, 0 ) AS doc_num,
						s.doc_status AS doc_status,
						s.total_sum,
						s.orders_sum,
						
						s.total_sum - s.orders_sum AS orders_remain,
						s.last_order_time AS last_order_time
					FROM
						<table> s
					WHERE
						s.to_arc = 0
						AND s.id_client = {$nID}

			";
            //ABS( s.total_sum - s.orders_sum ) AS orders_remain,
			$nResult = $oSalesDocsMonth->makeUnionSelect( $sRowQuery );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Грешка при изпълнение на операцията!", $nResult );
			}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						t.id,
						t.epay_provider,
						t.id_bank_epayment,
						t.easypay_date,
						t.doc_date,
						t.doc_num,
						t.doc_status,
						t.total_sum,
						t.orders_sum,
						t.orders_remain,
						t.last_order_time
					FROM
						( {$sRowQuery} ) t
			";
			
			$this->getResult( $sQuery, 'doc_date', DBAPI_SORT_DESC, $oResponse );
			
			//Calculate Totals
			$nTotalSum = $nTotalPaid = $nTotalRemain = 0;
			foreach( $oResponse->oResult->aData as $value ) {
				if ($value['doc_status'] != 'canceled'){
					$nTotalSum 		+= $value['total_sum'];
					$nTotalPaid 	+= $value['orders_sum'];
					$nTotalRemain 	+= $value['orders_remain'];
				}
			}
			
			$oResponse->addTotal( "total_sum", 		$nTotalSum . " лв." 	);
			$oResponse->addTotal( "orders_sum", 	$nTotalPaid . " лв." 	);
			$oResponse->addTotal( "orders_remain", 	$nTotalRemain . " лв." 	);
			//End Calculate Totals

			$oResponse->setField( "doc_date", 			"Дата", 	"Сортирай по Дата на документ", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
			$oResponse->setField( "doc_num", 			"№", 		"Сортирай по Номер на документ", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_ZEROLEADNUM ) );
			$oResponse->setField( "total_sum", 			"Сума", 	"Сортирай по Сума на документ", 			NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "orders_sum", 		"Платено", 	"Сортирай по Платена сума", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "orders_remain", 		"Дължимо", 	"Сортирай по Дължима сума", 				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "last_order_time", 	"Плащане",  "Сортирай по Дата на последно плащане", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_DATE ) );
            $oResponse->setField( "epay_provider",          "iPay",                          "Сортирай по начин на плащане");

			$oResponse->setFieldLink( "doc_num", "openSaleDoc" );

			foreach ($oResponse->oResult->aData as $key => &$value) {
				if ($value['doc_status'] == 'canceled'){
					$oResponse->setDataAttributes( $key, 'doc_date', array( 'style' => 'color:#FF0000;' ) );
					$oResponse->setDataAttributes( $key, 'doc_num', array( 'title' => 'анулиран' ) );
					$oResponse->setDataAttributes( $key, 'total_sum', array( 'style' => 'color:#FF0000;' ) );
					$oResponse->setDataAttributes( $key, 'orders_sum', array( 'style' => 'color:#FF0000;' ) );
					$oResponse->setDataAttributes( $key, 'orders_remain', array( 'style' => 'color:#FF0000;' ) );
				}

                $paid_type_icon = "";
                $paid_type_hint = "";

                if ( $value['id_bank_epayment'] > 0 && $value['epay_provider'] > 0 ) {
                    APILog::Log($aPayProvider);
                    if(isset($aPayProvider[$value['epay_provider']])) {
                        $paid_type_icon = strtolower($aPayProvider[$value['epay_provider']]['name']).".png";
                        $paid_type_hint = "Електронно плащане през ".$aPayProvider[$value['epay_provider']]['name'];
                    }

                }

                $value['epay_provider'] = 0;
                $oResponse->setDataAttributes( $key, 'epay_provider', array("title" => $paid_type_hint, "style" => "cursor: default; height: 18px; background: url(images/{$paid_type_icon}) center no-repeat;" ) );
			}
		}
		
		public function detachObjectFromClient( $nIDClient, $nIDObject ) {
			global $db_sod, $db_name_sod;
			
			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			if ( empty($nIDClient) || !is_numeric($nIDClient) ) {
				$wh = " 1 ";
			} else {
				$wh = " id_client = {$nIDClient} "; 
			}
			
			$sQuery = "UPDATE {$db_name_sod}.clients_objects SET to_arc = 1 WHERE {$wh} AND id_object = {$nIDObject} ";
			$db_sod->Execute($sQuery);
			
			$sQuery = "UPDATE {$db_name_sod}.objects SET id_client = 0 WHERE id = {$nIDObject} ";
			$db_sod->Execute($sQuery);		
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function attachObjectToClient( $nIDClient, $nIDObject ) {
			global $db_sod, $db_name_sod;
			
			$oClientsObjects = new DBClientsObjects();
			
			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			if ( empty($nIDClient) || !is_numeric($nIDClient) ) {
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sAttachDate = date( "Y-m-d H:i:s" );
			
			$aData = array();
			$aData['id']			= 0;
			$aData['id_client'] 	= $nIDClient;
			$aData['id_object'] 	= $nIDObject;
			$aData['attach_date'] 	= $sAttachDate;
			
			$nResult = $oClientsObjects->update( $aData );
			
			$sQuery = "UPDATE {$db_name_sod}.objects SET id_client = {$nIDClient} WHERE id = {$nIDObject} ";
			$db_sod->Execute($sQuery);				
			
			return $nResult;
		}
		
		public function isObjectAttachedToClient( $nIDClient, $nIDObject ) {
			global $db_name_sod;
			
			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return false;
			}
			
			if ( empty($nIDClient) || !is_numeric($nIDClient) ) {
				return false;
			}
			
			$sQuery = "
					SELECT
						*
					FROM {$db_name_sod}.clients_objects
					WHERE to_arc = 0
						AND id_client = {$nIDClient}
						AND id_object = {$nIDObject}
			";
			
			$aData = $this->select( $sQuery );
			
			if ( empty($aData) ) {
				return false;
			} else {
				return true;
			}
		}
		
		public function getClientInfoByPhoneNumber( $sPhoneNumber )
		{
			$sQuery = "
				SELECT
					fac.name AS mol_name,
					obj.id AS object_id,
					obj.num AS object_num,
					obj.name AS object_name,
					obj.address AS object_address,
					cli.id AS client_id,
					cli.name AS client_name
				FROM
					clients cli
				LEFT JOIN
					objects obj ON obj.id_client = cli.id
				LEFT JOIN
					faces fac ON fac.id_obj = obj.id
				WHERE
					REPLACE( fac.phone, ' ', '' ) = '{$sPhoneNumber}'
					OR
					REPLACE( cli.phone, ' ', '' ) = '{$sPhoneNumber}'
					OR
					REPLACE( obj.phone, ' ', '' ) = '{$sPhoneNumber}'
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
	}

?>