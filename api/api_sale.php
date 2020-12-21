<?php
	class ApiSale {
		
		public function isValidID( $nID ) {
			return preg_match("/^\d{13}$/", $nID);
		}	

		
		public function init( DBResponse $oResponse ) {
			$nID 			= Params::get("id", 0);		
			$nID			= strval($nID); 	
			$nIDObject		= Params::get("id_object", 0);	
			$nIDObject		= strval($nIDObject); 
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oSaleDoc		= new DBSalesDocs();
			$oSaleDocRows	= new DBSalesDocsRows();
			$oFirms 		= new DBFirms();
			$oBankAcc 		= new DBBankAccounts();
			$oPerson		= new DBPersonnel();
			$oClients		= new DBClients();
			$oCashier		= new DBCashiers();
			$oServices		= new DBObjectServices();
			$oStatuses		= new DBStatuses();
			
			$aSaleRows		= array();
			$aServices		= array();
			$aSaleDoc		= array();
			$aData			= array();
			$aFirms 		= array();	
			$aFirms2		= array();
			$aOrders		= array();	
			$aClients		= array();
			$aBankAcc2		= array();
			$aBankAcc		= array();
			$aRows			= array();
			$aCashier		= array();
			$aDeliverer		= array();
			$aStatuses		= array();
			$aAdvice		= array();
			$sBankOrder		= "";
			
			$oResponse->SetHiddenParam( "nID", $nID );	
			
			// Инициализираме масива сис сесията, ако не съществува
			if ( !isset($_SESSION['userdata']['access_right_levels']) ) {
				$_SESSION['userdata']['access_right_levels'] = array();
			}
			
			$aFirms 		= $oFirms->getFirmsAsClient();
			$aDeliverer		= $oFirms->getDelivererByPerson($nIDUser);
			
			if ( !empty($nID) ) {
				$aSaleDoc 	 = $oSaleDoc->getDoc( $nID );
				
				// ДДС
				$aDDS		= array();
				$aDDS 		= $oSaleDocRows->getDDSByDoc( $nID );				
	
				// Списък с описа
				$aSaleRows	= $oSaleDocRows->getRowsByDoc( $nID );	
				
				// Списък с ордерите - ко ШЪ гу прайм - не знъм
				$aOrders 	= $oSaleDocRows->getOrdersByDoc( $nID );
			
				if ( isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ) {
					$aTemp = array();
					$aTemp[0]['name'] 		= $aSaleDoc['deliverer_name'];
					$aTemp[0]['title'] 		= "---";
					$aTemp[0]['address'] 	= isset($aSaleDoc['deliverer_address']) 	? $aSaleDoc['deliverer_address'] : "";
					$aTemp[0]['idn'] 		= isset($aSaleDoc['deliverer_ein']) 		? $aSaleDoc['deliverer_ein'] 	 : "";
					$aTemp[0]['idn_dds'] 	= isset($aSaleDoc['deliverer_ein_dds']) 	? $aSaleDoc['deliverer_ein_dds'] : "";
					$aTemp[0]['jur_mol'] 	= isset($aSaleDoc['deliverer_mol']) 		? $aSaleDoc['deliverer_mol'] 	 : "";
					
					$aFirms = array_merge($aTemp, $aFirms);
				}
				
				$sDate						= isset($aSaleDoc['doc_date']) 				? $aSaleDoc['doc_date'] 			: date("Y-m-d");
				$nIDCreated 				= isset($aSaleDoc['created_user']) 			? $aSaleDoc['created_user']			: 0;
				$nIDUpdated 				= isset($aSaleDoc['updated_user']) 			? $aSaleDoc['updated_user']			: 0;
				$sDocType					= isset($aSaleDoc['doc_type']) 				? $aSaleDoc['doc_type'] 			: "faktura";
				$nAdvice					= isset($aSaleDoc['id_advice']) 			? $aSaleDoc['id_advice'] 			: 0;
				
				$aCPerson					= $oPerson->getByID($nIDCreated);
				$aUPerson					= $oPerson->getByID($nIDUpdated);
				
				if ( !empty($nAdvice) ) {
					$aAdvice = $oSaleDoc->getDoc($nAdvice);
				}
				//(($sDocType == "kreditno izvestie") || ($sDocType == "debitno izvestie"))	
				
				
				$aData['id']				= $nID;
				$aData['doc_num'] 			= isset($aSaleDoc['doc_num']) 				? zero_padding($aSaleDoc['doc_num'], 10) : "0000000000";
				$aData['doc_date'] 			= $sDate;
				$aData['doc_type'] 			= $sDocType;
				$aData['doc_status'] 		= isset($aSaleDoc['doc_status']) 			? $aSaleDoc['doc_status'] 			: "final";
				$aData['client_id']		 	= isset($aSaleDoc['id_client']) 			? $aSaleDoc['id_client'] 			: "";
				$aData['client_name'] 		= isset($aSaleDoc['client_name']) 			? $aSaleDoc['client_name'] 			: "";
				$aData['client_ein'] 		= isset($aSaleDoc['client_ein']) 			? $aSaleDoc['client_ein'] 			: "";
				$aData['client_ein_dds'] 	= isset($aSaleDoc['client_ein_dds']) 		? $aSaleDoc['client_ein_dds'] 		: "";
				$aData['client_address'] 	= isset($aSaleDoc['client_address']) 		? $aSaleDoc['client_address'] 		: "";
				$aData['client_mol'] 		= isset($aSaleDoc['client_mol']) 			? $aSaleDoc['client_mol'] 			: "";
				$aData['client_recipient'] 	= isset($aSaleDoc['client_recipient']) 		? $aSaleDoc['client_recipient'] 	: "";
				$aData['total_sum'] 		= isset($aSaleDoc['total_sum']) 			? $aSaleDoc['total_sum'] 			: 0;
				$aData['single_view_name']	= isset($aSaleDoc['single_view_name']) 		? $aSaleDoc['single_view_name'] 	: "услуга";
				$aData['paid_sum'] 			= isset($aSaleDoc['orders_sum']) 			? $aSaleDoc['orders_sum'] 			: 0;
				$aData['paid_type'] 		= isset($aSaleDoc['paid_type']) 			? $aSaleDoc['paid_type'] 			: "cash";
				$aData['id_bank_account'] 	= isset($aSaleDoc['id_bank_account']) 		? $aSaleDoc['id_bank_account'] 		: 0;
				$aData['view_type'] 		= isset($aSaleDoc['view_type']) 			? $aSaleDoc['view_type'] 			: "single";
				$aData['note'] 				= isset($aSaleDoc['note']) 					? $aSaleDoc['note'] 				: "";
				$aData['is_advice']			= isset($aSaleDoc['is_advice']) 			? $aSaleDoc['is_advice'] 			: 0;
				$aData['id_advice']			= isset($aSaleDoc['id_advice']) 			? $aSaleDoc['id_advice'] 			: 0;
				$aData['advice_num']		= isset($aAdvice['doc_num'])				? zero_padding($aAdvice['doc_num'], 10)	: "0000000000";
				$aData['advice_date']		= isset($aAdvice['doc_date'])				? $aAdvice['doc_date']				: "0000-00-00";
				$aData['doc_date_create']	= isset($aSaleDoc['created_time']) 			? $aSaleDoc['created_time']			: date("Y-m-d");
				$aData['created_time']		= isset($aSaleDoc['created_time']) 			? $aSaleDoc['created_time']			: date("Y-m-d");
				$aData['updated_time']		= isset($aSaleDoc['updated_time']) 			? $aSaleDoc['updated_time']			: date("Y-m-d");
				$aData['from_book']			= isset($aSaleDoc['is_book']) 	&& !empty($aSaleDoc['is_book'])		? true 		: false;
				$aData['created_user']		= isset($aCPerson['fname']) 	&& !empty($aCPerson['fname']) 		? $aCPerson['fname']." ".$aCPerson['lname'] : "";
				$aData['updated_user']		= isset($aUPerson['fname']) 	&& !empty($aUPerson['fname']) 		? $aUPerson['fname']." ".$aUPerson['lname'] : "";
								
				
				if ( !empty($aData['client_id']) ) {
					$aClients = $oClients->getByID($aData['client_id']);
					
					if ( isset($aClients['invoice_payment']) && !empty($aClients['invoice_payment']) ) {
						$aData['invoice_payment'] = $aClients['invoice_payment'];
					} else {
						$aData['invoice_payment'] = "cash";
					}
				} else {
					$aData['invoice_payment'] = "cash";
				}
				
				if ( isset($aDDS[0]['payed']) ) {
					$aData['dds_payed'] 	= $aDDS[0]['payed'] == 1 ? true : false;
				} else {
					$aData['dds_payed'] 	= false;
				}				
			} else {
				$aData['id'] 				= 0;
				$aData['doc_date'] 			= date("Y-m-d");
				$aData['doc_type'] 			= "faktura";
				$aData['doc_status']		= "final";
				$aData['paid_type'] 		= "cash";
				$aData['view_type']			= "single";
				$aData['dds_sum'] 			= 0;
				$aData['dds_payed'] 		= false;
				$aData['dds_for_payment'] 	= true;
				$aData['note']				= "";
				$aData['single_view_name']	= "услуга";
				$aData['locked']			= false;
				$aData['from_book']			= false;
				$aData['doc_date_create']	= date("Y-m-d");
				$aData['created_user']		= "";
				$aData['created_time']		= date("Y-m-d");	
				$aData['updated_user']		= "";
				$aData['updated_time']		= date("Y-m-d");	
				$aData['invoice_payment'] 	= "cash";
			}					
			
			$aData['user_id'] 			= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  	: 0;
			$aData['user_name'] 		= isset($_SESSION['userdata']['name']) 		? $_SESSION['userdata']['name']  		: "";
			$aData['user_office_id'] 	= isset($_SESSION['userdata']['id_office']) ? $_SESSION['userdata']['id_office']  	: 0;
			$aData['user_office_name'] 	= isset($_SESSION['userdata']['region']) 	? $_SESSION['userdata']['region']  		: "";
			$aData['user_uname'] 		= isset($_SESSION['userdata']['username']) 	? $_SESSION['userdata']['username']  	: "";
			$aData['user_row_limit'] 	= isset($_SESSION['userdata']['row_limit']) ? $_SESSION['userdata']['row_limit']  	: 0;
			$aData['id_schet_account'] 	= isset($_SESSION['userdata']['id_schet_account']) ? $_SESSION['userdata']['id_schet_account']  : 0;
			$aData['user_has_debug'] 	= isset($_SESSION['userdata']['has_debug']) ? $_SESSION['userdata']['has_debug']  	: 0;
			$aData['deliverer_ein']		= isset($aDeliverer['idn']) 				? $aDeliverer['idn'] 					: 0;
			$aData['deliverer_name']	= isset($aDeliverer['jur_name']) 			? $aDeliverer['jur_name'] 				: "";
			
			$aData['sale_doc_view'] 	= in_array('sale_doc_view', $_SESSION['userdata']['access_right_levels']) ? true : false;
			
			// При право за редакция - добавяме и право за преглед
			if ( in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
				$aData['sale_doc_view']	= true;
				$aData['sale_doc_edit'] = true;
			} else {
				$aData['sale_doc_edit'] = false;
			}
			
			// При пълно право за редакция - добавяме и право за преглед и редакция
			if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$aData['sale_doc_view']	= true;
				$aData['sale_doc_edit'] = true;
				$aData['sale_doc_grant'] = true;
				$aData['locked']		= false;
			} else {
				$aData['sale_doc_grant'] = false;
				$aData['locked']		= (isset($aSaleDoc['gen_pdf']) && $aSaleDoc['gen_pdf'] == 1) || (isset($aSaleDoc['exported']) && $aSaleDoc['exported'] == 1) ? true : false;
			}
	
			$aData['sale_doc_order_view'] = in_array('sale_doc_order_view', $_SESSION['userdata']['access_right_levels']) && $aData['sale_doc_view'] ? true : false;
			
			// При право за редакция - добавяме и право за преглед
			if ( in_array('sale_doc_order_edit', $_SESSION['userdata']['access_right_levels']) && $aData['sale_doc_view'] ) {
				$aData['sale_doc_order_view'] = true;
				$aData['sale_doc_order_edit'] = true;
			} else {
				$aData['sale_doc_order_edit'] = false;
			}
				
			if ( !$aData['sale_doc_view'] ) {
				//hrfgh
				
			}		

			$aCashier 	= $oCashier->getByIDPerson($nIDUser);
			
			if ( isset($_SESSION['userdata']['cbSmetkaOrder']) && !empty($_SESSION['userdata']['cbSmetkaOrder']) ) {
				if ( isset($aCashier['id']) && !empty($aCashier['id']) ) {
					$aCashier['id_cash_default'] = $_SESSION['userdata']['cbSmetkaOrder'];
					
					$oCashier->update($aCashier);
				}
				
				$aData['id_cash_default'] = $aCashier['id_cash_default'];
			} else {
				$aData['id_cash_default'] = isset($aCashier['id_cash_default']) && !empty($aCashier['id_cash_default']) ? $aCashier['id_cash_default'] : -1;
			}					

			$oResponse->SetHiddenParam("doc_status", $aData['doc_status']);
			$oResponse->SetHiddenParam("id_schet_account", $aData['id_schet_account']);
			
			// Общи данни
			$oResponse->SetFlexVar("aData", $aData);
			
			// Юридически лица
			$oResponse->SetFlexVar("arr_dostavchici", $aFirms);
			
			// Списък с банкови/касови сметки за ордерите
			$aBankAcc	= $oBankAcc->getAllAccounts( 2, 1, 1 );
			$oResponse->SetFlexVar("arr_smetki_orders", $aBankAcc);
			
			// Списък с банкови/касови сметки за фактурата
			$aBankAcc2	= $oBankAcc->getAllAccounts( 0, 0, 0 );
			$oResponse->SetFlexVar("arr_smetki", $aBankAcc2);			
			
			// Фирми и офиси
			$aFirms2 	= $oFirms->getFirmsByOffice();
			$oResponse->SetFlexVar("firm_regions", $aFirms2);	

			// Списък със услугите за комбото
			$aSrvDDS	= array();
			$aServices 	= $oServices->getFirmServices();
			$aSrvDDS 	= $oServices->getFirmDDSService();
			
			// Услуга ДДС
			foreach ( $aSrvDDS as $dds ) {
				$aServices[] = $dds;
			}

			$oResponse->SetFlexVar("arr_services", $aServices);		

			// Списък със статуси на обекти
			$tmpArray 		= array();
			$tmpArray[-1] 	= array("id" => "-1", "name" => "-= Платими =-");
			$tmpArray[0] 	= array("id" => "0", "name" => "-= Всички =-");
			
			$aStatuses 		= $oStatuses->getAllAssoc();
			$aStatuses		= array_merge( $tmpArray, $aStatuses );
			
			ksort($aStatuses);
			reset($aStatuses);
			
			//$oResponse->setAlert(ArrayToString($aStatuses));
			$oResponse->SetFlexVar("arrObjStatus", $aStatuses);							
			
			// Списък с описа
			foreach ( $aSaleRows as $key => $val ) {
				$aTemp = array();
				
				$aTemp['id'] 					= strval($val['id']);
				$aTemp['id_service'] 			= $val['id_service'];
				$aTemp['service_name'] 			= $val['service_name'];
				$aTemp['id_object'] 			= $val['id_object'];
				$aTemp['object_name'] 			= $val['obj_name'];				
				$aTemp['firm_region']['fcode'] 	= $val['id_firm'];
				$aTemp['firm_region']['firm'] 	= $val['firm_name'];
				$aTemp['firm_region']['rcode'] 	= $val['id_office'];
				$aTemp['firm_region']['region'] = $val['office_name'];
				$aTemp['id_duty'] 				= $val['id_duty'];
				$aTemp['month'] 				= $val['month'];
				$aTemp['single_price'] 			= floatval($val['single_price']);
				$aTemp['quantity'] 				= intval($val['quantity']);
				$aTemp['total_sum'] 			= floatval($val['total_sum']);
				$aTemp['paid_sum'] 				= floatval($val['paid_sum']);
				$aTemp['payed'] 				= $val['payed'] == 1 ? true : false;
				$aTemp['type'] 					= $val['type'];				
				
				$aRows[] 						= $aTemp;
	 		}
	 		
			$oResponse->SetFlexVar("arr_rows", $aRows);
			
			// Списък с ордерите
			$oResponse->SetFlexVar("arr_orders", $aOrders);			
			
			// Стойности по подразбиране
			$oResponse->SetFlexControl("cbDostavchik");
			//$oResponse->SetFlexControl("cbSmetkaOrder");
			
			if ( isset($nID) && !empty($nID) && isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ) {
				$oResponse->SetFlexControlDefaultValue("cbDostavchik", "title", "---");
			} else {
				$oResponse->SetFlexControlDefaultValue("cbDostavchik", "title", "ИНФРА ЕООД");
			}	

			// test

			//$oResponse->setAlert(ArrayToString($aTotals));
			
			// !test
			
			
			if ( empty($nID) && !empty($nIDObject) ) {
				Params::set("object_id", $nIDObject);
				Params::set("month_duty", date("Y-m-d"));
				Params::set("doc_type", "faktura"); 
				Params::set("arr_rows", array());
				Params::set("deliverer_name", 	isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ? $aSaleDoc['deliverer_name'] : "Инфра ЕООД");
				Params::set("deliverer_ein", 	isset($aSaleDoc['deliverer_ein']) 	? $aSaleDoc['deliverer_ein'] : "111111111");
				
				$this->getDutyObject($oResponse);
			} else {
				$oResponse->printResponse();
			}
		}		
		
		private function getConcession($nMonths) {
			$oConcession	= new DBConcession();
			$aConcession	= array();	
			$aConMonths		= array();	
			$nIDConcession	= 0;
			$nCurrent		= 0;
			
			$aConcession = $oConcession->getAll();	
				
			foreach ( $aConcession as $val ) {
				$month = isset($val['months_count']) ? $val['months_count'] : 0;
				
				if ( ($nMonths >= $month) && ($nCurrent < $month) ) {
					$nCurrent  		= $month;
					$nIDConcession	= isset($val['id']) ? $val['id'] : 0;
				}
			}

			return $nIDConcession;
		}
		
		// remote method	
		public function getDuty(DBResponse $oResponse) {
			$nIDClient 		= Params::get("client_id", 0);
			$sClientEIN		= Params::get("client_ein", "");
			$sDelivererEIN	= Params::get("deliverer_ein", "");
			$sDelivererName	= Params::get("deliverer_name", "");
			$sMonthDuty 	= Params::get("month_duty", "0000-00-00");
			
			$aParams		= Params::getAll();
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oFirms 		= new DBFirms();
			$oObjects		= new DBObjects();
			$oMonths		= new DBObjectServices();
			$oSingles		= new DBObjectsSingles();
			$oClients		= new DBClients();
			$oConcession	= new DBConcession();

			$aClients		= array();
			$aObjects		= array();
			$aServices		= array();
			$aJurNames		= array();
			$aJur			= array();	
			$aConcession	= array();	
			$aConMonths		= array();
			$nIDConcession	= 0;
			$is_con 		= false;
			
			$aClients 		= $oClients->getByID($nIDClient);
			$aObjects 		= $oObjects->getObjectsByClient($nIDClient);
			$aFirms 		= $oFirms->getFirmsAsClient();		
		
			foreach ( $aObjects as $val ) {		
				$nIDObject 	= $val['id'];
				$aTmp 		= array();
				$aTmp2 		= array();
				
				$aTmp		= $oMonths->getDutyByObject($nIDObject, $sMonthDuty);
				$aTmp2		= $oSingles->getDutyByObject($nIDObject, $sMonthDuty);
				
				foreach ( $aTmp as $srv ) {
					$aJur		= array();
					$aJur 		= $oMonths->getJur($srv['id_duty']);
					$sJurName 	= $aJur['jur_name'];	

					$aJurNames[$sJurName] = $sJurName;	
					
					if ( $sJurName == $sDelivererName ) {
						$aServices[] = $srv;	
						
						// Отстъпки
						$time = strtotime($srv['month']);
						
						$dNow = mktime( 0, 0, 0, date("m"), 1, date("Y") );
						$dCon = mktime( 0, 0, 0, date("m", $time), 1, date("Y", $time) );
						
						if ( $dCon >= $dNow ) {
							$aConcession[$srv['id_duty']][] = $srv['month'];
						}
					}				
				}
				
				unset($srv);				
				
				foreach ( $aTmp2 as $srv ) {
					$aJur		= array();
					$aJur 		= $oSingles->getJur($srv['id_duty']);
					$sJurName 	= $aJur['jur_name'];	

					$aJurNames[$sJurName] = $sJurName;	
					
					if ( $sJurName == $sDelivererName ) {
						$aServices[] = $srv;	
					}			
				}				
			}
			
			foreach ( $aConcession as $key => $val ) {
				$cnt 			= count($val);
				$nIDConcession	= 0;
				$aData			= array();
				$aTmp			= array();
					
				$nIDConcession = $this->getConcession($cnt);
				
				if ( $nIDConcession ) {
					$aData = $oMonths->getRecord($key);
					$aGrrr = $oConcession->getRecord($nIDConcession);
					$is_con = true;
						
					$aTmp['id']						= 0;
					$aTmp['id_duty']				= $key;
					$aTmp['id_object'] 				= $aData['id_object'];
					$aTmp['firm_region']['rcode']	= $aData['id_office'];
					$aTmp['firm_region']['region']	= 0;
					$aTmp['firm_region']['fcode']	= 0;
					$aTmp['firm_region']['firm']	= "";							
					$aTmp['id_service'] 			= $aGrrr['id_service'];
					$aTmp['month'] 					= date("Y-m")."-01";
					$aTmp['service_name'] 			= "Отстъпка: ".$aData['service_name'];
					$aTmp['object_name'] 			= $aGrrr['name'];
					$aTmp['single_price'] 			= floatval((($aData['total_sum'] / 1.2) * -1 * $cnt * $aGrrr['percent']) / 100);
					$aTmp['quantity'] 				= 1;
					$aTmp['total_sum'] 				= floatval((($aData['total_sum'] / 1.2) * -1 * $cnt * $aGrrr['percent']) / 100);
					$aTmp['payed']					= floatval(0);
					$aTmp['type']					= "free";
					$aTmp['for_payment']			= true;

					$aServices[] = $aTmp;				
				}
			}
							
			if ( $is_con ) {
				$oResponse->setAlert("\nИма предложени отстъпки!!!");
			}			

			//$oResponse->setAlert(ArrayToString($aServices));
			
			foreach ( $aFirms as &$firms ) {
				if ( isset($firms['name']) && in_array($firms['name'], $aJurNames) ) {
					$firms['haveDuty'] = true;
				} else {
					$firms['haveDuty'] = false;
				}
			}
			
			// Юридически лица
			$oResponse->SetFlexVar("arr_dostavchici", $aFirms);
			
			$oResponse->SetFlexControl("cbPoluchatel");
			$oResponse->SetFlexControlDefaultValue("cbPoluchatel", "title", $sDelivererName);
			
			// Зареждане на "забележка" ако има такава
			$oResponse->SetFlexControl("note");
			$oResponse->SetFlexControlAttr("note", "text", isset($aClients['note']) ? $aClients['note'] : "");		
			
			// Зареждане на "Получател" ако има такъв
			$oResponse->SetFlexControl("client_recipient");
			$oResponse->SetFlexControlAttr("client_recipient", "text", isset($aClients['invoice_recipient']) ? $aClients['invoice_recipient'] : "");		
			
			// Зареждане на "Предпочитан начин на плащане"
			$type = isset($aClients['invoice_payment']) && !empty($aClients['invoice_payment']) ? $aClients['invoice_payment'] : "cash";
			$sMessage = "Клиента предпочита: ";
			
			switch ($type) {
				case "cash": 	
					$sMessage .= "Фактура в брой";
					
					$oResponse->SetFlexControl("faktura");
					$oResponse->SetFlexControlAttr("faktura", "selected", "true");							
					
					$oResponse->SetFlexControl("cash");
					$oResponse->SetFlexControlAttr("cash", "selected", "true");	
				break;
				
				case "bank": 	
					$sMessage .= "Фактура по банка";
					
					$oResponse->SetFlexControl("faktura");
					$oResponse->SetFlexControlAttr("faktura", "selected", "true");

					$oResponse->SetFlexControl("bank");
					$oResponse->SetFlexControlAttr("bank", "selected", "true");										
				break;
				
				case "receipt": 
					$sMessage .= "Квитанция";
					
					$oResponse->SetFlexControl("kvitanciq");
					$oResponse->SetFlexControlAttr("kvitanciq", "selected", "true");
					
					$oResponse->SetFlexControl("cash");
					$oResponse->SetFlexControlAttr("cash", "selected", "true");					
				break;
				
				default:		
					$sMessage .= "Фактура в брой";
					
					$oResponse->SetFlexControl("faktura");
					$oResponse->SetFlexControlAttr("faktura", "selected", "true");					
					
					$oResponse->SetFlexControl("cash");
					$oResponse->SetFlexControlAttr("cash", "selected", "true");						
				break;
			}
			
			$oResponse->SetFlexControl("lblInvoicePayment");
			$oResponse->SetFlexControlAttr("lblInvoicePayment", "text", $sMessage);		
			
			
			$oResponse->SetFlexVar("arr_rows", $aServices);		
			
			$oResponse->printResponse();
		}
		
		// remote method	
		public function suggestClient(DBResponse $oResponse) {
	  		global $db_sod, $db_name_sod;
	  		
			$field 			= Params::get("field", "");
			$info 			= Params::get("info", "");
			
			$info = str_replace('"','',$info);

			if (($field == 'name') || ($field == 'address') || ($field == 'invoice_mol'))
				$info = str_replace(' ','%',trim($info));

	  		$arr_client 	= array();
	  		
	  		$sQuery = "
	  			SELECT 
	  				id,
	  				name,
	  				address,
					phone,
	  				invoice_ein,
	  				invoice_ein_dds,
	  				invoice_mol,
					invoice_layout
	  			FROM {$db_name_sod}.clients
	  			WHERE UPPER({$field}) LIKE UPPER('%$info%')
	  			LIMIT 10
	  		";
	  		
	  		$arr_client = $db_sod->getArray( $sQuery );
	  		
	  		$oResponse->SetFlexVar("arr_clients", $arr_client);
	
	  		$oResponse->printResponse();
		}	

		// remote method	
		public function suggestObject(DBResponse $oResponse) {
	  		global $db_sod, $db_name_sod;
	  		
			$field 			= Params::get("field", "");
			$info 			= Params::get("info", "");
			$nIDStatus		= Params::get("status", 0);
	  		
	  		$arr_object 	= array();
	  		$where			= "";
	  		
			switch ($field){
				case 'num':
					$field = 'o.num';
					break;
				case 'object_name':
					$field = 'o.name';
					break;
				case 'mol':
					$field = 'f.name';
					break;
				case 'address':
					$field = 'o.address';
					$info = str_replace(' ','%',trim($info));
					break;
			}
			
			if ( $nIDStatus > 0 ) {
				$where = " AND o.id_status = {$nIDStatus} ";
			} elseif ( $nIDStatus < 0 ) {
				$where = " AND s.payable = 1 ";
			}

	  		$sQuery = "
	  			SELECT 
	  				o.id as id,
	  				o.num as num,
	  				o.name as object_name,
	  				o.invoice_name as invoice_name,
					o.address as address,
					f.name as mol,
					c.id as client_id,
					c.name as client_name,
					s.name as status_name,
					c.address as client_address,
					c.invoice_ein as client_ein,
					c.invoice_ein_dds as client_ein_dds,
					c.invoice_mol as client_mol
	  			FROM {$db_name_sod}.objects o
	  			LEFT JOIN {$db_name_sod}.faces f ON (f.id = o.id_face AND f.id IS NOT NULL)
	  			LEFT JOIN {$db_name_sod}.clients c ON c.id = o.id_client
	  			LEFT JOIN {$db_name_sod}.statuses s ON (s.id = o.id_status AND s.to_arc = 0)
	  			WHERE 1 {$where}
	  		";
	  		
		  	if ( $field == "o.num" ) {
		  		$sQuery .= " AND UPPER({$field}) LIKE UPPER('$info%') ";
		  	} else {
		  		$sQuery .= " AND UPPER({$field}) LIKE UPPER('%$info%') ";
		  	}
	  		
	  		if ( $field == "o.num" ) {
	  			$sQuery .= " ORDER BY o.num ";
	  		}
	  		
	  		$sQuery .= " LIMIT 10 ";
	  		
	  		$arr_object = $db_sod->getArray( $sQuery );
	  		
	  		$oResponse->SetFlexVar("arr_object", $arr_object);
	
	  		$oResponse->printResponse();			
		}
		
		// remote method	
		public function suggestDocument(DBResponse $oResponse) {
	  		global $db_finance, $db_name_finance;
	  		
			$field 			= Params::get("field", "");
			$info 			= Params::get("info", "");
			$aTables		= array();
			$arr_document 	= array();
			
			switch ($field){
				case 'doc_num':
					$field = 'doc_num';
					break;
				case 'name':
					$field = 'client_name';
					break;					
				case 'ein':
					$field = 'client_ein';
					break;
			}

			// Списък с наличните таблици
			$aTables 		= SQL_get_tables( $db_finance, "sales_docs_20", "____" );	

	  		reset($aTables);
	  		ksort($aTables);
	  		reset($aTables);
	  		
	  		$sQuery 	= "";
	  		$tblName	= "";
	  		
	  		$br = 0;

	  		foreach ( $aTables as $key => $val ) {
	  			$br++;
	  			
	  			if ( $br > 3 ) {
	  				continue;
	  			}
	  			
	  			if ( (count($aTables) > $br) && ($br < 3) ) {
	  				$tblName = PREFIX_SALES_DOCS.substr($val, -6);
	  				
			  		$sQuery .= "
			  			(
				  			SELECT 
				  				id,
				  				doc_num,
								client_name as name,
								client_ein as ein					
				  			FROM {$db_name_finance}.{$tblName}
				  			WHERE UPPER({$field}) LIKE UPPER('%$info%')
				  				AND total_sum != orders_sum
				  				AND to_arc = 0
				  				AND doc_status != 'canceled'
			  			) UNION 
			  		";	  				
	  			}
	  			
	  			$tblName = PREFIX_SALES_DOCS.substr($val, -6);
	  		}
	  		
	  		$sQuery .= "
	  			(
		  			SELECT 
		  				id,
		  				doc_num,
						client_name as name,
						client_ein as ein					
		  			FROM {$db_name_finance}.{$tblName}
		  			WHERE UPPER({$field}) LIKE UPPER('%$info%')
		  				AND total_sum != orders_sum
		  				AND to_arc = 0
		  				AND doc_status != 'canceled'
		  		)
		  		
	  			LIMIT 10
	  		";
			//$oResponse->setAlert(ArrayToString($sQuery));
	  		$arr_document = $db_finance->getArray( $sQuery );

	  		$oResponse->SetFlexVar("arr_document", $arr_document);
	
	  		$oResponse->printResponse();			
		}
		
		/**
		 * Ппроверява за валидност на ЕГН/EIN по зададен низ
		 * 
		 * @name trimEin()
		 * @author Павел Петров
		 *
		 * @param string $ein
		 * @return bool
		 */
		public function trimEin( $ein ) {
			$oValidate 	= new Validate();
			$sEin 		= "";
			$aMatrix	= array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
			
			for ( $i = 0; $i < strlen($ein); $i++ ) {
				if ( in_array($ein[$i], $aMatrix) ) {
					$sEin .= $ein[$i];
				}
			}
		 			
			// Чужденци
			if ( $sEin == "999999999999999" ) {
				return true;
			}
						
		 	if ( (strlen($sEin) == 9) || (strlen($sEin) == 13) ) {
		 		if ( strlen($sEin) == 13 ) {
		 			$base 	= substr($sEin, 0, 9);
		 			$ext	= substr($sEin, -4);
		 					
					$oValidate->variable = $base;
					$oValidate->checkEIN();	 					
		 		} else {
					$oValidate->variable = $sEin;
					$oValidate->checkEIN();
				}
						
				if ( $oValidate->result ) {
					return $sEin;
				}
			} elseif ( strlen($sEin) == 10 ) {
				$oValidate->checkEGN();
					
				if ( $oValidate->result ) {
					return $sEin;
				}
			}
	
			return false;
		}			
		
		// remote method	
		public function save( DBResponse $oResponse ) {
			global $db_sod, $db_system, $db_finance, $db_name_system, $db_name_sod, $db_name_finance;
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oSaleDoc		= new DBSalesDocs();
			$oSaleDocRows	= new DBSalesDocsRows();
			$oFirms 		= new DBFirms();
			$oMonths		= new DBObjectServices();
			$oSingles		= new DBObjectsSingles();
			$oClients		= new DBClients();	
			$oObject		= new DBObjects();
			$oSync			= new DBSyncMoney();
			$oNomen			= new DBNomenclaturesEarnings();
			$oService		= new DBNomenclaturesServices();
			$oOffices		= new DBOffices();
			$oBooks			= new DBBooks();
			$oLog			= new DBLogErrors();
			
			$aSaleDocRows	= array();
			$aSaleDoc		= array();
			$aData			= array();
			$aFirms 		= array();
			$aOrders		= array();
			$aMonths		= array();
			$aSingles		= array();
			$aObject		= array();
			$aBooks			= array();
			$sErrMessage	= "";
			$test			= false;
			$lock			= false;
			$is_book		= false;
			
			$aParams		= Params::getAll();
			$nID 			= isset($aParams['hiddenParams']->nID) 				? $aParams['hiddenParams']->nID 			 : 0;	
			$nIDSchetAcc	= isset($aParams['hiddenParams']->id_schet_account) ? $aParams['hiddenParams']->id_schet_account : 0;	

			Params::set("id", $nID);
			
		// Права за достъп
			$edit_right 	= in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;
			$grant_right 	= false;
	
			if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$edit_right		= true;
				$grant_right 	= true;
			}		
			
			if ( !empty($nID) ) {
				$aSaleDoc 	= $oSaleDoc->getDoc($nID);
				$lock 		= isset($aSaleDoc['gen_pdf']) && $aSaleDoc['gen_pdf'] == 1 ? true 	: false;
				$nIDorder	= isset($aSaleDoc['last_order_id']) ? $aSaleDoc['last_order_id'] 	: 0;			
			
				if ( $lock ) {
					throw new Exception("Документа вече е бил разпечатан или експортиран!");
				}
				
//				if ( $nIDorder ) {
//					throw new Exception("Има текущи плащания към този документ!");
//				}			
			}
			
			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа е анулиран!", DBAPI_ERR_INVALID_PARAM);
			}	

			$cl_ein 	= isset($aParams['client_ein']) && $this->trimEin($aParams['client_ein']) ? $aParams['client_ein'] : "";	
			$cl_name	= isset($aParams['client_name']) 		? $aParams['client_name'] 			: "";
			$cl_addr	= isset($aParams['client_address']) 	? $aParams['client_address'] 		: "";
			$nIDClient	= 0;

			if ( !empty($cl_ein) && !empty($cl_name) && !empty($cl_addr) ) {
				if ( $cl_ein != "999999999999999" ) {
					$aClients	= $oClients->getClientByEIN($cl_ein);
				} else {
					$aClients	= $oClients->getClientByName($cl_name);
					//$aClients 	= array();
				}
	
				if ( empty($aClients) ) {
					$aClientData 							= array();
					$aClientData['id'] 						= 0;
					$aClientData['name'] 					= $cl_name;
					$aClientData['address'] 				= $cl_addr;
					$aClientData['email'] 					= "";
					$aClientData['phone'] 					= "";
					$aClientData['note'] 					= "";
					$aClientData['invoice_address'] 		= $cl_addr;
					$aClientData['invoice_ein'] 			= $cl_ein;
					$aClientData['invoice_ein_dds'] 		= isset($aParams['client_ein_dds']) 	? $aParams['client_ein_dds'] 	: "";
					$aClientData['invoice_mol'] 			= isset($aParams['client_mol']) 		? $aParams['client_mol'] 		: "";
					$aClientData['invoice_recipient'] 		= isset($aParams['client_mol']) 		? $aParams['client_mol'] 		: "";
					$aClientData['invoice_bring_to_object'] = 0;
					$aClientData['invoice_layout'] 			= "total";
					$aClientData['invoice_payment'] 		= isset($aParams['paid_type']) 			? $aParams['paid_type'] 		: "";
					$aClientData['invoice_email'] 			= "";
						
					$oClients->update($aClientData);
					
					$nIDClient = isset($aClientData['id']) ? $aClientData['id'] : 0;
				} else {
					$aClientData 							= array();
					$aClientData['id'] 						= $aClients['id'];
					$aClientData['name'] 					= $cl_name;
					$aClientData['invoice_address'] 		= $cl_addr;
					$aClientData['invoice_mol'] 			= isset($aParams['client_mol']) 		? $aParams['client_mol'] 		: "";
					$aClientData['invoice_recipient'] 		= isset($aParams['client_mol']) 		? $aParams['client_mol'] 		: "";
					$aClientData['invoice_payment'] 		= isset($aParams['paid_type']) 			? $aParams['paid_type'] 		: "";
							
					$oClients->update($aClientData);

					$nIDClient = isset($aClientData['id']) ? $aClientData['id'] : 0;				
				}
				
				$aParams['client_id'] 	= $nIDClient;
			} else {
				$aParams['client_id'] 	= 0;
				$nIDClient				= 0;
			}
						
			if ( ($aParams['doc_type'] == "faktura") ) {
				if ( empty($aParams['client_name']) || empty($aParams['client_ein']) || empty($aParams['client_id']) ) {
					throw new Exception("Въведете коректен клиент!");
				}	
			} else {
				if ( empty($aParams['client_id']) ) {
					// TODO: тука да променя!!!
				}
			}

			$total_sum = 0;
			
			foreach ( $aParams['arr_rows'] as $obj ) {							
				if ( !is_object($obj) && !empty($obj['type']) ) {			
					$total_sum += $obj['total_sum'];
				} elseif (is_object($obj) && isset($obj->type) ) {
					$total_sum += $obj->total_sum;
				}
			}			

			if ( empty($total_sum) ) {
				throw new Exception("Фактура с нулева стойност не може да бъде издадена!");
			}
			
			foreach ( $aParams['arr_rows'] as $key => $obj ) {
				$num 		= $key + 1;
				$nIDService	= 0;
				
				if ( !is_object($obj) && isset($obj['firm_region']['rcode']) && ( isset($obj['for_payment']) && !empty($obj['for_payment']) ) ) {
					$service_name 	= $obj['service_name'];	
					$nIDService		= $obj['id_service'];
										
					if ( $obj['total_sum'] > 200000 ) {
						$aDataLog 				= array();
						$aDataLog['params'] 	= serialize($aParams);
						$aDataLog['response']	= serialize($oResponse);
						$aDataLog['address']	= $_SERVER['SERVER_ADDR'];
						
						$oLog->update($aDataLog);
						
						throw new Exception("\nПроблем с приемането на данните!\nСвържете се с администратор!!!\n\n", DBAPI_ERR_FAILED_TRANS);
					}
					
					if ( empty($obj['firm_region']['rcode']) ) {
						throw new Exception("Услуга на ред {$num} - {$service_name}\nв ДЕТАЙЛЕН изглед \n\nсе нуждае от уточняване на региона!!! ", DBAPI_ERR_INVALID_PARAM);
					}				
				} elseif ( is_object($obj) && isset($obj->id_office) && ( isset($obj->for_payment) && !empty($obj->for_payment) ) ) {
					$service_name 	= $obj->service_name;
					$nIDService		= $obj->id_service;
					
					if ( $obj->total_sum > 200000 ) {
						$aDataLog 				= array();
						$aDataLog['params'] 	= serialize($aParams);
						$aDataLog['response']	= serialize($oResponse);
						$aDataLog['address']	= $_SERVER['SERVER_ADDR'];
						
						$oLog->update($aDataLog);
												
						throw new Exception("\nПроблем с приемането на данните!\nСвържете се с администратор!!!\n\n", DBAPI_ERR_FAILED_TRANS);
					}					
					
					if ( empty($obj->id_office) ) {
						throw new Exception("Услуга на ред {$num} - {$service_name}\nв ДЕТАЙЛЕН изглед \n\nсе нуждае от уточняване на региона!!! ", DBAPI_ERR_INVALID_PARAM);
					}							
				}	
				
				if ( $oService->checkForTransfer($nIDService) && ($aParams['doc_type'] != "oprostena") ) {
					throw new Exception("\nДокумент с НАПРАВЛЕНИЕ по ТРАНСФЕР\nможе да бъде само квитанция!!!\n\n", DBAPI_ERR_FAILED_TRANS);
				}						
			}

			$jur_name	= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
			
			$db_finance->StartTrans();
			$db_system->StartTrans();	
			$db_sod->StartTrans();	
			
			try {			
				if ( empty($nID) ) {
					
					// Пореден номер
					if ( $aParams['doc_type'] == "faktura" ) {
						
						if ( isset($aParams['from_book']) && !empty($aParams['from_book']) ) {
							if ( isset($aParams['doc_num']) && !empty($aParams['doc_num']) ) {
								$aBooks			= $oBooks->getRowByNum($aParams['doc_num']);
								
								if ( isset($aBooks['id']) && !empty($aBooks['id']) ) {
									// Имаме регистриран кочан с такъв диапазон
									if ( isset($aBooks['is_use']) && empty($aBooks['is_use']) ) {
										// Номера е свободен!!!
										$nLastOrder = $aParams['doc_num'];
										$is_book	= true;
										
										$aTemp 			= array();
										$aTemp['id'] 	= $aBooks['id'];
										$aTemp['is_use'] 	= 1;
										
										$oBooks->update($aTemp);
									} else {
										throw new Exception("Въведения номер за фактура вече е използван!!!", DBAPI_ERR_INVALID_PARAM);
									}
								} else {
									throw new Exception("Няма регистриран кочан с такъв диапазон!!!", DBAPI_ERR_INVALID_PARAM);
								}
								
							} else {
								throw new Exception("Въведете номер на фактура от кочан!", DBAPI_ERR_INVALID_PARAM);
							}
						} else {
							if ( isset($aParams['cbPoluchatel']['name']) && !empty($aParams['cbPoluchatel']['name']) ) {
								$oRes 			= $db_sod->Execute("SELECT last_num_sale_doc FROM {$db_name_sod}.firms WHERE jur_name = '{$jur_name}' FOR UPDATE");
								$nLastOrder 	= !empty($oRes->fields['last_num_sale_doc']) ? $oRes->fields['last_num_sale_doc'] + 1 : 0;						
							}
						}
					} else {
						$oRes 					= $db_system->Execute("SELECT last_num_receipt FROM {$db_name_system}.system FOR UPDATE");
						$nLastOrder 			= !empty($oRes->fields['last_num_receipt']) ? $oRes->fields['last_num_receipt'] + 1 : 0;
					}
					
					$aData['id'] 				= $nID;
					$aData['doc_num'] 			= $nLastOrder;
					$aData['doc_date'] 			= isset($aParams['doc_date']) 				? $aParams['doc_date'] 					: time();
					$aData['doc_type'] 			= isset($aParams['doc_type']) 				? $aParams['doc_type'] 					: "faktura";
					$aData['doc_status'] 		= "final";
					$aData['id_credit_master'] 	= 0;
					$aData['id_client'] 		= isset($aParams['client_id']) 				? $aParams['client_id'] 				: 0;
					$aData['id_schet'] 			= 0;

					$aData['client_name'] 		= isset($aParams['client_name']) 			? $aParams['client_name'] 				: "";
					$aData['client_ein'] 		= isset($aParams['client_ein']) 			? $aParams['client_ein'] 				: "";
					$aData['client_ein_dds'] 	= isset($aParams['client_ein_dds']) 		? $aParams['client_ein_dds'] 			: "";
					$aData['client_address'] 	= isset($aParams['client_address']) 		? $aParams['client_address'] 			: "";
					$aData['client_mol'] 		= isset($aParams['client_mol']) 			? $aParams['client_mol'] 				: "";
					$aData['client_recipient'] 	= isset($aParams['client_recipient']) 		&& !empty($aParams['client_recipient']) ? $aParams['client_recipient'] : $aData['client_mol'];
					
					$aData['deliverer_name'] 	= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
					$aData['deliverer_ein'] 	= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
					$aData['deliverer_ein_dds'] = isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
					$aData['deliverer_address'] = isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
					$aData['deliverer_mol'] 	= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
					
					$aData['total_sum'] 		= 0;
					$aData['orders_sum'] 		= 0;
					$aData['last_order_id'] 	= 0;
					$aData['last_order_time'] 	= "0000-00-00 00:00:00";
					$aData['paid_type'] 		= isset($aParams['paid_type']) 				? $aParams['paid_type'] 				: "cash";
					$aData['view_type'] 		= isset($aParams['cbView']) 				? $aParams['cbView'] 					: "detail";
					$aData['id_bank_account'] 	= isset($aParams['cbSmetka']) 				? $aParams['cbSmetka'] 					: 0;
					$aData['is_auto'] 			= 0;
					$aData['is_book'] 			= isset($aParams['from_book']) 				? $aParams['from_book'] 				: 0;
					$aData['gen_pdf'] 			= 0;
					$aData['note']				= isset($aParams['note'])					? $aParams['note']						: "";					
					$aData['created_user'] 		= $nIDUser;
					$aData['created_time'] 		= time();
					$aData['updated_user'] 		= $nIDUser;
					$aData['updated_time'] 		= time();			
	
					if ( $aData['view_type'] == "single" ) {
						if ( isset($aParams['single_row']->service_name) && !empty($aParams['single_row']->service_name) ) {
							$aData['single_view_name']	= $aParams['single_row']->service_name;
						}
					} else {
						$aData['single_view_name']		= "yслуга";
					}
										
					$oSaleDoc->update($aData);					
				
					$nID 						= $aData['id'];	
					
					if ( isset($aParams['client_id']) && !empty($aParams['client_id'] ) ) {
						$aData 						= array();
						$aData['id'] 				= $aParams['client_id'];
						$aData['note']				= $aParams['note'];
						$aData['invoice_recipient'] = isset($aParams['client_recipient']) && !empty($aParams['client_recipient']) 	? $aParams['client_recipient'] 	: $aParams['client_mol'];
						
						if ( isset($aParams['doc_type']) ) {
							if ( ($aParams['doc_type'] == "kvitanciq") || ($aParams['doc_type'] == "oprostena") ) {
								$aData['invoice_payment'] = "receipt";
							} else {
								if ( $aParams['paid_type'] == "cash" ) {
									$aData['invoice_payment'] = "cash";
								} else {
									$aData['invoice_payment'] = "bank";
								}
							} 
						} else {
							$aData['invoice_payment'] = "cash";
						}
						
						$oClients->update($aData);
					}
					
					if ( $aParams['doc_type'] == "faktura" ) {
						// тук омазваме!!!
						if ( !$is_book ) {
							if ( isset($aParams['cbPoluchatel']['name']) && !empty($aParams['cbPoluchatel']['name']) ) {
								$db_sod->Execute("UPDATE {$db_name_sod}.firms SET last_num_sale_doc = {$nLastOrder} WHERE LOWER(jur_name) = LOWER('{$jur_name}')");
							}
						}
					} else {
						$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_receipt = {$nLastOrder}");
					}
										
					$oResponse->SetHiddenParam( "nID", $nID );
					Params::set("id", $nID);
					$single_price	= array();
					$month_price	= array();
					$s_normal		= false;
					$s_dds			= false;
					$aSchetRows		= array();
	
					foreach ( $aParams['arr_rows'] as $key => $obj ) {							
						if ( !is_object($obj) && !empty($obj['total_sum']) && $obj['for_payment'] ) {
							$is_dds 	= 0;
							$nIDSchet	= 0;
							
							if ( !empty($obj['id_object']) && !empty($aParams['client_id']) ) {
								$test = false;
								$test = $oClients->isObjectAttachedToClient($aParams['client_id'], $obj['id_object']);
								
								if ( !$test ) {
									$aObject = $oObject->getByID($obj['id_object']);
									$sObjectName = isset($aObject['name']) ? "[".$aObject['num']."] ".$aObject['name'] : "";
									
									$oResponse->setAlert("Внимание!!!\n\nОбекта {$sObjectName} беше привързан към избрания клиент\n\n!!!");
									
									$oClients->detachObjectFromClient(0, $obj['id_object']);
									$oClients->attachObjectToClient($aParams['client_id'], $obj['id_object']);
								}
							}
							
							// Номенклатура ДДС
							if ( $obj['id_service'] == -1 ) {
								$aFirm 		= $oFirms->getFirmByIDOffice($obj['firm_region']['rcode']);
								$nIDOffice 	= isset($aFirm['id_office_dds']) 			? $aFirm['id_office_dds'] 					: 0;
								$nIDFirm	= $oFirms->getFirmByOffice($nIDOffice);
								$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);	
								$nIDDir 	= isset($nIDOffice) && !empty($nIDOffice) 	? $oSync->getDirectionByOffice($nIDOffice) 	: 0;									
								
								$obj['id_service'] 		= 0;
								$obj['service_name']	= ".:: ДДС ::.";		
								$obj['object_name']		= ".:: ДДС ::. - ".$sFirm;		
								$is_dds 				= 2;		
								$s_dds					= true;					
								
								if ( $aParams['doc_type'] != "oprostena" ) {
									
									foreach ( $aSchetRows as $nIDRowSchet ) {
										$oSync->delRow($nIDRowSchet);
									}
															
									throw new Exception("\nВ описа на документа има номенклатура ДДС!!!\nМоля изберете \"Опростена квитанция\"!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}
								
								// Schet
								$aSchetData = array();
								$aSchetData['id'] 				= 0;
								$aSchetData['id_obj'] 			= 0;	
								$aSchetData['data'] 			= time();	
								$aSchetData['mataksa'] 			= 0;	
								$aSchetData['bank'] 			= $aParams['paid_type'] == "bank" ? 1 : 0;	
								$aSchetData['confirm'] 			= 0;
								$aSchetData['confirm_date'] 	= "0000-00-00 00:00:00";
								$aSchetData['normal'] 			= 0;
								$aSchetData['sum'] 				= $obj['total_sum'];
								$aSchetData['taxes'] 			= 1;
								$aSchetData['paid_month'] 		= "0000-00-00";
								$aSchetData['tax_num'] 			= $nLastOrder;	
								$aSchetData['faktura_type'] 	= $aParams['doc_type'] == "faktura" ? 1 : 0; 	
								$aSchetData['f_name'] 			= iconv("UTF-8", "CP1251", $aParams['client_name']);		
								$aSchetData['f_address'] 		= iconv("UTF-8", "CP1251", $aParams['client_address']);	
								$aSchetData['f_dn'] 			= $aParams['client_ein_dds'];		
								$aSchetData['f_bulstat'] 		= $aParams['client_ein'];
								$aSchetData['f_mol'] 			= iconv("UTF-8", "CP1251", $aParams['client_mol']);
								$aSchetData['p_name'] 			= !empty($aParams['client_recipient']) ? iconv("UTF-8", "CP1251", $aParams['client_recipient']) : iconv("UTF-8", "CP1251", $aParams['client_mol']);	
								$aSchetData['p_lk'] 			= "";
								$aSchetData['p_year'] 			= "";
								$aSchetData['p_num'] 			= "";
								$aSchetData['measure'] 			= iconv("UTF-8", "CP1251", "бр.");
								$aSchetData['br'] 				= 1;
								$aSchetData['zero'] 			= 0;
								$aSchetData['zero_date'] 		= "0000-00-00";
								$aSchetData['faktura'] 			= $aParams['doc_type'] == "faktura" ? 1 : 0;
								$aSchetData['valid_sum'] 		= 0;
								$aSchetData['smetka_id'] 		= 0;
								$aSchetData['direction_id'] 	= $nIDDir;	//?
								$aSchetData['typepay_id'] 		= "1004";			//?
								$aSchetData['info'] 			= iconv("UTF-8", "CP1251", $obj['object_name']);	
								$aSchetData['user_id'] 			= $nIDSchetAcc;	
								$aSchetData['nareditel'] 		= "";	// samo pri razhod!!!
								$aSchetData['poluchatel'] 		= "";	// samo pri razhod!!!
								$aSchetData['razhod_num'] 		= "";	
								$aSchetData['saldo'] 			= 0;	
								
								if ( $nIDSchetAcc && $nID ) {
									$nIDSchet 		= $oSync->updateSchetMonth($aSchetData);
									$nIDSchet 		= date("Ym").zero_padding($nIDSchet, 7);
									$aSchetRows[] 	= $nIDSchet;
								}							
							
							} else {
								$s_normal 				= true;
								$nIDOffice				= $obj['firm_region']['rcode'];
							}
							
							$aDataRows 						= array();
							$aDataRows['id'] 				= 0;
							$aDataRows['id_sale_doc'] 		= $nID;	
							$aDataRows['id_office'] 		= $nIDOffice;
							$aDataRows['id_object'] 		= $obj['id_object'];
							$aDataRows['id_service'] 		= $obj['id_service'];
							$aDataRows['id_duty_row'] 		= $obj['id_duty'];
							$aDataRows['service_name'] 		= $obj['service_name'];
							$aDataRows['object_name'] 		= $obj['object_name'];
							$aDataRows['month'] 			= $obj['month'];
							$aDataRows['quantity']			= $obj['quantity'];
							$aDataRows['measure']			= "бр.";
							$aDataRows['single_price']		= $obj['single_price'];
							$aDataRows['total_sum']			= $obj['total_sum'];
							$aDataRows['paid_sum']			= 0;
							$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
							$aDataRows['is_dds']	 		= $is_dds;
							$aDataRows['type']	 			= $obj['type'];
							
							$oSaleDocRows->update($aDataRows);	
							
							if ( ($is_dds == 2) && !empty($nIDSchet) && !empty($aDataRows['id']) ) {
								$aTmp 					= array();
								$aTmp['id'] 			= $aDataRows['id'];
								$aTmp['id_schet_row'] 	= $nIDSchet;
										
								$oSaleDocRows->update($aTmp);								
							}

							// SCHET
							if ( $is_dds == 0 ) {	// Услугата не е по ДДС!
								$sVid	= isset($obj['type']) 		? $obj['type'] 		: "none";
								$sMonth = isset($obj['month']) 		? $obj['month'] 	: "0000-00-00";
								$nObj 	= isset($obj['id_object']) 	? $obj['id_object'] : 0;
								$nSum	= isset($obj['total_sum']) 	? $obj['total_sum'] : 0;
								
								// Обработвам месечни задължения към обект (вдигам падежа)
								if ( ($sVid != "single") && !empty($nObj) ) {
									$month 	= substr($sMonth, 0, 7)."-01";
									
									if ( $sVid == "month" ) {
										$month_price['months'][$month] 	= $month;
										
										if ( !isset($month_price['id_office']) ) {
											$month_price['id_object']	= $nObj;
											$month_price['service'] 	= isset($obj['service_name']) 			? $obj['service_name'] 			: "";
											$month_price['id_office'] 	= isset($obj['firm_region']['rcode']) 	? $obj['firm_region']['rcode'] 	: 0;
											
											if ( isset($obj['id_service']) && !empty($obj['id_service']) ) {
												$month_price['id_service']	= $obj['id_service'];
											}											
										}
									}
								} else {
									if ( !isset($month_price['service']) || empty($month_price['service']) ) {
										$month_price['service'] 	= isset($obj['service_name']) 	? $obj['service_name'] 	: "";
										$month_price['id_office'] 	= $nIDOffice;
										$month_price['id_service'] 	= isset($obj['id_service']) 	? $obj['id_service'] 	: 0;
									}
								}
								
								if ( isset($month_price['sum']) ) {
									$month_price['sum'] += $nSum;
								} else {
									$month_price['sum'] = $nSum;
								}									
							}
							
							// По обекти
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_objects" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_object) && ($names->id_object == $obj['id_object']) && $names->for_payment ) {
										$nIDSale							= isset($aDataRows['id']) && !empty($aDataRows['id']) ? $aDataRows['id'] : 0;
										
										if ( !empty($nIDSale) ) {
											$aDataSortRows					= array();
											$aDataSortRows['id'] 			= $nIDSale;
											$aDataSortRows['object_name'] 	= $names->object_name;
												
											$oSaleDocRows->update($aDataSortRows);
										}	
									}
								}
							}	

							// По услуги
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_services" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_service) && ($names->id_service == $obj['id_service']) && $names->for_payment ) {
										$nIDSale							= isset($aDataRows['id']) && !empty($aDataRows['id']) ? $aDataRows['id'] : 0;
										
										if ( !empty($nIDSale) ) {
											$aDataSortRows					= array();
											$aDataSortRows['id'] 			= $nIDSale;
											$aDataSortRows['service_name'] 	= $names->service_name;
												
											$oSaleDocRows->update($aDataSortRows);	
										}
									}
								}
							}	
							
						} elseif (is_object($obj) && isset($obj->type) && $obj->for_payment ) {
							$is_dds 	= 0;
							$nIDSchet	= 0;
							
							// Номенклатура ДДС
							if ( $obj->id_service == -1 ) {
								$aFirm 		= $oFirms->getFirmByIDOffice($obj->id_office);
								$nIDOffice 	= isset($aFirm['id_office_dds']) 			? $aFirm['id_office_dds'] 					: 0;
								$nIDFirm	= $oFirms->getFirmByOffice($nIDOffice);
								$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);		
								$nIDDir 	= isset($nIDOffice) && !empty($nIDOffice) 	? $oSync->getDirectionByOffice($nIDOffice) 	: 0;								

								$obj->id_service 	= 0;
								$obj->service_name	= ".:: ДДС ::.";
								$obj->object_name	= ".:: ДДС ::. - ".$sFirm;
								$is_dds 			= 2;
								$s_dds				= true;
								
								if ( $aParams['doc_type'] != "oprostena" ) {
									foreach ( $aSchetRows as $nIDRowSchet ) {
										$oSync->delRow($nIDRowSchet);
									}						
												
									throw new Exception("\nВ описа на документа има номенклатура ДДС!!!\nМоля изберете \"Опростена квитанция\"!!!\n\n", DBAPI_ERR_FAILED_TRANS);
								}
								
								// Schet
								$aSchetData = array();
								$aSchetData['id'] 				= 0;
								$aSchetData['id_obj'] 			= 0;	
								$aSchetData['data'] 			= time();	
								$aSchetData['mataksa'] 			= 0;	
								$aSchetData['bank'] 			= $aParams['paid_type'] == "bank" ? 1 : 0;	
								$aSchetData['confirm'] 			= 0;
								$aSchetData['confirm_date'] 	= "0000-00-00 00:00:00";
								$aSchetData['normal'] 			= 0;
								$aSchetData['sum'] 				= $obj->total_sum;
								$aSchetData['taxes'] 			= 1;
								$aSchetData['paid_month'] 		= "0000-00-00";
								$aSchetData['tax_num'] 			= $nLastOrder;	
								$aSchetData['faktura_type'] 	= $aParams['doc_type'] == "faktura" ? 1 : 0; 	
								$aSchetData['f_name'] 			= iconv("UTF-8", "CP1251", $aParams['client_name']);		
								$aSchetData['f_address'] 		= iconv("UTF-8", "CP1251", $aParams['client_address']);	
								$aSchetData['f_dn'] 			= $aParams['client_ein_dds'];		
								$aSchetData['f_bulstat'] 		= $aParams['client_ein'];
								$aSchetData['f_mol'] 			= iconv("UTF-8", "CP1251", $aParams['client_mol']);
								$aSchetData['p_name'] 			= !empty($aParams['client_recipient']) ? iconv("UTF-8", "CP1251", $aParams['client_recipient']) : iconv("UTF-8", "CP1251", $aParams['client_mol']);	
								$aSchetData['p_lk'] 			= "";
								$aSchetData['p_year'] 			= "";
								$aSchetData['p_num'] 			= "";
								$aSchetData['measure'] 			= iconv("UTF-8", "CP1251", "бр.");
								$aSchetData['br'] 				= 1;
								$aSchetData['zero'] 			= 0;
								$aSchetData['zero_date'] 		= "0000-00-00";
								$aSchetData['faktura'] 			= $aParams['doc_type'] == "faktura" ? 1 : 0;
								$aSchetData['valid_sum'] 		= 0;
								$aSchetData['smetka_id'] 		= 0;
								$aSchetData['direction_id'] 	= $nIDDir;	//?
								$aSchetData['typepay_id'] 		= "1004";			//?
								$aSchetData['info'] 			= iconv("UTF-8", "CP1251", $obj->object_name);	
								$aSchetData['user_id'] 			= $nIDSchetAcc;	
								$aSchetData['nareditel'] 		= "";	// samo pri razhod!!!
								$aSchetData['poluchatel'] 		= "";	// samo pri razhod!!!
								$aSchetData['razhod_num'] 		= "";	
								$aSchetData['saldo'] 			= 0;	
								
								if ( $nIDSchetAcc && $nID ) {
									$nIDSchet 		= $oSync->updateSchetMonth($aSchetData);
									$nIDSchet 		= date("Ym").zero_padding($nIDSchet, 7);
									$aSchetRows[] 	= $nIDSchet;
								}									
							} else {
								$s_normal			= true;
								$nIDOffice			= $obj->id_office;
							}
															
							$aDataRows['id'] 				= 0;
							$aDataRows['id_sale_doc'] 		= $nID;	
							$aDataRows['id_office'] 		= $nIDOffice;
							$aDataRows['id_object'] 		= 0;
							$aDataRows['id_service'] 		= $obj->id_service;
							$aDataRows['id_duty_row'] 		= 0;
							$aDataRows['service_name'] 		= $obj->service_name;
							$aDataRows['object_name'] 		= $obj->object_name;
							$aDataRows['month'] 			= $obj->month->getTimeStamp();
							$aDataRows['quantity']			= $obj->quantity;
							$aDataRows['measure']			= "бр.";
							$aDataRows['single_price']		= $obj->single_price;
							$aDataRows['total_sum']			= $obj->total_sum;
							$aDataRows['paid_sum']			= 0;
							$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
							$aDataRows['is_dds']	 		= $is_dds;
							$aDataRows['type']	 			= $obj->type;
							
							$oSaleDocRows->update($aDataRows);	
							
							if ( ($is_dds == 2) && !empty($nIDSchet) && !empty($aDataRows['id']) ) {
								$aTmp 					= array();
								$aTmp['id'] 			= $aDataRows['id'];
								$aTmp['id_schet_row'] 	= $nIDSchet;
										
								$oSaleDocRows->update($aTmp);								
							}							
							
							// SCHET
							if ( $is_dds == 0 ) {
								if ( !isset($month_price['service']) || empty($month_price['service']) ) {
									$month_price['service'] 	= isset($obj->service_name) ? $obj->service_name 	: "";
									$month_price['id_office'] 	= $nIDOffice;
									$month_price['id_service'] 	= isset($obj->id_service) 	? $obj->id_service 		: 0;
								}								
								
								if ( isset($month_price['sum']) ) {
									$month_price['sum'] += $obj->total_sum;
								} else {
									$month_price['sum'] = $obj->total_sum;
								}		
							}					
							// </SCHET>																				

							// По обекти
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_objects" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_object) && ($names->id_object == $obj->id_object) && $names->for_payment ) {
										$nIDSale						= isset($aDataRows['id']) && !empty($aDataRows['id']) ? $aDataRows['id'] : 0;
										
										if ( !empty($nIDSale) ) {
											$aDataSortRows					= array();
											$aDataSortRows['id'] 			= $nIDSale;
											$aDataSortRows['object_name'] 	= $names->object_name;
												
											$oSaleDocRows->update($aDataSortRows);
										}
									}
								}
							}
								
							// По услуги
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_services" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_service) && ($names->id_service == $obj->id_service) && $names->for_payment ) {
										$nIDSale						= isset($aDataRows['id']) && !empty($aDataRows['id']) ? $aDataRows['id'] : 0;
										
										if ( !empty($nIDSale) ) {
											$aDataSortRows					= array();
											$aDataSortRows['id'] 			= $nIDSale;
											$aDataSortRows['service_name'] 	= $names->service_name;
												
											$oSaleDocRows->update($aDataSortRows);	
										}
									}
								}
							}														
						}
					
					}	
					
					if ( $s_dds && $s_normal ) {
						foreach ( $aSchetRows as $nIDRowSchet ) {
							$oSync->delRow($nIDRowSchet);
						}

						throw new Exception("В документа има комбинация от услуги и ДДС!!!", DBAPI_ERR_INVALID_PARAM);
					}
					
					// Update на schet!!!
					//throw new Exception("dfsf".ArrayToString($month_price));
					//$oResponse->setAlert(ArrayToString($month_price));

					if ( isset($month_price['id_office']) ) {
						$nIDDirection 	= isset($month_price['id_office']) && !empty($month_price['id_office']) ? $oSync->getDirectionByOffice($month_price['id_office']) 	: 0;
						$aObj 			= isset($month_price['id_object']) && !empty($month_price['id_object'])	? $oObject->getByID($month_price['id_object']) 				: array();
						$oldObj			= isset($aObj['id_oldobj']) 		? $aObj['id_oldobj'] : 0;
						$nIDObject		= !empty($oldObj) 					? $oSync->getMasterFromID($oldObj)	: 0;
						$cnt			= isset($month_price['months']) 	? count($month_price['months'])		: 1;
						$max			= isset($month_price['months']) 	? max($month_price['months'])		: substr($aParams['doc_date'], 0, 7)."-01";
						$sum			= isset($month_price['sum']) 		? $month_price['sum'] 				: 0;
						$service_name	= isset($month_price['service'])	? $month_price['service']			: "";
						$nNomenclatres	= isset($month_price['id_service']) && !empty($month_price['id_service']) ? $oNomen->getByIDService($month_price['id_service']) : 0;
						$nIDNom			= isset($nNomenclatres[0]['id_schet']) ? $nNomenclatres[0]['id_schet'] 	: 0;
					} 
					
					if ( isset($month_price['sum']) && !$s_dds && $s_normal ) {
							
						$aSchetData = array();
						$aSchetData['id'] 				= 0;
						$aSchetData['id_obj'] 			= $nIDObject;	//!
						$aSchetData['data'] 			= time();	
						$aSchetData['mataksa'] 			= 0;	
						$aSchetData['bank'] 			= $aParams['paid_type'] == "bank" ? 1 : 0;	//!
						$aSchetData['confirm'] 			= 0;
						$aSchetData['confirm_date'] 	= "0000-00-00 00:00:00";
						$aSchetData['normal'] 			= 0;
						$aSchetData['sum'] 				= $aParams['doc_type'] != "oprostena" ? $sum * 1.2 : $sum;
						$aSchetData['taxes'] 			= $cnt;	//!
						$aSchetData['paid_month'] 		= $max; 		//substr($aParams['doc_date'], 0, 7)."-01";		//"0000-00-00";	//!
						$aSchetData['tax_num'] 			= $nLastOrder;	//!
						$aSchetData['faktura_type'] 	= $aParams['doc_type'] == "faktura" ? 1 : 0; 	//!
						$aSchetData['f_name'] 			= iconv("UTF-8", "CP1251", $aParams['client_name']);		//!
						$aSchetData['f_address'] 		= iconv("UTF-8", "CP1251", $aParams['client_address']);	//!
						$aSchetData['f_dn'] 			= $aParams['client_ein_dds'];		//!
						$aSchetData['f_bulstat'] 		= $aParams['client_ein'];	//!
						$aSchetData['f_mol'] 			= iconv("UTF-8", "CP1251", $aParams['client_mol']);		//!
						$aSchetData['p_name'] 			= !empty($aParams['client_recipient']) ? iconv("UTF-8", "CP1251", $aParams['client_recipient']) : iconv("UTF-8", "CP1251", $aParams['client_mol']);		//!
						$aSchetData['p_lk'] 			= "";
						$aSchetData['p_year'] 			= "";
						$aSchetData['p_num'] 			= "";
						$aSchetData['measure'] 			= iconv("UTF-8", "CP1251", "бр.");
						$aSchetData['br'] 				= $cnt;	//!
						$aSchetData['zero'] 			= 0;
						$aSchetData['zero_date'] 		= "0000-00-00";
						$aSchetData['faktura'] 			= $aParams['doc_type'] == "faktura" ? 1 : 0; 		//!
						$aSchetData['valid_sum'] 		= 0;
						$aSchetData['smetka_id'] 		= 0;
						$aSchetData['direction_id'] 	= $nIDDirection;	//!
						$aSchetData['typepay_id'] 		= $nIDNom;	//!
						$aSchetData['info'] 			= iconv("UTF-8", "CP1251", $service_name);	//!
						$aSchetData['user_id'] 			= $nIDSchetAcc;	//!
						$aSchetData['nareditel'] 		= "";	// samo pri razhod!!!
						$aSchetData['poluchatel'] 		= "";	// samo pri razhod!!!
						$aSchetData['razhod_num'] 		= "";	
						$aSchetData['saldo'] 			= 0;	
							
						if ( $nIDSchetAcc && $nID ) {
							$nIDSchet = $oSync->updateSchetMonth($aSchetData);
							$nIDSchet = date("Ym").zero_padding($nIDSchet, 7);
							$sTable		= PREFIX_SALES_DOCS_ROWS.date("Ym");
								
							$db_finance->Execute("UPDATE {$db_name_finance}.{$sTable} SET id_schet_row = '{$nIDSchet}' WHERE id_sale_doc = '{$nID}' AND is_dds != 2");
						}
					}
					
				} else {			// РЕДАКЦИЯ					
					// 1. Проверка за ордери към документ-а
					$aOrderRow 	= array();
					$aOrderRow 	= $oSaleDocRows->getOrdersByDoc( $nID );
					
					$oSaleDoc->getRecord($nID, $aOrders);
					
					//$nIDorder	= isset($aOrders['last_order_id']) 	? $aOrders['last_order_id'] : 0;	
					$sType		= isset($aOrders['doc_type']) 		? $aOrders['doc_type'] 		: "faktura";					
					
					// ДДС
					$aDDS		= array();
					$aDDS 		= $oSaleDocRows->getDDSByDoc( $nID );	
					$nIDDDS 	= isset($aDDS[0]['id']) ? $aDDS[0]['id'] : 0;
					
					// Списък от ИД-тата
					$sIDs 		= "";		
					$aNIDs		= array();
					
					// 1.0. - Имаме ордери, търсим права за анулиране!
					if ( !empty($aOrderRow) ) {
						
						if ( !$grant_right ) {
							throw new Exception("Нямате право за редакция на платени документи!!!", DBAPI_ERR_INVALID_PARAM);
						}						
						
						$oOrders	= new DBOrders();
												
						// Анулираме ордерите!!!
						foreach ( $aOrderRow as $aval ) {
							$nIDOrder = 0;
							
							if ( isset($aval['order_status']) && ($aval['order_status'] == "active") ) {
								$nIDOrder	= $aval['id'];
								
								if ( $this->isValidID($nIDOrder) ) {
									$oOrders->annulment($oResponse, $nIDOrder);
								}
								
							}
						}						
					}
	
					if ( !$edit_right ) {
						throw new Exception("Нямате достатъчни права за редакция!!!", DBAPI_ERR_INVALID_PARAM);
					}
					
					if ( isset($aParams['doc_num']) && !empty($aParams['doc_num']) ) {
						$nDocNum = $aParams['doc_num'];
					} else {
						$nDocNum = 0;
						
						if ( ($sType == "faktura") || ($sType == "kreditno izvestie") || ($sType == "debitno izvestie") ) {
							throw new Exception("Фактура с нулев номер не може да бъде въведена!");
						}
					}
					
					$aData 						= array();	
					
					$aData['id'] 				= strval($nID);
					$aData['doc_date'] 			= isset($aParams['doc_date']) 				? $aParams['doc_date'] 					: time();
					$aData['doc_status'] 		= "final";
					$aData['id_credit_master'] 	= 0;
					$aData['id_client'] 		= isset($aParams['client_id']) 				? $aParams['client_id'] 				: 0;

					$aData['client_name'] 		= isset($aParams['client_name']) 			? $aParams['client_name'] 				: "";
					$aData['client_ein'] 		= isset($aParams['client_ein']) 			? $aParams['client_ein'] 				: "";
					$aData['client_ein_dds'] 	= isset($aParams['client_ein_dds']) 		? $aParams['client_ein_dds'] 			: "";
					$aData['client_address'] 	= isset($aParams['client_address']) 		? $aParams['client_address'] 			: "";
					$aData['client_mol'] 		= isset($aParams['client_mol']) 			? $aParams['client_mol'] 				: "";
					$aData['client_recipient'] 	= isset($aParams['client_recipient']) 		&& !empty($aParams['client_recipient']) ? $aParams['client_recipient'] : $aData['client_mol'];
					
					$aData['deliverer_name'] 	= isset($aParams['cbPoluchatel']['name']) 	 ? $aParams['cbPoluchatel']['name'] 	: "";
					$aData['deliverer_ein'] 	= isset($aParams['cbPoluchatel']['idn']) 	 ? $aParams['cbPoluchatel']['idn'] 		: "";
					$aData['deliverer_ein_dds'] = isset($aParams['cbPoluchatel']['idn_dds']) ? $aParams['cbPoluchatel']['idn_dds']  : "";
					$aData['deliverer_address'] = isset($aParams['cbPoluchatel']['address']) ? $aParams['cbPoluchatel']['address']  : "";
					$aData['deliverer_mol'] 	= isset($aParams['cbPoluchatel']['jur_mol']) ? $aParams['cbPoluchatel']['jur_mol']  : "";
					
					$aData['total_sum'] 		= 0;
					$aData['orders_sum'] 		= 0;
					$aData['last_order_id'] 	= 0;
					$aData['last_order_time'] 	= "0000-00-00 00:00:00";
					$aData['paid_type'] 		= isset($aParams['paid_type']) 				? $aParams['paid_type'] 				: "cash";
					$aData['view_type'] 		= isset($aParams['cbView']) 				? $aParams['cbView'] 					: "detail";
					$aData['id_bank_account'] 	= isset($aParams['cbSmetka']) 				? $aParams['cbSmetka'] 					: 0;
					$aData['is_auto'] 			= 0;
					$aData['gen_pdf'] 			= 0;
					$aData['note']				= isset($aParams['note'])					? $aParams['note']						: "";	
					$aData['updated_user'] 		= $nIDUser;
					$aData['updated_time'] 		= time();	
					
					if ( $aData['view_type'] == "single" ) {
						if ( isset($aParams['single_row']->service_name) && !empty($aParams['single_row']->service_name) ) {
							$aData['single_view_name']	= $aParams['single_row']->service_name;
						}
					} else {
						$aData['single_view_name']		= "yслуга";
					}
					
					$oSaleDoc->update($aData);
					
					if ( isset($aParams['client_id']) && !empty($aParams['client_id'] ) ) {
						$aData 						= array();
						$aData['id'] 				= $aParams['client_id'];
						$aData['note']				= $aParams['note'];
						$aData['invoice_recipient'] = isset($aParams['client_recipient']) && !empty($aParams['client_recipient']) 	? $aParams['client_recipient'] 	: $aParams['client_mol'];

						if ( isset($aParams['doc_type']) ) {
							if ( ($aParams['doc_type'] == "kvitanciq") || ($aParams['doc_type'] == "oprostena") ) {
								$aData['invoice_payment'] = "receipt";
							} else {
								if ( $aParams['paid_type'] == "cash" ) {
									$aData['invoice_payment'] = "cash";
								} else {
									$aData['invoice_payment'] = "bank";
								}
							} 
						} else {
							$aData['invoice_payment'] = "cash";
						}
																			
						$oClients->update($aData);
					}										
	
					foreach ( $aParams['arr_rows'] as $key => $obj ) {							
						if ( !is_object($obj) && !empty($obj['total_sum']) ) {	
							$is_dds = 0;
							
							// Детайлен изглед
							if ( $obj['for_payment'] ) {					
								
								if ( !empty($obj['id_object']) && !empty($aParams['client_id']) ) {
									$test = false;
									$test = $oClients->isObjectAttachedToClient($aParams['client_id'], $obj['id_object']);
									
									if ( !$test ) {
										$aObject = $oObject->getByID($obj['id_object']);
										$sObjectName = isset($aObject['name']) ? "[".$aObject['num']."] ".$aObject['name'] : "";
										
										$oResponse->setAlert("Внимание!!!\nОбекта {$sObjectName} ще бъде привързан към избрания клиент при потвърждаване\n!!!");
																			
										$oClients->detachObjectFromClient(0, $obj['id_object']);
										$oClients->attachObjectToClient($aParams['client_id'], $obj['id_object']);
									}
								}

								// Номенклатура ДДС
								if ( $obj['id_service'] == -1 ) {
									$fname = $oOffices->getFirmNameByIDOffice($obj['firm_region']['rcode']);
									
									$obj['id_service'] 		= 0;
									$obj['service_name']	= ".:: ДДС ::.";
									$obj['object_name']		= ".:: ДДС ::. - ".$fname;		
									$is_dds 				= 2;							
									
									if ( $aParams['doc_type'] != "oprostena" ) {
										throw new Exception("\nВ описа на документа има номенклатура ДДС!!!\nМоля изберете \"Опростена квитанция\"!!!\n\n", DBAPI_ERR_FAILED_TRANS);
									}
								}																		
								
								$aDataRows 						= array();
								$aDataRows['id'] 				= isset($obj['id']) && !empty($obj['id']) ? $obj['id'] : 0;
								$aDataRows['id_sale_doc'] 		= $nID;	
								$aDataRows['id_office'] 		= $obj['firm_region']['rcode'];
								$aDataRows['id_object'] 		= $obj['id_object'];
								$aDataRows['id_service'] 		= $obj['id_service'];
								$aDataRows['id_duty_row'] 		= $obj['id_duty'];
								$aDataRows['service_name'] 		= $obj['service_name'];
								$aDataRows['object_name'] 		= $obj['object_name'];
								$aDataRows['month'] 			= $obj['month'];
								$aDataRows['quantity']			= $obj['quantity'];
								$aDataRows['measure']			= "бр.";
								$aDataRows['single_price']		= ($sType == "kreditno izvestie") ? $obj['single_price'] * -1 : $obj['single_price'];
								$aDataRows['total_sum']			= ($sType == "kreditno izvestie") ? $obj['total_sum'] * -1 : $obj['total_sum'];
								$aDataRows['paid_sum']			= 0;
								$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
								$aDataRows['is_dds']	 		= $is_dds;
								$aDataRows['type']	 			= $obj['type'];
															
								$oSaleDocRows->update($aDataRows);		
							}	
							
							// По обекти
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_objects" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_object) && ($names->id_object == $obj['id_object']) && $names->for_payment && !empty($obj['id']) ) {
										$aDataRows					= array();
										$aDataRows['id'] 			= isset($obj['id']) && !empty($obj['id']) ? $obj['id'] : 0;
										$aDataRows['object_name'] 	= $names->object_name;
										
										$oSaleDocRows->update($aDataRows);	
									}
								}
							}	

							// По услуги
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_services" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_service) && ($names->id_service == $obj['id_service']) && $names->for_payment && !empty($obj['id']) ) {
										$aDataRows					= array();
										$aDataRows['id'] 			= isset($obj['id']) && !empty($obj['id']) ? $obj['id'] : 0;
										$aDataRows['service_name'] 	= $names->service_name;
										
										$oSaleDocRows->update($aDataRows);	
									}
								}
							}																
						} elseif (is_object($obj) && isset($obj->type) ) {							
							
							// Детайлен изглед
							if ( $obj->for_payment ) {
								$is_dds = 0;
								
								// Номенклатура ДДС
								if ( $obj->id_service == -1 ) {
									$fname = $oOffices->getFirmNameByIDOffice($obj->id_office);
									$obj->id_service 	= 0;
									$obj->service_name	= ".:: ДДС ::.";
									$obj->object_name	= ".:: ДДС ::. - ".$fname;
									$is_dds 			= 2;
									
									if ( $aParams['doc_type'] != "oprostena" ) {
										throw new Exception("\nВ описа на документа има номенклатура ДДС!!!\nМоля изберете \"Опростена квитанция\"!!!\n\n", DBAPI_ERR_FAILED_TRANS);
									}
								}	
																
								$aDataRows 						= array();
								$aDataRows['id'] 				= isset($obj->id) && !empty($obj->id) ? $obj->id : 0;
								$aDataRows['id_sale_doc'] 		= $nID;	
								$aDataRows['id_office'] 		= $obj->id_office;
								$aDataRows['id_object'] 		= 0;
								$aDataRows['id_service'] 		= $obj->id_service;
								$aDataRows['id_duty_row'] 		= 0;
								$aDataRows['service_name'] 		= $obj->service_name;
								$aDataRows['object_name'] 		= $obj->object_name;
								$aDataRows['month'] 			= $obj->month->getTimeStamp();
								$aDataRows['quantity']			= $obj->quantity;
								$aDataRows['measure']			= "бр.";
								$aDataRows['single_price']		= ($sType == "kreditno izvestie") ? $obj->single_price * -1 : $obj->single_price;
								$aDataRows['total_sum']			= ($sType == "kreditno izvestie") ? $obj->total_sum * -1 : $obj->total_sum;
								$aDataRows['paid_sum']			= 0;
								$aDataRows['paid_date'] 		= "0000-00-00 00:00:00";
								$aDataRows['is_dds']	 		= $is_dds;
								$aDataRows['type']	 			= $obj->type;
								
								$oSaleDocRows->update($aDataRows);
							}
	
							// По обекти
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_objects" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_object) && ($names->id_object == $obj->id_object) && $names->for_payment && !empty($obj->id) ) {
										$aDataRows					= array();
										$aDataRows['id'] 			= isset($obj->id) && !empty($obj->id) ? $obj->id : 0;
										$aDataRows['object_name'] 	= $names->object_name;
										
										$oSaleDocRows->update($aDataRows);
									}
								}
							}
							
							// По услуги
							if ( isset($aParams['cbView']) && $aParams['cbView'] == "by_services" ) {
								foreach ( $aParams['grid'] as $names ) {
									if ( isset($names->id_service) && ($names->id_service == $obj->id_service) && $names->for_payment && !empty($obj->id) ) {
										$aDataRows					= array();
										$aDataRows['id'] 			= isset($obj->id) && !empty($obj->id) ? $obj->id : 0;
										$aDataRows['service_name'] 	= $names->service_name;
										
										$oSaleDocRows->update($aDataRows);	
									}
								}
							}															
						}
					}												
				
				}
				
				// Вдигаме падежите
				$aSaleRows	= array();
				$aSaleRows 	= $oSaleDocRows->getByIDSaleDoc($nID);

				foreach ( $aSaleRows as $val ) {
					if ( isset($val['id_duty_row']) && !empty($val['id_duty_row']) ) {
						$nIDRow = $val['id_duty_row'];
						$month 	= $val['month'];
						$type 	= $val['type'];
						
						if ( $type == "month" ) {
							$aUpdateData 				= array();
							$aUpdateData['id'] 			= $nIDRow;
							$aUpdateData['last_paid'] 	= substr($month, 0, 7)."-01";
							//$oResponse->setAlert(substr($month, 0, 7)."-01");
							$oMonths->update($aUpdateData);
						} elseif( $type == "single" ) {
							$aUpdateData 				= array();
							$aUpdateData['id'] 			= $nIDRow;
							$aUpdateData['paid_date'] 	= $month;
							$aUpdateData['id_sale_doc'] = $nID;
							
							$oSingles->update($aUpdateData);							
						}
					}
				}					
				
				if ( !empty($sErrMessage) ) {
					//$oResponse->setAlert($sErrMessage);
					
					$db_finance->FailTrans();
					$db_system->FailTrans();	
					$db_sod->FailTrans();
					
					throw new Exception($sErrMessage, DBAPI_ERR_FAILED_TRANS);
				}
					
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();	
				$db_sod->CompleteTrans();	

				$oResponse->SetFlexVar("save_status", true);			
			} catch (Exception $e) {
				$sMessage = $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				$db_sod->FailTrans();

				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}	
	
			$this->calculateDDS($oResponse);
				
			$this->init($oResponse);
		}	
		
		public function calculateDDS( DBResponse $oResponse ) {
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$oSaleDoc		= new DBSalesDocs();
			$oSaleDocRows	= new DBSalesDocsRows();
			$oFirms 		= new DBFirms();
			
			$aSaleRows		= array();
			$aSaleDoc		= array();
			$aData			= array();
			$aFirms 		= array();
			$nTotalSum		= 0;
			$nPaidSum		= 0;
			$sLastOrder		= "0";
			$sLastPtime		= "0000-00-00 00:00:00";
			$edit_right		= false;
			$grant_right 	= false;			
			
			$aParams		= Params::getAll();
			
			if ( isset($aParams['hiddenParams']->nID) && !empty($aParams['hiddenParams']->nID) ) {
				$nID = $aParams['hiddenParams']->nID;
			} else {
				$nID = isset($aParams['id']) ? $aParams['id'] : 0;
				$oResponse->SetFlexVar("nID", $nID);
			}
			
			Params::set("id", $nID);	
			
			if ( !$this->isValidID($nID) ) {
				continue;
			}			
			
			// Права за достъп
			$edit_right 	= in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;
			$grant_right 	= false;
	
			if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$edit_right		= true;
				$grant_right 	= true;
			}			
			
			$oSaleDoc->getRecord( $nID, $aSaleDoc );
			
			$sLastOrder			= isset($aSaleDoc['last_order_id']) 	? $aSaleDoc['last_order_id'] 	: 0;
			$sLastPtime			= isset($aSaleDoc['last_order_time'])	? $aSaleDoc['last_order_time'] 	: "0000-00-00 00:00:00";
			
			if ( isset($aSaleDoc['deliverer_ein']) && !empty($aSaleDoc['deliverer_ein']) ) {
				$doc_type		= $aSaleDoc['doc_type'];
				$doc_date		= $aSaleDoc['doc_date'];
				$ein			= $aSaleDoc['deliverer_ein'];					
			} else {
				$doc_type		= isset($aParams['doc_type']) ? $aParams['doc_type'] : "";
				$doc_date		= isset($aParams['doc_date']) ? $aParams['doc_date'] : "0000-00-00";
				$ein			= isset($aParams['cbPoluchatel']['idn']) ? $aParams['cbPoluchatel']['idn']: 0;						
			}
			
			// ДДС
			$aDDS		= array();
			$aDDS 		= $oSaleDocRows->getDDSByDoc( $nID );	
			$nDDS 		= isset($aDDS[0]['id']) 			? $aDDS[0]['id'] 			: 0;
			$nDDSSchet	= isset($aDDS[0]['id_schet_row']) 	? $aDDS[0]['id_schet_row'] 	: 0;
			
			$aSaleRows 	= $oSaleDocRows->getByIDSaleDoc( $nID );
			
			foreach( $aSaleRows as $val ) {
				if ( isset($val['id']) && !empty($val['id']) && $val['id'] != $nDDS ) {
					$nTotalSum 	+= $val['total_sum'];
					$nPaidSum	+= $val['paid_sum'];
					
					if ( empty($nDDSSchet) && !empty($val['id_schet_row']) ) {
						$nDDSSchet = $val['id_schet_row'];
					}
				}
			}
		
			if ( empty($sLastOrder) && !empty($nDDS) ) {				
				$this->delRows($nDDS, $oResponse);							
			} elseif ( !empty($sLastOrder) && !empty($nDDS) ) {
				if ( !$grant_right ) {
					continue;
				} else {					
					$this->delRows($nDDS, $oResponse);
				}
			}		
			
			if ( $doc_type != "oprostena" ) {
				//$aFirms 	= $oFirms->getDDSFirmByEIN( $ein );
				$nIDOffice	= $oFirms->getDDSOfficeByEIN($ein);
				
				$aData['id'] 				= 0;
				$aData['id_sale_doc'] 		= $nID;	
				$aData['id_office'] 		= $nIDOffice;		//isset($aFirms['id']) ? $aFirms['id'] : 0;	
				$aData['id_object'] 		= 0;
				$aData['month'] 			= $doc_date;
				$aData['id_service'] 		= 0;
				$aData['id_schet_row'] 		= $nDDSSchet;
				$aData['service_name'] 		= "ДДС";
				$aData['quantity']			= 1;
				$aData['measure']			= "бр.";
				$aData['single_price']		= $nTotalSum * 0.2;
				$aData['total_sum']			= $nTotalSum * 0.2;
				$aData['paid_sum']			= 0;
				$aData['paid_date'] 		= "0000-00-00 00:00:00";
				$aData['is_dds']	 		= 1;
				$aData['type'] 				= "month";
	
				$oSaleDocRows->update($aData);
					
				$nTotalSum *= 1.2;
			}				
		
			$aData						= array();
			$aData['id'] 				= $nID;
			$aData['total_sum'] 		= $nTotalSum;
			$aData['orders_sum'] 		= $nPaidSum;
			$aData['last_order_id'] 	= $sLastOrder;
			$aData['last_order_time']	= $sLastPtime;
				
			$oSaleDoc->update($aData);	
		}		

		
		public function delRows( $sRows, DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_name_sod; 
			
			$nIDUser		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$aData 			= array();
			$aData 			= explode(",", $sRows);
			$sErrMessage	= "";
			$flag 			= true;
			
			$oOrders		= new DBOrders();
			$oSaleDocRows	= new DBSalesDocsRows();
			$oSaleDoc		= new DBSalesDocs();
			$oFirms			= new DBFirms();
			$oOrderRows		= new DBOrdersRows();
			$oSaldo			= new DBSaldo();
			
			foreach ( $aData as $nID ) {
				$aSaleRows		= array();	
				$aSaleDoc		= array();		
											
				if ( !$this->isValidID($nID) ) {
					continue;
				}						
	
				$oSaleDocRows->getRecord($nID, $aSaleRows);
				
				$nIDSaleDoc		= isset($aSaleRows['id_sale_doc']) 	? $aSaleRows['id_sale_doc'] 		: 0;
				//$nSum			= isset($aSaleRows['total_sum']) 	&& isset($aSaleRows['paid_sum']) && ($aSaleRows['total_sum'] == $aSaleRows['paid_sum']) ? $aSaleRows['total_sum'] : 0;
				$nPaidSum		= isset($aSaleRows['paid_sum']) 	? abs($aSaleRows['paid_sum'])	: 0;
				$nSum			= isset($aSaleRows['paid_sum']) 	? $aSaleRows['paid_sum']		: 0;
				$nTotalSum		= isset($aSaleRows['total_sum']) 	? $aSaleRows['total_sum'] 		: 0;
				$isDDS			= isset($aSaleRows['is_dds']) 		? $aSaleRows['is_dds'] 			: 0;
				$nIDOffice		= isset($aSaleRows['id_office']) 	? $aSaleRows['id_office'] 		: 0;
				$nIDObject		= isset($aSaleRows['id_object']) 	? $aSaleRows['id_object'] 		: 0;
				$nIDService		= isset($aSaleRows['id_service']) 	? $aSaleRows['id_service'] 		: 0;
				$nIDRow			= isset($aSaleRows['id']) 			? $aSaleRows['id'] 				: 0;
				$sMonth			= isset($aSaleRows['month'])		? $aSaleRows['month']			: date("Y-m")."-01";
				$nIDEarning		= isset($aSaleRows['id_nomenclature_earning']) ? $aSaleRows['id_nomenclature_earning'] : 0;
				$nSaldo			= 0;
				$nIDTran		= 0;

				$oSaleDoc->getRecord($nIDSaleDoc, $aSaleDoc);
					
				$nIDOrder 		= isset($aSaleDoc['last_order_id'])	? $aSaleDoc['last_order_id'] 	: 0;
				$nIDFirm 		= $oFirms->getFirmByOffice($nIDOffice);
				$nIDTran		= $oSaleDocRows->checkForTransfer( $nIDSaleDoc );
		
				if ( !empty($nPaidSum) ) {
					// Имаме реализирано плащане за конкретния запис, създаваме обратено плащане на него!
					$aRow			= array();
					$aOrder			= array();
						
					$oOrders->getRecord($nIDOrder, $aRow);					
							
					$nIDAccount		= isset($aRow['bank_account_id']) 	? $aRow['bank_account_id'] 	: 0;
					$sOrderType		= isset($aRow['order_type']) 		? $aRow['order_type'] 		: "expense";
					$sAccType		= isset($aRow['account_type']) 		? $aRow['account_type'] 	: "cash";
					$sDocType		= isset($aRow['doc_type']) 			? $aRow['doc_type'] 		: "sale";
						
					// Пореден номер
					$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
					$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;						
						
					// Наличност по сметка
					$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} FOR UPDATE");
					$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;			
						
					// Салдо	
					$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, $isDDS);
					$nIDSaldo		= 0;
					$nCurrentSaldo	= 0;
	
					if ( !empty($aSaldo) ) {
						$nIDSaldo 	= $aSaldo['id'];
					}
								
					// Салдо на фирмата с изчакване!!!
					if ( !empty($nIDSaldo) ) {
						$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
					   	$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
					} else {
						$sErrMessage .= "Неизвестно салдо по фирма!\n";
					}
						
					if ( $nTotalSum > 0 ) {
						// Сумата е положителна, проверяваме за тип на документа
						if ( $sDocType == "buy" ) {
							// Типа на документа е разход. Прибавяме сумата към сметките!
							$sOrderType 	= "earning";
							$paid_account	= $nAccountState + $nPaidSum;
							$nSaldo			= $nCurrentSaldo + $nPaidSum;
						} else {
							// Типа е приход - вадим сумата от сметките (обратно действие)
							$sOrderType 	= "expense";
							$paid_account	= $nAccountState - $nPaidSum;
							$nSaldo			= $nCurrentSaldo - $nPaidSum;							
						}
					} else {
						// Сумата е отрицателна, връщаме стойностите!
						if ( $sDocType == "buy" ) {
							// Типа на документа е разход. Премахваме сумата към сметките!
							$sOrderType 	= "expense";
							$paid_account	= $nAccountState - $nPaidSum;
							$nSaldo			= $nCurrentSaldo - $nPaidSum;
						} else {
							// Типа е приход - прибавяме сумата от сметките (обратно действие)
							$sOrderType 	= "earning";
							$paid_account	= $nAccountState + $nPaidSum;
							$nSaldo			= $nCurrentSaldo + $nPaidSum;							
						}						
					}
						
					if ( $nAccountState < 0 ) {
						$sErrMessage .= "Недостатъчна наличност в сметката!\n";		
					}
						
					if ( $nSaldo < 0 ) {
						$sErrMessage .= "Недостатъчна наличност по салдо на фирмата!\n";		
					}	
	
					$aOrderData						= array();
					$aOrderData['id']				= 0;
					$aOrderData['num']				= $nLastOrder;
					$aOrderData['order_type'] 		= $sOrderType;
					$aOrderData['id_transfer']		= 0;
					$aOrderData['order_date']		= time();
					$aOrderData['order_sum']		= $nPaidSum;
					$aOrderData['account_type']		= $sAccType;
					$aOrderData['id_person']		= $nIDUser;
					$aOrderData['account_sum']		= $paid_account;
					$aOrderData['bank_account_id']	= $nIDAccount;
					$aOrderData['doc_id']			= $nIDSaleDoc;
					$aOrderData['doc_type']			= $sDocType;
					$aOrderData['note']				= "";
					$aOrderData['created_user']		= $nIDUser;
					$aOrderData['created_time']		= time();
					$aOrderData['updated_user']		= $nIDUser;
					$aOrderData['updated_time']		= time();
					
					$oOrders->update($aOrderData);	
					
					$db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
						
					$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum - '{$nSum}' WHERE id_bank_account = {$nIDAccount} ");

					if ( empty($nIDTran) ) {
						$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum - '{$nSum}' WHERE id = {$nIDSaldo} LIMIT 1");
					}	

					$nIDOrderNow = $aOrderData['id'];
					
					$aData = array();
					$aData['id'] 						= 0;
					$aData['id_order'] 					= $nIDOrderNow;
					$aData['id_doc_row'] 				= $nIDRow;
					$aData['id_office'] 				= $nIDOffice;
					$aData['id_object'] 				= $nIDObject;
					$aData['id_service'] 				= $nIDService;
					$aData['id_direction']				= 0;
					$aData['id_nomenclature_earning'] 	= $nIDEarning;
					$aData['id_nomenclature_expense'] 	= 0;
					$aData['id_saldo']					= $nIDSaldo;
					$aData['id_bank']					= $nIDAccount;
					$aData['saldo_state']				= empty($nIDTran) ? $nSaldo : $nCurrentSaldo;		
					$aData['account_state']				= $paid_account;						
					$aData['month'] 					= $sMonth;
					$aData['type'] 						= "free";
					$aData['paid_sum'] 					= ($nSum * -1);
					$aData['is_dds'] 					= $isDDS;
									
					$oOrderRows->update($aData);						
				}
			
				$oSaleDocRows->delete( $nID );
			}
				
			if ( !empty($sErrMessage) ) {
				$oResponse->setAlert($sErrMessage);
			}
					
			//$oResponse->printResponse();
			$this->init($oResponse);
		}
		
		
		public function makeOrder( DBResponse $oResponse ) {
			global $db_sod, $db_system, $db_finance, $db_name_system, $db_name_sod, $db_name_finance;
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oFirms 		= new DBFirms();
			$oOrders		= new DBOrders();
			$oOrderRows		= new DBOrdersRows();
			$oSaleDocRows	= new DBSalesDocsRows();
			$oSaleDoc		= new DBSalesDocs();
			$oServices		= new DBObjectServices();
			$oSync			= new DBSyncMoney();
			$oObject		= new DBObjects();
			$oOffices		= new DBOffices();
			$oSaldo			= new DBSaldo();
			
			$aFirms 		= array();
			$aOrders		= array();
			$aData			= array();
			$aData2			= array();
			$aOrderData		= array();
			$aSaleRows		= array();
			$aSaldo			= array();
			$sErrMessage	= "";
			$nLastOrder		= 0;
			$nAccountState	= 0;
			$nLast			= 0;
			
			$aParams		= Params::getAll();
			
			$nID 			= isset($aParams['hiddenParams']->nID) 	? $aParams['hiddenParams']->nID 			: 0;	
			$nIDAccount 	= isset($aParams['cbSmetkaOrder']) 		? $aParams['cbSmetkaOrder'] 				: 0;	
			$nSum		 	= isset($aParams['eOrderSum']) 			? sprintf("%01.2f", $aParams['eOrderSum'])	: sprintf("%01.2f", 0);	

			Params::set("id", $nID);

			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа е анулиран!", DBAPI_ERR_INVALID_PARAM);
			}
			
			// Право за редакция
			$edit_right = true; //in_array('sale_doc_order_edit', $_SESSION['userdata']['access_right_levels']) ? true : false;		

			// ДДС
			$aDDS		= array();
			$aDDS 		= $oSaleDocRows->getDDSByDoc( $nID );
			$nDDSWait	= isset($aDDS[0]['paid_sum']) 	? $aDDS[0]['total_sum'] - $aDDS[0]['paid_sum'] 	: 0;
			$nDDS 		= isset($aDDS[0]['id']) 		? $aDDS[0]['id'] 								: 0;	
			$nDDSSchet	= isset($aDDS[0]['id_schet_row']) ? $aDDS[0]['id_schet_row'] 					: 0;	

			$nTotalSum 	= 0;	
			$nRealTotal	= 0;
			$nPaidSum	= 0;
			$nDDSSum	= 0;
			$nIDTran	= 0;
			$nIDTran2	= 0;
			$nAccState	= 0;
			$checkDDS	= false;
			$sSaleName 	= PREFIX_SALES_DOCS.substr($nID, 0, 6);
			$sRowsName	= PREFIX_SALES_DOCS_ROWS.substr($nID, 0, 6);
			
			$aDoc 		= $oSaleDoc->getDoc($nID);
			$checkDDS	= $oSaleDocRows->checkForDDS($nID);
			
			$nIDTran	= $oSaleDocRows->checkForTransfer($nID);
			$nIDTran2	= $oSaleDocRows->checkForTransfer($nID, 1);
			
			if ( !empty($nIDTran) && !empty($nIDTran2) ) {
				throw new Exception("В документа участват комбинации от услуги и ТРАНСФЕР!!!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			$forPay		= array();
			
			if ( $aParams['cbView'] == "detail" ) {
				foreach ( $aParams['grid'] as $obj ) {							
					if ( !is_object($obj) && !empty($obj['id']) && !empty($obj['for_payment']) ) {
						$forPay[] = $obj['id'];
					}
				}
			}
			
			$db_finance->StartTrans();
			$db_system->StartTrans();	
			
			try {
				// Следващ номер за ордер
				$oRes 			= $db_system->Execute("SELECT last_num_order FROM {$db_name_system}.system FOR UPDATE");
				$nLastOrder 	= !empty($oRes->fields['last_num_order']) ? $oRes->fields['last_num_order'] + 1 : 0;
				
				// НАЧАЛНА наличност по сметка
				$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1");
				$nAccState	 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;		
				
				$aSaleRows 		= $oSaleDocRows->getRowsByDoc( $nID );
				
				foreach( $aSaleRows as $val ) {
					if ( isset($val['id']) && !empty($val['id']) ) {
						$nTotalSum 	+= $val['total_sum'];
						$nPaidSum	+= $val['paid_sum'];						
					}
				}		
				
				unset($val);
				
				$nWaitSum = sprintf("%01.2f", ($nTotalSum + $nDDSWait) - $nPaidSum);
				
				if ( abs($nSum) > abs($nWaitSum) ) {
					$nSum = $nWaitSum;
				}
				
				$nSumTemp		= $nSum;		
						
				// Плащания по точно определен запис!
				if ( !empty($forPay) && ($nSum != "0.00") ) {
					$aTemp		= array();
					$aTemp 		= $oSaleDocRows->getByIDsDDS($nID, $forPay);
					$aDataDDS	= array();
					$aData		= array();
					$nSumToPay	= $nSum; // Започваме с предложените парички
					
					$nRealTotal	= 0;
					$aDataOrder						= array();
					$aDataOrder['id']				= 0;
					$aDataOrder['num']				= $nLastOrder;
					$aDataOrder['order_type'] 		= "earning";
					$aDataOrder['id_transfer']		= 0;
					$aDataOrder['order_date']		= time();
					$aDataOrder['order_sum']		= 0;	
					$aDataOrder['account_type']		= isset($aParams['paid_type']) ? $aParams['paid_type'] : "cash";
					$aDataOrder['id_person']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
					$aDataOrder['account_sum']		= 0;
					$aDataOrder['bank_account_id']	= $nIDAccount;	//isset($aParams['cbAccount']) ? $aParams['cbAccount'] : 0;
					$aDataOrder['doc_id']			= $nID;
					$aDataOrder['doc_type']			= "sale";
					$aDataOrder['note']				= "";
					$aDataOrder['created_user']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
					$aDataOrder['created_time']		= time();
					$aDataOrder['updated_user']		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
					$aDataOrder['updated_time']		= time();
		
					$oOrders->update($aDataOrder);
					
					$nIDOrder 		= $aDataOrder['id'];	
		
					foreach ( $aTemp as $payed ) {
						$aFirm 			= array();
						$nIDFirm 		= 0;
						$is_dds	  		= 0;
						$is_dds2  		= 0;		
						$nAccountState	= 0;					
						
						if ( $payed['total_sum'] != $payed['paid_sum'] ) {
							$aFirm 		= $oFirms->getFirmByIDOffice($payed['id_office']);
							$aTmp		= array();
							$aFirms		= array();
							$nIDSchet 	= isset($payed['id_schet_row']) ? $payed['id_schet_row'] 	: 0;
							$nIDSingle 	= isset($payed['id_schet']) 	? $payed['id_schet'] 		: 0;
							$nIDTnet	= isset($payed['id']) 			? $payed['id'] 				: 0;	
							$nIDDuty	= isset($payed['id_duty']) 		? $payed['id_duty']			: 0;	
							$sMonth		= isset($payed['month']) 		? substr($payed['month'], 0, 7)."-01"	: "0000-00-00";		
							
							if ( isset($payed['type']) && ($payed['type'] == "month") && !empty($nIDDuty) ) {
								$aTemp = array();
								$aTemp = $oServices->getServiceByID($nIDDuty);
								
								if ( isset($aTemp['real_paid']) && ($aTemp['real_paid'] < $sMonth) ) {
									$aTempData 				= array();
									$aTempData['id'] 		= $nIDDuty;
									$aTempData['real_paid']	= $sMonth;

									$oServices->update($aTempData);
								}
							}
 							
							if ( $payed['is_dds'] == 2 ) {
								$nIDOffice 	= isset($aFirm['id_office_dds']) ? $aFirm['id_office_dds'] : 0;
								$nIDFirm	= $oFirms->getFirmByOffice($nIDOffice);
								$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);								
								$is_dds 	= 2;
								$is_dds2	= 1;
								$nIDService	= 0;
								$nIDEarning	= 0;
							} elseif ( $payed['is_dds'] == 1 ) {
								$nIDOffice 	= isset($aFirm['id_office_dds']) ? $aFirm['id_office_dds'] : 0;
								$nIDFirm	= $oFirms->getFirmByOffice($nIDOffice);
								$sFirm 		= $oOffices->getFirmNameByIDOffice($nIDOffice);								
								$is_dds 	= 1;
								$is_dds2	= 1;
								$nIDService	= 0;
								$nIDEarning	= 0;								
							} else {
								$nIDFirm 	= isset($aFirm['id']) ? $aFirm['id'] : 0;
								$sFirm 		= $oOffices->getFirmNameByIDOffice($payed['id_office']);								
								$nIDService = $payed['id_service'];		
								$aService	= $oServices->getService($payed['id_service']);
								$nIDEarning	= isset($aService['id_earning']) ? $aService['id_earning'] : 0;
							}

							if ( abs($nSumToPay) >= abs($payed['total_sum'] - $payed['paid_sum']) ) {
								$real_sum 	= $payed['total_sum'] - $payed['paid_sum'];
								$nSumToPay -= $real_sum;
							} else {
								$real_sum 	= $nSumToPay;
								$nSumToPay	= 0;
							}
							
							$nRealTotal 	+= $real_sum; 
							$aSaldo			= $oSaldo->getSaldoByFirm($nIDFirm, $is_dds2);
							$nIDSaldo		= 0;
							$nCurrentSaldo	= 0;

							if ( !empty($aSaldo) ) {
								$nIDSaldo 		= $aSaldo['id'];
							}
							
							if ( !empty($nIDSaldo) ) {
								// Салдо на фирмата с изчакване!!!
								$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
				    			$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
							} else {
								throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
							}	

							// Наличност по сметка
							if ( !empty($nIDAccount) ) {
								$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
								$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
							} else {
								throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
							}			
							
							if ( ($nAccountState + $real_sum) < 0 ) {
								throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
							}								
												
							// Ордери - разбивка!
							$aDataRows								= array();
							$aDataRows['id']						= 0;
							$aDataRows['id_order']					= $nIDOrder;
							$aDataRows['id_doc_row']				= $payed['id'];
							$aDataRows['id_office']					= $payed['id_office'];
							$aDataRows['id_object']					= $payed['id_object'];
							$aDataRows['id_service']				= $nIDService;
							$aDataRows['id_direction']				= 0;
							$aDataRows['id_saldo']					= $nIDSaldo;
							$aDataRows['id_bank']					= $nIDAccount;
							$aDataRows['saldo_state']				= !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo + $real_sum;
							$aDataRows['account_state']				= $nAccountState + $real_sum;
							$aDataRows['id_nomenclature_earning']	= $nIDEarning;
							$aDataRows['id_nomenclature_expense']	= 0;
							$aDataRows['month']						= $payed['month'];
							$aDataRows['type']						= $payed['type'];
							$aDataRows['paid_sum']					= $real_sum;
							$aDataRows['is_dds']					= $is_dds;
							
							if ( !empty($real_sum) && (strlen($payed['id']) == 13) ) {
								$oOrderRows->update($aDataRows);
								
								$nIDRow		= $payed['id'];
								$sRowsName	= PREFIX_SALES_DOCS_ROWS.substr($nIDRow, 0, 6);
								$db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$real_sum}', paid_date = NOW() WHERE id = '{$nIDRow}' ");	
							}	
									
							if ( empty($nIDTran) ) {			
								// Салда на фирмите
								if ( ($nCurrentSaldo + $real_sum) < 0 ) {
									throw new Exception("Недостатъчно салдо по фирма {$sFirm}!!!", DBAPI_ERR_INVALID_PARAM);
								}

								$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$real_sum}' WHERE id = {$nIDSaldo} LIMIT 1");
							}

							$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$real_sum}' WHERE id_bank_account = {$nIDAccount} ");
							
							// SCHET!!!
							if ( $this->isValidID($nIDSchet) && $this->isValidID($nIDTnet) ) {
								// Проверка за съвместимост
								$table1 = "mp".substr($nIDSchet, 0, 6);
								$table2 = "mp".date("Ym");
								$status	= true;
								
								if ( $table1 != $table2 ) {
									$nLast = $oSync->moveDocument($nIDSchet);
									
									if ( !empty($nLast) ) {
										$nIDSchet				= date("Ym").zero_padding($nLast, 7);
										
										$aUpdate 				= array();
										$aUpdate['id'] 			= $nIDTnet;
										$aUpdate['id_schet_row']= $nIDSchet;
										
										$oSaleDocRows->update($aUpdate);
									}
								}
								
								// Месечни задължения/ДДС								
								$nMonthSum			= $real_sum > 0 ? $real_sum : 1;
								
								$aRows 				= array();
								$aRows['id'] 		= $nIDSchet;
								$aRows['sum'] 		= $nMonthSum;
								$aRows['smetka']	= $nIDAccount;

								$status = $oSync->payMonth($aRows);	
								
//								if ( !$status ) {
//									throw new Exception("Проблем при синхронизация със счет-а", DBAPI_ERR_FAILED_TRANS);
//								}
								
								// Еднократни задължения
								if ( !empty($nIDSingle) ) {
									$oSync->payNow($nIDSingle);
								}									
							}							
						}						
					}
					
					// Слагаме стойностите в описа че са платени
					$sSaleName	= PREFIX_SALES_DOCS.substr($nID, 0, 6);
					$nState		= $nAccState + $nRealTotal;
					
					$oRes 		= $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");
					$oRes 		= $db_finance->Execute("UPDATE {$db_name_finance}.{$sSaleName} SET orders_sum = orders_sum + '{$nRealTotal}', last_order_id = '{$nIDOrder}', last_order_time = NOW() WHERE id = '{$nID}'");		

					// Оправяме тоталите
					if ( $nRealTotal >= 0 ) {
						$sTypeNow = "earning";
					} else {
						$sTypeNow = "expense";
					}
											
					$aData						= array();
					$aData['id']				= $nIDOrder;
					$aData['order_type'] 		= $sTypeNow;
					$aData['order_sum']			= abs($nRealTotal);
					$aData['bank_account_id']	= $nIDAccount;
					$aData['account_sum']		= $nState;
					
					$oOrders->update($aData);					
				} elseif ( !empty($nWaitSum) ) { // Пълно/пропорционално погасяване на оставащата сума!  && ($nSum >= $nWaitSum)

					if ( $checkDDS ) {
						throw new Exception("Не е възможно пропорционално\nплащане!!! Моля, изберете плащане!", DBAPI_ERR_INVALID_PARAM);
					}
					
					if ( $nIDTran ) {
						throw new Exception("Не е възможно пропорционално\nплащане!!! Моля, изберете плащане!", DBAPI_ERR_INVALID_PARAM);
					}					

					$nIDOrder 	= 0;
					$nTotalSum	= 0;
					$nSumPay	= 0;
					$nDDSWait 	= sprintf("%01.2f", $nDDSWait);

					if ( empty($nIDTran) && !empty($nWaitSum) ) {
						if ( $nWaitSum >= 0 ) {
							$sTypeNow = "earning";
						} else {
							$sTypeNow = "expense";
						}
						
						$aData['id']				= 0;
						$aData['num']				= $nLastOrder;
						$aData['order_type'] 		= $sTypeNow;
						$aData['id_transfer']		= 0;
						$aData['order_date']		= time();
						$aData['order_sum']			= abs($nWaitSum);
						$aData['account_type']		= isset($aParams['paid_type']) ? $aParams['paid_type'] : "cash";
						$aData['id_person']			= $nIDUser;
						$aData['account_sum']		= $nAccountState + $nWaitSum;
						$aData['bank_account_id']	= $nIDAccount;
						$aData['doc_id']			= $nID;
						$aData['doc_type']			= "sale";
						$aData['note']				= "";
						$aData['created_user']		= $nIDUser;
						$aData['created_time']		= time();
						$aData['updated_user']		= $nIDUser;
						$aData['updated_time']		= time();
			
						$oOrders->update($aData);
	
						$nIDOrder = $aData['id'];

						$oRes = $db_system->Execute("UPDATE {$db_name_system}.system SET last_num_order = {$nLastOrder}");			
					}				
					
					if ( !empty($nDDS) && ($nDDSWait != "0.00") ) {
						$aFirm 		= array();
						$aTmp		= array();
						$nIDFirm 	= 0;
						$nIDOffice	= 0;
						
						if ( !isset($aDDS[0]['id_office']) || empty($aDDS[0]['id_office']) ) {
							throw new Exception("Направлението за ДДС е неизвестно!", DBAPI_ERR_INVALID_PARAM);
						} else {
							$nIDOffice = $aDDS[0]['id_office'];
						}
												
						$nIDFirm 			= $oFirms->getFirmByOffice($nIDOffice);
						
						$aSaldo				= $oSaldo->getSaldoByFirm($nIDFirm, 1);
						$nIDSaldo			= 0;
						$nCurrentSaldo		= 0;	

						if ( !empty($aSaldo) ) {
							$nIDSaldo 		= $aSaldo['id'];
						}							
							
						if ( !empty($nIDSaldo) ) {
							// Салдо на фирмата с изчакване!!!
							$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
			    			$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
						} else {
							throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
						}						

						if ( abs($nDDSWait) > abs($nSum) ) {
							$nSumPay 	= $nSum;
							$nSumTemp	= 0;
						} else {
							$nSumPay 	= $nDDSWait;
							$nSumTemp	= $nSum - $nDDSWait;
						}
						
						if ( ($nCurrentSaldo + $nSumPay) < 0 ) {
							throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
						}	
						
						// Наличност по сметка
						if ( !empty($nIDAccount) ) {
							$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
							$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
						} else {
							throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
						}			
							
						if ( ($nAccountState + $nSumPay) < 0 ) {
							throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
						}							
											
						$aDataRow	= array();
						$aDataRow['id'] 						= 0;
						$aDataRow['id_order'] 					= $nIDOrder;
						$aDataRow['id_doc_row'] 				= $nDDS;
						$aDataRow['id_office'] 					= $nIDOffice;
						$aDataRow['id_object'] 					= 0;
						$aDataRow['id_service'] 				= 0;
						$aDataRow['id_direction']				= 0;
						$aDataRow['id_nomenclature_earning'] 	= 0;
						$aDataRow['id_nomenclature_expense'] 	= 0;
						$aDataRow['id_saldo']					= $nIDSaldo;
						$aDataRow['id_bank']					= $nIDAccount;
						$aDataRow['saldo_state']				= !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo + $nSumPay;	
						$aDataRow['account_state']				= $nAccountState + $nSumPay;					
						$aDataRow['month'] 						= isset($aDDS[0]['month']) ? $aDDS[0]['month'] : date("Y-m-d");
						$aDataRow['type'] 						= "month";
						$aDataRow['paid_sum'] 					= $nSumPay;
						$aDataRow['is_dds'] 					= 1;
							
				 		$oOrderRows->update($aDataRow);		
						
				 		// Слагаме стойността за ДДС-то!
				 		$db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$nSumPay}', paid_date = NOW(), updated_user = {$nIDUser}, updated_time = NOW() WHERE id = '{$nDDS}' ");
						
				 		// Променяме салдото на фирмита, която е получател на ДДС-то!
						if ( empty($nIDTran) ) {	
							$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nSumPay}' WHERE id = {$nIDSaldo} LIMIT 1");
						}
						
						$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nSumPay}' WHERE id_bank_account = {$nIDAccount} ");
					}	

					$nTotalSum += $nSumPay;
					
					

					// След ДДС-то имаме остатък!!!
					$nTotal 	= sprintf("%01.2f", $nSumTemp);
					$aMagic		= array();
					$nBigTotal	= 0;
					
					if ( $nTotal != "0.00" ) {
						foreach( $aSaleRows as $val ) {
							$aFirm 		= array();
							$nIDFirm 	= 0;
							$nIDOffice	= 0;
							$nBigTotal	+= ($val['total_sum'] - $val['paid_sum']);
														
							if ( $val['total_sum'] != $val['paid_sum'] ) {
								if ( !isset($val['id_office']) || empty($val['id_office']) ) {
									throw new Exception("Има услуга без направление!", DBAPI_ERR_INVALID_PARAM);
								} else {
									$nIDOffice = $val['id_office'];
								}	
																
								//$aFirm 		= $oFirms->getFirmByOffice($nIDOffice);
								//$nIDFirm 	= isset($aFirm['id']) ? $aFirm['id'] : 0;
								$nIDFirm 	= $oFirms->getFirmByOffice($nIDOffice);
								
								// Групираме дължимите суми по фирми
								if ( isset($aMagic[$nIDFirm]) ) {
									$aMagic[$nIDFirm] += ($val['total_sum'] - $val['paid_sum']);									
								} else {
									$aMagic[$nIDFirm] = ($val['total_sum'] - $val['paid_sum']);
								}							
							}
						}
						
						$off = $nTotal / $nBigTotal;

						foreach ( $aMagic as $key => $st ) {
							$offset 	= sprintf("%01.2f", ($st * $off) );
							$aMagic[$key]	= $offset;
						}	
						
						foreach( $aSaleRows as $val ) {
							$aFirm 		= array();
							$nIDFirm 	= 0;
							$nIDOffice	= 0;
							$aDataRow	= array();
							$nIDRow 	= isset($val['id']) 		? $val['id'] 		 : 0;
							$nIDService	= isset($val['id_service']) ? $val['id_service'] : 0;
							$nRealSum	= 0;
							
							if ( $val['total_sum'] != $val['paid_sum'] ) {
								if ( !isset($val['id_office']) || empty($val['id_office']) ) {
									throw new Exception("Има услуга без направление!", DBAPI_ERR_INVALID_PARAM);
								} else {
									$nIDOffice = $val['id_office'];
								}

								$nIDDuty	= isset($val['id_duty']) 	? $val['id_duty']			: 0;	
								$sMonth		= isset($val['month']) 		? substr($val['month'], 0, 7)."-01"	: "0000-00-00";		
															
//								$aFirm 		= $oFirms->getFirmByOffice($nIDOffice);
//								$nIDFirm 	= isset($aFirm['id']) ? $aFirm['id'] : 0;

								$nIDFirm 	= $oFirms->getFirmByOffice($nIDOffice);
								$aTmp		= array();
								$aService	= $oServices->getService($nIDService);
								$nRSum  	= $val['total_sum'] - $val['paid_sum'];
								$nFreeSum	= $aMagic[$nIDFirm];
								
								$aSaldo				= $oSaldo->getSaldoByFirm($nIDFirm, 0);
								$nIDSaldo			= 0;
								$nCurrentSaldo		= 0;
								
								$nRealSum	= $nRSum * $off;
								$nTotalSum	+= $nRealSum;								
	
								if ( !empty($aSaldo) ) {
									$nIDSaldo 		= $aSaldo['id'];
								}							
								
								if ( !empty($nIDSaldo) ) {
									// Салдо на фирмата с изчакване!!!
									$oRes 			= $db_finance->Execute("SELECT sum FROM {$db_name_finance}.saldo WHERE id = {$nIDSaldo} LIMIT 1 FOR UPDATE");
				    				$nCurrentSaldo 	= !empty($oRes->fields['sum']) ? $oRes->fields['sum'] : 0;
								} else {
									throw new Exception("Неизвестно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
								}	
	
								if ( ($nCurrentSaldo + $nRealSum) < 0 ) {
									throw new Exception("Недостатъчно салдо по фирма!", DBAPI_ERR_INVALID_PARAM);
								}	

								// Наличност по сметка
								if ( !empty($nIDAccount) ) {
									$oRes 			= $db_finance->Execute("SELECT current_sum FROM {$db_name_finance}.account_states WHERE id_bank_account = {$nIDAccount} LIMIT 1 FOR UPDATE");
									$nAccountState 	= !empty($oRes->fields['current_sum']) ? $oRes->fields['current_sum'] : 0;				
								} else {
									throw new Exception("Неизвестнa сметка!", DBAPI_ERR_INVALID_PARAM);
								}			
									
								if ( ($nAccountState + $nRealSum) < 0 ) {
									throw new Exception("Нямате достатъчно наличност по сметката!!!\n", DBAPI_ERR_INVALID_PARAM);
								}								

								// Реално плащане
								if ( isset($val['type']) && ($val['type'] == "month") && !empty($nIDDuty) ) {
									$aTemp 	= array();
									$aTemp 	= $oServices->getServiceByID($nIDDuty);
									$fmod	= abs($val['total_sum'] - ($val['paid_sum'] + $nRealSum)); 
									
									if ( isset($aTemp['real_paid']) && ($aTemp['real_paid'] < $sMonth) && ($fmod < 0.09) ) {
										$aTempData 				= array();
										$aTempData['id'] 		= $nIDDuty;
										$aTempData['real_paid']	= $sMonth;

										$oServices->update($aTempData);
									}
								}
																
								$aDataRow['id'] 						= 0;
								$aDataRow['id_order'] 					= $nIDOrder;
								$aDataRow['id_doc_row'] 				= $nIDRow;
								$aDataRow['id_office'] 					= isset($val['id_office']) 			? $val['id_office'] 		: 0;
								$aDataRow['id_object'] 					= isset($val['id_object']) 			? $val['id_object'] 		: 0;
								$aDataRow['id_service'] 				= isset($val['id_service']) 		? $val['id_service'] 		: 0;
								$aDataRow['id_direction']				= 0;
								$aDataRow['id_nomenclature_earning'] 	= isset($aService['id_earning']) 	? $aService['id_earning'] 	: 0;
								$aDataRow['id_nomenclature_expense'] 	= 0;
								$aDataRow['id_saldo']					= $nIDSaldo;
								$aDataRow['id_bank']					= $nIDAccount;
								$aDataRow['saldo_state']				= !empty($nIDTran) ? $nCurrentSaldo : $nCurrentSaldo + $nRealSum;		
								$aDataRow['account_state']				= $nAccountState + $nRealSum;					
								$aDataRow['month'] 						= isset($val['month']) 				? $val['month'] 			: date("Y-m-d");
								$aDataRow['type'] 						= isset($val['type']) 				? $val['type'] 				: "month";
								$aDataRow['paid_sum'] 					= $nRealSum;
								$aDataRow['is_dds'] 					= isset($val['is_dds']) 			? $val['is_dds'] 			: 0;
								
								if ( !empty($nRealSum) ) {
						 			$oOrderRows->update($aDataRow);	
						 			
						 			// Обновяваме салдото до текуща стойност
						 			if ( empty($nIDTran) ) {
						 				$db_finance->Execute("UPDATE {$db_name_finance}.saldo SET sum = sum + '{$nRealSum}' WHERE id = {$nIDSaldo} LIMIT 1");
						 			}
		
						 			$db_finance->Execute("UPDATE {$db_name_finance}.$sRowsName SET paid_sum = paid_sum + '{$nRealSum}', paid_date = NOW(), updated_user = {$nIDUser}, updated_time = NOW() WHERE id = '{$nIDRow}' ");
						 			$db_finance->Execute("UPDATE {$db_name_finance}.account_states SET current_sum = current_sum + '{$nRealSum}' WHERE id_bank_account = {$nIDAccount} ");
								}
							}
						}
					}
					
					// Оправяме тоталите
					if ( $nTotalSum >= 0 ) {
						$sTypeNow = "earning";
					} else {
						$sTypeNow = "expense";
					}
											
					$aData					= array();
					$aData['id']			= $nIDOrder;
					$aData['order_type'] 	= $sTypeNow;
					$aData['order_sum']		= abs($nTotalSum);
					$aData['account_sum']	= $nAccState + $nTotalSum;
					
					$oOrders->update($aData);
					
					$oRes = $db_finance->Execute("UPDATE {$db_name_finance}.{$sSaleName} SET orders_sum = orders_sum + '{$nTotalSum}', last_order_id = '{$nIDOrder}', last_order_time = NOW(), updated_user = {$nIDUser}, updated_time = NOW() WHERE id = '{$nID}'");
					
					
					// SCHET!!!
					$aSchetRows = $oSaleDocRows->getSchetByIDDoc($nID);

					foreach ( $aSchetRows as $value ) {
						$nIDSchet 	= isset($value['id_schet_row']) ? $value['id_schet_row'] 	: 0;
						$nIDSingle 	= isset($value['id_schet']) 	? $value['id_schet'] 		: 0;
						$nIDTnet	= isset($value['id']) 			? $value['id'] 				: 0;	
						
						if ( $this->isValidID($nIDSchet) && $this->isValidID($nIDTnet) ) {
							// Проверка за съвместимост
							$table1 = "mp".substr($nIDSchet, 0, 6);
							$table2 = "mp".date("Ym");
							$status	= true;
								
							if ( $table1 != $table2 ) {
								$nLast = $oSync->moveDocument($nIDSchet);
									
								if ( !empty($nLast) ) {
									$nIDSchet				= date("Ym").zero_padding($nLast, 7);
										
									$aUpdate 				= array();
									$aUpdate['id'] 			= $nIDTnet;
									$aUpdate['id_schet_row']= $nIDSchet;
										
									$oSaleDocRows->update($aUpdate);
								}
							}
								
							// Месечни задължения/ДДС																
							$aRows 				= array();
							$aRows['id'] 		= $nIDSchet;
							$aRows['sum'] 		= '999999999';
							$aRows['smetka']	= $nIDAccount;

							$status = $oSync->payMonth($aRows);	
								
//							if ( !$status ) {
//								throw new Exception("Проблем при синхронизация със счет-а", DBAPI_ERR_FAILED_TRANS);
//							}
								
							// Еднократни задължения
							if ( !empty($nIDSingle) ) {
								$oSync->payNow($nIDSingle);
							}									
						}							
					}
				
					// Край на пълното плащане
				}
				
				if ( !empty($sErrMessage) ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();	
					
					throw new Exception($sErrMessage, DBAPI_ERR_FAILED_TRANS);			
				}			
				
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();				
			} catch (Exception $e) {
				$sMessage = $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				
				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}
			
			$_SESSION['userdata']['cbSmetkaOrder'] = isset($aParams['cbSmetkaOrder']) ? $aParams['cbSmetkaOrder'] : 0;
			
			$this->init( $oResponse );	
		}
		
		// remote method	
		public function getDutyObject(DBResponse $oResponse) {
			$sMonthDuty 	= Params::get("month_duty", "0000-00-00");
			//$nIDClient 		= Params::get("client_id", 0);
			$sClientEIN		= Params::get("client_ein", "");
			
			$sDelivererEIN	= Params::get("deliverer_ein", "");
			$sDelivererName	= Params::get("deliverer_name", "");
			
			$nIDObject		= Params::get("object_id", 0);
			$aRows			= Params::get("arr_rows", array());
			
			$doc_type		= Params::get("doc_type", "");

			// Проверка за коректно избран обект
			if ( empty($nIDObject) ) {
				throw new Exception("Изберете обект, за който ще се извършва търсене!!!", DBAPI_ERR_INVALID_PARAM);
			}			
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oFirms 		= new DBFirms();
			$oObjects		= new DBObjects();
			$oMonths		= new DBObjectServices();
			$oSingles		= new DBObjectsSingles();
			$oClients		= new DBClients();
			$oConcession	= new DBConcession();			

			$aClients		= array();
			$aObjects		= array();
			$aServices		= array();
			$aJurNames		= array();
			$aJur			= array();	
			$aConcession	= array();	
			$aConMonths		= array();
			$nIDConcession	= 0;
			$is_con 		= false;				
			
			$nIDClient		= 0;		
			
			// Процедура на добавяне на задължения към вече въведен клиент
			if ( !empty($aRows) ) {
				// Добавяме, но нямаме избран клиент - сърдим се!
				if ( empty($sClientEIN) ) {
					throw new Exception("Няма избран клиент!!!", DBAPI_ERR_INVALID_PARAM);
				} else {
					$aClients 	= $oClients->getClientByEIN($sClientEIN);
					$nIDClient	= isset($aClients['id']) && !empty($aClients['id']) ? $aClients['id'] : 0;
					
					// TODO: Тука да добавям/ъпдейтвам клиент
					if ( empty($nIDClient) ) {
						$aData 						= array();	
						$aData['client_id']			= 0;
						$aData['client_name'] 		= "";
						$aData['client_ein'] 		= "";
						$aData['client_ein_dds'] 	= "";
						$aData['client_address'] 	= "";
						$aData['client_mol'] 		= "";
						$aData['client_recipient'] 	= "";
						$aData['invoice_payment'] 	= "cash";
	
						$oResponse->SetFlexVar("client_info", $aData);
						
						//$oResponse->setAlert("Клиента не може да бъде намерен!!!");						
						//throw new Exception("Клиента не може да бъде намерен!!!", DBAPI_ERR_INVALID_PARAM);
					}
				}
			} else {
				// Търсим данни за клиент по зададен обект
				$oResponse->SetFlexVar("arr_rows", $aRows);
				
				// Правим запитване за клиента
				$aClients		= $oClients->getClientByObject($nIDObject);				
				$nIDClient		= isset($aClients['id']) && !empty($aClients['id']) ? $aClients['id'] : 0;
				$aClients		= array();		
				
				// Обекта не е привързан към клиент - сърдим се!!!!
				if ( empty($nIDClient) ) {
					$aData 						= array();	
					$aData['client_id']			= 0;
					$aData['client_name'] 		= "";
					$aData['client_ein'] 		= "";
					$aData['client_ein_dds'] 	= "";
					$aData['client_address'] 	= "";
					$aData['client_mol'] 		= "";
					$aData['client_recipient'] 	= "";
					$aData['invoice_payment'] 	= "cash";

					$oResponse->SetFlexVar("client_info", $aData);
					
					if ( ($doc_type == "faktura") ) {
						//$oResponse->setAlert("Избрания обект не е привързан към клиент!!!");
						//throw new Exception("Избрания обект не е привързан към клиент!!!", DBAPI_ERR_INVALID_PARAM);	
					}			
				} else {
		
					// Клиента е намерен, попълваме даните за него:
					$aClients					= $oClients->getByID($nIDClient);
					$type 						= isset($aClients['invoice_payment']) && !empty($aClients['invoice_payment']) ? $aClients['invoice_payment'] : "cash";	
					
					$aData 						= array();	
					$aData['client_id']			= $nIDClient;
					$aData['client_name'] 		= isset($aClients['name']) 					? $aClients['name'] 					: "";
					$aData['client_ein'] 		= isset($aClients['invoice_ein']) 			? $aClients['invoice_ein'] 				: "";
					$aData['client_ein_dds'] 	= isset($aClients['invoice_ein_dds']) 		? $aClients['invoice_ein_dds'] 			: "";
					$aData['client_address'] 	= isset($aClients['invoice_address']) 		? $aClients['invoice_address'] 			: "";
					$aData['client_mol'] 		= isset($aClients['invoice_mol']) 			? $aClients['invoice_mol'] 				: "";
					$aData['client_recipient'] 	= isset($aClients['invoice_recipient']) 	? $aClients['invoice_recipient']		: $aClients['invoice_mol'];	
					$aData['invoice_payment'] 	= $type;
					
					$oResponse->SetFlexVar("client_info", $aData);						
				}
			}
			
			$aFirms = $oFirms->getFirmsAsClient();
			
			if ( !empty($nIDClient) ) {

				$aClients		= array();
				$aClients		= $oClients->getByID($nIDClient);			
				
				$aObjects 		= $oObjects->getObjectsByClient($nIDClient);				
				
				if ( !empty($aRows) ) {
					if ( !$test = $oClients->isObjectAttachedToClient($nIDClient, $nIDObject) ) {
						//$oResponse->setAlert("Внимание!!!\nОбекта ще бъде привързан към избрания клиент при потвърждаване\n!!!");
					}					
				}
			} else {
				if ( ($doc_type == "faktura") ) {
					//$oResponse->setAlert("\nВнимание!!!\nДокумент от тип фактура не можe да бъде издаден без валиден клиент!!!");
					//throw new Exception("Няма избран клиент!!!", DBAPI_ERR_INVALID_PARAM);
				}				
			}
			
			if ( empty($aRows) ) {
				$aObjects	= array();
				$aObjects[] = array("id" => $nIDObject);
			} else {
				$aObjects[] = array("id" => $nIDObject);
			}

			foreach ( $aRows as $key => $dump ) {
				if ( is_object($dump) && isset($dump->addRow) && $dump->addRow ) {
					unset($aRows[$key]);
				}
			}
			
			foreach ( $aObjects as $val ) {		
				$nIDObject 	= $val['id'];
				$aTmp 		= array();
				$aTmp2 		= array();

				$aTmp		= $oMonths->getDutyByObject($nIDObject, $sMonthDuty);
				$aTmp2		= $oSingles->getDutyByObject($nIDObject, $sMonthDuty);
				
				foreach ( $aTmp as $srv ) {
					$aJur		= array();
					$aJur 		= $oMonths->getJur($srv['id_duty']);
					$sJurName 	= $aJur['jur_name'];	
	
					$aJurNames[$sJurName] = $sJurName;	

					if ( $sJurName == $sDelivererName ) {
						//$aServices[] = $srv;	
						$aRows[] = $srv;
						
						// Отстъпки
						$time = strtotime($srv['month']);
						
						$dNow = mktime( 0, 0, 0, date("m"), 1, date("Y") );
						$dCon = mktime( 0, 0, 0, date("m", $time), 1, date("Y", $time) );
						
						if ( $dCon >= $dNow ) {
							$aConcession[$srv['id_duty']][] = $srv['month'];
						}						
					}				
				}
						
				unset($srv);				
					
				foreach ( $aTmp2 as $srv ) {
					$aJur		= array();
					$aJur 		= $oSingles->getJur($srv['id_duty']);
					$sJurName 	= $aJur['jur_name'];	
	
					$aJurNames[$sJurName] = $sJurName;	
						
					if ( $sJurName == $sDelivererName ) {
						//$aServices[] = $srv;	
						$aRows[] = $srv;
					}			
				}				
			}
			
			foreach ( $aConcession as $key => $val ) {
				$cnt 			= count($val);
				$nIDConcession	= 0;
				$aData			= array();
				$aTmp			= array();
				$nIDConcession	= $this->getConcession($cnt);
				
				if ( !empty($nIDConcession) ) {
					$aData 			= $oMonths->getRecord($key);
					$aGrrr 			= $oConcession->getRecord($nIDConcession);
					$is_con 		= true;
						
					$aTmp['id']						= 0;
					$aTmp['id_duty']				= $key;
					$aTmp['id_object'] 				= $aData['id_object'];
					$aTmp['firm_region']['rcode']	= $aData['id_office'];
					$aTmp['firm_region']['region']	= 0;
					$aTmp['firm_region']['fcode']	= 0;
					$aTmp['firm_region']['firm']	= "";							
					$aTmp['id_service'] 			= $aGrrr['id_service'];
					$aTmp['month'] 					= date("Y-m")."-01";
					$aTmp['service_name'] 			= "Отстъпка: ".$aData['service_name'];
					$aTmp['object_name'] 			= $aGrrr['name'];
					$aTmp['single_price'] 			= floatval((($aData['total_sum'] / 1.2) * -1 * $cnt * $aGrrr['percent']) / 100);
					$aTmp['quantity'] 				= 1;
					$aTmp['total_sum'] 				= floatval((($aData['total_sum'] / 1.2) * -1 * $cnt * $aGrrr['percent']) / 100);
					$aTmp['payed']					= floatval(0);
					$aTmp['type']					= "free";
					$aTmp['for_payment']			= true;

					$aRows[] = $aTmp;				
				}
			}
							
			if ( $is_con ) {
				$oResponse->setAlert("\nИма предложени отстъпки!!!");
			}				
	
			foreach ( $aFirms as &$firms ) {
				if ( isset($firms['name']) && in_array($firms['name'], $aJurNames) ) {
					$firms['haveDuty'] = true;
				} else {
					$firms['haveDuty'] = false;
				}
			}
				
			// Юридически лица
			$oResponse->SetFlexVar("arr_dostavchici", $aFirms);
			
			$oResponse->SetFlexControl("cbPoluchatel");
			$oResponse->SetFlexControlDefaultValue("cbPoluchatel", "title", $sDelivererName);
				
			// Зареждане на "забележка" ако има такава
			$oResponse->SetFlexControl("note");
			$oResponse->SetFlexControlAttr("note", "text", isset($aClients['note']) ? $aClients['note'] : "");				
			
			// Зареждане на "Предпочитан начин на плащане"
			$type = isset($aClients['invoice_payment']) && !empty($aClients['invoice_payment']) ? $aClients['invoice_payment'] : "cash";
			$sMessage = "Клиента предпочита: ";
			
			switch ($type) {
				case "cash": 	
					$sMessage .= "Фактура в брой";
					
					$oResponse->SetFlexControl("faktura");
					$oResponse->SetFlexControlAttr("faktura", "selected", "true");							
					
					$oResponse->SetFlexControl("cash");
					$oResponse->SetFlexControlAttr("cash", "selected", "true");	
				break;
				
				case "bank": 	
					$sMessage .= "Фактура по банка";
				
					$oResponse->SetFlexControl("faktura");
					$oResponse->SetFlexControlAttr("faktura", "selected", "true");

					$oResponse->SetFlexControl("bank");
					$oResponse->SetFlexControlAttr("bank", "selected", "true");										
				break;
				
				case "receipt": 
					$sMessage .= "Квитанция";
					
					$oResponse->SetFlexControl("kvitanciq");
					$oResponse->SetFlexControlAttr("kvitanciq", "selected", "true");
					
					$oResponse->SetFlexControl("cash");
					$oResponse->SetFlexControlAttr("cash", "selected", "true");					
				break;
				
				default:		
					$sMessage .= "Фактура в брой";
					
					$oResponse->SetFlexControl("faktura");
					$oResponse->SetFlexControlAttr("faktura", "selected", "true");					
					
					$oResponse->SetFlexControl("cash");
					$oResponse->SetFlexControlAttr("cash", "selected", "true");						
				break;
			}
			
			$oResponse->SetFlexControl("lblInvoicePayment");
			$oResponse->SetFlexControlAttr("lblInvoicePayment", "text", $sMessage);		
						
			$oResponse->SetFlexVar("arr_rows", $aRows);			
		
			$oResponse->printResponse();			
		}	
		
		
		public function gen_pdf(DBResponse $oResponse) {
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;

			$oDBSalesDocs	= new DBSalesDocs();
			$aSaleDoc		= array();

			
			$aParams		= Params::getAll();
			$nID 			= isset($aParams['hiddenParams']->nID) ? $aParams['hiddenParams']->nID : 0;	
			
			Params::set("id", $nID);			

			$oDBSalesDocs->getRecord($nID, $aSaleDoc);

			if ( $aSaleDoc['gen_pdf'] == 0 && $aSaleDoc['doc_status'] == 'final' ) {				
				$oSaleDocPDF = new SaleDocPDF("P");				
				$oSaleDocPDF->PrintReport($nID, '', $aSaleDoc['view_type'], 1);

				$aSaleDoc['gen_pdf'] = 1;
				$oDBSalesDocs->update($aSaleDoc);
			}

			$oResponse->setAlert( ArrayToString("PDF документа беше генериран!") );
		
			$oResponse->printResponse();
		}
		
		public function gen_pdf2() {
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$sale_doc_edit	= false;
			$sale_doc_grant	= false;
			
			// При пълно право за редакция - добавяме и право за преглед и редакция
			if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$sale_doc_edit 	= true;
				$sale_doc_grant = true;
			} else {
				$sale_doc_grant = false;
			}			

			$oDBSalesDocs	= new DBSalesDocs();
			$aSaleDoc		= array();

			$aParams		= Params::getAll();
			$nID 			= isset($aParams['nID']) ? $aParams['nID'] : 0;	
			
			Params::set("id", $nID);			

			$oDBSalesDocs->getRecord($nID, $aSaleDoc);

			if ( ($aSaleDoc['doc_status'] == 'final') || $sale_doc_grant ) {				
				$oSaleDocPDF = new SaleDocPDF("P");				
				$oSaleDocPDF->PrintReport($nID, '', $aSaleDoc['view_type'], 0);

				$aSaleDoc['gen_pdf'] = 1;
				$oDBSalesDocs->update($aSaleDoc);
			}
			
			return true;
		}	
		
		// remote method	
		public function annulment( DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_sod;
			
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$aParams		= Params::getAll();
			$nID 			= isset($aParams['nId']) ? $aParams['nId'] : 0;	
			$sale_doc_edit	= false;
			$sale_doc_grant	= false;

			Params::set("id", $nID);		
			
			// При право за редакция - добавяме и право за преглед
			if ( in_array('sale_doc_edit', $_SESSION['userdata']['access_right_levels']) ) {
				$sale_doc_edit = true;
			} else {
				$sale_doc_edit = false;
			}
			
			// При пълно право за редакция - добавяме и право за преглед и редакция
			if ( in_array('sale_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$sale_doc_edit 	= true;
				$sale_doc_grant = true;
			} else {
				$sale_doc_grant = false;
			}
			
			// Друго право за редакция...
			if ( in_array('orders_doc_grant', $_SESSION['userdata']['access_right_levels']) ) {
				$sale_doc_edit 	= true;
				$sale_doc_grant = true;
			}			
			
			$oSalesDocs		= new DBSalesDocs();
			$oSalesRows		= new DBSalesDocsRows();
			$oOrders		= new DBOrders();
			$oOrderRows		= new DBOrdersRows();
			$oServices		= new DBObjectServices();
			$oSingles		= new DBObjectsSingles();
			$oFirms			= new DBFirms();
			$oSync			= new DBSyncMoney();
			$oSaldo			= new DBSaldo();

			$aSaleDoc		= array();	
			$aSaleRows		= array();
			$aSalesDoc		= array();
			$aOrder			= array();
			$aOrders		= array();
			$aService		= array();	
			$aMonth			= array();
			$sErrMessage	= "";	
			$nTotalSum		= 0;
			$sTimeNow		= date("Ym");
			$sTimeDoc		= substr($nID, 0, 6);
			$nIDTran		= 0;
			$nIDOrder		= 0;
			
			if ( !$sale_doc_edit ) {
				throw new Exception("Нямате достатъчно права за операцията!!!", DBAPI_ERR_INVALID_PARAM);
			}

			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа вече е анулиран!", DBAPI_ERR_INVALID_PARAM);
			}
						
			if ( !$this->isValidID($nID) ) {
				throw new Exception("Невалиден документ {$nID}!!!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			if ( !empty($nID) ) {
				$aSaleDoc 	= $oSalesDocs->getDoc($nID);
				$lock 		= isset($aSaleDoc['doc_type']) && $aSaleDoc['doc_type'] == "oprostena" ? false : true;
				
				if ( ($sTimeNow != $sTimeDoc) && $lock ) {
					throw new Exception("Документа е издаден в предходен месец!", DBAPI_ERR_INVALID_PARAM);
				}									
			}			
			
			$nIDTran	= $oSalesRows->checkForTransfer($nID);

			$db_finance->StartTrans();	
			$db_system->StartTrans();	
			$db_sod->StartTrans();	
			
			try {			
				$aOrders 	= $oSalesRows->getOrdersByDoc( $nID );
				
				if ( !empty($aOrders) && !$sale_doc_grant ) {
					throw new Exception("Нямате достатъчно права за операцията!!!", DBAPI_ERR_INVALID_PARAM);
				}
				
				foreach ( $aOrders as $aval ) {
					$nIDOrder = 0;
					
					if ( isset($aval['order_status']) && ($aval['order_status'] == "active") ) {
						$nIDOrder	= $aval['id'];
						
						if ( $this->isValidID($nIDOrder) ) {
							$oOrders->annulment($oResponse, $nIDOrder);
						}
						
					}
				}	
				
				// Връщаме падежите
				$aSalesDoc = $oSalesRows->getByIDSaleDoc($nID);
				
				foreach ( $aSalesDoc as $aDocVal ) {
					$nIDRow = isset($aDocVal['id_duty_row']) ? $aDocVal['id_duty_row'] : 0;
										
					// Генериране на месечни задължения
					if ( !empty($nIDRow) && isset($aDocVal['type']) && ($aDocVal['type'] == "month") ) {
						if ( isset($aMonth[$nIDRow]) ) {
							if ( $aMonth[$nIDRow] > $aDocVal['month'] ) {
								$aMonth[$nIDRow] = $aDocVal['month'];
							}
						} else $aMonth[$nIDRow] = $aDocVal['month'];
					}	
						
					// Анулиране на еднократни задължения
					if ( !empty($nIDRow) && isset($aDocVal['type']) && ($aDocVal['type'] == "single") ) {
						$aSingleData				= array();
						$aSingleData['id']			= $nIDRow;
						$aSingleData['paid_date']	= "0000-00-00";
						$aSingleData['id_sale_doc']	= 0;
						
						$oSingles->update($aSingleData);
					}
				}
				
				unset($aDocVal);
				
				// Анулиране на месечни задължения
				foreach ( $aMonth as $key => $aDocVal ) {
					$aTmp 	= array();
					$aData	= array();
					$aTmp	= explode("-", $aDocVal);
					
					if ( isset($aTmp[2]) ) {
						$day = $aTmp[2];
						$mon = $aTmp[1];
						$yer = $aTmp[0];
						
						if ( $day == 1 ) {
							$newDate = date( "Y-m-d", mktime(0, 0, 0, $mon - 1, $day, $yer) );
						} else {
							$newDate = $aDocVal;
						}
						
						$aData['id'] 		= $key;
						$aData['last_paid']	= $newDate;
                        $aData['real_paid']	= $newDate;
						
						$oServices->update($aData);
					}
				}				
	
				$sDocName	= PREFIX_SALES_DOCS.substr($nID, 0, 6);
				
				$db_finance->Execute("UPDATE {$db_name_finance}.$sDocName SET doc_status = 'canceled', updated_user = '{$nIDUser}', updated_time = NOW() WHERE id = {$nID} ");
				
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();
				$db_sod->CompleteTrans();

				$oResponse->SetFlexVar("annulment_status", true);			
			} catch (Exception $e) {
				$sMessage 	= $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				$db_sod->FailTrans();
				
				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}	

			$this->init($oResponse);	
		}
		
		// remote method	
		public function getDocument( DBResponse $oResponse ) {
			$nID		= Params::get("id", 0);
			
			if ( !empty($nID) && $this->isValidID($nID) ) {
				Params::set("id", strval($nID));
				$oResponse->SetHiddenParam("nID", strval($nID));
				
			} else {
				throw new Exception("Грешка: Документа не може да бъде открит!!!", DBAPI_ERR_INVALID_PARAM);
			}
		
			$this->init($oResponse);
		}
		
		public function updateSingle( DBResponse $oResponse ) {
			$nID		= Params::get("id_duty", 0);
			$nIDOffice	= Params::get("rcode", 0);
			
			$oSingles	= new DBObjectsSingles();
			
			if ( empty($nID) ) {
				throw new Exception("Грешка при определяне на задължението!!!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( empty($nIDOffice) ) {
				throw new Exception("Моля, изберете регион!!!", DBAPI_ERR_INVALID_PARAM);
			}
						
			$aData				= array();
			$aData['id']		= $nID;
			$aData['id_office']	= $nIDOffice;
			
			$oSingles->update($aData);
			
			$oResponse->toAMF();	
		}
		
		// remote method
		public function debitAdvice( DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_sod, $db_name_sod;
			
			$aParams		= Params::getAll();
			$nID 			= isset($aParams['nId']) ? $aParams['nId'] : 0;
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			
			$oSalesDocs		= new DBSalesDocs();
			$oSalesRows		= new DBSalesDocsRows();
			
			$aSaleDoc		= array();	
			$aSaleRows		= array();		
			$sErrMessage	= "";	
			$nLastNum		= 0;
						
			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа вече е анулиран!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( !$this->isValidID($nID) ) {
				throw new Exception("Невалиден документ {$nID}!!!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			Params::set("id", $nID);	

			$db_finance->StartTrans();	
			$db_system->StartTrans();		
			
			try {			
				$oSalesDocs->getRecord($nID, $aSaleDoc);
				
				$jur_name = isset($aSaleDoc['deliverer_name']) ? $aSaleDoc['deliverer_name'] : "";
				
				if ( isset($aSaleDoc['is_advice']) && !empty($aSaleDoc['is_advice']) ) {
					throw new Exception("В/у този документ вече е било издадено известие!!!");
				}
				
				if ( isset($aSaleDoc['doc_type']) && ($aSaleDoc['doc_type'] != "faktura") ) {
					throw new Exception("Не може да бъде издадено известие на документ от такъв тип!!!", DBAPI_ERR_INVALID_PARAM);
				}				

				if ( isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ) {
					$oRes 			= $db_sod->Execute("SELECT last_num_sale_doc FROM {$db_name_sod}.firms WHERE LOWER(jur_name) = LOWER('{$jur_name}') FOR UPDATE");
					$nLastNum	 	= !empty($oRes->fields['last_num_sale_doc']) ? $oRes->fields['last_num_sale_doc'] + 1 : 0;	
				}
				
				if ( isset($aSaleDoc['id']) && !empty($aSaleDoc['id']) ) {
					$nIDRow				= $aSaleDoc['id'];
					
					$aSaleDoc['id']					= 0;
					$aSaleDoc['doc_num']			= $nLastNum;
					$aSaleDoc['doc_date']			= time();
					$aSaleDoc['doc_type']			= "debitno izvestie";
					$aSaleDoc['doc_status']			= "proforma";
					$aSaleDoc['orders_sum']			= 0;
					$aSaleDoc['last_order_id']		= 0;
					$aSaleDoc['last_order_time']	= "0000-00-00";
					$aSaleDoc['is_advice']			= 0;
					$aSaleDoc['id_advice']			= $nIDRow;
					$aSaleDoc['gen_pdf']			= 0;
					$aSaleDoc['exported']			= 0;
					$aSaleDoc['is_advice'] 			= 0;
					$aSaleDoc['created_user']		= $nIDUser;
					$aSaleDoc['created_time']		= time();
					
					$oSalesDocs->update($aSaleDoc);
					
					$nID	= isset($aSaleDoc['id']) ? $aSaleDoc['id'] : 0;
					Params::set("id", $nID);
					
					$aTmp 				= array();
					$aTmp['id'] 		= $nIDRow;
					$aTmp['is_advice'] 	= 1;
					$aTmp['id_advice']	= $nID;
					
					$oSalesDocs->update($aTmp);					
					
					if ( isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ) {
						$db_sod->Execute("UPDATE {$db_name_sod}.firms SET last_num_sale_doc = {$nLastNum} WHERE LOWER(jur_name) = LOWER('{$jur_name}') ");	
					}
				}									

				if ( !empty($sErrMessage) ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();
					
					throw new Exception("Грешка: ".$sErrMessage, DBAPI_ERR_FAILED_TRANS);
				}
					
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();	
			} catch (Exception $e) {
				$sMessage 	= $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				
				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}	

			$this->init($oResponse);
		}
		
		
		// remote method
		public function creditAdvice( DBResponse $oResponse ) {
			global $db_finance, $db_system, $db_name_finance, $db_name_system, $db_sod, $db_name_sod;
			
			$aParams		= Params::getAll();
			$nID 			= isset($aParams['nId']) ? $aParams['nId'] : 0;
			$nIDUser 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person']  : 0;
			$oSalesDocs		= new DBSalesDocs();
			$oSalesRows		= new DBSalesDocsRows();
			
			$aSaleDoc		= array();	
			$aSaleRows		= array();		
			$sErrMessage	= "";	
			$nLastNum		= 0;
						
			if ( isset($aParams['hiddenParams']->doc_status) && ($aParams['hiddenParams']->doc_status == "canceled") ) {
				throw new Exception("Документа вече е анулиран!", DBAPI_ERR_INVALID_PARAM);
			}
			
			if ( !$this->isValidID($nID) ) {
				throw new Exception("Невалиден документ {$nID}!!!", DBAPI_ERR_INVALID_PARAM);
			}	
			
			Params::set("id", $nID);	

			//$oResponse->setAlert(ArrayToString($aParams));
			$db_finance->StartTrans();	
			$db_system->StartTrans();		
			
			try {			
				$oSalesDocs->getRecord($nID, $aSaleDoc);
				
				$jur_name = isset($aSaleDoc['deliverer_name']) ? $aSaleDoc['deliverer_name'] : "";
				
				if ( isset($aSaleDoc['is_advice']) && !empty($aSaleDoc['is_advice']) ) {
					throw new Exception("В/у този документ вече е било издадено известие!!!");
				}
				
				if ( isset($aSaleDoc['doc_type']) && ($aSaleDoc['doc_type'] != "faktura") ) {
					throw new Exception("Не може да бъде издадено известие на документ от такъв тип!!!", DBAPI_ERR_INVALID_PARAM);
				}				

				if ( isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ) {
					$oRes 			= $db_sod->Execute("SELECT last_num_sale_doc FROM {$db_name_sod}.firms WHERE LOWER(jur_name) = LOWER('{$jur_name}') FOR UPDATE");
					$nLastNum	 	= !empty($oRes->fields['last_num_sale_doc']) ? $oRes->fields['last_num_sale_doc'] + 1 : 0;	
				}
				
				if ( isset($aSaleDoc['id']) && !empty($aSaleDoc['id']) ) {
					$nIDRow				= $aSaleDoc['id'];
					
					$aSaleDoc['id']					= 0;
					$aSaleDoc['doc_num']			= $nLastNum;
					$aSaleDoc['doc_date']			= time();
					$aSaleDoc['doc_type']			= "kreditno izvestie";
					$aSaleDoc['doc_status']			= "proforma";
					$aSaleDoc['orders_sum']			= 0;
					$aSaleDoc['last_order_id']		= 0;
					$aSaleDoc['last_order_time']	= "0000-00-00";
					$aSaleDoc['is_advice']			= 0;
					$aSaleDoc['id_advice']			= $nIDRow;
					$aSaleDoc['gen_pdf']			= 0;
					$aSaleDoc['exported']			= 0;
					$aSaleDoc['is_advice'] 			= 0;
					$aSaleDoc['created_user']		= $nIDUser;
					$aSaleDoc['created_time']		= time();
					
					$oSalesDocs->update($aSaleDoc);
					
					$nID	= isset($aSaleDoc['id']) ? $aSaleDoc['id'] : 0;
					Params::set("id", $nID);
					
					$aTmp 				= array();
					$aTmp['id'] 		= $nIDRow;
					$aTmp['is_advice'] 	= 1;
					$aTmp['id_advice']	= $nID;
					
					$oSalesDocs->update($aTmp);					
					
					if ( isset($aSaleDoc['deliverer_name']) && !empty($aSaleDoc['deliverer_name']) ) {
						$db_sod->Execute("UPDATE {$db_name_sod}.firms SET last_num_sale_doc = {$nLastNum} WHERE LOWER(jur_name) = LOWER('{$jur_name}') ");	
					}					
				}									

				if ( !empty($sErrMessage) ) {
					$db_finance->FailTrans();
					$db_system->FailTrans();
					
					throw new Exception("Грешка: ".$sErrMessage, DBAPI_ERR_FAILED_TRANS);
				}
					
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();	
			} catch (Exception $e) {
				$sMessage 	= $e->getMessage();
				
				$db_finance->FailTrans();
				$db_system->FailTrans();
				
				throw new Exception("Грешка: ".$sMessage, DBAPI_ERR_FAILED_TRANS);
			}	

			$this->init($oResponse);
		}		
	}
?>