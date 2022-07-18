<?php

class DBNotificationsDeny extends DBBase2 {

	public function __construct() {
		global $db_system;
		parent::__construct($db_system,'notifications_deny');
	}
	
	public function getReport($oResponse) {
		global $db_name_personnel;
		
		$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					nd.id,
					nd.target,
					CASE nd.type
						WHEN 'phone' THEN 'Телефон'
						WHEN 'email' THEN 'Email'
					END AS type_name,
					IF( 
						p.id, 
						CONCAT(
							CONCAT_WS(' ', p.fname, p.mname, p.lname),
							' (',
							DATE_FORMAT(nd.updated_time, '%d.%m.%Y %H:%i:%s'),
							')'
						),
						''
					) AS updated_user
				FROM notifications_deny nd
				LEFT JOIN {$db_name_personnel}.personnel p ON nd.updated_user = p.id
				WHERE nd.to_arc = 0
			";

		
		$this->getResult($sQuery,'id', DBAPI_SORT_DESC, $oResponse);
		
		foreach($oResponse->oResult->aData as $k => $aRow) {										
			$oResponse->setDataAttributes($k, 'target', array('contentEditable' => 'true'));			
		}
		
		
		$oResponse->setField('target','Email/Phone');
		$oResponse->setField('type_name','Тип');		
		$oResponse->setField('updated_user','Последно редактирал');
		
		$oResponse->setField( "btn_delete",	"", NULL, "images/cancel.gif", "deleteNotificationDeny", "" );
	}
	
	function deleteNotificationDeny($nID) {
		
		$nID = (int) $nID;
		
		if(empty($nID)) return false;
		
		$this->delete($nID);		
	}
	
	function addNotificationDeny($aData) {
		
		$this->update($aData);
	}
	
	function existNotificationDeny($sType,$sTarget) {
				
		$sQuery = "
			SELECT
				*
			FROM notifications_deny
			WHERE to_arc = 0
				AND type = '{$sType}'
				AND target = '{$sTarget}'
		";

		$aData = $this->select($sQuery);
		
		if(empty($aData)) {
			return false;
		} else {
			return true;
		}
	}
	
	function getNotificationsDeniesByType($sType ) {
		
		$sQuery = "
			SELECT
				target
			FROM notifications_deny
			WHERE to_arc = 0
				AND type = '{$sType}'
		";

		$aData = $this->select($sQuery);				
		
		$aFinalData = array();
		
		if(!empty($aData)) {
			foreach($aData as $value) {
				$aFinalData[] = $value['target'];
			}
		}
		
		return $aFinalData;
		
	}
}