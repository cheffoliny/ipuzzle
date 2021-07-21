<?php
$errMsg = '';
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : 0;
$id_person = isset ($_SESSION['userdata']['id_person']) ? $_SESSION['userdata']['id_person'] : 0;
//$db_personnel->debug=true;
// Проверка на правата за достъп
$personnel_edit 		= false; // Право на редактиране на информацията за служител
$personnel_view			= false; // Право за показване на служителите

$oPerson = New DBPersonnel();

if (!empty($_SESSION['userdata']['access_right_levels'] )) {
	if ( in_array('personInfo_edit', $_SESSION['userdata']['access_right_levels']) ) {
		$personnel_view = true;
		$personnel_edit = true;
	}

	if (in_array('personInfo_view', $_SESSION['userdata']['access_right_levels']))
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

if (in_array('person_honorariums', $_SESSION['userdata']['access_right_levels'])) {
	$tabs['honorariums'] = true;
}

if( in_array( 'phone_system_logs', $_SESSION['userdata']['access_right_levels'] ) )
{
	$tabs['calls'] = true;
}

if ( $_SESSION['userdata']['access_right_all_regions'] != 1 ) {
	if ( $id > 0 ) {
		$odata = $oPerson->getByID( $id );
		$office = isset($odata['id_office']) && is_numeric($odata['id_office']) ? $odata['id_office'] : 0;
	} else {
		//debug($_SESSION);
		$office = $_SESSION['userdata']['id_office'];
	}

	if ( !in_array($office, $_SESSION['userdata']['access_right_regions']) ) {
		$personnel_view = false;
		$personnel_edit = false;
		//$errMsg='Ограничени права!';
		//exit;
	}

}

$template->assign("tabs", $tabs);
$template->assign("id", $id);

if ( $personnel_view ) {
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

$oPerson = New DBBase( $db_personnel, 'person_images' );
$aData = array();

if( $nResult = $oPerson->getRecordByField( $id, 'id_person', $aData ) != DBAPI_ERR_SUCCESS ) {
	return $nResult;
}

$image = "images/default_person.png";
$filename = $_SESSION['BASE_DIR']."/person_images/{$id}.jpg";

if ( !empty($aData['image']) ) {

	if ( !$handle = fopen($filename, 'w') ) {
		exit;
	}

	if ( fwrite($handle, $aData['image']) === FALSE ) {
		exit;
	}

	fclose($handle);
	$image = "person_images/{$id}.jpg";
}

$enable_refresh = isset( $_GET['enable_refresh'] ) ? $_GET['enable_refresh'] : 1;
$template->assign( "enable_refresh", $enable_refresh );

$template->assign("errMsg", $errMsg);
$template->assign("image", $image);
$template->assign("personnel_edit", $personnel_edit);
$template->assign("personnel_view", $personnel_view);
$template->assign("OPTION_NULL", OPTION_NULL);
$template->assign("nIDPerson", $id_person);
?>
