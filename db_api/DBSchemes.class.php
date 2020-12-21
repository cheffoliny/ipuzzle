<?php
	class DBSchemes
		extends DBBase2 {
			
		public function __construct() {
			global $db_storage;
			//$db_sod->debug=true;
			
			parent::__construct($db_storage, 'schemes');
		}
		
		public function getAllSchemes()
		{
			$sQuery = "
					SELECT
						*
					FROM schemes
					WHERE to_arc = 0
			";
			
			return $this->select( $sQuery );
		}
		
		public function getReport( DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'schemes_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT 
						s.id,
						s.name,
						CONCAT(
							CONCAT_WS( ' ', up.fname, up.mname, up.lname ),
							' [',
							DATE_FORMAT( s.updated_time, '%d.%m.%Y %H:%i:%s' ),
							']'
						) AS updated_user,
						s.id_detector AS detector
					FROM schemes s
						LEFT JOIN {$db_name_personnel}.personnel up ON s.updated_user = up.id
						LEFT JOIN nomenclatures n ON ( n.id = s.id_detector ) AND ( n.to_arc = 0 )
					WHERE 1
						AND s.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "id", 			"Код", 						"Сортирай по код" );
			$oResponse->setField( "name", 			"Име", 						"Сортирай по име" );
			$oResponse->setField( "detector", 		"Шаблон за ел. договори", 	"Сортирай по номенклатура детектор", 'images/confirm.gif' );
			$oResponse->setField( "updated_user", 	"Последна редакция", 		"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteScheme', '' );
				$oResponse->setFieldLink( "id", 	"editScheme" );
				$oResponse->setFieldLink( "name", 	"editScheme" );
			}
		}

		
		public function getContractScheme() {
			
			$sQuery = "
				SELECT 
					id,
					name,
					id_detector
				FROM schemes
				WHERE 1
					AND id_detector != 0 
					AND to_arc = 0
			";
			
			return $this->selectOnce($sQuery);
		}
		
	}
?>