<?php
	class ApiObjectArchiv {
		public function result(DBResponse $oResponse) {
			global $db_sod, $db_sod_name;
			
			$nID = Params::get('nID', 0); // $nIDOld
			//$period = Params::get('sPeriod', '');
			$nReact = Params::get('nReact', 0);

			$periodFrom	= Params::get("sPeriodFrom", "");
			$periodFromH= Params::get("sPeriodFromH", "");
			$periodTo	= Params::get("sPeriodTo", "");
			$periodToH	= Params::get("sPeriodToH", "");
			$noTest		= Params::get("noTest", 0);
			$nNum		= Params::get("num", 0);
			
			$periodFrom = !empty($periodFrom) ? date("Y-m-d", jsDateToTimestamp($periodFrom)) : date("Y-m")."-01";
			$periodTo	= !empty($periodTo) ? date("Y-m-d", jsDateToTimestamp($periodTo)) : date("Y-m")."-31";
			$periodFromH= !empty($periodFromH) ? $periodFromH : "00:00:00";
			$periodToH	= !empty($periodToH) ? $periodToH : "23:59:00";

			$from	= $periodFrom." ".$periodFromH;
			$to		= $periodTo." ".$periodToH;
			
			$dat1 = array();
			$dat2 = array();
			
			$dat1 = explode("-", $periodFrom);
			$dat2 = explode("-", $periodTo);
	
			$min_date_mon = isset($dat1[1]) ? $dat1[1] : date("m");
			$min_date_ye = isset($dat1[0]) ? $dat1[0] : date("Y");
			
			$max_date_mon = isset($dat2[1]) ? $dat2[1] : date("m");
			$max_date_ye = isset($dat2[0]) ? $dat2[0] : date("Y");
			
			$tables = array();
			$months = array();
			
			$tables = SQL_get_tables($db_sod, 'archiv_', '______');
			
			//APILog::Log(0, $tables);
			foreach ( $tables as $val ) {
				if ( ($val >= "archiv_".$min_date_ye.$min_date_mon) && ($val <= "archiv_".$max_date_ye.$max_date_mon) ) {
					if ( !empty($last_date) && $val < "archiv_".$last_date ) {
						continue;
					}
						
					$months[] = $val;
				}
			}
			
			//APILog::Log(0, $months);
			
			$aData['obj'] 		= $nID;
			$aData['tables'] 	= $months;
			$aData['react'] 	= $nReact;
			$aData['from'] 		= $from;
			$aData['to'] 		= $to;
			$aData['no_test'] 	= $noTest;
			$aData['num']		= $nNum;
			//print_r($aData);
			$oArchiv = new DBArchiv();
			$oArchiv->getArchivTNet( $oResponse, $aData );


			$oResponse->printResponse(); 
		}

	}
?>