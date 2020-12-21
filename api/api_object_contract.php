<?php
	class ApiObjectContract {
		public function result(DBResponse $oResponse) {
			
			$nID = Params::get('nID','0');

			if ( !empty( $nID ) ) { 

				$oContract = new DBContracts();
				$aContract = $oContract->getContractByObj($nID);
				
				$pay = $aContract['pay_cash'] ? "В брой" : "По банка";
				$own = $aContract['technics_type'] == "own" || $aContract['technics_type'] == "buy" ? "На клиента" : "Под наем";
				$ein = $aContract['client_is_company'] ? $aContract['client_bul'] : $aContract['client_egn'];
				$from = date("d.m.Y", mktime(0, 0, 0, $aContract['month'], $aContract['day'], $aContract['year']));
				$to = date("d.m.Y", mktime(0, 0, 0, $aContract['month'] + $aContract['period_in_month'], $aContract['day'], $aContract['year']));
				$plan = $aContract['contract_type'] == "mdo"  ? "Месечна денонощна охрана" : "Мониторинг";
				$num = $aContract['contract_num'] > 0 ? $aContract['contract_num'] : "Няма текущ договор за обекта!";
				
				//$oResponse->setFormElement('form1', 'num', array(), $aContract['num']); 
				$oResponse->setFormElement('form1', 'kl_name', array(), $aContract['client_name']); 
				$oResponse->setFormElement('form1', 'kl_addr', array(), $aContract['client_address']);
				$oResponse->setFormElement('form1', 'kl_ein', array(), $ein); 
				$oResponse->setFormElement('form1', 'kl_eindds', array(), $aContract['client_dn']);
				$oResponse->setFormElement('form1', 'kl_mol', array(), $aContract['client_mol']); 
				$oResponse->setFormElement('form1', 'kl_pay', array(), $pay);
				$oResponse->setFormElement('form1', 'tech_own', array(), $own); 
				$oResponse->setFormElement('form1', 'tech_single_responsibility', array(), $aContract['single_liability']);
				$oResponse->setFormElement('form1', 'tech_yearly_responsibility', array(), $aContract['year_liability']); 
				$oResponse->setFormElement('form1', 'contract_num', array(), $num);
				$oResponse->setFormElement('form1', 'contract_date', array(), $from); 
				$oResponse->setFormElement('form1', 'contract_to', array(), $to);
				$oResponse->setFormElement('form1', 'contract_rs', array(), $aContract['rs_name']." [".$aContract['rs_code']."]"); 
				$oResponse->setFormElement('form1', 'schet_info', array(), $aContract['info_schet']);
				$oResponse->setFormElement('form1', 'tech_info', array(), $aContract['info_tehnics']);
				$oResponse->setFormElement('form1', 'tech_plan', array(), $plan);
				$oResponse->setFormElement('form1', 'detectors', array(), $aContract['count_detectors']);
							
				$oContract->getHistoryReport( $oResponse, $nID );
			}
			
			$oResponse->printResponse(); 
		}
		
		public function save(DBResponse $oResponse) {
			$nID 	= Params::get("nID", 		0);
			$sSchet	= Params::get("schet_info", "");
			$sTech	= Params::get("tech_info", 	"");
			
			$aData	= array();
			$aCon	= array();
			$oCon	= new DBContracts();
			
			if ( !empty($nID) ) {
				$aCon = $oCon->getContractByObj($nID);

				if ( isset($aCon['id']) && !empty($aCon['id']) ) {
					$nIDContract 			= $aCon['id'];
					
					$aData['id'] 			= $nIDContract;
					$aData['info_schet'] 	= $sSchet;
					$aData['info_tehnics'] 	= $sTech;	

					$oCon->update($aData);
				}
			}
			
			$oResponse->printResponse(); 	
		}

	}
?>