<?php
	
	class ApiAutos
	{
		public function load( DBResponse $oResponse )
		{
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement( 'form1', 'nIDFirm', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => '0' ) ), "--Изберете--" );
			foreach( $aFirms as $key => $value )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDFirm', array_merge( array( "value" => $key ) ), $value );
			}
			
			//$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			//$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			
			$aParams = Params::getAll();
			
			if( !isset( $aParams['nIDFirm'] ) && !isset( $aParams['nIDOffice'] ) )
			{
				$oOffices = new DBOffices();
				$oOffices->retrieveLoggedUserOffice( 'nIDFirm', 'nIDOffice', $oResponse, 1, 1, "--Всички--" );
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
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
	
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
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
		
		public function result( DBResponse $oResponse ) 
		{
			$nFirm = Params::get('nIDFirm','0');

			$oDBAuto = new DBAuto();

			if(empty($nFirm))	{
				throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}	

			$oDBAuto->getReport($oResponse);	
			$oResponse->printResponse("Автомобили","autos");  
		}
		public function delete()
		{
			$nID = Params::get('nID',0);
			
			$oDBAuto = new DBAuto();
			$oDBAuto->delete($nID);		
		}
	}
	
?>