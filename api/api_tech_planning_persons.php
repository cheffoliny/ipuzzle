<?php

	class ApiTechPlanningPersons {
		
		public function load( DBResponse $oResponse ) {
			
			$oDBTechRequests = new DBTechRequests();
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			$oDBContracts = new DBContracts();
			if(!empty($nIDRequest)) {
				$aRequest = $oDBTechRequests->getRecord($nIDRequest);
				$aContract = $oDBContracts->getRecord($aRequest['id_contract']);
				$nIDOffice = $aContract['id_office'];
			} else {
				$nIDOffice = $_SESSION['userdata']['id_office'];
			}
			$nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
			
			$aFirms = $oDBFirms->getFirms4();
			$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirm);
				
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Изберете--");
			foreach($aFirms as $key => $value) {
				
				if( $nIDFirm == $key ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key),array("selected" => "selected")), $value);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
				}
			}		

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
			if( $_SESSION['userdata']['access_right_all_regions'] )
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'-1')), "--Всички--");
				
			foreach($aOffices as $key => $value) {
				if($nIDOffice == $key) {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key),array("selected" => "selected")), $value);
				} else {
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			}
			
			$oResponse->setFormElement('form1', 'date', array('value' => date("d.m.Y")));		
			$oResponse->setFormElement('form1', 'dateM', array('value' => date("m.Y")));	
					
			$this->_result($oResponse,$nIDFirm,$nIDOffice,'1','1',date("d.m.Y"));
		}
		
		public function loadOffices(DBResponse $oResponse)
		{
			$nFirm 	=	Params::get('nIDFirm');
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if(!empty($nFirm))
			{
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
	
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Изберете--");
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'-1')), "Всички");
				foreach($aOffices as $key => $value)
				{
					$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
				}
			}
			else 
			{
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "Първо изберете фирма");
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse ) {
						
			$nIDFirm = Params::get('nIDFirm','0');
			$nIDOffice = Params::get('nIDOffice',0);
			$nOnlyTechnics = Params::get('OnlyTecnicks','');
			$nClosedLimitCards = Params::get('closedLimitCards','0');
			$sDate 		= Params::get("date", "");
			$sDateM 	= Params::get('dateM','');
			$sType = Params::get('type','');
			
			if($sType == 'day') {
				$this->_result($oResponse,$nIDFirm,$nIDOffice,$nOnlyTechnics, $nClosedLimitCards, $sDate);
			} else {
				$this->_resultMonth($oResponse,$nIDFirm,$nIDOffice,$nOnlyTechnics,$sDateM);
			}
		}
		
		public function _result($oResponse, $nIDFirm, $nIDOffice, $nOnlyTechnics,$nClosedLimitCards, $sDate ) {
			
			if($nIDOffice == '-1')$nIDOffice= '0';
			
			$nDate = jsDateToTimestamp( $sDate );
			
			$oDBPersonnel = new DBPersonnel();
			$oDBTechLimitCards = new DBTechLimitCards();
			$oDBLimitCardPersons = new DBLimitCardPersons();
			$oDBObjectDuty = new DBObjectDuty();
			$oDBSalary = new DBSalary();
			$oDBPPPElements = new DBPPPElements();
			
			$aPersons = array();
			$aLimitCardPersons = array();
			$aPersonLimitCards = array();
			
			if(!empty($nOnlyTechnics)) {
				$aPersons = $oDBPersonnel->getTechnics($nIDFirm, $nIDOffice);
				$aLimitCardPersons = $oDBLimitCardPersons->techPlanningTechnicsResult( $nIDFirm, $nIDOffice , $nDate, $nClosedLimitCards );
			} else {
				$aPersons = $oDBPersonnel->getPersons2( $nIDFirm, $nIDOffice);
				$aLimitCardPersons = $oDBLimitCardPersons->techPlanningPersonsResult( $nIDFirm, $nIDOffice , $nDate, $nClosedLimitCards );
			}
			
			foreach( $aPersons as $nIDPerson => $aPerson )
				$aPersonLimitCards[ $nIDPerson ] = array();
				
			foreach( $aLimitCardPersons as $aCard )
				$aPersonLimitCards[ $aCard['id_person'] ][ $aCard['id'] ] = $aCard;
				
			//APILog::Log(0,$_SESSION['userdata']);
			
			$oResponse->setField('name','Служители');
			if (in_array('tech_planning_personal_card',$_SESSION['userdata']['access_right_levels'])) {
				$oResponse->setFieldLink('name','openPersonalCard');
			} else {
				$oResponse->setFieldLink('name','openPerson');
			}
				
			$oResponse->setField('shift','Смяна');
				
			$oResponse->setField('hours','Часове');
			$oResponse->setField('earning','Нар.');
			$oResponse->setField('stake','Ставка');
			
			for( $i=8;$i<18;$i++)
			{
				$aRowTemplate[ sprintf("%02s", $i).":00" ] = "";
				$aRowTemplate[ sprintf("%02s", $i).":30" ] = "";		
				$oResponse->setField( sprintf("%02s", $i).":00", sprintf("%02s", $i).":00");
				$oResponse->setField( sprintf("%02s", $i).":30", sprintf("%02s", $i).":30");
			}
			
			
			$aData = array();
			$nRowNum = 0;
			
			//APILog::Log(0,$aPersonLimitCards);
			
			$nHoursTotal = '';
			$nEarningTotal = '';
			$nStakeTotal = '';
			
			foreach( $aPersonLimitCards as $nIDPerson => $aCards )
			{
				if(!empty($nIDPerson))
				{
					$nRowNum++;
					$sPersonCellID = $nIDPerson;
		
					$aData[ $sPersonCellID ] 				= $aRowTemplate;
					$aData[ $sPersonCellID ]['id']			= $sPersonCellID;
					$aData[ $sPersonCellID ]['name'] 		= $aPersons[ $nIDPerson ];
					$aData[ $sPersonCellID ]['class']		= 'person';
					$aData[ $sPersonCellID ]['shift']		= '';
					
					$aPersonDuty = $oDBObjectDuty->getShiftByDate($nIDPerson,date("Y-m-d",$nDate));	
					if(!empty($aPersonDuty)) {
						$aData[ $sPersonCellID ]['shift'] = $aPersonDuty['code'];
						
						$sShiftInfo = "Смяна: ".$aPersonDuty['name'];
						$sShiftInfo .= "\nОт ".$aPersonDuty['shiftFrom']." до ".$aPersonDuty['shiftTo'];
						
						$oResponse->setDataAttributes($sPersonCellID, 'shift', array('title' => "{$sShiftInfo}",'style' => 'cursor:pointer;'));
						if($aPersonDuty['mode'] == 'leave') {
							$oResponse->setDataAttributes($sPersonCellID, 'name', array('class' => "person_on_leave"));	
						} else {
							$oResponse->setDataAttributes($sPersonCellID, 'name', array('class' => "person_on_duty"));	
						}	
					}
					
					$nHours = $oDBTechLimitCards->getHours($nIDPerson,date('Y-m',$nDate));
					if(!empty($nHours)) {
						$sHours =$nHours." ч.";
					} else {
						$sHours = '';
					}
					$aData[ $sPersonCellID ]['hours'] = $sHours;
					$oResponse->setDataAttributes($sPersonCellID,'hours',array('style' => 'text-align:right'));
					
					$nEarning = $oDBSalary->getTechEarning($nIDPerson,date('Ym',$nDate));
					if(!empty($nEarning)) {
						$sEarning = $nEarning.' лв.';
					} else {
						$sEarning = '';
					}
					$aData[ $sPersonCellID]['earning'] = $sEarning;
					$oResponse->setDataAttributes($sPersonCellID,'earning',array('style' => 'text-align:right'));
					
					$nStake = $nEarning/$nHours;
					if(!empty($nStake)) {
						$nStake = number_format($nEarning/$nHours,2);
					} else {
						$nStake = '';
					}
					$aData[ $sPersonCellID]['stake'] = $nStake;
					$oResponse->setDataAttributes($sPersonCellID,'stake',array('style' => 'text-align:right'));
					/*
					for( $i=16;$i<36;$i++) {
						
						$sCol = sprintf("%02s",(int) ($i/2)).":".sprintf("%02s",$i*30%60);
						$sCol2 = sprintf("%02s",(int) ($i/2)).sprintf("%02s",$i*30%60);
						$oResponse->setDataAttributes($sPersonCellID,$sCol , array(	'style' => 	'cursor:pointer;',
																								'onClick' => "planning({$sPersonCellID},{$i},{$nRowNum})",
																								'id' =>  "{$sPersonCellID},{$i},{$nRowNum}"));	
					}
					*/	
					
					$nHoursTotal += $nHours;
					$nEarningTotal += $nEarning;
					
					foreach( $aCards as $nIDCard => $aCard )
					{

						$bBegin = true;
						for( $i=16;$i<36;$i++)
						{
							if( ($nDate + 30 * $i * 60 >= $aCard['p_start']) && ($nDate + 30 * $i * 60 < $aCard['p_end']))
							//if( ( ( 30 * $i ) >= $aCard['planned_start_mins']  ) &&	( ( 30 * $i ) < $aCard['planned_end_mins']  ))
							{
								if(date('d',$nDate) != date('d',$aCard['p_start'])) $bBegin = false;
								
								$sType = "";
								$sTitle = "";
									
								switch ($aCard['type'])
								{
									case 'create': $sBackgroundColor = '5957bb';	$sType = 'изграждане'; 	 break;
									case 'destroy': $sBackgroundColor = 'bd2937';	$sType = 'снемане';		 break;
									case 'arrange': $sBackgroundColor = '569457';$sType = 'аранжиране'; break;
									case 'holdup': $sBackgroundColor = 'purple';$sType = 'профилактика';  break;
									default: $sBackgroundColor = 'green'; $sType = ''; break;
								}		
								
								if( $aCard['r_start'] != '0' && $aCard['r_end'] == '0') {
									$sBackgroundColor = "#dddd00";
								}
									
								if($aCard['status'] == 'closed') $sBackgroundColor = 'silver';
								$sTitle = $aCard['object_name'];
								$sTitle.= "\n".$sType;	
									
								$aLimitCardNomenclatures = $oDBPPPElements->getElementsByIDLimitCard($nIDCard);
								
								if(!empty($aLimitCardNomenclatures)) {
									$sTitle .= "\n\nТехника:";
									foreach ($aLimitCardNomenclatures as $aNomenclature) {
										$sTitle .= "\n".round($aNomenclature['count'])." бр. ".$aNomenclature['name'];
									}
								}
								
								if($bBegin)
								{
									$oImage = "url('images/transperant_right_arrow.gif') no-repeat center";
									$bBegin = false;
									if($aCard['percent'] != 100) {
										$aPersonsWith = $oDBLimitCardPersons->getPersonsWith($aCard['id'],$aCard['id_person']);
										if(!empty($aPersonsWith)) $sTitle .= "\nЗаедно с:";
										foreach ($aPersonsWith as $v) {
											$sTitle .= "\n".$v['person']." ".$v['mobile'];
										}
										
										$aData[$sPersonCellID][sprintf("%02s",(int) ($i/2)).":".sprintf("%02s",$i*30%60)] = $aCard['percent']."%";
									}
								}
								else 
								{
									$oImage="";
								}
								$oResponse->setDataAttributes($sPersonCellID, sprintf("%02s",(int) ($i/2)).":".sprintf("%02s",$i*30%60), array('style' => " background: $sBackgroundColor $oImage;background-position:20%  ; cursor:pointer;color:white;text-align:right;",
																									 		   'onclick' => "openLimitCard({$aCard['_id']})",
																											   'title' => "{$sTitle}"
																											   ));	
								
							}
						}
						
					}
				}
			}
			
			$nHoursTotal .= ' ч.';
			$nEarningTotal .= ' лв.';
			$nStakeTotal = number_format($nEarningTotal/$nHoursTotal,2);
			
			$oResponse->addTotal('hours',$nHoursTotal);
			$oResponse->addTotal('earning',$nEarningTotal);
			$oResponse->addTotal('stake',$nStakeTotal);
			
			$oResponse->setData($aData);
			$oResponse->printResponse();
		}
		
		public function _resultMonth($oResponse, $nIDFirm, $nIDOffice, $nOnlyTechnics, $sDate ) {
			if($nIDOffice == '-1')$nIDOffice= '0';
			
			//throw new Exception($sDate);
			
			$nDate = jsDateToTimestamp('01.'.$sDate );
			$nDaysInMonth = date('t',$nDate);
			
			
			$oDBTechLimitCards = new DBTechLimitCards();
			$oDBPersonnel = new DBPersonnel();
			$oDBSalary = new DBSalary();
			
			
			if(!empty($nOnlyTechnics)) {
				$aPersons = $oDBPersonnel->getTechnics($nIDFirm, $nIDOffice);
			} else {
				$aPersons = $oDBPersonnel->getPersons2( $nIDFirm, $nIDOffice);
			}
			
			$oResponse->setField('name','Служители');
			if (in_array('tech_planning_personal_card',$_SESSION['userdata']['access_right_levels'])) {
				$oResponse->setFieldLink('name','openPersonalCard');
			} else {
				$oResponse->setFieldLink('name','openPerson');
			}
			
			
			$oResponse->setField('create','Изграждания');
			$oResponse->setField('holdup','Профилактики');
			$oResponse->setField('arrange','Аранжирания');
			$oResponse->setField('destroy','Снемания');
			
			$oResponse->setField('hours','Часове');
			$oResponse->setField('earning','Нар.');
			$oResponse->setField('stake','Ставка');
			
			for( $i = 1 ; $i <= $nDaysInMonth ; $i++ ) {
				$nDayTimeStamp = mktime(0,0,0,date('n',$nDate),$i,date('Y',$nDate));
				$nDay = date('N',$nDayTimeStamp);

				switch ($nDay) {
					case '1': $sDay = 'понеделник';break;
					case '2': $sDay = 'вторник';break;
					case '3': $sDay = 'сряда';break;
					case '4': $sDay = 'четвъртък';break;
					case '5': $sDay = 'петък';break;
					case '6': $sDay = 'събота';break;
					case '7': $sDay = 'неделя';break;
				}
				
				$oResponse->setField('day_'.$i.'_hours',zero_padding($i,2));
				$oResponse->setField('day_'.$i.'_earning',$sDay);
				$aRowTemplate['day_'.$i.'_hours'] = '';
				$aRowTemplate['day_'.$i.'_earning'] = '';
			}
			
			$aData = array();
			
			APILog::Log(0,$aPersons);
			
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
	
				$aCountServices = $oDBTechLimitCards->getCountServices($nIDPerson,date('Y-m',$nDate));
				
				isset($aCountServices['create']) ? $aData[$nIDPerson]['create'] = $aCountServices['create'] :$aData[$nIDPerson]['create'] = '';
				isset($aCountServices['destroy']) ? $aData[$nIDPerson]['destroy'] = $aCountServices['destroy'] :$aData[$nIDPerson]['destroy'] = '';
				isset($aCountServices['holdup']) ? $aData[$nIDPerson]['holdup'] = $aCountServices['holdup'] :$aData[$nIDPerson]['holdup'] = '';
				isset($aCountServices['arrange']) ? $aData[$nIDPerson]['arrange'] = $aCountServices['arrange'] :$aData[$nIDPerson]['arrange'] = '';
				
				$nCreateTotal += $aData[$nIDPerson]['create'];
				$nDestroyTotal += $aData[$nIDPerson]['destroy'];
				$nHoldupTotal += $aData[$nIDPerson]['holdup'];
				$nArrangeTotal += $aData[$nIDPerson]['arrange'];
				
				
				$nHours = $oDBTechLimitCards->getHours($nIDPerson,date('Y-m',$nDate));
				if(!empty($nHours)) {
					$sHours =$nHours." ч.";
				} else {
					$sHours = '';
				}
				$aData[ $nIDPerson]['hours'] = $sHours;
				
				$nEarning = $oDBSalary->getTechEarning($nIDPerson,date('Ym',$nDate));
				if(!empty($nEarning)) {
					$sEarning = $nEarning.' лв.';
				} else {
					$sEarning = '';
				}
				$aData[ $nIDPerson]['earning'] = $sEarning;
				
				$nStake = $nEarning/$nHours;
				if(!empty($nStake)) {
					$nStake = number_format($nEarning/$nHours,2);
				} else {
					$nStake = '';
				}
				$aData[ $nIDPerson]['stake'] = $nStake;
				
				$nHoursTotal += $nHours;
				$nEarningTotal += $nEarning;
				
				
				for ($i = 1 ; $i < $nDaysInMonth ; $i++ ) {
					$nDayHours = $oDBTechLimitCards->getHours($nIDPerson,date('Y-m-',$nDate).zero_padding($i,2));
					if(!empty($nDayHours))$aData[$nIDPerson]['day_'.$i.'_hours'] = $nDayHours." ч.";	
					
					$nDayEarning = $oDBSalary->getTechEarningForDay($nIDPerson,date('Y-m-',$nDate).zero_padding($i,2));
					
					if(!empty($nDayEarning))$aData[$nIDPerson]['day_'.$i.'_earning'] .= $nDayEarning." лв.";	
					
					$oResponse->setDataAttributes($nIDPerson,'day_'.$i.'_hours',array('style' => 'text-align:right;padding-left:10px;'));			
					$oResponse->setDataAttributes($nIDPerson,'day_'.$i.'_earning',array('style' => 'text-align:right;padding-left:10px;'));	
				}
				
				
				$oResponse->setDataAttributes($nIDPerson,'create',array('style' => 'text-align:right'));
				$oResponse->setDataAttributes($nIDPerson,'destroy',array('style' => 'text-align:right'));
				$oResponse->setDataAttributes($nIDPerson,'holdup',array('style' => 'text-align:right'));
				$oResponse->setDataAttributes($nIDPerson,'arrange',array('style' => 'text-align:right'));
				
				$oResponse->setDataAttributes($nIDPerson,'hours',array('style' => 'text-align:right;padding-left:10px'));
				$oResponse->setDataAttributes($nIDPerson,'earning',array('style' => 'text-align:right;padding-left:10px'));
				$oResponse->setDataAttributes($nIDPerson,'stake',array('style' => 'text-align:right;padding-left:10px'));
			
			}
		
			$oResponse->addTotal('create',$nCreateTotal);
			$oResponse->addTotal('destroy',$nDestroyTotal);
			$oResponse->addTotal('holdup',$nHoldupTotal);
			$oResponse->addTotal('arrange',$nArrangeTotal);
			
			$nHoursTotal .= ' ч.';
			$nEarningTotal .= ' лв.';
			$nStakeTotal = number_format($nEarningTotal/$nHoursTotal,2);
			
			$oResponse->addTotal('hours',$nHoursTotal);
			$oResponse->addTotal('earning',$nEarningTotal);
			$oResponse->addTotal('stake',$nStakeTotal);
			
			$oResponse->setData($aData);
			$oResponse->printResponse();
		}
		
	}

?>