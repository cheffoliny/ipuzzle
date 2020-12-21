<?php
	$date_now = date("d.m.Y");
	$time_now = date("H:i");

	$date_first = "01.".date("m.Y");
	
	$template->assign("date_now", $date_now);
	$template->assign("time_now", $time_now);
	$template->assign("date_first", $date_first);
?>