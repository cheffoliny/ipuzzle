<?php

	class ApiObjectInfo {
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');

			$oDBFirms = new DBFirms();
			$oDBObjectFunctions = new DBObjectFunctions();
			$oDBObjectTypes = new DBObjectTypes();
			$oDBStatuses = new DBStatuses();
			$oDBCities = new DBCities();
			
			$aFirms = $oDBFirms->getFirms4();
			$aObjectFunctions = $oDBObjectFunctions->getFunctions();
			$aObjectTypes = $oDBObjectTypes->getObjectTypes();
			$aStatuses = $oDBStatuses->getStatuses();
			//$aCities = $oDBCities->getCities();
			$sCity 		= "";
						
			$oResponse->setFormElement('form1', 'nIDFirm', 			array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', 		array(), '');
			$oResponse->setFormElement('form1', 'nIDReactionFirm', 	array(), '');
			$oResponse->setFormElement('form1', 'nIDReactionOffice',array(), '');
			$oResponse->setFormElement('form1', 'nIDTechFirm', 		array(), '');
			$oResponse->setFormElement('form1', 'nIDTechOffice', 	array(), '');
			$oResponse->setFormElement('form1', 'functions',		array(), '');
			$oResponse->setFormElement('form1', 'objtype',			array(), '');
			$oResponse->setFormElement('form1', 'statuses',			array(), '');
			
			$oResponse->setFormElement('form1', 'nIDArea', array(), '');
			
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "- Фирма администратор -");
			$oResponse->setFormElementChild('form1', 'nIDReactionFirm', array_merge(array("value"=>'0')), "- Реагираща фирма -");
			$oResponse->setFormElementChild('form1', 'nIDTechFirm', array_merge(array("value"=>'0')), "- Сервизна фирма -");
			$oResponse->setFormElementChild('form1', 'functions', array_merge(array("value"=>'0')), "- Дейност -");
			$oResponse->setFormElementChild('form1', 'objtype', array_merge(array("value"=>'0')), "- Тип обект -");
			$oResponse->setFormElementChild('form1', 'statuses', array_merge(array("value"=>'0')), "- Състояние -");
			//$oResponse->setFormElementChild('form1', 'nIDCity', array_merge(array("value"=>'0')), "--Изберете--");
			
			if ( !empty( $nID ) ) {  //Радактиране на обект
					
				$oDBObjects 	= new DBObjects();
				$oDBOffices 	= new DBOffices();
				$oDBObjectStatuses = new DBObjectStatuses();
				$oDBCityAreas 	= new DBCityAreas();
				$oDBCityStreets = new DBCityStreets();
				$oDBFaces 		= new DBFaces();
				
				$aObjectInfo 	= $oDBObjects->getInfoByID( $nID );
				
				$nIDCity 		= isset($aObjectInfo['address_city']) ? $aObjectInfo['address_city'] : 0;
				$sCity			= $oDBCities->getNameByID($nIDCity);
				
				$oResponse->setFormElement('form1', 'num', 			array(), $aObjectInfo['num']); //номер на обекта
                $oResponse->setFormElement('form1', 'oldNum',       array(), $aObjectInfo['num']); //номер на обекта
				$oResponse->setFormElement('form1', 'name', 		array(), $aObjectInfo['name']); //име на обекта
				$oResponse->setFormElement('form1', 'invoice_name', array(), $aObjectInfo['invoice_name']); //име на обекта за фактура
				$oResponse->setFormElement('form1', 'phone', 		array(), $aObjectInfo['phone']); //телефон на обекта
				$oResponse->setFormElement('form1', 'operativ_info',array(), $aObjectInfo['operativ_info']); //оперативна информация
                $oResponse->setFormElement('form1', 'tech_info',    array(), $aObjectInfo['tech_info']); //техническа информация
                $oResponse->setFormElement('form1', 'start_time', 	array(), $aObjectInfo['start_time']); //въведен в системата
                $oResponse->setFormElement('form1', 'work_time_alert', 	array(), $aObjectInfo['work_time_alert']); //въведен в системата
				$oResponse->setFormElement('form1', 'nIDCity', 		array(), $sCity);
				$oResponse->setFormElement('form1', 'id_city', 		array(), $aObjectInfo['address_city']);
                $oResponse->setFormElement('form1', 'geo_lat', 		array(), $aObjectInfo['geo_lat']);
                $oResponse->setFormElement('form1', 'geo_lan', 		array(), $aObjectInfo['geo_lan']);
				
				
				// ФИРМА
				
				foreach($aFirms as $key => $value) {
					if($key == $aObjectInfo['id_firm']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),$ch), $value);
				}
				
				// РЕГИОН
                $aOffices = $oDBOffices->getAllOfficesByIDFirm($aObjectInfo['id_firm']);
				//$aOffices = $oDBOffices->getOfficesByIDFirm($aObjectInfo['id_firm']);

                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
                foreach($aOffices as $key => $value) {
                    if($key == $aObjectInfo['id_office']) {
                        $ch = array( "selected" => "selected" );
                    } else {
                        $ch = array();
                    }
                    $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),$ch), $value);
                }

				// ФИРМА - РЕАКЦИЯ
				
				foreach($aFirms as $key => $value) {
					if($key == $aObjectInfo['id_reaction_firm']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDReactionFirm', array_merge(array("value"=>$key),$ch), $value);
				}
				
				
//				if (!empty($aObjectInfo['sod_under_executer'])) {
//					$oResponse->setFormElementChild('form1', 'nIDReactionFirm', array_merge(array("value"=>"-1"),array( "selected" => "selected" )),"Подизпълнител");
//					$oResponse->setFormElement('form1','have_sod_under_executer',array("value"=>"1"));
//					$oResponse->setFormElement('form1', 'sSodUnderExecuter', array(), $aObjectInfo['sod_under_executer']);
//				} else {
//					$oResponse->setFormElementChild('form1', 'nIDReactionFirm', array("value"=>"-1"), "Подизпълнител");
//					// РЕГИОН - РЕАКЦИЯ
		
					if (!empty($aObjectInfo['id_reaction_firm'])) {
						$aOffices = $oDBOffices->getOfficesByIDFirm($aObjectInfo['id_reaction_firm']);
						//throw new Exception($aObjectInfo['id_reaction_office']);
						$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>'0')), "--Офис на реакция--");
						foreach($aOffices as $key => $value) {
							if($key == $aObjectInfo['id_reaction_office']) {
								$ch = array( "selected" => "selected" );
							} else {
								$ch = array();
							}
							$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>$key),$ch), $value);
						}
					} else {
						$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
					}
				//}
				// ФИРМА - ТЕХНИЧЕСКА ПОДДРЪЖКА
				
				foreach($aFirms as $key => $value) {
					if($key == $aObjectInfo['id_tech_firm']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDTechFirm', array_merge(array("value"=>$key),$ch), $value);
				}
				
				// РЕГИОН - ТЕХНИЧЕСКА ПОДДРЪЖКА
				
				if (!empty($aObjectInfo['id_tech_firm'])) {	
					$aOffices = $oDBOffices->getTechOfficesByIDFirm($aObjectInfo['id_tech_firm']);
					
					$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>'0')), "--Изберете--");
					foreach($aOffices as $key => $value) {
						if($key == $aObjectInfo['id_tech_office']) {
							$ch = array( "selected" => "selected" );
						} else {
							$ch = array();
						}
						$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>$key),$ch), $value);
					}
				} else {
					$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
				}
				
				// НАЗНАЧЕНИЕ
				
				foreach($aObjectFunctions as $key => $value) {
					if($key == $aObjectInfo['id_function']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'functions', array_merge(array("value"=>$key),$ch), $value);
				}
				
				// ТИП
				
				foreach($aObjectTypes as $key => $value) {
					if($key == $aObjectInfo['id_objtype']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'objtype', array_merge(array("value"=>$key),$ch), $value);
				}
				
				// СТАТУС
				
				foreach($aStatuses as $key => $value) {
					if($key == $aObjectInfo['id_status']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'statuses', array_merge(array("value"=>$key),$ch), $value);
				}
				
				// НАСЕЛЕНО МЯСТО
				
//				foreach($aCities as $key => $value) {
//					if($key == $aObjectInfo['address_city'])	{
//						$ch = array( "selected" => "selected" );
//					} else {
//						$ch = array();
//					}
//					$oResponse->setFormElementChild('form1', 'nIDCity', array_merge(array("value"=>$key),$ch), $value);
//				}	
				
				// КВАРТАЛ
				
				$aCityAreas = $oDBCityAreas->getNamesByIDCity($aObjectInfo['address_city']);
				
				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aCityAreas as $key => $value) {
					if($key == $aObjectInfo['address_area']) {
						$ch = array( "selected" => "selected" );
					} else {
						$ch = array();
					}
					$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>$key),$ch), $value);
				}	
				
				
				if ( !empty($aObjectInfo['is_sod']) ) {
					$oResponse->setFormElement('form1','isSOD',array("checked" => "checked"));
				}
				
				if ( !empty($aObjectInfo['is_fo']) ) {
					$oResponse->setFormElement('form1','isFO',array("checked" => "checked"));
				}
				//APILog::Log(0, $aObjectInfo);
//				if ( !empty($aObjectInfo['is_tech']) ) {
//					$oResponse->setFormElement('form1', 'isTechPatrul', array(), 'isTech' );
//				}

//				if ( !empty($aObjectInfo['is_patrul']) ) {
//					$oResponse->setFormElement('form1', 'isTechPatrul', array(), 'isPatrul' );
//				}
				
//				if ( !empty($aObjectInfo['own_tech']) ) {
//					$oResponse->setFormElement('form1', 'own_tech', array("checked" => "checked") );
//				}

				//$sStreet = $oDBCityStreets->getNameByID($aObjectInfo['address_street']);
				$oResponse->setFormElement('form1', 'sAddress', array(), $aObjectInfo['address']);
				$oResponse->setFormElement('form1', 'nDistance', array(), $aObjectInfo['distance']);
//				$oResponse->setFormElement('form1', 'email',array(),$aObjectInfo['email']);
			//	$oResponse->setFormElement('form1', 'sOther', array(), $aObjectInfo['address_other']);
				$oResponse->setFormElement('form1', 'id_face', array(), $aObjectInfo['id_face']);
				
				
				$oDBFaces->getReport( $oResponse , $nID , $aObjectInfo['id_face']);
				
				
			} else {    // Нов обект
				
				// ФИРМА
				
				foreach($aFirms as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array("value"=>$key), $value);
					$oResponse->setFormElementChild('form1', 'nIDReactionFirm', array("value"=>$key), $value);
					$oResponse->setFormElementChild('form1', 'nIDTechFirm', array("value"=>$key), $value);
				}
				$oResponse->setFormElementChild('form1', 'nIDReactionFirm', array("value"=>"-1"), "Подизпълнител");
				
				// РЕГИОН
				
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			
				// РЕГИОН - РЕАКЦИЯ
				
				$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
				
				// РЕГИОН - ТЕХНИЧЕСКА ПОДДРЪЖКА
				
				$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
					
				// НАЗНАЧЕНИЕ	

				foreach($aObjectFunctions as $key => $value) {
					$oResponse->setFormElementChild('form1', 'functions', array("value"=>$key), $value);
				}
				
				// ТИП
				
				foreach($aObjectTypes as $key => $value) {
					$oResponse->setFormElementChild('form1', 'objtype',array("value"=>$key), $value);
				}
				
				// СТАТУС
				
				foreach($aStatuses as $key => $value) {
					$oResponse->setFormElementChild('form1', 'statuses', array("value"=>$key), $value);
				}
				
				// НАСЕЛЕНО МЯСТО
				
// 				foreach($aCities as $key => $value) {
// 					$oResponse->setFormElementChild('form1', 'nIDCity', array("value"=>$key), $value);
// 				}	
				
				$oResponse->setFormElementChild('form1', 'nIDArea', array("value"=>'0'), "Изберете населено място");
			}
			$oResponse->printResponse(); 
			
		}
		
		public function loadOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDFirm',0);

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');

			if( !empty( $nFirm ) ) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}
		
		public function loadReactionOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDReactionFirm',0);
			//throw new Exception($nFirm);
			$oResponse->setFormElement('form1', 'nIDReactionOffice', array(), '');

			if( !empty( $nFirm ) ) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getReactionOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>'0')), "--Офис на реакция--");
				foreach($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDReactionOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}
		
		public function loadTechOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDTechFirm',0);

			$oResponse->setFormElement('form1', 'nIDTechOffice', array(), '');

			if( !empty( $nFirm ) ) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getTechOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDTechOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}
		
		public function loadCityAreas(DBResponse $oResponse) {
			$nIDCity = Params::get('nIDCity');

			$oResponse->setFormElement('form1', 'nIDArea', array(), '');
			
			if( !empty( $nIDCity ) ) {
				$oDBCityAreas = new DBCityAreas();
				$aCityAreas = $oDBCityAreas->getNamesByIDCity($nIDCity);
					

				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "--Изберете--");
				foreach($aCityAreas as $key => $value) {
					$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>$key)), $value);
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDArea', array_merge(array("value"=>'0')), "Изберете населено място");
			}
			$oResponse->printResponse();
		}
		
		public function deleteFace() {
			$nID = Params::get('FaceID',0);
			$oDBFaces = new DBFaces();
			$oDBFaces->delete($nID);
		}

        public function closeServiceStatus() {
            $nIDObject = Params::get('nID', 0);
            $oDBObjects = new DBObjects();
            if ((int)$nIDObject > 0) {
                $oDBObjects->closeServiceStatus($nIDObject);
            }

        }

        public function setServiceStatus(DBResponse $oResponse){
            $nIDObject = Params::get('nID', 0);
            $oDBObjects = new DBObjects();
            //APILog::Log("1","da go");
            if ((int)$nIDObject > 0) {
                $oDBObjects->setServiceStatus($nIDObject);
            }
            $oResponse->printResponse();
        }

		public function save(DBResponse $oResponse) {
			global $db_name_sod, $db_sod;
			
			$aParams = Params::getAll();

			$oDBObjects = new DBObjects();
			$oObject 	= new DBObjects2();
			$oFirms		= new DBFirms();
			$oOffice	= new DBOffices();
			
			$nID		= 0;
			
			if(empty($aParams['num']))	{
					throw new Exception("Въведете номер на обекта!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($aParams['name']))	{
					throw new Exception("Въведете име на обекта!", DBAPI_ERR_INVALID_PARAM);
			}
			if(empty($aParams['nIDOffice'])) {
					throw new Exception("Изберете регион!", DBAPI_ERR_INVALID_PARAM);
			}	
			if(empty($aParams['statuses'])) {
					throw new Exception("Изберете статус!", DBAPI_ERR_INVALID_PARAM);
			}	


			$pOffice = $aParams['nIDOffice'];
			$pStatus = $aParams['statuses'];
			$pObjtype = $aParams['objtype'];
			// Promqna na status
			
			
			$object = $oDBObjects->getObjectById($aParams['nID']);
			$id_status = isset($object['id_status']) ? $object['id_status'] : 0;
		/*				
			if ( empty($pOffice) ) {
				throw new Exception("Избраният регион от Теленет няма аналог в PowerLink!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($pStatus) ) {
				throw new Exception("Избраният статус от Теленет няма аналог в PowerLink!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($pObjtype) && !empty($aParams['objtype']) ) {
				throw new Exception("Избраният тип на обекта от Теленет няма аналог в PowerLink!", DBAPI_ERR_INVALID_PARAM);
			}
		*/		
			if($aParams['statuses'] == 4 &&!empty($aParams['nID'])) {
				$oDBStates = new DBStates();
				
				$aObjectNomenclatures = array();
				
				$aObjectNomenclatures = $oDBStates->getNomenclaturesForObject($aParams['nID']);
				
				if(!empty($aObjectNomenclatures)) {
					throw new Exception("Статусът на обекта неможе да бъде 'неактивен', защото има зачисени номенклатури които са собственост на 'ИНФРА ЕООД'");
				}
			}

			
			//change_status
			$aData = array();
			$aOldData = array();
			
			$aData['id'] 			= $aParams['nID'];	
			$aData['name'] 			= $aParams['name'];
			$aData['invoice_name'] 	= $aParams['invoice_name'];
			$aData['id_objtype'] 	= $aParams['objtype'];
			$aData['phone'] 		= $aParams['phone'];
			$aData['num'] 			= $aParams['num'];	
			$aData['id_function'] 	= $aParams['functions'];
			$aData['id_status'] 	= $aParams['statuses'];
			$aData['id_office'] 	= $aParams['nIDOffice'];
			$aData['id_reaction_office'] = $aParams['nIDReactionOffice'];
			$aData['id_tech_office'] = $aParams['nIDTechOffice'];
			$aData['is_sod'] 		= $aParams['isSOD'];
			$aData['is_fo'] 		= $aParams['isFO'];
			$aData['address_city']	= $aParams['id_city'];
			$aData['address_area']	= $aParams['nIDArea'];
			$aData['address']		= $aParams['sAddress'];
			$aData['distance']		= $aParams['nDistance'];
            $aData['work_time_alert']= $aParams['work_time_alert'];
//			$aData['address_other'] = $aParams['sOther'];
			$aData['operativ_info'] = $aParams['operativ_info'];
            $aData['tech_info']     = $aParams['tech_info'];

			if($aParams['nIDReactionFirm'] == '-1') {
				$aData['sod_under_executer'] = $aParams['sSodUnderExecuter'];
				$aData['id_reaction_office'] = 0;
			} else {
				$aData['sod_under_executer'] = "";
				$aData['id_reaction_office'] = $aParams['nIDReactionOffice'];
			}
			
			
			if(empty($aParams['nID'])) {
				$aData['start'] = time();
			} else {
				$aObject = $oDBObjects->getByID($aParams['nID']);
			}

			$oDBObjects->update($aData);

			
//			$oDBObjectStatuses = new DBObjectStatuses();
//			$aData2['id_status'] = $aData['id_status'];
//			$aData2['id_obj'] = $aData['id'];
//
//			if( empty($aParams['nID']) ) {
//				$oDBObjectStatuses -> update($aData2);
//			} else {
//				if($aObject['id_status'] != $aData['id_status']) {
//					$oDBObjectStatuses -> update($aData2);
//				}
//			}
			
/*	
			if(!empty($aObject['id_oldobj'])) {
				$oDBObjects2 = new DBObjects2();
				$oDBObjects2->updateNum($aObject['id_oldobj'],$aData['num']);
			}
*/			
			$oResponse->setFormElement('form1', 'nID', array(), $aData['id']);
			$oResponse->setFormElement('form1', 'sName', array(), $aData['name']);
		//	$oResponse->setFormElement('form1', 'sNum', array(), $aData['num']);
			
			$oResponse->printResponse();
		}


        public function logObjectHistory()
        {
            $historyData = Params::get('historyData');
            $nIDObject= Params::get('id_object','');

			//ob_toFile(json_decode($historyData),'oh.txt');
			APILog::Log("1",$historyData);
            $oDBObjectsHistory = new DBObjectsHistory();

            $aData = [
                'id_object' => $nIDObject,
                'data' => json_encode(json_decode($historyData))
            ];

            $oDBObjectsHistory->update($aData);
        }
	}
	


?>
