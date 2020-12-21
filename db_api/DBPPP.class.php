<?php

	class DBPPP extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'ppp' );
		}
		
		public function deleteNomenclatures( $nID )
		{
			if( empty( $nID ) || !is_numeric( $nID ) )return false;
			
			$sDisposeQuery = "DELETE FROM ppp_elements WHERE id_ppp = {$nID} AND to_arc != 1";
			
			$this->select( $sDisposeQuery );
		}
		
		public function setDefaults( $aParams, DBResponse $oResponse )
		{
			$oResponse->setFormElement( 'form1', 'nDay', 	array( 'value' => date('d') ) );
			$oResponse->setFormElement( 'form1', 'nMonth', 	array( 'value' => date('m') ) );
			$oResponse->setFormElement( 'form1', 'nYear', 	array( 'value' => date('Y') ) );
			
			$nID = $aParams['nID'];
			if( !$nID )
			{
				
				if( $aParams['nIDObject'] )
				{
					
					$oResponse->setFormElement( 'form1', 'sSendType', array( 'value' => 'object' ), 'object' );
					$oResponse->setFormElement( 'form1', 'sReceiveType', array( 'value' => 'object' ), 'object' );

					
					$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aParams['nIDObject'] ), $aParams['nIDObject'] );
					$oResponse->setFormElement( 'form1', 'nIDDestName', array( 'value' => $aParams['nIDObject'] ), $aParams['nIDObject'] );
					
					$oObjects = new DBObjects();
					$oStoragehouses = new DBStoragehouses();
					
					$aObject = $oObjects->getByID( $aParams['nIDObject'] );
					if( !empty( $aObject ) )
					{
						$sObjectName = $aObject['name'] . " [{$aObject['num']}]";
						
						if( empty( $aParams['nSetStorage'] ) )
						{
							$oResponse->setFormElement( 'form1', 'sSendType', array( 'value' => 'object' ), 'object' );
							$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aParams['nIDObject'] ), $aParams['nIDObject'] );
							$oResponse->setFormElement( 'form1', 'sSourceName', array( 'value' => $sObjectName ), $sObjectName );
							
							$oResponse->setFormElement( 'form1', 'sReceiveType', array( 'value' => 'object' ), 'object' );
							$oResponse->setFormElement( 'form1', 'nIDDestName', array( 'value' => $aParams['nIDObject'] ), $aParams['nIDObject'] );
							$oResponse->setFormElement( 'form1', 'sDestName', array( 'value' => $sObjectName ), $sObjectName );
						}
						else
						{
							if( $aParams['nSetStorage'] == "1" )
							{
								$oResponse->setFormElement( 'form1', 'sReceiveType', array( 'value' => 'object' ), 'object' );
								$oResponse->setFormElement( 'form1', 'nIDDestName', array( 'value' => $aParams['nIDObject'] ), $aParams['nIDObject'] );
								$oResponse->setFormElement( 'form1', 'sDestName', array( 'value' => $sObjectName ), $sObjectName );
							}
							if( $aParams['nSetStorage'] == "2" )
							{
								$oResponse->setFormElement( 'form1', 'sSendType', array( 'value' => 'object' ), 'object' );
								$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aParams['nIDObject'] ), $aParams['nIDObject'] );
								$oResponse->setFormElement( 'form1', 'sSourceName', array( 'value' => $sObjectName ), $sObjectName );
							}
						}
					}
					
					//-- Право на Достъп : Обект към Обект
					$bObjectToObject = false;
					if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
					{
						if( in_array( 'ppp_object_to_object', $_SESSION['userdata']['access_right_levels'] ) )
						{
							$bObjectToObject = true;
						}
					}
					//--
					
					if( !empty( $aParams['nSetStorage'] ) )
					{
						switch( $aParams['nSetStorage'] )
						{
							case 1:
								$oResponse->setFormElement( "form1", "sSendType" );
								if( $bObjectToObject ) $oResponse->setFormElementChild( "form1", "sSendType", array( "value" => "object" ), "Обект" );
								$oResponse->setFormElementChild( "form1", "sSendType", array( "value" => "storagehouse" ), "Склад" );
								$oResponse->setFormElementChild( "form1", "sSendType", array( "value" => "person" ), "Служител" );
								$oResponse->setFormElementChild( "form1", "sSendType", array( "value" => "client" ), "Доставчик" );
								
								$oResponse->setFormElementAttribute( "form1", "sSendType", "value", "storagehouse" );
								
								$aStoragehouse = $oStoragehouses->getFirstNewStorageForOffice( $aObject['id_tech_office'] );
								if( !empty( $aStoragehouse ) )
								{
									$oResponse->setFormElement( 'form1', 'sSourceName', array( 'value' => $aStoragehouse['name'] ) );
									$oResponse->setFormElement( 'form1', 'sSentBy', array( 'value' => $aStoragehouse['mol'] ) );
									$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aStoragehouse['id'] ) );
								}
								
								//Disable Element That is The Object
								$oResponse->setFormElementAttribute( 'form1', 'sReceiveType', 'disabled', 'disabled' );
								$oResponse->setFormElementAttribute( 'form1', 'sDestName', 'disabled', 'disabled' );
								break;
							case 2:
								$oResponse->setFormElement( "form1", "sReceiveType" );
								if( $bObjectToObject ) $oResponse->setFormElementChild( "form1", "sReceiveType", array( "value" => "object" ), "Обект" );
								$oResponse->setFormElementChild( "form1", "sReceiveType", array( "value" => "storagehouse" ), "Склад" );
								$oResponse->setFormElementChild( "form1", "sReceiveType", array( "value" => "person" ), "Служител" );
								$oResponse->setFormElementChild( "form1", "sReceiveType", array( "value" => "client" ), "Доставчик" );
								
								$oResponse->setFormElementAttribute( "form1", "sReceiveType", "value", "storagehouse" );
								
								$aStoragehouse = $oStoragehouses->getFirstRemovedStorageFromOffice( $aObject['id_tech_office'] );
								if( !empty( $aStoragehouse ) )
								{
									$oResponse->setFormElement( 'form1', 'sDestName', array( 'value' => $aStoragehouse['name'] ) );
									$oResponse->setFormElement( 'form1', 'sReceivedBy', array( 'value' => $aStoragehouse['mol'] ) );
									$oResponse->setFormElement( 'form1', 'nIDDestName', array( 'value' => $aStoragehouse['id'] ) );
								}
								
								//Disable Element That is The Object
								$oResponse->setFormElementAttribute( 'form1', 'sSendType', 'disabled', 'disabled' );
								$oResponse->setFormElementAttribute( 'form1', 'sSourceName', 'disabled', 'disabled' );
								break;
							
							default:
								break;
						}
					}
				}
				else
				{
					//Ако не е зареден обект по подразбиране, зареждаме склада НОВА ТЕХНИКА, на който е МОЛ потребителя.
					$oStoragehouses = new DBStoragehouses();
					$nIDLoggedUser = $_SESSION['userdata']['id_person'];
					$sLoggedUser = $_SESSION['userdata']['name'];
					
					$aStoragehouse = $oStoragehouses->getFirstNewStorage( $nIDLoggedUser );
					
					if( !empty( $aStoragehouse ) )
					{
						$oResponse->setFormElement( 'form1', 'sSendType', array( 'value' => 'storagehouse' ) );
						$oResponse->setFormElement( 'form1', 'sSourceName', array( 'value' => $aStoragehouse['name'] ) );
						$oResponse->setFormElement( 'form1', 'sSentBy', array( 'value' => $sLoggedUser ) );
						$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aStoragehouse['id'] ) );
					}
				}
			}
		}
		
		public function getReport( $aParams, DBResponse $oResponse, $aStorage, $nClosed )
		{
			//Как е написана бройката в базата. По подразбиране, "бр."
			$oMeasures = new DBMeasures();
			//$oNomenclatures = new DBNomenclatures();
			//$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening( $sDefaultCountMeasure );
			
			$sQuery = "
					SELECT
						e.id,
						n.id AS id_nom,
						n.name,
						IF
						(
							n.unit = '{$sDefaultCountMeasure}',
							CONCAT( ROUND( e.count ), ' ', n.unit ),
							CONCAT( e.count, ' ', n.unit )
						) AS count,
						e.count AS count_float,
						e.client_own
					FROM ppp_elements e
						LEFT JOIN nomenclatures n ON e.id_nomenclature = n.id
					WHERE e.to_arc = 0
						AND e.id_ppp = {$aParams['nID']}
						AND n.to_arc = 0
					HAVING count_float != 0
					ORDER BY n.name ASC
			";
			
			$aNomenclatures = $this->select( $sQuery );
			
			$oResponse->setField( 'chk', '', NULL, NULL, NULL, NULL, array( 'type' => 'checkbox' ) );
			$oResponse->setFieldData( 'chk', 'input', array( 'type' => 'checkbox', 'exception' => 'false' ) );
			$oResponse->setFieldAttributes( 'chk', array( 'style' => 'width: 25px;' ) );
			
			$oResponse->setFormElement( 'form1', 'sel', array(), '' );
			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '1' ), "--- Маркирай всички ---" );
			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '2' ), "--- Отмаркирай всички ---" );
			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '0' ), "------");
			$oResponse->setFormElementChild( 'form1', 'sel', array( 'value' => '3' ), "--- Премахни ---" );
			
			$oResponse->setField( 'name', 			'Номенклатура', 			'Сортирай по Номенклатура' );
			$oResponse->setField( 'count', 			'Искано количество', 		'Сортирай по Искано количество' );
			$oResponse->setField( 'state_count', 	'Наличност', 				'Сортирай по Наличност' );
			$oResponse->setField( 'client_own', 	'Собственост на клиента', 	'', 'images/confirm.gif' );
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'deletePPPElement', '' );
			
			$oResponse->setFieldLink( "name", "setPPPElement" );
			
			$aData = array();
			foreach( $aNomenclatures as $key => $aNomenclature )
			{
				$aData[$key]['chk'] = 0;
				$aData[$key]['id'] = $aNomenclature['id'];
				$aData[$key]['name'] = $aNomenclature['name'];
				$aData[$key]['count'] = $aNomenclature['count'];
				
				//Проверка за наличност на номенклатура.
				$oStates = new DBStates();
				$aState = $oStates->checkNomenclature( $aNomenclature['id_nom'], $aStorage['sStorageType'], $aStorage['nID'] );
				
				//Проверка за потвърден протокол.
				$aSelectedPPP = $this->selectOnce( "SELECT dest_date FROM ppp WHERE id = {$aParams['nID']} LIMIT 1" );
				
				//Допускам реда за валиден.
				$aData[$key]['state_count'] = "---";
				$bIsRowInvalid = false;
				
				//Проверка дали не е посочен "Доставчик" или дали склада не е виртуален.
				if( isset( $aSelectedPPP['dest_date'] ) && $aSelectedPPP['dest_date'] == "0000-00-00 00:00:00" )
				{
					if( $aStorage['sStorageType'] != "client" )
					{
						$aStorageType = array();
						if( $aStorage['sStorageType'] == 'storagehouse' )
						{
							$oStoragehouses = new DBStoragehouses();
							$aStorageType = $oStoragehouses->getTypeByID( $aStorage['nID'] );
						}
						if( empty( $aStorageType ) )
						{
							$aStorageType['type'] = '';
						}
						
						if( $aStorageType['type'] == "virtual" )
						{
							$aData[$key]['state_count'] = "---";
							$bIsRowInvalid = false;
						}
						else
						{
							if( isset( $aState['state_count'] ) )
							{
								if( $aState['count_float'] != 0 )
								{
									$aData[$key]['state_count'] = $aState['state_count'];
									if( $aNomenclature['count_float'] > $aState['count_float'] )$bIsRowInvalid = true;
									else $bIsRowInvalid = false;
								}
								else
								{
									$aData[$key]['state_count'] = "Не е в наличност!";
									$bIsRowInvalid = true;
								}
							}
							else
							{
								$aData[$key]['state_count'] = "Не е в наличност!";
								$bIsRowInvalid = true;
							}
						}
					}
					else
					{
						$aData[$key]['state_count'] = "---";
						$bIsRowInvalid = false;
					}
				}
				else
				{
					$aData[$key]['state_count'] = "---";
					$bIsRowInvalid = false;
				}
				
				$aData[$key]['client_own'] = $aNomenclature['client_own'];
				
				if( $bIsRowInvalid )
				{
					$sColor = "FFE6E6";
					$oResponse->setDataAttributes( $key, 'state_count', array( "style" => "background: {$sColor};" ) );
				}
			}
			
			$oResponse->setData( $aData );
		}
		
		public function getReport2( $aParams, DBResponse $oResponse )
		{
			global $db_name_sod, $db_name_personnel;
			
			$aParams = Params::getAll();
			
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$off = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$wh = "AND ( id_office_source IN( {$off} ) OR id_office_dest IN( {$off} ) ) \n";
			}
			else $wh = "";
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						ppp.id,
						DATE_FORMAT( ppp.source_date, '%d.%m.%Y %H:%i:%s' ) AS source_date_,
						ppp.source_user,
						CASE ppp.source_type
							WHEN 'object' THEN CONCAT( ob1.name, ' [',ob1.num , ']' )
							WHEN 'person' THEN CONCAT_WS( ' ', p1.fname, p1.mname, p1.lname )
							WHEN 'client' THEN c1.name
							WHEN 'storagehouse' THEN s1.name
						END
						AS source_name,
						CASE ppp.source_type
							WHEN 'object' THEN ob1.id_office
							WHEN 'person' THEN p1.id_office
							WHEN 'client' THEN 0
							WHEN 'storagehouse' THEN s1.id_office
						END
						AS id_office_source,
						IF
						(
							ppp.dest_date,
							DATE_FORMAT( ppp.dest_date, '%d.%m.%Y %H:%i:%s' ),
							'---'
						) AS dest_date_,
						ppp.dest_user,
						CASE ppp.dest_type
							WHEN 'object' THEN CONCAT( ob2.name, ' [',ob2.num , ']' )
							WHEN 'person' THEN CONCAT_WS( ' ', p2.fname, p2.mname, p2.lname )
							WHEN 'client' THEN c2.name
							WHEN 'storagehouse' THEN s2.name
						END
						AS dest_name,
						CASE ppp.dest_type
							WHEN 'object' THEN ob2.id_office
							WHEN 'person' THEN p2.id_office
							WHEN 'client' THEN 0
							WHEN 'storagehouse' THEN s2.id_office
						END
						AS id_office_dest,
						IF
						(
							ppp.price,
							CONCAT( ppp.price, ' лв.' ),
							'---'
						) AS price,
						ppp.description,
						CONCAT_WS( ' ', 
							CONCAT_WS( ' ', us.fname, us.mname, us.lname ),
							CONCAT( ' ', '(', DATE_FORMAT( ppp.updated_time, '%Y.%m.%d %H:%i:%s' ), ')' )
						) AS updated_user,
						IF( ppp.status = 'confirm', 1, 0 ) AS is_closed,
						IF( ppp.status = 'cancel', 1, 0 ) AS is_null
					FROM ppp
						LEFT JOIN {$db_name_sod}.objects ob1 ON ppp.id_source = ob1.id
						LEFT JOIN {$db_name_personnel}.personnel p1 ON ( ppp.id_source = p1.id AND p1.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.clients c1 ON ppp.id_source = c1.id
						LEFT JOIN storagehouses s1 ON ( ppp.id_source = s1.id AND s1.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.objects ob2 ON ppp.id_dest = ob2.id
						LEFT JOIN {$db_name_personnel}.personnel p2 ON ( ppp.id_dest = p2.id AND p2.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.clients c2 ON ppp.id_dest = c2.id
						LEFT JOIN storagehouses s2 ON ( ppp.id_dest = s2.id AND s2.to_arc = 0 )
						LEFT JOIN {$db_name_personnel}.personnel us ON ppp.updated_user = us.id
					WHERE ppp.to_arc = 0
			";
			
			if( !empty( $aParams['sFromDate'] ) )
			{
				$nDateFrom = jsDateToTimestamp( $aParams['sFromDate'] );
				
				if( !empty( $nDateFrom ) )
				{
					$sQuery .= "AND DATE( ppp.source_date ) >= DATE( FROM_UNIXTIME( $nDateFrom ) )\n";
				}
			}
			
			if( !empty( $aParams['sToDate'] ) )
			{
				$nDateTo = jsDateToTimestamp( $aParams['sToDate'] );
				
				if( !empty( $nDateTo ) )
				{
					$sQuery .= "AND DATE( ppp.source_date ) <= DATE( FROM_UNIXTIME( $nDateTo ) )\n";
				}
			}
			
			$sSendType = 'Обект / Склад / Служител / Доставчик';
			$sReceiveType = 'Обект / Склад / Служител / Доставчик';
			if( !empty( $aParams['sSendType'] ) )
			{
				$sQuery .= sprintf( "AND source_type = %s\n", $this->oDB->Quote( $aParams['sSendType'] ) );
				switch( $aParams['sSendType'] )
				{
					case 'person': 			$sSendType = 'Служител'; 	break;
					case 'storagehouse': 	$sSendType = 'Склад'; 		break;
					case 'object': 			$sSendType = 'Обект'; 		break;
					case 'client': 			$sSendType = 'Доставчик'; 	break;
				}
			}
			if( !empty( $aParams['sReceiveType'] ) )
			{
				$sQuery .= sprintf( "AND dest_type = %s\n", $this->oDB->Quote( $aParams['sReceiveType'] ) );
				switch( $aParams['sReceiveType'] )
				{
					case 'person': 			$sReceiveType = 'Служител'; 	break;
					case 'storagehouse': 	$sReceiveType = 'Склад'; 		break;
					case 'object': 			$sReceiveType = 'Обект'; 		break;
					case 'client': 			$sReceiveType = 'Доставчик'; 	break;
				}
			}
			if ( !empty($aParams['nNumber']) ) {
				$sQuery .= " AND ppp.id = {$aParams['nNumber']} ";
			}
			
			if( isset( $aParams['sStatus'] ) )
			{
				if( $aParams['sStatus'] != "" )$sQuery .= "AND ppp.status = '{$aParams['sStatus']}'\n";
			}
			
			$sQuery .= "HAVING 1\n";
			
			$sQuery .= $wh;
			
			$sSourceName = 	$this->removeNumber( $aParams['sSourceName'] );
			$sDestName = 	$this->removeNumber( $aParams['sDestName'] );
			
			if( !empty( $sSourceName ) )
				$sQuery .= sprintf( "AND source_name LIKE '%%%s%%'\n", addslashes( $sSourceName ) );
			
			if( !empty( $sDestName ) )
				$sQuery .= sprintf( "AND dest_name LIKE '%%%s%%'\n", addslashes( $sDestName ) );
			
			$this->getResult( $sQuery, 'source_date_', DBAPI_SORT_DESC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $aRow )
			{
				if( empty( $aRow['is_closed'] ) )
				{
					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "font-weight: bold;" ) );
				}
				if( !empty( $aRow['is_null'] ) )
				{
					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "font-style: italic; color: #969696;" ) );
				}
			}
			
			$oResponse->setField( "id", 			"Номер", 					"Сортирай по Номер" );
			$oResponse->setField( "source_date_", 	"Дата на създаване", 		"Сортирай по Дата на Създаване" );
			$oResponse->setField( "source_user", 	"Предал", 					"Сортирай по Предал" );
			$oResponse->setField( "source_name", 	"От {$sSendType}", 			"Сортирай по {$sSendType}" );
			$oResponse->setField( "dest_date_", 	"Дата на потвърждаване", 	"Сортирай по Дата на Потвърждаване" );
			$oResponse->setField( "dest_user", 		"Получил", 					"Сортирай по Получил" );
			$oResponse->setField( "dest_name", 		"Към {$sReceiveType}", 		"Сортирай по {$sReceiveType}" );
			$oResponse->setField( "price", 			"Стойност на техниката", 	"Сортирай по Стойност на Техниката" );
			$oResponse->setField( "description", 	"Бележка", 					"" );
			$oResponse->setField( "updated_user", 	"...", 						"", "images/dots.gif" );
			
			$oResponse->setFieldLink( "id", "openPPP" );
		}
		
		public function getLimitCardReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$nIDLimitCard = !empty( $aParams['nID'] ) ? $aParams['nID'] : 0;
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						ppp.id,
						DATE_FORMAT(ppp.source_date, '%d.%m.%Y %H:%i:%s') AS source_date,
						ppp.price,
						CONCAT( CONCAT_WS( ' ', uc.fname, uc.mname, uc.lname ), ' (', DATE_FORMAT( ppp.source_date,'%d.%m.%Y %H:%i:%s' ), ')' ) AS created_user,
						CONCAT( CONCAT_WS( ' ', us.fname, us.mname, us.lname ), ' (', DATE_FORMAT( ppp.updated_time,'%d.%m.%Y %H:%i:%s' ), ')' ) AS updated_user,
						IF( ppp.status = 'confirm', 1, 0 ) AS is_closed
					FROM ppp
						LEFT JOIN {$db_name_personnel}.personnel us ON ppp.updated_user = us.id
						LEFT JOIN {$db_name_personnel}.personnel uc ON ppp.created_user = uc.id
					WHERE ppp.to_arc = 0
						AND ppp.status != 'cancel'
						AND ppp.id_limit_card = {$nIDLimitCard}
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $aRow )
			{
				if( empty( $aRow['is_closed'] ) )
					$oResponse->setRowAttributes( $aRow['id'], array( "style" => "font-weight:bold" ) );
			}
			
			$oResponse->setField( 'id', 			'номер', 					'Сортирай по Номер' );
			$oResponse->setField( 'source_date', 	'дата', 					'Сортирай по Дата' );
			$oResponse->setField( 'price', 			'стойност на техниката', 	'Сортирай по Стойност' );
			$oResponse->setField( 'created_user', 	'съставил', 				'' );
			$oResponse->setField( 'updated_user', 	'приключил', 				'' );
			
			$oResponse->setFieldLink( "id", 			"openPPP" );
			$oResponse->setFieldLink( "source_date", 	"openPPP" );
		}
		
		public function getReportMz( $aParams, DBResponse $oResponse )
		{
			$nIDLimitCard = !empty( $aParams['nID'] ) ? $aParams['nID'] : 0;
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						nt.name AS nomenclature_type,
						n.name AS nomenclature,
						CASE
							WHEN p.source_type = 'object' THEN -pe.count
							WHEN p.dest_type = 'object' THEN pe.count
							ELSE 0
						END AS nomenclature_count,
						CONCAT( pe.count * pe.single_price, ' лв.' ) AS nomenclature_price
					FROM ppp p
						LEFT JOIN ppp_elements pe ON (p.id = pe.id_ppp AND pe.to_arc = 0)
						LEFT JOIN nomenclatures n ON pe.id_nomenclature = n.id
						LEFT JOIN nomenclature_types nt ON nt.id = n.id_type
					WHERE 1
						AND p.to_arc = 0
						AND p.id_limit_card = {$nIDLimitCard}
						AND p.id_limit_card > 0
						AND p.status != 'cancel'
					HAVING nomenclature_count != 0
			";
			
			$this->getResult( $sQuery, 'nomenclature', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => $val )
			{
				$oResponse->setDataAttributes( $key, 'nomenclature_count', 	array( 'style' => 'text-align:right;' ) );
				$oResponse->setDataAttributes( $key, 'nomenclature_price', 	array( 'style' => 'text-align:right;' ) );
			}
			
			$oResponse->setField( 'nomenclature_type', 		'тип',				'Сортирай по Тип' );
			$oResponse->setField( 'nomenclature', 			'номенклатура',		'Сортирай по Номенклатура' );
			$oResponse->setField( 'nomenclature_count', 	'бр.',				'Сортирай по Брой' );
			$oResponse->setField( 'nomenclature_price', 	'цена', 			'Сортирай по Цена' );
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
		
		public function getPPPforPDF( $nID )
		{
			global $db_name_sod, $db_name_personnel;
			
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception( '', DBAPI_ERR_INVALID_PARAM );
			
			$sQuery = "
					SELECT
						ppp.id,
						DATE_FORMAT( ppp.source_date, '%d.%m.%Y %H:%i:%s' ) AS source_date,
						
						DATE_FORMAT( ppp.source_date, '%d' ) AS source_date_day,
						DATE_FORMAT( ppp.source_date, '%m' ) AS source_date_month,
						DATE_FORMAT( ppp.source_date, '%Y' ) AS source_date_year,
						
						ppp.source_user,
						ppp.source_type,
						CASE ppp.source_type
							WHEN 'object' THEN CONCAT( ob1.name, ' [', ob1.num , ']' )
							WHEN 'person' THEN CONCAT_WS( ' ', p1.fname, p1.mname, p1.lname )
							WHEN 'client' THEN c1.name
							WHEN 'storagehouse' THEN s1.name
						END
						AS source_name,
						DATE_FORMAT( ppp.dest_date, '%d.%m.%Y %H:%i:%s' ) AS dest_date,
						ppp.dest_user,
						ppp.dest_type,
						CASE ppp.dest_type
							WHEN 'object' THEN CONCAT( ob2.name, ' [', ob2.num , ']' )
							WHEN 'person' THEN CONCAT_WS( ' ', p2.fname, p2.mname, p2.lname )
							WHEN 'client' THEN c2.name
							WHEN 'storagehouse' THEN s2.name
						END
						AS dest_name,
						ppp.price,
						ppp.description,
						IF( ppp.status = 'confirm', 1, 0 ) AS is_closed
					FROM ppp
						LEFT JOIN {$db_name_sod}.objects ob1 ON ppp.id_source = ob1.id
						LEFT JOIN {$db_name_personnel}.personnel p1 ON ( ppp.id_source = p1.id AND p1.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.clients c1 ON ppp.id_source = c1.id
						LEFT JOIN storagehouses s1 ON ( ppp.id_source = s1.id AND s1.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.objects ob2 ON ppp.id_dest = ob2.id
						LEFT JOIN {$db_name_personnel}.personnel p2 ON ( ppp.id_dest = p2.id AND p2.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.clients c2 ON ppp.id_dest = c2.id
						LEFT JOIN storagehouses s2 ON ( ppp.id_dest = s2.id AND s2.to_arc = 0 )
						LEFT JOIN {$db_name_personnel}.personnel us ON ppp.updated_user = us.id
					WHERE 1
						AND ppp.to_arc = 0
						AND ppp.id = {$nID}
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getMZforPDF( $nID )
		{
			global $db_name_sod, $db_name_personnel;
			
			//Как е написана бройката в базата. По подразбиране, "бр."
			$oMeasures = new DBMeasures();
			//$oNomenclatures = new DBNomenclatures();
			//$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening( $sDefaultCountMeasure );
			
			if( empty( $nID ) || !is_numeric( $nID ) )
				throw new Exception( '', DBAPI_ERR_INVALID_PARAM );
			
			$sQuery = "
					SELECT
						p.id,
						n.name,
						IF
						(
							n.unit = '{$sDefaultCountMeasure}',
							CONCAT( ROUND( e.count ), ' ', n.unit ),
							CONCAT( e.count, ' ', n.unit )
						) AS count,
						e.client_own
					FROM ppp_elements e
						LEFT JOIN ppp p ON p.id = e.id_ppp
						LEFT JOIN nomenclatures n ON n.id = e.id_nomenclature
					WHERE 1
						AND p.id = {$nID}
						AND e.to_arc = 0
						AND p.to_arc = 0
						AND n.to_arc = 0
					ORDER BY name
			";
			
			return $this->select( $sQuery );
		}
		
		public function getPPPByObject( $nID, DBResponse $oResponse ) {
			global $db_name_personnel, $db_name_sod;
			
			$nID = !empty($nID) ? $nID : 0;
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						p.id,
						p.id as number,
						DATE_FORMAT(p.source_date, '%d.%m.%Y %H:%i') AS source_date,
						p.source_user,
						CASE p.source_type
							WHEN 'object' THEN ob.name
							WHEN 'person' THEN CONCAT_WS( ' ', per.fname, per.mname, per.lname )
							WHEN 'client' THEN c.name
							WHEN 'storagehouse' THEN s.name
						END as source_type,
						DATE_FORMAT(p.source_date, '%d.%m.%Y %H:%i') AS dest_date,
						p.dest_user,
						CASE p.dest_type
							WHEN 'object' THEN ob2.name
							WHEN 'person' THEN CONCAT_WS( ' ', per2.fname, per2.mname, per2.lname )
							WHEN 'client' THEN c2.name
							WHEN 'storagehouse' THEN s2.name
						END as dest_type,
						IF
						(
							p.source_type = 'object',
							1,
							0
						) AS sending,
						CONCAT( p.price, ' лв.' ) AS price,
						IF( p.status = 'confirm', 1, 0 ) AS is_closed
					FROM ppp p
						LEFT JOIN {$db_name_sod}.objects ob ON p.id_source = ob.id
						LEFT JOIN {$db_name_personnel}.personnel per ON ( p.id_source = per.id AND per.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.clients c ON p.id_source = c.id
						LEFT JOIN storagehouses s ON ( p.id_source = s.id AND s.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.objects ob2 ON p.id_dest = ob2.id
						LEFT JOIN {$db_name_personnel}.personnel per2 ON ( p.id_dest = per2.id AND per2.to_arc = 0 )
						LEFT JOIN {$db_name_sod}.clients c2 ON p.id_dest = c2.id
						LEFT JOIN storagehouses s2 ON ( p.id_dest = s2.id AND s2.to_arc = 0 )
						
					WHERE p.to_arc = 0
						AND
						(		
								(p.id_dest = {$nID}	AND p.dest_type = 'object')
								OR
								(p.id_source = {$nID} AND p.source_type = 'object')
						)
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => $aRow )
			{
				$sRowStyle = "";
				
				if( !empty( $aRow['sending'] ) )
				{
					$sRowStyle .= "color: blue;";
				}
				else
				{
					$sRowStyle .= "color: red;";
				}
				
				if ( empty( $aRow['is_closed'] ) )
				{
					$sRowStyle .= "font-weight:bold;";
				}
				
				$oResponse->setRowAttributes( $aRow['id'], array( "style" => "{$sRowStyle}" ) );
				
				$oResponse->setDataAttributes( $key, 'number', 			array( 'style' => 'text-align: right; width: 65px; cursor: pointer;', 'onclick' => "openPPP( {$aRow['id']} )" ) );
				$oResponse->setDataAttributes( $key, 'price', 			array( 'nowrap' => 'nowrap', 'style' => 'text-align: right; width: 65px;' ) );
				$oResponse->setDataAttributes( $key, 'source_user', 	array( 'nowrap' => 'nowrap', 'style' => 'text-align: center; width: 200px; white-space: nowrap !important; width: 230px;' ) );
				$oResponse->setDataAttributes( $key, 'source_type', 	array( 'nowrap' => 'nowrap', 'style' => 'text-align: center; width: 205px; white-space: nowrap !important; width: 300px;' ) );
				$oResponse->setDataAttributes( $key, 'dest_date', 		array( 'nowrap' => 'nowrap', 'style' => 'text-align: center; width: 100px; white-space: nowrap !important; width: 130px;' ) );
				$oResponse->setDataAttributes( $key, 'dest_user', 		array( 'nowrap' => 'nowrap', 'style' => 'text-align: center; width: 200px; white-space: nowrap !important; width: 230px;' ) );
				$oResponse->setDataAttributes( $key, 'source_date', 	array( 'nowrap' => 'nowrap', 'style' => 'text-align: center; width: 100px; white-space: nowrap !important; cursor: pointer; width: 130px;', 'onclick' => "openPPP( {$aRow['id']} )" ) );
				$oResponse->setDataAttributes( $key, 'dest_type', 		array( 'nowrap' => 'nowrap', 'style' => 'text-align: center; width: 205px; white-space: nowrap !important; width: 300px;' ) );
			}
			
			$oResponse->setField( 'number', 		' номер ', 			'номер' );
			$oResponse->setField( 'source_date', 	' дата ', 			'дата' );
			$oResponse->setField( 'source_user', 	' от лице ', 		'от лице' );
			$oResponse->setField( 'source_type', 	' от склад ', 		'от склад' );
			$oResponse->setField( 'dest_date', 		' дата ', 			'дата' );
			$oResponse->setField( 'dest_user', 		' до лице ', 		'до лице' );
			$oResponse->setField( 'dest_type', 		' до склад ', 		'до склад' );
			$oResponse->setField( 'price', 			' стойност ', 		'стойност' );
			
//			$oResponse->setFieldLink( "number",			"openPPP" );
//			$oResponse->setFieldLink( "source_date",	"openPPP" );
			
			$oResponse->setFieldAttributes( 'id', array( 'style' => 'width: 65px; white-space: nowrap !important;' ) );
		}
		
		public function getReportByObject( $nID, DBResponse $oResponse ) {
			$nID = !empty($nID) ? $nID : 0;
			
//			$sQuery = "
//					SELECT SQL_CALC_FOUND_ROWS
//						p.id,
//						nt.name AS nomenclature_type,
//						n.name AS nomenclature,
//						CASE
//							WHEN p.source_type = 'object' THEN -pe.count
//							WHEN p.dest_type = 'object' THEN pe.count
//							ELSE 0
//						END AS nomenclature_count,
//						CONCAT( pe.count * pe.single_price, ' лв.' ) AS nomenclature_price,
//						CONCAT( pe.count * n.support_price, ' лв.' ) AS support_price
//					FROM ppp p
//						LEFT JOIN ppp_elements pe ON (p.id = pe.id_ppp AND pe.to_arc = 0)
//						LEFT JOIN nomenclatures n ON pe.id_nomenclature = n.id
//						LEFT JOIN nomenclature_types nt ON nt.id = n.id_type
//					WHERE 1
//						AND p.to_arc = 0
//						AND p.id_dest = {$nID}
//						AND p.dest_type = 'object'
//					HAVING nomenclature_count != 0
//			";

			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					s.id,
					nt.name AS nomenclature_type,
					n.name as nomenclature,
					s.count as nomenclature_count,
					s.client_own,
					CONCAT( s.count * n.last_price, ' лв.' ) AS nomenclature_price,
					CONCAT( s.count * n.support_price, ' лв.' ) AS support_price
				FROM states s
				LEFT JOIN nomenclatures n ON n.id = s.id_nomenclature
				LEFT JOIN nomenclature_types nt ON nt.id = n.id_type
				WHERE 1
					AND s.to_arc = 0
					AND s.count != 0
					AND s.storage_type = 'object'
					AND s.id_storage = '{$nID}'
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => $val )
			{
				$oResponse->setDataAttributes( $key, 'client_own', 			array( 'style' => 'text-align:right;' ) );
				$oResponse->setDataAttributes( $key, 'nomenclature_count', 	array( 'style' => 'text-align:right;' ) );
				$oResponse->setDataAttributes( $key, 'nomenclature_price', 	array( 'style' => 'text-align:right;' ) );
				$oResponse->setDataAttributes( $key, 'support_price', 		array( 'style' => 'text-align:right;' ) );
			}
			
			$oResponse->setField( 'nomenclature_type', 		'тип',				'Сортирай по Тип' );
			$oResponse->setField( 'nomenclature', 			'номенклатура',		'Сортирай по Номенклатура' );
			$oResponse->setField( 'nomenclature_count', 	'бр.',				'Сортирай по Брой' );
			$oResponse->setField( 'client_own', 			'собств. на кл.',	'Сортирай по Собственост на Клиент', 'images/confirm.gif' );
			$oResponse->setField( 'nomenclature_price', 	'цена', 			'Сортирай по Цена' );
			$oResponse->setField( 'support_price', 			'цена за монтаж', 	'Сортирай по Цена за Монтаж' );
		}
		
		public function getPersonalCardReport( $nIDCurrentPPP, DBResponse $oResponse )
		{
			//Как е написана бройката в базата. По подразбиране, "бр."
			$oMeasures = new DBMeasures();
			//$oNomenclatures = new DBNomenclatures();
			//$oNomenclatures->fixNomenclatureMeasures();
			$sDefaultCountMeasure = "бр.";
			$sDefaultCountMeasure = $oMeasures->fixMeasureShortening( $sDefaultCountMeasure );
			
			$sQuery = "
					SELECT
						e.id,
						n.id AS id_nom,
						n.name,
						IF
						(
							n.unit = '{$sDefaultCountMeasure}',
							CONCAT( ROUND( e.count ), ' ', n.unit ),
							CONCAT( e.count, ' ', n.unit )
						) AS count,
						e.count AS count_float,
						e.client_own
					FROM ppp_elements e
						LEFT JOIN nomenclatures n ON e.id_nomenclature = n.id
					WHERE e.to_arc = 0
						AND e.id_ppp = {$nIDCurrentPPP}
						AND n.to_arc = 0
					ORDER BY name
			";
			
			$aQueryData = $this->select( $sQuery );
			$aData = array();
			
			foreach( $aQueryData as $nKey => $aElement )
			{
				$aData[$nKey]['id'] = 			$aElement['id'];
				$aData[$nKey]['name'] = 		$aElement['name'];
				$aData[$nKey]['count'] = 		$aElement['count'];
				$aData[$nKey]['client_own'] = 	$aElement['client_own'];
				
				$oResponse->setDataAttributes( $nKey, 'name', 		array( "style" => "width: 350px;" ) );
				$oResponse->setDataAttributes( $nKey, 'count', 		array( "style" => "width: 70px;" ) );
				$oResponse->setDataAttributes( $nKey, 'client_own', array( "style" => "width: 40px;" ) );
			}
			
			$oResponse->setField( 'name', 		'номенклатура', 	'' );
			$oResponse->setField( 'count', 		'кол.', 			'' );
			$oResponse->setField( 'client_own', 'на кл.', 			'', 'images/confirm.gif' );
			
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'deletePPPElement', '' );
			$oResponse->setFieldLink( 'name', 'setPPPElement' );
			
			$oResponse->setData( $aData );
		}
		
		public function getPPPMainInfo( $nIDPPP )
		{
			global $db_name_personnel, $db_name_sod;
			
			$nIDPPP = (int) $nIDPPP;
			
			$sQuery = "
					SELECT
						DATE_FORMAT( p.source_date, '%d.%m.%Y %H:%i' ) AS source_date,
						CASE p.source_type
							WHEN 'client' THEN 'доставчик'
							WHEN 'storagehouse' THEN 'склад'
							WHEN 'person' THEN 'служител'
							WHEN 'object' THEN 'обект'
						END AS source_type,
						CASE p.source_type
							WHEN 'client' THEN cl.name
							WHEN 'storagehouse' THEN st.name
							WHEN 'person' THEN CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname )
							WHEN 'object' THEN ob.name
						END AS source,
						DATE_FORMAT( p.dest_date, '%d.%m.%Y %H:%i' ) AS dest_date,
						CASE p.dest_type
							WHEN 'client' THEN 'доставчик'
							WHEN 'storagehouse' THEN 'склад'
							WHEN 'person' THEN 'служител'
							WHEN 'object' THEN 'обект'
						END AS dest_type,
						CASE p.dest_type
							WHEN 'client' THEN cl2.name
							WHEN 'storagehouse' THEN st2.name
							WHEN 'person' THEN CONCAT_WS( ' ', pe2.fname, pe2.mname, pe2.lname )
							WHEN 'object' THEN ob2.name
						END AS dest
					FROM ppp p
						LEFT JOIN {$db_name_sod}.clients cl ON cl.id = p.id_source
						LEFT JOIN storagehouses st ON st.id = p.id_source
						LEFT JOIN {$db_name_personnel}.personnel pe ON pe.id = p.id_source
						LEFT JOIN {$db_name_sod}.objects ob ON ob.id = p.id_source
						LEFT JOIN {$db_name_sod}.clients cl2 ON cl2.id = p.id_dest
						LEFT JOIN storagehouses st2 ON st2.id = p.id_dest
						LEFT JOIN {$db_name_personnel}.personnel pe2 ON pe2.id = p.id_dest
						LEFT JOIN {$db_name_sod}.objects ob2 ON ob2.id = p.id_dest
					WHERE 1
						AND p.to_arc = 0
						AND p.id = {$nIDPPP}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function countUnconfirmedPPPs($nIDLimitCard) {
			
			$sQuery = "
				SELECT 
					count(*)
				FROM ppp 
				WHERE 1
					AND dest_date = '0000-00-00 00:00:00'
					AND id_limit_card = {$nIDLimitCard}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function setDestObject( $nIDLimitCard, $nIDObject) {
			
			$sQuery = "
				UPDATE
					ppp
				SET 
					id_dest = {$nIDObject}
				WHERE 1
					AND id_limit_card = {$nIDLimitCard}
					AND id_dest = 0
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function delPPPByIDLimitCard($nIDLimitCard) {
			
			$sQuery = "
				UPDATE
					ppp
				SET 
					to_arc = 1
				WHERE
					id_limit_card = {$nIDLimitCard}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}

?>
