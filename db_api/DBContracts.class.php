<?php

	class DBContracts extends DBBase2 {
		public function __construct() {
			global $db_finance;
			//$db_finance->debug=true;
			parent::__construct($db_finance, "contracts");
		}	
		
		public function getReport(DBResponse $oResponse, $aData ) {
			global  $db_name_personnel, $db_name_finance, $db_name_sod;
			
			$sStatus 	= $aData['status'];
			$nIDCity	= $aData['id_city']; 
			$dFrom		= $aData['date_from'];
			$dTo		= $aData['date_to'];
			$date_to	= mktime(0, 0, 0, date("m", $dTo)  , date("d", $dTo)+1, date("Y", $dTo));
			
			$sQuery = "	
				SELECT SQL_CALC_FOUND_ROWS
					IF(
						IFNULL(p.id,'false'),
						CONCAT(con.id,',',p.id),
						con.id
					) AS id,
					con.contract_num,
					DATE_FORMAT(con.contract_date, '%d.%m.%Y') AS contract_date_,
					con.last_build_time,
					DATE_FORMAT(con.last_build_time, '%d.%m.%Y %H:%i:%s') AS last_build_time,
					ci.name AS city_name,
					con.rs_name,
					con.contract_status
				FROM contracts con
				LEFT JOIN {$db_name_sod}.cities ci ON ci.id_office = con.id_office
				LEFT JOIN {$db_name_personnel}.personnel p ON CONCAT_WS(' ',p.fname,p.mname,p.lname) = con.rs_name
				WHERE 1
					AND con.to_arc = 0
			";
			
			if ( !empty($sStatus)) {
				$sQuery .= " AND con.contract_status = '{$sStatus}' \n";
			}
			
			if ( !empty($nIDCity) ) {
				$sQuery .= " AND ci.id = '{$nIDCity}' \n";
			} else {
				$oDBCities = new DBCities();
				$aCities = $oDBCities -> getCitiesWithIDOffice();
				foreach ($aCities as $key => $value) {
					$aIDCities[$key] = $key;
				}
				$sIDCities = implode(',',$aIDCities);
				if(!empty($sIDCities)) {
					$sQuery .= " AND ci.id IN ({$sIDCities})\n";
				}
			}
			
			if ( !empty($dFrom) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(con.contract_date) >= '{$dFrom}' \n";
			}

			if ( !empty($dTo) ) {
				$sQuery .= " AND UNIX_TIMESTAMP(con.contract_date) <= '{$date_to}' \n";
			}
			
			$this->getResult($sQuery, 'contract_date_', DBAPI_SORT_DESC, $oResponse);
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				if ($val['contract_status'] != 'entered') {
					$oResponse->setDataAttributes( $key, '', array('disabled' => 'disabled'));
					$oResponse->setDataAttributes( $key, ' ', array('disabled' => 'disabled'));
				}
			}
			
			$oResponse->setField("contract_num", "Номер", "Сортирай по номер");
			$oResponse->setField("contract_date_", "Дата на договора", "Сортирай по дата");
			$oResponse->setField("last_build_time", "Крайна дата за изграждане", "Сортирай по крайна дата за изграждане");
			$oResponse->setField("city_name", "Населено място", "Сортирай по населено място");
			$oResponse->setField("rs_name", "Рекламен сътрудник", "Сортирай по рекламен сътрудник");
			
			//$oResponse->setField( '' ,'','','', 'openRequest', 'Валидиране');
			$oResponse->setField( ' ','','','', 'ignoreContract', 'Отказаване');
			
			$oResponse->setFieldLink('contract_num','openContractPDF');
			$oResponse->setFieldLink('rs_name','openPerson');

			
		}
		
		public function getInfoForPDF( $nID ) {
			
			global $db_name_personnel,$db_name_sod;
			
			$sQuery = "
				SELECT
					con.rs_name,
					con.rs_code,
					con.contract_num,
					DATE_FORMAT(con.contract_date, '%d') AS day,
					DATE_FORMAT(con.contract_date, '%m') AS month,
					DATE_FORMAT(con.contract_date, '%Y') AS year,
					con.client_is_company,
					con.client_name,
					con.client_address,
					con.client_dn,
					con.client_bul,
					con.client_mol,
					con.client_egn,
					con.client_phone,
					con.client_email,
					o.name AS obj_region,
					con.obj_name,
					con.obj_address,
					con.obj_phone,
					con.id_obj,
					DATE_FORMAT(con.last_build_time, '%H:%i') AS last_build_hour,
					DATE_FORMAT(con.last_build_time, '%d') AS last_build_day,
					DATE_FORMAT(con.last_build_time, '%m') AS last_build_month,
					DATE_FORMAT(con.last_build_time, '%Y') AS last_build_year,
					DATE_FORMAT(con.entered_from, '%H:%i') AS entered_hour,
					DATE_FORMAT(con.entered_from, '%d') AS entered_day,
					DATE_FORMAT(con.entered_from, '%m') AS entered_month,
					DATE_FORMAT(con.entered_from, '%Y') AS entered_year,
					con.period_in_month,
					con.reaction_time_normal,
					con.reaction_time_difficult,
					con.single_liability,
					con.year_liability,
					con.is_invoice,
					con.pay_cash,
					con.pay_bank,
					con.technics_type,
					con.technics_price,
					con.build_type,
					con.contract_type,
					cmc.fast_order_price,
					cmc.expres_order_price,
					cs_panic_kbd.price AS panic_kbd_price,
					cs_panic_kbd.panic_count AS panic_kbd_count,
					cs_panic_stat.panic_count AS panic_stat_count,
					cs_panic_stat.price AS panic_stat_price,
					cs_panic_radio.panic_count AS panic_radio_count,
					cs_panic_radio.price AS panic_radio_price,
					cmc.price_telepol_vest,
					con.info_operativ,
					con.info_schet,
					con.info_tehnics,
					con.count_detectors,
					CONCAT(CONCAT_WS(' ', p_entered.fname, p_entered.mname, p_entered.lname), ' [', DATE_FORMAT(con.entered_time, '%d.%m.%Yг.'), ']') AS entered_user,
					CONCAT(CONCAT_WS(' ', p_updated.fname, p_updated.mname, p_updated.lname), ' [', DATE_FORMAT(con.updated_time, '%d.%m.%Yг.'), ']') AS updated_user
				FROM contracts con
				LEFT JOIN {$db_name_sod}.offices o ON o.id = con.id_office 
				LEFT JOIN contract_month_charges cmc ON cmc.type = con.contract_type
				LEFT JOIN contracts_services cs_panic_kbd   ON cs_panic_kbd.id_contract   = con.id AND cs_panic_kbd.service_type   = 'panic_kbd' 
				LEFT JOIN contracts_services cs_panic_stat  ON cs_panic_stat.id_contract  = con.id AND cs_panic_stat.service_type  = 'panic_stat' 
				LEFT JOIN contracts_services cs_panic_radio ON cs_panic_radio.id_contract = con.id AND cs_panic_radio.service_type = 'panic_radio'
				LEFT JOIN {$db_name_personnel}.personnel p_entered ON p_entered.id = con.entered_user
				LEFT JOIN {$db_name_personnel}.personnel p_updated ON p_updated.id = con.updated_user
				WHERE con.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getInfoForPersonCard( $nID ) {
			
			global $db_name_personnel;
			
			$sQuery = "
				SELECT 
					c.id,
					c.contract_num,
					DATE_FORMAT(c.contract_date, '%d.%m.%Y') AS contract_date,
					CONCAT(p.fname,' ',p.lname) AS rs_name,
					p.mobile AS rs_mobile
				FROM contracts c
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = c.id_rs
				WHERE c.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		
		public function getContractByObj( $nID ) {
			
			global $db_name_personnel;
			
			$sQuery = "
				SELECT
					con.id,
					con.rs_name,
					con.rs_code,
					con.contract_num,
					DATE_FORMAT(con.contract_date, '%d') AS day,
					DATE_FORMAT(con.contract_date, '%m') AS month,
					DATE_FORMAT(con.contract_date, '%Y') AS year,
					con.client_is_company,
					con.client_name,
					con.client_address,
					con.client_dn,
					con.client_bul,
					con.client_mol,
					con.client_egn,
					con.client_phone,
					con.client_email,
					con.obj_region,
					con.obj_name,
					con.obj_address,
					con.obj_phone,
					con.id_obj,
					DATE_FORMAT(con.entered_from, '%d') AS entered_day,
					DATE_FORMAT(con.entered_from, '%m') AS entered_month,
					DATE_FORMAT(con.entered_from, '%Y') AS entered_year,
					con.period_in_month,
					con.reaction_time_normal,
					con.reaction_time_difficult,
					con.single_liability,
					con.year_liability,
					con.is_invoice,
					con.pay_cash,
					con.pay_bank,
					con.technics_type,
					con.technics_price,
					con.build_type,
					con.contract_type,
					con.info_schet,
					con.info_tehnics,
					con.count_detectors
				FROM contracts con
				WHERE con.id_obj = {$nID}
					AND con.to_arc = 0
					AND con.contract_status = 'validated'
			";

			return $this->selectOnce($sQuery);
		}
				
		public function getNumByID( $nID ) {
			$sQuery = "
				SELECT
					contract_num
				FROM contracts
				WHERE id = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getHistoryReport( DBResponse $oResponse, $aData ) {
			global  $db_name_personnel, $db_name_finance, $db_name_sod;
			
			$nIDObject	= $aData; 
			
			$sQuery = "	
				SELECT SQL_CALC_FOUND_ROWS
					CASE con.rs_name
						WHEN '' THEN con.id	
						ELSE    CONCAT(con.id,',',p.id)
					END AS _id,
					con.id_obj AS id,
					con.contract_num,
					DATE_FORMAT(con.contract_date, '%d.%m.%Y') AS contract_date_,
					con.last_build_time,
					DATE_FORMAT(con.last_build_time, '%d.%m.%Y %H:%i:%s') AS last_build_time,
					con.rs_name,
					con.contract_status,
					CONCAT(CONCAT_WS(' ', p.fname, p.mname, p.lname), ' [', DATE_FORMAT(con.updated_time, '%d.%m.%Yг.'), ']') AS updated_user
				FROM contracts con
				LEFT JOIN {$db_name_personnel}.personnel p ON con.updated_user = p.id
				WHERE 1
					AND con.id_obj = '{$nIDObject}'
					AND con.to_arc = 1
			";
						
			$this->getResult($sQuery, 'contract_date_', DBAPI_SORT_DESC, $oResponse);
			
			$oResponse->setField("contract_num", "Номер", "Сортирай по номер");
			$oResponse->setField("contract_date_", "Дата на договора", "Сортирай по дата");
			$oResponse->setField("rs_name", "Рекламен сътрудник", "Сортирай по рекламен сътрудник");
			$oResponse->setField("updated_user", "Последно редактирал", "Сортирай по Последно редактирал");
			
			$oResponse->setFieldLink('contract_num', 'openContract');
		}
		
		public function getIDbyOBJ( $nID ) {
			$sQuery = "
				SELECT
					id
				FROM contracts
				WHERE id_obj = {$nID}
					AND to_arc = 0
			";

			return $this->selectOne($sQuery);
		}	
		
		public function getTechPrice( $nID ) {
			$sQuery = "
				SELECT
					IF (technics_type = 'buy', technics_price, 0) as price
				FROM contracts
				WHERE id = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}			
		
		public function getContractByID( $nID ) {
			
			global $db_name_personnel;
			
			$sQuery = "
				SELECT
					con.rs_name,
					con.rs_code,
					con.contract_num,
					DATE_FORMAT(con.contract_date, '%d.%m.%Y') AS contract_date,
					DATE_FORMAT(con.contract_date, '%d') AS day,
					DATE_FORMAT(con.contract_date, '%m') AS month,
					DATE_FORMAT(con.contract_date, '%Y') AS year,
					con.client_is_company,
					con.client_name,
					con.client_address,
					con.client_dn,
					con.client_bul,
					con.client_mol,
					con.client_egn,
					con.client_phone,
					con.client_email,
					con.obj_region,
					con.obj_name,
					con.obj_address,
					con.obj_phone,
					con.obj_distance,
					con.id_obj,
					DATE_FORMAT(con.entered_from, '%d') AS entered_day,
					DATE_FORMAT(con.entered_from, '%m') AS entered_month,
					DATE_FORMAT(con.entered_from, '%Y') AS entered_year,
					con.period_in_month,
					con.reaction_time_normal,
					con.reaction_time_difficult,
					con.single_liability,
					con.year_liability,
					con.is_invoice,
					con.pay_cash,
					con.pay_bank,
					con.technics_type,
					con.technics_price,
					con.build_type,
					con.contract_type,
					con.info_schet,
					con.info_tehnics,
					con.count_detectors
				FROM contracts con
				WHERE con.id = {$nID}
					AND con.to_arc = 0
					
			";
			
			//AND con.contract_status = 'validated'
			//APILog::Log(0, $sQuery);
			return $this->selectOnce($sQuery);
		}
		
	}
?>