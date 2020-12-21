<?php
	class DBPatruls
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'patruls');
		}
				
		public function getPatrulByNum( $nID ) {
			$nID = (int) $nID;

			$sQuery = "
				SELECT 
					p.id,
					p.id_office
				FROM patruls p
				WHERE p.num_patrul = {$nID} 				
			";
			
			return $this->selectOnce( $sQuery );
		}


		public function getPatrulsByOffice( $nID , $busyPatruls ) {
			$nID = (int) $nID;
			global $db_name_auto;

			$sQuery = "
				SELECT 
					p.id,
					p.num_patrul
				FROM patruls p
				WHERE 1
					AND p.id_office = {$nID}			
			";
			
			if (!empty($busyPatruls))
			{
				$sQuery .= "AND p.id NOT IN ( $busyPatruls )"; 
			}
			
			return $this->selectAssoc( $sQuery );
		}
	
		public function getReport( DBResponse $oResponse )	
		{
			$nIDFirm = Params::get('nIDFirm');
			global $db_name_sod,$db_name_personnel;
			
			$right_edit = false;
			if (!empty($_SESSION['userdata']['access_right_levels']))
				if (in_array('auto_marks_edit', $_SESSION['userdata']['access_right_levels']))
				{
					$right_edit = true;
				}
			
			$sQuery = "
				SELECT
					 o.id,
				     o.code,
				     o.name, 
				     group_concat( p.num_patrul ORDER BY p.num_patrul SEPARATOR ', ') as patruls,
				     CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(p.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM patruls p
				LEFT JOIN {$db_name_personnel}.personnel as up ON p.updated_user = up.id
				LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
				WHERE p.id_office <> 0 ";
			if(!empty($nIDFirm))
			{
				$sQuery .= "AND o.id_firm = {$nIDFirm}";
			}
			$sQuery .= " GROUP BY o.id ";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("code", "Код", "Сортирай по код",'','');
			$oResponse->setField("name", "Регион", "Сортирай по регион");
			$oResponse->setField("patruls","Патрули", "Сортирай по патрули");
			$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
			
			if ($right_edit) {
				$oResponse->setField( 'id',			'',			'', 'images/edit.gif', 'editPatruls', '');
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'delPatruls', '');
				$oResponse->setFieldLink("code", "editPatruls");
				$oResponse->setFieldLink("name", "editPatruls");
			}
		}
		public function getReport1( DBResponse $oResponse )	{
		
			global $db_name_sod,$db_name_personnel;
			
			$sQuery = "
				SELECT
					 o.id,
				     o.code,
				     o.name, 
				     group_concat( p.num_patrul ORDER BY p.num_patrul SEPARATOR ', ') as patruls,
				     CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(p.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM patruls p
				LEFT JOIN {$db_name_personnel}.personnel as up ON p.updated_user = up.id
				LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id
				WHERE p.id_office <> 0
				GROUP BY o.id
				";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			$oResponse->setField("code", "Код", "Сортирай по код",'','');
			$oResponse->setField("name", "Регион", "Сортирай по регион");
			$oResponse->setField("patruls","Патрули", "Сортирай по патрули");
			$oResponse->setField("updated_user", "Последна редакция", "Сортирай по последна редакция");
			$oResponse->setField( 'id',			'',			'', 'images/edit.gif', 'editPatruls', '');
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'delPatruls', '');
			
			$oResponse->setFieldLink("code", "editPatruls");
			$oResponse->setFieldLink("name", "editPatruls");
		}
		public function detachAllPatrulsFromOffice( $nID )	
		{
			$sQuery = "DELETE FROM patruls WHERE id_office = {$nID}";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function delPatrul($nIDOffice,$nNum) {
			$sQuery = "
				DELETE
				FROM patruls
				WHERE id_office = {$nIDOffice}
					AND num_patrul = {$nNum}
			";
			
			$this->oDB->Execute($sQuery);
		}
		
		public function getAllPatrulsByIDOffice($nID)	{
			
			$sQuery = " 
				SELECT 
					group_concat( p.num_patrul ORDER BY p.num_patrul SEPARATOR ', ') as patruls
				FROM patruls p
				WHERE p.id_office = {$nID} 				
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function getNumByIDOffice($nIDOffice) {
			$sQuery = "
				SELECT
					id,
					num_patrul
				FROM patruls
				WHERE id_office = {$nIDOffice}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getAllPatruls()	{
			
			$sQuery = " 
				SELECT 
					group_concat( p.num_patrul) as patruls
				FROM patruls p		
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function getFreePatruls()	{
			
			$sQuery = " 
				SELECT 
					group_concat( p.num_patrul) as patruls
				FROM patruls p
				WHERE p.id_office = 0 				
			";
			return $this->selectOnce( $sQuery );
		}
		
		public function checkFreePatrul( $nNum )
		{
			if( empty( $nNum ) || !is_numeric( $nNum ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT *
				FROM patruls
				WHERE num_patrul = {$nNum}
				";
				
			$aData = $this->select( $sQuery );
			
			return empty( $aData );
		}
		public function getIDByNumPatrul($nID)
		{
			$sQuery = "
				SELECT 
					id
				FROM 
					patruls
				WHERE  
					num_patrul = {$nID}

					";
				
			return $this->selectOne($sQuery);	
		}
		
		public function getNumByID($nID) {
			$sQuery = "
				SELECT 
					num_patrul
				FROM 
					patruls
				WHERE  
					id = {$nID}
			";
				
			return $this->selectOne($sQuery);	
		}
	}
?>