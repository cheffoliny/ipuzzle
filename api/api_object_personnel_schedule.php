<?php

	class ApiObjectPersonnelSchedule
	{
		public function result( DBResponse $oResponse )
		{
			$oDBPersonObjects = new DBObjectPersonnel();
			$oDBPersonObjects->getReport( $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function delete( DBResponse $oResponse ) {
			
			$nID = Params::get("nID", 0);
			$nIDP = Params::get("nIDRelation", 0);
			$nPerson = Params::get("nIDPerson", 0);

			if( empty( $nIDP ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);

			if( empty( $nPerson ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$oDBPersonObjects = new DBObjectPersonnel();
			
			$aData = array();
			$aData['person'] = $nPerson;
			$aData['object'] = $nID;
			
			$aaa = $oDBPersonObjects->getPersonIsFree( $aData );
			
//			$oDBPersonObjects->StartTrans();
			
//			if ( isset($aaa['cnt']) ) {
//				if ( $aaa['cnt'] == 0 ) {
					$oDBPersonObjects->delete( $nIDP );
					$oDBPersonObjects->deleteFreePerson( $aData );
//					$oDBPersonObjects->CompleteTrans();
//				} else {
//					$oDBPersonObjects->FailTrans();
//					throw new Exception('Служитела се води в текущ график!!!', DBAPI_ERR_INVALID_PARAM);
//				}
//			}
			//APILog::Log(0, $aaa);
			//$oDBPersonObjects->delete( $nID );
			
			$oResponse->printResponse();
		}
		
		public function addPerson( DBResponse $oResponse ) {
			$nIDObject   = Params::get("nID", 0);
			$nPersonCode = Params::get("nPersonCode", 0);
			$sPersonName = Params::get("sPersonName", "");
			$sDateFrom	 = Params::get("dateFrom",'');
			
			if ( empty( $nIDObject ) ) {
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			}
				
			if ( empty( $nPersonCode ) && empty( $sPersonName ) ) {
				throw new Exception("Изберете служител!");
			}
				
			$aPerson = array();
			$oPersonnel = new DBPersonnel();
			
			if ( !empty( $nPersonCode ) ) {
				$aPerson = $oPersonnel->getPersonnelByCode( $nPersonCode );
			}
				
			if ( empty( $aPerson ) ) {
				if ( !empty( $sPersonName ) ) {
					$aPerson = $oPersonnel->getPersonnelByNames( $sPersonName );
				}
			}
			
			if ( empty( $aPerson ) ) {
				throw new Exception("Служителя неможе да бъде намерен!", DBAPI_ERR_INVALID_PARAM);
			}
			
			list($m,$y) = explode('.',$sDateFrom);
			$sDateFrom = $y."-".$m."-01";
			
			$aObjectPersonnel = array();	
			$aObjectPersonnel['id_object'] = $nIDObject;
			$aObjectPersonnel['id_person'] = $aPerson['id'];
			if(date('m') == $m && date('Y') == $y) {
				$aObjectPersonnel['afterDate'] = time();
			} else {
				$aObjectPersonnel['afterDate'] = $sDateFrom;
			}
			
			$oObjectPersonnel = new DBObjectPersonnel();
			
			try {
				$oObjectPersonnel->update( $aObjectPersonnel );
			} catch( ADODB_Exception $e ) {
				if( $e->getCode() == 1582 ) {
					throw new Exception("Служителя вече е въведен за текущия обект!", DBAPI_ERR_INVALID_PARAM);
				} else throw $e;
			}
			
			$oResponse->printResponse();
		}
		
		public function sortNow( DBResponse $oResponse ) {
			global $db_sod;
			
			$nID = Params::get("nID", 0);
			$sorts = Params::get("sorts", array());
			
			$oDBPersonObjects = new DBObjectPersonnel();
			
			$sortArray = array();
			foreach ( $sorts as $key => $val ) {
				$level = $oDBPersonObjects->getOrder( $key );
				
				if ( $level['level'] != $val ) {
					$k = !empty( $key )? explode(",", $key) : 0;
					$sortArray[$k[0]] = $val;
				}
			}

			foreach ( $sortArray as $key => $val ) {
				$db_sod->startTrans();
					$max = $oDBPersonObjects->getMaxOrder( $nID );
					$level = $oDBPersonObjects->getOrder( $key );
					
					if ( $level['level'] != 0 ) {
							$data = array();
							$data['obj'] = 	$nID;
							$data['level'] = $val;
							
								
							$nIDFrom = $oDBPersonObjects->getIDByLevel( $data );
							$nIDFrom = isset($nIDFrom['id']) ? $nIDFrom['id'] : 0;

							if ( !isset($sortArray[$nIDFrom]) && $nIDFrom > 0 ) {
								$aData = array();
								$aData['id'] = $nIDFrom;
								$aData['level'] = $level['level'];
																
								$oDBPersonObjects->update( $aData );
							}

							$aData = array();
							$aData['id'] = $key;
							$aData['level'] = $val;

							$oDBPersonObjects->update( $aData );
					} else {
						$data = array();
						$data['obj'] = 	$nID;
						$data['level_from'] = $val;
						$data['level_to'] = $max['level'] + 1;
							
						$oDBPersonObjects->toLevel( $data );
						
						$aData = array();
						$aData['id'] = $key;
						$aData['level'] = $val;
						
						$oDBPersonObjects->update( $aData );
					}
					
				$db_sod->completeTrans();
			}
			
			$oResponse->printResponse();
		}
		
	}
	
?>