<?php

	class DBFaces extends DBBase2 {
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			parent::__construct($db_sod, "faces");
		}	
		
		public function getReport(DBResponse $oResponse, $nIDObject ,$nIDFace) {
			global  $db_name_personnel;
			
			$right_edit = false;
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('objects_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}	
			
			$sQuery = "
				SELECT 
					f.id,
					f.name,
					f.phone,
					'' as MOL,
					f.post,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(f.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM faces f
				LEFT JOIN {$db_name_personnel}.personnel as up ON f.updated_user = up.id
				WHERE 1 
					AND f.id_obj = {$nIDObject}
					AND f.to_arc = 0
			";
			
			$this->getResult($sQuery, 'name', DBAPI_SORT_ASC, $oResponse);
			
			foreach ($oResponse->oResult->aData as $key => $value) {
				if($value['id'] == $nIDFace) {
					$oResponse->setDataAttributes( $key,'MOL', array('style' => 'background:url(images/confirm.gif) no-repeat center;'));
				}
			}
			
			$oResponse->setField("name", "Име", "Сортирай по име",NULL,NULL,NULL,array("style" => "width:200px;"));
			$oResponse->setField("post", "Длъжност", "Сортирай по длъжност",NULL,NULL,NULL,array("style" => "width:100px;"));
			$oResponse->setField("MOL","МОЛ","Сортирай по МОЛ",NULL,NULL,NULL,array("style" => "width:50px;"));
			$oResponse->setField("phone", "Телефон", "Сортирай по телефон");
			//$oResponse->setField("updated_user", "Последно редактирал", "Сортирай по последно редактирал");
			
			if($right_edit == true) {
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'delFace', '');
				$oResponse->setFieldLink('name','editFace');
			}
		}
		
		public function getFaces( $nID ) {
			
			$sQuery = "
				SELECT 
					f.name,
					f.phone,
					f.post
				FROM faces f
				WHERE 1 
					AND f.id_obj = {$nID}
					AND f.to_arc = 0
			";

			return $this->select($sQuery);
		}
		
		public function getFace( $nID ) {
			$sQuery = "
				SELECT
					*
				FROM faces
				WHERE id = {$nID}
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function fromContract($nIDObject,$nIDContract) {
			
			global $db_name_finance;
			
			$sQuery = "
			
				INSERT INTO faces (id_obj,name,phone)
				SELECT 
					{$nIDObject},
					name,
					phone
				FROM {$db_name_finance}.contracts_faces
				WHERE id_contract = {$nIDContract}		
					AND LENGTH(name) > 0	
			";
			
			$this->oDB->Execute($sQuery);
		}
		public function deleteFaces($nIDObject) {
			
			$sQuery = "
				UPDATE
					faces
				SET to_arc = 1
				WHERE id_obj = {$nIDObject}
			";
			
			$this->oDB->Execute($sQuery);
		}
	}
?>