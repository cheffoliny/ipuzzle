<?php
	class DBObjects2 extends DBBase2 	{
		public function __construct() {
			global $db_telepol;
			//$db_telepol->debug=true;
			parent::__construct($db_telepol, 'objects');
		}
				
		public function insertObject( $aData ) {
				
			$nNum = $aData['num'];
			$sName = trim($aData['name']);
			$sName = iconv('UTF-8', 'cp1251', $sName);
			
			$sQuery = "
				INSERT INTO objects ( num, name )
				VALUES ( $nNum , '{$sName}' )
			";
			$this->oDB->Execute( $sQuery );
		}
	
//		public function getObjectsByNum( $nID ) {
//			$sQuery = "
//				SELECT
//					o.id,
//					o.num,
//					o.name,
//					o.status
//				FROM objects o
//				WHERE o.num = {$nID}
//			";
//			
//			return $this->select($sQuery);
//		}
		
		public function getObjectsByNum(DBResponse $oResponse, $nNum) {
			global $db_name_personnel, $db_name_sod;
			
			$sQuery = "
				SELECT
					o.id_obj as id,
					o.num,
					o.name,
					s.name as status
				FROM objects o
				LEFT JOIN statuses s ON s.id_status = o.id_status
				WHERE 1
					AND o.num = {$nNum}
			";
		
			$aData = $this->select( $sQuery );
			
			foreach ( $aData as &$val ) {
				$val['name'] = iconv('cp1251', 'utf-8', $val['name']);
				$val['status'] = iconv('cp1251', 'utf-8', $val['status']);
			}
			
			$oResponse->setData( $aData );
			$oResponse->setSort("name", "DESC");
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: right; width: 65px;') );				
				//$oResponse->setDataAttributes( $key, 'status', array('style' => 'text-align: right; width: 75px;') );				
			}
			
			APILog::Log(0, $aData);
			
			$oResponse->setField("num", "Номер", "Сортирай по номер");
			$oResponse->setField("name", "Име", "Сортирай по име");
			$oResponse->setField("status", "Статус", "Сортирай по статус");
			$oResponse->setField( '', '', '', 'images/confirm.gif', 'choiceSync', 'Избери');
		}
		
		public function getObjectsByNumObj( $nNum ) {
			
			$sQuery = "
				SELECT
					id_obj,
					num,
				 	name
				FROM objects
				WHERE num = $nNum
			";
			
			return $this->select($sQuery);
		}
		
		public function getObjectById( $nID ) {
			
			$sQuery = "
				SELECT
					o.id_obj,
					o.num,
					o.name,
					o.address,
					o.phone,
					o.tax_num,
					o.bulstat,
					o.price,
					o.time_react,
					f.name AS MOL,
					o.id_status
				FROM objects o
				LEFT JOIN faces f ON f.id_face = o.id_face
				WHERE o.id_obj = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getObjectForContract( $nID ) {
			
			$sQuery = "
				SELECT
					o.id_obj,
					o.num,
					r.name AS region_name,
					o.name,
					o.address,
					o.phone,
					o.tax_num,
					o.bulstat,
					o.price,
					o.single_otg,
					o.yearly_otg,
					o.firm_name,
					o.firm_phone,
					o.mail,
					o.time_react,
					o.tax_num,
					o.bulstat,
					o.address_reg,
					f.name AS face_name
				FROM objects o
				LEFT JOIN regions r ON r.id_region = o.id_region
				LEFT JOIN faces f ON f.id_face = o.id_face
				WHERE o.id_obj = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function setPrice($nID, $nPrice) {
			
			$sQuery = "
				UPDATE 
					objects
				SET price = {$nPrice}
				WHERE id_obj = {$nID}
			";
			
			$this->oDB->Execute( $sQuery );
		}
		
		public function getObjectsWithoutSignals( $aData, DBResponse $oResponse ) {
			// Правена по възможно най-глупавия начин!
			
			$nTime = isset($aData['nTime']) ? $aData['nTime'] : 0;
			$nIDOffice = isset($aData['nIDOffice']) ? $aData['nIDOffice'] : -1;
			$alarm = isset($aData['alarm']) ? explode(",", $aData['alarm']) : array();
			//$restore = isset($aData['restore']) ? explode(",", $aData['restore']) : array();
			$where = "";
			$where2 = "";
			
			$table1 = date("Y_m");
			$table2 = date( "Y_m", mktime(0, 0, 0, date("m"), date("d") - $nTime, date("Y")) );
			
			
			if( empty( $nTime ) || !is_numeric( $nTime ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			foreach ( $alarm as $val ) {
				if ( is_numeric($val) ) {
					//$where .= "\n AND (m.id_sig != {$val} AND (a.status NOT IN (1,2,3,4,5,6,7,8,51,52,55,58) OR a.status IS NULL)) \n";
					$where .= "\n AND ( m.id_sig = {$val} ) \n";
					
					if ( $table1 == $table2 ) {
						$where2 .= "\n AND ( m.id_sig = {$val} AND a.alarm = 1 ) \n";
					} else {
						//$where .= "\n AND ( m.id_sig = {$val} AND (a.alarm != 1 AND a2.alarm != 1) ) \n";
						$where2 .= "\n AND ( m.id_sig = {$val} AND (a.alarm = 1 OR a2.alarm = 1) ) \n";
					}
				}
			}
			

//			foreach ( $restore as $valr ) {
//				if ( is_numeric($valr) ) {
//					//$where .= "\n AND (m.id_sig != {$valr} AND (a.status NOT IN (17,18,18,19,20,21,22,23,24,57,66,59,60,55) OR a.status IS NULL)) \n";
//					$where .= "\n AND ( m.id_sig = {$valr} ) \n";
//
//					if ( $table1 == $table2 ) {
//						$where2 .= "\n AND ( m.id_sig = {$valr} AND a.alarm = 0 ) \n";
//					} else {
//						$where2 .= "\n AND ( m.id_sig = {$valr} AND (a.alarm = 0 OR a2.alarm = 0) ) \n";
//					}
//				}
//			}
			
			// Взимаме обектите със сигнала и статуса, който искаме да изключим
			
			if ( $table1 == $table2 ) { // Периода влиза в областта на една архивна таблица
				
				$sQuery = "
					SELECT 
						o.id_obj as id
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN regions r ON r.id_region = o.id_region
					LEFT JOIN `{$table1}` a ON ( a.id_msg = m.id_msg )					
					WHERE o.id_status != 4
						AND r.telenet_id_office = {$nIDOffice}
						AND m.id_msg IS NOT NULL
						AND ( 1
							{$where2}
						)
						AND UNIX_TIMESTAMP(m.timeAl) < UNIX_TIMESTAMP(NOW()) - ({$nTime} * 86400) 
					GROUP BY o.id_obj
				";
			
			} else {  // Периода влиза в областта на повече от една архивна таблица
			
				$sQuery = "
					SELECT 
						o.id_obj as id
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN regions r ON r.id_region = o.id_region
					LEFT JOIN `{$table2}` a ON ( a.id_msg = m.id_msg )
					LEFT JOIN `{$table1}` a2 ON ( a2.id_msg = m.id_msg )					
					WHERE o.id_status != 4
						AND r.telenet_id_office = {$nIDOffice}
						AND m.id_msg IS NOT NULL
						AND ( 1
							{$where2}
						)
						AND UNIX_TIMESTAMP(m.timeAl) < UNIX_TIMESTAMP(NOW()) - ({$nTime} * 86400) 
					GROUP BY o.id_obj
					
				";
			
			}
			//APILog::Log(0, $sQuery);	
			$aDataId = $this->select( $sQuery );
			$sDataId = "0";
			
			foreach ( $aDataId as $vl ) {
				$sDataId .= empty($sDataId) ? $vl['id'] : ",".$vl['id'];
			}
						
			
			if ( $table1 == $table2 ) { // Периода влиза в областта на една архивна таблица
				
				$sQuery = "
					SELECT 
						o.id_obj as id,
						r.name as place
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN regions r ON r.id_region = o.id_region
					LEFT JOIN `{$table1}` a ON ( a.id_msg = m.id_msg )					
					WHERE o.id_status != 4
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_obj NOT IN ({$sDataId})
						AND m.id_msg IS NOT NULL
						AND ( 1
							{$where}
						)
						AND UNIX_TIMESTAMP(m.timeAl) < UNIX_TIMESTAMP(NOW()) - ({$nTime} * 86400) 
					GROUP BY o.id_obj
				";
			
			} else {  // Периода влиза в областта на повече от една архивна таблица
			
				$sQuery = "
					SELECT 
						o.id_obj as id,
						r.name as place
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN regions r ON r.id_region = o.id_region
					LEFT JOIN `{$table2}` a ON ( a.id_msg = m.id_msg )
					LEFT JOIN `{$table1}` a2 ON ( a2.id_msg = m.id_msg )					
					WHERE o.id_status != 4
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_obj NOT IN ({$sDataId})
						AND m.id_msg IS NOT NULL
						AND ( 1
							{$where}
						)
						AND UNIX_TIMESTAMP(m.timeAl) < UNIX_TIMESTAMP(NOW()) - ({$nTime} * 86400) 
					GROUP BY o.id_obj
					
				";
			
			}
			//APILog::Log(0, $sQuery);	
			$aData = $this->select( $sQuery );
			
			$nRowTotal = count($aData);

			$oResponse->setFormElement('form1', 'nTotalObjects', array(), $nRowTotal);
			$oResponse->setFormElement('form1', 'sTotals', array(), '');
			
			$totals = array();
			foreach ( $aData as &$val ) {
				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
				isset($totals[$val['place']]) ? $totals[$val['place']] += 1 : $totals[$val['place']] = 1;
			}

			foreach ( $totals as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'sTotals', array("value" => $key), $key." => ".$val );
			}					

			$aData = array();					
			
			if ( $table1 == $table2 ) { // Периода влиза в областта на една архивна таблица
				
				$sQuery = "
					SELECT 
						o.id_obj as id,
						o.num,
						o.name,
						r.name as place,
						o.address,
						DATE_FORMAT(m.timeAl, '%d.%m.%Y %H:%i:%s') as timeAl
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN regions r ON r.id_region = o.id_region
					LEFT JOIN `{$table1}` a ON ( a.id_msg = m.id_msg )
					WHERE o.id_status != 4
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_obj NOT IN ({$sDataId})
						AND m.id_msg IS NOT NULL
						AND ( 1
							{$where}
						)
						AND UNIX_TIMESTAMP(m.timeAl) < UNIX_TIMESTAMP(NOW()) - ({$nTime} * 86400) 
					GROUP BY o.id_obj
				";
			
			} else {  // Периода влиза в областта на повече от една архивна таблица
			
				$sQuery = "
					SELECT 
						o.id_obj as id,
						o.num,
						o.name,
						r.name as place,
						o.address,
						DATE_FORMAT(m.timeAl, '%d.%m.%Y %H:%i:%s') as timeAl
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN regions r ON r.id_region = o.id_region
					LEFT JOIN `{$table2}` a ON ( a.id_msg = m.id_msg )
					LEFT JOIN `{$table1}` a2 ON ( a2.id_msg = m.id_msg )
					WHERE o.id_status != 4
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_obj NOT IN ({$sDataId})
						AND m.id_msg IS NOT NULL
						AND ( 1
							{$where}
						)
						AND UNIX_TIMESTAMP(m.timeAl) < UNIX_TIMESTAMP(NOW()) - ({$nTime} * 86400) 
					GROUP BY o.id_obj
				";
			
			}
				//APILog::Log(0, $sQuery);									
			$oParams = Params::getInstance();
			$aParams = Params::getAll();

			$nPage = $oParams->get("current_page", 1);
			
			$sSortField = $oParams->get("sfield", "num");
			$nSortType	= $oParams->get("stype", "DBAPI_SORT_DESC");

			if ( empty($sSortField) ) {
				$sSortField = "num";
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";

			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);

			if ( $bLimited ) {
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}

			$aData = $this->select( $sQuery );
						
			foreach ( $aData as &$val ) {
				$val['name'] = iconv('cp1251', 'utf-8', $val['name']);
				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
				$val['address'] = iconv('cp1251', 'utf-8', $val['address']);
			}
				
			$oResponse->setData( $aData );						

			//APILog::Log(0, ceil($nRowOffset / $nRowCount) + 1);
			
			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}


			//$oResponse->setField('checkbox');
			//$oResponse->setFieldAttributes('checkbox', array('width' => "20px"));
			//$oResponse->setFieldData('checkbox', 'input', array('type' => 'checkbox', 'exception' => ''));
			
			$oResponse->setField('num', "номер", "Сортиране по номер");
			$oResponse->setField('name', "обект", "Сортиране по обект");
			$oResponse->setField('place', "нас. място", "Сортиране по населено място");
			$oResponse->setField('address', "адрес", "Сортиране по адрес");
			$oResponse->setField('timeAl', "време", "Сортиране по време");
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: right; width: 55px;') );
				$oResponse->setDataAttributes( $key, 'timeAl', array('style' => 'text-align: center; width: 150px;') );
			}
		}
		
		/* ********************************************************* 
		 * Дублиране на сигнали
		 * Блах :(
		 ***********************************************************/
		public function getObjectsDublicateSignals( $aData, DBResponse $oResponse ) {
			// Правена по възможно най-глупавия начин!
			
			$nTime		= isset($aData['nTime']) ? $aData['nTime'] : 1;
			$nTimeDublicate = isset($aData['nTimeDublicate']) ? $aData['nTimeDublicate'] : 0;
			$nCount		= isset($aData['nCount']) ? $aData['nCount'] : 0;
			$nIDOffice	= isset($aData['nIDOffice']) ? $aData['nIDOffice'] : -1;
			$alarm = isset($aData['alarm']) ? explode(",", $aData['alarm']) : array();
			$where = "";
			$where2 = "";
			
			$table1 = date("Y_m");
			$table2 = date( "Y_m", mktime(0, 0, 0, date("m"), date("d") - $nTime, date("Y")) );
			
//			APILog::Log(0, $alarm);
//			APILog::Log(0, $table2);
			
			if ( empty( $nTime ) || !is_numeric( $nTime ) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			foreach ( $alarm as $val ) {
				if ( is_numeric($val) ) {
					$where2 .= "\n AND ( m.id_sig = {$val} AND a.alarm = 1 ) \n";
				}
			}
							
			$aData = array();					

			if ( $table1 == $table2 ) { // Периода влиза в областта на една архивна таблица
				$sQuery = "
					SELECT 
						o.id_obj as id,
						o.num,
						o.name,
						r.name as place,
						o.address,
						m.id_sig,
						a.status,
						UNIX_TIMESTAMP(a.msgTime) as msgTime											
					FROM objects o
					LEFT JOIN messages m ON ( o.id_obj = m.id_obj )
					LEFT JOIN `{$table1}` a ON ( a.id_msg = m.id_msg )
					LEFT JOIN regions r ON r.id_region = o.id_region
					WHERE 1
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_status = 1
						AND m.id_msg IS NOT NULL
						AND UNIX_TIMESTAMP( a.msgTime ) > ( UNIX_TIMESTAMP( NOW() ) - ({$nTime} * 86400) ) 
						{$where2}
					ORDER BY o.id_obj, m.id_sig ASC
				";
				//AND o.id_status = 4
			} else {  // Периода влиза в областта на повече от една архивна таблица
				$sQuery = "
					
					( SELECT 
						o.id_obj as id,
						o.num,
						o.name,
						r.name as place,
						o.address,
						m.id_sig,
						a.status,
						UNIX_TIMESTAMP(a.msgTime) as msgTime
					FROM `{$table2}` a
					LEFT JOIN messages m ON m.id_msg = a.id_msg
					LEFT JOIN objects o ON o.id_obj = m.id_obj
					LEFT JOIN regions r ON r.id_region = o.id_region
					WHERE 1
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_status = 1
						AND UNIX_TIMESTAMP( a.msgTime ) > ( UNIX_TIMESTAMP( NOW() ) - ({$nTime} * 86400) ) 
						{$where2}
					ORDER BY o.id_obj, m.id_sig ASC )

					UNION 

					( SELECT 
						o.id_obj as id,
						o.num,
						o.name,
						r.name as place,
						o.address,
						m.id_sig,
						a.status,						
						UNIX_TIMESTAMP(a.msgTime) as msgTime
					FROM `{$table1}` a
					LEFT JOIN messages m ON m.id_msg = a.id_msg
					LEFT JOIN objects o ON o.id_obj = m.id_obj
					LEFT JOIN regions r ON r.id_region = o.id_region
					WHERE 1
						AND r.telenet_id_office = {$nIDOffice}
						AND o.id_status = 1
						AND UNIX_TIMESTAMP( a.msgTime ) > ( UNIX_TIMESTAMP( NOW() ) - ({$nTime} * 86400) ) 
						{$where2}
					ORDER BY o.id_obj, m.id_sig ASC )
				
				";
			}
			//APILog::Log(0, $sQuery);										
			$oParams = Params::getInstance();
			$aParams = Params::getAll();

			$nPage = $oParams->get("current_page", 1);
			
			$sSortField = $oParams->get("sfield", "num");
			$nSortType	= $oParams->get("stype", "DBAPI_SORT_DESC");

			if ( empty($sSortField) ) {
				$sSortField = "num";
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";

			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			//$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);

			if ( $bLimited ) {
				//$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}

			$aData = $this->select( $sQuery );
			
			$tmpArr = array();
			$final = array();
			
			foreach ( $aData as $val ) {
				$val['name'] = iconv('cp1251', 'utf-8', $val['name']);
				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
				$val['address'] = iconv('cp1251', 'utf-8', $val['address']);
				
				if ( !isset($tmpArr[$val['id']]) ) {
					$tmpArr[$val['id']] = $val;
				} else {
					$k = ($val['msgTime'] - $tmpArr[$val['id']]['msgTime']);
					
					if ( (($k > 0) && ($k <= $nTimeDublicate)) && ($val['status'] == $tmpArr[$val['id']]['status']) && ($val['id_sig'] == $tmpArr[$val['id']]['id_sig']) ) {
						$final[$val['num']] = $val;
						unset($tmpArr[$val['id']]);
					} else $tmpArr[$val['id']] = $val;
				}
				
			}
			
			//APILog::Log(0, $final);
			$nRowTotal = count( $final );

			$oResponse->setFormElement('form1', 'nTotalObjects', array(), $nRowTotal);
			$oResponse->setFormElement('form1', 'sTotals', array(), '');
			
			$totals = array();
			foreach ( $final as $val ) {
				isset($totals[$val['place']]) ? $totals[$val['place']] += 1 : $totals[$val['place']] = 1;
			}

			foreach ( $totals as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'sTotals', array("value" => $key), $key." => ".$val );
			}
			
			
			ksort($final);

			//APILog::Log(0, $final);
			if ( $bLimited ) {
				$final_out = array_slice( $final, $nRowOffset, $nRowCount );
				//$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}			
					
			$oResponse->setData( $final_out );			
			
			//APILog::Log(0, $aData);

			//APILog::Log(0, ceil($nRowOffset / $nRowCount) + 1);
			
			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}


			//$oResponse->setField('checkbox');
			//$oResponse->setFieldAttributes('checkbox', array('width' => "20px"));
			//$oResponse->setFieldData('checkbox', 'input', array('type' => 'checkbox', 'exception' => ''));
			
			$oResponse->setField('num', "Номер");
			$oResponse->setField('name', "Име");
			$oResponse->setField('place', "Населено място");
			$oResponse->setField('address', "Адрес");
			$oResponse->setField('msgTime', "време");
			
			$oResponse->setFieldAttributes('num', array('disabled' => "disabled"));
			$oResponse->setFieldAttributes('name', array('disabled' => "disabled"));
			$oResponse->setFieldAttributes('place', array('disabled' => "disabled"));
			$oResponse->setFieldAttributes('address', array('disabled' => "disabled"));
			$oResponse->setFieldAttributes('msgTime', array('disabled' => "disabled"));
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['msgTime'] = date("d.m.Y H:i:s" ,$val['msgTime']);
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: right; width: 55px;') );
				$oResponse->setDataAttributes( $key, 'msgTime', array('style' => 'text-align: center; width: 150px;') );
			}
		}
		
		public function getObjectsInstantStates( $aData, DBResponse $oResponse ) {
			// Правена по възможно най-глупавия начин!
			
			$data = isset($aData['signals']) ? explode(",", $aData['signals']) : array();
			$nIDOffice = isset($aData['nIDOffice']) ? $aData['nIDOffice'] : -1;
			$alarm = array();
			$restore = array();
			$signals1 = "";
			$signals2 = "";
			$where1 = "";
			$where2 = "";

			foreach ( $data as $val ) {
				
				if ( substr($val, 0, 3) == "al_" ) {
					$sig = substr($val, -(strlen($val) - 3));
					$where1 .= !empty($where1) ? " OR ( m.id_sig = {$sig} AND a.alarm = 1 ) " : " AND ( m.id_sig = {$sig} AND a.alarm = 1 ) ";
					$signals1 .= !empty($signals1) ? ",".substr($val, -(strlen($val) - 3)) : substr($val, -(strlen($val) - 3));					
				}	

				if ( substr($val, 0, 4) == "res_" ) {
					$sig = substr($val, -(strlen($val) - 4));
					$where2 .= !empty($where2) ? " OR ( m.id_sig = {$sig} AND a.alarm = 0 ) " : " AND ( m.id_sig = {$sig} AND a.alarm = 0 ) ";
					$signals2 .= !empty($signals2) ? ",".substr($val, -(strlen($val) - 4)) : substr($val, -(strlen($val) - 4));
				}	
			}

//			if ( !empty($signals1) ) {
//				$where1 = "\n AND ( m.id_sig IN ({$signals1}) AND a.alarm = 1) \n";
//			}
//			
//			if ( !empty($signals2) ) {
//				$where2 = "\n AND ( m.id_sig IN ({$signals2}) AND a.alarm = 0) \n";
//			} 
			
			$table = date("Y_m");		
			//$table = "2008_06";
			if ( empty($data) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			$sQuery = "
				SELECT
					o.id_obj as id,
					r.name as place
				FROM {$table} a
				LEFT JOIN messages m ON m.id_msg = a.id_msg
				LEFT JOIN objects o ON ( o.id_obj = m.id_obj AND o.id_status !=4 )
				LEFT JOIN regions r ON ( r.id_region = o.id_region )
				WHERE 1
					AND r.telenet_id_office = {$nIDOffice}
					AND m.id_msg IS NOT NULL
					AND o.id_status !=4
					{$where1}
					{$where2}
					AND m.flag = 1
				GROUP BY o.id_obj
			";
				
			$aData = $this->select( $sQuery );
			
			$nRowTotal = count($aData);

			$oResponse->setFormElement('form1', 'nTotalObjects', array(), $nRowTotal);
			$oResponse->setFormElement('form1', 'sTotals', array(), '');
			
			$totals = array();
			foreach ( $aData as &$val ) {
				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
				isset($totals[$val['place']]) ? $totals[$val['place']] += 1 : $totals[$val['place']] = 1;
			}

			foreach ( $totals as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'sTotals', array("value" => $key), $key." => ".$val );
			}					

			
			$aData = array();	
				
			$sQuery = "
				SELECT
					o.id_obj as id,
					o.num,
					o.name,
					r.name as place,
					o.address,
					m.id_sig,
					DATE_FORMAT(m.timeAl, '%d.%m.%Y %H:%i:%s') AS time_al
				FROM {$table} a
				LEFT JOIN messages m ON m.id_msg = a.id_msg
				LEFT JOIN objects o ON ( o.id_obj = m.id_obj AND o.id_status !=4 )
				LEFT JOIN regions r ON ( r.id_region = o.id_region )
				WHERE 1	
					AND r.telenet_id_office = {$nIDOffice}
					AND m.id_msg IS NOT NULL
					AND o.id_status !=4
					{$where1}
					{$where2}
					AND m.flag = 1
				GROUP BY o.id_obj
			";

			//APILog::Log(0, $sQuery);										
			$oParams = Params::getInstance();
			$aParams = Params::getAll();

			$nPage = $oParams->get("current_page", 1);
			
			$sSortField = $oParams->get("sfield", "num");
			$nSortType	= $oParams->get("stype", "DBAPI_SORT_DESC");

			if ( empty($sSortField) ) {
				$sSortField = "num";
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";

			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);

			if ( $bLimited ) {
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}

			$aData = $this->select( $sQuery );
			
			$oSignals = new DBSignals2();
			$aSignals = array();
			$countPic = 0;
			
			foreach ( $aData as &$val ) {
				$val['name'] = iconv('cp1251', 'utf-8', $val['name']);
				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
				$val['address'] = iconv('cp1251', 'utf-8', $val['address']);
				
				$aSignals = $oSignals->getSignalsBySig( $val['id'], $signals1 );
				
				if ( count($aSignals) > $countPic ) {
					$countPic = count($aSignals);
				}
			}
				
			$oResponse->setData( $aData );						

			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}


			$oResponse->setField('num', "номер", "Сортиране по номер");
			$oResponse->setField('name', "обект", "Сортиране по обект");
			$oResponse->setField('place', "нас. място", "Сортиране по населено място");
			$oResponse->setField('address', "адрес", "Сортиране по адрес");
			if ( $countPic > 0 ) {
				for ( $i = 1; $i <= $countPic; $i++ ) {
					$oResponse->setField("picx".$i, "");
				}
			}
			$oResponse->setField('time_al', "време", "Сортиране по време");
			$oResponse->setField( '', '', '', 'images/setup.gif', 'new_tech_request', '');
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				
				if ( $countPic > 0 ) {
					for ( $i = 1; $i <= $countPic; $i++ ) {
						$val['picx'.$i] = "";
					}
				}
				
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: right; width: 55px;') );
				$oResponse->setDataAttributes( $key, 'time_al', array('style' => 'text-align: center; width: 150px;') );

				$aSignals = $oSignals->getSignalsBySig( $val['id'], $signals1 );
				//APILog::Log(0, $aSignals);
				
				$k = 1;
				foreach ( $aSignals as $v ) {
					$oResponse->setDataAttributes( $key, 'picx'.$k, array("style" => "height: 18px; font-size: 11px; background: url(signal_images/{$v['id_sig']}.bmp) no-repeat;", "title" => $v['msg_al']."\n".$v['time_al']) );
					$k++;
				}
			}
			
			if ( $countPic > 0 ) {
				for ( $i = 1; $i <= $countPic; $i++ ) {
					$oResponse->setFieldAttributes( 'picx'.$i,	array('style' => 'width: 16px;' ) );
				}
			}
			
		}
//**************** bla

		public function getObjectsUnknownSignals( $aData, DBResponse $oResponse ) {
			// Правена по възможно най-глупавия начин!
			
			$data = isset($aData['data']) ? $aData['data'] : "";
			$nIDOffice = isset($aData['nIDOffice']) && is_numeric($aData['nIDOffice']) ? $aData['nIDOffice'] : 0;
			$nIDFirm = isset($aData['nIDFirm']) && is_numeric($aData['nIDFirm']) ? $aData['nIDFirm'] : 0;
			$from = isset($aData['from']) ? $aData['from'] : 0;
			$to = isset($aData['to']) ? $aData['to'] : 0;
			$where = "";
			$where2 = "";
			$where3 = "";
			
			if ( !empty($nIDOffice) ) {
				$where = " AND r.telenet_id_office = {$nIDOffice} ";
			} elseif ( !empty($nIDFirm) ) {
				$oFirm = new DBOffices();
				$aFirm = $oFirm->getOfficesIDByFirm( $nIDFirm );
				$where = " AND r.telenet_id_office IN ({$aFirm}) ";
			}
			
			if ( !empty($from) ) {
				$where2 = " AND a.MsgTime >= '{$from}' ";
			}
			
			if ( !empty($to) ) {
				$where3 = " AND a.MsgTime <= '{$to}' ";
			}			

			
			$table = date("Y_m");		
			//$table = "2008_06";
			
			if ( empty($data) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			$sQuery = "
				SELECT 
					a.num
				FROM `{$table}` a
				LEFT JOIN objects o ON o.num = a.num
				LEFT JOIN regions r ON r.id_region = o.id_region			
				WHERE ( a.Msg LIKE '%Unkn%{$data}%' OR a.Msg LIKE '%Невъв%{$data}%' )
					AND r.id_receiver = a.id_receiver
					{$where} {$where2} {$where3}
				GROUP BY o.num, a.id_receiver  	
			";
				
			$aData = $this->select( $sQuery );
			
			$nRowTotal = count($aData);

			//$oResponse->setFormElement('form1', 'nTotalObjects', array(), $nRowTotal);
			//$oResponse->setFormElement('form1', 'sTotals', array(), '');
			
			$totals = array();
//			foreach ( $aData as &$val ) {
//				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
//				isset($totals[$val['place']]) ? $totals[$val['place']] += 1 : $totals[$val['place']] = 1;
//			}

			foreach ( $totals as $key => $val ) {
				$oResponse->setFormElementChild('form1', 'sTotals', array("value" => $key), $key." => ".$val );
			}					

			
			$aData = array();	
				
			$sQuery = "
				SELECT 
					o.id_obj as id,
					count(a.id_arhiv) AS cnt, 
					a.num, 
					o.name,
					o.place,
					o.address,
					a.id_receiver 
				FROM `{$table}` a
				LEFT JOIN objects o ON o.num = a.num
				LEFT JOIN regions r ON r.id_region = o.id_region
				WHERE ( a.Msg LIKE '%Unkn%{$data}%' OR a.Msg LIKE '%Невъв%{$data}%' )
					AND r.id_receiver = a.id_receiver
					{$where} {$where2} {$where3}
				GROUP BY o.num, a.id_receiver 
			";

			//APILog::Log(0, $sQuery);	
			//LEFT JOIN messages m ON m.id_msg = a.id_msg										
			$oParams = Params::getInstance();
			$aParams = Params::getAll();

			$nPage = $oParams->get("current_page", 1);
			
			$sSortField = $oParams->get("sfield", "num");
			$nSortType	= $oParams->get("stype", "DBAPI_SORT_DESC");

			if ( empty($sSortField) ) {
				$sSortField = "num";
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";

			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);

			if ( $bLimited ) {
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			}

			$aData = $this->select( $sQuery );
			
			foreach ( $aData as &$val ) {
				$val['name'] = iconv('cp1251', 'utf-8', $val['name']);
				$val['place'] = iconv('cp1251', 'utf-8', $val['place']);
				$val['address'] = iconv('cp1251', 'utf-8', $val['address']);
				
			}
				
			$oResponse->setData( $aData );						

			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}


			$oResponse->setField('num', "номер", "Сортиране по номер");
			$oResponse->setField('cnt', "аларми", "Сортиране по брой на аларми");
			$oResponse->setField('name', "обект", "Сортиране по обект");			
			$oResponse->setField('place', "местоположение", "Сортиране по местоположение");
			$oResponse->setField('address', "адрес", "Сортиране по адрес");
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: right; width: 55px;') );
				$oResponse->setDataAttributes( $key, 'cnt', array('style' => 'text-align: right; width: 55px;') );				
			//	$oResponse->setDataAttributes( $key, 'time_al', array('style' => 'text-align: center; width: 150px;') );

			}
						
		}


// *************** </bla


		public function setServiceStatus($nIDObject) {
			
			$sQuery = "
			
				UPDATE
					objects
				SET 
					is_service_mode = 1 ,
					service_mode_time = now()
				WHERE id_obj = {$nIDObject}			
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		
		public function closeServiceStatus($nIDObject) {
			
			$sQuery = "
			
				UPDATE
					objects
				SET 
					is_service_mode = 0
				WHERE id_obj = {$nIDObject}			
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function setMOL($nIDObject,$nIDMOL) {
			
			$sQuery = "
			
				UPDATE
					objects
				SET
					id_face = {$nIDMOL}
				WHERE id_obj = {$nIDObject}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function increaseTechnicsPrice($nToPrice,$nIDObject) {
			
			$sQuery = "
				UPDATE
					objects
				SET
					tehnika_cena = tehnika_cena + {$nToPrice}
				WHERE
					id_obj = {$nIDObject}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function makeNotActive($nIDObject) {
			
			$sQuery = "
			
				UPDATE
					objects
				SET 
					id_status = 4,
					change_status = NOW()
				WHERE id_obj = {$nIDObject}			
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function updateNum($nID,$nNum) {
			
			$sQuery = "
			
				UPDATE
					objects
				SET
					num = {$nNum}
				WHERE
					id_obj = {$nID}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>