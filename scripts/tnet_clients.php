<?php
	include_once( "../include/validate.inc.php" );
	
	// bazata sod
	$sod		= "sod";
	$telepol	= "telepol_net";

	function debug( $array ) {
		echo "<pre>"; print_r($array); echo "<pre>";
	}

	//$link = mysql_connect('213.91.252.134', 'lamerko', 'Olig0fren');
	//$link = mysql_connect('localhost', 'root', '');
	if ( !$link ) {
	   die('Не мога да се конектна: ' . mysql_error());
	}

	$db_selected = mysql_select_db($telepol, $link);
	if ( !$db_selected ) {
	   die ('Не мога да селектна: ' . mysql_error());
	}	


	//$link2 = mysql_connect('213.91.252.129', 'lamerko', 'Olig0fren', 1);
	//$link2 = mysql_connect('172.16.10.254:3307', 'lamerko', 'Olig0fren', 1);
	if ( !$link2 ) {
	   die('Не мога да се конектна: ' . mysql_error());
	}

	$db_selected2 = mysql_select_db($sod, $link2);
	if ( !$db_selected2 ) {
	   die ('Не мога да селектна: ' . mysql_error());
	}	
	
	function trimEin( $ein ) {
		$oValidate 	= new Validate();
		$sEin 		= "";
		$aMatrix	= array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
		
		for ( $i = 0; $i < strlen($ein); $i++ ) {
			
			if ( in_array($ein[$i], $aMatrix) ) {
	 			$sEin .= $ein[$i];
	 			
	 			if (strlen($sEin) > 8 ) {
					$oValidate->variable = $sEin;
					$oValidate->checkEIN();
					
					if ( $oValidate->result ) {
						return $sEin;
					} else {
						$oValidate->checkEGN();
						
						if ( $oValidate->result ) {
							return $sEin;
						}
					}
	 			}
			}
		}

		return false;
	}
	
	function getClient( $aData, &$nID ) {
		global $link, $link2, $sod;
		
		$name 		= isset($aData['name']) 	? $aData['name'] : "";
		$address 	= isset($aData['address']) 	? $aData['address'] : "";
		$phone 		= isset($aData['phone']) 	? $aData['phone'] : "";
		$ein 		= isset($aData['ein']) 		? $aData['ein'].$aData['suf'] : "";
		$ein_dds 	= isset($aData['ein_dds']) 	? $aData['ein_dds'] : "";
		$mol 		= isset($aData['mol']) 		? $aData['mol'] : "";		
		
		$rClient1 	= mysql_query( "SELECT id FROM {$sod}.clients WHERE invoice_ein = '{$ein}' LIMIT 1", $link2 );
		$rowCL 		= mysql_fetch_row($rClient1);
		
		if ( isset($rowCL[0]) && !empty($rowCL[0]) ) {
			$nID = $rowCL[0];
		} else {			
			$resultutf = mysql_query( "SET NAMES UTF8", $link2 );
			
			$insQuery = "
				INSERT INTO {$sod}.clients 
					( name, address, email, phone, note, invoice_address, invoice_ein, invoice_ein_dds, invoice_mol, invoice_recipient, invoice_bring_to_object, invoice_layout, invoice_payment, invoice_email, updated_user, updated_time )
				VALUES
					(
						'{$name}',
						'{$address}',
						'',
						'{$phone}',
						'',
						'{$address}',
						'{$ein}',
						'{$ein_dds}',
						'{$mol}',
						'{$mol}',
						0,
						'total',
						'cash',
						'',
						35,
						NOW()
					)
			";

			$result33 = mysql_query( $insQuery, $link2 );

			$nID = mysql_insert_id($link2);			
		}
	}

	function getClient2( $aData, &$nID ) {
		global $link, $link2, $sod;
		
		$nIDMaster	= isset($aData['id_mas']) 	? $aData['id_mas']	: 0;
		$name 		= isset($aData['name']) 	? $aData['name']	: "";
		$address 	= isset($aData['address']) 	? $aData['address'] : "";
		$phone 		= isset($aData['phone']) 	? $aData['phone']	: "";
		$ein 		= isset($aData['ein']) 		? $aData['ein'].$aData['suf'] : "";
		$ein_dds 	= isset($aData['ein_dds']) 	? $aData['ein_dds'] : "";
		$mol 		= isset($aData['mol']) 		? $aData['mol']		: "";		
		
		//$rClient2 	= mysql_query( "SELECT id_client FROM {$sod}.clients_objects WHERE id_object = (SELECT id FROM {$sod}.objects WHERE id_oldobj = {$nIDMaster} ) LIMIT 1", $link2 );
		if ( !empty($ein) ) {
			$rClient2 	= mysql_query( "SELECT id FROM {$sod}.clients WHERE invoice_ein = '{$ein}' LIMIT 1", $link2 );
			$rowCL 		= mysql_fetch_row($rClient2);
		}
		
		if ( isset($rowCL[0]) && !empty($rowCL[0]) ) {
			$nID = $rowCL[0];
		} else {	
			if ( !empty($ein) ) {
				$result32 = mysql_query( "SET NAMES UTF8", $link2 );
				
				$insQuery = "
					INSERT INTO {$sod}.clients 
						( name, address, email, phone, note, invoice_address, invoice_ein, invoice_ein_dds, invoice_mol, invoice_recipient, invoice_bring_to_object, invoice_layout, invoice_payment, invoice_email, updated_user, updated_time )
					VALUES
						(
							'{$name}',
							'{$address}',
							'',
							'{$phone}',
							'',
							'{$address}',
							'{$ein}',
							'{$ein_dds}',
							'{$mol}',
							'{$mol}',
							0,
							'total',
							'cash',
							'',
							35,
							NOW()
						)
				";

				$result33 = mysql_query( $insQuery, $link2 );

				$nID = mysql_insert_id($link2);	
			} else {
				$nID = 0;
			}
		}
	}
	
	$sQuery = "
		SELECT 
			o.id_obj as id,
			o.firm_name as name,
			o.bulstat as ein,
			IF ( LENGTH(o.tax_num) > 0, 1, 0 ) as dds,
			o.address_reg as address,
			f.name as mol,
			f.phone as phone
		FROM objects o
		LEFT JOIN faces f ON f.id_face = o.id_face
		WHERE o.id_status != 4
			AND (o.id_master_obj = 0 OR o.id_master_obj IS NULL)
		HAVING ein > 8
	";
	//WHERE LENGTH(o.bulstat) >= 9

	$result_re = mysql_query( $sQuery, $link );
	
	$aData = array();
	
	while ($row = mysql_fetch_assoc($result_re)) { 
		if ( strlen($row['ein']) == 13 ) {
			$ein = substr($row['ein'], 0, 9);
			$suf = substr($row['ein'], -4);
		} else {
			$ein = $row['ein'];
			$suf = "";
		}

		$ein = trimEin($ein);
		
		//echo $ein;
		if ( $ein > 0 ) {
			$nID				= 0;
			$nIDObj				= $row['id'];
			$aData 				= array();
			$aData['ein']		= $ein;
			$aData['suf']		= $suf;

			if ( ($row['dds'] == 1) && (strlen($ein) == 9) ) {
				$aData['ein_dds'] = "BG".$ein;
			} else {
				$aData['ein_dds'] = "";
			}

			//$aData['ein_dds'] 	= strlen($ein) == 9 ? "BG".$ein : "";
			$aData['name']		= !empty($row['name']) ? mysql_real_escape_string(str_replace("''", "\"", iconv("CP1251", "UTF-8", $row['name']))) : "";
			$aData['address']	= !empty($row['address']) ? mysql_real_escape_string(str_replace("''", "\"", iconv("CP1251", "UTF-8", $row['address']))) : "";
			$aData['phone'] 	= mysql_real_escape_string($row['phone']);
			$aData['mol'] 		= mysql_real_escape_string(iconv("CP1251", "UTF-8", $row['mol']));

			getClient( $aData, $nID );
			
			if ( !empty($nID) ) {
				$sQueryObjects = "
					INSERT INTO {$sod}.clients_objects
						( id_client, id_object, attach_date, updated_user, updated_time, to_arc )
					SELECT {$nID}, id, NOW(), 35, NOW(), 0 FROM {$sod}.objects
					WHERE id_oldobj = {$nIDObj}
				";	

				$result44 = mysql_query( $sQueryObjects, $link2 );

				$sUpdateObjects = " UPDATE {$sod}.objects SET id_client = {$nID} WHERE id_oldobj = {$nIDObj} ";	
				
				$result45 = mysql_query( $sUpdateObjects, $link2 );
			}		
		} 
	}	

	$sQuery = "
		SELECT 
			o.id_obj as id,
			o.id_master_obj as id_master,
			o.firm_name as name,
			o.bulstat as ein,
			IF ( LENGTH(o.tax_num) > 0, 1, 0 ) as dds,
			o.address_reg as address,
			f.name as mol,
			f.phone as phone
		FROM objects o
		LEFT JOIN faces f ON f.id_face = o.id_face
		WHERE o.id_status != 4
			AND (o.id_master_obj > 0)
		HAVING ein > 8
	";
	//WHERE LENGTH(o.bulstat) >= 9

	$result_ri = mysql_query( $sQuery, $link );
	
	$aData = array();
	
	while ($row = mysql_fetch_assoc($result_ri)) { 
		if ( strlen($row['ein']) == 13 ) {
			$ein = substr($row['ein'], 0, 9);
			$suf = substr($row['ein'], -4);
		} else {
			$ein = $row['ein'];
			$suf = "";
		}

		$ein = trimEin($ein);
		
		$nID				= 0;
		$nIDObj				= $row['id'];
		$aData 				= array();
		$aData['id_mas']	= $row['id_master'];
		$aData['ein']		= $ein;
		$aData['suf']		= $suf;
		
		if ( ($row['dds'] == 1) && (strlen($ein) == 9) ) {
			$aData['ein_dds'] = "BG".$ein;
		} else {
			$aData['ein_dds'] = "";
		}
		
		//$aData['ein_dds'] 	= strlen($ein) == 9 ? "BG".$ein : "";
		$aData['name']		= !empty($row['name']) ? mysql_real_escape_string(str_replace("''", "\"", iconv("CP1251", "UTF-8", $row['name']))) : "";
		$aData['address']	= !empty($row['address']) ? mysql_real_escape_string(str_replace("''", "\"", iconv("CP1251", "UTF-8", $row['address']))) : "";
		$aData['phone'] 	= mysql_real_escape_string($row['phone']);
		$aData['mol'] 		= mysql_real_escape_string(iconv("CP1251", "UTF-8", $row['mol']));

		getClient2( $aData, $nID );
			
		if ( !empty($nID) ) {
			$sQueryObjects = "
				INSERT INTO {$sod}.clients_objects
					( id_client, id_object, attach_date, updated_user, updated_time, to_arc )
				SELECT {$nID}, id, NOW(), 35, NOW(), 0 FROM {$sod}.objects
				WHERE id_oldobj = {$nIDObj}
			";	

			$result54 = mysql_query( $sQueryObjects, $link2 );

			$sUpdateObjects = " UPDATE {$sod}.objects SET id_client = {$nID} WHERE id_oldobj = {$nIDObj} ";	
			
			$result55 = mysql_query( $sUpdateObjects, $link2 );
		}		
	}
	
	echo "OK";
?>