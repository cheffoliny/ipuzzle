<?php
	class DBLimitCardPersons
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'limit_card_persons');
		}
	
		public function getReport( $nID, DBResponse $oResponse ) {
			global $db_name_personnel;
			
//			$id_work_card = isset( $aData['id_work_card'] ) ? $aData['id_work_card'] : 0;
//			$id_office = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
			//debug($aData);

			$oLock = new DBTechLimitCards();
			$oOperation = new DBLimitCardOperations();
			$aLock = array();
			$aOperation = array();
			
			$aOperation = $oOperation->getPriceOperationByLC($nID);
			$aOperation = is_numeric($aOperation) && !empty($aOperation) ? $aOperation : 0;
			
			$aLock = $oLock->getStatus($nID);
			
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('tech_support', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					lcp.id AS _id,
					CONCAT(lcp.id, ',', lcp.id_person) AS id,
					lcp.id AS num,
					lcp.id_limit_card,
					tlc.type,
					tlc.arrange_count,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS person,
					pp.name AS position,
					lcp.percent,
					(p.tech_support_factor * o.factor_tech_support) AS factor
				FROM limit_card_persons lcp
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person
				LEFT JOIN {$db_name_personnel}.positions pp ON pp.id = p.id_position
				LEFT JOIN offices o ON o.id = p.id_office
				LEFT JOIN tech_limit_cards tlc ON tlc.id = lcp.id_limit_card
				
				WHERE 1
					AND lcp.id_limit_card = {$nID}
			";
						
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			$price = 0;
			$price = $this->getPersonTechPercent( $nID );	

			$oActiveSettings = new DBTechSettings();

			$setup = array();
			$setup = $oActiveSettings->getActiveSettings();
			
			$priceDestroy = isset($setup['tech_price_destroy']) ? $setup['tech_price_destroy'] : 0;
			$priceArrange = isset($setup['tech_price_arrange']) ? $setup['tech_price_arrange'] : 0;
			$priceHoldup = isset($setup['tech_price_holdup']) ? $setup['tech_price_holdup'] : 0;
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$nArrangeCount = isset($val['arrange_count']) ? $val['arrange_count'] : 0;
				$val['num']		= zero_padding($val['num']);
				
				switch ($val['type']) {
					case 'create':
						$val['price'] = number_format( $aOperation * ($val['percent']/100) * $val['factor'], 2, '.', '')." лв.";
					break;
					
					case 'destroy':
						$val['price'] = number_format( $priceDestroy * ($val['percent']/100) * $val['factor'], 2, '.', '')." лв.";
					break;
					
					case 'holdup':
						$val['price'] = number_format( $priceHoldup * ($val['percent']/100) * $val['factor'], 2, '.', '')." лв.";
					break;
					
					case 'arrange':
						$val['price'] = number_format( $priceArrange * $nArrangeCount * ($val['percent']/100) * $val['factor'], 2, '.', '')." лв.";
					break;
					
					default:
						$val['price'] = "0.00 лв.";
					break;
				}
				
				$val['percent']	= $val['percent']." %";	
				
				$oResponse->setDataAttributes( $key, 'num', array('nowrap' => 'nowrap','style' => 'text-align: center; width: 80px; white-space: nowrap !important;'));
				$oResponse->setDataAttributes( $key, 'percent', array('nowrap' => 'nowrap','style' => 'text-align: center; width: 60px; white-space: nowrap !important;'));
				$oResponse->setDataAttributes( $key, 'price', array('nowrap' => 'nowrap','style' => 'text-align: right; width: 60px; white-space: nowrap !important;'));
			}

			$oResponse->setField('num',					'код',			'сортирай по код');
			$oResponse->setField('person',				'служител',		'сортирай по служител');
			$oResponse->setField('position',			'длъжност',		'сортирай по длъжност');
			$oResponse->setField('percent',				'процент',		'сортирай по процент');
			$oResponse->setField('price',				'наработка',	'сортирай по цена');
			if ( !empty($aLock) && (($aLock != 'cancel') && ($aLock != 'closed')) ) {
				$oResponse->setField( 'btn_delete',	'',		'Премахни', 'images/cancel.gif', 'delLimitCardPerson', '');

				$oResponse->setFIeldLink('num',				'editLimitCardPersons' );
				$oResponse->setFIeldLink('person',			'openPerson' );
				$oResponse->setFIeldLink('position',		'editLimitCardPersons' );
				$oResponse->setFIeldLink('percent',			'editLimitCardPersons' );
				$oResponse->setFIeldLink('price',			'editLimitCardPersons' );	
			}		
		}	
		
		public function getReport2( $aData, DBResponse $oResponse ) {
			global $db_name_personnel;
			
//			$id_work_card = isset( $aData['id_work_card'] ) ? $aData['id_work_card'] : 0;
//			$id_office = isset( $aData['id_office'] ) ? $aData['id_office'] : 0;
			//debug($aData);
			
			$oLock = new DBTechLimitCards();
			$aLock = array();
			
			$aLock = $oLock->getStatus($nID);
			
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('tech_support', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$nID = $aData['id'];
			$start = $aData['start'];
			
			$sQuery = "
				SELECT 
					lcp.id_person,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as person,
					lcp.id_limit_card,
					CONCAT(lcp.id_limit_card, ';\n ', obj.name) as object,
					UNIX_TIMESTAMP(tlc.planned_start) as planned_start,
					UNIX_TIMESTAMP(tlc.planned_end) as planned_end
				FROM limit_card_persons lcp
				LEFT JOIN tech_limit_cards tlc ON tlc.id = lcp.id_limit_card
				LEFT JOIN objects obj ON obj.id = tlc.id_object
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person
				WHERE lcp.id_person IN ({$nID})
					AND DAY(tlc.planned_start) = DAY('{$start}')
					AND MONTH(tlc.planned_start) = MONTH('{$start}')
					AND YEAR(tlc.planned_start) = YEAR('{$start}')
			";
				
						
			//$this->getResult( $sQuery, 'planned_start', DBAPI_SORT_ASC, $oResponse );
			
			$aData = $this->select( $sQuery );
			
			$data = array();
			
			foreach ( $aData as &$val ) {
				if ( isset($data[$val['id_person']]) ) {
					for ( $i = 8; $i <= 18; $i++ ) {
						if ( ($i >= date("H", $val['planned_start'])) && ($i <= date("H", $val['planned_end'])) ) {
							$data[$val['id_person']][$i] = $val['object'];
						}
					}
				} else {
					$data[$val['id_person']]['person'] = $val['person'];
					for ( $i = 8; $i <= 18; $i++ ) {
						if ( ($i >= date("H", $val['planned_start'])) && ($i <= date("H", $val['planned_end'])) ) {
							$data[$val['id_person']][$i] = $val['object'];
						}
					}
						
				}
				
				$val[$val['id_limit_card']] = array( 
					'start' => date("H", $val['planned_start']),
					'end' => date("H", $val['planned_end'])

				);
				//$val['start'] = date("H", $val['planned_start']);
				//$val['end'] = date("H", $val['planned_end']);
			}
			
			
			$oResponse->setData( $data );
			
			$oResponse->setField('person', 'служител',	'служител');
			$oResponse->setFieldAttributes('person', array('style' => 'width: 200px;') );

			for ( $i = 8; $i <= 18; $i++ ) {
				$oResponse->setField('n'.$i, $i, $i.':00');
				$oResponse->setFieldAttributes('n'.$i, array('style' => 'font-size: 11px; width: 30px;', 'title' => $i.':00') );
				//$oResponse->setFieldCaption( 'n'.$i, $i.':00' );
				//$oResponse->setFieldTitle('n'.$i, $i.':00' );
			}
						
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$person = array();
				$data = array();
				$data['id'] = $val; //$val['id_person'];
				
				$person = $this->getPersonsByDate($data);
				
				for ( $i = 8; $i <= 18; $i++ ) {
					
					//if ( ($i >= $val) && ($i <= $val) ) {
					if ( array_key_exists($i, $val) ) {
						$st = array('style' => 'text-align: center; width: 30px; background-color: #F09E93;', 'title' => 'Лимитна карта № '.$val[$i]);
					} else $st = array('style' => 'text-align: center; width: 30px;');
					
					$val['n'.$i] = $i.':00';
					$oResponse->setDataAttributes( $key, 'n'.$i, $st);
				}
								
				$oResponse->setDataAttributes( $key, 'person', array('nowrap' => 'nowrap','style' => 'text-align: left; width: 200px;'));
			}
			
			if ( !empty($aLock) && (($aLock != 'cancel') && ($aLock != 'closed')) ) {
				$oResponse->setFIeldLink('person',	'editPersons' );
			}
		}	
		
		public function delRequests( $nIDs ) {
			global $db_sod;
			
			$sQuery = "UPDATE tech_requests SET to_arc = 1 WHERE id IN ({$nIDs})";
			$db_sod->Execute($sQuery);
		}	
			
		public function makeLimitCard( $nIDs ) {
			global $db_sod;
			
			//$sQuery = "UPDATE tech_requests SET to_arc = 1 WHERE id IN ({$nIDs})";
			//$db_sod->Execute($sQuery);
		}	

		public function getRequest( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sQuery = "
				SELECT 
					tr.id,
					tr.id AS num,
					DATE_FORMAT(tr.created_time, '%d.%m.%Y') AS created_time,
					of.id_firm,
					obj.id_office,
					tr.id_object,
					obj.name AS object,
					tr.id_limit_card AS limit_card,
					tr.type,
					IF ( tr.created_type = 'manual', CONCAT_WS(' ', cu.fname, cu.mname, cu.lname), 'Автоматична Задача' ) AS created_user,
					tr.request_person_name,
					tr.note
				FROM tech_requests AS tr
				LEFT JOIN objects obj ON obj.id = tr.id_object
				LEFT JOIN offices of ON of.id = obj.id_office	
				LEFT JOIN {$db_name_personnel}.personnel cu ON cu.id = tr.created_user		
				WHERE 1
					AND tr.id = {$nID}			
			";
			// LEFT JOIN firms frm ON frm.id = of.id_firm
			// LEFT JOIN clients cl ON obj.id_client = cl.id	

			return $this->selectOnce( $sQuery );
		}

		public function getLimitCard( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;

			$sQuery = "
				SELECT 
					tl.id,
					tl.id AS num,
					DATE_FORMAT(tl.created_time, '%d.%m.%Y') AS created_time,
					tl.id_object,
					obj.name AS object,
					tl.status,
					IF ( tl.distance > 0, tl.distance, '') AS distance,
					cl.name AS client,
					tl.note,
					IF ( UNIX_TIMESTAMP(tl.planned_start) > 0, DATE_FORMAT(tl.planned_start, '%d.%m.%Y'), '') AS pstartdate,
					IF ( UNIX_TIMESTAMP(tl.planned_start) > 0, DATE_FORMAT(tl.planned_start, '%H:%i'), '')  AS pstarttime,
					IF ( UNIX_TIMESTAMP(tl.planned_end) > 0, DATE_FORMAT(tl.planned_end, '%d.%m.%Y'), '')  AS penddate,
					IF ( UNIX_TIMESTAMP(tl.planned_end) > 0, DATE_FORMAT(tl.planned_end, '%H:%i'), '')  AS pendtime,
					IF ( UNIX_TIMESTAMP(real_start) > 0, DATE_FORMAT(tl.real_start, '%d.%m.%Y'), '')  AS rstartdate,
					IF ( UNIX_TIMESTAMP(real_start) > 0, DATE_FORMAT(tl.real_start, '%H:%i'), '')  AS rstarttime,
					IF ( UNIX_TIMESTAMP(tl.real_end) > 0, DATE_FORMAT(tl.real_end, '%d.%m.%Y'), '')  AS renddate,
					IF ( UNIX_TIMESTAMP(tl.real_end) > 0, DATE_FORMAT(tl.real_end, '%H:%i'), '')  AS rendtime,
				FROM tech_limit_cards AS tl
				LEFT JOIN objects obj ON obj.id = tl.id_object
				LEFT JOIN clients cl ON cl.id = obj.id_client
				LEFT JOIN {$db_name_personnel}.personnel cu ON cu.id = tl.created_user		
				WHERE 1
					AND tl.id = {$nID}			
			";

			return $this->selectOnce( $sQuery );
		}
		
			
		public function schedulePerson( $nIDPerson , $nDate )
		{
			global $db_name_personnel;
				
			$sQuery = "
				SELECT 
					t.id AS _id,
					IFNULL(lp.id_person, 0) AS id_person,
					lp.percent,
					CONCAT('[', o.num,'] ', o.name) AS object_name,
					ROUND(
						( UNIX_TIMESTAMP( t.planned_start ) - UNIX_TIMESTAMP( DATE( t.planned_start ) ) ) / 60 
						) AS planned_start_mins,
						
					ROUND(
						( UNIX_TIMESTAMP( t.planned_end ) - UNIX_TIMESTAMP( DATE( t.planned_end ) ) ) / 60 
						) AS planned_end_mins,
					UNIX_TIMESTAMP(t.planned_start) AS p_start,
					UNIX_TIMESTAMP(t.planned_end) AS p_end,
					UNIX_TIMESTAMP(t.real_start) AS r_start,
					UNIX_TIMESTAMP(t.real_end) AS r_end,
					t.*
				FROM tech_limit_cards t
				LEFT JOIN objects o ON t.id_object = o.id
				LEFT JOIN limit_card_persons lp ON t.id = lp.id_limit_card
				LEFT JOIN {$db_name_personnel}.personnel p ON lp.id_person = p.id
				WHERE 1
					AND p.status = 'active'
					AND lp.id_person = {$nIDPerson}
					AND DATE( t.planned_start ) <= DATE( FROM_UNIXTIME( $nDate ) )
					AND DATE( t.planned_end   ) >= DATE( FROM_UNIXTIME( $nDate ) )
				";
			
			$sQuery .= "ORDER BY p.fname, p.mname, p.lname\n";
				
			return $this->select( $sQuery );
			
		}
		
		
		public function techPlanningPersonsResult( $nIDFirm, $sIDOffices , $nDate, $nClosedLimitCards, $sLCType = "" )
		{
			global $db_name_personnel,$db_name_finance;
				
			$sQuery = "
				SELECT 
					t.id AS _id,
					IFNULL(lp.id_person, 0) AS id_person,
					lp.percent,
					CONCAT('[', o.num,'] ', o.name) AS object_name,
					ROUND(
						( UNIX_TIMESTAMP( t.planned_start ) - UNIX_TIMESTAMP( DATE( t.planned_start ) ) ) / 60 
						) AS planned_start_mins,
						
					ROUND(
						( UNIX_TIMESTAMP( t.planned_end ) - UNIX_TIMESTAMP( DATE( t.planned_end ) ) ) / 60 
						) AS planned_end_mins,
					UNIX_TIMESTAMP(t.planned_start) AS p_start,
					UNIX_TIMESTAMP(t.planned_end) AS p_end,
					UNIX_TIMESTAMP(t.real_start) AS r_start,
					UNIX_TIMESTAMP(t.real_end) AS r_end,
					t.*,
					tr.created_user AS request_create,
					tr.note,
					tr.id_contract,
					c.info_tehnics
				FROM tech_limit_cards t
				LEFT JOIN tech_requests tr ON tr.id = t.id_request
				LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
				LEFT JOIN objects o ON t.id_object = o.id
				LEFT JOIN limit_card_persons lp ON t.id = lp.id_limit_card
				LEFT JOIN {$db_name_personnel}.personnel p ON lp.id_person = p.id
				LEFT JOIN offices off ON off.id = p.id_office
				WHERE 1
					AND p.status = 'active'

					AND DATE( t.planned_start ) <= DATE( FROM_UNIXTIME( $nDate ) )
					AND DATE( t.planned_end   ) >= DATE( FROM_UNIXTIME( $nDate ) )
				";
			
			if(empty($nClosedLimitCards)) {
				$sQuery .= " AND t.status != 'closed'\n";
			}
			
			if(!empty($sIDOffices)) {
				$sQuery .= "AND p.id_office IN ( {$sIDOffices} )\n";
			} else {
				$sQuery .= "AND off.id_firm =  '{$nIDFirm}' \n";
			}
			
			if( !empty( $sLCType ) )
			{
				$sQuery .= "
					AND t.type = '{$sLCType}'
				";
			}
			
			$sQuery .= "ORDER BY p.fname, p.mname, p.lname\n";
				
			return $this->select( $sQuery );
			
		}
		
		public function techPlanningTechnicsResult($nIDFirm, $sIDOffices , $nDate, $nClosedLimitCards, $sLCType = "" )
		{
			global $db_name_personnel,$db_name_finance;
				
			$sQuery = "
				SELECT 
					t.id AS _id,
					IFNULL(lp.id_person, 0) AS id_person,
					lp.percent,
					CONCAT('[', o.num,'] ', o.name) AS object_name,
					ROUND(
						( UNIX_TIMESTAMP( t.planned_start ) - UNIX_TIMESTAMP( DATE( t.planned_start ) ) ) / 60 
						) AS planned_start_mins,
						
					ROUND(
						( UNIX_TIMESTAMP( t.planned_end ) - UNIX_TIMESTAMP( DATE( t.planned_end ) ) ) / 60 
						) AS planned_end_mins,
					UNIX_TIMESTAMP(t.planned_start) AS p_start,
					UNIX_TIMESTAMP(t.planned_end) AS p_end,
					UNIX_TIMESTAMP(t.real_start) AS r_start,
					UNIX_TIMESTAMP(t.real_end) AS r_end,
					t.*,
					tr.created_user AS request_create,
					tr.note,
					tr.id_contract,
					c.info_tehnics
				FROM tech_limit_cards t
				LEFT JOIN tech_requests tr ON tr.id = t.id_request
				LEFT JOIN {$db_name_finance}.contracts c ON c.id = tr.id_contract
				LEFT JOIN objects o ON t.id_object = o.id
				LEFT JOIN limit_card_persons lp ON t.id = lp.id_limit_card
				LEFT JOIN {$db_name_personnel}.personnel p ON lp.id_person = p.id
				LEFT JOIN offices off ON off.id = p.id_office
				LEFT JOIN {$db_name_personnel}.positions po ON p.id_position = po.id
				WHERE 1
					AND p.status = 'active'
					AND po.function = 'technic'
					AND DATE( t.planned_start ) <= DATE( FROM_UNIXTIME( $nDate ) )
					AND DATE( t.planned_end   ) >= DATE( FROM_UNIXTIME( $nDate ) )
				";
			
			if(empty($nClosedLimitCards)) {
				$sQuery .= " AND t.status != 'closed'\n";
			}
			
			if(!empty($sIDOffices)) {
				$sQuery .= "AND p.id_office IN ( {$sIDOffices} )\n";
			} else {
				$sQuery .= "AND off.id_firm =  '{$nIDFirm}' \n";
			}
			
			if( !empty( $sLCType ) )
			{
				$sQuery .= "
					AND t.type = '{$sLCType}'
				";
			}
			
			$sQuery .= "ORDER BY p.fname, p.mname, p.lname\n";
			
			return $this->select( $sQuery );
			
		}
		
		public function unAttachedLimitCards( $sIDOffices )
		{
			$sQuery= "
				SELECT
					CONCAT('[', o.num,'] ', o.name) AS object_name,
					ROUND(
						( UNIX_TIMESTAMP( t.planned_start ) - UNIX_TIMESTAMP( DATE( t.planned_start ) ) ) / 60 
						) AS planned_start_mins,
						
					ROUND(
						( UNIX_TIMESTAMP( t.planned_end ) - UNIX_TIMESTAMP( DATE( t.planned_end ) ) ) / 60 
						) AS planned_end_mins,
					IF ( UNIX_TIMESTAMP(t.planned_start) > 0, DATE_FORMAT(t.planned_start, '%d.%m.%Y'), '') AS pstartdate,
					t.*
				FROM
					tech_limit_cards t 
				LEFT JOIN objects o ON t.id_object = o.id
				WHERE 1
					AND t.to_arc = 0
					AND t.status = 'active' 
					AND	t.id NOT IN (	SELECT
										id_limit_card
									FROM
										limit_card_persons
									GROUP BY
										id_limit_card
									)
					AND o.id_office IN ( {$sIDOffices} )
									";
				return $this->select( $sQuery );
		}
		
		public function getPersonTechPercent( $nID ) {
			global $db_name_storage;
			
			$nIDLimitCard = !empty( $nID ) ? $nID : 0;
			
			$sQuery = "
				SELECT
					nt.name AS nomenclature_type,
					n.name AS nomenclature,
					SUM( 
						CASE
							WHEN p.source_type = 'object' THEN pe.count
							WHEN p.dest_type = 'object' THEN -pe.count
							ELSE 0
						END
						) AS nomenclature_count,
					SUM( 
						CASE
							WHEN p.source_type = 'object' THEN (pe.count * n.support_price)
							WHEN p.dest_type = 'object' THEN -(pe.count * n.support_price)
							ELSE 0
						END
						) AS price
				FROM {$db_name_storage}.ppp p
				LEFT JOIN {$db_name_storage}.ppp_elements pe ON (p.id = pe.id_ppp AND pe.to_arc = 0)
				LEFT JOIN {$db_name_storage}.nomenclatures n ON pe.id_nomenclature = n.id
				LEFT JOIN {$db_name_storage}.nomenclature_types nt ON nt.id = n.id_type
				WHERE 1
					AND p.to_arc = 0
					AND p.id_limit_card = {$nIDLimitCard}
					AND p.id_limit_card > 0
					AND p.dest_date > 0
				GROUP BY pe.id_nomenclature
			";
			//HAVING nomenclature_count > 0
			$tmpArr = array();
			$sum = 0;
			$tmpArr = $this->select( $sQuery );
			
			foreach ( $tmpArr as $val ) {
				if ( $val['nomenclature_count'] < 0 ) {
					$sum += abs($val['price']);
				}	
			}
			
			return $sum;
		}
		
		public function getPersonTechPercent2( $nID )
		{
			global $db_name_storage;
			
			$nIDLimitCard = !empty( $nID ) ? $nID : 0;
			
			$sQuery = "
					SELECT
						nt.name AS nomenclature_type,
						n.name AS nomenclature,
						SUM(
							CASE
								WHEN p.source_type = 'object' THEN pe.count
								WHEN p.dest_type = 'object' THEN -pe.count
								ELSE 0
							END
							) AS nomenclature_count,
						SUM(
							CASE
								WHEN p.source_type = 'object' THEN ( pe.count * n.support_price )
								WHEN p.dest_type = 'object' THEN -( pe.count * n.support_price )
								ELSE 0
							END
							) AS price
					FROM {$db_name_storage}.ppp p
					LEFT JOIN {$db_name_storage}.ppp_elements pe ON ( p.id = pe.id_ppp AND pe.to_arc = 0 )
					LEFT JOIN {$db_name_storage}.nomenclatures n ON pe.id_nomenclature = n.id
					LEFT JOIN {$db_name_storage}.nomenclature_types nt ON nt.id = n.id_type
					WHERE 1
						AND p.to_arc = 0
						AND p.id_limit_card = {$nIDLimitCard}
						AND p.id_limit_card > 0
					GROUP BY pe.id_nomenclature
			";
			
			$tmpArr = array();
			$sum = 0;
			$tmpArr = $this->select( $sQuery );
			
			foreach( $tmpArr as $val )
			{
				if( $val['nomenclature_count'] < 0 )
				{
					$sum += abs( $val['price'] );
				}
			}
			
			return $sum;
		}
		
		public function getPersonsByID( $aData ) {
			global $db_name_personnel;
			
			$nID = (int) $aData['id'];
			$nIDCard = (int) $aData['id_card'];
			
			if ( $nID > 0 ) {
				$sQuery = "
					SELECT
						lcp.id,
						lcp.id_limit_card,
						lcp.id_person,
						p.id_office,
						lcp.percent,
						o.id_firm,
						(SELECT SUM(percent) FROM limit_card_persons WHERE id_limit_card = {$nIDCard} AND id != {$nID} GROUP BY id_limit_card) AS sum_percent,
						(SELECT GROUP_CONCAT(id_person) FROM limit_card_persons WHERE id_limit_card = {$nIDCard} AND id != {$nID} GROUP BY id_limit_card) AS persons
					FROM limit_card_persons lcp
					LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person
					LEFT JOIN offices o ON o.id = p.id_office
					WHERE lcp.id = {$nID}
				";
			} else {
				$sQuery = "
					SELECT
						(SELECT SUM(percent) FROM limit_card_persons WHERE id_limit_card = {$nIDCard} GROUP BY id_limit_card) AS sum_percent,
						(SELECT GROUP_CONCAT(id_person) FROM limit_card_persons WHERE id_limit_card = {$nIDCard} GROUP BY id_limit_card) AS persons
					FROM limit_card_persons lcp
					LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person
					WHERE lcp.id_limit_card = {$nIDCard}
					LIMIT 1
				";
			} 
			
			$aTmp = array();
			$aTmp = $this->selectOnce( $sQuery );
			
			if ( !isset($aTmp['id']) ) {
				$aTmp['id'] = $nID;
				$aTmp['id_limit_card'] = $nIDCard;
				$aTmp['id_person'] = 0;
				$aTmp['id_office'] = 0;
				$aTmp['percent'] = 0;
			}
		
			return $aTmp;
		}

		public function getPercentByLC( $nID ) {
			global $db_name_personnel;
			
			$nIDCard = (int) $nID;
			
			$sQuery = "
				SELECT
					SUM(lcp.percent) AS percent
				FROM limit_card_persons lcp
				WHERE lcp.id_limit_card = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getFirstPercentByLC( $nID ) {
			global $db_name_personnel;
			
			$nIDCard = (int) $nID;
			
			$sQuery = "
				SELECT
					MIN(lcp.id) as id,
					lcp.percent
				FROM limit_card_persons lcp
				WHERE lcp.id_limit_card = {$nID}
				GROUP BY lcp.id_limit_card
			";

			return $this->selectOnce( $sQuery );
		}

		public function getPersonByLC( $nID ) {
			global $db_name_personnel;
			
			$nIDCard = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT
					lcp.id,
					lcp.id_person,
					lcp.percent,
					(p.tech_support_factor * o.factor_tech_support) AS factor,
					se.code AS code,
					se.name AS description,
					p.id_office,
					p.id_region_object
				FROM limit_card_persons lcp
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person
				LEFT JOIN {$db_name_personnel}.salary_earning_types se ON se.source = 'limit_card'
				LEFT JOIN offices o ON o.id = p.id_office				
				WHERE lcp.id_limit_card = {$nID}
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function delPersonByLC( $nID ) {
			global $db_name_personnel;
			
			$nIDCard = is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				DELETE FROM limit_card_persons
				WHERE id_limit_card = {$nID}
			";
			
			$this->oDB->Execute( $sQuery );
		}
		
		public function setSalary( $aData ) {
			global $db_name_personnel;
			
			$nIDPerson = (int) $aData['id_person'];
			$price = $aData['price'];
			$nIDLC = (int) $aData['nIDLC'];
			$month = $aData['month'];
			$code = $aData['code'];
			$description = $aData['description'];
			$nIDOffice = (int) $aData['id_office'];
			$rObject = (int) $aData['id_region_object'];
			$nIDObject = !empty($aData['nIDObject']) ? (int) $aData['nIDObject'] : $rObject;
				
			$sQuery = "
				INSERT INTO {$db_name_personnel}.salary 
					(id_person, 
					id_office, 
					id_object, 
					id_object_duty, 
					month, 
					code, 
					is_earning, 
					sum, 
					description, 
					count, 
					total_sum )
				VALUES
					('{$nIDPerson}',
					'{$nIDOffice}',
					'{$nIDObject}',
					'{$nIDLC}',
					'{$month}',
					'{$code}',
					1,
					'{$price}',
					'{$description}',
					1,
					'{$price}' )
			";
			
			$this->oDB->Execute( $sQuery );
		}

		public function getPrices( ) {
			
			$sQuery = "
				SELECT
					ts.type,
					ts.price
				FROM tech_support ts
				WHERE ts.type IN ('destroy', 'holdup', 'arrange')
			";
			
			return $this->selectAssoc( $sQuery );
		}

		public function getPersonDub( $aData ) {
			global $db_name_personnel;
			
			$nIDObj = (int) $aData['obj'];
			$nIDPer = (int) $aData['per'];
			$start = $aData['start'];
			$end = $aData['end'];

			$sQuery = "
				SELECT 
					lcp.id_limit_card
				FROM limit_card_persons lcp
				LEFT JOIN tech_limit_cards tlc ON lcp.id_limit_card = tlc.id
				WHERE lcp.id_person = {$nIDPer}
					AND tlc.id_object != {$nIDObj}
					AND (
							(UNIX_TIMESTAMP(tlc.planned_start) + 1) BETWEEN UNIX_TIMESTAMP('{$start}') AND UNIX_TIMESTAMP('{$end}')	
							OR (UNIX_TIMESTAMP(tlc.planned_end) - 1) BETWEEN UNIX_TIMESTAMP('{$start}') AND UNIX_TIMESTAMP('{$end}')	
						)	
					AND UNIX_TIMESTAMP(tlc.planned_start) > 0
					AND UNIX_TIMESTAMP(tlc.planned_end) > 0
			";

			return $this->selectOnce( $sQuery );
		}

		public function getPersonsByDate( $aData ) {
			global $db_name_personnel;
			
			$nID = (int) $aData['id'];
			//$sDate = $aData['sDate'];
			
			$sQuery = "
				SELECT
					lcp.id as _id,
					lcp.id as id,
					tlc.planned_start,
					tlc.planned_end,
					lcp.id_person
				FROM limit_card_persons lcp
				LEFT JOIN tech_limit_cards tlc ON tlc.id = lcp.id_limit_card
				WHERE lcp.id_person = {$nID}
			";

		
			return $this->selectAssoc( $sQuery );			
		}

		public function getPersonsByLC( $nID ) {
			global $db_name_personnel;
			
			$nID = (int) $nID;
			
			$sQuery = "
				SELECT
					group_concat(lcp.id_person) as id,
					tlc.planned_start
				FROM limit_card_persons lcp
				LEFT JOIN tech_limit_cards tlc ON tlc.id = lcp.id_limit_card
				WHERE lcp.id_limit_card = {$nID}
				GROUP BY tlc.id							
			";
		
			return $this->select( $sQuery );			
		}
		
		public function delPersonsByIDLimitCard( $nID ) {
			$sQuery = "
				DELETE 
				FROM limit_card_persons
				WHERE id_limit_card = {$nID}
			";
			$this->oDB->Execute($sQuery);
		}

		public function getPercent( $nIDLimitCard , $nIDPerson ) {
			
			$sQuery = "
				SELECT 
					percent
				FROM limit_card_persons
				WHERE 1
					AND id_limit_card = {$nIDLimitCard}
					AND id_person = {$nIDPerson}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getIDsOfClosedLimitCard() {
			
			$sQuery = "
				SELECT
					lcp.id
				FROM limit_card_persons lcp
				LEFT JOIN tech_limit_cards tlc ON tlc.id = lcp.id_limit_card
				WHERE tlc.status = 'cancel'
			";
			
			return $this->select($sQuery);
		}
		
		public function getPersonsWith( $nIDLimitCard, $nIDPerson) {
			
			global $db_name_personnel;
			
			$sQuery = "
				SELECT
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS person,
					p.mobile
				FROM limit_card_persons lcp
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = lcp.id_person 
				WHERE 1
					AND lcp.id_limit_card = {$nIDLimitCard}
					AND lcp.id_person != {$nIDPerson}
			";
			
			return $this->select($sQuery);
		}
		
		
		public function deleteByLimitCardId( $nID )
		{
		 
			global  $db_name_sod;
			
			$sQuery = "
			
				DELETE 
					FROM 
						{$db_name_sod}.limit_card_persons  

					WHERE 
						id_limit_card = '{$nID}'
			
			
			";
			
			if( $nID > 0 )
			{
				$this->select( $sQuery );
			}
			
			
		}
		
		
	}

?>