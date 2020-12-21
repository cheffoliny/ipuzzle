<?php
	class ApiSetSetupPatrol {
		public function result( DBResponse $oResponse ) {

			$oWorkingCards = new DBWorkCardOffices();
			$oRoadLists	= new DBRoadLists();
			
			$nID		= Params::get("nID", 0);				
			$nIDCard	= Params::get("nIDCard", 0);				
			$nRegion	= Params::get("nRegion", 0);				
			$sAct		= Params::get("sAct", "choice");	
			$nIDPatrul	= 0;	
			$nAuto		= 0;
			$nPersons	= array();

			if ( !empty($nID) && $sAct == "list" ) {
				$aRes = $oRoadLists->getRoadList( $nID );

				if ( !empty($aRes) ) {
					$nRegion	= $aRes['id_office'];
					$nIDPatrul	= $aRes['id_patrul'];
					$nAuto		= $aRes['id_auto'];
					$nPersons	= explode( ",", $aRes['persons'] );		
					$oResponse->setFormElement('form1', 'startKm', array(), $aRes['start_km']);
				}
				
				$sAct = "choice"; 
			}
	
			$aOffice = array();
			$aOffice = $oWorkingCards->getWorkCardOffices( $nIDCard );

			$oResponse->setFormElement('form1', 'nRegion', array(), '');
			$oResponse->setFormElement('form1', 'nIDPatrul', array(), '');
			$oResponse->setFormElement('form1', 'nAuto', array(), '');
			$oResponse->setFormElement('form1', 'all_persons', array(), '');
			$oResponse->setFormElement('form1', 'choice_persons', array(), '');
			
			$oResponse->setFormElementChild('form1', 'nRegion', array('value' => 0), 'Изберете');	
			$oResponse->setFormElementChild('form1', 'nIDPatrul', array('value' => 0), 'Изберете');		
			$oResponse->setFormElementChild('form1', 'nAuto', array('value' => 0), 'Изберете');		
			
			foreach ( $aOffice as $key => $val ) {
				if ( $nRegion == $key ) {
					$oResponse->setFormElementChild('form1', 'nRegion', array('value' => $key, 'selected' => 'selected'), $val);
				} else $oResponse->setFormElementChild('form1', 'nRegion', array('value' => $key), $val);
			}				
			
			if ( !empty($nRegion) && $sAct == "choice" ) {
				$oPatruls = new DBPatruls();
				$oAuto = new DBAuto();
				$oPersonnel = new DBPersonnel();
				
				$aPatruls = array();
				$aAuto = array();
				$aPersons = array();
				$aRaodList = array();
			
				
				$aBusyPatruls = $oRoadLists->getBusyPatruls();	
				$sBusyPatruls = implode(",",$aBusyPatruls);
				
				$aBusyAutos = $oRoadLists->getBusyAutos();	
				$sBusyAutos = implode(",",$aBusyAutos);
				
				$sBusyPersons = $oRoadLists->getBusyPersons();


				$aPatruls	= $oPatruls		->getPatrulsByOffice( $nRegion , $sBusyPatruls );  
				$aAuto 		= $oAuto		->getPatrul			( $nRegion , $sBusyAutos );
				$aPersons 	= $oPersonnel	->getPatrulByOffice	( $nRegion , $sBusyPersons );	
				
				
				if( !empty($nID) ) {
					$nCurrentPatrulNum = $oPatruls->getNumByID($aRes['id_patrul']);
					$aPatruls[$aRes['id_patrul']] = $nCurrentPatrulNum;  
					
					$sCurrentAuto = $oAuto -> getAutoByID($aRes['id_auto']);
					$aAuto[$aRes['id_auto']] = $sCurrentAuto;
					
					$aCurrentPersons = $oPersonnel -> getByIDs($aRes['persons']);
				}
				
				foreach ($aCurrentPersons as $key => $value) {
					$aPersons[$key] = $value;
				}

				foreach ( $aPatruls as $key => $val ) {
					if ( $nIDPatrul == $key ) {
						$oResponse->setFormElementChild('form1', 'nIDPatrul', array('value' => $key, 'selected' => 'selected'), $val);
					} else $oResponse->setFormElementChild('form1', 'nIDPatrul', array('value' => $key), $val);
				}				

				foreach ( $aPersons as $key => $val ) {
					if ( !in_array( $key, $nPersons ) ) {
						$oResponse->setFormElementChild('form1', 'all_persons',		array('value' => $key), $val);
					} else {
						$oResponse->setFormElementChild('form1', 'choice_persons',	array('value' => $key), $val);
					}
				}				

				foreach ( $aAuto as $key => $val ) {
					if ( $nAuto == $key ) {
						$oResponse->setFormElementChild('form1', 'nAuto', array('value' => $key, 'selected' => 'selected'), $val);
					} else $oResponse->setFormElementChild('form1', 'nAuto', array('value' => $key), $val);
				}								
			}
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse ) {

			$oRoadList = new DBRoadLists();
			$oAuto = new DBAuto();
			$aPosition = $oAuto->getPatrulPosition();

			$nID		= Params::get("nID", 0);	
			$nIDCard	= Params::get("nIDCard", 0);	
			$nRegion	= Params::get("nRegion", 0);	
			
			$aPersons	= Params::get("choice_persons", '');
			$sPersons	= implode( ',',$aPersons);
			$nIDPatrul	= Params::get("nIDPatrul", 0);
			$nAuto		= Params::get("nAuto", 0);
			$startKm	= Params::get("startKm", 0);

			if ( empty($nRegion) ) {
				throw new Exception("Изберете регион!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($nIDPatrul) ) {
				throw new Exception("Изберете позивна на патрул!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($nAuto) ) {
				throw new Exception("Изберете автомобил!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( empty($startKm) ) {
				throw new Exception("Въведете начален километраж!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($sPersons) ) {
				throw new Exception("Изберете служители!", DBAPI_ERR_INVALID_PARAM);
			}

			$aBusyPatruls = $oRoadList->getBusyPatruls();	
			
			if(in_array($nIDPatrul,$aBusyPatruls))	{
				throw new Exception("Тази позивна е заета!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$aBusyAutos = $oRoadList->getBusyAutos();
			
			if(in_array($nAuto,$aBusyAutos))	{
				throw new Exception("Този автомобил е зает!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$sBusyPersons = $oRoadList->getBusyPersons();
			$aBusyPersons = explode(',',$sBusyPersons);
			
			foreach ($aPersons as $value)	{
				if (in_array($value,$aBusyPersons)) {
					throw new Exception("Избрали сте зает вече човек, рефрешнете страницата!", DBAPI_ERR_INVALID_PARAM);
				}
			}
			
			$aData = array();
			$aData['id'] = 	$nID;
			$aData['id_auto'] = $nAuto;
			$aData['id_office'] = $nRegion;
			$aData['id_function'] = $aPosition['id'];
			$aData['id_patrul'] = $nIDPatrul;
			$aData['id_work_card'] = $nIDCard;
			$aData['persons'] = $sPersons;
			$aData['start_km'] = $startKm;
			$aData['start_time'] = time();;
			$aData['updated_user'] = $_SESSION['userdata']['id_person'];
			$aData['updated_time'] = time();;
				
			$oRoadList->update( $aData );
							
			$oResponse->printResponse();
		}		
		
	}
?>