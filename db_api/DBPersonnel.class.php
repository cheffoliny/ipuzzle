<?php

	class DBPersonnel 
		extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			//$db_personnel->debug=true;
			parent::__construct($db_personnel, "personnel");
		}
		
		public function getPersonnelByCode( $nCode )
		{
			if( empty( $nCode ) || !is_numeric( $nCode ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT * 
				FROM personnel 
				WHERE to_arc = 0
					AND status = 'active'
					AND code = {$nCode} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getCityName($nIDPerson) {
			
			global $db_name_sod;
			
			$sQuery = "
				SELECT
					c.name
				FROM personnel.personnel p
				LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office 
				LEFT JOIN {$db_name_sod}.cities c ON c.id = o.address_city
				WHERE p.id = {$nIDPerson}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getPersonnelByOffice( $nIDFirm, $nIDOffice, $sName, $nLimit )
		{
			global $db_name_sod;
//			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
//			{
//				if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
//				{
//					$oOffices = new DBOffices();
//					$aOffices = $oOffices->getOffices2();
//					if( !empty( $aOffices ) )$nIDOffice = $aOffices[0]['id'];
//					else $nIDOffice = 0;
//				}
//			}
//			else
//			{
//				if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
//				{
//					$oOffices = new DBOffices();
//					$aOffices = $oOffices->getOfficesByFirm( $nIDFirm );
//					if( !empty( $aOffices ) )$nIDOffice = $aOffices[0]['id'];
//					else $nIDOffice = 0;
//				}
//			}
//			if( !is_numeric( $nIDOffice ) )
//			{
//				$nIDOffice = 0;
//			}
//			
//			$sQuery = "
//				SELECT
//					id,
//					CONCAT_WS( ' ', fname, mname, lname ) as name
//				FROM personnel 
//				WHERE to_arc = 0
//				";
//			
//			if( !empty( $nIDOffice ) )$sQuery .= " AND id_office = {$nIDOffice} ";
//			
//			$sQuery .= " ORDER BY name ";
//			
//			return $this->select( $sQuery );
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT 
					p.id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as name
				FROM personnel p
				LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
				LEFT JOIN {$db_name_sod}.firms f ON o.id_firm = f.id
				WHERE p.to_arc = 0 AND o.to_arc = 0 AND f.to_arc = 0
					";
			
			if( !empty( $nIDOffice ) )$sQuery .= " AND p.id_office = {$nIDOffice} ";
			if( !empty( $nIDFirm ) )$sQuery .= " AND f.id = {$nIDFirm} ";
			
			$sQuery .= "
				HAVING 1
				";
			
			if( !empty( $sName ) )
				$sQuery .= sprintf( "AND 
									(
											name LIKE '%%%s%%'
											
							  		) ", addslashes( $sName ) );
			
			$sQuery .= "ORDER BY p.fname\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			return $this->select( $sQuery );
		}
		
		public function getByID( $nID ) {
			if ( empty( $nID ) || !is_numeric( $nID ) ) {
				//throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				return array();
			}
			
			$sQuery = "
				SELECT * 
				FROM personnel 
				WHERE to_arc = 0
				AND id = {$nID} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getPersonnelNames( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				//throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				return array();
			
			$sQuery = "
				SELECT
					CONCAT_WS( ' ', fname, mname, lname ) AS names
				FROM personnel 
				WHERE to_arc = 0
				AND id = {$nID} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getPersonnelNames2( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				return '';
			
			$sQuery = "
				SELECT
					CONCAT_WS( ' ', fname, mname, lname ) AS names
				FROM personnel 
				WHERE to_arc = 0
				AND id = {$nID} 
				LIMIT 1
				";
			
			return $this->selectOne( $sQuery );
		}
		
		public function getPersonnelOffice( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				//throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				return array();
			
			$sQuery = "
				SELECT
					id_office
				FROM personnel 
				WHERE to_arc = 0
				AND id = {$nID} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getPersonnelByNames( $sName )
		{
			if( empty( $sName ) ) return array();
				
			$sQuery = "
				SELECT * 
				FROM personnel 
				WHERE to_arc = 0
					AND status = 'active'
					AND CONCAT_WS(' ', fname, mname, lname) = {$this->oDB->Quote( $sName )}
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		function getPersonNameByName( $sName, $nLimit )
		{
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT 
					p.id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as name
				FROM personnel p
				WHERE p.to_arc = 0
				HAVING 1
				";
			
			if( !empty( $sName ) )
				$sQuery .= sprintf("AND 
									(
											name LIKE '%%%s%%'
											
							  		) ", addslashes( $sName ) );
				
			$sQuery .= "ORDER BY p.fname\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			//print $sQuery;
			return $this->select( $sQuery );
		}
		
		public function getPatrulByOffice( $nIDOffice ,$sBusyPersons) {
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
				global $db_name_auto;
				
			$sQuery = "
				SELECT 
					p.id,
					CONCAT_WS(' ', p.fname, p.lname) AS name
				FROM personnel p
				LEFT JOIN positions ps ON ps.id = p.id_position
				WHERE p.to_arc = 0
					AND p.id_office = {$nIDOffice} 
					AND ps.function = 'patrul'		
			";
			
			if(!empty($sBusyPersons))
			{
				$sQuery.= "AND p.id NOT IN ($sBusyPersons)";
			}
		
			return $this->selectAssoc( $sQuery );
		}
		public function getByIDs( $sID) {
				
			$sQuery = "
				SELECT 
					p.id,
					CONCAT_WS(' ', p.fname, p.lname) AS name
				FROM personnel p

				WHERE p.id IN ($sID)
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getTechnicsByOffice( $nIDOffice ) {
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT 
					p.id,
					CONCAT_WS(' ', p.fname, p.lname) AS name
				FROM personnel p
				LEFT JOIN positions ps ON ps.id = p.id_position
				WHERE p.to_arc = 0
					AND p.id_office = {$nIDOffice} 
					AND ps.function = 'technic'
			";
		
			return $this->selectAssoc( $sQuery );
		}
		
		public function getTechnicsByOffice2( $nIDOffice ) {
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT 
					p.id,
					CONCAT_WS(' ', p.fname, p.lname) AS name
				FROM personnel p
				LEFT JOIN positions ps ON ps.id = p.id_position
				WHERE p.to_arc = 0
					AND p.id_office = {$nIDOffice} 
					AND ps.function = 'technic'
			";
		
			return $this->select( $sQuery );
		}

		public function getPatrulPosition( ) {

			$sQuery = "
				SELECT 
					p.id
				FROM positions p
				WHERE p.to_arc = 0
					AND p.function = 'patrul'
				LIMIT 1
			";
		
			return $this->selectOnce( $sQuery );
		}
		
		public function getReport( DBResponse $oResponse ) {
			$nIDObject = Params::get("nID");
			
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('object_personnel_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$edit_rights = true;
				}
			}				
			
			if( !empty( $nID ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT 
					p.id,
					p.code,
					CONCAT_WS(' ', p.fname, p.mname, p.lname ) as name,
					CONCAT(
						CONCAT_WS(' ', up.fname, up.mname, up.lname), 
						' [', 
						DATE_FORMAT(p.updated_time, '%d.%m.%Y %H:%i:%s'), 
						']'
						) AS updated_user					
				FROM personnel p
				LEFT JOIN personnel up ON p.updated_user = up.id
				WHERE p.id_region_object = {$nIDObject} 
					AND p.to_arc = 0
					AND p.status = 'active'
				";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("code", "Код", "Сортирай по код");
			$oResponse->setField("name", "Име", "Сортирай по име");
			$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
			
			if ( $edit_rights ) {
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deletePerson', '');
				$oResponse->setFieldLink("code", "openPerson");
				$oResponse->setFieldLink("name", "openPerson");
			}
		}
		
		function detachPersonFromObject( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$aData = array();
			$aData['id'] = $nIDPerson;
			$aData['id_region_object'] = 0;
			
			$this->update( $aData );
		}
		
		function attachPersonToObject( $nIDPerson, $nIDObject ) {
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$aData = array();
			$aData['id'] = $nIDPerson;
			$aData['id_region_object'] = $nIDObject;
			
			$this->update( $aData );
		}
		
		public function getPersonObject( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return 0;
			
			$sQuery = "
				SELECT
					id_region_object AS id_object
				FROM
					personnel
				WHERE
					to_arc = 0
					AND id = {$nIDPerson}
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['id_object'] ) )
			{
				return $aData['id_object'];
			}
			else return 0;
		}
		
		public function getTechniciansAssoc( $nIDOffice )
		{
			if( !empty( $nIDOffice ) && !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT 
					p.id as _id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name, 
					p.*
				FROM personnel p
				LEFT JOIN positions po ON p.id_position = po.id
				WHERE p.to_arc = 0
				AND po.position_function = 'technic'
				";
			
			if( !empty( $nIDOffice ) )
				$sQuery .= "AND p.id_office = {$nIDOffice} ";
			
			return $this->selectAssoc( $sQuery );
		}
		public function getTechniciansAssoc2( $nIDOffices )
		{			
			$sQuery = "
				SELECT 
					p.id as _id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name, 
					p.*
				FROM personnel p
				LEFT JOIN positions po ON p.id_position = po.id
				WHERE 1
					AND p.status = 'active'
					AND po.position_function = 'technic'
				";
		
			
			if( !empty( $nIDOffices ) )
				$sQuery .= "AND p.id_office IN ( {$nIDOffices} ) ";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getTechnicsByOfficeRest( $aData ) {

			if ( empty($aData['office']) || !is_numeric($aData['office']) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
			
			$type = Params::get("all", 0);
			
			$where = empty($type) ? "AND (ps.function = 'technic' OR p.id = {$aData['current']} )\n" : "";
								
			$sQuery = "
				SELECT 
					p.id,
					CONCAT_WS(' ', p.fname, p.lname) AS name
				FROM personnel p
				LEFT JOIN positions ps ON ps.id = p.id_position
				WHERE p.to_arc = 0
					AND p.status = 'active'
					AND ( p.id_office = {$aData['office']} OR p.id = {$aData['current']} )
					
				".$where;
			
			if ( !empty($aData['persons']) ) {
				$sQuery .= " AND p.id NOT IN ({$aData['persons']}) ";
			}			
			
//			APILog::Log(0, $sQuery);
			return $this->selectAssoc( $sQuery );
		}
		
		public function getPersonnelsByIDOffice($nID)
		{
			$sQuery = "
				SELECT
					id,
					CONCAT_WS( ' ', fname, mname, lname ) as name
				FROM personnel 
				WHERE 1
					AND to_arc = 0 
					AND status = 'active'
					AND id_office = {$nID}
				";	
			return $this->selectAssoc( $sQuery );
		}
		
		public function getPersonnelsByIDOffice2($nID)
		{
			$sQuery = "
				SELECT
					id,
					code,
					CONCAT_WS( ' ', fname, mname, lname ) as name
				FROM personnel 
				WHERE
					status = 'active'
					AND to_arc = 0
			";
			
			if( $nID )
			{
				$sQuery .= "
					AND id_office = {$nID}
				";
			}
			
			$sQuery .= "
				ORDER BY name
			";
			return $this->select( $sQuery );
		}
		
		public function getPersonnelsByIDOffice3( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )
				return array();
			
			$sQuery = "
				SELECT
					id,
					CONCAT_WS( ' ', fname, mname, lname ) as name
				FROM personnel 
				WHERE 1
					AND to_arc = 0
					AND status = 'active'
					AND id_office = {$nID}
				ORDER BY name
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		/**
		 * Връща подходящи данни за Flex Combobox.
		 *
		 * @param int $nID
		 * @param int $nIDExpelPerson ( Пропускане на служител )
		 * @return array
		 */
		public function getPersonnelsByIDOffice4( $nID, $nIDExpelPerson = 0 )
		{
			$sQuery = "
				SELECT
					id AS id,
					CONCAT_WS( ' ', fname, mname, lname ) as label
				FROM personnel
				WHERE
					to_arc = 0
					AND status = 'active'
			";
			
			if( !empty( $nIDExpelPerson ) )
			{
				$sQuery .= "
					AND id != {$nIDExpelPerson}
				";
			}
			
			if( $nID )
			{
				$sQuery .= "
					AND id_office = {$nID}
				";
			}
			
			$sQuery .= "
				ORDER BY label
			";	
			return $this->select( $sQuery );
		}
		
		public function getTrialByID( $nID ) {
			
			$sQuery = "
				SELECT
					id,
					id_person,
					type_salary,
					rate_reward,
					trial_to
				FROM person_contract 
				WHERE to_arc = 0 
					AND id_person IN ({$nID})
			";	
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getPersons( $nIDFirm, $nIDOffice) {
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					p.id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name,
					CONCAT('[',f.name,'] ',o.name) AS office_name
				FROM personnel p
				LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				WHERE 1
					AND p.status = 'active'
			";
			
			if(!empty($nIDOffice)) {
				$sQuery .= " AND p.id_office = {$nIDOffice}";
			} else {
				$sQuery .= " AND o.id_firm = {$nIDFirm}";
			}
			
			$sQuery .= " ORDER BY p.fname";
			
			return $this->select($sQuery);			
		}
		
		public function getTechnics($nIDFirm, $nIDOffice) {
			
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					p.id as _id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name 
				FROM personnel p
				LEFT JOIN {$db_name_sod}.offices off ON off.id = p.id_office
				LEFT JOIN positions po ON p.id_position = po.id
				WHERE 1 
					AND p.status = 'active'
					AND po.position_function = 'technic'
				";
		
			
			if( !empty( $nIDOffice ) )
				$sQuery .= "AND p.id_office = {$nIDOffice} ";
			else 
				$sQuery .= "AND off.id_firm = {$nIDFirm} ";
				
			return $this->selectAssoc( $sQuery );
			
		}
		public function getPersons2($nIDFirm, $nIDOffice) {
			
			global $db_name_sod;
			
			$sQuery = "
				SELECT 
					p.id as _id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name 
				FROM personnel p
				LEFT JOIN {$db_name_sod}.offices off ON off.id = p.id_office
				WHERE 1 
					AND p.status = 'active'
				";
		
			
			if( !empty( $nIDOffice ) )
				$sQuery .= "AND p.id_office = {$nIDOffice} ";
			else 
			 	$sQuery .= "AND off.id_firm = {$nIDFirm} ";
				
			return $this->selectAssoc( $sQuery );
			
		}
		
		public function getPersonContractPDFData( $nIDPerson )
		{
			global $db_name_sod;
			
			if( empty( $nIDPerson ) && !is_numeric( $nIDPerson ) )
				return array();
			
			$sQuery = "
					SELECT
						fir.id																		AS nFirmID,
						fir.jur_name 																AS sFirmName,
						fir.idn																		AS nFirmIDN,
						fir.address																	AS sFirmAddress,
						fir.jur_mol																	AS sFirmMOL,
						CONCAT_WS( ' ', per.fname, per.mname, per.lname ) 							AS sPersonName,
						per.egn																		AS nPersonEGN,
						CONCAT(
							per.addr_city,
							IF
							(
								( ( per.address IS NOT NULL ) AND ( per.address != '' ) ),
								CONCAT( ', ', per.address ),
								''
							),
							IF
							(
								( ( per.addr_num IS NOT NULL ) AND ( per.addr_num != '' ) ),
								CONCAT( ', ', per.addr_num ),
								''
							),
							IF
							(
								( ( per.addr_floor IS NOT NULL ) AND ( per.addr_floor != '' ) ),
								CONCAT( ', ет. ', per.addr_floor ),
								''
							),
							IF
							(
								( ( per.addr_app IS NOT NULL ) AND ( per.addr_app != '' ) ),
								CONCAT( ', ап. ', per.addr_app ),
								''
							)
						)																			AS sPersonAddress,
						per.education																AS sPersonEducation,
						per.speciality																AS sPersonSpeciality,
						per.speciality_other														AS sPersonSpecialityOther,
						per.length_of_service														AS sPersonLengthOfService,
						CONCAT( fir.name, ' - ', off.name )											AS sPersonWorkPlace,
						pos.name																	AS sPersonPosition,
						pos.cipher																	AS sPersonPositionCode,
						con.min_cost																AS nPersonSalary,
						CONCAT_WS( ' ', per.fname, per.lname ) 										AS sPersonWorkName,
						per.lk_num																	AS sPersonIDNum,
						per.lk_date																	AS sPersonIDDate,
						per.lk_izdatel																AS sPersonIDCreated,
						obj.name																	AS sPersonObject
					FROM
						personnel per
					LEFT JOIN
						{$db_name_sod}.offices off ON off.id = per.id_office
					LEFT JOIN
						{$db_name_sod}.firms fir ON fir.id = off.id_firm
					LEFT JOIN
						{$db_name_sod}.objects obj ON obj.id = per.id_region_object
					LEFT JOIN
						positions_nc pos ON pos.id = per.id_position_nc
					LEFT JOIN
						person_contract con ON ( con.id_person = per.id AND con.to_arc = 0 )
					WHERE
						per.id = {$nIDPerson}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getPersonPosition( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();
			
			$sQuery = "
					SELECT
						pos.name AS position,
						pos.cipher AS cipher
					FROM personnel p
						LEFT JOIN positions pos ON pos.id = p.id_position
					WHERE 1
						AND pos.to_arc = 0
						AND p.to_arc = 0
						AND p.id = {$nIDPerson}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getPersonPositionNC( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();
			
			$sQuery = "
					SELECT
						pos.name AS position,
						pos.cipher AS cipher
					FROM
						personnel p
					LEFT JOIN
						positions_nc pos ON pos.id = p.id_position_nc
					WHERE
						pos.to_arc = 0
						AND p.to_arc = 0
						AND p.id = {$nIDPerson}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getCountPersonPositionByID ($nID) {
			$sQuery = "
				SELECT
					count(*) as count
				FROM personnel
				WHERE 1
					AND to_arc = 0
					AND id_position = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getCountPersonPositionNCByID ($nID) {
			$sQuery = "
				SELECT
					count(*) as count
				FROM personnel
				WHERE 1
					AND to_arc = 0
					AND id_position_nc = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getCountPersonnel ($aConditions) {
			global $db_name_sod;
			
				$sQuery = "
					SELECT 
						COUNT(*) as count
					FROM personnel p
					LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
					LEFT JOIN person_contract pc ON pc.id_person = p.id AND pc.to_arc = 0
					WHERE 1
						AND p.to_arc = 0
				";
				
			if (!empty($aConditions['nIDOffice'])) {
				$sQuery.=" AND p.id_office = {$aConditions['nIDOffice']}\n";
			}	
			
			if ($aConditions['nIDFirm'] == -1) $aConditions['nIDFirm'] = 0;
			
			if (!empty($aConditions['nIDFirm'])) {
				$sQuery.= " AND o.id_firm = {$aConditions['nIDFirm']}\n";
			}
			
			if (!empty($aConditions['sStatus']) && $aConditions['sStatus'] != 'all') {
				$sQuery.= " AND p.status = '{$aConditions['sStatus']}'\n";
			}
			
			if (!empty($aConditions['nIDObject'])) {
				$sQuery.= " AND p.id_region_object = {$aConditions['nIDObject']}\n";
			}
			
			if (!empty($aConditions['sName'])) {
				$aConditions['sName'] = addslashes( $aConditions['sName'] );
				$sQuery.= " AND CONCAT_WS(' ', p.fname, p.mname, p.lname) LIKE '%{$aConditions['sName']}%'\n";
			}
			
			if (!empty($aConditions['nPositions'])) {
				$sQuery.= " AND p.id_position = {$aConditions['nPositions']}\n";
			}
			
			if( !empty($aConditions['nMobile']) )
			{
				$nNum = $aConditions['nMobile'];
				$nNum.='%';
				$sQuery.= " AND ( (p.mobile like '{$nNum}') OR (p.phone like '{$nNum}') OR (p.business_phone like '{$nNum}') )\n";
			}

			return $this->selectOne($sQuery);
		}
		
		public function getPersonEducation( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return "";
			
			$sQuery = "
					SELECT
						education
					FROM personnel
					WHERE 1
						AND to_arc = 0
						AND id = {$nIDPerson}
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['education'] ) )
			{
				return $aData['education'];
			}
			else return "";
		}
		
		public function getAllPersons()
		{
			global $db_name_sod;
			
			$sQuery = "
					SELECT
						id AS _id,
						id AS id,
						CONCAT_WS( ' ', fname, mname, lname ) AS name
					FROM
						personnel
					WHERE
						status = 'active'
						AND to_arc = 0
					HAVING
						name != ''
					ORDER BY
						name
			";
			
			return $this->select( $sQuery );
		}
		
		public function isSubstituteNeeded( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return false;
			
			$sQuery = "
				SELECT
					is_substitute_needed
				FROM
					personnel
				WHERE
					to_arc = 0
					AND id = '{$nIDPerson}'
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) )return $aData['is_substitute_needed'] == 1 ? true : false;
			else return false;
		}
		
		public function getPersonByPhone( $sPhoneNumber )
		{
			$sQuery = "
				SELECT
					per.*
				FROM
					personnel per
				WHERE
					per.to_arc = 0
					AND per.status = 'active'
					AND
					(
						per.phone = '{$sPhoneNumber}'
						OR
						per.mobile = '{$sPhoneNumber}'
						OR
						per.business_phone = '{$sPhoneNumber}'
					)
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
	}
	
?>