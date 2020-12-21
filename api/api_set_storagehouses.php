<?php
	
	class ApiSetStoragehouses
	{
		public function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID');
			
			$oStoragehouses = new DBStoragehouses();
			$oDBCities = new DBCities();
			$oDBFirms = new DBFirms();
			
			$aFirms = $oDBFirms->getFirms4();
			$aCities = $oDBCities->getCities();
			
			
			if(!empty($nID))
			{
				$aStoragehouses = $oStoragehouses->getRecord($nID);
				$oResponse->setFormElement('form1','sName',array(),$aStoragehouses['name']);
				
				$oDBOffices = new DBOffices();
				
				$nFirm = $oDBOffices->getFirmByIDOffice($aStoragehouses['id_office']);
						
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
					if($key == $aStoragehouses['id_office'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),$ch), $value);
				}
				
				$oDBPersonnel = new DBPersonnel();
				$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice($aStoragehouses['id_office']);
				
				$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aResponsible as $key => $value)
				{
					if($key == $aStoragehouses['mol_id_person'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>$key),$ch), $value);
				}
				
				$oResponse->setFormElementAttribute( 'form1', 'sType', 'value', $aStoragehouses['type'] );
				
				$oResponse->setFormElement('form1', 'nIDCity', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDCity', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aCities as $key => $value)
				{
					if($key == $aStoragehouses['address_city'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDCity', array_merge(array("value"=>$key),$ch), $value);
				}	
					
				$oDBCityAreas = new DBCityAreas();
				$aCityAreas = $oDBCityAreas->getNamesByIDCity($aStoragehouses['address_city']);
				
				$oResponse->setFormElement('form1', 'nIDArea', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aCityAreas as $key => $value)
				{
					if($key == $aStoragehouses['address_area'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>$key),$ch), $value);
				}	
				
				$oDBCityStreets = new DBCityStreets();
				$sStreet = $oDBCityStreets->getNameByID($aStoragehouses['address_street']);
				$oResponse->setFormElement('form1', 'sStreet', array(), $sStreet);
				$oResponse->setFormElement('form1', 'nIDStreet', array(), $aStoragehouses['address_street']);
				
				$oResponse->setFormElement('form1', 'sNumber', array(), $aStoragehouses['address_num']);
				$oResponse->setFormElement('form1', 'sOther', array(), $aStoragehouses['address_other']);	
			}
			else 
			{		
				$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
			
				foreach($aFirms as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
				}
					
				$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
				
				$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "Първо изберете регион");
				

				
				$oResponse->setFormElement('form1', 'nIDCity', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDCity', array_merge(array("value"=>'0')), "--Изберете--");
			
				foreach($aCities as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDCity', array_merge(array("value"=>$key)), $value);
				}
				
				$oResponse->setFormElement('form1', 'nIDArea', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "Първо изберете град");			
				
			}
			
			$oResponse->printResponse();
		}
		
		public function loadOffices(DBResponse $oResponse)
		{
			$nFirm 	=	Params::get('nIDFirm',0);

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');

			if(!empty($nFirm))
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
		
		public function loadCityAreas(DBResponse $oResponse)
		{
			$nIDCity 	=	Params::get('nIDCity');

			$oResponse->setFormElement('form1', 'nIDArea', array(), '');
			
			if(!empty($nIDCity))
			{
				$oDBCityAreas = new DBCityAreas();
				$aCityAreas = $oDBCityAreas->getNamesByIDCity($nIDCity);
					

				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aCityAreas as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>$key)), $value);
				}
			}
			else 
			{
				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "Първо изберете град");
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
			if(empty($nIDCity))
			{
					throw new Exception("Въведете град!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			/*
			if(empty($nIDArea))
			{
					throw new Exception("Изберете квартал!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($sStreet))
			{
					throw new Exception("Въведете улица!", DBAPI_ERR_INVALID_PARAM);
			}		
			*/
			if(!empty($sStreet))
			{
				$oDBCityStreets = new DBCityStreets();
				
				$nIDStreet = $oDBCityStreets->getIDByName($sStreet);
				
				if(empty($nIDStreet))
				{
					throw new Exception("Няма такава улица в базата данни!", DBAPI_ERR_INVALID_PARAM);
				}
			}
			$oDBStoragehouses = new DBStoragehouses();
			
			$aData = array();
			
			$aData['id']			= $nID;
			$aData['name']			= $sName;
			$aData['id_office']   	= $nIDOffice;
			$aData['mol_id_person'] = $nIDPerson;
			$aData['type'] 			= $sType;
			$aData['address_city']	= $nIDCity;
			$aData['address_area']	= $nIDArea;
			$aData['address_street']= $nIDStreet;
			$aData['address_num']	= $sNumber;
			$aData['address_other'] = $sOther;

			
			$oDBStoragehouses->update($aData); 
		}
	}
?>