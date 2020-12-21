<?php

	class DBObjects
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, "objects");
		}	
		
		/*public function isObjectPhysicalGuard( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )return 0;
			
			$sQuery = "
				SELECT 
					o.id,
					of.is_fo
				FROM objects o
				LEFT JOIN object_functions of ON o.id_function = of.id
				WHERE o.id = {$nID}
				LIMIT 1
					";
			
			$aObject = $this->selectOnce( $sQuery );
			
			if( isset( $aObject['is_fo'] ) )return $aObject['is_fo'];
			else return 0;
		}*/
		public function isObjectPhysicalGuard( $nID ) {
			if( empty( $nID ) || !is_numeric( $nID ) )return 0;
			
			$sQuery = "
				SELECT 
					is_fo
				FROM objects 
				WHERE id = {$nID}
				LIMIT 1
			";
			
			return $this->selectOne( $sQuery );
		}
		
		/*public function getFoObjectsByOfficeAssoc( $nIDOffice )
		{
			if( !empty( $nIDOffice ) && !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT o.id as _id, o.* 
				FROM objects o
				LEFT JOIN object_functions f ON ( o.id_function = f.id AND f.to_arc = 0)
				WHERE f.is_fo = 1
				";
			
			if( !empty( $nIDOffice ) )
				$sQuery .= "AND id_office = {$nIDOffice}\n";
				
			$sQuery .= "
				ORDER BY num
				";
			
			return $this->selectAssoc( $sQuery );
		}*/

		public function getFoObjectsByOfficeAssoc( $nIDOffice ) {
			if( !empty( $nIDOffice ) && !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT o.id as _id, o.* 
				FROM objects o
				LEFT JOIN statuses s ON o.id_status = s.id
				WHERE 1 
					AND o.is_fo = 1
					AND s.play = 1
				";
			
			if( !empty( $nIDOffice ) )
				$sQuery .= " AND o.id_office = {$nIDOffice}\n";
				
			$sQuery .= "
				ORDER BY o.name
				";
			
			//APILog::Log(0, $sQuery);
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getFoObjectsByOfficeForFlexCombo( $nIDOffice )
		{
			if( !empty( $nIDOffice ) && !is_numeric( $nIDOffice ) )return array();
			
			$sQuery = "
				SELECT
					o.id AS id,
					CONCAT( '[ ', o.num, ' ] ', o.name ) AS label
				FROM
					objects o
				LEFT JOIN
					statuses s ON o.id_status = s.id
				WHERE 1
					AND o.is_fo = 1
					AND s.play = 1
				";
			
			if( !empty( $nIDOffice ) )
				$sQuery .= " AND o.id_office = {$nIDOffice}\n";
			
			$sQuery .= "
				ORDER BY o.name
			";
			
			return $this->select( $sQuery );
		}
		
		public function getObjectsByOffice( $nIDFirm, $nIDOffice, $sName, $nLimit )
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
//				SELECT * 
//				FROM objects 
//				WHERE 1
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
					ob.id, 
					ob.num, 
					ob.name
				FROM objects ob
				LEFT JOIN {$db_name_sod}.offices o ON o.id = ob.id_office
				LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
				WHERE o.to_arc = 0 AND f.to_arc = 0
				";
			
			if( !empty( $nIDOffice ) )$sQuery .= " AND ob.id_office = {$nIDOffice} ";
			if( !empty( $nIDFirm ) )$sQuery .= " AND f.id = {$nIDFirm} ";
			
			if( !empty( $sName ) )
			{
				if( is_numeric($sName) )$sQuery .= sprintf("AND ob.num = '%s'\n", addslashes( $sName ) );
				if( !is_numeric($sName) )$sQuery .= sprintf("AND ob.name LIKE '%%%s%%'\n", addslashes( $sName ) );
			}
			
			$sQuery .= "ORDER BY ob.name\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			return $this->select( $sQuery );

		}
		
		public function getByID( $nID ) {
			if ( empty($nID) || !is_numeric($nID) ) {
				//throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				return array();
			}
			
			$sQuery = "
				SELECT * 
				FROM objects 
				WHERE id = {$nID} 
				LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}

		public function getInfoByID( $nID ) {
			
			global $db_name_sod;
			
			$sQuery = "
				SELECT
					ob.*,
					DATE_FORMAT(  ob.start , '%d.%m.%Yг. %H:%i:%s') AS start_time,
					of.id_firm as id_firm,
					ofr.id_firm as id_reaction_firm,
					oft.id_firm as id_tech_firm
				FROM objects ob
				LEFT JOIN {$db_name_sod}.offices of ON of.id = ob.id_office
				LEFT JOIN {$db_name_sod}.offices ofr ON ofr.id = ob.id_reaction_office
				LEFT JOIN {$db_name_sod}.offices oft ON oft.id = ob.id_tech_office
				WHERE ob.id = {$nID}

				";
			

			return $this->selectOnce( $sQuery );
		}

//		public function getInfoByOldID( $nID ) {
//
//			global $db_name_sod;
//
//			$sQuery = "
//				SELECT
//					ob.*,
//					DATE_FORMAT(  ob.start , '%d.%m.%Yг. %H:%i:%s') AS start_time,
//					of.id_firm as id_firm,
//					ofr.id_firm as id_reaction_firm,
//					oft.id_firm as id_tech_firm
//				FROM objects ob
//				LEFT JOIN {$db_name_sod}.offices of ON of.id = ob.id_office
//				LEFT JOIN {$db_name_sod}.offices ofr ON ofr.id = ob.id_reaction_office
//				LEFT JOIN {$db_name_sod}.offices oft ON oft.id = ob.id_tech_office
//				WHERE ob.id = {$nID}
//
//				";
//
//
//			return $this->selectOnce( $sQuery );
//		}
//
		public function getObjectByName( $sName )
		{
			if( empty( $sName ) )
				return array();
				//throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT * 
				FROM objects 
				WHERE name = {$this->oDB->Quote( $sName )} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}

		public function getObjectsByName( $sName )
		{
			if( empty( $sName ) ) return array();
			
			$sQuery = "
				SELECT * 
				FROM objects 
				WHERE name = {$this->oDB->Quote( $sName )} 
				LIMIT 1
				";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getObjectsNameByName( $sName, $nLimit )
		{
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT
					obj.id,
					obj.num,
					obj.name,
					sta.name AS status
				FROM
					objects obj
				LEFT JOIN
					statuses sta ON ( sta.id = obj.id_status AND sta.to_arc = 0 )
				WHERE 1
				";
			
			if( !empty( $sName ) )
			{
				if( is_numeric($sName) )$sQuery .= sprintf("AND obj.num = '%s'\n", addslashes( $sName ) );
				if( !is_numeric($sName) )$sQuery .= sprintf("AND obj.name LIKE '%%%s%%'\n", addslashes( $sName ) );
			}
			
			$sQuery .= "ORDER BY name\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			return $this->select( $sQuery );
		}

		public function getObjectsWithoutSignals( $nTime, DBResponse $oResponse ) {
				
			if( empty( $nTime ) || !is_numeric( $nTime ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS 
					0 as checkbox,
					o.*, 
					MAX( m.time_al ) AS max_time_al,
					DATE_FORMAT( MAX( m.time_al ), '%Y.%m.%d %H:%i:%s') AS f_max_time_al
				FROM objects o
				LEFT JOIN object_statuses os ON ( o.id = os.id_obj AND os.to_arc = 0 )
				LEFT JOIN statuses s ON ( os.id_status = s.id AND s.to_arc = 0 )
				LEFT JOIN messages m ON ( o.id = m.id_obj AND m.to_arc = 0 )
				LEFT JOIN object_functions of ON ( o.id_function = of.id AND of.to_arc = 0 )
				WHERE 1
				AND s.id = 1
				AND of.is_sod = 1
				AND m.id IS NOT NULL
				GROUP BY o.id
				HAVING ( UNIX_TIMESTAMP( max_time_al ) + {$nTime} * 60 ) < UNIX_TIMESTAMP( NOW() )
				";
			
			$this->getResult($sQuery, 'num', DBAPI_SORT_ASC, $oResponse);
			
			//$oResponse->setField('checkbox');
			//$oResponse->setFieldAttributes('checkbox', array('width' => "20px"));
			//$oResponse->setFieldData('checkbox', 'input', array('type' => 'checkbox', 'exception' => ''));
			
			$oResponse->setField('num'			, "номер"		);
			$oResponse->setField('name'			, "име"			);
			$oResponse->setField('f_max_time_al', "време"		);
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$oResponse->setDataAttributes( $key, 'num', array('style' => 'text-align: right; width: 55px;') );
				$oResponse->setDataAttributes( $key, 'f_max_time_al', array('style' => 'text-align: center; width: 150px;') );
			}
		}
		
		public function getObjectsWithoutRestore( $nIDOffice, $nDays, DBResponse $oResponse )
		{
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			if( empty( $nDays ) || !is_numeric( $nDays ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS 
					0 as checkbox,
					o.*, 
					MAX( m.time_al ) AS max_time_al,
					DATE_FORMAT( MAX( m.time_al ), '%Y.%m.%d %H:%i:%s') AS f_max_time_al
				FROM objects o
				LEFT JOIN object_statuses os ON ( o.id = os.id_obj AND os.to_arc = 0 )
				LEFT JOIN statuses s ON ( os.id_status = s.id AND s.to_arc = 0 )
				LEFT JOIN messages m ON ( o.id = m.id_obj AND m.to_arc = 0 )
				LEFT JOIN signals sg ON ( m.id_sig = sg.id AND sg.to_arc = 0 )
				LEFT JOIN object_functions of ON ( o.id_function = of.id AND of.to_arc = 0 )
				WHERE 1
				AND o.id_office = {$nIDOffice}
				AND s.id = 1
				AND of.is_sod = 1
				AND m.id IS NOT NULL
				AND sg.isopenclose = 1
				GROUP BY o.id
				HAVING ( UNIX_TIMESTAMP( max_time_al ) + {$nDays} * 3600 * 24 ) < UNIX_TIMESTAMP( NOW() )
				";
			
			$this->getResult($sQuery, 'num', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField('checkbox');
			$oResponse->setFieldAttributes('checkbox', array('width' => "20px"));
			$oResponse->setFieldData('checkbox', 'input', array('type' => 'checkbox', 'exception' => ''));
			
			$oResponse->setField('num'			, "номер"		);
			$oResponse->setField('name'			, "име"			);
			$oResponse->setField('f_max_time_al', "време"		);
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
		}
		
		public function getObjectsWithoutPower( $nIDOffice, DBResponse $oResponse )
		{
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS 
					0 as checkbox,
					o.*
				FROM objects o
				LEFT JOIN object_statuses os ON ( o.id = os.id_obj AND os.to_arc = 0 )
				LEFT JOIN statuses s ON ( os.id_status = s.id AND s.to_arc = 0 )
				LEFT JOIN messages m ON ( o.id = m.id_obj AND m.to_arc = 0 )
				LEFT JOIN signals sg ON ( m.id_sig = sg.id AND sg.to_arc = 0 )
				LEFT JOIN object_functions of ON ( o.id_function = of.id AND of.to_arc = 0 )
				WHERE 1
				AND o.id_office = {$nIDOffice}
				AND s.id = 1
				AND of.is_sod = 1
				AND m.id IS NOT NULL
				AND sg.id IN (7,8,11)
				GROUP BY o.id
				HAVING COUNT( m.id ) = SUM( m.flag )
				";
			
			$this->getResult($sQuery, 'num', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField('checkbox');
			$oResponse->setFieldAttributes('checkbox', array('width' => "20px"));
			$oResponse->setFieldData('checkbox', 'input', array('type' => 'checkbox', 'exception' => ''));
			
			$oResponse->setField('num'			, "номер"		);
			$oResponse->setField('name'			, "име"			);
			
			$oResponse->setFieldLink('num', 'openObject');
			$oResponse->setFieldLink('name', 'openObject');
		}
		
		public function getObjects($aData) {
			
			$nIDFirm = isset($aData['nIDFirm']) ? $aData['nIDFirm'] : '';
			$nIDOffice = isset($aData['nIDOffice']) ? $aData['nIDOffice'] : '';
			
			$sQuery = "
				SELECT 
					ob.id,
					CONCAT('[',ob.num,'] ',ob.name) AS name
				FROM objects ob
				LEFT JOIN offices off ON off.id = ob.id_office
				WHERE 1
			";
			
			if(!empty($nIDFirm)) {
				$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
			}
			
			if(!empty($nIDOffice)) {
				$sQuery .= " AND ob.id_office = {$nIDOffice}\n";
			}
			
			if ( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
				$offices = implode(",", $_SESSION['userdata']['access_right_regions']);
				$sQuery .= " AND ob.id_office IN({$offices}) \n";
			} 
			
			return $this->selectAssoc($sQuery);
			
		}
		
		public function getNumByID($nID)
		{
			$sQuery = "
				SELECT 
					num
				FROM 
					objects
				WHERE
					id = {$nID}
			
			";
			return $this->selectOne($sQuery);	
		}
		
		public function getIdFace($nID) {
			$sQuery = "
				SELECT 
					id_face
				FROM objects
				WHERE id = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		public function setIdFace($nIDFace,$nIDObject) {
			
			$sQuery = "
			
				UPDATE
					objects
				SET id_face = {$nIDFace}
				WHERE id = {$nIDObject}
			";
			
			return $this->selectOne($sQuery);
			
		}
		
		public function getCountObjectsByTypeID ($nID) {
			$sQuery = "
				SELECT
					COUNT(*) as count
				FROM objects o
				WHERE 1
					AND o.id_objtype = {$nID}
			";
			return $this->selectOne($sQuery);
		}
		
		public function getCountObjectsByFunctionID ($nID) {
			$sQuery = "
				SELECT
					COUNT(*) as count
				FROM objects o
				WHERE 1
					AND o.id_function = {$nID}
			";
			return $this->selectOne($sQuery);
		}
		
		public function getCountObjectsByStatusID ($nID) {
			$sQuery = "
				SELECT
					COUNT(*) as count
				FROM objects o
				WHERE 1
					AND o.id_status = {$nID}
			";
			return $this->selectOne($sQuery);
		}
		
		public function getCountOfficesByID ($nID) {
			$sQuery = "
				SELECT
					COUNT(*) as count
				FROM objects
				WHERE 1
					AND (id_office = {$nID} 
					  OR id_tech_office = {$nID} 
					  OR id_reaction_office = {$nID})
					AND id_status != 4
			";
			return $this->selectOne($sQuery);
		}
		
		public function getIDByIDOldObj($nIDOldObj) {
			
			$sQuery = "
				SELECT 
					id
				FROM objects
				WHERE id_oldobj = {$nIDOldObj}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getFactorTechSupport($nIDObject) {
			
			$sQuery = "
			
				SELECT
					off.factor_tech_support,
					off.factor_tech_distance,
					ob.id_tech_office
				FROM objects ob
				LEFT JOIN offices off ON off.id = ob.id_office
				WHERE ob.id = {$nIDObject}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function getClientsReport( DBResponse $oResponse, $aParams )
		{
			global $db_name_sod;
			
			$oObjectsServices = new DBObjectServices();
			$oObjectsSingles = new DBObjectsSingles();
			
			$nID = (int) isset( $aParams['nID'] ) ? $aParams['nID'] : 0;
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						o.id,
						o.num,
						o.name,
                        IF( LENGTH( o.name ) > 35, CONCAT( SUBSTRING( o.name, 1, 35 ), '...'), o.name ) AS short_name,
						o.address,
						SUM( os.total_sum ) AS total_sum,
						DATE_FORMAT(
							MIN(
								IF
								(
									os.last_paid != '0000-00-00',
									IF
									(
										UNIX_TIMESTAMP( ( os.start_date - INTERVAL 1 MONTH ) ) > UNIX_TIMESTAMP( os.last_paid ),
										( os.start_date - INTERVAL 1 MONTH ),
										os.last_paid
									),
									os.start_date
								)
							),
							'%m.%Y'
						) AS last_paid,
						SUM( IF( osi.paid_date = '0000-00-00', osi.total_sum, 0 ) ) AS owed_singles,
						sta.payable
					FROM {$db_name_sod}.clients_objects co
					LEFT JOIN {$db_name_sod}.objects o ON o.id = co.id_object
					LEFT JOIN {$db_name_sod}.objects_services os ON ( os.id_object = o.id AND os.to_arc = 0 )
					LEFT JOIN {$db_name_sod}.objects_singles osi ON ( osi.id_object = o.id AND osi.to_arc = 0 )
					LEFT JOIN {$db_name_sod}.statuses sta ON ( sta.id = o.id_status )
					WHERE
						co.to_arc = 0
						AND co.id_client = {$nID}
					GROUP BY o.id
			";
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$nTotalSum = 0;
			foreach( $oResponse->oResult->aData as $key => $value )
			{
				//Services
				$nOwedTax = $oObjectsServices->getObjectUnpaidTaxesSum( $value['id'] );
				$nTotalSum += $nOwedTax;
				
				$oResponse->oResult->aData[$key]['owed_tax'] = $nOwedTax;
				//End Services
				
				if( $value['payable'] == "0" )
				{
					$oResponse->setRowAttributes( $value['id'], array( "style" => "color: #FF7766;" ) );
				}
			}

			$oResponse->setField( "num", 			"№", 			"Сортирай по Номер", 							NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "short_name",		"Име", 			"Сортирай по Име", 	                            NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			//$oResponse->setField( "address", 		"Адрес", 		"Сортирай по Адрес", 							NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "total_sum", 		"Абонамент", 	"Абонамент",             						NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "last_paid", 		"Падеж", 		"Сортирай", 									NULL, NULL, NULL, array( "DATA_FORMAT" => DF_ZEROLEADNUM ) );
			$oResponse->setField( "owed_tax", 		"Дължими", 		"Сортирай по Сума дължими месечни такси", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
			$oResponse->setField( "owed_singles", 	"Др.такси",     "Сортирай по сума на други дължими задължения", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_CURRENCY ) );
//			$oResponse->setField( '', 				'', 			'',                                             'delete', 'detachObject', '' );
			
			$oResponse->addTotal( "owed_tax", $nTotalSum . " лв." );
			
			$oResponse->setFieldLink( "num", "viewObject" );
			$oResponse->setFieldLink( "name", "viewObject" );
		}
		
		/******************************************************************************
		** Функцията връща ID-то на клиента, отговарящ на зададения обект nIDObject  **
		******************************************************************************/
		public function getObjectsIDClient( $nIDObject )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )
			{
				return 0;
			}
			
			$sQuery = "
					SELECT DISTINCT
						id_client
					FROM
						clients_objects
					WHERE
						to_arc = 0
						AND id_object = {$nIDObject}
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( isset( $aData['id_client'] ) )
			{
				return $aData['id_client'];
			}
			else return 0;
		}
		
		/**
		 * Намира и връща всички обекти по даден клиент, за които търсим плащане
		 * 
		 * @author Павел Петров
		 * @name getObjectsByClient
		 *
		 * @param int $nIDClient - ID на клиент, за който търсим обектите
		 * @return array - масив с данните за обектите ако има такъв, празен масив за ненамерени
		 */
		public function getObjectsByClient( $nIDClient ) {
			global $db_name_sod;
			
			$aData = array();
			
			if ( empty($nIDClient) || !is_numeric($nIDClient) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					DISTINCT o.*
				FROM {$db_name_sod}.objects o
				LEFT JOIN {$db_name_sod}.statuses s ON s.id = o.id_status 
				LEFT JOIN {$db_name_sod}.clients_objects c ON (c.id_object = o.id AND c.to_arc = 0)
				WHERE c.id_client = {$nIDClient}
					AND s.payable = 1
			";
			
			$aData = $this->select($sQuery);

			if ( !empty($aData) ) {
				return $aData;
			} else {
				return array();
			}		
		}


        public function setServiceStatus($nIDObject) {
            global $db_name_sod, $db_sod;

            if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
                return false;
            }

            $sQuery = "

				UPDATE {$db_name_sod}.objects
				SET
					service_status = 1,
					service_status_time = NOW(),
					set_service_status_user = {$_SESSION['userdata']['id_person']}
				WHERE id = {$nIDObject}
			";

            $db_sod->Execute($sQuery);
        }

        public function getServiceStatus($nIDObject) {
            global $db_name_sod, $db_sod;

            if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
                return false;
            }

            $sQuery = "
                        SELECT
                            service_status
                        FROM {$db_name_sod}.objects
                        WHERE id = {$nIDObject}
                        ";

            return $this->selectOne($sQuery);


        }

        public function closeServiceStatus($nIDObject) {
            global $db_name_sod, $db_sod;

            if ( empty($nIDObject) || !is_numeric($nIDObject) ) {
                return false;
            }

            $sQuery = "

				UPDATE {$db_name_sod}.objects
				SET
					service_status = 0,
					end_service_status = NOW(),
					end_service_status_user =  {$_SESSION['userdata']['id_person']}
				WHERE id = {$nIDObject}
			";

            $db_sod->Execute($sQuery);
        }


		public function getObjectsDistance() {
			global $db_name_sod;
				
			$sQuery = "
			SELECT
			o.id,
			o.geo_lan as object_geo_lan,
			o.geo_lat as object_geo_lat,
			off.geo_lan as reaction_office_geo_lan,
			off.geo_lat as reaction_office_geo_lat,
			off2.geo_lan as administration_office_geo_lan,
			off2.geo_lat as administration_office_geo_lat,
			off3.geo_lan as tech_office_geo_lan,
			off3.geo_lat as tech_office_geo_lat
			FROM {$db_name_sod}.objects o
			LEFT JOIN {$db_name_sod}.offices off ON ( off.id = o.id_reaction_office AND off.to_arc = 0 )
			LEFT JOIN {$db_name_sod}.offices off2 ON ( off2.id = o.id_office AND off2.to_arc = 0 )
			LEFT JOIN {$db_name_sod}.offices off3 ON ( off3.id = o.id_tech_office AND off3.to_arc = 0 )
			WHERE o.confirmed = 1
			AND o.id_status != 4
			";
				
			return $this->selectAssoc($sQuery);
		}
		
		
	}

	

	
?>