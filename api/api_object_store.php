<?php
	class ApiObjectStore
	{
		public function result( DBResponse $oResponse )
		{
			$oResponse->setFormElementAttribute( 'form1', 'object_state', 'src', 'page.php?page=object_store_state' );
			$oResponse->setFormElementAttribute( 'form1', 'object_ppp', 'src', 'page.php?page=object_store_ppp' );
			
			$oResponse->printResponse(); 
		}
	}
?>