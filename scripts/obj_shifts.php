<?php
	function debug( $array ) {
		echo "<pre>"; print_r($array); echo "<pre>";
	}

	//$link = mysql_connect('213.91.252.129', 'lamerko', 'Olig0fren');
	$link = mysql_connect('172.16.10.254:3307', 'lamerko', 'Olig0fren');
	if ( !$link ) {
	   die('Не мога да се конектна: ' . mysql_error());
	}

	$db_selected = mysql_select_db('sod', $link);
	if ( !$db_selected ) {
	   die ('Не мога да селектна: ' . mysql_error());
	}	
	
	$result = mysql_query( "SET NAMES UTF8", $link );

	$sQuery = "
		SELECT 
			id,
			shiftFrom,
			shiftTo,
			duration,
			stake,
			stake_duty
		FROM sod.object_shifts
	";
	
	$result = mysql_query( $sQuery, $link );
	
	$aData = array();
	
	while ($row = mysql_fetch_assoc($result)) {
		
		$nID		= is_numeric($row['id'])	? $row['id'] : 0;
		$shiftFrom 	= isset($row['shiftFrom'])	? $row['shiftFrom'] : "00:00:00";
		$shiftTo 	= isset($row['shiftTo'])	? $row['shiftTo'] : "00:00:00";
		$duration 	= isset($row['duration'])	? $row['duration'] : "00:00:00";
		$stake		= isset($row['stake'])		? $row['stake'] : 0;
		$stake_duty	= isset($row['stake_duty']) ? $row['stake_duty'] : 0;

		$f			= explode(":", $shiftFrom);
		$t			= explode(":", $shiftTo);
		$d			= explode(":", $duration);
		$st			= $stake;
		$hours		= "00:00:00";
	
		if ( isset($f[0]) && isset($f[1]) && isset($t[0]) && isset($t[1]) ) {
			$f_time = $f[0] * 3600 + $f[1] * 60;
			$t_time = $t[0] * 3600 + $t[1] * 60;
			$d_time = $d[0] * 3600 + $d[1] * 60;
			$dt 	= $d_time / 3600;
					
			if ( $t_time < $f_time ) {
				// Смяната преминава на другия ден
				$day = 24 * 60 * 60;
				$time_stamp = ($day - $f_time) + $t_time;
			} elseif ( ($t_time == $f_time) && ($t_time != "00:00:00") ) {
				$time_stamp = 24 * 60 * 60;
			} else {
				$time_stamp = $t_time - $f_time;
			}
					
			$h = floor($time_stamp / 3600);
			$m = floor(($time_stamp - ($h * 3600)) / 60);
					
			if ( strlen($h) == 1 ) {
				$h = "0".$h;
			}
					
			if ( strlen($m) == 1 ) {
				$m = "0".$m;
			}		
					
			$hours = $h.":".$m.":00";
			
			if ( $duration == "00:00:00" ) {
				$duration = $hours;
			}
					
			if ( ($stake == 0) || ($time_stamp == 0) ) {
				$st = 0;
			} else {			
				//$st = $duration / ( ($time_stamp / 3600) * $stake );
				$st = ($dt / ($time_stamp / 3600)) * $stake;	
			}
		}

		$insQuery = "
			UPDATE sod.object_shifts
			SET 
				duration = '{$duration}',
				stake_duty = '{$st}'
			WHERE id = {$nID}

		";
				
		//debug($insQuery);
		$result2 = mysql_query( $insQuery, $link );
		
		if ( !$result2 ) {
		    echo 'Не мога да изпълня: ' . mysql_error();
		    exit;
		}
	}	
	
	echo "OK";
?>