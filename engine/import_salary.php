<?
	if( isset($_FILES['browse_file']) && !empty($_FILES['browse_file']['tmp_name']) )
	{
		$sTmpName  = $_FILES['browse_file']['tmp_name'];
		
		$sFileType = $_FILES['browse_file']['type'];
		$sFileName = $_SESSION['BASE_DIR'].'/storage/salary_import_'.substr(session_id(),6,6).'.xls';
		move_uploaded_file($sTmpName, $sFileName);
		print("
			<script>
				if( el=window.opener.document.getElementById('uplaoded_file_name') ) 
				{
					el.value='{$sFileName}';
					if( el=window.opener.document.getElementById('uplaoded_file_type') ) 
					{
						el.value='{$sFileType}';
					}
					window.opener.loadXMLDoc('uplaod_file');
				}
				window.close();\n 
			</script>
		");
	}
	
?>