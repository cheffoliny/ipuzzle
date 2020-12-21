<?php

	class ApiSetScheduleMonthNorm
	{
		public function load( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			$aNorm = array();
			$oNorms = new DBScheduleMonthNorms();
			
			//Get Years
			$aYears = array();
			$bSetYearToCurrent = false;
			$nSelectedYear = 9999;
			$aYears = $oNorms->getYears();
			
			$oResponse->setFormElement( "form1", "nYear", array(), "" );
			foreach( $aYears as $aYear )
			{
				if( !$bSetYearToCurrent )
				{
					if( ( int ) $aYear['year'] < $nSelectedYear )
					{
						$nSelectedYear = ( int ) $aYear['year'];
					}
				}
				
				$oResponse->setFormElementChild( "form1", "nYear", array( "value" => $aYear['year'] ), $aYear['year'] );
				
				if( $aYear['year'] == date( "Y" ) )
				{
					$bSetYearToCurrent = true;
					$nSelectedYear = $aYear['year'];
				}
			}
			
			if( $bSetYearToCurrent )
			{
				$oResponse->setFormElementAttribute( "form1", "nYear", "value", $nSelectedYear );
			}
			//End Get Years
			
			if( !empty( $nID ) )
			{
				$nSearch = ( int ) $nSelectedYear . ( strlen( $nID ) < 2 ? 0 . $nID : $nID );
				$aNorm = $oNorms->getActiveNormsByMonth( $nSearch );
				
				if( !empty( $aNorm ) )
				{
					$oResponse->setFormElement( 'form1', 'sMonth', 		array( 'value' => $aNorm['month'] ) );
					$oResponse->setFormElement( 'form1', 'nNormShifts', array( 'value' => $aNorm['shifts'] ) );
					$oResponse->setFormElement( 'form1', 'nNormHours', 	array( 'value' => $aNorm['hours'] ) );
					$oResponse->setFormElement( 'form1', 'nIDToUpdate',	array( 'value' => $aNorm['id'] ) );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nIDToUpdate',	array( 'value' => "0" ) );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function changeYear( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			$nSelectedYear = Params::get( "nYear", 0 );
			
			$aNorm = array();
			$oNorms = new DBScheduleMonthNorms();
			
			if( !empty( $nID ) )
			{
				$nSearch = ( int ) $nSelectedYear . ( strlen( $nID ) < 2 ? 0 . $nID : $nID );
				$aNorm = $oNorms->getActiveNormsByMonth( $nSearch );
				
				if( !empty( $aNorm ) )
				{
					$oResponse->setFormElement( 'form1', 'sMonth', 		array( 'value' => $aNorm['month'] ) );
					$oResponse->setFormElement( 'form1', 'nNormShifts', array( 'value' => $aNorm['shifts'] ) );
					$oResponse->setFormElement( 'form1', 'nNormHours', 	array( 'value' => $aNorm['hours'] ) );
					$oResponse->setFormElement( 'form1', 'nIDToUpdate',	array( 'value' => $aNorm['id'] ) );
				}
				else
				{
					$oResponse->setFormElement( 'form1', 'nIDToUpdate',	array( 'value' => "0" ) );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nID			= Params::get( 'nIDToUpdate', 0 );
			$nNormShifts	= Params::get( "nNormShifts", 0 );
			$nNormHours		= Params::get( "nNormHours", 0 );
			
			if( empty( $nID ) )
			{
				throw new Exception( "Записа вече не съществува!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( empty( $nNormShifts ) )
			{
				throw new Exception( "Въведете максимален брой \nсмени за месеца!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if( empty( $nNormHours) )
			{
				throw new Exception( "Въведете максимален брой \nприравнени часове за месеца!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$aData = array();
			$aData['id'] 			= $nID;
			$aData['norm_shifts'] 	= $nNormShifts;
			$aData['norm_hours'] 	= $nNormHours;
			
			$oNorms = new DBScheduleMonthNorms();
			$oNorms->update( $aData );
		}
	}
	
?>