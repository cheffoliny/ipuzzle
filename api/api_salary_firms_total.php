<?php

	class ApiSalaryFirmsTotal {
		
		public function result(DBResponse $oResponse) {
			
			$nYear = Params::get('year','');
			$nMonth = Params::get('month','');
		
				
			if( empty($nYear) || $nYear < 2007 || $nYear > 2050 ) {
				throw new Exception("Въведете коректна година",DBAPI_ERR_INVALID_PARAM);
			}
			if( empty($nMonth) || $nMonth < 1 || $nMonth > 12 ) {
				throw new Exception("Въведете коректен месец",DBAPI_ERR_INVALID_PARAM);
			}
			
			$nMonth = zero_padding($nMonth,2);
			
			$oDBSalary = new DBSalary();
			$oDBFirms = new DBFirms();
			
			
			$aFirms = $oDBFirms->getFirms4();
			
			$aData = array();
			$aTotals = array();
			
			$oResponse->setField('name','фирми');
			foreach ( $aFirms as $keyFirmFrom => $nameFirmFrom ) {
				$oResponse->setField($keyFirmFrom,$nameFirmFrom);
				$oResponse->setDataAttributes($keyFirmFrom,'name', array( 'style' => '	color: ffffff;
																						background: #617cb3; 
																						border : 1px solid #e1e1e1;
																						font-weight:bold;
																						padding-left:20px;' ));
				$aTotals[$keyFirmFrom] = 0;
			}
			$oResponse->setField('total','Общо');
			
			foreach ( $aFirms as $keyFirmFrom => $nameFirmFrom ) {
				
				$aData[$keyFirmFrom]['name'] = $nameFirmFrom;		
				$sum_total = 0;
				
				foreach ( $aFirms as $keyFirmTo => $nameFirmTo ) {
					$aArray = array();
					$aArray['nIDFirmFrom'] = $keyFirmFrom;
					$aArray['nIDFirmTo'] = $keyFirmTo;
					$aArray['nMonth'] = $nYear.$nMonth;
					$aArray['sMonthSQL'] = date( "Y-m-01", mktime( 0, 0, 0, $nMonth, 1, $nYear ) );
					
					$sum = $oDBSalary->getByFirms($aArray);
					$aTotals[$keyFirmTo] += $sum;
					$sum_total += $sum;
					
					if(!empty($sum)) {
						$aData[$keyFirmFrom][$keyFirmTo] = $sum.' лв.';
						$nID = $keyFirmFrom.','.$keyFirmTo;
						$oResponse->setDataAttributes($keyFirmFrom,$keyFirmTo, array( 'style' => 'background: #eeeeee; 
																								  text-align:right;
																								  font-weight:bold;
																								  cursor: pointer;',
																						'onClick' => "openDetailed('$keyFirmFrom','$keyFirmTo')" ));
					} else {
						$aData[$keyFirmFrom][$keyFirmTo] = '';
						$oResponse->setDataAttributes($keyFirmFrom,$keyFirmTo, array( 'style' => 'background: #eeeeee; text-align:right;' ));
					}
				}
				
				if(!empty($sum_total)) $sum_total .= " лв.";
				$aData[$keyFirmFrom]['total'] = $sum_total;	
				$oResponse->setDataAttributes($keyFirmFrom,'total', array( 'style' => '	background: #cfedc6; 
																						text-align:right;
																						border-bottom:1px solid #91D67C;
																						font-weight:bold;' ));
			}
			
			$the_total = 0;
			$aData['dolen_total']['name'] = 'Общо';
			$oResponse->setDataAttributes('dolen_total','name',array( 'style' => '	color: ffffff;
																					text-align:right;
																					background: #617cb3; 
																					font-weight:bold;
																					padding-right:10px;'));
			foreach ( $aFirms as $keyFirmTo => $nameFirmTo ) {
				$the_total += $aTotals[$keyFirmTo];
				
				if(!empty($aTotals[$keyFirmTo])) {
					$aData['dolen_total'][$keyFirmTo] = $aTotals[$keyFirmTo].' лв.';
				} else {
					$aData['dolen_total'][$keyFirmTo] = '';
				}
				$oResponse->setDataAttributes('dolen_total',$keyFirmTo, array( 'style' => '	background: #cfedc6;
																							text-align:right;
																							border-bottom:1px solid #91D67C;
																							font-weight:bold;' ));
			}
			
			if( !empty( $the_total ) ) {
				$aData['dolen_total']['total'] = $the_total.' лв.';
				$oResponse->setDataAttributes('dolen_total','total', array( 'style' => 'background: #cfedc6;
																						text-align:right;
																						border-bottom:1px solid #91D67C;
																						font-weight:bold;' ));
			}
			
			$oResponse->setData( $aData );
			$oResponse->printResponse("Работна заплата - По фирми(Обобщена)","salary_firms_total");
		}
		
	}

?>