<?php
	class DBObjectScheduleHours extends DBBase2 
	{
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			parent::__construct( $db_sod, 'object_duty' );
		}
		
		public function getReport( $nIDObject, DBResponse $oResponse ) {
			global $db_sod, $db_name_personnel;
			
			$right_edit = false;
			
			if ( !empty( $_SESSION['userdata']['access_right_levels'] ) ) {
				if( in_array( 'object_personnel_schedule_settings', $_SESSION['userdata']['access_right_levels'] ) ) {
					$right_edit = true;
				}
			}
			
			$nIDObject = is_numeric($nIDObject) && !empty($nIDObject) ? $nIDObject : -1;
			$period = date("m") > 6 ? date("Y")."-07-01" : date("Y")."-01-01";
			$periodMonth = date("m");
			$periodYear = date("Y");
			//APILog::Log(0, $period);
			$oSettings = new DBObjectScheduleSettings();
			$aSettings = $oSettings->getActiveSettings();
			
			$factor = isset($aSettings['factor']) ? $aSettings['factor'] : 0;
			$limit = isset($aSettings['max_hours']) ? $aSettings['max_hours'] : 0;

			//APILog::Log(0, $aSettings);
			$sQueryReal = "
				SELECT SQL_CALC_FOUND_ROWS
					od.id,
					od.id_person,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as person,
					UNIX_TIMESTAMP(od.startRealShift) as start,
					UNIX_TIMESTAMP(od.endRealShift) as end
				FROM object_duty od
				LEFT JOIN {$db_name_personnel}.personnel as p ON od.id_person = p.id
				WHERE 1
					AND od.id_shift > 0
					AND UNIX_TIMESTAMP(od.startRealShift) > 0
					AND UNIX_TIMESTAMP(od.endRealShift) > 0
					AND od.id_obj = {$nIDObject}
					AND DATE(od.startRealShift) >= '{$period}'
			";

			$aDataReal = $db_sod->getAssoc( $sQueryReal );
			
			$sQueryPlan = "
				SELECT SQL_CALC_FOUND_ROWS
					od.id,
					od.id_person,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as person,
					UNIX_TIMESTAMP(od.startShift) as start,
					UNIX_TIMESTAMP(od.endShift) as end
				FROM object_duty od
				LEFT JOIN {$db_name_personnel}.personnel as p ON od.id_person = p.id
				WHERE 1
					AND od.id_shift > 0
					AND od.id_obj = {$nIDObject}
					AND YEAR(od.startShift) = '{$periodYear}'
					AND MONTH(od.startShift) = '{$periodMonth}'
			";

			$aDataPlan = $db_sod->getAssoc( $sQueryPlan );	
			
			$sQueryMonthReal = "
				SELECT SQL_CALC_FOUND_ROWS
					od.id,
					od.id_person,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as person,
					UNIX_TIMESTAMP(od.startRealShift) as start,
					UNIX_TIMESTAMP(od.endRealShift) as end
				FROM object_duty od
				LEFT JOIN {$db_name_personnel}.personnel as p ON od.id_person = p.id
				WHERE 1
					AND od.id_shift > 0
					AND od.id_obj = {$nIDObject}
					AND YEAR(od.startRealShift) = '{$periodYear}'
					AND MONTH(od.startRealShift) = '{$periodMonth}'
					AND UNIX_TIMESTAMP(od.startRealShift) > 0
					AND UNIX_TIMESTAMP(od.endRealShift) > 0					
			";

			$aDataMonthReal = $db_sod->getAssoc( $sQueryMonthReal );	
			
			$aDuty 		= array();
			$aDutyPlan 	= array();
			$aDutyReal 	= array();
			$aDutyMonthReal	= array();
			
			$aDutyReal = $this->calculateDuty( $aDataReal );
			$aDutyPlan = $this->calculateDuty( $aDataPlan );
			$aDutyMonthReal = $this->calculateDuty( $aDataMonthReal );
			
			foreach ( $aDutyReal as $key => $val ) {
				if ( isset($aDutyPlan[$key]) ) {
					$val['dutyPlan'] 	= $aDutyPlan[$key]['duty'];
					$val['nightPlan'] 	= $aDutyPlan[$key]['night'];
					$val['dayPlan'] 	= $aDutyPlan[$key]['day'];
				} else {
					$val['dutyPlan'] 	= 0;
					$val['nightPlan'] 	= 0;
					$val['dayPlan'] 	= 0;
				}
				
				if ( isset($aDutyMonthReal[$key]) ) {
					$val['dutyReal'] 	= $aDutyMonthReal[$key]['duty'];
					$val['nightReal'] 	= $aDutyMonthReal[$key]['night'];
					$val['dayReal'] 	= $aDutyMonthReal[$key]['day'];
				} else {
					$val['dutyReal'] 	= 0;
					$val['nightReal'] 	= 0;
					$val['dayReal'] 	= 0;
				}				
				
				$aDuty[$key] = $val;
			}
			
			
		
			//APILog::Log(0, $aDuty);
			
			$nRowTotal = count($aDuty);
			$oParams = Params::getInstance();
			
			$nPage = $oParams->get("current_page", 1);
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			//$newArray = array_splice($aData, $nRowOffset, $nRowCount);
			
			$oResponse->setData( $aDuty );	
			
			$bLimited = !empty( $nPage );
			
			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}	
						
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['day_night'] 		= $this->SecToHours( $val['day'] + ( $val['night'] * $factor ) );
				$val['day_night_plan'] 	= $this->SecToHours( $val['dayPlan'] + ( $val['nightPlan'] * $factor ) );
				$val['plan_month'] 	= $this->SecToHours( $val['dayPlan'] + ( $val['nightPlan'] ) );
				$val['real_month'] 	= $this->SecToHours( $val['dayReal'] + ( $val['nightReal'] ) );
				$val['balance']		= $limit - $val['day_night_plan'];
				
				//$val['day'] 		= $this->SecToHours( $val['day'] );
				//$val['night'] 		= $this->SecToHours( $val['night'] );
				//$val['dayPlan'] 	= $this->SecToHours( $val['dayPlan'] );
				//$val['nightPlan'] 	= $this->SecToHours( $val['nightPlan'] );				
				
				//$oResponse->setDataAttributes( $key, 'day', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				//$oResponse->setDataAttributes( $key, 'night', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				//$oResponse->setDataAttributes( $key, 'dayPlan', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				//$oResponse->setDataAttributes( $key, 'nightPlan', 	array( 'style' => 'text-align: right; width: 150px;' ) );				
				$oResponse->setDataAttributes( $key, 'day_night', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'day_night_plan', 	array( 'style' => 'text-align: right; width: 130px;' ) );
				$oResponse->setDataAttributes( $key, 'plan_month', 	array( 'style' => 'text-align: right; width: 130px;' ) );
				$oResponse->setDataAttributes( $key, 'real_month', 	array( 'style' => 'text-align: right; width: 130px;' ) );
				$oResponse->setDataAttributes( $key, 'balance', 	array( 'style' => 'text-align: right; width: 130px;' ) );
			}
			
			$oResponse->setField( 'person',			'Служител',		'сортирай по служител' );
			$oResponse->setField( 'day_night',		'Изр. ПР за полугодието',	'сортирай по общо изработени ПРИРАВНЕНИ часове за ПОЛУГОДИЕТО' );
			$oResponse->setField( 'day_night_plan',	'Пл. ПР за месеца',			'сортирай по общо планирани ПРИРАВНЕНИ часове за МЕСЕЦА' );
			$oResponse->setField( 'plan_month',		'Общо планирани',	'сортирай по общо планирани за месеца' );
			$oResponse->setField( 'real_month',		'Общо изработени',	'сортирай по общо изработени за месеца' );
			$oResponse->setField( 'balance',		'Разл. от оптимал.',	'сортирай по разлика от оптимална планировка' );
			
			//$oResponse->setField( 'dayPlan',		'Пл.дневни',	'сортирай по планирани дневни' );
			//$oResponse->setField( 'nightPlan',		'Пл. нощни',	'сортирай по планирани нощни' );
			//$oResponse->setField( 'day',			'Изр. дневни',	'сортирай по изработени дневни' );
			//$oResponse->setField( 'night',			'Изр. нощни',	'сортирай по изработени нощни' );
			

		}
		
		function calculateDuty( $aData ) {

			$oSettings = new DBObjectScheduleSettings();
			$aSettings = $oSettings->getActiveSettings();
			
			$factor = isset($aSettings['factor']) ? $aSettings['factor'] : 0;
			$aDuty = array();
			
			foreach ( $aData as $val ) {
				// 1. Смяната е в едно денонощие
				if ( date("d", $val['start']) == date("d", $val['end']) ) {

					$duty 			= $val['end'] - $val['start'];
					$day_start 		= explode(":", $aSettings['night_to']);
					$night_start 	= explode(":", $aSettings['night_from']);	
					// Начало на НОЩЕН период в UNIX Timestamp
					$nstart 		= mktime( $night_start[0], $night_start[1], 0, date("m", $val['end']), date("d", $val['end']), date("Y", $val['end']) );	
					// Начало на ДНЕВЕН период в UNIX Timestamp
					$dstart 		= mktime( $day_start[0], $day_start[1], 0, date("m", $val['start']), date("d", $val['start']), date("Y", $val['start']) );	
					$night 			= 0;
					$day 			= 0;
					
					// 1.1. Смяната е започнала през деня
					if ( (date("H:i", $val['start']) >= $aSettings['night_to']) && (date("H:i", $val['start']) <= $aSettings['night_from']) ) {
						
						// Края на смяната застъпва нощтна смяна
						if ( $nstart <  $val['end'] ) {
							
							// Взимаме остатъкa от нощното застъпване
							$night = $val['end'] - $nstart;							
						}
						
						$day = $duty - $night;
						
					} else {		// 1.2. Смяната е започнала през нощта

						// Взимаме първия остатък от нощното застъпване
						$night += $dstart - $val['start'];
						
						// Края на смяната застъпва нощтна смяна
						if ( $nstart < $val['end'] ) {
							
							// Взимаме втория остатък от нощното застъпване
							$night += $val['end'] - $nstart;							
						}
						
						$day = $duty - $night;
					}

				} else {
					// 2. Смяната е в две денонощия
					// Разглеждаме смяната като две отделни смени от едно денонощие 
					
					$two_day_night 	= $aSettings['night_from'] > $aSettings['night_to'] ? TRUE : FALSE;
					$night_start 	= explode(":", $aSettings['night_from']);
					$day_start 		= explode(":", $aSettings['night_to']);
					$day1_start 	= $val['start'];
					$day1_end 		= mktime( 0, 0, 0, date("m", $val['start']), date("d", $val['start'])+1, date("Y", $val['start']) );
					$day2_start 	= $day1_end;
					$day2_end 		= $val['end'];
					$duty 			= $val['end'] - $val['start'];
					$night1 		= 0;
					$night2 		= 0;						
					
					// Начало на НОЩЕН период за денонощие 1 в UNIX Timestamp
					$n1start	= mktime( $night_start[0], $night_start[1], 0, date("m", $val['start']), date("d", $val['start']), date("Y", $val['start']) );	
					// Начало на ДНЕВЕН период за денонощие 2 в UNIX Timestamp
					$d2start 	= mktime( $day_start[0], $day_start[1], 0, date("m", $val['end']), date("d", $val['end']), date("Y", $val['end']) );	
					$d1start 	= mktime( $day_start[0], $day_start[1], 0, date("m", $val['start']), date("d", $val['start']), date("Y", $val['start']) );
					$n2start	= mktime( $night_start[0], $night_start[1], 0, date("m", $val['end']), date("d", $val['end']), date("Y", $val['end']) );
											
					if ( $two_day_night ) {
						// Нощния период преминава от едно денонощие в друго
						
						// Часове нощен труд за денонощие 1 и 2
						$night1 += $day1_start > $n1start ? $day1_end - $day1_start : $day1_end - $n1start;
						$night2 += $day2_end > $d2start ? $d2start - $day1_end : $day2_end - $day1_end;
						
						// Има ли нощно застъпване в началото на първо денонощие?
						if ( $day1_start < $d1start ) {
							// Има застъпване
							$night1 += $d1start - $day1_start;
						}						
					} else {
//						// Има ли нощно застъпване в началото на първо денонощие?
//						if ( $day1_start < $d1start ) {
//							// Има застъпване
//							$night1 += $d1start - $day1_start;
//						}
						
						// Има ли нощно застъпване в началото на второ денонощие?
						if ( $day2_end >= $d2start ) {
							// Има застъпване
							$night2 += $d2start - $n2start;
						} elseif ( ($day2_end < $d2start) AND ($day2_end >$n2start) ) {
							$night2 += $day2_end - $n2start;
						}
					}
					
					$night = $night1 + $night2;
					$day = $duty - $night;					
								
				}
				
				if( isset( $val['rest'] ) )
				{
					$val['rest'] = ( int ) $val['rest'];
					
					$night -= $val['rest'];
					if( $night < 0 )
					{
						$day += $night;
						if( $day < 0 ) $day = 0;
						$night = 0;
					}
					$duty -= $val['rest'];
					if( $duty <= 0 )
					{
						$duty = 0;
						$duty = $day = 28800;
					}
				}
				
				if ( isset($aDuty[$val['id_person']]) ) {
					
					$aDuty[$val['id_person']]['duty'] 	+= $duty;
					$aDuty[$val['id_person']]['night'] 	+= $night;
					$aDuty[$val['id_person']]['day'] 	+= $day;
				} else {
					$aDuty[$val['id_person']]['id'] 	= $val['id_person'];
					$aDuty[$val['id_person']]['duty'] 	= $duty;
					$aDuty[$val['id_person']]['night'] 	= $night;
					$aDuty[$val['id_person']]['day'] 	= $day;		
					$aDuty[$val['id_person']]['person'] = $val['person'];					
				}
				
			}
			
			return $aDuty;
		}
		
		public function getActiveSettings() {
			$sQuery = "
				SELECT
					ds.id,
					ds.factor,
					ds.max_hours,
					DATE_FORMAT(ds.night_from, '%H:%i') as night_from,
					DATE_FORMAT(ds.night_to, '%H:%i') as night_to
				FROM object_duty_settings ds
				WHERE 1
					AND ds.to_arc = 0
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function SecToHours( $sec ) {
			$min = $sec / 60; // време в мин
			
			if ( $min < 60 ) {
				$tmp_min = strlen($min) == 1 ? "0".$min : $min;
				
				return "00:".$tmp_min;
			} 
			
			$hours = floor( $min / 60 );
			$mins = $min - ( $hours * 60 );

			$hours = strlen($hours) == 1 ? "0".$hours : $hours;
			$mins = strlen($mins) == 1 ? "0".$mins : $mins;
			
			return $hours.":".$mins;
		}
	}
	
?>