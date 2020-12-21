<?php

	class ApiSetNomenclatureService {
		
		public function load(DBResponse $oResponse) {
			
			$nID = Params::get('nID',0);
			
			$oDBNomenclaturesEarnings = new DBNomenclaturesEarnings();
			$oDBMeasures = new DBMeasures();
			
			$aNomenclatureEarnings = $oDBNomenclaturesEarnings->getAllAssoc();
			$aMeasures = $oDBMeasures->getMeasures();
			
			if(empty($nID)) {
			
				$oResponse->setFormElement('form1','nIDMeasure');
				$oResponse->setFormElementChild('form1','nIDMeasure',array('value'=>0),'---Изберете---');
				foreach ($aMeasures as $val) {
					$oResponse->setFormElementChild('form1','nIDMeasure',array('value'=>$val['id']),$val['description']);
				}
				
				$oResponse->setFormElement('form1','nIDNomenclatureEarning');
				$oResponse->setFormElementChild('form1','nIDNomenclatureEarning',array('value' => '0'),"---Изберете---");
				foreach ($aNomenclatureEarnings as $key => $value) {
					$oResponse->setFormElementChild('form1','nIDNomenclatureEarning',array("value" => $key),$value);
				}
			} else {
				$oDBNomenclaturesServices = new DBNomenclaturesServices();
				
				$aNomenclatureService = $oDBNomenclaturesServices->getRecord($nID);
				
				$oResponse->setFormElement('form1','sCode',array(),$aNomenclatureService['code']);
				$oResponse->setFormElement('form1','sName',array(),$aNomenclatureService['name']);
				$oResponse->setFormElement('form1','price',array(),$aNomenclatureService['price']);
				
				if(!empty($aNomenclatureService['name_edit'])) {
					$oResponse->setFormElement('form1','name_edit',array("checked" => "checked"));
				}
				
				if(!empty($aNomenclatureService['quantity_edit'])) {
					$oResponse->setFormElement('form1','quantity_edit',array("checked" => "checked"));
				}
				
				if(!empty($aNomenclatureService['price_edit'])) {
					$oResponse->setFormElement('form1','price_edit',array("checked" => "checked"));
				}
				
				if ( !empty($aNomenclatureService['for_transfer']) ) {
					$oResponse->setFormElement( 'form1', 'for_trans', array("checked" => "checked") );
				}				
				
				$oResponse->setFormElement('form1','type_service');
				if(!empty($aNomenclatureService['is_month'])) {
					$oResponse->setFormElementChild('form1','type_service',array("value"=>"month","selected"=>"selected"),"месечна");
					$oResponse->setFormElementChild('form1','type_service',array("value"=>"single"),"еднократна");
				} else {
					$oResponse->setFormElementChild('form1','type_service',array("value"=>"month"),"месечна");
					$oResponse->setFormElementChild('form1','type_service',array("value"=>"single","selected"=>"selected"),"еднократна");
				}
				
				$oResponse->setFormElement('form1','nIDMeasure');
				$oResponse->setFormElementChild('form1','nIDMeasure',array('value'=>0),'---Изберете---');
				foreach ($aMeasures as $val) {
					if($val['id'] == $aNomenclatureService['id_measure']) {
						$oResponse->setFormElementChild('form1','nIDMeasure',array("value"=> $val['id'],"selected" => "selected"),$val['description']);
					} else {
						$oResponse->setFormElementChild('form1','nIDMeasure',array("value"=> $val['id']),$val['description']);
					}
				}
				
				$oResponse->setFormElement('form1','nIDNomenclatureEarning');
				$oResponse->setFormElementChild('form1','nIDNomenclatureEarning',array('value' => '0'),"---Изберете---");
				foreach ($aNomenclatureEarnings as $key => $value) {
					if($key == $aNomenclatureService['id_nomenclature_earning']) {
						$oResponse->setFormElementChild('form1','nIDNomenclatureEarning',array("value" => $key,"selected" => "selected"),$value);
					} else {
						$oResponse->setFormElementChild('form1','nIDNomenclatureEarning',array("value" => $key),$value);
					}
				}
			}
			
			$oResponse->printResponse();
		}
		
		public function save() {
			$nID = Params::get('nID',0);
			$sCode = Params::get('sCode','');
			$sName = Params::get('sName','');
			$dPrice = Params::get('price','0.00');
			$nNameEdit = Params::get('name_edit',0);
			$nQuantityEdit = Params::get('quantity_edit',0);
			$nPriceEdit = Params::get('price_edit',0);
			$sTypeService = Params::get('type_service','');
			$nIDMeasure = Params::get('nIDMeasure',0);
			$nIDNomenclatureEarning = Params::get('nIDNomenclatureEarning',0);
			$for_transfer 		= Params::get('for_trans', 0);
			
			if(empty($sCode)) {
				throw new Exception("Въведете код на номенклатурата услуга");
			}
			
			if(empty($sName)) {
				throw new Exception("Въведте име на номенклатурата услуга");
			}
			
			if(empty($nIDMeasure)) {
				throw new Exception("Изберете мярка");
			}
			
			if(empty($nIDNomenclatureEarning)) {
				throw new Exception('Изберете Номенклатура-приход');
			}
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['code'] = $sCode;
			$aData['name'] = $sName;
			$aData['price'] = $dPrice;
			$aData['for_transfer'] = $for_transfer;
			$aData['name_edit'] = $nNameEdit;
			$aData['quantity_edit'] = $nQuantityEdit;
			$aData['price_edit'] = $nPriceEdit;
			if($sTypeService == 'month') {
				$aData['is_month'] = 1;
			} else {
				$aData['is_month'] = 0;
			}
			$aData['id_measure'] = $nIDMeasure;
			$aData['id_nomenclature_earning'] = $nIDNomenclatureEarning;
			
			$oDBNomenclaturesServices = new DBNomenclaturesServices();
			$oDBNomenclaturesServices->update($aData);
		}
	}

?>