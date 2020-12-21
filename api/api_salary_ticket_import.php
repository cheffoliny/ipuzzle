<?php
	require_once("include/fpdi/fpdi.php");
	require_once("pdf/pdf.inc.php");
	
	class ApiSalaryTicketImport {
		
		public function load(DBResponse $oResponse) {
			
			
			$aData = array('3' => 'tri' , '4' => '4etri' ,'5' => array( '1' => 'edno', '2' => 'dve') );
			
			APILog::Log(0,$aData);
			
			$sData = serialize($aData);
			
			APILog::Log(0,$sData);
			
			$sData = unserialize($sData);
			
			APILog::Log(0,$sData);
			
			$oResponse->printResponse();
		}
	}

?>