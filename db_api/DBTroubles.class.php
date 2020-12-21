<?php
	class DBTroubles
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			$db_sod->debug=true;
			
			parent::__construct($db_sod, 'object_troubles');
		}
		
		public function getTroubles( DBResponse $oResponse, $data ) {
			global  $db_sod, $db_name_personnel, $db_name_sod;
			
			
			
			$nID = is_numeric( $data['obj'] ) ? $data['obj'] : 0;
						
			if ( $data['type'] == "tech" ) {
				$where = " AND tp.is_operativ = 0 \n";
			} elseif ( $data['type'] == "operativ" ) {
				$where = " AND tp.is_operativ = 1 \n";
			} else $where = "";

			if ( $data['service'] == 1 ) {
				$where2 = " AND ot.id_reason = 0 \n";
			} else $where2 = "";
			
		//	APILog::Log(0, $where);
			$sQuery = "	
				SELECT SQL_CALC_FOUND_ROWS 
					ot.id,
					IF ( tp.is_operativ = 'tech', 'Технически', 'Оперативен' ) as type,
					tp.name as problem,
					tr.name as reason
				FROM object_troubles ot
				LEFT JOIN trouble_problems tp ON tp.id = ot.id_problem
				LEFT JOIN trouble_reasons tr ON tr.id = ot.id_reason
				WHERE ot.id_obj = {$nID}
					AND ot.to_arc = 0
					{$where} {$where2}
			";
			
			$this->getResult($sQuery, 'id', DBAPI_SORT_DESC, $oResponse);
									
			$oResponse->setField("type", "Тип", "Сортирай по тип");
			$oResponse->setField("problem", "Проблем", "Сортирай по проблем");
			$oResponse->setField("reason", "Причина", "Сортирай по причини");
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'delTrouble', 'Изтрий');
			$oResponse->setFIeldLink('type',	'editTrouble' );
			$oResponse->setFIeldLink('problem',	'editTrouble' );
			$oResponse->setFIeldLink('reason',	'editTrouble' );
					
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'type', array('style' => 'text-align: center; width: 130px;') );				
			}
		}
		
		public function getTroubleNames() {
			$sQuery = "
				SELECT
					id,
					name,
					IF ( is_operativ = 0, 'tech', 'operativ' ) as operativ
				FROM trouble_problems
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
		}

		public function getReasonsNames() {
			$sQuery = "
				SELECT
					id,
					name,
					IF ( is_operativ = 0, 'tech', 'operativ' ) as operativ
				FROM trouble_reasons
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getTroubleByID( $nID ) {
			$sQuery = "
				SELECT
					ot.id,
					IF ( tp.is_operativ = 0, 'tech', 'operativ' ) as type,
					ot.id_problem,
					ot.id_reason,
					ot.problem_info,
					ot.reason_info
				FROM object_troubles ot
				LEFT JOIN trouble_problems tp ON tp.id = ot.id_problem
				WHERE ot.id = {$nID}
			";
			
			return $this->select($sQuery);
		}
		
	}
?>