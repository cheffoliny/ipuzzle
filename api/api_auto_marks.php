<?php
	
	class ApiAutoMarks
	{
		public function result( DBResponse $oResponse )
		{
			$oDBAutoMarks = new DBAutoMarks();
			$oDBAutoMarks->getReport( $oResponse );
			
			$oResponse->printResponse("Автомобили-Марки","auto_marks");
		}
		public function delete()
		{
			$nID = Params::get('nID');
			$oDBAutoMarks = new DBAutoMarks();
			$oDBAutoMarks->delete($nID);
		}
	}
	
?>