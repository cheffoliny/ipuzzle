<?php
	class DBMessages extends DBBase2 	{
		public function __construct() {
			global $db_sod;
			parent::__construct($db_sod, 'messages');
		}
		
		public function getSignalById( $nID )	{
			global $db_sod, $db_name_sod;
			
			$sQuery = "
				SELECT
					id as id,
					id_sig,
					id_obj,
					msg_al as msg_al,
					code_al as code_al,
					msg_rest as msg_rest,
					code_rest as code_rest,
					test_flag as test_flag,
					test,
					IF (is_phone, IF(is_cid, 'cid', 'phone'), 'radio') as channel,
					is_cid,
					id_cid,
					flag,
					test_flag,
					test,
					is_phone					
				FROM {$db_name_sod}.messages
				WHERE id = {$nID}
			";

			return $this->selectOnce( $sQuery );
		}		
		
		public function insertMessages( $nIDTemplet, $nIDObject) {

			$sQuery = "
				INSERT 	INTO messages 
								(id_sig,
								id_obj,
								is_cid,
								id_cid,
								zone,
								code_al,
								msg_al,
								code_rest,
								msg_rest,
								time_al,
								flag,
								test_flag,
								test,
								is_phone)
				SELECT 
						tv.id_sig,
						'{$nIDObject}',
						tv.is_cid,
						tv.id_cid,
						tv.zone,
						tv.code_al,
						tv.msg_al,
						tv.code_rest,
						tv.msg_rest,
						tv.time_al,
						tv.flag,
						tv.test_flag,
						tv.test,
						tv.is_phone
				FROM schemes tv
				WHERE tv.id_scheme = {$nIDTemplet}
			";
			
			$this->oDB->Execute( $sQuery );
		}

		public function del( $nID ) {
			global $db_sod, $db_name_sod;
			
			$aMessage	= array();
			$aMessage 	= $this->getSignalById($nID);
			$oSig 		= new DBObjectSignals();
			$oObj		= new DBObjects();
			
			$nOb2	 	= isset($aMessage['id_obj']) 	? $aMessage['id_obj'] 	: 0;
			$nObj		= $nID;
			//$nObj 		= $oObj->getIDByIDOldObj($nOb2);
			
			$aData					= array();
			$aData['id'] 			= 0;
			$aData['id_obj'] 		= $nObj;
			$aData['id_signal'] 	= isset($aMessage['id_sig']) 		? $aMessage['id_sig'] 		: 0;
			$aData['id_old_signal'] = isset($aMessage['id_sig']) 		? $aMessage['id_sig'] 		: 0;
			$aData['code_al'] 		= isset($aMessage['code_al']) 		? $aMessage['code_al'] 		: 0;
			$aData['code_old_al'] 	= isset($aMessage['code_al']) 		? $aMessage['code_al'] 		: 0;
			$aData['code_rest'] 	= isset($aMessage['code_rest']) 	? $aMessage['code_rest'] 	: 0;
			$aData['code_old_rest'] = isset($aMessage['code_rest']) 	? $aMessage['code_rest'] 	: 0;
			$aData['msg_al'] 		= isset($aMessage['msg_al']) 		? $aMessage['msg_al']		: "";
			$aData['msg_old_al'] 	= isset($aMessage['msg_al']) 		? $aMessage['msg_al'] 		: "";
			$aData['type'] 			= "delete";
			$aData['to_arc'] 		= 0;

			$oSig->update($aData);			
			
			$sQuery = "
				UPDATE {$db_name_sod}.messages SET to_arc = 1
				WHERE id = {$nID}
			";
			
			$db_sod->Execute( $sQuery );
		}

		public function del2( $nID ) {
			global $db_sod, $db_name_sod;
			
			$aIDs	= array();
			$aIDs	= explode(",", $nID);
			$oSig 	= new DBObjectSignals();
			$oObj	= new DBObjects();
			
			
			
			foreach ( $aIDs as $val ) {
				$aMessage	= array();
				$aMessage 	= $this->getSignalById($val);
				$nOb2	 	= isset($aMessage['id_obj']) 	? $aMessage['id_obj'] 	: 0;
				//$nObj 		= $nID;	
				
				$aData					= array();
				$aData['id'] 			= 0;
				$aData['id_obj'] 		= $nOb2;
				$aData['id_signal'] 	= isset($aMessage['id_sig']) 		? $aMessage['id_sig'] 		: 0;
				$aData['id_old_signal'] = isset($aMessage['id_sig']) 		? $aMessage['id_sig'] 		: 0;
				$aData['code_al'] 		= isset($aMessage['code_al']) 		? $aMessage['code_al'] 		: 0;
				$aData['code_old_al'] 	= isset($aMessage['code_al']) 		? $aMessage['code_al'] 		: 0;
				$aData['code_rest'] 	= isset($aMessage['code_rest']) 	? $aMessage['code_rest'] 	: 0;
				$aData['code_old_rest'] = isset($aMessage['code_rest']) 	? $aMessage['code_rest'] 	: 0;
				$aData['msg_al'] 		= isset($aMessage['msg_al']) 		? $aMessage['msg_al']		: "";
				$aData['msg_old_al'] 	= isset($aMessage['msg_al']) 		? $aMessage['msg_al']		: "";
				$aData['type'] 			= "delete";
				$aData['to_arc'] 		= 0;
	
				$oSig->update($aData);							
			}
			
			if ( !empty($nID) ) {
				$sQuery = "
					UPDATE {$db_name_sod}.messages SET to_arc = 1
					WHERE id IN ({$nID})
				";
				
				$db_sod->Execute( $sQuery );
			}
		}
		
		public function checkMessage($nIDObject, $nIDSignal, $nCodeAlarm) {
			global $db_sod, $db_name_sod;
			
			$sQuery	= "
				SELECT 
					1 
				FROM {$db_name_sod}.messages 
				WHERE id_obj = {$nIDObject} 
					AND id_sig = {$nIDSignal} 
					AND code_al = {$nCodeAlarm} 
			";
			
			return $this->selectOne($sQuery);
		}

		public function delMessage($nIDObject, $nIDSignal, $nCodeAlarm) {
			global $db_sod, $db_name_sod;
			
			$sQuery	= "
				UPDATE {$db_name_sod}.messages SET to_arc = 1
				WHERE id_obj = {$nIDObject} 
					AND id_sig = {$nIDSignal} 
					AND code_al = {$nCodeAlarm} 
			";
			
			$db_sod->Execute($sQuery);
		}		
	}
?>