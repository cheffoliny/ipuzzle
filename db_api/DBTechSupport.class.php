<?php
	class DBTechSupport
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			$db_sod->debug=true;
			
			parent::__construct($db_sod, 'object_tech_support');
		}
		
		public function getTroubles( DBResponse $oResponse, $data ) {
			global  $db_sod, $db_name_sod;
			
			$nID = is_numeric( $data['obj'] ) ? $data['obj'] : 0;
						
			if ( $data['service'] == 1 ) {
				$where = " AND ot.id_reason = 0 \n";
			} else $where = "";
			
			$sQuery = "	
				SELECT SQL_CALC_FOUND_ROWS 
					ots.id,
					DATE_FORMAT(ots.date_tech, '%d.%m.%Y %H:%i:%s') as date_tech,
					ts.name as type,
					DATE_FORMAT(ots.date_planing, '%d.%m.%Y %H:%i:%s') as date_planing,
					DATE_FORMAT(ots.date_complete, '%d.%m.%Y %H:%i:%s') as date_complete,
					CONCAT( CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(ots.updated_time, '%d.%m.%Y %H:%i:%s'), ']' ) AS updated_user
				FROM object_tech_support ots
				LEFT JOIN tech_support ts ON ts.id = ots.id_tech
				LEFT JOIN personnel.personnel up ON up.id = ots.updated_user
				WHERE ots.id_obj = {$nID}
					AND ots.to_arc = 0
					{$where}
			";
			
			$this->getResult($sQuery, 'id', DBAPI_SORT_DESC, $oResponse);
									
			$oResponse->setField("date_tech", "Дата", "Сортирай по дата");
			$oResponse->setField("type", "Тип", "Сортирай по тип");
			$oResponse->setField("date_planing", "Дата на планиране", "Сортирай по дата");
			$oResponse->setField("date_complete", "Дата на изпълнение", "Сортирай по дата");
			$oResponse->setField("updated_user", "...", "Сортиране по последно редкатирал", "images/dots.gif");
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'delSupport', 'Изтрий');
			$oResponse->setFIeldLink('type',	'editSupport' );
					
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'type', array('style' => 'text-align: center; width: 130px;') );				
				$oResponse->setDataAttributes( $key, 'updated_user', array('style' => 'text-align: center; width: 30px;') );				
			}
		}
		
		public function getSupportNames() {
			$sQuery = "
				SELECT
					id,
					name
				FROM tech_support
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getSupportByID( $nID ) {
			$sQuery = "
				SELECT
					ots.id,
					ots.date_planing,
					ots.id_user_tech_planing,
					ots.info_planing,
					ots.date_complete,
					ots.id_user_tech_complete,
					ots.info_complete
				FROM object_tech_support ots
				LEFT JOIN tech_support ts ON ts.id = ots.id_tech
				WHERE ot.id = {$nID}
			";
			
			return $this->select($sQuery);
		}
		
	}
?>