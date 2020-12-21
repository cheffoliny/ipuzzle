<?php
	
	class ApiExportToEmail {
		
		public function result(DBResponse $oResponse) {
			
			$oExport = new DBExportToEmail();

			$oExport->getReport($oResponse);
			
			$oResponse->printResponse();
		}
		
		public function delete() {
			$nID = Params::get("nID", 0);
			
			$oExport = new DBExportToEmail();
			$oExport->delete($nID);
		}
	}
?>