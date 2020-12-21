<?php

	class DBPersonDocs extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct($db_personnel, 'person_docs');
		}
		public function getReport(DBResponse $oResponse, $sDocuments, $nIDFirm, $nIDOffice) {
			global $db_name_personnel, $db_name_sod;
			
			$dToday = time();
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					pd.id, 
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as name
				FROM person_docs pd
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = pd.id_person
				LEFT JOIN {$db_name_sod}.offices off ON off.id = p.id_office
				WHERE 1
					AND pd.to_arc = 0
					AND pd.id_document IN ({$sDocuments})
					AND UNIX_TIMESTAMP(pd.valid_to) < {$dToday}
			";
		
			if ( !empty( $nIDOffice) ) {
				$sQuery .= " AND off.id = {$nIDOffice}\n";
			} else {
				$sQuery .= " AND off.id_firm = {$nIDFirm}\n";
			}
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			$oResponse->setField("name", "Име", "Сортирай по име");
		}
		
		public function getCountDocsByID ($nID) {
			$sQuery = "
				SELECT
					COUNT(*) as count
				FROM person_docs pd
				WHERE 1
					AND pd.to_arc	= 0
					AND pd.id_document	= {$nID}
			";
			
			return $this->selectOne($sQuery);
		}

		public function getValidPersonDocs( $nIDFirm, $nIDOffice, $dToday, $sDocuments ) {
			
			global $db_name_sod,$db_name_personnel;
			
			$sQuery = "
				SELECT 
					pd.id,
					pd.id_person,
					pd.id_document
				FROM person_docs pd
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = pd.id_person
				LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office 
				WHERE pd.to_arc = 0 
					AND pd.id_document IN ({$sDocuments})
					AND   UNIX_TIMESTAMP(pd.valid_from) < {$dToday}
					AND	( UNIX_TIMESTAMP(pd.valid_to) = 0 OR UNIX_TIMESTAMP(pd.valid_to) > {$dToday} )
			";
			
			if(!empty($nIDOffice)) {
				$sQuery .= " AND p.id_office = {$nIDOffice}";
			} else {
				$sQuery .= " AND o.id_firm = {$nIDFirm}";
			}
			
			return $this->select($sQuery);
		}
		public function getExpiredPersonDocument( $nIDFirm, $nIDOffice , $dToday, $sDocuments )
		{
				global $db_name_sod,$db_name_personnel;
			
			$sQuery = "
				SELECT 
					pd.id,
					pd.valid_to
				FROM person_docs pd
				LEFT JOIN document_types dt ON pd.id_document = dt.id
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = pd.id_person
				LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office 
				WHERE pd.to_arc = 0 
					AND pd.id_document IN ({$sDocuments})
					AND	( UNIX_TIMESTAMP(pd.valid_to) <> 0 AND UNIX_TIMESTAMP(pd.valid_to) < {$dToday} )
			";
			
			if(!empty($nIDOffice)) {
				$sQuery .= " AND p.id_office = {$nIDOffice}";
			} else {
				$sQuery .= " AND o.id_firm = {$nIDFirm}";
			}
			return $this->select($sQuery);	
		}
		public function getExpiredDocByPersonID( $nID, $sDocuments, $dToday)
		{
				global $db_name_sod,$db_name_personnel;
			
			$sQuery = "
				SELECT
					pd.id_document,
					pd.id_person,
					pd.valid_to
				FROM person_docs pd
				WHERE pd.to_arc = 0 
					AND pd.id_document IN ({$sDocuments})
					AND	( UNIX_TIMESTAMP(pd.valid_to) <> 0 AND UNIX_TIMESTAMP(pd.valid_to) < {$dToday} )
					AND pd.id_person = {$nID}
			";
			return $this->select($sQuery);	
		}
	}
?>