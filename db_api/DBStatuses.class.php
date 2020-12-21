<?php

	class DBStatuses extends DBBase2
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct( $db_sod, 'statuses' );
		}
		
		public function getStatuses()
		{
			$sQuery = "
				SELECT
					id,
					name
				FROM statuses
				WHERE to_arc = 0
			";
			return $this->selectAssoc( $sQuery );
		}
		
		public function getStatuses2()
		{
			$sQuery = "
				SELECT
					id,
					name
				FROM statuses
				WHERE to_arc = 0
			";
			return $this->select( $sQuery );
		}
		
		public function getFirmStatuses( $id )
		{
			$sQuery = "
				SELECT
					id_status
				FROM
					firms_object_statuses
				WHERE
					id_firm = {$id}
				";
			return $this->select( $sQuery );
		}
		
		public function getStatusesAlphabetic()
		{
			$sQuery = "
				SELECT
					id,
					name
				FROM statuses
				WHERE to_arc = 0
				ORDER BY name
			";
			return $this->select( $sQuery );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'object_statuses', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						s.id,
						s.name,
						s.is_sod,
						s.play,
						s.payable,
						IF
						(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( s.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
								),
								''
						) AS updated_user
					FROM statuses s
						LEFT JOIN {$db_name_personnel}.personnel p ON s.updated_user = p.id
					WHERE s.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Статус", 				"Сортирай по статус" );
			$oResponse->setField( "is_sod", 		"СОД", 					"", "images/confirm.gif" );
			$oResponse->setField( "play", 			"Активен", 				"", "images/confirm.gif" );
			$oResponse->setField( "payable", 		"Платим", 				"", "images/confirm.gif" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( "", "", "", "images/cancel.gif", "deleteStatus", "" );
				$oResponse->setFieldLink( "name", "openStatus" );
			}
		}
	}
?>