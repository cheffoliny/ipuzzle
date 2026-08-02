<?php
	
	class ApiSetupPatruls
	{
		public function load( DBResponse $oResponse )
		{
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
				
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
			foreach($aFirms as $key => $value)
			{
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
			}		

			$oResponse->printResponse();
		}
		public function result( DBResponse $oResponse )
		{
			$nFirm = Params::get('nIDFirm');
			
			if(empty($nFirm))
			{
					throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$oDBPatruls = new DBPatruls();
			$oDBPatruls->getReport( $oResponse );
			
			$oResponse->printResponse("Позивни","setup_patruls");
		}
		public function delete( DBResponse $oResponse ) {
			$nID = Params::get('nID');
			$oDBPatruls = new DBPatruls();
			$oDBPatruls->detachAllPatrulsFromOffice( $nID );
			$oResponse->printResponse();
		}
	}
	
?>
