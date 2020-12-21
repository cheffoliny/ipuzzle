<?php

	class ApiSaleDocOrders {
		
		public function result(DBResponse $oResponse) {
			$nID = Params::get('nID',0);
			
			$oDBOrders = new DBOrders();
			$oDBSaleDocs = new DBSalesDocs();
			
			$aSaleDoc = array();
			$oDBSaleDocs->getRecord($nID,$aSaleDoc);
			
			$oResponse->setFormElement('form1','total_sum',array(),sprintf('%0.2f лв.',$aSaleDoc['total_sum']));
			$oResponse->setFormElement('form1','orders_sum',array(),sprintf('%0.2f лв.',$aSaleDoc['orders_sum']));
			$oResponse->setFormElement('form1','rest_sum',array(),sprintf('%0.2f лв.',$aSaleDoc['total_sum'] - $aSaleDoc['orders_sum']));
			
			$oDBOrders->getReport($oResponse,$nID,'sale');
			
			
			
			$oResponse->printResponse("Документ за продажба - Ордери",'sale_doc_orders');
		}
		
	}

?>