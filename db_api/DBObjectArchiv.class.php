<?php
	class DBObjectArchiv extends DBBase2 {
		public function __construct() {
			global $db_sod;
			
			parent::__construct($db_sod, 'messages');
		}

		public function getReport( $aData, DBResponse $oResponse ) {
			global $db_name_sod, $db_sod;
			
			$tables 		= array();
			$months 		= array();
			$last_date		= "";
			$sQuery			= "";
				
			$obj 			= isset($aData['nID']) 				? $aData['nID'] 			: 0;
			$periodFrom		= isset($aData['sPeriodFrom']) 		? $aData['sPeriodFrom'] 	: "";
			$periodFromH	= isset($aData['sPeriodFromH']) 	? $aData['sPeriodFromH'] 	: "";
			$periodTo		= isset($aData['sPeriodTo']) 		? $aData['sPeriodTo'] 		: "";
			$periodToH		= isset($aData['sPeriodToH']) 		? $aData['sPeriodToH'] 		: "";
			$bReact			= isset($aData['nReact']) 			? $aData['nReact'] 			: "false";
			$noTest 		= isset($aData['noTest']) 			? $aData['noTest'] 			: 0;
			$nNum 			= isset($aData['num']) 				? $aData['num'] 			: 0;
			$last 			= isset($aData['lastID']) 			? $aData['lastID'] 			: 0;
			
			if ( $bReact == "true" ) {
				$nReact = 1;
			} else {
				$nReact = 0;
			}

			$periodFrom 	= !empty($periodFrom) 		? date("Y-m-d", jsDateToTimestamp($periodFrom)) : date("Y-m")."-01";
			$periodTo		= !empty($periodTo) 		? date("Y-m-d", jsDateToTimestamp($periodTo)) 	: date("Y-m")."-31";
			$periodFromH	= !empty($periodFromH) 		? $periodFromH.":00"	: "00:00:00";
			$periodToH		= !empty($periodToH) 		? $periodToH.":00"		: "23:59:59";
						
			$from			= $periodFrom." ".$periodFromH;
			$to				= $periodTo." ".$periodToH;
		
			$dat1 			= array();
			$dat2 			= array();
			
			$dat1 			= explode("-", $periodFrom);
			$dat2 			= explode("-", $periodTo);
		
			$min_date_mon 	= isset($dat1[1]) ? $dat1[1] : date("m");
			$min_date_ye 	= isset($dat1[0]) ? $dat1[0] : date("Y");
			
			$max_date_mon 	= isset($dat2[1]) ? $dat2[1] : date("m");
			$max_date_ye 	= isset($dat2[0]) ? $dat2[0] : date("Y");	
	
			$tables 		= SQL_get_tables($db_sod, 'archiv_', '______');
		
			//if ( strlen($last) > 7  ) {
			//	$last_date 	= substr($last, 0, 6);
			//}
			
			foreach ( $tables as $val ) {
				if ( ($val >= "archiv_".$min_date_ye.$min_date_mon) && ($val <= "archiv_".$max_date_ye.$max_date_mon) ) {
					if ( !empty($last_date) && $val < "archiv_".$last_date ) {
						continue;
					}
					
					$months[] = $val;
				}				
			}	
			
			if ( !empty($obj) && (count($months) == 1) ) { 
				$mt = current($months);
				$yt = str_replace("archiv_", "", $mt);
		
				$sQuery = "
					SELECT 
						a.id as id,
						m.id_obj,
						DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
						a.msg as msg,
						IF (m.is_phone > 0, 'phone', 'radio') as type,
						a.pass as passibility
									
					FROM {$db_name_sod}.`{$mt}` a 
					LEFT JOIN {$db_name_sod}.messages m ON a.id_msg = m.id
					
					WHERE 1
						AND m.id_obj = '{$obj}'
						AND a.msg_time >= '{$from}'
						AND a.msg_time <= '{$to}'
				";
				
				if ( !empty($nReact) ) {
					$sQuery .= "\n AND UNIX_TIMESTAMP(a.response) > 0 \n";
				}
				
				if ( !empty($noTest) ) {
					$sQuery .= "\n AND (m.test_flag = 0 OR m.id_sig = 16) \n";
				}
				
				$sQuery .= " GROUP BY a.id ORDER BY a.msg_time DESC";
				

				//$this->getResult( $sQuery, 't', DBAPI_SORT_DESC, $oResponse );
			} elseif ( !empty($obj) && (count($months) > 1) )  {
				$sQuery = "";
				
				for ( $i = 0; $i < count($months) - 1; $i++ ) {
					$mt = $months[$i];
					$yt = str_replace("archiv_", "", $mt);
					
					$sQuery .= "
						( SELECT 
							a.id as id,
							m.id_obj,
							a.msg_time as t,
							DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
							a.msg as msg,
							IF (m.is_phone > 0, 'phone', 'radio') as type,
							a.pass as passibility
											
						FROM {$db_name_sod}.`{$mt}` a 
						LEFT JOIN {$db_name_sod}.messages m ON a.id_msg = m.id
						
						WHERE 1
							AND m.id_obj = '{$obj}'
							AND a.msg_time >= '{$from}'
							AND a.msg_time <= '{$to}'
					";
							
					if ( !empty($nReact) ) {
						$sQuery .= "\n AND UNIX_TIMESTAMP(a.response) > 0 \n";
					}
					
					if ( !empty($noTest) ) {
						$sQuery .= "\n AND (m.test_flag = 0 OR m.id_sig = 16) \n";
					}					
					
					$sQuery .= " GROUP BY a.id
						)
					   	UNION 
					"; 		
				}
				
				$mt = end($months);
				$yt = str_replace("archiv_", "", $mt);
				
				$sQuery .= "
					( SELECT 
						a.id as id,
						m.id_obj,
						a.msg_time as t,
						DATE_FORMAT(a.msg_time, '%d.%m.%Y %H:%i:%s') as msg_time,
						a.msg as msg,
						IF (m.is_phone > 0, 'phone', 'radio') as type,
						a.pass as passibility
						
					FROM {$db_name_sod}.`{$mt}` a 
					LEFT JOIN {$db_name_sod}.messages m ON a.id_msg = m.id
					
					WHERE 1
						AND m.id_obj = '{$obj}'
						AND a.msg_time >= '{$from}'
						AND a.msg_time <= '{$to}'			
				";				
						
				if ( !empty($nReact) ) {
					$sQuery .= "\n AND UNIX_TIMESTAMP(a.response) > 0 \n";
				}
				
				if ( !empty($noTest) ) {
					$sQuery .= "\n AND (m.test_flag = 0 OR m.id_sig = 16) \n";
				}				
				
				$sQuery .= " GROUP BY a.id )";	
				$sQuery .= " ORDER BY t DESC ";	
				
			}
			
			if ( count($months) > 0 ) {
				$aData = $this->select( $sQuery );
			} else {
				$aData = array();
			}
		
			$oResponse->setData( $aData );
			$oResponse->setSort("t", "ASC");			

		
			$oResponse->setField( "msg_time", 			"Дата", 		"" );
			$oResponse->setField( "msg", 				"Съобщение", 		"" );
			//$oResponse->setField( "RR", 				"РР", 		"" );
			//$oResponse->setField( "VR", 				"ВР", 		"" );
			//$oResponse->setField( "SSP", 				"ССП", 		"" );
			$oResponse->setField( "type", 				"Канал", 		"" );
		}
	}
?>