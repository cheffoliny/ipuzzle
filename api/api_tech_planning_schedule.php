<?php

class ApiTechPlanningSchedule {

    public function load(DBResponse $oResponse) {
//            APILog::Log(0, ArrayToString(Params::getAll()));
//            $oResponse->setAlert(ArrayToString(Params::getAll()));
        $nIDRequest = Params::get('id_request_from_contract', '0');

        $oDBTechRequests = new DBTechRequests();
        $oDBFirms = new DBFirms();
        $oDBOffices = new DBOffices();
        $oDBContracts = new DBContracts();
        $oDBTechTiming = new DBTechTiming();

        if (!empty($nIDRequest)) {
            $aRequest = $oDBTechRequests->getRecord($nIDRequest);
            if ($aRequest['tech_type'] == 'contract') {
                $aContract = $oDBContracts->getRecord($aRequest['id_contract']);
                $nIDOffice = $aContract['id_office'];
            } else {
                $oDBObjects = new DBObjects();

                $sTechTimingName = $oDBTechTiming->getType((int)$aRequest['id_tech_timing'],1);

                if( $sTechTimingName != 'create' )
                {
                    // ако е изграждане няма ид на обект
                    $aObject = $oDBObjects->getRecord($aRequest['id_object']);
                    $nIDOffice = $aObject['id_office'];
                }
                else
                {
                    $nIDOffice = $_SESSION['userdata']['id_office'];
                }

            }
        } else {
            $nIDOffice = $_SESSION['userdata']['id_office'];
        }
        $nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);

        $aFirms = $oDBFirms->getFirms3();
        $aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirm);

        /* NEW */
        $aFirmsNew = $oDBOffices->getOfficesRight();

        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');
        $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => '0')), "--Изберете--");

        foreach ($aFirmsNew as $key => $value) {

            if ($nIDOffice == $key) {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => $key), array("selected" => "selected")), $value);
            } else {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => $key)), $value);
            }
        }

        /*END NEW*/
        /*
        $oResponse->setFormElement('form1', 'nIDFirm', array(), '');
        $oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value" => '0')), "--Изберете--");
        foreach ($aFirms as $key => $value) {

            if ($nIDFirm == $key) {
                $oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value" => $key), array("selected" => "selected")), $value);
            } else {
                $oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value" => $key)), $value);
            }
        }

        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');
        $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => '0')), "--Изберете--");
        if ($_SESSION['userdata']['access_right_all_regions'])
            $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => '-1')), "--Всички--");

        foreach ($aOffices as $key => $value) {
            if ($nIDOffice == $key) {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => $key), array("selected" => "selected")), $value);
            } else {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => $key)), $value);
            }
        }
        */
        $oResponse->setFormElement('form1', 'date', array('value' => date("d.m.Y")));
        $oResponse->setFormElement('form1', 'dateM', array('value' => date("m.Y")));
        $this->_result($oResponse, $nIDFirm, $nIDOffice, '1', '1', date("d.m.Y"));
    }

    public function loadOffices(DBResponse $oResponse) {
        $nFirm = Params::get('nIDFirm');

        $oResponse->setFormElement('form1', 'nIDOffice', array(), '');

        if (!empty($nFirm)) {
            $oDBOffices = new DBOffices();
            $aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);

            $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => '0')), "--Изберете--");

            if ($_SESSION['userdata']['access_right_all_regions'])
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => '-1')), "--Всички--");

            foreach ($aOffices as $key => $value) {
                $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => $key)), $value);
            }
        } else {
            $oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value" => '0')), "Първо изберете фирма");
        }

        $oResponse->printResponse();
    }

    public function result(DBResponse $oResponse) {

        $nIDFirm = Params::get('nIDFirm', '0');
        $nIDOffice = Params::get('nIDOffice', 0);
        $nOnlyTechnics = Params::get('OnlyTecnicks', '0');
        $nClosedLimitCards = Params::get('closedLimitCards', '1');
        $sDate = Params::get("date", "");
        $sDateM = Params::get('dateM', '');
        $sType = Params::get('type', '');

        if ($sType == 'day') {
            $this->_result($oResponse, $nIDFirm, $nIDOffice, $nOnlyTechnics, $nClosedLimitCards, $sDate);
        } else {
            $this->_resultMonth($oResponse, $nIDFirm, $nIDOffice, $nOnlyTechnics, $sDateM);
        }
    }

    public function _result($oResponse, $nIDFirm, $nIDOffice, $nOnlyTechnics, $nClosedLimitCards, $sDate) {

        if ($nIDOffice == '-1')
            $nIDOffice = '0';

        $nDate = jsDateToTimestamp($sDate);
        $sDateMysql = jsDateToMySQLDate($sDate);
        $sOperation = Params::get("lc_type", "");

        $oDBPersonnel = new DBPersonnel();
        $oDBTechLimitCards = new DBTechLimitCards();
        $oDBLimitCardPersons = new DBLimitCardPersons();
        $oDBObjectDuty = new DBObjectDuty();
        $oDBSalary = new DBSalary();
        $oDBPPPElements = new DBPPPElements();
        $oDBHoldupReasons = new DBHoldupReasons();
        $oDBTechTiming = new DBTechTiming();
        $oDBPersonLeaves = new DBPersonLeaves();
        $oDBTechTeams = new DBTechTeams();

        $aPersons = array();
        $aLimitCardPersons = array();
        $aPersonLimitCards = array();
        $aContract = array();

        $aTechTiming = $oDBTechTiming->getAllAssoc();

        if (!empty($nOnlyTechnics)) {
            $aPersons = $oDBPersonnel->getTechnics($nIDFirm, $nIDOffice);
            $aLimitCardPersons = $oDBLimitCardPersons->techPlanningTechnicsResult($nIDFirm, $nIDOffice, $nDate, $nClosedLimitCards, $sOperation);
        } else {
            $aPersons = $oDBPersonnel->getPersons2($nIDFirm, $nIDOffice);
            $aLimitCardPersons = $oDBLimitCardPersons->techPlanningPersonsResult($nIDFirm, $nIDOffice, $nDate, $nClosedLimitCards, $sOperation);
        }

        foreach ($aPersons as $nIDPerson => $aPerson)
            $aPersonLimitCards[$nIDPerson] = array();

        foreach ($aLimitCardPersons as $aCard)
            $aPersonLimitCards[$aCard['id_person']][$aCard['id']] = $aCard;

        $oResponse->setField('name', 'Служители');
        if (in_array('tech_planning_personal_card', $_SESSION['userdata']['access_right_levels'])) {
            $oResponse->setFieldLink('name', 'openPersonalCard');
        } else {
            $oResponse->setFieldLink('name', 'openPerson');
        }

        $oResponse->setField('shift', 'Смяна');

        $oResponse->setField('hours', 'Часове');
        $oResponse->setField('earning', 'Нар.');
//        $oResponse->setField('stake', 'Извън рб. вр.');
        $oResponse->setField('beforeWork', '00:00 - 07:30');

        for ($i = 8; $i < 18; $i++) {
            $aRowTemplate[sprintf("%02s", $i) . ":00"] = "";
            $aRowTemplate[sprintf("%02s", $i) . ":30"] = "";
            $oResponse->setField(sprintf("%02s", $i) . ":00", sprintf("%02s", $i) . ":00");
            $oResponse->setField(sprintf("%02s", $i) . ":30", sprintf("%02s", $i) . ":30");
        }

        $oResponse->setField('afterWork', '18:00 - 24:00');



        $aData = array();
        $nRowNum = 0;


        $nHoursTotal = '';
        $nEarningTotal = '';
        $nStakeTotal = '';
        //APILog::Log(100, ArrayToString($aPersonLimitCards));
        foreach ($aPersonLimitCards as $nIDPerson => $aCards) {
            if (!empty($nIDPerson)) {
                $nRowNum++;
                $sPersonCellID = $nIDPerson;

                $aData[$sPersonCellID] = $aRowTemplate;
                $aData[$sPersonCellID]['id'] = $sPersonCellID;
                $aData[$sPersonCellID]['name'] = $aPersons[$nIDPerson];
                $aData[$sPersonCellID]['class'] = 'person';
                $aData[$sPersonCellID]['shift'] = '';

                //проверка дали е отпуска или болничен
                $aPersonLeaves = $oDBPersonLeaves->personInHospitalOrApplicationByIDPersonAndDate($nIDPerson , $sDateMysql);
//                APILog::Log('PERSON_OTPUSKA',$aPersonLeaves);
                $aPersonDuty = $oDBObjectDuty->getShiftByDate($nIDPerson, date("Y-m-d", $nDate));

                $teamMate = $oDBTechTeams->getTeamMate($nIDPerson);
                $mate = array();



                if(count($teamMate) > 1) {
                    foreach($teamMate as $key => $val) {
                        if($val['id_person'] != $nIDPerson) {
                            $mate = $val;
                        }
                    }
                } elseif(!empty($teamMate)) {

                    $mate = reset($teamMate);

                }



                if(!empty($mate)){
                    $oResponse->setDataAttributes($sPersonCellID, 'name', array("class" => "in_team","title"=>"В екип с ".$mate['name'],"data-color"=>dechex(intval($nIDPerson+$mate['id_person']+1344)), "data-teamnum" => $mate['team_num_entered'], "data-numteam" => $mate['team_num']));
                }

                if (!empty($aPersonDuty)) {
                    $aData[$sPersonCellID]['shift'] = $aPersonDuty['code'];

                    $sShiftInfo = "Смяна: " . $aPersonDuty['name'];
                    $sShiftInfo .= "\nОт " . $aPersonDuty['shiftFrom'] . " до " . $aPersonDuty['shiftTo'];

                    $oResponse->setDataAttributes($sPersonCellID, 'shift', array('title' => "{$sShiftInfo}", 'style' => 'cursor:pointer;'));
                    if ($aPersonDuty['mode'] == 'leave') {
                        if(!empty($mate)) {
                            $oResponse->setDataAttributes($sPersonCellID, 'name', array('class' => "person_on_leave in_team", "title" => "В екип с " . $mate['name'], "data-color" => dechex(intval($nIDPerson + $mate['id_person'] + 1344)) , "data-teamnum" => $mate['team_num_entered'], "data-numteam" => $mate['team_num']));
                        } else {
                            $oResponse->setDataAttributes($sPersonCellID, 'name', array('class' => "person_on_leave"));

                        }

                    } else {
                        if(!empty($mate)) {
                            $oResponse->setDataAttributes($sPersonCellID, 'name', array('class' => "person_on_duty in_team", "title" => "В екип с " . $mate['name'], "data-color" => dechex(intval($nIDPerson + $mate['id_person'] + 1344)), "data-teamnum" => $mate['team_num_entered'], "data-numteam" => $mate['team_num'])); //"onclick" => "loadTeam({$mate['team_num']})"
                        }else {
                            $oResponse->setDataAttributes($sPersonCellID, 'name', array('class' => "person_on_duty"));

                        }
                    }
                }

                $sHoursHint = "";
                $nHours = $oDBTechLimitCards->getHours($nIDPerson, date('Y-m', $nDate), $sHoursHint);
                if (!empty($nHours)) {
                    $sHours = $nHours . " ч.";
                } else {
                    $sHours = '';
                }
                $aData[$sPersonCellID]['hours'] = $sHours;
                $oResponse->setDataAttributes($sPersonCellID, 'hours', array('style' => 'text-align:right', "title" => "{$sHoursHint}"));

                $sSalaryHint = "";
                $nEarning = $oDBSalary->getTechEarning($nIDPerson, date('Ym', $nDate), $sSalaryHint);
                //$oResponse->setAlert(ArrayToString($sSalaryHint));
                //$sSalaryHint = str_replace("@@", "\n", $sSalaryHint);
                if (!empty($nEarning)) {
                    $sEarning = $nEarning . ' лв.';
                } else {
                    $sEarning = '';
                }
                $aData[$sPersonCellID]['earning'] = $sEarning;
                $d = date('Ym', $nDate);
                $oResponse->setDataAttributes($sPersonCellID, 'earning', array('onclick' => "show_hint('{$sPersonCellID}', '{$d}')", 'style' => 'text-align:right', "title" => "Подробно..."));

                if (!empty($nHours)) {
                    $nStake = $nEarning / $nHours;
                } else {
                    $nStake = 0;
                }

                if (!empty($nStake)) {
                    $nStake = number_format($nEarning / $nHours, 2);
                } else {
                    $nStake = '';
                }
//                $aData[$sPersonCellID]['stake'] = '';


                $beforeLimitCards = $oDBTechLimitCards->getOnDutyLimitCards($nIDPerson,true,$sDateMysql);
                $afterLimitCards = $oDBTechLimitCards->getOnDutyLimitCards($nIDPerson,false,$sDateMysql);


                if(!empty($beforeLimitCards)) {
                    $first = reset($beforeLimitCards);
                    $last = end($beforeLimitCards);
                    $aData[$sPersonCellID]['beforeWork'] = date('H:i',strtotime($first['planned_start'])).' - '.date('H:i',strtotime($last['planned_end']));

                    $oResponse->setDataAttributes($sPersonCellID, 'beforeWork', array('class'=>'real_graph', 'style'=>'background-color:red;color:white;', 'onclick'=>"show_daily_graph({$sPersonCellID}, {$nDate}, this)"));
                } else {
                    $aData[$sPersonCellID]['beforeWork'] = '';
                    $oResponse->setDataAttributes($sPersonCellID, 'beforeWork', array( 'class' => 'real_graph', 'onclick' => "show_daily_graph({$sPersonCellID}, {$nDate}, this)"));
                }

                if(!empty($afterLimitCards)) {

                    $first = reset($afterLimitCards);
                    $last = end($afterLimitCards);
                    $aData[$sPersonCellID]['afterWork'] = date('H:i',strtotime($first['planned_start'])).' - '.date('H:i',strtotime($last['planned_end']));

                    $oResponse->setDataAttributes($sPersonCellID, 'afterWork', array('style' => 'background-color: red; color: white;'));
                } else {

                    $aData[$sPersonCellID]['afterWork'] = '';

                }

//                $oResponse->setDataAttributes($sPersonCellID, 'stake', array('style' => 'text-align:right'));

                for ($i = 16; $i < 36; $i++) {

                    $sCol = sprintf("%02s", (int) ($i / 2)) . ":" . sprintf("%02s", $i * 30 % 60);
                    $sCol2 = sprintf("%02s", (int) ($i / 2)) . sprintf("%02s", $i * 30 % 60);

                    //ако е отпуска или болничен да не може да се планира и да сложи цвят !
                    if(!empty($aPersonLeaves))
                    {

                        $sTitle = ($aPersonLeaves['type'] == 'application') ? 'Служителя е отпуска от ' : 'Служителя е болничен от ';
                        $sVal = ($aPersonLeaves['type'] == 'application') ? 'О' : 'Б';
                        $sTitle.= $aPersonLeaves['format_res_leave_from']. " до ". $aPersonLeaves['format_res_leave_to'];

                        $oResponse->setDataAttributes($sPersonCellID, $sCol,
                            array(
                                'style' => 'cursor:pointer;background-color:silver;text-align:center;',
                                'id' => "{$sPersonCellID},{$i},{$nRowNum}",
                                'title' => $sTitle
                            )
                        );


                        $aData[$sPersonCellID][$sCol] = $sVal;
                    } else {

                        $oResponse->setDataAttributes($sPersonCellID, $sCol, array('style' => 'cursor:pointer;',
                            'onClick' => "planning({$sPersonCellID},{$i},{$nRowNum})",
                            'id' => "{$sPersonCellID},{$i},{$nRowNum}"));
                    }
                }

                $nHoursTotal += $nHours;
                $nEarningTotal += $nEarning;
//              APILog::Log(0,  ArrayToString($aCards));
                APILog::Log("1111", $nDate);
                APILog::Log("123231", $aCards);
                foreach ($aCards as $nIDCard => $aCard) {

                    $bBegin = true;
                    for ($i = 16; $i < 36; $i++) {
                        if (($nDate + 30 * $i * 60 >= $aCard['p_start']) && ($nDate + 30 * $i * 60 < $aCard['p_end'])) {
                            //if( ( ( 30 * $i ) >= $aCard['planned_start_mins']  ) &&	( ( 30 * $i ) < $aCard['planned_end_mins']  ))
                            if (date('d', $nDate) != date('d', $aCard['p_start']))
                                $bBegin = false;

                            $sType = "";
                            $sTitle = "";

//                            switch ($aCard['id_tech_timing']) {
//                                case 1 :
//                                    $sType = 'Прием за поддръжка';
//                                    break;
//                                case 2:
//                                    $sType = 'Снемане от поддръжка';
//                                    break;
//                                case 3:
//                                    $sType = 'Ремонт';
//                                    break;
//                                case 4:
//                                    $sType = 'Авария';
//                                    break;
//                                default:
//                                    $sType = 'Планово';
//                                    break;
//                            }

                            $sType = $aTechTiming[$aCard['id_tech_timing']]['description'];
                            $sBackgroundColor = $aTechTiming[$aCard['id_tech_timing']]['color'];
                            $sBackgroundColorCloseCard = $aTechTiming[$aCard['id_tech_timing']]['color'];

                            if ($aCard['r_start'] != '0' && $aCard['r_end'] == '0') {
                                $sBackgroundColor = "#dddd00";
                            }

                            if ($aCard['status'] == 'closed') {
                                $sBackgroundColor = 'silver';
                                if($aCard['fictive'] == 'fictive'){
                                    $sBackgroundColor = '#ffcccc';
                                }
                            }


                            $sTitle = $aCard['object_name'];

                            if($oDBHoldupReasons->isWarranty($aCard['id_object'])) {
                                $sTitle.=" - В гаранция!";
                            }
                            $sTitle.= "\n" . $sType;

                            $sTitle .= "\n\nСъздал заявката:";
                            $aPersonNames = $oDBPersonnel->getPersonnelNames($aCard['request_create']);
                            $sTitle .= "\n" . $aPersonNames['names'];

                            $sTitle .= "\n\nПланирал:";
                            $aPersonNames = $oDBPersonnel->getPersonnelNames($aCard['created_user']);
                            $sTitle .= "\n" . $aPersonNames['names'];
                            //ako e ot dogovor i ne e aneks za snemane
                            if (!empty($aCard['id_contract']) && $aCard['id_tech_timing'] != 2 ) {
                                $sNote = $aCard['info_tehnics'];
                            } else {
                                $sNote = $aCard['note'];
                            }
                            if (!empty($sNote)) {
                                $sTitle .= "\n\nЗабележка";
                                $sTitle .= "\n" . $sNote;
                            }

                            /*
                              // Техника - махаме го и слагаме теч инфо!
                              $aLimitCardNomenclatures = $oDBPPPElements->getElementsByIDLimitCard($nIDCard);

                              if(!empty($aLimitCardNomenclatures)) {
                              $sTitle .= "\n\nТехника:";
                              foreach ($aLimitCardNomenclatures as $aNomenclature) {
                              $sTitle .= "\n".round($aNomenclature['count'])." бр. ".$aNomenclature['name'];
                              }
                              }
                             */
                            $aContract = $oDBTechLimitCards->getContractByLimitCard($nIDCard);
                            $sTechInfo = isset($aContract['info_tehnics']) ? $aContract['info_tehnics'] : "";

                            if (!empty($sTechInfo)) {
                                $sTitle .= "\n\nТехническа информация:";
                                $sTitle .= "\n" . $sTechInfo;
                            }

                            if ($bBegin) {
                                //$oImage = "url('images/transperant_right_arrow.gif') no-repeat center";
                                $oImage = "";
                                $bBegin = false;
                                if ($aCard['percent'] != 100) {
                                    $aPersonsWith = $oDBLimitCardPersons->getPersonsWith($aCard['id'], $aCard['id_person']);
                                    $sTitle .= "\n\nПроцент: " . $aCard['percent'] . "%";
                                    if (!empty($aPersonsWith))
                                        $sTitle .= "\nЗаедно с:";
                                    foreach ($aPersonsWith as $v) {
                                        $sTitle .= "\n" . $v['person'] . " " . $v['mobile'];
                                    }

                                    $aData[$sPersonCellID][sprintf("%02s", (int) ($i / 2)) . ":" . sprintf("%02s", $i * 30 % 60)] = $aCard['object_num'] . "  (%)";
//                                    $aData[$sPersonCellID][date('H',$aCard['p_start']) . ":" . sprintf("%02s", $i * 30 % 60)] = $aCard['object_num'] . "  (%)";
                                } else {
                                    if ($aCard['object_num'])
                                        $aData[$sPersonCellID][sprintf("%02s", (int) ($i / 2)). ":" . sprintf("%02s", $i * 30 % 60)] = $aCard['object_num'];
//                                        $aData[$sPersonCellID][date('H',$aCard['p_start']) . ":" . sprintf("%02s", $i * 30 % 60)] = $aCard['object_num'];
                                }
                            }
                            else {
                                $oImage = "";
                            }


                            $aAtributes = array('style' => " background: $sBackgroundColor $oImage;background-position:20%  ; cursor:pointer;color:white;text-align:center; font-weight: bold; font-size: 10px;",
                                'onclick' => "openLimitCard({$aCard['_id']})",
                                'title' => "{$sTitle}"
                            );

                            //дали е подписан договора
                            if( $aCard['id_contract'] > 0 && $aCard['signed'] == '0' &&  $aCard['id_contract_termination_reason'] == '0' ) {
                                $aAtributes['style'].= " border: 2px solid red;";
                            }

                            if($aCard['status'] == 'closed') {
                                $aAtributes['style'].= " border: 2px solid ".$sBackgroundColorCloseCard.";";
                            }


//                            APILog::Log(333333,sprintf("%02s", (int) ($i / 2)) . ":" . sprintf("%02s", $i * 30 % 60));
//                            APILog::Log(333333,($i * 30 % 60));
//                            APILog::Log(333333,($i));
//
//                            //($nDate + 30 * $i * 60 >= $aCard['p_start']) && ($nDate + 30 * $i * 60 < $aCard['p_end'])
//
//                            APILog::Log(333333,($aCard['p_start']));
//                            APILog::Log(333333,(date('d-m-Y H:i:s',$aCard['p_start'])));
//                            APILog::Log(333333,(date('d-m-Y H:i:s',$nDate + 30 * $i * 60)));
//                            APILog::Log(333333,30 * $i * 60);
//
//                            APILog::Log(333333, $i );


                            $oResponse->setDataAttributes($sPersonCellID, sprintf("%02s", (int) ($i / 2)) . ":" . sprintf("%02s", $i * 30 % 60),$aAtributes );
//                            $oResponse->setDataAttributes($sPersonCellID, date('H',$aCard['p_start']) . ":" . sprintf("%02s", $i * 30 % 60),$aAtributes );
                        }
                    }
                }
            }
        }

        $nHoursTotal .= ' ч.';
        $nEarningTotal .= ' лв.';
        if ($nHoursTotal != 0)
            $nStakeTotal = number_format($nEarningTotal / $nHoursTotal, 2);

        $oResponse->addTotal('hours', $nHoursTotal);
        $oResponse->addTotal('earning', $nEarningTotal);
//        $oResponse->addTotal('stake', $nStakeTotal);

        $oResponse->setData($aData);
        $oResponse->printResponse();
    }

    public function planning(DBResponse $oResponse) {

        $nIDRequest = Params::get('id_request', '');
        $sData = Params::get('date', '');
        $sStart = Params::get('start', '');
        $sEnd = Params::get('end', '');

        if (empty($nIDRequest)) {
            throw new Exception('Изберете заявка');
        }
        if (empty($sStart)) {
            throw new Exception('Маркирайте планировка');
        }

        $aHours = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,
            '08:00:00', '08:30:00', '09:00:00', '09:30:00', '10:00:00', '10:30:00', '11:00:00', '11:30:00', '12:00:00', '12:30:00', '13:00:00', '13:30:00'
        , '14:00:00', '14:30:00', '15:00:00', '15:30:00', '16:00:00', '16:30:00', '17:00:00', '17:30:00', '18:00:00', '18:30:00');

        $aStart = explode(',', $sStart);
        $aEnd = explode(',', $sEnd);

        $sPlanStart = substr($sData, 6, 4) . '-' . substr($sData, 3, 2) . '-' . substr($sData, 0, 2) . ' ' . $aHours[$aStart[1]];
        $sPlanEnd = substr($sData, 6, 4) . '-' . substr($sData, 3, 2) . '-' . substr($sData, 0, 2) . ' ' . $aHours[$aEnd[1] + 1];

        $oDBTechRequests = new DBTechRequests();
        $oDBTechLimitCards = new DBTechLimitCards();
        $oDBLimitCardPersons = new DBLimitCardPersons();
        $oDBContracts = new DBContracts();
        $oDBPPP = new DBPPP();
        $oDBStoragehouses = new DBStoragehouses();
        $oDBPPPElements = new DBPPPElements();
        $oDBContractsGuardedRoomsNomenclatures = new DBContractsGuardedRoomsNomenclatures();
        $oDBPersonLeaves = new DBPersonLeaves();



        $aRequest = $oDBTechRequests->getRecord($nIDRequest);
        $aContract = $oDBContracts->getRecord($aRequest['id_contract']);
        $nIDObject = $aRequest['id_object'];


        // ako zaqvkata e ot dogovor i se znae obekta, proverqvame dali obekta ima zadyljeniq
        if (!empty($aRequest['id_contract']) && !empty($aRequest['id_object'])) {
            $oDBObjectServices = new DBObjectServices();
            $oDBObjectsSingles = new DBObjectsSingles();

            $nObjectUnpaidMonths = (float) $oDBObjectServices->getObjectUnpaidTaxesSum($nIDObject);
            $nObjectUnpaidSingles = (float) $oDBObjectsSingles->getObjectUnpaidSingles($nIDObject);

//            if ($nObjectUnpaidMonths > 0) {
            //    throw new Exception('Обектът има неплатени месечни такси, договорът не може да бъде валидиран!'.$nObjectUnpaidMonths, DBAPI_ERR_INVALID_PARAM);
//            }

//            if ($nObjectUnpaidSingles > 0) {
//                throw new Exception('Обектът има неплатени еднократни задължения, договорът не може да бъде валидиран!', DBAPI_ERR_INVALID_PARAM);
//            }
        }


        if (empty($aRequest['id_limit_card'])) {
            $nIDPlanningPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

            $aData = array();

            $aData['status'] = 'active';
            $aData['id_tech_timing'] = $aRequest['id_tech_timing'];
            $aData['id_object'] = $aRequest['id_object'];
            $aData['id_request'] = $nIDRequest;
            $aData['planned_start'] = $sPlanStart;
            $aData['planned_end'] = $sPlanEnd;
            $aData['make_planning_person'] = $nIDPlanningPerson;

            $oDBTechLimitCards->update($aData);

            $aRequest['id_limit_card'] = $aData['id'];
            $aRequest['make_planning_person'] = $nIDPlanningPerson;
//				throw new Exception ('stop');
            $oDBTechRequests->update($aRequest);
        } else {

            $aLimitCard = $oDBTechLimitCards->getRecord($aRequest['id_limit_card']);
            $aLimitCard['planned_start'] = $sPlanStart;
            $aLimitCard['planned_end'] = $sPlanEnd;
            $oDBTechLimitCards->update($aLimitCard);
        }

        $oDBLimitCardPersons->delPersonsByIDLimitCard($aRequest['id_limit_card']);


        $oDBTechTeams = new DBTechTeams();
        $team_num = $oDBTechTeams->getTeamByIDPerson($aStart[0]);

        if(!empty($team_num)) {

            $team_members = $oDBTechTeams->getMembersByTeamNum($team_num['team_num']);

            foreach($team_members as $id => $val) {

                $planedDate = date('Y-m-d',strtotime($sPlanStart));
                $aLeave = $oDBPersonLeaves->personInHospitalOrApplicationByIDPersonAndDate($val['id_person'], $planedDate);
                if(empty($aLeave)) {
                    $aLimitCardPersons = array();
                    $aLimitCardPersons['id'] = 0;
                    $aLimitCardPersons['id_limit_card'] = $aRequest['id_limit_card'];
                    $aLimitCardPersons['id_person'] = $val['id_person'];
                    $aLimitCardPersons['percent'] = $val['percent'];
                    $oDBLimitCardPersons->update($aLimitCardPersons);
                }
            }


        } else {

            $aLimitCardPersons = array();
            $aLimitCardPersons['id_limit_card'] = $aRequest['id_limit_card'];
            $aLimitCardPersons['id_person'] = $aStart[0];
            $aLimitCardPersons['percent'] = '100';

            $oDBLimitCardPersons->update($aLimitCardPersons);

        }





        if ($aRequest['tech_type'] == 'contract' && $aRequest['id_tech_timing'] == 1) {    //Ако заявката е от електроннен договор, създаваме ППП
            //$nIDStoragehouse 	= $oDBStoragehouses->getIDNova($aContracts['id_office']);
            $nIDTechnic = $oDBLimitCardPersons->getFirstPersonByLimitCard($aRequest['id_limit_card']);
            $nIDStoragehouse = $oDBStoragehouses->getIDReady($nIDTechnic);

            $aPPP = array();
            $aPPP['id_limit_card'] = $aRequest['id_limit_card'];
            $aPPP['created_user'] = $_SESSION['userdata']['id_person'];
            $aPPP['source_date'] = time();
            $aPPP['source_type'] = 'storagehouse';
            $aPPP['dest_type'] = 'object';

            if (!empty($nIDStoragehouse)) {
                $aPPP['id_source'] = $nIDStoragehouse;
            }

            $oDBPPP->update($aPPP);

            $aContractNomenclatures = $oDBContractsGuardedRoomsNomenclatures->getContractNomenclaturesWithPrices($aRequest['id_contract']);

            $aPPPElementsMulti = array();
            foreach ($aContractNomenclatures as $aContractNomenclature) {

                $aPPPElements = array();
                $aPPPElements['id_ppp'] = $aPPP['id'];
                $aPPPElements['id_nomenclature'] = $aContractNomenclature['id_nomenclature'];
                $aPPPElements['count'] = $aContractNomenclature['count'];
                $aPPPElements['single_price'] = $aContractNomenclature['sales_price'];
                $aPPPElements['client_own'] = $aContractNomenclature['own_by_client'];
                $aPPPElements['updated_time'] = date('Y-m-d H:i:s');
                $aPPPElements['updated_user'] = !empty($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;

                $aPPPElementsMulti[] = $aPPPElements;
            }
            $oDBPPPElements->multiInsert($aPPPElementsMulti);
        }

        /*
          if( $aRequest['tech_type'] == 'contract' && $aRequest['id_tech_timing'] == 3) {


          $nIDContract = $aContract['id'];

          $nIDLastContract = $aContract['id_parent'];
          $nIDLastAnnex = $oDBContracts->getLastAnnexID($nIDLastContract,$nIDContract);

          if(!empty($nIDLastAnnex)) {
          $nIDLastContract = $nIDLastAnnex;
          }

          //var_dump($nIDLastContract);die;
          }
         *
         */

//        ДА НЕ СЪСТЯВА ППП ИСКАНЕ НА НЕЙКО 17.12.2015 ПРИ СНЕМАНЕ!
//        if ($aRequest['id_tech_timing'] == 2) {
//
//            $oDBPPP = new DBPPP();
//            $oDBPPPElements = new DBPPPElements();
//            $oDBStoragehouses = new DBStoragehouses();
//            $oDBStates = new DBStates();
//            //$oDBObjects 		= new DBObjects();
//            //$aObject 			= $oDBObjects->getRecord($aRequest['id_object']);
//            $nIDTechnic = $oDBLimitCardPersons->getFirstPersonByLimitCard($aRequest['id_limit_card']);
//            $nIDStoragehouse = $oDBStoragehouses->getIDRemovedPerson($nIDTechnic);
//            //$nIDStoragehouse 	= $oDBStoragehouses->getIDRemoved($aObject['id_office']);
//
//            $oDBPPP->delPPPByIDLimitCard($aRequest['id_limit_card']);
//
//            $aPPP = array();
//            $aPPP['id_limit_card'] = $aRequest['id_limit_card'];
//            $aPPP['created_user'] = $_SESSION['userdata']['id_person'];
//            $aPPP['source_date'] = time();
//            $aPPP['source_type'] = 'object';
//            $aPPP['id_source'] = $aRequest['id_object'];
//            $aPPP['dest_type'] = 'storagehouse';
//
//            if (!empty($nIDStoragehouse)) {
//                $aPPP['id_dest'] = $nIDStoragehouse;
//            }
//
//            $oDBPPP->update($aPPP);
//
//            $aStates = array();
//            $aStates = $oDBStates->getNomenclaturesForObject($aRequest['id_object']);
//
//            foreach ($aStates as $aState) {
//
//                $aPPPElements = array();
//                $aPPPElements['id_ppp'] = $aPPP['id'];
//                $aPPPElements['id_nomenclature'] = $aState['id'];
//                $aPPPElements['count'] = $aState['count'];
//                $aPPPElements['single_price'] = $aState['price'];
//
//                $oDBPPPElements->update($aPPPElements);
//            }
//        }
//  ДА НЕ СЪСТЯВА ППП ИСКАНЕ НА НЕЙКО 17.12.2015 ПРИ СНЕМАНЕ!

        $this->result($oResponse);
    }

    /*
     * 102:30 ч.
     * 102 часа и 30 мин = 102*60+30
     * връща минути
     */
    public function timeFormatToMinutes($sTimeFormat) {

        if(empty($sTimeFormat)) {
            return 0;
        }

        $aTmp = explode(':',$sTimeFormat);

        if(!isset($aTmp[1])) {
            return (int)$aTmp[1];
        } else {
            return (int)$aTmp[0]*60+(int)$aTmp[1];
        }
    }

    public function _resultMonth($oResponse, $nIDFirm, $nIDOffice, $nOnlyTechnics, $sDate) {
        if ($nIDOffice == '-1')
            $nIDOffice = '0';

        //throw new Exception($sDate);

        $nDate = jsDateToTimestamp('01.' . $sDate);
        $nDaysInMonth = date('t', $nDate);


        $oDBTechLimitCards = new DBTechLimitCards();
        $oDBPersonnel = new DBPersonnel();
        $oDBSalary = new DBSalary();


        if (!empty($nOnlyTechnics)) {
            $aPersons = $oDBPersonnel->getTechnics($nIDFirm, $nIDOffice);
        } else {
            $aPersons = $oDBPersonnel->getPersons2($nIDFirm, $nIDOffice);
        }

        $oResponse->setField('name', 'ч');
        if (in_array('tech_planning_personal_card', $_SESSION['userdata']['access_right_levels'])) {
            $oResponse->setFieldLink('name', 'openPersonalCard');
        } else {
            $oResponse->setFieldLink('name', 'openPerson');
        }


        $oResponse->setField('create', 'Изграждания');
        $oResponse->setField('holdup', 'Профилактики');
        $oResponse->setField('arrange', 'Аранжирания');
        $oResponse->setField('destroy', 'Снемания');

        $oResponse->setField('hours', 'Часове');
        $oResponse->setField('earning', 'Нар.');
        $oResponse->setField('stake', 'Ставка');

        for ($i = 1; $i <= $nDaysInMonth; $i++) {
            $nDayTimeStamp = mktime(0, 0, 0, date('n', $nDate), $i, date('Y', $nDate));
            $nDay = date('N', $nDayTimeStamp);

            switch ($nDay) {
                case '1': $sDay = 'понеделник';
                    break;
                case '2': $sDay = 'вторник';
                    break;
                case '3': $sDay = 'сряда';
                    break;
                case '4': $sDay = 'четвъртък';
                    break;
                case '5': $sDay = 'петък';
                    break;
                case '6': $sDay = 'събота';
                    break;
                case '7': $sDay = 'неделя';
                    break;
            }

            $oResponse->setField('day_' . $i . '_hours', zero_padding($i, 2));
            $oResponse->setField('day_' . $i . '_earning', $sDay);
            $aRowTemplate['day_' . $i . '_hours'] = '';
            $aRowTemplate['day_' . $i . '_earning'] = '';
        }

        $aData = array();

        $nCreateTotal = '';
        $nDestroyTotal = '';
        $nHoldupTotal = '';
        $nArrangeTotal = '';

        $nHoursTotal = '';
        $nEarningTotal = '';
        $nStakeTotal = '';

        foreach ($aPersons as $nIDPerson => $sPersonName) {

            $aData[$nIDPerson] = $aRowTemplate;
            $aData[$nIDPerson]['id'] = $nIDPerson;
            $aData[$nIDPerson]['name'] = $sPersonName;


            $aPersonInfo = $oDBPersonnel->getRecord($nIDPerson);

            $aCountServices = $oDBTechLimitCards->getCountServices($nIDPerson, date('Y-m', $nDate));

            isset($aCountServices['create']) ? $aData[$nIDPerson]['create'] = $aCountServices['create'] : $aData[$nIDPerson]['create'] = '';
            isset($aCountServices['destroy']) ? $aData[$nIDPerson]['destroy'] = $aCountServices['destroy'] : $aData[$nIDPerson]['destroy'] = '';
            isset($aCountServices['holdup']) ? $aData[$nIDPerson]['holdup'] = $aCountServices['holdup'] : $aData[$nIDPerson]['holdup'] = '';
            isset($aCountServices['arrange']) ? $aData[$nIDPerson]['arrange'] = $aCountServices['arrange'] : $aData[$nIDPerson]['arrange'] = '';

            $nCreateTotal += $aData[$nIDPerson]['create'];
            $nDestroyTotal += $aData[$nIDPerson]['destroy'];
            $nHoldupTotal += $aData[$nIDPerson]['holdup'];
            $nArrangeTotal += $aData[$nIDPerson]['arrange'];

            $nHours = $oDBTechLimitCards->getHours($nIDPerson, date('Y-m', $nDate));
            if (!empty($nHours)) {
                $sHours = $nHours . " ч.";
            } else {
                $sHours = '';
            }

            $nMinutes = $this->timeFormatToMinutes($sHours);

            $aData[$nIDPerson]['hours'] = $sHours;

            //$nEarning = $oDBSalary->getTechEarning($nIDPerson, date('Ym', $nDate));

            if (!empty($nMinutes)) {
                $nEarning = round( ($nMinutes/60) * $aPersonInfo['tech_support_factor'], 2 ) ;
                $sEarning = round( (($nMinutes/60) * $aPersonInfo['tech_support_factor']) , 2 ) . ' лв.';
            } else {
                $nEarning = 0;
                $sEarning = '';
            }

            $aData[$nIDPerson]['earning'] = $sEarning;

//            $nStake = $nEarning / $nHours;
//            if (!empty($nStake)) {
//                $nStake = number_format($nEarning / $nHours, 2);
//            } else {
//                $nStake = '';
//            }

            $aData[$nIDPerson]['stake'] = $aPersonInfo['tech_support_factor'];

//            $nHoursTotal += $nHours;
            $nHoursTotal += $nMinutes; // добавяма минутите за тотал-а
            $nEarningTotal += $nEarning;

            for ($i = 1; $i < $nDaysInMonth; $i++) {
                $nDayHours = $oDBTechLimitCards->getHours($nIDPerson, date('Y-m-', $nDate) . zero_padding($i, 2));
                if (!empty($nDayHours))
                    $aData[$nIDPerson]['day_' . $i . '_hours'] = $nDayHours . " ч.";

                $nDayEarning = $oDBSalary->getTechEarningForDay($nIDPerson, date('Y-m-', $nDate) . zero_padding($i, 2));

                if (!empty($nDayEarning))
                    $aData[$nIDPerson]['day_' . $i . '_earning'] .= $nDayEarning . " лв.";

                $oResponse->setDataAttributes($nIDPerson, 'day_' . $i . '_hours', array('style' => 'text-align:right;padding-left:10px;'));
                $oResponse->setDataAttributes($nIDPerson, 'day_' . $i . '_earning', array('style' => 'text-align:right;padding-left:10px;'));
            }


            $oResponse->setDataAttributes($nIDPerson, 'create', array('style' => 'text-align:right'));
            $oResponse->setDataAttributes($nIDPerson, 'destroy', array('style' => 'text-align:right'));
            $oResponse->setDataAttributes($nIDPerson, 'holdup', array('style' => 'text-align:right'));
            $oResponse->setDataAttributes($nIDPerson, 'arrange', array('style' => 'text-align:right'));

            $oResponse->setDataAttributes($nIDPerson, 'hours', array('style' => 'text-align:right;padding-left:10px'));
            $oResponse->setDataAttributes($nIDPerson, 'earning', array('style' => 'text-align:right;padding-left:10px'));
            $oResponse->setDataAttributes($nIDPerson, 'stake', array('style' => 'text-align:right;padding-left:10px'));
        }

        $oResponse->addTotal('create', $nCreateTotal);
        $oResponse->addTotal('destroy', $nDestroyTotal);
        $oResponse->addTotal('holdup', $nHoldupTotal);
        $oResponse->addTotal('arrange', $nArrangeTotal);

//        $nHoursTotal .= ' ч.';
        $nEarningTotal .= ' лв.';
        $nStakeTotal = number_format($nEarningTotal / $nHoursTotal, 2);

        $oResponse->addTotal('hours', sprintf("%02d:%02d ч." , floor($nHoursTotal/60) ,  ($nHoursTotal%60)) );
        $oResponse->addTotal('earning', $nEarningTotal);
//        $oResponse->addTotal('stake', $nStakeTotal);

        $oResponse->setData($aData);
        $oResponse->printResponse();
    }

}

?>