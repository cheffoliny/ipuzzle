<?php

    $right_edit = false;
    $right_nap = false;

    if (!empty($_SESSION['userdata']['access_right_levels'])) {
        if (in_array('objects_edit', $_SESSION['userdata']['access_right_levels'])) {
            $right_edit = true;
        }
        if (in_array('nap_view', $_SESSION['userdata']['access_right_levels'])) {
            $right_nap = true;
        }
    }


    $nIDFirm 	= isset($_GET['nIDFirm']) 	&& !empty($_GET['nIDFirm']) ? $_GET['nIDFirm'] 	: 0;
    $nIDOffice 	= isset($_GET['nIDOffice']) && !empty($_GET['nIDOffice']) ? $_GET['nIDOffice'] 	: 0;
    $dFrom		= isset($_GET['dFrom']) 	&& !empty($_GET['dFrom']) 	? $_GET['dFrom'] 	: "";
    $dTo		= isset($_GET['dTo']) 		&& !empty($_GET['dTo']) 	? $_GET['dTo'] 		: "";
    $nOpenWindow= isset($_GET['nWindow']) && !empty($_GET['nWindow']) ? $_GET['nWindow'] : 0;
    $sPaidTo	= isset($_GET['sPaidTo']) && !empty($_GET['sPaidTo']) ? $_GET['sPaidTo'] : "";

    $template->assign('id_firm', 	$nIDFirm);
    $template->assign('id_reg', 	$nIDOffice);
    $template->assign('sFromDate', 	$dFrom);
    $template->assign('sToDate', 	$dTo);
    $template->assign('sPaidTo', 	$sPaidTo );
    $template->assign('nOpenWindow',	$nOpenWindow);
    $template->assign('right_edit', $right_edit );
    $template->assign('right_nap', $right_nap );

?>