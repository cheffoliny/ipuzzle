<?php

class ApiObjectsTraning {
	
	public function load(DBResponse $oResponse) {
			global $db_sod, $db_name_sod, $db_auto_trans, $db_name_auto_trans;
			
			$nIDOffice	= Params::get("office", 0);			

			$oOffices 	= new DBOffices();
			$aOffices 	= array();			

			$aOffices 	= $oOffices->getReactionOffices();

			$oResponse->setFormElement("form1", "nIDOffice");			
			
			$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => 0), "--- Изберете ---");

			foreach( $aOffices as $key => $val ) {	
				if ( $key == $nIDOffice ) {
					$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => $key, "selected" => "selected"), $val);
				} else {
					$oResponse->setFormElementChild("form1", "nIDOffice", array("value" => $key), $val);
				}
			}
			
			$oResponse->printResponse();
		}
	
	public function result(DBResponse $oResponse){
		global $db_sod,$db_name_sod,$db_personnel,$db_name_personnel;
		$oBase = new DBBase2($db_personnel,'personnel');
		$nOffice   = Params::getInstance()->nIDOffice;

		$oResponse->setField('person',			'Служител');
		//$oResponse->setField('manager_ap',		'Началник автопатрул');
		$oResponse->setField('unknown_objects',	'Не познава',	null,null,'onClickOpenUknown', NULL, array( "DATA_FORMAT" => DF_CENTER ) );
		$oResponse->setField('known_objects',	'Познава',		null,null,'onClickOpenKnown', NULL, array( "DATA_FORMAT" => DF_CENTER ) );
        $oResponse->setField('visited_objects',	'Посетени',		null,null,'onClickOpenVisited', NULL, array( "DATA_FORMAT" => DF_CENTER ) );
		$oResponse->setField('reacted_objects',	'Реагирал',		null,null,'onClickOpenReacted', NULL, array( "DATA_FORMAT" => DF_CENTER ) );

		$sQuery = "
			SELECT SQL_CALC_FOUND_ROWS
				CONCAT(p.id,'@',p.id_office) AS id,
                CONCAT(p.fname,' ',p.mname,' ',p.lname) AS person,
                (	
					 	SELECT COUNT(vo.id) 
						FROM sod.objects o
						LEFT JOIN sod.visited_objects vo ON vo.id_object = o.id
						WHERE o.id_office = p.id_office AND o.id_status <> 4 AND o.is_sod = 1 AND vo.id_person = p.id AND vo.`type` = 'reacted'
			      )  AS 'reacted_objects',
			      (	
					 	SELECT COUNT(vo.id) 
						FROM sod.objects o
						LEFT JOIN sod.visited_objects vo ON vo.id_object = o.id
						WHERE o.id_office = p.id_office AND o.id_status <> 4 AND o.is_sod = 1 AND vo.id_person = p.id AND vo.`type` = 'visited'
			      )  AS 'visited_objects',
			      (	
					 	SELECT COUNT(vo.id) 
						FROM sod.objects o
						LEFT JOIN sod.visited_objects vo ON vo.id_object = o.id
						WHERE o.id_office = p.id_office AND o.id_status <> 4 AND o.is_sod = 1 AND vo.id_person = p.id AND vo.`type` = 'familiar'
			      )  AS 'known_objects',
			      (	
					 	SELECT (COUNT(DISTINCT o.id) - SUM(IF(vo.id_person = p.id, 1, 0)))
						FROM sod.objects o
						LEFT JOIN sod.visited_objects vo ON vo.id_object = o.id
						WHERE o.id_office = p.id_office AND o.id_status <> 4 AND o.is_sod = 1
			      )  AS 'unknown_objects'
			      
                #COUNT(o.id), 
               # SUM( IF( vo.`type` = 'familiar', 1, 0) ) AS 'known_objects',
               # SUM( IF( vo.`type` = 'visited', 1, 0) ) AS 'visited_objects',
               # SUM( IF( vo.`type` = 'reacted', 1, 0) ) AS 'reacted_objects',
               # (COUNT(DISTINCT o.id) - COUNT(DISTINCT vo.id)) AS 'unknown_objects'
                
			FROM {$db_name_personnel}.personnel p
			JOIN {$db_name_personnel}.positions pp ON pp.id = p.id_position AND pp.position_function = 'patrul'
	 		LEFT JOIN {$db_name_sod}.objects o ON o.id_office = p.id_office AND o.id_status <> 4 AND o.is_sod = 1
			LEFT JOIN {$db_name_sod}.visited_objects vo ON vo.id_object = o.id 
        ";
		$aWhere = array();
		$aWhere[] = "p.to_arc = 0";
		$aWhere[] = "(p.vacate_date = '0000-00-00' OR p.vacate_date > DATE(NOW()))";
		$aWhere[] = "p.status='active'";		

		if (!empty($nOffice))	$aWhere[] = "p.id_office = $nOffice ";
		if (!empty($aWhere)) $sQuery.= "\nWHERE ".implode(" \nAND ",$aWhere);
		$sQuery .= "\nGROUP BY p.id\n";

		APILog::Log(2222, $sQuery);
		$oBase->getResult($sQuery,'person',DBAPI_SORT_DESC,$oResponse);
		$oResponse->printResponse();
	}
}
