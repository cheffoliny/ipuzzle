<?php		
	$oPerson = 				New DBBase( $db_personnel, 	'personnel' );
	$oProfile = 			New DBBase( $db_system, 	'access_profile' );
	$oAccount = 			New DBBase( $db_system, 	'access_account' );
	$oRegions = 			New DBBase( $db_sod, 		'offices' );
	$account_office =		New DBBase( $db_system, 	'account_office' );
	
	$db_personnel->debug=true;
	$db_system->debug=true;
	$db_sod->debug=true;
	
	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
			}			
			
			function Handler( $aParams ) {
				global $oResponse;
				$aData = array();

				switch( $aParams['api_action']) {
					case "save" :
						$this->SaveData( $aParams );
					break;
					default :
						$aFirm = array();
						$aRegion = array();
						if ( !isset($aParams['nIDFirm']) ) $aParams['nIDFirm'] = 0;
						
						$person_regions = $aRegion = $aPerson = $aProfile = $aCurrentPerson = $person_regions = array();
						
						APILog::log(0, $aParams);
						
						if ( $nResult = $this->loadFirms( $aFirm ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						
						
//						if ( $nResult = $this->loadAllPersons( $region, $aPerson ) != DBAPI_ERR_SUCCESS ) {
//							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
//							print( $oResponse->toXML() );
//							return $nResult;
//						}

						if ( $nResult = $this->loadProfile( $aProfile ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
								
						$oResponse->setFormElement('form1', 'nIDFirm',	array(), '');
						$oResponse->setFormElement('form1', 'id_profile',	array(), '');
						$oResponse->setFormElement('form1', 'all_regions', array(), '');
						$oResponse->setFormElement('form1', 'account_regions', array(), '');
	
						if ( $aParams['api_action'] == 'result' ) {
							
							if ( $nResult = $this->loadPerson( $aParams['id'], $aCurrentPerson ) != DBAPI_ERR_SUCCESS ) {
								$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
								print( $oResponse->toXML() );
								return $nResult;
							}
							
							$CP = each( $aCurrentPerson );
							
							$nIDFirm = !empty($aParams['nIDFirm']) ? $aParams['nIDFirm'] : $CP['value']['id_firm'];

							if ( $nIDFirm > 0 ) {
								if ( $nResult = $this->loadAllRegions( $nIDFirm, $aRegion ) != DBAPI_ERR_SUCCESS ) {
									$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
									print( $oResponse->toXML() );
									return $nResult;
								}
							}
							
							if ( !empty($aParams['id']) ) {
								if ( $nResult = $this->getRegionByPerson( $aParams['id'], $person_regions ) != DBAPI_ERR_SUCCESS ) {
									$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
									print( $oResponse->toXML() );
									return $nResult;
								}
								
								$oResponse->setFormElement('form1', 'username',  array(), $CP['value']['username']);
								$oResponse->setFormElement('form1', 'person',	 array(), $CP['value']['name']);
								$oResponse->setFormElement('form1', 'id_person', array(), $CP['value']['id_person']);
								$oResponse->setFormElement('form1', 'row_limit', array(), $CP['value']['row_limit']);
								
								foreach ( $aFirm as $id_firm => $firm_name ) {
									if ( $nIDFirm == $id_firm ) {
										$sel = array('selected' => 'selected');
									} else $sel = array();
														
									$oResponse->setFormElementChild('form1', 'nIDFirm',	array_merge(array('value' => $id_firm), $sel), $firm_name['name']);
								}
																	
								if ( in_array( 0, $person_regions ) ) {
									$oResponse->setFormElementChild('form1', 'account_regions',	array('value' => 0), "Всички");
									
									foreach ( $aRegion as $id_region => $region_name ) {
										$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => $id_region), $region_name['name']);
									}	
								} else {
									$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => 0), "Всички");
									
									foreach ( $aRegion as $id_region => $region_name ) {
										if ( !in_array( $id_region, $person_regions ) ) {
											$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => $id_region), $region_name['name']);
										} else {
											$oResponse->setFormElementChild('form1', 'account_regions',	array('value' => $id_region), $region_name['name']);
										}
									}	

								}
								
								foreach ( $aProfile as $id_profile => $profile_name ) {	
									if ( $CP['value']['id_profile'] == $id_profile ) {
										$sel = array('selected' => 'selected');
									} else $sel = array();
												
									$oResponse->setFormElementChild('form1', 'id_profile',	array_merge(array('value' => $id_profile), $sel), $profile_name['name']);
								}						
							
							} else {
								
								$oResponse->setFormElementChild('form1', 'nIDFirm',	array('value' => 0), "Изберете");
								foreach ( $aFirm as $id_firm => $firm_name ) {
									if ( $nIDFirm == $id_firm ) {
										$sel = array('selected' => 'selected');
									} else $sel = array();
									
									$oResponse->setFormElementChild('form1', 'nIDFirm',	array_merge(array('value' => $id_firm), $sel), $firm_name['name']);
								}

								if ( $aParams['nIDFirm'] > 0 ) {
									if ( $nResult = $this->loadAllRegions( $aParams['nIDFirm'], $aRegion ) != DBAPI_ERR_SUCCESS ) {
										$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
										print( $oResponse->toXML() );
										return $nResult;
									}
								}
								
								$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => 0), "Всички");
								foreach ( $aRegion as $id_region => $region_name ) {
									$oResponse->setFormElementChild('form1', 'all_regions',	array('value' => $id_region), $region_name['name']);
								}

								$oResponse->setFormElementChild('form1', 'id_profile',	array('value' => 0), "Всички");
								foreach ( $aProfile as $id_profile => $profile_name ) {	
									$oResponse->setFormElementChild('form1', 'id_profile',	array('value' => $id_profile), $profile_name['name']);
								}																													
							}
							
						}
																								
						print( $oResponse->toXML() );
					break;
				}
			}
			
			
			function SaveData( $aParams ) {
				global $oAccount, $oPerson, $oResponse, $db_system;

				if( empty( $aParams['username']) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете псевдоним на потребитела!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				$aDuplicate = array();
				$nUser = !empty( $aParams['username'] ) ? $aParams['username'] : "";
				$aWhere[] = " id_person != {$aParams['id_person']} ";
				$aWhere[] = " username = '{$nUser}' ";
				$aWhere[] = " to_arc = 0 ";
				
				if ( $nResult = $oAccount->getResult( $aDuplicate, NULL, $aWhere ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( DBAPI_ERR_SQL_QUERY, "Проблем при комуникацията!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_SQL_QUERY;
				}

				if ( !empty( $aDuplicate ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Вече съществува потребител с този псевдоним!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}		
				
				if ( empty( $aParams['id_person'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете име на служител!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if ( empty( $aParams['id_profile'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Изберете профил на служитела!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				if ( empty( $aParams['row_limit'] ) ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете лимит на записите по страници!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return DBAPI_ERR_INVALID_PARAM;
				}

				$aAccount = array();
				$aAccount['id'] 			= (int) $aParams['id'];
				$aAccount['id_person'] 		= (int) $aParams['id_person'];
				//$aAccount['id_office'] 		= (int) $aParams['nIDOffice'];
				$aAccount['username'] 		= $aParams['username'];
				$aAccount['row_limit'] 		= (int) $aParams['row_limit'];
				$aAccount['id_profile'] 	= (int) $aParams['id_profile'];
				
				$db_system->StartTrans();
				
				if( $nResult = $oAccount->update( $aAccount ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
					return $nResult;
				}
				
				//APILog::log(0, $aAccount);
				$region_access = "";
				if ( !empty($aParams['account_regions']) ) {
					foreach ( $aParams['account_regions'] as $val ) {
						$region_access .= "('".$aAccount['id']."', '".$val."'), ";
					}
					
					$db_system->Execute( "DELETE FROM account_office WHERE id_account = {$aAccount['id']}" );
					
					$region_access = substr( $region_access, 0, -2 );
					$qry = "
						INSERT INTO account_office 
							(id_account, id_office) 
						VALUES
							{$region_access}
					";
					
					$db_system->Execute( $qry );
				}
				
				$db_system->CompleteTrans();
				
				print( $oResponse->toXML() );
												
				return DBAPI_ERR_SUCCESS;
			}
				
			function loadData( $nID, &$aData ) {
				global $oResponse, $db_name_sod;
				
				$id = (int) $nID;
				
				if ( $id > 0 ) {
					$aData = array();
					$aWhere = array();
					
					$aWhere[] = sprintf(" p.id = '%d' ", $id );
					$aWhere[] = sprintf(" p.to_arc = 0 " );

					$sQuery = sprintf(" 
						SELECT 
							p.id,
							p.id as _id, 
							p.id as id_person,
							p.id_office,
							o.name as region,
							p.id_region_object AS id_object,
							r.name as object,
							o.id_firm AS id_firm,
							f.name AS firm,
							p.id_position,
							pp.name AS position,
							DATE_FORMAT(t.date_in, '%%d.%%m.%%Y') AS date_in,
							IF ( t.date_out > '2000-12-31', DATE_FORMAT(t.date_out, '%%d.%%m.%%Y'), '') AS date_out,
							p.length_service,
							p.status AS status
						FROM 
							personnel p
						LEFT JOIN personnel as up on up.id = t.updated_user
						LEFT JOIN {$db_name_sod}.offices as o on o.id = p.id_office
						LEFT JOIN {$db_name_sod}.firms as f on f.id = o.id_firm
						LEFT JOIN {$db_name_sod}.region_objects as r on r.id = p.id_region_object
						LEFT JOIN positions as pp on pp.id = p.id_position
					", 
					$this->_oBase->_sTableName
					);
					
					return $this->_oBase->getResult( $aData, $sQuery, $aWhere );
				}
			}
			
			
			function loadAllPersons( $nID, &$aData ) {
				global $oPerson, $oResponse, $db_name_sod;
				
				$id = (int) $nID;
				
				$aData = array();
				$aWhere = array();
				
				$aWhere[] = sprintf(" p.id_office = '%d' ", $id );
				$aWhere[] = sprintf(" p.to_arc = 0 " );

				$sQuery = sprintf(" 
					SELECT 
						p.id,
						p.id AS _id, 
						CONCAT('[', IF (f.id != 0, f.name, '--'), '/', IF (o.id != 0, o.name, '--'), '] ', CONCAT_WS(' ', p.fname, p.lname)) AS name
					FROM 
						%s p
					LEFT JOIN {$db_name_sod}.offices as o on o.id = p.id_office
					LEFT JOIN {$db_name_sod}.firms as f on f.id = o.id_firm
					LEFT JOIN positions as pp on pp.id = p.id_position
				", 
				$oPerson->_sTableName
				);
				
				return $oPerson->getResult( $aData, $sQuery, $aWhere, 'name' );
			}			

			function loadProfile( &$aData ) {
				global $oProfile, $oResponse;
				
				$aData = array();
				$aWhere = array();
				
				$sQuery = sprintf(" 
					SELECT 
						p.id,
						p.id AS _id, 
						p.name AS name
					FROM 
						%s p
				", 
				$oProfile->_sTableName
				);
				
				return $oProfile->getResult( $aData, $sQuery, $aWhere, 'name' );
			}			


			function loadAllRegions( $nIDFirm, &$aData ) {
				global $db_name_sod, $oRegions, $oResponse;
				
				$nIDFirm = (int) $nIDFirm;
				$aData = array();
				$aWhere = array();
				
				$sQuery = " 
					SELECT 
						o.id,
						o.id AS _id, 
						o.name AS name
					FROM 
						{$db_name_sod}.offices o
					WHERE 1
					AND id_firm = {$nIDFirm}
					AND o.to_arc = 0
				";
			//	APILog::log(0, $sQuery);
				return $oRegions->getResult( $aData, $sQuery, $aWhere, 'name' );
			}

			function loadFirms( &$aData ) {
				global $oRegions, $oResponse;
				
				$aData = array();
				$aWhere = array();
				
				$sQuery = " 
					SELECT 
						f.id,
						f.id AS _id, 
						f.name AS name
					FROM 
						firms f
					WHERE f.to_arc = 0
				";
			
				return $oRegions->getResult( $aData, $sQuery, $aWhere, 'name' );
			}
			
			function loadPerson( $nID, &$aData ) {
				global $oAccount, $oResponse, $db_name_personnel, $db_name_sod;
				
				$id = (int) $nID;
				
				$aData = array();
				$aWhere = array();
				
				$aWhere[] = sprintf(" a.id = '%d' ", $id );
				$aWhere[] = sprintf(" a.to_arc = 0 " );

				$sQuery = sprintf(" 
					SELECT 
						a.id,
						a.id AS _id,
						a.id_person AS id_person,
						CONCAT_WS(' ', p.fname, p.mname, p.lname) AS name,
						a.username AS username,
						a.id_profile AS id_profile,
						a.row_limit AS row_limit,
						p.id_office AS id_office,
						o.id_firm AS id_firm
					FROM 
						%s a
					LEFT JOIN {$db_name_personnel}.personnel as p on p.id = a.id_person
					LEFT JOIN {$db_name_sod}.offices as o on o.id = p.id_office
				", 
				$oAccount->_sTableName
				);
				
				return $oAccount->getResult( $aData, $sQuery, $aWhere, 'name' );
			}			
			
			function getRegionByPerson( $nID, &$aData ) {
				global $account_office, $db_name_sod, $oResponse;
				
				$id = (int) $nID;
				
				$aData = array();
				$aWhere = array();
				
				$aWhere[] = sprintf(" a.id_account = '%d' ", $id );

				$sQuery = sprintf(" 
					SELECT 
						a.id_office AS id,
						a.id_office
					FROM 
						%s a
				", 
				$account_office->_sTableName
				);
				
				return $account_office->getResult( $aData, $sQuery, $aWhere, 'id_office' );
			}						
						
		}

	$oHandler = new MyHandler( $oPerson, 'id', 'person_data', 'Служебни данни' );
	$oHandler->Handler( $aParams );	
?>