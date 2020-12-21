<?php

	class ApiDayShifts2
	{
		public function load( DBResponse $oResponse )
		{
			$oDBFirms = new DBFirms();
			$aFirms = $oDBFirms->getFirms4();
				
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>'0')), "--Всички--");
			foreach($aFirms as $key => $value)
			{
				$oResponse->setFormElementChild('form1', 'nIDFirm', array_merge(array("value"=>$key)), $value);
			}		

			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");			
			
			$oResponse->printResponse();
		}
		
		public function loadOffices(DBResponse $oResponse) {
			$nFirm 	=	Params::get('nIDFirm');
			
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			
			if(!empty($nFirm)) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
				foreach($aOffices as $key => $value) {
					if (in_array($key,$_SESSION['userdata']['access_right_regions'])) {
						$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>$key)), $value);
					}
				}
			} else {
				$oResponse->setFormElementChild('form1', 'nIDOffice', array_merge(array("value"=>'0')), "--Всички--");
			}
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oObjectDuty = new DBObjectDuty();
			$oObjectShifts = new DBObjectShifts();
			$oDBSystem = new DBSystem();
			
			$aObjects = array();
			$aObjects = $oObjectShifts->getObjects($aParams['nIDFirm'],$aParams['nIDOffice']);
					
			$dTime = time();// - (56 * 20 * 60 * 60);
			
			$dMonthBefore = mktime(0, 0, 0, date("m")- 1, 1, date("Y"));
			//$dMonthBefore = strtotime("-1 month");
			
			$nMonth = date('m',$dMonthBefore);
			$nYear = date('Y',$dMonthBefore);
			$nAutoSalaryLastMonth = $oDBSystem->getAutoSalaryLastMonth();
			
			
			if($nAutoSalaryLastMonth != $nYear.$nMonth) {
				$this->auto_salary($nMonth,$nYear);
				$oDBSystem->updateAutoSalaryLastMonth($nYear.$nMonth);
			}
			
			$dTimeRound = mktime(date('H',$dTime),0,0,date('m',$dTime),date('d',$dTime),date('Y',$dTime));
			
			$dPlusSix = $dTimeRound + (7 * 60 * 60);
			$dMinusSix = $dTimeRound - (6 * 60 * 60);
			$dTimeRound = date("Y-m-d H:i:s", $dTimeRound);
			
			$aShifts = array();
			$aShifts = $oObjectDuty->getShiftsInInterval($dMinusSix,$dPlusSix,$aParams['nIDFirm'],$aParams['nIDOffice']);
			$aTmp = array();
			
			foreach ($aShifts as $key => $value) {
				if($value['startShift'] > $dMinusSix && $value['startShift'] < $dPlusSix) {
					if(empty($aObjects[$value['id_obj']][$value['startShift']])) {
						$aObjects[$value['id_obj']][$value['startShift']] = array();
					}
					if(!empty($value['startRealShift'])) {
						$aTmp['shift_type'] = 3;
					} else {
						$aTmp['shift_type'] = 1;
					}
					$aTmp['person'] = $value['person'];
					$aTmp['shift'] = $value['shift'];
					array_push($aObjects[$value['id_obj']][$value['startShift']],$aTmp);
				} 
				
				if( $value['endShift'] > $dMinusSix && $value['endShift'] < $dPlusSix) {
					if(empty($aObjects[$value['id_obj']][$value['endShift']])) {
						$aObjects[$value['id_obj']][$value['endShift']] = array();
					}
					if(!empty($value['endRealShift'])) {
						$aTmp['shift_type'] = 4;
					} else {
						$aTmp['shift_type'] = 2;
					}
					$aTmp['person'] = $value['person'];
					$aTmp['shift'] = $value['shift'];
					array_push($aObjects[$value['id_obj']][$value['endShift']],$aTmp);
				} 
			}
			
			$aRowTemplate = array('name' => "");
			
			$oResponse->setField('name','Обект','Обект');
			$oResponse->setFieldLink('name','openShift');
			

			$dMakeTime = $dMinusSix;
			for( $i=1 ; $i<14 ; $i++ ) {
				if( $i==7 ) {
					$oResponse->setField(date("H", $dTime),date("H:i:s", $dTime),date("H:i:s", $dTime),NULL,NULL,NULL,array("style" =>" text-align:center;"));
				} else {
					$oResponse->setField(date("H", $dMakeTime),date("H:i", $dMakeTime),date("H:i", $dMakeTime));
				}
				$aRowTemplate[date("H", $dMakeTime)] = "";
				$dMakeTime += 60*60;
			}
			
			$aData = array();
			$aRedObjects = array();
			
			foreach ($aObjects as $key => $value) {
				
				$aParams = array();
				$aParams['nID'] = $key;
				$aFirstUnvalidated = $oObjectDuty->getShift($aParams); 
				
				if(empty($aFirstUnvalidated)) $aFirstUnvalidated['nTime'] = "";
				
				if( $aFirstUnvalidated['nTime'] < $dMinusSix) {
					if(!empty($aFirstUnvalidated['nTime']))
					array_push($aRedObjects,$key);
				} else {
					$aData[$key] = $aRowTemplate;
					$aData[$key]['id'] = $key;
					$aData[$key]['name'] = $value['name'];
					
					foreach ($value as $key2 => $value2 ) {
						if ( $key2 != 'name' && $key2 != 'num') {
							
							$sInfo = "Обект: ".$value['name'];
							$aData[$key][date("H",$key2)] = date("H:i",$key2);
									
							$have_unvalidated = 0;
							foreach ( $value2 as $k => $v ) {
								$sInfo .= "\nСмяна: ".$v['shift']."\n";
								switch ($v['shift_type']) {
									case '1': $sInfo .= "Застъпва:";$have_unvalidated = 1;break;
									case '2': $sInfo .= "Отстъпва:";$have_unvalidated = 1;break;
									case '3': $sInfo .= "Застъпил:";break;
									case '4': $sInfo .= "Отстъпил:" ;break;
								}
								$sInfo .= $v['person'];
							}
							
							$sColor = "C8C8C8";
	
							if(!empty($have_unvalidated)) 	{	
								if($key2 < $dTime) {
									$sColor = "FF6464";
								} else {
									$sColor = "00C8FF";
								}
							}	
												
							$oResponse->setDataAttributes($key,date("H",$key2),array( "title" => $sInfo,
																					  "style" => "background: {$sColor}; text-align:center; cursor:pointer;",
																					  "onClick" => "openShift({$key})"
																					  ));
						}
					}
				}
			}
			
			foreach ($aRedObjects AS $red) {
				$aData[$red] = $aRowTemplate;
				$aData[$red]['id'] = $red;
				$aData[$red]['name'] = $aObjects[$red]['name'];
				$oResponse->setDataAttributes($red, 'name', array('class' => "red"));
			}
			
			$oResponse->setData($aData);
					
			$oResponse->printResponse("Смени","dayshifts");
		}
		
		public function auto_salary($nMonth,$nYear) {
			
			$nIDPerson = $_SESSION['userdata']['id_person'];
			$_SESSION['userdata']['id_person'] = 0;
			
			$tMonth = mktime(0,0,0,$nMonth,1,$nYear);
			$tMonthNext = mktime(0,0,0,$nMonth+1,1,$nYear);
			
			$nMonth = zero_padding($nMonth,2);
			
			$nMonth = $nYear.$nMonth;
			
			
			$oDBSalary = new DBSalary();
			$oDBPersonContract = new DBPersonContract();
			
			$aPersonsFix = array();
			$aPersonsMin = array();
			$aPersonsEarnings = array();
			$aIDPersons = array();
			$sIDPersons = '';
			
			
			$oDBSalary->delFixSalary($nMonth);
			
			$aPersonsFix = $oDBPersonContract->getPersonsWithFix($tMonth,$tMonthNext); 
			
			//APILog::Log(0,$aPersonsFix);
			
			foreach ( $aPersonsFix AS $value ) {
				$aData = array();
				$aData['id_person'] = $value['id_person'];
				$aData['id_office'] = $value['id_office'];
				$aData['month'] = $nMonth;
				$aData['code'] = '+ЩАТ';
				$aData['is_earning'] = 1;
				$aData['sum'] = $value['cost'];
				$aData['description'] = 'Щатна заплата';
				$aData['count'] = 1;
				$aData['total_sum'] = $value['cost'];
				
				$oDBSalary->update($aData);
			}
			
			$aPersonsMin = $oDBPersonContract->getPersonsWithMin($tMonth,$tMonthNext);
			
			foreach ($aPersonsMin as $key => $value ) {
				$aIDPersons[] = $value['id_person'];
			}
			
			$sIDPersons = implode(',',$aIDPersons);
			
			if(!empty($aPersonsEarnings)) $aPersonsEarnings = $oDBSalary->getPersonsEarnings($nMonth,$sIDPersons);
			
			foreach ($aPersonsMin as $key => $value ) {
				$nEarning = isset($aPersonsEarnings[$value['id_person']]) ? $aPersonsEarnings[$value['id_person']] : 0;
				
				if($value['cost'] > $nEarning) {
					
					$aData = array();
					$aData['id_person'] = $value['id_person'];
					$aData['id_office'] = $value['id_office'];
					$aData['month'] = $nMonth;
					$aData['code'] = '+ДО_МИН';
					$aData['is_earning'] = 1;
					$aData['sum'] = $value['cost'] - $nEarning;
					$aData['description'] = 'допълване до минимална заплата';
					$aData['count'] = 1;
					$aData['total_sum'] = $value['cost'] - $nEarning;
					
					$oDBSalary->update($aData);
					
				}
			}
			
			$dTwoMonthBefore = mktime(0, 0, 0, date("m")- 2, 1, date("Y"));
			//$dTwoMonthBefore = strtotime("-2 month");
			$nMonth2 = date('m',$dTwoMonthBefore);
			$nYear2 = date('Y',$dTwoMonthBefore);

			$nMonth2 = zero_padding($nMonth2,2);
			
			$nMonth2 = $nYear2.$nMonth2;

			$aAutoSalary = $oDBSalary->getAutoSalaries($nMonth2);
			
			foreach($aAutoSalary as $value) {
				$aData = $oDBSalary->getRecord($value['id']);
				$aData['id'] = 0;
				$aData['month'] = $nMonth;
				$oDBSalary->update($aData);
			}
			
			
			// Активи - Самоучастие --------------------------------------------------
			
			$oDBAssets = new DBAssets();
			$oDBAssetsSettings = new DBAssetsSettings();
			$oDBSalaryEarning = new DBSalaryEarning();
			$oDBPersonnel = new DBPersonnel();
			
			$aAssetsSettings = $oDBAssetsSettings->getRecord(1);
			$aIDMOLsAndIDAssets = $oDBAssets->getIDMOLsAndIDAssets();
			$aCodeAssetOwn = $oDBSalaryEarning->getCodeAssetOwn();
			
			if(!empty($aCodeAssetOwn)) {
			
				$aPersons = array();
				
				foreach ($aIDMOLsAndIDAssets as $value) {
					$aPersons[$value['id_person']][] = $value['id_asset'];
				}
				
				foreach ($aPersons as $id_person => $aAssets) {
					if(!empty($id_person)) {
						$aPersonnel = $oDBPersonnel->getRecord($id_person);
						$dSum = '';
						foreach ($aAssets as $id_asset) {
							$nPrice = $oDBAssets->getPrice($id_asset);
							$dSum += $nPrice;
						}
						
						$dSum = $dSum * $aAssetsSettings['asset_own_coef'] / 100;
						$dSum = round($dSum,2);
						
						if(!empty($dSum)) {
							$aData = array();
							$aData['id_person'] = $id_person;
							$aData['id_office'] = $aPersonnel['id_office'];
							$aData['month'] = $nMonth;
							$aData['code'] = $aCodeAssetOwn['code'];
							$aData['is_earning'] = 1;
							$aData['sum'] = $dSum;
							$aData['description'] = $aCodeAssetOwn['name'];
							$aData['count'] = 1;
							$aData['total_sum'] = $dSum;
							
							$oDBSalary->update($aData);
						}
					}
				}
				
			}
			
			$_SESSION['userdata']['id_person'] = $nIDPerson;
		}
		
		
		//Pavel
		public function autoValidate( DBResponse $oResponse ) {
			global $db_sod, $db_name_sod, $db_name_personnel;
			
			$day = date("Y-m-d");
			$user = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			$nIDs = "";
			
			$db_sod->startTrans();
			
			$sQueryID = "
				SELECT 
					GROUP_CONCAT(od.id) as id 
				FROM object_duty od
				LEFT JOIN object_shifts os on os.id = od.id_shift
				WHERE od.id_shift > 0
					AND os.automatic = 1
					
					AND UNIX_TIMESTAMP(od.endRealShift) = 0	
					AND od.endShift <= '".$day." 23:59:00'				
			";
			//AND UNIX_TIMESTAMP(od.startRealShift) = 0
			$res = $db_sod->Execute( $sQueryID );
			
			if ( !$res->EOF ) {
				$nIDs = $res->fields['id'];
			}
			
			if ( empty($nIDs) ) {
				$nIDs = "-1";
			}
			
			$sQuery = "
				UPDATE object_duty od, object_shifts os
				SET od.startRealShift = od.startShift,
					od.endRealShift = od.endShift,
					od.note = 'Автоматична смяна', 
					od.stake = os.stake,
					od.updated_user = {$user},
					od.updated_time = NOW()
				WHERE od.id_shift = os.id
					AND od.id_shift > 0
					
					AND UNIX_TIMESTAMP(od.endRealShift) = 0	
					AND od.endShift <= '".$day." 23:59:00'				
					AND od.id IN ({$nIDs})
			
			";
			//AND UNIX_TIMESTAMP(od.startRealShift) = 0
			$db_sod->Execute( $sQuery );
			
			$sQuery2 = "
				INSERT INTO {$db_name_personnel}.salary (id_person, id_office, id_object, id_object_duty, month, code, is_earning, sum, description, count, total_sum, created_user, created_time, updated_user, updated_time, to_arc )
				SELECT 
					od.id_person, 
					o.id_office, 
					od.id_obj, 
					od.id, 
					CONCAT( 
						DATE_FORMAT( od.startRealShift, '%Y' ), 
						DATE_FORMAT( od.startRealShift, '%m' ) 
						), 
					se.code, 
					1, 
					IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) AS stake, 
					CONCAT('Автоматична - [', os.code, '] ', DATE_FORMAT( od.startRealShift, '%d.%m.%Y %H:%i' ) ) as name,
					CONCAT(( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) div 3600, '.', (( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) mod 3600) / 60),
					ROUND( IF (od.stake > 0, IF (pc.rate_reward, ((od.stake*pc.rate_reward)/100), od.stake), IF (pc.rate_reward, ((os.stake*pc.rate_reward)/100), os.stake)) * ( ( UNIX_TIMESTAMP( od.endRealShift ) - UNIX_TIMESTAMP( od.startRealShift ) ) / 3600 ), 2 ),
					{$user},
					NOW(),
					{$user},
					NOW(),
					0
				FROM {$db_name_sod}.object_duty od
				LEFT JOIN {$db_name_sod}.objects o ON od.id_obj = o.id
				LEFT JOIN {$db_name_personnel}.salary_earning_types se ON se.source = 'schedule'
				LEFT JOIN {$db_name_sod}.object_shifts os ON od.id_shift = os.id
				LEFT JOIN {$db_name_personnel}.person_contract pc ON (pc.to_arc = 0 AND pc.id_person = od.id_person AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_from)) <= UNIX_TIMESTAMP(od.startRealShift) AND UNIX_TIMESTAMP((INTERVAL 1 DAY + trial_to)) >= UNIX_TIMESTAMP(od.endRealShift) )
				WHERE 1
					AND od.stake > 0
					AND od.startRealShift > 0
					AND od.endRealShift   > 0
					AND od.endShift > od.startShift
					AND od.id IN ({$nIDs})

			";
			//APILog::Log(0, $sQuery2);
			$db_sod->Execute( $sQuery2 );
			
			$db_sod->completeTrans();
			
			$oResponse->printResponse();
		}				
	}
?>