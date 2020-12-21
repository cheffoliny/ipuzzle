<?php

	class DBContractPrint extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, 'contract_print' );
		}
		
		public function processLastNum()
		{
			$sQuery = "
					SELECT
						*
					FROM contract_print
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			$nNum = 0;
			if( !empty( $aData ) )
			{
				$nNum = $aData['last_num'];
				$aData['last_num']++;
				
				$this->update( $aData );
			}
			
			return $nNum;
		}
		
		public function updateHead( $nIDFirm, $sName, $sPosition )
		{
			$sQuery = "
					SELECT
						*
					FROM contract_print
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			$aHeadNames 	= unserialize( $aData['head_name'] );
			$aHeadPositions = unserialize( $aData['head_position'] );
			if( !$aHeadNames )$aHeadNames = array();
			if( !$aHeadPositions )$aHeadPositions = array();
			
			$aHeadNames[$nIDFirm] = $sName;
			$aHeadPositions[$nIDFirm] = $sPosition;
			
			$aData['head_name'] = serialize( $aHeadNames );
			$aData['head_position'] = serialize( $aHeadPositions );
			
			$this->update( $aData );
		}
		
		public function updateHeadTRZ( $sNewName )
		{
			$sQuery = "
					SELECT
						*
					FROM contract_print
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			$aData['head_trz'] = $sNewName;
			
			$this->update( $aData );
		}
		
		public function updateHeadAccountant( $sNewName )
		{
			$sQuery = "
					SELECT
						*
					FROM contract_print
					LIMIT 1
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			$aData['head_accountant'] = $sNewName;
			
			$this->update( $aData );
		}
	}

?>