<?php
		class DBAssetsAttributes extends DBBase2 
		{
			public function __construct()
			{
				global $db_storage;
				$db_storage->debug=true;
				parent::__construct( $db_storage, 'assets_attributes' );
			}
				
				public function updateAttributes($aAttributes)
				{
					global $db_storage;
					$aAttributesIDs = array();
					for($i=1; $i < count($aAttributes); $i++)
					{
						//$sQuery.=' OR id_attribute='.$aAttributes[$i]['id_attribute'];
						array_push($aAttributesIDs,$aAttributes[$i]['id_attribute']);
					}
					$sAttributes = implode(',',$aAttributesIDs);
					$sQuery="
							UPDATE assets_attributes
							SET to_arc=1
							WHERE 1
							AND id_asset={$aAttributes[0]['id_asset']}
					";
					
					$db_storage->startTrans();
					$oRs=$this->oDB->Execute($sQuery);
					if($db_storage->HasFailedTrans() == false){
						$db_storage->CompleteTrans();
						for($i=0;$i<count($aAttributes); $i++)
						{
							if(!empty($aAttributes[$i]['value']))
							{
								$this->update($aAttributes[$i]);
							}
						}
					}
					else {
						$db_storage->CompleteTrans();
						throw new Exception('Грешка при изпълнение на операцията! Моля опитайте по-късно!');
					}
				}
				
				public function getAttributesByIDAsset($nID)
				{
					
					$sQuery = "
						SELECT 
							a.id_attribute,
							atr.name,
							atr.is_require,
							atr.type,
							atr.type_values,
							a.value,
							ast.id_nomenclature,
							m.code AS code
						FROM 
							assets_attributes a
						LEFT JOIN attributes atr ON	atr.id = a.id_attribute
						LEFT JOIN assets ast ON	a.id_asset=ast.id
						LEFT JOIN measures m ON m.id = atr.id_measure
						WHERE 1
							AND	a.id_asset={$nID}
							AND a.to_arc=0
							AND atr.to_arc=0
					";
				
					return  $this->select($sQuery);
				}
				
				public function getAttributes($nID) {
					
					$sQuery = "
						SELECT 
							a.id_attribute,
							a.value
						FROM assets_attributes a
						WHERE 1
							AND a.id_asset = {$nID}
							AND a.to_arc = 0
					";
					
					return $this->select($sQuery);
				}
				
		}
?>