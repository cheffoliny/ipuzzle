<?php

	class ApiSetObjectClient {
		public function result(DBResponse $oResponse) {
			
			$nIDObject 	= Params::get('nIDObject', 0);

			$oClient = new DBClients();
			$aClient = $oClient->getClientByObject($nIDObject);
				
			$nIDClient = isset($aClient['id']) && is_numeric($aClient['id']) ? $aClient['id'] : 0;
			$sClient = isset($aClient['name']) && !empty($aClient['name']) ? $aClient['name'] : "";				

			$oResponse->setFormElement('form1', 'nID', array(), $nIDClient);
			$oResponse->setFormElement('form1', 'ClientName', array(), $sClient);			
			
			$oResponse->printResponse(); 
			
		}
				
		public function save(DBResponse $oResponse) {
			$nID 		= Params::get('nID', 0);
			$nIDObject 	= Params::get('nIDObject', 0);

			
			if ( empty($nID) || empty($nIDObject) ) {
				throw new Exception("Изберете клиент!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['id_client']		= $nID;
			$aData['id_object']		= $nIDObject;
			$aData['attach_date']	= time();
			$aData['updated_user']	= !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			$aData['updated_time']	= time();
			$aData['to_arc']		= 0;
			
			$oClient = new DBClients();
			$aClient = $oClient->updateClientObject($aData);
			
			
			$oResponse->printResponse();
		}
	}
	


?>