<?php
	class DBObjectShifts
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, 'object_shifts');
		}
		
		public function getReport( $nID, DBResponse $oResponse ) {
			global $db_name_personnel;
			$nID = (int) $nID;
			
			$right_edit = false;
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('object_shifts_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$oData = array();
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					os.id, 
					os.id as info, 
					os.code,
					os.name,
					os.automatic,
					os.stake,
					TIME_FORMAT(os.shiftFrom, '%H:%i') AS start,
					TIME_FORMAT(os.shiftTo, '%H:%i') AS end,
					TIME_FORMAT(os.duration, '%H:%i') AS duration,
					os.description,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(os.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM object_shifts os
				LEFT JOIN {$db_name_personnel}.personnel as up ON os.updated_user = up.id
				WHERE 1
					AND os.to_arc = 0
					AND os.id_obj = {$nID}
			";
			
			//LEFT JOIN objects ob ON ob.id = os.id_obj
			//ob.name AS object,
			$oParams = Params::getInstance();
			$oParams->set( "current_page", 0 );
			
			$this->getResult( $sQuery, 'shiftFrom', DBAPI_SORT_ASC, $oResponse );
			
			//debug($oData);

			$oResponse->setField('name',			'име',				'сортирай по име');
			$oResponse->setField('code',			'код',				'сортирай по код');
			$oResponse->setField('start',			'начало',			'сортирай по начало');
			$oResponse->setField('end',				'край',				'сортирай по край');
			$oResponse->setField('duration',		'продълж.',			'сортирай по продължителност');
			$oResponse->setField('automatic',		'авт.',				'сортирай по автоматични', 'images/confirm.gif');
 			$oResponse->setField('stake',			'ставка',			'сортирай по ставка');
			$oResponse->setField('description',		'описание',			'сортирай по описание');
			$oResponse->setField('updated_user',	'...',				'Сортиране по последно редактирал', 'images/dots.gif' );
			//$oResponse->setField('updated_user'	, 'последно редактирал'	, 'сортирай по последно редактирал'	);
			$oResponse->setField('info',			'',				'', 'images/info.gif', 'shiftHistory', '');
			if( $right_edit ) {
				$oResponse->setField( 'id',			'',			'', 'images/edit.gif', 'editShifts', '');
				$oResponse->setField( '',			'',			'', 'images/cancel.gif', 'delShifts', '');

				$oResponse->setFieldLink('name',	'editShifts' );
				$oResponse->setFieldLink('code',	'editShifts' );
			}			
		}
		
		public function getShiftTypes( ) {
			global $db_name_personnel;
			
			$sQuery = "
				SELECT
					ps.id, 
					ps.code,
					ps.name,
					TIME_FORMAT(ps.start, '%H:%i') AS start,
					TIME_FORMAT(ps.end, '%H:%i') AS end
				FROM {$db_name_personnel}.person_shifts ps
				WHERE 1
					AND ps.to_arc = 0
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		
		public function getObjectShifts( $nIDObject, $bAssoc = FALSE, $bIncludeAutomatic = true )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			$sQuery = "
				SELECT 
					s.id AS _id,
					s.*,
					DATE_FORMAT( s.shiftFrom, '%H:%i' ) AS shiftFromShort,
					DATE_FORMAT( s.shiftTo,	'%H:%i' ) AS shiftToShort,
					TIME_FORMAT( s.duration, '%H:%i' ) AS paidDuration,
					TIME_FORMAT(
						IF
						(
							s.shiftTo > s.shiftFrom,
							TIMEDIFF( s.shiftTo, s.shiftFrom ),
							TIMEDIFF( ADDTIME( s.shiftTo, '24:00:00' ), s.shiftFrom )
						),
						'%H:%i'
					) AS shiftDuration,
					ROUND( s.stake, 2 ) AS custStake,
					IF( s.mode = 'leave', 1, 0 ) AS shiftIsLeave,
					CASE
						WHEN s.shiftFrom >= s.shiftTo THEN
							( ( 86400 - TIME_TO_SEC( s.shiftFrom ) ) + TIME_TO_SEC( s.shiftTo ) ) - IF( s.mode != 'leave', TIME_TO_SEC( s.duration ), 0 )
						WHEN s.shiftFrom < s.shiftTo THEN
							( TIME_TO_SEC( s.shiftTo ) - TIME_TO_SEC( s.shiftFrom ) ) - IF( s.mode != 'leave', TIME_TO_SEC( s.duration ), 0 )
					END AS rest,
					IF( s.mode = 'leave', 'none', 'block' ) AS visible
				FROM
					object_shifts s
				WHERE
					s.to_arc = 0
					AND s.id_obj = {$nIDObject}
			";
			
			if( !$bIncludeAutomatic )
			{
				$sQuery .= "
					AND s.automatic = 0
				";
			}
			
			return $bAssoc ? $this->selectAssoc( $sQuery ) : $this->select( $sQuery );
		}
		
		public function getPersonIDsForSchedule( $nIDObject, $nYear, $nMonth )
		{
			global $db_name_personnel;
			
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nYear ) || !is_numeric( $nYear ) || strlen( $nYear ) != 4  )
				throw new Exception($nYear, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

//				Premahvane na izlishnite slujiteli ot grafika!!!				
//				SELECT p.id AS _id, p.id AS id_person
//				FROM {$db_name_personnel}.personnel p
//				WHERE p.to_arc = 0
//					AND p.id_region_object = {$nIDObject}
//				
//				UNION
//				
//			$sQuery = "
//				(SELECT op.id_person AS _id, op.id_person, op.level AS levels
//				FROM object_personnel op
//				WHERE op.id_person > 0
//					AND op.id_object = {$nIDObject} 
//				)
//				
//				UNION 
//				
//				(SELECT od.id_person AS _id, od.id_person, opp.level AS levels
//				FROM object_duty od
//				LEFT JOIN object_personnel opp ON od.id_person = opp.id_person
//				WHERE od.id_person > 0
//					AND od.id_obj = {$nIDObject}
//					AND YEAR( od.startShift ) = {$nYear}
//					AND MONTH( od.startShift ) = {$nMonth} 
//					AND opp.level >= 0
//				GROUP BY id_person
//				)
//			
//			ORDER BY levels
//				
//			";

			$toDate = mktime(0, 0, 0, $nMonth + 1, 1, $nYear);
			
			global $db_name_personnel;
			
			$sQuery = "
				( SELECT op.id_person AS _id, op.id_person, op.level AS levels
				FROM object_personnel op
				LEFT JOIN {$db_name_personnel}.personnel pp ON op.id_person = pp.id
				WHERE op.id_person > 0
					AND UNIX_TIMESTAMP( op.afterDate ) < {$toDate} 
					AND op.id_object = {$nIDObject} 
					AND pp.status = 'active'
				)
				
				UNION 
				
				( SELECT od.id_person AS _id, od.id_person, opp.level AS levels
				FROM object_duty od
				LEFT JOIN object_personnel opp ON od.id_person = opp.id_person
				WHERE od.id_person > 0
					AND od.id_obj = {$nIDObject}
					AND YEAR( od.startShift ) = {$nYear}
					AND MONTH( od.startShift ) = {$nMonth} 
					
				GROUP BY id_person
				ORDER BY levels ASC
				)
			
				ORDER BY levels ASC
				
			";
			//LEFT JOIN object_personnel opp ON od.id_person = opp.id_person
			//LEFT JOIN personnel.personnel pp ON od.id_person = pp.id
			//AND pp.status = 'active'

			$aResult = $this->selectAssoc( $sQuery );
			
			return $aResult;
		}
		
		public function result( $nIDObject, $nYear, $nMonth, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_system, $db_name_sod, $db_sod, $db_personnel;
			
			//$db_sod->debug = true;
			//$db_personnel->debug = true;
			
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nYear ) || !is_numeric( $nYear ) || strlen( $nYear ) != 4  )
				throw new Exception($nYear, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$nCurrentTime = mktime(12, 12, 12, $nMonth, 12, $nYear);
			
			$nDays = date("t", mktime(12, 12, 12, $nMonth, 12, $nYear));
			
			$oPersonnel 		= new DBPersonnel();
			$oDBLeaves 			= new DBLeaves();
			$oDBPersonLeaves 	= new DBPersonLeaves();
			
			$aPersonIDs = $this->getPersonIDsForSchedule( $nIDObject, $nYear, $nMonth );
			
			$data = array();
			
			foreach ( $aPersonIDs as $key => $val ) {
				$data[$key] = $key;
			}

			$data = !empty( $data )? implode(", ", $data) : "NULL";

			
			/*
			$sQuery = "
				SELECT 
					p.id AS _id, 
					p.id,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS personName,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS personName2,
					p.fname, 
					p.mname, 
					p.lname,
					IF ( op.level, op.level, 1000) AS level,
				
".
*/
/*
			       ROUND(
			       	IFNULL(
			           SUM(
			               IF( od.startShift AND od.endShift, UNIX_TIMESTAMP( od.endShift ) - UNIX_TIMESTAMP( od.startShift ), 0 ) / 3600
			               * 
			               os.stake
			               )
			               ,
			               0),
			             2)
			       	AS planMoney,
*/
/*
"					ROUND(
				       	IFNULL(
				           SUM(
				               IF( 
				               		od.id_shift AND od.startShift AND od.endShift, 
				       
							       IF( 
							          	DATE( od.endShift ) <> DATE( od.startShift ),
							          	
							          	( UNIX_TIMESTAMP( DATE( DATE_ADD( od.startShift, INTERVAL 1 DAY ) ) ) - UNIX_TIMESTAMP( od.startShift ) ) 
							          	* 
							          	IF( (h.id IS NOT NULL AND os.automatic != 1 ), s.holiday_stake_factor, DEFAULT( s.holiday_stake_factor ) )
							          	
							          	+
							          	
							          	( UNIX_TIMESTAMP( od.endShift ) - UNIX_TIMESTAMP( DATE( od.endShift ) ) )
							          	*
							          	IF( (h_y.id IS NOT NULL AND os.automatic != 1 ), s.holiday_stake_factor, DEFAULT( s.holiday_stake_factor ) )
							          	, 
							          	
							          	( UNIX_TIMESTAMP( od.endShift ) - UNIX_TIMESTAMP( od.startShift ) )
							          	* 
							          	IF( (h.id IS NOT NULL AND os.automatic != 1 ), s.holiday_stake_factor, DEFAULT( s.holiday_stake_factor ) )
							          	
										)
										* 
	                                    os.stake 
	                                    / 
	                                    3600 
										,
										0 
									)
								)
							, 0)
						, 2
					) AS planMoney,
								       	
			       SEC_TO_FIXTIME(
						SUM( 
							IF( od.id_shift > 0 AND od.startShift AND od.endShift, UNIX_TIMESTAMP( od.endShift ) - UNIX_TIMESTAMP( od.startShift ), 0 ) 
						), 0
			       ) AS planDuration,				
			       	
			       SEC_TO_FIXTIME(
						SUM( 
							IF( od.id_shift > 0 AND od.startRealShift AND od.endRealShift, UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ), 0 ) 
						), 0
			       ) AS realDuration,

					ROUND( IFNULL( SUM(sa.total_sum), 0), 2) as realMoney,
				        
					GROUP_CONCAT(CONCAT(h.id, '=>',DAY( od.startShift ))) as h,
					GROUP_CONCAT(CONCAT(h_y.id, '=>',DAY( od.endShift))) as h2,
					GROUP_CONCAT(DAY( od.startShift ))
				   
				FROM {$db_name_personnel}.personnel p
				LEFT JOIN object_duty od ON ( p.id = od.id_person AND od.id_obj = {$nIDObject} AND YEAR( od.startShift ) = {$nYear} AND MONTH( od.startShift ) = {$nMonth} )
				LEFT JOIN object_shifts os ON od.id_shift = os.id
				LEFT JOIN object_personnel op ON od.id_person = op.id_person AND op.id_object = {$nIDObject}
				LEFT JOIN {$db_name_sod}.holidays h ON ( DAY( od.startShift ) = h.day AND MONTH( od.startShift ) = h.month )
				LEFT JOIN {$db_name_sod}.holidays h_y ON ( DAY( od.endShift ) = h_y.day AND MONTH( od.endShift ) = h_y.month )
				LEFT JOIN {$db_name_personnel}.salary sa ON ( sa.id_object_duty = od.id AND sa.to_arc = 0 )
				LEFT JOIN {$db_name_system}.system s ON 1
				WHERE p.to_arc = 0
					AND p.id IN ( $data )
				GROUP BY p.id WITH ROLLUP
				
				";
		*/

			$sQuery = "
				SELECT 
					p.id AS _id, 
					p.id,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS personName,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS personName2,
					p.fname, 
					p.mname, 
					p.lname,
					IF ( op.level, op.level, 1000) AS level,	
					SEC_TO_FIXTIME (
					   SUM(
					   		IF(od.id_shift != 0  AND od.startShift AND od.endShift,TIME_TO_SEC(os.duration),0)
					   ), 0
					) AS planDuration,
				       
					CONCAT(
						SEC_TO_FIXTIME (
						   SUM(
						   		IF(od.id_shift != 0  AND od.startShift AND od.endShift AND os.mode != 'leave' AND os.mode != 'sick',TIME_TO_SEC(os.duration),0)
						   ), 0
						)
					,' / ',
						SEC_TO_FIXTIME(
							SUM( 
								IF( od.id_shift > 0 AND od.startRealShift AND od.endRealShift AND os.mode != 'leave' AND os.mode != 'sick', TIME_TO_SEC(os.duration), 0 ) 
							), 0
						)
					) AS duration,
					CONCAT(
						SEC_TO_FIXTIME (
						   SUM(
						   		IF(od.id_shift != 0  AND od.startShift AND od.endShift,TIME_TO_SEC(os.duration),0)
						   ), 0
						)
					,' / ',
						SEC_TO_FIXTIME(
							SUM( 
								IF( od.id_shift > 0 AND od.startRealShift AND od.endRealShift, TIME_TO_SEC(os.duration), 0 ) 
							), 0
						)
					) AS durationTotal,
					CONCAT(
						SUM(IF(od.id_shift != 0 AND os.automatic = 0,1,0))
						,' / ',
						SUM(IF(od.id_shift != 0 AND os.automatic = 0 AND  od.startRealShift AND od.endRealShift,1,0)) 
					) AS shifts_count,
					CONCAT(
						ROUND(
							SUM(
								if(
									od.id_shift != 0,
									ROUND((TIME_TO_SEC(os.duration) / (60 * 60)) * os.stake * p.shifts_factor,2),
									0
								) 
							) 
							,
							2
						)
					,' / ',
						ROUND( IFNULL( SUM(sa.total_sum), 0), 2)
					) AS money
				FROM {$db_name_personnel}.personnel p
				LEFT JOIN object_duty od ON ( p.id = od.id_person AND od.id_obj = {$nIDObject} AND YEAR( od.startShift ) = {$nYear} AND MONTH( od.startShift ) = {$nMonth} )
				LEFT JOIN object_shifts os ON od.id_shift = os.id
				LEFT JOIN object_personnel op ON od.id_person = op.id_person AND op.id_object = {$nIDObject}
				LEFT JOIN {$db_name_personnel}.salary sa ON ( sa.id_object_duty = od.id AND sa.to_arc = 0 )
				WHERE p.to_arc = 0
					AND p.id IN ( $data )
				GROUP BY p.id WITH ROLLUP
				
				";
			

/*		


			       ROUND(
			       	IFNULL(
			           SUM(
			               IF( od.startRealShift AND od.endRealShift, UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ), 0 ) / 3600
			               * 
			               od.stake
			               )
			               ,
			               0),
			             2)
			       	AS realMoneys,

			
	ROUND( IFNULL( (SELECT SUM(total_sum) FROM {$db_name_personnel}.salary WHERE id_person = od.id_person AND month = '{$nYear}{$nMonth}' AND id_object = od.id_obj), 0), 2) as realMoney,
			       ROUND(
			       	IFNULL(
			           SUM(
			               IF( od.startRealShift AND od.endRealShift, UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ), 0 ) / 3600
			               * 
			               od.stake
			               )
			               ,
			               0),
			             2)
			       	AS realMoneysssss,
					


					ROUND(
				       	IFNULL(
				           SUM(
				               IF( 
				               		od.id_shift AND od.startShift AND od.endShift, 
				       
							       IF( 
							          	DATE( od.endShift ) <> DATE( od.startShift ),
							          	
							          	( UNIX_TIMESTAMP( DATE( DATE_ADD( od.startShift, INTERVAL 1 DAY ) ) ) - UNIX_TIMESTAMP( od.startShift ) ) 
							          	* 
							          	IF( h.id IS NOT NULL, s.holiday_stake_factor, DEFAULT( s.holiday_stake_factor ) )
							          	
							          	+
							          	
							          	( UNIX_TIMESTAMP( od.endShift ) - UNIX_TIMESTAMP( DATE( od.endShift ) ) )
							          	*
							          	IF( h_y.id IS NOT NULL, s.holiday_stake_factor, DEFAULT( s.holiday_stake_factor ) )
							          	, 
							          	
							          	UNIX_TIMESTAMP( od.endShift ) - UNIX_TIMESTAMP( od.startShift )
							          	
										)
										* 
	                                    os.stake 
	                                    / 
	                                    3600 
										,
										0 
									)
								)
							, 0)
						, 2
					) AS planMoney,
		*/	
			
			$oResponse->setField('personName'	, "Име"			);
			//$oResponse->setField('planMoney'	, "План.нар."	);
			//$oResponse->setField('planDuration'	, "План.час."	);
			//$oResponse->setField('realMoney'	, "Изп.нар."	);
			//$oResponse->setField('realDuration'	, "Изп.час"		);
			
			//BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
			$oResponse->setField( "shift_hours", "Часове Смени" );
			//END CODE : Person Shift Hours Limit
			
			$oResponse->setField('durationTotal', "Общо часове",	NULL,NULL,NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('duration'		, "Часове деж.",	NULL,NULL,NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('shifts_count'	, "Смени",			NULL,NULL,NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			$oResponse->setField('money'		, "Наработка",		NULL,NULL,NULL,NULL,array('DATA_FORMAT' => DF_CENTER));
			
			$oResponse->setFieldLink('personName', 'onClickPerson');
			
			//$oResponse->setFieldAttributes('planMoney', 	array("DATA_TOTAL" => 'DATA_TOTAL'));
			//$oResponse->setFieldAttributes('planDuration', 	array("DATA_TOTAL" => 'DATA_TOTAL'));
			//$oResponse->setFieldAttributes('realMoney', 	array("DATA_TOTAL" => 'DATA_TOTAL'));
			//$oResponse->setFieldAttributes('realDuration', 	array("DATA_TOTAL" => 'DATA_TOTAL'));

			$aDataTemp = $this->selectAssoc( $sQuery );
			APILog::Log(0, $sQuery);
			$aRowTotal = array_pop( $aDataTemp );
			$aRowTotal['id'] = '__TOTAL__';
			$aRowTotal['shift_hours'] 	= "";
			$aRowTotal['personName'] 	= "";
			$aRowTotal['personName2'] 	= "";
//			$aRowTotal['personName3'] 	= "";
			
			$aDataTemp = !empty($aDataTemp) ? array_multi_csort($aDataTemp, 'level') : array();			

			$aData = array();
			
			$oDBObjectScheduleSettings 	= new DBObjectScheduleSettings();
			$oDBObjectScheduleHours 	= new DBObjectScheduleHours();
			
			foreach( $aDataTemp as $aRow )
				$aData[ $aRow['id'] ] = $aRow;
		
			unset( $aDataTemp );
			
			$sQueryTemplate = "
				SELECT 
					p.id as _id,
					os.code,
					
					DATE_FORMAT( os.shiftFrom, 	'%%H:%%i' ) AS shiftFromShort,
					DATE_FORMAT( os.shiftTo,	'%%H:%%i' ) AS shiftToShort,
					CASE
						WHEN os.shiftFrom >= os.shiftTo THEN
							( ( 86400 - TIME_TO_SEC( os.shiftFrom ) ) + TIME_TO_SEC( os.shiftTo ) ) - IF( os.mode != 'leave', TIME_TO_SEC( os.duration ), 0 )
						WHEN os.shiftFrom < os.shiftTo THEN
							( TIME_TO_SEC( os.shiftTo ) - TIME_TO_SEC( os.shiftFrom ) ) - IF( os.mode != 'leave', TIME_TO_SEC( os.duration ), 0 )
					END AS rest,
					
					IF( LENGTH(os.code) AND NOT od.startRealShift, 
						CONCAT_WS('\n',
							CONCAT('служител:\t', p.fname, ' ', p.mname, ' ', p.lname), 
							CONCAT('телефон:\t',  p.mobile), 
							CONCAT('смяна:\t\t',  os.code), 
							CONCAT('застъпва:\t', DATE_FORMAT(od.startShift, '%%d.%%m.%%Y %%H:%%i')),
							CONCAT('отстъпва:\t', DATE_FORMAT(od.endShift, '%%d.%%m.%%Y %%H:%%i'))
							)
						, '') AS code2,
					IF( od.startRealShift AND od.endRealShift, 
						CONCAT_WS('\n',
							CONCAT('служител:\t', p.fname, ' ', p.mname, ' ', p.lname), 
							CONCAT('телефон:\t',  p.mobile), 
							CONCAT('смяна:\t\t',  os.code), 
							CONCAT('застъпил:\t', DATE_FORMAT(od.startRealShift, '%%d.%%m.%%Y %%H:%%i')),
							CONCAT('отстъпил:\t', DATE_FORMAT(od.endRealShift, '%%d.%%m.%%Y %%H:%%i'))
							)
						, '') AS isValidated,
					IF( od.id_shift > 0 AND UNIX_TIMESTAMP(od.startRealShift) > 0 AND UNIX_TIMESTAMP(od.endRealShift) = 0, 
						CONCAT_WS('\n',
							CONCAT('служител:\t', p.fname, ' ', p.mname, ' ', p.lname), 
							CONCAT('телефон:\t',  p.mobile), 
							CONCAT('смяна:\t\t',  os.code), 
							CONCAT('застъпил:\t', DATE_FORMAT(od.startRealShift, '%%d.%%m.%%Y %%H:%%i'))
							)
						, '') AS isValidatedStart,
					IF( od2.id, 
						CONCAT_WS('\n', 
							CONCAT('служител:\t', p.fname, ' ', p.mname, ' ', p.lname), 
							CONCAT('телефон:\t',  p.mobile), 
							CONCAT('смяна:\t\t',  os2.code),
							CONCAT('обект:\t\t'	, o2.num, ' ', o2.name),
							CONCAT('застъпил:\t'	, DATE_FORMAT(od2.startShift, '%%d.%%m.%%Y %%H:%%i')),
							CONCAT('отстъпил:\t'	, DATE_FORMAT(od2.endShift, '%%d.%%m.%%Y %%H:%%i'))
							)
					, '') AS busy, 

					CONCAT_WS('\n', 
						CONCAT('служител:\t', p.fname, ' ', p.mname, ' ', p.lname),
						CONCAT('телефон:\t',  p.mobile), 
						CONCAT('дата:\t\t', DATE_FORMAT(od.startShift, '%%d.%%m.%%Y'))
					) AS empt
					
				FROM {$db_name_personnel}.personnel p
				LEFT JOIN object_duty od ON ( od.id_person = p.id AND od.id_obj = {$nIDObject} )
				LEFT JOIN object_shifts os ON od.id_shift = os.id
				LEFT JOIN object_duty od2 ON ( od.id_person = od2.id_person AND DATE( od.startShift ) = DATE( od2.startShift ) AND od2.id_obj <> od.id_obj AND od2.id_shift > 0 )
				LEFT JOIN object_shifts os2 ON od2.id_shift = os2.id
				LEFT JOIN objects o2 ON od2.id_obj = o2.id
				WHERE 1
				".'
				AND DATE( od.startShift ) = \'%1$u-%2$u-%3$u\'
				';
			
			if( !empty( $aData ) )
				$sQueryTemplate .= sprintf("AND p.id IN( %s )\n", implode(", ", array_keys( $aData )));
			else 
				$sQueryTemplate .= "AND 0\n";
				
			$sQueryTemplate .= "
				GROUP BY p.id
				";
			
			$sRowTotalQueryTemplate = "
				SELECT 
			        SEC_TO_FIXTIME( 
			          SUM( 
				          IF( od.startShift AND od.endShift, TIME_TO_SEC(os.duration), 0) 
			          ), 0
					) AS totalDuration
				FROM object_duty od
				LEFT JOIN object_shifts os ON os.id = od.id_shift
				WHERE 1
				AND od.id_shift > 0
				AND od.id_obj = {$nIDObject}
				"
				.'AND DATE( od.startShift ) = \'%1$u-%2$u-%3$u\''
				;
			
			if( !empty( $aData ) )
				$sRowTotalQueryTemplate .= sprintf(" AND id_person IN( %s )\n", implode(", ", array_keys( $aData )));
			else
				$sRowTotalQueryTemplate .= "AND 0\n";
			
			$oHolidays = new DBHolidays();
			//APILog::Log(0, $sRowTotalQueryTemplate);

			$wk[0] = 'Н';
			$wk[1] = 'П';
			$wk[2] = 'В';
			$wk[3] = 'С';
			$wk[4] = 'Ч';
			$wk[5] = 'П';
			$wk[6] = 'С';

			$wks[0] = 'Неделя';
			$wks[1] = 'Понеделник';
			$wks[2] = 'Вторник';
			$wks[3] = 'Сряда';
			$wks[4] = 'Четвъртък';
			$wks[5] = 'Петък';
			$wks[6] = 'Събота';
			
			for($i=1;$i<=$nDays;$i++) {
				
				$day = date( "w", mktime(0, 0, 0, $nMonth, $i, $nYear) ); //{$nMonth} {$nYear}
				$oResponse->setField($i, sprintf("%02u%s", $i, $wk[$day]));
				
				$sQuery = sprintf($sQueryTemplate, $nYear, $nMonth, $i);
				
				$aDaysData = $this->selectAssoc( $sQuery );
				
				foreach( $aData as $nIDPerson => $aRow )
				{
					$aData[ $nIDPerson ][ $i ] 				= !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['code'] 			: "";
					$aData[ $nIDPerson ]['isValidated']		= !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['isValidated'] 	: "";
					$aData[ $nIDPerson ]['code']			= !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['code'] 			: 0;
					$aData[ $nIDPerson ]['code2']			= !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['code2'] 			: 0;
					$aData[ $nIDPerson ]['isValidatedStart'] = !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['isValidatedStart'] 	: "";
					$aData[ $nIDPerson ]['busy'] 			= !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['busy'] 			: "";
					$aData[ $nIDPerson ]['empt'] 			= !empty( $aDaysData[ $nIDPerson ] ) ? $aDaysData[ $nIDPerson ]['empt'] 			: "";
					
					//BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
					if( isset( $aDaysData[ $nIDPerson ] ) )
					{
						$sCurrentDayHours = $oDBObjectScheduleSettings->calculateShiftHours( $aDaysData[ $nIDPerson ]['shiftFromShort'], $aDaysData[ $nIDPerson ]['shiftToShort'], $aDaysData[$nIDPerson]['rest'], false );
						
						$oResponse->setFormElement( "form1", "sid[{$i}][{$nIDPerson}]", array(), $sCurrentDayHours );
					}
					//END CODE : Person Shift Hours Limit
				}
				
				$bIsWeeked = in_array( date("w", mktime(12, 12, 12, $nMonth, $i, $nYear)), array(0, 6) );
				
				$sClass = "";
				
				$bWeekendLeave = false;
				if( $bIsWeeked )
				{
					if( $oHolidays->isWorkday( $i, $nMonth, $nYear ) )
					{
						$sClass .= "work_weekend ";
					}
					else
					{
						$sClass .= "weekend ";
						$bWeekendLeave = true;
					}
				}
				
				if( $oHolidays->isHoliday($i, $nMonth) || $oHolidays->isRestday( $i, $nMonth, $nYear ) )
				{
					$sClass .= "holiday ";
					$bWeekendLeave = true;
				}
				
				$oResponse->setFieldAttributes($i, array("class" => $sClass, 'title' => $wks[$day].', '.$i.'.'.$nMonth.'.'.$nYear, 'style'=> 'text-align: center;', "PDF_WIDTH"=>6));
				
				$sQueryTotalRow = sprintf($sRowTotalQueryTemplate, $nYear, $nMonth, $i);
				
				$aRowTotal[ $i ] = $this->selectOne( $sQueryTotalRow );
				
				//Leave Check
				$aPersonsLeaves = $oDBPersonLeaves->getPersonLeavesForMonth( $nYear, $nMonth );
				//End Leave Check
				
				foreach( $aData as $nIDPerson => $aRow )
				{
					$sInnerTitle = "";
					
					$sInnerClass  = $sClass;
					$sInnerClass .= "days ";
					
					if( !empty( $aRow['isValidated'] ) ) {
						if ( !empty($aRow['code']) ) {
							$sInnerTitle = $aRow['isValidated'];
						} else $sInnerTitle = $aRow['empt'];
					} else $sInnerClass .= "invalidated ";
						
					if( !empty( $aRow['busy'] ) )
					{
						$sInnerClass .= "busy ";
						$sInnerTitle = $aRow['busy'];
					}

					if( !empty( $aRow['isValidatedStart'] ) ) {
						$sInnerClass .= "isValidatedStart ";
						$sInnerTitle = $aRow['isValidatedStart'];
					}

					if( !empty( $aRow['code2'] ) ) {
						//$sInnerClass .= "isValidatedStart ";
						$sInnerTitle = $aRow['code2'];
					}
						
					if( empty($aRow['isValidated']) && empty($aRow['busy']) && empty($aRow['isValidatedStart']) && empty($aRow['code2']) ) {
						$sInnerTitle = $aRow['empt'];
					}
					
					//--Leave Check
					$sYearMonth = $nYear . "-" . LPAD( $nMonth, 2, 0 ) . "-" . LPAD( $i, 2, 0 );
					
					foreach( $aPersonsLeaves as $nIDLeavePerson => $aLeaves )
					{
						if( $nIDLeavePerson == $nIDPerson )
						{
							foreach( $aLeaves as $nIDLeave => $aLeave )
							{
								if( $sYearMonth >= $aLeave['leave_from'] && $sYearMonth <= $aLeave['leave_to'] )
								{
									$sWord = "";
									$sWord = $aLeave['application_type'] == "application" ? "leave_planned" : "hospital";
									if( $bWeekendLeave )$sInnerClass .= "{$sWord}_weekend ";
									else if( empty( $aLeave['is_confirm_num'] ) || $aLeave['application_type'] == "hospital" )$sInnerClass .= "{$sWord} ";
									
									$sWord = $aLeave['application_type'] == "application" ? "Отпуск" : "Болничен";
									$sToAdd = "\n\n";
									$sToAdd .= "{$sWord} От:\t" . 	$aLeave['leave_from_bg'] ."\n";
									$sToAdd .= "{$sWord} До:\t" . 	$aLeave['leave_to_bg'] 	."\n";
									$sToAdd .= "Тип:\t\t" . 		$aLeave['leave_type'] ."\n";
									$sToAdd .= "Статус:\t" . 		$aLeave['is_confirm'];
									
									if( !empty( $aLeave['is_confirm_num'] ) )
									{
										$sToAdd .= "\nПродължит. :\t8 часа";
									}
									
									$sInnerTitle .= $sToAdd;
								}
							}
						}
					}
					//--End Leave Check
					
					$oResponse->setDataAttributes( $nIDPerson, $i, array( "class" => $sInnerClass, "title" => $sInnerTitle, "align" => "center", "onclick" => "onDayClick()" ) );
				}
				
				// Pavel 
//				if ( $i == 15 ) {
//					$oResponse->setField('personName2'	, "Име"			);
//					$oResponse->setFieldLink('personName2', 'onClickPerson');
//				}
				//</Pavel
				
				// < dido2k >
				// Павкааа, не бар-базик че работата сериозна :P
				// </ dido2k >
			}
			
			//BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
			$oDBScheduleMonthNorms 	= new DBScheduleMonthNorms();
			$oDBPersonMonthLimits 	= new DBPersonMonthLimits();
			$oDBObjectDuty 			= new DBObjectDuty();
			
			$nHoursBeginTotal = 0;
			$nHoursEndTotal = 0;
			// СУМА от броя на ЧАСОВЕ за смени по НОРМА, за МЕСЕЦИ БЕЗ ТЕКУЩИЯ.
			$nHoursNormSum = $oDBScheduleMonthNorms->getHalfyearHourNormsToDate( $nYear, $nMonth, false );
			// СУМА от броя на ЧАСОВЕ за смени по НОРМА, за ТЕКУЩИЯ МЕСЕЦ.
			$nHoursNormEnd = $oDBScheduleMonthNorms->getHourNormsForDate( $nYear, $nMonth );
			
			foreach( $aData as $nIDPerson => $aRow )
			{
				// ЧАСОВЕ изработени смени до ПРЕДХОДНИЯ МЕСЕЦ.
				$nHoursCurrentSum = $oDBPersonMonthLimits->getHalfyearHourCurrentToDate( $nIDPerson, $nYear, $nMonth, false );
				// ЧАСОВЕ смени за ТЕКУЩИЯ МЕСЕЦ.
				$sScheduleCurrentHours = $oDBObjectDuty->getHourCurrentForDate( $nIDPerson, $nMonth, $nYear, false );
				
				//Set BEGIN and END values
				$sHoursBeginReal = getTimeSum( $nHoursCurrentSum, $nHoursNormSum, true );
				$nHoursBegin = getRoundTime( $sHoursBeginReal );
				
//				$oResponse->setAlert( $nHoursCurrentSum . " - " . $nHoursNormSum . " = " . $nHoursBegin );
//				$oResponse->setAlert( getTimeSum( -$nHoursNormEnd, $nHoursBegin ) . " + " . $sScheduleCurrentHours . " = " . getTimeSum( getTimeSum( -$nHoursNormEnd, $nHoursBegin ), $sScheduleCurrentHours ) );
				
				//$sHoursEndReal = $sScheduleCurrentHours;
				$sHoursEndReal = getTimeSum( getTimeSum( -$nHoursNormEnd, $sHoursBeginReal ), $sScheduleCurrentHours );
				$oResponse->setFormElement( "form1", "real_hours[{$nIDPerson}]", array(), $sHoursEndReal );
				
				$nHoursEnd = getRoundTime( $sHoursEndReal );
				//End Set BEGIN and END values
				
				//Coloring
				if( $nHoursBegin < 0 )$sColorBegin = "#0000FF";
				if( $nHoursBegin == 0 )$sColorBegin = "#00BB00";
				if( $nHoursBegin > 0 )$sColorBegin = "#FF0000";
				
				if( $nHoursEnd < 0 )$sColorEnd = "#0000FF";
				if( $nHoursEnd == 0 )$sColorEnd = "#00BB00";
				if( $nHoursEnd > 0 )$sColorEnd = "#FF0000";
				$sInnerHTML = "
					<SPAN style=\"color: {$sColorBegin};\">{$nHoursBegin}</SPAN>
					/
					<SPAN style=\"color: {$sColorEnd};\">{$nHoursEnd}</SPAN>
				";
				
				//End Coloring
				
				$aData[$nIDPerson]['shift_hours'] = $nHoursBegin . " / " . $nHoursEnd;
				$oResponse->setDataAttributes( $nIDPerson, "shift_hours", array( "value" => $sInnerHTML, "style" => "text-align: center;" ) );
				
				$nHoursBeginTotal += $nHoursBegin;
				$nHoursEndTotal += $nHoursEnd;
			}
			
			$aRowTotal['shift_hours'] = $nHoursBeginTotal . " / " . $nHoursEndTotal;
			//END CODE : Person Shift Hours Limit
			
			//Pavel
			$oResponse->setField('personName2'	, "Име"			);
			$oResponse->setFieldLink('personName2', 'onClickPerson');
			//</Pavel
			
			$aData[] = $aRowTotal;
			
			$oResponse->setData( $aData );
			$oResponse->setRowAttributes('__TOTAL__', array("class" => "total"));
		}
		
		public function getObjectShiftsGraph( DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'edit_object', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$oData = array();
			
			//Create Working Time Period
			$sCurrentTime = date( "H:i:s" );
			$sCurrentDate = date( 'd-m-Y' );
			$aTimeScale = array();
			
			for( $q = -6; $q <= 6; $q++ )
			{
				$aTimeScale[] = $this->getTimeGoing( $sCurrentTime, $q );
			}
			
			if( empty( $aTimeScale ) )
				throw new Exception( NULL, DBAPI_ERR_UNKNOWN );
			//End Create Working Time Period
			
			//Prepare Report Table
			for( $q = 0; $q < 14; $q++ )
			{
				if( $q == 0 )
				{
					$oResponse->setField( 'object', 'Обект' );
					$oResponse->setFieldAttributes( 'object', array( 'width' => '100px' ) );
				}
				else
				{
					if( $q == 7 )$oResponse->setField( 'h' . substr( $aTimeScale[$q - 1], 0, 2 ), $aTimeScale[$q - 1] );
					else
					{
						$oResponse->setField( 'h' . substr( $aTimeScale[$q - 1], 0, 2 ), substr( $aTimeScale[$q - 1], 0, 2) );
						$oResponse->setFieldAttributes( 'h' . substr( $aTimeScale[$q - 1], 0, 2 ), array( 'width' => '50px' ) );
					}
				}
			}
			//End Prepare Report Table
			
			//Classifying Object-Shifts Data
			
			//Create Time Relations
			if( substr( $aTimeScale[0], 8, 1 ) == "|" && substr( $aTimeScale[12], 8, 1 ) == "|" )
			{
				$sTimeFrom = 	date( "Y-m-d" ) . " " . substr( $aTimeScale[0], 0, 3 ) . "00:00";
				$sTimeTo = 		date( "Y-m-d" ) . " " . substr( $aTimeScale[12], 0, 3 ) . "59:59";
			}
			if( substr( $aTimeScale[0], 8, 1 ) == "|" && substr( $aTimeScale[12], 8, 1 ) == "+" )
			{
				$sTimeFrom = 	date( "Y-m-d" ) . " " . substr( $aTimeScale[0], 0, 3 ) . "00:00";
				$sTimeTo = 		date( "Y-m-d", mktime( 0, 0, 0, date('m'), date('d') + 1, date('Y') ) ) . " " . substr( $aTimeScale[12], 0, 3 ) . "59:59";
			}
			if( substr( $aTimeScale[0], 8, 1 ) == "|" && substr( $aTimeScale[12], 8, 1 ) == "-" )
			{
				$sTimeFrom = 	date( "Y-m-d", mktime( 0, 0, 0, date('m'), date('d') - 1, date('Y') ) ) . " " . substr( $aTimeScale[0], 0, 3 ) . "00:00";
				$sTimeTo = 		date( "Y-m-d" ) . " " . substr( $aTimeScale[12], 0, 3 ) . "59:59";
			}
			//End Create Time Relations
			
			//Extracting Objects Data
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						ob.id AS id,
						ob.name AS name,
						ob.num AS num,
						ob.is_sod,
						od.startShift AS start,
						od.endShift AS end,
						od.startRealShift AS real_start,
						od.endRealShift AS real_end,
						IF
						(
							(
								(
									od.startShift < '{$sTimeFrom}'
									AND
									od.startRealShift = '0000-00-00 00:00:00'
								)
								OR
								(
									od.endShift < '{$sTimeFrom}'
									AND
									od.endRealShift = '0000-00-00 00:00:00'
								)
							),
							1,
							0
						) AS outdated
					FROM object_duty od
						LEFT JOIN objects ob ON ob.id = od.id_obj
						LEFT JOIN object_shifts os ON os.id = od.id_shift
					WHERE 1
						AND od.id_shift != 0
						AND ob.id != 0
						AND ob.is_fo = 1
						AND os.to_arc = 0
					ORDER BY ob.is_sod DESC, name ASC, outdated ASC
			";
			
			$aObjectsQuery = $this->select( $sQuery );
			//End Extracting Objects Data
			
			//Processing Objects
			$aObjects = array();
			foreach( $aObjectsQuery as $aObjectQuery )
			{
				$aObjects[ $aObjectQuery['id'] ]['id'] 			= $aObjectQuery['id'];
				$aObjects[ $aObjectQuery['id'] ]['name']		= $aObjectQuery['name'];
				$aObjects[ $aObjectQuery['id'] ]['num']			= $aObjectQuery['num'];
				$aObjects[ $aObjectQuery['id'] ]['is_sod']		= $aObjectQuery['is_sod'];
				
				//Records, that are outdated
				if( isset( $aObjects[ $aObjectQuery['id'] ]['outdated'] ) )
				{
					if( $aObjects[ $aObjectQuery['id'] ]['outdated'] != 1 )
					{
						$aObjects[ $aObjectQuery['id'] ]['outdated'] = $aObjectQuery['outdated'];
					}
				}
				else
				{
					$aObjects[ $aObjectQuery['id'] ]['outdated'] = $aObjectQuery['outdated'];
				}
				//Records, that are outdated
				
				//Records, that are displayed
				if( !isset( $aObjects[ $aObjectQuery['id'] ]['display'] ) )
				{
					$aObjects[ $aObjectQuery['id'] ]['display'] = false;
				}
				if( $aObjectQuery['real_start'] == "0000-00-00 00:00:00" || $aObjectQuery['real_end'] == "0000-00-00 00:00:00" )
				{
					$aObjects[ $aObjectQuery['id'] ]['display'] = true;
				}
				if( ( $aObjectQuery['start'] >= $sTimeFrom && $aObjectQuery['start'] <= $sTimeTo ) || 
					( $aObjectQuery['end'] >= $sTimeFrom && $aObjectQuery['end'] <= $sTimeTo ) )
				{
					$aObjects[ $aObjectQuery['id'] ]['display'] = true;
				}
				//Records, that are displayed
			}
			//End Processing Objects
			
			$aData = array();
			foreach( $aObjects as $aObject )
			{
				if( !$aObject['display'] )continue;
					
				//Extract Shifts Data
				$sQuery = "
						SELECT
							od.id,
							os.name,
							CONCAT_WS( ' ', p.fname, p.mname, p.lname, ' GSM: ', p.mobile ) AS p_name,
							od.startShift AS start,
							od.endShift AS end,
							od.startRealShift AS real_start,
							od.endRealShift AS real_end
						FROM object_duty od
							LEFT JOIN objects ob ON ob.id = od.id_obj
							LEFT JOIN object_shifts os ON od.id_shift = os.id
							LEFT JOIN {$db_name_personnel}.personnel p ON od.id_person = p.id
						WHERE 1
							AND od.id_obj = {$aObject['id']}
							AND p.to_arc = 0
							AND os.to_arc = 0
				";
				
				$aShifts = $this->select( $sQuery );
				//End Extract Shifts Data
				
				//Form a Row
				for( $q = 0; $q < 13; $q++ )
				{
					$sCurrentCell = 'h' . substr( $aTimeScale[$q], 0, 2 );					//Името на текущата клетка
					$sInfo = "Обект: " . " {$aObject['name']} [{$aObject['num']}]";			//Инициализация на съпровождаща информация за клетката
					$bReadOnly = false;														//Затваряне на клетката, ако попадне на невалидирана смяна
					
					//Initialize Cell
					$aData[ $aObject['id'] ][ $sCurrentCell ] = "";
					
					foreach( $aShifts as $aShift )
					{
						$sComparisonShiftStart 	= substr( $aShift['start'], 11, 2 );		//Първите 2 цифри от часа на startShift
						$sComparisonShiftEnd 	= substr( $aShift['end'], 11, 2 );			//Първите 2 цифри от часа на endShift
						$sComparisonTimescale 	= substr( $aTimeScale[$q], 0, 2 );			//Първите 2 цифри от часа на текущата клетка
						
						if( ( $aShift['start'] >= $sTimeFrom && $aShift['start'] <= $sTimeTo ) || ( $aShift['end'] >= $sTimeFrom && $aShift['end'] <= $sTimeTo ) )
						{
							if( $aShift['start'] >= $sTimeFrom && $aShift['start'] <= $sTimeTo )$bStartAllowed = true;
							else $bStartAllowed = false;
							if( $aShift['end'] >= $sTimeFrom && $aShift['end'] <= $sTimeTo )$bEndAllowed = true;
							else $bEndAllowed = false;
							
							if( $sComparisonShiftStart == $sComparisonTimescale || $sComparisonShiftEnd == $sComparisonTimescale )
							{
								$sInfo .= "\n Смяна: " . $aShift['name'];
								
								if( $bStartAllowed )
								{
									if( $sComparisonShiftStart == $sComparisonTimescale )
									{
										if( !$bReadOnly )$sCellColour = "C8C8C8";
										
										$aData[ $aObject['id'] ][ $sCurrentCell ] = substr( $aShift['start'], 11, 5 );
										$sInfo .= "\n Застъпил: " . $aShift['p_name'];
										
										if( !$bReadOnly )
										{
											if( $aShift['real_start'] == "0000-00-00 00:00:00" )
											{
												if( $q < 6 )$sCellColour = "FF6464";
												else $sCellColour = "00C8FF";
												$bReadOnly = true;
											}
										}
										
										$oResponse->setDataAttributes( $aObject['id'], $sCurrentCell, array( 'title' 	=> $sInfo,
																											 'style'	=> "background: {$sCellColour}; text-align: center; cursor: pointer;",
																											 'onclick' 	=> "openShift( {$aObject['id']} )" ) );
									}
								}
								
								if( $bEndAllowed )
								{
									if( $sComparisonShiftEnd == $sComparisonTimescale )
									{
										if( !$bReadOnly )$sCellColour = "C8C8C8";
										
										$aData[ $aObject['id'] ][ $sCurrentCell ] = substr( $aShift['end'], 11, 5 );
										$sInfo .= "\n Отстъпил: " . $aShift['p_name'];
										
										if( !$bReadOnly )
										{
											if( $aShift['real_end'] == "0000-00-00 00:00:00" )
											{
												if( $q < 6 )$sCellColour = "FF6464";
												else $sCellColour = "00C8FF";
												$bReadOnly = true;
											}
										}
										
										$oResponse->setDataAttributes( $aObject['id'], $sCurrentCell, array( 'title' 	=> $sInfo,
																											 'style'	=> "background: {$sCellColour}; text-align: center; cursor: pointer;",
																											 'onclick' 	=> "openShift( {$aObject['id']} )" ) );
									}
								}
							}
						}
					}
				}
				
				$aData[ $aObject['id'] ]['object'] = $aObject['name'] . " [{$aObject['num']}]";
				
				if( $aObject['outdated'] == 1 )
				{
					if( $aObject['is_sod'] )$sColor = "C87777";
					else $sColor = "C80000";
				}
				else
				{
					if( $aObject['is_sod'] )$sColor = "323232";
					else $sColor = "969696";
				}
				
				$oResponse->setDataAttributes( $aObject['id'], 'object', array( 'onclick' => "openShift( {$aObject['id']} )",
																				'style' => "color: $sColor; cursor: pointer;" ) );
				
				//End Form a Row
			}
			//End Classifying Object-Shifts Data
			
			$oResponse->setData( $aData );
		}
		
		public function getTimeGoing( $sCurrentTime, $nOffset )
		{
			$sDayOffset = "|";
			$sFB = $nOffset > 0 ? "forward" : "backward";
			if( $nOffset < 0 )$nOffset = -$nOffset;
			
			$nHours = (int) substr( $sCurrentTime, 0, 2 );
			
			if( empty( $nHours ) || empty( $nOffset ) ) return $sCurrentTime;
			
			for( $i = 0; $i < $nOffset; $i++ )
			{
				if( $sFB == "forward" )
				{
					$nHours++;
					if( $nHours > 23 )
					{
						$nHours = 0;
						$sDayOffset = "+";
					}
				}
				if( $sFB == "backward" )
				{
					$nHours--;
					if( $nHours < 0 )
					{
						$nHours = 23;
						$sDayOffset = "-";
					}
				}
			}
			
			$sHours = (string) $nHours;
			if( strlen( $sHours ) == 1 )$sHours = '0' . $sHours;
			
			$sCurrentTime = substr( $sCurrentTime, 2, strlen( $sCurrentTime ) - 2 );
			$sCurrentTime = $sHours . $sCurrentTime;
			
			$sCurrentTime .= $sDayOffset;
			
			return $sCurrentTime;
		}
		
		public function getObjects( $nIDFirm, $nIDOffice) {
			
			$sIDOffices = implode(',',$_SESSION['userdata']['access_right_regions']);
			$bAllOffices = !empty($_SESSION['userdata']['access_right_all_regions']) ? true : false;
			
			$sQuery = "
					SELECT
						o.id AS id,
						o.name AS name,
						o.num AS num
					FROM object_shifts osh
						LEFT JOIN objects o ON o.id = osh.id_obj
						LEFT JOIN offices off ON off.id = o.id_office
					WHERE 1
						AND osh.id_obj != 0
						AND osh.to_arc = 0
						AND o.is_fo = 1\n
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
			
			$sQuery .= "
					GROUP BY id
					ORDER BY name ASC
			";
			return $this->selectAssoc($sQuery);
		}
	
		public function shiftInUse( $nIDShift ) {
			if( empty( $nIDShift ) || !is_numeric( $nIDShift ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT 
					count(od.id) as br
				FROM object_duty od
				WHERE od.id_shift = {$nIDShift}
					AND UNIX_TIMESTAMP(od.endRealShift) = 0
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function makeArchivFromShift( $nIDShift ) {
			global $db_sod;
			
			if ( empty($nIDShift) || !is_numeric($nIDShift) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			/*
				
			$updated_user = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			
			$sQuery = "
				INSERT INTO object_shifts
					(id_obj, id_archiv, code, name, mode, automatic, shiftFrom, shiftTo, stake, validFrom, validTo, description, updated_user, updated_time, to_arc)
				SELECT
					 id_obj, id, 		code, name, mode, automatic, shiftFrom, shiftTo, stake, validFrom, NOW(),	description, '{$updated_user}', NOW(), 1
				FROM object_shifts
				WHERE id = '{$nIDShift}'			
			";
			$db_sod->Execute($sQuery);
			*/
			$aObjectShift = array();
			$aObjectShift = $this->getRecord($nIDShift);
			
			$aObjectShift['id_archiv'] = $aObjectShift['id'];
			$aObjectShift['validTo'] = time();
			$aObjectShift['to_arc'] = 1;
			unset($aObjectShift['id']);
			
			$this->update($aObjectShift);
			
			return $aObjectShift['id'];			
		}
	
		public function getReportArchiv( $nID, DBResponse $oResponse ) {
			global $db_name_personnel;
			$nID = !empty($nID) && is_numeric($nID) ? $nID : 0;
			
			$right_edit = false;
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('object_shifts_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$oData = array();
			
			$sQuery = "
				SELECT
					os.id, 
					os.id as info, 
					os.code,
					os.name,
					os.automatic,
					os.stake,
					TIME_FORMAT(os.shiftFrom, '%H:%i') AS start,
					TIME_FORMAT(os.shiftTo, '%H:%i') AS end,
					DATE_FORMAT(os.validFrom, '%d.%m.%Y') AS validFrom,
					DATE_FORMAT(os.validTo, '%d.%m.%Y') AS validTo,
					os.description,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(os.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM object_shifts os
				LEFT JOIN {$db_name_personnel}.personnel as up ON os.updated_user = up.id
				WHERE 1
					AND os.to_arc = 1
					AND os.id_archiv = {$nID}
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_DESC, $oResponse );
			
			$oResponse->setField('name',			'име',			'сортирай по име');
			$oResponse->setField('code',			'код',			'сортирай по код');
			$oResponse->setField('validFrom',		'от',			'сортирай по Валидност');
			$oResponse->setField('validTo',			'до',			'сортирай по Валидност');
			$oResponse->setField('start',			'начало',		'сортирай по начало');
			$oResponse->setField('end',				'край',			'сортирай по край');
			$oResponse->setField('automatic',		'авт.',			'сортирай по автоматични', 'images/confirm.gif');
 			$oResponse->setField('stake',			'ставка',		'сортирай по ставка');
			$oResponse->setField('description',		'описание',		'сортирай по описание');
			$oResponse->setField('updated_user',	'...',			'Сортиране по последно редактирал', 'images/dots.gif' );
		}
		
		public function getShiftLeaveForObject( $nIDObject )
		{
			$aNullArray = array();
			$aNullArray['id'] = 0;
			$aNullArray['shift_from'] = "00:00:00";
			$aNullArray['shift_to'] = "00:00:00";
			$aNullArray['shift_from_sec'] = 0;
			$aNullArray['shift_to_sec'] = 0;
			
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
			{
				return $aNullArray;
			}
			
			$sQuery = "
				SELECT
					os.id AS id,
					os.shiftFrom AS shift_from,
					os.shiftTo AS shift_to,
					TIME_TO_SEC( os.shiftFrom ) AS shift_from_sec,
					TIME_TO_SEC( os.shiftTo ) AS shift_to_sec
				FROM
					object_shifts os
				WHERE
					os.to_arc = 0
					AND os.id_obj = {$nIDObject}
					AND os.mode = 'leave'
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )return $aData;
			else return $aNullArray;
		}
	}
	
?>