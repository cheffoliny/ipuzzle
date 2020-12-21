<?
	$nDate = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	
	$template->assign( 'year', date('Y', $nDate) );
	$template->assign( 'month', date('m', $nDate) );
	
	if( isset($_FILES['browse_file']) && !empty($_FILES['browse_file']['tmp_name']) )
	{
		$sTmpName  = $_FILES['browse_file']['tmp_name'];
		
		$sFileType = $_FILES['browse_file']['type'];
		$sFileName = $_SESSION['BASE_DIR'].'/storage/salary_import_gsm_'.substr(session_id(),6,6).'.xls';
		move_uploaded_file($sTmpName, $sFileName);
		
		$nYear 	= (int) !empty( $_POST['year'] ) 	? $_POST['year'] 	: 0;  
		$nMonth = (int) !empty( $_POST['month'] ) 	? $_POST['month'] 	: 0;  
		$nYearMonth = $nYear*100 + $nMonth;
		
		print("
			<script>
				if( el=window.opener.document.getElementById('uplaoded_file_name') ) 
				{
					el.value='{$sFileName}';
					if( el=window.opener.document.getElementById('uplaoded_file_type') ) 
					{
						el.value='{$sFileType}';
					}
					if( el=window.opener.document.getElementById('year_month') ) 
					{
						el.value='{$nYearMonth}';
					}
					window.opener.loadXMLDoc('uplaod_file_gsm');
				}
				window.close();\n 
			</script>
		");
	}
	
?>