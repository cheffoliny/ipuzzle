<?php
	class ApiSetSetupBooksAdd {
		
		public function save( DBResponse $oResponse ) {
			global $db_finance, $db_name_finance;
			
			$oBooks 	= new DBBooks();
			$status		= false;
			
			//Params
			$nNumFrom 	= Params::get("nNumFrom", 	0 );
			$nNumTo 	= Params::get("nNumTo", 	"" );
			$busy 		= Params::get("busy",	 	0 );
			$sNote 		= Params::get("note",	 	"" );
			$nIDPerson 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;	
			//End Params
			
			//Validation
			if ( $nNumFrom < 700 ) {
				throw new Exception("Въведете първия номер от кочана!!!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if ( $nNumTo < $nNumFrom ) {
				$nNumTo = $nNumFrom;
			}
			
			if ( !empty($busy) ) {
 				$busy = 1;
			}
			//End Validation
			
			$db_finance->StartTrans();

			for ( $i = $nNumFrom; $i <= $nNumTo; $i++ ) {
				$status 			= $oBooks->checkNum($i);
				
				if ( $status > 0 ) {
					$db_finance->FailedTrans();
					throw new Exception("Номер {$i} вече е регистриран!!!\nПроцеса е прекратен!", DBAPI_ERR_INVALID_PARAM );
				}

				$aData 				= array();
				$aData["id"] 		= 0;
				$aData["num"] 		= $i;
				$aData["is_use"] 	= $busy;
	
				$oBooks->update($aData);		
			}
			
			
			
			$sQuery = "
				INSERT INTO {$db_name_finance}.books_history 
					( id_person, act, from_num, to_num, note, updated_time )
				VALUES
					( {$nIDPerson}, 'add', '{$nNumFrom}', '{$nNumTo}', '{$sNote}', NOW() )
			";
			
			$db_finance->Execute($sQuery);

			$db_finance->CompleteTrans();
			
			$oResponse->printResponse();
		}
	}
	
?>