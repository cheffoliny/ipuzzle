<?php
class ApiSetSetupTechRequest {
    public function result( DBResponse $oResponse ) {
        $this->load($oResponse);
    }

    public function load( DBResponse $oResponse ) {
        $aData			= array();
        $aTechRequests	= array();
        $aTechReason	= array();
        $aFirms			= array();
        $aOffices		= array();

        $nID			= Params::get("nID", 0);
        //$nIDOldObj		= Params::get("nIDOldObj", 0);
        $nIDFirm		= Params::get("nIDFirm", 0);
        $nIDOffice		= Params::get("nIDOffice", 0);
        $nIDReason		= Params::get("nIDReason", 0);
        $sAct			= Params::get("sAct", 'load');
        $sType  		= Params::get("sType", 0);
        $nIDRequestsDirections = Params::get('requests_directions',0);

        $oTechRequests		= new DBTechRequests();
        $oTechLimitCards 	= new DBTechLimitCards();
        $oTechReason		= new DBHoldupReasons();
        $oFirms				= new DBFirms();
        $oOffices			= new DBOffices();
        $oDBHoldupReasons   = new DBHoldupReasons();
        $oTechTiming        = new DBTechTiming();
        //$oDBAscertainmentsProtocols = new DBAscertainmentsProtocols();



        if ( !empty($nID) && $sAct == 'load' ) {
            $aTechRequests = $oTechRequests->getRequest( $nID );
//				APILog::Log(0,$aTechRequests);
            if ( !empty($aTechRequests) ) {
                $nIDFirm		    = $aTechRequests['id_firm'];
                $nIDOffice		    = $aTechRequests['id_office'];
                $nIDReason		    = $aTechRequests['reason'];
                $sPlannedStart	    = $aTechRequests['planned_date'];
                $sPlannedStartH	    = $aTechRequests['planned_hour'];
                $sType              = $aTechRequests['id_tech_timing'];
                $sRequestBy         = $aTechRequests['request_by'];
                $sTimeLimitDate     = $aTechRequests['time_limit_date'];
                $sTimeLimitHour     = $aTechRequests['time_limit_hour'];


                APILog::Log('11112222',$sRequestBy);

                $oResponse->setFormElement('form1', 'sNum', array(), zero_padding( $aTechRequests['num'] )."/".$aTechRequests['created_time']);
                $oResponse->setFormElement('form1', 'sRequestTime', array(), date("H:i",strtotime($aTechRequests['request_time'])));
                $oResponse->setFormElement('form1', 'sRequestDate', array(), date("d.m.Y",strtotime($aTechRequests['request_time'])));
//					$oResponse->setFormElement('form1', 'sType', array(), $aTechRequests['id_tech_timing']);
                $oResponse->setFormElement('form1', 'obj', array(), $aTechRequests['object']);
                $oResponse->setFormElement('form1', 'nObject', array(), $aTechRequests['id_object']);
                $oResponse->setFormElement('form1', 'sRequestName', array(), $aTechRequests['request_person_name']);
                $oResponse->setFormElement('form1', 'sCreatedUser', array(), $aTechRequests['created_user']);
                $oResponse->setFormElement('form1', 'sUpdatedUser', array(), $aTechRequests['updated_user']);
                $oResponse->setFormElement('form1', 'nIDLimitCard', array(), zero_padding( $aTechRequests['limit_card'] ) );
                $oResponse->setFormElement('form1', 'sDescription', array(), $aTechRequests['note']);
                $oResponse->setFormElement('form1', 'sPlannedStart', array(), $sPlannedStart);
                $oResponse->setFormElement('form1', 'sPlannedStartH', array(), $sPlannedStartH);
                $oResponse->setFormElement('form1', 'sTimeLimit', array(), $sTimeLimitDate);
                $oResponse->setFormElement('form1', 'sTimeLimitH', array(), $sTimeLimitHour);
                $oResponse->setFormElement('form1', 'nPriority', array(), $aTechRequests['priority']);
                $oResponse->setFormElement('form1', 'requests_directions', array(), $aTechRequests['id_requests_directions']);

                $oResponse->setFormElement('form1', 'nDuration', array(), $aTechRequests['duration']);
                $oResponse->setFormElement('form1', 'nFrequency', array(), $aTechRequests['frequency']);
                $oResponse->setFormElement('form1', 'nMaxCnt', array(), $aTechRequests['max_cnt']);
                $oResponse->setFormElement('form1', 'nIDPerson', array(), $aTechRequests['id_person_planning']);
                $oResponse->setFormElement('form1', 'person', array(), $aTechRequests['auto_user_planning']);
                $oResponse->setFormElement('form1', 'bIsUpdated', array(), (int)($aTechRequests['updated_time'] !== "0000-00-00 00:00:00"));

                $oResponse->setFormElement('form1', 'idOffer', array(), $aTechRequests['id_contract']);
                if($aTechRequests['id_contract']) {
                    $oResponse->setFormElementChild('form1', 'idOffer', array('value' => $aTechRequests['id_contract']), $aTechRequests['id_contract']);
                }


                if($aTechRequests['is_auto_planning'])
                    $oResponse->setFormElement('form1', 'nIsAutoGenerate', array('checked'=>'checked'));

                if (!empty($sRequestBy) && $sRequestBy == 'client') {
                    $oResponse->setFormElementAttribute('form1', 'sRequestBy', 'value', 'client');
                    $oResponse->setFormElementAttribute('form1', 'sRequestName', 'style','float: left;');

                }
                else if($sRequestBy == 'telepol') {
                    $oResponse->setFormElementAttribute('form1', 'sRequestBy', 'value', 'telepol');
                } else {
                    $oResponse->setFormElementAttribute('form1', 'sRequestBy', 'value', 'office');
                }


                /*Офертите*/
                if((int)($aTechRequests['id_contract']) != 0) {
                    $bIsSigned = 0;
                    $oContracts = new DBContracts();
                    $aContractData = $oContracts->getRecord($aTechRequests['id_contract']);

                    if(count($aContractData) > 0) {
                        if($aContractData['contract_status'] === 'ignored') {
                            $bIsSigned = 2;
                        }
                        else if($aContractData['contract_status'] === 'signed' &&  (int) $aContractData['signed'] === 1 ) {
                            $bIsSigned = 1;
                        }
                    }

                    $oResponse->setFormElement('form1', 'bIsSigned', array(), $bIsSigned);
                    $oResponse->setFormElement('form1', 'nIDContract', array(), $aTechRequests['id_contract']);
                }

                // Констативен протокол
                //$aAscertainmentsProtocols = $oDBAscertainmentsProtocols->getByIDRequest($nID);

//                if(!empty($aAscertainmentsProtocols)) {
//                    $oResponse->setFormElement('form1', 'nIDAscertainmentProtocol', array(), $aAscertainmentsProtocols['id']);
//                }

                //Get Last Service
                if( !empty( $aTechRequests['id_object'] ) )
                {
                    if ($oDBHoldupReasons->isWarranty($aTechRequests['id_object']))
                        $aLastService = $oTechLimitCards->getLastService( $aTechRequests['id_object'],1 );
                    else
                        $aLastService = $oTechLimitCards->getLastService( $aTechRequests['id_object']);


                    if( !empty( $aLastService ) )
//						$sLastService = $aLastService['date'] . "\n";
//						$sLastService .= $aLastService['type'] . "\n";
//						$sLastService .= $aLastService['persons'];

                        $sLastService =  $aLastService['date'] . " : ";
                    $sLastService .= $aLastService['type'] . " : ";
//                        $sLastService .= $aLastService['card'] . "\n";
                    $sLastService .= $aLastService['persons'];


                    if (!empty($nIDReason)) {
                        $reason = $oTechReason->getReasonById($nIDReason);
                        //                           APILog::Log(0,$reason);

//                            if ((strtotime('today - '.$aLastService['warranty_time'].' months') < strtotime($aLastService['date'])) && $aLastService['is_warranty']) {
                        if ($oDBHoldupReasons->isWarranty($aTechRequests['id_object'])) {

                            $sLastService .= "\n Обекта е в гаранция!";

                        }
                    }

                }
                else
                {
                    $sLastService = "";
                }

//                    if ((strtotime('today - '.$aLastService['warranty_time'].' months') < strtotime($aLastService['date'])) && $aLastService['is_warranty']){
                if ($oDBHoldupReasons->isWarranty($aTechRequests['id_object'])) {
                    $oResponse->setFormElement( "form1", "sLastService", array( "value" => $sLastService,
                        "style" => "border: 0px; width: 225px; height: 50px; color: white; cursor: pointer; font-weight: bold; background-color: red;" ) );
                }
                else
                    $oResponse->setFormElement( "form1", "sLastService", array( "value" => $sLastService ) );


//                    $sIsWarranty = '';

//                    if (!empty($nIDReason)) {
//                        $reason = $oTechReason->getReasonById($nIDReason);
//                        //                           APILog::Log(0,$reason);
//
//                        if ((strtotime('today - '.$reason[0]['warranty_time'].' months') < strtotime($aLastService['date'])) && $reason[0]['is_warranty']) {
//
//                            $sIsWarranty = "Обекта е в гаранция!";
//
//                        }
//                    }


//                    if ($sIsWarranty != '')
//                        $oResponse->setFormElement( "form1", "sIsWarranty", array( "value" => $sIsWarranty,
//                                                "style" => "border: 0px; width: 370px; height: 20px; background-color: transparent; color: white; cursor: pointer; font-weight: bold; background-color: red;" ) );
//                    else
//                        $oResponse->setFormElement( "form1", "sIsWarranty", array( "value" => $sIsWarranty));
                //End Get Last Service

//					$oResponse->setFormElement('form1', 'nIDReason', array(), '');
//					$oResponse->setFormElementChild('form1', 'nIDReason', array('value' => 0), 'Избери');

//					foreach ( $aTechReason as $key => $val ) {
//						if ( $nIDReason == $key ) {
//							$oResponse->setFormElementChild('form1', 'nIDReason', array('value' => $key, 'selected' => 'selected'), $val);
//						} else $oResponse->setFormElementChild('form1', 'nIDReason', array('value' => $key), $val);
//					}
            }
        }
//        elseif ( $nIDOldObj > 0 ) {
//            $oObject = new DBObjects();
//            $aObject = array();
//
//            $aObject = $oObject->getInfoByID( $nID );
//            //APILog::Log(0, $aObject);
//
//            $nIDFirm	= isset($aObject['id_firm']) ? $aObject['id_firm'] : 0;
//            $nIDOffice	= isset($aObject['id_office']) ? $aObject['id_office'] : 0;
//            $nIDReason = 4;
//
////				$oResponse->setFormElement('form1', 'sType', array(), 'holdup');
//            $oResponse->setFormElement('form1', 'obj', array(), isset($aObject['name']) ? $aObject['name'] : '');
//            $oResponse->setFormElement('form1', 'nObject', array(), isset($aObject['id']) ? $aObject['id'] : 0);
//        }

        //трябва да се виждат всички фирми от всички потребители L1-210
        $aFirms = $oFirms->getFirmsWithoutRights();

        //APILog::Log(0, $aTechReason);
        $oResponse->setFormElement('form1', 'nIDReason', array(), '');
        $oResponse->setFormElement('form1', 'nIDFirm', array(), '');
        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');
        $oResponse->setFormElement('form1', 'sType', array(), '');
        $oResponse->setFormElementChild('form1', 'sType', array('value' => 0), 'Изберете тип');
        $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => 0), 'Фирма');
        $oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Офис');
        $oResponse->setFormElementChild('form1', 'nIDReason', array('value' => 0), 'Изберете причина');

//            $aTechReason = $oTechReason->getTechReason();

        if ($sType != 0)
            $aTechReason = $oTechReason->getReasonByType($sType);

      //  if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
            //if ( in_array( 'tech_request_type', $_SESSION['userdata']['access_right_levels']) ) {
                // ако има права да вижда всички типове
//						$aTechTiming = $oTechTiming->getAllAssoc();
                $aTechTiming = $oTechTiming->select("
                        SELECT 
                          tt.id,
                          tt.description
                          FROM tech_timing_firms ttf
                           LEFT JOIN tech_timing tt ON tt.id = ttf.id_tech_timing
                           WHERE ttf.id_firm = {$nIDFirm}
                           AND tt.to_arc = 0 
					");
//            } else {
//                //ако няма права да не вижда типовете изграждане и сваляне и аранживорка и пуска нова заявка
//                $aTechTiming = $oTechTiming->select("
//                        SELECT
//                          tt.id,
//                          tt.description
//                          FROM tech_timing_firms ttf
//                           LEFT JOIN tech_timing tt ON tt.id = ttf.id_tech_timing
//                           WHERE ttf.id_firm = {$nIDFirm}
//                           AND tt.name NOT IN ('create', 'destroy' , 'arrange')
//                           AND tt.to_arc = 0
//					");
//            }
     //   }

        foreach ($aTechTiming as $val) {
            if ( $sType == $val['id'])
                $oResponse->setFormElementChild('form1', 'sType', array('value' => $val['id'], 'selected' => 'selected'), $val['description']);
            else
                $oResponse->setFormElementChild('form1', 'sType', array('value' => $val['id']), $val['description']);

        }

        if ($sType != 0)
            foreach ( $aTechReason as $val ) {

                // И НЯМАМ ИД НА ЗАЯВКАТА ПРОВЕРКА ДАЛИ ЗА ОБЕКТА ИМА РЕМОНТ В РАМКИТЕ НА ЕДНА СЕДМИЦА
                //АКО ИМА РЕМОНТ ЩЕ ИЗВЕЖДАМ И ТЕКСТА ЗА НЕДОВЪРШЕН РЕМОНТ
//                    if( $val['id'] == $oTechRequests->nIDReasonContinueArrange ) {
//                        continue;
//                    }

//                    APILog::Log($val['id'], "TESTA");

                if ( $nIDReason == $val['id'] ) {
                    $oResponse->setFormElementChild('form1', 'nIDReason', array('value' => $val['id'], 'selected' => 'selected'), $val['name']);
                } else $oResponse->setFormElementChild('form1', 'nIDReason', array('value' => $val['id']), $val['name']);

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
        }

        $oResponse->printResponse();
    }

    public function save( DBResponse $oResponse ) {
        $nID			        = Params::get( "nID", 0 );
        $nObject		        = Params::get( "nObject", 0 );
        $nIDFirm		        = Params::get( "nIDFirm", 0 );
        $nIDReason		        = Params::get( "nIDReason", 0 );
        $sType			        = Params::get( "sType", 0 );
        $sRequestName	        = Params::get( "sRequestName", '' );
        $sRequestBy	            = Params::get( "sRequestBy", '' );
        $sDescription	        = Params::get( "sDescription", '' );
        $sPlannedStart	        = Params::get("sPlannedStart", '0000-00-00');
        $sPlannedStartH         = Params::get("sPlannedStartH", '');
        $sRequestTime           = Params::get("sRequestTime", '');
        $sRequestDate           = Params::get("sRequestDate", '');
        $sTimeLimitH            = Params::get("sTimeLimitH", '');
        $sTimeLimit             = Params::get("sTimeLimit", '');

        $nPriority              = Params::get("nPriority", 0);
        $sNameTemplate          = Params::get("sTemplateName", ''); //име на шаблон
        $nIsTemplate            = Params::get("nIsTemplate", 0); //дали да се запазва като шаблон
//            $nRequestsDirections    = Params::get("requests_directions", 0);

        $nFrequency     = Params::get("nFrequency", 0);
        $nMaxCnt        = Params::get("nMaxCnt", 0);
        $nIsAutoGenerate= Params::get("nIsAutoGenerate", 0);
        $nIDPerson      = Params::get("nIDPerson", 0);
        $nDuration      = Params::get("nDuration", 0);
        $nIDOffer       = Params::get("idOffer", 0);



        $oTechRequests = new DBTechRequests();
        $oTechRequestsTemplates = new DBTechRequestsTemplates();
        $oDBObjectStates = new DBStates(); // за извличане на номенклатуррите на обект
        $oDBTechOperations = new DBTechOperations();
        $oTechTiming = new DBTechTiming();
        $oTechLimitCard = new DBTechLimitCards();
        $oDBTechPlanSupport = new DBTechPlanSupport();

        //if ( $right_edit ) {

        $aMatches = array();
        if ( !empty($sPlannedStart) && empty($sPlannedStartH) )	{
            if( !preg_match("/^(\d{2})\.(\d{2})\.(\d{4})$/", Params::get("sPlannedStart", '0000-00-00'), $aMatches ) )
                throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
            else {
                $sTmp = jsDateToTimestamp(Params::get("sPlannedStart", '0000-00-00'));
                $sPlannedStart = date("Y-m-d", $sTmp);
            }
        }
        elseif ( empty($sPlannedStart) && !empty($sPlannedStartH) ) {
            throw new Exception("Моля въведете дата за планиран старт!", DBAPI_ERR_INVALID_PARAM);
        }
        elseif ( !empty($sPlannedStart) && !empty($sPlannedStartH) ) {
            if( !preg_match("/^(\d{2})\.(\d{2})\.(\d{4})$/", Params::get("sPlannedStart", '0000-00-00'), $aMatches ) )
                throw new Exception("Невалидна дата!", DBAPI_ERR_INVALID_PARAM);
            if( !preg_match("/^(\d{2})\:(\d{2})$/", $sPlannedStartH, $aMatches ) )
                throw new Exception("Невалиден час!", DBAPI_ERR_INVALID_PARAM);
            $sTmp = jsDateToTimestamp(Params::get("sPlannedStart", '0000-00-00'));
            $sPlannedStart = date("Y-m-d", $sTmp)." ".$sPlannedStartH;
        }
        elseif ( empty($sPlannedStart) && empty($sPlannedStartH) ) {
            $sPlannedStart = '0000-00-00 00:00:00';
        }

        if ($sPlannedStart != '0000-00-00 00:00:00') {
            $sCurrentDate = mktime( 0, 0, 0,date('m'),date('d'),date('Y'));
            $sEnteredDate = jsDateToTimestamp(Params::get("sPlannedStart"));

            if ($sCurrentDate > $sEnteredDate) {
                throw new Exception("Не може да зададете за планиран старт минала дата!");
            }
        }


        if ( empty($sType) ) {
            throw new Exception("Изберете тип на обслужването!", DBAPI_ERR_INVALID_PARAM);
        }

        if(!empty($nID)) {
            $aTmpRowData = $oTechRequests->getRecord($nID);

            if($aTmpRowData['id_limit_card']) {
                throw new Exception("Не може да се променя планирана заявка!!!");
            }
        }

        $sTypeReason = $oTechTiming->getType($sType,1); // взимам типовете обслужвания

        if( $sTypeReason != 'create' &&  empty($nID))
        {
            //ако не е изграждане да прави проверка за обект
            // при изграждане и нова заявка не трябва да има обект
            if ( empty($nObject) ) {
                throw new Exception("Въведете обект!", DBAPI_ERR_INVALID_PARAM);
            }

            $oDBObjects = new DBObjects();
            $objectData = $oDBObjects->getRecord($nObject);
//                    if(!$objectData['confirmed']) {
//                        throw new Exception('Трябва да потвърдите координатите на обекта!');
//                    }

        }

        if (empty($nIDReason) ) {
            throw new Exception("Изберете причина за обслужването!", DBAPI_ERR_INVALID_PARAM);
        }

        if(empty($nID))
        {
            /*
                нова заявка
                проверка каква причина е избрана
                ако е сваляне или е аранжировка се проверява обекта
                дали има нов договор
            */

            $aType = array('destroy' , 'arrange'); // типове при който ще следим дали обекта е нов

            if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
                if ( in_array( 'tech_request_type', $_SESSION['userdata']['access_right_levels']) ) {
                    // ако има права да вижда всички типове може да пуска и аранжировка на нов договор
                    $aType = array('destroy');
                }
            }

//					if(in_array($sTypeReason ,$aType)) {
//						// проверяваме дали обекта е с нов договор
//						$oDBContract = new DBContracts();
//
//						$nRes = $oDBContract->hasNewContractByIdObject($nObject);
//
//						if($nRes)
//						{
//							 throw new Exception("Обекта е с нов договор. Трябва да се направи анекс за типа обслужване!", DBAPI_ERR_INVALID_PARAM);
//						}
//					}
        }
        // край на проверката за обекта и причината

        // Времена за всяка заявка
        $aObjectNomenclatures = $oDBObjectStates->getNomenclaturesForObjectWithRooms($nObject);

        $aIDsNomenclature = array(); // id-та на номенклатури за заявката
        $aNomenclatureCnt = array(); // за всяко ид колко пъти го имам като номенклатура

        if(!empty($aObjectNomenclatures)) {
            //пресмятам времето за всички номенклатури
            foreach($aObjectNomenclatures as $aRoom) {
                if(!empty($aRoom['nomenclatures'])) {
                    foreach($aRoom['nomenclatures'] as $aNomenclatures)
                    {
                        //гледам колко пъти имам една номенклатура на обекта
                        if( isset( $aNomenclatureCnt[$aNomenclatures['id']] ) )
                        {

                            $aNomenclatureCnt[$aNomenclatures['id']]+=$aNomenclatures['count'];
                        }
                        else
                        {
                            $aNomenclatureCnt[$aNomenclatures['id']] = $aNomenclatures['count'];
                        }

                        $aIDsNomenclature[$aNomenclatures['id']] = $aNomenclatures['id'];
                    }
                }
            }
        }

        $nNomenclaturesTime = 0; // време за номенкатурите

        if(!empty($aIDsNomenclature)) {

            $sQueryTime = "
                        SELECT
                        n.id AS id,
                        ntt.minute As `min`,
                        n.id AS n_id
                        FROM
                        nomenclatures AS n
                        LEFT JOIN
                        nomenclature_types nt
                        ON n.id_type = nt.id
                        LEFT JOIN nomenclature_types_timing AS ntt
                        ON nt.id = ntt.id_nomenclature_types
                        WHERE 1
                        AND ntt.to_arc = 0
                        AND ntt.id_tech_timing = {$sType}
                        AND n.id IN (".implode($aIDsNomenclature, ",").")
                        GROUP BY n.id_type
                    ";

            // за всеки ред според броя да сумирам времето за номенкклатурите
            $aNomeclaturesTimes = $oDBObjectStates->selectAssoc($sQueryTime);

            foreach($aNomeclaturesTimes as $sKey=>$Val)
            {
                $nNomenclaturesTime+= $aNomenclatureCnt[$sKey]*$aNomeclaturesTimes[$sKey]['min'];
            }
        }
        else {
            //ако няма номенклатури на обекта вземам времето за кокретната операция
            $aInfoTiming = $oTechTiming->getRecord($sType);
            $nNomenclaturesTime = $aInfoTiming['minute'];
        }

        //Време само за операции
        $nOperationTime = $oDBTechOperations->calcTimeByIDTechTiming($sType);

//				if ( empty($nIDReason) && $sType == 'holdup' ) {
//					throw new Exception("Изберете причина за обслужването!", DBAPI_ERR_INVALID_PARAM);
//				}


        if($nIDReason == $oTechRequests->nIDReasonContinueArrange && empty($nIDOffer)) {
            throw new Exception('Изберете оферта за която е продължението за ремонт!');
        }

        $aData = array();
        $aData['id']					                = $nID;
        $aData['id_object']                             = $nObject;
        $aData['id_tech_timing']			            = $sType;
        $aData['note']					                = $sDescription;
        $aData['id_tech_reason']                        = $nIDReason;
        $aData['request_person_name']                   = $sRequestName;
        $aData['request_by']                            = $sRequestBy;
        $aData['id_contract']                           = $nIDOffer;

        if ($sPlannedStart) {
            $aData['planned_start']                        = $sPlannedStart;
        }

        $aData['created_type'] = 'manual';
        $aData['priority']  = $nPriority;
        $aData['time']      = $nOperationTime + $nNomenclaturesTime;

        if( empty( $nID ) )
        {
            $aData['created_time'] = time();
            $aData['created_user'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
        }

        $aData['frequency'] = $nFrequency;
        $aData['max_cnt'] = $nMaxCnt;
        $aData['is_auto_planning'] = $nIsAutoGenerate;
        $aData['id_person_planning'] = $nIDPerson;
        $aData['duration'] = $nDuration;
        $aData['request_time'] = date("Y-m-d" ,jsDateToTimestamp($sRequestDate)).' '.$sRequestTime;

        $aData['time_limit'] = jsDateToMySQLDate($sTimeLimit).' '.$sTimeLimitH;

        //ако е шаблон да го запази при щаблоните
        if($nIsTemplate){
            $aData['name'] = $sNameTemplate;

            $oTechRequestsTemplates->update($aData);
        } else {

            if($nID == 0 ) {
                if((int)$sType == 5) {
                    //Проверка дали има такова планово обслужване за обекта

                    $aPlanSupport = $oDBTechPlanSupport->getPlanSupportByIDObjectAndIDReason((int)$nObject,(int)$nIDReason);

                    if(!empty($aPlanSupport)) {

                        if ((int)$nIDReason != 6) {

                            $sQuery = "
                                        UPDATE
                                          tech_plan_support tps
                                        JOIN tech_reason tr ON tr.id = tps.id_reason
                                        SET tps.last_date = '{$aPlanSupport['end_date_raw']}'
                                        WHERE
                                          tps.id = {$aPlanSupport['id']}
                                        AND tps.id_object = {$nObject}
                                    ";

                            $oDBTechPlanSupport->select($sQuery);
                        }
                    }
                }
            }


            $oTechRequests->update( $aData );
        }

        $oResponse->setFormElement('form1','nID',array(),$aData['id']);
        //}

        $oResponse->printResponse();
    }

    public function getunprocessed( DBResponse $oResponse )
    {
        $oTechRequests = 	new DBTechRequests();
        $oObjects = 		new DBObjects();
        $oObject = 			new DBObjectDuty();
        $oOffices = 		new DBOffices();
        $oTechLimitCards = 	new DBTechLimitCards();
        $oDBHoldupReasons   = new DBHoldupReasons();

        $nIDObject = Params::get( "nObject", 0 );
        $sUnprocessed = "";
        $nDisplayInfo = true;

        //Get Object Info
        $aObject = $oObjects->getRecord( $nIDObject );

        if( !empty( $aObject ) )
        {
            $aOffice = $oOffices->getRecord( $aObject['id_tech_office'] );
            //APILog::Log(0,$aOffice);
            if( !empty( $aOffice ) )
            {
                $oResponse->setFormElement( "form1", "nTempIDOffice", array( "value" => $aOffice['id'] ) );
                $oResponse->setFormElement( "form1", "nTempIDFirm", array( "value" => $aOffice['id_firm'] ) );
            }
        }
        if( !empty( $nIDObject ) )
        {
            if ($oDBHoldupReasons->isWarranty($nIDObject))
                $aLastService = $oTechLimitCards->getLastService( $nIDObject,1 );
            else
                $aLastService = $oTechLimitCards->getLastService( $nIDObject);



            if( !empty( $aLastService ) )
                $sLastService = $aLastService['date'] . " : ";
                $sLastService .= $aLastService['type'] . " : ";
                $sLastService .= $aLastService['persons'];

//                if ((strtotime('today - '.$aLastService['warranty_time'].' months') < strtotime($aLastService['date'])) && $aLastService['is_warranty']){
            if ($oDBHoldupReasons->isWarranty($nIDObject)){
                $oResponse->setFormElement( "form1", "sLastService", array( "value" => $sLastService,
                    "style" => "border: 0px; width: 225px; height: 50px; color: white; cursor: pointer; font-weight: bold; background-color: red;" ) );
                $sLastService .= "\n Обекта е в гаранция!";
            }
            else {
                $oResponse->setFormElement( "form1", "sLastService", array( "value" => $sLastService,
                    "style" => "border: 0px; width: 225px; height: 50px; color: white; cursor: pointer; font-weight: bold; background-color: white;" ) );
            }
        }
        else
        {
            $sLastService = "";
        }

        $oResponse->setFormElement( "form1", "sLastService", array( "value" => $sLastService ) );
        //End Get Object Info

        //Process Requests
        $aRequests = $oTechRequests->getByObject( $nIDObject );

        if( !empty( $aRequests ) )
        {
            foreach( $aRequests as $aRequest )
            {
                $nDisplayInfo = true;

                if( !empty( $aRequest['id_limit_card'] ) )
                {
                    $aLC = $oTechLimitCards->getRecord( $aRequest['id_limit_card'] );

                    if( !empty( $aLC ) && $aLC['status'] != 'active' )
                    {
                        $nDisplayInfo = false;
                    }
                }

                if( $nDisplayInfo )
                {
                    $sUnprocessed .= $aRequest['created_time'] . "\t" . $aRequest['type'] . "\t" . $aRequest['request_person_name'];
                    $sUnprocessed .= "\n";
                }
            }
        }

        $oResponse->setFormElement( "form1", "sUnprocessed", array( "value" => $sUnprocessed ) );
        //End Process Requests

        //Syncronization
        $aOld = $oObject->getObjectOLD( $nIDObject );
        $aObject = $oObject->getObjectName( $nIDObject );

        if( !isset( $aObject['num'] ) )$nIDObject = 0;
        $num = isset( $aObject['num'] ) && !empty( $aObject['num'] ) ? $aObject['num'] : 0;

        $old = isset($aOld['id_oldobj']) && !empty($aOld['id_oldobj']) ? $aOld['id_oldobj'] : -1;

        $oResponse->setFormElement( 'form1', 'nNum', array( "value" => $num ) );
        $oResponse->setFormElement( 'form1', 'nOld', array( "value" => $old ) );
        //End Syncronization

        $oResponse->printResponse();
    }

    public function getReasons(DBResponse $oResponse) {

        $sType  		= Params::get("sType", 0);
        $nID  		    = Params::get("nID", 0);
        $nObject  		= Params::get("nObject", 0);

        $nHaveArrange = 0; // АКО ИМА РЕМОНТ В РАМКИТЕ НА 10 ДЕНА ДА ДАВАМ ДА СЕ ИЗБИРА ПРИЧИНА

        $oTechReasons = new DBHoldupReasons();
        $oTechTiming = new DBTechTiming();
        $oTechRequests = new DBTechRequests();
        $oTechLimitCard = new DBTechLimitCards();

        $aType = $oTechReasons->getReasonByType($sType);

        $sTypeReason = $oTechTiming->getType($sType,1); // взимам типа обслужване
        $aTech = $oTechTiming->getRecord($sType);

        $aLimitCardArrange = $oTechLimitCard->getArrangeByIDObjectAndDays($nObject);
        $nHaveArrange = !empty($aLimitCardArrange)? 1 : 0;

        $oResponse->setFormElement( 'form1', 'obj', array('disabled' => null));
        $oResponse->setFormElement('form1', 'nPriority', array(), $aTech['priority']);
        $oResponse->setFormElement('form1', 'nIDReason', null, 0);
        $oResponse->setFormElement('form1', 'idOffer', array(), 0);
        array_unshift($aType, array('id' => '0', 'name' => 'Избери'));

        foreach ($aType as $data) {

            // И НЯМАМ ИД НА ЗАЯВКАТА ПРОВЕРКА ДАЛИ ЗА ОБЕКТА ИМА РЕМОНТ В РАМКИТЕ НА ЕДНА СЕДМИЦА
            //АКО ИМА РЕМОНТ ЩЕ ИЗВЕЖДАМ И ТЕКСТА ЗА НЕДОВЪРШЕН РЕМОНТ
            if( $nID == 0 && $data['id'] == $oTechRequests->nIDReasonContinueArrange && !$nHaveArrange ) {
                continue;
            }

            $oResponse->setFormElementChild('form1', 'nIDReason', array('value' => $data['id']), $data['name']);
        }

        if($nHaveArrange) {
            $oResponse->setFormElementChild('form1', 'idOffer', array('value' => 0), '-Изберете оферта-');
            foreach ($aLimitCardArrange as $aRow) {
                $oResponse->setFormElementChild('form1', 'idOffer', array('value' => $aRow['id_contract']), $aRow['id_contract']);
            }
        }

        die($oResponse->printResponse());
    }

    public function getTechTiming(DBResponse $oResponse)
    {
        $nIDRequestsDirections = Params::get('requests_directions',0);

        $oTechTiming = new DBTechTiming();

        $aRes = $oTechTiming->getAllByIDRequestsDirections($nIDRequestsDirections);

        $oResponse->setFormElement('form1', 'sType', array(), '');
        $oResponse->setFormElementChild('form1', 'sType', array('value' => 0), 'Избери');

        foreach($aRes as $aRow)
        {
            $oResponse->setFormElementChild('form1', 'sType', array('value' => $aRow['id']), $aRow['description']);
        }

        $oResponse->printResponse();
    }

    public function getPlanSupport(DBResponse $oResponse) {

        $nIDObject = Params::get('nIDObject', 0);

        $oDBTechPlanSupport = new DBTechPlanSupport();

        $aPlanSupport = $oDBTechPlanSupport->getAllPlanSupportByObject($nIDObject);

        echo json_encode($aPlanSupport);
        exit();
    }
}
?>