<?php
	class DBHolidays
		extends DBBase2 
	{
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, 'holidays');
		}
		
		public function isHoliday($nDay, $nMonth)
		{
			if( empty( $nDay ) || !is_numeric( $nDay ) || $nDay < 1 || $nDay > 31 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT *
				FROM holidays
				WHERE
					type = 'holiday'
					AND day = {$nDay}
					AND month = {$nMonth}
				";
			
			$aData = $this->selectOnce( $sQuery );
			
			return !empty( $aData );
		}
		
		public function isWorkday($nDay, $nMonth, $nYear)
		{
			if( empty( $nDay ) || !is_numeric( $nDay ) || $nDay < 1 || $nDay > 31 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $nYear ) || !is_numeric( $nYear ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT *
				FROM holidays
				WHERE
					type = 'workday'
					AND day = {$nDay}
					AND month = {$nMonth}
					AND year = {$nYear}
				";
			
			$aData = $this->selectOnce( $sQuery );
			
			return !empty( $aData );
		}
		
		public function isRestday($nDay, $nMonth, $nYear)
		{
			if( empty( $nDay ) || !is_numeric( $nDay ) || $nDay < 1 || $nDay > 31 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $nMonth ) || !is_numeric( $nMonth ) || $nMonth < 1 || $nMonth > 12 )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			if( empty( $nYear ) || !is_numeric( $nYear ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT *
				FROM holidays
				WHERE
					type = 'restday'
					AND day = {$nDay}
					AND month = {$nMonth}
					AND year = {$nYear}
				";
			
			$aData = $this->selectOnce( $sQuery );
			
			return !empty( $aData );
		}
		
		function getCalendarInfo( $aParams, $bForPDF = false )
		{
			global $db_name_personnel;
			
			$nYear = isset( $aParams['nYear'] ) ? $aParams['nYear'] : date( "Y" );
			
			$sQuery = "
				SELECT
					hol.id 		AS id,
					hol.day 	AS day,
					hol.month 	AS month,
					hol.year 	AS year,
					hol.type 	AS type
			";
			
			if( !$bForPDF )
			{
				$sQuery .= ",
					IF
					(
						hol.updated_user != 0,
						CONCAT(
							CONCAT_WS(
								' ',
								per.fname,
								per.mname,
								per.lname
							),
							' [',
							DATE_FORMAT( hol.updated_time, '%d.%m.%Y %H:%i:%s' ),
							']'
						),
						''
					) AS updated_time
				";
			}
			
			$sQuery .= "
				FROM
					holidays hol
				LEFT JOIN
					{$db_name_personnel}.personnel per ON per.id = hol.updated_user
				WHERE 1
			";
			
			if( !empty( $nYear ) )
			{
				$sQuery .= "
					AND
						( hol.year = {$nYear} OR hol.year = 0 )
				";
			}
			
			$aInfo = $this->selectAssoc( $sQuery );
			
			$aElements = array();
			foreach( $aInfo as $nID => $aElement )
			{
				$aElements[] = implode( ",", $aElement );
			}
			
			return implode( "|", $aElements );
		}
		
		function getDayID( $nDay, $nMonth, $nYear )
		{
			$sQuery = "
				SELECT
					id
				FROM
					holidays
				WHERE 1
			";
			
			if( !empty( $nDay ) )$sQuery .= " AND day = '{$nDay}'";
			if( !empty( $nMonth ) )$sQuery .= " AND month = '{$nMonth}'";
			if( !empty( $nYear ) )$sQuery .= " AND ( year = '{$nYear}' OR year = 0 )";
			
			$nID = $this->selectOne( $sQuery );
			
			return empty( $nID ) ? 0 : $nID;
		}
		
		/**
		 * Функцията връща следващия работен ден, от текущата дата, във формат YYYY-MM-DD.
		 *
		 * @param string $sStartDate ( YYYY-MM-DD )
		 * @return string ( YYYY-MM-DD )
		 */
		function getNextWorkday( $sStartDate = "" )
		{
			$aStartDate = explode( "-", $sStartDate );
			if( !isset( $aStartDate[0] ) || !isset( $aStartDate[1] ) || !isset( $aStartDate[2] ) )
			{
				$nYear 	= ( int ) date( "Y" );
				$nMonth = ( int ) date( "m" );
				$nDay 	= ( int ) date( "d" );
			}
			else
			{
				$nYear 	= ( int ) $aStartDate[0];
				$nMonth = ( int ) $aStartDate[1];
				$nDay 	= ( int ) $aStartDate[2];
			}
			
			//Initial Data
			$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
			$bSkipFirst = !empty( $sStartDate );
			//End Initial Data
			
			$sMyDate = "";
			do
			{
				//Progress Date
				if( empty( $sMyDate ) && !$bSkipFirst )
				{
					$nDay++;
					if( $nDay > $nDaysInMonth )
					{
						$nDay = 1;
						$nMonth++;
						if( $nMonth > 12 ) { $nMonth = 1; $nYear++; }
						
						$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
					}
				}
				
				$bSkipFirst = false;
				//End Progress Date
				
				$nMyWeekday = ( int ) date( "w", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
				
				if( $nMyWeekday == 0 || $nMyWeekday == 6 )
				{
					if( $this->isWorkday( $nDay, $nMonth, $nYear ) )
					{
						$sMyDate = $nYear . "-" . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth ) . "-" . ( strlen( $nDay ) < 2 ? ( "0" . $nDay ) : $nDay );
					}
				}
				else
				{
					if( !$this->isHoliday( $nDay, $nMonth ) && !$this->isRestday( $nDay, $nMonth, $nYear ) )
					{
						$sMyDate = $nYear . "-" . ( strlen( $nMonth ) < 2 ? ( "0" . $nMonth ) : $nMonth ) . "-" . ( strlen( $nDay ) < 2 ? ( "0" . $nDay ) : $nDay );
					}
				}
			}
			while( empty( $sMyDate ) );
			
			return $sMyDate;
		}
		
		/**
		 * Функцията връща броя на работните дни от дата до дата.
		 *
		 * @param string $sStartDate ( YYYY-MM-DD )
		 * @param string $sEndDate ( YYYY-MM-DD )
		 * @return int
		 */
		function getWorkdaysInPeriod( $sStartDate = "", $sEndDate = "" )
		{
			$aStartDate = explode( "-", $sStartDate );
			if( !isset( $aStartDate[0] ) || !isset( $aStartDate[1] ) || !isset( $aStartDate[2] ) )
			{
				$nSYear 	= ( int ) date( "Y" );
				$nSMonth 	= ( int ) date( "m" );
				$nSDay 		= ( int ) date( "d" );
			}
			else
			{
				$nSYear 	= ( int ) $aStartDate[0];
				$nSMonth 	= ( int ) $aStartDate[1];
				$nSDay 		= ( int ) $aStartDate[2];
			}
			
			$aEndDate = explode( "-", $sEndDate );
			if( !isset( $aEndDate[0] ) || !isset( $aEndDate[1] ) || !isset( $aEndDate[2] ) )
			{
				$nEYear 	= ( int ) date( "Y" );
				$nEMonth 	= ( int ) date( "m" );
				$nEDay 		= ( int ) date( "d" );
			}
			else
			{
				$nEYear 	= ( int ) $aEndDate[0];
				$nEMonth 	= ( int ) $aEndDate[1];
				$nEDay 		= ( int ) $aEndDate[2];
			}
			
			//Initial Data
			$nDaysInMonth 	= ( int ) date( "t", mktime( 0, 0, 0, $nSMonth, $nSDay, $nSYear ) );
			$bKeepAlive 	= true;
			$nDays 			= 0;
			//End Initial Data
			
			do
			{
				$nMyWeekday = ( int ) date( "w", mktime( 0, 0, 0, $nSMonth, $nSDay, $nSYear ) );
				
				if( $nMyWeekday == 0 || $nMyWeekday == 6 )
				{
					if( $this->isWorkday( $nSDay, $nSMonth, $nSYear ) )
					{
						$nDays++;
					}
				}
				else
				{
					if( !$this->isHoliday( $nSDay, $nSMonth ) && !$this->isRestday( $nSDay, $nSMonth, $nSYear ) )
					{
						$nDays++;
					}
				}
				
				//Progress Date
				if( ( LPAD( $nSYear, 4, 0 ) . LPAD( $nSMonth, 2, 0 ) . LPAD( $nSDay, 2, 0 ) ) < ( LPAD( $nEYear, 4, 0 ) . LPAD( $nEMonth, 2, 0 ) . LPAD( $nEDay, 2, 0 ) ) )
				{
					$nSDay++;
					if( $nSDay > $nDaysInMonth )
					{
						$nSDay = 1;
						$nSMonth++;
						if( $nSMonth > 12 ) { $nSMonth = 1; $nSYear++; }
						
						$nDaysInMonth = ( int ) date( "t", mktime( 0, 0, 0, $nSMonth, $nSDay, $nSYear ) );
					}
				}
				else
				{
					$bKeepAlive = false;
					break;
				}
				//End Progress Date
			}
			while( $bKeepAlive );
			
			return $nDays;
		}
		
		function getWorkdaysForMonth( $nYear, $nMonth, $nDayFrom = 0, $nDayTo = 0 )
		{
			if( empty( $nYear ) || !is_numeric( $nYear ) || empty( $nMonth ) || !is_numeric( $nMonth ) )
			{
				return 0;
			}
			
			$nMonthDays = ( int ) date( "t", mktime( 0, 0, 0, $nMonth, 1, $nYear ) );
			$nWorkdays = 0;
			
			$nDayFrom 	= ( int ) $nDayFrom;
			$nDayTo 	= ( int ) $nDayTo;
			
			$nStartDay 	= empty( $nDayFrom ) 	? 1 			: $nDayFrom;
			$nEndDay 	= empty( $nDayTo ) 		? $nMonthDays 	: $nDayTo;
			
			if( $nEndDay > $nMonthDays ) $nEndDay = $nMonthDays;
			if( $nStartDay > $nEndDay ){ $nStartDay = 1; $nEndDay = $nMonthDays; }
			
			for( $nDay = $nStartDay; $nDay <= $nEndDay; $nDay++ )
			{
				$nMyWeekday = ( int ) date( "w", mktime( 0, 0, 0, $nMonth, $nDay, $nYear ) );
				
				if( $nMyWeekday == 0 || $nMyWeekday == 6 )
				{
					if( $this->isWorkday( $nDay, $nMonth, $nYear ) )$nWorkdays++;
				}
				else
				{
					if( !$this->isHoliday( $nDay, $nMonth ) && !$this->isRestday( $nDay, $nMonth, $nYear ) )$nWorkdays++;
				}
			}
			
			return $nWorkdays;
		}
	}

?>