<?php
	$oPersonnels = new DBBase( $db_personnel, 'personnel' );
	
	//$db_personnel->debug=true;
//	APILog::Log(0, $_SESSION['userdata']['id_office']);
	
	class MyHandler 
		extends APIHandler 
		{
			
			function setFields( $aParams ) {
				global $oResponse;
				
				
				
				$nIDTab = isset($aParams['tabs']) ? $aParams['tabs'] : 0;
				$oTabs = new DBVisibleTabs();
				$aTabs = $oTabs->getTabsByID($nIDTab);
				$aData = array();

				if ( isset($aTabs[0]['data']) && !empty($aTabs[0]['data']) ) {
					$aData = unserialize($aTabs[0]['data']);
				}
				
				APILog::Log(0, $aData);

				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('person_data_view', $_SESSION['userdata']['access_right_levels']) ) {


					}
				}	


				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('personInfo_view', $_SESSION['userdata']['access_right_levels']) ) {

						if ( isset($aData['sCode']) && ($aData['sCode'] == 1) ) {
							$oResponse->setField( 'code', 'код', 'Сортирай по код', NULL, NULL, NULL, array('DATA_FORMAT' => DF_NUMBER) );
						}
					}
				}
				
				$oDBPersonnel = new DBPersonnel();
				$aConditions = Params::getAll();
				$Total = $oDBPersonnel->getCountPersonnel($aConditions);
				$Total = "Служители: ".$Total;
                $oResponse->addTotal( 'name', $Total);
                $oResponse->setField( 'name', 'име', 'Сортирай по име', NULL, "personnel", NULL, array('DATA_TOTAL' => 1));
				
			
				
				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('person_data_view', $_SESSION['userdata']['access_right_levels']) ) {
						if ( isset($aData['sPosition']) && ($aData['sPosition'] == 1) ) {
							$oResponse->setField( 'position_name', 	'длъжност', 'Сортирай по длъжност' );
						}
						
						if ( isset($aData['sCipher']) && ($aData['sCipher'] == 1) ) {
							$oResponse->setField( 'cipher', 	'шифър', 'Сортирай по шифър' );
						}
						
						if ( isset($aData['sPositionNC']) && ($aData['sPositionNC'] == 1) ) {
							$oResponse->setField( 'pn_name', 	'длъжност по НКИД', 'Сортирай по длъжност по НКИД' );
						}

						if ( isset($aData['sFirm']) && ($aData['sFirm'] == 1) ) {
							$oResponse->setField( 'firm_name', 'фирма', 'Сортирай по фирма' );
						}

						if ( isset($aData['sRegion']) && ($aData['sRegion'] == 1) ) {
							$oResponse->setField( 'region_name', 'регион', 'Сортирай по регион'  );
						}

						if ( isset($aData['sObject']) && ($aData['sObject'] == 1) ) {
							$oResponse->setField( 'object_name', 'обект', 'Сортирай по обект' );
						}
					}
				}	
				
				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('personInfo_view', $_SESSION['userdata']['access_right_levels']) ) {
						if ( isset($aData['sAddress']) && ($aData['sAddress'] == 1) ) {
							$oResponse->setField( 'address', 'адрес', 'Сортирай по адрес' );
						}
						
						if ( isset($aData['sPhone']) && ($aData['sPhone'] == 1) ) {
							$oResponse->setField( 'phone', 'телефон', 'Сортирай по телефон' );
						}

						if ( isset($aData['sMobile']) && ($aData['sMobile'] == 1) ) {
							$oResponse->setField( 'mobile', 'моб. телефон', 'Сортирай по телефон'  );
						}

						if ( isset($aData['sBusinessPhone']) && ($aData['sBusinessPhone'] == 1) ) {
							$oResponse->setField( 'business_phone', 'сл. телефон', 'Сортирай по телефон' );
						}

						if ( isset($aData['sEGN']) && ($aData['sEGN'] == 1) ) {
							$oResponse->setField( 'egn', 'ЕГН', 'Сортирай по ЕГН' );
						}

						if ( isset($aData['sLK_Num']) && ($aData['sLK_Num'] == 1) ) {
							$oResponse->setField( 'lk_num', '№ ЛК', 'Сортирай по лична карта' );
						}
						
						if ( isset($aData['sEducation']) && ($aData['sEducation'] == 1) ) {
							$oResponse->setField( 'education', 'образование', 'Сортирай по образование' );
						}
					}
				}	

				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('person_data_view', $_SESSION['userdata']['access_right_levels']) ) {
						if ( isset($aData['sDateFrom']) && ($aData['sDateFrom'] == 1) ) {
							$oResponse->setField( 'date_from', 'назначен', 'Сортирай по дата' );
						}
					}
				}	

				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('personInfo_view', $_SESSION['userdata']['access_right_levels']) ) {
						if ( isset($aData['sIBAN']) && ($aData['sIBAN'] == 1) ) {
							$oResponse->setField( 'iban', 'IBAN', 'Сортирай по IBAN' );
						}

						if ( isset($aData['sEmail']) && ($aData['sEmail'] == 1) ) {
							$oResponse->setField( 'email', 'email', 'Сортирай по email' );
						}
						
						if ( isset($aData['sMinSalary']) && ($aData['sMinSalary'] == 1) ) {
							$oResponse->setField( 'insurance', 	'мин.осиг.праг', 'Сортирай по мин.осиг.праг', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_CURRENCY ) );
						}
					}
				}	


				if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
					if ( in_array('person_data_view', $_SESSION['userdata']['access_right_levels']) ) {
						if ( isset($aData['sPeriod']) && ($aData['sPeriod'] == 1) ) {
							$oResponse->setField( 'period_', 'стаж', 'Сортирай по стаж' );
						}
						
						

						if ( isset($aData['sStatus']) && ($aData['sStatus'] == 1) ) {
							$oResponse->setField( 'status', 'статус', 'Сортирай по статус' );
						}

						if ( isset($aData['sDateVacate']) && ($aData['sDateVacate'] == 1) ) {
							$oResponse->setField( 'vacate_date', 'уволнен', 'Сортирай по дата' );
						}
					}
				}
				
				$oResponse->setField( 'factor', 'фактор', 'Сортирай по фактор', NULL, NULL, NULL, array( 'DATA_FORMAT' => DF_DIGIT ));
				$oResponse->setField( 'updated_user', '...', 'Сортиране по последно редактирал', 'images/dots.gif' );
				
					
			}			
			
			function getReport( $aParams ) {
				global $db_name_sod, $oPersonnels;
				
				if ( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
					$off = implode(",", $_SESSION['userdata']['access_right_regions']);
				} else $off = "";
				
				if($aParams['nIDFirm'] == -1) $aParams['nIDFirm'] = 0;
				
				$aWhere = array();
				//$aWhere[] = sprintf(" t.status = 'active' " );
				
				//$aWhere[] = sprintf(" pc.to_arc = 0 ");
				
				if( !empty($aParams['nIDFirm']) ) {
					$aWhere[] = sprintf(" f.id = '%s' ", addslashes( $aParams['nIDFirm'] ) );
				} else {
					if ( !empty($off) ) $aWhere[] = sprintf(" r.id IN(%s) ", addslashes( $off ) );
				}
				
				if( !empty($aParams['nIDOffice']) ) {
					$aWhere[] = sprintf(" r.id = '%s' ", addslashes( $aParams['nIDOffice'] ) );
				} else {
					if ( !empty($off) ) $aWhere[] = sprintf(" r.id IN(%s) ", addslashes( $off ) );
				}
													
				if( !empty($aParams['nIDObject']) )
					$aWhere[] = sprintf(" obj.id = '%s' ", addslashes( $aParams['nIDObject'] ) );
				
				if( !empty($aParams['sName']) )
					$aWhere[] = sprintf(" CONCAT_WS( ' ', t.fname, t.mname, t.lname ) LIKE '%%%s%%' ", addslashes( $aParams['sName'] ) );

				if( !empty($aParams['sStatus']) && $aParams['sStatus'] != 'all' ) {
					$aWhere[] = sprintf(" t.status = '%s' ", addslashes( $aParams['sStatus'] ) );
				}
				
				if( !empty($aParams['nPositions']) )
					$aWhere[] = sprintf(" t.id_position = '%s' ", addslashes( $aParams['nPositions'] ) );
					
				if( !empty($aParams['nMobile']) )
				{
					$nNum = addslashes( $aParams['nMobile'] );
					$nNum.='%';
					$aWhere[] = sprintf(" (t.mobile like '%%%s%%' OR t.phone like '%%%s%%' OR t.business_phone like '%%%s%%') ", $nNum, $nNum, $nNum );
				}
				
				$sQuery = sprintf(" 
					SELECT 
						SQL_CALC_FOUND_ROWS 
						t.id as _id, 
						t.id, 
						t.code, 
						t.mobile,
						t.tech_support_factor as factor,
						CONCAT_WS( ' ', t.fname, t.mname, t.lname) as name,
						r.name as region_name,
						f.name as firm_name,
						obj.name as object_name,
						p.name as position_name,
						t.egn,
						t.lk_num,
						t.phone,
						t.business_phone,
						t.address,
						t.iban,
						t.email,
						t.education,
						pn.name as pn_name,
						pn.cipher,
						pc.insurance,
						IF ( UNIX_TIMESTAMP(t.date_from) > 0, DATE_FORMAT(t.date_from, '%%d.%%m.%%Y'), '' ) as date_from,
						IF ( UNIX_TIMESTAMP(t.vacate_date) > 0, DATE_FORMAT(t.vacate_date, '%%d.%%m.%%Y'), '' ) as vacate_date,
						CASE t.status
							WHEN 'active' THEN 'активен'
							WHEN 'vacate' THEN 'напуснал'
							WHEN 'moved' THEN 'преместен'
						END as status,
						IF ( UNIX_TIMESTAMP(t.date_from) > 0 AND t.status = 'active', ROUND((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) / 2505600, 1), 0 )  as period,
						IF ( UNIX_TIMESTAMP(t.date_from) > 0 AND t.status = 'active', IF ( (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) > 2505600, CONCAT_WS( ' ', FORMAT((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) / 2505600, 0), 'м.'), CONCAT_WS( ' ', FORMAT((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) / 86400, 0), 'д.') ), '' )  as period_,
						CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user 
					FROM 
						%s t 
						LEFT JOIN {$db_name_sod}.objects obj ON obj.id = t.id_region_object
						LEFT JOIN {$db_name_sod}.offices r ON r.id = t.id_office
						LEFT JOIN {$db_name_sod}.firms f ON f.id = r.id_firm
						LEFT JOIN positions p ON p.id = t.id_position 
						LEFT JOIN personnel as up on up.id = t.updated_user
						LEFT JOIN positions_nc pn ON pn.id = t.id_position_nc
						LEFT JOIN person_contract pc ON pc.id_person = t.id AND pc.to_arc = 0
					", 
					$this->_oBase->_sTableName
				);
			
				//IF ( UNIX_TIMESTAMP(t.date_from) > 0 AND t.status = 'active', IF ( (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) > 2505600, CONCAT_WS( ' ', FORMAT((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) / 2505600, 0), 'м.'), CONCAT_WS( ' ', FORMAT((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP( t.date_from )) / 86400, 0), 'д.') ), '' )  as period,
				global $db_personnel_backup;
				$aData = array();
			
				return $oPersonnels->getReport( $aParams, $sQuery, $aWhere, NULL, $aData, $db_personnel_backup  );
			}
			
			function Handler( $aParams )
			{
				
				global $oResponse;

				switch ( $aParams['api_action'] ) {
					case 'load' :
						$nIDFirm	= isset($aParams['nIDFirm']) 	? $aParams['nIDFirm'] 	: 0;
						$nIDOffice	= isset($aParams['nIDOffice']) 	? $aParams['nIDOffice'] : 0;
						$nIDObject	= isset($aParams['nIDObject']) 	? $aParams['nIDObject'] : 0;
						$nIDTabs 	= isset($aParams['tabs']) 		? $aParams['tabs'] 		: 0;
						
						$person = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
						
						$oFirms		= new DBFirms();
						$oOffices	= new DBOffices();
						$oObjects	= new DBObjects();
						$oTabs		= new DBVisibleTabs();
						$oPositions	= new DBPositions();
						$aFirms		= $oFirms->getFirms4();
						$aTabs		= $oTabs->getTabsByPerson($person);
						$aPositions	= $oPositions->getPositions();
						
						if ( $nIDFirm == 0 ) {
							$nIDOffice = $_SESSION['userdata']['id_office'];
							$nIDFirm = $oOffices->getFirmByIDOffice( $nIDOffice );
						}
						
						//APILog::Log(0, "test: ".$nIDOffice);
						$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
						$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
						$oResponse->setFormElement('form1', 'nIDObject', array(), '');
						$oResponse->setFormElement('form1', 'nPositions', array(), '');
						$oResponse->setFormElement('form1', 'tabs', array(), '');
						$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => -1), 'Избери');
						$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Избери');
						$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), 'Избери');
						$oResponse->setFormElementChild('form1', 'nPositions', array('value' => 0), 'Избери');
						$oResponse->setFormElementChild('form1', 'tabs', array('value' => 0), 'Избери');
						
						foreach ( $aTabs as $key => $val ) {
							if ( $nIDTabs > 0 ) {
								if ($nIDTabs == $key) {
									$oResponse->setFormElementChild('form1', 'tabs', array('value' => $key, 'selected' => 'selected'), $val['name']);
								} else $oResponse->setFormElementChild('form1', 'tabs', array('value' => $key), $val['name']);
							} elseif ( $val['def'] == 1 ) {
								$oResponse->setFormElementChild('form1', 'tabs', array('value' => $key, 'selected' => 'selected'), $val['name']);
							} else $oResponse->setFormElementChild('form1', 'tabs', array('value' => $key), $val['name']);
						}

						foreach ( $aFirms as $key => $val ) {
							if ( $nIDFirm == $key ) {
								$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key, 'selected' => 'selected'), $val);
							} else $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
						}
						
						unset($key); unset($val);
						
						if ( $nIDFirm > 0 ) {
							$aOffices = $oOffices->getFirmOfficesRightAssoc( $nIDFirm );
							foreach ( $aOffices as $key => $val ) {
								if ( $nIDOffice == $key ) {
									$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key, 'selected' => 'selected'), $val['name']);
								} else $oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
							}
							
							if ( empty($nIDOffice) ) {
								$oResponse->setFormElement('form1', 'nIDObject', array(), '');
								//$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), 'Избери');
							}
						} else {
							$oResponse->setFormElement('form1', 'nIDObject', array(), '');
							//$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), 'Избери');
						}
						
						if ( $nIDOffice > 0 ) {
							$aObjects = $oObjects->getFoObjectsByOfficeAssoc( $nIDOffice );
							
							foreach ( $aObjects as $key => $val ) {
								if ( $nIDObject == $key ) {
									$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => $key, 'selected' => 'selected'), $val['name']);
								} else $oResponse->setFormElementChild('form1', 'nIDObject', array('value' => $key), $val['name']);
							}
							
							if ( empty($aObjects) ) {
								$oResponse->setFormElement('form1', 'nIDObject', array(), '');
							}
						} else {
							$oResponse->setFormElement('form1', 'nIDObject', array(), '');
							//$oResponse->setFormElementChild('form1', 'nIDObject', array('value' => 0), 'Избери');
						}
						
						unset($key); unset($val);
						
						foreach ($aPositions as $key=>$val) {
							$oResponse->setFormElementChild('form1', 'nPositions', array('value' => $key), $val);
						}
						
						print( $oResponse->toXML() );
						break;
					case 'result':
						
						//print( $oResponse->toXML() );
					break;
					case 'delete':
						$oTabs = new DBVisibleTabs();
						$nID = isset($aParams['tabs']) ? $aParams['tabs'] : 0;
						$oTabs->delete( $nID );
						print( $oResponse->toXML() );
					break;
				}

				APIHandler::Handler( $aParams );
				
			}
		}

	$oHandler = new MyHandler( $oPersonnels, 'name', 'personnel', 'Служители' );
		
	$oHandler->Handler( $aParams );
	
?>