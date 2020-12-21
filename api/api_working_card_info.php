<?php
	class ApiWorkingCardInfo {
		public function result( DBResponse $oResponse ) {

			$oCurrentCard = new DBWorkCard();
			$nIDCard = Params::get("nIDCard", 0);	
			
			if ( $nIDCard ) {
				$aData = array();
				$aData = $oCurrentCard->getWorkCardInfo( $nIDCard );

				if ( isset($aData['endTime']) && empty($aData['endTime']) ) {
					$locked = "no";	
				} else $locked = "yes";
				
				$oResponse->setFormElement('form1', 'isLOCK', array('value' => $locked), $locked);

				$oResponse->setFormElement('form1', 'sDispatcher', array(), $aData['dispatcher']);
				$oResponse->setFormElement('form1', 'sFrom', array(), $aData['startTime']);
				$oResponse->setFormElement('form1', 'sTo', array(), $aData['endTime']);
								
				$aOffices = array();
				$aOffices = $oCurrentCard->getWorkCardOffices( $nIDCard );
				
				$oResponse->setFormElement('form1', 'all_regions',		array(), '');
				$oResponse->setFormElement('form1', 'account_regions',	array(), '');

				
				foreach ( $aOffices as $key => $val ) {
					$access = explode( ",", $val['perm'] );
					
					if ( !in_array( $key, $access ) ) {
						$oResponse->setFormElementChild('form1', 'all_regions',		array('value' => $key),$val['name']);
					} else {
						$oResponse->setFormElementChild('form1', 'account_regions',	array('value' => $key), $val['name']);
					}
				}
				
			}
			
			$oResponse->printResponse("Работни карти", "working_cards");
		}

		public function save( DBResponse $oResponse ) {
			global $db_sod;
			
			$oCurrentCard = new DBWorkCardOffices();
			$oCard = new DBWorkCard();
			
			$regions = "";
			$nIDCard = Params::get("nIDCard", 0);	
			$sTo = Params::get("sTo", '');
			$from = Params::get("sFrom", '');
			$test2 = explode(" ", $from);
			$test = jsDateToTimestamp( isset($test2[0]) ? $test2[0] : "1970-01-01" );
			$sFrom = date("Y-m-d", $test);
			$sFrom .= isset($test2[1]) ? " ".$test2[1] : " 00:00";
			APILog::Log(0, $sFrom);
			//$sFrom = jsDateToTimestamp( Params::get("sFrom", '') );
				
			$account_regions = Params::get("account_regions", '');
			
			if ( !empty($nIDCard) && empty($sTo) ) {	
				$db_sod->StartTrans();
				
				$aData = array();
				$aData['id'] = $nIDCard;
				$aData['start_time'] = $sFrom;
				
				$oCard->update( $aData );		

				$regions = "";

				$db_sod->Execute( "DELETE FROM work_card_offices WHERE id_work_card = {$nIDCard}" );

				if ( !empty($account_regions) ) {
						
					foreach ( $account_regions as $val ) {
						$regions .= " ({$nIDCard}, {$val}),";		
					}
					
					$regions = substr( $regions, 0, -1 );
					$qry = "
						INSERT INTO work_card_offices 
							(id_work_card, id_office) 
						VALUES
							{$regions}
					";
					
					$db_sod->Execute( $qry );
				}
			
				$db_sod->CompleteTrans();
			}
						
			$oResponse->printResponse();
		}		

		public function close( DBResponse $oResponse ) {
			global $db_sod;
			
			$oCurrentCard = new DBWorkCard();
			
			$nIDCard = Params::get("nIDCard", 0);	
			$sTo = Params::get("sTo", '');	
			
			if ( !empty($nIDCard) && empty($sTo) ) {	
				
				$aData = array();
				$aData['id'] = $nIDCard;
				$aData['end_time'] = time();
				$oCurrentCard->update( $aData );		
			}
						
			$oResponse->printResponse();
		}		
		
	}
?>