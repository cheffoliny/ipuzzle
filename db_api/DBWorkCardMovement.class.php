<?php
	class DBWorkCardMovement
		extends DBBase2 {
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'work_card_movement');
		}
				
		public function getReport( $aData, DBResponse $oResponse ) {
			global $db_name_sod,$db_name_telepol;
			
			$nIDScheme	= isset( $aData['nIDScheme']) ? $aData['nIDScheme'] : 0;
			$id_office  = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
			$id_offices = isset( $aData['id_offices'] ) ? $aData['id_offices'] : 0;
			$num_patrul = isset( $aData['num_patrul'] ) ? $aData['num_patrul'] : 0;
			$sAlarmType = isset( $aData['sAlarmType']) ? $aData['sAlarmType'] : 'all';
			$dFrom		= isset( $aData['date_from'] ) ? $aData['date_from'] : 0;
			$dTo		= isset( $aData['date_to'] ) ? $aData['date_to'] : 0;
			$date_to	= mktime(0, 0, 0, date("m", $dTo)  , date("d", $dTo)+1, date("Y", $dTo));
		
			//throw new Exception(date('Y-m-d H:i',$date_to));
			//throw new Exception($date_to);
			
			$dFrom = isset($aData['start_time']) ? $aData['start_time'] : $dFrom;	// Ако са сетнати start_time и end_time 		
			$dTo = isset($aData['end_time']) ? $aData['end_time'] : $dTo;			// значи функцията е викната от справката 	
			$date_to = isset($aData['end_time']) ? $aData['end_time'] :$date_to;	// движение в самата работна карта
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					wcm.id,
					p.num_patrul AS patrul,
					CONCAT('[',f.name,']' ,o.name) AS office,
					wcm.type,
					CASE
						WHEN wcm.type = 'tour' THEN 'обход'
						WHEN wcm.type = 'parking' THEN pp.name
						WHEN wcm.type = 'object' THEN wcm.obj_name
					END AS object,
					wcm.id_object,
					wcm.alarm_type,
					' ' AS empty1,
					' ' AS empty2,
					' ' AS empty3,
					' ' AS empty4,
					' ' AS empty5,
					' ' AS empty6,
					' ' AS empty7,
					' ' AS empty8,
					' ' AS empty9,
					' ' AS empty10,
					' ' AS empty11,
					' ' AS empty12,
					' ' AS empty13,
					' ' AS empty14,
					' ' AS empty15,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.alarm_time ) = 0 THEN '---'
						ELSE DATE_FORMAT(wcm.alarm_time	, '%d.%m.%Y %H:%i:%s')
					END AS alarm_time_,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.start_time ) = 0 THEN '---'
						ELSE DATE_FORMAT(wcm.start_time	, '%H:%i:%s')
					END AS start_time_,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.start_time ) = 0 THEN '0'
						ELSE DATE_FORMAT(wcm.start_time	, '%c')
					END AS start_time_month,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.end_time ) = 0 THEN '---'
						ELSE DATE_FORMAT(wcm.end_time	, '%H:%i:%s')
					END AS endTime,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.end_time ) = 0 THEN '---'
						ELSE SEC_TO_TIME(UNIX_TIMESTAMP( wcm.end_time ) - UNIX_TIMESTAMP( wcm.start_time ) ) 
					END AS reactionTime,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.end_time ) = 0 THEN '0'
						ELSE UNIX_TIMESTAMP( wcm.end_time ) - UNIX_TIMESTAMP( wcm.start_time )  
					END AS reactionTimeSeconds,
					CASE
						WHEN wcm.id_alarm_reasons = 0 THEN '---'
						ELSE ar.name
					END AS reason,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.reason_time ) = 0 THEN '---'
						ELSE DATE_FORMAT(wcm.reason_time	, '%H:%i:%s')
					END AS reasonTime,
					CASE
						WHEN UNIX_TIMESTAMP( wcm.reason_time ) = 0 THEN '---'
						ELSE SEC_TO_TIME(UNIX_TIMESTAMP( wcm.reason_time ) - UNIX_TIMESTAMP( wcm.end_time ))
					END AS stayTime,
					'' as confirm,
					wcm.note
				FROM work_card_movement		wcm
				LEFT JOIN offices			o	ON	o.id	= wcm.id_office
				LEFT JOIN firms 			f	ON	f.id	= o.id_firm
				LEFT JOIN patruls		 	p	ON	p.id	= wcm.id_patrul
				LEFT JOIN patrul_parking 	pp	ON	pp.id	= wcm.id_parking
				LEFT JOIN objects 			ob	ON	ob.id	= wcm.id_object
				LEFT JOIN alarm_reasons ar ON wcm.id_alarm_reasons = ar.id
				WHERE 1
			";
			
			if($sAlarmType != 'all') {
				if($sAlarmType == 'visited') {
					$sQuery .= " AND wcm.end_time != '0000-00-00 00:00:00'\n";
				} else {
					$sQuery .= " AND wcm.end_time = '0000-00-00 00:00:00'\n";
				}
			}
			
			if ( !empty($id_office) ) {
				$sQuery .= " AND wcm.id_office = {$id_office} ";
			}	
			else if(!empty($id_offices)) {
				$sQuery .= " AND wcm.id_office IN ({$id_offices}) ";
			} else {
				$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
				$sQuery .= " AND wcm.id_office IN ({$sAccessRegions})";
			}
		
			if ( !empty($num_patrul) ) {
				$sQuery .= " AND p.num_patrul = {$num_patrul} ";
			}
			
			if ( !empty($dFrom) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.start_time) >= '{$dFrom}' \n";
			}

			if ( !empty($dTo) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.start_time) <= '{$date_to}' \n";
			}
			$this->getResult( $sQuery, 'alarm_time_', DBAPI_SORT_DESC, $oResponse );
			
			$oDBSignals = new DBSignals();
			
			$maxAlarms = 0;	
			
			$oDBObjects = new DBObjects();
			$oDBObjects2 = new DBObjects2();
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$nID = $val['id'];
				
				if(substr($val['reactionTime'],0,2) == '00')
					$val['reactionTime'] = substr($val['reactionTime'],3);
				
				if(substr($val['stayTime'],0,2) == '00')
					$val['stayTime'] = substr($val['stayTime'],3);
				
				$aAlarms = array();
				$aAlarms = explode(',',$val['alarm_type']);
				$i = 0;
			
				$nIDNewObject = $oDBObjects->getIDByIDOldObj($val['id_object']); 
		
				if(!empty($nIDNewObject)) {
				
					$oResponse->setDataAttributes($key,'object',array('onclick' => "openObjectArchiv({$nIDNewObject})",'style' => 'cursor:pointer;color:#253878;'));
					
					$oDBTechlimitCards = new DBTechLimitCards();
					$oDBPPPElements = new DBPPPElements();	
					$aSupportForWorkCardMovement=$oDBTechlimitCards->getTechLimitCardsForWorkCardMovement($val['id_object'],$dFrom,$date_to);
					
					
					$sTitle="Обслужвания:";
					foreach ($aSupportForWorkCardMovement as $val2) {
								$sTitle.="    \n\n".$val2['type']."\n";
								$sTitle.="       - дата: ".$val2['real_end']."\n";
								if (!empty($val2['reason'])) {
									$sTitle.="       - причина: ".$val2['reason']."\n";
								}
								if ($val2['expl']) {
									$sTitle.="       - допълнителни пояснения: ".$val2['expl']."\n";
								}	
						
								$aLimitCardNomenclatures = $oDBPPPElements->getElementsByIDLimitCard($val2['id']);
								if(!empty($aLimitCardNomenclatures)) {
									$sTitle .= "       - техника:";
									foreach ($aLimitCardNomenclatures as $aNomenclature) {
											$sTitle .= "\n	  ".round($aNomenclature['count'])." бр. ".$aNomenclature['name'];
									}
								}	
					      $oResponse->setDataAttributes($key,'confirm',array('onclick' => "openObjectSup({$nIDNewObject})",'title' => "{$sTitle}" ,'style' => "background-image:  url('images/confirm.gif');background-repeat: no-repeat;background-position: center; cursor:pointer;"));
					      
					}
				}

				
				foreach ($aAlarms as $alarm)	{
					if(!empty($alarm)) {
					$i++;
					
					$image = "images/default.bmp";
					$filename = $_SESSION['BASE_DIR']."/signal_images/{$alarm}.bmp";
					
					if($file = fopen($filename, 'r'))	{
						$image = "signal_images/{$alarm}.bmp";
						fclose($file);
					}	else	{						
						$pic = $oDBSignals->getPic($alarm);
						if (!empty($pic)) {
							$file = fopen($filename, 'w');
							$pic = $oDBSignals->getPic($alarm);
							fwrite($file, $pic);
							$image = "signal_images/{$alarm}.bmp";
							fclose($file);
						}
					}   

					$oResponse->setDataAttributes( $key, 'empty'."$i", array('style' => "background-image: url($image); background-repeat: no-repeat; background-position: center; width: 100px !important;"));
					}
				}
				if($i > $maxAlarms) $maxAlarms = $i;
				
				
				$oResponse->setDataAttributes( $key, 'alarmTime', array('style' => ' '));
				$oResponse->setDataAttributes( $key, 'start_time_', array('style' => 'text-align:center; '));
				$oResponse->setDataAttributes( $key, 'patrul', array('style' => 'text-align:center;'));
				
				
				
				if($val['type'] == 'object' && $val['endTime'] != '---' && !empty($val['id_object'])) { 
							
					$aObject = $oDBObjects2->getObjectById($val['id_object']);

					
					if( $val['start_time_month'] > 3 && $val['start_time_month'] < 11 ) {  // Реакция при нормални условия
						
						if( $aObject['time_react'] < ($val['reactionTimeSeconds']/60) ) {
							$oResponse->setRowAttributes($nID,array('style' => 'background: ffbbbb;'));
						}
					
					} else {														//Реакция при зимни условия
						
						if( round($aObject['time_react']*1.66) < ($val['reactionTimeSeconds']/60) ) {
							$oResponse->setRowAttributes($nID,array('style' => 'background: ffbbbb;'));
						}
					}
				}
				
				// Pavel - razreshavam dialoga za vsichki situacii
				// PP: bivali taka da go napravish....
				//if(  $val['reasonTime'] == '---'  ) {
					$oResponse->setDataAttributes( $key, '', array("onclick" => "stopMovement({$nID});"));
				//}
				
				if (utf8_strlen($val['note']) > 10){
					$oResponse->setDataAttributes( $key, 'note', array("width" => "100px", "title" => $val['note']));
					$val['note'] = utf8_substr($val['note'], 0, 10) . '...';
				}
			}	

			
			if(!empty($nIDScheme)) {
				$oDBMovementSchemes = new DBMovementSchemes();
				$aScheme = $oDBMovementSchemes->getRecord($nIDScheme);				
			}
			
			$oResponse->setField('patrul',			'екип',					'сортирай по екип');
			if(!empty($nIDScheme)) {
				if(!empty($aScheme['office']))	{
					$oResponse->setField('office',			'регион',					'сортирай по регион');
				}
			}
				
			   
			$oResponse->setField('object',			'обект/позиция',			'сортирай по обект');
				
			
			for ( $j = 1; $j <= $maxAlarms ; $j++)	{
				$oResponse->setField('empty'.$j,'');
			}
			

			$oResponse->setField('confirm', 'обслужвания',		'сортирай по обслужвания');
	
			$oResponse->setField('alarm_time_',		'време на аларма',			'сортирай по време на аларма');
			    
			if(!empty($nIDScheme)) {
			
				if(!empty($aScheme['start_time']))	{
					$oResponse->setField('start_time_',		'оповестяване',	'сортирай по време на оповестяване');
				}
				if(!empty($aScheme['end_time']))	{
					$oResponse->setField('endTime',			'пристигане',		'сортирай по време на пристигане');
				}
			}
			$oResponse->setField('reactionTime',	'реакция',		'сортирай по време на реакция');
			$oResponse->setField('reason',			'причина',					'сортирай по причина');
			
			if(!empty($nIDScheme)) {
				if(!empty($aScheme['reason_time']))	{
					$oResponse->setField('reasonTime',		'освобождаване',	'сортирай по време на освобождаване');
				}
				if(!empty($aScheme['stay_time']))	{
					$oResponse->setField('stayTime',		'престой',	'сортирай по време на престой');
				}
				if(!empty($aScheme['note']))	{
					$oResponse->setField('note',		'бележка',	'note');
				}
			}
			$oResponse->setField( '', '', '', 'images/history.gif', '', '' );
			
		}
				
		public function getReportTotal2($aData,DBResponse $oResponse) {
			
			$id_office  = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
			$id_offices = isset( $aData['id_offices'] ) ? $aData['id_offices'] : 0;
			$dFrom		= isset( $aData['date_from'] ) ? $aData['date_from'] : 0;
			$dTo		= isset( $aData['date_to'] ) ? $aData['date_to'] : 0;
			$date_to	= mktime(0, 0, 0, date("m", $dTo)  , date("d", $dTo)+1, date("Y", $dTo));
			
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					wcm.id_object,
					wcm.obj_name,
					count(*) AS visits,
					GROUP_CONCAT(DISTINCT DATE_FORMAT(wcm.end_time	, '%d.%m.%Y') ORDER BY wcm.alarm_time SEPARATOR ',  ') AS visits_time
				FROM work_card_movement wcm
				WHERE 1
					AND wcm.end_time != '0000-00-00 00:00:00'
			";
			
			if ( !empty($id_office) ) {
				$sQuery .= " AND wcm.id_office = {$id_office} ";
			}	
			else if(!empty($id_offices)) {
				$sQuery .= " AND wcm.id_office IN ({$id_offices}) ";
			}
			
			if ( !empty($dFrom) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.start_time) >= '{$dFrom}' \n";
			}

			if ( !empty($dTo) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.start_time) <= '{$date_to}' \n";
			}
			
			$sQuery .= " GROUP BY wcm.id_object \n";
			
			$this->getResult( $sQuery, 'visits', DBAPI_SORT_DESC, $oResponse );
			
			$oDBObjects = new DBObjects();
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$nIDNewObject = $oDBObjects->getIDByIDOldObj($val['id_object']); 
				
				if(!empty($nIDNewObject)) {
					$oResponse->setDataAttributes($key,'obj_name',array('onclick' => "openObjectArchiv({$nIDNewObject})",'style' => 'cursor:pointer;color:#253878;'));
				}
			}
				
			$oResponse->setField('obj_name','Обект','сортирай по име на обект');
			$oResponse->setField('visits','Посещения','сортирай по брой посещения');
			$oResponse->setField('visits_time','Времена на посещенията','сортирай по времена на посещения');
		}
		
		public function getReportTotal($aData,DBResponse $oResponse) {
			
			$id_office  = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
			$id_offices = isset( $aData['id_offices'] ) ? $aData['id_offices'] : 0;
			$dFrom		= isset( $aData['date_from'] ) ? $aData['date_from'] : 0;
			$dTo		= isset( $aData['date_to'] ) ? $aData['date_to'] : 0;
			$date_to	= !empty($dTo) ? mktime(0, 0, 0, date("m", $dTo)  , date("d", $dTo)+1, date("Y", $dTo)) : 0;
			$sAlarmType = isset( $aData['sAlarmType']) ? $aData['sAlarmType'] : 'visited';
			
			$dFrom = isset($aData['start_time']) ? $aData['start_time'] : $dFrom;	// Ако са сетнати start_time и end_time 		
			$dTo = isset($aData['end_time']) ? $aData['end_time'] : $dTo;			// значи функцията е викната от справката 	
			$date_to = isset($aData['end_time']) ? $aData['end_time'] :$date_to;	// движение в самата работна карта
			
			$sQueryVisits = "
				SELECT
					DISTINCT DATE_FORMAT(wcm.alarm_time	, '%d_%m_%Y') AS visit_time
				FROM work_card_movement wcm
				WHERE 1
			";
			
			if($sAlarmType != 'all') {
				if($sAlarmType == 'visited') {
					$sQueryVisits .= " AND wcm.end_time != '0000-00-00 00:00:00'\n";
				} else {
					$sQueryVisits .= " AND wcm.end_time = '0000-00-00 00:00:00'\n";
				}
			}
			
			if ( !empty($id_office) ) {
				$sQueryVisits .= " AND wcm.id_office = {$id_office} ";
			}	
			else if(!empty($id_offices)) {
				$sQueryVisits .= " AND wcm.id_office IN ({$id_offices}) ";
			}
			
			if ( !empty($dFrom) ) {
				$sQueryVisits .= " AND UNIX_TIMESTAMP(wcm.alarm_time) >= '{$dFrom}' \n";
			}

			if ( !empty($dTo) ) {
				$sQueryVisits .= " AND UNIX_TIMESTAMP(wcm.alarm_time) <= '{$date_to}' \n";
			}
			
			$aVisits = $this->select($sQueryVisits);
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
			";
			
			foreach ($aVisits as $visits) {
				
				$sQuery .= "
					'' AS v{$visits['visit_time']},
				";
			}
				
			$sQuery .= "
					wcm.id_object,
					wcm.obj_name,
					count(*) AS visits,
					GROUP_CONCAT(DISTINCT DATE_FORMAT(wcm.end_time	, '%d.%m.%Y') ORDER BY wcm.alarm_time SEPARATOR ',  ') AS visits_time
				FROM work_card_movement wcm
				WHERE 1
			";
			
			if($sAlarmType != 'all') {
				if($sAlarmType == 'visited') {
					$sQuery .= " AND wcm.end_time != '0000-00-00 00:00:00'\n";
				} else {
					$sQuery .= " AND wcm.end_time = '0000-00-00 00:00:00'\n";
				}
			}
			
			if ( !empty($id_office) ) {
				$sQuery .= " AND wcm.id_office = {$id_office} ";
			}	
			else if(!empty($id_offices)) {
				$sQuery .= " AND wcm.id_office IN ({$id_offices}) ";
			} else {
				$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
				$sQuery .= " AND wcm.id_office IN ({$sAccessRegions})";
			}
			
			if ( !empty($dFrom) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.alarm_time) >= '{$dFrom}' \n";
			}

			if ( !empty($dTo) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.alarm_time) <= '{$date_to}' \n";
			}
			
			$sQuery .= " GROUP BY wcm.id_object \n";
			
			$this->getResult( $sQuery, 'visits', DBAPI_SORT_DESC, $oResponse );
			
			$oDBObjects = new DBObjects();
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$nIDNewObject = $oDBObjects->getIDByIDOldObj($val['id_object']); 
				
				
				$sObjectName = $val['obj_name'];
				$sObjectNameCut = mb_substr($sObjectName,0,40,'UTF-8');
				$val['obj_name'] = $sObjectNameCut;
				
				if(!empty($nIDNewObject)) {
					$oResponse->setDataAttributes($key,'obj_name',array('onclick' => "openObjectArchiv({$nIDNewObject})",'style' => 'cursor:pointer;color:#253878;','title' => $sObjectName));
					$oResponse->setDataAttributes($key,'visits',array('onclick' => "openObjectArchiv({$nIDNewObject},'{$sAlarmType}',{$dFrom},{$dTo})",'style' => 'cursor:pointer;'));
				} else {
					$oResponse->setDataAttributes($key,'obj_name',array('title' => $sObjectName));
				}
				
				foreach ($aVisits as $visits) {
					
					$sVisitDate = str_replace('_','.',$visits['visit_time']);
					$sVisitDate = jsDateToTimestamp($sVisitDate);
					//throw new Exception($val['id_object']);
					$aObjectVisits = $this->getObjectVisits($val['id_object'],date('Y-m-d',$sVisitDate),$sAlarmType,$dFrom,$date_to); 
					
					if(!empty($aObjectVisits)) {
						$sObjectVisits = $sObjectName."\n\n".$aObjectVisits['visits'];
						
						$val['v'.$visits['visit_time']] = 
							substr(str_replace('_','.',$visits['visit_time']),0,5).
							" / red_tag".$aObjectVisits['cou']."tag_red_end"
						;						
						
						
						$sBackgoundColor = '';
						if(!empty($nIDNewObject)) {
							$oDBTechLimitCards = new DBTechLimitCards();
							$aLimitCards = $oDBTechLimitCards->getByObjAndDate($nIDNewObject,date('Y-m-d',$sVisitDate));
							if(!empty($aLimitCards['closed'])) {
								$sBackgoundColor = "#888888";
							} elseif (!empty($aLimitCards['active'])) {
								$sBackgoundColor = "#ffccff";
							}
						}
						
						if(!empty($sBackgoundColor)) {
							$oResponse->setDataAttributes($key,'v'.$visits['visit_time'],array('title' => $sObjectVisits,'style' => "background-color:{$sBackgoundColor}"));
						} else {
							$oResponse->setDataAttributes($key,'v'.$visits['visit_time'],array('title' => $sObjectVisits));
						}
					}
					
				}
			}
				
			$oResponse->setField('obj_name','Обект','сортирай по име на обект');
			$oResponse->setField('visits','П','сортирай по брой посещения');
			
			foreach ($aVisits as $visits) {
				$nTotalVisits = $this->countTotalVisits($visits['visit_time'],$sAlarmType,$id_office,$id_offices,$dFrom,$date_to);
				$oResponse->addTotal('v'.$visits['visit_time'],$nTotalVisits);
				$oResponse->setField('v'.$visits['visit_time'],substr($visits['visit_time'],0,5),'сортирай по времена на посещения', NULL, NULL, NULL, array( 'DATA_TOTAL' => 1 ));
			}
				
			//$oResponse->setField('visits_time','Времена на посещенията','сортирай по времена на посещения');
		}
		
		public function countTotalVisits($sDate,$sAlarmType,$id_office,$id_offices,$dFrom = 0,$date_to = 0) {
			list($d,$m,$y) = explode('_',$sDate);
			$sDate = $y."-".$m."-".$d;
			
			$sQuery = "
				SELECT 
					wcm.id
				FROM work_card_movement wcm
				WHERE
					wcm.alarm_time LIKE '{$sDate}%'
			";
			
			if($sAlarmType != 'all') {
				if($sAlarmType == 'visited') {
					$sQuery .= " AND wcm.end_time != '0000-00-00 00:00:00'\n";
				} else {
					$sQuery .= " AND wcm.end_time = '0000-00-00 00:00:00'\n";
				}
			}
			
			if ( !empty($dFrom) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.alarm_time) >= {$dFrom} \n";
			}

			if ( !empty($date_to) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.alarm_time) <= {$date_to} \n";
			}
			
			if ( !empty($id_office) ) {
				$sQuery .= " AND wcm.id_office = {$id_office} ";
			}	
			else if(!empty($id_offices)) {
				$sQuery .= " AND wcm.id_office IN ({$id_offices}) ";
			} else {
				$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
				$sQuery .= " AND wcm.id_office IN ({$sAccessRegions})";
			}
			
			$aReuslt = $this->select($sQuery);
			return count($aReuslt);
		}
		
		public function getObjectVisits($nIDObject,$sDate,$sAlarmType,$dFrom =0 ,$date_to = 0) {
			
			$sQuery = "
			
				SELECT
					count(*) AS cou,
					GROUP_CONCAT(SUBSTRING(wcm.alarm_time,12) ORDER BY wcm.alarm_time SEPARATOR ' \n') AS visits
				FROM work_card_movement wcm
				WHERE 1
					AND wcm.id_object = {$nIDObject}
					AND wcm.alarm_time LIKE '{$sDate}%'
			";
			
			if($sAlarmType != 'all') {
				if($sAlarmType == 'visited') {
					$sQuery .= " AND wcm.end_time != '0000-00-00 00:00:00'\n";
				} else {
					$sQuery .= " AND wcm.end_time = '0000-00-00 00:00:00'\n";
				}
			}
			
			if ( !empty($dFrom) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.alarm_time) >= {$dFrom} \n";
			}

			if ( !empty($date_to) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wcm.alarm_time) <= {$date_to} \n";
			}
			
			$sQuery .= " GROUP BY wcm.id_object\n";
			
			return $this->selectOnce($sQuery);
			
		}
		
		
		public function getReport2(  DBResponse $oResponse )
		{
			$nIDCard = Params::get("nIDCard", 0);
		
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
	
					wcm.id,
					p.num_patrul AS patrul,
					o.name AS office,
					CASE
						WHEN wcm.type	= 'tour' THEN 'Обход'
						WHEN wcm.status = 'start' THEN 'Тръгва'
						WHEN wcm.status = 'arrive' THEN 'Пристига'
					END AS status,
					CASE
						WHEN wcm.type = 'tour' THEN '---'
						WHEN wcm.type = 'parking' THEN pp.name
						WHEN wcm.type = 'object' THEN CONCAT('[', ob.num, '] ', ob.name)
					END AS object,

					CASE
						WHEN wcm.id_alarm_reasons = 0 THEN '---'
						ELSE ar.name
					END AS reason,
					DATE_FORMAT(wcm.start_time, '%d.%m.%Y %H:%i:%s') AS sTime
				FROM work_card_movement wcm
				LEFT JOIN offices o ON o.id = wcm.id_office
				LEFT JOIN patruls p ON p.id = wcm.id_patrul
				LEFT JOIN patrul_parking pp ON pp.id = wcm.id_parking
				LEFT JOIN objects ob ON ob.id = wcm.id_object
				LEFT JOIN alarm_reasons ar ON wcm.id_alarm_reasons = ar.id
				WHERE wcm.id IN (
									SELECT
										max(wc.id)
									FROM
										work_card_movement wc
									WHERE
										wc.id_work_card = {$nIDCard}
									GROUP BY 
										wc.id_patrul
								)
				
				";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_DESC, $oResponse );
	
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'sTime', array('style' => 'text-align:center; width: 150px;'));
				$oResponse->setDataAttributes( $key, 'status', array('style' => 'text-align:center; width: 85px;'));
				$oResponse->setDataAttributes( $key, 'patrul', array('style' => 'text-align:center; width: 65px;'));
			}	
	
			$oResponse->setField('patrul',			'патрул',		'сортирай по позивна');
			$oResponse->setField('office',			'регион',		'сортирай по регион');
			$oResponse->setField('status',			'статус',		'сортирай по статус');
			$oResponse->setField('object',			'обект/позиция','сортирай по обект');
			$oResponse->setField('reason',			'причина',		'сортирай по причина');
			$oResponse->setField('sTime',			'време',		'сортирай по време');
		}	

		
		public function getPatrulInfo($nIDPatrul)
		{
			$nIDCard = Params::get("nIDCard", 0);
			$sPatrul 	= Params::get('sPatrul');
			
			$sQuery = "
				SELECT 
	
					wcm.id,
					wcm.status,
					wcm.type,
					wcm.id_object
					
				FROM work_card_movement wcm

				WHERE wcm.id IN (
									SELECT
										max(wc.id)
									FROM
										work_card_movement wc
									WHERE
										wc.id_work_card = {$nIDCard} AND wc.id_patrul = {$nIDPatrul}
									GROUP BY 
										wc.id_patrul
								)
				
				";
			
			return $this->selectOnce( $sQuery );
	
		}	
//		public function getWorkCardInfo( $nID ) {
//			global $db_name_personnel;
//			
//			$nID = (int) $nID;
//
//			$sQuery = "
//				SELECT 
//					wc.id,
//					CONCAT_WS(' ', pn.fname, pn.mname, pn.lname) AS dispatcher,
//					DATE_FORMAT(wc.start_time, '%d.%m.%Y %H:%i:%s') AS startTime,
//					IF ( UNIX_TIMESTAMP(wc.end_time) = 0, '', DATE_FORMAT(wc.end_time, '%d.%m.%Y %H:%i:%s') ) AS endTime
//				FROM work_card wc
//				LEFT JOIN {$db_name_personnel}.personnel as pn ON wc.id_user = pn.id
//				WHERE wc.id = {$nID} 				
//			";
//			
//			return $this->selectOnce( $sQuery );
//		}


		public function getAnnounce( $aData ) {
			$nID	= isset($aData['obj']) ? $aData['obj'] : 0;
			$from 	= isset($aData['from']) ? $aData['from'] : '0000-00-00 00:00:00';
			$to 	= isset($aData['to']) ? $aData['to'] : '0000-00-00 00:00:00';
			
			$sQuery = "
				SELECT 
					count(w.id_object) as cnt
				FROM work_card_movement w
				WHERE w.id_object = {$nID}	
					AND w.alarm_time >= '{$from}'
					AND w.alarm_time <= '{$to}'
			";
			//AND UNIX_TIMESTAMP(w.end_time) = 0
			return $this->selectOne( $sQuery );
	
		}	


		public function getVisited( $aData ) {
			$nID	= isset($aData['obj']) ? $aData['obj'] : 0;
			$from 	= isset($aData['from']) ? $aData['from'] : '0000-00-00 00:00:00';
			$to 	= isset($aData['to']) ? $aData['to'] : '0000-00-00 00:00:00';
			
			$sQuery = "
				SELECT 
					count(w.id_object) as cnt
				FROM work_card_movement w
				WHERE w.id_object = {$nID}	
					AND w.alarm_time >= '{$from}'
					AND w.alarm_time <= '{$to}'
					AND UNIX_TIMESTAMP(w.end_time) > 0
			";
			
			return $this->selectOne( $sQuery );
	
		}	



	}
	
?>