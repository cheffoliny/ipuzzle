<?php
	class DBObjectFunctions extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, 'object_functions');
		}
		
		public function getPhysicalGuard()
		{

			$sQuery = "
				SELECT *
				FROM object_functions
				WHERE to_arc = 0
					AND is_fo = 1
			";
			
			return $this->select( $sQuery );
		}
		public function getFunctions() {
			$sQuery = "
				SELECT
					id,
					name
				FROM object_functions
				WHERE to_arc = 0
			";
			return $this->selectAssoc($sQuery);
		}
		public function getFunctions2() {
			$sQuery = "
				SELECT
					id,
					name
				FROM object_functions
				WHERE to_arc = 0
			";
			return $this->select($sQuery);
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'object_functions', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						o.id,
						o.name,
						o.is_sod,
						o.is_fo,
						IF
						( 
							p.id, 
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( o.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
								''
						) AS updated_user
					FROM object_functions o
						LEFT JOIN {$db_name_personnel}.personnel p ON o.updated_user = p.id
					WHERE o.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Наименование", 		"Сортирай по наименование" );
			$oResponse->setField( "is_sod", 		"СОД", 					"", "images/confirm.gif" );
			$oResponse->setField( "is_fo", 			"ФО", 					"", "images/confirm.gif" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteFunction', '' );
				$oResponse->setFieldLink( "name", "openFunction" );
			}
		}
	}
?>