<?php
	class DBAccessLevelProfile extends DBBase2
	{
		public function __construct()
		{
			global $db_system;
			
			parent::__construct( $db_system, 'access_level_profile' );
		}
		
		public function getAccessLevels( $nIDProfile )
		{
			if( !is_numeric( $nIDProfile ) )return array();
			
			if( !empty( $nIDProfile ) )
			{
				$sQuery = "
					SELECT
						id_level
					FROM access_level_profile
					WHERE id_profile = {$nIDProfile}
					ORDER BY id_level
				";
				
				$aData = $this->select( $sQuery );
				
				if( !empty( $aData ) )
				{
					if( $aData[0]['id_level'] == 0 )
					{
						$sQuery = "
								SELECT
									id AS id_level
								FROM access_level
								ORDER BY id_level
						";
						
						return $this->select( $sQuery );
					}
					else
					{
						return $aData;
					}
				}
				else return array();
			}
			else
			{
				$sQuery = "
						SELECT
							id AS id_level
						FROM access_level
						ORDER BY id_level
				";
				
				return $this->select( $sQuery );
			}
		}
		
		public function EditLevelForProfile( $nIDProfile, $nIDLevel, $IsIncluded = 1 )
		{
			//Expand Level List
			$aAccessLevels = $this->getAccessLevels( $nIDProfile );
			$aAllLevels = $this->getAccessLevels( 0 );
			
			$bAlreadyExists = false;
			foreach( $aAccessLevels as $aAccessLevel )
			{
				if( $IsIncluded == 0 )
				{
					if( $nIDLevel != $aAccessLevel['id_level'] )$aNewAccessLevels[]['id_level'] = $aAccessLevel['id_level'];
				}
				else $aNewAccessLevels[]['id_level'] = $aAccessLevel['id_level'];
				if( $nIDLevel == $aAccessLevel['id_level'] )$bAlreadyExists = true;
			}
			if( $IsIncluded == 1 && !$bAlreadyExists )$aNewAccessLevels[]['id_level'] = $nIDLevel;
			
			//Delete All Elements
			$this->select( "DELETE FROM access_level_profile WHERE id_profile = {$nIDProfile}" );
			
			//Sort The New Elements List and Add it
			sort( $aNewAccessLevels );
			if( $aNewAccessLevels == $aAllLevels )
			{
				$aData = array();
				$aData['id_profile'] = $nIDProfile;
				$aData['id_level'] = 0;
				$this->update( $aData );
			}
			else
			{
				foreach( $aNewAccessLevels as $aAccessLevel )
				{
					$aData = array();
					$aData['id_profile'] = $nIDProfile;
					$aData['id_level'] = $aAccessLevel['id_level'];
					$this->update( $aData );
				}
			}
		}
	}
?>