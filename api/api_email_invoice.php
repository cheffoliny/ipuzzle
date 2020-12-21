<?php
	class ApiEmailInvoice {
		public function result( DBResponse $oResponse ) {
			
			$aInvoice 		= array();
			$oDBMailInvoice = new DBMailInvoice();
			$aInvoice		= $oDBMailInvoice->getMailSettings();
	
			$aftp = array();
			$aftp['host'] = $aInvoice['ftp_host'];
			$aftp['set_timeout_sec'] = 360;
			$aftp['user'] = $aInvoice['ftp_user'];
			$aftp['pass'] = $aInvoice['ftp_password'];
			$aftp['passive'] = true;
			$aftp['chdir'] = "~/";
			
			$ftp_con_id = $oDBMailInvoice->myftp_connect($aftp);
			$files = $oDBMailInvoice->GetFtpFiles($ftp_con_id);
			$oDBMailInvoice->refresh($files, $oResponse);
			$oDBMailInvoice->myftp_disconnect($ftp_con_id);	
			//APILog::Log(0, $files);		

			
			$oResponse->printResponse();	
		}
		
				
		public function send( DBResponse $oResponse ) {
			
			ini_set("max_execution_time", "0");
			
			$aInvoice 		= array();
			$oDBMailInvoice = new DBMailInvoice();
			
			$aInvoice 		= $oDBMailInvoice->getMailSettings();
			
			$aftp = array();
			$aftp['host'] = $aInvoice['ftp_host'];
			$aftp['set_timeout_sec'] = 360;
			$aftp['user'] = $aInvoice['ftp_user'];
			$aftp['pass'] = $aInvoice['ftp_password'];
			$aftp['passive'] = true;
			$aftp['chdir'] = "~/";
			
			$ftp_con_id = $oDBMailInvoice->myftp_connect($aftp);
			$files = $oDBMailInvoice->GetFtpFiles($ftp_con_id);
			$oDBMailInvoice->onMailAction( $files, $ftp_con_id, $aInvoice, $oResponse );
			$oDBMailInvoice->refresh($files, $oResponse);
			$oDBMailInvoice->myftp_disconnect($ftp_con_id);	
			//APILog::Log(0, $files);		

			
			$oResponse->printResponse();	
		}
		

	}
	
?>