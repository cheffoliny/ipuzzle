<?php
	class ApiSetSetupBooksSet {
		
		public function save( DBResponse $oResponse ) {
			global $db_finance, $db_name_finance;
			
			$oBooks 	= new DBBooks();
			$status		= false;
			$is_use		= 0;
			
			$nID 		= Params::get("nID",	 	0);
			$nNumFrom 	= Params::get("nNumFrom", 	0);
			$nNumTo 	= Params::get("nNumTo", 	"");
			$busy 		= Params::get("busy",	 	0);
			$sNote 		= Params::get("note",	 	"");
			
			$nIDPerson 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;	
			
			//throw new Exception("ID: ".ArrayToString($busy), DBAPI_ERR_INVALID_PARAM );

			if ( $nNumFrom < 700 ) {
				throw new Exception("Въведете първия номер от кочана!!!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if ( $nNumTo < $nNumFrom ) {
				$nNumTo = $nNumFrom;
			}
			
			if ( !empty($busy) ) {
 				if ( $busy == "lock" ) {
 					$is_use = 1;
 				} elseif ( $busy == "unlock" ) {
 					$is_use = 0;
 				} else {
 					throw new Exception("Посочете действие - ЗАКЛЮЧВАНЕ или ОТКЛЮЧВАНЕ!!!", DBAPI_ERR_INVALID_PARAM );	
 				}
			} else {
				throw new Exception("Посочете действие - ЗАКЛЮЧВАНЕ или ОТКЛЮЧВАНЕ!!!", DBAPI_ERR_INVALID_PARAM );	
			}
			
			$db_finance->StartTrans();

			for ( $i = $nNumFrom; $i <= $nNumTo; $i++ ) {
				$status 			= $oBooks->checkNum($i);
				
				if ( empty($status) ) {
					$db_finance->FailedTrans();
					throw new Exception("Номер {$i} НЕ е регистриран!!!\nПроцеса е прекратен!", DBAPI_ERR_INVALID_PARAM );
				}

				$aData 				= array();
				$aData["id"] 		= $status;
				$aData["num"] 		= $i;
				$aData["is_use"] 	= $is_use;
	
				$oBooks->update($aData);		
			}
			
			
			
			$sQuery = "
				INSERT INTO {$db_name_finance}.books_history 
					( id_person, act, from_num, to_num, note, updated_time )
				VALUES
					( {$nIDPerson}, 'set', '{$nNumFrom}', '{$nNumTo}', '{$sNote}', NOW() )
			";
			
			$db_finance->Execute($sQuery);

			$db_finance->CompleteTrans();
			
			$oResponse->printResponse();
		}
		
		public function result( DBResponse $oResponse ) {
			global $db_finance, $db_name_finance;
			
			$oBooks 	= new DBBooks();
			$status		= false;
			$aData		= array();
			
			$nID 		= Params::get("nID",	 	0 );
			$nNumFrom 	= Params::get("nNumFrom", 	0 );
			$nNumTo 	= Params::get("nNumTo", 	"" );
			$busy 		= Params::get("busy",	 	0 );
			$nIDPerson 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;	
	
			if ( !empty($nID) ) {
				$aData = $oBooks->getHistoryByID($nID);
				
				if ( isset($aData['from_num']) && !empty($aData['from_num']) && isset($aData['act']) && (($aData['act'] == 'add') || ($aData['act'] == 'set')) ) {
					$nNumFrom 	= $aData['from_num'];
					$nNumTo		= $aData['from_num'] >= $aData['to_num'] ? $aData['from_num'] : $aData['to_num'];
				}
			}
			
			$oResponse->setFormElement( "form1", "nNumFrom", array(), $nNumFrom );
			$oResponse->setFormElement( "form1", "nNumTo", array(), $nNumTo );
			
			$oResponse->printResponse();
		}				
	}
	
?>