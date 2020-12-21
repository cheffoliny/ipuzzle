<?php
	class ApiSetSetupPatrulParking {
		public function load( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
			
			$aPatrulParking = array();
			$oPatrulParking = new DBPatrulParking();
			
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if( !empty( $nID ) )
			{

				$aPatrulParking = $oPatrulParking->getRecord( $nID );
				
				$oDBOffices = new DBOffices();
				
				$nFirm = $oDBOffices->getFirmByIDOffice($aPatrulParking['id_office']);
				
				$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aFirms as $key => $value)
				{
					if($key == $nFirm)
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),$ch), $value);
				}
				
				
				$aOffices = $oDBOffices->getPatrulOfficesByIDFirm($nFirm);
				
				

				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aOffices as $key => $value)
				{
					if($key == $aPatrulParking['id_office'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),$ch), $value);
				}
				
				
				
				$oResponse->setFormElement('form1', 'sName', array('value' => $aPatrulParking['name']));	
				$oResponse->setFormElement('form1', 'sDescription', array('value' => $aPatrulParking['description']));					
			}
			else
			{	
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aFirms as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
				}		
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо избери фирма");
						
			}	
			$oResponse->printResponse();
		}
		public function loadOffices(DBResponse $oResponse)
		{
			$nFirm 	=	Params::get('nIDFirm');
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');

			if(!empty($nFirm))
			{		
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getPatrulOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aOffices as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			}
			else 
			{
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}	
		public function save( DBResponse $oResponse )
		{
			$nID		= Params::get('nID', 0);
			$nIDFirm	= Params::get('nIDFirm', 0);
			$nIDOffice	= Params::get('nIDOffice', 0);
			$sName		= Params::get("sName");
			$sDescription	= Params::get("sDescription");
			
			if ( empty($sName) ) {
				throw new Exception("Въведете наименование на стоянка!", DBAPI_ERR_INVALID_PARAM);
			}
			if ( empty($nIDFirm) ) {
				throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}
			if ( empty($nIDOffice) ) {
				throw new Exception("Изберете регион!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['id_office'] = $nIDOffice;
			$aData['name'] = $sName;
			$aData['description'] = $sDescription;
			
			$oPatrulParking = new DBPatrulParking();
			$oPatrulParking->update( $aData );
		}
			
	}
	
?>