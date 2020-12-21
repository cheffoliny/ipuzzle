<?php
	class DBObjectTypes extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct($db_sod, 'object_types');
		}
		
		public function getObjectTypes() {
			$sQuery = "
				SELECT
					id,
					name
				FROM object_types
				WHERE to_arc = 0
			";
			return $this->selectAssoc($sQuery);
		}
			
		public function getObjectTypes2( $sName, $nLimit )
		{
			
			if( empty( $nLimit ) || !is_numeric( $nLimit ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
			
			$sQuery = "
				SELECT
					id,
					name
				FROM object_types
				WHERE to_arc = 0
				";
			
			if( !empty( $sName ) )
			{
				$sQuery .= sprintf("AND name LIKE '%%%s%%'\n", addslashes( $sName ) );
			}
			
			$sQuery .= "ORDER BY name\n";
			$sQuery .= "LIMIT {$nLimit}\n";
			
			return $this->select( $sQuery );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'object_types', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						o.id,
						o.name,
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
					FROM object_types o
						LEFT JOIN {$db_name_personnel}.personnel p ON o.updated_user = p.id
					WHERE o.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Тип", 					"Сортирай по тип" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteType', '' );
				$oResponse->setFieldLink( "name", "openType" );
			}
		}
	}
?>