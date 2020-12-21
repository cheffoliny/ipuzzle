<?php
	
	class ApiSetAutoMark
	{
		public function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID');

			if(!empty($nID))
			{

				$oDBAutoMarks = new DBAutoMarks();
				$aDBAutoMark = $oDBAutoMarks->getRecord($nID);
						
				$oResponse->setFormElement('form1', 'sName', array(), $aDBAutoMark['mark']);
				
			}
			
		$oResponse->printResponse();
			
		}
		public function save()
		 {
			$nID = Params::get('nID');
			$sName = Params::get('sName');
			
			if(empty($sName))
			{
					throw new Exception("Въведете наименование!", DBAPI_ERR_INVALID_PARAM);
			}
			
			
			$oDBAutoMarks = new DBAutoMarks();
			
			$aData = array();
			
			$aData['mark'] = $sName;
			
			if(!empty($nID))
			{
				$aData['id']=$nID;
			}
			$oDBAutoMarks->update($aData);
		}
	}
	
?>