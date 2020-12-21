<?php
	require_once('pdf/pdf_sale_doc.php');
	class ApiSalesDocs {
		
		public function load(DBResponse $oResponse) {
			
			$aParams 	= Params::getAll();
			$nIDScheme 	= Params::get("schemes", 0);
			$aFirms	= array();
			
			$oDBFirms 	= new DBFirms();
			$oDBFilters 	= new DBFilters();
			
			//Load Firms
			$aFirms 	= $oDBFirms->getFirms4();
			
			$oResponse->setFormElement("form1", 		"nIDFirm", array(), "");
			
			if( $_SESSION['userdata']['access_right_all_regions'] == 1 ) 
			{
				$oResponse->setFormElementChild("form1", 	"nIDFirm", array_merge(array("value" => 0)), "--Всички--");
			}
			
			foreach ( $aFirms as $key => $value ) {
				$oResponse->setFormElementChild("form1", "nIDFirm", array_merge(array("value" => $key)), $value);
			}
			
			if ( !empty($aParams['nIDFirm']) ) {
				$oResponse->setFormElementAttribute("form1", "nIDFirm", "value", $aParams['nIDFirm']);
			}
			//End Load Firms
						
			$nIDPerson = isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			$aFilters = $oDBFilters->getFiltersByReportClass("DBSalesDocs", $nIDPerson);
			
			$oResponse->setFormElement('form1','schemes');
			$oResponse->setFormElementChild('form1','schemes',array("value"=>0),"---Изберете---");
			
			foreach ($aFilters as $key => $value) {
				if(!empty($nIDScheme)) {
					if($key == $nIDScheme) {
						$oResponse->setFormElementChild('form1','schemes',array("value"=>$key,"selected"=>"selected"),$value['name']);
					} else {
						$oResponse->setFormElementChild('form1','schemes',array("value"=>$key),$value['name']);
					}
				} else {
					if(!empty($value['is_default'])) {
						$oResponse->setFormElementChild('form1','schemes',array("value"=>$key,"selected"=>"selected"),$value['name']);
					} else {
						$oResponse->setFormElementChild('form1','schemes',array("value"=>$key),$value['name']);
					}
				}
			}
			
			//Default Date and Time
			if( !isset( $aParams['sFromDate'] ) || empty( $aParams['sFromDate'] ) )
			{
				$oResponse->setFormElement( 'form1', 'sFromDate', array( 'value' => date( '01.m.Y' ) ) );
			}
			if( !isset( $aParams['sToDate'] ) || empty( $aParams['sToDate'] ) )
			{
				$oResponse->setFormElement( 'form1', 'sToDate', array( 'value' => date( 'd.m.Y' ) ) );
			}
			//End Default Date and Time
			
			$oResponse->printResponse();
		}
		
		public function result(DBResponse $oResponse) {
			$aParams = Params::getAll();
			
			if ( isset($aParams['subm']) && $aParams['subm'] == "yes" ) {
				$_SESSION['sales_rows'] = array();
			} elseif ( isset($aParams['subm']) && $aParams['subm'] == "no" ) {
				if ( isset($aParams['chk']) && !empty($aParams['chk']) ) {
					foreach ( $aParams['chk'] as $key => $value ) {
						if ( !empty($value) ) {
							$_SESSION['sales_rows'][$key] = $key;
						} else {
							if ( isset($_SESSION['sales_rows'][$key]) ) {
								unset($_SESSION['sales_rows'][$key]);
							}
						}
					}
				}
			}
			
			$oDBSalesDocs = new DBSalesDocs();
			$oDBSalesDocs->getReport($aParams, $oResponse);
			
			$oResponse->printResponse("Документи за продажба", "sales_docs");
		}
		
		public function group_sale(DBResponse $oResponse) {
			$sel 			= Params::get('sel', '');
			$aCheckboxes 	= Params::get('chk', array());
			
			$nIDPerson 		= isset($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
			
			if ( $sel == "person_account" ) {
				$sAccountType 	= 'person';
				$nIDAccount 	= $nIDPerson;
			} elseif (is_numeric($sel)) {
				$sAccountType 	= 'bank';
				$nIDAccount 	= $sel;
			} else {
				throw new Exception("Невалидна стойност");
			}
			
			$oDBSalesDocs = new DBSalesDocs();
			
			global $db_finance, $db_system;
			
			$db_finance->startTrans();
			$db_system->startTrans();
			
			try {
				$aNumPaidDocs = array();
				
				foreach ( $aCheckboxes as $key => $value ) {
					if ( !empty($value) ) {
						$nDocNum = $oDBSalesDocs->makePayment($key, $sAccountType, $nIDAccount);
						if ( !empty($nDocNum) ) {
							$aNumPaidDocs[] = zero_padding($nDocNum, 10);
						}
					}
				}
			
				if ( empty($aNumPaidDocs) ) {
					$oResponse->setAlert("Не беше изплатен нито един документ за продажба");
				} else {
					$oResponse->setAlert("Следните документи бяха изплатени: \n".implode(',', $aNumPaidDocs));
				}
				
				$db_finance->CompleteTrans();
				$db_system->CompleteTrans();
			} catch (Exception $e) {
				$db_finance->FailTrans();
				$db_system->FailTrans();
			}
	
			
			$oResponse->printResponse();
		}
		
		public function gen_pdfs() {
			$aCheckboxes = Params::get('chk',array());
			
			$oDBSalesDocs = new DBSalesDocs();
			
			$br = 0;
			foreach ($aCheckboxes as $key => $value) {
				if(!empty($value)) {
					
					$aSaleDoc = array();
					$oDBSalesDocs->getRecord($key,$aSaleDoc);
					
					if(	$aSaleDoc['gen_pdf'] == 0 && 
						$aSaleDoc['doc_status'] == 'final'
						
					) {
						$oSaleDocPDF = new SaleDocPDF("P");
						$oSaleDocPDF->PrintReport($key,'',$aSaleDoc['view_type'],1);
						$br++;
						$aSaleDoc['gen_pdf'] = 1;
						$oDBSalesDocs->update($aSaleDoc);
					}
				}
			}
			print "<center><span style='font-size:20px;color:#00aa00;'>".'Генерирани бяха '.$br.' pdf-документ(а)'."</span></center>";
		}
		
		public function deleteFilter() {
			$nIDScheme = Params::get('schemes','');
			
			$oDBFilters = new DBFilters();
			$oDBFilters->delete($nIDScheme);
		}
		
	}

?>