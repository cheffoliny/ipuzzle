<?php
	class ApiSetSetupBooksDel {
		
		public function save( DBResponse $oResponse ) {
			global $db_finance, $db_name_finance;
			
			$oBooks 	= new DBBooks();
			$status		= false;
			
			$nID 		= Params::get("nID",	 	0);
			$nNumFrom 	= Params::get("nNumFrom", 	0);
			$nNumTo 	= Params::get("nNumTo", 	"");
			$sNote 		= Params::get("note",	 	"");
			
			$nIDPerson 	= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;	
			
			if ( $nNumFrom < 700 ) {
				throw new Exception("Въведете първия номер от кочана!!!", DBAPI_ERR_INVALID_PARAM );
			}
			
			if ( $nNumTo < $nNumFrom ) {
				$nNumTo = $nNumFrom;
			}
			
			$db_finance->StartTrans();

			for ( $i = $nNumFrom; $i <= $nNumTo; $i++ ) {
				$status = $oBooks->checkNum($i);

				if ( !empty($status) ) {
					$oBooks->delete($status);
				}		
			}
			
			$sQuery = "
				INSERT INTO {$db_name_finance}.books_history 
					( id_person, act, from_num, to_num, note, updated_time )
				VALUES
					( {$nIDPerson}, 'delete', '{$nNumFrom}', '{$nNumTo}', '{$sNote}', NOW() )
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