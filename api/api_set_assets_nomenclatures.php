<?php
	
	class ApiSetAssetsNomenclatures
	{	

		
		public function load(DBResponse $oResponse)
		{   
			$nID = Params::get('nID','0');
			$oGroup= new DBAssetsNomenclatures();
			$aGroup = $oGroup->getGroup();
			$aGroupAll=$oGroup->getAssetsNomenclatures($nID);
			$nIDSelectedGroup=((isset($aGroupAll['id_group']))?$aGroupAll['id_group']:0);
			
			
			if(!empty($nID))	
				{	
			### Dava vsi4ki elementi ot reda otgovarq6t na tova ID			
						$aGroupAll=$oGroup->getAssetsNomenclatures($nID);
						$oResponse->setFormElement('form1','sName',array(),$aGroupAll['name']);
						$this->SelectedAttributes($oResponse,$nID);
				}	
			$oResponse->setFormElement('form1', 'nIDGroup', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDGroup', array_merge(array("value"=>'0')), "--Всички--");
			if(empty($nID))
				{
					
					$oResponse->setFormElement('form1', 'all_attributes', array(), '');
					$oAttributes = new DBAttributes();
					$aAttributes = $oAttributes->getAttributes();
					foreach($aAttributes as $key => $value)
					{
						$ch = array();
						$oResponse->setFormElementChild('form1', 'all_attributes', array_merge(array("value"=>$key),$ch), $value);
					}
				}	
			$this->showGroups($oResponse,0,$nIDSelectedGroup);
			$oResponse->printResponse();
		}
		
		
		function showGroups($oResponse,$nIDGroup,$nIDSelectedGroup)
			{	
			$oDBAssetsGroup = new DBAssetsGroups();
			$aGroups = $oDBAssetsGroup -> getChilds($nIDGroup);	
			
			global $space;
			if(!empty($nIDGroup))$space .= "    ";
			
			foreach ($aGroups as $key => $value ) 
				{
					if($key == $nIDSelectedGroup)
						{
							$ch = array( "selected" => "selected" );
						}
					else{
							$ch=array();
						}
					$oResponse->setFormElementChild('form1', 'nIDGroup', array_merge(array("value"=>$key),$ch), $space.$value);
					$this->showGroups($oResponse,$key,$nIDSelectedGroup);
				}
			
			if(!empty($nIDGroup))$space = substr($space,4);
			}
				
		function SelectedAttributes($oResponse,$nID)
		{
			$oResponse->setFormElement('form1', 'all_attributes', array(), '');
			$oResponse->setFormElement('form1', 'account_attributes', array(), '');
			
			$oAttributes= new DBAttributes();
				$allattributes=$oAttributes->getAttributes();
			$oSelectedAttributes= new DBAssetsNomenclaturesAttributes();
				$selectedAttributes=$oSelectedAttributes->getAttrIDsByNomId($nID);
				
			foreach($allattributes as $key => $attrib)
			{
				 if(in_array($key,$selectedAttributes))
				 {
						
						$oResponse->setFormElementChild('form1', 'account_attributes',array('value'=>$key),$attrib);
				 }else
				 	{
						$oResponse->setFormElementChild('form1', 'all_attributes', array('value'=>$key),$attrib);
				 	}
			}
		}

			
		 function save(DBResponse $oResponse)
		{
			$nID 			=	Params::get('nID','0');
			$sName			=	Params::get('sName');
			$nIDGroup		=	Params::get('nIDGroup');	
			APILog::Log(0,$nID);			
			if(empty($sName))
			{
					throw new Exception("Въведете име на номенклатура!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($nIDGroup))
			{
					throw new Exception("Изберете група!", DBAPI_ERR_INVALID_PARAM);
			}
			$oDBAssetsNomenclatures = new DBAssetsNomenclatures();
			$aData = array();
			
			$aData['id']			= $nID;
			$aData['name']			= $sName;
			$aData['id_group']   	= $nIDGroup;
			$oDBAssetsNomenclatures->update($aData);
			
			$nID = $aData['id'];  
			unset($aData);
			$oDBAssetsNomenclaturesAttributes = new DBAssetsNomenclaturesAttributes();
			$oDBAssetsNomenclatures->getDELETEfromAssetsNomenclaturesAttributes($nID); 
			
			$nIDnomenclatures	=	$nID;
			$nIDAttributes 		= 	array();
			$nIDAttributes	 	=	Params::get('account_attributes');

			foreach ($nIDAttributes as $value)
				{	
					$aData['id_nomenclature']	= $nIDnomenclatures;
					$aData['id_attribute']  	= $value;			
					$oDBAssetsNomenclaturesAttributes->update($aData);
					unset($aData);
				}	
		}
	}
?>