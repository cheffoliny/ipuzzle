<?php

	class ApiBuyDocOrders {
		
		public function result(DBResponse $oResponse) {
			$nID = Params::get('nID',0);
			
			$oDBOrders = new DBOrders();
			$oDBBuyDocs = new DBBuyDocs();
			
			$aBuyDoc = array();
			$oDBBuyDocs->getRecord($nID,$aBuyDoc);
			
			$oResponse->setFormElement('form1','total_sum',array(),sprintf('%0.2f лв.',$aBuyDoc['total_sum']));
			$oResponse->setFormElement('form1','orders_sum',array(),sprintf('%0.2f лв.',$aBuyDoc['orders_sum']));
			$oResponse->setFormElement('form1','rest_sum',array(),sprintf('%0.2f лв.',$aBuyDoc['total_sum'] - $aBuyDoc['orders_sum']));
			
			$oDBOrders->getReport($oResponse,$nID,'buy');
			
			$oResponse->printResponse("Документ за покупка - Ордери",'buy_doc_orders');
		}
		
	}

?>