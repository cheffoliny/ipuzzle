<?php

	class ApiSetHoldupReason
	{
		public function get( DBResponse $oResponse )
		{
			$nID = Params::get( "nID", 0 );
			
			if( !empty( $nID ) )
			{
				$oHoldupReasons = new DBHoldupReasons();
				$aHReasons = $oHoldupReasons->getRecord( $nID );
				
				$oResponse->setFormElement( 'form1', 'sName', array( 'value' => $aHReasons['name'] ) );
				if( $aHReasons['from_tech_signals'] )
				{
					$oResponse->setFormElementAttribute( 'form1', 'nFromTechSignals', "checked", "checked" );
				}
				else
				{
					$oResponse->setFormElementAttribute( 'form1', 'nFromTechSignals', "checked", "" );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$oHoldupReasons = new DBHoldupReasons();
			
			$nID = 					Params::get( "nID", 0 );
			$sName = 				Params::get( "sName" );
			$nFromTechSignals = 	Params::get( "nFromTechSignals", 0 );
			
			if( empty( $sName ) )
				throw new Exception( "Въведете наименование!", DBAPI_ERR_INVALID_PARAM );
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['name'] = $sName;
			$aData['from_tech_signals'] = $nFromTechSignals;
			
			if( $nFromTechSignals )
			{
				//Clear All Other Tech Signals
				$aHoldupReasons = $oHoldupReasons->getAllReasons();
				if( !empty( $aHoldupReasons ) )
				{
					foreach( $aHoldupReasons as $aHoldupReason )
					{
						if( $aHoldupReason['id'] != $nID && $aHoldupReason['from_tech_signals'] !=0 )
						{
							$aHoldupReason['from_tech_signals'] = 0;
							$oHoldupReasons->update( $aHoldupReason );
						}
					}
				}
			}
			
			$oHoldupReasons->update( $aData );
		}
	}

?>