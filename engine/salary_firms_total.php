<?php
	$nDate = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
	
	$template->assign( 'year', date('Y', $nDate) );
	$template->assign( 'month', date('m', $nDate) );
?>