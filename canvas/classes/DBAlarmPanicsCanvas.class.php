<?php

class DBAlarmPanicsCanvas extends DBBase2 {
	public function __construct() {
		global $db_sod;

		parent::__construct($db_sod, 'alarm_panics');
	}
	
	public function getNewPanics() {
		
		$sQuery = "
				SELECT
					ap.*,
					CONCAT('[',o.num,'] ',o.name) AS object_name
				FROM alarm_panics ap
				JOIN objects o ON o.id = ap.id_object
				WHERE ap.status = 'new'
			";

		return $this->select($sQuery);
	}
	
	public function getAlarmPanic($nID) {
		
		$nID = (int) $nID;
		if(empty($nID)) return false;
		
		$sQuery = "
				SELECT
					ap.*,
					o.id_office
				FROM alarm_panics ap
				JOIN objects o ON o.id = ap.id_object
				WHERE ap.id = {$nID}
			";

		return $this->selectOnce($sQuery);
		
	}
	
}
		