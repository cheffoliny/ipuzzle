<?php
	
	class ApiAutoModels
	{
		public function load( DBResponse $oResponse )
		{
			$oDBAutoMarks = new DBAutoMarks();
			$aAutoMarks = $oDBAutoMarks->getMarks();
			//APILog::Log(0, $aAutoMarks);
			
			$oResponse->setFormElement('form1', 'id_mark', array(), '');
			
			$oResponse->setFormElementChild('form1', 'id_mark', array_merge(array("value"=>'0')), "Всички");
			foreach($aAutoMarks as $key => $value)
			{
				$oResponse->setFormElementChild('form1', 'id_mark', array_merge(array("value"=>$key)), $value);
			}
			
			
			$oResponse->printResponse();
		}
		public function result( DBResponse $oResponse ) 
		{
			$nMark = Params::get('id_mark','0');

			$oDBAutoModels = new DBAutoModels();

			$oDBAutoModels->getReport($nMark , $oResponse);

			$oResponse->printResponse("Автомобили-Модели","auto_models");  
		}
		public function delete()
		{
			$nID = Params::get('nID',0);
			
			$oDBAutoModels = new DBAutoModels();
			$oDBAutoModels->delete($nID);
			
		}
	}
	
?>