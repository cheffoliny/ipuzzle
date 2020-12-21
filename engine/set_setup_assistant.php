<?php

	$nID = !empty( $_GET['id'] ) ? $_GET['id'] : 0;
	
	//Read Entered Cities
	$oAssistantsCities = new DBAssistantsCities();
	
	$sQuery = "
				SELECT
					c.id
				FROM assistants_cities a
					LEFT JOIN {$db_name_sod}.cities c ON c.id = a.id_city
				WHERE a.to_arc = 0
					AND a.id_assistant = {$nID}
 					AND c.to_arc = 0
 					AND c.id_office != 0
 				ORDER BY c.name ASC
	";
	
	$aNeededCities = $oAssistantsCities->select( $sQuery );
	
	$sOfficesParam = '';
	foreach( $aNeededCities as $aNeededCity )
	{
		$sOfficesParam .= $aNeededCity['id'] . ' ';
	}
	//End Read Entered Cities
	
	$template->assign( "sOfficesParam", $sOfficesParam );
	$template->assign( "nID", $nID );
	
?>