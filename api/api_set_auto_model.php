<?php
	
	class ApiSetAutoModel
	{
		public function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID');

			if(!empty($nID))
			{

				$oDBAutoModels = new DBAutoModels();
				$aDBAutoModel = $oDBAutoModels->getRecord($nID);
						
				$oResponse->setFormElement('form1', 'sName', array(), $aDBAutoModel['model']);
			}
			
			$oDBAutoMarks = new DBAutoMarks();
			$aAutoMarks = $oDBAutoMarks->getMarks();
			//APILog::Log(0, $aAutoMarks);
			
			$oResponse->setFormElement('form1', 'id_mark', array(), '');
			
			$oResponse->setFormElementChild('form1', 'id_mark', array_merge(array("value"=>'0')), "--Изберете--");
			$ch='';
			foreach($aAutoMarks as $key => $value)
			{
				if($key == $aDBAutoModel['id_mark'])
				{
					$ch = array( "selected" => "selected" );
				} 
				else
				{
					$ch = array();
				}
				$oResponse->setFormElementChild('form1', 'id_mark', array_merge(array("value"=>$key),$ch), $value);
			}
			
		$oResponse->printResponse();
			
		}
		public function save()
		 {
			$nID 	=	Params::get('nID');
			$sName 	=	Params::get('sName');
			$sMark 	=	Params::get('id_mark');
			
			if(empty($sName))
			{
					throw new Exception("Въведете модел!", DBAPI_ERR_INVALID_PARAM);
			}
			if(empty($sMark))
			{
					throw new Exception("Въведете марка!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$oDBAutoModels = new DBAutoModels();
			
			$aData = array();
			
			$aData['model'] = $sName;
			$aData['id_mark'] = $sMark;
			
			if(!empty($nID))
			{
				$aData['id']=$nID;
			}
			$oDBAutoModels->update($aData); 
		}
	}
	
?>