<?php
	class ApiAdminServices {

		public function init( DBResponse $oResponse ) {
			$oFirms 		= new DBFirms();
			$oPositions		= new DBPositionsNC();
			$oEarnings		= new DBNomenclaturesEarnings();
			$oService		= new DBServicesReceipt();
			$oServiceTree	= new DBServicesReceiptTree();
			$oActivity		= new DBActivitiesOperations();
			
			$nID 			= Params::get("receipt_id", 0);	
			$nIDFirm		= Params::get("id_firm", 0);	
			$nIDOffice		= Params::get("id_office", 0);
			$nIDObject		= Params::get("id_object", 0);	

			$aFirms			= array();
			$aPositions 	= array();
			$aService		= array();
			$aEarnings 		= array();
			$aExpenses		= array();
			$aActivies		= array();
			$aOperations	= array();
			$aReceipt		= array();
			$aReceiptRows	= array();
			$aReceiptData	= array();
			$aData			= array();
			
			$aReceiptRows = $oServiceTree->getServiceAttributesByID($nID);
			$aReceiptData = $oServiceTree->getDataByID($nID);
			
			
			if ( isset($aReceiptData['receipt_name']) && !empty($aReceiptData['receipt_name']) ) {
				$oResponse->SetFlexVar("receipt_name", $aReceiptData['receipt_name']);
			}
			
			if ( isset($aReceiptData['id_object']) && !empty($aReceiptData['id_object']) ) {
				$oResponse->SetFlexVar("receipt_object_id", $aReceiptData['id_object']);
				$oResponse->SetFlexVar("receipt_object_name", $aReceiptData['object_name']);
			}			
			
			if ( isset($aReceiptData['id_firm']) && !empty($aReceiptData['id_firm']) ) {
//				$oResponse->SetFlexControl("receipt_firm_id");
//				$oResponse->SetFlexControlDefaultValue("receipt_firm_id", "id", $aReceiptData['id_firm']);
				$oResponse->SetFlexVar("receipt_firm_id", $aReceiptData['id_firm']);
			}	
			
			if ( isset($aReceiptData['id_office']) && !empty($aReceiptData['id_office']) ) {
				$oResponse->SetFlexVar("receipt_region_id", $aReceiptData['id_office']);
			}						
			
			foreach ( $aReceiptRows as $aServiceData ) {
				$aData[$nID]['id'] 				= $aServiceData['id_service'];
				$aData[$nID]['name'] 			= $aServiceData['service_name'];
				$aData[$nID]['type'] 			= "service";
				$aData[$nID]['earning_correction_id']	= $aServiceData['id_correction'];
				
				$aData[$nID]['earning_id']		= $aServiceData['id_earning'];
				$aData[$nID]['expense_id']		= $aServiceData['id_expense'];
				$aData[$nID]['position_id']		= $aServiceData['id_position'];
				$aData[$nID]['region_id'] 		= $aServiceData['id_office'];
				$aData[$nID]['firm_id'] 		= $aServiceData['id_firm'];
				$aData[$nID]['price'] 			= $aServiceData['price'];
				$aData[$nID]['price_type'] 		= $aServiceData['price_type'];		
				$aData[$nID]['office_option']	= $aServiceData['office_option'];						
					
				if ( isset($aServiceData['id']) && !empty($aServiceData['id']) ) {
					if ( $aServiceData['type'] == "activity" ) {
						$aData[$nID]['children'][$aServiceData['id']]['id'] 	= $aServiceData['id_activity'];
						$aData[$nID]['children'][$aServiceData['id']]['name'] 	= $aServiceData['activity_name'];
						$aData[$nID]['children'][$aServiceData['id']]['type'] 	= "activity";
						
						$p_type = 0;
						$r_type = 0;
								
						switch ($aServiceData['aprice_type']) {
							case "lv": 		$p_type = 0; 	break;
							case "percent": $p_type = 1; 	break;	
							default: 		$p_type = 1; 	break;							
						}
								
						switch ($aServiceData['aoffice_option']) {
							case "always_this": 	$r_type = 0;	break;
							case "from_object":		$r_type = 1;	break;
							case "default_this":	$r_type = 2;	break;										
							default:				$r_type = 0;	break;							
						}				

						$aData[$nID]['children'][$aServiceData['id']]['earning_id']		= $aServiceData['aid_earning'];
						$aData[$nID]['children'][$aServiceData['id']]['expense_id']		= $aServiceData['aid_expense'];
						$aData[$nID]['children'][$aServiceData['id']]['position_id']	= $aServiceData['aid_position'];
						$aData[$nID]['children'][$aServiceData['id']]['region_id'] 		= $aServiceData['aid_office'];
						$aData[$nID]['children'][$aServiceData['id']]['firm_id'] 		= $aServiceData['aid_firm'];
						$aData[$nID]['children'][$aServiceData['id']]['price'] 			= $aServiceData['aprice'];
						$aData[$nID]['children'][$aServiceData['id']]['price_type'] 	= $p_type;		
						$aData[$nID]['children'][$aServiceData['id']]['region_type']	= $r_type;							
					} else {
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['id'] 			= $aServiceData['id_activity'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['name'] 			= $aServiceData['activity_name'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['type'] 			= "operation";
						
						$p_type = 0;
						$r_type = 0;
								
						switch ($aServiceData['aprice_type']) {
							case "lv": 		$p_type = 0; 	break;
							case "percent": $p_type = 1; 	break;	
							default: 		$p_type = 1; 	break;							
						}
								
						switch ($aServiceData['aoffice_option']) {
							case "always_this": 	$r_type = 0;	break;
							case "from_object":		$r_type = 1;	break;
							case "default_this":	$r_type = 2;	break;										
							default:				$r_type = 0;	break;							
						}						
						
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['earning_id']		= $aServiceData['aid_earning'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['expense_id']		= $aServiceData['aid_expense'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['position_id']	= $aServiceData['aid_position'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['region_id'] 		= $aServiceData['aid_office'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['firm_id'] 		= $aServiceData['aid_firm'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['price'] 			= $aServiceData['aprice'];
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['price_type'] 	= $p_type;		
						$aData[$nID]['children'][$aServiceData['id_parent']]['children'][$aServiceData['id']]['region_type']	= $r_type;							
					}
				}
			}
			
			$oResponse->SetFlexVar("arr_receipt", $aData);	

			// de e kirikicata?
			$aFirms 	= $oFirms->getFirmsByOfficeAll();
			$oResponse->SetFlexVar("arr_firms_regions", $aFirms);

			$aPositions = $oPositions->getPositions();
			$oResponse->SetFlexVar("arr_positions", $aPositions);
			
			$aEarnings 	= $oEarnings->getEarnings();
			$oResponse->SetFlexVar("arr_earnings", $aEarnings);
			
			$aExpenses = $oEarnings->getExpenses();
			$oResponse->SetFlexVar("arr_expenses", $aExpenses);
			
			$aService	= $oService->getAllServiceReceipts();
			$oResponse->SetFlexVar("arr_service", $aService);
			
			$aActivies	= $oActivity->getActiviesForReceipts();
			$oResponse->SetFlexVar("arr_activity", $aActivies);
			
			$aOperations= $oActivity->getOperationsForReceipts();
			$oResponse->SetFlexVar("arr_operation", $aOperations);			
			
			$oResponse->printResponse();
		}
		
		public function save( DBResponse $oResponse ) {
			$nID 		= Params::get("receipt_id", 		0);
			$nIDFirm	= Params::get("receipt_firm_id", 	0);
			$nIDOffice	= Params::get("receipt_region_id", 	0);
			$nIDObject	= Params::get("receipt_object_id", 	0);
			$sName		= Params::get("receipt_name", 		"");
			$aRows		= Params::get("arr_receipt", 		array());

			$nIDFirm	= $nIDFirm > 0 		? $nIDFirm 		: 0;
			$nIDOffice	= $nIDOffice > 0 	? $nIDOffice 	: 0;
			$nIDObject	= $nIDObject > 0 	? $nIDObject 	: 0;
			
			$aData			= array();
			$aReceiptData	= array();
			$nIDAct			= 0;
			$nIDop			= 0;
			$nIDService		= 0;
			$nCheck			= 0;
			
			$oServiceTree	= new DBServicesReceiptTree();
			$aReceiptData 	= $oServiceTree->getDataByID($nID);
			
			if ( isset($aRows[0]) && is_object($aRows[0]) ) {
				$nIDService = isset($aRows[0]->id) ? $aRows[0]->id : 0;
			} else {
				$nIDService = isset($aRows[0]['id']) ? $aRows[0]['id'] : 0;
			}
			
			$nCheck 		= $oServiceTree->checkIsExist( $nIDService, $nIDFirm, $nIDOffice, $nIDObject );
			
			if ( empty($nID) && !empty($nCheck) ) {
				throw new Exception("Вече има добавена услуга с това направление!!!", DBAPI_ERR_INVALID_PARAM);
			}
			
			$oServiceTree->deleteByID($nID);
			
			$nID 		= 0;
			
			if ( empty($nID) ) {
				$aData['id'] 			= 0;
				$aData['name'] 			= $sName;
				$aData['service_type'] 	= "service";
				$aData['id_service'] 	= 0;
				$aData['id_parent'] 	= 0;
				$aData['id_activity'] 	= 0;
				$aData['is_leaf'] 		= 1;
				$aData['id_office'] 	= $nIDOffice;
				$aData['id_firm'] 		= $nIDFirm;
				$aData['id_object'] 	= $nIDObject;
				$aData['id_service'] 	= isset($aReceiptData['id_service']) ? $aReceiptData['id_service'] : 0;
				
				$oServiceTree->update($aData);
				
				if ( isset($aData['id']) && !empty($aData['id']) ) {
					$nID = $aData['id'];
					Params::set("receipt_id", $nID);
					$oResponse->SetHiddenParam( "receipt_id", $nID );
				}
				
				if ( !empty($nID) ) {
					foreach ( $aRows as $aVal ) {
						$aData	= array();
						
						if ( is_object($aVal) ) {
							$id  			= isset($aVal->id) 						? $aVal->id 						: 0;
							$id_earning_c 	= isset($aVal->earning_correction_id) 	? $aVal->earning_correction_id 		: 0;
							$children		= isset($aVal->children) 				? $aVal->children 					: array();
							$id_earning 	= isset($aVal->earning_id) 				? $aVal->earning_id 				: 0;	
							$id_expense 	= isset($aVal->expense_id) 				? $aVal->expense_id 				: 0;		
							$id_position 	= isset($aVal->position_id) 			? $aVal->position_id 				: 0;	
							$id_firm 		= isset($aVal->firm_id) 				? $aVal->firm_id 					: 0;	
							$id_office 		= isset($aVal->region_id) 				? $aVal->region_id 					: 0;
							$price 			= isset($aVal->price) 					? $aVal->price 						: 0;
							$price_type		= isset($aVal->price_type) 				? $aVal->price_type 				: 0;
							$region_type	= isset($aVal->region_type) 			? $aVal->region_type 				: 0;							
						} else {
							$id  			= isset($aVal['id']) 					? $aVal['id']						: 0;
							$id_earning_c 	= isset($aVal['earning_correction_id']) ? $aVal['earning_correction_id'] 	: 0;
							$children		= isset($aVal['children']) 				? $aVal['children'] 				: array();
							$id_earning 	= isset($aVal['earning_id']) 			? $aVal['earning_id'] 				: 0;	
							$id_expense 	= isset($aVal['expense_id']) 			? $aVal['expense_id'] 				: 0;		
							$id_position 	= isset($aVal['position_id']) 			? $aVal['position_id'] 				: 0;	
							$id_firm 		= isset($aVal['firm_id']) 				? $aVal['firm_id']					: 0;	
							$id_office 		= isset($aVal['region_id']) 			? $aVal['region_id'] 				: 0;
							$price 			= isset($aVal['price']) 				? $aVal['price'] 					: 0;
							$price_type		= isset($aVal['price_type']) 			? $aVal['price_type'] 				: 0;
							$region_type	= isset($aVal['region_type']) 			? $aVal['region_type'] 				: 0;							
						}						
						
						$p_type = "";
						$r_type = "";
								
						switch ($price_type) {
							case 0: 	$p_type = "lv"; 		break;
							case 1: 	$p_type = "percent"; 	break;	
							default: 	$p_type = "percent"; 	break;							
						}
								
						switch ($region_type) {
							case 0: 	$r_type = "always_this";	break;
							case 1:		$r_type = "from_object";	break;
							case 2:		$r_type = "default_this";	break;										
							default:	$r_type = "default_this";	break;							
						}
							
						$aData['id'] 			= $nID;
						$aData['id_service'] 	= isset($aReceiptData['id_service']) ? $aReceiptData['id_service'] : $id;
						$aData['id_earning']	= $id_earning;
						$aData['id_correction']	= $id_earning_c;
						$aData['id_expense']	= $id_expense;
						$aData['id_position']	= $id_position;
						$aData['is_leaf'] 		= 1;
//						$aData['id_office'] 	= !empty($id_office) 	? $id_office 	: $nIDOffice;
//						$aData['id_firm'] 		= !empty($id_firm) 		? $id_firm 		: $nIDFirm;		
						$aData['price'] 		= $price;
						$aData['price_type'] 	= $p_type;		
						$aData['office_option']	= $r_type;							
							
						$oServiceTree->update($aData);
							
						foreach ( $children as $aChild ) {
							$aData					= array();
									
							$aData['id'] 			= $nID;
							$aData['is_leaf'] 		= 0;
									
							$oServiceTree->update($aData);
									
							$aData					= array();
								
							if ( is_object($aChild) ) {
								$id  			= isset($aChild->id) 						? $aChild->id 					: 0;
								$name 			= isset($aChild->name) 						? $aChild->name 				: $sName;
								$id_earning 	= isset($aChild->earning_id) 				? $aChild->earning_id 			: 0;	
								$id_expense 	= isset($aChild->expense_id) 				? $aChild->expense_id 			: 0;		
								$id_position 	= isset($aChild->position_id) 				? $aChild->position_id 			: 0;	
								$id_firm 		= isset($aChild->firm_id) 					? $aChild->firm_id 				: 0;	
								$id_office 		= isset($aChild->region_id) 				? $aChild->region_id 			: 0;
								$price 			= isset($aChild->price) 					? $aChild->price 				: 0;
								$price_type		= isset($aChild->price_type) 				? $aChild->price_type 			: 0;
								$region_type	= isset($aChild->region_type) 				? $aChild->region_type 			: 0;	
								$baby			= isset($aChild->children) 					? $aChild->children 			: array();									
							} else {
								$id  			= isset($aChild['id']) 						? $aChild['id']					: 0;
								$name 			= isset($aChild['name']) 					? $aChild['name'] 				: $sName;
								$id_earning 	= isset($aChild['earning_id']) 				? $aChild['earning_id']			: 0;	
								$id_expense 	= isset($aChild['expense_id']) 				? $aChild['expense_id']			: 0;
								$id_position 	= isset($aChild['position_id']) 			? $aChild['position_id']		: 0;
								$id_firm	 	= isset($aChild['firm_id']) 				? $aChild['firm_id']			: 0;
								$id_office	 	= isset($aChild['region_id']) 				? $aChild['region_id']			: 0;
								$price	 		= isset($aChild['price']) 					? floatval($aChild['price'])	: 0;
								$price_type		= isset($aChild['price_type']) 				? $aChild['price_type'] 		: 0;
								$region_type	= isset($aChild['region_type']) 			? $aChild['region_type'] 		: 0;
								$baby			= isset($aChild['children']) 				? $aChild['children'] 			: array();						
							}
							
							$p_type = "";
							$r_type = "";
									
							switch ($price_type) {
								case 0: 	$p_type = "lv"; 		break;
								case 1: 	$p_type = "percent"; 	break;	
								default: 	$p_type = "percent"; 	break;							
							}
									
							switch ($region_type) {
								case 0: 	$r_type = "always_this";	break;
								case 1:		$r_type = "from_object";	break;
								case 2:		$r_type = "default_this";	break;										
								default:	$r_type = "default_this";	break;							
							}
							
							$aData['id'] 			= 0;
							$aData['name'] 			= $name;
							$aData['service_type'] 	= "activity";
							$aData['id_service'] 	= $nID;
							$aData['id_earning']	= $id_earning;
							$aData['id_expense']	= $id_expense;
							$aData['id_position']	= $id_position;
							$aData['id_parent'] 	= $nID;
							$aData['id_activity'] 	= $id;
							$aData['is_leaf'] 		= 1;
							$aData['id_office'] 	= !empty($id_office) 	? $id_office 	: $nIDOffice;
							$aData['id_firm'] 		= !empty($id_firm) 		? $id_firm 		: $nIDFirm;		
							$aData['price'] 		= $price;
							$aData['price_type'] 	= $p_type;		
							$aData['office_option']	= $r_type;									
								
							$oServiceTree->update($aData);
								
							if ( isset($aData['id']) && !empty($aData['id']) ) {
								$nIDAct = $aData['id'];
							}	
							
							foreach ( $baby as $aBaby ) {
								$aData					= array();
										
								$aData['id'] 			= $nIDAct;
								$aData['is_leaf'] 		= 0;
										
								$oServiceTree->update($aData);
										
								$aData					= array();								
								
								if ( is_object($aChild) ) {
									$id  			= isset($aBaby->id) 						? $aBaby->id 					: 0;
									$name 			= isset($aBaby->name) 						? $aBaby->name 					: $sName;
									$id_earning 	= isset($aBaby->earning_id) 				? $aBaby->earning_id 			: 0;	
									$id_expense 	= isset($aBaby->expense_id) 				? $aBaby->expense_id 			: 0;		
									$id_position 	= isset($aBaby->position_id) 				? $aBaby->position_id 			: 0;	
									$id_firm 		= isset($aBaby->firm_id) 					? $aBaby->firm_id 				: 0;	
									$id_office 		= isset($aBaby->region_id) 					? $aBaby->region_id 			: 0;
									$price 			= isset($aBaby->price) 						? $aBaby->price 				: 0;
									$price_type		= isset($aBaby->price_type) 				? $aBaby->price_type 			: 0;
									$region_type	= isset($aBaby->region_type) 				? $aBaby->region_type 			: 0;									
								} else {
									$id  			= isset($aBaby['id']) 						? $aBaby['id']					: 0;
									$name 			= isset($aBaby['name']) 					? $aBaby['name'] 				: $sName;
									$id_earning 	= isset($aBaby['earning_id']) 				? $aBaby['earning_id']			: 0;	
									$id_expense 	= isset($aBaby['expense_id']) 				? $aBaby['expense_id']			: 0;
									$id_position 	= isset($aBaby['position_id']) 				? $aBaby['position_id']			: 0;
									$id_firm	 	= isset($aBaby['firm_id']) 					? $aBaby['firm_id']				: 0;
									$id_office	 	= isset($aBaby['region_id']) 				? $aBaby['region_id']			: 0;
									$price	 		= isset($aBaby['price']) 					? floatval($aBaby['price'])		: 0;
									$price_type		= isset($aBaby['price_type']) 				? $aBaby['price_type'] 			: 0;
									$region_type	= isset($aBaby['region_type']) 				? $aBaby['region_type'] 		: 0;																	
								}	
								
								$p_type = "";
								$r_type = "";
										
								switch ($price_type) {
									case 0: 	$p_type = "lv"; 		break;
									case 1: 	$p_type = "percent"; 	break;	
									default: 	$p_type = "percent"; 	break;							
								}
										
								switch ($region_type) {
									case 0: 	$r_type = "always_this";	break;
									case 1:		$r_type = "from_object";	break;
									case 2:		$r_type = "default_this";	break;										
									default:	$r_type = "default_this";	break;							
								}								
								
								$aData['id'] 			= 0;
								$aData['name'] 			= $name;
								$aData['service_type'] 	= "operation";
								$aData['id_service'] 	= $nID;
								$aData['id_earning']	= $id_earning;
								$aData['id_expense']	= $id_expense;
								$aData['id_position']	= $id_position;
								$aData['id_parent'] 	= $nIDAct;
								$aData['id_activity'] 	= $id;
								$aData['is_leaf'] 		= 1;
								$aData['id_office'] 	= !empty($id_office) 	? $id_office 	: $nIDOffice;
								$aData['id_firm'] 		= !empty($id_firm) 		? $id_firm 		: $nIDFirm;		
								$aData['price'] 		= $price;
								$aData['price_type'] 	= $p_type;		
								$aData['office_option']	= $r_type;									
									
								$oServiceTree->update($aData);															
							}
						}
 
					}
				}
			}
			
			$aParams = Params::getAll();
			//$oResponse->printResponse();
			$this->init($oResponse);
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
	}
?>