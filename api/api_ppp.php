<?php

	require_once( "pdf/pdf_ppp.php" );
	
	class ApiPPP
	{
		public function result( DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$aParams = Params::getAll();
			$nID = Params::get( 'nID', '' );
			
			$sApiAction = Params::get( "api_action", "" );
			if( $sApiAction == 'export_to_pdf' )
			{
				$pppPDF = new pppPDF( "L" );
				$pppPDF->PrintReport( $nID );
			}
			
			$oPPP = new DBPPP();
			$oTechLimitCards = new DBTechLimitCards();
			
			if( !$nID )
			{
				$oPPP->setDefaults( $aParams, $oResponse );
				$oResponse->setFormElement( 'form1', 'nElementsSet', array( 'value' => 1 ) );
			}
			else
			{
				/*
				// Limit Card is Real-Ended.
				$aLimitCard = $oTechLimitCards->getLimitCardByPPP( $nID );
				if( !empty( $aLimitCard ) && $aLimitCard['real_end'] != '0000-00-00 00:00:00' )
				{
					$oResponse->setFormElement( 'form1', 'nOnlyValidate', array( "value" => 1 ) );
				}
				*/
				
				if( isset( $aParams['nElementsSet'] ) && $aParams['nElementsSet'] == 0 )
				{
					
					$aData = array();
					$aData = $oPPP->getRecord( $nID );
					
					if( !empty( $aData ) 
						&& isset( $aData['id_source'] ) 
						&& !empty( $aData['id_source'] ) )
					{
						switch( $aData['source_type'] )
						{
							case 'person':
								$oPersonnel = new DBPersonnel();
								$aSource = $oPersonnel->getByID( $aData['id_source'] );
								if( !empty( $aSource ) )
									$sSourceName = $aSource['fname'] . ' ' . $aSource['mname'] . ' ' . $aSource['lname'];
							break;
							case 'storagehouse':
								$oStoragehouses = new DBStoragehouses();
								$aSource = $oStoragehouses->getByID( $aData['id_source'] );
								if( !empty( $aSource ) )$sSourceName = $aSource['name'];
							break;
							case 'object':
								$oObjects = new DBObjects();
								$aSource = $oObjects->getByID( $aData['id_source'] );
								if( !empty( $aSource ) )$sSourceName = $aSource['name'] . " [{$aSource['num']}]";
							break;
							case 'client':
								$oClients = new DBClients();
								$aSource = $oClients->getByID( $aData['id_source'] );
								if( !empty( $aSource ) )$sSourceName = $aSource['name'];
							break;
							
							default: break;
						}
					}
					if( !empty( $aData )
						&& isset( $aData['id_dest'] ) 
						&& !empty( $aData['id_dest'] ) )
					{
						switch( $aData['dest_type'] )
						{
							case 'person':
								$oPersonnel = new DBPersonnel();
								$aDest = $oPersonnel->getByID( $aData['id_dest'] );
								if( !empty( $aDest ) )
									$sDestName = $aDest['fname'] . ' ' . $aDest['mname'] . ' ' . $aDest['lname'];
							break;
							case 'storagehouse':
								$oStoragehouses = new DBStoragehouses();
								$aDest = $oStoragehouses->getByID( $aData['id_dest'] );
								if( !empty( $aDest ) )$sDestName = $aDest['name'];
							break;
							case 'object':
								$oObjects = new DBObjects();
								$aDest = $oObjects->getByID( $aData['id_dest'] );
								if( !empty( $aDest ) )$sDestName = $aDest['name'] . " [{$aDest['num']}]";
							break;
							case 'client':
								$oClients = new DBClients();
								$aDest = $oClients->getByID( $aData['id_dest'] );
								if( !empty( $aDest ) )$sDestName = $aDest['name'];
							break;
							
							default: break;
						}
					}
					
					$oResponse->setFormElement( 'form1', 'nID', 			array( 'value' => $aData['id'] ) );
					$oResponse->setFormElement( 'form1', 'nIDLimitCard',	array( 'value' => $aData['id_limit_card'] ) );
					
					$oResponse->setFormElement( 'form1', 'sSendType', 		array( 'value' => $aData['source_type'] ) );
					$oResponse->setFormElement( 'form1', 'sReceiveType', 	array( 'value' => $aData['dest_type'] ) );
					
					$sTempName = Params::get( "sSourceName", '' );
					if( !empty( $sSourceName ) )$oResponse->setFormElement( 'form1', 'sSourceName', array( 'value' => $sSourceName ) );
					else $oResponse->setFormElement( 'form1', 'sSourceName', array( 'value' => $sTempName ) );
					$sTempName = Params::get( "sDestName", '' );
					if( !empty( $sDestName ) )$oResponse->setFormElement( 'form1', 'sDestName', 	array( 'value' => $sDestName ) );
					else $oResponse->setFormElement( 'form1', 'sDestName', 	array( 'value' => $sTempName ) );
					
					$oResponse->setFormElement( 'form1', 'sSentBy', 		array( 'value' => $aData['source_user'] ) );
					$oResponse->setFormElement( 'form1', 'sReceivedBy', 	array( 'value' => $aData['dest_user'] ) );
					$oResponse->setFormElement( 'form1', 'sNote', 			array( 'value' => $aData['description'] ) );
					
					$nYear = '';
					for( $i = 0; $i < 4; $i++ )$nYear .= $aData['source_date'][$i];
					$nMonth = '';
					for( $i = 5; $i < 7; $i++ )$nMonth .= $aData['source_date'][$i];
					$nDay = '';
					for( $i = 8; $i < 10; $i++ )$nDay .= $aData['source_date'][$i];
					
					$sHour = substr( $aData['source_date'], 11, 8 );
					
					$oResponse->setFormElement( 'form1', 'nDay', 	array( 'value' => $nDay ) );
					$oResponse->setFormElement( 'form1', 'nMonth', 	array( 'value' => $nMonth ) );
					$oResponse->setFormElement( 'form1', 'nYear', 	array( 'value' => $nYear ) );
					$oResponse->setFormElement( 'form1', 'sHour', 	array( 'value' => $sHour ) );
					
					$nClosed = 0;
					if( $aData['status'] == "confirm" )
					{
						$oResponse->setFormElement( 'form1', 'nClosed', array( "checked" => "checked" ) );
						$oResponse->setFormElement( 'form1', 'nLoadedClosed', array( "value" => "1" ), 1 );
						$nClosed = 1;
					}
					else if( $aData['status'] == "cancel" )
					{
						$oResponse->setFormElement( 'form1', 'nCanceled', array( "value" => "1" ) );
						$oResponse->setFormElement( 'form1', 'nLoadedClosed', array( "value" => "1" ) );
					}
					else
					{
						//$oResponse->setFormElement( 'form1', 'nClosed', array( "checked" => "" ) );
						$oResponse->setFormElement( 'form1', 'nLoadedClosed', array( "value" => "0" ), 0 );
						$nClosed = 0;
					}
					
					$oResponse->setFormElement( 'form1', 'nElementsSet', array( 'value' => 1 ) );
					
					//Ако четем данните, вземаме склада, които е прочетен и го записваме.
					if( !empty( $aData ) 
						&& isset( $aData['id_source'] ) 
						&& !empty( $aData['id_source'] ) )
					{
						$aStorage = array();
						$aStorage['nID'] = $aData['id_source'];
						$aStorage['sStorageType'] = $aData['source_type'];
						
						$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aData['id_source'] ) );
						$oResponse->setFormElement( 'form1', 'nIDDestName', array( 'value' => $aData['id_dest'] ) );
					}
					if( !empty( $aData )
						&& isset( $aData['id_dest'] ) 
						&& !empty( $aData['id_dest'] ) )
					{
						$aStorage = array();
						$aStorage['nID'] = $aData['id_source'];
						$aStorage['sStorageType'] = $aData['source_type'];
						
						$oResponse->setFormElement( 'form1', 'nIDSourceName', array( 'value' => $aData['id_source'] ) );
						$oResponse->setFormElement( 'form1', 'nIDDestName', array( 'value' => $aData['id_dest'] ) );
					}
				}
				else
				{
					//Ако имаме склад от параметри, вземаме под внимание него.
					$nClosed = 0;
					
					$aStorage = array();
					$aStorage['nID'] = 				Params::get( "nIDSourceName", 0 );
					$aStorage['sStorageType'] = 	Params::get( "sSendType", "" );
				}
				
				$oPPP->getReport( $aParams, $oResponse, $aStorage, $nClosed );
				
				//Set Edit Fields
				$sQuery = "
						SELECT
							CONCAT_WS( ' ', pc.fname, pc.mname, pc.lname ) 	AS created,
							CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ) 	AS edited,
							CONCAT_WS( ' ', pa.fname, pa.mname, pa.lname ) 	AS confirmed,
							pc.id 											AS id_created
						FROM ppp p
							LEFT JOIN {$db_name_personnel}.personnel pc ON pc.id = p.created_user
							LEFT JOIN {$db_name_personnel}.personnel pe ON pe.id = p.updated_user
							LEFT JOIN {$db_name_personnel}.personnel pa ON pa.id = p.confirm_user
						WHERE 1
							AND p.id = {$nID}
						LIMIT 1
				";
				
				$aEditFields = $oPPP->selectOnce( $sQuery );
				
				if( !empty( $aEditFields ) )
				{
					if( empty( $aEditFields['created'] ) )
					{
						$oResponse->setFormElement( 'form1', 'sCreatedBy', array( "value" => 'Не е посочен' ), 'Не е посочен' );
					}
					else
					{
						$oResponse->setFormElement( 'form1', 'sCreatedBy', array( "value" => $aEditFields['created'] ), $aEditFields['created'] );
					}
					if( empty( $aEditFields['edited'] ) )
					{
						$oResponse->setFormElement( 'form1', 'sEditedBy', array( "value" => 'Не е посочен' ), 'Не е посочен' );
					}
					else
					{
						$oResponse->setFormElement( 'form1', 'sEditedBy', array( "value" => $aEditFields['edited'] ), $aEditFields['edited'] );
					}
					if( empty( $aEditFields['confirmed'] ) )
					{
						$oResponse->setFormElement( 'form1', 'sConfirmedBy', array( "value" => 'Не е посочен' ), 'Не е посочен' );
					}
					else
					{
						$oResponse->setFormElement( 'form1', 'sConfirmedBy', array( "value" => $aEditFields['confirmed'] ), $aEditFields['confirmed'] );
					}
				}
				//End Set Edit Fields
				
				//Set Edit Permissions for MOLs and Creators
				$nIDLoggedUser = $_SESSION['userdata']['id_person'];
				
				$nIDCreatedUser = (int) $aEditFields['id_created'];
				
				if( $aData['source_type'] == 'storagehouse' && $aData['dest_type'] == 'storagehouse' )
				{
					$aSourceMOL = $oStoragehouses->getMOL( $aData['id_source'] );
					$aDestMOL = $oStoragehouses->getMOL( $aData['id_dest'] );
					
					$nIDSourceMOL 	= isset( $aSourceMOL['mol_id'] ) 	? $aSourceMOL['mol_id'] : 0;
					$nIDDestMOL 	= isset( $aDestMOL['mol_id'] ) 		? $aDestMOL['mol_id'] 	: 0;
					
					$nReadOnly = 1;
					$nOnlyValidate = 0;
					
					if( !empty( $nIDDestMOL ) && $nIDDestMOL == $nIDLoggedUser )
					{
						$nOnlyValidate = 1;
					}
					if( ( !empty( $nIDSourceMOL ) && $nIDSourceMOL == $nIDLoggedUser ) || ( !empty( $nIDCreatedUser ) && $nIDCreatedUser == $nIDLoggedUser ) )
					{
						$nOnlyValidate = 0;
						$nReadOnly = 0;
					}
					
					$oResponse->setFormElement( 'form1', 'nReadOnly', array( "value" => $nReadOnly ) );
					$oResponse->setFormElement( 'form1', 'nOnlyValidate', array( "value" => $nOnlyValidate ) );
				}
				//End Set Edit Permissions for MOLs and Creators
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse )
		{
			$nLoadedClosed = Params::get( "nLoadedClosed", 0 );
			
			if( $nLoadedClosed )
				throw new Exception( 'Протокола е потвърден!', DBAPI_ERR_INVALID_PARAM );
			
			$aParams		= Params::getAll();
			$nClosed		= Params::get( "nClosed", 0 );
			$nPseudoSave	= Params::get( "nPseudoSave", 0 );
			$nID			= Params::get( "nID", 0 );
			$nIDLimitCard	= Params::get( "nIDLimitCard", 0 );
			
			$nDay			= Params::get( "nDay", 0 );
			$nMonth			= Params::get( "nMonth", 0 );
			$nYear			= Params::get( "nYear", 0 );
			$sHour			= Params::get( "sHour", date('H:i:s') );
			$sSourceName 	= Params::get( "sSourceName", '' );
			$sDestName 		= Params::get( "sDestName", '' );
			$sSentBy 		= Params::get( "sSentBy", '' );
			$sReceivedBy 	= Params::get( "sReceivedBy", '' );
			$sSourceType 	= Params::get( "sSendType", 'object' );
			$sDestType 		= Params::get( "sReceiveType", 'object' );
			$sNote 			= Params::get( "sNote", '' );
			
			if ( $sHour == '00:00:00' ) {
				$sHour = date('H:i:s');
			}
			
			$sSourceDate = $nYear . '-' . $nMonth . '-' . $nDay . ' ' . $sHour;
			
			
			
			$aData 					= array();
			$aData['id'] 			= $nID;
			$aData['id_limit_card'] = $nIDLimitCard;
			$aData['source_date'] 	= $sSourceDate;
			
			$nIsThereObject = 0;
			$nIsThereStoragehouse = 0;
			
			$nStoreToStore = 0;
			$nObjectToObject = 0;
			
			$aData['source_type'] = $sSourceType;
			$aData['id_source'] = Params::get( 'nIDSourceName', 0 );
			
			if( empty( $aData['id_source'] ) )
			{
				//Try to get by name
				switch( $aData['source_type'] )
				{
					case 'person':
						$oPersonnel = new DBPersonnel();
						$aSource = $oPersonnel->getPersonnelByNames( $sSourceName );
						if( !empty( $aSource ) )$aData['id_source'] = $aSource['id'];
						else $sSType = "Служител";
					break;
					case 'storagehouse':
						$oStoragehouses = new DBStoragehouses();
						$aSource = $oStoragehouses->getStoragehouseByName( $sSourceName );
						if( !empty( $aSource ) )$aData['id_source'] = $aSource['id'];
						else $sSType = "Склад";
						$nIsThereStoragehouse = 1;
					break;
					case 'object':
						$oObjects = new DBObjects();
						$sSourceName = $this->removeNumber( $sSourceName );
						$aSource = $oObjects->getObjectsByName( $sSourceName );
						if( !empty( $aSource ) )$aData['id_source'] = $aSource['id'];
						else $sSType = "Обект";
						$nIsThereObject = 1;
					break;
					case 'client':
						$oClients = new DBClients();
						$aSource = $oClients->getClientByName( $sSourceName );
						if( !empty( $aSource ) )$aData['id_source'] = $aSource['id'];
						else $sSType = "Доставчик";
					break;
					
					default: break;
				}
			}
			else
			{
				if( $aData['source_type'] == 'object' )$nIsThereObject = 1;
				if( $aData['source_type'] == 'storagehouse' )$nIsThereStoragehouse = 1;
			}
			
			$aData['dest_type'] = $sDestType;
			$aData['id_dest'] = Params::get( 'nIDDestName', 0 );
			
			if( empty( $aData['id_dest'] ) )
			{
				//Try to get by name
				switch( $aData['dest_type'] )
				{
					case 'person':
						$oPersonnel = new DBPersonnel();
						$aDest = $oPersonnel->getPersonnelByNames( $sDestName );
						if( !empty( $aDest ) )$aData['id_dest'] = $aDest['id'];
						else $sRType = "Служител";
					break;
					case 'storagehouse':
						$oStoragehouses = new DBStoragehouses();
						$aDest = $oStoragehouses->getStoragehouseByName( $sDestName );
						if( !empty( $aDest ) )$aData['id_dest'] = $aDest['id'];
						else $sRType = "Склад";
						$nIsThereStoragehouse = 1;
					break;
					case 'object':
						$oObjects = new DBObjects();
						$sDestName = $this->removeNumber( $sDestName );
						$aDest = $oObjects->getObjectsByName( $sDestName );
						if( !empty( $aDest ) )$aData['id_dest'] = $aDest['id'];
						else $sRType = "Обект";
						$nIsThereObject = 1;
					break;
					case 'client':
						$oClients = new DBClients();
						$aDest = $oClients->getClientByName( $sDestName );
						if( !empty( $aDest ) )$aData['id_dest'] = $aDest['id'];
						else $sRType = "Доставчик";
					break;
					
					default: break;
				}
			}
			else
			{
				if( $aData['dest_type'] == 'object' )$nIsThereObject = 1;
				if( $aData['dest_type'] == 'storagehouse' )$nIsThereStoragehouse = 1;
			}
			
			if( $aData['source_type'] == 'storagehouse' && $aData['dest_type'] == 'storagehouse' )$nStoreToStore = 1;
			if( $aData['source_type'] == 'object' && $aData['dest_type'] == 'object' )$nObjectToObject = 1;
			
			//-- Право на Достъп : Обект към Обект
			$bObjectToObject = false;
			$bVirtualStorage = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'ppp_object_to_object', $_SESSION['userdata']['access_right_levels'] ) ) $bObjectToObject = true;
				if( in_array( 'ppp_virtual_storagehouse', $_SESSION['userdata']['access_right_levels'] ) ) $bVirtualStorage = true;
			}
			//--
			
			if( $nPseudoSave == 0 )
			{
				if( $nIDLimitCard != 0 )
				{
					if( $nIsThereObject == 0 )
					{
						throw new Exception( 'Няма посочен обект в записа!', DBAPI_ERR_INVALID_PARAM );
					}
				}
				if( $nIsThereStoragehouse == 0 && !$bObjectToObject )
				{
					throw new Exception( 'Няма посочен склад в записа!', DBAPI_ERR_INVALID_PARAM );
				}
				
				if( empty( $aData['id_source'] ) )
				{
					throw new Exception( "Наименованието на предаващ {$sSType} е невалидно!" , DBAPI_ERR_INVALID_PARAM );
				}
				
				if( empty( $aData['id_dest'] ) )
				{
					throw new Exception( "Наименованието на приемащ {$sRType} е невалидно!" , DBAPI_ERR_INVALID_PARAM );
				}
			}
			
			$aData['source_user'] = $sSentBy;
			$aData['dest_user'] = $sReceivedBy;
			$aData['description'] = $sNote;
			
			$oPPPElements = new DBPPPElements();
			$aData['price'] = $oPPPElements->calcElementsPrice( $nID );
			$aData['to_arc'] = $nPseudoSave;
			
			if( $nClosed && $nPseudoSave == 0 )
			{
				$oStates = new DBStates();
				
				//Check if Logged User is "MOL"
				$oStoragehouses = new DBStoragehouses();
				$oStoragehousesMols = new DBStoragehousesMols();
				
				$nRequestUser = $_SESSION['userdata']['id_person'];
				$aRequestStoragehouse = $oStoragehouses->getByMOLID( $nRequestUser );
				
				$bIsUserMol = false;
				if( $nStoreToStore == 0 )
				{
					if( $sSourceType == "storagehouse" && !empty( $aData['id_source'] ) )
					{
						$aStorageMols = $oStoragehousesMols->getAllPersons( $aData['id_source'] );
						
						foreach( $aStorageMols as $aStorageMol )
						{
							if( $aStorageMol['id'] == $nRequestUser )$bIsUserMol = true;
						}
						
						$aTitleMol = $oStoragehouses->getMOL( $aData['id_source'] );
						
						if( $aTitleMol['mol_id'] == $nRequestUser )$bIsUserMol = true;
					}
				}
				
				if( $sDestType == "storagehouse" && !empty( $aData['id_dest'] ) )
				{
					$aStorageMols = $oStoragehousesMols->getAllPersons( $aData['id_dest'] );
					
					foreach( $aStorageMols as $aStorageMol )
					{
						if( $aStorageMol['id'] == $nRequestUser )$bIsUserMol = true;
					}
					
					$aTitleMol = $oStoragehouses->getMOL( $aData['id_dest'] );
					
					if( $aTitleMol['mol_id'] == $nRequestUser )$bIsUserMol = true;
				}
				
				if( empty( $aRequestStoragehouse ) && $nObjectToObject == 0 )
					throw new Exception( "Не сте МОЛ на склад!", DBAPI_ERR_INVALID_PARAM );
				
				if( !$bIsUserMol && $nObjectToObject == 0 )
				{
					if( $nStoreToStore == 0 )
					{
						throw new Exception( "Не сте МОЛ на предаващия или приемащия склад!", DBAPI_ERR_INVALID_PARAM );
					}
					else
					{
						throw new Exception( "Не сте МОЛ на приемащия склад!", DBAPI_ERR_INVALID_PARAM );
					}
				}
				
				$aData['dest_date'] = date( 'Y-m-d H:i:s' );
				$aData['status'] = "confirm";
				$aData['confirm_user'] = $nRequestUser;
				
				$oStates->transmitNomenclatures( $aParams, $aData, $oResponse );
			}
			else
			{
				$oPPP = new DBPPP();
				$oPPP->update( $aData );
			}
			
			$oResponse->setFormElement( 'form1', 'nID', array( 'value' => $aData['id'] ) );
			
			$oResponse->printResponse();
		}
		
		public function purgeDatabase( DBResponse $oResponse )
		{
			$oPPP = new DBPPP();
			
			$nID = Params::get( "nID", 0 );
			$nCloseCancels = Params::get( "nCloseCancels", 1 );
			
			if( $nID != 0 )
			{
				$aPPP = $oPPP->getRecord( $nID );
				if( !empty( $aPPP ) )
				{
					if( $nCloseCancels == 0 )
					{
						if( $aPPP['to_arc'] == 1 )
						{
							$oPPP->select( "DELETE FROM ppp WHERE id = {$nID}" );
							$oPPP->select( "DELETE FROM ppp_elements WHERE id_ppp = {$nID}" );
						}
					}
					if( $nCloseCancels == 1 )
					{
						if( $aPPP['to_arc'] == 1 )
						{
							$oPPP->select( "UPDATE ppp SET to_arc = 0 WHERE id = {$nID}" );
						}
					}
				}
			}
		}
		
		public function refreshMOL( DBResponse $oResponse )
		{
			$oPPP 			= new DBPPP();
			$oPPPElements 	= new DBPPPElements();
			
			$aParams = Params::getAll();
			
			$oStoragehouses = 	new DBStoragehouses();
			$oPersonnel = 		new DBPersonnel();
			$oObjects = 		new DBObjects();
			$oFaces = 			new DBFaces();
			
			$sSourceName = 		Params::get( "sSourceName", 	'' );
			$nIDSourceName =	Params::get( "nIDSourceName", 	0  );
			$sDestName = 		Params::get( "sDestName", 		'' );
			$nIDDestName = 		Params::get( "nIDDestName", 	0  );
			
			$bObjectUnloads = isset( $aParams['nSetStorage'] ) ? ( $aParams['nSetStorage'] == "2" ? true : false ) : false;
			
			if( $aParams['sActiceElement'] == "sSourceName" || $bObjectUnloads )
			{
				if( $aParams['sSendType'] == 'storagehouse' )
				{
					if( !empty( $nIDSourceName ) )
					{
						$aSource = $oStoragehouses->getByID( $nIDSourceName );
					}
					else
					{
						$aSource = $oStoragehouses->getStoragehouseByName( $sSourceName );
					}
					if( !empty( $aSource ) )
					{
						$aMOL = $oStoragehouses->getMOL( $aSource['id'] );
						if( !empty( $aMOL ) && !empty( $aMOL['mol'] ) )
							$oResponse->setFormElement( 'form1', 'sSentBy', array( 'value' => $aMOL['mol'] ) );
					}
				}
				if( $aParams['sSendType'] == 'person' )
				{
					if( !empty( $nIDSourceName ) )
					{
						$aSource = $oPersonnel->getByID( $nIDSourceName );
					}
					else
					{
						$aSource = $oPersonnel->getPersonnelByNames( $sSourceName );
					}
					if( !empty( $aSource ) )
					{
						$aPerson = $oPersonnel->getPersonnelNames( $aSource['id'] );
						if( !empty( $aPerson ) && !empty( $aPerson['names'] ) )
							$oResponse->setFormElement( 'form1', 'sSentBy', array( 'value' => $aPerson['names'] ) );
					}
					
					if( !empty( $nIDSourceName ) && $aParams['nID'] != 0 )
					{
						//Load Nomenclatures For Object
						$oStates = new DBStates();
						
						$aPresentNomenclatures = $oStates->getNomenclaturesForPerson( $nIDSourceName );
						$oPPP->deleteNomenclatures( $aParams['nID'] );
						
						foreach( $aPresentNomenclatures as $aNomenclature )
						{
							$aNomData = array();
							
							$aNomData['id_ppp'] = $aParams['nID'];
							$aNomData['id_nomenclature'] = $aNomenclature['id'];
							$aNomData['count'] = $aNomenclature['count'];
							$aNomData['single_price'] = $aNomenclature['price'];
							$aNomData['client_own'] = 0;
							
							if( $aNomData['count'] > 0 )
							{
								$oPPPElements->update( $aNomData );
							}
						}
						//End Load Nomenclatures For Object
					}
				}
				if( $aParams['sSendType'] == 'object' )
				{
					if( !empty( $nIDSourceName ) )
					{
						$aSource = $oObjects->getByID( $nIDSourceName );
					}
					else
					{
						$aSource = $oObjects->getObjectByName( $sSourceName );
					}
					if( !empty( $aSource ) )
					{
						$nFace = $oObjects->getIdFace( $aSource['id'] );
						if( !empty( $nFace ) )
						{
							$aFace = $oFaces->getFace( $nFace );
						}
						if( !empty( $aFace ) )
						{
							$oResponse->setFormElement( 'form1', 'sSentBy', array( 'value' => $aFace['name'] ) );
						}
					}
					
					if( !empty( $nIDSourceName ) && $aParams['nID'] != 0 )
					{
						//Load Nomenclatures For Object
						$oStates = new DBStates();
						
						$aPresentNomenclatures = $oStates->getNomenclaturesToArcForObject( $nIDSourceName );
                        //да вземе дори изтрите номенклатури, защото на обекта може да има такива
						$oPPP->deleteNomenclatures( $aParams['nID'] );
						
						foreach( $aPresentNomenclatures as $aNomenclature )
						{
							$aNomData = array();
							
							$aNomData['id_ppp'] = $aParams['nID'];
							$aNomData['id_nomenclature'] = $aNomenclature['id'];
							$aNomData['count'] = $aNomenclature['count'];
							$aNomData['single_price'] = $aNomenclature['price'];
							$aNomData['client_own'] = 0;
							
							if( $aNomData['count'] > 0 )
							{
								$oPPPElements->update( $aNomData );
							}
						}
						//End Load Nomenclatures For Object
					}
				}
			}
			
			if( $aParams['sActiceElement'] == "sDestName" )
			{
				if( $aParams['sReceiveType'] == 'storagehouse' )
				{
					if( !empty( $nIDDestName ) )
					{
						$aDest = $oStoragehouses->getByID( $nIDDestName );
					}
					else
					{
						$aDest = $oStoragehouses->getStoragehouseByName( $sDestName );
					}
					if( !empty( $aDest ) )
					{
						$aMOL = $oStoragehouses->getMOL( $aDest['id'] );
						if( !empty( $aMOL ) && !empty( $aMOL['mol'] ) )
							$oResponse->setFormElement( 'form1', 'sReceivedBy', array( 'value' => $aMOL['mol'] ) );
					}
				}
				if( $aParams['sReceiveType'] == 'person' )
				{
					if( !empty( $nIDDestName ) )
					{
						$aDest = $oPersonnel->getByID( $nIDDestName );
					}
					else
					{
						$aDest = $oPersonnel->getPersonnelByNames( $sDestName );
					}
					if( !empty( $aDest ) )
					{
						$aPerson = $oPersonnel->getPersonnelNames( $aDest['id'] );
						if( !empty( $aPerson ) && !empty( $aPerson['names'] ) )
							$oResponse->setFormElement( 'form1', 'sReceivedBy', array( 'value' => $aPerson['names'] ) );
					}
				}
				if( $aParams['sReceiveType'] == 'object' )
				{
					if( !empty( $nIDDestName ) )
					{
						$aDest = $oObjects->getByID( $nIDDestName );
					}
					else
					{
						$aDest = $oObjects->getObjectByName( $sDestName );
					}
					if( !empty( $aDest ) )
					{
						$nFace = $oObjects->getIdFace( $aDest['id'] );
						if( !empty( $nFace ) )
						{
							$aFace = $oFaces->getFace( $nFace );
						}
						if( !empty( $aFace ) )
						{
							$oResponse->setFormElement( 'form1', 'sReceivedBy', array( 'value' => $aFace['name'] ) );
						}
					}
				}
			}
			
			//Refresh Nomenclatures
			$aStorage = array();
			$aStorage['nID'] = 				Params::get( "nIDSourceName", 0 );
			$aStorage['sStorageType'] = 	Params::get( "sSendType", "" );
			$nClosed = 						Params::get( "nClosed", 0 );
			
			$oPPP->getReport( $aParams, $oResponse, $aStorage, $nClosed );
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID = Params::get( "nIDElement", 0 );
			
			$oPPPElements = new DBPPPElements();
			$oPPPElements->delete( $nID );
			
			$oResponse->printResponse();
		}
		
		function deleteAll( DBResponse $oResponse )
		{
			$oPPPElements = new DBPPPElements();
			$chk = Params::get( 'chk', 0 );
			
			foreach( $chk as $k => $v )
			{
				if( !empty( $v ) )
				{
					$oPPPElements->delete( $k );
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function cancel( DBResponse $oResponse )
		{
			$nID = Params::get( 'nID', 0 );
			
			$oPPP = new DBPPP();
			$aPPP = $oPPP->getRecord( $nID );
			
			if( !empty( $aPPP ) && $aPPP['to_arc'] == 0 )
			{
				$aPPP['status'] = "cancel";
				$oPPP->update( $aPPP );
			}
			else
			{
				throw new Exception( "ППП не е запазен!", DBAPI_ERR_UNKNOWN );
			}
			
			$oResponse->printResponse();
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
	}

?>