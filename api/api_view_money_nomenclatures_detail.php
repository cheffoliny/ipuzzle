<?php
	class ApiViewMoneyNomenclaturesDetail {
		public function load( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$oDBFirms 			= new DBFirms();
			$oSaldo 			= new DBSaldo();
			$oDBOffices 		= new DBOffices();
			$oDBPersonnel 		= new DBPersonnel();
			$oDBBankAccounts 	= new DBBankAccounts();
			$oExpenses 			= new DBNomenclaturesExpenses();
			$oEarnings 			= new DBNomenclaturesEarnings();
			$oDirections		= new DBDirections();
			$aData				= array();
			$aDirectins			= array();
			$start				= Params::get("start",		0);
			$nIDFirm 			= Params::get("nIDFirm",	0);
			$nIDOffice 			= Params::get("nIDOffice",	0);
			
			$nFirm	 			= Params::get("firm",		0);
			$nNomenclature		= Params::get("nomenclature", 0);
			$nOffice	 		= Params::get("office",		0);
			$nObject			= Params::get("object", 	0);
			$sDateFrom	 		= Params::get("date_from",	date("d.m.Y"));
			$sDateTo			= Params::get("date_to", 	date("d.m.Y"));
			$sMonth				= Params::get("month", 		0);  	// Y-m, при неизбран - 0!
			$sFrom				= date("d.m.Y");
			$sTo				= date("d.m.Y");

			$aFirms 			= $oDBFirms->getFirms4();
			
			//APILog::Log(0, $nIDFirm.' '.$nIDOffice);
			if (!empty($nIDFirm) || !empty($nIDOffice)){
				$aDirectins	= $oDirections->getDirectionsByFirmRegionGrouped($nIDFirm, $nIDOffice);
			} else {
				$aDirectins = $oDirections->getRegionDirectionsGrouped();
			}
			
			$oResponse->setFormElement("form1", "nIDFirm", array(), "");
			$oResponse->setFormElementChild("form1", "nIDFirm", array("value" => '0'), "- Изберете фирма -");
			
			$oResponse->setFormElement("form1", "nIDDirection", array(), "");
			$oResponse->setFormElementChild("form1", "nIDDirection", array("value" => '0'), "- Изберете направление -");	
			
			$oResponse->setFormElement("form1", "nIDBankAccount", array(), "");
			$oResponse->setFormElementChild("form1", "nIDBankAccount", array( "value" => 0 ), "- Изберете сметка -");			
			
			foreach ( $aFirms as $key => $value ) {
				$oResponse->setFormElementChild("form1", "nIDFirm", array("value" => $key), $value);
			}
			
			foreach ( $aDirectins as $value ) {
				$oResponse->setFormElementChild("form1", "nIDDirection", array("value" => $value['id']), $value['name']);
			}			
			
			if ( $start && !empty($nFirm) ) {
				$nIDFirm = $nFirm;
			}	

			if ( $start && !empty($nOffice) ) {
				$nIDOffice = $nOffice;
			}	

			if ( $start && !empty($sDateFrom) ) {
				$sFrom = $sDateFrom;
			}

			if ( $start && !empty($sDateFrom) ) {
				$sTo = $sDateTo;
			}

			if ( !$start ) {
				$sMonth = 0;
			}			

			if ( !empty($nIDFirm) ) {
				$oResponse->setFormElementAttribute("form1", "nIDFirm", "value", $nIDFirm);
				
				$aOffices = $oDBOffices->getOfficesByIDFirm( $nIDFirm );
				
				$oResponse->setFormElement("form1", "nIDOffice", array(), "");
				$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => '0' ), "- Изберете регион -");
				
				foreach( $aOffices as $key => $value ) {
					$oResponse->setFormElementChild("form1", "nIDOffice", array( "value" => $key ), $value);
				}
			} 
			
			if ( !empty($nIDOffice) ) {
				$oResponse->setFormElementAttribute("form1", "nIDOffice", "value", $nIDOffice);
			} else {
				$oResponse->setFormElement("form1", "nIDOffice", array(), "");
				$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => 0), "Първо изберете фирма");
			}
			
			if ( !empty($_SESSION['userdata']['id_person']) ) {
				$aBankAccounts = $oDBBankAccounts->getBankAccountsForPerson($_SESSION['userdata']['id_person']);
				
				foreach ( $aBankAccounts as $aBankAccount ) {
					
					$oResponse->setFormElementChild("form1", "nIDBankAccount", array("value" => $aBankAccount['id']), $aBankAccount['name']);
				}
				
				if ( !empty($aParams['nIDBankAccount']) ) {
					$oResponse->setFormElementAttribute( "form1", "nIDBankAccount", "value", $aParams['nIDBankAccount'] );
				}
				else if( isset( $aParams['nBankAccount'] ) && !empty( $aParams['nBankAccount'] ) )
				{
					$oResponse->setFormElementAttribute( "form1", "nIDBankAccount", "value", $aParams['nBankAccount'] );
				}
			}

			$oResponse->setFormElement("form1", "sFromDate", 	array("value" => $sFrom));
			$oResponse->setFormElement("form1", "sToDate", 		array("value" => $sTo));

			$oResponse->setFormElement("form1", "nIDNomenclature", array(), "");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => 0), "- Изберете номенклатура -");

			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => -1), ">>> ДДС <<<");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => -2), ">>> ТРАНСФЕР <<<");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => -3), ">>> Невъведени <<<");
		
			switch( $aParams['sOrderType'] ) {
				case "earning":
					$aData = $oEarnings->getNomenclaturesEarnings(1);
					
					foreach ( $aData as $key => $value ) {
						if ( $start && ($nNomenclature == "111".$key) ) {
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => "111".$key, "selected" => "selected"), $value);
						} else {
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => "111".$key ), $value);
						}
					}					
				break;
					
				case "expense":
					$aData = $oExpenses->getNomenclaturesExpenses(1);
					
					foreach ( $aData as $key => $value ) {
						if ( $start && ($nNomenclature == "222".$key) ) {
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "222".$key, "selected" => "selected"), $value);
						} else {						
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "222".$key ), $value);
						}
					}						
				break;
				
				default:
					$aData1 = $oEarnings->getNomenclaturesEarnings(1);
					$aData2 = $oExpenses->getNomenclaturesExpenses(1);
					
					foreach ( $aData1 as $key => $value ) {
						if ( $start && ($nNomenclature == "111".$key) ) {
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => "111".$key, "selected" => "selected"), $value);
						} else {
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => "111".$key ), $value);
						}
					}

					foreach ( $aData2 as $key => $value ) {
						if ( $start && ($nNomenclature == "222".$key) ) {
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "222".$key, "selected" => "selected"), $value);
						} else {						
							$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "222".$key ), $value);
						}
					}						
				break;	
			}
			
			if ( $start && ($nNomenclature < 1) ) {
				$oResponse->setFormElementAttribute("form1", "nIDNomenclature", "value", $nNomenclature);
			}
			
			$oResponse->setFormElement("form1", "sMonth", array(), "");
			
			for ( $i = -12; $i <= 11; $i++ ) {
				$sKey = 0;
				
				if ( $i == 0 ) {
					$oResponse->setFormElementChild( "form1", "sMonth", array("value" => 0 ), "- Изберете месец -");
				} 
				
				$sKey = date("Y-m", strtotime("{$i} months"));

				if ( $start && ($sKey == $sMonth) ) {
					$oResponse->setFormElementChild("form1", "sMonth", array("value" => $sKey, "selected" => "selected"), date("m Y", strtotime("{$i} months")));
				} else {
					$oResponse->setFormElementChild("form1", "sMonth", array("value" => $sKey), date("m Y", strtotime("{$i} months")));
				}
			}

			if ( !$start || ($sMonth == 0) ) {
				$oResponse->setFormElementAttribute("form1", "sMonth", "value", 0);
			}
			
			$oResponse->printResponse( "Парични Потоци - Подробна", "view_money_nomenclatures_detail" );
		}
		
		public function result( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$nIDSaldo	= Params::get("nIDSaldo", 	0);
			$nIDFirm	= Params::get("nIDFirm", 	0);
			$nIDOffice	= Params::get("nIDOffice", 	0);
			
			$oOrders 	= new DBOrders();
			
			$oOrders->getReportBySaldo($oResponse, $aParams);

			//$oOrders->getReportMoneyNomenclatures( $oResponse, $aParams );
			
			$oResponse->printResponse( "Парични Потоци - Подробна", "view_money_nomenclatures_detail" );
		}
		
		public function loadOffices( DBResponse $oResponse ) {
			$nFirm = Params::get("nIDFirm", 0);
			
			$oResponse->setFormElement("form1", "nIDOffice", array(), "");
			
			if ( !empty($nFirm) ) {
				$oDBOffices = new DBOffices();
				$aOffices = $oDBOffices->getOfficesByIDFirm($nFirm);
				
				$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => 0), "- Изберете регион -");
				
				foreach( $aOffices as $key => $value ) {
					$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => $key), $value);
				}
			} else {
				$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => 0), "Първо изберете фирма");
			}
			
			$oResponse->setFormElement("form1", "nIDPerson", array(), "");
			$oResponse->setFormElementChild("form1", "nIDPerson", array("value" => 0), "Първо изберете регион");


			APILog::Log(0, 'firma: '.$nFirm);
			$oResponse->setFormElement("form1", "nIDDirection", array(), "");
			$oResponse->setFormElementChild("form1", "nIDDirection", array("value" => '0'), "- Изберете направление -");
			$oDirections		= new DBDirections();
			if (!empty($nFirm)){
				$aDirectins	= $oDirections->getDirectionsByFirmRegionGrouped($nFirm, 0);
			} else {
				$aDirectins = $oDirections->getRegionDirectionsGrouped();
			}
			foreach ( $aDirectins as $value ) {
				$oResponse->setFormElementChild("form1", "nIDDirection", array("value" => $value['id']), $value['name']);
			}
			
			$oResponse->printResponse();
		}
		
		public function loadPersons( DBResponse $oResponse ) {
			$nOffice 		= Params::get("nIDOffice", 0);
			$oDBPersonnel 	= new DBPersonnel();
			$aResponsible	= array();
			
			$oResponse->setFormElement("form1", "nIDPerson", array(), "");
			
			if ( !empty($nOffice) ) {
				$aResponsible = $oDBPersonnel->getPersonnelsByIDOffice($nOffice);
				
				$oResponse->setFormElementChild("form1", "nIDPerson", array( "value" => 0), "- Изберете служител -");
				
				foreach ( $aResponsible as $key => $value ) {
					$oResponse->setFormElementChild("form1", "nIDPerson", array( "value" => $key ), $value);
				}
			} else {
				$oResponse->setFormElementChild("form1", "nIDPerson", array("value" => 0), "Първо изберете регион");
			}
			
			$oResponse->printResponse();
		}
		
		public function loadNomenclatures( DBResponse $oResponse ) {
			$aParams = Params::getAll();
			
			$oExpenses 	= new DBNomenclaturesExpenses();
			$oEarnings 	= new DBNomenclaturesEarnings();
			$aData		= array();
			
			$oResponse->setFormElement("form1", "nIDNomenclature", array(), "");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => 0), "- Изберете номенклатура -");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => -1), ">>> ДДС <<<");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => -2), ">>> ТРАНСФЕР <<<");
			$oResponse->setFormElementChild("form1", "nIDNomenclature", array("value" => -3), ">>> Невъведени <<<");
			
			switch( $aParams['sOrderType'] ) {
				case "earning":
					$aData = $oEarnings->getNomenclaturesEarnings(1);
					
					foreach ( $aData as $key => $value ) {
						$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "111".$key ), $value);
					}					
				break;
					
				case "expense":
					$aData = $oExpenses->getNomenclaturesExpenses(1);
					
					foreach ( $aData as $key => $value ) {
						$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "222".$key ), $value);
					}						
				break;
				
				default:
					$aData1 = $oEarnings->getNomenclaturesEarnings(1);
					$aData2 = $oExpenses->getNomenclaturesExpenses(1);
					
					foreach ( $aData1 as $key => $value ) {
						$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "111".$key ), $value);
					}

					foreach ( $aData2 as $key => $value ) {
						$oResponse->setFormElementChild("form1", "nIDNomenclature", array( "value" => "222".$key ), $value);
					}						
				break;				
			}

			$oResponse->printResponse();
		}
	}
?>