<?php

	class DBFirms
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, 'firms');
		}
		
		public function getName( $nIDFirm )
		{
			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
			{
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
				SELECT
					name
				FROM
					firms
				WHERE
					id = {$nIDFirm}
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['name'] ) )
			{
				return $aData['name'];
			}
			else
			{
				return "";
			}
		}
		
		public function getEIN( $nIDFirm )
		{
			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
			{
				throw new Exception( "Невалидно ID!", DBAPI_ERR_INVALID_PARAM );
			}
			
			$sQuery = "
				SELECT
					idn
				FROM
					firms
				WHERE
					id = {$nIDFirm}
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['idn'] ) )
			{
				return $aData['idn'];
			}
			else
			{
				return "";
			}
		}
		
		public function getFirms()
		{
			$sAccessRegions = implode( ',', $_SESSION['userdata']['access_right_regions'] );
			
			$sQuery = "
				SELECT 
					f.id,
					CONCAT(f.name, ' [', f.code, ']') AS name
				FROM firms f
					RIGHT JOIN offices o ON o.id_firm = f.id 
				WHERE 
					f.to_arc = 0
					AND o.id IN ({$sAccessRegions})
				ORDER BY f.name
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		
		public function getFirmByOffice( $nIDOffice ) {
			global $db_name_sod;
			
			$nIDOffice = is_numeric($nIDOffice) ? $nIDOffice : 0;
			
			$sQuery = "
				SELECT 
					f.id
				FROM {$db_name_sod}.firms f
				RIGHT JOIN {$db_name_sod}.offices o ON o.id_firm = f.id 
				WHERE 
					f.to_arc = 0
					AND o.id = {$nIDOffice}
				LIMIT 1
			";
			
			return $this->selectOne( $sQuery );
		}		
		
		public function getFirmsForFlexCombo()
		{
			$sAccessRegions = implode( ",", $_SESSION['userdata']['access_right_regions'] );
			
			$sQuery = "
				SELECT DISTINCT
					f.id,
					CONCAT( f.name, ' [', f.code, ']' ) AS label
				FROM firms f
				RIGHT JOIN offices o ON o.id_firm = f.id 
				WHERE 
					f.to_arc = 0
					AND o.id IN ({$sAccessRegions})
				ORDER BY label
			";
			
			return $this->select( $sQuery );
		}
		
		public function getFirms2() {
			
			$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']);
			
			$sQuery = "
				SELECT DISTINCT
					f.id,
					CONCAT(f.name, ' [', f.code, ']') AS name
				FROM firms f
				RIGHT JOIN offices o ON o.id_firm = f.id 
				WHERE 
					f.to_arc = 0
					AND o.id IN ({$sAccessRegions})
				ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
		
		public function getFirms3() 
		{	
			$sQuery = "
				SELECT 
					id,
					name
				FROM firms 
				WHERE 
					to_arc = 0
				ORDER BY name
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		
		public function getFirms4() {
		
			
			$sAccessRegions = "";
			if(isset($_SESSION['userdata']['access_right_regions'])) {
				$sAccessRegions = implode(',',$_SESSION['userdata']['access_right_regions']); 
			}
			
			$sQuery = "
				SELECT 
					f.id,
					f.name
				FROM firms f
				RIGHT JOIN offices o ON o.id_firm = f.id 
				WHERE 1
					AND f.to_arc = 0
					
			";

			if(!empty($sAccessRegions)) {
				$sQuery .= " AND o.id IN ({$sAccessRegions})\n";
			}

			$sQuery .= "ORDER BY f.name";

			return $this->selectAssoc( $sQuery );
		
		}
		
		public function getByJurName($sJurName) {
			
			$sQuery = "
				SELECT
					*
				FROM firms
				WHERE to_arc = 0 
					AND jur_name = {$this->oDB->Quote($sJurName)}
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getDeliverers() {
			
			$sQuery = "
				SELECT
					DISTINCT jur_name AS deliverer
				FROM firms 
				WHERE to_arc = 0 AND jur_name != ''
			";
			
			return $this->select($sQuery);
		}
		

		/**
		 * Функцията връща всички фирми, които са указани, че имат регион по ДДС
		 *
		 * @name getFirmsAsDeliverer
		 * @return array масив с фирмите
		 */
		public function getFirmsAsDeliverer() {
			global $db_name_sod;
			
			$sQuery = "
				SELECT DISTINCT ff.* 
				FROM {$db_name_sod}.firms f
				LEFT JOIN {$db_name_sod}.offices o on o.id = f.id_office_dds
				LEFT JOIN {$db_name_sod}.firms ff on ff.id = o.id_firm
				WHERE to_arc = 0 
					AND jur_name != ''			
			";
			
			return $this->select($sQuery);
		}

        public function getJurFirm($nIDFirm) {
            global $db_name_sod;

            $sQuery = "
				SELECT
					f.jur_name,
					f.address,
					f.idn,
					f.idn_dds,
					f.jur_mol,
					f.id_office_dds as dds
				FROM {$db_name_sod}.firms ff
				LEFT JOIN {$db_name_sod}.offices o ON (o.id = ff.id_office_dds AND o.to_arc = 0)
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				WHERE ff.id = {$nIDFirm}
				  AND f.to_arc = 0
				  AND ff.to_arc = 0
				  AND f.jur_name != ''
			";

            return $this->selectOnce($sQuery);
        }

        public function getFirstJurFirm() {
            global $db_name_sod;

            $sQuery = "
				SELECT
					f.jur_name,
					f.address,
					f.idn,
					f.idn_dds,
					f.jur_mol,
					f.id_office_dds as dds
				FROM {$db_name_sod}.firms ff
				LEFT JOIN {$db_name_sod}.offices o ON (o.id = ff.id_office_dds AND o.to_arc = 0)
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				WHERE f.to_arc = 0
				  AND ff.to_arc = 0
				  AND f.jur_name != ''
			";

            return $this->selectOnce($sQuery);
        }

		/**
		 * Функцията връща всички фирми, групирани по юридически имена
		 *
		 * @name getFirmsAsClient
		 * @return array масив с фирмите
		 */
		public function getFirmsAsClient() {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					jur_name AS name, 
					jur_name AS title, 
					address, 
					idn,
					idn_dds,
					jur_mol 
				FROM {$db_name_sod}.firms
				WHERE to_arc = 0 
					AND jur_name != ''	
				GROUP BY jur_name		
			";
			
			return $this->select($sQuery);
		}	

		/**
		 * Функцията връща всички oофиси, групирани по фирми
		 * 
		 * @author Павел Петров
		 * @name getFirmsByOffice
		 * @return array масив с фирмите
		 */
		public function getFirmsByOffice() {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					f.id as fcode, 
					f.name as firm, 
					o.id as rcode, 
					o.name as region,
					f.jur_name 
				FROM {$db_name_sod}.firms f
				RIGHT JOIN {$db_name_sod}.offices o ON o.id_firm = f.id 
				WHERE f.to_arc = 0
				ORDER BY fcode, region
			";
			
			return $this->select($sQuery);
		}	
		
		/**
		 * Функцията връща всички oофиси, групирани по фирми
		 * с функция "Всички" съответно на фирмите и за офисите
		 * 
		 * @author Павел Петров
		 * @name getFirmsByOfficeAll
		 * @return array масив с фирмите
		 */
		public function getFirmsByOfficeAll() {
			global $db_name_sod;
			
			$aData 		= array();
			$aTmp 		= array();
			$aDataFinal	= array();			

			$sQuery = "
				SELECT 
					f.id as fcode, 
					f.name as firm, 
					o.id as rcode, 
					o.name as region,
					f.jur_name 
				FROM {$db_name_sod}.firms f
				RIGHT JOIN {$db_name_sod}.offices o ON o.id_firm = f.id 
				WHERE f.to_arc = 0
				ORDER BY fcode, region
			";
			
			$aData	= $this->select($sQuery);
			
			$aDataFinal[]= array(
	            "fcode" => 0,
	            "firm" => "-= Всички =-",
	            "rcode" => 0,
	            "region" => "-= Всички =-",
	            "jur_name" => "-= Всички =-"		
			);
			
			foreach ( $aData as $aVal ) {
				if ( !isset($aTmp[$aVal['fcode']]) ) {
					$aDataFinal[]		= array(
				            "fcode" => $aVal['fcode'],
				            "firm" => $aVal['firm'],
				            "rcode" => 0,
				            "region" => "-= Всички =-",
				            "jur_name" => $aVal['jur_name']		
					);					
				} 
				
				$aDataFinal[]			= $aVal;
				$aTmp[$aVal['fcode']] 	= 1;
			}
			
			unset($aVal);
			unset($aData);
			
			return $aDataFinal;
		}			

		/**
		 * Функцията връща фирмата по ДДС, която е указана за фирмата по EIN
		 * 
		 * @author Павел Петров
		 * @name getDDSFirmByEIN
		 * 
		 * @param bigint $nEIN - EIN на фирмата, за която се търси фирмата по ДДС
		 * @return array масив с данните за фирмата
		 */
		public function getDDSFirmByEIN( $nEIN ) {
			global $db_name_sod;
			
			$sQuery = "
				SELECT ff.* 
				FROM {$db_name_sod}.firms f
				LEFT JOIN {$db_name_sod}.offices o on o.id = f.id_office_dds
				LEFT JOIN {$db_name_sod}.firms ff on ff.id = o.id_firm
				WHERE f.to_arc = 0 
					AND f.idn = '{$nEIN}'	
				LIMIT 1	
			";
			
			return $this->selectOnce($sQuery);
		}

		
		/**
		 * Функцията връща юридическо лице по зададен потребител
		 * 
		 * @author Павел Петров
		 * @name getDelivererByPerson
		 * 
		 * @param int $nIDPerson - ID на потребител
		 * 
		 * @return array масив с данните за юридическото лице
		 */
		public function getDelivererByPerson( $nIDPerson ) {
			global $db_name_sod, $db_name_personnel;
			
			if ( empty($nIDPerson) || !is_numeric($nIDPerson) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					f.jur_name,
					f.idn,
					f.idn_dds,
					f.jur_mol
				FROM {$db_name_personnel}.personnel p
				LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				WHERE p.id = {$nIDPerson}
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		
		/**
		 * Функцията връща данните за фирмата спроред зададения офис
		 * 
		 * @author Павел Петров
		 * @name getFirmByIDOffice
		 * 
		 * @param int $nIDOffice - ID на офиса
		 * 
		 * @return array масив с данните за фирмата
		 */
		public function getFirmByIDOffice( $nIDOffice ) {
			global $db_name_sod;
			
			if ( empty($nIDOffice) || !is_numeric($nIDOffice) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					f.*
				FROM {$db_name_sod}.offices o
				LEFT JOIN {$db_name_sod}.firms f ON (f.id = o.id_firm AND f.to_arc = 0)
				WHERE o.id = {$nIDOffice}
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}	
		
		
		
		/**
		 * Функцията връща ID на офиса по ДДС по зададен данъчен номер
		 * 
		 * @author Павел Петров
		 * @name getDDSOfficeByEIN
		 * 
		 * @param int $ein - данъчния номер на фирмата
		 * 
		 * @return int - ID на офиса по ДДС
		 */
		public function getDDSOfficeByEIN( $ein ) {
			global $db_name_sod;
			
			if ( empty($ein) || !is_numeric($ein) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					f.id_office_dds
				FROM {$db_name_sod}.firms f
				WHERE f.idn = '{$ein}'
				LIMIT 1
			";
			
			return $this->selectOne($sQuery);
		}				
	}
?>