<?php
	$view_object = false;
	$edit_object = false;
	
	$edit = array();
	$view = array();

	$nID = !empty($_GET['nID']) ? $_GET['nID'] : 0;
	$mobile = !empty($_GET['mobile']) ? $_GET['mobile'] : 0;
	$template->assign("mobile", $mobile);

	$oObject = new DBObjectDuty();
	$aObject = $oObject->getObjectName( $nID );
	$aOld = $oObject->getObjectOLD( $nID );
	
	if ( !isset($aObject['num']) ) {
		$nID = 0;
	}
	
	$isSOD = isset($aObject['is_sod']) ? $aObject['is_sod'] : 0;
	$isFO = isset($aObject['is_fo']) ? $aObject['is_fo'] : 0;

	if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
		// ����������
		if ( in_array('object_info_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view['object_info_view'] = true;
		}

		if ( in_array('object_info_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_info_edit'] = true;
			$view['object_info_view'] = true;
		}

		// ���������-������
		if ( in_array('object_personnel_schedule_view', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$view['object_personnel_schedule_view'] = true;
		}

		if ( in_array('object_personnel_schedule_edit', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$edit['object_personnel_schedule_edit'] = true;
			$view['object_personnel_schedule_view'] = true;
		}

		// ������ �����
		if ( in_array('object_shifts_view', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$view['object_shifts_view'] = true;
		}

		if ( in_array('object_shifts_edit', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$edit['object_shifts_edit'] = true;
			$view['object_shifts_view'] = true;
		}

		// �����
		if ( in_array('object_duty_view', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$view['object_duty_view'] = true;
		}

		if ( in_array('object_duty_edit', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$edit['object_duty_edit'] = true;
			$view['object_duty_view'] = true;
		}

		// ���������
		if ( in_array('object_personnel_view', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$view['object_personnel_view'] = true;
		}

		if ( in_array('object_personnel_edit', $_SESSION['userdata']['access_right_levels']) && $isFO ) {
			$edit['object_personnel_edit'] = true;
			$view['object_personnel_view'] = true;
		}

		// �������
		if ( in_array('object_contract_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view['object_contract_view'] = true;
		}

		if ( in_array('object_contract_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_contract_edit'] = true;
			$view['object_contract_view'] = true;
		}

		// �����
		if ( in_array('object_taxes_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view['object_taxes_view'] = true;
		}

		if ( in_array('object_taxes_add_client', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_taxes_add_client'] = true;
			$view['object_taxes_view'] = true;
		}
		
		if ( in_array('object_taxes_month_obligations_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_taxes_month_obligations_edit'] = true;
			$view['object_taxes_view'] = true;
		}
		
		if ( in_array('object_taxes_single_obligations_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_taxes_single_obligations_edit'] = true;
			$view['object_taxes_view'] = true;
		}

		// ���������
		if ( in_array('object_messages_view', $_SESSION['userdata']['access_right_levels']) && $isSOD ) {
			$view['object_messages_view'] = true;
		}

		if ( in_array('object_messages_edit', $_SESSION['userdata']['access_right_levels']) && $isSOD ) {
			$edit['object_messages_edit'] = true;
			$view['object_messages_view'] = true;
		}

		// �����
		if ( in_array('object_archiv_view', $_SESSION['userdata']['access_right_levels']) && $isSOD ) {
			$view['object_archiv_view'] = true;
		}

		if ( in_array('object_archiv_edit', $_SESSION['userdata']['access_right_levels']) && $isSOD ) {
			$edit['object_archiv_edit'] = true;
			$view['object_archiv_view'] = true;
		}

		// ��������
		if ( in_array('object_troubles_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view['object_troubles_view'] = true;
		}

		if ( in_array('object_troubles_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_troubles_edit'] = true;
			$view['object_troubles_view'] = true;
		}

		// ����������
		if ( in_array('object_support_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view['object_support_view'] = true;
		}

		if ( in_array('object_support_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_support_edit'] = true;
			$view['object_support_view'] = true;
		}

		// ������
		if ( in_array('object_store_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view['object_store_view'] = true;
		}

		if ( in_array('object_store_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit['object_store_edit'] = true;
			$view['object_store_view'] = true;
		}

		// Call Center
		if ( in_array('phone_system_logs', $_SESSION['userdata']['access_right_levels']) ) {
			$view['phone_system_logs'] = true;
		}

		// ���� ����� - �� �� �������!
		if ( in_array('objects_view', $_SESSION['userdata']['access_right_levels']) ) {
			$view_object = true;
		}

		if ( in_array('objects_edit', $_SESSION['userdata']['access_right_levels']) ) {
			$edit_object = true;
			$view_object = true;
		}
	}
	
	$template->assign( "edit", $edit );
	$template->assign( "view", $view );	
	$template->assign("cnt", count($view));	
	
	$template->assign( "edit_object", $edit_object );
	$template->assign( "view_object", $view_object );

	$template->assign( "nID", $nID );
	$template->assign( "isSOD", $isSOD );
	$template->assign( "isFO", $isFO );
	$template->assign( "object", @$aObject['object'] );
?>