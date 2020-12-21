<?php
	class DBObjectDuty
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'object_duty');
		}
		
		public function getReport( $params, DBResponse $oResponse ) {
			global $db_name_personnel;
			$obj = (int) $params['nID'];
			$nTime = $params['nTime'];
			$nStep = $params['nStep'];
			
			$right_edit = false;
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('object_duty_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$oData = array();
			$date = new DateTime(date("Y-m-d", $nTime));
			$date->modify("-1 month");
			$monPrev = $date->format("m");
			$yePrev = $date->format("Y");
			
			$monNow = date("m", $nTime);
			$yeNow = date("Y", $nTime);
			
			$sQuery = "
				(SELECT 
					DISTINCT(id_person),
					CONCAT_WS(' ', pe.fname, pe.mname, pe.lname) AS person
				FROM object_duty od
				LEFT JOIN {$db_name_personnel}.personnel as pe ON od.id_person = pe.id
				WHERE 
					( 
						( MONTH(od.startShift) = '{$monNow}' AND YEAR(od.startShift) = '{$yeNow}' ) 
						OR ( MONTH(od.startShift) = '{$monPrev}' AND YEAR(od.startShift) = '{$yePrev}' )
					)
					AND UNIX_TIMESTAMP(od.endRealShift) = 0
					AND od.id_obj = {$obj}
					AND od.id_shift > 0
				ORDER BY od.id_person)
			";
			//APILog::Log(0, $sQuery);
			$this->getResult( $sQuery, 'person', DBAPI_SORT_ASC, $oResponse );			
//
//				UNION
//
//				(SELECT 
//					DISTINCT(id_person), 
//					CONCAT_WS(' ', pe.fname, pe.mname, pe.lname) AS person
//				FROM object_personnel op
//				LEFT JOIN {$db_name_personnel}.personnel as pe ON op.id_person = pe.id
//				WHERE op.id_object = {$obj}
//				ORDER BY op.id_person)
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				if ( $nStep == 0 ) {
					$qry = "
						SELECT 
							ob.id,
							IF ( UNIX_TIMESTAMP(MIN(distinct ob.startShift)) = '{$nTime}', 1, 0 ) AS chk2,
							IF ( UNIX_TIMESTAMP(MIN(distinct ob.endShift)) = '{$nTime}', 1, 0 ) AS chk1,
							ob.note,
							CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(ob.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
						FROM object_duty ob
						LEFT JOIN {$db_name_personnel}.personnel as up ON ob.updated_user = up.id
						WHERE ob.id_person = '{$val['id_person']}'
							AND ob.id_obj = '{$obj}'
							AND ( 
								UNIX_TIMESTAMP(ob.startShift) = '{$nTime}'
								OR UNIX_TIMESTAMP(ob.endShift) = '{$nTime}'
							)
							AND (	
									UNIX_TIMESTAMP(startRealShift) = 0
									OR UNIX_TIMESTAMP(endRealShift) = 0
								)
							AND UNIX_TIMESTAMP(endShift) > UNIX_TIMESTAMP(startShift)
						GROUP BY ob.id_person
					";
				} else {
					$qry = "
						SELECT 
							ob.id,
							IF ( UNIX_TIMESTAMP(MAX(distinct ob.startRealShift)) = '{$nTime}', 1, 0 ) AS chk2,
							IF ( UNIX_TIMESTAMP(MAX(distinct ob.endRealShift)) = '{$nTime}', 1, 0 ) AS chk1,
							ob.note,
							CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(ob.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
						FROM object_duty ob
						LEFT JOIN {$db_name_personnel}.personnel as up ON ob.updated_user = up.id
						WHERE ob.id_person = '{$val['id_person']}'
							AND ob.id_obj = '{$obj}'
							AND ( 
								UNIX_TIMESTAMP(ob.startRealShift) = '{$nTime}'
								OR UNIX_TIMESTAMP(ob.endRealShift) = '{$nTime}'
							)
							AND UNIX_TIMESTAMP(startRealShift) > 0
							AND UNIX_TIMESTAMP(endRealShift) > 0
							AND UNIX_TIMESTAMP(endShift) > UNIX_TIMESTAMP(startShift)
						GROUP BY ob.id_person
					";
				}
				
				$aData = $this->selectOnce( $qry );

				if ( isset($val['id']) && $val['id'] == 0 ) {
					$oResponse->setDataAttributes( $key, 'note', array('style' => 'width:100px;', 'disabled' => 'disabled', 'readonly' => 'readonly'));					
				} else {
					$oResponse->setDataAttributes( $key, 'note', array('style' => 'width:100px; text-align:left;'));
					$oResponse->setDataAttributes( $key, 'updated_user', array('style' => 'text-align:center;'));
				}
				
				if ( isset($aData['id']) ) {				
					$val['id'] = $aData['id'].",".$val['id_person'];
					$val['note'] = $aData['note'];
					$val['updated_user'] = $aData['updated_user'];
				} else {
					$val['updated_user'] = '';
					$val['note'] = '';
					$val['id'] = "0,".$val['id_person'];;
				}

				$val['chk'] = isset($aData['chk1']) ? $aData['chk1'] : 0;
				$val['chk2'] = isset($aData['chk2']) ? $aData['chk2'] : 0;
				//debug($aData);
				$oResponse->setDataAttributes( $key, 'chk', array('style' => 'text-align:center;'));
				$oResponse->setDataAttributes( $key, 'chk2', array('style' => 'text-align:center;'));
				
				if ( ($nStep == 0) && ($val['chk'] == 0) ) {
					$oResponse->setDataAttributes( $key, 'chk', array('style' => 'text-align:center;', 'disabled' => 'disabled'));
				}
				
				if ( $nStep != 0 ) {
					$oResponse->setDataAttributes( $key, 'chk2', array('style' => 'text-align:center;', 'disabled' => 'disabled'));
					$oResponse->setDataAttributes( $key, 'chk', array('style' => 'text-align:center;', 'disabled' => 'disabled'));
				}
				
			}
			//debug($oResponse->oResult->aData);
			$oResponse->setField( 'person',			'служител',		'Сортиране по служител');
			$oResponse->setField( 'chk',			'сдава',		'Сортиране по сдаване', NULL, NULL, NULL, array('style' => 'width: 60px;') );
			$oResponse->setField( 'chk2',			'приема',		'Сортиране по приемане', NULL, NULL, NULL, array('style' => 'width: 60px;') );
			$oResponse->setField( 'note',			'забележка',	'Сортиране по забележка' );
			$oResponse->setField( 'updated_user',	'...',			'Сортиране по последно редактирал', 'images/dots.gif', NULL, NULL, array('style' => 'text-align: center; width: 35px;') );
			
			if ( $right_edit ) {
				$oResponse->setFieldLink("person", "openPerson");
			}
			
			$oResponse->setFieldData( 'chk', 'input', array('type' => 'checkbox', 'exception' => 'true') );
			$oResponse->setFieldData( 'chk2', 'input', array('type' => 'checkbox', 'exception' => 'true') );
			$oResponse->setFormElement(		'form1',	'note_'	, array() );
			$oResponse->setFieldAttributes( 'note',	array('type' => 'text', 'ref' => 'note_' ) );
		}

		public function getShift( $params ) {
			$obj = (int) $params['nID'];
			
			$sQuery = "
				SELECT
					id,
					IF (UNIX_TIMESTAMP(MIN(distinct startRealShift)) > 0, UNIX_TIMESTAMP(MIN(distinct endShift)), UNIX_TIMESTAMP(MIN(distinct startShift)) ) AS nTime
				FROM object_duty
				WHERE 1
					AND id_shift > 0
					AND (	
							UNIX_TIMESTAMP(startRealShift) = 0
							OR UNIX_TIMESTAMP(endRealShift) = 0
						)
					AND UNIX_TIMESTAMP(endShift) > UNIX_TIMESTAMP(startShift)
					AND id_obj = {$obj}
				GROUP BY id
				ORDER BY nTime ASC
				LIMIT 1				
			";
			
			return $this->selectOnce( $sQuery );
			
//			$data = array();
//			$data = $this->selectAssoc( $sQuery );
//			$data = array_unique( $data );
//			natsort( $data );
//			reset( $data );
//			$data = array_values( $data );
//			
//			//APILog::Log(0, $data);
//
//			$data['nTime'] = isset($data[0]) ? $data[0] : "";
//			return $data;
		}

		public function getPrevShiftOnce( $params ) {
			$obj = (int) $params['nID'];
			
			$sQuery = "
				SELECT
					IF (UNIX_TIMESTAMP(MAX(startShift)) > UNIX_TIMESTAMP(MAX(endShift)), UNIX_TIMESTAMP(MAX(startShift)), UNIX_TIMESTAMP(MAX(endShift)) ) AS pTime
				FROM object_duty
				WHERE 1
					AND UNIX_TIMESTAMP(startRealShift) > 0
					AND UNIX_TIMESTAMP(endRealShift) > 0
					AND UNIX_TIMESTAMP(endShift) > UNIX_TIMESTAMP(startShift)
					AND id_obj = {$obj}
				GROUP BY id_obj
			";
			
			return $this->selectOnce( $sQuery );
		}
		

		public function getNextShift( $params ) {
			$tmp = array();
			$time = $params['nTime'];
			if ( empty($time) ) $time = time();
			$obj = (int) $params['nID'];
			
			$sQuery = "
				SELECT
					MIN( UNIX_TIMESTAMP(startShift) ) as n1,
					MIN( UNIX_TIMESTAMP(endShift) ) as n2
				FROM object_duty
				WHERE 1
					AND UNIX_TIMESTAMP( endShift ) > {$time}
					AND id_obj = {$obj}
			";
			
			$tmp = $this->selectOnce( $sQuery );
			
			if ( ($tmp['n1'] < $tmp['n2']) && ($tmp['n1'] > $time) ) {
				return $tmp['n1']; 
			} else return $tmp['n2'];
		}

		public function getPrevShift( $params ) {
			$tmp = array();
			$time = $params['nTime'];
			if ( empty($time) ) $time = time();
			$obj = (int) $params['nID'];
			
			$sQuery = "
				SELECT
					MAX( UNIX_TIMESTAMP(startShift) ) as n1,
					MAX( UNIX_TIMESTAMP(endShift) ) as n2
				FROM object_duty
				WHERE 1
					AND UNIX_TIMESTAMP( startShift ) < {$time}
					AND id_obj = {$obj}
			";
			
			$tmp = $this->selectOnce( $sQuery );

			if ( ($tmp['n2'] > $tmp['n1']) && ($tmp['n2'] < $time) ) {
				return $tmp['n2']; 
			} else return $tmp['n1'];
		}
		
		public function getObjectName( $nID ) {
			$nID = (int) $nID;
			
			$sQuery = "
				SELECT
					ob.id, 
					CONCAT('[', ob.num, '] ', ob.name) as object,
					ob.num,
					ob.is_sod,
					ob.is_fo
				FROM objects ob
				WHERE 1
					AND ob.id = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function erase( $aData ) {
			if( empty( $aData['nIDObj'] ) || !is_numeric( $aData['nIDObj'] ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
					
			$nIDObj = $aData['nIDObj'];					
			$nIDStart = $aData['nIDStart'];					
			$nIDStop = $aData['nIDStop'];					
				
			$this->StartTrans();				
											
			if ( !empty($nIDStart) ) {
				$sQuery = "
					UPDATE object_duty 
					SET startRealShift = '0000-00-00 00:00:00'
					WHERE id IN ({$nIDStart})
				";
				
				$this->oDB->Execute( $sQuery );
			}

			if ( !empty($nIDStop) ) {
				$sQuery = "
					DELETE 
					FROM personnel.salary
					WHERE id_object = {$nIDObj}
						AND id_object_duty IN ({$nIDStop})
				";
				
				$this->oDB->Execute( $sQuery );

				$sQuery = "
					UPDATE object_duty 
					SET endRealShift = '0000-00-00 00:00:00'
					WHERE id IN ({$nIDStop})
				";
				
				$this->oDB->Execute( $sQuery );
			}

			$this->CompleteTrans();
		}
		
		public function validate( $aData ) {
			$nTime		= $aData['nTime'];  // Време за РЕАЛНА смяна (timestamp)
			$nIDObject	= $aData['nIDObj'];
			$chk		= isset($aData['chk']) ? $aData['chk'] : NULL;		// Предават
			$chk2		= isset($aData['chk2']) ? $aData['chk2'] : NULL;	// Приемат
			
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nTime ) || !is_numeric( $nTime ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$nMonth = date("m", $nTime);
			$nYear  = date("Y", $nTime);
			
			$qry1 = "
				UPDATE object_duty
				SET startRealShift = FROM_UNIXTIME({$nTime})
				WHERE id_obj = {$nIDObject}
					AND id IN ({$chk2})
					AND id_shift > 0
			";

			$qry2 = "
				UPDATE object_duty
				SET endRealShift = FROM_UNIXTIME({$nTime})
				WHERE id_obj = {$nIDObject}
					AND id IN ({$chk})
			";
			
//			$qry3 = "
//				UPDATE 
//					object_duty
//				SET 
//					startRealShift = startShift,
//					endRealShift = endShift	
//				WHERE id_obj = {$nIDObject}
//					AND id_shift = 0
//					AND endShift <= FROM_UNIXTIME({$nTime})
//			";

			$qry3 = "
				UPDATE 
					object_duty
				SET 
					startRealShift = startShift,
					endRealShift = FROM_UNIXTIME({$nTime})
				WHERE id_obj = {$nIDObject}
					AND id_shift = 0
					AND endShift <= FROM_UNIXTIME({$nTime})
			";
			
			//APILog::log(0, $qry3);
			
			$qryOUT = "
				SELECT 
					od.id,
					UNIX_TIMESTAMP(od.startRealShift) as start,
					od.startRealShift,
					os.stake,
					os.code,
					os.automatic
				FROM object_duty od
				LEFT JOIN object_shifts os ON os.id = od.id_shift
				WHERE od.id IN ({$chk})
			";
			
			$rsOUT = array();
			
			$oHolidays = new DBHolidays();
						
			$this->StartTrans();
		
			try {
				if ( $chk ) $rsOUT = $this->selectAssoc( $qryOUT );
				
				foreach ( $rsOUT as $key => &$val ) {
					$nStartDaySeconds 	= 0;	// Продължителността в секунди на първия ден от смяната, ако смяната е в рамките на деня, ще съдържа продължителността на цялата смяна
					$nEndDaySeconds 	= 0;	// Продължителността в ... на втория ден .., ако смяната е в рамките на един ден, то стойността е 0
										
					if ( date("d", $val['start']) != date("d", $nTime) ) {						
						$nNextDayOffsetSeconds = 24*60*60;
						
						$nStartDaySeconds = mktime(
							0, 
							0, 
							0, 
							date("m", $val['start'] + $nNextDayOffsetSeconds),
							date("d", $val['start'] + $nNextDayOffsetSeconds),
							date("Y", $val['start'] + $nNextDayOffsetSeconds)
							) 
							- 
							$val['start'];
							
						$nEndDaySeconds = 
							$nTime 
							- 
							mktime(
								0, 
								0, 
								0, 
								date("m", $nTime ),
								date("d", $nTime ),
								date("Y", $nTime )
							);
					} else {
						$nStartDaySeconds 	= $nTime - $val['start'];
						$nEndDaySeconds 	= 0;
					}
										
					$nStartDayStakeFactor = 1.0;
					
					if ( $oHolidays->isHoliday( date("d", $val['start'] ), date("m", $val['start'] ) ) && empty($val['automatic']) ) {
						
						// Павел - 4.12.2008 - премахвам празничните наработки
						//$nStartDayStakeFactor = $_SESSION['system']['holiday_stake_factor'];
						//APILog::Log(0, $nStartDayStakeFactor);
					}
				
					$nEndDayStakeFactor = 1.0;
					
					if ( $oHolidays->isHoliday( date("d", $nTime ), date("m", $nTime ) ) && empty($val['automatic']) ) {
						// Павел - 4.12.2008 - премахвам празничните наработки
						//$nEndDayStakeFactor = $_SESSION['system']['holiday_stake_factor'];
						//APILog::Log(0, $nEndDayStakeFactor);
					}
						
					//$val['stake'] = round(( ( ( $nStartDaySeconds * $nStartDayStakeFactor + $nEndDaySeconds * $nEndDayStakeFactor ) / 3600 ) * $val['stake'] ) / (( $nTime - $val['start'] ) / 3600), 2);
				
					$qryStake = "
						UPDATE 
							object_duty
						SET 
							stake = '{$val['stake']}'
						WHERE id = {$key}
					";
					
					$this->oDB->Execute( $qryStake );
				}
				
				unset($val);
							
				$this->CompleteTrans();
			} catch( Exception $e ) {
				$this->FailTrans();
			}
					
			//APILog::Log( 0, $rsOUT );

					
			$oSalary = new DBSalary();
			
			$this->StartTrans();
			$oSalary->StartTrans();

			try 
			{
				if ( $chk2 ) $this->oDB->Execute( $qry1 );
				if ( $chk ) $this->oDB->Execute( $qry2 );
				
				$this->oDB->Execute( $qry3 );
				
				$oSalary->objectSalaryFromDuty( $nIDObject, $nTime, $chk );
				$oSalary->CompleteTrans();
				
				$this->CompleteTrans();
			} 
			catch( Exception $e ) 
			{
				$this->FailTrans();
				$oSalary->FailTrans();
			}
		}
		
		public function getObjectYearMonth()
		{
			$sQuery = "
				SELECT DISTINCT 
					DATE_FORMAT(startShift, '%Y%m'),
					DATE_FORMAT(startShift, '%Y-%m')
				FROM object_duty
				WHERE 1
				";
			
			return $this->selectAssoc( $sQuery );
		}

		public function isExistMonth($sMonth) {
			$sQuery = "
				SELECT
					id
				FROM object_duty
				WHERE 1
					AND DATE_FORMAT(startShift, '%Y-%m') = '{$sMonth}'
				LIMIT 1
			";
			
			$nResult = $this->selectOnce($sQuery);
			
			if (!empty($nResult)) return 1;
			return 0;
		}
		
		public function clearAllInvalidatedMonthDutiesFromObject( $nIDObject, $nYear, $nMonth )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nYear ) || !is_numeric( $nYear ) || strlen( $nYear ) != 4  )
				throw new Exception($nYear, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				DELETE 
				FROM object_duty
				WHERE id_obj = {$nIDObject}
				AND YEAR( startShift ) = {$nYear}
				AND MONTH( startShift ) = {$nMonth}
				AND startRealShift = 0
				";
			
			$this->oDB->Execute( $sQuery );
		}
		
		public function checkObjectDutyAvaliability( $nIDPerson, $nIDObject, $nTimeFrom, $nTimeTo )
		{
			global $db_name_personnel;
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nTimeFrom ) || !is_numeric( $nTimeFrom ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $nTimeTo ) || !is_numeric( $nTimeTo ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			/*
				@autor dido2k
				proverka za zastypvane na smqna
				
				variant 1. |--|==|==|
				variant 2. |==|==|--|
				variant 3. |--|==|--|
				variant 4. |==|==|==| avtomati4no se izkliu4va ot pyrvite 2!
			*/
			 
			$sQuery = "
				SELECT 
					od.id,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS person,
					CONCAT_WS('\n', 
						CONCAT('смяна:\t', os.code),
						CONCAT('обект:\t', o.num, ' ', o.name),
						CONCAT('от:\t'	, DATE_FORMAT(od.startShift, '%d.%m.%Y %H:%i')),
						CONCAT('до:\t'	, DATE_FORMAT(od.endShift, '%d.%m.%Y %H:%i'))
						) AS shift
				FROM object_duty od
				LEFT JOIN object_shifts os ON od.id_shift = os.id
				LEFT JOIN {$db_name_personnel}.personnel p ON od.id_person = p.id
				LEFT JOIN objects o ON od.id_obj = o.id
				WHERE 1
				AND od.id_shift > 0
				AND od.id_obj <> {$nIDObject}
				AND od.id_person = {$nIDPerson}
				AND 
				(
					( $nTimeFrom > UNIX_TIMESTAMP( od.startShift ) AND $nTimeFrom < UNIX_TIMESTAMP( od.endShift ) )
					OR
					( $nTimeTo   > UNIX_TIMESTAMP( od.startShift ) AND $nTimeTo   < UNIX_TIMESTAMP( od.endShift ) )
					OR 
					( $nTimeFrom < UNIX_TIMESTAMP( od.startShift ) AND $nTimeTo   > UNIX_TIMESTAMP( od.endShift ) )
				)
				LIMIT 1
				";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )
			{
				$sMessage = "{$aData['person']} има смяна в друг обект:\n\n{$aData['shift']}";
				throw new Exception($sMessage, DBAPI_ERR_UNKNOWN);
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function getObjectDuty( $nIDObject, $nYear, $nMonth, $bAssoc = TRUE )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nYear ) || !is_numeric( $nYear ) || strlen( $nYear ) != 4 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT 
					od.id as _id,
					DAY( od.startShift ) as `day`,
					od.*
				FROM object_duty od
				WHERE id.id_obj = {$nIDObject}
				AND YEAR( od.startShift ) = {$nYear}
				AND MONTH( od.endShift ) = {$nMonth}
				";
			
			return $bAssoc ? $this->selectAssoc( $sQuery ) : $this->select( $sQuery );
		}
		
		public function checkAutomatic( $nIDShift ) {
			if( empty( $nIDShift ) || !is_numeric( $nIDShift ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
								
			$sQuery = "
				SELECT 
					os.automatic
				FROM object_shifts os
				WHERE os.id = {$nIDShift}
			";

			$auto = $this->selectOnce( $sQuery );
			return !empty($auto) ? $auto['automatic'] : 0;
		}
		
		public function invalidate($nIDObject, $nTimeTo)
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nTimeTo ) || !is_numeric( $nTimeTo ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$nMonth = date("m", $nTimeTo);
			$nYear  = date("Y", $nTimeTo);
			
			$sQuery1 = "
				DELETE 
				FROM object_duty
				WHERE id_obj = {$nIDObject}
					AND UNIX_TIMESTAMP( startShift ) >= {$nTimeTo}
					AND MONTH( startShift ) = {$nMonth}
					AND startShift = 0 AND endShift = 0
					AND startRealShift = 0 AND endRealShift = 0
				";
			
			$sQuery2 = "
				UPDATE
					object_duty od, object_shifts os
				SET
					od.startRealShift = 0,
					od.endRealShift = 0
				WHERE
					( os.id = od.id_shift OR od.id_shift = 0 )
					AND od.id_obj = {$nIDObject}
					AND UNIX_TIMESTAMP( od.startShift ) >= {$nTimeTo}
					AND MONTH( od.startShift ) = {$nMonth}
					AND os.mode != 'leave'
			";
			
			$oSalary = new DBSalary();
			
			$this->StartTrans();
			$oSalary->StartTrans();
			
			try 
			{
				$this->oDB->Execute( $sQuery1 );
				$this->oDB->Execute( $sQuery2 );
				
				$oSalary->transferMonthObjectSalaryFromDuty($nIDObject, $nYear, $nMonth);
				$oSalary->CompleteTrans();
				
				$this->CompleteTrans();
			}
			catch( Exception $e )
			{
				$this->FailTrans();
				$oSalary->FailTrans();
			}
		}
		
		public function IsPersonDutyExistsOnDate( $nIDObject, $nIDPerson, $nYear, $nMonth, $nDay )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nYear ) || !is_numeric( $nYear ) || strlen( $nYear ) != 4 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nDay ) || !is_numeric( $nDay ) || $nDay < 1 || $nDay > 31 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT id
				FROM object_duty
				WHERE 1
					AND id_obj = {$nIDObject}
					AND id_person = {$nIDPerson}
					AND YEAR( startShift ) = {$nYear}
					AND MONTH( startShift ) = {$nMonth}
					AND DAY( startShift ) = {$nDay}
				LIMIT 1
				";
			
			$aResult = $this->select( $sQuery );
			
			return !empty( $aResult );
		}
		
		public function countPersonShifts( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$oHolidays 					= new DBHolidays();
			$oNorms 					= new DBScheduleMonthNorms();
			$oLeaves					= new DBLeaves();
			$oDBPersonMonthLimits 		= new DBPersonMonthLimits();
			$oDBPersonnel				= new DBPersonnel();
			$oDBFiltersVisibleFields 	= new DBFiltersVisibleFields();
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'personnel_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			//Getting All Persons
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						p.id,
						p.id AS id_person,
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS name,
						DATE_FORMAT( od.startShift, '%Y%m' ) AS record_start
					FROM object_duty od
						LEFT JOIN {$db_name_personnel}.personnel p ON od.id_person = p.id
						LEFT JOIN offices o ON p.id_office = o.id
						LEFT JOIN firms f ON o.id_firm = f.id
						LEFT JOIN object_shifts os ON os.id = od.id_shift
					WHERE 1
						AND p.id != 0
						AND od.id_shift != 0
						AND ( od.startRealShift != '0000-00-00 00:00:00' AND od.endRealShift != '0000-00-00 00:00:00' )
			";
			
			//Filtering Conditions
			if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
			{
				$sQuery .= "
						AND f.id = {$aParams['nIDFirm']}
				";
			}
			
			if( isset( $aParams['nIDRegion'] ) && !empty( $aParams['nIDRegion'] ) )
			{
				$sQuery .= "
						AND o.id = {$aParams['nIDRegion']}
				";
			}
			
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$sQuery .= "
						AND o.id IN ({$sAccessable})
				";
			}
			
			if( isset( $aParams['sShiftType'] ) && $aParams['sShiftType'] != 'all' )
			{
				$sQuery .= "
						AND os.mode = '{$aParams['sShiftType']}'
				";
			}
			
			if( isset( $aParams['sNameStart'] ) && !empty( $aParams['sNameStart'] ) )
			{
				$sNameStart = addslashes( $aParams['sNameStart'] );
				$sQuery .= "
						AND CONCAT_WS( ' ', p.fname, p.mname, p.lname ) LIKE '{$sNameStart}%'
				";
			}
			
			//--Year and Month
			$nFilterEYear = $nFilterEMonth = $nFilterSYear = $nFilterSMonth = 0;
			
			if( $aParams['nRadio'] == 1 )
			{
				if( isset( $aParams['sYearMonth'] ) && !empty( $aParams['sYearMonth'] ) )
				{
					$aYearMonth = explode( "-", $aParams['sYearMonth'] );
					if( isset( $aYearMonth[0] ) && isset( $aYearMonth[1] ) )
					{
						$nFilterSYear 	= $nFilterEYear		= ( int ) $aYearMonth[0];
						$nFilterSMonth 	= $nFilterEMonth 	= ( int ) $aYearMonth[1];
					}
					
					$sQuery .= "
							AND od.startShift LIKE '{$aParams['sYearMonth']}%'
					";
				}
			}
			
			if( $aParams['nRadio'] == 2 )
			{
				if( isset( $aParams['date_from'] ) && !empty( $aParams['date_from'] ) )
				{
					$dFrom = jsDateToTimestamp( $aParams['date_from'] );
					
					$nFilterSYear 	= ( int ) date( "Y", $dFrom );
					$nFilterSMonth	= ( int ) date( "m", $dFrom );
					
					$sQuery .= "
							AND UNIX_TIMESTAMP( od.startShift ) >= '{$dFrom}'
					";
				}
				
				if( isset( $aParams['date_to'] ) && !empty( $aParams['date_to'] ) )
				{
					$dTo = $this->jsDateEndToTimestamp( $aParams['date_to'] );
					
					$nFilterEYear 	= ( int ) date( "Y", $dTo );
					$nFilterEMonth	= ( int ) date( "m", $dTo );
					
					$sQuery .= "
							AND UNIX_TIMESTAMP( od.startShift ) <= '{$dTo}'
					";
				}
			}
			//--End Year and Month
			
			if( isset( $aParams['nIDPosition'] ) && !empty( $aParams['nIDPosition'] ) )
			{
				$sQuery .= "
						AND p.id_position = {$aParams['nIDPosition']}
				";
			}
			//End Filtering Conditions
			
			$sQuery .= "
					GROUP BY od.id_person
			";
			
			$aFinalData = $this->select( $sQuery );
			
			//Define ColSpans
			$nColSpanPerson = 2;
			$nColSpanDay = 0;
			$nColSpanNight = 0;
			$nColSpanSick = 0;
			$nColSpanLeave = 0;
			$nColSpanOverall = 0;
			$nColSpanEnding = 0;
			//End Define ColSpans
			
			if( !empty( $aParams['nIDScheme'] ) )
			{
				$aVisibleFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $aParams['nIDScheme'] );
			}
			
			$oResponse->setField( "name", "Име на Служител", "Сортирай по Име", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			if( empty( $aParams['nIDScheme'] ) || in_array( "nShiftsCount", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_count", "Брой Смени", "Сортирай по Брой", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
				$nColSpanPerson++;
			}
			
			if( empty( $aParams['nIDScheme'] ) || in_array( "nDayShifts", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_day_count", "Бр. Смени", "Сортирай по Бр. Смени", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$oResponse->setField( "shifts_day_hours", "Часове", "Сортирай по Часове", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$nColSpanDay += 2;
			}
			
			if( empty( $aParams['nIDScheme'] ) || in_array( "nNightShifts", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_night_count", "Бр. Смени", "Сортирай по Бр. Смени", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$oResponse->setField( "shifts_night_hours", "Часове", "Сортирай по Часове", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$nColSpanNight += 2;
			}
			
			if( empty( $aParams['nIDScheme'] ) || in_array( "nSickDays", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_sick_count", "Бр. Дни", "Сортирай по Бр. Дни", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$oResponse->setField( "shifts_sick_hours", "Часове", "Сортирай по Часове", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$nColSpanSick += 2;
			}
			
			if( empty( $aParams['nIDScheme'] ) || in_array( "nLeaveDays", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_leave_count", "Бр. Дни", "Сортирай по Бр. Дни", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$oResponse->setField( "shifts_leave_hours", "Часове", "Сортирай по Часове", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$nColSpanLeave += 2;
			}
			
			if( empty( $aParams['nIDScheme'] ) || in_array( "nOverallShifts", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_total", "Бр. Смени", "Сортирай по Бр. Смени", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$oResponse->setField( "shifts_hours", "Часове", "Сортирай по Часове", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CENTER ) );
				$nColSpanOverall += 2;
			}
			
			if( empty( $aParams['nIDScheme'] ) || in_array( "nHolidayHours", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_rests", "Празнични Часове", "Сортирай по Празнични Часове", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_ZEROLEADNUM ) );
				$nColSpanEnding++;
			}
			if( empty( $aParams['nIDScheme'] ) || in_array( "nExtraHours", $aVisibleFields ) )
			{
				$oResponse->setField( "hours_over", "Извънр. часове", "Сортирай по Извънредни Часове" );
				$nColSpanEnding++;
			}
			if( empty( $aParams['nIDScheme'] ) || in_array( "nYearExtraHours", $aVisibleFields ) )
			{
				$oResponse->setField( "year_hours_over", "Изв. ч. за год.", "Сортирай по Извънредни Часове за Годината" );
				$nColSpanEnding++;
			}
			if( empty( $aParams['nIDScheme'] ) || in_array( "nNormHours", $aVisibleFields ) )
			{
				$oResponse->setField( "shifts_stdrd", "Брой по Норма", "Сортирай по Брой по Норма", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_ZEROLEADNUM ) );
				$nColSpanEnding++;
			}
			
			//Titles
			$nCurrentIndex = 1;
			if( !empty( $nColSpanPerson ) )		$oResponse->setTitle( 1, $nCurrentIndex, "Служител", 	array( "colspan" => $nColSpanPerson ) 	);
			$nCurrentIndex += $nColSpanPerson;
			if( !empty( $nColSpanDay ) )		$oResponse->setTitle( 1, $nCurrentIndex, "Дневни", 		array( "colspan" => $nColSpanDay ) 		);
			$nCurrentIndex += $nColSpanDay;
			if( !empty( $nColSpanNight ) )		$oResponse->setTitle( 1, $nCurrentIndex, "Нощни", 		array( "colspan" => $nColSpanNight ) 	);
			$nCurrentIndex += $nColSpanNight;
			if( !empty( $nColSpanSick ) )		$oResponse->setTitle( 1, $nCurrentIndex, "Болнични", 	array( "colspan" => $nColSpanSick ) 	);
			$nCurrentIndex += $nColSpanSick;
			if( !empty( $nColSpanLeave ) )		$oResponse->setTitle( 1, $nCurrentIndex, "Отпуск", 		array( "colspan" => $nColSpanLeave ) 	);
			$nCurrentIndex += $nColSpanLeave;
			if( !empty( $nColSpanOverall ) )	$oResponse->setTitle( 1, $nCurrentIndex, "Общо", 		array( "colspan" => $nColSpanOverall ) 	);
			$nCurrentIndex += $nColSpanOverall;
			if( !empty( $nColSpanEnding ) )		$oResponse->setTitle( 1, $nCurrentIndex, "", 			array( "colspan" => $nColSpanEnding ) 	);
			//End Titles
			
			$oResponse->setFieldAttributes( "name", array( 'width' => '250px' ) );
			
			foreach( $aFinalData as $key => $value )
			{
				//Set Search Criterions
				$sSearchCriterions = "
							1
							AND od.id_shift != 0
							AND ( od.startRealShift != '0000-00-00 00:00:00' AND od.endRealShift != '0000-00-00 00:00:00' )
							AND od.id_person = {$value['id_person']}
				";
				
				if( isset( $aParams['sShiftType'] ) && $aParams['sShiftType'] != 'all' )
				{
					$sSearchCriterions .= "
							AND os.mode = '{$aParams['sShiftType']}'
					";
				}
				
				if( $aParams['nRadio'] == 1 )
				{
					if( isset( $aParams['sYearMonth'] ) && !empty( $aParams['sYearMonth'] ) )
					{
						$sSearchCriterions .= "
							AND od.startShift LIKE '{$aParams['sYearMonth']}%'
						";
					}
				}
				
				if( $aParams['nRadio'] == 2 )
				{
					if( isset( $aParams['date_from'] ) && !empty( $aParams['date_from'] ) )
					{
						$dFrom = jsDateToTimestamp( $aParams['date_from'] );
						
						$sSearchCriterions .= "
							AND UNIX_TIMESTAMP( od.startShift ) >= '{$dFrom}'
						";
					}
					
					if( isset( $aParams['date_to'] ) && !empty( $aParams['date_to'] ) )
					{
						$dTo = $this->jsDateEndToTimestamp( $aParams['date_to'] );
						
						$sSearchCriterions .= "
							AND UNIX_TIMESTAMP( od.startShift ) <= '{$dTo}'
						";
					}
				}
				//End Set Search Criterions
				
				//Get Data
				$sQuery = "
						SELECT
							od.id,
							os.code AS code,
							COUNT( od.id_shift ) AS count,
							
							IF( os.automatic = 0, COUNT( od.id_shift ), 0 ) AS count_nonauto,
							SEC_TO_TIME( SUM( IF( os.automatic = 0, TIME_TO_SEC( os.duration ), 0 ) ) ) AS shifts_hours,
							
							IF( ( os.automatic = 0 AND os.mode = 'day' ), COUNT( od.id_shift ), 0 ) AS shifts_day_count,
							SEC_TO_TIME( SUM( IF( ( os.automatic = 0 AND os.mode = 'day' ), TIME_TO_SEC( os.duration ), 0 ) ) ) AS shifts_day_hours,
							
							IF( ( os.automatic = 0 AND os.mode = 'night' ), COUNT( od.id_shift ), 0 ) AS shifts_night_count,
							SEC_TO_TIME( SUM( IF( ( os.automatic = 0 AND os.mode = 'night' ), TIME_TO_SEC( os.duration ), 0 ) ) ) AS shifts_night_hours,
							
							IF( ( os.automatic = 0 AND os.mode = 'sick' ), COUNT( od.id_shift ), 0 ) AS shifts_sick_count,
							SEC_TO_TIME( SUM( IF( ( os.automatic = 0 AND os.mode = 'sick' ), TIME_TO_SEC( os.duration ), 0 ) ) ) AS shifts_sick_hours,
							
							IF( ( os.mode = 'leave' ), COUNT( od.id_shift ), 0 ) AS shifts_leave_count,
							SEC_TO_TIME( SUM( IF( ( os.mode = 'leave' ), TIME_TO_SEC( os.duration ), 0 ) ) ) AS shifts_leave_hours,
							
							IF( os.automatic = 0, GROUP_CONCAT( od.startRealShift SEPARATOR '|' ), '' ) AS starts,
							IF( os.automatic = 0, GROUP_CONCAT( od.endRealShift SEPARATOR '|' ), '' ) AS ends
						FROM object_duty od
							LEFT JOIN object_shifts os ON od.id_shift = os.id
						WHERE {$sSearchCriterions}
						GROUP BY od.id_shift
						ORDER BY name
				";
				
				$aShifts = $this->select( $sQuery );
				
				foreach( $aShifts as $aShift )
				{
					//Get Count
					if( !isset( $aFinalData[$key]['shifts_count'] ) )$aFinalData[$key]['shifts_count'] = '';
					$aFinalData[$key]['shifts_count'] .= "{$aShift['code']} - {$aShift['count']}; ";
					//End Get Count
					
					//Get Day Count
					if( !isset( $aFinalData[$key]['shifts_day_count'] ) )$aFinalData[$key]['shifts_day_count'] = 0;
					$aFinalData[$key]['shifts_day_count'] += $aShift['shifts_day_count'];
					//End Get Day Count
					//Get Day Hours
					if( !isset( $aFinalData[$key]['shifts_day_hours'] ) )$aFinalData[$key]['shifts_day_hours'] = '0';
					$aFinalData[$key]['shifts_day_hours'] += $this->convertTimeToHours( $aShift['shifts_day_hours'] );
					//End Get Day Hours
					
					//Get Night Count
					if( !isset( $aFinalData[$key]['shifts_night_count'] ) )$aFinalData[$key]['shifts_night_count'] = 0;
					$aFinalData[$key]['shifts_night_count'] += $aShift['shifts_night_count'];
					//End Get Night Count
					//Get Night Hours
					if( !isset( $aFinalData[$key]['shifts_night_hours'] ) )$aFinalData[$key]['shifts_night_hours'] = '0';
					$aFinalData[$key]['shifts_night_hours'] += $this->convertTimeToHours( $aShift['shifts_night_hours'] );
					//End Get Night Hours
					
					//Get Sick Count
					if( !isset( $aFinalData[$key]['shifts_sick_count'] ) )$aFinalData[$key]['shifts_sick_count'] = 0;
					$aFinalData[$key]['shifts_sick_count'] += $aShift['shifts_sick_count'];
					//End Get Sick Count
					//Get Sick Hours
					if( !isset( $aFinalData[$key]['shifts_sick_hours'] ) )$aFinalData[$key]['shifts_sick_hours'] = '0';
					$aFinalData[$key]['shifts_sick_hours'] += $this->convertTimeToHours( $aShift['shifts_sick_hours'] );
					//End Get Sick Hours
					
					//Get Leave Count
					if( !isset( $aFinalData[$key]['shifts_leave_count'] ) )$aFinalData[$key]['shifts_leave_count'] = 0;
					$aFinalData[$key]['shifts_leave_count'] += $aShift['shifts_leave_count'];
					//End Get Leave Count
					//Get Leave Hours
					if( !isset( $aFinalData[$key]['shifts_leave_hours'] ) )$aFinalData[$key]['shifts_leave_hours'] = '0';
					$aFinalData[$key]['shifts_leave_hours'] += $this->convertTimeToHours( $aShift['shifts_leave_hours'] );
					//End Get Leave Hours
					
					//Get Total Count
					if( !isset( $aFinalData[$key]['shifts_total'] ) )$aFinalData[$key]['shifts_total'] = 0;
					$aFinalData[$key]['shifts_total'] += $aShift['count_nonauto'];
					//End Get Total Count
					//Get Total Hours
					if( !isset( $aFinalData[$key]['shifts_hours'] ) )$aFinalData[$key]['shifts_hours'] = '0';
					$aFinalData[$key]['shifts_hours'] += $this->convertTimeToHours( $aShift['shifts_hours'] );
					//End Get Total Hours
					
					/* -- Стария начин за пресмятане на празнични часове. Новия е описан в МАНТИС ID : 1245 --
					
					//Get Holiday Hours
					$aShiftStarts = $aShiftEnds = array();
					$aShiftStarts = explode( "|", $aShift['starts'] );
					$aShiftEnds = explode( "|", $aShift['ends'] );
					
					$sHolidayLength = "00:00";
					$nHolidayHours = 0;
					if( !empty( $aShiftStarts ) && !empty( $aShiftEnds ) )
					{
						for( $nIter = 0; $nIter < $aShift['count']; $nIter++ )
						{
							//Starts
							$nSYear = ( int ) substr( $aShiftStarts[$nIter], 0, 4 );
							$nSMonth = ( int ) substr( $aShiftStarts[$nIter], 5, 2 );
							$nSDay = ( int ) substr( $aShiftStarts[$nIter], 8, 2 );
							//End Starts
							//Ends
							$nEYear = ( int ) substr( $aShiftEnds[$nIter], 0, 4 );
							$nEMonth = ( int ) substr( $aShiftEnds[$nIter], 5, 2 );
							$nEDay = ( int ) substr( $aShiftEnds[$nIter], 8, 2 );
							//End Ends
							//Current
							$nCYear = ( int ) substr( $aShiftStarts[$nIter], 0, 4 );
							$nCMonth = ( int ) substr( $aShiftStarts[$nIter], 5, 2 );
							$nCDay = ( int ) substr( $aShiftStarts[$nIter], 8, 2 );
							//End Current
							
							if( empty( $nSYear ) || empty( $nSMonth ) || empty( $nSDay ) )continue;
							if( empty( $nEYear ) || empty( $nEMonth ) || empty( $nEDay ) )continue;
							
							$nMonthDays = ( int ) date( "t", mktime( 0, 0, 0, $nCMonth, $nCDay, $nCYear ) );
							$bFinished = false;
							
							while( !$bFinished )
							{
								//Ending Condition
								if( $nCDay == $nEDay && $nCMonth == $nEMonth && $nCYear == $nEYear )
								{
									$bFinished = true;
								}
								//End Ending Condition
								
								//Body
								if( $oHolidays->isHoliday( $nCDay, $nCMonth ) )
								{
									if( $nSDay == $nEDay && $nSMonth == $nEMonth && $nSYear == $nEYear )
									{
										$nSHour = ( int ) substr( $aShiftStarts[$nIter], 11, 2 );
										$nEHour = ( int ) substr( $aShiftEnds[$nIter], 11, 2 );
										$nHolidayHours += $nEHour - $nSHour;
									}
									else if( $nCDay == $nSDay && $nCMonth == $nSMonth && $nCYear == $nSYear )
									{
										$nHour = ( int ) substr( $aShiftStarts[$nIter], 11, 2 );
										$nHolidayHours += ( 24 - $nHour );
									}
									else if( $nCDay == $nEDay && $nCMonth == $nEMonth && $nCYear == $nEYear )
									{
										$nHour = ( int ) substr( $aShiftEnds[$nIter], 11, 2 );
										$nHolidayHours += $nHour;
									}
									else
									{
										$nHolidayHours += 24;
									}
								}
								//End Body
								
								//Iteration
								$nCDay++;
								if( $nCDay > $nMonthDays )
								{
									$nCDay = 1;
									$nCMonth++;
									if( $nCMonth > 12 )
									{
										$nCMonth = 1;
										$nCYear++;
									}
									
									$nMonthDays = date( "t", mktime( 0, 0, 0, $nCMonth, $nCDay, $nCYear ) );
								}
								//End Iteration
							}
						}
					}
					
					if( !isset( $aFinalData[$key]['shifts_rests'] ) )$aFinalData[$key]['shifts_rests'] = 0;
					$aFinalData[$key]['shifts_rests'] += $nHolidayHours;
					//End Get Holiday Hours
					
					-- */
				}
				
				//Get Holiday Hours
				$nHolidayHours = $this->getPersonHolidayHours( $value['id_person'], $nFilterSYear, $nFilterSMonth, $nFilterEYear, $nFilterEMonth );
				$aFinalData[$key]['shifts_rests'] = $nHolidayHours;
				//End Get Holiday Hours
				
				if( empty( $aFinalData[$key]['shifts_count'] ) )$aFinalData[$key]['shifts_count'] = "Няма смени!";
				else
				{
					$aFinalData[$key]['shifts_count'] = substr( $aFinalData[$key]['shifts_count'], 0, strlen( $aFinalData[$key]['shifts_count'] ) - 2 );
				}
				if( empty( $aFinalData[$key]['shifts_day_count'] ) )$aFinalData[$key]['shifts_day_count'] = "-";
				if( empty( $aFinalData[$key]['shifts_day_hours'] ) )$aFinalData[$key]['shifts_day_hours'] = "-";
				if( empty( $aFinalData[$key]['shifts_night_count'] ) )$aFinalData[$key]['shifts_night_count'] = "-";
				if( empty( $aFinalData[$key]['shifts_night_hours'] ) )$aFinalData[$key]['shifts_night_hours'] = "-";
				if( empty( $aFinalData[$key]['shifts_sick_count'] ) )$aFinalData[$key]['shifts_sick_count'] = "-";
				if( empty( $aFinalData[$key]['shifts_sick_hours'] ) )$aFinalData[$key]['shifts_sick_hours'] = "-";
				if( empty( $aFinalData[$key]['shifts_leave_count'] ) )$aFinalData[$key]['shifts_leave_count'] = "-";
				if( empty( $aFinalData[$key]['shifts_leave_hours'] ) )$aFinalData[$key]['shifts_leave_hours'] = "-";
				if( empty( $aFinalData[$key]['shifts_total'] ) )$aFinalData[$key]['shifts_total'] = "-";
				if( empty( $aFinalData[$key]['shifts_hours'] ) )$aFinalData[$key]['shifts_hours'] = "-";
				if( empty( $aFinalData[$key]['shifts_rests'] ) )$aFinalData[$key]['shifts_rests'] = "-";
				
				//Get Extra Hours
				if( isset ( $aParams['api_action'] ) && ( $aParams['api_action'] == "export_to_pdf" || $aParams['api_action'] == "export_to_xls" ) )$bPaged = false;
				else $bPaged = true;
				
				if( $aParams['nRadio'] == 1 && isset( $aParams['sYearMonth'] ) && !empty( $aParams['sYearMonth'] ) )
				{
					$nIDPerson = $value['id_person'];
					
					if( $bPaged )
					{
						//Get Titler
						$nIDTitledObject = $oDBPersonnel->getPersonObject( $nIDPerson );
						
						$sAllQuery = "
							SELECT
								DISTINCT id_obj AS id_object
							FROM
								object_duty
							WHERE
								id_shift != 0
								AND id_person = {$nIDPerson}
								AND ( startShift LIKE '{$aParams['sYearMonth']}%' OR startRealShift LIKE '{$aParams['sYearMonth']}%' )
						";
						
						$aAllObjects = $this->select( $sAllQuery );
						
						$bChosen = false;
						$nIDObject = 0;
						foreach( $aAllObjects as $aObject )
						{
							if( $aObject['id_object'] == $nIDTitledObject )
							{
								$nIDObject = $nIDTitledObject;
								$bChosen = true;
							}
							
							if( !$bChosen )
							{
								$nIDObject = $aObject['id_object'];
							}
						}
						//End Get Titler
					}
					
					$aYearMonth = explode( "-", $aParams['sYearMonth'] );
					if( isset( $aYearMonth[0] ) && isset( $aYearMonth[1] ) )
					{
						$nYear = $aYearMonth[0];
						$nMonth = $aYearMonth[1];
					}
					else
					{
						$nYear = date( "Y" );
						$nMonth = date( "m" );
					}
					
					$aMonthNames = array(
						1 => "Януари",
						2 => "Февруари",
						3 => "Март",
						4 => "Април",
						5 => "Май",
						6 => "Юни",
						7 => "Юли",
						8 => "Август",
						9 => "Септември",
						10 => "Октомври",
						11 => "Ноември",
						12 => "Декември"
					);
					
					$sTooltip = "По Месеци :\n";
					$nTotalSum = 0;
					
					$sYearTooltip = "По Месеци :\n";
					$nYearTotalSum = 0;
					
					$nMonthsMin = ( $nMonth <= 6 ) ? 1 : 7;
					$nMonthsMax = $nMonthsMin + 5;
					
					for( $nMonthS = 1; $nMonthS <= 12; $nMonthS++ )
					{
						if( $nMonthS >= $nMonthsMin && $nMonthS <= $nMonthsMax )
						{
							$nHoursNorm 	= $oNorms->getHourNormsForDate( $nYear, $nMonthS );
							$nHoursCurrent 	= getRoundTime( $oDBPersonMonthLimits->getHourCurrentForDate( $nIDPerson, $nYear, $nMonthS ) );
							
							$nHoursEnd = $nHoursCurrent - $nHoursNorm;
							
							$nTotalSum += ( int ) $nHoursEnd;
							$sTooltip .= "\n{$aMonthNames[$nMonthS]} {$nYear} : {$nHoursEnd} часа.";
						}
						
						$nYearHoursNorm 	= $oNorms->getHourNormsForDate( $nYear, $nMonthS );
						$nYearHoursCurrent 	= getRoundTime( $oDBPersonMonthLimits->getHourCurrentForDate( $nIDPerson, $nYear, $nMonthS ) );
						
						$nYearHoursEnd = $nYearHoursCurrent - $nYearHoursNorm;
						
						$nYearTotalSum += ( int ) $nYearHoursEnd;
						$sYearTooltip .= "\n{$aMonthNames[$nMonthS]} {$nYear} : {$nYearHoursEnd} часа.";
					}
					
					//-- Attribs
					$aHourOverAttributes = array();
					$aYearHourOverAttributes = array();
					
					$aHourOverAttributes['title'] = $sTooltip;
					if( $nTotalSum >= 65 )						$aHourOverAttributes["style"] 	= "color: #FF0000; text-align: center; cursor: pointer;";
					if( $nTotalSum >= 50 && $nTotalSum < 65 )	$aHourOverAttributes["style"] 	= "color: #0000FF; text-align: center; cursor: pointer;";
					if( $nTotalSum < 50 )						$aHourOverAttributes["style"] 	= "color: #00BB00; text-align: center; cursor: pointer;";
					if( $bPaged )								$aHourOverAttributes['onclick'] = "dialogPersonSchedule( {$nIDObject}, {$nYear}{$nMonth} );";
					
					$aYearHourOverAttributes['title'] = $sYearTooltip;
					$aYearHourOverAttributes["style"] = "text-align: center;";
					//--End Attribs
					
					$aFinalData[$key]['hours_over'] = $nTotalSum;
					$aFinalData[$key]['hour_over_attributes'] = $aHourOverAttributes;
					
					$aFinalData[$key]['year_hours_over'] = $nYearTotalSum;
					$aFinalData[$key]['year_hour_over_attributes'] = $aYearHourOverAttributes;
				}
				else
				{
					$aFinalData[$key]['hours_over'] = 0;
					$aFinalData[$key]['year_hours_over'] = 0;
				}
				//End Get Extra Hours
				
				//Get Max Shifts
				$nMonth = ( int ) $value['record_start'];
				$aNorms = $oNorms->getActiveNormsByMonth( $nMonth );
				$nMaxShifts = isset( $aNorms['shifts'] ) ? $aNorms['shifts'] : 0;
				$aFinalData[$key]['shifts_stdrd'] = $nMaxShifts;
				//End Get Max Shifts
				
				//End Get Data
			}
			
			$nCount = count( $aFinalData );
			
			if( $right_edit )$oResponse->setFieldLink( "name", "editPersonnel" );
			
			//Fix the Sorting
			$oParams = Params::getInstance();
			
			$sSortField = $oParams->get( "sfield", "name" );
			$nSortType	= $oParams->get( "stype", DBAPI_SORT_ASC );
			
			if( empty( $sSortField ) )$sSortField = "name";
			
			foreach( $aFinalData as $key => $row )
			{
				$name[$key] = 			$row['name'];
				$shifts_count[$key] = 	$row['shifts_count'];
				
				$shifts_count[$key] = 	$row['shifts_day_count'];
				$shifts_hours[$key] = 	$row['shifts_day_hours'];
				$shifts_count[$key] = 	$row['shifts_night_count'];
				$shifts_hours[$key] = 	$row['shifts_night_hours'];
				$shifts_count[$key] = 	$row['shifts_sick_count'];
				$shifts_hours[$key] = 	$row['shifts_sick_hours'];
				$shifts_count[$key] = 	$row['shifts_leave_count'];
				$shifts_hours[$key] = 	$row['shifts_leave_hours'];
				$shifts_total[$key] = 	$row['shifts_total'];
				$shifts_hours[$key] = 	$row['shifts_hours'];
				
				$shifts_rests[$key] = 	$row['shifts_rests'];
				$hours_over[$key] = 	$row['hours_over'];
				$year_hours_over[$key] = 	$row['year_hours_over'];
				$shifts_stdrd[$key] = 	$row['shifts_stdrd'];
			}
			
			if( $nSortType == DBAPI_SORT_ASC )$nSortOrderArray = SORT_ASC;
			if( $nSortType == DBAPI_SORT_DESC )$nSortOrderArray = SORT_DESC;
			
			if( $sSortField == "shifts_day_count" 	||
				$sSortField == "shifts_day_hours" 	||
				$sSortField == "shifts_night_count" ||
				$sSortField == "shifts_night_hours" ||
				$sSortField == "shifts_sick_count" 	||
				$sSortField == "shifts_sick_hours" 	||
				$sSortField == "shifts_leave_count" ||
				$sSortField == "shifts_leave_hours" ||
				$sSortField == "shifts_total" 		||
				$sSortField == "shifts_hours" 		||
				$sSortField == "shifts_rests" 		||
				$sSortField == "hours_over" 		||
				$sSortField == "year_hours_over" 	||
				$sSortField == "shifts_stdrd" 	)$nSortTypeArray = SORT_NUMERIC;
			else $nSortTypeArray = SORT_STRING;
			
			array_multisort( $$sSortField, $nSortOrderArray, $nSortTypeArray, $aFinalData );
			
			$oResponse->setSort( $sSortField, $nSortType );
			//End Fix the Sorting
			
			//Fix the Paging
			$nPage = $oParams->get( "current_page", 1 );
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ( $nPage - 1 ) * $nRowCount;
			$nRowTotal = $nCount;
			
			$nIndex = 0;
			$aPagedData = array();
			foreach( $aFinalData as $FDKey => $FDValue )
			{
				if( $nIndex >= $nRowOffset && $nIndex < ( $nRowOffset + $nRowCount ) )
				{
					$aPagedData[$FDKey] = $FDValue;
				}
				
				$nIndex++;
			}
			
			$oResponse->setPaging( $nRowCount, $nRowTotal, ceil( $nRowOffset / $nRowCount ) + 1 );
			//End Fix the Paging
			
			if( isset ( $aParams['api_action'] ) && ( $aParams['api_action'] == "export_to_pdf" || $aParams['api_action'] == "export_to_xls" ) )
			{
				foreach( $aFinalData as $key => $value )
				{
					$oResponse->setDataAttributes( $key, "hours_over", $value['hour_over_attributes'] );
					$oResponse->setDataAttributes( $key, "year_hours_over", $value['year_hour_over_attributes'] );
				}
				$oResponse->setData( $aFinalData );
			}
			else
			{
				foreach( $aPagedData as $key => $value )
				{
					$oResponse->setDataAttributes( $key, "hours_over", $value['hour_over_attributes'] );
					$oResponse->setDataAttributes( $key, "year_hours_over", $value['year_hour_over_attributes'] );
				}
				$oResponse->setData( $aPagedData );
			}
		}
		
		public function getShiftsInInterval($dMinusSix,$dPlusSix,$nIDFirm,$nIDOffice) {
			global $db_name_personnel;
			
			$sIDOffices = implode(',',$_SESSION['userdata']['access_right_regions']);
			$bAllOffices = !empty($_SESSION['userdata']['access_right_all_regions']) ? true : false;
			
			$sQuery = "
				SELECT 
					od.id_obj,
					UNIX_TIMESTAMP(od.startShift) AS startShift,
					UNIX_TIMESTAMP(od.endShift) AS endShift,
					UNIX_TIMESTAMP(od.startRealShift) AS startRealShift,
					UNIX_TIMESTAMP(od.endRealShift) AS endRealShift,
					CONCAT(p.fname,' ',p.mname,' ',p.lname,' GSM: ',p.mobile) AS person,
					osh.name AS shift 
				FROM object_duty od
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = od.id_person
				LEFT JOIN object_shifts osh ON osh.id = od.id_shift
				LEFT JOIN objects o ON o.id = od.id_obj
				LEFT JOIN offices off ON off.id = o.id_office
				WHERE 
					od.id_shift != 0 AND (
						(UNIX_TIMESTAMP( od.startShift ) >= {$dMinusSix} AND UNIX_TIMESTAMP( od.startShift ) < {$dPlusSix} ) OR
						(UNIX_TIMESTAMP( od.endShift ) >= {$dMinusSix} AND UNIX_TIMESTAMP( od.endShift ) < {$dPlusSix} ) OR
						(UNIX_TIMESTAMP( od.startRealShift ) >= {$dMinusSix} AND UNIX_TIMESTAMP( od.startRealShift ) < {$dPlusSix} ) OR
						(UNIX_TIMESTAMP( od.endRealShift ) >= {$dMinusSix} AND UNIX_TIMESTAMP( od.endRealShift ) < {$dPlusSix} ) 
					)
			";
			
			if(!empty($nIDOffice)) {
				$sQuery .= " AND o.id_office = {$nIDOffice}\n";
			} elseif (!empty($nIDFirm)) {
				$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
				if( $bAllOffices == false ) {
					$sQuery .= " AND o.id_office IN ({$sIDOffices})\n";
				}
			} elseif( $bAllOffices == false ) {
				$sQuery .= " AND o.id_office IN ({$sIDOffices})\n";
			}
			
			global $db_sod_backup;
			
			return $this->selectFromDB($db_sod_backup, $sQuery);
		}
		
		public function getObjectOLD( $nID ) {
			$nID = (int) $nID;
			
			$sQuery = "
				SELECT
					ob.id_oldobj
				FROM objects ob
				WHERE 1
					AND ob.id = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getObjectNew( $nID ) {
			$nID = (int) $nID;
			
			$sQuery = "
				SELECT
					ob.id
				FROM objects ob
				WHERE 1
					AND ob.id_oldobj = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getShiftByDate($nIDPerson,$sDate) {
			
			$sQuery = "
				SELECT 
					od.id,
					os.code,
					os.name,
					os.shiftFrom,
					os.shiftTo,
					os.mode
				FROM object_duty od
				LEFT JOIN object_shifts os ON os.id = od.id_shift
				WHERE 1
					AND od.id_person = {$nIDPerson}
					AND od.id_shift != 0
					AND SUBSTRING(od.startShift,1,10) = '{$sDate}'
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getTechnicsOnDuty($nIDPerson,$sDate) {
			
			$sQuery = "
				SELECT 
					od.id
				FROM object_duty od
				LEFT JOIN objects ob ON ob.id = od.id_obj
				WHERE 1
					AND od.id_person = {$nIDPerson}
					AND ob.is_tech = 1
					AND id_shift != 0
					AND SUBSTRING(od.startShift,1,10) = '{$sDate}'
			";
			
			return $this->select($sQuery);
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
		
		function convertTimeToHours( $sTime )
		{
			if( strlen( $sTime ) < 8 )
			{
				return 0;
			}
			
			$aTime = explode( ":", $sTime );
			
			$nHours = 0;
			
			if( !isset( $aTime[0] ) || !isset( $aTime[1] ) || !isset( $aTime[2] ) )
			{
				return 0;
			}
			else
			{
				$nHours += ( int ) $aTime[0];
				if( ( int ) $aTime[1] >= 30 )$nHours += 1;
			}
			
			return $nHours;
		}
		
		/**
		 * Връща часовете смени за посочения месец и служител.
		 *
		 * @param int $nIDPerson
		 * @param int $nYear
		 * @param int $nMonth
		 * @param bool $bRound ( Закръгляне на минути )
		 * 
		 * @return int
		 */
		function getHourCurrentForDate( $nIDPerson, $nMonth, $nYear, $bRound = true )
		{
			$oDBObjectScheduleSettings = new DBObjectScheduleSettings();
			
			//Validation
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return 0;
			}
			
			if( $nMonth < 1 || $nMonth > 12 || strlen( $nYear ) != 4 )
			{
				return 0;
			}
			//End Validation
			
			//Initialization
			$sDate = $nYear . "-" . ( ( strlen( $nMonth ) < 2 ) ? ( "0" . $nMonth ) : $nMonth );
			//End Initialization
			
			$sQuery = "
				SELECT
					od.id,
					DATE_FORMAT( os.shiftFrom, 	'%H:%i' ) AS shiftFromShort,
					DATE_FORMAT( os.shiftTo,	'%H:%i' ) AS shiftToShort,
					CASE
						WHEN os.shiftFrom >= os.shiftTo THEN
							( ( 86400 - TIME_TO_SEC( os.shiftFrom ) ) + TIME_TO_SEC( os.shiftTo ) ) - IF( os.mode != 'leave', TIME_TO_SEC( os.duration ), 0 )
						WHEN os.shiftFrom < os.shiftTo THEN
							( TIME_TO_SEC( os.shiftTo ) - TIME_TO_SEC( os.shiftFrom ) ) - IF( os.mode != 'leave', TIME_TO_SEC( os.duration ), 0 )
					END AS rest
				FROM
					object_duty od
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				WHERE
					od.id_person = {$nIDPerson}
					AND od.id_shift != 0
					AND IF
					(
						od.startRealShift != '0000-00-00 00:00:00' AND od.endRealShift != '0000-00-00 00:00:00',
						od.startRealShift LIKE '{$sDate}%',
						od.startShift LIKE '{$sDate}%'
					)
			";
			
			$aData = $this->select( $sQuery );
			
			$sTime = $bRound ? 0 : "00:00";
			
			foreach( $aData as $nKey => $aValue )
			{
				$sHour = $oDBObjectScheduleSettings->calculateShiftHours( $aValue['shiftFromShort'], $aValue['shiftToShort'], $aValue['rest'], $bRound );
				
				if( $bRound )$sTime += $sHour;
				else $sTime = getTimeSum( $sTime, $sHour );
			}
			
			return $sTime;
		}
		
		/**
		 * Връща имената на обектите, ако служителя има смени към тях в подадения период от време ( различни от "отпуск" ).
		 *
		 * @param int $nIDPerson
		 * @param string $sStartTime 	( Y-m-d H:i:s )
		 * @param string $sEndTime		( Y-m-d H:i:s )
		 * @return array
		 */
		function getObjectsForPersonShifts( $nIDPerson, $sStartTime, $sEndTime )
		{
			global $db_name_personnel;
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return "";
			}
			
			$sQuery = "
				SELECT
					DISTINCT obj.name AS name
				FROM
					object_duty od
				LEFT JOIN
					objects obj ON obj.id = od.id_obj
				LEFT JOIN
					statuses s ON s.id = obj.id_status
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				LEFT JOIN
					{$db_name_personnel}.personnel per ON per.id = od.id_person
				LEFT JOIN
					{$db_name_personnel}.positions_nc pos ON pos.id = per.id_position_nc
				WHERE
					od.id_shift != 0
					AND obj.is_fo = 1
					AND s.play = 1
					AND od.id_person = {$nIDPerson}
					AND os.mode != 'leave'
					AND IF
					(
						pos.cipher = '41912012',
						NOT
						(
							( UNIX_TIMESTAMP( od.startShift ) < UNIX_TIMESTAMP( '{$sStartTime}' ) )
							OR
							( UNIX_TIMESTAMP( od.startShift ) > UNIX_TIMESTAMP( '{$sEndTime}' ) )
						),
						NOT
						(
							( UNIX_TIMESTAMP( od.startShift ) < UNIX_TIMESTAMP( '{$sStartTime}' ) AND UNIX_TIMESTAMP( od.endShift ) < UNIX_TIMESTAMP( '{$sStartTime}' ) )
							OR
							( UNIX_TIMESTAMP( od.startShift ) > UNIX_TIMESTAMP( '{$sEndTime}' ) AND UNIX_TIMESTAMP( od.endShift ) > UNIX_TIMESTAMP( '{$sEndTime}' ) )
						)
					)
			";
			
			return $this->select( $sQuery );
		}
		
		public function clearPersonLeaveForDays( $nIDPerson, $sStartDate, $sEndDate, $aMonthStats = array() )
		{
			global $db_sod;
			
			$oDBPersonMonthLimits = new DBPersonMonthLimits();
			
			$sQuery = "
				DELETE
					od.*
				FROM
					object_duty od
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				WHERE
					od.id_person = {$nIDPerson}
					AND os.mode = 'leave'
					AND UNIX_TIMESTAMP( od.startShift ) >= UNIX_TIMESTAMP( '{$sStartDate}' )
					AND UNIX_TIMESTAMP( od.startShift ) <= UNIX_TIMESTAMP( '{$sEndDate}' )
			";
			
			$oRes = $db_sod->Execute( $sQuery );
			
			if( !$oRes )
			{
				return DBAPI_ERR_SQL_QUERY;
			}
			
			foreach( $aMonthStats as $nYearMonth => $nDays )
			{
				$oDBPersonMonthLimits->IncreaseHours( $nIDPerson, $nYearMonth, -( $nDays * 8 ) );
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		/**
		 * Нанасяне на смени отпуск за служител от дата, за краен брой работни дни.
		 *
		 * @param int $nIDPerson
		 * @param string $sStartDate ( Y-m-d )
		 * @param int $nDays
		 */
		public function putPersonLeaveForDays( $nIDPerson, $sStartDate, $nDays )
		{
			global $db_sod;
			
			$oDBHolidays 			= new DBHolidays();
			$oDBObjectShifts		= new DBObjectShifts();
			$oDBPersonMonthLimits 	= new DBPersonMonthLimits();
			$oDBPersonnel			= new DBPersonnel();
			
			//Initial Data
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return DBAPI_ERR_INVALID_PARAM;
			
			$aStartDate = explode( "-", $sStartDate );
			if( !isset( $aStartDate[0] ) || !isset( $aStartDate[1] ) || !isset( $aStartDate[2] ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			else
			{
				$nYear 	= ( int ) $aStartDate[0];
				$nMonth = ( int ) $aStartDate[1];
				$nDay 	= ( int ) $aStartDate[2];
			}
			
			$nIDPersonObject = $oDBPersonnel->getPersonObject( $nIDPerson );
			if( empty( $nIDPersonObject ) )return DBAPI_ERR_SUCCESS;
			
			$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
			$sSQLDate = $nYear . "-" . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth ) . "-" . ( strlen( $nDay ) < 2 ? ( "0" . $nDay ) : $nDay );
			
			$aObjectLeaveShift = $oDBObjectShifts->getShiftLeaveForObject( $nIDPersonObject );
			//End Initial Data
			
			$this->StartTrans();
			$nIteration = 0;
			$nAddWorkDay = false;
			
			while( $nIteration < $nDays )
			{
				$nMyWeekday = ( int ) date( "w", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
				
				if( $nMyWeekday == 0 || $nMyWeekday == 6 )
				{
					if( $oDBHolidays->isWorkday( $nDay, $nMonth, $nYear ) )$nAddWorkDay = true;
				}
				else
				{
					if( !$oDBHolidays->isHoliday( $nDay, $nMonth ) && !$oDBHolidays->isRestday( $nDay, $nMonth, $nYear ) )$nAddWorkDay = true;
				}
				
				if( $nAddWorkDay )
				{
					$nHoursToAdd = 0;
					if( !empty( $nIDPersonObject ) && !empty( $aObjectLeaveShift['id'] ) )
					{
						if( $aObjectLeaveShift['shift_to_sec'] <= $aObjectLeaveShift['shift_from_sec'] )$nAddDay = 86400;
						else $nAddDay = 0;
						
						$aData = array();
						$aData['id_obj'] = $nIDPersonObject;
						$aData['id_shift'] = $aObjectLeaveShift['id'];
						$aData['id_person'] = $nIDPerson;
						$aData['startShift'] = date( "Y-m-d", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) ) . " " . $aObjectLeaveShift['shift_from'];
						$aData['endShift'] = date( "Y-m-d", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) + $nAddDay ) . " " . $aObjectLeaveShift['shift_to'];
						$aData['startRealShift'] = $aData['startShift'];
						$aData['endRealShift'] = $aData['endShift'];
						
						//Existance Check
						$sExisting = "
							SELECT
								id
							FROM
								object_duty
							WHERE
								id_obj = {$aData['id_obj']}
								AND id_person = {$aData['id_person']}
								AND id_shift = {$aData['id_shift']}
								AND startShift = '{$aData['startShift']}'
								AND endShift = '{$aData['endShift']}'
						";
						$aExisting = $this->select( $sExisting );
						//End Existance Check
						
						if( empty( $aExisting ) )
						{
							//Delete Null Shift For Date
							$sDeleteQuery = "
								DELETE
								FROM
									object_duty
								WHERE
									id_obj = {$nIDPersonObject}
									AND id_shift = 0
									AND id_person = {$nIDPerson}
									AND startShift LIKE '{$sSQLDate}%'
							";
							
							$oRes = $db_sod->Execute( $sDeleteQuery );
							if( !$oRes )return DBAPI_ERR_SQL_QUERY;
							//End Delete Null Shift For Date
							
							$nResult = $this->update( $aData );
							if( $nResult != DBAPI_ERR_SUCCESS )
							{
								$this->FailTrans();
								return $nResult;
							}
							
							$nHoursToAdd += 8;
						}
					}
					
					$nResult = $oDBPersonMonthLimits->IncreaseHours( $nIDPerson, $nYear . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth ), $nHoursToAdd );
					if( $nResult != DBAPI_ERR_SUCCESS )
					{
						$this->FailTrans();
						return $nResult;
					}
					
					$nIteration++;
					$nAddWorkDay = false;
				}
				
				//Progress Date
				if( $nIteration < $nDays )
				{
					$nDay++;
					if( $nDay > $nDaysInMonth )
					{
						$nDay = 1;
						$nMonth++;
						if( $nMonth > 12 ) { $nMonth = 1; $nYear++; }
						
						$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
					}
					
					$sSQLDate = $nYear . "-" . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth ) . "-" . ( strlen( $nDay ) < 2 ? ( "0" . $nDay ) : $nDay );
				}
				//End Progress Date
			}
			
			$this->CompleteTrans();
			
			return DBAPI_ERR_SUCCESS;
		}
		
		/**
		 * Връща нощните часове за служител, според настройките за начало и край на нощния труд.
		 *
		 * @param int $nIDPerson
		 * @param int $nYear
		 * @param int $nMonth
		 * @param float $nFixedSalary
		 * @param int $nWorkdays
		 * 
		 * @return float
		 */
		public function getPersonNightHoursSalary( $nIDPerson, $nYear, $nMonth, $nFixedSalary, $nWorkdays = 0 )
		{
			$oDBObjectScheduleSettings = new DBObjectScheduleSettings();
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return 0;
			}
			
			if( empty( $nWorkdays ) )$nWorkdays = $oDBHolidays->getWorkdaysForMonth( $nYear, $nMonth );
			if( !empty( $nWorkdays ) )$nMoneyPerDay = round( ( $nFixedSalary / $nWorkdays ), 2 );
			else $nMoneyPerDay = 0;
			$nMoneyPerHour = $nMoneyPerDay / 8;
			
			$nMaxDays = date( "t", mktime( 0, 0, 0, $nMonth, 1, $nYear ) );
			
			$sMinDate = $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-01 00:00:00";
			$sMaxDate = $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-" . $nMaxDays . " 24:00:00";
			
			$sQuery = "
				SELECT
					IF
					(
						od.startShift > '{$sMinDate}',
						od.startShift,
						'{$sMinDate}'
					) AS startShift,
					IF
					(
						od.endShift < '{$sMaxDate}',
						od.endShift,
						'{$sMaxDate}'
					) AS endShift
				FROM
					object_duty od
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				WHERE
					od.id_person = {$nIDPerson}
					AND od.id_shift != 0
					AND os.mode != 'leave'
					AND os.mode != 'sick'
					AND
					(
						( od.startShift >= '{$sMinDate}' AND od.startShift <= '{$sMaxDate}' )
						OR
						( od.endShift >= '{$sMinDate}' AND od.endShift <= '{$sMaxDate}' )
					)
			";
			
			$aData = $this->select( $sQuery );
			
			$aSettings = $oDBObjectScheduleSettings->getActiveSettings();
			
			$sNightLength = "00:00";
			foreach( $aData as $nKey => $aShift )
			{
				$sStartTime = substr( $aShift['startShift'], 11, 5 );
				$sEndTime 	= substr( $aShift['endShift'], 11, 5 );
				
				if( $sStartTime == "00:00" )$sStartTime = "24:00";
				if( $sEndTime == "00:00" )$sEndTime = "24:00";
				if( $aSettings['night_from'] == "00:00" )$aSettings['night_from'] = "24:00";
				if( $aSettings['night_to'] == "00:00" )$aSettings['night_to'] = "24:00";
				
				if( $sStartTime >= $sEndTime )$sEndTime = getTimeSum( $sEndTime, "24:00" );
				if( $aSettings['night_from'] >= $aSettings['night_to'] )$aSettings['night_to'] = getTimeSum( $aSettings['night_to'], "24:00" );
				
				$sMinEnd = $sEndTime < $aSettings['night_to'] ? $sEndTime : $aSettings['night_to'];
				$sMaxStart = $sStartTime > $aSettings['night_from'] ? $sStartTime : $aSettings['night_from'];
				
				$sTimeResult = getTimeSum( $sMinEnd, $sMaxStart, true );
				if( substr( $sTimeResult, 0, 1 ) == "-" )$sTimeResult = "00:00";
				
				$sNightLength = getTimeSum( $sNightLength, $sTimeResult );
			}
			
			//$nFinalPrice = round( ( getRoundTime( $sNightLength ) * ( $nMoneyPerHour * $aSettings['factor'] ) ), 2 );
			$nFinalPrice = round( getRoundTime( $sNightLength ) * 0.25, 2 );
			
			return $nFinalPrice;
		}
		
		public function getPersonHolidayHoursSalary( $nIDPerson, $nYear, $nMonth, $nFixedSalary, $nWorkdays = 0 )
		{
			$oDBHolidays = new DBHolidays();
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return 0;
			}
			
			if( empty( $nWorkdays ) )$nWorkdays = $oDBHolidays->getWorkdaysForMonth( $nYear, $nMonth );
			if( !empty( $nWorkdays ) )$nMoneyPerDay = round( ( $nFixedSalary / $nWorkdays ), 2 );
			else $nMoneyPerDay = 0;
			$nMoneyPerHour = $nMoneyPerDay / 8;
			
			$nMaxDays = date( "t", mktime( 0, 0, 0, $nMonth, 1, $nYear ) );
			
			$sMinDate = $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-01 00:00:00";
			$sMaxDate = $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-" . $nMaxDays . " 23:59:59";
			
			$sQuery = "
				SELECT
					od.startShift AS startShift,
					od.endShift AS endShift
				FROM
					object_duty od
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				WHERE
					od.id_person = {$nIDPerson}
					AND od.id_shift != 0
					AND os.mode != 'leave'
					AND os.mode != 'sick'
					AND od.startRealShift != '0000-00-00 00:00:00' AND od.endRealShift != '0000-00-00 00:00:00'
					AND
					(
						( od.startShift >= '{$sMinDate}' AND od.startShift <= '{$sMaxDate}' )
						OR
						( od.endShift >= '{$sMinDate}' AND od.endShift <= '{$sMaxDate}' )
					)
			";
			
			$aData = $this->select( $sQuery );
			
			$sHolidayLength = "00:00";
			
			foreach( $aData as $nKey => $aShift )
			{
				$sStartDate = substr( $aShift['startShift'], 0, 10 );
				$sStartTime = substr( $aShift['startShift'], 11, 5 );
				$sEndDate 	= substr( $aShift['endShift'], 0, 10 );
				$sEndTime 	= substr( $aShift['endShift'], 11, 5 );
				
				if( $sEndDate == $sStartDate )
				{
					$aMonthDay = explode( "-", $sStartDate );
					if( isset( $aMonthDay[1] ) && isset( $aMonthDay[2] ) && is_numeric( $aMonthDay[1] ) && is_numeric( $aMonthDay[2] ) )
					{
						if( $oDBHolidays->isHoliday( $aMonthDay[2], $aMonthDay[1] ) )
						{
							$sHolidayLength = getTimeSum( $sHolidayLength, getTimeSum( $sEndTime, $sStartTime, true ) );
						}
					}
				}
				else
				{
					$aStartDate = explode( "-", $sStartDate );
					$aEndDate = explode( "-", $sEndDate );
					
					if( isset( $aStartDate[0] ) &&
						isset( $aStartDate[1] )	&&
						isset( $aStartDate[2] )	&&
						isset( $aEndDate[0] )	&&
						isset( $aEndDate[1] )	&&
						isset( $aEndDate[2] ) 	)
					{
						if( $aStartDate[0] == $nYear && LPAD( $aStartDate[1], 2, 0 ) == LPAD( $nMonth, 2, 0 ) )
						{
							if( $oDBHolidays->isHoliday( $aStartDate[2], $aStartDate[1] ) )
							{
								$sHolidayLength = getTimeSum( $sHolidayLength, getTimeSum( "24:00", $sStartTime, true ) );
							}
						}
						
						if( $aEndDate[0] == $nYear && LPAD( $aEndDate[1], 2, 0 ) == LPAD( $nMonth, 2, 0 ) )
						{
							if( $oDBHolidays->isHoliday( $aEndDate[2], $aEndDate[1] ) )
							{
								$sHolidayLength = getTimeSum( $sHolidayLength, $sEndTime );
							}
						}
					}
				}
			}
			
			$nFinalPrice = round( ( getRoundTime( $sHolidayLength ) * $nMoneyPerHour ), 2 );
			
			return $nFinalPrice;
		}
		
		public function getPersonHolidayHours( $nIDPerson, $nYearFrom, $nMonthFrom, $nYearTo, $nMonthTo )
		{
			global $db_name_personnel;
			
			$oDBHolidays = new DBHolidays();
			
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return 0;
			}
			
			if( !empty( $nYearFrom ) && !empty( $nMonthFrom ) )
			{
				$sMinDate = $nYearFrom . "-" . LPAD( $nMonthFrom, 2, 0 ) . "-01 00:00:00";
			}
			else $sMinDate = 0;
			
			if( !empty( $nYearTo ) && !empty( $nMonthTo ) )
			{
				$nMaxDays = date( "t", mktime( 0, 0, 0, $nMonthTo, 1, $nYearTo ) );
				$sMaxDate = $nYearTo . "-" . LPAD( $nMonthTo, 2, 0 ) . "-" . $nMaxDays . " 23:59:59";
			}
			else $sMaxDate = 0;
			
			$sQuery = "
				SELECT
					od.startRealShift AS startShift,
					od.endRealShift AS endShift
				FROM
					object_duty od
				LEFT JOIN
					object_shifts os ON os.id = od.id_shift
				LEFT JOIN
					{$db_name_personnel}.personnel per ON per.id = od.id_person
				LEFT JOIN
					offices o ON per.id_office = o.id
				LEFT JOIN
					firms f ON o.id_firm = f.id
				WHERE
					od.id_person = {$nIDPerson}
					AND od.id_shift != 0
					AND os.mode != 'leave'
					AND os.mode != 'sick'
					AND od.startRealShift != '0000-00-00 00:00:00' AND od.endRealShift != '0000-00-00 00:00:00'
			";
			
			if( !empty( $sMinDate ) )
			{
				$sQuery .= "
					AND
					(
						od.startRealShift >= '{$sMinDate}' OR od.endRealShift >= '{$sMinDate}'
					)
				";
			}
			if( !empty( $sMaxDate ) )
			{
				$sQuery .= "
					AND
					(
						od.startRealShift <= '{$sMaxDate}' OR od.endRealShift <= '{$sMaxDate}'
					)
				";
			}
			
			$aData = $this->select( $sQuery );
			
			$sHolidayLength = "00:00";
			
			foreach( $aData as $nKey => $aShift )
			{
				$sStartDate = substr( $aShift['startShift'], 0, 10 );
				$sStartTime = substr( $aShift['startShift'], 11, 5 );
				$sEndDate 	= substr( $aShift['endShift'], 0, 10 );
				$sEndTime 	= substr( $aShift['endShift'], 11, 5 );
				
				if( $sEndDate == $sStartDate )
				{
					$aMonthDay = explode( "-", $sStartDate );
					if( isset( $aMonthDay[1] ) && isset( $aMonthDay[2] ) && is_numeric( $aMonthDay[1] ) && is_numeric( $aMonthDay[2] ) )
					{
						if( $oDBHolidays->isHoliday( $aMonthDay[2], $aMonthDay[1] ) )
						{
							$sHolidayLength = getTimeSum( $sHolidayLength, getTimeSum( $sEndTime, $sStartTime, true ) );
						}
					}
				}
				else
				{
					$aStartDate = explode( "-", $sStartDate );
					$aEndDate = explode( "-", $sEndDate );
					
					if( isset( $aStartDate[0] ) &&
						isset( $aStartDate[1] )	&&
						isset( $aStartDate[2] )	&&
						isset( $aEndDate[0] )	&&
						isset( $aEndDate[1] )	&&
						isset( $aEndDate[2] ) 	)
					{
						if( $oDBHolidays->isHoliday( $aStartDate[2], $aStartDate[1] ) )
						{
							$sHolidayLength = getTimeSum( $sHolidayLength, getTimeSum( "24:00", $sStartTime, true ) );
						}
						
						if( !empty( $nYearTo ) && !empty( $nMonthTo ) )
						{
							if( $aEndDate[0] <= $nYearFrom && LPAD( $aEndDate[1], 2, 0 ) <= LPAD( $nMonthTo, 2, 0 ) )
							{
								if( $oDBHolidays->isHoliday( $aEndDate[2], $aEndDate[1] ) )
								{
									$sHolidayLength = getTimeSum( $sHolidayLength, $sEndTime );
								}
							}
						}
						else
						{
							if( $oDBHolidays->isHoliday( $aEndDate[2], $aEndDate[1] ) )
							{
								$sHolidayLength = getTimeSum( $sHolidayLength, $sEndTime );
							}
						}
					}
				}
			}
			
			return getRoundTime( $sHolidayLength );
		}
	}

?>