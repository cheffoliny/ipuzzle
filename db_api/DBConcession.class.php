<?php
	class DBConcession extends DBBase2 
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, "concession" );
		}
		
		public function getReport($aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty($_SESSION["userdata"]["access_right_levels"] ) )
			{
				if( in_array( "setup_concessions", $_SESSION["userdata"]["access_right_levels"] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT
					con.id AS id,
					con.id AS _id,
					con.name AS name,
					CONCAT( '[', nom_ear.code, ']', ' ', nom_ear.name ) AS nomenclature,
					con.months_count AS months_count,
					con.percent AS percent,
					IF
					(
						p.id, 
						CONCAT(
							CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
							' (',
							DATE_FORMAT( con.updated_time, '%d.%m.%Y %H:%i:%s' ),
							')'
						),
						''
					) AS updated_user
				FROM
					concession con
				LEFT JOIN
					nomenclatures_services nom_ear ON nom_ear.id = con.id_service
				LEFT JOIN
					{$db_name_personnel}.personnel p ON p.id = con.updated_user
				WHERE
					con.to_arc = 0
			";
			
			$this->getResult( $sQuery, "name", DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "name", 			"Наименование", 		"Сортирай по наименование", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "nomenclature", 	"Услуга", 				"Сортирай по услуга", NULL, NULL, NULL, array( "DATA_FORMAT" => DF_STRING ) );
			$oResponse->setField( "months_count", 	"Брой Месеци", 			"Сортирай по брой месеци", 	NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "percent", 		"Процент", 				"Сортирай по процент", 		NULL, NULL, NULL, array( "DATA_FORMAT" => DF_NUMBER ) );
			$oResponse->setField( "updated_user", 	"Последна редакция", 	"Сортирай по последна редакция" );
			
			if( $right_edit )
			{
				$oResponse->setField( "", "", "", "images/cancel.gif", "deleteConcession", "" );
				$oResponse->setFieldLink( "name", "openConcession" );
			}
		}
		
		/**
		 * Проверка дали броя месеци е уникален.
		 *
		 * @param int $nMonthCount
		 */
		public function isMonthsCountUnique( $nMonthsCount )
		{
			if( empty( $nMonthsCount ) || !is_numeric( $nMonthsCount ) )
			{
				return true;
			}
			
			$sCheckQuery = "
				SELECT
					*
				FROM
					concession
				WHERE
					months_count = {$nMonthsCount}
			";
			
			$aData = $this->select( $sCheckQuery );
			
			if( !empty( $aData ) )
			{
				return false;
			}
			else return true;
		}
	}
?>