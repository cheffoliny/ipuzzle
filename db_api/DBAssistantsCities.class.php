<?php
	class DBAssistantsCities extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct($db_personnel, 'assistants_cities');
		}

	}
?>