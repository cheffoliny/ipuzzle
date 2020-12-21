<?php
	class DBWorkCard
		extends DBBase2 {
		
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'work_card');
		}
			
		public function getReport( $aData, DBResponse $oResponse ) {
			global $db_name_personnel;
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					wc.id, 
					wc.num,
					CONCAT_WS(' ', pn.fname, pn.mname, pn.lname) AS name,
					DATE_FORMAT(wc.start_time, '%d.%m.%Y %H:%i:%s') AS startTime,
					DATE_FORMAT(wc.end_time, '%d.%m.%Y %H:%i:%s') AS endTime
				FROM work_card wc
				LEFT JOIN {$db_name_personnel}.personnel as pn ON wc.id_user = pn.id
				WHERE 1				
			";
			
			if ( !empty($aData['num']) ) {
				$sQuery .= " AND wc.num = {$aData['num']} ";
			}

			if ( !empty($aData['from']) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(wc.start_time) >= {$aData['from']} ";
			}

			if ( !empty($aData['to']) ) {
				$aData['status'] = 'all';
				$sQuery .= " AND UNIX_TIMESTAMP(wc.end_time) != 0 AND UNIX_TIMESTAMP(wc.end_time) <= {$aData['to']} ";
			}

			if ( $aData['status'] == 'active' )  {
				$sQuery .= " AND UNIX_TIMESTAMP(wc.end_time) = 0 ";
			} elseif ( $aData['status'] == 'inactive' )  {
				$sQuery .= " AND UNIX_TIMESTAMP(wc.end_time) != 0 ";
			}
			
			if ( !empty($aData['dispatcher']) ) {
				$sQuery .= " AND wc.id_user = {$aData['dispatcher']} ";
			}			
			
			$this->getResult( $sQuery, 'start_time', DBAPI_SORT_DESC, $oResponse );
	
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['num'] = zero_padding($val['num']);
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align:center; width: 80px;'));
				if($val['endTime'] == '00.00.0000 00:00:00') {
					$oResponse->setRowAttributes($val['id'],array('style' => 'background-color:#ffbbbb;'));
				}
			}	
	
			$oResponse->setField('num',				'номер',		'сортирай по номер');
			$oResponse->setField('name',			'диспечер',		'сортирай по диспечер');
			$oResponse->setField('startTime',		'отваряне',		'сортирай по време');
			$oResponse->setField('endTime',			'приключване',	'сортирай по време');
			$oResponse->setFIeldLink('num',			'openWorkCard' );
		}		
		
		public function getDispecherName() {
			global $db_name_personnel, $db_personnel;
			$db_personnel->debug=true;
			
			$sQuery = "
				SELECT
					dp.id,
					CONCAT_WS(' ', dp.fname, dp.mname, dp.lname) AS dName 
				FROM {$db_name_personnel}.personnel dp
				WHERE 1
					AND dp.status = 'active'
					AND dp.id_position IN (SELECT id FROM {$db_name_personnel}.positions WHERE function = 'dispatcher' AND to_arc = 0)
				ORDER BY dName
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getLastActiveWorkCard() 
		{
			$nID = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			
			//$nID = 7; //!!!
			$sQuery = "
				SELECT 
					DISTINCT MAX(id) AS nID
				FROM work_card 
				WHERE id_user = {$nID} 
				AND end_time = 0	
			";
			
			$res = $this->selectOnce( $sQuery );
			
			return !empty($res['nID']) ? $res['nID'] : 0;
		}
		
		public function getLastUncompliteWorkCard() {
			$sQuery = "
				SELECT
					id
				FROM work_card
				WHERE 1
					AND end_time = '0000-00-00 00:00:00'
				ORDER BY start_time DESC
			";
			return $this->selectOne($sQuery);
		}
		
		public function getWorkCardInfo( $nID ) {
			global $db_name_personnel;
			
			$nID = is_numeric( $nID ) ? $nID : 0;

			$sQuery = "
				SELECT 
					wc.id,
					wc.id_user,
					CONCAT_WS(' ', pn.fname, pn.mname, pn.lname) AS dispatcher,
					DATE_FORMAT(wc.start_time, '%d.%m.%Y %H:%i') AS startTime,
					IF ( UNIX_TIMESTAMP(wc.end_time) = 0, '', DATE_FORMAT(wc.end_time, '%d.%m.%Y %H:%i') ) AS endTime,
					UNIX_TIMESTAMP(wc.start_time) as sttime,
					UNIX_TIMESTAMP(wc.end_time) as locked
				FROM work_card wc
				LEFT JOIN {$db_name_personnel}.personnel as pn ON wc.id_user = pn.id
				WHERE wc.id = {$nID} 				
			";
			//echo $sQuery;
			return $this->selectOnce( $sQuery );
		}
		
		public function getWorkCardOffices( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
			
			$sQuery = "
				SELECT 
					o.id, 
					CONCAT('[',f.name,'] ',o.name) AS name,
					(SELECT GROUP_CONCAT(id_office) FROM work_card_offices WHERE id_work_card = {$nID}) AS perm
				FROM offices o
				LEFT JOIN firms f ON o.id_firm = f.id
				WHERE o.is_reaction = 1
					AND o.to_arc = 0			
					AND o.id IN ({$sAccessRegions})
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getUnixTimestamp( $nID ) {
			
			$sQuery = "
				SELECT 
				 	id,
					UNIX_TIMESTAMP(start_time) AS start_time,
					UNIX_TIMESTAMP(end_time) AS end_time
				FROM work_card
				WHERE id = {$nID} 
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getTechs( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$sNow = date( "Y-m-d H:i:s" );
			
			$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						off.id AS id,
						CONCAT( off.name, ' - ', fir.name ) AS office
					FROM work_card_offices wco
						LEFT JOIN offices off ON off.id = wco.id_office
						LEFT JOIN firms fir ON fir.id = off.id_firm
					WHERE 1
						AND id_work_card = {$aParams['nIDCard']}
						AND off.id IN ({$sAccessRegions})
					ORDER BY office
			";
			
			//$this->getResult( $sQuery, "office", 'ASC', $oResponse );
			$aData = $this->select( $sQuery );
			
			$oResponse->setField( 'office',		'Регион - фирма',	'' );
			$oResponse->setField( 'persons',	'Служители',		'' );
			
			$aFinalData = array();
			
			//foreach( $oResponse->oResult->aData as $key => $value )
			foreach( $aData as $key => $value )
			{
				$sQuery = "
						SELECT DISTINCT
							CONCAT_WS( ' ', per.fname, per.mname, per.lname ) AS name
						FROM {$db_name_personnel}.personnel per
							LEFT JOIN object_duty obd ON obd.id_person = per.id
							LEFT JOIN objects obj ON obj.id = obd.id_obj
						WHERE 1
							AND per.to_arc = 0
							AND per.id_office = {$value['id']}
							AND obd.id_shift != 0
							AND obj.is_tech = 1
							AND obd.startShift <= '$sNow'
							AND obd.endShift >= '$sNow'
				";
				
				$aPersonData = $this->select( $sQuery );
				
				foreach( $aPersonData as $aPerson )
				{
					if( !isset( $aFinalData[$key]['persons'] ) )$aFinalData[$key]['persons'] = "";
					$aFinalData[$key]['persons'] .= $aPerson['name'] . "; ";
				}
				
				if( isset( $aFinalData[$key]['persons'] ) && !empty( $aFinalData[$key]['persons'] ) )
				{
					$aFinalData[$key]['id'] = $value['id'];
					$aFinalData[$key]['office'] = $value['office'];
				}
			}
			
			$oResponse->setData( $aFinalData );
		}
	}

?>