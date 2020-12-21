<?php
	
	class ApiSetAssetsStoragehouses
	{
		public function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID','0');
			
			$oAssetsStoragehouses = new DBAssetsStoragehouses();
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();            
     		
     		
     		
			if(!empty($nID))
			{
				$aAssetsStoragehouses=$oAssetsStoragehouses->getFirmIDByMOL($nID);
				$oResponse->setFormElement('form1','sName',array(),$aAssetsStoragehouses['name']);

				$oDBOffices = new DBOffices();				
				$nFirm = $oDBOffices->getFirmByIDOffice($aAssetsStoragehouses['id_office']);
				
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
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aOffices as $key => $value)
				{
					if (in_array($key,$_SESSION['userdata']['access_right_regions'])) {
						if($key == $aAssetsStoragehouses['id_office'])
						{
							$ch = array( "selected" => "selected" );
						} 
						else
						{
							$ch = array();
						}
						$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),$ch), $value);
					}
				}
				
				$oDBPersonnel = new DBPersonnel();
				$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice($aAssetsStoragehouses['id_office']);
				
				$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aResponsible as $key => $value)
				{
					if($key == $aAssetsStoragehouses['id_mol'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>$key),$ch), $value);
				}
			}
			else 
			{
				//$aAssetsStoragehouses = $oAssetsStoragehouses->getRecord($nID);			//sega
				//$aAssetsStoragehouses = $oAssetsStoragehouses->get();
				//$oResponse->setFormElement('form1','sName',array(),$aAssetsStoragehouses['id']);
				//$oDBOffices = new DBOffices();
				
				//$nFirm = $oDBOffices->getFirmByIDOffice($aStoragehouses['id_office']);
						
				$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aFirms as $key => $value)
				{
					if($key == $nFirm)
					{
						$ch = array( "selected" => "selected" );
					}
					else
					
						$ch = array();
					
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),$ch), $value);
				
				}
				$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
				
				$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "Първо изберете регион");

			
			}
				
			$oResponse->printResponse();
		}
			
		
		
		public function loadOffices(DBResponse $oResponse)
		{
			$nFirm 	=	Params::get('nIDFirm',0);

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			if($nFirm)
			{
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
				
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
			
			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "Първо изберете регион");
			
			$oResponse->printResponse();
		}
		
		public function loadPersons(DBResponse $oResponse)
		{
			$nOffice 	=	Params::get('nIDOffice');
	
			$oDBPersonnel = new DBPersonnel();
			$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice($nOffice);
				
			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Изберете--");
			foreach($aResponsible as $key => $value)
			{
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>$key)), $value);
			}
			
			$oResponse->printResponse();
		}
		public function save()
		{
			$nID 			=	Params::get('nID');
			$sName			=	Params::get('sName');
			$nIDFirm		=	Params::get('nIDFirm');
			$nIDOffice		=	Params::get('nIDOffice');
			$nIDPerson		=	Params::get('nIDPerson');
			
			$sType			=	Params::get('sType');
			
			$nIDCity		=	Params::get('nIDCity');
			$nIDArea		=	Params::get('nIDArea');
			$sStreet		=	Params::get('sStreet');
			$sNumber		=	Params::get('sNumber');
			$sOther			=	Params::get('sOther');					
			
			if(empty($sName))
			{
					throw new Exception("Въведете име на склад!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($nIDFirm))
			{
					throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}
			if(empty($nIDOffice))
			{
					throw new Exception("Изберете регион!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($nIDPerson))
			{
					throw new Exception("Изберете материално отговорно лице!", DBAPI_ERR_INVALID_PARAM);
			}
			/*	
			if(empty($nIDCity))
			{
					throw new Exception("Въведете град!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			
			if(empty($nIDArea))
			{
					throw new Exception("Изберете квартал!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($sStreet))
			{
					throw new Exception("Въведете улица!", DBAPI_ERR_INVALID_PARAM);
			}		
			
			if(!empty($sStreet))
			{
				$oDBCityStreets = new DBCityStreets();
				
				$nIDStreet = $oDBCityStreets->getIDByName($sStreet);
				
				if(empty($nIDStreet))
				{
					throw new Exception("Няма такава улица в базата данни!", DBAPI_ERR_INVALID_PARAM);
				}
			}*/
			$oDBAssetsStoragehouses = new DBAssetsStoragehouses();
			
			$aData = array();
			
			$aData['id']			= $nID;
			$aData['name']			= $sName;
			//$aData['id_office']   	= $nIDOffice;
			$aData['id_mol'] = $nIDPerson;
			$oDBAssetsStoragehouses->update($aData); 
		}

	}	