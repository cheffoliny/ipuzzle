<?php
    $template->assign("nIDSelectObject"		, @$_GET['nIDSelectObject']						);
    $template->assign("nCustomDate"			, @$_GET['nCustomDate']							);
    $template->assign("nHolidayStakeFactor"	, $_SESSION['system']['holiday_stake_factor']	);

    $right_edit 	= false;
    $super_right 	= false;
    $auto_schedule 	= false;
    $save_right		= false;

    if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
        if ( in_array('person_schedule', $_SESSION['userdata']['access_right_levels']) ) {
            $right_edit 	= true;
        }

        if ( in_array('person_schedule_super_rights', $_SESSION['userdata']['access_right_levels']) ) {
            $super_right 	= true;
        }

        if ( in_array('person_schedule_edit', $_SESSION['userdata']['access_right_levels']) ) {
            $save_right 	= true;
        }

        if ( in_array('auto_schedule', $_SESSION['userdata']['access_right_levels']) ) {
            $auto_schedule 	= true;
        }
    }

    //debug($_SESSION);

    $template->assign( 'right_edit', 	$right_edit );
    $template->assign( 'super_right', 	$super_right );
    $template->assign( 'auto_schedule', $auto_schedule );
    $template->assign( 'save_right', 	$save_right );
?>