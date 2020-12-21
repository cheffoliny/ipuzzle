<?php

	class DBObjectsSingles extends DBBase2 {
		
		function __construct() {
			global $db_sod;
			parent::__construct($db_sod,'objects_singles');
		}
		
		public function getJur($nID) {
			
			$sQuery = "
				SELECT
					f.jur_name,
					f.address,
					f.idn,
					f.idn_dds,
					f.jur_mol,
					f.id_office_dds as dds
				FROM objects_singles os
				LEFT JOIN objects o ON o.id = os.id_object
				LEFT JOIN offices off ON off.id = o.id_office
				LEFT JOIN firms f ON f.id = off.id_firm
				WHERE os.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getRow($aParams) {
			
			$nIDService = isset($aParams['id_service']) ? $aParams['is_service'] : 0;
			$nIDObject = isset($aParams['id_object']) ? $aParams['id_object'] : 0;
			
			if(empty($nIDService)) {
				throw new Exception("Няма стойност на id на услуга");
			}
			if(empty($nIDObject)) {
				throw new Exception("Няма стойност за id на обект");
			}
			
			$sQuery = "
				SELECT
					*
				FROM object_singles
				WHERE to_arc = 0
					AND id_object = {$nIDObject}
					AND id_service = {$nIDService}
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getSingle($nID) {
			
			global $db_name_finance,$db_name_storage;
			
			$sQuery = "
				SELECT 
					os.id,
					os.id_object,
					obj.id_oldobj,
					IF ( LENGTH(obj.invoice_name) > 0, obj.invoice_name, obj.name ) as oname,
					IF ( os.id_office > 0, os.id_office, obj.id_office ) as id_office,
					os.id_service,
					os.service_name,
					os.quantity,
					os.single_price,
					os.total_sum,
					m.code AS measure_code,
					os.start_date
				FROM objects_singles os
				LEFT JOIN objects obj ON obj.id = os.id_object
				LEFT JOIN {$db_name_finance}.nomenclatures_services ns ON ns.id = os.id_service
				LEFT JOIN {$db_name_storage}.measures m ON m.id = ns.id_measure
				WHERE os.id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
			
		}
		
		/**
		 * По зададен обект и месец връща мсив с данните за текущи МЕСЕЧНИ задължения;
		 *
		 * @author Павел Петров
		 * @name getDutyByObject
		 * 
		 * @param int $nIDObject - ID на обекта, за който търсим задълженията;
		 * @param string $sMonth - ДО кой месец търсим - формат 0000-00-00;
		 * 
		 * @return array - подробни данни за чакащите задължения;
		 */
		public function getDutyByObject($nIDObject, $sMonth) {
			global $db_name_sod, $db_name_finance;
			
			$aMon 	= array();
			$aData	= array();
			$aDuty	= array();
			
			if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
				return array();
			}
			
			$aMon = explode("-", $sMonth);
			
			$dayTo 		= intval($aMon[2]);
			$monthTo 	= intval($aMon[1]);
			$yearTo 	= intval($aMon[0]);			

			if ( empty($sMonth) || ($sMonth == "0000-00-00") || !checkdate($monthTo, $dayTo, $yearTo) ) {
				return array();
			}			
			
			$sQuery = "
				SELECT
					os.id,
					os.start_date AS payment_date,
					os.id_office,
					r.name as region,
					r.id_firm,
					f.name as firm,
					os.id_service,
					os.service_name as name,
					IF ( char_length(o.invoice_name), o.invoice_name, o.name ) as object_name,
					os.single_price,
					os.quantity,
					os.total_sum AS payment_sum
				FROM {$db_name_sod}.objects_singles os
				LEFT JOIN {$db_name_sod}.objects o ON o.id = os.id_object
				LEFT JOIN {$db_name_sod}.offices r ON r.id = os.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
				WHERE os.to_arc = 0
					AND os.id_object = {$nIDObject}
					AND UNIX_TIMESTAMP(paid_date) = 0 
					AND id_sale_doc = 0
			";
			
			$aData = $this->select( $sQuery );			
			
			foreach ( $aData as $val ) {
				$aPayMon = array();
				$aPayMon = explode("-", $val['payment_date']);
				
				if ( !isset($aPayMon[2]) ) {
					continue;
				}
				
				$day 		= intval($aPayMon[2]);
				$month 		= intval($aPayMon[1]);
				$year 		= intval($aPayMon[0]);
				
				if ( mktime(0, 0, 0, $month, $day, $year) <= mktime(0, 0, 0, $monthTo, $dayTo, $yearTo) ) {
					$aTmp							= array();

					$aTmp['id_duty']				= $val['id'];
					$aTmp['id_object'] 				= $nIDObject;
					$aTmp['firm_region']['rcode']	= $val['id_office'];
					$aTmp['firm_region']['region']	= $val['region'];
					$aTmp['firm_region']['fcode']	= $val['id_firm'];
					$aTmp['firm_region']['firm']	= $val['firm'];							
					$aTmp['id_service'] 			= $val['id_service'];
					$aTmp['month'] 					= date("Y-m-d", mktime(0, 0, 0, $month, 1, $year));
					$aTmp['service_name'] 			= $val['name'];
					$aTmp['object_name'] 			= $val['object_name'];
					$aTmp['single_price'] 			= floatval(($val['single_price'] / 1.2));
					$aTmp['quantity'] 				= intval($val['quantity']);
					$aTmp['total_sum'] 				= floatval(($val['payment_sum'] / 1.2));
					$aTmp['payed']					= floatval(0);
					$aTmp['type']					= "single";
					$aTmp['for_payment']			= true;		
					
					$aDuty[] 						= $aTmp;			
				}
			}
			
			return $aDuty;
		}		
	}

?>