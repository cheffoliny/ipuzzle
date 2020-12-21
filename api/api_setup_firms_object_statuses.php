<?php

	class ApiSetupFirmsObjectStatuses
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBFirms 				= new DBFirms();
			$oDBFirmsObjectStatuses = new DBFirmsObjectStatuses();
			$oDBStatuses 			= new DBStatuses();
			
			$aFirms = $oDBFirms->getFirms2();
			
			$oResponse->setFormElement( "form1", "nIDFirm" );
			foreach( $aFirms as $aFirm )
			{
				$oResponse->setFormElementChild( "form1", "nIDFirm", array( "value" => $aFirm['id'] ), $aFirm['name'] );
			}
			
			if( !isset( $aParams['nIDFirm'] ) )
			{
				$aParams['nIDFirm'] = isset( $aFirms[0] ) ? $aFirms[0]['id'] : 0;
			}
			else
			{
				$oResponse->setFormElementAttribute( "form1", "nIDFirm", "value", $aParams['nIDFirm'] );
			}
			
			$aStatuses = $oDBStatuses->getStatusesAlphabetic();
			$aFirmStatuses = $oDBFirmsObjectStatuses->getStatusesByIDFirm( $aParams['nIDFirm'] );
			
			$oResponse->setFormElement( "form1", "statuses_current", array(), "" );
			$oResponse->setFormElement( "form1", "statuses_all", array(), "" );
			
			foreach( $aStatuses as $aStatus )
			{
				if( in_array( $aStatus['id'], $aFirmStatuses ) )
				{
					$oResponse->setFormElementChild( "form1", "statuses_current", array( "value" => $aStatus['id'] ), $aStatus['name'] );
				}
				else
				{
					$oResponse->setFormElementChild( "form1", "statuses_all", array( "value" => $aStatus['id'] ), $aStatus['name'] );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBFirmsObjectStatuses = new DBFirmsObjectStatuses();
			
			$oDBFirmsObjectStatuses->delByIDFirm( $aParams['nIDFirm'] );
			
			if( isset( $aParams['statuses_current'] ) )
			{
				foreach( $aParams['statuses_current'] as $nKey => $nIDStatus )
				{
					$aData = array();
					$aData['id'] = 0;
					$aData['id_firm'] = $aParams['nIDFirm'];
					$aData['id_status'] = $nIDStatus;
					
					$oDBFirmsObjectStatuses->update( $aData );
				}
			}
			
			$oResponse->printResponse();
		}
	}

?>