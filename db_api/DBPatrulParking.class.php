<?php
	class DBPatrulParking
		extends DBBase2 {
			
		public function __construct() {
			global $db_sod;
			//$db_sod->debug=true;
			
			parent::__construct($db_sod, 'patrul_parking');
		}
		
		public function getReport( $nIDOffice,$nIDFirm, DBResponse $oResponse ) {
			global $db_name_personnel;
			$nIDOffice = (int) $nIDOffice;
			$nIDFirm = (int) $nIDFirm;
			
			$right_edit = false;
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('patrul_parking_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$oData = array();
			
			$sQuery = "
				SELECT
					pp.id, 
					pp.name,
					pp.id_office,
					of.name AS office,
					pp.description,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(pp.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM patrul_parking pp
				LEFT JOIN {$db_name_personnel}.personnel as up ON pp.updated_user = up.id
				LEFT JOIN offices of ON pp.id_office = of.id
				WHERE 1
					AND pp.to_arc = 0 AND of.id_firm = {$nIDFirm}
			";
			
			if ( !empty($nIDOffice) ) {
				$sQuery .= " AND pp.id_office = '{$nIDOffice}' ";
			}

			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			//debug($oData);

			$oResponse->setField('name',			'име',			'сортирай по име');
			$oResponse->setField('office',			'регион',		'сортирай по регион');
			$oResponse->setField('description',		'описание',		'сортирай по описание');
			$oResponse->setField('updated_user',	'...',			'Сортиране по последно редактирал', 'images/dots.gif' );
			//$oResponse->setField('updated_user'	, 'последно редактирал'	, 'сортирай по последно редактирал'	);
			if( $right_edit ) {
				$oResponse->setField( 'id',			'',			'', 'images/edit.gif', 'editParking', '');
				$oResponse->setField( '',			'',			'', 'images/cancel.gif', 'delParking', '');
			}
			
			$oResponse->setFIeldLink('name',		'editParking' );
		}
		
		public function getOffices( ) {
			//global $db_name_personnel;
			
			$sQuery = "
				SELECT
					of.id, 
					of.code,
					of.name
				FROM offices of
				WHERE 1
					AND of.to_arc = 0
					AND of.id_firm = 1
			";
			
			return $this->selectAssoc( $sQuery );
		}


		public function getParkingByOffice( $nID ) {
			
			$sQuery = "
				SELECT
					pp.id,
					pp.name
				FROM patrul_parking pp
				WHERE 1
					AND pp.to_arc = 0
					AND pp.id_office = {$nID}
			";
			
			return $this->selectAssoc( $sQuery );
		}
	}
	
?>