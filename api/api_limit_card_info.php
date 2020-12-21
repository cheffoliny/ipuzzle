<?php
	class ApiLimitCardInfo {
		public function result( DBResponse $oResponse ) {
			
			$aData		= array();
			$aMol		= array();
			$aContract	= array();
			$nID		= Params::get("nID", 0);
			$nIDObject	= Params::get("nIDObject", 0);
			
			if ( $nID > 0 ) {
				$oLimitCard = new DBTechLimitCards();
				$oTechRequest = new DBTechRequests();

				$aData = $oLimitCard->getLimitCard( $nID );
				
				//APILog::Log(0, $aData);
				$nIDContract = isset($aData['id_contract']) && is_numeric($aData['id_contract']) ? $aData['id_contract'] : 0;
				
				if ( !empty($nIDContract) ) {
					$oContract = new DBContracts();
					$aContract = $oContract->getContractByID( $nIDContract );
				}
				
				
				if ( empty($aData['pstartdate']) ) $aData['pstartdate'] = date("d.m.Y");
				if ( empty($aData['penddate']) ) $aData['penddate'] = date("d.m.Y");
				if ( empty($aData['rstartdate']) ) $aData['rstartdate'] = date("d.m.Y");
				if ( empty($aData['renddate']) ) $aData['renddate'] = date("d.m.Y");
				
				if ( empty($aData['id_object']) && !empty($aData['id_contract']) && ($aData['type'] == 'create') && ($aData['created_type'] == 'automatic') ) {
					
					$aData['object'] = isset($aContract['obj_name']) ? $aContract['obj_name']." [от договор!!!]" : "[от договор!]";
					$aData['address'] = isset($aContract['obj_address']) ? $aContract['obj_address'] : "";
					$aData['distance'] = isset($aContract['obj_distance']) ? $aContract['obj_distance'] : "";
					$mol = isset($aContract['client_mol']) ? $aContract['client_mol'] : "";
					$phone = isset($aContract['client_phone']) ? $aContract['client_phone'] : "";
					
					//APILog::Log(0, $aContract);
				} else {
					$aMol = $oLimitCard->getMol( $aData['id_object'] );	
					//APILog::Log(0, $aMol);
					$mol = isset($aMol['name']) ? $aMol['name'] : "";
					$phone = isset($aMol['phone']) ? $aMol['phone'] : "";
				}
								
				$oResponse->setFormElement( 'form1', 'nNum', array(), zero_padding($aData['num']) );
				$oResponse->setFormElement( 'form1', 'sObject', array(), $aData['object'] );
				$oResponse->setFormElement( 'form1', 'sAddress', array(), $aData['address'] );
				$oResponse->setFormElement( 'form1', 'nIDObject', array(), $aData['id_object'] );
				$oResponse->setFormElement( 'form1', 'sDate', array(), $aData['created_time'] );
				$oResponse->setFormElement( 'form1', 'sStatus', array(), $aData['status'] );
				$oResponse->setFormElement( 'form1', 'sType', array(), $aData['type'] );
				$oResponse->setFormElement( 'form1', 'nDistance', array(), $aData['distance'] );
				$oResponse->setFormElement( 'form1', 'nArrangeCount', array(), $aData['arrange_count'] );
				$oResponse->setFormElement( 'form1', 'sMol', array(), $mol );
				$oResponse->setFormElement( 'form1', 'sPhone', array(), $phone );

				$oResponse->setFormElement( 'form1', 'sPlannedStartH', array(), $aData['pstarttime'] );
				$oResponse->setFormElement( 'form1', 'sPlannedStart', array(), $aData['pstartdate'] );
				$oResponse->setFormElement( 'form1', 'sPlannedEndH', array(), $aData['pendtime'] );
				$oResponse->setFormElement( 'form1', 'sPlannedEnd', array(), $aData['penddate'] );
				$oResponse->setFormElement( 'form1', 'sRealStartH', array(), $aData['rstarttime'] );
				$oResponse->setFormElement( 'form1', 'sRealStart', array(), $aData['rstartdate'] );
				$oResponse->setFormElement( 'form1', 'sRealEndH', array(), $aData['rendtime'] );
				$oResponse->setFormElement( 'form1', 'sRealEnd', array(), $aData['renddate'] );

				$oResponse->setFormElement( 'form1', 'note', array(), $aData['note'] );
				
				if ( !empty($aData['id_contract']) && ($aData['tech_type'] == 'contract') ) {
					$conNum = isset($aContract['contract_num']) ? $aContract['contract_num'] : 0;
					$conDate = isset($aContract['contract_date']) ? $aContract['contract_date'] : "";
					$rsName = isset($aContract['rs_name']) ? $aContract['rs_name'] : "";
					
					$oResponse->setFormElement( 'form1', 'sContract', array(), $conNum." / ".$conDate );
					$oResponse->setFormElement( 'form1', 'sRS', array(), $rsName );
					$oResponse->setFormElement( 'form1', 'nIDContract', array(), $nIDContract );
				}
				
				if ( $aData['type'] == 'holdup' ) {
					$reqNum = isset($aData['reqNum']) ? $aData['reqNum'] : 0;
					$reqDate = isset($aData['req_created_time']) ? $aData['req_created_time'] : "";
					$reqReason = isset($aData['holdup_reason']) ? $aData['holdup_reason'] : "";
					$reqInfo = isset($aData['reqInfo']) ? $aData['reqInfo'] : "";

					$oResponse->setFormElement( 'form1', 'sRequest', array(), $reqNum." / ".$reqDate );
					$oResponse->setFormElement( 'form1', 'sReason', array(), $reqReason );
					$oResponse->setFormElement( 'form1', 'reqInfo', array(), $reqInfo );
					$oResponse->setFormElement( 'form1', 'nIDRequest', array(), $reqNum );
				}


				if ( ($aData['type'] == 'destroy') || ($aData['type'] == 'arrange') ) {
					$reqNum = isset($aData['reqNum']) ? $aData['reqNum'] : 0;
					$reqDate = isset($aData['req_created_time']) ? $aData['req_created_time'] : "";
					$reqInfo = isset($aData['reqInfo']) ? $aData['reqInfo'] : "";

					$oResponse->setFormElement( 'form1', 'sDRequest', array(), $reqNum." / ".$reqDate );
					$oResponse->setFormElement( 'form1', 'reqDInfo', array(), $reqInfo );
					$oResponse->setFormElement( 'form1', 'nIDRequest', array(), $reqNum );
				}
			}

			$oResponse->printResponse("Задачи", "tech_requests");
		}

		function save( DBResponse $oResponse ) {
			$nID = Params::get("nID", 0);
//			$nNum = Params::get("nNum", 0);
			$nIDObject = Params::get("nIDObject", 0);
			$sStatus = Params::get("sStatus", 'active');
		
			$sType = Params::get("sType", 'create');
			$nDistance = Params::get("nDistance", 0);
			$nArrangeCount = Params::get("nArrangeCount", 0);

			$oLimitCard = new DBTechLimitCards();
			$oLCPersons = new DBLimitCardPersons();
			$oActiveSettings = new DBTechSettings();
			$aLCPersons = array();
			$setup = array();
			
			$sPlannedStart = jsDateToTimestamp( Params::get("sPlannedStart", '0000-00-00') );
			$sPlannedEnd = jsDateToTimestamp( Params::get("sPlannedEnd", '0000-00-00') );
			$sRealStart = jsDateToTimestamp( Params::get("sRealStart", '0000-00-00') );
			$sRealEnd = jsDateToTimestamp( Params::get("sRealEnd", '0000-00-00') );
			
			$sPlannedStartH = Params::get("sPlannedStartH", '');
			$sPlannedEndH = Params::get("sPlannedEndH", '');
			$sRealStartH = Params::get("sRealStartH", '');
			$sRealEndH = Params::get("sRealEndH", '');
			
			$month = date('Ym', $sRealStart);
			$dDate = date('d.m.Y', $sRealStart);
			
			$aLimitCard = $oLimitCard->getLimitCard($nID);
			if ( !empty($sRealEndH)) {
				if ( empty($sRealStart) || empty($sRealStartH) || empty($sRealEnd) )
					throw new Exception("Не може да приключите лимитната карта, \nбез да сте въвели час и дата за реален старт и реален край!");
				
				if (empty($aLimitCard['id_object']))
					throw new Exception("Не може да приключите лимитната карта, \nзащото към нея няма привързан обект!");
			
			}
			
			if ( !empty($sPlannedStartH) ) {
				$sPlannedStart = date("Y-m-d", $sPlannedStart)." ".$sPlannedStartH;
			} else {
				$sPlannedStart = '0';
			}

			if ( !empty($sPlannedEndH) ) {
				$sPlannedEnd = date("Y-m-d", $sPlannedEnd)." ".$sPlannedEndH;
			} else {
				$sPlannedEnd = '0';
			}

			if ( !empty($sRealStartH) ) {
				$sRealStart = date("Y-m-d", $sRealStart)." ".$sRealStartH;
			} else {
				$sRealStart = '0';
			}

			if ( !empty($sRealEndH) ) {
				$sRealEnd = date("Y-m-d", $sRealEnd)." ".$sRealEndH;
			} else {
				$sRealEnd = '0';
			}
			
			
			$note = Params::get("note", '');

			if ( strtotime($sPlannedStart) > strtotime($sPlannedEnd) ) {
				throw new Exception("Планирания старт не може да бъде по-голям от планирания край!!!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( $sType == 'arrange' && empty($nArrangeCount) ) {
				//throw new Exception("Въведете бройка на аранжираната техника!!!", DBAPI_ERR_INVALID_PARAM);
			}
	
			$setup = $oActiveSettings->getActiveSettings();

			$aLCPersons = array();
			$aLCPersons = $oLCPersons->getPersonByLC( $nID );
			
			foreach ( $aLCPersons as $key => $val ) {
				$nIDPerson = (int) $val['id_person'];
				
				$tmpArr = array();
				$tmpArr['per'] = $nIDPerson;
				$tmpArr['obj'] = $nIDObject;
				$tmpArr['start'] = $sPlannedStart;
				$tmpArr['end'] = $sPlannedEnd;
				
				$test = $oLCPersons->getPersonDub( $tmpArr );

				if ( isset($test['id_limit_card']) && !empty($test['id_limit_card']) ) {
					$lc = zero_padding($test['id_limit_card']);
					throw new Exception("Грешка при планиране: презастъпване с лимитна карта № {$lc}!!!", DBAPI_ERR_INVALID_PARAM);
				}
			}

			$priceDestroy = isset($setup['tech_price_destroy']) ? $setup['tech_price_destroy'] : 0;
			$priceArrange = isset($setup['tech_price_arrange']) ? $setup['tech_price_arrange'] : 0;
			$priceHoldup = isset($setup['tech_price_holdup']) ? $setup['tech_price_holdup'] : 0;
			//throw new Exception($priceArrange, DBAPI_ERR_INVALID_PARAM);
			
			if ( $sStatus == 'closed' ) {
				//APILog::Log(0, "okoto");
				
//				$aLCPersons = $oLCPersons->getPercentByLC( $nID );
//
////				Временно премахване на ограниченията на процента. Да се възстанови!!! / Павел
////				if ( !isset($aLCPersons['percent']) || ($aLCPersons['percent'] != 100) ) {
////					throw new Exception("Неправилно разпределение на процент трудово разпределение!!!", DBAPI_ERR_INVALID_PARAM);
////				}
//
//				$aLCPersons = array();
//				$aLCPersons = $oLCPersons->getPersonByLC( $nID );
//				
//				$oOperation = new DBLimitCardOperations();
//				$aOperation = array();
//				
//				$aOperation = $oOperation->getPriceOperationByLC($nID);
//				$aOperation = is_numeric($aOperation) && !empty($aOperation) ? $aOperation : 0;
//				
//				foreach ( $aLCPersons as $val ) {
//					$data = array();
//					$data['id_person'] = $val['id_person'];
//					$data['nIDObject'] = $nIDObject;
//					$data['nIDLC'] = $nID;
//					$data['month'] = $month;
//					$data['code'] = $val['code'];
//					$data['description'] = "Наработка".' ['.$val['code'].'/'.$dDate.'] - '.$sRealStartH.'/'.$sRealEndH;
//					$data['id_office'] = $val['id_office'];
//					$data['id_region_object'] = $val['id_region_object'];
//					
//					if ( $sType == 'create' ) {
//						$price = array();
//						$price = $oLCPersons->getPersonTechPercent( $nID );
//						
//						$data['price'] = number_format( $aOperation * ($val['percent']/100) * $val['factor'], 2, '.', '');
//					} else {
//						$price = array();
//						//$price = $oLCPersons->getPrices( );
//						
//						switch ($sType) {
//							case 'holdup':
//								$data['price'] = number_format( $priceHoldup * ($val['percent']/100) * $val['factor'], 2, '.', '');
//							break;
//							
//							case 'destroy':
//								$data['price'] = number_format( $priceDestroy * ($val['percent']/100) * $val['factor'], 2, '.', '');
//							break;
//							
//							case 'arrange':
//								$data['price'] = number_format( $priceArrange * $nArrangeCount * ($val['percent']/100) * $val['factor'], 2, '.', '');
//							break;
//							
//							default:
//								$data['price'] = 0;
//							break;
//						}
//					}
//					
//					$oLCPersons->setSalary( $data );						
//				}
//				//APILog::log(0, $data);
			}
			
//			$aData = array();
//			$aData['id'] = $nID;
////			$aData['id_object'] = $nIDObject;
//			if ($sStatus=='Активна') $aData['status']='active';
//			$aData['type'] = $sType;
//			$aData['distance'] = $nDistance;
//			$aData['arrange_count'] = $nArrangeCount;
//			
//			$aData['planned_start'] = $sPlannedStart == 0 ? '0000-00-00 00:00:00' : $sPlannedStart;
//			$aData['planned_end'] = $sPlannedEnd == 0 ? '0000-00-00 00:00:00' : $sPlannedEnd;
//			$aData['real_start'] = $sRealStart == 0 ? '0000-00-00 00:00:00' : $sRealStart;
//			if ($sRealEnd == 0) {
//				$aData['real_end'] =  '0000-00-00 00:00:00';
//			} 
//			 else {
//			 	$aData['status']='closed';
//			 	$aData['real_end'] = $sRealEnd;
//				$oLimitCard->setSalaries( $nID ); 
//			 }
//			 
//			$aData['note'] = $note;
//			
////			APILog::Log(0, $aData);
//			$oLimitCard->update( $aData );
			
			$oResponse->printResponse();
		}

		function limit( DBResponse $oResponse ) {
			$chk = Params::get('chk', 0);
			$bla = array();
			$oTechRequests = new DBTechRequests();
			
			foreach( $chk as $k => $v ) {
				if ( !empty($v) ) {
					array_push($bla, $k);
				}
			}
			
			if ( !empty($bla) ) {	
				$par = implode( ",", $bla );	
				$oTechRequests->makeLimitCard( $par );
			}
			
			$oResponse->printResponse();
		}

		function cancel( DBResponse $oResponse ) {
			global $db_sod;
			
			$nID = Params::get("nID", 0);

			$oLimitCard = new DBTechLimitCards();
			$oTechRequests = new DBTechRequests();
			$oLimitCardPersons = new DBLimitCardPersons(); 
			$oPPP = new DBPPP();
			
			$oPPP->delPPPByIDLimitCard($nID);
			$oLimitCardPersons->delPersonByLC( $nID );
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['status'] = 'cancel';
			
			$oLimitCard->update($aData);
			
			$sQuery = "
				UPDATE tech_requests
				SET id_limit_card = 0,
					to_arc = 1
				WHERE id_limit_card = {$nID}
			";

			$db_sod->Execute($sQuery);
			
			$oResponse->printResponse();
		}
		
		function cancel2( DBResponse $oResponse ) {
			global $db_sod;
			
			$nID = Params::get("nID", 0);

			$oLimitCard = new DBTechLimitCards();
			$oTechRequests = new DBTechRequests();
			$oLimitCardPersons = new DBLimitCardPersons(); 
			$oPPP = new DBPPP();
			
			$oPPP->delPPPByIDLimitCard($nID);
			$oLimitCardPersons->delPersonByLC( $nID );
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['status'] = 'cancel';
			
			$oLimitCard->update($aData);
			
			$sQuery = "
				UPDATE tech_requests
				SET id_limit_card = 0
				WHERE id_limit_card = {$nID}
			";

			$db_sod->Execute($sQuery);
			
			$oResponse->printResponse();
		}
	}
?>