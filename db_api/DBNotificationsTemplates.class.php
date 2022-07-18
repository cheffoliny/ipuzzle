<?php

class DBNotificationsTemplates extends DBBase2 {

	public function __construct() {
		global $db_system;
		parent::__construct($db_system,'notifications_templates');
	}
	
	public function getTemplateByEvent($nIDEvent) {
		
		$sQuery = "
				SELECT
					*
				FROM notifications_templates
				WHERE id_event = {$nIDEvent}
			";				
				
		return $this->selectOnce($sQuery);				
	}
}