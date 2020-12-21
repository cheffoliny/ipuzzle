<?php
	class DBServicesReceiptTree extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance, "new_services_tree");
		}
		

		/**
		 * Функцията връща писък с услугите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 * 
		 * @author Павел Петров
		 * @name getAllServiceReceipts()
		 * 
		 * @return (array) - списък с услугите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 */		
		public function getAllServiceReceipts() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT
					id,
					name
				FROM {$db_name_finance}.new_services
				WHERE to_arc = 0
				ORDER BY name
			";
			
			return $this->select($sQuery);
		}
		
		
		public function getDataByID( $nID ) {
			global $db_name_finance, $db_name_sod;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}
			
			$sQuery = "
				SELECT
					st.id,
					st.name as receipt_name,
					st.id_firm,
					st.id_office,
					st.id_object,
					st.id_service,
					o.name as object_name,
					st.price,
					st.id_earning
				FROM {$db_name_finance}.new_services_tree st
				LEFT JOIN {$db_name_sod}.objects o ON ( o.id = st.id_object AND st.id_object > 0 )
				WHERE st.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);			
		}
		
		/**
		 * Прави проверка дали има услуга, която е със зададенана основна услуга
		 * и е насочена към конкретни фирма, офис и регион.
		 *
		 * @author Павел Петров
		 * @name checkIsExist()
		 * 
		 * @param (integer) $nIDService - ID на тип-а услуга, който ще се проверява. Задължителен!
		 * @param (integer) $nIDFirm	- ID на фирма, за която е предзнаначена услугата. Не задължителен.
		 * @param (integer) $nIDOffice	- ID на офис, за която е предзнаначена услугата. Не задължителен.
		 * @param (integer) $nIDObject	- ID на обект, за която е предзнаначена услугата. Не задължителен.
		 * 
		 * @return (integer) - Връща ID-то на услугата (ако е намерена такава)
		 */
		public function checkIsExist( $nIDService, $nIDFirm = 0, $nIDOffice = 0, $nIDObject = 0 ) {
			global $db_name_finance;
			
			if ( empty($nIDService) || !is_numeric($nIDService) ) {
				return 0;
			}
			
			$sQuery = "
				SELECT
					id
				FROM {$db_name_finance}.new_services_tree 
				WHERE service_type = 'service' 
					AND id_service = {$nIDService}
					AND id_firm = {$nIDFirm}
					AND id_office = {$nIDOffice}
					AND id_object = {$nIDObject}
			";
			
			return $this->selectOne($sQuery);			
		}		
			
		public function deleteByID( $nID ) {
			global $db_name_finance;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}
			
			$this->select("UPDATE {$db_name_finance}.new_services_tree SET to_arc = 1 WHERE id = {$nID} ");
			$this->select("UPDATE {$db_name_finance}.new_services_tree SET to_arc = 1 WHERE id_service = {$nID} ");
		}
				
		public function getServiceAttributesByID( $nID ) {
			global $db_name_finance;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return array();
			}
			
			$sQuery = "
				SELECT
					sta.id,
					st.id_service,
					s.name as service_name,
					st.name as receipt_name,
					st.id_firm,
					st.id_office,
					st.id_object,
					st.price,
					st.id_earning,
					st.id_expense,
					st.id_position,
					st.office_option,
					st.price_type,
					sta.id_activity,
					sta.id_parent,
					CASE sta.service_type
						WHEN 'service' THEN s.name
						WHEN 'activity' THEN a.name
						WHEN 'operation' THEN a.name
					END as activity_name,
					
					sta.service_type as type,
					st.id_correction,
					sta.id_firm as aid_firm,
					sta.id_office as aid_office,
					sta.price as aprice,
					sta.id_earning as aid_earning,
					sta.id_expense as aid_expense,
					sta.id_position as aid_position,
					sta.office_option as aoffice_option,
					sta.price_type as aprice_type					
				FROM {$db_name_finance}.new_services_tree st
				LEFT JOIN {$db_name_finance}.new_services s ON (s.id = st.id_service)
				LEFT JOIN finance.new_services_tree sta ON (sta.id_service = st.id AND (sta.service_type = 'activity' OR sta.service_type = 'operation'))
				LEFT JOIN finance.new_activity_operation a ON (a.id = sta.id_activity AND sta.id_activity > 0 AND (sta.service_type = 'activity' OR sta.service_type = 'operation'))
					
				WHERE st.id = {$nID}
			";
			
			return $this->select($sQuery);
		}		
	}
?>