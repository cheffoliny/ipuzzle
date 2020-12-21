<?php
	class ApiTestPage {
	
		public function test( DBResponse $oResponse ) {
			$oTest = new DBTest();
			$aTest = array();
			
			$oTest->getReport( $oResponse );

			$oResponse->printResponse();
		}
	}
?>