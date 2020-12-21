<?php
	
	class DBObjectPersonnel
		extends DBBase2 {
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'object_personnel');
		}
		
		public function getReport( DBResponse $oResponse ) {
				global $db_name_sod, $db_name_personnel;

				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('object_personnel_schedule_edit', $_SESSION['userdata']['access_right_levels']) ) {
						$edit_rights = true;
					}
				}	
				
				$nIDObject = Params::get("nID");
				$mon = date('m');
				$ye = date('Y');
				
				$sQuery = "
					SELECT 
						CONCAT(op.id, ',', op.id_person) AS id,
						op.level AS sortsa,
						IF ( op.level > 0, op.level, 1000) as sorts,
						p.code,
						ob.name as object,
						CONCAT_WS(' ', p.fname, p.mname, p.lname ) as name,
						CONCAT(
							CONCAT_WS(' ', up.fname, up.mname, up.lname), 
							' [', 
							DATE_FORMAT(op.updated_time, '%d.%m.%Y %H:%i:%s'), 
							']'
							) AS updated_user
						FROM object_personnel op
						LEFT JOIN {$db_name_personnel}.personnel p ON op.id_person = p.id
						LEFT JOIN {$db_name_personnel}.personnel up ON op.updated_user = up.id
						LEFT JOIN {$db_name_sod}.objects ob ON p.id_region_object = ob.id
						WHERE op.id_object = {$nIDObject}
							AND p.status = 'active'
							AND p.to_arc = 0
							
					";
//				APILog::Log(0, $sQuery);
				
//							AND MONTH(op.afterDate) <= '{$mon}'
//							AND YEAR(op.afterDate) <= '{$ye}'
//							AND IF ( UNIX_TIMESTAMP(op.vacateDate) > UNIX_TIMESTAMP(op.afterDate), MONTH(op.vacateDate) >= '{$mon}', 1)
//							AND IF ( UNIX_TIMESTAMP(op.vacateDate) > UNIX_TIMESTAMP(op.afterDate), YEAR(op.vacateDate) >= '{$ye}', 1)
				
				$nRowCount = $_SESSION['userdata']['row_limit'];
				$_SESSION['userdata']['row_limit'] = 100;
				
				$this->getResult($sQuery, 'sorts, name', DBAPI_SORT_ASC, $oResponse);
				
				$_SESSION['userdata']['row_limit'] = $nRowCount;
				
				$oResponse->setField("sorts", "№", "Сортирай по номер");
				
				foreach( $oResponse->oResult->aData as $key => &$val ) {
					$val['sorts'] = $val['sorts'] < 1000 ? $val['sorts'] : 0;
					$oResponse->setDataAttributes( $key, 'sorts', array('style' => 'text-align: center; width: 35px;'));
					$oResponse->setDataAttributes( $key, 'code', array('style' => 'text-align: right; width: 40px;'));
					$oResponse->setDataAttributes( $key, 'updated_user', array('style' => 'text-align: center; width: 30px;'));
				}	
				$oResponse->setFieldData('sorts', 'input', array('type' => 'text', 'exception' => 'false', 'style' => 'width: 20px; text-align: center;', 'maxlength' => '2' ));
				
				$oResponse->setField("code", "Код", "Сортирай по код");
				$oResponse->setField("name", "Име", "Сортирай по име");
				$oResponse->setField("object", "Титуляр в обект", "Сортирай по Титуляр в обект");
				//$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
				$oResponse->setField("updated_user", "...", "Сортиране по последно редкатирал", "images/dots.gif");
				
				if ( $edit_rights ) {
					$oResponse->setField( '', '', '', 'images/cancel.gif', 'deletePerson', '');
					$oResponse->setFieldLink("code", "openPerson");
					$oResponse->setFieldLink("name", "openPerson");
				}				
		}
		
		public function getMaxOrder( $nID ) {
			
			$nIDObject = (int) $nID;

			$sQuery = "
				SELECT 
					MAX(distinct op.level) as level
				FROM object_personnel op
				WHERE op.id_object = {$nIDObject}
			";

			return $this->selectOnce( $sQuery );
		}

		public function getOrder( $nID ) {
			
			$nID = (int) $nID;

			$sQuery = "
				SELECT 
					op.id,
					op.level as level
				FROM object_personnel op
				WHERE op.id = {$nID}
			";

			return $this->selectOnce( $sQuery );
		}

		public function toLevel( $data ) {
			global $db_sod;
			
			$nIDObj = (int) $data['obj'];
			$level_from = (int) $data['level_from'];
			$level_to = (int) $data['level_to'];

			$sQuery = "
				UPDATE object_personnel
				SET level = {$level_to}
				WHERE
					id_object = '{$nIDObj}'
					AND level = '{$level_from}'
			";

			$db_sod->Execute($sQuery);
		}
				
		public function getIDByLevel( $data ) {
			global $db_sod;
			
			$nIDObj = (int) $data['obj'];
			$level = (int) $data['level'];

			$sQuery = "
				SELECT id
				FROM object_personnel
				WHERE
					id_object = {$nIDObj}
					AND level = {$level}
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getPersonIsFree( $aData ) {
			global $db_sod;
			
			$nIDPerson = $aData['person'];
			$nIDObject = $aData['object'];
			
			$mon = date('m');
			$ye = date('Y');
			
			$sQuery = "
				SELECT 
					count(od.id) as cnt
				FROM object_duty od
				LEFT JOIN object_personnel op ON (op.id_person = '{$nIDPerson}' AND op.id_object = '{$nIDObject}')
				WHERE od.id_person = {$nIDPerson}
					AND od.id_obj = {$nIDObject}
					AND MONTH(od.startShift) >= {$mon}
					AND YEAR(od.startShift) >= {$ye}
					AND od.id_shift > 0
				LIMIT 1
			";
			//APILog::Log(0, $sQuery);
			return $this->selectOnce( $sQuery );
		}

		public function deleteFreePerson( $aData ) {
			global $db_sod;

			$nIDPerson = $aData['person'];
			$nIDObject = $aData['object'];

			$mon = date('m');
			$ye = date('Y');
			
			$sQuery = "
				DELETE
				FROM object_duty
				WHERE id_person = {$nIDPerson}
					AND id_obj = {$nIDObject}
					AND MONTH(startShift) >= '{$mon}'
					AND YEAR(startShift) >= '{$ye}'
					AND id_shift = 0
			";
			//APILog::Log(0, $sQuery);
			$db_sod->Execute($sQuery);
		}
	}
	
?>