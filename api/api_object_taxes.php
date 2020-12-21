<?php
	class ApiObjectTaxes {
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');

			if ( !empty( $nID ) ) { 
				
				$aObjectServices	= 0;
				$aObjectSingles	= 0;
				$oClient 		= new DBClients();
				$oObjectServices 	= new DBObjectServices();
				
				$aClient 		= $oClient->getClientByObject($nID);
				$aObjectServices 	= $oObjectServices->getSumPriceByObject($nID);
				$aObjectSingles 	= $oObjectServices->getSinglePriceByObject($nID);
				
				$nIDClient 		= isset($aClient['id']) && is_numeric($aClient['id']) ? $aClient['id'] : 0;
				$sClient 		= isset($aClient['name']) && !empty($aClient['name']) ? $aClient['name'] : "Няма привързан клиент!";	
				
				 
				$oResponse->setFormElement('form1', 'nIDClient', array(), $nIDClient);
				$oResponse->setFormElement('form1', 'sClient', array(), $sClient);	
				//$oResponse->setFormElement('form1', 'tax_mon', array(), ($aObjectServices/1.2));	 //bez dds
				$oResponse->setFormElement('form1', 'tax_mon', array(), ($aObjectServices));	 //bez dds
				//$oResponse->setFormElement('form1', 'tax_single', array(), ($aObjectSingles/1.2)); //bez dds	
				$oResponse->setFormElement('form1', 'tax_single', array(), ($aObjectSingles)); //bez dds	
				
				$oObjectServices->getReport($nID, $oResponse);
							
			}
			 
			$oResponse->printResponse(); 
		}

		public function result2(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			
			if ( !empty( $nID ) ) { 

				$oObjectServices = new DBObjectServices();
				
				Params::set( "cut_page", "1" );
				Params::set( "sfield", "unpaid DESC, paid_date" );
				Params::set( "stype", DBAPI_SORT_DESC );
				$oObjectServices->getReport2($nID, $oResponse);
			}
			
			$oResponse->printResponse(); 
		}
		
		public function deleteMonth() {
			global $db_telepol;
			
			$nIDRecord 	= Params::get('nIDRecord', 	0);
			$nIDObject 	= Params::get('nID', 		0);
			$aObject	= array();
			
			if ( !empty($nIDRecord) ) { 
				$oObjectServices = new DBObjectServices();
				$oObject		 = new DBObjects();
				
				$oObjectServices->delete1($nIDRecord);
				$aObject 	= $oObject->getByID($nIDObject);
				$nIDRegion	= isset($aObject['id_office']) && !empty($aObject['id_office']) ? $aObject['id_office'] : 0;

				if ( !empty($nIDObject) ) {
					$nSum = 0;
					$nSum = $oObjectServices->getSumPriceByObjectRegion($nIDObject, $nIDRegion);
					
					$nSum /= 1.2;

					if ( isset($aObject['id_oldobj']) && !empty($aObject['id_oldobj']) ) {
						$nObj = $aObject['id_oldobj'];
						
						$sQuery = " UPDATE objects SET price = '{$nSum}' WHERE id_obj = {$nObj} ";
		
						$db_telepol->Execute($sQuery);
					}					
				}
			}
		}		

		
		public function deleteSingle() {
			
			$nIDRecord = Params::get('nIDRecord2','0');
			
			if ( !empty($nIDRecord) ) { 
				$oObjectServices = new DBObjectServices();
				$oObjectServices->delete2($nIDRecord);				
			}
		}		

	}
?>