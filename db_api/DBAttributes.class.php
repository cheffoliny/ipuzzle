<?php

	class DBAttributes extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'attributes' );
		}
		
		public function showAllAttributes(DBResponse $oResponse)
		{
		
			$aFields = array(
						'id'=>'Номер',
						'name'=>'Име',
						'tp'=>'Тип',
						'type_values'=>'Диапазон на типа',
						'is_required'=>'Задължителен',
						'des'=>'Мерна единица'	);
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					a.id,
					a.name,
					CASE a.type
					WHEN 'text' THEN 'текст'
					WHEN 'number' THEN 'число'
					WHEN 'list' THEN 'списък'
					END AS tp,
					a.type_values,
					a.id_measure,
					 a.is_require AS is_required,
					m.description AS des
				FROM 
									attributes a
				LEFT JOIN	measures m ON m.id = a.id_measure
				WHERE to_arc=0
				
			";
			
			$this->getResult($sQuery, 'id', DBAPI_SORT_ASC, $oResponse);
			foreach ($aFields as $k=>$v)
			{	
				if($k == "is_required")$oResponse->setField($k,$v,"Сортирай по ".mb_strtolower($v,"UTF-8"),'images/confirm.gif');
				$oResponse->setField($k,$v,"Сортирай по ".mb_strtolower($v,"UTF-8"));
			}
			$oResponse->setField("","","","images/cancel.gif","deleteAttribute","");
			$oResponse->setFieldLink('name','openAttribute');
			
		}
		
		public function setAttributeValues( DBResponse $oResponse,$nID, &$aData)
		{
			if($nID){
			$aData = array();
			$sQuery="
					SELECT
					a.name AS name,
				    a.type AS type_value,
					a.type_values,
					a.id_measure AS id_measure ,
					a.is_require AS is_required,
					m.description AS measure
				FROM 
									attributes a
				LEFT JOIN	measures m ON m.id = a.id_measure
				WHERE a.id={$nID}
			";
			//DBLog::Log($aData['type_value']);
			$aData = $this->oDB->GetArray($sQuery);
			if($aData == false)
			{
				$oResponse->setError(DBAPI_ERR_SQL_QUERY,"Грешка при Задача към базата от данни ",__FILE__,__LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
			return DBAPI_ERR_SUCCESS;
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		
		public function updateAttributeValues(&$aData)
		{	
			
			
			
			//$nID          = $aData["nID"];
			
			$this->update($aData);
		}
		
		public function getAttributes() 
		{	
			global $db_storage;
			$sQuery = "
				SELECT 
					id,
					name
				FROM storage.attributes 
				WHERE 
					to_arc = 0
			";
			
			return $this->selectAssoc( $sQuery );
		}
		
	}
?>