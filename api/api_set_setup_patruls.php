<?php
	class ApiSetSetupPatruls {
	
		public function load( DBResponse $oResponse ) 
		{
			$nIDOffice = Params::get("nID", 0);
			
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if( !empty( $nIDOffice ) )
			{
				$oOffices = new DBOffices();	
				$oPatruls = new DBPatruls();
				
				$nIDFirm = $oOffices->getFirmByIDOffice($nIDOffice);
							
				foreach($aFirms as $key => $value)
				{
					if($key == $nIDFirm)
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),$ch), $value);
				}
				
				$aOffices = $oOffices->getPatrulOfficesByIDFirm($nIDFirm);
							
				foreach($aOffices as $key => $value)
				{
					if($key == $nIDOffice)
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),$ch), $value);
				}

				$aPatruls = $oPatruls->getAllPatrulsByIDOffice($nIDOffice);
				$oResponse->setFormElement('form1', 'sPatruls', array('value' => $aPatruls['patruls']) );		
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
		
		public function save( DBResponse $oResponse ) 
		{
			$nIDFirm		= Params::get('nIDFirm', 0);
			$nIDOffice		= Params::get('nIDOffice', 0);
			$sPatruls		= Params::get("sPatruls");

			if( empty( $nIDFirm ) )
				throw new Exception("Изберете фирма!");
			
			if( empty( $nIDOffice ) )
				throw new Exception("Изберете регион!");
			
			$aPatruls = explode(',', $sPatruls);
			
			foreach( $aPatruls as $key => &$value)
			{
				$aPatruls[ $key ] = trim( $value );
				
				if( !is_numeric( $value ) )
					throw new Exception("Въведете коректно патрулите!", DBAPI_ERR_INVALID_PARAM);
			}
			unset($value);
			
			$oPatruls = new DBPatruls();
			$oPatruls->StartTrans();
			
			$aOldPatruls = $oPatruls->getNumByIDOffice($nIDOffice);
			
			try
			{
				foreach ($aOldPatruls as $value) {
					if(!in_array($value,$aPatruls)) {
						$oPatruls->delPatrul($nIDOffice,$value);
					}
				}

				foreach( $aPatruls as $nNum )
				{
					if(!in_array($nNum,$aOldPatruls)) {
						if( !$oPatruls->checkFreePatrul( $nNum ) )
							throw new Exception("Номер на позивна {$nNum} e зает!");
							
						$aPatrul = array();
						$aPatrul['num_patrul'] = $nNum;
						$aPatrul['id_office'] = $nIDOffice;
					
						$oPatruls->update( $aPatrul );
					}
				}

			}
			catch( Exception $e )
			{
				$oPatruls->FailTrans();
				throw new Exception($e->getMessage());
			}
			
			$oPatruls->CompleteTrans();
			$oResponse->printResponse();
		}
		
		public function getPatruls( DBResponse $oResponse )
		{
			$nIDOffice = Params::get("nIDOffice", 0);
			$sPatruls = "";
			
			if( !empty( $nIDOffice ) )
			{
				$oPatruls = new DBPatruls();
				$aPatruls = $oPatruls->getAllPatrulsByIDOffice( $nIDOffice );
				$sPatruls = $aPatruls['patruls'];
			}
			
			$oResponse->setFormElement('form1', 'sPatruls', array('value' => $sPatruls));
			$oResponse->setFormElement('form1', 'nID', array('value' => $nIDOffice ));
			
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
				
				$oResponse->setFormElement('form1', 'sPatruls', array('value'=>''));
			}
			else
			{
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}		
			$oResponse->printResponse();
		}	
	}
	
?>