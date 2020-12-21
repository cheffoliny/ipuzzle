<?php

	class ApiBuyDocInventory {
		
		public function result(DBResponse $oResponse) {
			
			$nIDBuyDoc = Params::get('nID','');
			
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->getReportBuy($oResponse,$nIDBuyDoc);
			
			$oResponse->printResponse("Документ за покупка - Опис","buy_doc_inventory");
		}
		
		public function del_row(DBResponse $oResponse) {
			
			$nIDRowToDel = Params::get('id_row_to_del','');
			
			$oDBBuyDocsRows = new DBBuyDocsRows();
			
			$oDBBuyDocsRows->delete($nIDRowToDel);
			
			$oResponse->printResponse();
		}
	}

?>