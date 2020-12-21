<?php

	class DBPersonMonthLimits extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			parent::__construct( $db_personnel, 'person_month_limits' );
		}
		
		/**
		 * Връща сумата часове за смени по план за полугодието, до посочения месец, за посочения служител.
		 *
		 * @param int $nIDPerson
		 * @param int $nYear
		 * @param int $nMonth
		 * @param bool $bIncluding ( Включително последния месец )
		 * 
		 * @return string ( HH:mm:ss )
		 */
		public function getHalfyearHourCurrentToDate( $nIDPerson, $nYear, $nMonth, $bInclude )
		{
			global $db_name_sod;
			
			//Validation
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return "00:00:00";
			}
			
			if( $nMonth < 1 || $nMonth > 12 || strlen( $nYear ) != 4 )
			{
				return "00:00:00";
			}
			//End Validation
			
			//Initialization
			$sStartDate = ( $nMonth <= 6 )
				? $nYear . "01"
				: $nYear . "07";
			
			$sEndDate = $nYear . ( ( strlen( $nMonth ) < 2 ) ? ( "0" . $nMonth ) : $nMonth );
			//End Initialization
			
			$sQuery = "
				SELECT
					SUM(
						IF
						(
							per_mon_lim.hours != 0,
							TIME_TO_SEC( per_mon_lim.hours ),
							( sch_mon_nor.norm_hours * 60 * 60 )
						)
					) AS seconds
				FROM
 					{$db_name_sod}.schedule_month_norms sch_mon_nor
				LEFT JOIN
					person_month_limits per_mon_lim	ON ( per_mon_lim.month = sch_mon_nor.month AND per_mon_lim.id_person = {$nIDPerson} )
				WHERE
					sch_mon_nor.to_arc = 0
					AND sch_mon_nor.month >= '{$sStartDate}'
			";
			
			if( $bInclude )
			{
				$sQuery .= "
					AND sch_mon_nor.month <= '{$sEndDate}'
				";
			}
			else
			{
				$sQuery .= "
					AND sch_mon_nor.month < '{$sEndDate}'
				";
			}
			
			$sQuery .= "
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['seconds'] ) )
			{
				return SecToTime( $aData['seconds'] );
			}
			else return "00:00:00";
		}
		
		/**
		 * Връща регистрираните часове за смени по план за посочената дата, за посочения служител.
		 *
		 * @param int $nIDPerson
		 * @param int $nYear
		 * @param int $nMonth
		 * 
		 * @return string ( HH:mm:ss )
		 */
		public function getHourCurrentForDate( $nIDPerson, $nYear, $nMonth )
		{
			global $db_name_sod;
			
			//Validation
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return "00:00:00";
			}
			
			if( $nMonth < 1 || $nMonth > 12 || strlen( $nYear ) != 4 )
			{
				return "00:00:00";
			}
			//End Validation
			
			//Initialization
			$sDate = $nYear . ( ( strlen( $nMonth ) < 2 ) ? ( "0" . $nMonth ) : $nMonth );
			//End Initialization
			
			$sQuery = "
				SELECT
					IF
					(
						per_mon_lim.hours != 0,
						TIME_TO_SEC( per_mon_lim.hours ),
						( sch_mon_nor.norm_hours * 60 * 60 )
					) AS seconds
				FROM
 					{$db_name_sod}.schedule_month_norms sch_mon_nor
				LEFT JOIN
					person_month_limits per_mon_lim	ON ( per_mon_lim.month = sch_mon_nor.month AND per_mon_lim.id_person = {$nIDPerson} )
				WHERE
					sch_mon_nor.to_arc = 0
					AND sch_mon_nor.month = '{$sDate}'
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['seconds'] ) )
			{
				return SecToTime( $aData['seconds'] );
			}
			else return "00:00:00";
		}
		
		/**
		 * Увеличава записания брой часове за служител, за посочената дата с nHoursStep брой часове.
		 *
		 * @param int $nIDPerson
		 * @param int $nYearmonth ( YYYYMM )
		 * @param int $nHoursStep
		 */
		public function IncreaseHours( $nIDPerson, $nYearMonth, $nHoursStep )
		{
			if( empty( $nHoursStep ) || !is_numeric( $nHoursStep ) )return DBAPI_ERR_SUCCESS;
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sQuery = "
				SELECT
					*
				FROM
					person_month_limits
				WHERE
					month = '{$nYearMonth}'
					AND id_person = {$nIDPerson}
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( empty( $aData ) )
			{
				$aData['id'] = 0;
				$aData['month'] = $nYearMonth;
				$aData['id_person'] = $nIDPerson;
				$aData['hours'] = ( ( $nHoursStep < 0 ) ? "00" : $nHoursStep ) . ":00:00";
			}
			else
			{
				if( $nHoursStep < 0 )$aData['hours'] = getTimeSum( $aData['hours'], -$nHoursStep, true );
				else $aData['hours'] = getTimeSum( $aData['hours'], $nHoursStep, false );
			}
			
			return $this->update( $aData );
		}
	}
	
?>