<?php

	class DBContractsServices extends DBBase2 {
		public function __construct() {
			global $db_finance;
			parent::__construct($db_finance, "contracts_services");
		}	
		
		public function getSMSVest( $nID ) {
			$sQuery = "
				SELECT
					id, 
					period,
					user_name,
					user_gsm
				FROM contracts_services
				WHERE 1 
					AND id_contract = {$nID}
					AND service_type = 'tp_vest_sms'
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getEmailVest( $nID ) {
			$sQuery = "
				SELECT
					id, 
					period,
					price,
					user_name,
					user_email
				FROM contracts_services
				WHERE 1 
					AND id_contract = {$nID}
					AND service_type = 'tp_vest_email'
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function countPanicKBD( $nIDContract) {
			
			$sQuery = "
			
				SELECT 
					SUM(panic_count)
				FROM contracts_services 
				WHERE 1
					AND id_contract = {$nIDContract}
					AND service_type = 'panic_kbd'
				GROUP BY id_contract
			";
			
			return $this->selectOne($sQuery);
			
		}
		
		public function getOnlinePrice( $nID ) {
			$sQuery = "
				SELECT
					price
				FROM
					contracts_services
				WHERE 1
					AND id_contract = {$nID}
					AND service_type = 'tp_vest_online'
			";
			return $this->selectOne($sQuery);
		}
		public function countMonthAccount( $nID ) {
			$sQuery = "
				SELECT
					SUM(price)
				FROM contracts_services
				WHERE 1
					AND id_contract = {$nID}
					AND service_type IN ('mdo','tp_plan','tp_plan_online','tp_plan_sms','tp_plan_sms_opening','panic_stat','panic_radio','panic_kbd','monitoring','tp_vest_sms','tp_vest_online','tp_vest_email','fire','tp_video','other_month')					
			";

			return $this->selectOne($sQuery);
		}
		public function countOthers( $nID ) {
			$sQuery = "
				SELECT
					SUM(price)
				FROM contracts_services
				WHERE 1
					AND id_contract = {$nID}
					AND service_type = 'single_other'					
			";
			return $this->selectOne($sQuery);
		}
		public function getSingles( $nID ) {
			$sQuery = "
				SELECT
					service_type,
					service_name,
					price
				FROM contracts_services
				WHERE 1 
					AND service_type IN ('single_expres','single_fast','single_normal','single_other')
					AND id_contract = {$nID}
			";
			return $this->select($sQuery);
		}
		public function getMonthWithoutBase( $nID ) {
			$sQuery = "
				SELECT
					price,
					service_type,
					user_name,
					user_gsm,
					user_email
				FROM contracts_services
				WHERE 1 
					AND service_type IN ('panic_stat','panic_radio','panic_kbd','tp_vest_sms','tp_vest_online','tp_vest_email')
					AND id_contract = {$nID}
			";
			return $this->select($sQuery);
		}
		
		public function getAllSingles( $nID ) {
			$sQuery = "
				SELECT
					SUM(price) as price
				FROM contracts_services
				WHERE 1 
					AND service_type IN ('single_expres','single_fast','single_normal','single_other')
					AND id_contract = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getMonthReport( DBResponse $oResponse, $aData ) {
			global  $db_name_personnel, $db_name_finance, $db_name_sod;
			
			$nID = is_numeric( $aData ) ? $aData : 0; 
			
			$sQuery = "	
				SELECT
					id,
					service_type,
					IF ( LENGTH(service_name), service_name, service_type ) as service_name,
					panic_count,
					period,
					price
				FROM contracts_services
				WHERE 1
					AND id_contract = {$nID}
					AND service_type IN ('mdo','tp_plan','tp_plan_online','tp_plan_sms','tp_plan_sms_opening','panic_stat','panic_radio','panic_kbd','monitoring','tp_vest_sms','tp_vest_online','tp_vest_email','fire','tp_video','other_month')					
			";

			$this->getResult($sQuery, 'service_type', DBAPI_SORT_ASC, $oResponse);
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'period', array('style' => 'text-align: right; width: 65px;'));
				$oResponse->setDataAttributes( $key, 'price', array('style' => 'text-align: right; width: 65px;'));
				$oResponse->setDataAttributes( $key, 'panic_count', array('style' => 'text-align: right; width: 75px;'));
				$val = str_replace("<li class=\"pager\">", "", $val);
				//APILog::Log(0, 	$val);
			}
			
			$oResponse->setField("service_name", "Тип на прихода", "Сортирай по тип");
			$oResponse->setField("panic_count", "Количество", "Сортирай по количествоп");
			$oResponse->setField("period", "Период", "Сортирай по период");
			$oResponse->setField("price", "Сума", "Сортирай по сума");
			//$oResponse->setField("updated_user", "Последно редактирал", "Сортирай по Последно редактирал");
		}
			
		public function getSingleReport( DBResponse $oResponse, $aData ) {
			global  $db_name_personnel, $db_name_finance, $db_name_sod;
			
			$nID = is_numeric( $aData ) ? $aData : 0; 
			
			$sQuery = "	
				SELECT
					id,
					service_type,
					IF ( LENGTH(service_name), service_name, service_type ) as service_name,
					panic_count,
					period,
					price
				FROM contracts_services
				WHERE 1
					AND id_contract = {$nID}
					AND service_type IN ('single_expres', 'single_fast', 'single_normal', 'single_other')
			";

			$this->getResult($sQuery, 'service_type', DBAPI_SORT_ASC, $oResponse);
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
//				$oResponse->setDataAttributes( $key, 'period', array('style' => 'text-align: right; width: 65px;'));
//				$oResponse->setDataAttributes( $key, 'price', array('style' => 'text-align: right; width: 65px;'));
//				$oResponse->setDataAttributes( $key, 'panic_count', array('style' => 'text-align: right; width: 75px;'));
				$val = str_replace("<li class=\"pager\">", "", $val);
				//APILog::Log(0, 	$val);
			}
			
			$oResponse->setField("service_name", "Тип на прихода", "Сортирай по тип");
			$oResponse->setField("panic_count", "Количество", "Сортирай по количествоп");
			$oResponse->setField("period", "Период", "Сортирай по период");
			$oResponse->setField("price", "Сума", "Сортирай по сума");
			//$oResponse->setField("updated_user", "Последно редактирал", "Сортирай по Последно редактирал");
		}

		// Pavel - taksi kym obekt v Telenet!
		public function getTaxes( $nIDContract, $single ) {
			global $db_name_finance;
			
			$single = !empty($single) ? 1 : 0;
			
			$sQuery = "
				SELECT
					cs.service_type,
					cs.service_name,
					cs.price,
					IF ( cs.panic_count = 0, 1, cs.panic_count ) as quantity,
					css.id_nomenclatures_service,
					css.service_name as name
				FROM {$db_name_finance}.contracts_services cs
				LEFT JOIN {$db_name_finance}.contracts_services_default_settings css ON css.service_type = cs.service_type
				WHERE 1 
					AND css.is_single = {$single}
					AND cs.id_contract = {$nIDContract}
					AND cs.price > 0
			";
			
			return $this->select($sQuery);
		}
	
	}
?>