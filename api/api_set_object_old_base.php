<?php
	require_once("pdf/pdf_contract_old_base.php");

	class ApiSetObjectOldBase {
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');
			
			$oDBObjects2 = new DBObjects2();
			$oDBServises = new DBServices();
			
			$aObject = $oDBObjects2->getObjectById($nID); 
			$aTaxes = $oDBServises->getTaxesByIdObject($nID);
			
			
			foreach ( $aObject as $key => &$value ) {
				$value = trim($value);
				$value = iconv('cp1251','utf-8',$value);
			}
			
			$sTaxes = "";
			$nSumTaxes = 0;
			foreach ( $aTaxes as $key => &$value ) {
				
				$nSumTaxes += $value['price'];
				$value['name'] = trim($value['name']);
				$value['price'] = trim($value['price']);
				$value['name'] = iconv('cp1251','utf-8',$value['name']);
				$value['price'] = iconv('cp1251','utf-8',$value['price']);
				
				$sTaxes .= $value['name']."  ".round($value['price']*1.20,1)." лв.\n";
			}

			$oldTax = round(($aObject['price'] - $nSumTaxes)*1.20,1);
			$nPrice = round(($aObject['price'])*1.20,1);
			
			$oResponse->setFormElement('form1','nNum',array(),$aObject['num']);
			$oResponse->setFormElement('form1','sName',array(),$aObject['name']);
			$oResponse->setFormElement('form1','sAddress',array(),$aObject['address']);
			$oResponse->setFormElement('form1','sPhone',array(),$aObject['phone']);
			$oResponse->setFormElement('form1','sMOL',array(),$aObject['MOL']);
			$oResponse->setFormElement('form1','sEIK',array(),$aObject['tax_num']);
			$oResponse->setFormElement('form1','sEIKDDS',array(),$aObject['bulstat']);
			$oResponse->setFormElement('form1','tax',array(),$oldTax);
			$oResponse->setFormElement('form1','nOldTax',array(),$oldTax);
			$oResponse->setFormElement('form1','taxes',array(),$sTaxes);
			$oResponse->setFormElement('form1','price',array(),$nPrice." лв.");
			$oResponse->setFormElement('form1','nOldPrice',array(),$nPrice);
			
			
			$oResponse->printResponse();
		}
		
		public function result() {
			
			$nID = Params::get('nID','0');
			$nOldTax = Params::get('nOldTax','0');
			$nNewTax = Params::get('tax','0');
			$nOldPrice = Params::get('nOldPrice','0');
			
			if(!is_numeric($nNewTax)) {
				throw new Exception("Въведете число за такса");
			}
			
			if( $nOldTax != $nNewTax ) {
				
				$oDBObjects2 = new DBObjects2();
				$nNewPrice = (double) ($nOldPrice - $nOldTax + $nNewTax)*(5/6);
				$oDBObjects2->setPrice($nID,$nNewPrice);
			}
			
			$oPDF = new ContractOldBasePDF("P");
			$oPDF -> PrintReport($nID);
			
		}
		
	}

?>