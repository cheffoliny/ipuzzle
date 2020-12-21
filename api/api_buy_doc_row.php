<?php

	class ApiBuyDocRow {
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID','');
			
			$oDBFirms = new DBFirms();
			$oDBOffices = new DBOffices();
			
			$aFirms = $oDBFirms->getFirms4();
			
			$oResponse->setFormElement('form1','nIDFirm');
			$oResponse->setFormElementChild('form1','nIDFirm',array("value" => ''),"---Изберете---");
			
			$oResponse->setFormElement('form1','nIDOffice');
			$oResponse->setFormElementChild('form1','nIDOffice',array("value" => ''),"---Изберете---");
			
			$oResponse->setFormElement('form1','nIDNomenclatureExpense');
			$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value" => ''),'---Изберете---');
			
			//Set Months List
			$oResponse->setFormElement( "form1", "dateM" );
			
			for( $i = -6; $i < 6; $i++ )
			{
				if( $i == 0 )
				{
					$oResponse->setFormElementChild( "form1", "dateM", array( "value" => 0 ), "-----------" );
				}
				
				$oResponse->setFormElementChild( "form1", "dateM", array( "value" => date( "m.Y", strtotime( "{$i} months" ) ) ), date( "m.Y", strtotime( "{$i} months" ) ) );
			}
			
			$oResponse->setFormElementAttribute( "form1", "dateM", "value", 0 );
			//End Set Months List
			
			if(empty($nID)) {
				
				$nIDOffice = $_SESSION['userdata']['id_office'] ? $_SESSION['userdata']['id_office'] : 0;
				
				if(!empty($nIDOffice)) {
					$nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
					
					foreach ($aFirms as $key => $value) {
						if($key == $nIDFirm) {
							$oResponse->setFormElementChild('form1','nIDFirm',array("selected" => "selected","value" => $key),$value);
						} else {
							$oResponse->setFormElementChild('form1','nIDFirm',array("value" => $key),$value);
						}
					}
					
					$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirm);
				
					foreach ($aOffices as $key => $value) {
						if($key == $nIDOffice) {
							$oResponse->setFormElementChild('form1','nIDOffice',array("value" => $key,"selected"=>"selected"),$value);
						} else {
							$oResponse->setFormElementChild('form1','nIDOffice',array("value" => $key),$value);
						}
					}
					
					$oDBNomenclaturesEarexpFirms = new DBNomenclaturesEarexpFirms();	
					$aExpenses = $oDBNomenclaturesEarexpFirms->getExpensesNamesByIDFirmAssoc($nIDFirm);
				
					foreach ($aExpenses as $key => $value) {
						if($key == $nIDNomenclatureExpense) {
							$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value"=>$key,"selected"=>"selected"),sprintf( "[%d] %s", $value['code'], $value['name'] ));
						} else {
							$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value"=>$key),sprintf( "[%d] %s", $value['code'], $value['name'] ));
						}
					}
				
				} else {
					foreach ($aFirms as $key => $value) {
						$oResponse->setFormElementChild('form1','nIDFirm',array("value" => $key),$value);
					}
				}			
				
			} else {
				$oDBBuyDocsRows = new DBBuyDocsRows();
				
				$oDBObjects = new DBObjects();
				$oDBNomenclaturesEarexpFirms = new DBNomenclaturesEarexpFirms();				
				
				$aBuyDocRow = array();
				
				$oDBBuyDocsRows->getRecord($nID,$aBuyDocRow);
				$nIDOffice = $aBuyDocRow['id_office'];
				$nIDFirm = $oDBOffices->getFirmByIDOffice($nIDOffice);
				$nIDObject = $aBuyDocRow['id_object'];
				$nIDNomenclatureExpense = $aBuyDocRow['id_nomenclature_expense'];
				$nMonth = mysqlDateToTimestamp($aBuyDocRow['month']);
				$nSinglePrice = $aBuyDocRow['single_price'];
				$nQuantity = $aBuyDocRow['quantity'];
				$nTotalSum = $aBuyDocRow['total_sum'];
				$sNote = $aBuyDocRow['note'];
				
				$aObject = $oDBObjects->getRecord($nIDObject);
				
				foreach ($aFirms as $key => $value) {
					if($key == $nIDFirm) {
						$oResponse->setFormElementChild('form1','nIDFirm',array("value" => $key,"selected"=>"selected"),$value);
					} else {
						$oResponse->setFormElementChild('form1','nIDFirm',array("value" => $key),$value);
					}
				}
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirm);
				
				foreach ($aOffices as $key => $value) {
					if($key == $nIDOffice) {
						$oResponse->setFormElementChild('form1','nIDOffice',array("value" => $key,"selected"=>"selected"),$value);
					} else {
						$oResponse->setFormElementChild('form1','nIDOffice',array("value" => $key),$value);
					}
				}
				
				$oResponse->setFormElement('form1','id_object',array(),$nIDObject);
				$oResponse->setFormElement('form1','nObjectNum',array(),$aObject['num']);
				$oResponse->setFormElement('form1','sObjectName',array(),$aObject['name']);
				
				$aExpenses = $oDBNomenclaturesEarexpFirms->getExpensesNamesByIDFirmAssoc($nIDFirm);
				
				foreach ($aExpenses as $key => $value) {
					if($key == $nIDNomenclatureExpense) {
						$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value"=>$key,"selected"=>"selected"),sprintf( "[%d] %s", $value['code'], $value['name'] ));
					} else {
						$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value"=>$key),sprintf( "[%d] %s", $value['code'], $value['name'] ));
					}
				}
				
				$oResponse->setFormElement('form1','single_price',array(),$nSinglePrice);
				$oResponse->setFormElement('form1','quantity',array(),$nQuantity);
				$oResponse->setFormElement('form1','total_sum',array(),$nTotalSum);
				$oResponse->setFormElement('form1','note',array(),$sNote);
			}
			
			$oResponse->printResponse();
		}
		
		public function loadOffices(DBResponse $oResponse) {
			$nIDFirm = Params::get('nIDFirm', 0);
			$nIDNomenclatureExpense = Params::get('nIDNomenclatureExpense', 0);
			
			$oResponse->setFormElement('form1','nIDOffice');
			$oResponse->setFormElementChild('form1','nIDOffice',array("value" => ''),'---Изберете---');
			
			$oResponse->setFormElement('form1','nIDNomenclatureExpense');
			$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value" => ''),'---Изберете---');
			
			if(!empty($nIDFirm)) {
				$oDBOffices = new DBOffices();
				$oDBNomenclaturesEarexpFirms = new DBNomenclaturesEarexpFirms();
				
				$aOffices = $oDBOffices->getOfficesByIDFirm($nIDFirm);
				
				foreach ($aOffices as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDOffice',array("value" => $key),$value);
				}
				unset($key);unset($value);
				
				$aExpenses = $oDBNomenclaturesEarexpFirms->getExpensesNamesByIDFirmAssoc($nIDFirm);
				
				foreach ($aExpenses as $key => $value) {
					//Pavel
					if ( $key == $nIDNomenclatureExpense ) {
						$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureExpense', array("value" => $key, "selected" => "selected"), sprintf( "[%d] %s", $value['code'], $value['name'] ) );
					} else {
						$oResponse->setFormElementChild('form1','nIDNomenclatureExpense',array("value"=>$key),sprintf( "[%d] %s", $value['code'], $value['name'] ));
					}
				}
			} 
			
			$oResponse->printResponse();
		}
		
		public function list_objects(DBResponse $oResponse) {

			$nIDFirm = Params::get('nIDFirm','');
			$nIDOffice = Params::get('nIDOffice','');
			$nIDObject = Params::get('id_object','');
			
			$oDBObjects = new DBObjects();
			
			$aObjects = array();
			
			$aData = array();
			$aData['nIDFirm'] = $nIDFirm;
			$aData['nIDOffice'] = $nIDOffice;
			
			$aObjects = $oDBObjects->getObjects($aData);
			
			$oResponse->setFormElement('form1','nIDObject');
			$oResponse->setFormElementChild('form1','nIDObject',array('value' => ''),'--- Списък от обекти ---');
			foreach ($aObjects as $key => $value) {
				if($key == $nIDObject) {
					$oResponse->setFormElementChild('form1','nIDObject',array("value" => $key,"selected" => "selected"),$value);
				} else {
					$oResponse->setFormElementChild('form1','nIDObject',array("value" => $key),$value);
				}
			}
				
			$oResponse->printResponse();

		}
		
		public function save()
		{
			//Objects
			$oDBSaldo 		= new DBSaldo();
			$oDBBuyDocsRows = new DBBuyDocsRows();
			//End Objects
			
			//Params
			$nID 					= Params::get( "nID", 						"" );
			$nIDBuyDoc 				= Params::get( "nIDBuyDoc", 				"" );
			$nIDFirm 				= Params::get( "nIDFirm", 					"" );
			$nIDOffice 				= Params::get( "nIDOffice", 				"" );
			$nIDObject 				= Params::get( "id_object", 				"" );
			$nIDNomenclatureExpense = Params::get( "nIDNomenclatureExpense", 	"" );
			$sMonth 				= Params::get( "dateM", 					"" );
			$nSinglePrice 			= Params::get( "single_price", 				"" );
			$nQuantity 				= Params::get( "quantity", 					"" );
			$nTotalSum 				= Params::get( "total_sum", 				"" );
			$sNote 					= Params::get( "note", 						"" );
			//End Params
			
			//Validation
			if(empty($nIDFirm)) {
				throw new Exception("Не сте избрали фирма");
			}
			
			if(empty($nIDOffice)) {
				throw new Exception("Не сте избрали регион");
			}
			
			if(empty($nIDNomenclatureExpense)) {
				throw new Exception("Не сте избрали номенклатур разход");
			}
			
			if(empty($nSinglePrice)) {
				throw new Exception("Не сте въвели единична цена");
			}
			
			if(empty($nQuantity)) {
				throw new Exception("Не сте въвели количество");
			}
			
			if(empty($sMonth)) {
				throw new Exception("Не сте въвели месец!");
			}
			
			if( !$oDBSaldo->checkFirmBalance( $nIDFirm, ( $oDBBuyDocsRows->getBuyDocFirmTotalSum( $nIDBuyDoc, $nIDFirm ) ) + $nTotalSum ) )
			{
				throw new Exception( "Недостатъчна наличност по текущото салдо на фирмата!" );
			}
			//End Validation
			
			list($m,$y) = explode(".",$sMonth);
			$sMonth = $y."-".$m."-01";
			
			$aBuyDocRow = array();
			$aBuyDocRow['id'] = $nID;
			$aBuyDocRow['id_buy_doc'] = $nIDBuyDoc;
			$aBuyDocRow['id_office'] = $nIDOffice;
			$aBuyDocRow['id_object'] = $nIDObject;
			$aBuyDocRow['id_nomenclature_expense'] = $nIDNomenclatureExpense;
			$aBuyDocRow['month'] = $sMonth;
			$aBuyDocRow['single_price'] = $nSinglePrice;
			$aBuyDocRow['quantity'] = $nQuantity;
			$aBuyDocRow['total_sum'] = $nTotalSum;
			$aBuyDocRow['note'] = $sNote;
			
			$oDBBuyDocsRows->update($aBuyDocRow);
			
		}
		
	}

?>