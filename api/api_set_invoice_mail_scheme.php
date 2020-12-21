<?php
	class ApiSetInvoiceMailScheme {
		public function result( DBResponse $oResponse ) {
			
			$aInvoice = array();
			$oDBMailInvoice = new DBMailInvoice();
			
			$aInvoice = $oDBMailInvoice->getMailSettings();
			
			//APILog::Log(0, $aInvoice);
			$nID 		= isset($aInvoice['id']) ? $aInvoice['id'] : 0;
			$subject 	= isset($aInvoice['subject']) ? $aInvoice['subject'] : "";
			$text 		= isset($aInvoice['text']) ? htmlspecialchars($aInvoice['text'], ENT_QUOTES) : "";		
			$email_from = isset($aInvoice['email_from']) ? $aInvoice['email_from'] : "";
			$email_reply	= isset($aInvoice['email_reply']) ? $aInvoice['email_reply'] : "";	
			$ftp_password	= isset($aInvoice['ftp_password']) ? $aInvoice['ftp_password'] : "";	
			$ftp_host	= isset($aInvoice['ftp_host']) ? $aInvoice['ftp_host'] : "";	
			$ftp_user	= isset($aInvoice['ftp_user']) ? $aInvoice['ftp_user'] : "";	
			
			$oResponse->setFormElement('form1', 'nID', array(), $nID );
			$oResponse->setFormElement('form1', 'textarea', array(), $text );
			$oResponse->setFormElement('form1', 'email_subject', array(), $subject );
			$oResponse->setFormElement('form1', 'email_from', array(), $email_from );
			$oResponse->setFormElement('form1', 'email_reply', array(), $email_reply );
			$oResponse->setFormElement('form1', 'ftp_host', array(), $ftp_host );
			$oResponse->setFormElement('form1', 'ftp_user', array(), $ftp_user );
			$oResponse->setFormElement('form1', 'ftp_password', array(), $ftp_password );
			
			$oResponse->printResponse();	
		}
		
				

		public function update() {
			$nID 		= Params::get("nID", 0);
			$text	 	= Params::get("textarea", "");
			$subject	= Params::get("email_subject", "");
			$email_from = Params::get("email_from", "");
			$email_reply	= Params::get("email_reply", "");
			$ftp_password	= Params::get("ftp_password", "");
			$ftp_host	= Params::get("ftp_host", "");
			$ftp_user	= Params::get("ftp_user", "");
			
			$oDBMailInvoice = new DBMailInvoice();
			
			$aData = array();
			$aData['id'] 		= $nID;
			$aData['updated_user'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			$aData['updated_time'] = time();
			$aData['to_arc'] 	= 1;

			$oDBMailInvoice->update($aData);		
			
			$aData = array();
			$aData['id'] 		= 0;
			$aData['subject'] 	= $subject;
			$aData['text'] 		= htmlspecialchars_decode($text);
			$aData['email_from'] 	= $email_from;
			$aData['email_reply'] 	= $email_reply;
			$aData['ftp_password'] 	= $ftp_password;
			$aData['ftp_host'] 	= $ftp_host;
			$aData['ftp_user'] 	= $ftp_user;
			$aData['updated_user'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			$aData['updated_time'] = time();
			$aData['to_arc'] 	= 0;
			
			$oDBMailInvoice->update($aData);	
		}				
		

	}
	
?>