<?php

	class ApiSetupScheduleMonthNorms
	{
		public function result( DBResponse $oResponse )
		{
			$oNorms = new DBScheduleMonthNorms();
			$oNorms->getReport( $oResponse );
			
			$oResponse->printResponse( "Месечни Норми", "schedule_month_norms" );
		}
		
		public function saveShiftData( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oNorms = new DBScheduleMonthNorms();
			
			//Read Year and Month
			$nYear = $nMonth = "";
			$sReadWhat = "Y";
			
			for( $i = 0; $i < strlen( $aParams['sField'] ); $i++ )
			{
				if( $sReadWhat == "Y" )
				{
					if( is_numeric( $aParams['sField'][$i] ) )
					{
						$nYear .= $aParams['sField'][$i];
					}
					
					if( $aParams['sField'][$i] == "[" )
					{
						$sReadWhat = "M";
						continue;
					}
				}
				
				if( $sReadWhat == "M" )
				{
					if( is_numeric( $aParams['sField'][$i] ) )
					{
						$nMonth .= $aParams['sField'][$i];
					}
					
					if( $aParams['sField'][$i] == "]" )
					{
						$sReadWhat = "";
						continue;
					}
				}
			}
			
			if( strlen( $nMonth ) < 2 )$nMonth = "0" . $nMonth;
			//End Read Year and Month
			
			$nYearMonth = ( int ) $nYear . $nMonth;
			$aData = $oNorms->getActiveNormsByMonth( $nYearMonth );
			
			$nID = isset( $aData['id'] ) ? $aData['id'] : 0;
			$sMode = isset( $aParams['sMode'] ) ? $aParams['sMode'] : 0;
			$nValue = isset( $aParams['nValue'] ) ? $aParams['nValue'] : 0;
			
			//Validations
			if( empty( $nID ) || empty( $sMode ) )
			{
				throw new Exception( "Грешка при изпълнение на операцията!" );
			}
			
			if( empty( $nValue ) || !is_numeric( $nValue ) )
			{
				throw new Exception( "Невалидна стойност!" );
			}
			//End Validations
			
			//var_dump( "Save ID:" . $nID . " - Value:" . $nValue );
			$aSaveData = array();
			$aSaveData['id'] = $aData['id'];
			if( $sMode == "S" )$aSaveData['norm_shifts'] = $nValue;
			if( $sMode == "H" )$aSaveData['norm_hours'] = $nValue;
			
			$oNorms->update( $aSaveData );
			
			$oResponse->printResponse();
		}
	}

?>