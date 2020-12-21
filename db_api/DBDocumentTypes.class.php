<?php

	class DBDocumentTypes extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct($db_personnel, 'document_types');
		}
		public function getDocumentTypes()
		{
			$sQuery = "
				SELECT
					id,
					name
				FROM document_types
				WHERE to_arc = 0
			";
			return $this->selectAssoc($sQuery);
		}
	}
?>