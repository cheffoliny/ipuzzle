<?php
	class DBOfficesDirections extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct( $db_sod, 'offices_directions' );
		}
		
		public function getOfficeDirectionsIDs( $nIDOffice )
		{
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
			{
				return array();
			}
			
			$sQuery = "
				SELECT
					GROUP_CONCAT( off_dir.id_direction SEPARATOR ',' ) AS ids
				FROM
					offices_directions off_dir
				WHERE
					off_dir.id_office = {$nIDOffice}
				GROUP BY
					off_dir.id_office
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['ids'] ) )
			{
				return explode( ",", $aData['ids'] );
			}
			else return array();
		}
		
		public function deleteByOffice( $nIDOffice )
		{
			global $db_sod;
			
			if( empty( $nIDOffice ) || !is_numeric( $nIDOffice ) )
			{
				return DBAPI_ERR_SUCCESS;
			}
			
			$sQuery = "
				DELETE
				FROM
					offices_directions
				WHERE
					id_office = {$nIDOffice}
			";
			
			$oRS = $db_sod->Execute( $sQuery );
			
			if( !$oRS )return DBAPI_ERR_SQL_QUERY;
			else return DBAPI_ERR_SUCCESS;
		}
		
		public function deleteByDirection( $nIDDirection )
		{
			global $db_sod;
			
			if( empty( $nIDDirection ) || !is_numeric( $nIDDirection ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$sQuery = "
				DELETE
				FROM
					offices_directions
				WHERE
					id_direction = {$nIDDirection}
			";
			
			$oRS = $db_sod->Execute( $sQuery );
			
			if( !$oRS )return DBAPI_ERR_SQL_QUERY;
			else return DBAPI_ERR_SUCCESS;
		}
		
		public function isDirectionInAnOffice( $nIDDirection )
		{
			if( empty( $nIDDirection ) || !is_numeric( $nIDDirection ) )
			{
				return false;
			}
			
			$sQuery = "
				SELECT
					*
				FROM
					offices_directions off_dir
				WHERE
					off_dir.id_direction = {$nIDDirection}
			";
			
			$aData = $this->select( $sQuery );
			
			if( !empty( $aData ) )return true;
			else return false;
		}
	}
?>