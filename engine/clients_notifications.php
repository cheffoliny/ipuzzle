<?php

    $oDBNotificationsEvents = new DBNotificationsEvents();

    $sQ = "SELECT *
            FROM notifications_events ";


//    $aEvents = $oDBNotificationsEvents->getAll();
    $aEvents = $oDBNotificationsEvents->select($sQ);

    $right_edit = false;
    if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
    {
        if( in_array( 'clients_notifications.', $_SESSION['userdata']['access_right_levels'] ) )
        {
            $right_edit = true;
        }
    }

    $template->assign( 'right_edit', $right_edit );

    //require_once('engine/clients_tabs.php');

    $template->assign('aDates', array(
        'endDate' => date("d.m.Y"),
        'startDate' => date("d.m.Y",strtotime('first day of this month'))
    ));
    $template->assign("view", $view);
    $template->assign('aEvents' , $aEvents);

?>