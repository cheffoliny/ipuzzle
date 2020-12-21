<?php
	class ApiSetSetupPersonShifts {
		public function load( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			
			if( !empty( $nID ) ) {
				$oShift = new DBPersonShifts();
				$aShift = $oShift->getRecord( $nID );
				
				$oResponse->setFormElement('form1', 'sCode', array('value' => $aShift['code']));	
				$oResponse->setFormElement('form1', 'sName', array('value' => $aShift['name']));	
				$oResponse->setFormElement('form1', 'sDescription', array('value' => $aShift['description']));	
				$oResponse->setFormElement('form1', 'sShiftFrom', array('value' => $aShift['start']));	
				$oResponse->setFormElement('form1', 'sShiftTo', array('value' => $aShift['end']));	
			}
			
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$sName		= Params::get("sName");
			$nID		= Params::get("nID", 0);
			$sCode		= Params::get("sCode");
			$sShiftFrom = Params::get("sShiftFrom");
			$sShiftTo	= Params::get("sShiftTo");
			$sDescription	= Params::get("sDescription");
			
			if ( empty($sCode) ) {
				throw new Exception("Въведете код на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sName) ) {
				throw new Exception("Въведете наименование на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sShiftFrom) ) {
				throw new Exception("Въведете начално време на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sShiftTo) ) {
				throw new Exception("Въведете крайно време на смяна!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$oShift = new DBPersonShifts();
			$aShift = array();
			
			$sQuery = "
				SELECT id, code
				FROM person_shifts
				WHERE id != {$nID}
					AND code = '{$sCode}' 
			";
			
			$aShift = $oShift->selectOne( $sQuery );
			
			if ( !empty($aShift) ) {
				throw new Exception("Вече съществува запис с този код!", DBAPI_ERR_INVALID_PARAM);
				$oResponse->printResponse();
			}
				
			$aData = array();
			$aData['id'] = Params::get('nID', 0);
			$aData['code'] = $sCode;
			$aData['name'] = $sName;
			$aData['start'] = $sShiftFrom;
			$aData['end'] = $sShiftTo;
			$aData['description'] = $sDescription;
			
			$oShift->update( $aData );
			
			$oResponse->printResponse();
		}
			
	}
	
?>