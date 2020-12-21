<?php
	$nDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	
	$template->assign( 'year', date('Y', $nDate) );
	$template->assign( 'month', date('m', $nDate) );
?>