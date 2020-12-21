<?php

	class ApiPersonalCardSchedule {
		
		public function result(DBResponse $oResponse) {
			
			$sDate 	= Params::get("date", "");
			$nIDPerson = Params::get("id_person","0");
			
			
			$nDate = jsDateToTimestamp( $sDate );
			
			$oDBLimitCardPersons = new DBLimitCardPersons();
			$oDBPersonnel = new DBPersonnel();
			
			$aLimitCardPerson = array();
			$aPersonLimitCards = array();
			
			$aLimitCardPerson = $oDBLimitCardPersons->schedulePerson($nIDPerson,$nDate);
			
			$aPersonLimitCards[$nIDPerson] = array();
			
			foreach( $aLimitCardPerson as $aCard )
				$aPersonLimitCards[ $aCard['id_person'] ][ $aCard['id'] ] = $aCard;
				
			//APILog::Log(0,$aPersonLimitCards);
			
			for( $i=8;$i<18;$i++)
			{
				$aRowTemplate[ sprintf("%02s", $i).":00" ] = "";
				$aRowTemplate[ sprintf("%02s", $i).":30" ] = "";		
				$oResponse->setField( sprintf("%02s", $i).":00",  sprintf("%02s", $i).":00");
				$oResponse->setField(sprintf("%02s", $i).":30", sprintf("%02s", $i).":30");
			}
			
			
			$aData = array();
			
			$sPersonCellID = $nIDPerson;

			
			$aData[ $sPersonCellID ] 				= $aRowTemplate;
			//$aData[ $sPersonCellID ]['id']			= $sPersonCellID;
			//$aData[ $sPersonCellID ]['class']		= 'person';	
				
			
			foreach( $aPersonLimitCards[$nIDPerson] as $nIDCard => $aCard )
			{
				$bBegin = true;
				for( $i=16;$i<36;$i++)
				{
					if( ($nDate + 30 * $i * 60 >= $aCard['p_start']) && ($nDate + 30 * $i * 60 < $aCard['p_end']))
					//if( ( ( 30 * $i ) >= $aCard['planned_start_mins']  ) &&	( ( 30 * $i ) < $aCard['planned_end_mins']  ))
					{
						if(date('d',$nDate) != date('d',$aCard['p_start'])) $bBegin = false;
						
						$sType = "";
						$sTitle = "";
							
						switch ($aCard['type'])
						{
							case 'create': $sBackgroundColor = '5957bb';	$sType = 'изграждане'; 	 break;
							case 'destroy': $sBackgroundColor = 'bd2937';	$sType = 'снемане';		 break;
							case 'arrange': $sBackgroundColor = '569457';$sType = 'аранжиране'; break;
							case 'holdup': $sBackgroundColor = 'purple';$sType = 'профилактика';  break;
							default: $sBackgroundColor = 'green'; $sType = ''; break;
						}	
						
						if( $aCard['r_start'] != '0' && $aCard['r_end'] == '0') {
							$sBackgroundColor = "#dddd00";
						}
						
						if($aCard['status'] == 'closed') $sBackgroundColor = 'silver';
								
						$sTitle = $aCard['object_name'];
						$sTitle.= "\n".$sType;	
					
						$sTitle .= "\n\nСъздал Задачата:";
						$aPersonNames = $oDBPersonnel->getPersonnelNames($aCard['request_create']);
						$sTitle .= "\n".$aPersonNames['names'];
						
						$sTitle .= "\n\nПланирал:";
						$aPersonNames = $oDBPersonnel->getPersonnelNames($aCard['created_user']);
						$sTitle .= "\n".$aPersonNames['names'];
						
						if($bBegin)
						{
							$oImage = "url('images/transperant_right_arrow.gif') no-repeat center";
							$bBegin = false;
							if($aCard['percent'] != 100) {
								
								$aPersonsWith = $oDBLimitCardPersons->getPersonsWith($aCard['id'],$aCard['id_person']);
								if(!empty($aPersonsWith)) $sTitle .= "\nЗаедно с:";
								foreach ($aPersonsWith as $v) {
									$sTitle .= "\n".$v['person']." ".$v['mobile'];
								}
							
								$aData[$sPersonCellID][sprintf("%02s",(int) ($i/2)).":".sprintf("%02s",$i*30%60)] = $aCard['percent']."%";
							}
						}
						else 
						{
							$oImage="";
						}
						$oResponse->setDataAttributes($sPersonCellID, sprintf("%02s",(int) ($i/2)).":".sprintf("%02s",$i*30%60), array('style' => "background: $sBackgroundColor $oImage;background-position:20%  ; cursor:pointer;color:white;text-align:right;",
																							 		   'onclick' => "refreshIFrames({$aCard['_id']})",
																									   'title' => "{$sTitle}"
																									   ));	
					}
				}
				
			}	
				
			
			$oResponse->setData($aData);
			$oResponse->printResponse();
		}
	}

?>