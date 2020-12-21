<?php
	class ApiObjectPersonnelScheduleSettings
	{
		public function result( DBResponse $oResponse )
		{
			$oSchedule = new DBObjectScheduleSettings();
			$oSchedule->getReport( $oResponse );
			
			$oResponse->printResponse( "Настройки на график", "object_personnel_schedule_settings" );
		}

	}
?>