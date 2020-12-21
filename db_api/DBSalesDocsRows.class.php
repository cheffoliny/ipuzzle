<?php

	class DBSalesDocsRows extends DBMonthTable {
		
		function __construct() {
			global $db_finance,$db_name_finance;
			
			parent::__construct($db_name_finance,PREFIX_SALES_DOCS_ROWS,$db_finance);
		}
		
		public function getReport(DBResponse $oResponse,$nIDSaleDoc) {
			
			global $db_name_sod;
			
			if($this->isValidID($nIDSaleDoc)) {
				$sYearMonth = substr($nIDSaleDoc,0,6);
			} else {
				throw new Exception("Невалиндно id");
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					sdr.id,
					f.name AS firm_name,
					o.name AS office_name,
					obj.name AS object_name,
					sdr.id_object,
					DATE_FORMAT(sdr.month,'%m.%Y') AS for_month,
					ns.code AS service_code,
					sdr.service_name,
					sdr.quantity,
					sdr.measure,
					abs(sdr.single_price) AS single_price,
					abs(sdr.total_sum) AS total_sum,
					sdr.paid_sum,
					sdr.paid_date
				FROM ".PREFIX_SALES_DOCS_ROWS.$sYearMonth." sdr
				LEFT JOIN {$db_name_sod}.objects obj ON obj.id = sdr.id_object
				LEFT JOIN {$db_name_sod}.offices o ON o.id = sdr.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				LEFT JOIN nomenclatures_services ns ON ns.id = sdr.id_service
				WHERE sdr.id_sale_doc = {$nIDSaleDoc}			
			";
			//APILog::Log(0, $sQuery);
			$this->getResult($sQuery,'id',DBAPI_SORT_ASC,$oResponse);
			
			foreach ($oResponse->oResult->aData as $key => &$value) {
				$value['chk'] = 1;
				$oResponse->setDataAttributes($key,'object_name',array( 'onclick' 	=> "openObject({$value['id_object']})",
																		'style' 	=> "cursor:pointer;padding-right:20px;"
																	   )
												);
			}
			
			$oResponse->setField('chk','','');
			$oResponse->setFieldData('chk','input',array('type' => 'checkbox','exception' => 'false'));
			
			$oResponse->setField('firm_name','Фирма','Сортирай по фирма',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_STRING));
			$oResponse->setField('office_name','Офис','Сортирай по офис',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_STRING));
			$oResponse->setField('object_name','Обект','Сортирай по обект');
			$oResponse->setField('for_month','месец','Сортирай по месец',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_STRING));
			$oResponse->setField('service_code','Код','Сортирай по код на услугата',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_STRING));
			$oResponse->setField('service_name','Услуга','Сортирай по име на услуга',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_STRING));
			$oResponse->setField('quantity','Кол.','Сортирай по количество',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_NUMBER));
			$oResponse->setField('measure','Мярка','Сортирай по мерна единица');
			$oResponse->setField('single_price','Единична цена','Сортирай по единична цена',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_CURRENCY));
			$oResponse->setField('total_sum','Сума','Сортирай по сума',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_CURRENCY));
			$oResponse->setField('paid_sum','Изплатена сума','Сортирай по изплатена сума',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_CURRENCY));
			$oResponse->setField('paid_date','Последно погасяване','Сортирай по последно погасяване',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_DATETIME));
			
			$oResponse -> setFormElement('form1', 'sel', array(), '');		
			$oResponse -> setFormElementChild('form1', 'sel', array('value' => '1'), "--- маркирай всички ---");
			$oResponse -> setFormElementChild('form1', 'sel', array('value' => '2'), "--- отмаркирай всички ---");
			
		}
		
		public function prepareReportQuery($nIDSaleDoc,$sViewType) {
			global $db_name_sod;
			
			if($this->isValidID($nIDSaleDoc)) {
				$sYearMonth = substr($nIDSaleDoc,0,6);
			} else {
				throw new Exception("Невалиндно id");
			}
			
			$oSaleDoc = new DBSalesDocs();
			$aSaleDoc = array();
			$aSaleDoc = $oSaleDoc->getDoc($nIDSaleDoc);
			
			$service_name = isset($aSaleDoc['single_view_name']) ? $aSaleDoc['single_view_name'] : "услуга";
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					sdr.id,
			";
			
			switch ($sViewType) {
				case 'single':
					$sQuery .= "
						'{$service_name}' AS service,
						'1' AS quantity,
						'бр.' as measure,
						SUM(sdr.total_sum) AS single_price,
					";
				break;
				case 'by_services':
					$sQuery .= "
						sdr.service_name AS service,
						'1' AS quantity,
						'бр.' as measure,
						SUM(sdr.total_sum) AS single_price,
					";
				break;
				case 'by_objects':
					$sQuery .= "
						IF ( LENGTH(sdr.object_name) > 0, sdr.object_name, ob.name ) as service,
						'1' AS quantity,
						'бр.' as measure,
						SUM(sdr.total_sum) AS single_price,
					";	
				break;
				case 'detail':
					$sQuery .= "
						CONCAT( sdr.service_name, ' - ', IF( LENGTH(sdr.object_name) > 0, sdr.object_name, ob.name ) ) AS service,
						sdr.month,
						sdr.quantity,
						sdr.measure,
						SUM(sdr.single_price) AS single_price,
					";
			}
			
			$sQuery .= "
					
					SUM(sdr.total_sum) AS total_sum
				FROM ".PREFIX_SALES_DOCS_ROWS.$sYearMonth." sdr
				LEFT JOIN {$db_name_sod}.objects ob ON ob.id = sdr.id_object
				WHERE sdr.id_sale_doc = {$nIDSaleDoc}
					AND sdr.is_dds = 0
			";
			
			switch ($sViewType) {		
				case 'single':
					$sQuery .= " GROUP BY service\n";	
				break;		
				case 'by_services':
					$sQuery .= " GROUP BY service\n";
				break;	
				case 'by_objects':
					$sQuery .= " GROUP BY sdr.id_object\n";
				break;
				case 'detail':
					$sQuery .= " GROUP BY sdr.id\n";
				break;
			}
			
			return $sQuery;
		}
		
		public function getReport2( DBResponse $oResponse, $nIDSaleDoc, $sViewType = "single" ) {
			
			$sQuery = $this->prepareReportQuery($nIDSaleDoc,$sViewType);
			//APILog::Log(0, $sQuery);
			$this->getResult( $sQuery, "service_name", DBAPI_SORT_ASC, $oResponse );

			$nSumTotal = 0;
			
			foreach ($oResponse->oResult->aData as $key => &$value) {
				
				$value['single_price'] 	= sprintf('%0.3f лв.', $value['single_price'] );
				$value['total_sum'] 	= sprintf('%0.3f лв.', $value['total_sum'] );	
					
				$oResponse->setDataAttributes( $key, "single_price", array("style" => "text-align: right") );
				$oResponse->setDataAttributes( $key, "total_sum",	 array("style" => "text-align: right") );
				
				$nSumTotal += $value['total_sum'];
			}
			
			$oResponse->setFormElement( "form1", "sum_all",   array(), sprintf('%0.2f лв.', $nSumTotal) );
			$oResponse->setFormElement( "form1", "sum_dds",	  array(), sprintf('%0.2f лв.', $nSumTotal * 0.2) );
			$oResponse->setFormElement( "form1", "sum_total", array(), sprintf('%0.2f лв.', $nSumTotal * 1.2) );
			
			$oResponse->setField( "service", "Услуга", "Сортирай по услуга" );
			
			if ( $sViewType == "detail" ) {
				$oResponse->setField( "month", "За месец", "Сортирай по месец", NULL, NULL, NULL, array("DATA_FORMAT" => DF_MONTH) );
			}
			
			$oResponse->setField( "quantity", 	"Количество", 		"Сортирай по количество",	NULL, NULL, NULL, array("DATA_FORMAT" => DF_NUMBER) );
			$oResponse->setField( "measure", 	"Мерна единица", 	"Сортирай по мерна единица" );
			$oResponse->setField( "single_price", "Единична цена", 	"Сортирай по единична цена" );
			$oResponse->setField( "total_sum", 	"Сума", 			"Сортирай по сума" );
		}
		
		public function getReport3( DBResponse $oResponse, $sViewType = 'single' ) {
			// Pavel
			$oDBObjectServices = new DBObjectServices();
			$oDBObjectsSingles = new DBObjectsSingles();
			//$sQuery = $this->prepareReportQuery($nIDSaleDoc,$sViewType);
				
			//$this->getResult($sQuery,'service_name',DBAPI_SORT_ASC,$oResponse);
			$nIDUser	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$aData 		= isset($_SESSION['invoice'][$nIDUser]['earnings']) && !empty($_SESSION['invoice'][$nIDUser]['earnings']) ? $_SESSION['invoice'][$nIDUser]['earnings'] : array();
			$aService 	= array();
			$nDDS		= isset($_SESSION['invoice'][$nIDUser]['sale_doc']['id_office_dds']) ? $_SESSION['invoice'][$nIDUser]['sale_doc']['id_office_dds'] : 0;
			$aRows 		= array();
			$nSumTotal	= 0;
			$nQty 		= 0;
			$sMsre		= "";
			$aView		= array();
			$aObj		= array();
			$aSrvce		= array();
			$aAll		= array();
			//APILog::Log(0, $aData);
			foreach ( $aData as $val ) {
				$aRow = array();
				list( $nID, $sDate ) = $val;
				
				$aRow['is_dds'] 		= 0;
				$aRow['nIDService'] 	= $nID;
				$aRow['paid_sum'] 		= 0;
				$aRow['paid_date'] 		= "0000-00-00 00:00:00";
				$aRow['updated_user'] 	= $nIDUser;
				$aRow['updated_time'] 	= time();
				
				if ( $sDate == "single" ) {
					$aService = $oDBObjectsSingles->getSingle( $nID );					
				
					$aRow['sTypeService'] 	= "single";
					$aRow['id_office'] 		= $aService['id_office'];
					$aRow['id_object'] 		= $aService['id_object'];
					$aRow['oname']	 		= $aService['oname'];
					$aRow['month'] 			= $aService['start_date'];
					$aRow['id_service'] 	= $aService['id_service'];
					$aRow['service_name']	= $aService['service_name'];
					$aRow['quantity'] 		= $aService['quantity'];
					$aRow['measure'] 		= $aService['measure_code'];
					$aRow['single_price'] 	= $aService['single_price'] * 5/6;
					$aRow['total_sum'] 		= $aService['total_sum'] * 5/6;
					
					$nSumTotal 				+= $aRow['total_sum'];
					$nQty					+= 1;
				} else {
					$aService = $oDBObjectServices->getService( $nID );
					
					$aRow['sTypeService'] 	= "month";
					$aRow['id_office'] 		= $aService['id_office'];
					$aRow['id_object'] 		= $aService['id_object'];
					$aRow['oname']	 		= $aService['oname'];
					$aRow['month'] 			= $sDate;
					$aRow['id_service'] 	= $aService['id_service'];
					$aRow['service_name']	= $aService['service_name'];
					$aRow['quantity'] 		= $aService['quantity'];
					$aRow['measure'] 		= $aService['measure_code'];
					
					list($y, $m, $d) = explode( "-", $sDate );
					
					if ( $d != "01" ) {
						$nTime 					= mktime( 0, 0, 0, $m, $d, $y);
						$nMonthDays 			= date("t", $nTime);
						$nPartOfMonth 			= ( ($nMonthDays - intval($d)) + 1 ) / $nMonthDays;
						
						$aRow['single_price'] 	= ($aService['single_price'] * 5/6) * $nPartOfMonth;
						$aRow['total_sum'] 		= ($aService['total_sum'] * 5/6) * $nPartOfMonth;
					} else {
						$aRow['single_price'] 	= $aService['single_price'] * 5/6;
						$aRow['total_sum'] 		= $aService['total_sum'] * 5/6;
					}					
					
					$nSumTotal 				+= $aRow['total_sum'];
					$nQty					+= 1;	
				}

				$aRows[] 	= $aRow;
				$sMsre		= $aRow['measure'];
				
				// Обекти
				if ( !isset($aObj[$aRow['id_object']]) ) {
					$aObj[$aRow['id_object']]['service_name'] 	= $aRow['oname'];
					$aObj[$aRow['id_object']]['single_price'] 	= $aRow['total_sum'];
					$aObj[$aRow['id_object']]['total_sum'] 		= $aRow['total_sum'];
					//$aObj[$aRow['id_object']]['quantity'] 		= 13;//$aRow['quantity'];
					$aObj[$aRow['id_object']]['measure'] 		= $aRow['measure'];
				} else {
					$aObj[$aRow['id_object']]['single_price'] 	+= $aRow['total_sum'];
					$aObj[$aRow['id_object']]['total_sum'] 		+= $aRow['total_sum'];
					//$aObj[$aRow['id_object']]['quantity'] 		+= $aRow['quantity'];
				}
				$aObj[$aRow['id_object']]['quantity'] = 1; // винаги е 1

				// Услуги
				if ( !isset($aSrvce[$aRow['nIDService']]) ) {
					$aSrvce[$aRow['nIDService']]['service_name'] 	= $aRow['service_name'];
					$aSrvce[$aRow['nIDService']]['single_price'] 	= $aRow['total_sum'];
					$aSrvce[$aRow['nIDService']]['total_sum'] 		= $aRow['total_sum'];
					$aSrvce[$aRow['nIDService']]['quantity'] 		= $aRow['quantity'];
					$aSrvce[$aRow['nIDService']]['measure'] 		= $aRow['measure'].$aRow['nIDService'];
				} else {
					$aSrvce[$aRow['nIDService']]['single_price'] 	+= $aRow['total_sum'];
					$aSrvce[$aRow['nIDService']]['total_sum'] 		+= $aRow['total_sum'];
					$aSrvce[$aRow['nIDService']]['quantity'] 		+= $aRow['quantity'];
				}
				//$aSrvce[$aRow['nIDService']]['single_price'] = $aSrvce[$aRow['nIDService']]['total_sum'] / $aSrvce[$aRow['nIDService']]['quantity'];
				
				// Подробна
				$tmpArr 					= array();
				$tmpArr['service_name'] 	= "[".$aRow['oname']."] ".$aRow['service_name'];
				$tmpArr['single_price'] 	= $aRow['total_sum'] / $aRow['quantity']; // смята се единичната цена
				$tmpArr['total_sum'] 		= $aRow['total_sum'];
				$tmpArr['quantity'] 		= $aRow['quantity'];
				$tmpArr['measure'] 			= $aRow['measure'];
				$tmpArr['month'] 			= $aRow['month'];
				
				$aAll[] = $tmpArr;
	
			}
			
			if ( $sViewType == "single" ) {
				$tmpArr = array();
				
				$tmpArr['service_name']	= "Услуга";
				$tmpArr['single_price']	= $nSumTotal;
				$tmpArr['total_sum']	= $nSumTotal;
				$tmpArr['quantity']		= 1; // винаги е 1
				$tmpArr['measure']		= $sMsre;
				
				$aView[] = $tmpArr;
			}	

			if ( $sViewType == "by_objects" ) {
				$aView = $aObj;
			}
			
			if ( $sViewType == "by_services" ) {
				$aView = $aSrvce;
			}
			
			if ( $sViewType == "detail" ) {
				$aView = $aAll;
			}			
			
			// ДДС :)
			$aDDS = array();
			$aDDS['service_name'] 	= "ДДС";
			$aDDS['single_price'] 	= sprintf( "%0.2f", $nSumTotal * 0.2 );
			$aDDS['total_sum'] 		= sprintf( "%0.2f", $nSumTotal * 0.2 );
			$aDDS['quantity'] 		= 1;
			$aDDS['measure']		= $sMsre;
			$aDDS['month']			= date("Y-m-d");
			$aDDS['is_dds'] 		= 1;
			$aDDS['nIDService'] 	= 0;
			$aDDS['id_service'] 	= 0;		
			$aDDS['id_office'] 		= $nDDS;
			$aDDS['id_object'] 		= 0;
			$aDDS['oname']	 		= "ДДС";	
			$aDDS['sTypeService'] 	= "single";
			$aDDS['paid_sum'] 		= 0;
			$aDDS['paid_date'] 		= "0000-00-00 00:00:00";
			$aDDS['updated_user'] 	= $nIDUser;
			$aDDS['updated_time'] 	= time();	
	
			$aRows[]				= $aDDS;
			
			$_SESSION['invoice'][$nIDUser]['sale_doc']['total_sum'] = $nSumTotal * 1.2;
			$_SESSION['invoice'][$nIDUser]['rows'] = $aRows;			
			
			//APILog::Log(0, $_SESSION['invoice'][$nIDUser]);
			$oResponse->setData( $aView );
			
			foreach ( $oResponse->oResult->aData as $key => &$value ) {
				$value['single_price'] 	= sprintf('%0.3f лв.', $value['single_price'] );
				$value['total_sum'] 	= sprintf('%0.3f лв.', $value['total_sum'] );
				
				$oResponse->setDataAttributes( $key, "single_price",	array('style' => 'text-align:right') );
				$oResponse->setDataAttributes( $key, "total_sum",		array('style' => 'text-align:right') );
			}
			
			
			$oResponse->setFormElement('form1', 'sum_all',array(), sprintf('%0.2f лв.', $nSumTotal));
			$oResponse->setFormElement('form1', 'sum_dds',array(), sprintf('%0.2f лв.', $nSumTotal*0.2));
			$oResponse->setFormElement('form1', 'sum_total',array(), sprintf('%0.2f лв.', $nSumTotal*1.2));
			
			$oResponse->setField('service_name','Услуга','Сортирай по услуга');
			
			if($sViewType == 'detail') {
				$oResponse->setField('month','За месец','Сортирай по месец',NULL,NULL,NULL,array('DATA_FORMAT' => DF_MONTH));;
			}
			
			$oResponse->setField('quantity','Количество','Сортирай по количество',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_NUMBER));
			$oResponse->setField('measure','Мерна единица','Сортирай по мерна единица');
			$oResponse->setField('single_price','Единична цена','Сортирай по единична цена');
			$oResponse->setField('total_sum','Сума','Сортирай по сума');
		}		
		
		public function getFirstPaidMonths($nIDSaleDoc,$sIDs = '') {
			
			if($this->isValidID($nIDSaleDoc)) {
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr($nIDSaleDoc,0,6);
			} else {
				throw new Exception("Невалидно id");
			}
			
			$sQuery = "
				SELECT
					id_service,
					id_object,
					MIN(`month`) as first_paid_month
				FROM {$sTableName}
				WHERE id_sale_doc = {$nIDSaleDoc}
			";	
			
			if(!empty($sIDs)) {
				$sQuery .= " AND id IN ({$sIDs})\n";
			}
			
			$sQuery .= "
				GROUP BY id_service
			";
			
			return $this->select2($sQuery);
			
		}
		
		public function getObjectsIDs($nIDSaleDoc) {
			
			$sTableName = PREFIX_SALES_DOCS_ROWS.substr($nIDSaleDoc,0,6);
			
			$sQuery = "
				SELECT 
					id_object
				FROM {$sTableName} 
				WHERE id_sale_doc = {$nIDSaleDoc}
				GROUP BY id_object
			";
			
			return $this->select2($sQuery);
		}
		
		public function getByIDSaleDoc($nIDSaleDoc) {
			$sTableName = PREFIX_SALES_DOCS_ROWS.substr($nIDSaleDoc, 0, 6);
			
			$sQuery = "
				SELECT
					*
				FROM {$sTableName}
				WHERE id_sale_doc = {$nIDSaleDoc}
			";
			
			return $this->select2($sQuery);			
		}
		
		/**
		 * Връща редовете на конкретен документ, както и work_flow_acc кода на офиса, за който се отнася реда
		 * @author Румен Пенчев
		 * 
		 * @param int $nIDSaleDoc - ID на документа за продажба (приходен)
		 * @return array масив със записи
		 */
		public function getByIDSaleDocOffice($nIDSaleDoc) {
			global $db_name_finance;
			global $db_name_sod;

			$sTableName = PREFIX_SALES_DOCS_ROWS.substr($nIDSaleDoc, 0, 6);
			$sCurrentMonth = substr($nIDSaleDoc, 0, 4);

			$sQuery = "
				SELECT
					sd.*,
					IF( ns.is_stock = 1 ,
 						 o.work_flow_acc_tech,
						 IF( DATE_FORMAT( sd.month, '%Y' ) = '$sCurrentMonth' ,
							o.work_flow_acc ,
							o.work_flow_acc_future
						 )
					)
					as work_flow_acc,
					o.work_flow_acc_paydesk as work_flow_acc_paydesk
				FROM {$db_name_finance}.{$sTableName} sd
				LEFT JOIN sod.offices o ON sd.id_office = o.id
				LEFT JOIN finance.nomenclatures_services ns ON ns.id = sd.id_service
				WHERE (id_sale_doc = {$nIDSaleDoc}) and (sd.id_office = o.id)
			";
			return $this->select2($sQuery);
		}
		
		public function sumDDS($sMonth,$nIDOffice) {
			
			$sTableName = PREFIX_SALES_DOCS_ROWS.$sMonth;
			$sTableNameJoin = PREFIX_SALES_DOCS.$sMonth;
			
			$sQuery = "
				SELECT 
					SUM(sdr.total_sum*5/6) AS dds
				FROM {$sTableName} sdr
				LEFT JOIN {$sTableNameJoin} sd ON sd.id = sdr.id_sale_doc
				WHERE sdr.id_office = {$nIDOffice}
					AND sd.to_arc = 0
					AND sd.doc_status = 'final'
				GROUP BY sdr.id_office 
			";
			
			return $this->selectOne2($sQuery);
		}
		
		public function getSaleDocRows($nIDSaleDoc,$sViewType) {
			
			$sQuery = $this->prepareReportQuery($nIDSaleDoc,$sViewType);
			
			return $this->select2($sQuery);
		}
		
		
		
		/**
		 * Функцията връща записа с ДДС към конкретен документ
		 * 
		 * @author Павел Петров
		 * @name getDDSByDoc
		 * 
		 * @param int $nID - ID на документа за продажба (приходен)
		 * @return array масив със записа за ДДС-то
		 */
        public function getDDSByDoc( $nID, $bForBudget = false ) {
            global $db_name_finance, $db_name_sod;

            if ( $this->isValidID($nID) ) {
                $sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
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
					bd.month as month,
					bd.id_service,
					bd.service_name,
					bd.total_sum as total_sum,
					bd.paid_sum as paid_sum,
					IF ( bd.total_sum = bd.paid_sum, 1, 0 ) as payed,
					bd.is_dds
				FROM {$db_name_finance}.{$sTableName} bd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = bd.id_office AND o.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				WHERE bd.id_sale_doc = {$nID}
			";

            if ( $bForBudget ) {
                $sQuery .= " AND bd.is_dds = 1 ";
            } else {
                $sQuery .= " AND bd.is_dds != 0 ";
            }

            $sQuery .= " LIMIT 1 ";

            return $this->select2($sQuery);
        }
		
		
		/**
		 * Функцията връща подробен опис на документа за продажба без записите за ДДС
		 * 
		 * @author Павел Петров
		 * @name getRowsByDoc
		 * 
		 * @param int $nID - ID на документа за продажба (приходен)
		 * @return array масив с описа на документа
		 */			
		public function getRowsByDoc( $nID ) {
			global $db_name_finance, $db_name_sod;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
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
					bd.id_schet,
					f.name as firm_name,
					bd.id_object,
					CONCAT('[', obj.num, '] ', obj.invoice_name) as object_name,
					bd.object_name as obj_name,
					bd.id_duty_row as id_duty,
					bd.month as month,
					bd.id_service,
					bd.service_name,
					bd.single_price as single_price,
					bd.quantity as quantity,
					bd.total_sum as total_sum,
					bd.paid_sum as paid_sum,
					bd.type as type,
					bd.is_dds,
					IF ( ABS(bd.total_sum - bd.paid_sum) < '0.01', 1, 0 ) as payed
				FROM {$db_name_finance}.{$sTableName} bd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = bd.id_office AND o.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.objects obj ON ( obj.id = bd.id_object AND obj.id IS NOT NULL )
				WHERE bd.id_sale_doc = {$nID}
					AND bd.is_dds != 1
			";
			
			return $this->select2($sQuery);			
		}				
		
		/**
		 * Функцията избраните редове от опис на документ + ДДС
		 * 
		 * @author Павел Петров
		 * @name getByIDsDDS
		 * 
		 * @param int $nID - ID на документа
		 * @param array $aData - ID на редове от опис на документ
		 * @return array масив с описа на документа
		 */			
		public function getByIDsDDS( $nID, $aData ) {
			global $db_name_finance, $db_name_sod;
			
			if ( empty($aData) ) {
				return array();
			}
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				//throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
				return array();
			}	
			
			$sRows	= implode(",", $aData);	
			
			$sQuery = "
				SELECT
					bd.id,
					bd.id_office,
					o.name as office_name,
					o.id_firm,
					bd.id_service,
					bd.id_schet,
					bd.id_schet_row,
					f.name as firm_name,
					bd.id_object,
					CONCAT('[', obj.num, '] ', obj.invoice_name) as object_name,
					bd.object_name as obj_name,
					bd.id_duty_row as id_duty,
					bd.month as month,
					bd.id_service,
					bd.service_name,
					bd.single_price as single_price,
					bd.quantity as quantity,
					bd.total_sum as total_sum,
					bd.paid_sum as paid_sum,
					bd.type as type,
					bd.is_dds as is_dds,
					IF ( bd.total_sum = bd.paid_sum, 1, 0 ) as payed
				FROM {$db_name_finance}.{$sTableName} bd
				LEFT JOIN {$db_name_sod}.offices o ON ( o.id = bd.id_office AND o.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND f.to_arc = 0 )
				LEFT JOIN {$db_name_sod}.objects obj ON ( obj.id = bd.id_object AND obj.id IS NOT NULL )
				WHERE ( bd.id_sale_doc = '{$nID}' AND bd.is_dds = 1 ) OR bd.id IN ({$sRows}) 
			";
			
			return $this->select2($sQuery);			
		}			
		
		/**
		 * Функцията връща наличните ордери на основа на документ 
		 * 
		 * @author Павел Петров
		 * @name getOrdersByDoc
		 * 
		 * @param int $nID - ID на документа за продажба (приходен)
		 * @return array масив с ордерите към документа
		 */			
		public function getOrdersByDoc( $nID ) {
			global $db_name_finance, $db_finance, $db_name_personnel;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				//throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
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
	  		$sTime3 = date( "Ym", mktime(0, 0, 0, substr( $nID, 4, 2 )-2, 1, substr( $nID, 0, 4 )) );
	  		$sTime4 = date( "Ym", mktime(0, 0, 0, substr( $nID, 4, 2 )+2, 1, substr( $nID, 0, 4 )) );
	  		$sTime5 = date( "Ym", mktime(0, 0, 0, substr( $nID, 4, 2 )-3, 1, substr( $nID, 0, 4 )) );
	  		$sTime6 = date( "Ym", mktime(0, 0, 0, substr( $nID, 4, 2 )+3, 1, substr( $nID, 0, 4 )) );
	  			
	  		$aTableWatch = array();
	  		
	  		// Таблици, обхващащи период 3 месеца преди и след месеца на конкретния ордер:
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime5;	
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime3;	
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime1;	
	  		$aTableWatch[] = PREFIX_ORDERS.substr( $nID, 0, 6 );	
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime2;	
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime4;	
	  		$aTableWatch[] = PREFIX_ORDERS.$sTime6;	

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
						ba.name_account as smetka,
						o.order_status,
						IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as sum,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
					FROM {$db_name_finance}.{$sTableName} o
					LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
					LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
					WHERE o.doc_id = '{$nID}'
						AND o.doc_type = 'sale'
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
								ba.name_account as smetka,
								o.order_status,
								IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as sum,
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
							FROM {$db_name_finance}.{$sTableName} o
							LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
							LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
							WHERE o.doc_id = '{$nID}'
								AND o.doc_type = 'sale'
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
							ba.name_account as smetka,
							o.order_status,
							IF ( o.order_type = 'expense', o.order_sum * -1, o.order_sum ) as sum,
							CONCAT_WS( ' ', p.fname, p.mname, p.lname ) as user
						FROM {$db_name_finance}.{$sTableName} o
						LEFT JOIN {$db_name_finance}.bank_accounts ba ON (ba.id = o.bank_account_id AND ba.to_arc = 0)
						LEFT JOIN {$db_name_personnel}.personnel p ON (p.id = o.id_person AND p.to_arc = 0)
						WHERE o.doc_id = '{$nID}'
							AND o.doc_type = 'sale'
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
		 * Функцията връща true ако имаме специална номенклатура ДДС
		 * по зададено ID на документ-а.
		 * 
		 * @author Павел Петров
		 * @name checkForDDS
		 * 
		 * @param int $nID - ID на документа 
		 * 
		 * @return bool 
		 */			
		public function checkForDDS($nID) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				//throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
				return false;
			}	

			$sQuery = "SELECT id FROM {$db_name_finance}.{$sTableName} WHERE id_sale_doc = {$nID} AND is_dds = 2 ";
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
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return 0;
			}	

			$sQuery = "
				SELECT 
					SUM(total_sum - paid_sum) as unpaid_sum 
				FROM {$db_name_finance}.{$sTableName} 
				WHERE id_sale_doc = {$nID} 
			";
			
			$result = $this->selectOne2($sQuery);	
			
			return !empty($result) ? $result : 0;	
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
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nIDDoc, 0, 6 );
			} else {
				return array();
			}	

			$sQuery = "SELECT id, id_schet, id_schet_row FROM {$db_name_finance}.{$sTableName} WHERE id_sale_doc = {$nIDDoc} AND id_schet_row > 0 ";
			$result = $this->select2($sQuery);	

			return !empty($result) ? $result : array();	
		}	
		
		
		
			/**
		 * Функцията връща true ако имаме номенклатура по ТРАНСФЕР
		 * по зададено ID на документ-а.
		 * 
		 * @author Павел Петров
		 * @name checkForTransfer
		 * 
		 * @param int $nID - ID на документа 
		 * 
		 * @return bool 
		 */			
		public function checkForTransfer($nID, $rb = 0) {
			global $db_name_finance, $db_finance;
			
			if ( $this->isValidID($nID) ) {
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
			} else {
				return false;
			}	

			$sQuery = "
				SELECT 
					ns.id 
				FROM {$db_name_finance}.{$sTableName} s
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON (s.id_service > 0 AND ns.id = s.id_service AND ns.to_arc = 0 )
				WHERE s.id_sale_doc = {$nID} 
			";
			
			if ( $rb == 1 ) {
				$sQuery .= " AND ns.for_transfer = 0 ";
			} else {
				$sQuery .= " AND ns.for_transfer = 1 ";
			}
			
			$sQuery .= " LIMIT 1 ";			
			
			$result = $this->selectOne2($sQuery);	
			
			return !empty($result) ? $result : false;	
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
				$sTableName = PREFIX_SALES_DOCS_ROWS.substr( $nID, 0, 6 );
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
			$aTables	= SQL_get_tables($db_finance, "sales_docs_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "sales_docs_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		
			

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				
				$sQuery = "
					( SELECT 
						DISTINCT srw.id_office, 
						IF ( srw.is_dds > 0, -1, IF (na.id > 1, na.id, -3) ) as id_nomenclature, 
						srw.is_dds
					FROM {$db_name_finance}.{$sTableName} srw
					LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = srw.id_service )
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = na.id_group )
					LEFT JOIN {$db_name_sod}.offices o ON ( o.id = srw.id_office AND srw.id_office > 0 )
					WHERE srw.id_office > 0
						AND DATE_FORMAT(srw.month, '%Y-%m') = '{$sPeriod}'
						AND srw.paid_sum > 0	
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
			$aTables	= SQL_get_tables($db_finance, "sales_docs_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "sales_docs_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				$sTable = "sales_docs_".substr($sTableName, -6);
				
				$sQuery = "
					( SELECT 
						IF ( srw.is_dds > 0, 13, IF (na.id_group > 1, na.id_group, -3) ) as id,
						IF ( srw.is_dds > 0, 'Други', IF (na.id > 1, ng.name, 'Невъведена') ) as gname,
						IF ( srw.is_dds > 0, -1, IF (na.id > 1, na.id, -3) ) as id_nomenclature,
						SUM(srw.paid_sum) as price,
						IF ( srw.is_dds > 0, 'ДДС', IF (na.id > 1, na.name, 'Невъведена') ) as nomenclature,
						srw.is_dds
					FROM {$db_name_finance}.{$sTableName} srw
					LEFT JOIN {$db_name_finance}.{$sTable} sr ON ( srw.id_sale_doc = sr.id )
					LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = srw.id_service )
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = na.id_group )
					WHERE srw.id_office > 0
						AND DATE_FORMAT(srw.month, '%Y-%m') = '{$sPeriod}'
						AND srw.paid_sum != 0	
						AND sr.doc_status = 'final'
				";
				
				if ( !empty($nIDOffice) ) {
					$sQuery 	.= " AND srw.id_office = {$nIDOffice} ";
				} elseif ( !empty($nIDFirm) ) {
					$sOffice	.= $oOffice->getIdsByFirm($nIDFirm);
					
					if ( !empty($sOffice) ) {
						$sQuery .= " AND srw.id_office IN ({$sOffice}) ";
					}
				}			
				
				$sQuery .= " GROUP BY ns.id_nomenclature_earning, srw.is_dds ";
				
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
		public function getTotalsByNom2( $sPeriod, $nIDFirm = 0, $nIDOffice = 0, $sFilter = "" ) {
			global $db_finance, $db_name_finance;
			
			$aTotal		= array();
			$sOffice	= "";
			$aPeriod	= explode("-", $sPeriod);
			$oOffice	= new DBOffices();
			$aTables	= array();
			$sQuery 	= "";
			$br 		= 0;
			$aTables	= SQL_get_tables($db_finance, "orders_rows_", "______", "ASC");
			
			foreach ( $aTables as $key => $aVal ) {
				if ( $aVal == "orders_rows_origin" ) {
					unset($aTables[$key]);
				}
			}	

			unset($aVal);		

			foreach ( $aTables as $sTableName ) {
				$aData 	= array();
				$sTable = "orders_".substr($sTableName, -6);
				
				$sQuery = "
					( SELECT 
						IF ( srw.is_dds > 0, 13, IF (na.id_group > 1, na.id_group, -3) ) as id,
						IF ( srw.is_dds > 0, 'Други', IF (na.id > 1, ng.name, 'Невъведена') ) as gname,
						IF ( srw.is_dds > 0, -1, IF (na.id > 1, na.id, -3) ) as id_nomenclature,
						SUM(srw.paid_sum) as price,
						IF ( srw.is_dds > 0, 'ДДС', IF (na.id > 1, na.name, 'Невъведена') ) as nomenclature,
						srw.is_dds
					FROM {$db_name_finance}.{$sTableName} srw
					LEFT JOIN {$db_name_finance}.{$sTable} sr ON ( srw.id_order = sr.id )
					LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = srw.id_service )
					LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning )
					LEFT JOIN {$db_name_finance}.nomenclatures_groups ng ON ( ng.id = na.id_group )
					WHERE srw.id_office > 0
						AND DATE_FORMAT(srw.month, '%Y-%m') = '{$sPeriod}'
						AND srw.paid_sum != 0	
						AND sr.order_type = 'earning'
						AND sr.order_status = 'active'
				";
				
				if ( !empty($nIDOffice) ) {
					$sQuery 	.= " AND srw.id_office = {$nIDOffice} ";
				} elseif ( !empty($nIDFirm) ) {
					$sOffice	.= $oOffice->getIdsByFirm($nIDFirm);
					
					if ( !empty($sOffice) ) {
						$sQuery .= " AND srw.id_office IN ({$sOffice}) ";
					}
				}			
				
				$sQuery .= " GROUP BY ns.id_nomenclature_earning, srw.is_dds ";
				
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

        function getPaidObjectsIds($sTableSuffix) {

            global $db_name_sod;

            $dds = isset($_SESSION['system']['dds']) ? (float) $_SESSION['system']['dds'] : 20 ;
            $dds = 1 + $dds/100;

            $sQuery = "
				SELECT
					sdr.id_object,
					SUM(sdr.total_sum * IF(sdr.is_dds = 1,1,{$dds}))  as paid_sum,
					SUM(os.total_sum) AS month_tax
				FROM sales_docs_rows_{$sTableSuffix} sdr				
				LEFT JOIN {$db_name_sod}.objects_services os ON os.id_object = sdr.id_object
				WHERE sdr.`type` = 'month' AND
					sdr.id_object > 0
				GROUP BY sdr.id_object	
				HAVING paid_sum >= month_tax
			";

            $oRs = $this->_oDB->Execute($sQuery);

            $aData = !$oRs->EOF ? $oRs->getAssoc() : array();

            if(!empty($aData)) {
                $aData = array_keys($aData);
            }
            return $aData;
        }


        public function checkForCredit($nIDSaleDoc) {
            global $db_name_finance;

            if($this->isValidID($nIDSaleDoc)) {
                $sYearMonth = substr($nIDSaleDoc,0,6);
            } else {
                return false;
            }

            $sTable = PREFIX_SALES_DOCS_ROWS.$sYearMonth;

            $sQuery = "
				SELECT
					sdr.id
				FROM {$db_name_finance}.{$sTable} sdr
				WHERE sdr.id_sale_doc = {$nIDSaleDoc}
					AND sdr.is_dds = 0
					AND sdr.type = 'credit'
			";

            $aData = $this->select2($sQuery);

            return count($aData) > 0;
        }
	}

?>