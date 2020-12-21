<?php
	class DBAssetsNomenclaturesAttributes extends DBBase2 
	{
		public  function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_nomenclatures_attributes' );
		}
		
		public function getAttrIDsByNomId($nID)
		{
			$sQuery="SELECT 
						id,
						id_attribute
					FROM 
						assets_nomenclatures_attributes
					WHERE
						id_nomenclature={$nID}
						 	";
			return $this->selectAssoc($sQuery);
		}
		
		
		public function getCountAttributesByID ($nID) {
			$sQuery = "
				SELECT
					COUNT(*) as count
				FROM assets_nomenclatures_attributes an
				WHERE 1
					AND an.id_attribute = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getAtrInfoByIDs($sIDs)
		{
			
			$sQuery="
			SELECT 
						a.id, 
						a.id AS id_attribute,
						a.name AS name,
						a.is_require AS is_require,
						a.type AS type,
						a.type_values AS type_values,
						a.id_measure AS id_measure,
						m.code AS code
					FROM 
						attributes a
					LEFT JOIN
						measures m
					ON a.id_measure = m.id
					WHERE 1
					AND a.to_arc=0";
			if(!empty($sIDs))
			{
				$sQuery.=' AND a.id IN ('.$sIDs.')';
				 	
			} 	
			
//			for($i=1;$i<count($aIDs);$i++)
//			{
//				if(!empty($aIDs[$i]['id_attribute']))
//					$sQuery.=" OR a.id={$aIDs[$i]['id_attribute']}";
//				else return;
//			}
			APILog::Log(0,$sQuery);
			$aRs= $this->selectAssoc($sQuery);
			if($aRs==false) throw new Exception();
			return $aRs;
			
		}
		
		public function getAttrIDsByNomIdArray($nID)
		{
			$sQuery="SELECT 
						id,
						id_attribute
					FROM 
						assets_nomenclatures_attributes
					WHERE
						id_nomenclature={$nID} 	";
			return $this->select($sQuery);
		}
		
		public function getAtrInfoByIDsArray($aIDs)
		{
			
			$sQuery="
			SELECT 
						a.id, 
						a.id AS id_attribute,
						a.name AS name,
						a.is_require AS is_require,
						a.type AS type,
						a.type_values AS type_values,
						a.id_measure AS id_measure,
						m.code AS code
					FROM 
						attributes a
					LEFT JOIN
						measures m
					ON a.id_measure = m.id
					WHERE
				 	a.id IN ({$aIDs})
				 	";
			
//			for($i=1;$i<count($aIDs);$i++)
//			{
//				if(!empty($aIDs[$i]['id_attribute']))
//					$sQuery.=" OR a.id={$aIDs[$i]['id_attribute']}";
//				else return;
//			}
			APILog::Log(0,$sQuery);
			$aRs= $this->select($sQuery);
			if($aRs==false) throw new Exception();
			return $aRs;
			
		}
	}
?>