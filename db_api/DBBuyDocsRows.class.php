<?php

	class DBBuyDocsRows extends DBMonthTable {
		
		function __construct() {
			global $db_finance,$db_name_finance;
			
			parent::__construct($db_name_finance,PREFIX_BUY_DOCS_ROWS,$db_finance);
		}
		
		public function sumSum($nIDBuyDoc) {
			
			if($this->isValidID($nIDBuyDoc)) {
				$sYearMonth = substr($nIDBuyDoc,0,6);
			} else {
				throw new Exception("Невалиндно id");
			}
			
			$sQuery = "
				SELECT
					SUM(total_sum) AS sum_sum
				FROM ".PREFIX_BUY_DOCS_ROWS.$sYearMonth." 
				WHERE id_buy_doc = {$nIDBuyDoc}
				GROUP BY id_buy_doc
			";
			
			return $this->selectOne2($sQuery);
		}
		
		public function insertSalary($aParams) {
			
			$nIDFirm = $aParams['nIDFirm'];
			$nIDOffice = $aParams['nIDOffice'];
			$nIDObject = $aParams['nIDObject'];
			$nIDPerson = $aParams['nIDPerson'];
			$sMonth = $aParams['sMonth'];	
					
			$nIDBuyDoc = $aParams['id_buy_doc'];
			$sTableName = PREFIX_BUY_DOCS_ROWS.substr($nIDBuyDoc,0,6);
			
			$nIDUpdatedPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$sWebServerTime = date("Y-m-d H:i:s"); 
			
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$nIDNomenclatureExpense = $oDBNomenclaturesExpenses->getIDSalaryNomenclature();
			
			if(empty($nIDNomenclatureExpense)) {
				throw new Exception("Няма избрана номенклатура разход за заплати от менюто НОМЕНКЛАТУРИ->ФИНАНСИ->НОМЕНКЛАТУРИ РАЗХОДИ");
			}
			
			global $db_name_personnel,$db_name_sod;
			
			$sQuery = "
				INSERT INTO {$sTableName}
					(
						id_buy_doc,
						id_office,
						id_object,
						id_person,
						id_nomenclature_expense,
						id_salary_row,
						month,
						quantity,
						measure,
						single_price,
						total_sum,
						updated_user,
						updated_time
					)
				SELECT 
					{$nIDBuyDoc},
					s.id_office,
 					IF(s.id_object_duty != 0,s.id_object_duty,s.id_object) AS id_object,
 					s.id_person,
 					{$nIDNomenclatureExpense},
 					s.id,
 					CONCAT(SUBSTRING(s.month,1,4),'-',SUBSTRING(s.month,5,2),'-01'),
 					s.count,
 					'бр.',
 					IF(s.is_earning > 0, (s.total_sum - s.paid_sum)/s.count, -(s.total_sum - s.paid_sum)/s.count),
 					IF(s.is_earning > 0, (s.total_sum - s.paid_sum), -(s.total_sum - s.paid_sum)),
 					{$nIDUpdatedPerson},
 					'{$sWebServerTime}'
 				FROM {$db_name_personnel}.salary s
 				LEFT JOIN {$db_name_sod}.offices off ON off.id = s.id_office
 				WHERE LENGTH(s.month) = 6
 					AND s.to_arc = 0
 					AND s.paid_sum < s.total_sum
			";
			
			if(!empty($sMonth)) {
				$sQuery .= " AND s.month = '{$sMonth}'\n";
			}
			
			if(!empty($nIDPerson)) {
				$sQuery .= " AND s.id_person = {$nIDPerson}\n";
			}
			
			if(!empty($nIDObject)) {
				$sQuery .= " AND (s.id_object_duty = {$nIDObject} OR s.id_object = {$nIDObject})\n";
			}
			
			if(!empty($nIDOffice)) {
				$sQuery .= " AND s.id_office = {$nIDOffice}\n";
			}
			
			if(!empty($nIDFirm)) {
				$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
			}
			
			if(!empty($nIDObject)) {
				$sQuery .= " HAVING id_object = {$nIDObject}\n";
			}
			
			$this->_oDB->Execute($sQuery);
		}
		
		public function getRowsByIDBuyDoc($nIDBuyDoc) {
			
			if($this->isValidID($nIDBuyDoc)) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr($nIDBuyDoc,0,6);
			} else {
				throw new Exception("Невалидно id");
			}
			
			$sQuery = "
				SELECT
					*
				FROM {$sTableName} 
				WHERE id_buy_doc = {$nIDBuyDoc}
			";
			
			return $this->select2($sQuery);
			
		}
		
		public function insertSalaryGSM($aParams) {
			
			$nIDBuyDoc = $aParams['id_buy_doc'];
			$sMonth = $aParams['sMonth'];	
					
			$sTableName = PREFIX_BUY_DOCS_ROWS.substr($nIDBuyDoc,0,6);
			
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$sWebServerTime = date("Y-m-d H:i:s"); 
			
			$oDBNomenclaturesExpenses = new DBNomenclaturesExpenses();
			$nIDNomenclatureExpense = $oDBNomenclaturesExpenses->getIDGSMNomenclature();
			
			if(empty($nIDNomenclatureExpense)) {
				throw new Exception("Няма избрана номенклатура разход за GSM-Mtel от менюто НОМЕНКЛАТУРИ->ФИНАНСИ->НОМЕНКЛАТУРИ РАЗХОДИ");
			}
			
			global $db_name_personnel,$db_name_sod;
			
			$sQuery = "
				INSERT INTO {$sTableName}
					(
						id_buy_doc,
						id_office,
						id_object,
						id_person,
						id_nomenclature_expense,
						month,
						quantity,
						measure,
						single_price,
						total_sum,
						updated_user,
						updated_time
					)
				SELECT 
					{$nIDBuyDoc},
					s.id_office,
 					IF(s.id_object_duty != 0,s.id_object_duty,s.id_object) AS id_object,
 					s.id_person,
 					{$nIDNomenclatureExpense},
 					CONCAT(SUBSTRING(s.month,1,4),'-',SUBSTRING(s.month,5,2),'-01'),
 					s.count,
 					'бр.',
 					(s.total_sum - s.paid_sum)/s.count,
 					s.total_sum - s.paid_sum,
 					{$nIDPerson},
 					'{$sWebServerTime}'
 				FROM {$db_name_personnel}.salary s
 				LEFT JOIN {$db_name_sod}.offices off ON off.id = s.id_office
 				WHERE LENGTH(s.month) = 6
 					AND s.is_earning = 0
 					AND s.to_arc = 0
 					AND s.paid_sum < s.total_sum
 					AND s.code = '-GSM'
 					AND s.month = {$sMonth}
			";
			
			$this->_oDB->Execute($sQuery);
		}
		
		public function delBuyIDBuyDoc($nIDBuyDoc) {
			
			$sTableName = PREFIX_BUY_DOCS_ROWS.substr($nIDBuyDoc,0,6);
			
			$sQuery = "
				DELETE 
				FROM {$sTableName}
				WHERE id_buy_doc = {$nIDBuyDoc}
			";
			
			$this->_oDB->Execute($sQuery);
			
		}
		
		public function getByIDBuyDoc($nIDBuyDoc) {
			$sTableName = PREFIX_BUY_DOCS_ROWS.substr($nIDBuyDoc,0,6);
			
			$sQuery = "
				SELECT SQL_NO_CACHE
					*
				FROM {$sTableName}
				WHERE id_buy_doc = {$nIDBuyDoc}
			";
			
			return $this->select2($sQuery);			
		}
		
		public function sumDDS($sMonth,$nIDOffice) {
			
			$sTableName = PREFIX_BUY_DOCS_ROWS.$sMonth;
			$sTableNameJoin = PREFIX_BUY_DOCS.$sMonth;
			
			$sQuery = "
				SELECT 
					SUM(bdr.total_sum) AS dds
				FROM {$sTableName} bdr
				LEFT JOIN {$sTableNameJoin} bd ON bd.id = bdr.id_buy_doc
				WHERE bd.to_arc = 0
					AND bd.doc_status = 'final'
					AND (bd.doc_type = 'kvitanciq' OR bd.doc_type = 'faktura')
					AND bdr.id_office = {$nIDOffice}
				GROUP BY bdr.id_nomenclature_expense
			
			";
			
			return $this->selectOne2($sQuery);
			
		}
		
		public function getReportBuy(DBResponse $oResponse,$nIDBuyDoc) {
			
			if($this->isValidID($nIDBuyDoc)) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr($nIDBuyDoc,0,6);
			} else {
				throw new Exception("Невалидно id");
			}
			
			$aBuyDoc = array();
			
			$oDBBuyDocs = new DBBuyDocs();
			$oDBBuyDocs->getRecord($nIDBuyDoc,$aBuyDoc);
			
			global $db_name_sod,$db_name_personnel;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					CONCAT_WS(',',t.id,id_person) as id,
					ne.name expense_name,	
					f.name AS firm_name,
					off.name AS office_name,
					obj.name AS object_name,
					t.month,
					t.single_price,
					t.quantity,
					t.measure,
					t.total_sum,
					t.paid_sum,
					t.paid_date,
					t.note,
					id_person,
					CONCAT_WS(' ',p.fname,p.mname,p.lname) as person_name
				FROM {$sTableName} t
				LEFT JOIN nomenclatures_expenses ne ON ne.id = t.id_nomenclature_expense
				LEFT JOIN {$db_name_sod}.objects obj ON obj.id = t.id_object
				LEFT JOIN {$db_name_sod}.offices off ON off.id = t.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = off.id_firm
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = t.id_person
				WHERE t.id_buy_doc = {$nIDBuyDoc}
			";
			
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse);

			$oResponse->setField('expense_name','Разход','Сортирай по разход');
			$oResponse->setField('firm_name','Фирма','Сортирай по фирма');
			$oResponse->setField('office_name','Офис','Сортирай по офис');
			$oResponse->setField('object_name','Обект','Сортирай по обект');
			if($aBuyDoc['doc_type'] == 'salary') {
				$oResponse->setField('person_name',"Служител","Сортирай по служител");
			}
			$oResponse->setField('month','Месец','Сортирай по месец',NULL,NULL,NULL,array("DATA_FORMAT" => DF_MONTH));
			$oResponse->setField('note','Бележка','Сортирай по бележка',NULL,NULL,NULL,array("DATA_FORMAT" => DF_STRING));
			$oResponse->setField('single_price','Ед. цена','Сортирай по единична цена',NULL,NULL,NULL,array("DATA_FORMAT" => DF_CURRENCY4));
			$oResponse->setField('quantity','Брой','Сортирай по брой',NULL,NULL,NULL,array("DATA_FORMAT"=>DF_NUMBER));
			$oResponse->setField('total_sum','Сума','Сортирай по сума',NULL,NULL,NULL,array("DATA_FORMAT"=>DF_CURRENCY4));
			
			if($aBuyDoc['to_arc'] == 0) {
				$oResponse->setField('paid_sum','Изплатена сума','Сортирай по изплатена сума',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_CURRENCY4));
				$oResponse->setField('paid_date','Последно погасяване','Сортирай по последно погасяване',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_DATETIME));
			}
			
			if($aBuyDoc['doc_status'] == 'proforma') {
				$oResponse->setField( ' ', '', '', 'images/edit.gif', 'editRow', '');
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'delRow', '');
			}
			
			$oResponse->setFieldLink('person_name', 'openPersonnel');
		}
		
		/**
		 * Връща общата сума за фирмата, от описа на документа за покупка.
		 *
		 * @param int $nIDBuyDoc
		 * @param int $nIDFirm
		 * @return float
		 */
		public function getBuyDocFirmTotalSum( $nIDBuyDoc, $nIDFirm )
		{
			global $db_name_sod;
			
			//Validations
			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
			{
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( !$this->isValidID( $nIDBuyDoc ) )
			{
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}
			//End Validations
			
			$sYearMonth = substr( $nIDBuyDoc, 0, 6 );
			
			$sQuery = "
				SELECT
					SUM( bdr.total_sum ) AS total_sum
				FROM
					" . PREFIX_BUY_DOCS_ROWS . $sYearMonth . " bdr
				LEFT JOIN
					{$db_name_sod}.offices off ON off.id = bdr.id_office
				WHERE
					bdr.id_buy_doc = {$nIDBuyDoc}
					AND off.id_firm = {$nIDFirm}
				GROUP BY
					off.id_firm
				LIMIT 1
			";
			
			$aData = $this->selectOnce2( $sQuery );
			
			if( !empty( $aData ) )return $aData['total_sum'];
			else return 0;
		}
		
		/**
		 * Връща масив с всички фирми, които участват в описа на документа за покупка, и техните суми.
		 *
		 * @param int $nIDBuyDoc
		 * @return array
		 */
		public function getBuyDocFirmsTotalSum( $nIDBuyDoc )
		{
			global $db_name_sod;
			
			if( $this->isValidID( $nIDBuyDoc ) )
			{
				$sYearMonth = substr( $nIDBuyDoc, 0, 6 );
			}
			else
			{
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
				SELECT
					fir.id,
					fir.name,
					SUM( bdr.total_sum ) AS total_sum
				FROM
					" . PREFIX_BUY_DOCS_ROWS . $sYearMonth . " bdr
				LEFT JOIN
					{$db_name_sod}.offices off ON off.id = bdr.id_office
				LEFT JOIN
					{$db_name_sod}.firms fir ON fir.id = off.id_firm
				WHERE
					bdr.id_buy_doc = {$nIDBuyDoc}
				GROUP BY
					fir.id
			";
			
			return $this->select2( $sQuery );
		}
		
		
		/**
		 * Функцията връща подробен опис на документа за покупка без записите за ДДС
		 * 
		 * @author Павел Петров
		 * @name getRowsByDoc
		 * 
		 * @param int $nID - ID на документа за покупка (разходен)
		 * @return array масив с описа на документа
		 */			
		public function getRowsByDoc( $nID ) {
			global $db_name_finance, $db_name_sod;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}			
			
			$sQuery = "
				SELECT
					bd.id,
					bd.id_office,
					o.name as office_name,
					o.id_firm,
					f.name as firm_name,
					bd.id_nomenclature_expense as id_nomenclature,
					ne.code as nomenclature_code,
					ne.name as nomenclature_name,
					bd.id_direction,
					d.name as direction_name,
					bd.month as month,
					bd.quantity as quantity,
					bd.single_price as single,
					bd.total_sum as sum,
					bd.is_dds,
					bd.note as note,
					IF ( total_sum = paid_sum, 1, 0 ) as payed
				FROM {$db_name_finance}.{$sTableName} bd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = bd.id_office AND o.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.directions d ON d.id = bd.id_direction
				LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON (ne.id = bd.id_nomenclature_expense AND ne.to_arc = 0)
				WHERE bd.id_buy_doc = {$nID}
					AND bd.is_dds != 1
			";
			
			return $this->select2($sQuery);			
		}	
		
		/**
		 * Функцията връща последния ордер по зададено ID на документ 
		 * 
		 * @author Павел Петров
		 * @name getLastOrderByDoc
		 * 
		 * @param int $nID - ID на документа
		 * @return array масив с ордерa към документа
		 */			
		public function getLastOrderByDoc($nID) {
			global $db_name_finance;
			
			$aData = array();
			$nIDorder	= 0;
			
			if ( $this->isValidID($nID) ) {
				$sDocName = PREFIX_BUY_DOCS.substr( $nID, 0, 6 );
			} else {
				return array();
			}	

			$sQuery = "
				SELECT last_order_id FROM {$db_name_finance}.{$sDocName} WHERE id = {$nID}
			";
			
			$nIDorder = $this->selectOne2($sQuery);
			
			if ( $this->isValidID($nIDorder) ) {
				$sOrderName = PREFIX_ORDERS.substr( $nIDorder, 0, 6 );
			} else {
				return array();
			}	
			
			$sQuery = "
				SELECT 
					*
				FROM {$db_name_finance}.{$sOrderName}
				WHERE id = {$nIDorder}
			";
			
			return $this->select2($sQuery);
		}
		
		/**
		 * Функцията връща наличните ордери на основа на документ 
		 * 
		 * @author Павел Петров
		 * @name getOrdersByDoc
		 * 
		 * @param int $nID - ID на документа
		 * @return array масив с ордерите към документа
		 */			
		public function getOrdersByDoc( $nID ) {
			global $db_name_finance, $db_finance, $db_name_personnel;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return array();
			}				
			
			$aOrders	= array();
			$aTemp		= array();
			$aTables	= array();
	  		
			// Всички налични периодични таблици за ордери
			$aTemp	 	= SQL_get_tables( $db_finance, "orders_20", "____" );	
			
	  		reset($aTemp);
	  		ksort($aTemp);
	  		reset($aTemp);
	  		
	  		$sTime1 = date( "Ym", mktime(0, 0, 0, substr( $nID, 4, 2 )-1, 1, substr( $nID, 0, 4 )) );
	  		$sTime2 = date( "Ym", mktime(0, 0, 0, substr( $nID, 4, 2 )+1, 1, substr( $nID, 0, 4 )) );
	  			
	  		$aTableWatch = array();
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime1;	
	  		$aTableWatch[] = PREFIX_ORDERS.substr( $nID, 0, 6 );	
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime2;	

			foreach ( $aTemp as $val ) {
				// Попълваме масив с имена на периодични таблици
	  			if ( in_array($val, $aTableWatch) ) {
	  				$aTables[] = $val;	
	  			}
			}
			
			unset($val); unset($aTemp);
			
			reset($aTables);
			
			$sQuery = "";
			
			if ( count($aTables) == 1 ) {
				$sTableName = current($aTables);
				
				$sQuery = "
					SELECT 
						o.id,
						o.num,
						DATE_FORMAT(o.order_date, '%Y-%m-%d') as date,
						o.bank_account_id as id_account,
						ba.name_account as smetka,
						o.order_status,
						IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as sum,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
					FROM {$db_name_finance}.{$sTableName} o
					LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
					LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
					WHERE o.doc_id = '{$nID}'
						AND o.doc_type = 'buy'
				";
			} else {
				for ( $i = 1; $i < count($aTables); $i++ ) {
					$sTableName = current($aTables);
					
					$sQuery .= "
						( 
							SELECT 
								o.id,
								o.num,
								DATE_FORMAT(o.order_date, '%Y-%m-%d') as date,
								o.bank_account_id as id_account,
								ba.name_account as smetka,
								o.order_status,
								IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as sum,
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
							FROM {$db_name_finance}.{$sTableName} o
							LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
							LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
							WHERE o.doc_id = '{$nID}'
								AND o.doc_type = 'buy'
						) UNION 
					";

					next($aTables);			
				}
				
				$sTableName = end($aTables);
				
				$sQuery .= "
					(
						SELECT 
							o.id,
							o.num,
							DATE_FORMAT(o.order_date, '%Y-%m-%d') as date,
							o.bank_account_id as id_account,
							ba.name_account as smetka,
							o.order_status,
							IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as sum,
							CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
						FROM {$db_name_finance}.{$sTableName} o
						LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
						LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
						WHERE o.doc_id = '{$nID}'
							AND o.doc_type = 'buy'
					)
				";				
			}

			if ( empty($aTables) ) {
				return array();
			} else {
				return $this->select2($sQuery);
				
			}	
		}					
		
		/**
		 * Функцията връща записа с ДДС към конкретен документ
		 * 
		 * @author Павел Петров
		 * @name getDDSByDoc
		 * 
		 * @param int $nID - ID на документа за покупка (разходен)
		 * @return array масив със записа за ДДС-то
		 */			
		public function getDDSByDoc( $nID ) {
			global $db_name_finance, $db_name_sod, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				//throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
				return array();
			}			
			
			$sQuery = "
				SELECT
					bd.id,
					bd.id_office,
					o.name as office_name,
					o.id_firm,
					f.name as firm_name,
					bd.id_nomenclature_expense as id_nomenclature,
					ne.code as nomenclature_code,
					ne.name as nomenclature_name,
					bd.id_direction,
					d.name as direction_name,
					bd.month as month,
					bd.quantity as quantity,
					bd.single_price as single,
					bd.total_sum as sum,
					bd.paid_sum as paid_sum,
					bd.is_dds,
					IF ( bd.total_sum = bd.paid_sum, 1, 0 ) as payed
				FROM {$db_name_finance}.{$sTableName} bd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = bd.id_office AND o.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.directions d ON d.id = bd.id_direction
				LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON (bd.id_nomenclature_expense > 0 AND  ne.id = bd.id_nomenclature_expense AND ne.to_arc = 0)
				WHERE bd.id_buy_doc = {$nID}
					AND bd.is_dds = 1
				LIMIT 1
			";
			//return $sQuery;
			//return $db_finance->getArray($sQuery);
			return $this->select2($sQuery);			
		}	

		/**
		 * Функцията връща записа с ДДС към конкретен документ
		 * 
		 * @author Павел Петров
		 * @name getDDSByDoc
		 * 
		 * @param int $nID - ID на документа за покупка (разходен)
		 * @return array масив със записа за ДДС-то
		 */			
		public function getAllByRow( $nID ) {
			global $db_name_finance, $db_name_sod;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return array();
			}			
			
			$sQuery = "
				SELECT 
					*
				FROM {$db_name_finance}.{$sTableName}
				WHERE id = {$nID}
			";
			
			return $this->select2($sQuery);			
		}			
		
		/**
		 * Функцията връща списък от ID-тата на съответните 
		 * документи в счета по зададено ID на документ-а.
		 * 
		 * @author Павел Петров
		 * @name getSchetByIDDoc
		 * 
		 * @param int $nIDDoc - ID на документа 
		 * 
		 * @return array - списък от ID-тата на съответните документи в счета 
		 */			
		public function getSchetByIDDoc($nIDDoc) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nIDDoc) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nIDDoc, 0, 6 );
			} else {
				return array();
			}	

			$sQuery = "SELECT id, id_schet_row FROM {$db_name_finance}.{$sTableName} WHERE id_buy_doc = {$nIDDoc} AND id_schet_row > 0 ";
			$result = $this->select2($sQuery);	

			return !empty($result) ? $result : array();	
		}			
		
		/**
		 * Функцията изтрива записи по ид на документ и номера на редове
		 * 
		 * @author Павел Петров
		 * @name deleteRows
		 * 
		 * @param int $nID - ID на документа за покупка (разходен)
		 * @param string $sIDs - поредица от ID-та на записи, които НЕ трябва да се трият
		 * @return bool
		 */			
		public function deleteRows( $nID, $sIDs ) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				//throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
				return false;
			}			
			
			$sQuery = "DELETE FROM {$db_name_finance}.{$sTableName} WHERE id_buy_doc = {$nID} AND is_dds = 0 AND id NOT IN ({$sIDs}) ";
			$db_finance->Execute($sQuery);
			
			return true;
		}	
		
		
		/**
		 * Функцията връща true ако имаме специална номенклатура ДДС
		 * по зададено ID на документ-а.
		 * 
		 * @author Павел Петров
		 * @name checkForDDS
		 * 
		 * @param int $nID - ID на документа за покупка (разходен)
		 * 
		 * @return bool 
		 */			
		public function checkForDDS($nID) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				//throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
				return false;
			}	

			$sQuery = "SELECT id FROM {$db_name_finance}.{$sTableName} WHERE id_buy_doc = {$nID} AND is_dds = 2 ";
			$result = $this->selectOne2($sQuery);	
			
			return !empty($result) ? $result : 0;	
		}
		
		/**
		 * Функцията връща неплатените задължения на издаден
		 * документ по зададено ID на документ-а.
		 * 
		 * @author Павел Петров
		 * @name getDuty
		 * 
		 * @param int $nID - ID на документа 
		 * 
		 * @return real - неплатена стойност 
		 */			
		public function getDuty($nID) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return 0;
			}	

			$sQuery = "
				SELECT 
					SUM(total_sum - paid_sum) as unpaid_sum 
				FROM {$db_name_finance}.{$sTableName} 
				WHERE id_buy_doc = {$nID} 
			";
			
			$result = $this->selectOne2($sQuery);	
			
			return !empty($result) ? $result : 0;	
		}		
		
		/**
		 * Функцията връща true ако имаме специална номенклатура ДДС
		 * по зададено ID на документ-а.
		 * 
		 * @author Павел Петров
		 * @name checkForTransfer
		 * 
		 * @param int $nID - ID на документа за покупка (разходен)
		 * 
		 * @return bool 
		 */			
		public function checkForTransfer($nID, $rb = 0) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return false;
			}	

			$sQuery = "
				SELECT 
					b.id 
				FROM {$db_name_finance}.{$sTableName} b
				LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( b.id_nomenclature_expense > 0 AND ne.id = b.id_nomenclature_expense AND ne.to_arc = 0 )
				WHERE b.id_buy_doc = {$nID} 
			";
			
			if ( $rb == 1 ) {
				$sQuery .= " AND ne.for_transfer = 0 ";
			} else {
				$sQuery .= " AND ne.for_transfer = 1 ";
			}
			
			$sQuery .= " LIMIT 1 ";
			
			$result = $this->selectOne2($sQuery);	
			
			return !empty($result) ? $result : 0;	
		}

		/**
		 * Функцията връща данните за определен запис
		 * 
		 * @author Павел Петров
		 * @name getByIDRow
		 * 
		 * @param int $nID - ID на записа 
		 * 
		 * @return array 
		 */			
		public function getByIDRow($nID) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_BUY_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return array();
			}	

			$sQuery = "
				SELECT 
					* 
				FROM {$db_name_finance}.{$sTableName}
				WHERE id = {$nID} 
				LIMIT 1
			";
			
			$result = $this->selectOnce2($sQuery);	
			
			return !empty($result) ? $result : array();	
		}		

		
		/**
		 * Връща списък с офисите, участващи в документите според зададените критерии.
		 *
		 * @author Павел Петров 
		 * @name getOfficesForTotals()
		 * 
		 * @param (string) 	- $sPeriod
		 * @param (integer) - $nIDFirm
		 * @param (string) 	- $sFilter
		 * @return (array) 	- Масив от офисите, за които има данни според филтъра
		 */
		public function getOfficesForTotals( $sPeriod, $nIDFirm = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance, $db_name_sod;
			
			$aTotal		= array();
			$aPeriod	= explode("-", $sPeriod);
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "buy_docs_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "buy_docs_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		
			

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				
				$sQuery = "
					( SELECT 
						DISTINCT brw.id_office, 
						IF ( brw.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature, 
						brw.is_dds
					FROM {$db_name_finance}.{$sTableName} brw
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = brw.id_nomenclature_expense )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
					LEFT JOIN {$db_name_sod}.offices o ON ( o.id = brw.id_office AND brw.id_office > 0 )
					WHERE brw.id_office > 0
						AND DATE_FORMAT(brw.month, '%Y-%m') = '{$sPeriod}'
						AND brw.paid_sum > 0
				";
				
				if ( !empty($nIDFirm) ) {
					$sQuery .= " AND o.id_firm = {$nIDFirm} ";
				}
								
				if ( !empty($sFilter) ) {
					$aTemp = array();
					$aTemp = explode(",", $sFilter);
					
					// ДДС
					if ( in_array(-1, $aTemp) ) {
						$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
					} else {
						$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
					}
				}		
				
				$sQuery .= " ) ";			
				
				$br++;
				
				$aData 	= $this->select2($sQuery);
				
				foreach ( $aData as $v ) {
					$aTotal[$v['id_office']] = $v['id_office'];
				}
			}

			return $aTotal;
		}
				
		
		/**
		 * Функцията връща тотали на сумите по номенклатури към определен месец
		 * Ползва се във флекс справката за Финанси/Събираемост
		 * 
		 * @author Павел Петров
		 * @name getTotalsByNom
		 * 
		 * @param (string) $sPeriod 	- За кой МЕСЕЦ приходи;
		 * @param (int) $nIDFirm 		- ID на фирмата (незадължителен)
		 * @param (string) $nIDOffices 	- Числова редица с ID-та на офиса (незадължителен)
		 * 
		 * @return array - Списък с резултата групиран по номенклатури
		 */			
		public function getTotalsByNom( $sPeriod, $nIDFirm = 0, $nIDOffice = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance;
			
			$aTotal		= array();
			$sOffice	= "";
			$aPeriod	= explode("-", $sPeriod);
			$oOffice	= new DBOffices();
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "buy_docs_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "buy_docs_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				
				$sQuery = "
					( SELECT 
						IF ( brw.is_dds > 0, 13, IF (ne.id_group > 1, IF ( ne.id_group = 8, 13, ne.id_group), -3) ) as id,
						IF ( brw.is_dds > 0, 'Други', IF (ne.id > 1, ng.name, 'Невъведена') ) as gname,
						IF ( brw.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature,
						SUM(brw.paid_sum) as price,
						IF ( brw.is_dds > 0, 'ДДС', IF (ne.id > 1, ne.name, 'Невъведена') ) as nomenclature,
						brw.is_dds
					FROM {$db_name_finance}.{$sTableName} brw
					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = brw.id_nomenclature_expense )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
					WHERE brw.id_office > 0
						AND DATE_FORMAT(brw.month, '%Y-%m') = '{$sPeriod}'
						AND brw.paid_sum > 0	
				";
				
				if ( !empty($nIDOffice) ) {
					$sQuery 	.= " AND brw.id_office = {$nIDOffice} ";
				} elseif ( !empty($nIDFirm) ) {
					$sOffice	.= $oOffice->getIdsByFirm($nIDFirm);
					
					if ( !empty($sOffice) ) {
						$sQuery .= " AND brw.id_office IN ({$sOffice}) ";
					}
				}			
				
				$sQuery .= " GROUP BY brw.id_nomenclature_expense, brw.is_dds ";
				
				if ( !empty($sFilter) ) {
					$aTemp = array();
					$aTemp = explode(",", $sFilter);
					
					// ДДС
					if ( in_array(-1, $aTemp) ) {
						$sQuery .= " HAVING ( is_dds > 0 OR id_nomenclature IN ({$sFilter}) ) ";
					} else {
						$sQuery .= " HAVING id_nomenclature IN ({$sFilter}) ";
					}
				}		
				
				$sQuery .= " ) ";			
				
				$br++;
				
				$aData 	= $this->select2($sQuery);
				//return $sQuery;
				foreach ( $aData as $v ) {
					$found_key = -1;
					
					if ( ($found_key = array_search_value($aTotal, "id_nomenclature", $v['id_nomenclature'])) !== FALSE ) {
						$aTotal[$found_key]['price'] += $v['price'];
					} else {
						$aTotal[] = $v;
					}
				}
			}
	
			return $aTotal;
		}	
				
		
//		/**
//		 * Функцията връща тотали на сумите по номенклатури към определен месец
//		 * Ползва се във флекс справката за Финанси/Събираемост
//		 * 
//		 * @author Павел Петров
//		 * @name getTotalsByNom
//		 * 
//		 * @param (string) $sPeriod - За кой МЕСЕЦ разходи;
//		 * @param (int) $nIDFirm 	- ID на фирмата (незадължителен)
//		 * @param (int) $nIDOffice 	- ID на офиса (незадължителен)
//		 * 
//		 * @return array - Списък с резултата групиран по номенклатури
//		 */			
//		public function getTotalsByNom( $sPeriod, $nIDFirm = 0, $nIDOffice = 0 ) {
//			global $db_finance, $db_name_finance;
//			
//			$aData 	= array();
//			$sOfce	= "";
//			$aPer	= explode("-", $sPeriod);
//			$oOfce	= new DBOffices();
//			$aTbls	= array();
//			
//			if ( isset($aPer[1]) ) {
//				$ye = $aPer[0];
//				$mo = $aPer[1];
//				
//				if ( checkdate($mo, 1, $ye) ) {	
//					$sTableName1	= PREFIX_BUY_DOCS_ROWS.date("Ym", mktime(0, 0, 0, $mo, 1, $ye));
//					$sTableName2	= PREFIX_BUY_DOCS_ROWS.date("Ym", mktime(0, 0, 0, $mo - 1, 1, $ye));
//
//				} else {
//					return array();
//				}
//			}
//			
//			$aTbls	= SQL_get_tables($db_finance, "buy_docs_rows_", "______", "ASC");
//			
//			if ( !in_array($sTableName1, $aTbls) ) {
//				return array();
//			}
//			
//			if ( !in_array($sTableName2, $aTbls) ) {
//				$sTableName2 = "";
//			}			
//			
//			$sQuery = "
//				(SELECT 
//					IF ( brw.is_dds > 0, 13, IF (ne.id_group > 1, ne.id_group, -3) ) as id,
//					IF ( brw.is_dds > 0, 'Други', IF (ne.id > 1, ng.name, 'Невъведена') ) as gname,
//					IF ( brw.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature,
//					SUM(brw.paid_sum) as price,
//					IF ( brw.is_dds > 0, 'ДДС', IF (ne.id > 1, ne.name, 'Невъведена') ) as nomenclature,
//					brw.is_dds
//				FROM {$db_name_finance}.{$sTableName1} brw
//				LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = brw.id_nomenclature_expense )
//				LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
//				WHERE DATE_FORMAT(brw.month, '%Y-%m') = '{$sPeriod}'
//					AND brw.paid_sum > 0	
//			";
//			
//			if ( !empty($nIDOffice) ) {
//				$sQuery .= " AND brw.id_office = '{$nIDOffice}' ";
//			} elseif ( !empty($nIDFirm) ) {
//				$sOfce	.= $oOfce->getIdsByFirm($nIDFirm);
//				
//				if ( !empty($sOfce) ) {
//					$sQuery .= " AND brw.id_office IN ('{$sOfce}') ";
//				}
//			}			
//			
//			$sQuery .= " GROUP BY brw.id_nomenclature_expense, brw.is_dds )";
//			
//			if ( !empty($sTableName2) ) {
//				$sQuery .= " 
//					UNION ( SELECT 
//						IF ( brw.is_dds > 0, 13, IF (ne.id_group > 1, ne.id_group, -3) ) as id,
//						IF ( brw.is_dds > 0, 'Други', IF (ne.id > 1, ng.name, 'Невъведена') ) as gname,
//						IF ( brw.is_dds > 0, -1, IF (ne.id > 1, ne.id, -3) ) as id_nomenclature,
//						SUM(brw.paid_sum) as price,
//						IF ( brw.is_dds > 0, 'ДДС', IF (ne.id > 1, ne.name, 'Невъведена') ) as nomenclature,
//						brw.is_dds
//					FROM {$db_name_finance}.{$sTableName1} brw
//					LEFT JOIN {$db_name_finance}.nomenclatures_expenses ne ON ( ne.id = brw.id_nomenclature_expense )
//					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = ne.id_group )
//					WHERE DATE_FORMAT(brw.month, '%Y-%m') = '{$sPeriod}'
//						AND brw.paid_sum > 0	
//				";
//				
//				if ( !empty($nIDOffice) ) {
//					$sQuery .= " AND brw.id_office = '{$nIDOffice}' ";
//				} elseif ( !empty($nIDFirm) ) {
//					$sOfce	.= $oOfce->getIdsByFirm($nIDFirm);
//					
//					if ( !empty($sOfce) ) {
//						$sQuery .= " AND brw.id_office IN ('{$sOfce}') ";
//					}
//				}			
//				
//				$sQuery .= " GROUP BY brw.id_nomenclature_expense, brw.is_dds )";				
//			}
//			
//			$aData 	= $this->select2($sQuery);
//
//			return $aData;
//		}					
	}
?>