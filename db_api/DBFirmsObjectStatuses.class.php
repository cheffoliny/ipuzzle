<?php

	class DBFirmsObjectStatuses extends DBBase2
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct( $db_sod, 'firms_object_statuses' );
		}
		
		public function delByIDFirm( $nIDFirm )
		{
			global $db_name_sod;
			
			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
			{
				return false;
			}
			
			$sQuery = "
				DELETE
				FROM {$db_name_sod}.firms_object_statuses
				WHERE id_firm = {$nIDFirm}
			";
			
			$this->oDB->Execute( $sQuery );
		}
		
		public function getStatusesByIDFirm( $nIDFirm )
		{
			if( empty( $nIDFirm ) || !is_numeric( $nIDFirm ) )
			{
				return array();
			}
			
			$sQuery = "
				SELECT
					id_status
				FROM
					firms_object_statuses
				WHERE
					id_firm = {$nIDFirm}
			";
			
			$aRawData = $this->select( $sQuery );
			$aData = array();
			foreach( $aRawData as $nKey => $aIDStatus )
			{
				$aData[] = $aIDStatus['id_status'];
			}
			
			return $aData;
		}
		
		public function getAllFirmObjectStatuses()
		{
			$sQuery = "
				SELECT
					DISTINCT id_status AS id_status
				FROM
					firms_object_statuses
			";
			
			$aData = $this->select( $sQuery );
			$aReturnData = array();
			
			foreach( $aData as $nKey => $aValue )
			{
				$aReturnData[] = $aValue['id_status'];
			}
			
			return $aReturnData;
		}
	}

?>