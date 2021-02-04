<?php

$nID = $_GET['id'];
$nIDPerson = $_GET['id_person'];

if(isset($_SESSION['leave_tmp'])) {
    $nID = $_SESSION['leave_tmp']['id'];
    $nIDPerson = $_SESSION['leave_tmp']['id_person'];
    unset($_SESSION['leave_tmp']);
}

$bRightResolute = false;
$pdf_person_leave_without_num_and_date = false;
$pdf_person_leave_without_num_and_date_userdata = false;

if (!empty($_SESSION['userdata']['access_right_levels'])) {
    if (in_array('setup_person_leave_resolution', $_SESSION['userdata']['access_right_levels'])) {
        $bRightResolute = true;
    }

    if (in_array('tiger_pdf_person_leave', $_SESSION['userdata']['access_right_levels'])) {
        $pdf_person_leave_without_num_and_date_userdata = true;
    }
}

$oAccessLevel = new DBAccessLevel();
if (!empty($oAccessLevel->select("SELECT 1 FROM access_level where name = 'tiger_pdf_person_leave'"))) {
    $pdf_person_leave_without_num_and_date = true;
}

$template->assign('nID',$nID);
$template->assign('nIDPerson',$nIDPerson);
$template->assign('bRightResolute',$bRightResolute);
$template->assign('pdf_person_leave_without_num_and_date',$pdf_person_leave_without_num_and_date);
$template->assign('pdf_person_leave_without_num_and_date_userdata',$pdf_person_leave_without_num_and_date_userdata);