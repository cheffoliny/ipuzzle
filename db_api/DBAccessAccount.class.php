<?php
	class DBAccessAccount extends DBBase2
	{
		public function __construct()
		{
			global $db_system;
			
			parent::__construct( $db_system, 'access_account' );
		}
		
		public function getCountProfileByID ($nID) {
			$sQuery = "
			SELECT
				count(*) as count
				FROM access_account aa
				WHERE 1
					AND aa.to_arc = 0
					AND aa.id_profile = {$nID}
			";
			return $this->selectOne($sQuery);
		}
		
		public function getIDProfile( $nIDAccount )
		{
			if( empty( $nIDAccount ) || !is_numeric( $nIDAccount ) )return 0;
			
			$sQuery = "
				SELECT
					id_profile
				FROM access_account
				WHERE 1
					AND to_arc = 0
					AND id = {$nIDAccount}
				LIMIT 1
			";
			
			$aResult = $this->selectOnce( $sQuery );
			
			if( !empty( $aResult ) )
			{
				return $aResult['id_profile'];
			}
			else return 0;
		}
		
		/****************************************************************************
		* Ф-ята проверява идентичност на потребителски account.                     *
		*                                                                           *
		* - Ако потребителя е идентифициран, връща id_person.                       *
		* - Ако потребителя не е идентифициран, или не е намерен, връща 0.          *
		*****************************************************************************/
		
		public function checkIdentity( $sUsername, $sPassword )
		{
			$sPasswordDec = md5( $sPassword );
			
			$sQuery = "
					SELECT
						id_person
					FROM
						access_account
					WHERE
						to_arc = 0
						AND username = '{$sUsername}'
						AND password = '{$sPasswordDec}'
			";
			
			$aData = $this->selectOnce( $sQuery );
			
			if( !empty( $aData ) && isset( $aData['id_person'] ) )
			{
				return $aData['id_person'];
			}
			else return 0;
		}
	}
?>