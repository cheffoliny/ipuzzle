<?php

	class DBAssetsGroups extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_groups' );
		}
		
		public function getGroups() {
			
			$sQuery = " 
				SELECT 
					id,
					name
				FROM assets_groups
				WHERE to_arc = 0
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getRootGroups()
		{
			
			$sQuery = " 
				SELECT 
					id,
					name
				FROM assets_groups
				WHERE to_arc = 0
					AND parent_id = 0
			";
			
			return $this->select( $sQuery );
		}
		
		public function getGroupsAlphabetic()
		{
			$sQuery = " 
				SELECT 
					id,
					name
				FROM assets_groups
				WHERE to_arc = 0
				ORDER BY name
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
		public function getChilds($nIDGroup) {
			
			$sQuery = " 
				SELECT 
					id,
					name
				FROM assets_groups
				WHERE 1
					AND to_arc = 0
					AND parent_id = {$nIDGroup}
			";
			
			return $this->selectAssoc($sQuery);
		}
		
		public function getParent($nIDGroup)
		{
			$sQuery = "SELECT name 
						FROM assets_groups
						WHERE id= (SELECT parent_id FROM assets_groups WHERE id={$nIDGroup})";
			
			APILog::Log(0,$this->selectOne($sQuery));
			return $this->selectOne($sQuery);
		}
		
		public function deleteGroup($nIDGroups)
		{
			
			$sQuery = "
					UPDATE assets_groups
					SET to_arc=1
					WHERE id = {$nIDGroups}
					OR parent_id = {$nIDGroups}
			";
			throw new Exception($sQuery);
			$oRs=$this->oDB->Execute($sQuery);
			if($oRs==false)
			{
				throw new Exception("Грешка при изпълнение на Задачата ".$this->oDB->ErrorMsg());
			}
		}
		
		public function getGroupByIdGroup($id_group) {
			
			$sQuery = " 
				SELECT 
					id,
					name
				FROM assets_groups
				WHERE to_arc = 0
				and id=$id_group
			";
			
			return $this->selectOnce($sQuery);
		}
	}
?>