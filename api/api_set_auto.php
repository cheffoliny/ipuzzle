<?php
	
	class ApiSetAuto
	{
		public function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID');
			
			if(!empty($nID))
			{

				$oDBAuto = new DBAuto();
				$aDBAuto = $oDBAuto->getRecord($nID);
				
				$oDBAutoMarks = new DBAutoMarks();
				$oDBAutoModels = new DBAutoModels();
				
				
				$aMark = $oDBAutoModels->getMarkByIDModel($aDBAuto['id_model']);
				
				//APILog::Log(0, $sMark);
				
				$aMarks = $oDBAutoMarks->getMarks();
				
				
				$oResponse->setFormElement('form1', 'nIDMark', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDMark', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aMarks as $key => $value)
				{
					if($key == $aMark[0]['id_mark'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDMark', array_merge(array("value"=>$key),$ch), $value);
				}
				
				$aModels = $oDBAutoModels->getModelsByIDMark($aMark[0]['id_mark']);
				
				
				$oResponse->setFormElement('form1', 'nIDModel', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDModel', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aModels as $key => $value)
				{
					if($key == $aDBAuto['id_model'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDModel', array_merge(array("value"=>$key),$ch), $value);
				}
				
				$oDBOffices = new DBOffices();
				
				$nFirm = $oDBOffices->getFirmByIDOffice($aDBAuto['id_office']);
				
				//throw new Exception($nFirm);
				//APILog::Log(0, $nFirm);
				
				$oDBFirms = new DBFirms();
				$aFirms = $oDBFirms->getFirms4();
				
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
					if($key == $aDBAuto['id_office'])
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
				$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice($aDBAuto['id_office']);
				
				$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aResponsible as $key => $value)
				{
					if($key == $aDBAuto['id_person'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>$key),$ch), $value);
				}
				
				
				
				
				$oResponse->setFormElement('form1', 'sRegNum', array(), $aDBAuto['reg_num']);
				$oResponse->setFormElement('form1', 'sColor', array(), $aDBAuto['color']);
				$oResponse->setFormElement('form1', 'sDvigatelNum', array(), $aDBAuto['dvigatel_num']);
				$oResponse->setFormElement('form1', 'sRamaNum', array(), $aDBAuto['rama_num']);
				
						
				$oResponse->setFormElement('form1', 'sSummerCity', array(), $aDBAuto['rate_summer_city']);
				$oResponse->setFormElement('form1', 'sSummerOutcity', array(), $aDBAuto['rate_summer_outcity']);
				$oResponse->setFormElement('form1', 'sWinterCity', array(), $aDBAuto['rate_winter_city']);
				$oResponse->setFormElement('form1', 'sWinterOutcity', array(), $aDBAuto['rate_winter_outcity']);
							
				$DBFunctions = new DBFunctions();
				$aFunctions = $DBFunctions->getFunctions();
				
				$oResponse->setFormElement('form1', 'nIDFunction', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDFunction', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aFunctions as $key => $value)
				{
					if($key == $aDBAuto['id_function'])
					{
						$ch = array( "selected" => "selected" );
					} 
					else
					{
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDFunction', array_merge(array("value"=>$key),$ch), $value);
				}
				
			}
			else 
			{
				$oDBAutoMarks = new DBAutoMarks();
				$aMarks = $oDBAutoMarks->getMarks();
							
				$oResponse->setFormElement('form1', 'nIDMark', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDMark', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aMarks as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDMark', array_merge(array("value"=>$key)), $value);
				}
				
				$oResponse->setFormElement('form1', 'nIDModel', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDModel', array_merge(array("value"=>'0')), "Първо избери марка");	
				
				$oDBOffices = new DBOffices();
				
			
				$oDBFirms = new DBFirms();
				$aFirms = $oDBFirms->getFirms4();
				
				$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aFirms as $key => $value)
				{

					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
				}
				
				$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо избери фирма");
				
				$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "Първо избери регион");				
				
				$DBFunctions = new DBFunctions();
				$aFunctions = $DBFunctions->getFunctions();
				
				$oResponse->setFormElement('form1', 'nIDFunction', array(), '');
				$oResponse->setFormElementChild('form1', 'nIDFunction', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aFunctions as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDFunction', array_merge(array("value"=>$key)), $value);
				}
			
			}
			

						
		$oResponse->printResponse(); 
			
		}
		
		public function loadModels(DBResponse $oResponse)
		{
			$nMark 	=	Params::get('nIDMark');
			$oResponse->setFormElement('form1', 'nIDModel', array(), '');
			
			if(!empty($nMark))
			{		
				$oDBAutoModels = new DBAutoModels();
				$aModels = $oDBAutoModels->getModelsByIDMark($nMark);
					

				$oResponse->setFormElementChild('form1', 'nIDModel', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aModels as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDModel', array_merge(array("value"=>$key)), $value);
				}
			}
			else 
			{
				$oResponse->setFormElementChild('form1', 'nIDModel', array_merge(array("value"=>'0')), "Първо избери марка");
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
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо избери фирма");
			}

			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "Първо избери регион");	
			
			$oResponse->printResponse();
		}
		
		public function loadPersons(DBResponse $oResponse)
		{
			$nOffice 	=	Params::get('nIDOffice');
	
			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			
			if(!empty($nOffice))
			{
			
				$oDBPersonnel = new DBPersonnel();
				$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice($nOffice);
					
				
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aResponsible as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>$key)), $value);
				}
			}
			else 
			{
				$oResponse->setFormElementChild('form1', 'nIDPerson', array_merge(array("value"=>'0')), "Първо избери регион");
			}
			$oResponse->printResponse();
		}
		
		public function save()
		{
			$nID 			=	Params::get('nID');
			$nIDMark		=	Params::get('nIDMark');
			$nIDModel		=	Params::get('nIDModel');
			$nIDFirm		=	Params::get('nIDFirm');
			$nIDOffice		=	Params::get('nIDOffice');
			$nIDPerson		=	Params::get('nIDPerson');
			$sRegNum		=	Params::get('sRegNum');
			$sColor 		=	Params::get('sColor');
			$sDvigatelNum 	=	Params::get('sDvigatelNum');
			$sRamaNum 		=	Params::get('sRamaNum');
			$sSummerCity	=	Params::get('sSummerCity');
			$sSummerOutcity	=	Params::get('sSummerOutcity');
			$sWinterCity 	=	Params::get('sWinterCity');
			$sWinterOutcity	=	Params::get('sWinterOutcity');
			$nIDFunction	=	Params::get('nIDFunction');
							
			
			
			if(empty($nIDMark))
			{
					throw new Exception("Изберете марка!", DBAPI_ERR_INVALID_PARAM);
			}
			if(empty($nIDModel))
			{
					throw new Exception("Изберете модел!", DBAPI_ERR_INVALID_PARAM);
			}
			if(empty($nIDFirm))
			{
					throw new Exception("Изберете фирма!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($nIDOffice))
			{
					throw new Exception("Изберете регион!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($sRegNum))
			{
					throw new Exception("Въведете регистрационен номер!", DBAPI_ERR_INVALID_PARAM);
			}	

					
			$oDBAuto = new DBAuto();
			
			$aData = array();
			

			$aData['id_mark'] = $nIDMark;
			$aData['id_model'] = $nIDModel;
			$aData['id_office'] = $nIDOffice;
			$aData['id_person'] = $nIDPerson;
			$aData['reg_num'] = $sRegNum;
			$aData['color'] = $sColor;
			$aData['dvigatel_num'] = $sDvigatelNum;
			$aData['rama_num'] = $sRamaNum;
			$aData['rate_summer_city'] = $sSummerCity;
			$aData['rate_summer_outcity'] = $sSummerOutcity;
			$aData['rate_winter_city'] = $sWinterCity;
			$aData['rate_winter_outcity'] = $sWinterOutcity;
			$aData['id_function'] = $nIDFunction;

			
			if(!empty($nID))
			{
				$aData['id']=$nID;
			}
			$oDBAuto->update($aData); 
		}
	}
	
?>