<?php

	require_once('include/db_include.inc.php');

	class DBOrders extends DBMonthTable {
		
		function __construct() {
			global $db_name_finance, $db_finance;
			
			parent::__construct( $db_name_finance, PREFIX_ORDERS, $db_finance );
		}
		
		public function getReport( DBResponse $oResponse, $nIDDoc, $sDocType ) {
			global $db_finance, $db_name_personnel;
			
			$oDBSalesDocs 	= new DBSalesDocs();
			$aSaleDocs 		= array();
			
			if ( $this->isValidID($nIDDoc) ) {
				$oDBSalesDocs->getRecord( $nIDDoc, $aSaleDocs );
				
				//$sTableName = PREFIX_ORDERS.substr($nIDDoc,0,6);
				$sTableName = isset($aSaleDocs['last_order_id']) ? PREFIX_ORDERS.substr( $aSaleDocs['last_order_id'], 0, 6 ) : PREFIX_ORDERS.substr($nIDDoc,0,6);
				
			} else {
				throw new Exception("Невалидно id");
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					t.id,
					t.num,
					t.order_date,
					t.order_sum,
					t.note,
					CONCAT(CONCAT_WS(' ',p.fname,p.mname,p.lname),' ',DATE_FORMAT(t.created_time,'%d.%m.%Y %H:%i:%s')) AS created
				FROM {$sTableName} t
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = t.created_user
				WHERE t.doc_type = {$this->_oDB->Quote($sDocType)}
					AND t.doc_id = {$nIDDoc}			
			";
			//APILog::Log(0, $sQuery);
			$this->getResult($sQuery,'num',DBAPI_SORT_DESC,$oResponse);
			
			foreach ($oResponse->oResult->aData as $key => &$value) {
				$value['num'] = zero_padding($value['num'],10);
				$oResponse->setDataAttributes($key,'num',array('style' => 'text-align:right;'));
			}
			
			$oResponse->setField('num','Номер','Сортирай по номер');
			$oResponse->setField('order_date','Дата','Сортирай по дата',NULL,NULL,NULL,array('DATA_FORMAT' => DF_DATETIME));
			$oResponse->setField('order_sum','Сума','Сортирай по сума',NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY));
			$oResponse->setField('note','Допълнителна информация','Сортирай по допълнителна информация');
			$oResponse->setField('created','Създал','Сортирай по създал');
			$oResponse->setFieldLink('num','openOrder');
		}
		
		public function sumOrders($aParams) {
			
			$nIDDoc = isset($aParams['id_doc']) ? $aParams['id_doc'] : 0;
			$sDocType = isset($aParams['doc_type']) ? $aParams['doc_type'] : '';
			$sOrderType = isset($aParams['order_type']) ? $aParams['order_type'] : '';
			
			if($this->isValidID($nIDDoc)) {
				$sTableName = PREFIX_ORDERS.substr($nIDDoc,0,6);
			} else {
				throw new Exception("Невалидно id");
			}
			
			$sQuery = "
				SELECT 
					SUM(order_sum) AS order_sum
				FROM {$sTableName}
				WHERE doc_id = {$nIDDoc}
					AND doc_type = {$this->_oDB->Quote($sDocType)}
			";
			
			if(!empty($sOrderType)) {
				$sQuery .= " AND order_type = {$this->_oDB->Quote($sOrderType)} \n";
			}
			
			$sQuery .= "
				GROUP BY doc_id
			";
			
			return $this->selectOne2($sQuery);
		}
		
		public function getOrders($aParams) {
			
			$nIDDoc = isset($aParams['id_doc']) ? $aParams['id_doc'] : 0;
			$sDocType = isset($aParams['doc_type']) ? $aParams['doc_type'] : '';
			$sOrderType = isset($aParams['order_type']) ? $aParams['order_type'] : '';
			
			if($this->isValidID($nIDDoc)) {
				$sTableName = PREFIX_ORDERS.substr($nIDDoc,0,6);
			} else {
				throw new Exception("Невалидно id");
			}
			
			$sQuery = "
				SELECT 
					*
				FROM {$sTableName}
				WHERE doc_id = {$nIDDoc}
					AND doc_type = {$this->_oDB->Quote($sDocType)}
			";
			
			if(!empty($sOrderType)) {
				$sQuery .= " AND order_type = {$this->_oDB->Quote($sOrderType)} \n";
			}
			
			return $this->select2($sQuery);
		}

		public function getReportCurrencyMovement( DBResponse $oResponse, $aParams ) {
			global $db_name_personnel, $db_name_sod, $db_finance;

			$nTimeFrom		= jsDateToTimestamp( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp( $aParams['sToDate'] );
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
			$nIDClient      = (int)$aParams['nClientNum'];

			$oSaleDocs		= new DBSalesDocs();
			$oBuyDocs		= new DBBuyDocs();

			$nIDAccount = ( isset( $aParams['nIDBankAccount'] ) && !empty( $aParams['nIDBankAccount'] ) ) ? $aParams['nIDBankAccount'] : 0;

			$sQuery = "
				SELECT
					DISTINCT LEFT( ord.doc_id, 6 ) AS ym,
					ord.doc_type AS doc_type
				FROM
					<table> ord
			";

			$aTablesRaw = array();
			$this->select( $sQuery, $aTablesRaw, $nTimeFrom, $nTimeTo );

			$aTables = array();
			foreach( $aTablesRaw as $nKey => $aTable )
			{
				if( $aTable['doc_type'] == 'sale' )
				{
					$aTables['sale'][$aTable['ym']] = "
						( SELECT * FROM sales_docs_{$aTable['ym']} )
					";
				}
				if( $aTable['doc_type'] == 'buy' )
				{
					$aTables['buy'][$aTable['ym']] = "
						( SELECT * FROM buy_docs_{$aTable['ym']} )
					";
				}
			}

//			$aUnions = array();
//			$aUnions['sale'] = implode( " UNION ALL ", $aTables['sale'] );
//			$aUnions['buy'] = implode( " UNION ALL ", $aTables['buy'] );


			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						o.id,
						
						IF ( o.doc_type = 'sale', cl.name, f.jur_name ) as client_name,
						IF ( o.doc_type = 'sale', f.jur_name, cl.name ) as deliverer_name,
						
						LPAD( o.num, 10, 0 ) AS order_num,
						DATE_FORMAT( o.order_date, '%d.%m.%Y %H:%i:%s' ) AS order_date,
						UNIX_TIMESTAMP( o.order_date ) AS order_date_sort,
						CASE o.order_type
							WHEN 'earning' THEN ABS( o.order_sum )
							WHEN 'expense' THEN 0
						END AS order_income,
						CASE o.order_type
							WHEN 'earning' THEN 0
							WHEN 'expense' THEN ABS( o.order_sum )
						END AS order_outgo,
						o.order_sum AS order_difference,
						o.account_sum AS order_account_balance,
						
						o.doc_num AS order_doc_num,
						
						IF( o.order_type = 'expense', 1, 0 ) AS is_expense,
						o.note AS order_note,
						
						o.doc_type AS link_doc_type,
						o.doc_id as link_doc_id,
						
						IF
						(
							o.updated_user,
							CONCAT( CONCAT_WS( ' ', p.fname, p.mname, p.lname ), ' ' , DATE_FORMAT( o.updated_time, '%d.%m.%Y %H:%i:%s' ) ),
							''
						) AS updated
					FROM
						<table> o
			";

			/*
                                    CONCAT(
                                        CASE o.doc_type
                                            WHEN 'sale' THEN
                                                CASE sd.doc_type
                                                    WHEN 'kvitanciq' THEN '[кв.]'
                                                    WHEN 'faktura' THEN '[ф-ра]'
                                                    WHEN 'oprostena' THEN '[опр. кв.]'
                                                END
                                            WHEN 'buy' THEN
                                                CASE bd.doc_type
                                                    WHEN 'kvitanciq' THEN '[кв.]'
                                                    WHEN 'faktura' THEN '[ф-ра]'
                                                    WHEN 'oprostena' THEN '[опр. ф-ра]'
                                                END
                                        END,
                                        ' ',
                                        CASE o.doc_type
                                            WHEN 'sale' THEN LPAD( sd.doc_num, 10, 0 )
                                            WHEN 'buy' THEN LPAD( bd.doc_num, 10, 0 )
                                        END
                                    ) AS order_doc_num,
            */
			//Dynamic Buy Docs
//			if( empty( $aUnions['buy'] ) )
//			{
//				$sQuery .= "
//					LEFT JOIN
//						buy_docs_<yearmonth> bd ON bd.id = o.doc_id
//				";
//			}
//			else
//			{
//				$sQuery .= "
//					LEFT JOIN
//						( {$aUnions['buy']} ) AS bd ON bd.id = o.doc_id
//				";
//			}
			//End Dynamic Buy Docs
			//Dynamic Sales Docs
//			if( empty( $aUnions['sale'] ) )
//			{
//				$sQuery .= "
//					LEFT JOIN
//						sales_docs_<yearmonth> sd ON sd.id = o.doc_id
//				";
//			}
//			else
//			{
//				$sQuery .= "
//					LEFT JOIN
//						( {$aUnions['sale']} ) AS sd ON sd.id = o.doc_id
//				";
//			}
			//End Dynamic Sales Docs

			$sQuery .= "
					LEFT JOIN {$db_name_personnel}.personnel p ON p.id = o.updated_user
					LEFT JOIN {$db_name_sod}.clients cl ON cl.id = o.id_contragent
					LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_doc_firm
					WHERE
						( UNIX_TIMESTAMP( o.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( o.order_date ) <= {$nFullTimeTo} )
			";

			if ( !empty($nIDClient) ) {
				$sQuery .= " AND o.id_contragent = {$nIDClient} ";
			}

			//Filtering
			if( !empty( $nIDAccount ) )
			{
				$sQuery .= "
					AND o.bank_account_id = {$nIDAccount}
				";
			}
			//End Filtering

			$aTotalData = array();
			$nResult = $this->selectFromDB( $db_finance, $sQuery, $aTotalData, $nTimeFrom, $nTimeTo );
			if( $nResult != DBAPI_ERR_SUCCESS )
			{
				throw new Exception( "Грешка при изпълнение на операцията!", DBAPI_ERR_SQL_QUERY );
			}

			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );

			$this->getResult( $sQuery, 'order_num', DBAPI_SORT_DESC, $oResponse );

			//Page Totals
			$sTotalIncome = $sTotalOutgo = 0;

			foreach( $aTotalData as $nKey => $aValue )
			{
				$sTotalIncome += $aValue['order_income'];
				$sTotalOutgo += $aValue['order_outgo'];
			}

			$sTotalIncome .= " лв.";
			$sTotalOutgo .= " лв.";

			$oResponse->addTotal( "order_income", $sTotalIncome );
			$oResponse->addTotal( "order_outgo", $sTotalOutgo );
			//End Page Totals

			foreach( $oResponse->oResult->aData as $nKey => &$aValue ) {
				$aSaleDoc	= array();
				$aBuyDoc	= array();
				$nIDDoc		= is_numeric($aValue['link_doc_id']) && !empty($aValue['link_doc_id']) ? $aValue['link_doc_id'] : 0;

				if ( $aValue['is_expense'] ) {
					$oResponse->setRowAttributes( $aValue['id'], array("style" => "color: FF3C3C;") );
				}

				// Doc num
				$aValue['order_doc_num'] = zero_padding($aValue['order_doc_num'], 10);

				if ( $aValue['link_doc_type'] == "sale" ) {
					$aSaleDoc = $oSaleDocs->getDoc($nIDDoc);

					if ( isset($aSaleDoc['doc_type']) ) {
						switch ($aSaleDoc['doc_type']) {
							case "kvitanciq":	$aValue['order_doc_num'] = "[кв.] ".$aValue['order_doc_num'];
								break;

							case "faktura": 	$aValue['order_doc_num'] = "[ф-ра] ".$aValue['order_doc_num'];
								break;

							case "oprostena": 	$aValue['order_doc_num'] = "[опр. кв.] ".$aValue['order_doc_num'];
								break;

							default: 			$aValue['order_doc_num'] = "[опр. кв.] ".$aValue['order_doc_num'];
								break;
						}
					}
				} else {
					$aBuyDoc = $oBuyDocs->getDoc($nIDDoc);

					if ( isset($aBuyDoc['doc_type']) ) {
						switch ($aBuyDoc['doc_type']) {
							case "kvitanciq":	$aValue['order_doc_num'] = "[кв.] ".$aValue['order_doc_num'];
								break;

							case "faktura": 	$aValue['order_doc_num'] = "[ф-ра] ".$aValue['order_doc_num'];
								break;

							case "oprostena": 	$aValue['order_doc_num'] = "[опр. ф-ра] ".$aValue['order_doc_num'];
								break;

							default: 			$aValue['order_doc_num'] = "[кв.] ".$aValue['order_doc_num'];
								break;
						}
					}
				}

				//Fix Links
				$aWhatToSet 			= array();
				$aWhatToSet['align'] 	= "center";

				if ( !empty($nIDDoc) ) {
					$sWhatToCall 			= "openDoc( {$nIDDoc}, '{$aValue['link_doc_type']}' );";
					$aWhatToSet['style'] 	= "cursor: pointer;";
					$aWhatToSet['onclick'] 	= $sWhatToCall;
				}

				$oResponse->setDataAttributes( $nKey, "order_doc_num", $aWhatToSet );
				//End Fix Links
			}

			//Life-time Totals
			if( isset( $aParams['nRefreshTotals'] ) && !empty( $aParams['nRefreshTotals'] ) )
			{
				$nTotalStartBalance = $nTotalEndBalance = 0;

				$nTotalStartBalance = round( $this->getAccountBalanceStart( $nIDAccount, $aParams['sFromDate'] ), 2 );
				$nTotalEndBalance 	= round( $this->getAccountBalanceEnd( $nIDAccount, $aParams['sToDate'] ), 2 );

				if( empty( $nTotalStartBalance ) )$nTotalStartBalance = 0;
				if( empty( $nTotalEndBalance ) )$nTotalEndBalance = 0;

				$nTotalStartBalance = sprintf( "%01.2f лв.", $nTotalStartBalance );
				$nTotalEndBalance = sprintf( "%01.2f лв.", $nTotalEndBalance );

				//Totals
				$oResponse->setFormElement( "form1", "nTotalStartBalance", array( "value" => $nTotalStartBalance ), $nTotalStartBalance );
				$oResponse->setFormElement( "form1", "nTotalEndBalance", array( "value" => $nTotalEndBalance ), $nTotalEndBalance );
				//End Totals

				$oResponse->setFormElement( "form1", "nRefreshTotals", array( "value" => "0" ), "0" );
			}
			//End Life-time Totals

			$oResponse->setField( 'client_name', 			'Име на Клиент',			'Сортирай по Име на Клиент', 			NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'deliverer_name', 		'Име на Доставчик',			'Сортирай по Име на Доставчик', 		NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'order_num', 				'Номер', 					'Сортирай по Номер', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'order_date', 			'Време',					'Сортирай по Време', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DATETIME ) );
			$oResponse->setField( 'order_income', 			'Приход', 					'Сортирай по Приход', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_outgo', 			'Разход', 					'Сортирай по Разход', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_difference', 		'Реална Стойност', 			'Сортирай по Реална Стойност', 			NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_account_balance', 	'Наличност', 				'Сортирай по Наличност', 				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_doc_num', 			'Номер на Документ', 		'Сортирай по Номер на Документ' );
			$oResponse->setField( 'order_note', 			'Допълнителна информация', 	'Сортирай по Допълнителна Информация', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'updated', 				'...', 						'Сортирай по Последна Редакция', 		"images/dots.gif" );

			$oResponse->setFieldLink( 'order_num', 'openOrder' );
		}
		
		/**
		 * Функция за намиране на начално салдо по сметка за стартова дата на период от време.
		 *
		 * @param int $nIDAccount ( ID-то на сметката )
		 * @param int $sDate ( Във формат "ДД.ММ.ГГГГ" )
		 * @return float
		 */
		function getAccountBalanceStart( $nIDAccount, $sDate )
		{
			$oDBAccountStates = new DBAccountStates();
			
			//Validation
			if( empty( $sDate ) || strlen( $sDate ) != 10 || empty( $nIDAccount ) && !is_numeric( $nIDAccount ) )
			{
				return 0;
			}
			//End Validation
			
			//Fix Dates
			$nLeastTo = jsDateToTimestamp( $sDate );
			$sLeastTo = jsDateToMySQLDate( $sDate );
			
			$sLeastTo .= " 00:00:00";
			//End Fix Dates
			
			$sQuery = "
				SELECT
					ord.id as id,
					IF
					(
						ord.order_type = 'earning',
						ord.account_sum - ord.order_sum,
						ord.account_sum + ord.order_sum
					) AS account_sum,
					ord.order_date AS order_date,
					ord.num
				FROM
					<table> ord
				WHERE
					UNIX_TIMESTAMP( ord.order_date ) >= UNIX_TIMESTAMP( '{$sLeastTo}' )
					AND ord.bank_account_id = {$nIDAccount}
			";
			
			$this->makeUnionSelect( $sQuery, $nLeastTo, 0 );
			
			$sQuery .= "
				ORDER BY
					id ASC
				LIMIT 1
			";
			
			$aData = array();
			$aData = $this->selectOnce2( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['account_sum'] ) )
			{
				return $aData['account_sum'];
			}
			else
			{
				$aRow = array();
				$aRow = $oDBAccountStates->getRow( NULL, 0, $nIDAccount );
				
				if( !empty( $aRow ) && isset( $aRow['current_sum'] ) )
				{
					return $aRow['current_sum'];
				}
				
				return 0;
			}
		}
		
		/**
		 * Функция за намиране на крайно салдо по сметка за крайна дата на период от време.
		 *
		 * @param int $nIDAccount ( ID-то на сметката )
		 * @param int $sDate ( Във формат "ДД.ММ.ГГГГ" )
		 * @return float
		 */
		function getAccountBalanceEnd( $nIDAccount, $sDate )
		{
			$oDBAccountStates = new DBAccountStates();
			
			//Validation
			if( empty( $sDate ) || strlen( $sDate ) != 10 || empty( $nIDAccount ) && !is_numeric( $nIDAccount ) )
			{
				return 0;
			}
			//End Validation
			
			//Fix Dates
			$nMostTo = jsDateToTimestamp( $sDate );
			$sMostTo = jsDateToMySQLDate( $sDate );
			
			$sMostTo .= " 23:59:59";
			//End Fix Dates
			
			$sQuery = "
				SELECT
					ord.id AS id,
					ord.account_sum AS account_sum,
					ord.order_date AS order_date,
					ord.num
				FROM
					<table> ord
				WHERE
					UNIX_TIMESTAMP( ord.order_date ) <= UNIX_TIMESTAMP( '{$sMostTo}' )
					AND ord.bank_account_id = {$nIDAccount}
			";
			
			$this->makeUnionSelect( $sQuery, 0, $nMostTo );
			
			$sQuery .= "
				ORDER BY
					id DESC
				LIMIT 1
			";
			
			$aData = array();
			$aData = $this->selectOnce2( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['account_sum'] ) )
			{
				return $aData['account_sum'];
			}
			else
			{
				$aRow = array();
				$aRow = $oDBAccountStates->getRow( NULL, 0, $nIDAccount );
				
				if( !empty( $aRow ) && isset( $aRow['current_sum'] ) )
				{
					return $aRow['current_sum'];
				}
				
				return 0;
			}
		}
		
			function jsDateEndToTimestamp( $sDate )
		{
			if( !empty( $sDate ) )
			{
				@list( $d, $m, $y ) = explode( ".", $sDate );
				
				if( @checkdate( $m, $d, $y ) )
				{
					return mktime( 23, 59, 59, $m, $d, $y );
				}
			}
			
			return 0;
		}
		
		function jsDateEndToTimestamp2( $sDate ) {
			if ( !empty($sDate) ) {
				@list($d, $m, $y) = explode( ".", date("d.m.Y", $sDate) );
				
				if ( @checkdate($m, $d, $y) ) {
					return mktime( 23, 59, 59, $m, $d, $y );
				}
			}
			
			return 0;
		}		
		
		public function getMomentSaldoBefore($nIDSaldo, $nTimeFrom) {
			global $db_name_sod, $db_name_finance;
			
			$timeTo	 	= mktime(23, 59, 0, date("m", $nTimeFrom), date("d", $nTimeFrom) -1, date("Y", $nTimeFrom));
			$timeFrom	= mktime(0, 0, 0, date("m", $nTimeFrom) -1, date("d", $nTimeFrom), date("Y", $nTimeFrom));
			
			$sQuery = "
				(SELECT
					ord_row.id,
					ord_row.saldo_state as sum,
					IF (ord.doc_type = 'buy', ord_row.paid_sum *-1, ord_row.paid_sum) as min_sum
				FROM {$db_name_finance}.orders_rows_<yearmonth> ord_row
				LEFT JOIN <table> ord ON ( ord.id = ord_row.id_order  )						
				WHERE ord_row.id_saldo = {$nIDSaldo}
					AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$timeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$timeTo} )
				ORDER BY ord.id DESC)
			";

			$this->makeUnionSelect( $sQuery, $timeFrom, $timeTo );

			$sQuery .= " ORDER BY id DESC LIMIT 1 ";

			$sSum = $this->select2($sQuery);
			
			if ( isset($sSum[0]) ) {
				return $sSum[0];
			} else return array();
		}
				
		public function getTotals( $aParams ) {
			global $db_finance, $db_name_finance, $db_name_sod;

			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] );
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );


			$sSum			= array();	
			$aData			= array();	
			$aDataSum		= array();
			$aTotal			= array();
			
			$sQuery = "
				SELECT
					ord_row.id_saldo,
					GROUP_CONCAT(DISTINCT ord_row.saldo_state ORDER BY ord.id, ord_row.id ASC SEPARATOR ';') as sum,
					GROUP_CONCAT(DISTINCT IF (ord.doc_type = 'buy', ord_row.paid_sum *-1, ord_row.paid_sum) ORDER BY ord.id, ord_row.id ASC SEPARATOR ';') as min_sum
				FROM {$db_name_finance}.orders_rows_<yearmonth> ord_row
				LEFT JOIN <table> ord ON ( ord.id = ord_row.id_order  )						
				WHERE ord.order_sum != 0
					AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} )
					
			";
			//AND ord.order_status = 'active'	

			$sQuery .= " GROUP BY ord_row.id_saldo ";
			
			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nFullTimeTo );
			
			$sSum = $this->select2($sQuery);
			//APILog::log(0, $sQuery);
			foreach ( $sSum as $key ) {
				$sKey 		= isset($key['sum']) 		? $key['sum'] 		: "";
				$sSum 		= isset($key['min_sum']) 	? $key['min_sum']	: "";
				$nSal 		= isset($key['id_saldo']) 	? $key['id_saldo'] 	: 0;
				
				$aData 		= explode(";", $sKey);
				$aDataSum	= explode(";", $sSum);
				$tmpArr		= array();
				$nSumMin 	= 0;
				$nSumMax 	= 0;				

				if ( (count($aData) > 0) && (count($aDataSum) > 0) ) {
					$nSumMin = $aData[0] - $aDataSum[0];
					$nSumMax = $aData[count($aData) -1];
					
					$tmpArr	 		= array("min" => $nSumMin, "max" => $nSumMax);
					$aTotal[$nSal]	= $tmpArr;
				}
			}
			
			return $aTotal;
		}		
		
		public function getTotals2( $aParams ) {
			global $db_finance, $db_name_finance, $db_name_sod;

			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] );
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );


			$sSum			= array();	
			$aData			= array();	
			$aDataSum		= array();
			$aTotal			= array();
			
			$sQuery = "
				SELECT
					ord.bank_account_id as id_bank,
					GROUP_CONCAT(DISTINCT ord_row.account_state ORDER BY ord.id, ord_row.id ASC SEPARATOR ';') as sum,
					GROUP_CONCAT(DISTINCT ord.account_sum ORDER BY ord.id, ord_row.id ASC SEPARATOR ';') as sum2,
					GROUP_CONCAT(DISTINCT IF (ord.doc_type = 'buy', ord_row.paid_sum *-1, ord_row.paid_sum) ORDER BY ord.id, ord_row.id ASC SEPARATOR ';') as min_sum
				FROM {$db_name_finance}.orders_rows_<yearmonth> ord_row
				LEFT JOIN <table> ord ON ( ord.id = ord_row.id_order  )						
				WHERE ord.order_sum != 0
					AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} )
				GROUP BY ord.bank_account_id
			";
			
			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nFullTimeTo );

			$sSum = $this->select2($sQuery);
			//APILog::log(0, $sQuery);
			foreach ( $sSum as $key ) {
				$sKey 		= isset($key['sum']) 		? $key['sum'] 		: "";
				$sSum 		= isset($key['min_sum']) 	? $key['min_sum']	: "";
				$nBank 		= isset($key['id_bank']) 	? $key['id_bank'] 	: 0;
				
				$aData 		= explode(";", $sKey);
				$aDataSum	= explode(";", $sSum);
				$tmpArr		= array();
				$nSumMin 	= 0;
				$nSumMax 	= 0;				

				if ( (count($aData) > 0) && (count($aDataSum) > 0) ) {
					$nSumMin = $aData[0] - $aDataSum[0];
					$nSumMax = $aData[count($aData) -1];
					
					$tmpArr	 		= array("min" => $nSumMin, "max" => $nSumMax);
					$aTotal[$nBank]	= $tmpArr;
				}
			}
			
			return $aTotal;
		}				
		
		function getBalanceReport( DBResponse $oResponse, $aParams ) {
			$oDBAccountStates 	= new DBAccountStates();
			$nTotal	 			= 0;
			$aData2				= array();
			$aData 				= $oDBAccountStates->getAllPermited( $oResponse );

			$aData2 = $this->getTotals2($aParams);	
			
			//APILog::log(0, $aData2);

			foreach ( $aData as $nKey => &$aItem ) {
				$nTotal += $aItem["account_sum"];
				$min 	= 0;
				$max 	= 0;				
				$nRow	= isset($aItem['id']) ? $aItem['id'] : 0;
				
				if ( isset($aData2[$nRow]['min']) ) {
					$min = $aData2[$nRow]['min'];
					$max = $aData2[$nRow]['max'];
				} 
				
				$aItem['min'] 		= $min;
				$aItem['max'] 		= $max;
				$aItem['change'] 	= $max - $min;
			}
			
			$oResponse->setField("name", 		"Сметка");
			$oResponse->setField("account_sum", "Наличност", 	"", NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			$oResponse->setField("min", 		"Начална", 		"", NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			$oResponse->setField("max", 		"Крайна", 		"", NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			$oResponse->setField("change", 		"Промяна", 		"", NULL, "MoneyNomenclaturesDetailsView", NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			
			$oResponse->addTotal("account_sum",	$nTotal);
			
			$oResponse->setData($aData);
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function getReportBySaldo(DBResponse $oResponse, $aParams) {
			global $db_finance, $db_name_personnel, $db_name_sod, $db_name_finance;
			
			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] );
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
			
			$nIDFirm		= isset($aParams['nIDFirm']) 	? $aParams['nIDFirm'] 	: 0;
			$nIDOffice		= isset($aParams['nIDOffice']) 	? $aParams['nIDOffice'] : 0;
			$nIDObject		= isset($aParams['nIDObject']) 	? $aParams['nIDObject'] : 0;
			$nIDPerson		= isset($aParams['nIDPerson']) 	? $aParams['nIDPerson'] : 0;			
			$cDDS			= isset($aParams['cDDS']) 		? $aParams['cDDS'] 		: 0;
			$cTransfer		= isset($aParams['cTransfer']) 	? $aParams['cTransfer'] : 0;
			$sMonth			= isset($aParams['sMonth']) 	? $aParams['sMonth'] 	: "0000-00";
			$sOrderType		= isset($aParams['sOrderType']) ? $aParams['sOrderType'] : "all";
			$nIDNom			= isset($aParams['nIDNomenclature']) 	? $aParams['nIDNomenclature'] : 0;
			$nIDAccount		= isset($aParams['nIDBankAccount']) 	? $aParams['nIDBankAccount']  : 0;
			$nIDDirection	= isset($aParams['nIDDirection']) 		? $aParams['nIDDirection'] 	: 0;
		
			$oOffices		= new DBOffices();
			$oSaleDoc		= new DBSalesDocs();
			$oBuyDoc		= new DBBuyDocs();

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					o.id,
					LPAD( o.num, 10, 0 ) AS order_num,
					o.doc_type,
					o.order_status,
					o.doc_id AS id_document,
					of.name AS office,
					f.name as firm,
					DATE_FORMAT(o.order_date, '%d.%m.%Y') AS order_date,
					IF (o.doc_type = 'sale', ow.paid_sum, 0) AS order_earning_sum,
					IF (o.doc_type = 'buy', ow.paid_sum, 0) AS order_expense_sum,
					
					ow.saldo_state as saldo,
					IF (ow.is_dds > 0, 'ДДС', IF (o.doc_type = 'sale', IF (ner.id > 0, ner.name, 'Невъведена'), IF (nex.id > 0, nex.name, 'Невъведенa')) ) AS nomenclature_name,
					IF (ow.is_dds > 0, 'ДДС', IF (o.doc_type = 'sale', IF (ner.id > 0, CONCAT('222', ner.id), -3), IF (nex.id > 0, CONCAT('222', nex.id), -3)) ) AS id_nomenclature,
					o.note AS order_note,
					IF (o.updated_user, CONCAT(CONCAT_WS(' ', p.fname, p.mname, p.lname), ' ' , DATE_FORMAT(o.updated_time, '%d.%m.%Y %H:%i:%s')), '') AS updated					
				FROM <table> o
				LEFT JOIN {$db_name_finance}.orders_rows_<yearmonth> ow ON ow.id_order = o.id
				LEFT JOIN {$db_name_finance}.saldo s ON s.id = ow.id_saldo	
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = o.updated_user			
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = ow.id_service AND ow.id_service > 0 )
				LEFT JOIN {$db_name_finance}.nomenclatures_earnings ner ON ( ner.id = ns.id_nomenclature_earning AND ns.id_nomenclature_earning > 0 )
				LEFT JOIN {$db_name_finance}.nomenclatures_expenses nex ON ( nex.id = ow.id_nomenclature_expense AND ow.id_nomenclature_expense > 0 )
				LEFT JOIN {$db_name_sod}.offices of ON ( of.id = ow.id_office AND ow.id_office > 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = of.id_firm AND of.id_firm > 0 )				
										
				WHERE ( UNIX_TIMESTAMP( o.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( o.order_date ) <= {$nFullTimeTo} )
			";
			
			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND ow.id_office = '{$nIDOffice}' ";
			} elseif ( !empty($nIDFirm) ) {
				$sID	= $oOffices->getIdsByFirm($nIDFirm);
				
				if ( !empty($sID) ) {
					$sQuery .= " AND ow.id_office IN ({$sID}) ";
				}
			}
			
			if ( !empty($cDDS) ) {
				$sQuery .= " AND ow.is_dds = 0 ";
			}
			
			if ( !empty($cTransfer) ) {
				$sQuery .= " AND ( IF (ow.is_dds = 0, IF(o.doc_type = 'sale', ns.for_transfer = 0, nex.for_transfer = 0), 1) ) ";
			}			
			
			if ( !empty($nIDObject) ) {
				$sQuery .= " AND ow.id_object = {$nIDObject} ";
			}
			
			if ( !empty($nIDPerson) ) {
				$sQuery .= " AND o.updated_user = {$nIDPerson} ";
			}	

			if ( !empty($nIDAccount) ) {
				$sQuery .= " AND o.bank_account_id = {$nIDAccount} ";
			}				
			
			if ( !empty($nIDDirection) ) {
				$sQuery .= " AND ow.id_direction = {$nIDDirection} ";
			}
						
			if ( $sOrderType == "earning" ) {
				$sQuery .= " AND o.doc_type = 'sale' ";
			} elseif ( $sOrderType == "expense" ) {
				$sQuery .= " AND o.doc_type = 'buy' ";
			}
			
			if ( !empty($sMonth) ) {
				$sQuery .= " AND DATE_FORMAT(ow.month, '%Y-%m') = '{$sMonth}' ";
			}	

			if ( !empty($nIDNom) ) {
				if ( $nIDNom > 0 ) {
					$nIDN 	= substr($nIDNom, 3);
					$nFlag 	= substr($nIDNom, 0, 3) == "111" ? "earning" : "expense";
					
					if ( $nFlag == "earning" ) {
						$sQuery .= " AND ner.id = '{$nIDN}' ";
					} else {
						$sQuery .= " AND nex.id = '{$nIDN}' ";
					}
				} else {
					if ( $nIDNom == -1 ) {
						$sQuery .= " AND ow.is_dds > 0 ";
					} elseif ( $nIDNom == -2 ) {
						$sQuery .= " AND ( IF (ow.is_dds = 0, IF(o.doc_type = 'sale', ns.for_transfer = 1, nex.for_transfer = 1), 0) ) ";
					} elseif ( $nIDNom == -3 ) {
						$sQuery .= " AND ( IF (ow.is_dds = 0, IF(o.doc_type = 'sale', ner.id is null, nex.id is null), 0) ) ";
					} else {
						$sQuery .= " AND 0 ";
					}
				}
			}				
			
			$sRealQuery = $sQuery;
			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );
			//APILog::Log(0, $sQuery);
			$this->getResult( $sQuery, 'order_num', DBAPI_SORT_DESC, $oResponse );	

			foreach ($oResponse->oResult->aData as $key => &$value) {
				$nIDDoc = $value['id_document'];
				$aDoc	= array();
				$sType	= $value['doc_type'];
				
				if ( $sType == "sale" ) {
					$aDoc = $oSaleDoc->getDoc($nIDDoc);
					
					if ( $value['order_status'] != "active" ) {
						$oResponse->setRowAttributes( $value['id'], array("style" => "color: green; font-style: italic; text-decoration: line-through;") );
					} else {
						$oResponse->setRowAttributes( $value['id'], array("style" => "color: green;") );
					}
				} elseif ( $sType == "buy" ) {
					$aDoc = $oBuyDoc->getDoc($nIDDoc);
					
					if ( $value['order_status'] != "active" ) {
						$oResponse->setRowAttributes( $value['id'], array("style" => "color: FF3C3C; font-style: italic; text-decoration: line-through;") );
					} else {
						$oResponse->setRowAttributes( $value['id'], array("style" => "color: FF3C3C;") );
					}					
				}
				
				$value['client_name'] 	= isset($aDoc['client_name']) 	? $aDoc['client_name'] 				 : "Неизвестен";
				$value['order_doc_num'] = isset($aDoc['doc_num']) 		? zero_padding($aDoc['doc_num'], 10) : zero_padding(0, 10);
				
				$oResponse->setDataAttributes( $key, "order_num",		array("style" => "text-align: right; padding-right: 10px;") );
				$oResponse->setDataAttributes( $key, "order_doc_num", 	array("onclick" => "openDoc({$nIDDoc}, '{$sType}')", "style" => "cursor: pointer; text-align: right; padding-right: 10px;") );
			}				
			
			$oResponse->setField( 'order_num', 				'Ордер', 					'Сортирай по Номер на Ордера', 						NULL, NULL, NULL, NULL );
			$oResponse->setField( 'order_date', 			'Време',					'Сортирай по Време', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DATE ) );
			$oResponse->setField( 'client_name', 			'Име на Клиент', 			'Сортирай по Име на Клиент', 						NULL, NULL, NULL, NULL );
			$oResponse->setField( 'nomenclature_name', 		'Номенклатура',				'Сортирай по Номенклатура', 						NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'firm', 					'Фирма',					'Сортирай по Фирма', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'office', 				'Регион',					'Сортирай по Регион', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'order_earning_sum', 		'Приход по Ном.', 			'Сортирай по Приход по Номенклатурата от Ордера', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_expense_sum', 		'Разход по Ном.', 			'Сортирай по Разход по Номенклатурата от Ордера', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'saldo',			 		'Салдо', 					'Сортирай по Салдо', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_doc_num', 			'Документ', 				'Сортирай по Номер на Документ', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'order_note', 			'Описание', 				'Сортирай по Описание', 							NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'updated', 				'...', 						'Сортирай по Последна Редакция', "images/dots.gif" );
			
			$oResponse->setFieldLink( 'order_num', 		'openOrder' );		

			// ТОТАЛИ!!!
			if( isset($aParams['nRefreshTotals']) && !empty($aParams['nRefreshTotals']) ) {
				$aFullData = array();
				$nResult = $this->selectFromDB( $db_finance, $sRealQuery, $aFullData, $nTimeFrom, $nTimeTo );
				
				if ( $nResult != DBAPI_ERR_SUCCESS ) {
					throw new Exception( NULL, $nResult );
				}
				
				$nTotalEarnings = 0;
				$nTotalExpenses = 0;
				$nTotalDifference = 0;	

				foreach( $aFullData as $aElement ) {
					if ( isset($aElement['order_earning_sum']) ) {
						$nTotalEarnings += $aElement['order_earning_sum'];
					}
					
					if ( isset($aElement['order_expense_sum']) ) {
						$nTotalExpenses += $aElement['order_expense_sum'];
					}
				}
				
				$nTotalEarnings 	= round($nTotalEarnings, 2);
				$nTotalExpenses 	= round($nTotalExpenses, 2);
				$nTotalDifference 	= ( $nTotalEarnings > $nTotalExpenses ) ? $nTotalEarnings - $nTotalExpenses : $nTotalExpenses - $nTotalEarnings;
				$nTotalDifference 	= round($nTotalDifference, 2);
				
				$nTotalEarnings 	= sprintf("%01.2f лв.", $nTotalEarnings);
				$nTotalExpenses 	= sprintf("%01.2f лв.", $nTotalExpenses);
				$nTotalDifference 	= sprintf("%01.2f лв.", $nTotalDifference);
				
				$oResponse->setFormElement( "form1", "nTotalExpense", 	array( "value" => $nTotalExpenses ),   $nTotalExpenses );
				$oResponse->setFormElement( "form1", "nTotalEarning", 	array( "value" => $nTotalEarnings ),   $nTotalEarnings );
				$oResponse->setFormElement( "form1", "nTotalChange", 	array( "value" => $nTotalDifference ), $nTotalDifference );
				
				$oResponse->setFormElement( "form1", "nRefreshTotals", array( "value" => 0 ), 0 );
			}				
		}
		
		public function getReportMoneyNomenclatures( DBResponse $oResponse, $aParams )
		{
			global $db_name_personnel, $db_name_sod, $db_finance;
			
			//Initialize
			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] );
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
			//End Initialize
			
			//Pavel
			$cDDS			= isset($aParams['cDDS']) 		? $aParams['cDDS'] 		: 0;
			$cTransfer		= isset($aParams['cTransfer']) 	? $aParams['cTransfer'] : 0;
			//Pavel
			
			//Form Basic Query
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						ord.id,
						LPAD( ord.num, 10, 0 ) AS order_num,
						CASE ord.doc_type
							WHEN 'sale' THEN sd.client_name
							WHEN 'buy' THEN bd.client_name
						END AS client_name,
						DATE_FORMAT( ord.order_date, '%d.%m.%Y' ) AS order_date,
						IF
						(
							ord_row.is_dds = 1,
							'ДДС',
							CASE ord.doc_type
								WHEN 'sale' THEN nom_er.name
								WHEN 'buy' THEN nom_ex.name
							END
						) AS nomenclature_name,
						CASE ord.doc_type
							WHEN 'sale' THEN fir_sale.name
							WHEN 'buy' THEN fir_buy.name
						END AS firm,
						CASE ord.doc_type
							WHEN 'sale' THEN off_sale.name
							WHEN 'buy' THEN off_buy.name
						END AS office,
						IF
						(
							ord.doc_type = 'sale',
							ord_row.paid_sum,
							0
						) AS order_earning_sum,
						IF
						(
							ord.doc_type = 'buy',
							ord_row.paid_sum,
							0
						) AS order_expense_sum,
						
						CONCAT( ord_row.saldo_state, ' лв. [', sal.name, ']' ) as saldo,			
									
						LPAD(
							CASE ord.order_type
								WHEN 'earning' THEN sd.doc_num
								WHEN 'expense' THEN bd.doc_num
							END,
							10,
							0
						) AS order_doc_num,
						ord.note AS order_note,
						IF
						(
							ord.updated_user,
							CONCAT( CONCAT_WS( ' ', p.fname, p.mname, p.lname ), ' ' , DATE_FORMAT( ord.updated_time, '%d.%m.%Y %H:%i:%s' ) ),
							''
						) AS updated
					FROM
						<table> ord
					LEFT JOIN orders_rows_<yearmonth> ord_row ON ord_row.id_order = ord.id
					LEFT JOIN saldo sal ON sal.id = ord_row.id_saldo
					LEFT JOIN
						sales_docs_<yearmonth> sd ON ( sd.id = ord.doc_id AND ord.doc_type = 'sale' )
					LEFT JOIN
						{$db_name_sod}.offices off_sale ON ( off_sale.id = ord_row.id_office AND ord.doc_type = 'sale' )
					LEFT JOIN
						{$db_name_sod}.firms fir_sale ON ( fir_sale.id = off_sale.id_firm AND ord.doc_type = 'sale' )
					LEFT JOIN
						nomenclatures_services nom_serv ON ( nom_serv.id = ord_row.id_service AND ord.doc_type = 'sale' )
					LEFT JOIN
						nomenclatures_earnings nom_er ON ( nom_er.id = nom_serv.id_nomenclature_earning AND ord.doc_type = 'sale' )
					LEFT JOIN
						buy_docs_<yearmonth> bd ON ( bd.id = ord.doc_id AND ord.doc_type = 'buy' )
					LEFT JOIN
						{$db_name_sod}.offices off_buy ON ( off_buy.id = ord_row.id_office AND ord.doc_type = 'buy' )
					LEFT JOIN
						{$db_name_sod}.firms fir_buy ON ( fir_buy.id = off_buy.id_firm AND ord.doc_type = 'buy' )
					LEFT JOIN
						nomenclatures_expenses nom_ex ON ( nom_ex.id = ord_row.id_nomenclature_expense AND ord.doc_type = 'buy' )
					LEFT JOIN
						{$db_name_personnel}.personnel p ON p.id = ord.updated_user
					WHERE
						( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} )
			";
			//End Form Basic Query
			
			//Additional Filtering
//			if( isset( $aParams['sOrderType'] ) && !empty( $aParams['sOrderType'] ) )
//			{
//				$sQuery .= "
//						AND ord.order_type = '{$aParams['sOrderType']}'
//				";
//			}

			// Pavel 
			if ( isset($aParams['sOrderType']) && !empty($aParams['sOrderType']) ) {
				if ( $aParams['sOrderType'] == "earning" ) {
					$sQuery .= " AND ord.doc_type = 'sale' ";
					
					if ( !empty($cTransfer) ) {
						$sQuery .= " AND nom_serv.for_transfer = 0 ";
					}
				} elseif ( $aParams['sOrderType'] == "expense" ) {
					$sQuery .= " AND ord.doc_type = 'buy' ";
					
					if ( !empty($cTransfer) ) {
						$sQuery .= " AND nom_ex.for_transfer = 0 ";
					}					
				}
			} else {
				if ( !empty($cTransfer) ) {
					$sQuery .= " 
						AND nom_serv.for_transfer = 0 OR (nom_serv.for_transfer IS NULL AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} ))
						AND nom_ex.for_transfer = 0 OR (nom_ex.for_transfer IS NULL	AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} ))	
					";
				}
			}
			// Pavel							
			
			if( isset( $aParams['nIDObject'] ) && !empty( $aParams['nIDObject'] ) )
			{
				$sQuery .= "
						AND CASE ord.doc_type
							WHEN 'sale' THEN ord_row.id_object = {$aParams['nIDObject']}
							WHEN 'buy' THEN ord_row.id_object = {$aParams['nIDObject']}
						END
				";
			}

			if( isset( $aParams['sMonth'] ) && !empty( $aParams['sMonth'] ) )
			{
				$sQuery .= "
						AND CASE ord.doc_type
							WHEN 'sale' THEN ord_row.month LIKE '{$aParams['sMonth']}%'
							WHEN 'buy' THEN ord_row.month LIKE '{$aParams['sMonth']}%'
						END
				";
			}
			
			// Ако е посочена банкова сметка, или фирма ...
			if( ( isset( $aParams['nIDBankAccount'] ) 	&& !empty( $aParams['nIDBankAccount'] ) ) ||
				( isset( $aParams['nIDFirm'] ) 			&& !empty( $aParams['nIDFirm'] ) )			)
			{
				
				// ... добавям "AND ( ... )", който ще съдържа условия за банкова сметка, служител или фирма по опис на ордер.
				$sQuery .= "
						AND
						(
				";
			}
			
			// Отделна проверка, посочена ли е банкова сметка?
			if( isset( $aParams['nIDBankAccount'] ) && !empty( $aParams['nIDBankAccount'] ) )
			{
				// Ако е посочена, то се има предвид от филтъра.
				$sQuery .= "
							( ord.account_type = 'bank' AND ord.bank_account_id = {$aParams['nIDBankAccount']} )
				";
				
				// Предварителна проверка дали има посочена фирма. Ако има, се добавя OR, с цел комбиниран филтър от
				// банкова сметка И/ИЛИ служител.
				if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
				{
					$sQuery .= " OR ";
				}
			}
			
			// Същинска проверка дали има посочена фирма. Следва проверка с каква цел е посочена фирма.
			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
			{
				$sQuery .= "
							(
				";
				
				if( isset( $aParams['nIDPerson'] ) && !empty( $aParams['nIDPerson'] ) )
				{
					// Ако има зададена персонална сметка по фирмата и региона, то тя се има предвид от филтъра.
					$sQuery .= "
								( ord.account_type = 'person' AND ord.id_person = {$aParams['nIDPerson']} )
					";
				}
				else
				{
					// Ако няма посочена персонална сметка, то фирмата и региона са за описа на ордера.
					$sQuery .= "
								(
									CASE ord.doc_type
										WHEN 'sale' THEN fir_sale.id = {$aParams['nIDFirm']}
										WHEN 'buy' THEN fir_buy.id = {$aParams['nIDFirm']}
									END
								)
					";
					
					if( isset( $aParams['nIDOffice'] ) && !empty( $aParams['nIDOffice'] ) )
					{
						$sQuery .= "
								AND
								(
									CASE ord.doc_type
										WHEN 'sale' THEN off_sale.id = {$aParams['nIDOffice']}
										WHEN 'buy' THEN off_buy.id = {$aParams['nIDOffice']}
									END
								)
						";
					}
				}
				
				$sQuery .= "
							)
				";
			}
			
			// Затварям скобата от "AND ( ... )" за общото условие.
			if( ( isset( $aParams['nIDBankAccount'] ) 	&& !empty( $aParams['nIDBankAccount'] ) ) ||
				( isset( $aParams['nIDFirm'] ) 			&& !empty( $aParams['nIDFirm'] ) )			)
			{
				$sQuery .= "
						)
				";
			}
			
			if( isset( $aParams['nIDNomenclature'] ) && !empty( $aParams['nIDNomenclature'] ) )
			{
				if( $aParams['nIDNomenclature'] > 0 )
				{
					$sQuery .= "
						AND IF( ord.order_type = 'earning', nom_er.id = {$aParams['nIDNomenclature']}, 1 )
						AND IF( ord.order_type = 'expense', nom_ex.id = {$aParams['nIDNomenclature']}, 1 )
					";
				}
				else
				{
					$sQuery .= "
						AND ord_row.is_dds = 1
					";
				}
			}
			//End Additional Filtering
			
			
			// Pavel 
/*			if ( !empty($cTransfer) ) {
				if ( isset($aParams['sOrderType']) && !empty($aParams['sOrderType']) ) {
					if ( $aParams['sOrderType'] == "earning" ) {
						$sQuery .= " AND nom_serv.for_transfer = 0 ";
					} elseif ( $aParams['sOrderType'] == "expense" ) {
						$sQuery .= " AND nom_ex.for_transfer = 0 ";
					}
				} else {
					$sQuery .= " 
						AND nom_serv.for_transfer = 0  
						AND nom_ex.for_transfer = 0 				
					";
				}
				
				//OR (nom_serv.for_transfer IS NULL AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} ))
				//OR (nom_ex.for_transfer IS NULL AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} ))
			}*/
			
			if ( !empty($cDDS) ) {
				$sQuery .= " AND ord_row.is_dds = 0 ";
			}			
			// Pavel			
			
			$sOriginalQuery = $sQuery;
			
			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );
			
			$this->getResult( $sQuery, 'order_num', DBAPI_SORT_DESC, $oResponse );
			
			//Life-time Totals
			if( isset( $aParams['nRefreshTotals'] ) && !empty( $aParams['nRefreshTotals'] ) )
			{
				$aFullData = array();
				$nResult = $this->selectFromDB( $db_finance, $sOriginalQuery, $aFullData, $nTimeFrom, $nTimeTo );
				
				if( $nResult != DBAPI_ERR_SUCCESS )
				{
					throw new Exception( NULL, $nResult );
				}
				
				$nTotalEarnings = 0;
				$nTotalExpenses = 0;
				$nTotalDifference = 0;
				
				foreach( $aFullData as $aElement )
				{
					if( isset( $aElement['order_earning_sum'] ) )
					{
						$nTotalEarnings += $aElement['order_earning_sum'];
					}
					
					if( isset( $aElement['order_expense_sum'] ) )
					{
						$nTotalExpenses += $aElement['order_expense_sum'];
					}
				}
				
				$nTotalEarnings = round( $nTotalEarnings, 2 );
				$nTotalExpenses = round( $nTotalExpenses, 2 );
				
				$nTotalDifference = ( $nTotalEarnings > $nTotalExpenses ) ? $nTotalEarnings - $nTotalExpenses : $nTotalExpenses - $nTotalEarnings;
				
				$nTotalDifference = round($nTotalDifference, 2);
				
				$nTotalEarnings = sprintf("%01.2f лв.", $nTotalEarnings);
				$nTotalExpenses = sprintf("%01.2f лв.", $nTotalExpenses);
				$nTotalDifference = sprintf("%01.2f лв.", $nTotalDifference);
				
				//Totals
				$oResponse->setFormElement( "form1", "nTotalExpense", array( "value" => $nTotalExpenses ), $nTotalExpenses );
				$oResponse->setFormElement( "form1", "nTotalEarning", array( "value" => $nTotalEarnings ), $nTotalEarnings );
				$oResponse->setFormElement( "form1", "nTotalChange", array( "value" => $nTotalDifference ), $nTotalDifference );
				//End Totals
				
				$oResponse->setFormElement( "form1", "nRefreshTotals", array( "value" => 0 ), 0 );
			}
			//End Life-time Totals
			
			$oResponse->setField( 'order_num', 				'Номер на Орд.', 			'Сортирай по Номер на Ордера', 						NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'order_date', 			'Време',					'Сортирай по Време', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DATE ) );
			$oResponse->setField( 'client_name', 			'Име на Клиент', 			'Сортирай по Име на Клиент', 						NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'nomenclature_name', 		'Номенклатура',				'Сортирай по Номенклатура', 						NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'firm', 					'Фирма',					'Сортирай по Фирма', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'office', 				'Регион',					'Сортирай по Регион', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'order_earning_sum', 		'Приход по Ном.', 			'Сортирай по Приход по Номенклатурата от Ордера', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'order_expense_sum', 		'Разход по Ном.', 			'Сортирай по Разход по Номенклатурата от Ордера', 	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
			$oResponse->setField( 'saldo',			 		'Салдо', 					'Сортирай по Салдо', 								NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'order_doc_num', 			'Номер на Документ', 		'Сортирай по Номер на Документ', 					NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_ZEROLEADNUM ) );
			$oResponse->setField( 'order_note', 			'Описание', 				'Сортирай по Описание', 							NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'updated', 				'...', 						'Сортирай по Последна Редакция', "images/dots.gif" );
			
			$oResponse->setFieldLink( 'order_num', 'openOrder' );
		}
		
		public function getMax( $aParams ) {
			global $db_finance, $db_name_finance, $db_name_sod;

			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] );
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
			$nIDFirm		= isset($aParams['nIDFirm']) 	? $aParams['nIDFirm'] 	: 0;
			$nIDOffice  	= isset($aParams['nIDOffice']) 	? $aParams['nIDOffice'] : 0;
			$nIDObject  	= isset($aParams['nIDObject']) 	? $aParams['nIDObject'] : 0;

			$sSum			= array();	
			$aData			= array();	
			$nSumMin 		= 0;
			$nSumMax 		= 0;	
			$nSum			= 0;
			$buy			= 0;
			$sale			= 0;
			
			$sQuery = "
				SELECT
					GROUP_CONCAT(DISTINCT ord.account_sum ORDER BY ord.id ASC SEPARATOR ';') as sum,
					SUM(IF (ord.doc_type = 'buy', (ord_row.paid_sum * -1), ord_row.paid_sum)) as ssum,
					GROUP_CONCAT(DISTINCT IF (ord.doc_type = 'buy', (ord_row.paid_sum * -1), ord_row.paid_sum) ORDER BY ord.id, ord_row.id ASC SEPARATOR ';') as min_sum
				FROM {$db_name_finance}.orders_rows_<yearmonth> ord_row
				LEFT JOIN <table> ord ON ( ord.id = ord_row.id_order  )			
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = ord_row.id_office AND ord_row.id_office > 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND o.id_firm > 0 )				
				WHERE ord.order_sum != 0
					AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} )
					AND ord.order_status = 'active'
			";
			
			if ( !empty($nIDFirm) ) {
				$sQuery .= " AND f.id = {$nIDFirm} ";
			}

			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND ord_row.id_office = {$nIDOffice} ";
			}		

			if ( !empty($nIDObject) ) {
				$sQuery .= " AND ord_row.id_object = {$nIDObject} ";
			}			

			$sQuery .= " GROUP BY ord.bank_account_id ";
			
			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nFullTimeTo );

			$sSum = $this->select2($sQuery);
			
			foreach ( $sSum as $key ) {
				$sKey = isset($key['sum']) 		? $key['sum'] 		: "";
				$bank = isset($key['id_bank']) 	? $key['id_bank'] 	: "";
				$nSum += isset($key['ssum']) 	? $key['ssum'] 		: 0;
				$buy  += isset($key['buy']) 	? $key['buy'] 		: 0;
				$sale += isset($key['sale']) 	? $key['sale'] 		: 0;
				
				$aData = explode(";", $sKey);

				if ( count($aData) > 0 ) {
					$nSumMin += $aData[0];
					$nSumMax += $aData[count($aData) -1];
					
				}
			}
			
			return array("min" => $nSumMin, "max" => $nSumMax, "sum" => $nSum, "buy" => $buy, "sale" => $sale);
		}
		
		// основна редакция - Павел
		public function getReportMoneyNomenclaturesOverview( DBResponse $oResponse, $aParams ) {
			global $db_finance, $db_name_finance, $db_name_personnel, $db_name_sod;
			
			$nTimeFrom		= jsDateToTimestamp ( $aParams['sFromDate'] );
			$nTimeTo		= jsDateToTimestamp ( $aParams['sToDate'] ); 
			$nFullTimeTo 	= $this->jsDateEndToTimestamp( $aParams['sToDate'] );
			$nIDFirm		= isset($aParams['nIDFirm']) 	? $aParams['nIDFirm'] 	: 0;
			$nIDOffice  	= isset($aParams['nIDOffice']) 	? $aParams['nIDOffice'] : 0;
			$nIDObject  	= isset($aParams['nIDObject']) 	? $aParams['nIDObject'] : 0;
			$sMonth  		= isset($aParams['sMonth']) 	? $aParams['sMonth'] 	: date("Y-m")."-01";
			$nRowCount		= 0;
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						IF ( ord_row.is_dds > 0, -1, IF (ord.doc_type = 'sale', IF (nom_er.id > 0, CONCAT('111', nom_er.id), -3), IF (nom_ex.id > 0, CONCAT('222', nom_ex.id), -3)) ) AS id,
						IF ( ord_row.is_dds > 0, 'ДДС', IF (ord.doc_type = 'sale', IF (nom_er.id > 0, nom_er.name, 'Невъведена'), IF (nom_ex.id > 0, nom_ex.name, 'Невъведена')) ) AS nomenclature_name,
						IF ( ord.doc_type = 'sale', 'Приход', 'Разход' ) AS order_type,
						ord.doc_type,
						
						IF ( ord.order_date = MIN( ord.order_date ), SUM( ord.account_sum ), 0 ) AS order_start_balance,
						IF ( ord.order_date = MAX( ord.order_date ), SUM( ord.account_sum ), 0 ) AS order_end_balance,
						
						SUM( IF (ord.doc_type = 'sale', ord_row.paid_sum, 0) ) AS order_earning,
						SUM( IF (ord.doc_type = 'buy', ord_row.paid_sum, 0) ) AS order_expense,
						SUM( ord_row.paid_sum ) AS order_diff
					FROM {$db_name_finance}.orders_rows_<yearmonth> ord_row
					LEFT JOIN <table> ord ON ( ord.id = ord_row.id_order  )						
					LEFT JOIN {$db_name_finance}.nomenclatures_services nom_serv ON ( nom_serv.id = ord_row.id_service AND ord_row.id_service > 0 )
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings nom_er ON ( nom_er.id = nom_serv.id_nomenclature_earning AND nom_serv.id_nomenclature_earning > 0 )
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses nom_ex ON ( nom_ex.id = ord_row.id_nomenclature_expense AND ord_row.id_nomenclature_expense > 0 )
					LEFT JOIN {$db_name_sod}.offices o ON ( o.id = ord_row.id_office AND ord_row.id_office > 0 )
					LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND o.id_firm > 0 )
					
					WHERE 
						ord_row.paid_sum != 0
						AND ( UNIX_TIMESTAMP( ord.order_date ) >= {$nTimeFrom} AND UNIX_TIMESTAMP( ord.order_date ) <= {$nFullTimeTo} )
						AND ord.order_status = 'active'
			";
			
			if ( !empty($nIDFirm) ) {
				$sQuery .= " AND f.id = {$nIDFirm} ";
			}

			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND ord_row.id_office = {$nIDOffice} ";
			}			

			
			if ( !empty($nIDObject) ) {
				$sQuery .= " AND ord_row.id_object = {$nIDObject} ";
			}
			
			if ( !empty($sMonth) ) {
				$sQuery .= " AND ord_row.month LIKE '{$sMonth}%' ";
			}
			
			$sQuery .= "
				GROUP BY id
			";
			
			$sOriginalQuery = $sQuery;
			
			$this->makeUnionSelect( $sQuery, $nTimeFrom, $nTimeTo );

			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$_SESSION['userdata']['row_limit'] 	= 1000;

			$this->getResult( $sQuery, 'id', DBAPI_SORT_DESC, $oResponse );
			
			$_SESSION['userdata']['row_limit'] 	= $nRowCount;
			
			$nTotalEarning 		= 0;
			$nTotalExpense 		= 0;
			$nTotalDifference 	= 0;
			$nTotalStartBalance = 0;
			$nTotalEndBalance 	= 0;
							
			$blah 				= $this->getMax($aParams);
			
			sort($oResponse->oResult->aData);
			reset($oResponse->oResult->aData);
			
			foreach ( $oResponse->oResult->aData as $nKey => &$aValue ) {
				$nTotalEarning += $aValue['order_earning'];
				$nTotalExpense += $aValue['order_expense'];
				
				$sType			= $aValue['doc_type'];
				$sPack			= "";
				
				$aPack			= array();
				$aPack['id']	= $aValue['id'];
				$aPack['type']	= $sType;
				
				$sPack			= implode("***", $aPack);
				
				if ( $aValue['id'] < 100 ) {
					$aValue['id'] = "";
				} else {
					$aValue['id'] = substr($aValue['id'], 3, strlen($aValue['id']) -1);
				}				
				
				$oResponse->setDataAttributes($nKey, 	"id", 					array("style" => "text-align: right;") );
				$oResponse->setDataAttributes($nKey, 	"nomenclature_name", 	array("onclick" => "getDetail('{$sPack}')", "style" => "cursor: pointer; padding-right: 10px;") );	
				
				if ( ($sType == "sale") && ($aValue['id'] > 1) ) {
					$oResponse->setRowAttributes( $aValue['id'], array("style" => "color: green;") );
				} elseif ( ($sType == "buy") && ($aValue['id'] > 1) ) {
					$oResponse->setRowAttributes( $aValue['id'], array("style" => "color: FF3C3C;") );				
				}
			}
			
			
			
			$nTotalEarning 		= sprintf("%01.2f лв.", $nTotalEarning);
			$nTotalExpense 		= sprintf("%01.2f лв.", $nTotalExpense);			
			$nTotalStartBalance = isset($blah['min']) ? sprintf("%01.2f лв.", $blah['min']) : "0.00";
			$nTotalEndBalance 	= isset($blah['max']) ? sprintf("%01.2f лв.", $blah['max']) : "0.00";
			$nTotalDifference 	= isset($blah['sum']) ? sprintf("%01.2f лв.", $blah['sum']) : "0.00";

			
			$oResponse->setFormElement("form1", "nTotalEarning", 		array( "value" => $nTotalEarning ), 	$nTotalEarning );
			$oResponse->setFormElement("form1", "nTotalExpense", 		array( "value" => $nTotalExpense ), 	$nTotalExpense );			
			$oResponse->setFormElement("form1", "nTotalChange", 		array( "value" => $nTotalDifference ), 	$nTotalDifference );
			$oResponse->setFormElement("form1", "nTotalStartBalance", 	array( "value" => $nTotalStartBalance ), $nTotalStartBalance);
			$oResponse->setFormElement("form1", "nTotalEndBalance", 	array( "value" => $nTotalEndBalance ), 	$nTotalEndBalance );
			
			$oResponse->setField("id", 					"№",				"Сортирай по Номер", 								NULL, NULL, NULL, NULL );
			$oResponse->setField("nomenclature_name", 	"Номенклатура",		"Сортирай по Номенклатура", 						NULL, NULL, NULL, NULL );
			$oResponse->setField("order_type",	 		"Тип", 				"Сортирай по Тип", 									NULL, NULL, NULL, array("DATA_FORMAT" => DF_STRING)   );
			$oResponse->setField("order_earning", 		"Приход по Ном.", 	"Сортирай по Приход по Номенклатурата от Ордера", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			$oResponse->setField("order_expense", 		"Разход по Ном.", 	"Сортирай по Разход по Номенклатурата от Ордера", 	NULL, NULL, NULL, array("DATA_FORMAT" => DF_CURRENCY) );
			
			$oResponse->setFieldLink( 'order_num', 'openOrder' );
		}
		
		public function getReportInventory( DBResponse $oResponse, $nIDOrder, $sDocType ) {
			global $db_name_sod, $db_name_finance;
			
			if ( $this->isValidID($nIDOrder) ) {
				$sYearMonth = substr( $nIDOrder, 0, 6 );
				$sTableOrders 	= PREFIX_ORDERS.$sYearMonth;
				$sTableOrdRows	= PREFIX_ORDERS_ROWS.$sYearMonth;
				$sTableSaleRows	= PREFIX_SALES_DOCS_ROWS.$sYearMonth;
				$sTableBuyRows	= PREFIX_BUY_DOCS_ROWS.$sYearMonth;
			} else {
				throw new Exception( "Невалиндно ID!" );
			}
			
			$oOrder = new DBOrders();
			$aOrder	= array();
			$sType 	= "sale";

			$oOrder->getRecord($nIDOrder, $aOrder);
			
			$sType 	= isset($aOrder['doc_type']) && !empty($aOrder['doc_type']) ? $aOrder['doc_type'] : "sale";
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ord_row.id AS id,
					fir.name AS firm,
					off.name AS office,
					obj.name AS object,
					DATE_FORMAT( ord_row.month, '%m.%Y' ) AS month,
			";

			if ( $sType == "sale" ) {
				$sQuery .= "
					IF( ord_row.is_dds = 0, nom_ser.name, 'ДДС' ) AS service_name,	 
					IF( ord_row.is_dds = 0, nom_ser.code, '' ) AS service_code,		
					sale_row.total_sum AS doc_sum,
				";
			} else {
				$sQuery .= "
					IF( ord_row.is_dds = 0, nom_exp.name, 'ДДС' ) AS expense_name,  
					buy_row.total_sum AS doc_sum,
				";				
			}

			$sQuery .= "
					ord_row.paid_sum AS order_sum
				FROM {$db_name_finance}.{$sTableOrdRows} as ord_row
				LEFT JOIN {$db_name_finance}.{$sTableOrders} as ord ON ord.id = ord_row.id_order
				LEFT JOIN {$db_name_sod}.objects obj ON obj.id = ord_row.id_object
				LEFT JOIN {$db_name_sod}.offices off ON off.id = ord_row.id_office
				LEFT JOIN {$db_name_sod}.firms fir ON fir.id = off.id_firm				
			";
			
			if ( $sType == "sale" ) {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.{$sTableSaleRows} as sale_row ON ( sale_row.id = ord_row.id_doc_row AND ord.doc_type = 'sale' )
					LEFT JOIN {$db_name_finance}.nomenclatures_services nom_ser ON ( nom_ser.id = ord_row.id_service AND ord.doc_type = 'sale' )
				";
			} else {
				$sQuery .= "
					LEFT JOIN {$db_name_finance}.{$sTableBuyRows} as buy_row ON ( buy_row.id = ord_row.id_doc_row AND ord.doc_type = 'buy' )
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses nom_exp ON ( nom_exp.id = ord_row.id_nomenclature_expense AND ord.doc_type = 'buy' )
				";				
			}
				
			$sQuery .= "
				WHERE ord.id = {$nIDOrder}
			";
			
			$this->getResult( $sQuery, "id", DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( 'firm',			'Фирма',			'Сортирай по фирма',			NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'office',			'Офис',				'Сортирай по офис',				NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'object',			'Обект',			'Сортирай по обект',			NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			$oResponse->setField( 'month',			'Месец',			'Сортирай по месец',			NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			
			if ( $sType == "sale" ) {
				$oResponse->setField( 'service_code',	'Код на услугата',	'Сортирай по код на услугата',	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
				$oResponse->setField( 'service_name',	'Име на услуга',	'Сортирай по име на услуга',	NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			} else {
				$oResponse->setField( 'expense_name',	'Разход',			'Сортирай по разход',			NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_STRING ) );
			}
			
			$oResponse->setField( 'doc_sum',		'Сума по Документ',	'Сортирай по сума',				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( 'order_sum',		'Сума по Ордер',	'Сортирай по сума',				NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
		}
		
		public function payInventorySaleByOrder( DBResponse $oResponse, $nIDSaleDoc )
		{
			global $db_finance;
			
			$oSalesDocsRows = new DBSalesDocsRows();
			
			if( $oSalesDocsRows->isValidID( $nIDSaleDoc ) )
			{
				$sYearMonth = substr( $nIDSaleDoc, 0, 6 );
			}
			else
			{
				throw new Exception( "Невалиндно ID!" );
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sQuery = "
					SELECT
						sdr.id,
						ABS( sdr.total_sum ) AS total_sum,
						ABS( sd.total_sum ) AS total_doc_sum,
						ABS( sd.orders_sum ) AS orders_sum
					FROM
						" . PREFIX_SALES_DOCS_ROWS . $sYearMonth . " sdr
					LEFT JOIN
						" . PREFIX_SALES_DOCS . $sYearMonth . " sd ON sd.id = {$nIDSaleDoc}
					WHERE
						sdr.id_sale_doc = {$nIDSaleDoc}
			";
			
			$aData = array();
			$oResult = $db_finance->Execute( $sQuery );
			
			$aData = $oResult->getArray();
			
			foreach( $aData as $value )
			{
				$aUpdateData = array();
				
				$nPercentage = ( $value['orders_sum'] / $value['total_doc_sum'] ) * 100;
				
				$aUpdateData['id'] = $value['id'];
				$aUpdateData['paid_sum'] = ( $value['total_sum'] * $nPercentage ) / 100;
				$aUpdateData['paid_date'] = date( "Y-m-d H:i:s" );
				
				$oSalesDocsRows->update( $aUpdateData );
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function payInventoryBuyByOrder( DBResponse $oResponse, $nIDBuyDoc )
		{
			global $db_finance;
			
			$oBuyDocsRows = new DBBuyDocsRows();
			
			if( $oBuyDocsRows->isValidID( $nIDBuyDoc ) )
			{
				$sYearMonth = substr( $nIDBuyDoc, 0, 6 );
			}
			else
			{
				throw new Exception( "Невалиндно ID!" );
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sQuery = "
					SELECT
						bdr.id,
						ABS( bdr.total_sum ) AS total_sum,
						ABS( bd.total_sum ) AS total_doc_sum,
						ABS( bd.orders_sum ) AS orders_sum
					FROM
						" . PREFIX_BUY_DOCS_ROWS . $sYearMonth . " bdr
					LEFT JOIN
						" . PREFIX_BUY_DOCS . $sYearMonth . " bd ON bd.id = {$nIDBuyDoc}
					WHERE
						bdr.id_buy_doc = {$nIDBuyDoc}
			";
			
			$aData = array();
			$oResult = $db_finance->Execute( $sQuery );
			
			$aData = $oResult->getArray();
			
			foreach( $aData as $value )
			{
				$aUpdateData = array();
				
				$nPercentage = ( $value['orders_sum'] / $value['total_doc_sum'] ) * 100;
				
				$aUpdateData['id'] = $value['id'];
				$aUpdateData['paid_sum'] = ( $value['total_sum'] * $nPercentage ) / 100;
				$aUpdateData['paid_date'] = date( "Y-m-d H:i:s" );
				
				$oBuyDocsRows->update( $aUpdateData );
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function getReportFirmBalances( DBResponse $oResponse, $nIDOrder, $nIDDoc = 0, $sDocType = "" )
		{
			global $db_name_sod;
			
			$sWhereType = "";
			//Validation
			if( $this->isValidID( $nIDOrder ) )
			{
				$sYearMonth = substr( $nIDOrder, 0, 6 );
				$sWhereType = "Order";
			}
			else if( $this->isValidID( $nIDDoc ) )
			{
				$sYearMonth = substr( $nIDDoc, 0, 6 );
				$sWhereType = "Doc";
			}
			else
			{
				throw new Exception( "Невалиндно ID!" );
				return DBAPI_ERR_INVALID_PARAM;
			}
			//End Validation
			
			if( $sWhereType == "Order" )
			{
				$sQuery = "
				SELECT
					CASE ord.doc_type
						WHEN 'buy' THEN sal_buy.id
						WHEN 'sale' THEN sal_sale.id
					END AS id_balance,
					CASE ord.doc_type
						WHEN 'buy' THEN fir_buy.name
						WHEN 'sale' THEN fir_sale.name
					END AS name_firm,
					CASE ord.doc_type
						WHEN 'buy' THEN sal_buy.name
						WHEN 'sale' THEN sal_sale.name
					END AS name_balance,
					CASE ord.doc_type
						WHEN 'buy' THEN sal_buy.sum
						WHEN 'sale' THEN sal_sale.sum
					END AS balance_firm,
					CASE ord.doc_type
						WHEN 'buy' THEN sal_buy.is_dds
						WHEN 'sale' THEN sal_sale.is_dds
					END AS balance_dds
				FROM
					" . PREFIX_ORDERS . $sYearMonth . " ord
				LEFT JOIN
					" . PREFIX_BUY_DOCS_ROWS . $sYearMonth . " bdr ON ( bdr.id_buy_doc = ord.doc_id AND ord.doc_type = 'buy' )
				LEFT JOIN
					" . PREFIX_SALES_DOCS_ROWS . $sYearMonth . " sdr ON ( sdr.id_sale_doc = ord.doc_id AND ord.doc_type = 'sale' )
				LEFT JOIN
					{$db_name_sod}.offices off_buy ON ( off_buy.id = bdr.id_office AND ord.doc_type = 'buy' )
				LEFT JOIN
					{$db_name_sod}.offices off_sale ON ( off_sale.id = sdr.id_office AND ord.doc_type = 'sale' )
				LEFT JOIN
					{$db_name_sod}.firms fir_buy ON ( fir_buy.id = off_buy.id_firm AND ord.doc_type = 'buy' )
				LEFT JOIN
					{$db_name_sod}.firms fir_sale ON ( fir_sale.id = off_sale.id_firm AND ord.doc_type = 'sale' )
				LEFT JOIN
					saldo sal_buy ON ( sal_buy.id_firm = fir_buy.id AND ord.doc_type = 'buy' )
				LEFT JOIN
					saldo sal_sale ON ( sal_sale.id_firm = fir_sale.id AND ord.doc_type = 'sale' )
				WHERE
					ord.id = {$nIDOrder}
				";
			}
			if( $sWhereType == "Doc" )
			{
				switch( $sDocType )
				{
					case "buy":
						$sQuery = "
							SELECT
								sal_buy.id AS id_balance,
								fir_buy.name AS name_firm,
								sal_buy.name AS name_balance,
								sal_buy.sum AS balance_firm,
								sal_buy.is_dds AS balance_dds
							FROM
								" . PREFIX_BUY_DOCS_ROWS . $sYearMonth . " bdr
							LEFT JOIN
								{$db_name_sod}.offices off_buy ON off_buy.id = bdr.id_office
							LEFT JOIN
								{$db_name_sod}.firms fir_buy ON fir_buy.id = off_buy.id_firm
							LEFT JOIN
								saldo sal_buy ON sal_buy.id_firm = fir_buy.id
							WHERE
								bdr.id_buy_doc = {$nIDDoc}
						";
						break;
					
					case "sale":
						$sQuery = "
							SELECT
								sal_sale.id AS id_balance,
								fir_sale.name AS name_firm,
								sal_sale.name AS name_balance,
								sal_sale.sum AS balance_firm,
								sal_sale.is_dds AS balance_dds
							FROM
								" . PREFIX_SALES_DOCS_ROWS . $sYearMonth . " sdr
							LEFT JOIN
								{$db_name_sod}.offices off_sale ON off_sale.id = sdr.id_office
							LEFT JOIN
								{$db_name_sod}.firms fir_sale ON fir_sale.id = off_sale.id_firm
							LEFT JOIN
								saldo sal_sale ON sal_sale.id_firm = fir_sale.id
							WHERE
								sdr.id_sale_doc = {$nIDDoc}
						";
						break;
				}
			}
			
			$sQuery .= "
				GROUP BY
					id_balance
			";
			
			$this->getResult( $sQuery, "name_firm", SORT_ASC, $oResponse );
			
			$oResponse->setField( "name_firm", 		"Фирма", 		"Сортирай по Фирма", 		NULL, 					NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "name_balance", 	"Наименование", "Сортирай по Наименование", NULL, 					NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "balance_firm", 	"Салдо", 		"Сортирай по Салдо", 		NULL, 					NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "balance_dds", 	"ДДС", 			"Сортирай", 				"images/confirm.gif", 	NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
		}
		
		
		
		public function annulment( DBResponse $oResponse, $nID ) {
			global $db_name_system, $db_name_finance, $db_system, $db_finance;
			
			if ( !$this->isValidID($nID) ) {
				throw new Exception("Невалиден документ!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			$nIDUser	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oOrders 	= new DBOrders();
			$oOrderRow	= new DBOrdersRows();
			$oSaldo		= new DBSaldo();
			$oFirms		= new DBFirms();
			$oSync		= new DBSyncMoney();
			$oSales		= new DBSalesDocsRows();
			$oBuys		= new DBBuyDocsRows();
			
			$aOrder 	= array();
			$aOrderRow	= array();
			$aSalesDoc	= array();
			
			
			$db_finance->StartTrans();
			$db_system->StartTrans();	
						
			if ( !empty($nID) ) {
				try {
					$nResult = $oOrders->getRecord( $nID, $aOrder );

					if(  $nResult != DBAPI_ERR_SUCCESS ) {
						throw new Exception("Грешка при изпълнение на операцията!", $nResult);
					}
					
					$nIDAccount 	= isset($aOrder['bank_account_id']) ? $aOrder['bank_account_id'] : 0;
					$nSum 			= isset($aOrder['order_sum']) 		? $aOrder['order_sum'] 		 : 0;
					$account_type 	= isset($aOrder['account_type']) 	? $aOrder['account_type'] 	 : "cash";
					$doc_id 		= isset($aOrder['doc_id']) 			? $aOrder['doc_id'] 		 : 0;
					$doc_type 		= isset($aOrder['doc_type']) 		? $aOrder['doc_type'] 	 	 : "sale";
					$order_status	= isset($aOrder['order_status']) 	? $aOrder['order_status'] 	 : "active";
					$doc_num		= isset($aOrder['num']) 			? $aOrder['num'] 	 	 	 : 0;
					$order_type 	= $aOrder['order_type'] == "earning" ? "expense"				 : "earning";
					$paid_sum		= 0;
					$sSufixTableDoc	= "";
					$sTableDoc		= "";
					$nAccState		= 0;
					$nAccountState	= 0;
					
					if ( $order_status != "active" ) {
						throw new Exception("Ордера не подлежи на промяна!!!", DBAPI_ERR_INVALID_PARAM);
					}
					
					if ( empty($nIDAccount) ) {
						throw new Exception("Банковата сметка не може да бъде намерена!!!", DBAPI_ERR_INVALID_PARAM);
					}
					
					$aOrderRow = $oOrderRow->getByIDOrder($nID);
					
					// Следващ номер за ордер
					$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
					$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;
					
					// НАЧАЛНА наличност по сметка
					$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
					$nAccState	 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;					

					if ( !$this->isValidID($doc_id) ) {
						throw new Exception("Невалиден документ!", DBAPI_ERR_INVALID_PARAM);
					} else {
						$sSufixTableDoc	= substr($doc_id, 0, 6);
					}	
			
					if ( $doc_type == "buy" ) {
						$sTableDoc	= PREFIX_BUY_DOCS.$sSufixTableDoc;
						
						if ( $order_type == "earning" ) {
							$paid_sum = $nAccState + $nSum;
							//$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSum}' WHERE id_bank_account = {$nIDAccount} LIMIT 1");
							$db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum - '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
						} else {
							$paid_sum = $nAccState - $nSum;
							//$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSum}' WHERE id_bank_account = {$nIDAccount} LIMIT 1");
							$db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum + '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
						}
					} else {
						$sTableDoc	= PREFIX_SALES_DOCS.$sSufixTableDoc;
						
						if ( $order_type == "earning" ) {
							$paid_sum = $nAccState + $nSum;
							//$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSum}' WHERE id_bank_account = {$nIDAccount} LIMIT 1");
							$db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum + '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
						} else {
							$paid_sum = $nAccState - $nSum;
							//$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSum}' WHERE id_bank_account = {$nIDAccount} LIMIT 1");
							$db_finance->Execute("UPDATE {$db_name_finance}.$sTableDoc SET orders_sum = orders_sum - '{$nSum}' WHERE id = {$doc_id} LIMIT 1");
						}						
					}
					
					$aDataOrder						= array();
					$aDataOrder['id']				= $nID;					
					$aDataOrder['order_status']		= "canceled";
					
					$oOrders->update($aDataOrder);					
					
					$aDataOrder						= array();
					$aDataOrder['id']				= 0;
					$aDataOrder['num']				= $nLastOrder;
					$aDataOrder['order_type'] 		= $order_type;
					$aDataOrder['order_status']		= "opposite";
					$aDataOrder['id_transfer']		= 0;
					$aDataOrder['order_date']		= time();
					$aDataOrder['order_sum']		= $nSum;	
					$aDataOrder['account_type']		= $account_type;
					$aDataOrder['id_person']		= $nIDUser;
					$aDataOrder['account_sum']		= $paid_sum;
					$aDataOrder['bank_account_id']	= $nIDAccount;
					$aDataOrder['doc_id']			= $doc_id;
					$aDataOrder['doc_type']			= $doc_type;
					$aDataOrder['note']				= "Анулиране на номер ".$doc_num;
					$aDataOrder['created_user']		= $nIDUser;
					$aDataOrder['created_time']		= time();
					$aDataOrder['updated_user']		= $nIDUser;
					$aDataOrder['updated_time']		= time();
		
					$oOrders->update($aDataOrder);
					
					$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");	
					
					
					$nIDOrder = $aDataOrder['id'];	
					
//					foreach ( $aOrderRow as $val ) {
//						$nIDRow	= isset($val['id_doc_row']) ? $val['id_doc_row'] : 0;
//					}
					
					foreach ( $aOrderRow as $val ) {
						$nIDSaldo	= $val['id_saldo'];
						$nIDFirm 	= $oFirms->getFirmByOffice($val['id_office']);
						$isDDS		= isset($val['is_dds']) 	? $val['is_dds'] 	: 0;
						$nSumRow	= isset($val['paid_sum']) 	? $val['paid_sum'] 	: 0;
						$nIDRow		= isset($val['id_doc_row']) ? $val['id_doc_row'] : 0;
						$aDocRow	= array();
						$nIDTran	= 0;
						$state		= 0;

						if ( $doc_type == "sale" ) {
							$nIDTran	= $oSales->checkForTransfer($doc_id);
							$aDocRow 	= $oSales->getByIDRow($nIDRow);
							$tRow		= PREFIX_SALES_DOCS_ROWS.substr($nIDRow, 0, 6);					
						} else {
							$nIDTran	= $oBuys->checkForTransfer($doc_id);
							$aDocRow 	= $oBuys->getByIDRow($nIDRow);
							$tRow		= PREFIX_BUY_DOCS_ROWS.substr($nIDRow, 0, 6);
						}
						
										
						
						$nIDSchet 		= isset($aDocRow['id_schet_row']) ? $aDocRow['id_schet_row'] : 0;
						$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, $isDDS);
						$nIDSaldo		= 0;
						$nCurrentSaldo	= 0;
						$nAccountState	= 0;		
	
						if ( !empty($aSaldo) ) {
							$nIDSaldo 	= $aSaldo['id'];
						}
								
						// Салдо на фирмата с изчакване!!!
						if ( !empty($nIDSaldo) ) {
							$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
					    	$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
						} else {
							throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
						}									

						// Наличност по сметка
						if ( !empty($nIDAccount) ) {
							$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
							$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
						} else {
							throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
						}			
													
						if ( $doc_type == "buy" ) {
							$state = sprintf("%01.2f", $nAccountState + $nSumRow);
							
							if ( empty($nIDTran) ) {
								$saldo = sprintf("%01.2f", $nCurrentSaldo + $nSumRow);
								$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nSumRow}' WHERE id = {$nIDSaldo} LIMIT 1");	
							} else {
								$saldo = sprintf("%01.2f", $nCurrentSaldo);
							}
							
							$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSumRow}' WHERE id_bank_account = {$nIDAccount} ");	
							$db_finance->Execute("UPDATE {$db_name_finance}.{$tRow} SET paid_sum = paid_sum - '{$nSumRow}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = {$nIDRow} LIMIT 1");
						} else {
							$state = sprintf("%01.2f", $nAccountState - $nSumRow);
							
							if ( empty($nIDTran) ) {
								$saldo = sprintf("%01.2f", $nCurrentSaldo - $nSumRow);
								$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSumRow}' WHERE id = {$nIDSaldo} LIMIT 1");	
							} else {
								$saldo = sprintf("%01.2f", $nCurrentSaldo);
							}
								
							$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSumRow}' WHERE id_bank_account = {$nIDAccount} ");
							$db_finance->Execute("UPDATE {$db_name_finance}.{$tRow} SET paid_sum = paid_sum - '{$nSumRow}', updated_user = {$nIDUser}, updated_time = NOW() WHERE id = {$nIDRow} LIMIT 1");					
						}						
							
						if ( $saldo < 0 ) {
							throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
						}

						if ( $state < 0 ) {
							throw new Exception("Недостатъчна наличност по сметка!", DBAPI_ERR_INVALID_PARAM);
						}						
						
						$val['id']				= 0;
						$val['id_order'] 		= $nIDOrder;
						$val['saldo_state'] 	= $saldo;
						$val['account_state'] 	= $state;
						$val['paid_sum'] 		= $nSumRow * -1;
						
						$oOrderRow->update($val); 
						
						// Schet
						if ( !empty($nIDSchet) ) {
							$oSync->invalidate($nIDSchet);
						}					
					}

					$db_finance->CompleteTrans();
					$db_system->CompleteTrans();					
				} catch (Exception $e) {
					$sMessage = $e->getMessage();
					
					$db_finance->FailTrans();
					$db_system->FailTrans();
					
					throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
				}
			}
			
			$oResponse->printResponse();
		}						
	}

?>
