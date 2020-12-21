<?php
	class ApiSetLimitCardPersons {
		public function load( DBResponse $oResponse ) {
			$aData		= array();
			$aFirms		= array();
			$aOffices	= array();
			$aPersonnel	= array();
			$aPersons	= array();
		
			$aParams = Params::getAll();
			
			$nID		= Params::get("nID", 0);
			$nIDCard	= Params::get("nIDCard", 0);
			$nIDFirm	= Params::get("nIDFirm", 0);
			$nIDOffice	= Params::get("nIDOffice", 0);
			$nIDPerson	= Params::get("nIDPerson", 0);
			
			$oFirms				= new DBFirms();
			$oOffices			= new DBOffices();
			$oLimitCards		= new DBTechLimitCards();
			$oPersonnel			= new DBPersonnel();
			$oLimitCardPersons	= new DBLimitCardPersons();
			
			if ( empty($nID) && !empty($nIDCard) && (empty($nIDFirm) && empty($nIDOffice)) ) {
				$tmp = $oLimitCards->getLimitCard($nIDCard);
				$nIDOffice = isset($tmp['tech_office']) ? $tmp['tech_office'] : 0;

				if ( !empty($nIDOffice) ) {
					$nIDFirm = $oOffices->getFirmByIDOffice($nIDOffice);
				}
			}
			
			$aDataPerson = array();
			$aDataPerson['id'] = $nID;
			$aDataPerson['id_card'] = $nIDCard;
			$aPersons = $oLimitCardPersons->getPersonsByID( $aDataPerson );
			//APILog::Log(0, $aPersons);
			$aPrsn = isset($aPersons['id_person']) && !empty($aPersons['id_person']) ? $aPersons['id_person'] : 0;
			$aPrsns = isset($aPersons['persons']) && !empty($aPersons['persons']) ? $aPersons['persons'] : '';
			$aOffs = isset($aPersons['id_office']) && !empty($aPersons['id_office']) ? $aPersons['id_office'] : 0;
			$aFirm = isset($aPersons['id_firm']) && !empty($aPersons['id_firm']) ? $aPersons['id_firm'] : 0;
			$aPernt = isset($aPersons['percent']) && !empty($aPersons['percent']) ? $aPersons['percent'] : 0;
			$aSumPernt = isset($aPersons['sum_percent']) && !empty($aPersons['sum_percent']) ? $aPersons['sum_percent'] : 0;
						
			$oResponse->setFormElement('form1', 'nIDPerson', array(), '');
			$oResponse->setFormElement('form1', 'nIDOffice', array(), '');
			$oResponse->setFormElement('form1', 'nIDFirm', array(), '');
			$oResponse->setFormElementChild('form1', 'nIDPerson', array('value' => 0), 'Избери');			
			$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => 0), 'Избери');			
			$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => 0), 'Избери');			

			if ( $nID > 0 ) {
				$oResponse->setFormElement('form1', 'nPercent', array(), $aPernt);
			} else {		
				$percent = $oLimitCardPersons->getFirstPercentByLC($nIDCard);
				//APILog::Log(0, "Процент: ".$aSumPernt);
				if ( $aSumPernt == 0 ) {
					$oResponse->setFormElement('form1', 'nPercent', array(), 100);
				} else {
					if ( $aSumPernt < 100 ) {
						$oResponse->setFormElement('form1', 'nPercent', array(), (100 - $aSumPernt) );
					} else {
						$percent = isset($percent['percent']) && !empty($percent['percent']) ? $percent['percent'] : 100;	
						$oResponse->setFormElement('form1', 'nPercent', array(), floor($percent / 2) );
					}
				}
			}
			
			$aFirms = $oFirms->getFirms();
			
			$nIDFirm = !empty($nIDFirm) ? $nIDFirm : $aFirm;
			$nIDOffice = !empty($nIDOffice) ? $nIDOffice : $aOffs;
			$nIDPerson = !empty($nIDPerson) ? $nIDPerson : $aPrsn;
			
			foreach ( $aFirms as $key => $val ) {
				if ( $nIDFirm == $key ) {
					$oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key, 'selected' => 'selected'), $val);
				} else $oResponse->setFormElementChild('form1', 'nIDFirm', array('value' => $key), $val);
			}	
						
			unset($key); unset($val);
						
			if ( $nIDFirm > 0 ) {
				$aOffices = $oOffices->getFirmOfficesAssoc( $nIDFirm );
				foreach ( $aOffices as $key => $val ) {
					if ( $nIDOffice == $key ) {
						$oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key, 'selected' => 'selected'), $val['name']);
					} else $oResponse->setFormElementChild('form1', 'nIDOffice', array('value' => $key), $val['name']);
				}	
			}			

			if ( ($nIDOffice > 0) && ($nIDFirm > 0) ) {
				$aDataPerson = array();
				$aDataPerson['office'] = $nIDOffice;
				$aDataPerson['persons'] = $aPrsns;
				$aDataPerson['current'] = $nIDPerson;

				$aPersonnel = $oPersonnel->getTechnicsByOfficeRest( $aDataPerson );
				
				foreach ( $aPersonnel as $key => $val ) {
					if ( $nIDPerson == $key ) {
						$oResponse->setFormElementChild('form1', 'nIDPerson', array('value' => $key, 'selected' => 'selected'), $val);
					} else $oResponse->setFormElementChild('form1', 'nIDPerson', array('value' => $key), $val);
					
					//$oResponse->setFormElementChild('form1', 'nIDPerson', array('value' => $key), $val);
				}	
			}			
			
			$oResponse->printResponse();
		}
			
		public function save( DBResponse $oResponse ) {
			$nID			= Params::get("nID", 0);
			$nIDCard		= Params::get("nIDCard", 0);
			$nIDPerson		= Params::get("nIDPerson", 0);
			$nPercent		= Params::get("nPercent", 0);
			
			$oLimitCardPersons	= new DBLimitCardPersons();
			
			$aDataPerson = array();
			$aDataPerson['id'] = $nID;
			$aDataPerson['id_card'] = $nIDCard;
			$aPersons = $oLimitCardPersons->getPersonsByID( $aDataPerson );
			
			$nSum = isset($aPersons['sum_percent']) && !empty($aPersons['sum_percent']) ? $aPersons['sum_percent'] : 0;
			
			if ( empty($nIDPerson) ) {
				throw new Exception("Изберете служител!!!", DBAPI_ERR_INVALID_PARAM);
			}

//			Временно премахване на ограничението върху процента - да се възстанови!!! /Павел
//			if ( empty($nPercent) ) {
//				throw new Exception("Въведете процентно съотношение за обслужването!", DBAPI_ERR_INVALID_PARAM);
//			} elseif ( ($nSum + $nPercent) > 100 ) {
//				throw new Exception("Сбора от процентното съотношение не може да надхвърли 100!!!", DBAPI_ERR_INVALID_PARAM);
//			}
			
			$percent = $oLimitCardPersons->getFirstPercentByLC($nIDCard);
			$perPercent = isset($percent['percent']) && !empty($percent['percent']) ? $percent['percent'] : 100;
			$perID = isset($percent['id']) && !empty($percent['id']) ? $percent['id'] : 0;
			
			if ( empty($nPercent) ) {
				throw new Exception("Въведете процентно съотношение за обслужването!", DBAPI_ERR_INVALID_PARAM);
			} 
			
			$free = (100 - $nSum);
			
			if ( $nSum < 100 ) { // Имаме свободни проценти
				if ( $nPercent > ($free + $perPercent) ) {
					throw new Exception("Надхвърляте максималния допустим процент!!!", DBAPI_ERR_INVALID_PARAM);
				}
			} 
			
			if ( $perID > 0 ) {
				if ( $nPercent > $free ) {
					$per = array();
					$per['id'] = $perID;
					$per['percent'] = ( $perPercent - ($nPercent - $free) );
				
					$oLimitCardPersons->update( $per );
				}
			}
			
			$lCard = array();
			$oLimitCard = new DBTechLimitCards();
			$lCard = $oLimitCard->getLimitCard( $nIDCard );

			$nIDObject = isset($lCard['id_object']) ? (int) $lCard['id_object'] : 0;
			$sPlannedStart = isset($lCard['planned_start']) ? $lCard['planned_start'] : '0000-00-00';
			$sPlannedEnd = isset($lCard['planned_end']) ? $lCard['planned_end'] : '0000-00-00';
			
			$tmpArr = array();
			$tmpArr['per'] = $nIDPerson;
			$tmpArr['obj'] = $nIDObject;
			$tmpArr['start'] = $sPlannedStart;
			$tmpArr['end'] = $sPlannedEnd;
			//throw new Exception($tmpArr['end'], DBAPI_ERR_INVALID_PARAM);
			$test = $oLimitCardPersons->getPersonDub( $tmpArr );
			
			if ( isset($test['id_limit_card']) && !empty($test['id_limit_card']) ) {
				$lc = zero_padding($test['id_limit_card']);
				throw new Exception("Грешка при планиране: презастъпване с лимитна карта № {$lc}!!!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aData = array();
			$aData['id']			= $nID;
			$aData['id_limit_card'] = $nIDCard;
			$aData['id_person']		= $nIDPerson;
			$aData['percent']		= $nPercent;
			
			$oLimitCardPersons->update( $aData );
		}
			
	}
	
?>