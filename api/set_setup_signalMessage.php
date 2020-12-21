<?php
	class ApiSetSetupObjectShifts {
		public function load( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
			$aShiftTypes = array();
			$oShift = new DBObjectShifts();
			
			if( !empty( $nID ) ) {
				$aShift = $oShift->getRecord( $nID );
				
				//$oResponse->setFormElement('form1', 'nIDObject', array('value' => $aShift['id_obj']));	
				$oResponse->setFormElement('form1', 'sCode', array('value' => $aShift['code']));	
				$oResponse->setFormElement('form1', 'sName', array('value' => $aShift['name']));	
				$oResponse->setFormElement('form1', 'sStake', array('value' => $aShift['stake']));
				$oResponse->setFormElement('form1', 'sDescription', array('value' => $aShift['description']));	
				$oResponse->setFormElement('form1', 'sShiftFrom', array('value' => $aShift['shiftFrom']));	
				$oResponse->setFormElement('form1', 'sShiftTo', array('value' => $aShift['shiftTo']));	
				
				if ( $aShift['automatic'] ) {
					$oResponse->setFormElement('form1', 'nAuto', array('checked' => 'checked'));
						
				}
			}
	
			$aShiftTypes = $oShift->getShiftTypes( );

			$oResponse->setFormElement('form1',			'nType', array(), '');	
			$oResponse->setFormElementChild('form1',	'nType', array('value' => 0), 'Изберете шаблон');	
			
			foreach ( $aShiftTypes as $key => $val ) {
				$id = $val['start'].','.$val['end'].','.$val['code'].','.$val['name'];
				$oResponse->setFormElementChild('form1',	'nType', array('value' => $key, 'id' => $id), '['.$val['code'].'] '.$val['name']);
			}
			
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID		= Params::get('nID', 0);
			$nIDObj		= Params::get('nIDObject', 0);
			$sName		= Params::get("sName");
			$nAutomatic	= Params::get("nAuto");
			$sStake		= Params::get("sStake");
			$sCode		= Params::get("sCode");
			$sShiftFrom = Params::get("sShiftFrom");
			$sShiftTo	= Params::get("sShiftTo");
			$sDescription	= Params::get("sDescription");
			
			$now = time();
			
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

			if ( empty($sStake) ) {
				throw new Exception("Въведете ставка за смяната!", DBAPI_ERR_INVALID_PARAM);
			}
							
			$oShift = new DBObjectShifts();
			$aShift = array();
			
			$sQuery = "
				SELECT id, code
				FROM object_shifts
				WHERE id != {$nID}
					AND code = '{$sCode}' 
					AND id_obj = '{$nIDObj}'
					AND to_arc = 0
			";
			
			$aShift = $oShift->selectOne( $sQuery );
			
			if ( !empty($aShift) ) {
				throw new Exception("Вече съществува запис с този код!", DBAPI_ERR_INVALID_PARAM);
				$oResponse->printResponse();
			}

			
			if ( empty($nID) ) {
				$aData = array();
				$aData['id'] = $nID;
				$aData['id_obj'] = $nIDObj;
				$aData['code'] = $sCode;
				$aData['name'] = $sName;
				$aData['automatic'] = $nAutomatic;
				$aData['stake'] = $sStake;
				$aData['shiftFrom'] = $sShiftFrom;
				$aData['shiftTo'] = $sShiftTo;
				$aData['validFrom'] = $now;
				$aData['description'] = $sDescription;
			
				$oShift->update( $aData );
			} else {
				$aData = array();
				$aData['id'] = $nID;
				$aData['validTo'] = $now;
				$aData['to_arc'] = 1;
			
				$oShift->update( $aData );
				
				$aData['id'] = 0;
				$aData['id_obj'] = $nIDObj;
				$aData['code'] = $sCode;
				$aData['name'] = $sName;
				$aData['automatic'] = $nAutomatic;
				$aData['stake'] = $sStake;
				$aData['shiftFrom'] = $sShiftFrom;
				$aData['shiftTo'] = $sShiftTo;
				$aData['description'] = $sDescription;				
				$aData['validFrom'] = $now;
				$aData['validTo'] = '0000-00-00';
				$aData['to_arc'] = 0;
				
				$oShift->update( $aData );
			}
			
			$oResponse->printResponse();
		}
			
	}
	
?>