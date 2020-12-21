<?php
 
	class DBObjectServices
		extends DBBase2 {
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, "objects_services");
		}	
		
		public function getService($nID) {
			global $db_name_sod, $db_name_finance, $db_name_storage;
			/**/
			$sQuery = "
				SELECT 
					os.id,
					os.id_object, 
					IF ( LENGTH(obj.invoice_name) > 0, obj.invoice_name, obj.name ) as oname,
					IF ( os.id_office > 0, os.id_office, obj.id_office ) as id_office,
					os.id_service,
					os.service_name,
					os.quantity,
					os.single_price,
					os.total_sum,
					m.code AS measure_code,
					na.id_schet as id_schet,
					ns.id_nomenclature_earning as id_earning
				FROM {$db_name_sod}.objects_services os
				LEFT JOIN {$db_name_sod}.objects obj ON obj.id = os.id_object
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ns.id = os.id_service
				LEFT JOIN {$db_name_finance}.nomenclatures_earnings na ON ( na.id = ns.id_nomenclature_earning AND na.to_arc = 0 )
				LEFT JOIN {$db_name_storage}.measures m ON m.id = ns.id_measure
				WHERE os.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
			
		}
		
		public function getRow($aParams) {
			
			$nIDService = isset($aParams['id_service']) ? $aParams['id_service'] : 0;
			$nIDObject = isset($aParams['id_object']) ? $aParams['id_object'] : 0;
			
			if(empty($nIDService)) {
				throw new Exception("Няма стойност на id на услуга");
			}
			if(empty($nIDObject)) {
				throw new Exception("Няма стойност за id на обект");
			}
			
			$sQuery = "
				SELECT
					*
				FROM objects_services
				WHERE to_arc = 0
					AND id_object = {$nIDObject}
					AND id_service = {$nIDService}
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getReportSales(DBResponse $oResponse,$aParams) {
			
			global $db_name_finance,$db_sod;
			
			$sSortField = isset($aParams['sfield']) ? $aParams['sfield'] : 'month';
			$sSortType = isset($aParams['stype']) ? $aParams['stype'] : DBAPI_SORT_ASC;
			
			$sSortType = $sSortType == DBAPI_SORT_ASC ? SORT_ASC : SORT_DESC;
			
			$sDate = $aParams['dateM'] ? $aParams['dateM'] : '';
			list($m,$y) = explode('.',$sDate);
			$sSearchDate = $y."-".$m."-01";
			
			$nSearchDate2 = mktime(0,0,0,$m+1,1,$y);
			$sSearchDate2 = date("Y-m-01",$nSearchDate2);
			
			
			$mname = array();
			$mname['01'] = 'Януари';
			$mname['02'] = 'Февруари';
			$mname['03'] = 'Март';
			$mname['04'] = 'Април';
			$mname['05'] = 'Май';
			$mname['06'] = 'Юни';
			$mname['07'] = 'Юли';
			$mname['08'] = 'Август';
			$mname['09'] = 'Септември';
			$mname['10'] = 'Октомври';
			$mname['11'] = 'Ноември';
			$mname['12'] = 'Декември';
			
			
			// Задача ЗА МЕСЕЧНИТЕ ЗАДЪЛЖЕНИЯ НА КЛИЕНТА
			
			$sQuery = "
				SELECT
					os.id,
					o.num AS object_num,
					IF ( LENGTH(o.invoice_name) > 0, o.invoice_name, o.name ) AS object_name,
					ns.code AS service_code,
					IF(os.service_name != '', os.service_name, ns.name) AS service_name,
					#ns.name AS service_name,
					os.single_price,
					os.quantity,
					os.total_sum,
					os.start_date,
					os.last_paid,
					cl.invoice_payment
				FROM objects_services os
				LEFT JOIN objects o ON o.id = os.id_object
				LEFT JOIN clients_objects co ON co.id_object = os.id_object AND co.to_arc = 0
				LEFT JOIN clients cl ON ( cl.id = co.id_client )
				LEFT JOIN statuses s ON ( s.id = o.id_status AND s.to_arc = 0 )
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ns.id = os.id_service
				WHERE 
					os.to_arc = 0
					AND os.last_paid < '{$sSearchDate}' AND os.start_date < '{$sSearchDate2}'
			";
			
			if ( $aParams['search_type'] == 'by_client' ) {
				$sQuery .= " AND co.id_client = {$aParams['client_eik']} \n AND s.payable = 1 \n";
			} else {
				$sQuery .= " AND os.id_object = {$aParams['id_object']} \n";
			}
			
			$rs = $db_sod->Execute($sQuery);
			
			$aData = array();
			$aDataFinal = array();
			$aDataServices = array();
			$aDataSingles = array();
			
			$aData = $rs->getArray();
			
			foreach ($aData as $value) {
				
				if($value['last_paid'] != '0000-00-00') {
					$sDate = $value['last_paid'];
					list($y,$m,$d) = explode("-",$sDate);
					$nNextMonth = mktime(0,0,0,$m+1,$d,$y);
					$sDate = date("Y-m-d",$nNextMonth);
					
				} else {
					$sDate = $value['start_date'];
				}
				
				$aDates = array();
				while($sDate < $sSearchDate2) {
					$aTmp = array();
					$aTmp = $value;

					list($y,$m,$d) = explode("-",$sDate);
					$aTmp['month'] = $mname[$m]." ".$y;
					$aTmp['month_sort'] = $sDate;
					$aTmp['id'] = $aTmp['id'].",".$sDate;
					
					if($sDate == $value['start_date']) {
						list($y,$m,$d) = explode("-",$sDate);
						$nTime = mktime(0,0,0,$m,$d,$y);
						$nMonthDays = date("t",$nTime);
						
						$nPartOfMonth = ($nMonthDays - $d + 1) / $nMonthDays;
						$aTmp['single_price'] *= $nPartOfMonth;
						$aTmp['total_sum'] *= $nPartOfMonth;						
					}
						
					$aDataServices[] = $aTmp;
					$nNextMonth = mktime(0,0,0,$m+1,1,$y);
					$sDate = date('Y-m-d',$nNextMonth);	
				}
			}
			unset($value);
			
			// Задача ЗА ЕДНОКРАТНИ ЗАДЪЛЖЕНИЯ
			
			$sQuery = "
				SELECT
					os.id,
					o.num AS object_num,
					o.name AS object_name,
					ns.code AS service_code,
					IF(os.service_name != '', os.service_name, ns.name) AS service_name,
					#ns.name AS service_name,
					os.single_price,
					os.quantity,
					os.total_sum,
					'' AS last_paid,
					cl.invoice_payment
				FROM objects_singles os
				LEFT JOIN objects o ON o.id = os.id_object
				LEFT JOIN clients_objects co ON co.id_object = os.id_object AND co.to_arc = 0
				LEFT JOIN clients cl ON ( cl.id = co.id_client )
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ns.id = os.id_service
				WHERE 
					os.to_arc = 0
					AND os.paid_date = '0000-00-00'
					AND os.start_date < '{$sSearchDate2}'
			";
			
			if($aParams['search_type'] == 'by_client') {
				$sQuery .= " AND co.id_client = {$aParams['client_eik']} \n";
			} else {
				$sQuery .= " AND os.id_object = {$aParams['id_object']} \n";
			}
			
			$rs = $db_sod->Execute($sQuery);
			
			$aDataSingles = $rs->GetArray();
			
			foreach ($aDataSingles as &$value) {
				$value['month'] = 'Еднократно задължение';
				$value['month_sort'] = '';
				$value['id'] .= ",single";
			}
			unset($value);
			
			$aDataFinal = array_merge($aDataServices,$aDataSingles);
			
			$sSortFieldOrigin = $sSortField;
			if($sSortField == 'month') {
				$sSortField = 'month_sort';
			}
//			APILog::Log(0, $sSortType);
			
			$aDataFinal = array_multi_csort($aDataFinal,$sSortField,$sSortType);
			
			$nSumAll 	= 0;
			$sPay		= "";
			
			foreach ($aDataFinal as $key => &$value) {
				$value['chk'] = 1;
				$nSumAll += $value['total_sum'];
				$oResponse->setDataAttributes($key,'month',array("style" => "text-align:right;"));
				$oResponse->setDataAttributes($key,'chk',array("total_sum" => $value['total_sum']));
				
				$sPay = $value['invoice_payment'];
			}
			
			$sSortField = $sSortFieldOrigin;
			$sSortType = $sSortType == SORT_ASC ? DBAPI_SORT_ASC : DBAPI_SORT_DESC;
			
			$oResponse->setData($aDataFinal);
			$oResponse->setSort($sSortField,$sSortType);
			$oResponse->setPaging(200,200,1);
			
			$oResponse->setFormElement( 'form1',	'sum_all',	array(), sprintf('%0.2f лв.',$nSumAll) );
			
			switch ( $sPay ) {
				case "cash": $sPayOpt = "фактура в брой";
				break;
				
				case "bank": $sPayOpt = "фактура по банка";
				break;

				case "receipt": $sPayOpt = "квитанция в брой";
				break;
			
				default: $sPayOpt = "няма открити обекти";
				break;
			}
			
			$oResponse->setFormElement( "form1", "pay",	array(), $sPayOpt );
			
			$oResponse->setField('chk', '', NULL, NULL, NULL, NULL, array('type' => 'checkbox'));
			$oResponse->setFieldData('chk', 'input', array('type' => 'checkbox','onclick' => 'changePrice(this);'));
			
			$oResponse->setField('month','За месец','Сортирай по месец');
			$oResponse->setField('object_num','Номер','Сортирай по номер на обект',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_NUMBER));
			$oResponse->setField('object_name','Обект','Сортирай по име на обект');
			$oResponse->setField('service_code','Код','Сортирай по код');
			$oResponse->setField('service_name','Услуга','Сортирай по име на услуга');
			$oResponse->setField('single_price','Ед. цена','Сортирай по единична цена',NULL,NULL,NULL,array('DATA_FORMAT'=> DF_CURRENCY));
			$oResponse->setField('quantity','Количество','Сортирай по количество',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_NUMBER));
			$oResponse->setField('total_sum','Сума','Сортирай по сума',NULL,NULL,NULL,array('DATA_FORMAT'=>DF_CURRENCY));
			
			$oResponse->setFieldLink( "object_num", "openObject" );
			$oResponse->setFieldLink( "object_name", "openObject" );
		}
		
		public function getJur($nID) {
			
			$sQuery = "
				SELECT
					f.jur_name,
					f.address,
					f.idn,
					f.idn_dds,
					f.jur_mol,
					f.id_office_dds as dds
				FROM objects_services os
				LEFT JOIN objects o ON o.id = os.id_object
				LEFT JOIN offices off ON off.id = o.id_office
				LEFT JOIN firms f ON f.id = off.id_firm
				WHERE os.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getSumPriceByObject( $nID ) {
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT SUM(total_sum) 
				FROM objects_services 
				WHERE id_object = {$nID} 
					AND to_arc = 0
				LIMIT 1
			";
			
			return $this->selectOne( $sQuery );
		}
		
		public function getSumPriceByObjectRegion( $nID, $nIDOffice ) {
			if ( empty($nID) || !is_numeric($nID) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($nIDOffice) || !is_numeric($nIDOffice) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}			
			
			$sQuery = "
				SELECT 
					SUM(total_sum) 
				FROM objects_services 
				WHERE id_object = {$nID} 
					AND id_office = {$nIDOffice}
					AND to_arc = 0
				LIMIT 1
			";
			
			return $this->selectOne( $sQuery );
		}		
		
		public function getPriceByID( $nID ) {
			global $db_name_sod, $db_name_finance;
			
			if ( empty( $nID ) || !is_numeric( $nID ) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			$sQuery = "
				SELECT 
					os.id,
					os.id_service as service,
					os.id_office as office,
					os.single_price,
					os.service_name,
					os.quantity,
					os.total_sum,
					DATE_FORMAT(os.start_date, '%d.%m.%Y') as start_date,
					ns.name_edit,
					ns.quantity_edit,
					ns.price_edit
				FROM {$db_name_sod}.objects_services os
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service AND ns.to_arc = 0 )
				WHERE os.id = {$nID} 
					AND os.to_arc = 0
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getSingleByID( $nID ) {
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT 
					id,
					id_service as service,
					id_office as office,
					service_name,
					single_price,
					quantity,
					total_sum,
					DATE_FORMAT(start_date, '%d.%m.%Y') as start_date,
					DATE_FORMAT(paid_date, '%m.%Y') as paid_date,
					id_sale_doc as doc_num
				FROM objects_singles 
				WHERE id = {$nID} 
					AND to_arc = 0
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}		
				
		public function getSinglePriceByObject( $nID, $bDDS = 0 ) {
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT SUM(total_sum) 
				FROM objects_singles 
				WHERE id_object = {$nID} 
					AND to_arc = 0
					AND paid_date < '1950-01-01'
				LIMIT 1
				";
			
			return $this->selectOne( $sQuery );
		}
				
		
		public function getReport( $nID, DBResponse $oResponse )	{
			global $db_name_sod, $db_name_finance, $db_name_storage, $db_name_personnel;
			
			
			$right_edit = false;
			
			if (!empty($_SESSION['userdata']['access_right_levels'])) {
				if (in_array('object_taxes_month_obligations_edit', $_SESSION['userdata']['access_right_levels'])) {
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT
					 os.id as _id,
					 os.id,
					 os.single_price ,
				     os.service_name as code,
				     m.description as measure,
				     os.quantity,
				     os.total_sum,
				  os.total_sum / 1.2  AS total_sum_woDDS,
				     IF ( os.start_date > os.last_paid, UNIX_TIMESTAMP(os.start_date), UNIX_TIMESTAMP(os.last_paid) ) as last_paid,
				     IF ( os.start_date > os.last_paid, UNIX_TIMESTAMP(os.start_date), UNIX_TIMESTAMP(os.last_paid) ) as unpaid, 
				     IF ( os.start_date > os.last_paid, UNIX_TIMESTAMP(os.start_date), UNIX_TIMESTAMP(os.last_paid) ) as paid_date,
				     IF ( os.start_date > os.last_paid, 1, 0 ) as start,
				     IF
				     (
				     	os.real_paid != '0000-00-00',
				     	DATE_FORMAT( os.real_paid, '%m.%Y' ),
				     	''
				     ) AS real_paid,
					 CONCAT(CONCAT_WS(' ', p.fname, p.mname, p.lname), ' [', DATE_FORMAT(os.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM objects_services os
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service AND ns.to_arc = 0 )
				LEFT JOIN {$db_name_storage}.measures m ON ( m.id = ns.id_measure )
				LEFT JOIN {$db_name_personnel}.personnel p ON ( p.id = os.updated_user )
				WHERE os.id_object = {$nID}
					AND os.to_arc = 0
				
			";
			
			//DATE_FORMAT(os.last_paid, '%m.%Y') as last_paid,
			//$sQuery .= " GROUP BY os.id ";
			
			$this->getResult($sQuery, "code", DBAPI_SORT_ASC, $oResponse);
		
			
			foreach ( $oResponse->oResult->aData as $key => &$aRow ) {
				if ( isset($aRow['last_paid']) && !empty($aRow['last_paid']) ) {
					if ( date("Y", $aRow['last_paid']) > 2000 ) {
						if ( $aRow['start'] == 1 ) {
							$aRow['last_paid'] = date("m.Y", mktime(0, 0, 0, date("m", $aRow['last_paid']) -1, date("d", $aRow['last_paid']), date("Y", $aRow['last_paid'])));
						} else {
							$aRow['last_paid'] = date("m.Y", mktime(0, 0, 0, date("m", $aRow['last_paid']), date("d", $aRow['last_paid']), date("Y", $aRow['last_paid'])));
						}
					} else {
						$aRow['last_paid'] = "";
					}
				} else {
					$aRow['last_paid'] = "";
				}
								
				$oResponse->setDataAttributes( $key, 'quantity', 	 array("style" => "width: 40px; text-align: right;") );
				$oResponse->setDataAttributes( $key, 'single_price', array("style" => "width: 60px; text-align: right;") );
				$oResponse->setDataAttributes( $key, 'total_sum',	 array("style" => "width: 60px; text-align: right;") );
				$oResponse->setDataAttributes( $key, 'total_sum_woDDS',	 array("style" => "width: 100px; text-align: right;") );					
				$oResponse->setDataAttributes( $key, 'updated_user', array("style" => "text-align: center; width: 30px;") );
				$oResponse->setDataAttributes( $key, 'last_paid', 	 array("style" => "text-align: center; width: 60px;") );
				$oResponse->setDataAttributes( $key, 'real_paid', 	 array("style" => "text-align: center; width: 60px;") );
				
				$aRow['total_sum_woDDS']	= sprintf( "%0.2f", $aRow['total_sum_woDDS']);
				 
				
			}			
			
			$oResponse->setField("code", "услуга", "Сортирай по услуга");
			$oResponse->setField("single_price", "ед. цена", "Сортирай по единична цена");
			$oResponse->setField("measure", "мярка", "Сортирай по мярка");
			$oResponse->setField("quantity", "кол.", "Сортирай по количество");
			$oResponse->setField("total_sum", "сума", "Сортирай по сума", null, null, null, array( "style" => "font-weight: bold;"));
			$oResponse->setField("total_sum_woDDS", "сума (без ДДС)", "Сортирай");
			$oResponse->setField("last_paid", "падеж", "Сортирай по падеж");
			$oResponse->setField("real_paid", "платен", "Сортирай по реално платен месец");
			$oResponse->setField("updated_user", "...", "Последна редакция", "images/dots.gif" );
			
			if ($right_edit) {
				$oResponse->setField( 'id', "", "", "images/edit.gif", "editService", "");
				$oResponse->setField( "_id", "", "", "images/cancel.gif", "delService", "");
			}
		}		
			
	
		public function getReport2( $nID, DBResponse $oResponse )	{
			global $db_name_sod, $db_name_finance, $db_name_storage, $db_name_personnel;
			
			$right_edit = false;
			
			if (!empty($_SESSION['userdata']['access_right_levels'])) {
				if (in_array('object_taxes_single_obligations_edit', $_SESSION['userdata']['access_right_levels'])) {
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					 os.id as _id,
					 os.id,
					 os.single_price,
				     os.service_name as code,
				     m.description as measure,
				     os.quantity,
				     os.total_sum,
				 os.total_sum / 1.2    AS total_sum_woDDS,
				     DATE_FORMAT(os.paid_date, '%m.%Y') as last_paid,
				     IF( os.paid_date = '0000-00-00', os.start_date, os.paid_date ) AS paid_date,
				     IF( os.paid_date = '0000-00-00', 1, 0 ) AS unpaid,
				     os.id_sale_doc as doc_num,
					 CONCAT(CONCAT_WS(' ', p.fname, p.mname, p.lname), ' [', DATE_FORMAT(os.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM objects_singles os
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ( ns.id = os.id_service AND ns.to_arc = 0 )
				LEFT JOIN {$db_name_storage}.measures m ON ( m.id = ns.id_measure )
				LEFT JOIN {$db_name_personnel}.personnel p ON ( p.id = os.updated_user )
				WHERE os.id_object = {$nID}
					AND os.to_arc = 0				
			";
				 
			//$sQuery .= " GROUP BY os.id ";
			
			$this->getResult($sQuery, "unpaid DESC, paid_date", DBAPI_SORT_DESC, $oResponse);
			

			foreach ( $oResponse->oResult->aData as $key => &$aRow ) {
				
				if ( isset($aRow['doc_num']) && is_numeric($aRow['doc_num']) ) {
					$aRow['doc_num'] = $this->getDocNumById($aRow['doc_num']);
				}
				
				if( $aRow['unpaid'] == "1" )
				{
					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "color: FF0000;" ) );
				}
				
				if ( isset($aRow['last_paid']) && $aRow['last_paid'] == '00.0000' ) {
					$aRow['last_paid'] = "";
				}
				$aRow['total_sum_woDDS']	= sprintf( "%0.2f лв.", $aRow['total_sum_woDDS']);
				$oResponse->setDataAttributes( $key, 'quantity', 	 '' );
				$oResponse->setDataAttributes( $key, 'single_price', '' );
				$oResponse->setDataAttributes( $key, 'total_sum',	 '' );
				 $oResponse->setDataAttributes( $key, 'total_sum_woDDS',	 '' );
				
				
				$oResponse->setDataAttributes( $key, 'doc_num',	 	 '' );
				$oResponse->setDataAttributes( $key, 'updated_user', '' );
				$oResponse->setDataAttributes( $key, 'last_paid', 	 '' );
				
				
				$aRow['total_sum_woDDS']	= sprintf( "%0.2f", $aRow['total_sum_woDDS']);		
			}			
			
			$oResponse->setField("code", "услуга", "Сортирай по услуга");
			$oResponse->setField("single_price", "ед. цена ", "Сортирай по единична цена");
			$oResponse->setField("measure", "мярка", "Сортирай по мярка");
			$oResponse->setField("quantity", "кол.", "Сортирай по количество");
			$oResponse->setField("total_sum", "сума ", "Сортирай по сума");
			$oResponse->setField("total_sum_woDDS", "сума (без ДДС)", "Сортирай по сума" );
			$oResponse->setField("last_paid", "платено", "Сортирай по дата на плащане");
			$oResponse->setField("doc_num", "документ", "Сортирай по номер на документ за плащане");
			$oResponse->setField("updated_user", "...", "Последна редакция", "images/dots.gif" );
			
			if ($right_edit) {
				$oResponse->setField( 'id', "", "", "images/edit.gif", "editService2", "");
				$oResponse->setField( "_id", "", "", "images/cancel.gif", "delService2", "");
			}
		}				
		
		
		public function delete1( $nID ) {
			global $db_sod;

			$nID 	= is_numeric($nID) ? $nID : 0;
			$user 	= !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			
			if ( !empty($nID) ) {
				$db_sod->Execute("UPDATE objects_services set to_arc = 1, updated_time = NOW(), updated_user = '{$user}' WHERE id = {$nID};");
			}
		}					
		
		public function delete2( $nID ) {
			global $db_sod, $db_name_sod;

			$nID 	= is_numeric($nID) ? $nID : 0;
			$user 	= !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			
			if ( !empty($nID) ) {
				
				$sQuery = "
					SELECT id_sale_doc 
					FROM {$db_name_sod}.objects_singles
					WHERE id = {$nID} 
						AND to_arc = 0
					LIMIT 1
				";
	
				$nIDDoc = $this->selectOne( $sQuery );
				$nNumDoc = $this->getDocNumById($nIDDoc);
				
				if ( !empty($nNumDoc) ) {
					throw new Exception("Има активен документ за продажба!", DBAPI_ERR_INVALID_PARAM);
				}
							
				$db_sod->Execute("UPDATE objects_singles set to_arc = 1, updated_time = NOW(), updated_user = '{$user}' WHERE id = {$nID};");
			}
		}					
		
		public function getDocNumById( $nID ) {
			global $db_finance, $db_name_finance;
			
			if ( !empty($nID) && is_numeric($nID) ) {
				$tables = SQL_get_tables($db_finance, 'sales_docs_', '______');
				$table = "sales_docs_".substr($nID, 0, 6);
				
				if ( in_array($table, $tables) ) {
					$sQuery = "
						SELECT doc_num 
						FROM {$db_name_finance}.{$table} 
						WHERE id = {$nID} 
							AND to_arc = 0
						LIMIT 1
					";
					//APILog::Log(0, $sQuery);
					return $this->selectOne( $sQuery );		
				}		
			}
		}		
				
				
		public function getObjectServices( $nIDFirm, $month = 1 ) {
			global $db_name_finance;
			
			$nIDFirm = is_numeric($nIDFirm) ? $nIDFirm : 0;
			
			$sQuery = "
				SELECT 
					ns.id,
					ns.name,
					ns.name_edit,
					ns.quantity_edit,
					ns.price_edit
				FROM {$db_name_finance}.nomenclatures_services_firms sf
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON (ns.id = sf.id_nomenclature_service AND ns.to_arc = 0)
				WHERE ns.is_month = {$month}
					AND sf.id_firm = {$nIDFirm}
				ORDER BY name
			";
			//APILog::Log(0, $sQuery);
			return $this->selectAssoc( $sQuery );
		}		
		
		
		public function updateSingle( $aData ) {
			global $db_sod, $db_name_sod;
				
			$nID = isset($aData['id']) && is_numeric($aData['id']) ? $aData['id'] : 0;
			
			if ( !empty($nID) ) {
				$oRes = $db_sod->Execute("SELECT * FROM objects_singles WHERE id = {$nID};");
				$updateSQL = $db_sod->GetUpdateSQL($oRes, $aData); 
				$oRes = $db_sod->Execute($updateSQL);
			} else {
				$oRes = $db_sod->Execute("SELECT * FROM objects_singles WHERE id = -1;");
				$insertSQL = $db_sod->GetInsertSQL($oRes, $aData); 
				$oRes = $db_sod->Execute($insertSQL);				
			}
		}							

		public function getByID( $nID ) {
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
		
		
		
		public function getClientByName( $sName ) {
			if( empty( $sName ) ) return array();
			
			$sQuery = "
				SELECT * 
				FROM clients 
				WHERE name = {$this->oDB->Quote( $sName )} 
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
		
		function getObjectUnpaidTaxesSum( $nIDObject )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
			{
				return 0;
			}
			
			$sQuery = "
					SELECT
						IF
						(
							os.last_paid != '0000-00-00',
							IF
							(
								UNIX_TIMESTAMP( ( os.start_date - INTERVAL 1 MONTH ) ) > UNIX_TIMESTAMP( os.last_paid ),
								( os.start_date - INTERVAL 1 MONTH ),
								os.last_paid
							),
							os.start_date
						) AS payment_start,
						os.total_sum AS payment_amount
					FROM
						objects_services os
					WHERE
						os.to_arc = 0
						AND os.id_object = {$nIDObject}
			";
			
			$aData = $this->select( $sQuery );
			
			$nTotalTax = 0;
			foreach( $aData as $aValue )
			{
				$aPaymentStart = explode( "-", $aValue['payment_start'] );
				if( empty( $aPaymentStart ) )continue;
				
				$nPSD = (int) $aPaymentStart[2];
				$nPSM = (int) $aPaymentStart[1];
				$nPSY = (int) $aPaymentStart[0];
				
				//DATE EDITTING ( Преобразуване на падежната дата в датата, ОТ която ще се пресметне таксата )
				if( $nPSD == 1 )
				{
					//Ако падежната дата е на 1-ви, смятаме месеца за платен, и започваме плащането от следващия месец.
					$nPSM++;
					if( $nPSM > 12 ) { $nPSM = 1; $nPSY++; }
				}
				//END DATE EDITTING
				
				if( mktime( 0, 0, 0, $nPSM, $nPSD, $nPSY ) >= mktime( 0, 0, 0, ( int ) date( "m" ), ( int ) date( "d" ), ( int ) date( "Y" ) ) )
				{
					continue;
				}
				
				$bSearchFinished = false;
				while( !$bSearchFinished )
				{
					if( $nPSY == date( "Y" ) && $nPSM == date( "m" ) )
					{
						$bSearchFinished = true;
					}
					
					$nDayCount = date( 't', mktime( 0, 0, 0, $nPSM, 1, $nPSY ) );
					$nTaxPerDay = $aValue['payment_amount'] / $nDayCount;
					$nOwedTax = 0;
					
					if( $nPSD != 1 )
					{
						$nOwedTax = $nTaxPerDay * ( ( $nDayCount - $nPSD ) + 1 );
					}
					else
					{
						$nOwedTax = $aValue['payment_amount'];
					}
					
					$nTotalTax += $nOwedTax;
					
					$nPSD = 1;
					$nPSM++;
					if( $nPSM > 12 )
					{
						$nPSM = 1;
						$nPSY++;
					}
				}
			}
			
			return $nTotalTax;
		}
		
		function getLastPaidMonth( $nIDObject )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
			{
				return "";
			}
			
			$sQuery = "
					SELECT
						MIN( last_paid ) AS last_paid
					FROM
						objects_services
					WHERE
						to_arc = 0
						AND id_object = {$nIDObject}
					GROUP BY id_object
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )
			{
				return $aData['last_paid'];
			}
			else return "";
		}
		
		/***************************************************************************************
		** Начало : Финанси -> Обобщена справка ( Резултат : Неплатили / Предплатили Обекти ) **
		***************************************************************************************/
		
		private function getMonthYearString( $sMonthYear )
		{
			$nMonth = (int) substr( $sMonthYear, 0, 2 );
			$nYear = (int) substr( $sMonthYear, 3, 4 );
			$sMonth = "";
			
			switch( $nMonth )
			{
				case 1: $sMonth = "Януари"; break;
				case 2: $sMonth = "Февруари"; break;
				case 3: $sMonth = "Март"; break;
				case 4: $sMonth = "Април"; break;
				case 5: $sMonth = "Май"; break;
				case 6: $sMonth = "Юни"; break;
				case 7: $sMonth = "Юли"; break;
				case 8: $sMonth = "Август"; break;
				case 9: $sMonth = "Септември"; break;
				case 10: $sMonth = "Октомври"; break;
				case 11: $sMonth = "Ноември"; break;
				case 12: $sMonth = "Декември"; break;
			}
			
			return $sMonth . " " . $nYear . " г.";
		}
		
		public function getObjectsUnpaidSince( $sYearMonth, $nIDFirm, $nIDOffice = 0 )
		{
			$aResult = array();
			
			$oDBFirmsObjectStatuses = new DBFirmsObjectStatuses();
			
			//Validation
			$sQueryYearMonth = $sYearMonth . "-01";
			
			if( strlen( $sQueryYearMonth ) != 10 )
			{
				$aResult['count_unpaid'] = $aResult['sum_unpaid'] = 0;
			}
			
			$nIDOffice = ( int ) $nIDOffice;
			//End Validation
			
			//Get Statuses
			$sObjectStatuses = "";
			if( empty( $nIDFirm ) )
			{
				$aObjectStatuses = $oDBFirmsObjectStatuses->getAllFirmObjectStatuses();
				$sObjectStatuses = implode( ",", $aObjectStatuses );
			}
			
			if( empty( $sObjectStatuses ) )$sObjectStatuses = "0";
			//End Get Statuses
			
			$sQuery = "
				SELECT
					COUNT( DISTINCT o.id ) AS count_unpaid,
					SUM( s.total_sum ) / 1.2 AS sum_unpaid #bez dds
				FROM
					objects o
				LEFT JOIN
					objects_services s ON s.id_object = o.id AND s.to_arc = 0
				LEFT JOIN
					offices of ON of.id = o.id_office
				LEFT JOIN
					firms f ON f.id = of.id_firm
				WHERE
					o.id_status IN
					(
						SELECT
							fos.id_status
						FROM
							firms_object_statuses fos
						WHERE
							fos.id_firm = f.id
					)
			";
			
			if( !empty( $nIDFirm ) )
			{
				$sQuery .= "
					AND f.id = {$nIDFirm}
				";
			}
			
			$sQuery .= "
				AND of.id != 0
				AND of.to_arc = 0
				AND f.id != 0
			";
			
			if( !empty( $nIDOffice ) )
			{
				$sQuery .= "
					AND of.id = {$nIDOffice}
				";
			}
			
			$sQuery .= "
					AND IF
					(
						s.real_paid != '0000-00-00',
						( UNIX_TIMESTAMP( s.real_paid ) < UNIX_TIMESTAMP( '{$sQueryYearMonth}' ) ),
						( UNIX_TIMESTAMP( s.start_date - INTERVAL 1 MONTH ) < UNIX_TIMESTAMP( '{$sQueryYearMonth}' ) )
					)
				LIMIT 1
			";
			
			$aResult = $this->selectOnce( $sQuery );
			if( empty( $aResult ) )
			{
				$aResult['count_unpaid'] = $aResult['sum_unpaid'] = 0;
			}
			
			return $aResult;
		}
		
		public function getObjectsPaidTo( $sYearMonth, $nIDFirm )
		{
			$aResult = array();
			
			$oDBFirmsObjectStatuses = new DBFirmsObjectStatuses();
			
			//Validation
			$sQueryYearMonth = $sYearMonth . "-01";
			
			if( strlen( $sQueryYearMonth ) != 10 )
			{
				$aResult['count_paid'] = $aResult['sum_paid'] = 0;
			}
			//End Validation
			
			//Get Statuses
			$sObjectStatuses = "";
			if( empty( $nIDFirm ) )
			{
				$aObjectStatuses = $oDBFirmsObjectStatuses->getAllFirmObjectStatuses();
				$sObjectStatuses = implode( ",", $aObjectStatuses );
			}
			
			if( empty( $sObjectStatuses ) )$sObjectStatuses = "0";
			//End Get Statuses
			
			$sQuery = "
				SELECT
					COUNT( DISTINCT o.id ) AS count_paid,
					SUM( s.total_sum ) / 1.2 AS sum_paid #bez dds
				FROM
					objects o
				LEFT JOIN
					objects_services s ON s.id_object = o.id AND s.to_arc = 0
				LEFT JOIN
					statuses st ON st.id = o.id_status AND st.to_arc = 0
				LEFT JOIN
					offices of ON of.id = o.id_office
				LEFT JOIN
					firms f ON f.id = of.id_firm
				WHERE
			";
			
			if( !empty( $nIDFirm ) )
			{
				$sQuery .= "
					st.id IN
					(
						SELECT
							fos.id_status
						FROM
							firms_object_statuses fos
						WHERE
							fos.id_firm = {$nIDFirm}
					)
					AND f.id = {$nIDFirm}
				";
			}
			else
			{
				$sQuery .= "
					st.id IN ( {$sObjectStatuses} )
				";
			}
			
			$sQuery .= "
					AND IF
					(
						s.real_paid != '0000-00-00',
						( UNIX_TIMESTAMP( s.real_paid ) >= UNIX_TIMESTAMP( '{$sQueryYearMonth}' ) ),
						0
					)
				LIMIT 1
			";
			
			$aResult = $this->selectOnce( $sQuery );
			if( empty( $aResult ) )
			{
				$aResult['count_unpaid'] = $aResult['sum_unpaid'] = 0;
			}
			
			return $aResult;
		}
		
		function getSummaryObjectPaymentReport( DBResponse $oResponse, $aParams )
		{
			//Interval to check ( in months ).
			$nInterval = isset( $aParams['nInterval'] ) ? $aParams['nInterval'] : 6;
			
			//Firm
			$nIDFirm = isset( $aParams['nIDFirm'] ) ? $aParams['nIDFirm'] : 0;
			
			//Get Month and Year
			$sMonthYear = $this->getMonthYearString( date( "m-Y" ) );
			//End Get Month and Year
			
			//Set Titles and Fields
			$oResponse->setTitle( 1, 1, "Неплатили за месец {$sMonthYear}", 		array( "colspan" => "3" ) );
			$oResponse->setTitle( 1, 4, "Предплатили относно месец {$sMonthYear}", 	array( "colspan" => "3" ) );
			
			$oResponse->setField( "count_unpaid", 	"Брой", 		"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "months_unpaid", 	"Месеци", 		"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "sum_unpaid", 	"Сума (лв.)", 	"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			
			$oResponse->setField( "count_paid", 	"Брой", 		"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "months_paid", 	"Месеци", 		"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "sum_paid", 		"Сума (лв.)", 	"", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			//End Set Titles and Fields
			
			//Calculating Periods
			$aData = array();
			
			for( $i = 0; $i < $nInterval; $i++ )
			{
				$nStartPaidPeriod = $i;
				$nStartUnpaidPeriod = -$nInterval + ( $i + 1 );
				
				$sDateUnpaid = date( "m-Y", strtotime( "{$nStartUnpaidPeriod} months" ) );
				$sDatePaid = date( "m-Y", strtotime( "{$nStartPaidPeriod} months" ) );
				
				$aData[$i] = array();
				
				//Unpaid
				$aData[$i]['months_unpaid'] = $this->getMonthYearString( $sDateUnpaid );
				$aData[$i] = array_merge( $aData[$i], $this->getObjectsUnpaidSince( date( "Y-m", strtotime( "{$nStartUnpaidPeriod} months" ) ), $nIDFirm ) );
				//End Unpaid
				
				//Paid
				$aData[$i]['months_paid'] = $this->getMonthYearString( $sDatePaid );
				$aData[$i] = array_merge( $aData[$i], $this->getObjectsPaidTo( date( "Y-m", strtotime( "{$nStartPaidPeriod} months" ) ), $nIDFirm ) );
				//End Paid
			}
			//End Calculating Periods
			
			$oResponse->setData( $aData );
		}
		
		/***************************************************************************************
		** Край : Финанси -> Обобщена справка ( Резултат : Неплатили / Предплатили Обекти )   **
		***************************************************************************************/
		
		/**
		 * @author Павел Петров
		 * @name getPaidSingleByObj
		 * 
		 * Връща най-задната дата, за която имаме 
		 * еднократно плащане към определен обект
		 *
		 * @param unsigned int $nID
		 * @return timestamp paid_date
		 */
		public function getPaidSingleByObj( $nID ) {
			$nID = is_numeric( $nID ) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					MIN( UNIX_TIMESTAMP(paid_date) ) as paid_date
				FROM objects_singles 
				WHERE id_object = {$nID} 
				LIMIT 1
			";
			
			return $this->selectOne( $sQuery );
		}	
		
		/**
		 * @author Павел Петров
		 * @name getPaidServiceByObj
		 * 
		 * Връща най-задната дата, за която имаме 
		 * месечно плащане към определен обект 
		 *
		 * @param unsigned int $nID
		 * @return timestamp paid_date
		 */		
		public function getPaidServiceByObj( $nID ) {
			$nID = is_numeric( $nID ) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					MIN( UNIX_TIMESTAMP(last_paid) ) as paid_date
				FROM objects_services
				WHERE id_object = {$nID} 
				LIMIT 1
			";
			//APILog::Log(0, $sQuery);
			return $this->selectOne( $sQuery );
		}		
		
		/**
		 * По зададен обект и месец връща мсив с данните за текущи МЕСЕЧНИ задължения;
		 *
		 * @author Павел Петров
		 * @name getDutyByObject
		 * 
		 * @param int $nIDObject - ID на обекта, за който търсим задълженията;
		 * @param string $sMonth - ДО кой месец търсим - формат 0000-00-00;
		 * 
		 * @return array - подробни данни за чакащите задължения;
		 */
		public function getDutyByObject($nIDObject, $sMonth) {
			global $db_name_sod, $db_name_finance;
			
			$aMon 	= array();
			$aData	= array();
			$aDuty	= array();
			
			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return array();
			}
			
			$aMon = explode("-", $sMonth);
			
			$dayTo 		= intval($aMon[2]);
			$monthTo 	= intval($aMon[1]);
			$yearTo 	= intval($aMon[0]);			

			if ( empty($sMonth) || ($sMonth == "0000-00-00") || !checkdate($monthTo, $dayTo, $yearTo) ) {
				return array();
			}			
			
			$sQuery = "
				SELECT
					os.id,
					IF ( os.start_date > os.last_paid AND MONTH(os.start_date) != MONTH(os.last_paid), os.start_date, os.last_paid ) AS payment_date,
					IF ( os.start_date > os.last_paid AND MONTH(os.start_date) != MONTH(os.last_paid), 1, 0 ) AS start,					
					os.id_office,
					r.name as region,
					r.id_firm,
					f.name as firm,
					os.id_service,
					os.service_name as name,
					IF ( char_length(o.invoice_name), o.invoice_name, o.name ) as object_name,
					os.single_price,
					os.quantity,
					os.total_sum AS payment_sum
				FROM {$db_name_sod}.objects_services os
				LEFT JOIN {$db_name_sod}.objects o ON o.id = os.id_object
				LEFT JOIN {$db_name_sod}.offices r ON r.id = os.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				WHERE os.to_arc = 0
					AND os.id_object = {$nIDObject}
			";
			//CONCAT('[', o.num, '] ', o.invoice_name) as object_name,
			//IF ( os.last_paid != '0000-00-00', os.last_paid, os.start_date ) AS payment_date,
			//UNIX_TIMESTAMP(os.start_date) as sd,
			//UNIX_TIMESTAMP(os.last_paid) as ld,			
			$aData = $this->select( $sQuery );			
			
			foreach ( $aData as $val ) {
				$aPayMon = array();
				$aPayMon = explode("-", $val['payment_date']);
				
				if ( !isset($aPayMon[2]) ) {
					continue;
				}
				
				$day 		= intval($aPayMon[2]);
				$month 		= intval($aPayMon[1]);
				$year 		= intval($aPayMon[0]);
				$pay_days	= 0;
				
				// Приемаме, че целия месец е платен на първо число АКО старт = 0!
				if ( $val['start'] == 0 ) {
					if ( $day == 1 ) {
						$month++;
					}
					
					if ( $month > 12 ) { 
						$month = 1; 
						$year++; 
					}	
				}			
				
				$i = 0;
				
				while ( mktime(0, 0, 0, $month + $i, 1, $year) <= mktime(0, 0, 0, $monthTo, 1, $yearTo) ) {
					$aTmp		= array();
					
					if ( $day != 1 ) {
						$lastday 	= mktime(0, 0, 0, $month + 1, 0, $year);
						$lastday	= date("d", $lastday);
						$pay_days 	= $lastday - $day;					
						
						$aTmp['id_duty']				= $val['id'];
						$aTmp['id_object'] 				= $nIDObject;
						$aTmp['firm_region']['rcode']	= $val['id_office'];
						$aTmp['firm_region']['region']	= $val['region'];
						$aTmp['firm_region']['fcode']	= $val['id_firm'];
						$aTmp['firm_region']['firm']	= $val['firm'];	
						$aTmp['id_service'] 			= $val['id_service'];
						$aTmp['month'] 					= $val['payment_date'];
						$aTmp['service_name'] 			= $val['name'];
						$aTmp['object_name'] 			= $val['object_name'];
						$aTmp['single_price'] 			= floatval((($pay_days * $val['single_price']) / $lastday) / 1.2);
						$aTmp['quantity'] 				= intval($val['quantity']);
						$aTmp['total_sum'] 				= floatval((($pay_days * $val['payment_sum']) / $lastday) / 1.2);
						$aTmp['payed']					= floatval(0);
						$aTmp['type']					= "month";
						$aTmp['for_payment']			= true;						
					} else {
						$aTmp['id_duty']				= $val['id'];
						$aTmp['id_object'] 				= $nIDObject;
						$aTmp['firm_region']['rcode']	= $val['id_office'];
						$aTmp['firm_region']['region']	= $val['region'];
						$aTmp['firm_region']['fcode']	= $val['id_firm'];
						$aTmp['firm_region']['firm']	= $val['firm'];							
						$aTmp['id_service'] 			= $val['id_service'];
						$aTmp['month'] 					= date("Y-m-d", mktime(0, 0, 0, $month + $i, 1, $year));
						$aTmp['service_name'] 			= $val['name'];
						$aTmp['object_name'] 			= $val['object_name'];
						$aTmp['single_price'] 			= floatval(($val['single_price'] / 1.2));
						$aTmp['quantity'] 				= intval($val['quantity']);
						$aTmp['total_sum'] 				= floatval(($val['payment_sum'] / 1.2));
						$aTmp['payed']					= floatval(0);
						$aTmp['type']					= "month";
						$aTmp['for_payment']			= true;
					}
					
					$aDuty[] 	= $aTmp;
					$day 		= 1;			
					
					$i++;
				}
			}
			
			return $aDuty;
		}
		
		
		/**
		 * @author Павел Петров
		 * @name getFirmServices
		 * 
		 * Групира всички налични услуги по фирми.
		 * Да се използва при комбинирания комбо във флекс! 
		 * 
		 * @return array Всички услуги по фирми!
		 */		
		public function getFirmServices( ) {
			global $db_name_finance, $db_name_sod;
			
			$sQuery = "
				SELECT 
					ns.id as id_service,
					nsf.id_firm, 
					ns.name,
					ns.name_edit,
					ns.quantity_edit,
					ns.price_edit,
					ns.is_month,
					ns.price,
					IF ( ns.is_month = 0, is_default, 0 ) as is_default
				FROM {$db_name_finance}.nomenclatures_services ns
				LEFT JOIN {$db_name_finance}.nomenclatures_services_firms nsf ON nsf.id_nomenclature_service = ns.id
				WHERE ns.to_arc = 0
			";
			
			return $this->select( $sQuery );
		}

		/**
		 * @author Павел Петров
		 * @name getFirmDDSService
		 * 
		 * Взима всички фирми и по избрана структура генерира ДДС запис.
		 * Да се използва при комбинирания комбо във флекс! 
		 * 
		 * @return array ДДС услуга по фирми!
		 */		
		public function getFirmDDSService( ) {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					-1 as id_service,
					f.id as id_firm, 
					'.:: ДДС ::.' name,
					1 as name_edit,
					1 as quantity_edit,
					1 as price_edit,
					0 as is_month,
					0 as price,
					0 as is_default
				FROM {$db_name_sod}.firms f
				WHERE f.to_arc = 0
			";
			
			return $this->select( $sQuery );
		}

		public function getServiceByID( $nID ) {
			global $db_name_sod;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					* 
				FROM {$db_name_sod}.objects_services
				WHERE id = {$nID} 
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}		
	}
	
?>