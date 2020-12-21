<?php
	class DBObjectScheduleSettings extends DBBase2 
	{
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			parent::__construct( $db_sod, 'object_duty_settings' );
		}
		
		public function getReport( DBResponse $oResponse ) {
			global $db_name_personnel;
			
			$right_edit = false;
			
			if ( !empty( $_SESSION['userdata']['access_right_levels'] ) ) {
				if( in_array( 'object_personnel_schedule_settings', $_SESSION['userdata']['access_right_levels'] ) ) {
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ds.id,
					ds.factor,
					DATE_FORMAT(ds.night_from, '%H:%i') as night_from,
					DATE_FORMAT(ds.night_to, '%H:%i') as night_to,
					IF (
						ds.updated_user,
						CONCAT( CONCAT_WS( ' ', up.fname, up.mname, up.lname ), ' [', DATE_FORMAT( ds.updated_time, '%d.%m.%Y %H:%i:%s' ), ']' ),
						'---'
					) AS updated_user
				FROM object_duty_settings ds
				LEFT JOIN {$db_name_personnel}.personnel as up ON ds.updated_user = up.id
				WHERE 1
					AND ds.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				
				$oResponse->setDataAttributes( $key, 'factor', 		array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'max_shifts', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'max_hours', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'night_from', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'night_to', 	array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'updated_user', array( 'style' => 'text-align: center;' ) );
			}
			
			$oResponse->setField( 'factor',			'Коефицент',			'сортирай по коефицент' );
			$oResponse->setField( 'night_from',		'Начален час',			'сортирай по час' );
			$oResponse->setField( 'night_to',		'Краен час',			'сортирай по час' );
			$oResponse->setField( 'updated_user',	'Последно редактирал', 	'сортирай по последно редактирал' );
			
			if ( $right_edit ) {
				$oResponse->setFieldLink( 'factor',		'editScheduleSettings' );
				$oResponse->setFieldLink( 'max_shifts',	'editScheduleSettings' );
				$oResponse->setFieldLink( 'max_hours',	'editScheduleSettings' );
				$oResponse->setFieldLink( 'night_from',	'editScheduleSettings' );
				$oResponse->setFieldLink( 'night_to',	'editScheduleSettings' );
			}
		}
		
		public function getActiveSettings() {
			$sQuery = "
				SELECT
					ds.id,
					ds.factor,
					ds.max_shifts,
					ds.max_hours,
					DATE_FORMAT(ds.night_from, '%H:%i') as night_from,
					DATE_FORMAT(ds.night_to, '%H:%i') as night_to
				FROM object_duty_settings ds
				WHERE 1
					AND ds.to_arc = 0
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		/**
		 * Изчислява часовете смяна според коефициента от настройките по графика.
		 *
		 * @param string $sStartHour ( H:m )
		 * @param string $sEndHour ( H:m )
		 * @param int $nRestSec
		 * @param bool $bRound ( Закръгляне на минути )
		 * 
		 * @return int / string ( Брой часове / H:m )
		 */
		public function calculateShiftHours( $sStartHour, $sEndHour, $nRestSec, $bRound = true )
		{
			$oDBObjectScheduleHours = new DBObjectScheduleHours();
			$oSettings 				= new DBObjectScheduleSettings();
			
			//Params Validation
			$aPS = explode( ":", $sStartHour );		//Param Start
			$aPE = explode( ":", $sEndHour );		//Param End
			
			if( !isset( $aPS[0] ) || !isset( $aPS[1] ) || !isset( $aPE[0] ) || !isset( $aPE[1] ) )
			{
				if( $bRound ) return 0;
				else return "00:00";
			}
			//End Params Validation
			
			//Params Initialize
			$sPSH = ( int ) $aPS[0];
			$sPSM = ( int ) $aPS[1];
			$sPEH = ( int ) $aPE[0];
			$sPEM = ( int ) $aPE[1];
			//End Params Initialize
			
			$bIsShiftSpanned = false;
			
			if( $sPSH > $sPEH )
			{
				$bIsShiftSpanned = true;
			}
			if( $sPSH < $sPEH )
			{
				$bIsShiftSpanned = false;
			}
			if( $sPSH == $sPEH )
			{
				if( $sPSM >= $sPEM )
				{
					$bIsShiftSpanned = true;
				}
				if( $sPSH < $sPEH )
				{
					$bIsShiftSpanned = false;
				}
			}
			
			$sStart = mktime( $sPSH, $sPSM, 0, date( "m" ), date( "d" ), date( "Y" ) );
			if( $bIsShiftSpanned )
			{
				$sEnd = mktime( $sPEH, $sPEM, 0, date( "m", strtotime( "+1 DAYS" ) ), date( "d", strtotime( "+1 DAYS" ) ), date( "Y", strtotime( "+1 DAYS" ) ) );
			}
			else
			{
				$sEnd = mktime( $sPEH, $sPEM, 0, date( "m" ), date( "d" ), date( "Y" ) );
			}
			
			$aData = array();
			
			$aData[0]['start'] = $sStart;
			$aData[0]['end'] = $sEnd;
			$aData[0]['id_person'] = 0;
			$aData[0]['person'] = 0;
			$aData[0]['rest'] = ( int ) $nRestSec;
			
			$aDuty = $oDBObjectScheduleHours->calculateDuty( $aData );
			
			$aSettings = $oSettings->getActiveSettings();
			$factor = isset( $aSettings['factor'] ) ? $aSettings['factor'] : 0;
			
			$sHour = $oDBObjectScheduleHours->SecToHours( $aDuty[0]['day'] + ( $aDuty[0]['night'] * $factor ) );
			
			$aHourValues = explode( ":", $sHour );
			
			if( !empty( $aHourValues ) && isset( $aHourValues[0] ) && isset( $aHourValues[1] ) )
			{
				$aHourValues[0] = ( int ) $aHourValues[0];
				$aHourValues[1] = ( int ) $aHourValues[1];
				
				if( $bRound )
				{
					if( $aHourValues[1] > 30 )$aHourValues[0]++;
					
					return $aHourValues[0];
				}
				else
				{
					//$aHourValues[1] = ( round( $aHourValues[1], 0 ) < 59 ) ? round( $aHourValues[1], 0 ) : 59;
					$aHourValues[1] = ( int ) $aHourValues[1];
					
					return ( ( strlen( $aHourValues[0] < 2 ) ) ? "0" . $aHourValues[0] : $aHourValues[0] ) . ":" . ( ( strlen( $aHourValues[1] < 2 ) ) ? "0" . $aHourValues[1] : $aHourValues[1] );
				}
			}
			else
			{
				if( $bRound ) return 0;
				else return "00:00";
			}
		}
	}
	
?>