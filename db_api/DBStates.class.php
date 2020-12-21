<?php
require_once('include/db_include.inc.php');
	class DBStates extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct( $db_storage, 'states' );
		}
		
		public function getNomenclaturesForObject( $nIDObject )
		{
			if( empty( $nIDObject ) || !is_numeric( $nIDObject ) )return array();
			
			$sQuery = "
					SELECT
						nom.id AS id,
						nom.last_price AS price,
						st.count AS count
					FROM states st
						LEFT JOIN nomenclatures nom ON nom.id = st.id_nomenclature
					WHERE 1
						AND st.to_arc = 0
						AND nom.to_arc = 0
						AND st.count != 0
						AND st.storage_type = 'object'
						AND st.id_storage = {$nIDObject}
						AND st.client_own = 0
			";
			
			return $this->select( $sQuery );
		}
		
		public function getNomenclaturesToArcForObject($nIDObject)
		{
			/*
			 справка необходима при Приемно Предавателните протоколи
			там трябва да се вземат дори изтритите номенклатури защото на
			обекта може да има такива
			*/
			if (empty($nIDObject) || !is_numeric($nIDObject))
				return array();
		
			$sQuery = "
			SELECT
			nom.id AS id,
			nom.last_price AS price,
			st.count AS count
			FROM states st
			LEFT JOIN nomenclatures nom ON nom.id = st.id_nomenclature
			WHERE 1
			AND st.to_arc = 0
			AND st.count != 0
			AND st.storage_type = 'object'
			AND st.id_storage = {$nIDObject}
			AND st.client_own = 0
			";
		
			return $this->select($sQuery);
		}
		
		
		public function getNomenclaturesForPerson( $nIDPerson )
		{
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();
			
			$sQuery = "
					SELECT
						nom.id AS id,
						nom.last_price AS price,
						st.count AS count
					FROM states st
						LEFT JOIN nomenclatures nom ON nom.id = st.id_nomenclature
					WHERE 1
						AND st.to_arc = 0
						AND nom.to_arc = 0
						AND st.storage_type = 'person'
						AND st.id_storage = {$nIDPerson}
			";
			
			return $this->select( $sQuery );
		}
		
		public function getCountNomenclaturesByStoragehouseID ($nID) {
			$sQuery = "
				SELECT 
					#COUNT(*) as places,
					SUM(s.count) as count
					FROM states s
					WHERE 1
					AND s.to_arc=0
					AND s.id_storage={$nID}
					AND s.storage_type='storagehouse'
					AND count != 0
			";
			return $this->selectOnce($sQuery);
		}
		
		public function checkNomenclature( $nIDNomenclature, $sStorageType, $nIDStorage )
		{
			if( empty( $nIDNomenclature ) || !is_numeric( $nIDNomenclature ) )
				throw new Exception( 'Няма такава Номенклатура!', DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $sStorageType ) )
				return array();
			
			if( empty( $nIDStorage ) || !is_numeric( $nIDStorage ) )
				return array();
			
			//Как е написана бройката в базата. По подразбиране, "бр."
			$oMeasures = new DBMeasures();
			//$oNomenclatures = new DBNomenclatures();
			//$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening( $sDefaultCountMeasure );
			
			$sQuery = "
					SELECT
						IF
						(
							n.unit = '{$sDefaultCountMeasure}',
							CONCAT( ROUND( s.count ), ' ', n.unit ),
							CONCAT( s.count, ' ', n.unit )
						) AS state_count,
						s.count AS count_float
					FROM states s
						LEFT JOIN nomenclatures n ON n.id = s.id_nomenclature
					WHERE 1
						AND s.to_arc = 0
						AND n.to_arc = 0
						AND s.id_nomenclature = {$nIDNomenclature}
						AND s.storage_type = '{$sStorageType}'
						AND s.id_storage = {$nIDStorage}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function transmitNomenclatures( $aParams, $aData, DBResponse $oResponse ) {
			$sQuery = "
					SELECT *
					FROM ppp_elements
					WHERE id_ppp = {$aParams['nID']} AND to_arc = 0 AND count > 0
			";
			
			$aElements = $this->select( $sQuery );
			
			if( empty( $aElements ) )
				throw new Exception( "Няма добавени номенклатури!" , DBAPI_ERR_INVALID_PARAM );
			
			$nTransmitNomenclatures = true;
			$nCountIn = true;
			if( $aParams['sSendType'] == 'client' )$nTransmitNomenclatures = false;
			if ($aParams['sReceiveType'] == 'client') $nCountIn = false;
			
			if( empty( $aParams['nIDSourceName'] ) )
			{
				switch( $aParams['sSendType'] )
				{
					case 'person':
						$oPersonnel = new DBPersonnel();
						$aSource = $oPersonnel->getPersonnelByNames( $aParams['sSourceName'] );
						if( !empty( $aSource ) )$nIDSource = $aSource['id'];
					break;
					case 'storagehouse':
						$oStoragehouses = new DBStoragehouses();
						$aSource = $oStoragehouses->getStoragehouseByName( $aParams['sSourceName'] );
						if( !empty( $aSource ) )$nIDSource = $aSource['id'];
					break;
					case 'object':
						$oObjects = new DBObjects();
						$aParams['sSourceName'] = $this->removeNumber( $aParams['sSourceName'] );
						$aSource = $oObjects->getObjectsByName( $aParams['sSourceName'] );
						if( !empty( $aSource ) )$nIDSource = $aSource['id'];
					break;
					case 'client':
						$oClients = new DBClients();
						$aSource = $oClients->getClientByName( $aParams['sSourceName'] );
						if( !empty( $aSource ) )$nIDSource = $aSource['id'];
					break;
					
					default: break;
				}
			}
			else
			{
				$nIDSource = $aParams['nIDSourceName'];
			}
			
			if( empty( $aParams['nIDDestName'] ) )
			{
				switch( $aParams['sReceiveType'] )
				{
					case 'person':
						$oPersonnel = new DBPersonnel();
						$aDest = $oPersonnel->getPersonnelByNames( $aParams['sDestName'] );
						if( !empty( $aDest ) )$nIDDest = $aDest['id'];
					break;
					case 'storagehouse':
						$oStoragehouses = new DBStoragehouses();
						$aDest = $oStoragehouses->getStoragehouseByName( $aParams['sDestName'] );
						if( !empty( $aDest ) )$nIDDest = $aDest['id'];
					break;
					case 'object':
						$oObjects = new DBObjects();
						$aParams['sDestName'] = $this->removeNumber( $aParams['sDestName'] );
						$aDest = $oObjects->getObjectsByName( $aParams['sDestName'] );
						if( !empty( $aDest ) )$nIDDest = $aDest['id'];
					break;
					case 'client':
						$oClients = new DBClients();
						$aDest = $oClients->getClientByName( $aParams['sDestName'] );
						if( !empty( $aDest ) )$nIDDest = $aDest['id'];
					break;
					
					default: break;
				}
			}
			else
			{
				$nIDDest = $aParams['nIDDestName'];
			}
			
			$oStoragehouses = new DBStoragehouses();
			if ($aParams['sSendType'] == "storagehouse") {
				$aType = $oStoragehouses->getTypeByID($nIDSource);
				if (isset($aType['type']) && $aType['type'] == "virtual") {
					$nTransmitNomenclatures = false;
				}
			}
			
			if ($aParams['sReceiveType'] == "storagehouse") {
				$aType = $oStoragehouses->getTypeByID($nIDDest);
				if (isset($aType['type']) && $aType['type'] == "virtual") {
					$nCountIn = false;
				}
			}
			
			//Check Nomenclatures
			$sErrorLog = "";
			if( $nTransmitNomenclatures ) {
				foreach( $aElements as $aElement ) {
					$sQuery = "
							SELECT
								*
							FROM states
							WHERE 1
								AND id_nomenclature = {$aElement['id_nomenclature']}
								AND id_storage = {$nIDSource}
								AND storage_type = '{$aParams['sSendType']}'
								#AND client_own = '{$aElement['client_own']}'
								AND to_arc = 0
							LIMIT 1
					";
					
					$aState = $this->selectOnce( $sQuery );
					
					if( empty( $aState ) ) {
						$aNomenclature = $this->selectOnce( "SELECT name FROM nomenclatures WHERE id = {$aElement['id_nomenclature']} LIMIT 1" );
						$sNomenclature = isset( $aNomenclature['name'] ) ? $aNomenclature['name'] : "";
						$sErrorLog .= "Номенклатура {$sNomenclature} не е в наличност!\n";
					} else {
						if( $aElement['count'] > $aState['count'] ) {
							$aNomenclature = $this->selectOnce( "SELECT name FROM nomenclatures WHERE id = {$aElement['id_nomenclature']} LIMIT 1" );
							$sNomenclature = isset( $aNomenclature['name'] ) ? $aNomenclature['name'] : "";
							$sErrorLog .= "От номенклатура {$sNomenclature} има в наличност {$aState['count']}!\n";
						}
					}
				}
			}
			//End Check Nomenclatures
			
			if( empty( $sErrorLog ) )
			{
				foreach( $aElements as $aElement )
				{
					if( $nTransmitNomenclatures )
					{
						$sQuery = "
								SELECT
									*
								FROM states
								WHERE 1
									AND id_nomenclature = {$aElement['id_nomenclature']}
									AND id_storage = {$nIDSource}
									AND storage_type = '{$aParams['sSendType']}'
									AND to_arc = 0
								LIMIT 1
						";
						
						$aState = $this->selectOnce( $sQuery );
						
						$aState['count'] -= $aElement['count'];
						//Проверка за всеки случай - ако се наложи да се трият записите, които са с бройка 0.
						if( $aState['count'] < 0 )$aState['count'] = 0;
						
						$this->update( $aState );
					}
					
					if( $nCountIn )
					{
						$sQuery = "
								SELECT
									*
								FROM states
								WHERE 1
									AND id_nomenclature = {$aElement['id_nomenclature']}
									AND id_storage = {$nIDDest}
						";
						
						if( $aParams['sReceiveType'] != "client" )
						{
							$sQuery .= "
									AND storage_type = '{$aParams['sReceiveType']}'
							";
						}
						
						$sQuery .= "
									AND to_arc = 0
								LIMIT 1
						";
						
						$aState = $this->selectOnce( $sQuery );
						
						if( empty( $aState ) )
						{
							$aState = array();
							$aState['id'] = 0;
							$aState['id_nomenclature'] = $aElement['id_nomenclature'];
							$aState['count'] = $aElement['count'];
							$aState['client_own'] = $aElement['client_own'];
							$aState['id_storage'] = $nIDDest;
							$aState['storage_type'] = $aParams['sReceiveType'];
						}
						else
						{
							if( $aState['client_own'] == $aElement['client_own'] )
							{
								$aState['count'] += $aElement['count'];
							}
							else
							{
								$aState = array();
								$aState['id'] = 0;
								$aState['id_nomenclature'] = $aElement['id_nomenclature'];
								$aState['count'] = $aElement['count'];
								$aState['client_own'] = $aElement['client_own'];
								$aState['id_storage'] = $nIDDest;
								$aState['storage_type'] = $aParams['sReceiveType'];
							}
						}
						
						$this->update( $aState );
					}
				}
				
				$oPPP = new DBPPP();
				$oPPP->update( $aData );
				$oResponse->setFormElement( 'form1', 'nLoadedClosed', array( "value" => 1 ), 1 );
			}
			else
			{
				throw new Exception( $sErrorLog, DBAPI_ERR_INVALID_PARAM );
			}
		}
		
		public function getReport($aParams, DBResponse $oResponse = NULL )
		{			
			$sQuery = $this->prepareStatesQuery( 0, $aParams );
			$sQueryTotal = $this->prepareStatesQuery( 1, $aParams );
			
			$sStorageType = 'Склад / Служител / Обект';
			switch( $aParams['sStorageType'] )
			{
				case 'storagehouse': 	$sStorageType = "Склад"; 		break;
				case 'person': 			$sStorageType = "Служител"; 	break;
				case 'object': 			$sStorageType = "Обект"; 		break;
				default: break;
			}
			
			$aTotal = $this->select( $sQueryTotal ); 
			
			if( isset( $aTotal[0] ) )
			{
				$sTotalCount = '';
				if( !empty( $aTotal[0]['total_broi'] ) )$sTotalCount .= $aTotal[0]['total_broi'] . " бр.   ";
				if( !empty( $aTotal[0]['total_metri'] ) )$sTotalCount .= round( $aTotal[0]['total_metri'], 2 ) . " м.";
			}
			
			if( isset( $aParams['robot'] ) )
			{
				$nIDFilter = $aParams['schemes'];
				
				$oDBFiltersTotals = new DBFiltersTotals();
				$aFilterTotals = $oDBFiltersTotals->getFilterTotalsByIDFilter( $nIDFilter );
				
				$aTotals = array();
				
				if( in_array( 'total_count', $aFilterTotals ) )
				{
					$aTotals['count']['name'] = 'Брой';
					$aTotals['count']['value'] = $sTotalCount;
					$aTotals['count']['data_format'] = DF_STRING;
				}
				if( in_array( 'total_price', $aFilterTotals ) )
				{
					$aTotals['price']['name'] = 'Стойност';
					$aTotals['price']['value'] = $aTotal[0]['total_price'];
					$aTotals['price']['data_format'] = DF_CURRENCY;
				}
				
				return $aTotals;
			}
			else
			{
				if( isset( $aTotal[0] ) )
				{
					$oResponse->addTotal( 'count', $sTotalCount );
					$oResponse->addTotal( 'last_price', $this->mround( $aTotal[0]['total_price'] ) . ' лв.' );
				}
				$this->getResult( $sQuery, 'office, storage, nom_type', DBAPI_SORT_ASC, $oResponse );
				
				foreach( $oResponse->oResult->aData as $key => $value )
				{
					if( $sStorageType == "Обект" )$oResponse->setDataAttributes( $key, 'client_own', array( 'style' => 'text-align:right;' ) );
					
					$oResponse->setDataAttributes( $key, 'count', 		array( 'style' => 'text-align:right;' ) );
					$oResponse->setDataAttributes( $key, 'last_price', 	array( 'style' => 'text-align:right;' ) );
				}
				
				if( !empty( $aParams['schemes'] ) )
				{
					$oDBFiltersVisibleFields = new DBFiltersVisibleFields();
					
					$aVisibleFields = $oDBFiltersVisibleFields->getFieldsByIDFilter( $aParams['schemes'] );
					
					if( in_array( "field_office", $aVisibleFields ) )
					{
						$oResponse->setField( "office", "Регион", "Сортирай по регион" );
					}
					
					if( in_array( "field_storage_type", $aVisibleFields ) )
					{
						$oResponse->setField( "storage", $sStorageType, "Сортирай по {$sStorageType}" );
						
						if( $sStorageType == "Обект" )
						{
							$oResponse->setFieldLink( "storage", "openObject" );
						}
					}
					
					if( in_array( "field_nomenclature_type", $aVisibleFields ) )
					{
						$oResponse->setField( "nom_type", "Тип", "Сортирай по тип" );
					}
					
					$oResponse->setField( "nom", "Номенклатура", "Сортирай по номенклатура" );
					
					if( $sStorageType == "Обект" )
					{
						$oResponse->setField( "client_own", "Собств. на кл.", "Сортирай по собственост на клиент", 'images/confirm.gif' );
					}
					
					if( in_array( "field_count", $aVisibleFields ) )
					{
						$oResponse->setField( "count", "Количество", "Сортирай по количество" );
					}
					
					$oResponse->setField( "last_price", "Цена", "Сортирай по цена" );
				}
				else
				{
					$oResponse->setField( "office", 		"Регион", 			"Сортирай по регион" );
					$oResponse->setField( "storage", 		$sStorageType, 		"Сортирай по {$sStorageType}" );
					$oResponse->setField( "nom_type", 		"Тип", 				"Сортирай по тип" );
					$oResponse->setField( "nom", 			"Номенклатура", 	"Сортирай по номенклатура" );
					if( $sStorageType == "Обект" )
					{
						$oResponse->setField( "client_own", "Собств. на кл.", "Сортирай по собственост на клиент", 'images/confirm.gif' );
						$oResponse->setFieldLink( "storage", "openObject" );
					}
					$oResponse->setField( "count", 			"Количество", 		"Сортирай по количество" );
					$oResponse->setField( "last_price", 	"Цена", 			"Сортирай по цена" );
				}
			}
		}
		
		public function prepareStatesQuery( $Total, &$aParams )
		{
			global $db_name_personnel, $db_name_sod;
			
			if( isset( $aParams['robot'] ) )
			{
				$wh = '';
				
				$nIDFilter = $aParams['schemes'];
				
				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter( $nIDFilter );
				
				$aParams['nIDFirm'] = $aFilterParams['id_firm'];
				$aParams['nIDOffice'] = $aFilterParams['id_office'];
				$aParams['nIDStoragehouse'] = $aFilterParams['id_storage'];
				$aParams['nIDNomenclatureType'] = $aFilterParams['id_nomenclature_type'];
				$aParams['nIDNomenclature'] = $aFilterParams['id_nomenclature'];
				$aParams['sStorageType'] = $aFilterParams['storage_type'];
				$aParams['sStoragehouseType'] = $aFilterParams['storagehouse_type'];
			}
			elseif( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$off = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$wh = "AND of.id IN( {$off} ) \n";
			}
			else $wh = "";
			
			//Как е написана бройката в базата. По подразбиране, "бр."
			$oMeasures = new DBMeasures();
			//$oNomenclatures = new DBNomenclatures();
			//$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening( $sDefaultCountMeasure );
			
			//Initialize Grouping
			$Sortings = 'storage_type';
			$nIDFirm = isset( $aParams['nIDFirm'] ) ? $aParams['nIDFirm'] : 0;
			if( !empty( $nIDFirm ) )
			{
				$Sortings .= ',firm ';
			}
			//By Offices
			$nIDOffice = isset( $aParams['nIDOffice'] ) ? $aParams['nIDOffice'] : 0;
			if( !empty( $nIDOffice ) )
			{
				$Sortings .= ',office ';
			}
			//By Storagehouse
			$nIDStoragehouse = isset( $aParams['nIDStoragehouse'] ) ? $aParams['nIDStoragehouse'] : 0;
			if( !empty( $nIDStoragehouse ) )
			{
				$Sortings .= ',storage ';
			}
			//By Nomenclature Type
			$nIDNomenclatureType = isset( $aParams['nIDNomenclatureType'] ) ? $aParams['nIDNomenclatureType'] : 0;
			if( !empty( $nIDNomenclatureType ) )
			{
				$Sortings .= ',nom_type ';
			}
			//By Nomenclature
			$nIDNomenclature = isset( $aParams['nIDNomenclature'] ) ? $aParams['nIDNomenclature'] : 0;
			if( !empty( $nIDNomenclature ) )
			{
				$Sortings .= ',nom ';
			}
			
			if( $Total )
			{
				$sQuery = "
					SELECT
				";
			}
			else
			{
				$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
				";
			}
			$sQuery .= "
						CONCAT( of.id, ',', ob.id ) AS id,
						of.name AS office,
						fr.name AS firm,
						of.id AS office_id,
						fr.id AS firm_id,
						st.id_storage AS storage_id,
			";
			
			switch( $aParams['sStorageType'] )
			{
				case 'storagehouse':
					$sQuery .= "sg.name AS storage, ";
					break;
				case 'person':
					$sQuery .= "CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ) AS storage, ";
					break;
				case 'object':
					$sQuery .= "CONCAT( ob.name, ' [ ', ob.num, ' ]' ) AS storage, ";
					break;
				
				default: break;
			}
			
			$sQuery .= "
						nt.name AS nom_type,
						nt.id AS nom_type_id,
						no.name AS nom,
						no.id AS nom_id,
						IF
						(
							no.unit = '{$sDefaultCountMeasure}',
							CONCAT_WS( ' ', ROUND( st.count ), no.unit ),
							CONCAT_WS( ' ', st.count, no.unit )
						) AS count,
						CONCAT( no.last_price, ' лв.' ) AS last_price,
						st.storage_type AS storage_type,
						st.client_own
			";
			
			if( $Total )$sQuery .= ", SUM( no.last_price * st.count ) AS total_price \n
									, SUM( IF(no.unit = 'бр.',st.count,'')) AS total_broi \n
									, SUM( IF(no.unit = 'м.',st.count,'')) AS total_metri \n";
			
			$sQuery .= "
					FROM states st
						LEFT JOIN storagehouses sg ON st.id_storage = sg.id
						LEFT JOIN {$db_name_personnel}.personnel pe ON st.id_storage = pe.id
						LEFT JOIN {$db_name_sod}.objects ob ON st.id_storage = ob.id
			";
			
			switch( $aParams['sStorageType'] )
			{
				case 'storagehouse':
					$sQuery .= "LEFT JOIN {$db_name_sod}.offices of ON sg.id_office = of.id ";
					break;
				case 'person':
					$sQuery .= "LEFT JOIN {$db_name_sod}.offices of ON pe.id_office = of.id ";
					break;
				case 'object':
					$sQuery .= "LEFT JOIN {$db_name_sod}.offices of ON ob.id_office = of.id ";
					break;
				
				default: break;
			}
			
			$sQuery .= "
						LEFT JOIN {$db_name_sod}.firms fr ON of.id_firm = fr.id
						LEFT JOIN nomenclatures no ON st.id_nomenclature = no.id
						LEFT JOIN nomenclature_types nt ON no.id_type = nt.id
					WHERE 1
						AND fr.to_arc = 0
						AND of.to_arc = 0
						{$wh}
						AND nt.to_arc = 0
						AND no.to_arc = 0
						AND st.to_arc = 0
						AND count > 0
			";
			
			if( $aParams['sStorageType'] == "storagehouse" )
			{
				$sQuery .= "
							AND sg.to_arc = 0
				";
				if( !empty( $aParams['sStoragehouseType'] ) )
				{
					$sQuery .= "
								AND sg.type = '{$aParams['sStoragehouseType']}'
					";
				}
			}
			if( $aParams['sStorageType'] == "object" )
			{
				if(  $aParams['sStoragehouseTypeObj'] == 'clientown'  )
				{
					$sQuery .= "
								AND client_own > 0
					";
				}
			}
			if( $aParams['sStorageType'] == "object" )
			{
				if(  $aParams['sStoragehouseTypeObj'] == 'firmown'  )
				{
					$sQuery .= "
								AND client_own = 0
					";
				}
			}
			if( $aParams['sStorageType'] == "person" )
			{
				$sQuery .= "
							AND pe.to_arc = 0
				";
			}
			
			if( $Total )$sQuery .= " GROUP BY {$Sortings} ";
			
			//Filtering the Result
			$sQuery .= " HAVING 1 ";
			
			//By Firms
			if( !empty( $nIDFirm ) && is_numeric( $nIDFirm ) )
			{
				$sQuery .= " AND firm_id = {$nIDFirm} ";
			}
			
			//By Offices
			if( !empty( $nIDOffice ) && is_numeric( $nIDOffice ) )
			{
				$sQuery .= " AND office_id = {$nIDOffice} ";
			}
			
			//By Storagehouse
			if( !empty( $nIDStoragehouse ) && is_numeric( $nIDStoragehouse ) )
			{
				$sQuery .= " AND storage_id = {$nIDStoragehouse} ";
			}
			else
			{
				//Try to get by name
				$sStoragehouse = isset( $aParams['sStoragehouse'] ) ? $aParams['sStoragehouse'] : '';
				$sStoragehouse = addslashes( $sStoragehouse );
				if( !empty( $sStoragehouse ) )
					$sQuery .= " AND storage LIKE '{$sStoragehouse}' ";
			}
			$sQuery .= " AND storage_type = '{$aParams['sStorageType']}' ";
			
			//By Nomenclature Type
			if( !empty( $nIDNomenclatureType ) && is_numeric( $nIDNomenclatureType ) )
			{
				$sQuery .= " AND nom_type_id = {$nIDNomenclatureType} ";
			}
			
			//By Nomenclature
			if( !empty( $nIDNomenclature ) && is_numeric( $nIDNomenclature ) )
			{
				$sQuery .= " AND nom_id = {$nIDNomenclature} ";
			}
			
			return $sQuery;
		}
		
		public function removeNumber( $sName )
		{
			$sProduct = '';
			$sCount = 0;
			
			for( $i = 0; $i < strlen( $sName ); $i++ )
			{
				$sCount++;
				
				if( isset( $sName[$i + 1] ) )
					if( $sName[$i + 1] == ' ' )
						if( isset( $sName[$i + 2] ) )
							if( $sName[$i + 2] == '[' )
								break;
			}
			
			$sProduct = substr( $sName, 0, $sCount );
			return $sProduct;
		}
		
		function mround( $value )
		{
			return ceil( (string) ( $value * 100 ) ) / 100;
		}

		public function getReport2($aParams, DBResponse $oResponse = NULL) {
			$sQuery = $this->prepareStatesQuery2(0, $aParams);
			$sQueryTotal = $this->prepareStatesQuery2(1, $aParams);

			//APILog::Log("---------------",$sQuery);
			//APILog::Log("------ТОТАЛ----",$sQueryTotal);

			$sStorageType = 'Склад / Служител / Обект';
			switch ($aParams['sStorageType']) {
				case 'storagehouse': $sStorageType = "Склад";
					break;
				case 'person': $sStorageType = "Служител";
					break;
				case 'object': $sStorageType = "Обект";
					break;
				default: break;
			}

			$aTotal = $this->select($sQueryTotal);

			//APILog::Log("======  ==============",$aTotal[0]);

			if (isset($aTotal[0])) {
				$sTotalCount = '';
				if (!empty($aTotal[0]['total_broi']))
					$sTotalCount .= round($aTotal[0]['total_broi'], 2) . " бр.   ";
				if (!empty($aTotal[0]['total_metri']))
					$sTotalCount .= round($aTotal[0]['total_metri'], 3) . " м. ";
				if (!empty($aTotal[0]['total_metri2']))
					$sTotalCount .= round($aTotal[0]['total_metri2'], 3) . " m. ";
				if (!empty($aTotal[0]['total_litri']))
					$sTotalCount .= round($aTotal[0]['total_litri'], 3) . " л. ";
			}

			if (isset($aParams['robot'])) {
				$nIDFilter = $aParams['schemes'];

				$oDBFiltersTotals = new DBFiltersTotals();
				$aFilterTotals = $oDBFiltersTotals->getFilterTotalsByIDFilter($nIDFilter);

				$aTotals = array();

				if (in_array('total_count', $aFilterTotals)) {
					$aTotals['count']['name'] = 'Брой';
					$aTotals['count']['value'] = $sTotalCount;
					$aTotals['count']['data_format'] = DF_STRING;
				}
				if (in_array('total_price', $aFilterTotals)) {
					$aTotals['price']['name'] = 'Стойност';
					$aTotals['price']['value'] = $aTotal[0]['total_price'];
					$aTotals['price']['data_format'] = DF_CURRENCY;
				}

				return $aTotals;
			} else {
				if (isset($aTotal[0])) {
					$oResponse->addTotal('count', $sTotalCount);
					$oResponse->addTotal('total_price', $this->mround($aTotal[0]['total_price_sum']) . ' лв.');
				}
				$this->getResult($sQuery, 'office, storage, nom_type', DBAPI_SORT_ASC, $oResponse , NULL ,1);

				foreach ($oResponse->oResult->aData as $key => &$val) {
					$val['s2'] = $val['s1'];
				}

				foreach ($oResponse->oResult->aData as $key => $value) {
					if ($sStorageType == "Обект")
						$oResponse->setDataAttributes($key, 'client_own', array('style' => 'text-align:right;'));

					$oResponse->setDataAttributes($key, 'count', array('style' => 'text-align:right;'));
					$oResponse->setDataAttributes($key, 'last_price', array('style' => 'text-align:right;'));
				}

				if (!empty($aParams['schemes'])) {
					$oDBFiltersVisibleFields = new DBFiltersVisibleFields();

					$aVisibleFields = $oDBFiltersVisibleFields->getFieldsByIDFilter($aParams['schemes']);

					if (in_array("field_office", $aVisibleFields)) {
						$oResponse->setField("office", "Регион", "Сортирай по регион");
					}

					if (in_array("field_storage_type", $aVisibleFields)) {
						$oResponse->setField("storage", $sStorageType, "Сортирай по {$sStorageType}");

						if ($sStorageType == "Обект") {
							$oResponse->setFieldLink("storage", "openObject");
						}
					}

					if (in_array("field_nomenclature_type", $aVisibleFields)) {
						$oResponse->setField("nom_type", "Тип", "Сортирай по тип");
					}

					$oResponse->setField("nom", "Номенклатура", "Сортирай по номенклатура");

					if ($sStorageType == "Обект") {
						$oResponse->setField("s2", "Стартирал", "Обекта е стартирал на:");
						$oResponse->setField("client_own", "Собств. на кл.", "Сортирай по собственост на клиент", 'images/confirm.gif');
						$oResponse->setFieldLink("office", "openObjectMessage");
					}

					if (in_array("field_count", $aVisibleFields)) {
						$oResponse->setField("count", "Количество", "Сортирай по количество");
					}

					$oResponse->setField("last_price", "Цена", "Сортирай по цена");
				} else {
					$oResponse->setField("office", "Регион", "Сортирай по регион");
					$oResponse->setField("storage", $sStorageType, "Сортирай по {$sStorageType}");
					$oResponse->setField("nom_type", "Тип", "Сортирай по тип");
					$oResponse->setField("nom", "Номенклатура", "Сортирай по номенклатура");
					if ($sStorageType == "Обект") {
						$oResponse->setField("s2", "Стартирал", "Обекта е стартирал на:");
						$oResponse->setField("client_own", "Собств. на кл.", "Сортирай по собственост на клиент", 'images/confirm.gif');
						$oResponse->setFieldLink("office", "openObjectMessage");
						$oResponse->setFieldLink("storage", "openObject");
					}
					$oResponse->setField("count", "Количество", "Сортирай по количество");
					$oResponse->setField("last_price", "Ед. Цена", "Сортирай по Ед. Цена");
					$oResponse->setField("total_price", "Общо Цена", "Сортирай по Общо Цена" , NULL , NULL , NULL , array('DATA_FORMAT'=> DF_CURRENCY4));
				}
			}
		}

		public function prepareStatesQuery2($Total, &$aParams) {
			global $db_name_personnel, $db_name_sod , $db_name_storage;

			if (isset($aParams['robot'])) {
				$wh = '';

				$nIDFilter = $aParams['schemes'];

				$oDBFiltersParams = new DBFiltersParams();
				$aFilterParams = $oDBFiltersParams->getParamsByIDFilter($nIDFilter);

				$aParams['nIDFirm'] = $aFilterParams['id_firm'];
				$aParams['nIDOffice'] = $aFilterParams['id_office'];
				$aParams['nIDStoragehouse'] = $aFilterParams['id_storage'];
				$aParams['nIDNomenclatureType'] = $aFilterParams['id_nomenclature_type'];
				$aParams['nIDNomenclature'] = $aFilterParams['id_nomenclature'];
				$aParams['sStorageType'] = $aFilterParams['storage_type'];
				$aParams['sStoragehouseType'] = $aFilterParams['storagehouse_type'];
			} elseif ($_SESSION['userdata']['access_right_all_regions'] != 1) {
				$off = implode(",", $_SESSION['userdata']['access_right_regions']);
				$wh = "AND of.id IN( {$off} ) \n";
			}
			else
				$wh = "";

			//Как е написана бройката в базата. По подразбиране, "бр."
			$oMeasures = new DBMeasures();
			//$oNomenclatures = new DBNomenclatures();
			//$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening($sDefaultCountMeasure);

			//Initialize Grouping
			$Sortings = 'storage_type';
			$nIDFirm = isset($aParams['nIDFirm']) ? $aParams['nIDFirm'] : 0;
			if (!empty($nIDFirm)) {
				$Sortings .= ',firm ';
			}
			//By Offices
			$nIDOffice = isset($aParams['nIDOffice']) ? $aParams['nIDOffice'] : 0;
			if (!empty($nIDOffice)) {
				$Sortings .= ',office ';
			}
			//By Storagehouse
			$nIDStoragehouse = isset($aParams['nIDStoragehouse']) ? $aParams['nIDStoragehouse'] : 0;
			if (!empty($nIDStoragehouse)) {
				$Sortings .= ',storage ';
			}
			//By Nomenclature Type
			$nIDNomenclatureType = isset($aParams['nIDNomenclatureType']) ? $aParams['nIDNomenclatureType'] : 0;
			if (!empty($nIDNomenclatureType)) {
				$Sortings .= ',nom_type ';
			}
			//By Nomenclature
			$nIDNomenclature = isset($aParams['nIDNomenclature']) ? $aParams['nIDNomenclature'] : 0;
			if (!empty($nIDNomenclature)) {
				$Sortings .= ',nom ';
			}



			$sSubQuery = "
            SELECT
            ppp_e.single_price
            FROM
            {$db_name_storage}.ppp p
            LEFT JOIN {$db_name_storage}.ppp_elements ppp_e ON (ppp_e.id_ppp = p.id AND ppp_e.to_arc = 0)
            WHERE 1
            AND p.to_arc = 0
            AND p.`status` = 'confirm'
            AND p.source_type = 'client'
            AND p.dest_type = 'storagehouse'
            #AND p.id_dest = @IDSource
            #AND ppp_e.id_nomenclature = @IDNomeclature
            AND p.id_dest = st.id_storage
            AND ppp_e.id_nomenclature = no.id
            ORDER BY p.id DESC
            LIMIT 1
        ";

			if ($Total) {
				$sQuery = "
					SELECT
				";
			} else {
				$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
				";
			}

			$sQuery .= "
						CONCAT( of.id, ',', ob.id ) AS id,
						of.name AS office,
						fr.name AS firm,
						of.id AS office_id,
						fr.id AS firm_id,
						DATE_FORMAT(ob.start, '%d.%m.%Y') as s1,
						ob.start as s2,
						st.id_storage AS storage_id,
			";

			switch ($aParams['sStorageType']) {
				case 'storagehouse':
					$sQuery .= "sg.name AS storage, ";
					break;
				case 'person':
					$sQuery .= "CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ) AS storage, ";
					break;
				case 'object':
					$sQuery .= "CONCAT( '[', ob.num, '] ', ob.name ) AS storage, ";
					break;

				default: break;
			}

			$sQuery .= "
						nt.name AS nom_type,
						nt.id AS nom_type_id,
						no.name AS nom,
						no.id AS nom_id,
                        IF
						(
							no.unit = '{$sDefaultCountMeasure}',
							CONCAT_WS( ' ', ROUND( st.count ), no.unit ),
							CONCAT_WS( ' ', st.count, no.unit )
						) AS count,
						st.storage_type AS storage_type,
        ";

			//20.02.2015
			//ИСКАНЕ КОГАТО Е ИЗБРАН ОФИС ЦЕНАТА ДА Е ОТ СРЕДНАТА ДОСТАВНА ЗА ОФИСА АКО НЕ УСРЕДНЕНА ЗА ВСИЧКИ ОФИСИ
			if (!empty($nIDOffice)) {
				$sQuery .= "
                @IDSource:=st.id_storage,
                @IDNomeclature:=no.id,

                CONCAT (
                    IF(
                        (
                            {$sSubQuery}
                        )  IS NOT NULL,
                        (
                            {$sSubQuery}
                        ),
                        no.last_price
                    )
                    ,
                    ' лв. '
                )AS last_price,

                st.count * IF(
                    (
                        {$sSubQuery}
                    )  IS NOT NULL,
                    (
                        {$sSubQuery}
                    ),
                    no.last_price
                ) AS total_price,
            ";
			}
			else {
				$sQuery .= "
                @IDSource:=st.id_storage,
                @IDNomeclature:=no.id,
                CONCAT( no.last_price, ' лв.' ) AS last_price,
                st.count * no.last_price AS total_price,
            ";
			}

			$sQuery .= "
            st.client_own
        ";

			if ($Total) {

				//20.02.2015
				//ИСКАНЕ КОГАТО Е ИЗБРАН ОФИС ЦЕНАТА ДА Е ОТ СРЕДНАТА ДОСТАВНА ЗА ОФИСА АКО НЕ УСРЕДНЕНА ЗА ВСИЧКИ ОФИСИ
				if (!empty($nIDOffice)) {
					$sQuery .= ", SUM( IF(
                                        (
                                            {$sSubQuery}
                                        )  IS NOT NULL,
                                        (
                                            {$sSubQuery}
                                        ),
                                        no.last_price
                                    )
                                    * st.count
                             ) AS total_price_sum \n";
				} else {
					$sQuery .= ", SUM( no.last_price * st.count ) AS total_price_sum \n";
				}

				$sQuery .= "
                        , SUM( IF(no.unit = 'бр.',st.count,'')) AS total_broi \n
                        , SUM( IF(no.unit = 'м.',st.count,'')) AS total_metri \n
                        , SUM( IF(no.unit = 'm',st.count,'')) AS total_metri2 \n
                        , SUM( IF(no.unit = 'литър',st.count,'')) AS total_litri \n";
			}


			$sQuery .= "
					FROM states st
						LEFT JOIN storagehouses sg ON st.id_storage = sg.id
						LEFT JOIN {$db_name_personnel}.personnel pe ON st.id_storage = pe.id
						LEFT JOIN {$db_name_sod}.objects ob ON st.id_storage = ob.id
			";

			switch ($aParams['sStorageType']) {
				case 'storagehouse':
					$sQuery .= "LEFT JOIN {$db_name_sod}.offices of ON sg.id_office = of.id ";
					break;
				case 'person':
					$sQuery .= "LEFT JOIN {$db_name_sod}.offices of ON pe.id_office = of.id ";
					break;
				case 'object':
					$sQuery .= "LEFT JOIN {$db_name_sod}.offices of ON ob.id_office = of.id ";
					break;

				default: break;
			}

			$sQuery .= "
						LEFT JOIN {$db_name_sod}.firms fr ON of.id_firm = fr.id
						LEFT JOIN nomenclatures no ON st.id_nomenclature = no.id
						LEFT JOIN nomenclature_types nt ON no.id_type = nt.id
					WHERE 1
						AND fr.to_arc = 0
						AND of.to_arc = 0
						{$wh}
						AND nt.to_arc = 0
						AND no.to_arc = 0
						AND st.to_arc = 0
						AND count > 0
			";

			if ($aParams['sStorageType'] == "storagehouse") {
				$sQuery .= "
							AND sg.to_arc = 0
				";
				if (!empty($aParams['sStoragehouseType'])) {
					$sQuery .= "
								AND sg.type = '{$aParams['sStoragehouseType']}'
					";
				}
			}
			if ($aParams['sStorageType'] == "object") {
				if ($aParams['sStoragehouseTypeObj'] == 'clientown') {
					$sQuery .= "
								AND client_own > 0
					";
				}
			}
			if ($aParams['sStorageType'] == "object") {
				if ($aParams['sStoragehouseTypeObj'] == 'firmown') {
					$sQuery .= "
								AND client_own = 0
					";
				}
			}
			if ($aParams['sStorageType'] == "person") {
				$sQuery .= "
							AND pe.to_arc = 0
				";
			}

			if ($Total)
				$sQuery .= " GROUP BY {$Sortings} ";

			//Filtering the Result
			$sQuery .= " HAVING 1 ";

			//By Firms
			if (!empty($nIDFirm) && is_numeric($nIDFirm)) {
				$sQuery .= " AND firm_id = {$nIDFirm} ";
			}

			//By Offices
			if (!empty($nIDOffice) && is_numeric($nIDOffice)) {
				$sQuery .= " AND office_id = {$nIDOffice} ";
			}

			//By Storagehouse
			if (!empty($nIDStoragehouse) && is_numeric($nIDStoragehouse)) {
				$sQuery .= " AND storage_id = {$nIDStoragehouse} ";
			} else {
				//Try to get by name
				$sStoragehouse = isset($aParams['sStoragehouse']) ? $aParams['sStoragehouse'] : '';
				$sStoragehouse = addslashes($sStoragehouse);
				if (!empty($sStoragehouse))
					$sQuery .= " AND storage LIKE '{$sStoragehouse}' ";
			}
			$sQuery .= " AND storage_type = '{$aParams['sStorageType']}' ";

			//By Nomenclature Type
			if (!empty($nIDNomenclatureType) && is_numeric($nIDNomenclatureType)) {
				$sQuery .= " AND nom_type_id = {$nIDNomenclatureType} ";
			}

			//By Nomenclature
			if (!empty($nIDNomenclature) && is_numeric($nIDNomenclature)) {
				$sQuery .= " AND nom_id = {$nIDNomenclature} ";
			}

			return $sQuery;
		}





	}



?>