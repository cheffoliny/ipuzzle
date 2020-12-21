<?php
	class DBRoadLists
		extends DBBase2 {
			
		public function __construct() {
			global $db_auto;
			$db_auto->debug=true;
			
			parent::__construct($db_auto, 'road_lists');
		}
				
		public function getReport( $nRegion, $sOffices, DBResponse $oResponse,$nWorkCardStartTime,$nWorkCardEndTime, $nIDCard ) {
			global $db_name_personnel, $db_name_sod;
			
			$right_edit = false;
			
			$oCurrentCard = new DBWorkCard();
			$aData = array();
			$aData = $oCurrentCard->getWorkCardInfo( $nIDCard );
			
			$logged = $_SESSION['userdata']['id_person'];
			$owner = $aData['id_user'];
		
			if ($logged == $owner ) {
				$right_edit = true;
			}	
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					rl.id, 
					pat.num_patrul AS code,
					CONCAT('[',fm.name,'] ',o.name) AS office,
					CONCAT(m.model, ' [', a.reg_num, ']') AS auto,
					GROUP_CONCAT(CONCAT_WS(' ', p.fname, p.lname) SEPARATOR ', ' ) AS patruls,
					rl.start_km
				FROM road_lists rl 
				LEFT JOIN {$db_name_personnel}.personnel p ON ( FIND_IN_SET(p.id, rl.persons) AND p.id IN (rl.persons) )
				LEFT JOIN {$db_name_sod}.offices o ON o.id = rl.id_office
				LEFT JOIN {$db_name_sod}.firms fm ON fm.id = o.id_firm
				LEFT JOIN auto a ON a.id = rl.id_auto
				LEFT JOIN auto_models m ON a.id_model = m.id
				LEFT JOIN functions f ON f.id = rl.id_function
				LEFT JOIN {$db_name_sod}.patruls pat ON pat.id = rl.id_patrul
				WHERE 1
					
					AND f.function_type = 'patrul'
			";
			
			if(empty($nWorkCardEndTime)) {
				$sQuery .= " AND UNIX_TIMESTAMP(rl.end_time) = 0\n";
			} else {
				$sQuery .= " AND (	
									(
										(UNIX_TIMESTAMP(rl.start_time) > {$nWorkCardStartTime})
										AND 
										(UNIX_TIMESTAMP(rl.start_time) < {$nWorkCardEndTime})
									)	
									OR
									(
										(UNIX_TIMESTAMP(rl.end_time) > {$nWorkCardStartTime})
										AND 
										(UNIX_TIMESTAMP(rl.end_time) < {$nWorkCardEndTime})
									)	
									OR
									(
										(UNIX_TIMESTAMP(rl.start_time) < {$nWorkCardStartTime})
										AND 
										(UNIX_TIMESTAMP(rl.end_time) > {$nWorkCardEndTime})
									)							 
								 )	
				";
			}
			
			if ( !empty($nRegion) ) {
				$sQuery .= " AND rl.id_office = {$nRegion} \n";
			}	else {
				$sQuery .= " AND rl.id_office IN ({$sOffices}) \n";
			}
			
			$sQuery .= " GROUP BY rl.id	\n";
			
			$this->getResult( $sQuery, 'start_time', DBAPI_SORT_DESC, $oResponse );
	
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'start_km', array('style' => 'text-align: right; width: 120px;'));
				$oResponse->setDataAttributes( $key, 'code', array('style' => 'text-align: center; width: 70px;'));
				$oResponse->setDataAttributes( $key, 'id', array('title' => 'Отвори горивен лист'));
			}	
	
			$oResponse->setField('code',			'позивна',			'сортирай по позивна');
			$oResponse->setField('office',			'регион',		'сортирай по регион');
			$oResponse->setField('auto',			'автомобил',		'сортирай по автомобил');
			$oResponse->setField('patruls',			'патрули',		'сортирай по патрули');
			$oResponse->setField('start_km',		'нач. километраж',	'сортирай по километри');

			if ($right_edit) {
				$oResponse->setField( 'id',			'',				'Отвори горивен лист', 'images/fuel.gif', 'openFuelList', '');
				$oResponse->setField( 'finish',			'',				'Затвори пътен лист', 'images/cancel.gif', 'stopRoadList', 'Приключи');
			}

			$oResponse->setFIeldLink('patruls',		'editPatrol' );
//			if (!$right_edit) {
//				$oResponse->setFieldAttributes('id', array('disabled' => "disabled"));
//				$oResponse->setFieldAttributes('finish', array('disabled' => "disabled"));
//			}
		}		
		
		public function getWorkCardInfo( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sQuery = "
				SELECT 
					wc.id,
					CONCAT_WS(' ', pn.fname, pn.mname, pn.lname) AS dispatcher,
					DATE_FORMAT(wc.start_time, '%d.%m.%Y %H:%i:%s') AS startTime,
					IF ( UNIX_TIMESTAMP(wc.end_time) = 0, '', DATE_FORMAT(wc.end_time, '%d.%m.%Y %H:%i:%s') ) AS endTime
				FROM work_card wc
				LEFT JOIN {$db_name_personnel}.personnel as pn ON wc.id_user = pn.id
				WHERE wc.id = {$nID} 				
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getRoadList( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sQuery = "
				SELECT 
					rl.*, UNIX_TIMESTAMP(rl.start_time) AS stime
				FROM road_lists AS rl
				WHERE 1
					AND id = {$nID}			
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getPatrulByRoadList( $nData ) {
			global $db_name_sod;
			
			$nIDPatrul = (int) $nData['id_patrul'];
			$nIDWorkCard = (int) $nData['id_work_card'];

			$sQuery = "
				SELECT 
					p.id,
					p.id_office
				FROM road_lists AS rl
				LEFT JOIN {$db_name_sod}.patruls p ON p.id = rl.id_patrul
				WHERE 
					p.num_patrul = {$nIDPatrul} 
					AND rl.id_work_card = {$nIDWorkCard}
					AND UNIX_TIMESTAMP(rl.end_time) = 0;
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getBusyPersons()
		{
			$sQuery =	"
				SELECT 
					GROUP_CONCAT(persons)
				FROM road_lists
				WHERE end_time = 0
			";
			return $this->selectOne($sQuery);
		}
		
		public function getBusyPatruls()
		{
			$sQuery =	"
					SELECT 
						id,
						id_patrul
					FROM road_lists
					WHERE end_time = 0 
			";
			return $this->selectAssoc($sQuery);
		}
		
		public function getBusyAutos()
		{
			$sQuery =	"
					SELECT 
						id,
						id_auto
					FROM road_lists
					WHERE end_time = 0 
			";
			return $this->selectAssoc($sQuery);
		}
		
	}
	
?>