<?php
	class DBAssistants extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, 'assistants' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_sod;
			
			$right_edit = false;
			if( !empty($_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'setup_assistants', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			//Accessable Regions
			if( $_SESSION['userdata']['access_right_all_regions'] != 1 )
			{
				$sAccessable = implode( ",", $_SESSION['userdata']['access_right_regions'] );
				$sCondition = " AND o.id IN ({$sAccessable}) ";
			}
			else $sCondition = "";
			//End Accessable Regions
			
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						a.id, 
						p.code, 
						CONCAT_WS( ' ', p.fname, p.mname, p.lname ) AS name, 
						a.next_num, 
						IF( 
							pe.id,
							CONCAT(
								CONCAT_WS( ' ', pe.fname, pe.mname, pe.lname ),
								' (',
								DATE_FORMAT( a.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM assistants a
						LEFT JOIN personnel p ON a.id_person = p.id AND p.to_arc = 0
						LEFT JOIN personnel pe ON a.updated_user = pe.id
						LEFT JOIN {$db_name_sod}.offices o ON p.id_office = o.id AND o.to_arc = 0
						LEFT JOIN {$db_name_sod}.firms f ON o.id_firm = f.id AND f.to_arc = 0
					WHERE a.to_arc = 0
					{$sCondition}
			";
			
			if( isset( $aParams['nIDFirm'] ) && $aParams['nIDFirm'] )
			{
				$sQuery .= "
						AND f.id = {$aParams['nIDFirm']}
				";
			}
			if( isset( $aParams['nIDRegion'] ) && $aParams['nIDRegion'] )
			{
				$sQuery .= "
						AND o.id = {$aParams['nIDRegion']}
				";
			}
			
			$this->getResult( $sQuery, 'name', DBAPI_SORT_ASC, $oResponse );
			
			$oResponse->setField( "code", 			"Код на РС", 									"Сортирай по Код" );
			$oResponse->setField( "name", 			"Име на РС", 									"Сортирай по Име" );
			$oResponse->setField( "next_num", 		"Следващ номер за ел. Договор", 				"Сортирай по Следващ Номер" );
			$oResponse->setField( "cities", 		"Населени места, в които работи сътрудника", 	"Сортирай по Региони" );
			$oResponse->setField( "updated_user", 	"Последна редакция", 							"Сортирай по последна редакция" );
			
			//Edit The Received Data
			$aFinalData = $oResponse->oResult->aData;
			foreach( $oResponse->oResult->aData as $key => $value )
			{
				$oAssistantsCities = new DBAssistantsCities();
				
				$sCityQuery = "
							SELECT
								c.name
							FROM assistants_cities a
								LEFT JOIN {$db_name_sod}.cities c ON c.id = a.id_city AND c.to_arc = 0
							WHERE a.to_arc = 0
								AND a.id_assistant = {$value['id']}
							ORDER BY c.name ASC
				";
				
				$aNeededCities = $oAssistantsCities->select( $sCityQuery );
				
				foreach( $aNeededCities as $aNeededCity )
				{
					if( !isset( $aFinalData[$key]['cities'] ) )$aFinalData[$key]['cities'] = '';
					$aFinalData[$key]['cities'] .= $aNeededCity['name'] . '; ';
				}
				if( empty( $aFinalData[$key]['cities'] ) )$aFinalData[$key]['cities'] = "Не са въведени Градове!";
			}
			//End Edit The Received Data
			
			foreach( $oResponse->oResult->aData as $key => $value )
			{
				$oResponse->setDataAttributes( $key, "code", array( 'style' => 'text-align: center;' ) );
			}
			
			if ($right_edit)
			{
				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteAssistant', '' );
				$oResponse->setFieldLink( "name", "openAssistant" );
			}
			
			$oResponse->setData( $aFinalData );
		}
	}
?>