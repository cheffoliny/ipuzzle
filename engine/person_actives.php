<?php
	$errMsg = '';

	// Проверка на правата за достъп
	$personnel_edit 		= false; // Право на редактиране на информацията за служител
	$personnel_view			= false; // Право за показване на служителите

	if (!empty($_SESSION['userdata']['access_right_levels'] )) {
		if ( in_array('person_actives_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$personnel_view = true;
			$personnel_edit = true;
		}
		
		if (in_array('person_actives_view', $_SESSION['userdata']['access_right_levels'])) 
			$personnel_view = true;		
	}

	// Табове!!!
	$tabs['info'] = false;
	$tabs['data'] = false;
	$tabs['docs'] = false;
	$tabs['ates'] = false;
	$tabs['contr'] = false;
	$tabs['leave'] = false;
	$tabs['activ'] = false;
	$tabs['salary'] = false;
	$tabs['reports'] = false;
	$tabs['calls'] = false;

	if (in_array('personInfo_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['info'] = true;
	}
	
	if (in_array('person_data_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['data'] = true;
	}
	
	if (in_array('person_docs_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['docs'] = true;
	}
	
	if (in_array('client_orders_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['ates'] = true;
	}
	
	if (in_array('person_contract_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['contr'] = true;
	}
	
	if (in_array('person_leave_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['leave'] = true;
	}
	
	if (in_array('person_actives_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['activ'] = true;
	}
	
	if (in_array('person_salary_view', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['salary'] = true;
	}
	
	if (in_array('region_manager_report', $_SESSION['userdata']['access_right_levels'])) {
		$tabs['reports'] = true;
	}
	
	if( in_array( 'phone_system_logs', $_SESSION['userdata']['access_right_levels'] ) )
	{
		$tabs['calls'] = true;
	}
	
	$template->assign("tabs", $tabs);
		
	if ( $personnel_view ) {
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$template->assign("id", $id);	

		$oPerson = New DBBase( $db_personnel, 'personnel' );
		$data = array();
		
		if( $nResult = $oPerson->getRecordByField( $id, 'id', $data ) != DBAPI_ERR_SUCCESS ) {
			return $nResult;
		}
		
		if ( !empty($data) ) {
			$person_name = 	$data['fname']." ".$data['mname']." ".$data['lname'];
			$template->assign("person_name", $person_name);
		}				
	} else {
		$errMsg='Ограничени права!';
	}
	
	$enable_refresh = isset( $_GET['enable_refresh'] ) ? $_GET['enable_refresh'] : 1;
	$template->assign( "enable_refresh", $enable_refresh );
	
	$template->assign("errMsg", $errMsg);
	$template->assign("personnel_edit", $personnel_edit);
	$template->assign("personnel_view", $personnel_view);
	$template->assign("OPTION_NULL", OPTION_NULL);	
?>
