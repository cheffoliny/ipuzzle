<?php
	class ApiBooks {
		public function result( DBResponse $oResponse ) {
			$oBooks = new DBBooks();
			$oBooks->getHistory($oResponse );			

			$oResponse->printResponse();	
		}
	}
	
?>